<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerCredit;
use App\Models\CustomerLedger;
use App\Models\CustomerRecovery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (Auth::id()) {
            $userId = Auth::id();
            $customer = Customer::create([
                'admin_or_user_id' => $userId,
                'customer_name' => $request->customer_name,
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
                'previous_balance' => $request->opening_balance, // Pehli dafa opening balance = previous balance
                'closing_balance' => $request->opening_balance, // Closing balance bhi initially same hoga
                'created_at' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Customer created successfully');
        } else {
            return redirect()->back();
        }

        
    }
    public function update_customer(Request $request)
    {
        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            // dd($request);
            $update_id = $request->input('customer_id');

            Customer::where('id', $update_id)->update([
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'city' => $request->city,
                'area' => $request->area,
                'customer_address' => $request->customer_address,
                'opening_balance' => $request->opening_balance,
                'updated_at' => Carbon::now(),
            ]);
            return redirect()->back()->with('success', 'Customer Updated Successfully');
        } else {
            return redirect()->back();
        }
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
        $ledger = CustomerLedger::find($request->ledger_id);
        $ledger->previous_balance -= $request->amount_paid;
        $ledger->closing_balance -= $request->amount_paid;
        $ledger->save();

        $userId = Auth::id();

        // Store recovery record (Optional)
        CustomerRecovery::create([
            'admin_or_user_id' => $userId,
            'customer_ledger_id' => $ledger->id,
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



   
}
