<?php

namespace App\Http\Controllers;

use App\Exports\ServiceExport;
use App\Http\Requests\ServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('service_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('laptop_brand', 'like', "%{$search}%")
                    ->orWhere('laptop_model', 'like', "%{$search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->from && $request->to) {
            $query->whereBetween('created_at', [
                $request->from,
                $request->to
            ]);
        }

        if ($request->min_cost) {
            $query->where('total_cost', '>=', $request->min_cost);
        }

        if ($request->max_cost) {
            $query->where('total_cost', '<=', $request->max_cost);
        }

        $query->withCount('products');

        $services = $query
            ->latest()
            ->paginate($request->per_page ?? 10);

        return ServiceResource::collection($services);
    }

    public function store(ServiceRequest $request)
    {
        $data = $request->validated();

        // upload images laptop
        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $file) {
                $paths[] = $file->store('services', 'public');
            }
            $data['images'] = $paths;
        }

        $data['service_code'] = 'SRV-' . time();
        $service = Service::create($data);
        $data['service_cost'] = $data['service_cost']??0;
        $totalSparepart = 0;

        if ($request->products) {
            foreach ($request->products as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) {
                    return response()->json([
                        "message" => "Product not found"
                    ], 404);
                }

                $subtotal = $product->price * $item['qty'];
                $totalSparepart += $subtotal;

                // kurangi stok
                $product->decrement('stok', $item['qty']);

                $service->products()->attach($product->id, [
                    'qty' => $item['qty'],
                    'price' => $product->price,
                    'subtotal' => $subtotal
                ]);
            }
        }

        $service->update([
            'total_cost' => $data['service_cost'] + $totalSparepart
        ]);

        $service->load("products");

        return new ServiceResource($service);
    }

    public function detail(Service $service)
    {
        $service->loadCount('products')->load('products');
        return new ServiceResource($service);
    }

    public function update(ServiceRequest $request, Service $service)
    {
        $data = $request->validated();

        $service->load("products");
        foreach ($service->products as $old) {
            $old->increment('stok', $old->pivot->qty);
        }

        $service->products()->detach();

        $totalSparepart = 0;

        if ($request->products) {
            foreach ($request->products as $item) {
                $product = Product::find($item['product_id']);

                $subtotal = $product->price * $item['qty'];
                $totalSparepart += $subtotal;

                $product->decrement('stok', $item['qty']);

                $service->products()->attach($product->id, [
                    'qty' => $item['qty'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ]);
            }
        }

        // update images (optional)
        if ($request->hasFile('images')) {
            foreach ($service->images ?? [] as $img) {
                Storage::disk('public')->delete($img);
            }

            $paths = [];
            foreach ($request->file('images') as $file) {
                $paths[] = $file->store('services', 'public');
            }
            $data['images'] = $paths;
        }

        $service->update([
            ...$data,
            'total_cost' => $data['service_cost'] + $totalSparepart
        ]);

        return new ServiceResource($service->load('products'));
    }



    public function trashed()
    {
        return ServiceResource::collection(
            Service::onlyTrashed()->paginate(10)
        );
    }

    public function restore($id)
    {
        $service = Service::onlyTrashed()->findOrFail($id);
        $service->restore();

        return response()->json(['message' => 'Service restored']);
    }

    public function forceDelete($id)
    {
        $service = Service::onlyTrashed()->findOrFail($id);

        foreach ($service->images ?? [] as $img) {
            Storage::disk('public')->delete($img);
        }

        $service->products()->detach();
        $service->forceDelete();

        return response()->json(['message' => 'Service permanently deleted']);
    }

    public function export()
    {
        return response()->streamDownload(function () {
            echo Excel::raw(
                new ServiceExport,
                \Maatwebsite\Excel\Excel::XLSX
            );
        }, 'services.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }

    public function changeStatus(Request $request, Service $service)
    {
        $request->validate([
            'status' => ['required', 'in:received,process,done,taken,cancelled']
        ]);

        $newStatus = $request->status;
        $oldStatus = $service->status;

        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {

            foreach ($service->products as $product) {
                $product->increment('stok', $product->pivot->qty);
            }
        }

        // Tidak boleh cancel setelah taken
        if ($oldStatus === 'taken') {
            return response()->json([
                'message' => 'Servis sudah diambil, status tidak bisa diubah'
            ], 422);
        }

        $service->update([
            'status' => $newStatus
        ]);

        return new ServiceResource($service->fresh()->load('products'));
    }

    public function destroy(Service $service)
    {
        // Jika servis belum selesai / belum diambil
        if (in_array($service->status, ['received', 'process'])) {
            foreach ($service->products as $product) {
                $product->increment('stok', $product->pivot->qty);
            }
        }

        $service->delete(); // soft delete

        return response()->json([
            'message' => 'Service moved to trash'
        ]);
    }
}
