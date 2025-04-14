<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\SupplierPayments;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function supplier()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            // dd($userId);
            $Suppliers = Supplier::where('admin_or_user_id', '=', $userId)->get();
            return view('admin_panel.supplier.supplier', [
                'Suppliers' => $Suppliers
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store_supplier(Request $request)
    {
        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            $Supplier = Supplier::create([
                'admin_or_user_id'    => $userId,
                'name'          => $request->name,
                'mobile'          => $request->mobile,
                'city'          => $request->city,
                'area'          => $request->area,
                'address'          => $request->address,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]);

            // Vendor Ledger Create (One-time Opening Balance)
            SupplierLedger::create([
                'admin_or_user_id' => $userId,
                'supplier_id' => $Supplier->id,
                'opening_balance' => $request->opening_balance, // Pehli dafa opening balance = previous balance
                'previous_balance' => $request->opening_balance, // Pehli dafa opening balance = previous balance
                'closing_balance' => $request->opening_balance, // Closing balance bhi initially same hoga
                'created_at' => Carbon::now(),
            ]);
            return redirect()->back()->with('success', 'Supplier Added Successfully');
        } else {
            return redirect()->back();
        }
    }
    public function update_supplier(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $update_id = $request->input('supplier_id');

            $supplier = Supplier::find($update_id); // Use Eloquent to get the model

            if ($supplier) {
                $supplier->update([
                    'admin_or_user_id' => $userId,
                    'name'             => $request->input('name'),
                    'mobile'           => $request->input('mobile'),
                    'city'             => $request->input('city'),
                    'area'             => $request->input('area'),
                    'address'          => $request->input('address'),
                    'updated_at'       => Carbon::now(),
                ]);

                // Check if ledger already exists
                $existingLedger = SupplierLedger::where('supplier_id', $supplier->id)->first();
                if (!$existingLedger) {
                    SupplierLedger::create([
                        'admin_or_user_id'   => $userId,
                        'supplier_id'        => $supplier->id,
                        'opening_balance'    => $request->opening_balance,
                        'previous_balance'   => $request->opening_balance,
                        'closing_balance'    => $request->opening_balance,
                        'created_at'         => Carbon::now(),
                    ]);
                }

                return redirect()->back()->with('success', 'Supplier Updated Successfully');
            } else {
                return redirect()->back()->with('error', 'Supplier not found');
            }
        } else {
            return redirect()->back();
        }
    }

    public function supplier_ledger()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $SupplierLedgers = SupplierLedger::where('admin_or_user_id', $userId)->with('supplier')->get();
            return view('admin_panel.supplier.supplier_ledger', compact('SupplierLedgers'));
        } else {
            return redirect()->back();
        }
    }

    public function supplier_payment_store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'amount_paid' => 'required',
            'payment_date' => 'required',
            'description' => 'required',
        ]);

        DB::beginTransaction();

        try {
            // 1. Save to supplier_payments
            SupplierPayments::create([
                'admin_or_user_id' => auth()->id(), // or fixed 1
                'supplier_id' => $request->supplier_id,
                'amount_paid' => $request->amount_paid,
                'payment_date' => $request->payment_date,
                'description' => $request->description,
            ]);

            // 2. Update existing ledger record
            $ledger = SupplierLedger::where('supplier_id', $request->supplier_id)->first();

            if ($ledger) {
                $ledger->closing_balance -= $request->amount_paid;
                $ledger->save();
            } else {
                return response()->json(['message' => 'Ledger not found'], 404);
            }

            DB::commit();

            return response()->json(['message' => 'Payment recorded and ledger updated successfully.']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public function supplier_payment()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $SupplierPayments = SupplierPayments::where('admin_or_user_id', $userId)->with('supplier')->get();
            return view('admin_panel.supplier.SupplierPayments', compact('SupplierPayments'));
        } else {
            return redirect()->back();
        }
    }
}
