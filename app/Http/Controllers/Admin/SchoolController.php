<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schools = School::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.schools.index', compact('schools'))->with('page', 'schools');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.schools.create')->with('page', 'schools');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'principal_name' => 'nullable|string|max:255',
            'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'total_students' => 'nullable|integer|min:0',
            'total_teachers' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        School::create($request->all());

        return redirect()->route('admin.schools.index')
            ->with('success', 'School created successfully!')
            ->with('page', 'schools');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $school = School::findOrFail($id);
        return view('admin.schools.show', compact('school'))->with('page', 'schools');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $school = School::findOrFail($id);
        return view('admin.schools.edit', compact('school'))->with('page', 'schools');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'principal_name' => 'nullable|string|max:255',
            'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'total_students' => 'nullable|integer|min:0',
            'total_teachers' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $school = School::findOrFail($id);
        $school->update($request->all());

        return redirect()->route('admin.schools.index')
            ->with('success', 'School updated successfully!')
            ->with('page', 'schools');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $school = School::findOrFail($id);
        $school->delete();

        return redirect()->route('admin.schools.index')
            ->with('success', 'School deleted successfully!')
            ->with('page', 'schools');
    }
}
