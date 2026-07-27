<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\FeeType;
use App\Models\InventoryItem;
use App\Models\Student;
use App\Models\TextbookCopy;
use App\Models\TextbookLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TextbookController extends Controller
{
    // A lost/damaged copy is charged at this multiple of the book's catalogue price.
    protected const PENALTY_MULTIPLIER = 1.0;

    public function index()
    {
        $textbookTitles = InventoryItem::where('category', 'textbook')->orderBy('name')->get();
        $copies = TextbookCopy::with(['item', 'currentStudent.user'])->latest()->paginate(15);
        $activeLoans = TextbookLoan::with(['copy.item', 'student.user'])->where('status', 'issued')->latest()->get();

        return view('admin.inventory.textbooks', compact('textbookTitles', 'copies', 'activeLoans'));
    }

    /**
     * Register a new physical copy of a textbook title under a unique barcode/serial.
     * Any USB or Bluetooth barcode scanner types into the barcode field like a keyboard,
     * so no special hardware integration is needed — just keep the field focused.
     */
    public function storeCopy(Request $request)
    {
        $data = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'barcode' => 'required|string|max:100|unique:textbook_copies,barcode',
        ]);
        $data['status'] = 'in_store';
        $data['condition'] = 'good';

        TextbookCopy::create($data);
        InventoryItem::where('id', $data['inventory_item_id'])->increment('quantity_in_stock');

        return back()->with('success', 'Copy '.$data['barcode'].' added to the library.');
    }

    /**
     * Scan/type a copy's barcode + the student's admission number to lend it out.
     */
    public function issue(Request $request)
    {
        $data = $request->validate([
            'barcode' => 'required|string|exists:textbook_copies,barcode',
            'admission_no' => 'required|string|exists:students,admission_no',
            'due_date' => 'nullable|date',
        ]);

        $copy = TextbookCopy::where('barcode', $data['barcode'])->firstOrFail();

        if ($copy->status === 'issued') {
            return back()->with('error', 'Copy '.$copy->barcode.' is already out on loan.');
        }

        $student = Student::where('admission_no', $data['admission_no'])->firstOrFail();

        DB::transaction(function () use ($copy, $student, $data) {
            TextbookLoan::create([
                'textbook_copy_id' => $copy->id,
                'student_id' => $student->id,
                'issued_by' => Auth::id(),
                'issued_at' => now(),
                'due_date' => $data['due_date'] ?? now()->addMonths(4),
                'condition_at_issue' => $copy->condition,
                'status' => 'issued',
            ]);

            $copy->update(['status' => 'issued', 'current_student_id' => $student->id]);
        });

        return back()->with('success', 'Copy '.$copy->barcode.' issued to '.$student->admission_no.'.');
    }

    /**
     * Scan the barcode back in at return time. If marked lost/damaged, an automatic
     * penalty invoice is raised against the student's fee account.
     */
    public function returnCopy(Request $request)
    {
        $data = $request->validate([
            'barcode' => 'required|string|exists:textbook_copies,barcode',
            'condition_at_return' => 'required|in:good,fair,damaged,lost',
        ]);

        $copy = TextbookCopy::where('barcode', $data['barcode'])->firstOrFail();
        $loan = TextbookLoan::where('textbook_copy_id', $copy->id)->where('status', 'issued')->latest()->first();

        if (! $loan) {
            return back()->with('error', 'No active loan found for copy '.$copy->barcode.'.');
        }

        DB::transaction(function () use ($copy, $loan, $data) {
            $isPenalised = in_array($data['condition_at_return'], ['damaged', 'lost']);
            $penaltyInvoice = null;

            if ($isPenalised) {
                $feeType = FeeType::firstOrCreate(
                    ['name' => 'Textbook Penalty'],
                    ['amount' => 0, 'frequency' => 'one_time']
                );

                $penaltyInvoice = FeeInvoice::create([
                    'student_id' => $loan->student_id,
                    'fee_type_id' => $feeType->id,
                    'amount' => $copy->item->unit_price * self::PENALTY_MULTIPLIER,
                    'due_date' => now()->addDays(14),
                    'status' => 'unpaid',
                ]);
            }

            $loan->update([
                'returned_at' => now(),
                'condition_at_return' => $data['condition_at_return'],
                'status' => $data['condition_at_return'] === 'lost' ? 'lost' : ($data['condition_at_return'] === 'damaged' ? 'damaged' : 'returned'),
                'penalty_invoice_id' => $penaltyInvoice?->id,
            ]);

            // A lost copy leaves the usable pool entirely; a returned/damaged one goes back to store.
            $copy->update([
                'status' => $data['condition_at_return'] === 'lost' ? 'issued' : 'in_store',
                'condition' => $data['condition_at_return'],
                'current_student_id' => $data['condition_at_return'] === 'lost' ? $copy->current_student_id : null,
            ]);
        });

        return back()->with('success', 'Copy '.$copy->barcode.' marked '.$data['condition_at_return'].'.'.(in_array($data['condition_at_return'], ['damaged', 'lost']) ? ' Penalty invoice raised.' : ' Back in the library.'));
    }
}
