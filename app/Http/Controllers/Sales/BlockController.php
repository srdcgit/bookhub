<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Block;
use App\Models\District;

class BlockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Session::put('page', 'blocks');

        $blocks = Block::with('district.state.country')
            ->orderBy('id', 'desc')
            ->get();

        return view('sales.blocks.index')->with(compact('blocks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Session::put('page', 'blocks');

        $districts = District::where('status', true)
            ->with('state.country')
            ->orderBy('name')
            ->get();

        return view('sales.blocks.create')->with(compact('districts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
            'status' => 'boolean'
        ]);

        $data = $request->all();
        $data['status'] = $request->has('status') ? true : false;

        Block::create($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        Session::put('page', 'blocks');

        $block = Block::with('district.state.country')
            ->findOrFail($id);

        return view('sales.blocks.show')->with(compact('block'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        Session::put('page', 'blocks');

        $block = Block::with('district.state.country')
            ->findOrFail($id);
        $districts = District::where('status', true)
            ->with('state.country')
            ->orderBy('name')
            ->get();

        return view('sales.blocks.edit')->with(compact('block','districts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
            'status' => 'boolean'
        ]);

        $block = Block::findOrFail($id);
        $data = $request->all();
        $data['status'] = $request->has('status') ? true : false;

        $block->update($data);

        return redirect('sales/blocks')->with('success_message', 'Block has been updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $block = Block::findOrFail($id);
        $block->delete();

        return redirect('sales/blocks')->with('success_message', 'Block has been deleted successfully');
    }
}
