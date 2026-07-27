<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherDocument extends Model
{
    use HasFactory;

    protected $fillable = ["teacher_id", "type", "original_name", "path"];

    public const TYPES = [
        "passport_photo" => "Passport Photo",
        "national_id_document" => "National ID / Passport",
        "police_clearance" => "Police Clearance Certificate",
        "other" => "Other Document",
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function label(): string
    {
        return self::TYPES[$this->type] ?? "Document";
    }
}
