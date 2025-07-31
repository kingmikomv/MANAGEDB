<?php

namespace App\Http\Controllers;

use App\Models\OLT;
use App\Models\Mikrotik;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){

        $mikrotik = Mikrotik::get();
        $olt = OLT::get();
        return view('Dashboard/depan/index', compact('mikrotik', 'olt'));
    }
}
