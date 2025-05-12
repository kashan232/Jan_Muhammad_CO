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
        // Validate customer_name
        $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
        ]);


        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            $Supplier = Supplier::create([
                'admin_or_user_id'    => $userId,
                'name'          => $request->name,
                'urdu_name'      => $request->urdu_name,
                'mobile'          => $request->mobile,
                'city'          => $request->city,
                'area'          => $request->area,
                'address'          => $request->address,
                'opening_balance'          => $request->opening_balance,
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
        if (!Auth::id()) {
            return redirect()->back();
        }

        $userId = Auth::id();
        $update_id = $request->input('supplier_id');

        // Find existing supplier
        $supplier = Supplier::find($update_id);
        if (!$supplier) {
            return redirect()->back()->with('error', 'Supplier not found');
        }

        // Get old and recap values
        $old_opening_balance = $supplier->opening_balance;
        $recape_addition = $request->input('receipt_opening_balance') ?? 0;
        $adjustment_type = $request->input('adjustment_type'); // Get the adjustment type (plus/minus)

        // Adjust the opening balance based on the selected type
        if ($adjustment_type == 'plus') {
            $new_opening_balance = $old_opening_balance + $recape_addition;
        } elseif ($adjustment_type == 'minus') {
            $new_opening_balance = $old_opening_balance - $recape_addition;
        } else {
            $new_opening_balance = $old_opening_balance;
        }

        // Update supplier record
        $supplier->update([
            'admin_or_user_id' => $userId,
            'name'             => $request->input('name'),
            'urdu_name'        => $request->input('urdu_name'),
            'mobile'           => $request->input('mobile'),
            'city'             => $request->input('city'),
            'area'             => $request->input('area'),
            'address'          => $request->input('address'),
            'opening_balance'  => $new_opening_balance,
            'updated_at'       => Carbon::now(),
        ]);

        // Update ledger
        $latestLedger = SupplierLedger::where('supplier_id', $update_id)
            ->orderBy('id', 'desc')
            ->first();

        if ($latestLedger) {
            $latestLedger->opening_balance = $new_opening_balance;
            if ($adjustment_type == 'plus') {
                $latestLedger->closing_balance += $recape_addition;
            } elseif ($adjustment_type == 'minus') {
                $latestLedger->closing_balance -= $recape_addition;
            }
            $latestLedger->updated_at = now();
            $latestLedger->save();
        } else {
            SupplierLedger::create([
                'admin_or_user_id' => $userId,
                'supplier_id' => $update_id,
                'previous_balance' => 0,
                'opening_balance' => $new_opening_balance,
                'closing_balance' => $new_opening_balance,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Supplier Updated Successfully');
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

    public function Supplier_balance()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $SupplierLedger = SupplierLedger::where('admin_or_user_id', $userId)->with('supplier')->get();
            return view('admin_panel.supplier.supplier_balance', compact('SupplierLedger'));
        } else {
            return redirect()->back();
        }
    }

    public function Supplier_balance_ledger($supplier_id)
    {
        // Step 1: Find supplier name from suppliers table
        $supplierName = DB::table('suppliers')
            ->where('id', $supplier_id)
            ->pluck('name')
            ->first();

        if (!$supplierName) {
            return response()->json(['error' => 'Supplier not found'], 404);
        }

        // Step 2: Find all vendor bills using supplier name as vendorId
        $bills = DB::table('vendor_bills')
            ->where('vendorId', $supplierName)
            ->select(
                'id',
                'created_at as date',
                DB::raw("'Bill' as type"),
                'net_pay as amount',
                DB::raw("CONCAT('Truck #: ', trucknumber) as remarks")
            )
            ->get();

        // Step 3: Find all supplier payments
        $payments = DB::table('supplier_payments')
            ->where('supplier_id', $supplier_id)
            ->select(
                'id',
                'payment_date as date',
                DB::raw("'Payment' as type"),
                'amount_paid as amount',
                'description as remarks'
            )
            ->get();

        // Step 4: Merge and sort all records by date
        $merged = $bills->concat($payments)->sortByDesc('date')->values();

        return response()->json($merged);
    }

    public function toggleStatus(Request $request)
    {
        $supplier = Supplier::find($request->id);
        if ($supplier) {
            $supplier->status = $request->status;
            $supplier->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Supplier not found.'
            ]);
        }
    }
}
