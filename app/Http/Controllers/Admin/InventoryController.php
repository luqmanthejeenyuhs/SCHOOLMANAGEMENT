<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\FeeType;
use App\Models\InventoryIssue;
use App\Models\InventoryItem;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $items = InventoryItem::withCount('issues')->orderBy('name')->get();
        $recentIssues = InventoryIssue::with(['item', 'student.user'])->latest()->limit(15)->get();

        return view('admin.inventory.index', compact('items', 'recentIssues'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100|unique:inventory_items,code',
            'category' => 'required|in:consumable,textbook',
            'unit_price' => 'required|numeric|min:0',
            'quantity_in_stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        InventoryItem::create($data);

        return back()->with('success', 'Item added to inventory.');
    }

    public function destroy(InventoryItem $item)
    {
        $item->delete();

        return back()->with('success', 'Item removed from inventory.');
    }

    /**
     * Storekeeper "Point of Sale" action: look up a student by admission number,
     * hand them a consumable item, decrement stock, and — if the school charges
     * for it — instantly append a fee line item to the student's pending bill
     * (the "Smart Charge Backing / Direct Purchases" workflow).
     */
    public function issue(Request $request, InventoryItem $item)
    {
        $data = $request->validate([
            'admission_no' => 'required|string|exists:students,admission_no',
            'quantity' => 'required|integer|min:1',
            'bill_to_fee_account' => 'nullable|boolean',
        ]);

        if ($data['quantity'] > $item->quantity_in_stock) {
            return back()->with('error', 'Not enough stock — only '.$item->quantity_in_stock.' left of "'.$item->name.'".');
        }

        $student = Student::where('admission_no', $data['admission_no'])->firstOrFail();
        $bill = $request->boolean('bill_to_fee_account');

        DB::transaction(function () use ($item, $student, $data, $bill) {
            $item->decrement('quantity_in_stock', $data['quantity']);

            $invoice = null;

            if ($bill && $item->unit_price > 0) {
                $feeType = FeeType::firstOrCreate(
                    ['name' => 'Stationery & Books'],
                    ['amount' => 0, 'frequency' => 'one_time']
                );

                $invoice = FeeInvoice::create([
                    'student_id' => $student->id,
                    'fee_type_id' => $feeType->id,
                    'amount' => $item->unit_price * $data['quantity'],
                    'due_date' => now()->addDays(7),
                    'status' => 'unpaid',
                ]);
            }

            InventoryIssue::create([
                'inventory_item_id' => $item->id,
                'student_id' => $student->id,
                'quantity' => $data['quantity'],
                'billed_to_fee_account' => $bill,
                'fee_invoice_id' => $invoice?->id,
                'issued_by' => Auth::id(),
                'issued_at' => now(),
            ]);
        });

        return back()->with('success', $data['quantity'].' x '.$item->name.' issued to '.$student->admission_no.($bill ? ' and charged to their fee account.' : '.'));
    }
}
