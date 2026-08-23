<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['school_id', 'employee_id', 'date', 'clock_in', 'clock_out', 'method', 'status', 'marked_by', 'remarks'];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
