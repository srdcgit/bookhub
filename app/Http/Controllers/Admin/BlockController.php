<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\District;
// use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BlockController extends Controller
{
    public function index()
    {
        Session::put('page', 'blocks');

        $blocks = Block::with('district.state.country')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.blocks.index')->with(compact('blocks'));
    }

    public function create()
    {
        Session::put('page', 'blocks');

        $districts = District::where('status', true)
            ->with('state.country')
            ->orderBy('name')
            ->get();

        return view('admin.blocks.create')->with(compact('districts'));
    }

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

        return redirect('admin/blocks')->with('success_message', 'Block has been added successfully');
    }

    public function edit($id)
    {
        Session::put('page', 'blocks');

        $block = Block::findOrFail($id);
        $districts = District::where('status', true)
            ->with('state.country')
            ->orderBy('name')
            ->get();

        return view('admin.blocks.edit')->with(compact('block', 'districts'));
    }

    public function update(Request $request, $id)
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

        return redirect('admin/blocks')->with('success_message', 'Block has been updated successfully');
    }

    public function destroy($id)
    {
        $block = Block::findOrFail($id);
        $block->delete();

        return redirect('admin/blocks')->with('success_message', 'Block has been deleted successfully');
    }

    public function updateStatus(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            if ($data['status'] == "Active") {
                $status = 0;
            } else {
                $status = 1;
            }

            Block::where('id', $data['block_id'])->update(['status' => $status]);
            return response()->json(['status' => $status, 'block_id' => $data['block_id']]);
        }
    }
}
