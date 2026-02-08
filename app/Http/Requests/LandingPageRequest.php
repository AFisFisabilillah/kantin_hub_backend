<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LandingPageRequest extends FormRequest
{
    public function rules()
    {
        return [
            "hero_title" => "required|string|max:100",
            "hero_description" => "required|string",
            "hero_image" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
            "about_me" => "required|string",
            "about_image" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
        ];
    }

    public function authorize()
    {
        return true;
    }
}
