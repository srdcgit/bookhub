<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::orderBy('id','desc')->get();
        Session::put('page', 'authors');
        return view('admin.authors.author', compact('authors'));
    }

    public function add()
    {

        return view('admin.authors.add_author');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $store = Author::create([
            'name' => $request->name,
        ]);
        return redirect()->route('author')->with('success', 'Author name inserted successfully!!');
    }

    public function edit($id)
    {
        $authors = Author::find($id);
        return view('admin.authors.edit_author', compact('authors'));
    }

    public function update(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $update = Author::find($request->id);
        $update->update([
            'name'=> $request->name,
        ]);
        return redirect()->route('author')->with('success', 'Author name updated successfully!!');
    }

    public function delete($id){
        $delete=Author::find($id);
        $delete->delete();
        return redirect()->back();
    }
}
