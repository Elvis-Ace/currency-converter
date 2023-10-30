<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\File;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public static function image($image,$path):string{
        $canvas = Image::canvas(800,800);
        $img = Image::make($image)->resize(800, 800, function($constraint)
        {
            $constraint->aspectRatio();
        });
        $canvas->insert($img, 'center');
        //dd($image);
        $imageName = time().rand(1,9999).'.jpg';

        $canvas->save(public_path('assets/images/'.$path).$imageName,100);
        return $imageName;
    }

    public static function delete($folder,$file){
        File::delete(public_path("assets/images/".$folder.'/'.$file));
    }
}
