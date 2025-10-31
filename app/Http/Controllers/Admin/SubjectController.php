<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SubjectController extends Controller
{

    public function index()
    {
        $subjects = Subject::orderBy('id','desc')->get();
        Session::put('page', 'subjects');
        return view('admin.subject.subject', compact('subjects'));
    }

    public function add()
    {
        return view('admin.subject.add_subject');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $store = Subject::create([
            'name' => $request->name,
        ]);
        return redirect()->route('subject')->with('success', 'Subject inserted successfully');
    }

    public function edit($id)
    {
        $subjects = Subject::find($id);
        return view('admin.subject.edit_subject', compact('subjects'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $update = Subject::find($request->id);
        $update->update([
            'name' => $request->name,
        ]);
        return redirect()->route('subject')->with('success', 'Subject updated successfully');
    }

    public function delete($id)
    {
        $delete = Subject::find($id);
        $delete->delete();
        return redirect()->back()->with('success', 'Subject deleted successfully');
    }
}
