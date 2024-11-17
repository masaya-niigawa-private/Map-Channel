<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AdminController;

class MapController extends Controller
{
    public function showMap()
    {
        //Spotテーブルを取得
        $admin = new AdminController();
        $spots = $admin->get();
        //GOOGLE_MAPS_API_KEYを取得
        $api_key = env('GOOGLE_MAPS_API_KEY');
        //Viewに渡す
        return view("sample", compact('spots', 'api_key'));
    }
}
