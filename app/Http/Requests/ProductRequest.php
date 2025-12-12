<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('product')?->id;

        return [
            'sku' => ['required', "unique:products,sku,$id"],
            'name' => ['required'],
            'brand' => ['required'],
            'price' => ['required', 'integer'],
            'stok' => ['required', 'integer'],
            'description' => ['required'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,png,jpeg,webp', 'max:2048'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
