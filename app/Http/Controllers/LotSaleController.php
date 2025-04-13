<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\LotEntry;
use App\Models\LotSale;
use App\Models\TruckEntry;
use App\Models\VendorBill;
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
            ->having('total_units', '>', 0)
            ->get();

        return view('admin_panel.lot_sale.truck_list', compact('trucks'));
    }


    public function trucks_sold()
    {
        $trucks = DB::table('truck_entries')
            ->leftJoin('lot_entries', 'truck_entries.id', '=', 'lot_entries.truck_id')
            ->leftJoin('vendor_bills', 'truck_entries.id', '=', 'vendor_bills.truck_id') // 👈 JOIN vendor_bills
            ->select(
                'truck_entries.id',
                'truck_entries.truck_number',
                'truck_entries.vendor_id',
                'truck_entries.entry_date',
                DB::raw('SUM(lot_entries.lot_quantity) as total_units'),
                'vendor_bills.id as bill_id' // 👈 check if bill exists
            )
            ->groupBy(
                'truck_entries.id',
                'truck_entries.truck_number',
                'truck_entries.vendor_id',
                'truck_entries.entry_date',
                'vendor_bills.id'
            )
            ->having('total_units', '<=', 0)
            ->get();

        return view('admin_panel.lot_sale.trucks_sold', compact('trucks'));
    }




    public function show_Lots($truck_id)
    {
        $lots = DB::table('lot_entries')->where('truck_id', $truck_id)->get();
        $truck = DB::table('truck_entries')->where('id', $truck_id)->first();
        $customers = Customer::orderBy('customer_name', 'asc')->get(); // Alphabetically sorted customers

        return view('admin_panel.lot_sale.lot_list', compact('lots', 'truck', 'customers'));
    }


    public function store_lot(Request $request)
    {
        $request->validate([
            'customer_type' => 'required|string',
            'sale_date' => 'required|date',
            'sales' => 'required|array',
            'sales.*.lot_id' => 'required|integer', // Lot ID zaroori hai
            'sales.*.quantity' => 'required|integer|min:1',
            'sales.*.price' => 'required|numeric|min:0',
        ]);

        $customerType = $request->customer_type;
        $customerId = $request->customer_id ?? null;
        $saleDate = $request->sale_date;
        $subTotal = 0;

        foreach ($request->sales as $sale) {
            $lot = LotEntry::findOrFail($sale['lot_id']);

            // **Available Quantity Check**
            if ($sale['quantity'] > $lot->lot_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$lot->lot_quantity} units available for sale in Lot ID: {$lot->id}."
                ], 400);
            }

            $totalAmount = $sale['quantity'] * $sale['price'];
            $subTotal += $totalAmount;

            // Reduce Lot Quantity
            $lot->lot_quantity -= $sale['quantity'];
            $lot->save();

            // Save Sale Record
            LotSale::create([
                'customer_type' => $customerType,
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
    public function showSaleRecord($truck_id)
    {
        // Lot-wise data fetch karna
        $lots = DB::table('lot_entries')
            ->where('truck_id', $truck_id)
            ->select('id', 'category', 'variety', 'total_units') // Corrected to total_units
            ->get();

        foreach ($lots as $lot) {
            // Total Sold (Cash + Credit) Calculate Karna
            $sold_quantity = DB::table('lot_sales')
                ->where('lot_id', $lot->id)
                ->sum('quantity');

            // Available Quantity Calculate Karna
            $lot->sold_quantity = $sold_quantity;
            $lot->available_quantity = $lot->total_units - $sold_quantity;

            // Lot Sales Details Fetch (Cash + Credit Customers)
            $lot->sales = DB::table('lot_sales')
                ->leftJoin('customers', 'lot_sales.customer_id', '=', 'customers.id')
                ->where('lot_sales.lot_id', $lot->id)
                ->select(
                    'lot_sales.id', // 👈 This is required for form
                    DB::raw("COALESCE(customers.customer_name, 'Cash Sale') as customer_name"),
                    DB::raw("COALESCE(customers.customer_phone, '-') as customer_phone"),
                    'lot_sales.quantity',
                    'lot_sales.price',
                    'lot_sales.total',
                    'lot_sales.sale_date',
                    DB::raw("IF(lot_sales.customer_id IS NULL, 'Cash', 'Credit') as customer_type")
                )
                ->get();
        }

        // Truck Details Fetch
        $truck = DB::table('truck_entries')->where('id', $truck_id)->first();
        return view('admin_panel.lot_sale.sale_record', compact('lots', 'truck'));
    }

    public function updateLotSale(Request $request)
    {

        $sale = LotSale::find($request->sale_id);
        $sale->quantity = $request->quantity;
        $sale->price = $request->price;
        $sale->sale_date = $request->sale_date;
        $sale->save();

        return back()->with('success', 'Sale record updated successfully!');
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

    public function cash_sale()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            // Fetch Cash Sales
            $cash_sales = DB::table('lot_sales')
                ->join('lot_entries', 'lot_sales.lot_id', '=', 'lot_entries.id')
                ->where('lot_sales.customer_type', 'cash') // Sirf cash type ki sales
                ->select(
                    'lot_entries.category',
                    'lot_entries.variety',
                    'lot_sales.quantity',
                    'lot_sales.price',
                    'lot_sales.total',
                    'lot_sales.sale_date'
                )
                ->get();

            return view('admin_panel.lot_sale.cash_sale', compact('cash_sales'));
        } else {
            return redirect()->back();
        }
    }

    public function daily_sale()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            return view('admin_panel.lot_sale.daily_sale');
        } else {
            return redirect()->back();
        }
    }

    public function getDailySales(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;

        $sales = LotSale::whereBetween('sale_date', [$start, $end])
            ->with([
                'lot.truckEntry:id,truck_number',
                'lot:id,truck_id,category,variety,unit,unit_in',
                'customer:id,customer_name'
            ])
            ->get();

        $data = $sales->map(function ($sale) {
            return [
                'customer' => $sale->customer_type === 'cash'
                    ? 'Cash'
                    : ($sale->customer->customer_name ?? 'N/A'),
                'truck_number' => $sale->lot->truckEntry->truck_number ?? 'N/A',
                'category' => $sale->lot->category ?? 'N/A',
                'variety' => $sale->lot->variety ?? 'N/A',
                'unit' => $sale->lot->unit ?? 'N/A',
                'unit_in' => $sale->lot->unit_in ?? 'N/A',
                'quantity' => $sale->quantity,
                'price' => $sale->price,
                'total' => $sale->total,
                'sale_date' => $sale->sale_date,
            ];
        });

        return response()->json($data);
    }

    public function Create_Bill($truck_id)
    {
        $lots = DB::table('lot_entries')
            ->where('truck_id', $truck_id)
            ->get()
            ->map(function ($lot) {
                // Get all sales for this lot
                $sales = DB::table('lot_sales')->where('lot_id', $lot->id)->get();

                // Calculate total sale for this lot
                $totalSale = $sales->sum(function ($sale) {
                    return $sale->quantity * $sale->price;
                });

                // Calculate average sale
                $averageSale = $lot->total_units > 0 ? $totalSale / $lot->total_units : 0;

                // Attach to object
                $lot->total_sale = $totalSale;
                $lot->average_sale = $averageSale;

                return $lot;
            });

        $truck = DB::table('truck_entries')->where('id', $truck_id)->first();
        $customers = Customer::orderBy('customer_name', 'asc')->get();

        return view('admin_panel.lot_sale.create_bill', compact('lots', 'truck', 'customers'));
    }


    public function store_Bill(Request $request)
    {
        // Validation
        $request->validate([
            'truck_id' => 'required|integer',
            'trucknumber' => 'required|string',
            'subtotal' => 'required|numeric',
            'total_expense' => 'required|numeric',
            'net_pay' => 'required|numeric',
            'bill_details' => 'required|array',
            'expenses' => 'required|array',
        ]);

        // Extract bill_details
        $lot_ids = [];
        $sale_units = [];
        $rates = [];
        $amounts = [];
        $unit_ins = [];

        foreach ($request->bill_details as $detail) {
            $lot_ids[] = $detail['lot_id'];
            $sale_units[] = $detail['sale_units'];
            $rates[] = $detail['rate'];
            $amounts[] = $detail['amount'];
            $unit_ins[] = $detail['unit_in'];
        }

        // Extract expenses
        $categories = [];
        $values = [];
        $final_amounts = [];

        foreach ($request->expenses as $exp) {
            $categories[] = $exp['category'];
            $values[] = $exp['value'];
            $final_amounts[] = $exp['final_amount'];
        }

        // Save to vendor_bills table
        $bill = new VendorBill();
        $bill->truck_id = $request->truck_id;
        $bill->trucknumber = $request->trucknumber;
        $bill->subtotal = $request->subtotal;
        $bill->total_expense = $request->total_expense;
        $bill->net_pay = $request->net_pay;

        // Store each field as JSON array in its column
        $bill->lot_id = json_encode($lot_ids);
        $bill->sale_units = json_encode($sale_units);
        $bill->rate = json_encode($rates);
        $bill->amount = json_encode($amounts);
        $bill->unit_in = json_encode($unit_ins);

        $bill->category = json_encode($categories);
        $bill->value = json_encode($values);
        $bill->final_amount = json_encode($final_amounts);

        $bill->save();

        return response()->json(['success' => true, 'message' => 'Vendor bill saved successfully.']);
    }

    public function view($id)
    {
        $bill = VendorBill::findOrFail($id);
        return view('admin_panel.lot_sale.view_bill', compact('bill'));
    }

    public function bill_book($id)
    {
        $bill = VendorBill::find($id);

        // Truck entry find using truck_id
        $truckEntry = TruckEntry::where('id', $bill->truck_id)->first();

        // Vendor name
        $vendorName = $truckEntry->vendor_id ?? 'N/A';

        // Get lot_ids (JSON to array)
        $lot_ids = json_decode($bill->lot_id, true); // ["1","2"]

        // Get entries for those lot_ids
        $lotEntries = LotEntry::whereIn('id', $lot_ids)->get();

        // Separate arrays for each field
        $lotcategories = $lotEntries->pluck('category')->toArray();
        $varieties = $lotEntries->pluck('variety')->toArray();
        $units = $lotEntries->pluck('unit')->toArray();
        $units_in = $lotEntries->pluck('unit_in')->toArray();

        return view('admin_panel.lot_sale.bill_book', compact(
            'bill',
            'vendorName',
            'lot_ids',
            'lotcategories',
            'varieties',
            'units',
            'units_in'
        ));
    }
}
