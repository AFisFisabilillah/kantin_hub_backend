<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Admin::query();
        if($request->search){
            $search = $request->search;
            $query->where(function ($query) use ($search) {
                $query->where('fullname', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%');
            });
        }

        $admins = $query->latest()->paginate($request->per_page ?? 10);
        return AdminResource::collection($admins);
    }

    public function store(AdminRequest $request)
    {
        $data = $request->validated();

        if($request->hasFile('profile')){
            $data["profile"] = $request->file("profile")->store("profiles", "public");
        }

       $admin =  Admin::create([
            "fullname" => $data["fullname"],
            "username" => $data["username"],
            "password" => Hash::make($data["password"]),
            "phone" => $data["phone"],
            "profile" => $data["profile"] ?? null,
        ]);
        return new AdminResource($admin);
    }

    public function show(Admin $admin)
    {
        return new AdminResource($admin);
    }

    public function update(Request $request, Admin $admin)
    {
        $data = $request->validate([
            "fullname" => "required|string",
            "phone" => "required|string",
            "password" => "nullable|string|min:6",
            "profile" => "nullable|file|mimes:jpg,jpeg,png,heic",
        ]);

        if($request->hasFile('profile')){
            if($admin->profile){
                if(Storage::exists($admin->profile)){
                    Storage::delete($admin->profile);
                }
            }

            $data["profile"] = $request->file("profile")->store("profiles", "public");
        }

       if(isset($data["password"])){
           $admin->password = Hash::make($data["password"]);
       }

       $admin->fullname = $data["fullname"];
       $admin->phone = $data["phone"];
       $admin->profile = $data["profile"];
       $admin->save();

        return new AdminResource($admin);
    }

    public function destroy(Admin $admin)
    {
        $admin->delete();

        return response()->json([
            "message" => "Admin deleted successfully"
        ]);
    }
}
