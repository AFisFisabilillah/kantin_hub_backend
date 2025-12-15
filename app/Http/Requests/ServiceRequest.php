<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required'],
            'customer_phone' => ['required'],
            'laptop_brand' => ['required'],
            'laptop_model' => ['required'],
            'complaint' => ['required'],
            'service_cost' => ['required','integer'],
            'products' => ['nullable','array'],
            'products.*.product_id' => ['required','exists:products,id'],
            'products.*.qty' => ['required','integer','min:1'],
            'images.*' => ['nullable','image','max:2048'],
        ];
    }
}
