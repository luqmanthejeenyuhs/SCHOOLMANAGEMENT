<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per report delivered (or attempted) to one guardian over one
 * channel — the audit trail shown at the bottom of Communication > Parent Reports.
 */
class GuardianReportLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        "school_id", "student_id", "channel", "recipient", "recipient_name",
        "report_types", "subject", "status", "error", "sent_by",
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, "sent_by");
    }
}
