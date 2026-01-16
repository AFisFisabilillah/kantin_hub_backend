<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Imports\ProductsImport;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index()
    {
        $query = Product::query();

        // Search fulltext
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereFullText(['name', 'description', 'brand'], $search)
                    ->orWhere('sku', $search);
            });
        }



        // Range harga
        if ($min = request('min_price')) {
            $query->where('price', '>=', $min);
        }

        if ($max = request('max_price')) {
            $query->where('price', '<=', $max);
        }

        return ProductResource::collection(
            $query->paginate(request('per_page', 10))
        );
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        $paths = [];
        // Upload images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $paths[] = $file->store('products', 'public');
            }
            $data['images'] = $paths;
        }


        $product = Product::create($data);
        return new ProductResource($product);
    }

    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    public function destroyAll(Request $request)
    {
        $data = $request->validate([
            "products.*" => "required|exists:products,id",
        ]);

        Product::whereIn('id', $data['products'])->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus',
        ]);
    }
    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();

        // Handle image updates
        if ($request->hasFile('images')) {

            // Hapus gambar lama
            if ($product->images) {
                foreach ($product->images as $img) {
                    Storage::disk('public')->delete($img);
                }
            }

            $paths = [];
            foreach ($request->file('images') as $file) {
                $paths[] = $file->store('products', 'public');
            }
            $data['images'] = $paths;
        }

        $product->update($data);

        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        $product->delete(); // soft delete

        return response()->json(['message' => 'deleted']);
    }

    public function trashed()
    {
        return ProductResource::collection(
            Product::onlyTrashed()->paginate(10)
        );
    }

    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();

        return response()->json([
            'message' => 'restored',
            'product' => $product
        ]);
    }

    public function restoreAll(Request $request){
        $data = $request->validate([
            "products.*" => "required|exists:products,id",
        ]);

        Product::onlyTrashed()->whereIn('id', $data['products'])->restore();

        return response()->json([
            "message" => "products alredy restored",
        ]);
    }

    public function forceAll(Request $request)
    {
        $data = $request->validate([
            'products'   => 'required|array',
            'products.*' => 'required|exists:products,id',
        ]);

        $products = Product::onlyTrashed()
            ->whereIn('id', $data['products'])
            ->get();

        foreach ($products as $product) {
            if (!empty($product->images)) {
                foreach ($product->images as $img) {
                    Storage::disk('public')->delete($img);
                }
            }
        }

        Product::onlyTrashed()
            ->whereIn('id', $data['products'])
            ->forceDelete();

        return response()->json([
            'message' => 'force deleted',
        ]);
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        // Hapus permanen image
        if ($product->images) {
            foreach ($product->images as $img) {
                Storage::disk('public')->delete($img);
            }
        }

        $product->forceDelete();

        return response()->json(['message' => 'force deleted']);
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new ProductsImport, $request->file('file'));
            return response()->json([
                'message' => 'Import berhasil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import gagal'.$e->getMessage()
            ],400);
        }
    }


}
