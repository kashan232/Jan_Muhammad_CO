<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    public function unit()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $all_unit = Unit::get();
            // ->map(function ($Unit) {
            //     $Unit->products_count = $Unit->products()->count();
            //     return $Unit;
            // });
            return view('admin_panel.unit.unit', [
                'all_unit' => $all_unit
            ]);
        } else {
            return redirect()->back();
        }
    }
    public function store_unit(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();
            Unit::create([
                'admin_or_user_id' => $userId,
                'unit' => $request->unit,
                'unit_urdu' => $request->unit_urdu, // Save Urdu name
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            return redirect()->back()->with('success', 'Unit Added Successfully');
        } else {
            return redirect()->back();
        }
    }
    public function update_unit(Request $request)
    {
        if (Auth::id()) {
            $update_id = $request->input('unit_id');
            $unit = $request->input('unit_name');
            $unit_urdu = $request->input('unit_urdu'); // Get Urdu name

            Unit::where('id', $update_id)->update([
                'unit' => $unit,
                'unit_urdu' => $unit_urdu, // Update Urdu name
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Unit Updated Successfully');
        } else {
            return redirect()->back();
        }
    }
}
