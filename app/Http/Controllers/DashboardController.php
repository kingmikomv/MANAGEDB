<?php

namespace App\Http\Controllers;

use App\Models\Mikrotik;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){

        $mikrotik = Mikrotik::get();
        return view('Dashboard/depan/index', compact('mikrotik'));
    }
}
