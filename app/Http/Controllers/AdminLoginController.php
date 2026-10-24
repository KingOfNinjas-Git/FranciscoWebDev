<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    public function index()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $pw = (string) $request->input('password');
        $expected = (string) config('admin.password');
        $ok = $expected !== '' && hash_equals($expected, $pw);

        if ($ok) {
            $request->session()->regenerate();
            $request->session()->put('is_admin', true);
            return redirect()->route('admin.projects.index');
        }

        return redirect()->route('admin.login')->with('error', 'Invalid password');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('is_admin');
        return redirect()->route('admin.login');
    }
}