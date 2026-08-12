<?php

namespace App\Models;

use App\Support\Facades\Tenant;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ["school_id", "name", "email", "password", "role", "phone", "is_active"];

    protected $hidden = ["password", "remember_token"];

    protected $casts = [
        "email_verified_at" => "datetime",
        "password" => "hashed",
        "is_active" => "boolean",
    ];

    protected static function booted(): void
    {
        static::creating(function (self $user) {
            if (empty($user->school_id) && Tenant::check()) {
                $user->school_id = Tenant::id();
            }
        });
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

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

    public function children()
    {
        return $this->belongsToMany(Student::class, "parent_student", "parent_id", "student_id")
            ->withPivot("relationship")
            ->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(\App\Models\Permission::class);
    }

    public function hasPermission(string $key): bool
    {
        if ($this->role === 'super_admin' || $this->is_super_admin) {
            return true;
        }

        return $this->permissions()->where('key', $key)->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === "super_admin";
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
}
