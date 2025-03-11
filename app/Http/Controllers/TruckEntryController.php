<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\LotEntry;
use App\Models\Supplier;
use App\Models\TruckEntry;
use App\Models\Unit;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TruckEntryController extends Controller
{

    public function Truck_Entry()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            // dd($userId); 
            $vendors = Supplier::get();
            $categories = Category::get();
            $varieties = Brand::get();
            $Units = Unit::get();

            return view('admin_panel.Truck_entry.truck_entry', [
                'vendors' => $vendors,
                'categories' => $categories,
                'varieties' => $varieties,
                'Units' => $Units
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'truck_number' => 'required|string',
            'driver_name' => 'required|string',
            'driver_cnic' => 'nullable|string',
            'driver_contact' => 'nullable|string',
            'vendor_id' => 'required|string',
            'entry_date' => 'required|date',
            'category' => 'required|array',
            'variety' => 'required|array',
            'unit' => 'required|array',
            'unit_in' => 'required|array',
            'lot_quantity' => 'required|array'
        ]);

        // Truck Entry Save
        $truckEntry = TruckEntry::create([
            'truck_number' => $request->truck_number,
            'driver_name' => $request->driver_name,
            'driver_cnic' => $request->driver_cnic,
            'driver_contact' => $request->driver_contact,
            'vendor_id' => $request->vendor_id,
            'entry_date' => $request->entry_date
        ]);

        // Lot Entries Save
        foreach ($request->category as $key => $category) {
            LotEntry::create([
                'truck_id' => $truckEntry->id,
                'category' => $category,
                'variety' => $request->variety[$key],
                'unit' => $request->unit[$key],
                'unit_in' => $request->unit_in[$key],
                'lot_quantity' => $request->lot_quantity[$key],
            ]);
        }
        return redirect()->back()->with('success', 'Truck Entry Added Successfully');
    }

    public function Truck_Enters()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            // Truck entries fetch karna
            $truckEntries = TruckEntry::with('lots')->orderBy('id', 'desc')->get();

            return view('admin_panel.Truck_entry.truck_enteries', compact('truckEntries'));
        } else {
            return redirect()->back();
        }
    }

    public function show($id)
    {
        $truckEntry = TruckEntry::with('lots')->findOrFail($id);
        return view('admin_panel.Truck_entry.truck_entry_details', compact('truckEntry'));
    }
}
