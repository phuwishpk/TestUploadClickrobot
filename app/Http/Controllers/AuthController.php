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
        return view('auth.login');
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

        return $this->redirectToDashboard($user);
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
        $school = $user->school;
        $role = $user->role;

        // Clear URL defaults to ensure correct subdomain
        \Illuminate\Support\Facades\URL::defaults([]);

        return match($role) {
            'admin' => redirect()->away($this->getAdminUrl()),
            'school_admin' => redirect()->away($this->getSchoolUrl($school, 'school-admin/dashboard')),
            'teacher' => redirect()->away($this->getSchoolUrl($school, 'teacher/dashboard')),
            'parent' => redirect()->away($this->getSchoolUrl($school, 'parent/dashboard')),
            'student' => redirect()->away($this->getSchoolUrl($school, 'student/dashboard')),
            default => redirect()->route('login'),
        };
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
     * Build URL with school subdomain
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

        return "{$protocol}://{$school->slug}.{$baseDomain}:{$port}/{$path}";
    }
}
