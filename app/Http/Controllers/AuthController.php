<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
    {
    public function showLogin() {
        return view('auth.login');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors(['username' => 'Invalid credentials.']);
        }

        if (! Auth::user()->isActive) {
            Auth::logout();
            return back()->withErrors(['username' => 'Account is inactive.']);
        }

        $request->session()->regenerate();

        return match(Auth::user()->role) {
            'admin'      => redirect()->route('admin.index'),
            'hr'         => redirect()->route('hresource.employees.index'),
            'accounting' => redirect()->route('accounting.payroll.index'),
            default      => redirect()->route('employee.profile.index'),
        };
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
