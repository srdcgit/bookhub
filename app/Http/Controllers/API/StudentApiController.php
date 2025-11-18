<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Admin;
use App\Models\User;
use App\Models\SalesExecutive;
use Illuminate\Support\Facades\Auth;
use App\Models\InstitutionManagement;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class StudentApiController extends Controller
{
    private function detectUserType($user)
    {
        if ($user instanceof Admin && $user->type === 'superadmin') {
            return 'superadmin';
        } elseif ($user instanceof SalesExecutive) {
            return 'sales';
        }

        return null;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $type = $this->detectUserType($user);

        if (!in_array($type, ['superadmin', 'sales'])) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied! Only Superadmin or Sales can view students.'
            ], 403);
        }

        if ($type === 'superadmin') {
            $students = Student::with('institution')->orderBy('id', 'desc')->get();
        } else {
            $students = Student::with('institution')
                ->where('added_by', $user->id)
                ->orderBy('id', 'desc')
                ->get();
        }

        return response()->json([
            'status' => true,
            'message' => ucfirst($type) . ' fetched students successfully.',
            'data' => $students
        ], 200);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $type = strtolower(trim($this->detectUserType($user)));

        if (!in_array($type, ['superadmin', 'sales'])) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied! Only Superadmin or Sales can add students.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:students,email|unique:users,email',
                'phone' => 'required|string|min:10|max:15|unique:students,phone|unique:users,mobile',
                'institution_id' => 'nullable|exists:institution_managements,id',
                'class' => 'required|string|max:255',
                'gender' => 'required|string|in:male,female,other',
                'dob' => 'required|date|before:today',
                'roll_number' => 'nullable|string|max:255|unique:students,roll_number',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }


        if (!empty($validated['institution_id'])) {
            $institution = InstitutionManagement::find($validated['institution_id']);
            if ($institution && $institution->status == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'This institution is inactive. You cannot add students.'
                ], 403);
            }
        }

        $studentStatus = ($type === 'superadmin') ? 1 : 0;
        $validated['status']   = $studentStatus;
        $validated['added_by'] = $user->id;


        $student = Student::create($validated);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['phone'],
            'status' => $studentStatus,
            'password' => Hash::make('12345678'),
        ]);

        return response()->json([
            'status' => true,
            'message' => ucfirst($type) . ' added student successfully.',
            'data' => $student
        ], 201);
    }



    public function update(Request $request, $id)
    {
        $user = $request->user();
        $type = $this->detectUserType($user);

        if (!in_array($type, ['superadmin', 'sales'])) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied! Only Superadmin or Sales can update students.'
            ], 403);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'status' => false,
                'message' => 'Student not found.'
            ], 404);
        }


        if ($type === 'sales' && $student->added_by !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied! You can only update students added by you.'
            ], 403);
        }

        $linkedUserID = User::where('mobile', $student->phone)
            ->orWhere('email', $student->email)
            ->value('id');

        $validationRules = [
            'name' => 'required|string|max:255',

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('students', 'email')->ignore($student->id),
                Rule::unique('users', 'email')->ignore($linkedUserID)
            ],


            'phone' => [
                'required',
                'string',
                'min:10',
                'max:15',
                Rule::unique('students', 'phone')->ignore($student->id),
                Rule::unique('users', 'mobile')->ignore($linkedUserID)
            ],

            'institution_id' => 'nullable|exists:institution_managements,id',
            'class' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female,other',
            'dob' => 'required|date|before:today',
            'roll_number' => Rule::unique('students', 'roll_number')->ignore($student->id),
        ];

        if ($type === 'superadmin') {
            $validationRules['status'] = 'boolean';
        }

        $validated = $request->validate($validationRules);

    
        if ($type !== 'superadmin') {
            unset($validated['status']);
        }

        $student->update($validated);

        $userRecord = User::find($linkedUserID);

        if ($userRecord) {
            $userRecord->update([
                'name'   => $validated['name'],
                'email'  => $validated['email'],
                'mobile' => $validated['phone'],
                'status' => $validated['status'] ?? $userRecord->status, // Only superadmin updates status
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => ucfirst($type) . ' updated student successfully.',
            'data' => $student
        ], 200);
    }



    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $type = $this->detectUserType($user);


        if (!in_array($type, ['superadmin', 'sales'])) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied! Only Superadmin or Sales can delete students.'
            ], 403);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'status' => false,
                'message' => 'Student not found.'
            ], 404);
        }


        if ($type === 'sales' && $student->added_by !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied! You can only delete students added by you.'
            ], 403);
        }

        $student->delete();

        return response()->json([
            'status' => true,
            'message' => ucfirst($type) . ' deleted student successfully.'
        ], 200);
    }
}
