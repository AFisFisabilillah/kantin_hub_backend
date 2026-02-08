<?php

namespace App\Http\Controllers;

use App\Http\Requests\LandingPageRequest;
use App\Http\Resources\LandingPageResource;
use App\Models\LandingPage;
use Illuminate\Support\Facades\Storage;

class LandingPageController extends Controller
{

    public function index()
    {
        $landingPage = LandingPage::find('1');

        return new LandingPageResource($landingPage);
    }

    public function update(LandingPageRequest $request){
        $request->validated();
        $landingPage = LandingPage::find('1');

        if(!$landingPage){
            $heroImageUrl=$request->file('hero_image')->store('landing',"public" );
            $aboutImageUrl=$request->file('about_image')->store('landing',"public" );

            $landingPage = LandingPage::create([
                "hero_image"=>$heroImageUrl,
                "about_image" => $aboutImageUrl,
                "about_me" => $request->about_me,
                "hero_description" => $request->hero_description,
                "hero_tittle" => $request->hero_title,
            ]);
            return new LandingPageResource($landingPage);
        }else {
            if($request->hero_image){
                if(Storage::exists($landingPage->hero_image)){
                    Storage::delete($landingPage->hero_image);
                }
                $heroImageUrl = $request->file("hero_image")->store("landing","public");
                $landingPage->hero_image = $heroImageUrl;
            }
            if($request->about_image){
                if(Storage::exists($landingPage->about_image)){
                    Storage::delete($landingPage->about_image);
                }
                $aboutImageUrl =  $request->file("about_image")->store("landing","public");
                $landingPage->about_image = $aboutImageUrl;
            }
            $landingPage->about_me = $request->about_me;
            $landingPage->hero_description = $request->hero_description;
            $landingPage->hero_tittle = $request->hero_title;
            $landingPage->save();
            return new LandingPageResource($landingPage);
        }

    }
}
