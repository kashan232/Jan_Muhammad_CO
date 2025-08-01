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
            ->leftJoin('vendor_bills', 'truck_entries.id', '=', 'vendor_bills.truck_id')
            ->select(
                'truck_entries.id',
                'truck_entries.truck_number',
                'truck_entries.vendor_id',
                'truck_entries.entry_date',
                DB::raw('SUM(lot_entries.lot_quantity) as total_units'),
                'vendor_bills.id as bill_id'
            )
            ->groupBy(
                'truck_entries.id',
                'truck_entries.truck_number',
                'truck_entries.vendor_id',
                'truck_entries.entry_date',
                'vendor_bills.id'
            )
            ->having('total_units', '<=', 0)
            ->orderBy('truck_entries.id', 'desc') // 👈 Sort by latest
            ->get();

        return view('admin_panel.lot_sale.trucks_sold', compact('trucks'));
    }



    public function deleteBill($billId)
    {
        \Log::info("Attempting to delete Bill with ID: " . $billId);

        $bill = VendorBill::withTrashed()->find($billId);

        if ($bill) {
            $bill->forceDelete(); // This will permanently delete the record
            \Log::info("Bill permanently deleted.");
        } else {
            \Log::warning("Bill not found with ID: " . $billId);
        }

        return redirect()->back()->with('success', 'Bill has been permanently deleted.');
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

            // Calculate Total Amount based on weight if available
            $totalAmount = isset($sale['weight']) && $sale['weight'] !== null
                ? $sale['weight'] * $sale['price']
                : $sale['quantity'] * $sale['price'];

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
        $data = $request->validate([
            'sale_id'   => 'required|exists:lot_sales,id',
            'add_units' => 'required|integer|min:0',
            'price'     => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'weight'    => 'nullable|numeric|min:0', // Accept optional weight
        ]);

        $sale = LotSale::findOrFail($data['sale_id']);
        $lot  = LotEntry::findOrFail($sale->lot_id);

        $oldQty    = $sale->quantity;
        $addUnits  = $data['add_units'];
        $delta     = $addUnits;
        $oldWeight = $sale->weight;
        $newWeight = $data['weight'] ?? null;

        if ($delta > 0 && $lot->lot_quantity < $delta) {
            return back()->withErrors([
                'add_units' => "Only {$lot->lot_quantity} units remain in stock."
            ]);
        }

        DB::transaction(function () use ($sale, $lot, $data, $oldQty, $addUnits, $delta, $oldWeight, $newWeight) {

            // 1) Compute old total
            $oldTotal = ($oldWeight !== null)
                ? $oldWeight * $sale->price
                : $sale->quantity * $sale->price;

            // 2) Update sale record
            $newQty = $oldQty + $addUnits;

            $sale->quantity   = $newQty;
            $sale->price      = $data['price'];
            $sale->sale_date  = $data['sale_date'];
            $sale->weight     = $newWeight; // Can be null or a number
            $sale->total      = ($newWeight !== null)
                ? $newWeight * $data['price']
                : $newQty * $data['price'];
            $sale->save();

            // 3) Adjust lot stock
            $lot->lot_quantity = $lot->lot_quantity - $delta;
            $lot->save();

            // 4) Ledger update if applicable
            if ($sale->customer_type === 'credit' && $sale->customer_id) {
                $ledger = CustomerLedger::where('customer_id', $sale->customer_id)
                    ->latest('created_at')
                    ->first();

                if ($ledger) {
                    $newTotal = $sale->total;

                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance  = $ledger->closing_balance - $oldTotal + $newTotal;
                    $ledger->save();
                }
            }
        });

        return back()->with(
            'success',
            "Sale updated. Stock & ledger adjusted successfully."
        );
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

        // 6. Delete vendor bill related to this lot's truck_id
        if ($lot->truck_id) {
            DB::table('vendor_bills')->where('truck_id', $lot->truck_id)->delete();
        }

        return response()->json(['message' => 'Sale deleted, quantity restored, balance adjusted, and related vendor bill deleted.']);
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

        // Fetch lot sales for the current range
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
                'lot_sales.weight',
                'lot_sales.price',
                'lot_sales.total',
                'truck_entries.truck_number',
                'truck_entries.driver_name'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('lot_sales.sale_date', [$startDate, $endDate]);
        }

        $sales = $query->orderBy('lot_sales.sale_date')->get();

        // Get opening balance
        $customerLedger = DB::table('customer_ledgers')
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->first();

        $openingBalance = $customerLedger ? $customerLedger->opening_balance : 0;

        // Calculate previous balance excluding recoveries
        $previousSales = DB::table('lot_sales')
            ->where('customer_id', $customerId)
            ->where('sale_date', '<', $startDate)
            ->sum('total');

        $previousRecoveries = DB::table('customer_recoveries')
            ->where('customer_ledger_id', $customerId)
            ->where('date', '<', $startDate)
            ->sum('amount_paid');

        $calculatedPreviousBalance = $openingBalance + $previousSales - $previousRecoveries;

        // Current Sales
        $currentSales = $sales->sum('total');

        // Current Recoveries within the date range
        $currentRecoveries = DB::table('customer_recoveries')
            ->where('customer_ledger_id', $customerId)
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                return $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->sum('amount_paid');

        // Closing Balance Calculation
        $closingBalance = $calculatedPreviousBalance + $currentSales - $currentRecoveries;

        return response()->json([
            'sales' => $sales,
            'previous_balance' => $calculatedPreviousBalance,
            'total_recovery' => $currentRecoveries,
            'closing_balance' => $closingBalance,
        ]);
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

    public function daily_recovery()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            return view('admin_panel.lot_sale.daily_recovery');
        } else {
            return redirect()->back();
        }
    }
    public function getrecovery(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;

        $recoveries = DB::table('customer_recoveries')
            ->join('customers', 'customer_recoveries.customer_ledger_id', '=', 'customers.id')
            ->whereBetween('date', [$start, $end])
            ->select(
                'customers.customer_name',
                'customer_recoveries.amount_paid',
                'customer_recoveries.description',
                'customer_recoveries.date',
                'customer_recoveries.Bank'
            )
            ->get();

        $data = $recoveries->map(function ($recovery) {
            return [
                'customer' => $recovery->customer_name,
                'amount' => $recovery->amount_paid,
                'description' => $recovery->description,
                'date' => $recovery->date,
                'payment_method' => $recovery->Bank,
            ];
        });

        return response()->json($data);
    }

    public function daily_sale_truck_wise()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            return view('admin_panel.lot_sale.daily_sale_truck_wise');
        } else {
            return redirect()->back();
        }
    }

    public function daily_sale_truck_report(Request $request)
    {
        // 1) Validate inputs
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        // 2) Build & execute query
        $rows = LotSale::whereBetween('lot_sales.sale_date', [$data['start_date'], $data['end_date']])
            ->join('lot_entries',   'lot_sales.lot_id',      '=', 'lot_entries.id')
            ->join('truck_entries', 'lot_entries.truck_id',  '=', 'truck_entries.id')
            ->leftJoin('customers', 'lot_sales.customer_id', '=', 'customers.id')
            ->select([
                'truck_entries.truck_number',
                // “Cash” when type=cash, otherwise customer_name
                DB::raw("
                    CASE
                        WHEN lot_sales.customer_type = 'cash' THEN 'Cash'
                        ELSE customers.customer_name
                    END AS customer_name
                "),
                DB::raw('SUM(lot_sales.quantity) AS total_quantity'),
            ])
            // Must GROUP BY every non-aggregated column or expression
            ->groupBy(
                'truck_entries.truck_number',
                'lot_sales.customer_type',
                'customers.customer_name'
            )
            ->orderBy('truck_entries.truck_number')
            ->get();

        // 3) Return JSON
        return response()->json($rows);
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
            $weight = $sale->weight ?? 0;
            $quantity = $sale->quantity ?? 0;

            return [
                'customer' => $sale->customer_type === 'cash' ? 'Cash' : ($sale->customer->customer_name ?? 'N/A'),
                'truck_number' => $sale->lot->truckEntry->truck_number ?? 'N/A',
                'category' => $sale->lot->category ?? 'N/A',
                'variety' => $sale->lot->variety ?? 'N/A',
                'unit' => $sale->lot->unit ?? 'N/A',
                'unit_in' => $sale->lot->unit_in ?? 'N/A',
                'quantity' => $quantity,   // direct quantity dikhao
                'weight' => $weight,       // direct weight dikhao
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
                    // 👇 Use weight if it's not null and greater than 0
                    return ($sale->weight !== null && $sale->weight > 0)
                        ? $sale->weight * $sale->price
                        : $sale->quantity * $sale->price;
                });

                $totalUnitsSold = $sales->sum('quantity');
                $totalWeight = $sales->sum('weight');

                // 👇 Average based on quantity if available, otherwise weight
                $averageSale = $totalUnitsSold > 0
                    ? $totalSale / $totalUnitsSold
                    : ($totalWeight > 0 ? $totalSale / $totalWeight : 0);

                $lot->total_sale = $totalSale;
                $lot->average_sale = $averageSale;
                $lot->total_weight = $totalWeight;

                return $lot;
            });

        $truck = DB::table('truck_entries')->where('id', $truck_id)->first();
        $customers = Customer::orderBy('customer_name', 'asc')->get();

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
        $bill->bill_date = $request->date;
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

    public function recepit_customer_sale()
    {
        $customers = Customer::all();
        return view('admin_panel.lot_sale.recepit_customer_sale', compact('customers'));
    }
}
