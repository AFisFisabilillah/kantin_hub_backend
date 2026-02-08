<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LandingPageRequest extends FormRequest
{
    public function rules()
    {
        return [
            "hero_title" => "required|string|max:100",
            "hero_"
        ];
    }

    public function authorize()
    {
        return true;
    }
}
