<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankController extends Controller
{
    public function index()
    {
        $banks = Bank::orderBy("name")->get();

        return view("admin.banks.index", compact("banks"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => ["required", "string", "max:255", Rule::unique("banks")->where("school_id", Tenant::id())],
        ]);

        Bank::create($data);

        return back()->with("success", "Bank added.");
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();

        return back()->with("success", "Bank removed.");
    }
}
