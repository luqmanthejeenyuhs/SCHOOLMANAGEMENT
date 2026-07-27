<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ["name", "email", "password", "role", "phone", "is_active"];

    protected $hidden = ["password", "remember_token"];

    protected $casts = [
        "email_verified_at" => "datetime",
        "password" => "hashed",
        "is_active" => "boolean",
    ];

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === "admin";
    }

    public function isTeacher(): bool
    {
        return $this->role === "teacher";
    }

    public function isStudent(): bool
    {
        return $this->role === "student";
    }
    public function isParent(): bool
{
    return $this->role === "parent";
}

public function children()
{
    return $this->belongsToMany(Student::class, "parent_student", "parent_id", "student_id")
        ->withPivot("relationship")->withTimestamps();
}
}
