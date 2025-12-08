<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;

class AdminController extends Controller
{
    public function index()
    {
        return AdminResource::collection(Admin::all());
    }

    public function store(AdminRequest $request)
    {
        return new AdminResource(Admin::create($request->validated()));
    }

    public function show(Admin $admin)
    {
        return new AdminResource($admin);
    }

    public function update(AdminRequest $request, Admin $admin)
    {
        $admin->update($request->validated());

        return new AdminResource($admin);
    }

    public function destroy(Admin $admin)
    {
        $admin->delete();

        return response()->json();
    }
}
