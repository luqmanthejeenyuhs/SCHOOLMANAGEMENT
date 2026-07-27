<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeType;
use Illuminate\Http\Request;

class FeeTypeController extends Controller
{
    public function index()
    {
        $feeTypes = FeeType::latest()->get();

        return view("admin.fees.index", compact("feeTypes"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "amount" => "required|numeric|min:0",
            "frequency" => "required|string|max:50",
        ]);
        FeeType::create($data);

        return back()->with("success", "Fee type created.");
    }

    public function destroy(FeeType $feeType)
    {
        $feeType->delete();

        return back()->with("success", "Fee type deleted.");
    }
}
