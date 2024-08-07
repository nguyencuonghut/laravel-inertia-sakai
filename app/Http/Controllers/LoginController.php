<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('Auth/Login');
    }

    public function handleLogin(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        $messages = [
            'email.required' => 'Bạn phải nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Bạn phải nhập mật khẩu.'
        ];
        $credentials = $request->validate($rules, $messages);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended();
        }

        return back()->withErrors([
            'password' => 'Email hoặc mật khẩu không khớp!'
        ]);
    }

    public function handleLogout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
