<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Country;
use App\Models\District;
use App\Models\InstitutionManagement;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class InstitutionManagementController extends Controller
{
    public function index()
    {
        // $id=Auth::guard('sales')->user()->name;
        $institutions = InstitutionManagement::orderBy('id', 'desc')->paginate(20);

        return view ('sales.institution_managements.index', compact('institutions'));
    }

    public function create()
    {
        return view('sales.institution_managements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'board' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'country_id' => 'required|integer',
            'state_id' => 'required|integer',
            'district_id' => 'required|integer',
            'block_id' => 'nullable|integer',
            'pincode' => 'required|string|max:10',

        ]);

            $data['status'] = 0;
            $data['added_by'] = Auth::guard('sales')->user()->id;

        InstitutionManagement::create($data);

        return redirect()->route('sales.institution_managements.index')
            ->with('success_message', 'Institution has been added successfully');
    }

    public function show($id)
    {
        Session::put('page', 'sales.institution_managements');

        $institution = InstitutionManagement::with(['institutionClasses', 'country', 'state', 'district', 'block'])->findOrFail($id);

        return view('sales.institution_managements.show')->with(compact('institution'));
    }

    public function edit($id)
    {
        Session::put('page', 'sales.institution_managements');

        $institution = InstitutionManagement::with(['institutionClasses', 'country', 'state', 'district', 'block'])->findOrFail($id);
        if (!$institution) {
            return response()->json(['error' => 'Institution not found'], 404);
        }

        return view('sales.institution_managements.edit')->with(compact('institution'));
    }

    public function update(Request $request, $id)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'board' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'district_id' => 'required|string|max:255',
            'block_id' => 'nullable|string|max:255',
            // 'city_id' => 'required|string|max:255',
            'state_id' => 'required|string|max:255',
            'pincode' => 'required|string|max:10',
            'country_id' => 'required|string|max:255',
            // 'status' => 'boolean',
        ];

        // Only require classes array if type is school
        if ($request->input('type') === 'school') {
            $validationRules['classes'] = 'required|array|min:1';
            $validationRules['classes.*.class_name'] = 'required|string|max:255';
            $validationRules['classes.*.strength'] = 'required|integer|min:1';
        }

        $request->validate($validationRules);

        $institution = InstitutionManagement::findOrFail($id);
        $data = $request->all();

        // $data['status'] = $request->status;

        $institution->update($data);

        // Handle institution classes
        if ($request->has('classes') && is_array($request->classes)) {
            // Delete existing classes
            $institution->institutionClasses()->delete();

            // Add new classes
            foreach ($request->classes as $classData) {
                if (!empty($classData['class_name']) && !empty($classData['strength'])) {
                    $institution->institutionClasses()->create([
                        'class_name' => $classData['class_name'],
                        'total_strength' => $classData['strength'],
                    ]);
                }
            }
        }

        return redirect('sales/institution-managements')->with('success_message', 'Institution has been updated successfully');
    }

    public function destroy($id)
    {
        $institution = InstitutionManagement::findOrFail($id);
        $institution->delete();

        return redirect('sales/institution-managements')->with('success_message', 'Institution has been deleted successfully');
    }


    public function getClasses(Request $request)
    {
        $type = $request->input('type');

        if ($type === 'school') {
            $classes = [
                'Nursery', 'LKG', 'UKG',
                'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5',
                'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10',
                'Class 11', 'Class 12'
            ];
        } else {
            $classes = [];
        }

        return response()->json($classes);
    }

    public function getLocationData(Request $request)
    {
        $pincode = $request->input('pincode');

        // Sample location data based on pincode patterns - you can replace this with actual API calls or database queries
        $locationData = [
            'block' => 'Central Block',
            'district' => 'Sample District',
            // 'city' => 'Sample City',
            'state' => 'Sample State',
            'country' => 'India'
        ];

        // You can implement actual location lookup here
        // For example, using a postal code API like India Post API or database lookup
        // For now, we'll provide sample data based on pincode patterns

        if ($pincode) {
            // Simple pincode-based location mapping (you can expand this)
            $pincodeData = [
                '110001' => ['block' => 'New Delhi Block', 'district' =>'New Delhi', 'state' => 'Delhi'],
                '400001' => ['block' => 'Mumbai Block', 'district' => 'Mumbai', 'state' => 'Maharashtra'],
                '560001' => ['block' => 'Bangalore Block', 'district'=> 'Bangalore', 'state' => 'Karnataka'],
                '700001' => ['block' => 'Kolkata Block', 'district'=> 'Kolkata', 'state' => 'West Bengal'],
                '600001' => ['block' => 'Chennai Block', 'district' => 'Chennai', 'state' => 'Tamil Nadu'],
            ];

            if (isset($pincodeData[$pincode])) {
                $locationData = array_merge($locationData, $pincodeData[$pincode]);
            }
        }

        return response()->json($locationData);
    }

    public function getCountries()
    {
        $countries = Country::where('status', true)
            ->pluck('name', 'id')
            ->toArray();

        return response()->json($countries);
    }

    public function getStates(Request $request)
    {
        $countryId = $request->input('country');

        $states = State::where('country_id', $countryId)
            ->where('status', true)
            ->pluck('name', 'id')
            ->toArray();

        return response()->json($states);
    }

    public function getDistricts(Request $request)
    {
        $stateId = $request->input('state');

        $districts = District::where('state_id', $stateId)
            ->where('status', true)
            ->pluck('name', 'id')
            ->toArray();

        return response()->json($districts);
    }

    public function getBlocks(Request $request)
    {
        $districtId = $request->input('district');

        $blocks = Block::where('district_id', $districtId)
            ->where('status', true)
            ->pluck('name', 'id')
            ->toArray();

        return response()->json($blocks);
    }

}


