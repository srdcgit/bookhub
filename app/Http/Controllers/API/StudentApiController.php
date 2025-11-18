<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Admin;
use App\Models\SalesExecutive;
use Illuminate\Support\Facades\Auth;
use App\Models\InstitutionManagement;
use Illuminate\Validation\ValidationException;


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

    // ✅ Get all students (only for superadmin)
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
            // Superadmin can see all students
            $students = Student::with('institution')->orderBy('id', 'desc')->get();
        } else {
            // Sales can see only students added by them
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


    // ✅ Add student
    public function store(Request $request)
    {
        $user = $request->user();
        $type = $this->detectUserType($user);

        if (!in_array($type, ['superadmin', 'sales'])) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied! Only Superadmin or Sales can add students.'
            ], 403);
        }

        // ✅ Validation with unique rules
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:students,email',
                'phone' => 'required|string|min:10|max:15|unique:students,phone',
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

        // Institution check
        if (!empty($validated['institution_id'])) {
            $institution = InstitutionManagement::find($validated['institution_id']);
            if ($institution && $institution->status == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'This institution is inactive. You cannot add students.'
                ], 403);
            }
        }

        // Status based on role
        $validated['status'] = ($type === 'superadmin') ? 1 : 0;
        $validated['added_by'] = $user->id;

        $student = Student::create($validated);

        return response()->json([
            'status' => true,
            'message' => ucfirst($type) . ' added student successfully.',
            'data' => $student
        ], 201);
    }



    // ✅ Update student
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

        // ✅ Sales can only update their own students
        if ($type === 'sales' && $student->added_by !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied! You can only update students added by you.'
            ], 403);
        }

        // ✅ Validation
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|min:10|max:15',
            'institution_id' => 'nullable|exists:institution_managements,id',
            'class' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female,other',
            'dob' => 'required|date|before:today',
            'roll_number' => 'nullable|string|max:255',
        ];

        // ✅ Superadmin can update status
        if ($type === 'superadmin') {
            $validationRules['status'] = 'boolean';
        }

        $validated = $request->validate($validationRules);

        // ❌ Prevent Sales from updating status
        if ($type !== 'superadmin') {
            unset($validated['status']);
        }

        $student->update($validated);

        return response()->json([
            'status' => true,
            'message' => ucfirst($type) . ' updated student successfully.',
            'data' => $student
        ], 200);
    }


    // ✅ Delete student
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

        // ✅ Sales can delete only their own students
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
