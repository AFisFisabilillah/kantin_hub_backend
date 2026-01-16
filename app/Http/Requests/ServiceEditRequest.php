<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceEditRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_name' => ['required'],
            'customer_phone' => ['required'],
            'laptop_brand' => ['required'],
            'laptop_model' => ['required'],
            'complaint' => ['required'],
            'status' => ['required', 'in:received,process,done,taken,cancelled'],
            'service_cost' => ['nullable','integer'],
            'products' => ['nullable','array'],
            'products.*.product_id' => ['required','exists:products,id'],
            'products.*.qty' => ['required','integer','min:1'],
            'images.*' => ['nullable','image','max:2048'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
