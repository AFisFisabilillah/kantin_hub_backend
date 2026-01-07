<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            "profile" => "nullable|file|mimetypes:image/jpeg,image/png|max:4040|image",
            "password" => "required|string|min:6",
            "fullname" => "required|string",
            "username" => "required|string|unique:admins,username",
            "phone" => "required|string",
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
