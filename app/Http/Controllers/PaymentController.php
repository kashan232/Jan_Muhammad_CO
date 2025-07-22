<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerRecovery;
use App\Models\LotSale;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\SupplierPayments;
use App\Models\Vendor;
use App\Models\VendorBill;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public function customer_payments()
    {
        $customers = Customer::all(['id', 'customer_name']);
        return view('admin_panel.payments.customer_payments', compact('customers'));
    }

    public function getCustomerBalance($id)
    {
        $customer = Customer::with(['lotSales' => function ($q) {
            $q->select('id', 'customer_id', 'sale_date', 'total')->latest()->take(5);
        }])->find($id);

        if (!$customer) {
            return response()->json(['balance' => 0, 'sales' => []]);
        }

        $latestLedger = CustomerLedger::where('customer_id', $id)
            ->latest('id') // or ->orderByDesc('created_at')
            ->first();

        $closingBalance = $latestLedger ? $latestLedger->closing_balance : 0;

        return response()->json([
            'balance' => $closingBalance,
            'sales' => $customer->lotSales ?? []
        ]);
    }


    public function storeCustomerPayment(Request $request)
    {   
        $latestLedger = CustomerLedger::where('customer_id', $request->customer_id)
            ->latest('id')
            ->first();

        if (!$latestLedger) {
            return redirect()->back()->with('error', 'Ledger record not found for this customer.');
        }

        $previous_balance = $latestLedger->closing_balance;
        $new_closing_balance = $previous_balance - $request->amount;

        // ✅ Step 1: Update existing ledger
        $latestLedger->update([
            'closing_balance' => $new_closing_balance,
        ]);

        // ✅ Step 2: Create Recovery Record
        CustomerRecovery::create([
            'admin_or_user_id' => auth()->id(),
            'customer_ledger_id' => $request->customer_id,
            'amount_paid' => $request->amount,
            'description' => $request->bank,
            'Bank' => $request->detail,
            'date' => $request->date,
        ]);

        return redirect()->back()->with('success', 'Payment recorded successfully.');
    }




    public function Vendor_payments()
    {
        $Suppliers = Supplier::all(['id', 'name']);
        return view('admin_panel.payments.Vendor_payments', compact('Suppliers'));
    }

    public function storeVendorPayment(Request $request)
    {
        $supplierId = $request->supplier_id;
        // Get last ledger entry for this supplier
        $latestLedger = SupplierLedger::where('supplier_id', $supplierId)->latest()->first();

        $previousBalance = $latestLedger ? $latestLedger->closing_balance : 0;
        $newClosing = $previousBalance - $request->amount;

        // ✅ Update existing ledger if found
        if ($latestLedger) {
            $latestLedger->update([
                'closing_balance' => $newClosing,
            ]);
            $ledgerId = $latestLedger->id;
        } else {
            // ✅ Create new ledger only if none exists
            $newLedger = SupplierLedger::create([
                'admin_or_user_id' => auth()->id(),
                'supplier_id' => $supplierId,
                'previous_balance' => $previousBalance,
                'closing_balance' => $newClosing,
            ]);
            $ledgerId = $newLedger->id;
        }

        // Save in supplier_payments
        SupplierPayments::create([
            'admin_or_user_id' => auth()->id(),
            'supplier_id' => $supplierId,
            'amount_paid' => $request->amount,
            'payment_date' => $request->date,
            'description' => $request->bank,
            'Bank' => $request->detail,
        ]);

        return redirect()->back()->with('success', 'Vendor payment saved successfully.');
    }


    public function getVendorBalance($id)
    {
        // Step 1: Get supplier by ID
        $vendor = Supplier::find($id);

        if (!$vendor) {
            return response()->json([
                'balance' => 0,
                'sales' => [],
                'bills' => []
            ]);
        }

        // Step 2: Get latest ledger entry
        $latestLedger = SupplierLedger::where('supplier_id', $id)->latest()->first();
        $closingBalance = $latestLedger ? $latestLedger->closing_balance : 0;

        // Step 3: Get last 10 payments from supplier_payments table
        $lastPayments = SupplierPayments::where('supplier_id', $id)
            ->latest('id')
            ->take(10)
            ->get(['payment_date', 'amount_paid']);

        // ✅ Step 4: Get vendor bills from vendor_bills table using vendorId (ID, not name)
        $vendorBills = VendorBill::where('vendorId', $vendor->name)
            ->latest('id')
            ->take(10)
            ->get(['created_at', 'net_pay']);
        return response()->json([
            'balance' => $closingBalance,
            'sales' => $lastPayments,
            'bills' => $vendorBills
        ]);
    }
}
