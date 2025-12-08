<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAdminRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticateController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            "username" => "required",
            "password" => "required"
        ]);

        $admin =Admin::where("username", $request->username)->first();
        if (!$admin && !Hash::check($request->password, $admin->password)) {
            return response()->json([
                "message" => "username or passworw is wrong"
            ], 401);
        }

        $token = $admin->createToken("auth_token")->plainTextToken;
        return response()->json([
            "message" => "success login",
            "token" => $token,
            "user" => new AdminResource($admin)
        ]);
    }

    public function logout(Request $request){
        $admin = auth("admin")->user();
        $admin->tokens()->delete();
        return response()->json([
            "message" => "success logout"
        ]);
    }

    public function profile(Request $request){
        $admin = auth("admin")->user();
        return new AdminResource($admin);
    }

    public function update(UpdateAdminRequest $request)
    {
        $data = $request->validated();

        $admin = auth("admin")->user();
        if ($request->hasFile('profile')) {
            if ($admin->profile && \Storage::exists($admin->profile)) {
                \Storage::delete($admin->profile);
            }

            $data['profile'] = $request->file('profile')->store('profiles/admin', 'public');
        }

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        } else {
            unset($data['password']);
        }

        $admin->update($data);

        return new AdminResource($admin);
    }

}
