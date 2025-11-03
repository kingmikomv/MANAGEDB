<?php

use App\Http\Controllers\ModemController;
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

Auth::routes(['register' => true]);

Route::prefix('home')->group(function () {

    Route::get('/', [VpnController::class, 'index'])->name('index.depan');
    // Tambahkan route lain di sini
});
Route::prefix('/home/network')->controller(VpnController::class)->group(function ($id = null) {

    Route::get('/', 'index')->name('vpn.index');
    Route::post('/uploadvpn', 'uploadvpn')->name('vpn.upload');
    Route::post('/uploadmikrotik', 'tambahmikrotik')->name('vpn.tambahmikrotik');
    Route::delete('/hapusvpn/{id}', 'hapusvpn')->name('hapusvpn');

    Route::post('/editmikrotik', 'editmikroti')->name('vpn.editmikotik');
    Route::post('/updatemikrotik', 'updatemikrotik')->name('vpn.updatemikotik');
    Route::delete('/destroymikrotik/{id}', 'destroymikrotik')->name('vpn.destroymikrotik');
    Route::get('/aksesmikrotik', 'aksesmikrotik')->name('vpn.aksesmikrotik');

    Route::get('/masukmikrotik', 'masukmikrotik')->name('masukmikrotik');
    Route::get('/dashboardmikrotik', 'dashboardmikrotik')->name('dashboardmikrotik');
    // URL: /home/network/dashboardmikrotik/traffic?ipmikrotik=...&traffic=...
    Route::get('/dashboardmikrotik/status', 'statusPage')->name('mikrotik.status');
    Route::get('/dashboardmikrotik/status/get-traffic', 'getTrafficFromIp')->name('get.traffic');

    Route::get('/sync/{ipmikrotik}', 'sync')->name('sync');

    Route::post('/tambaholt', 'tambaholt')->name('tambaholt');
    Route::get('/hapusolt/{id}', 'hapusolt')->name('hapusolt');
    Route::put('/updateolt', 'updateolt')->name('update.olt');

    Route::post('/monitoring/add-firewall-rule', 'addFirewallRule')->name('addFirewallRule');
    Route::post('/monitoring/restartmodem', 'restartmodem')->name('restartmodem');
});

Route::prefix('/home/modem')
    ->controller(ModemController::class)
    ->group(function () {
        Route::get('/', 'index')->name('modem.index');                // Menampilkan daftar modem
        Route::get('/tambah', 'tambahmodem')->name('modem.tambahmodem'); // Halaman tambah modem
        Route::post('/store', 'store')->name('modem.store');          // Simpan data modem baru
        Route::post('/tarik', 'tarik')->name('modem.tarik');          // Update status modem jadi ditarik
        Route::get('/detail/{id}', 'show')->name('modem.show');

        Route::get('/history/{serial_number}', 'history')->name('modem.history');
        Route::get('/pasang/{serial_number}', [ModemController::class, 'pasangModem'])->name('modem.pasang');
        Route::post('/pasang/ok', [ModemController::class, 'storePasang'])->name('modem.storePasang');

    });

Route::get('/mikrotik/uptime/{ipmikrotik}', [VpnController::class, 'getUptime']);
// routes/web.php
