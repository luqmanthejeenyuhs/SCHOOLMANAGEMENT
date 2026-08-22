<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class StudentCredit extends Model
{
    use BelongsToTenant;

    protected $fillable = ["school_id", "student_id", "balance"];

    protected $casts = ["balance" => "decimal:2"];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
