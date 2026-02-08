<?php

namespace App\Http\Resources;

use App\Models\LandingPage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LandingPage */
class LandingPageResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'hero_tittle' => $this->hero_tittle,
            'hero_description' => $this->hero_description,
            'hero_image' => asset($this->hero_image),
            'about_me' => $this->about_me,
            'about_image' => asset($this->about_image),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
