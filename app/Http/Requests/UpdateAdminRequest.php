<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'profile' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'password' => ['nullable', 'string', 'min:6'],
            'fullname' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
