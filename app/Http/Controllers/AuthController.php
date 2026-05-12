<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show login form - available from any domain
     */
    public function showLoginForm(Request $request)
    {
        \Illuminate\Support\Facades\Log::debug('showLoginForm called', [
            'host' => $request->getHost(),
            'session_id' => $request->session()->getId(),
            'session_token' => $request->session()->token() ?? 'no token',
            'cookie' => $request->cookie('laravel_session') ?? 'no cookie',
        ]);

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
        \Illuminate\Support\Facades\Log::debug('login called', [
            'session_id' => $request->session()->getId(),
            'session_token' => $request->session()->token() ?? 'no token',
            'cookie' => $request->cookie('laravel_session') ?? 'no cookie',
            'has_csrf' => $request->has('_token'),
            'request_token' => $request->input('_token') ?? 'no token',
        ]);

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
     * Consume a short-lived login handoff from the main domain to a school subdomain.
     */
    public function consumeSubdomainLogin(Request $request)
    {
        $token = (string) $request->query('token');
        $cacheKey = 'subdomain-login:' . hash('sha256', $token);
        $payload = Cache::pull($cacheKey);

        if (!$token || !$payload || empty($payload['user_id']) || empty($payload['path'])) {
            return redirect()->route('login')->withErrors(['email' => 'ลิงก์เข้าสู่ระบบหมดอายุ กรุณาเข้าสู่ระบบอีกครั้ง']);
        }

        $user = User::find($payload['user_id']);

        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'ไม่พบบัญชีผู้ใช้ กรุณาเข้าสู่ระบบอีกครั้ง']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->school_id) {
            $request->session()->put('school_id', $user->school_id);
        }

        return redirect()->to($payload['path']);
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

        // Use school subdomains for school users so the browser URL stays
        // bangrak.localhost:8080/teacher/dashboard.
        $targetPath = match($role) {
            'admin' => '/admin/dashboard',
            'school_admin' => '/school-admin/dashboard',
            'teacher' => '/teacher/dashboard',
            'parent' => '/parent/dashboard',
            'student' => '/student/dashboard',
            default => "/login",
        };

        if ($school && $school->slug && $role !== 'admin') {
            $targetUrl = $this->getSchoolUrl($school, ltrim($targetPath, '/'));
            $targetHost = parse_url($targetUrl, PHP_URL_HOST);

            if ($targetHost && $targetHost !== request()->getHost()) {
                $token = Str::random(64);
                Cache::put('subdomain-login:' . hash('sha256', $token), [
                    'user_id' => $user->id,
                    'path' => $targetPath,
                ], now()->addMinute());

                $handoffUrl = $this->getSchoolUrl($school, 'login/consume?token=' . $token);
                \Illuminate\Support\Facades\Log::debug('Redirecting to school subdomain login handoff: ' . $handoffUrl);

                return redirect()->to($handoffUrl);
            }

            \Illuminate\Support\Facades\Log::debug('Redirecting to school subdomain: ' . $targetUrl);
            return redirect()->to($targetUrl);
        }

        \Illuminate\Support\Facades\Log::debug('Redirecting to: ' . $targetPath);
        return redirect()->to($targetPath);
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
     * Build URL with school subdomain: {slug}.{base-domain}/{path}
     */
    protected function getSchoolUrl($school, string $path): string
    {
        if (!$school || !$school->slug) {
            $defaultSchool = School::on('mysql')->where('is_active', true)->first();
            if ($defaultSchool) {
                $school = $defaultSchool;
            } else {
                return '/' . $path;
            }
        }

        $port = config('app.port', '8080');
        $baseDomain = config('app.base_domain', 'localhost');
        $protocol = request()->secure() ? 'https' : 'http';

        return "{$protocol}://{$school->slug}.{$baseDomain}:{$port}/{$path}";
    }
}
