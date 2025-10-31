<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ebook;
use App\Models\Category;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;


class EbooksController extends Controller
{
    public function index()
    {
        $ebooks = Ebook::with(['category', 'section', 'admin'])->get();
        return view('admin.ebooks.ebooks', compact('ebooks'));
    }

    public function create()
    {
        $categories = Category::all();
        $sections = Section::all();
        return view('admin.ebooks.add_edit_ebook', compact('categories', 'sections'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:ebooks,isbn',
            'author' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image',
            'file' => 'nullable|mimes:pdf,epub',
            'category_id' => 'nullable|exists:categories,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        // Handle file uploads
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('ebooks/covers', 'public');
        }
        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('ebooks/files', 'public');
        }

        $data['admin_id'] = Auth::guard('admin')->id();
        $data['admin_type'] = Auth::guard('admin')->user()->type ?? 'admin';

        Ebook::create($data);

        return redirect()->route('admin.ebooks.index')->with('success_message', 'Ebook added successfully!');
    }

    public function edit($id)
    {
        $ebook = Ebook::findOrFail($id);
        $categories = Category::all();
        $sections = Section::all();
        return view('admin.ebooks.add_edit_ebook', compact('ebook', 'categories', 'sections'));
    }

    public function update(Request $request, $id)
    {
        $ebook = Ebook::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:ebooks,isbn,' . $ebook->id,
            'author' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image',
            'file' => 'nullable|mimes:pdf,epub',
            'category_id' => 'nullable|exists:categories,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('ebooks/covers', 'public');
        }
        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('ebooks/files', 'public');
        }

        $ebook->update($data);

        return redirect()->route('admin.ebooks.index')->with('success_message', 'Ebook updated successfully!');
    }

    public function destroy($id)
    {
        $ebook = Ebook::findOrFail($id);
        $ebook->delete();
        return redirect()->route('admin.ebooks.index')->with('success_message', 'Ebook deleted successfully!');
    }
}
