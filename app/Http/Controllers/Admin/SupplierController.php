<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount("bills")->orderBy("name")->paginate(15);

        return view("admin.suppliers.index", compact("suppliers"));
    }

    public function create()
    {
        return view("admin.suppliers.create");
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "contact_person" => "nullable|string|max:255",
            "phone" => "nullable|string|max:50",
            "email" => "nullable|email|max:255",
            "kra_pin" => "nullable|string|max:20",
            "address" => "nullable|string",
            "payment_terms_days" => "required|integer|min:0|max:365",
        ]);

        Supplier::create($data);

        return redirect()->route("admin.suppliers.index")->with("success", "Supplier added.");
    }

    public function edit(Supplier $supplier)
    {
        return view("admin.suppliers.edit", compact("supplier"));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "contact_person" => "nullable|string|max:255",
            "phone" => "nullable|string|max:50",
            "email" => "nullable|email|max:255",
            "kra_pin" => "nullable|string|max:20",
            "address" => "nullable|string",
            "payment_terms_days" => "required|integer|min:0|max:365",
            "is_active" => "nullable|boolean",
        ]);
        $data["is_active"] = $request->boolean("is_active");

        $supplier->update($data);

        return redirect()->route("admin.suppliers.index")->with("success", "Supplier updated.");
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(["bills" => fn ($q) => $q->latest(), "creditNotes"]);

        return view("admin.suppliers.show", compact("supplier"));
    }
}
