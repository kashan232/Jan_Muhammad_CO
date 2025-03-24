<?php

namespace App\Http\Controllers;

use App\Models\UnitIn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitInController extends Controller
{
    
    public function In_unit()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $all_unit = UnitIn::get();
                // ->map(function ($Unit) {
                //     $Unit->products_count = $Unit->products()->count();
                //     return $Unit;
                // });
            return view('admin_panel.unit_ins.unit_ins', [
                'all_unit' => $all_unit
            ]);
        } else {
            return redirect()->back();
        }
    }
    public function store_In_unit(Request $request)
    {
        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            UnitIn::create([
                'admin_or_user_id'    => $userId,
                'unit_in'          => $request->unit,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]);
            return redirect()->back()->with('success', 'Unit Added Successfully');
        } else {
            return redirect()->back();
        }
    }
    public function update_In_unit(Request $request)
    {
        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            // dd($reques   t);
            $update_id = $request->input('unit_id');
            $unit = $request->input('unit_name');

            UnitIn::where('id', $update_id)->update([
                'unit_in'   => $unit,
                'updated_at' => Carbon::now(),
            ]);
            return redirect()->back()->with('success', 'unit Updated Successfully');
        } else {
            return redirect()->back();
        }
    }

}
