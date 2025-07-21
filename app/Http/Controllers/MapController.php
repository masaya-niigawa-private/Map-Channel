<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AdminController;

class MapController extends Controller
{
    public function showMap()
    {
        //GOOGLE_MAPS_API_KEYを取得
        $api_key = env('GOOGLE_MAPS_API_KEY');
        //Viewに渡す
        return view("all_category", compact('api_key'));
    }

    public function showKandaiMap()
    {
        //GOOGLE_MAPS_API_KEYを取得
        $api_key = env('GOOGLE_MAPS_API_KEY');
        //Viewに渡す
        return view("kandai", compact('api_key'));
    }
}
