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
        $data = $request->validate([
            'login'    => ['required', 'string', 'max:150'],
            'password' => ['required'],
        ]);

        $loginInput   = trim($data['login']);
        $numericLogin = preg_replace('/\D/', '', $loginInput);
        $credentials  = ['password' => $data['password']];

        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $loginInput;
        } elseif (strlen($numericLogin) >= 10 && strlen($numericLogin) <= 11) {
            $credentials['phone'] = $numericLogin;
        } else {
            return back()
                ->withErrors(['login' => 'Enter a valid email or 10/11-digit mobile number.'])
                ->withInput();
        }

        if (Auth::guard('sales')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/sales/dashboard');
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
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
        $sales->status = 0;
        $sales->password = Hash::make($data['password']);
        $sales->save();

        Auth::guard('sales')->login($sales);

        return redirect('/sales/dashboard')
        ->with([
            'logos' => $logos,
            'headerLogo' => $headerLogo,
        ]);
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
        return view('sales.dashboard', compact('logos', 'headerLogo'));
    }
}


