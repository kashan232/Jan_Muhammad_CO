<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\LotEntry;
use App\Models\LotSale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LotSaleController extends Controller
{

    public function show_trucks()
    {
        $trucks = DB::table('truck_entries')
            ->leftJoin('lot_entries', 'truck_entries.id', '=', 'lot_entries.truck_id')
            ->select(
                'truck_entries.id',
                'truck_entries.truck_number',
                'truck_entries.vendor_id',
                'truck_entries.entry_date',
                DB::raw('SUM(lot_entries.lot_quantity) as total_units')
            )
            ->groupBy('truck_entries.id', 'truck_entries.truck_number', 'truck_entries.vendor_id', 'truck_entries.entry_date')
            ->get();

        return view('admin_panel.lot_sale.truck_list', compact('trucks'));
    }

    public function show_Lots($truck_id)
    {
        $lots = DB::table('lot_entries')->where('truck_id', $truck_id)->get();
        $truck = DB::table('truck_entries')->where('id', $truck_id)->first();
        $customers = Customer::all(); // Fetch all customers

        return view('admin_panel.lot_sale.lot_list', compact('lots', 'truck', 'customers'));
    }

    public function store_lot(Request $request)
    {
        $request->validate([
            'customer_type' => 'required|string',
            'sale_date' => 'required|date',
            'sales' => 'required|array',
            'sales.*.lot_id' => 'nullable|integer', // Change 'required' to 'nullable'
            'sales.*.quantity' => 'required|integer|min:1',
            'sales.*.price' => 'required|numeric|min:0',
        ]);

        $customerType = $request->customer_type;
        $customerId = $request->customer_id ?? null;
        $saleDate = $request->sale_date;
        $subTotal = 0;

        foreach ($request->sales as $sale) {
            $lot = LotEntry::findOrFail($sale['lot_id']);
            $totalAmount = $sale['quantity'] * $sale['price'];
            $subTotal += $totalAmount;

            // Reduce Lot Quantity
            $lot->lot_quantity -= $sale['quantity'];
            $lot->save();

            // Save Sale Record
            LotSale::create([
                'customer_id' => $customerId,
                'lot_id' => $sale['lot_id'],
                'quantity' => $sale['quantity'],
                'price' => $sale['price'],
                'total' => $totalAmount,
                'sale_date' => $saleDate,
            ]);
        }

        // Update Customer Ledger for Credit Customer
        if ($customerType === 'credit' && $customerId) {
            $customerLedger = CustomerLedger::where('customer_id', $customerId)->latest()->first();
            $previousBalance = $customerLedger ? $customerLedger->closing_balance : 0;
            $closingBalance = $previousBalance + $subTotal;

            if ($customerLedger) {
                // Agar ledger pehle se hai, toh update karo
                $customerLedger->update([
                    'closing_balance' => $closingBalance,
                ]);
            } else {
                // Agar ledger nahi hai, toh create karo
                CustomerLedger::create([
                    'admin_or_user_id' => auth()->id(),
                    'customer_id' => $customerId,
                    'previous_balance' => $previousBalance,
                    'closing_balance' => $closingBalance,
                ]);
            }
        }


        return response()->json(['success' => true, 'message' => 'Sale recorded successfully']);
    }

    public function customer_sale()
    {
        $customers = Customer::all();
        return view('admin_panel.lot_sale.customer_lot_list', compact('customers'));
    }

    public function getCustomerLots(Request $request)
    {
        $customerId = $request->customer_id;
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : null;

        $query = DB::table('lot_sales')
            ->join('lot_entries', 'lot_sales.lot_id', '=', 'lot_entries.id')
            ->join('truck_entries', 'lot_entries.truck_id', '=', 'truck_entries.id')
            ->where('lot_sales.customer_id', $customerId)
            ->select(
                'lot_sales.sale_date',
                'lot_entries.category',
                'lot_entries.variety',
                'lot_entries.unit',
                'lot_sales.quantity',
                'lot_sales.price',
                'lot_sales.total',
                'truck_entries.truck_number',
                'truck_entries.driver_name'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('lot_sales.sale_date', [$startDate, $endDate]);
        }

        $sales = $query->orderBy('lot_sales.sale_date')->get();

        return response()->json($sales);
    }
}
