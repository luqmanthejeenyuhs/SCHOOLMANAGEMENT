<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\SmsLog;
use App\Models\Student;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsController extends Controller
{
    public function __construct(protected SmsService $sms)
    {
    }

    public function index()
    {
        $classes = SchoolClass::all();
        $logs = SmsLog::with("student.user")->latest()->paginate(20);

        return view("admin.sms.index", compact("classes", "logs"));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            "audience" => "required|in:all,class,unpaid_balance",
            "school_class_id" => "nullable|exists:school_classes,id",
            "message" => "required|string|max:480",
            "category" => "required|in:announcement,fee_reminder,closure,general",
        ]);

        $query = Student::with("user")->whereNotNull("guardian_phone");

        if ($data["audience"] === "class" && $data["school_class_id"]) {
            $query->where("school_class_id", $data["school_class_id"]);
        } elseif ($data["audience"] === "unpaid_balance") {
            $query->whereHas("feeInvoices", fn ($q) => $q->where("status", "!=", "paid"));
        }

        $students = $query->get();

        $recipients = $students->map(fn ($s) => [
            "phone" => $s->guardian_phone,
            "student_id" => $s->id,
        ])->all();

        [$sent, $failed] = $this->sms->sendBulk($recipients, $data["message"], $data["category"], Auth::id());

        return back()->with("success", "SMS broadcast complete: {$sent} sent, {$failed} failed/queued. See log below.");
    }
}
