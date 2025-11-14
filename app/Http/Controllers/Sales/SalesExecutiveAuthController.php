<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\HeaderLogo;
use App\Models\SalesExecutive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SalesExecutiveAuthController extends Controller
{
    public function showLogin()
    {
        $logos    = HeaderLogo::first();
        $headerLogo = HeaderLogo::first();
        return view('sales.login', compact('logos', 'headerLogo'));
    }

    public function login(Request $request)
    {
        $logos = HeaderLogo::first();
        $headerLogo = HeaderLogo::first();
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('sales')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/sales/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        $headerLogo = HeaderLogo::first();
        $logos    = HeaderLogo::first();
        return view('sales.register', compact('logos', 'headerLogo'));
    }

    public function register(Request $request)
    {
        $headerLogo = HeaderLogo::first();
        $logos = HeaderLogo::first();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:sales_executives,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $sales = new SalesExecutive();
        $sales->name = $data['name'];
        $sales->email = $data['email'];
        $sales->phone = $data['phone'];
        $sales->password = Hash::make($data['password']);
        $sales->save();

        Auth::guard('sales')->login($sales);

        return redirect('/sales/dashboard', compact('logos', 'headerLogo'));
    }

    public function dashboard()
    {
        $headerLogo = HeaderLogo::first();
        $logos    = HeaderLogo::first();
        return view('sales.dashboard', compact('logos', 'headerLogo'), [
            'user' => auth('sales')->user()
        ]);
    }

    public function logout(Request $request)
    {
        $headerLogo =HeaderLogo::first();
        $logos = HeaderLogo::first();
        Auth::guard('sales')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/sales/login');
    }
}


