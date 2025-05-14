<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\LotEntry;
use App\Models\Supplier;
use App\Models\TruckEntry;
use App\Models\Unit;
use App\Models\UnitIn;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
            $UnitIns = UnitIn::get();

            return view('admin_panel.Truck_entry.truck_entry', [
                'vendors' => $vendors,
                'categories' => $categories,
                'varieties' => $varieties,
                'Units' => $Units,
                'UnitIns' => $UnitIns
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
                'total_units' => $request->lot_quantity[$key],
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

    public function destroy($id): JsonResponse
    {
        // 1) Load the truck entry and its lots
        $entry = TruckEntry::with('lotEntries.sales')->findOrFail($id);

        // 2) Check each lotEntry for existing sales
        $lotsWithSales = $entry->lotEntries
            ->filter(fn($lot) => $lot->sales->isNotEmpty())
            ->pluck('id')
            ->all();

        if (! empty($lotsWithSales)) {
            return response()->json([
                'message' => 'Cannot delete this truck entry. ' .
                    'The following lot IDs have sales and must be cleared first: ' .
                    implode(', ', $lotsWithSales)
            ], 422);
        }

        // 3) No sales found → safe to permanently delete
        DB::transaction(function () use ($entry) {
            // Permanently drop all related lot_entries
            $entry->lotEntries()->forceDelete();

            // Permanently drop the truck entry
            $entry->forceDelete();
        });

        return response()->json([
            'message' => 'Truck entry and its lot entries have been permanently deleted.'
        ]);
    }

    public function edit($id)
    {
        $truckEntry = TruckEntry::findOrFail($id);
        $lotEntries = LotEntry::where('truck_id', $id)->get();

        $vendors = Supplier::get();
        $categories = Category::all();
        $varieties = Brand::all();
        $Units = Unit::all();
        $UnitIns = UnitIn::all();

        return view('admin_panel.Truck_entry.truck_edit', compact('truckEntry', 'lotEntries', 'vendors', 'categories', 'varieties', 'Units', 'UnitIns'));
    }

    public function update(Request $request, $id)
    {
        $truckEntry = TruckEntry::findOrFail($id);
        $truckEntry->truck_number = $request->truck_number;
        $truckEntry->driver_name = $request->driver_name;
        $truckEntry->driver_cnic = $request->driver_cnic;
        $truckEntry->driver_contact = $request->driver_contact;
        $truckEntry->vendor_id = $request->vendor_id;
        $truckEntry->entry_date = $request->entry_date;
        $truckEntry->save();

        foreach ($request->category as $key => $category) {
            $variety = $request->variety[$key];
            $unit = $request->unit[$key];
            $unit_in = $request->unit_in[$key];
            $lot_quantity = $request->lot_quantity[$key];
            $lot_entry_id = $request->lot_entry_id[$key] ?? null;

            if ($lot_entry_id) {
                $lotEntry = LotEntry::find($lot_entry_id);
                if ($lotEntry) {
                    $lotEntry->lot_quantity = $lot_quantity; // Direct update to the new quantity
                    $lotEntry->total_units = $lot_quantity;
                    $lotEntry->save();
                }
            } else {
                $existingLot = LotEntry::where('truck_id', $truckEntry->id)
                    ->where('category', $category)
                    ->where('variety', $variety)
                    ->where('unit', $unit)
                    ->where('unit_in', $unit_in)
                    ->first();

                if ($existingLot) {
                    // Update existing lot entry directly
                    $existingLot->lot_quantity = $lot_quantity;
                    $existingLot->total_units = $lot_quantity;
                    $existingLot->save();
                } else {
                    // Create new lot entry
                    $lotEntry = new LotEntry();
                    $lotEntry->truck_id = $truckEntry->id;
                    $lotEntry->category = $category;
                    $lotEntry->variety = $variety;
                    $lotEntry->unit = $unit;
                    $lotEntry->unit_in = $unit_in;
                    $lotEntry->lot_quantity = $lot_quantity;
                    $lotEntry->total_units = $lot_quantity;
                    $lotEntry->save();
                }
            }
        }

        return redirect()->back()->with('success', 'Truck Entry updated successfully');
    }
}
