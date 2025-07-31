<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VpnController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['register' => false]);


Route::prefix('home')->group(function () {

    Route::get('/', [VpnController::class, 'index'])->name('index.depan');
    // Tambahkan route lain di sini
});
Route::prefix('/home/network')->controller(VpnController::class)->group(function () {

    Route::get('/', 'index')->name('vpn.index');
    Route::post('/uploadvpn', 'uploadvpn')->name('vpn.upload');
    Route::post('/uploadmikrotik', 'tambahmikrotik')->name('vpn.tambahmikrotik');
    Route::post('/editmikrotik', 'editmikroti')->name('vpn.editmikotik');
    Route::post('/updatemikrotik', 'updatemikrotik')->name('vpn.updatemikotik');
    Route::post('/destroymikrotik', 'destroymikrotik')->name('vpn.destroymikrotik');
    Route::get('/aksesmikrotik', 'aksesmikrotik')->name('vpn.aksesmikrotik');

    Route::get('/masukmikrotik', 'masukmikrotik')->name('masukmikrotik');
    Route::get('/dashboardmikrotik', 'dashboardmikrotik')->name('dashboardmikrotik');
    Route::get('/sync/{ipmikrotik}', 'sync')->name('sync');

    Route::post('/tambaholt', 'tambaholt')->name('tambaholt');
        Route::get('/hapusolt', 'hapusolt')->name('hapusolt');
    Route::post('/updateolt', 'updateolt')->name('update.olt');


    Route::get('/monitoring/active-connection/traffic', 'getTrafficData')->name('mikrotik.traffic');
    Route::post('/monitoring/add-firewall-rule', 'addFirewallRule')->name('addFirewallRule');
    Route::post('/monitoring/restartmodem', 'restartmodem')->name('restartmodem');
});



Route::get('/mikrotik/uptime/{ipmikrotik}', [VpnController::class, 'getUptime']);
