<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:teacher,parent,student',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'])->withInput();
        }

        if ($user->role !== $request->role) {
            return back()->withErrors(['role' => 'บทบาทไม่ตรงกับบัญชีนี้'])->withInput();
        }

        Auth::login($user);

        $request->session()->regenerate();

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
            'teacher' => redirect()->route('teacher.dashboard'),
            'parent' => redirect()->route('parent.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
