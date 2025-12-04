<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\HeaderLogo;
use App\Models\SalesExecutive;
use App\Models\InstitutionManagement;
use App\Models\Student;
use App\Models\InstitutionClass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail; // ✅ IMPORTANT

class SalesExecutiveAuthController extends Controller
{
    // LOGIN ---------------------
    public function showLogin()
    {
        $headerLogo = HeaderLogo::first();
        $logos      = $headerLogo;

        return view('sales.login', compact('logos', 'headerLogo'));
    }

    public function login(Request $request)
    {
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

    // REGISTER (SHOW FORM) ---------------------
    public function showRegister()
    {
        $headerLogo = HeaderLogo::first();
        $logos      = $headerLogo;

        return view('sales.register', compact('logos', 'headerLogo'));
    }

    // SEND OTP ---------------------
    public function sendOtp(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:sales_executives,email', // ✅ correct table
            'phone' => 'required|string|max:20',
        ]);

        $otp = rand(100000, 999999);

        session([
            'otp'       => $otp,
            'user_data' => $request->only('name', 'email', 'phone'),
        ]);

        try {
            Mail::raw("Your OTP is: $otp", function ($message) use ($request) {
                $message->to($request->email)->subject('Verify OTP - BookHub');
            });
        } catch (\Exception $e) {
            // For debugging
            logger()->error('Mail send failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email.',
        ]);
    }

    // FINAL REGISTER (VERIFY OTP + CREATE USER) -------------
    public function register(Request $request)
    {
        // 1. Check OTP
        if (!$request->otp || $request->otp != session('otp')) {
            return back()->with('error', 'Invalid OTP');
        }

        // 2. Validate password fields
        $data = $request->validate([
            'password'              => ['required', 'min:6', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        // 3. Get user data from session
        $userData = session('user_data');

        if (!$userData) {
            return back()->with('error', 'Session expired. Please register again.');
        }

        // 4. Clear OTP/session
        session()->forget('otp');
        session()->forget('user_data');

        // 5. Create Sales Executive
        $sales = new SalesExecutive();
        $sales->name     = $userData['name'];
        $sales->email    = $userData['email'];
        $sales->phone    = $userData['phone'];
        $sales->status   = 0;
        $sales->password = Hash::make($data['password']);
        $sales->save();

        // 6. Login
        Auth::guard('sales')->login($sales);

        return redirect('/sales/dashboard')->with('success', 'Registration successful!');
    }

    // DASHBOARD ---------------------
    public function dashboard()
    {
        $headerLogo = HeaderLogo::first();
        $logos      = $headerLogo;

        $salesExecutive = auth('sales')->user();
        $salesExecutiveId = $salesExecutive->id;

        // Get income_per_target from sales executive
        $incomePerTarget = $salesExecutive->income_per_target ?? 0;

        // Calculate total institutions
        $totalInstitutions = InstitutionManagement::where('added_by', $salesExecutiveId)->count();

        // Calculate total students
        $totalStudents = Student::where('added_by', $salesExecutiveId)->count();

        // Calculate today's students
        $todayStudents = Student::where('added_by', $salesExecutiveId)
            ->whereDate('created_at', Carbon::today())
            ->count();

        // Calculate total classes (sum of classes from all institutions added by this sales executive)
        $institutionIds = InstitutionManagement::where('added_by', $salesExecutiveId)->pluck('id');
        $totalClasses = InstitutionClass::whereIn('institution_id', $institutionIds)->count();

        // Calculate total blocks (distinct blocks from institutions)
        $totalBlocks = InstitutionManagement::where('added_by', $salesExecutiveId)
            ->whereNotNull('block_id')
            ->distinct('block_id')
            ->count('block_id');

        // Calculate earnings
        $totalEarning = $incomePerTarget * $totalStudents;
        $todayEarning = $incomePerTarget * $todayStudents;

        // Prepare graph data for last 30 days
        $days = 30;
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        
        $dates = [];
        $dateKeys = [];
        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($days - 1 - $i);
            $dates[] = $date->format('d M');
            $dateKeys[] = $date->format('Y-m-d');
        }
        
        $studentData = Student::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('added_by', $salesExecutiveId)
            ->whereDate('created_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
        
        $institutionData = InstitutionManagement::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('added_by', $salesExecutiveId)
            ->whereDate('created_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
        
        $studentsCount = [];
        $institutionsCount = [];
        foreach ($dateKeys as $dateKey) {
            $studentsCount[] = $studentData[$dateKey] ?? 0;
            $institutionsCount[] = $institutionData[$dateKey] ?? 0;
        }
        
        // Calculate earnings for graph (students * income_per_target)
        $earningsData = [];
        foreach ($studentsCount as $count) {
            $earningsData[] = $count * $incomePerTarget;
        }

        return view('sales.dashboard', compact(
            'logos',
            'headerLogo',
            'totalInstitutions',
            'totalStudents',
            'todayStudents',
            'totalClasses',
            'totalEarning',
            'todayEarning',
            'incomePerTarget',
            'dates',
            'studentsCount',
            'institutionsCount',
            'earningsData'
        ), [
            'user' => $salesExecutive
        ]);
    }

    // LOGOUT ---------------------
    public function logout(Request $request)
    {
        Auth::guard('sales')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/sales/login');
    }
}
