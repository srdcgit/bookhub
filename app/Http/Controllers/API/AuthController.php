<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use App\Models\SalesExecutive;

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
}
