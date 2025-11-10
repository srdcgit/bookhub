<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use App\Models\SalesExecutive;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Define user types and their models
        $userTypes = [
            'superadmin' => Admin::class, // stored in admins table
            'vendor'     => Admin::class, // stored in same admins table
            'sales'      => SalesExecutive::class,
            'user'       => User::class,
        ];

        foreach ($userTypes as $type => $model) {
            $user = $model::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {

                // ✅ check if user's type matches (for admins)
                if (in_array($type, ['superadmin', 'vendor'])) {
                    if ($user->type !== $type) {
                        continue; // skip if not matching
                    }
                }

                $token = $user->createToken("{$type}-token")->plainTextToken;

                return response()->json([
                    'status'  => true,
                    'message' => ucfirst($type) . ' login successful',
                    'type'    => $type,
                    'token'   => $token,
                    'data'    => $user,
                ]);
            }
        }

        return response()->json([
            'status'  => false,
            'message' => 'Invalid credentials',
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logout successful',
        ]);
    }

    public function validateToken(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired token',
            ], 401);
        }

        // ✅ Determine user type
        $type = 'user';
        if ($user instanceof Admin) {
            $type = $user->type; // from DB column
        } elseif ($user instanceof SalesExecutive) {
            $type = 'sales';
        }

        return response()->json([
            'status'  => true,
            'message' => 'Token is valid',
            'type'    => $type,
            'data'    => $user,
        ]);
    }

    public function register(Request $request)
    {
        // ✅ Step 1: Manual validator for custom JSON error handling
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:sales_executives,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|min:6|confirmed',
        ]);

        // ✅ Step 2: If validation fails, return a JSON response
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ Step 3: Create new Sales Executive
        $sales = SalesExecutive::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // ✅ Step 4: Send success response
        return response()->json([
            'status' => true,
            'message' => 'Sales Executive registered successfully. Please log in to continue.',
            'data' => $sales,
        ], 201);
    }
}
