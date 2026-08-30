<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    public function show()
    {
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(10)],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->input('password')),
            'must_change_password' => false,
        ]);

        return redirect(RouteServiceProvider::redirectByRole())->with('success', 'Password set. Welcome in!');
    }
}
