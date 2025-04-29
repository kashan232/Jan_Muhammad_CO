<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\LotEntry;
use App\Models\LotSale;
use App\Models\Supplier;
use App\Models\SupplierLedger;
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
        // dd($request);
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
                'weight' => $sale['weight'],
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
                    'lot_sales.id',
                    'lot_sales.customer_id',
                    'lot_sales.lot_id', // <-- 👈 Add this line
                    DB::raw("COALESCE(customers.customer_name, 'Cash Sale') as customer_name"),
                    DB::raw("COALESCE(customers.customer_phone, '-') as customer_phone"),
                    'lot_sales.quantity',
                    'lot_sales.weight',
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
    public function deleteSale(Request $request)
    {
        $lot_id = $request->lot_id;
        $sale_id = $request->sale_id;
        $customer_id = $request->customerid;

        // 1. Get the sale record
        $sale = DB::table('lot_sales')->where('id', $sale_id)->first();
        if (!$sale) {
            return response()->json(['message' => 'Sale not found.'], 404);
        }

        // 2. Get the related lot
        $lot = DB::table('lot_entries')->where('id', $lot_id)->first();
        if (!$lot) {
            return response()->json(['message' => 'Lot not found.'], 404);
        }

        // 3. Add the deleted quantity back to lot_quantity
        $newQuantity = $lot->lot_quantity + $sale->quantity;
        DB::table('lot_entries')->where('id', $lot_id)->update([
            'lot_quantity' => $newQuantity,
        ]);

        // 4. Adjust customer closing balance
        $ledger = DB::table('customer_ledgers')->where('customer_id', $customer_id)->first();

        if ($ledger) {
            $newBalance = $ledger->closing_balance - $sale->total;

            DB::table('customer_ledgers')->where('customer_id', $customer_id)->update([
                'closing_balance' => $newBalance,
            ]);
        }

        // 5. Delete the sale record
        DB::table('lot_sales')->where('id', $sale_id)->delete();

        return response()->json(['message' => 'Sale deleted, quantity restored, and balance adjusted.']);
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

    public function Create_Bill($truck_id, $vendor_id)
    {
        $lots = DB::table('lot_entries')
            ->where('truck_id', $truck_id)
            ->get()
            ->map(function ($lot) {
                $sales = DB::table('lot_sales')->where('lot_id', $lot->id)->get();

                $totalSale = $sales->sum(function ($sale) {
                    return $sale->quantity * $sale->price;
                });

                $averageSale = $lot->total_units > 0 ? $totalSale / $lot->total_units : 0;

                $totalWeight = $sales->sum('weight'); // 👈 Add this line

                $lot->total_sale = $totalSale;
                $lot->average_sale = $averageSale;
                $lot->total_weight = $totalWeight; // 👈 Attach weight

                return $lot;
            });

        $truck = DB::table('truck_entries')->where('id', $truck_id)->first();
        $customers = Customer::orderBy('customer_name', 'asc')->get();
        $vendor_id = $vendor_id;

        return view('admin_panel.lot_sale.create_bill', compact('lots', 'truck', 'customers', 'vendor_id'));
    }


    public function store_Bill(Request $request)
    {
        // Step 1: Truck Entry fetch karo
        $truckEntry = TruckEntry::find($request->truck_id);
        if (!$truckEntry) {
            return response()->json(['success' => false, 'message' => 'Truck not found'], 404);
        }

        // Step 2: TruckEntry se vendor name lo
        $vendorName = $truckEntry->vendor_id; // assuming vendor_id is actually name

        // Step 3: Supplier table me vendor name se supplier nikalna
        $supplier = Supplier::where('name', $vendorName)->first();
        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'Supplier not found'], 404);
        }

        // Step 4: Supplier Ledger me supplier ka record nikalna
        $ledger = SupplierLedger::where('supplier_id', $supplier->id)->latest()->first();

        // Step 5: Ledger update ya create
        if ($ledger) {
            $ledger->previous_balance = $ledger->closing_balance;
            $ledger->closing_balance += $request->net_pay;
            $ledger->save();
        } else {
            SupplierLedger::create([
                'supplier_id' => $supplier->id,
                'previous_balance' => 0,
                'closing_balance' => $request->net_pay,
                'admin_or_user_id' => auth()->id() ?? 1, // optional
            ]);
        }

        // ✅ Step 6: Lot validation
        $lotSaleTotals = [];

        foreach ($request->bill_details as $detail) {
            $lotId = $detail['lot_id'];
            $saleUnits = $detail['sale_units'];
            $weight = $detail['weight'];

            if (!isset($lotSaleTotals[$lotId])) {
                $lotSaleTotals[$lotId] = 0;
            }

            $lotSaleTotals[$lotId] += $saleUnits;
        }

        foreach ($lotSaleTotals as $lotId => $totalSaleUnits) {
            $lotEntry = LotEntry::find($lotId);

            if (!$lotEntry) {
                return response()->json([
                    'success' => false,
                    'message' => "Lot ID $lotId not found."
                ], 404);
            }

            if ($totalSaleUnits > $lotEntry->total_units) {
                return response()->json([
                    'success' => false,
                    'message' => "Lot $lotId has only {$lotEntry->total_units} units available. You are trying to bill $totalSaleUnits units."
                ], 400);
            }
        }
        $adjustment = $request->input('adjustment');
        $netPayToVendor = $request->input('net_pay');
        // ✅ Save Bill
        $bill = new VendorBill();
        $bill->truck_id = $request->truck_id;
        $bill->trucknumber = $request->trucknumber;
        $bill->vendorId = $request->vendorId;
        $bill->subtotal = $request->subtotal;
        $bill->total_expense = $request->total_expense;
        $bill->adjustment = $adjustment;
        $bill->net_pay = $netPayToVendor;

        $bill->lot_id = json_encode(array_column($request->bill_details, 'lot_id'));
        $bill->sale_units = json_encode(array_column($request->bill_details, 'sale_units'));
        $bill->weight = json_encode(array_column($request->bill_details, 'weight'));
        $bill->rate = json_encode(array_column($request->bill_details, 'rate'));
        $bill->amount = json_encode(array_column($request->bill_details, 'amount'));
        $bill->unit_in = json_encode(array_column($request->bill_details, 'unit_in'));

        $bill->category = json_encode(array_column($request->expenses, 'category'));
        $bill->value = json_encode(array_column($request->expenses, 'value'));
        $bill->final_amount = json_encode(array_column($request->expenses, 'final_amount'));

        $bill->save();

        return response()->json(['success' => true, 'message' => 'Vendor bill saved and ledger updated.']);
    }


    public function view($id)
    {
        $bill = VendorBill::findOrFail($id);
        return view('admin_panel.lot_sale.view_bill', compact('bill'));
    }

    public function bill_book($id)
    {
        $bill = VendorBill::find($id);

        $truckEntry = TruckEntry::where('id', $bill->truck_id)->first();

        // نیا کوڈ: vendor ka urdu name nikalna
        $vendorName = 'N/A';
        if ($truckEntry && $truckEntry->vendor_id) {
            $supplier = \App\Models\Supplier::where('name', $truckEntry->vendor_id)->first();
            if ($supplier) {
                $vendorName = $supplier->urdu_name ?? $supplier->name; // اگر اردو نہ ہو تو انگلش نیم
            }
        }

        $lot_ids = json_decode($bill->lot_id, true);
        $sale_units = json_decode($bill->sale_units, true);
        $rates = json_decode($bill->rate, true);
        $amounts = json_decode($bill->amount, true);
        $units_in = json_decode($bill->unit_in, true);
        $categories = json_decode($bill->category ?? '[]');

        // Translation maps
        $unitMap = \App\Models\Unit::pluck('unit_urdu', 'unit')->toArray();
        $unitInMap = \App\Models\UnitIn::pluck('unit_in_urdu', 'unit_in')->toArray();
        $categoryMap = \App\Models\Category::pluck('category_urdu', 'category')->toArray();
        $varietyMap = \App\Models\Brand::pluck('brand_urdu', 'brand')->toArray();

        $weights = json_decode($bill->weight, true); // assuming 'weight' field exists in DB and is a JSON array
        $totalWeights = array_sum($weights);
        // Custom categories
        $customCategoryMap = [
            'Mazdori' => 'مزدوری',
            'Commission' => 'کمیشن',
            'Rent' => 'کرایہ',
            'Market Tax' => 'مارکیٹ ٹیکس',
        ];

        $categories_ur = collect($categories)->map(function ($item) use ($customCategoryMap) {
            return $customCategoryMap[$item] ?? $item;
        });

        $lotEntries = LotEntry::whereIn('id', $lot_ids)->get()->mapWithKeys(function ($item) use ($unitMap, $categoryMap, $varietyMap, $unitInMap) {
            return [
                $item->id => (object)[
                    'category' => $item->category,
                    'variety' => $item->variety,
                    'unit' => $item->unit,
                    'category_ur' => $categoryMap[$item->category] ?? $item->category,
                    'variety_ur' => $varietyMap[$item->variety] ?? $item->variety,
                    'unit_ur' => $unitMap[$item->unit] ?? $item->unit,
                    'unit_in_ur' => $unitInMap[$item->unit_in] ?? $item->unit_in,
                ],
            ];
        });
        return view('admin_panel.lot_sale.bill_book', compact(
            'bill',
            'vendorName',
            'lot_ids',
            'sale_units',
            'rates',
            'amounts',
            'units_in',
            'lotEntries',
            'categories',
            'categories_ur',
            'weights',
            'totalWeights'
        ));
    }
}
