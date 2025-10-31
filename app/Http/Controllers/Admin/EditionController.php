<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Edition;
use Illuminate\Support\Facades\Session;

class EditionController extends Controller
{
    public function index()
    {
        Session::put('page', 'edition');
        $editions = Edition::all();
        return view('admin.edition.edition', compact('editions'));
    }

    public function create()
    {
        return view('admin.edition.edition_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'edition' => 'required|string|max:255',
        ]);
        Edition::create($request->only('edition'));
        return redirect()->route('edition.index')->with('success', 'Edition created successfully.');
    }

    public function edit($id)
    {
        $edition = Edition::findOrFail($id);
        return view('admin.edition.edition_edit', compact('edition'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'edition' => 'required|string|max:255',
        ]);
        $edition = Edition::findOrFail($id);
        $edition->update($request->only('edition'));
        return redirect()->route('edition.index')->with('success', 'Edition updated successfully.');
    }

    public function destroy($id)
    {
        $edition = Edition::findOrFail($id);
        $edition->delete();
        return redirect()->route('edition.index')->with('success', 'Edition deleted successfully.');
    }
}
