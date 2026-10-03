<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeCategory;
use App\Models\FeeType;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeeTypeController extends Controller
{
    public function index()
    {
        $feeTypes = FeeType::with("feeCategory")->latest()->get();
        $categories = FeeCategory::orderBy("name")->get();

        return view("admin.fees.index", compact("feeTypes", "categories"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "amount" => "required|numeric|min:0",
            "frequency" => "required|string|max:50",
            "fee_category_id" => ["nullable", Rule::exists("fee_categories", "id")->where("school_id", Tenant::id())],
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
