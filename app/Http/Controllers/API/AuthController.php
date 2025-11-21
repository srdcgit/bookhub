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


        $userTypes = [
            'superadmin' => Admin::class, 
            'vendor'     => Admin::class, 
            'sales'      => SalesExecutive::class,
            'user'       => User::class,
        ];

        foreach ($userTypes as $type => $model) {

            $user = $model::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {

                if (in_array($type, ['superadmin', 'vendor'])) {
                    if ($user->type !== $type) {
                        continue;
                    }
                }


                if (isset($user->status) && $user->status == 0) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Your account is inactive. Please contact admin.',
                    ], 403);
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


        $type = 'user';
        if ($user instanceof Admin) {
            $type = $user->type; 
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:sales_executives,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|min:6|confirmed',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $sales = SalesExecutive::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => '0',
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Sales Executive registered successfully. Please log in to continue.',
            'data' => $sales,
        ], 201);
    }
}
