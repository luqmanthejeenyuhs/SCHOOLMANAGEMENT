<?php

namespace App\Services;

use App\Mail\AccountCredentialsMail;
use App\Models\School;
use App\Models\User;
use App\Support\Facades\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Central place for "an admin is creating a login for someone else"
 * (teacher, student, parent, or — from the super_admin — a school's first
 * admin). The admin supplies a name, email, and username; this service
 * generates the password, never returns it to the caller for display, and
 * emails it directly to the new user. See App\Mail\AccountCredentialsMail.
 *
 * This is what makes requirement #3 hold: the admin who creates the
 * account genuinely never sees the password, and the account owner can
 * change it afterwards (via /account/password) without the admin knowing
 * the new value either.
 */
class AccountProvisioningService
{
    /**
     * @param  array{name:string,email:string,username:string,role:string,phone?:?string}  $attributes  Extra fillable User attributes (e.g. is_super_admin) can also be included.
     */
    public function createUserAccount(array $attributes, ?School $school = null): User
    {
        $plainPassword = Str::password(12);

        $user = User::create(array_merge($attributes, [
            "password" => Hash::make($plainPassword),
            // Forces them through /account/password before anything else —
            // see App\Http\Middleware\EnsurePasswordIsChanged — so this
            // system-generated password doesn't linger as their permanent
            // one just because they never got around to changing it.
            "must_change_password" => true,
        ]));

        $this->sendCredentials($user, $plainPassword, $school);

        return $user;
    }

    /**
     * Used by SuperAdmin\SchoolController, which creates the school's first
     * admin user "as" that school via Tenant::runFor so school_id gets
     * stamped automatically — this needs the $school passed explicitly
     * for the email branding since Tenant::current() isn't reliable
     * outside that closure.
     */
    public function sendCredentials(User $user, string $plainPassword, ?School $school = null): void
    {
        $school ??= $user->school_id ? ($user->school ?? Tenant::current()) : null;

        if (empty($user->email)) {
            return;
        }

        Mail::to($user->email)->send(new AccountCredentialsMail($user, $plainPassword, $school));
    }
}
