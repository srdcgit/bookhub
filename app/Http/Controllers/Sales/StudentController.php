<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\InstitutionManagement;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Session::put('page', 'students');

        $students = Student::where('added_by', Auth::guard('sales')->user()->id)->with('institution')->orderBy('id', 'desc')->get();

        return view('sales.students.index')->with(compact('students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Session::put('page', 'students');

        $institutions = InstitutionManagement::where('status', 1)->orderBy('name')->get();

        return view('sales.students.create')->with(compact('institutions'));
    }

    /**
     * Store a newly created resource in storage.
     */
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
        $data['status'] = 0;
        $data['added_by'] = Auth::guard('sales')->user()->id;

        Student::create($data);

        return redirect('sales/students')->with('success_message', 'Student has been added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        Session::put('page', 'students');

        $student = Student::findOrFail($id);

        return view('sales.students.show')->with(compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        Session::put('page', 'students');

        $student = Student::findOrFail($id);
        $institutions = InstitutionManagement::where('status', 1)->orderBy('name')->get();

        return view('sales.students.edit')->with(compact('student', 'institutions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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

        $student = Student::findOrFail($id);
        $data = $request->all();

        $student->update($data);

        return redirect('sales/students')->with('success_message', 'Student has been updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect('sales/students')->with('success_message', 'Student has been deleted successfully');
    }
}
