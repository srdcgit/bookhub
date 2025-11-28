<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\HeaderLogo;
use App\Models\SalesExecutive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function edit()
    {
        $logos = HeaderLogo::first();
        $headerLogo = HeaderLogo::first();
        $sales = Auth::guard('sales')->user();
        return view('sales.profile', compact('sales', 'logos', 'headerLogo'));
    }

    public function update(Request $request)
    {
        $logos = HeaderLogo::first();
        $headerLogo = HeaderLogo::first();
        $sales = Auth::guard('sales')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:sales_executives,email,' . $sales->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'block' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'ifsc_code' => ['nullable', 'string', 'max:50'],
            'bank_branch' => ['nullable', 'string', 'max:150'],
            'upi_id' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'confirmed', 'min:6'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        // Fields that can be mass-updated
        $updatable = collect($validated)->except(['password', 'password_confirmation', 'profile_picture'])->toArray();

        /** @var SalesExecutive $sales */
        $sales->fill($updatable);

        if (!empty($validated['password'])) {
            $sales->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_picture')) {
            if (!empty($sales->profile_picture)) {
                if (Str::startsWith($sales->profile_picture, 'assets/')) {
                    File::delete(public_path($sales->profile_picture));
                } else {
                    Storage::disk('public')->delete($sales->profile_picture);
                }
            }

            $destinationPath = public_path('assets/sales/profile_pictures');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $filename = Str::uuid()->toString() . '.' . $request->file('profile_picture')->getClientOriginalExtension();
            $request->file('profile_picture')->move($destinationPath, $filename);
            $sales->profile_picture = 'assets/sales/profile_pictures/' . $filename;
        }

        $sales->save();

        return redirect()->route('sales.profile.edit')->with('success_message', 'Profile updated successfully.');
        return view('sales.profile', compact('sales', 'logos', 'headerLogo'));
    }
}


