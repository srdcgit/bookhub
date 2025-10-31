<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\InstitutionManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class StudentController extends Controller
{
    public function index()
    {
        Session::put('page', 'students');

        $students = Student::with('institution')->orderBy('id', 'desc')->get();

        return view('admin.students.index')->with(compact('students'));
    }

    public function create()
    {
        Session::put('page', 'students');

        $institutions = InstitutionManagement::orderBy('name')->get();

        return view('admin.students.create')->with(compact('institutions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|min:10|max:15',
            'institution_id' => 'nullable|exists:institution_managements,id',
            'class' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female,other',
            'dob' => 'required|date|before:today',
            'roll_number' => 'nullable|string|max:255',
        ]);

        $data = $request->all();

        
        $data['status'] = 1;
        $data['added_by'] = Auth::guard('admin')->user()->id;

        Student::create($data);

        return redirect('admin/students')->with('success_message', 'Student has been added successfully');
    }

    public function edit($id)
    {
        Session::put('page', 'students');

        $student = Student::findOrFail($id);
        $institutions = InstitutionManagement::orderBy('name')->get();

        return view('admin.students.edit')->with(compact('student', 'institutions'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|min:10|max:15',
            'institution_id' => 'nullable|exists:institution_managements,id',
            'class' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female,other',
            'dob' => 'required|date|before:today',
            'roll_number' => 'nullable|string|max:255',
            'status' => 'boolean'
        ]);

        $student = Student::findOrFail($id);
        $data = $request->all();
        $data['status'] = $request->status;

        $student->update($data);

        return redirect('admin/students')->with('success_message', 'Student has been updated successfully');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect('admin/students')->with('success_message', 'Student has been deleted successfully');
    }
}
