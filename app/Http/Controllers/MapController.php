<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AdminController;

class MapController extends Controller
{
    public function showMap()
    {
        //スポット情報を取得しViewに渡す
        $admin = new AdminController();
        $spots = $admin->get();
        
        return view("map", compact('spots'));
    }

}
