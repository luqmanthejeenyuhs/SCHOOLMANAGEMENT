<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "password" => "required|min:6",
            "phone" => "nullable|string",
            "children" => "array",
            "children.*" => "exists:students,id",
            "relationship" => "nullable|string|max:50",
        ]);

        $parent = User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => Hash::make($data["password"]),
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
