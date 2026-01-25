<?php

namespace App\Http\Controllers;

use App\Exports\SaleExport;
use App\Http\Requests\SaleRequest;
use App\Http\Resources\SaleDetailResource;
use App\Http\Resources\SaleResource;
use App\Imports\SaleImport;
use App\Imports\ServicesImport;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::query();

        // 🔍 Search invoice / customer
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%$search%")
                    ->orWhere('customer_name', 'like', "%$search%");
            });
        }

        // 📅 Range tanggal
        if ($request->date_from && $request->date_to) {
            $query->whereBetween('created_at', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59',
            ]);
        }


        if ($request->min_total) {
            $query->where('total_price', '>=', $request->min_total);
        }

        //  total
        if ($request->max_total) {
            $query->where('total_price', '<=', $request->max_total);
        }

        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query->orderBy($sort, $order);

        return SaleResource::collection(
            $query->with('products')->paginate(
                $request->get('per_page', 10)
            )
        );
    }

    public function store(SaleRequest $request)
    {
        $data  = $request->validated();

        $sale = Sale::create([
            'invoice_number' => 'INV-'. now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
            'customer_name' => $request->customer_name,
            'payment_method' => $request->payment_method,
            'total_price' => 0
        ]);

        $total = 0;
        foreach ($request->products as $item) {
            $product = Product::findOrFail($item['product_id']);
            if ($product->stok < $item['qty']) {
                return response()->json([
                    'message' => "Stok {$product->name} tidak cukup"
                ], 422);
            }

            $subtotal = $product->price * $item['qty'];
            $total += $subtotal;

            $product->decrement('stok', $item['qty']);

            $sale->products()->attach($product->id, [
                'qty' => $item['qty'],
                'price' => $product->price,
                'subtotal' => $subtotal
            ]);
        }

        $sale->update(['total_price' => $total]);

        return new SaleResource($sale->load('products'));
    }

    public function update(SaleRequest $request, Sale $sale)
    {
        // kembalikan stok lama
        foreach ($sale->products as $product) {
            $product->increment('stok', $product->pivot->qty);
        }

        $sale->products()->detach();

        $total = 0;

        foreach ($request->products as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($product->stok < $item['qty']) {
                return response()->json([
                    'message' => "Stok {$product->name} tidak cukup"
                ], 422);
            }

            $subtotal = $product->price * $item['qty'];
            $total += $subtotal;

            $product->decrement('stok', $item['qty']);

            $sale->products()->attach($product->id, [
                'qty' => $item['qty'],
                'price' => $product->price,
                'subtotal' => $subtotal
            ]);
        }

        $sale->update([
            'customer_name' => $request->customer_name,
            'payment_method' => $request->payment_method,
            'total_price' => $total
        ]);

        return new SaleResource($sale->load('products'));
    }

    public function destroy(Sale $sale)
    {
        foreach ($sale->products as $product) {
            $product->increment('stok', $product->pivot->qty);
        }

        $sale->delete();

        return response()->json([
            'message' => 'Penjualan dipindahkan ke trash'
        ]);
    }


    public function destroyAll(Request $request)
    {
        $request->validate([
            "sales" => "required|array|min:1",
            "sales.*" => "required|exists:sales,id",
        ]);

        DB::transaction(function () use ($request) {
            Sale::whereIn('id', $request->sales)->delete();
        });

        return response()->json([
            'message' => 'Data penjualan berhasil dihapus'
        ], 200);
    }


    public function show(Sale $sale){
        return new SaleDetailResource($sale->load('products'));
    }

    public function trashed(Request $request){
        $sales = Sale::onlyTrashed()->paginate($request->per_page ?? 10);
        return SaleResource::collection($sales);
    }

    public function restore(int $id){
        DB::transaction(function () use ($id){
            Sale::onlyTrashed()->findOrFail($id)->restore();
        });
        return response()->json([
            "message" =>"Success restore penjualan"
        ]);
    }

    public function force(int $id){
        DB::transaction(function () use ($id){
            Sale::onlyTrashed()->findOrFail($id)->forceDelete();
        });
        return response()->json([
            "message" =>"Success delete penjualan"
        ]);
    }

    public function export()
    {
        return Excel::download(new SaleExport, 'sales.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new SaleImport, $request->file('file'));
            return response()->json([
                'message'=> 'Data Service berhasil diimport!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message'=> 'Data Service gagal diimport! ',
                "error" => $e->getMessage()
            ], 400);
        }
    }

}
