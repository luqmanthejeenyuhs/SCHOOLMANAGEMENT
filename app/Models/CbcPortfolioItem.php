<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbcPortfolioItem extends Model
{
    use HasFactory;

    protected $fillable = [
        "student_id", "cbc_sub_strand_id", "title", "evidence_type",
        "file_path", "original_filename", "term", "notes", "uploaded_by",
    ];

    public const TYPES = [
        "photo" => "Photo",
        "document" => "Document / Scan",
        "audio" => "Audio Clip",
        "video" => "Video Clip",
        "other" => "Other",
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subStrand()
    {
        return $this->belongsTo(CbcSubStrand::class, "cbc_sub_strand_id");
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, "uploaded_by");
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->evidence_type] ?? $this->evidence_type;
    }
}
