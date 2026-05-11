<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        $school = $request->attributes->get('school');

        return view('auth.login', [
            'school' => $school,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:admin,school_admin,teacher,parent,student',
        ]);

        $school = $request->attributes->get('school');

        // Find user from school-specific database
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'])->withInput();
        }

        // For non-admin roles, verify user belongs to this school
        if (!in_array($request->role, ['admin']) && $school) {
            if ($user->school_id !== $school->id) {
                return back()->withErrors(['email' => 'ไม่พบผู้ใช้นี้ในสาขานี้'])->withInput();
            }
        }

        if ($user->role !== $request->role) {
            return back()->withErrors(['role' => 'บทบาทไม่ตรงกับบัญชีนี้'])->withInput();
        }

        Auth::login($user);

        $request->session()->regenerate();

        // Store school in session for later use
        if ($school) {
            $request->session()->put('school_id', $school->id);
            $request->session()->put('school_domain', $school->domain);
        }

        return $this->redirectToDashboard($user->role);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function redirectToDashboard(string $role): \Illuminate\Http\RedirectResponse
    {
        return match($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'school_admin' => redirect()->route('school_admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'parent' => redirect()->route('parent.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
