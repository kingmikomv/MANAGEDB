<?php

namespace App\Http\Controllers;

use App\Models\OLT;
use App\Models\Vpn;
use App\Models\Mikrotik;
use Illuminate\Http\Request;

class ModemController extends Controller
{
    public function tambahmodem(){

         $data = Vpn::get();
        $mikrotik = Mikrotik::get();
        $olts = OLT::all();
        $olt = OLT::get();


        return view('Dashboard.depan.modem.tambahmodem', compact('data', 'mikrotik', 'olts', 'olt'));
    }
}
