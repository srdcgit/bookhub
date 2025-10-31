<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\InstitutionManagement;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class StudentApiController extends Controller
{
    // ✅ Get all students (only for superadmin)
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->type !== 'superadmin') {
            return response()->json([
                'status' => false,
                'message' => 'Access denied! Only superadmin can view students.'
            ], 403);
        }

        $students = Student::with('institution')->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => true,
            'message' => 'Students fetched successfully.',
            'data' => $students
        ], 200);
    }

    // ✅ Add student
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->type !== 'superadmin') {
            return response()->json([
                'status' => false,
                'message' => 'Access denied! Only superadmin can add students.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|min:10|max:15',
            'institution_id' => 'nullable|exists:institution_managements,id',
            'class' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female,other',
            'dob' => 'required|date|before:today',
            'roll_number' => 'nullable|string|max:255',
        ]);

        $student = Student::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Student added successfully.',
            'data' => $student
        ], 201);
    }

    // ✅ Update student
    public function update(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || $user->type !== 'superadmin') {
            return response()->json([
                'status' => false,
                'message' => 'Access denied! Only superadmin can update students.'
            ], 403);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'status' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|min:10|max:15',
            'institution_id' => 'nullable|exists:institution_managements,id',
            'class' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female,other',
            'dob' => 'required|date|before:today',
            'roll_number' => 'nullable|string|max:255',
        ]);

        $student->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Student updated successfully.',
            'data' => $student
        ], 200);
    }

    // ✅ Delete student
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || $user->type !== 'superadmin') {
            return response()->json([
                'status' => false,
                'message' => 'Access denied! Only superadmin can delete students.'
            ], 403);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'status' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        $student->delete();

        return response()->json([
            'status' => true,
            'message' => 'Student deleted successfully.'
        ], 200);
    }
}
