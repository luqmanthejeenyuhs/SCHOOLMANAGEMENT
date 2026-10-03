<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeCategory;
use App\Models\SchoolClass;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeeCategoryController extends Controller
{
    /**
     * "Fee Categories" group classes that share a fee schedule — e.g.
     * Pre-Primary (PP1-PP2), Lower Primary (Grade 1-3), Upper Primary
     * (Grade 4-6), Junior Secondary (Grade 7-9). Each school defines its
     * own; a class assigned to a category is automatically removed from
     * whichever category it was in before (a class belongs to at most one).
     */
    public function index()
    {
        $categories = FeeCategory::withCount(["schoolClasses", "feeTypes"])->with("schoolClasses")->orderBy("name")->get();
        $classes = SchoolClass::orderBy("name")->get();

        return view("admin.fee_categories.index", compact("categories", "classes"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => ["required", "string", "max:255", Rule::unique("fee_categories")->where("school_id", Tenant::id())],
            "school_class_ids" => "nullable|array",
            "school_class_ids.*" => [Rule::exists("school_classes", "id")->where("school_id", Tenant::id())],
        ]);

        $category = FeeCategory::create(["name" => $data["name"]]);

        if (! empty($data["school_class_ids"])) {
            SchoolClass::whereIn("id", $data["school_class_ids"])->update(["fee_category_id" => $category->id]);
        }

        return back()->with("success", "Fee category created.");
    }

    public function update(Request $request, FeeCategory $feeCategory)
    {
        $data = $request->validate([
            "name" => ["required", "string", "max:255", Rule::unique("fee_categories")->where("school_id", Tenant::id())->ignore($feeCategory->id)],
            "school_class_ids" => "nullable|array",
            "school_class_ids.*" => [Rule::exists("school_classes", "id")->where("school_id", Tenant::id())],
        ]);

        $feeCategory->update(["name" => $data["name"]]);

        // Detach any class that used to be in this category but wasn't
        // re-selected, then (re)assign the ones that were.
        SchoolClass::where("fee_category_id", $feeCategory->id)->update(["fee_category_id" => null]);
        if (! empty($data["school_class_ids"])) {
            SchoolClass::whereIn("id", $data["school_class_ids"])->update(["fee_category_id" => $feeCategory->id]);
        }

        return back()->with("success", "Fee category updated.");
    }

    public function destroy(FeeCategory $feeCategory)
    {
        // Classes and fee types just fall back to "uncategorized" (nullable
        // FK, ON DELETE SET NULL) — nothing else is deleted.
        $feeCategory->delete();

        return back()->with("success", "Fee category deleted.");
    }
}
