<?php

namespace App\Http\Controllers;

use App\Models\OLT;
use App\Models\Vpn;
use App\Models\Modem;
use App\Models\Mikrotik;
use App\Models\ModemHistory;
use Illuminate\Http\Request;

class ModemController extends Controller
{
    public function index()
    {
        $modems = Modem::with('histories')->get();
        $data = Vpn::get();
        $mikrotik = Mikrotik::get();
        $olts = OLT::all();
        $olt = OLT::get();
        return view('Dashboard.depan.modem.index', compact('modems', 'data', 'mikrotik', 'olts', 'olt'));
    }

    public function tambahmodem()
    {
        $data = Vpn::get();
        $mikrotik = Mikrotik::get();
        $olts = OLT::all();
        $olt = OLT::get();
        return view('Dashboard.depan.modem.tambahmodem', compact('data', 'mikrotik', 'olts', 'olt'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'required',
            'pelanggan' => 'nullable|string',
        ]);

        $modem = Modem::firstOrCreate(
            ['serial_number' => $request->serial_number],
            ['status' => 'tersedia']
        );

        // Jika pelanggan baru diisi
        if ($request->filled('pelanggan')) {
            $modem->update([
                'status' => 'terpasang',
                'pelanggan_aktif' => $request->pelanggan
            ]);

            ModemHistory::create([
                'modem_id' => $modem->id,
                'pelanggan' => $request->pelanggan,
                'tanggal_pasang' => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Data modem disimpan!']);
    }

    public function tarik(Request $request)
    {
        $modem = Modem::where('serial_number', $request->serial_number)->first();

        if (!$modem) {
            return response()->json(['success' => false, 'message' => 'Modem tidak ditemukan!']);
        }

        // Update status modem
        $modem->update([
            'status' => 'ditarik',
            'pelanggan_aktif' => null
        ]);

        // Update history terakhir
        $lastHistory = $modem->histories()->latest()->first();
        if ($lastHistory && !$lastHistory->tanggal_tarik) {
            $lastHistory->update(['tanggal_tarik' => now()]);
        }

        return response()->json(['success' => true, 'message' => 'Modem berhasil ditarik!']);
    }
   public function history($id)
{
    $modems = Modem::find($id);
    $history = $modems->histories()->latest()->get();

    $mikrotik = Mikrotik::get();
    $olts = OLT::all();
    $olt = OLT::get();
    //dd($history);
   return view('Dashboard.depan.modem.history', compact('modems', 'history', 'mikrotik', 'olts', 'olt'));
}


public function storePasang(Request $request)
{
    $request->validate([
        'serial_number' => 'required|exists:modems,serial_number',
        'nama_pelanggan' => 'required|string|max:100',
    ]);

    try {
        $modem = Modem::where('serial_number', $request->serial_number)->firstOrFail();

        // update modem
        $modem->status = 'terpasang';
        $modem->pelanggan_aktif = $request->nama_pelanggan;
        $modem->save();

        // simpan history sesuai struktur migration
        \App\Models\ModemHistory::create([
            'modem_id' => $modem->id,
            'pelanggan' => $request->nama_pelanggan,
            'tanggal_pasang' => now(),
            // 'tanggal_tarik' biarkan null karena baru dipasang
            'keterangan' => 'Dipasang ke pelanggan baru',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Modem berhasil dipasang ke pelanggan baru.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal memasang modem: ' . $e->getMessage(),
        ]);
    }

    
}

public function updateStatus(Request $request)
{
    try {
        $request->validate([
            'serial_number' => 'required|string|exists:modems,serial_number',
            'status' => 'required|string|in:rusak,return',
            'keterangan' => 'nullable|string|max:255'
        ]);

        // Ambil modem berdasarkan serial number
        $modem = \App\Models\Modem::where('serial_number', $request->serial_number)->first();

        if (!$modem) {
            return response()->json([
                'success' => false,
                'message' => 'Modem tidak ditemukan.'
            ]);
        }

        // Simpan status lama untuk histori
        $statusLama = $modem->status;

        // Update status modem
        $modem->update([
            'status' => $request->status,
            'pelanggan_aktif' => null, // kosongkan karena tidak sedang terpasang
        ]);

        // Catat ke tabel modem_histories
        \App\Models\ModemHistory::create([
            'modem_id' => $modem->id,
            'pelanggan' => $modem->pelanggan_aktif ?? '-',
            'tanggal_tarik' => now(),
            'keterangan' => "Status diubah dari '{$statusLama}' menjadi '{$request->status}'" 
                . ($request->keterangan ? " ({$request->keterangan})" : ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status modem berhasil diperbarui menjadi: ' . ucfirst($request->status)
        ]);
    } catch (\Throwable $th) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $th->getMessage()
        ]);
    }
}








}
