<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerCredit;
use App\Models\CustomerLedger;
use App\Models\CustomerRecovery;
use App\Models\LotSale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function customer()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            // dd($userId); 
            $Customers = Customer::get();
            return view('admin_panel.customers.customers', [
                'Customers' => $Customers
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store_customer(Request $request)
    {
        // Validate customer_name
        $request->validate([
            'customer_name' => 'required|string|max:255|unique:customers,customer_name',
        ]);

        if (Auth::id()) {
            $userId = Auth::id();

            $customer = Customer::create([
                'admin_or_user_id' => $userId,
                'customer_name' => $request->customer_name,
                'customer_name_urdu' => $request->customer_name_urdu,
                'customer_phone' => $request->customer_phone,
                'city' => $request->city,
                'area' => $request->area,
                'customer_address' => $request->customer_address,
                'opening_balance' => $request->opening_balance,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            CustomerLedger::create([
                'admin_or_user_id' => $userId,
                'customer_id' => $customer->id,
                'opening_balance' => $request->opening_balance,
                'previous_balance' => $request->opening_balance,
                'closing_balance' => $request->opening_balance,
                'created_at' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Customer created successfully');
        } else {
            return redirect()->back();
        }
    }
    public function update_customer(Request $request)
    {
        if (!Auth::id()) {
            return redirect()->back();
        }

        $update_id = $request->input('customer_id');

        // Get existing customer
        $customer = Customer::find($update_id);
        if (!$customer) {
            return redirect()->back()->with('error', 'Customer not found');
        }

        // Get old opening balance
        $old_opening_balance = $customer->opening_balance;

        // Get recap adjustment type and amount
        $recape_type = $request->input('recape_type');
        $recape_amount = $request->input('recape_opening_balance') ?? 0;

        // Adjust the balance based on the selected type
        if ($recape_type === 'plus') {
            $new_opening_balance = $old_opening_balance + $recape_amount;
        } elseif ($recape_type === 'minus') {
            $new_opening_balance = $old_opening_balance - $recape_amount;
        } else {
            $new_opening_balance = $old_opening_balance;
        }

        // Update customer info
        $customer->update([
            'customer_name' => $request->customer_name,
            'customer_name_urdu' => $request->customer_name_urdu,
            'customer_phone' => $request->customer_phone,
            'city' => $request->city,
            'area' => $request->area,
            'opening_balance' => $new_opening_balance,
            'updated_at' => Carbon::now(),
        ]);

        // Update ledger
        $latestLedger = CustomerLedger::where('customer_id', $update_id)
            ->orderBy('id', 'desc')
            ->first();

        if ($latestLedger) {
            $latestLedger->opening_balance = $new_opening_balance;
            $latestLedger->closing_balance = ($recape_type === 'plus') ? $latestLedger->closing_balance + $recape_amount : $latestLedger->closing_balance - $recape_amount;
            $latestLedger->updated_at = now();
            $latestLedger->save();
        } else {
            CustomerLedger::create([
                'admin_or_user_id' => Auth::id(),
                'customer_id' => $update_id,
                'previous_balance' => 0,
                'opening_balance' => $new_opening_balance,
                'closing_balance' => $new_opening_balance,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Customer Updated Successfully');
    }




    public function addCredit(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'credit_amount' => 'required|numeric|min:0',
        ]);
        // Get customer credit entry if it exists
        $customerCredit = CustomerCredit::where('customerId', $request->customer_id)->first();

        $creditAmount = $request->credit_amount;
        $customer_name = $request->customer_name;

        if ($customerCredit) {
            // Update the existing entry if customer credit exists
            $customerCredit->previous_balance += $creditAmount;
            $customerCredit->closing_balance += $creditAmount;
            $customerCredit->save();
        } else {
            // Create a new entry if customer credit does not exist
            $customerCredit = CustomerCredit::create([
                'customerId' => $request->customer_id,
                'customer_name' => $customer_name,
                'previous_balance' => $creditAmount,
                'net_total' => '0',
                'closing_balance' => $creditAmount, // Assuming the balance starts with the credit amount
            ]);
        }

        return redirect()->back()->with('success', 'Credit added successfully to the customer.');
    }

    public function customer_ledger()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $CustomerLedgers = CustomerLedger::where('admin_or_user_id', $userId)->with('Customer')->get();
            return view('admin_panel.customers.customers_ledger', compact('CustomerLedgers'));
        } else {
            return redirect()->back();
        }
    }

    public function customer_recovery_store(Request $request)
    {
        // Find ledger using customer_id
        $ledger = CustomerLedger::where('customer_id', $request->ledger_id)->first();

        // Adjust balances
        $ledger->previous_balance -= $request->amount_paid;
        $ledger->closing_balance -= $request->amount_paid;
        $ledger->save();
        $userId = Auth::id();

        // Store recovery record (Optional)
        CustomerRecovery::create([
            'admin_or_user_id' => $userId,
            'customer_ledger_id' => $ledger->customer_id,
            'amount_paid' => $request->amount_paid,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        return response()->json([
            'success' => true,
            'new_closing_balance' => number_format($ledger->closing_balance, 0)
        ]);
    }

    public function customer_recovery()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Recoveries = CustomerRecovery::where('admin_or_user_id', $userId)->with('customer')->get();
            return view('admin_panel.customers.customers_recoveries', compact('Recoveries'));
        } else {
            return redirect()->back();
        }
    }


    public function Customer_balance()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $CustomerLedgers = CustomerLedger::where('admin_or_user_id', $userId)->with('Customer')->get();
            return view('admin_panel.customers.Customer_balance', compact('CustomerLedgers'));
        } else {
            return redirect()->back();
        }
    }

    public function fetchLedger($id)
    {
        $sales = DB::table('lot_sales')
            ->where('customer_id', $id)
            ->select(
                'id', // This is lot_sales.id
                'sale_date as date',
                DB::raw("'Sale' as type"),
                'total as amount',
                DB::raw("CONCAT('Lot ID: ', lot_id) as remarks"),
                'lot_id'
            )
            ->get();


        $recoveries = DB::table('customer_recoveries')
            ->where('customer_ledger_id', $id)
            ->select(
                'id',
                'date as date',
                DB::raw("'Recovery' as type"),
                'amount_paid as amount',
                'description as remarks'
            )
            ->get();

        $merged = $sales->concat($recoveries)->sortByDesc('date')->values();

        return response()->json($merged);
    }

    public function getLotDetails($id)
    {
        $lotSale = DB::table('lot_sales')
            ->where('id', $id)
            ->first();

        return response()->json($lotSale);
    }
}
