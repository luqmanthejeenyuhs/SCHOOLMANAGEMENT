<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ["school_id", "student_id", "recipient_phone", "message", "category", "status", "provider_response", "sent_by"];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, "sent_by");
    }
}
