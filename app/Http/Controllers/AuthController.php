<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show login form - available from any domain
     */
    public function showLoginForm(Request $request)
    {
        $school = null;
        $host = $request->getHost();
        $slug = $this->extractSlug($host);

        if ($slug && $slug !== 'localhost') {
            $school = School::on('mysql')->where('slug', $slug)->first();
        }

        return view('auth.login', compact('school'));
    }

    private function extractSlug(string $host): ?string
    {
        if (preg_match('/^([a-z0-9-]+)\.localhost(?::\d+)?$/i', $host, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Handle login from unified login page
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:admin,school_admin,teacher,parent,student',
        ]);

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'])->withInput();
        }

        // Verify role matches
        if ($user->role !== $request->role) {
            return back()->withErrors(['role' => 'บทบาทไม่ตรงกับบัญชีนี้'])->withInput();
        }

        // For non-admin roles, user must belong to a school
        if (!in_array($user->role, ['admin']) && !$user->school_id) {
            return back()->withErrors(['email' => 'ไม่พบโรงเรียนที่ผูกกับบัญชีนี้'])->withInput();
        }

        // Login user
        Auth::login($user);
        $request->session()->regenerate();

        // Store school info in session
        if ($user->school_id) {
            $request->session()->put('school_id', $user->school_id);
        }

        \Illuminate\Support\Facades\Log::debug('User logged in, role: ' . $user->role . ', school_id: ' . $user->school_id);

        $response = $this->redirectToDashboard($user);
        \Illuminate\Support\Facades\Log::debug('Redirect response prepared');

        return $response;
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear URL defaults
        \Illuminate\Support\Facades\URL::defaults([]);

        // Always redirect to main domain login (no subdomain)
        $baseDomain = config('app.base_domain', 'localhost');
        $port = config('app.port', '8080');

        return redirect("http://{$baseDomain}:{$port}/login");
    }

    /**
     * Redirect to appropriate dashboard based on role
     */
    protected function redirectToDashboard(User $user): \Illuminate\Http\RedirectResponse
    {
        $school = null;
        if ($user->school_id) {
            $school = School::on('mysql')->find($user->school_id);
        }
        $role = $user->role;

        // Clear URL defaults
        \Illuminate\Support\Facades\URL::defaults([]);

        $targetUrl = match($role) {
            'admin' => $this->getAdminUrl(),
            'school_admin' => $this->getSchoolUrl($school, 'school-admin/dashboard'),
            'teacher' => $this->getSchoolUrl($school, 'teacher/dashboard'),
            'parent' => $this->getSchoolUrl($school, 'parent/dashboard'),
            'student' => $this->getSchoolUrl($school, 'student/dashboard'),
            default => "/login",
        };

        // Debug: log the target URL
        \Illuminate\Support\Facades\Log::debug('Login redirect target URL: ' . $targetUrl);

        // Use redirect()->to() for absolute URLs
        return redirect()->to($targetUrl, 302);
    }

    /**
     * Build admin URL (no subdomain, main domain)
     */
    protected function getAdminUrl(): string
    {
        $baseDomain = config('app.base_domain', 'localhost');
        $port = config('app.port', '8080');

        return "http://{$baseDomain}:{$port}/admin/dashboard";
    }

    /**
     * Build URL with school path prefix
     */
    protected function getSchoolUrl($school, string $path): string
    {
        if (!$school || !$school->slug) {
            $defaultSchool = School::first();
            if ($defaultSchool) {
                $school = $defaultSchool;
            } else {
                return '/' . $path;
            }
        }

        $baseDomain = config('app.base_domain', 'localhost');
        $port = config('app.port', '8080');
        $protocol = request()->secure() ? 'https' : 'http';

        // Use path-based routing: /school/{slug}/{path}
        return "{$protocol}://{$baseDomain}:{$port}/school/{$school->slug}/{$path}";
    }
}
