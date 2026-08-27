<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Services\AccountProvisioningService;
use App\Support\Facades\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParentController extends Controller
{
    public function index()
    {
        $parents = User::where("role", "parent")
            ->withCount("children")
            ->latest()
            ->paginate(10);

        return view("admin.parents.index", compact("parents"));
    }

    public function create()
    {
        $students = Student::with("user")->orderBy("admission_no")->get();

        return view("admin.parents.create", compact("students"));
    }

    public function store(Request $request, AccountProvisioningService $accounts)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "username" => ["required", "string", "max:50", "alpha_dash", Rule::unique("users", "username")->where("school_id", Tenant::id())],
            "phone" => "nullable|string",
            "children" => "array",
            "children.*" => "exists:students,id",
            "relationship" => "nullable|string|max:50",
        ]);

        // Password is generated and emailed, not chosen here — see
        // AccountProvisioningService. The admin never sees it.
        $parent = $accounts->createUserAccount([
            "name" => $data["name"],
            "email" => $data["email"],
            "username" => $data["username"],
            "role" => "parent",
            "phone" => $data["phone"] ?? null,
        ]);

        if (! empty($data["children"])) {
            $syncData = [];
            foreach ($data["children"] as $studentId) {
                $syncData[$studentId] = ["relationship" => $data["relationship"] ?? null];
            }
            $parent->children()->sync($syncData);
        }

        return redirect()->route("admin.parents.index")->with("success", "Parent account created successfully.");
    }

    public function show(User $parent)
    {
        abort_unless($parent->role === "parent", 404);

        $parent->load(["children.user", "children.schoolClass", "children.section"]);

        return view("admin.parents.show", compact("parent"));
    }

    public function destroy(User $parent)
    {
        abort_unless($parent->role === "parent", 404);

        $parent->delete();

        return back()->with("success", "Parent account removed.");
    }
}
