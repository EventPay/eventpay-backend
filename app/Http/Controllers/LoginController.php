<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);


        if (Auth::attempt($credentials)) {
            // Check if the authenticated user is an admin
            if (auth()->user()->admin) {
                return redirect()->route('admin.dashboard');
            } else {
                Auth::logout();
                return redirect()->route('admin_login')->with('error', 'You are not an admin');
            }
        } else {
            // Authentication failed
            return redirect()->route('admin_login')->with('error', 'Invalid credentials');
        }
    }

}
