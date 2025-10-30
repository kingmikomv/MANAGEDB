<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Mikrotik;
use App\Models\OLT;
use App\Models\Vpn;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RouterOS\Client;
use RouterOS\Query;

class VpnController extends Controller
{
    public function index()
    {
        $data = Vpn::get();
        $mikrotik = Mikrotik::get();
        $olts = OLT::all();
        $olt = OLT::get();

        return view('Dashboard.depan.vpn.index', compact('data', 'mikrotik', 'olts', 'olt'));
    }

    public function uploadvpn(Request $req)
    {
        $namaakun = $req->input('namaakun');
        $username = $req->input('username');
        $password = $req->input('password');
        $portwbx = $req->input('portwbx');

        $akuncomment = 'AQT_'.$namaakun;

        $portwbx = $portwbx ?: 8291;

        try {
            // ==========================
            // KONEKSI KE MIKROTIK
            // ==========================
            $client = new Client([
                'host' => env('MIKROTIK_HOST'),
                'user' => env('MIKROTIK_USER'),
                'pass' => env('MIKROTIK_PASS'),
            ]);

            // ==========================
            // CEK USERNAME SUDAH ADA
            // ==========================
            $queryAllSecrets = new Query('/ppp/secret/print');
            $response = $client->query($queryAllSecrets)->read();
            $existingUsernames = array_column($response, 'name');

            if (in_array($username, $existingUsernames)) {
                session()->flash('error', 'Username sudah ada, silakan gunakan username lain.');

                return redirect()->back();
            }

            // ==========================
            // GENERATE IP ADDRESS OTOMATIS
            // ==========================
            $firstOctet = '172';
            $secondOctet = 16;
            $usedThirdOctets = array_map(function ($secret) {
                return isset($secret['local-address'])
                    ? explode('.', $secret['local-address'])[2]
                    : null;
            }, $response);

            $usedThirdOctets = array_filter($usedThirdOctets);
            $thirdOctet = 11;

            while (in_array($thirdOctet, $usedThirdOctets)) {
                $thirdOctet++;
                if ($thirdOctet > 254) {
                    throw new \Exception('Tidak ada third octet yang tersedia.');
                }
            }

            $existingCount = count($response);
            $fourthOctetLocal = 1;
            $fourthOctetRemote = 10 + ($existingCount % 255);
            $localIp = "$firstOctet.$secondOctet.$thirdOctet.$fourthOctetLocal";
            $remoteIp = "$firstOctet.$secondOctet.$thirdOctet.$fourthOctetRemote";

            // ==========================
            // TAMBAH PPP SECRET
            // ==========================
            try {
                $query = new Query('/ppp/secret/add');
                $query->equal('name', $username)
                    ->equal('password', $password)
                    ->equal('comment', $akuncomment)
                    ->equal('profile', 'default')
                    ->equal('local-address', $localIp)
                    ->equal('remote-address', $remoteIp);
                $client->query($query)->read();
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal menambahkan PPP Secret: '.$e->getMessage());

                return redirect()->back();
            }

            // ==========================
            // CEK PORT SUDAH DIGUNAKAN
            // ==========================
            $queryAllNAT = new Query('/ip/firewall/nat/print');
            $natResponse = $client->query($queryAllNAT)->read();

            $usedPorts = [];
            foreach ($natResponse as $natRule) {
                if (isset($natRule['dst-port'])) {
                    $usedPorts[] = (int) $natRule['dst-port'];
                }
            }

            // ==========================
            // GENERATE PORT MIKROTIK (REMOTE)
            // ==========================
            $portmikrotik = null;
            for ($i = 43000; $i <= 43999; $i++) {
                if (! in_array($i, $usedPorts)) {
                    $portmikrotik = $i;
                    break;
                }
            }
            if (! $portmikrotik) {
                throw new \Exception('Tidak ada port MikroTik tersedia di range 43000-43999.');
            }

            // ==========================
            // ALOKASI PORT API DAN WEB
            // ==========================
            $portapi = null;
            for ($i = 40000; $i <= 41999; $i++) {
                if (! in_array($i, $usedPorts)) {
                    $portapi = $i;
                    break;
                }
            }
            if (! $portapi) {
                throw new \Exception('Tidak ada port API tersedia di range 40000-41999.');
            }

            $portweb = null;
            for ($i = 42000; $i <= 42999; $i++) {
                if (! in_array($i, $usedPorts)) {
                    $portweb = $i;
                    break;
                }
            }
            if (! $portweb) {
                throw new \Exception('Tidak ada port Web tersedia di range 42000-42999.');
            }

            // ==========================
            // TAMBAH NAT RULES
            // ==========================
            try {
                // NAT untuk API
                $natQuery1 = new Query('/ip/firewall/nat/add');
                $natQuery1->equal('chain', 'dstnat')
                    ->equal('protocol', 'tcp')
                    ->equal('dst-port', $portapi)
                    ->equal('dst-address-list', 'ip-public')
                    ->equal('action', 'dst-nat')
                    ->equal('to-addresses', $remoteIp)
                    ->equal('to-ports', 9000)
                    ->equal('comment', $akuncomment.'_API');
                $client->query($natQuery1)->read();

                // NAT untuk WEB
                $natQuery2 = new Query('/ip/firewall/nat/add');
                $natQuery2->equal('chain', 'dstnat')
                    ->equal('protocol', 'tcp')
                    ->equal('dst-port', $portweb)
                    ->equal('dst-address-list', 'ip-public')
                    ->equal('action', 'dst-nat')
                    ->equal('to-addresses', $remoteIp)
                    ->equal('to-ports', $portweb)
                    ->equal('comment', $akuncomment.'_WEB');
                $client->query($natQuery2)->read();

                // NAT untuk Winbox/MikroTik
                $natQuery3 = new Query('/ip/firewall/nat/add');
                $natQuery3->equal('chain', 'dstnat')
                    ->equal('protocol', 'tcp')
                    ->equal('dst-port', $portmikrotik)
                    ->equal('dst-address-list', 'ip-public')
                    ->equal('action', 'dst-nat')
                    ->equal('to-addresses', $remoteIp)
                    ->equal('to-ports', $portwbx)
                    ->equal('comment', $akuncomment.'_MikroTik');
                $client->query($natQuery3)->read();
            } catch (\Exception $e) {
                // Kalau gagal menambah NAT, hapus PPP Secret agar tidak nyangkut
                try {
                    $removePPP = new Query('/ppp/secret/remove');
                    $removePPP->equal('name', $username);
                    $client->query($removePPP)->read();
                } catch (\Throwable $t) {
                    // Abaikan error hapus
                }

                session()->flash('error', 'Gagal menambahkan NAT rules: '.$e->getMessage());

                return redirect()->back();
            }

            // ==========================
            // SIMPAN KE DATABASE
            // ==========================
            Vpn::create([
                'namaakun' => $namaakun,
                'username' => $username,
                'password' => $password,
                'ipaddress' => $remoteIp,
                'portapi' => $portapi,
                'portweb' => $portweb,
                'portmikrotik' => $portmikrotik,
                'portwbx' => $portwbx,
            ]);

            session()->flash('success', 'Akun VPN berhasil dibuat!');

            return redirect()->back();

        } catch (ClientException $e) {
            session()->flash('error', 'Gagal terhubung ke MikroTik: '.$e->getMessage());

            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());

            return redirect()->back();
        }
    }

    public function tambahmikrotik(Request $req)
    {
        $ipmikrotik = $req->input('ipmikrotik');
        $site = $req->input('site');
        $username = $req->input('username');
        $password = $req->input('password');

        try {
            // Assuming Mikrotik::create() method exists
            $data = Mikrotik::create([
                'ipmikrotik' => $ipmikrotik,
                'site' => $site,
                'username' => $username,
                'password' => $password,
            ]);

            session()->flash('success', 'Mikrotik '.$site.' Berhasil Di Tambahkan');

            return redirect()->back();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save data: '.$e->getMessage(),
            ]);
        }
    }

    public function editmikrotik($id)
    {
        $mikrotik = Mikrotik::find($id);
        if (! $mikrotik) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($mikrotik);
    }

    public function updatemikrotik(Request $request, $id)
    {
        $request->validate([
            'ipmikrotik' => 'required|ip',
            'site' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $mikrotik = Mikrotik::find($id);
        if ($mikrotik) {
            $mikrotik->ipmikrotik = $request->input('ipmikrotik');
            $mikrotik->site = $request->input('site');
            $mikrotik->username = $request->input('username');
            $mikrotik->password = $request->input('password');
            $mikrotik->save();

            return redirect()->back()->with('success', 'MikroTik updated successfully.');
        }

        return redirect()->back()->with('error', 'MikroTik not found.');
    }

    public function destroymikrotik($id)
    {
        $mikrotik = Mikrotik::find($id);
        if ($mikrotik) {
            $mikrotik->delete();

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error']);
    }

    public function aksesMikrotik(Request $request)
    {

        $ipmikrotik = $request->query('ipmikrotik');
        $username = $request->query('username');
        $password = $request->query('password');

        $dataport = Vpn::where('ipaddress', $ipmikrotik)->first();

        if (is_null($dataport)) {
            // Handle case when there is no data for the IP address in the database
            try {
                // Attempt to connect using default MikroTik IP without port information
                $connection = new Client([
                    'host' => env('MIKROTIK_CHOST').':'.$dataport->portapi,
                    'user' => $username,
                    'pass' => $password,

                ]);

                // If connection is successful
                session()->flash('success', 'Mikrotik Terhubung');

                return redirect()->back();
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to connect to MikroTik router : '.$e->getMessage());

                return redirect()->back();
            }
        } else {
            // Case where database entry exists for the IP
            if (is_null($dataport->portapi) == false) {
                try {
                    // Connect with port information from the database
                    $connection = new Client([
                        'host' => env('MIKROTIK_CHOST').':'.$dataport->portapi,
                        'user' => $username,
                        'pass' => $password,

                    ]);

                    // If connection is successful
                    session()->flash('success', 'Mikrotik Terhubung');

                    return redirect()->back();
                } catch (\Exception $e) {
                    session()->flash('error', 'Failed to connect to MikroTik router :  '.$e->getMessage());

                    return redirect()->back();
                }
            } else {
                try {
                    // Connect using the IP without port information
                    $connection = new Client([
                        'host' => env('MIKROTIK_CHOST').':'.$dataport->portapi,
                        'user' => $username,
                        'pass' => $password,

                    ]);

                    // If connection is successful
                    session()->flash('success', 'Mikrotik Terhubung tanpa port dari database');

                    return redirect()->back();
                } catch (\Exception $e) {
                    session()->flash('error', 'Failed to connect to MikroTik router: '.$e->getMessage());

                    return redirect()->back();
                }
            }
        }
    }

    public function masukmikrotik(Request $request)
    {
        // Ambil data MikroTik dari database berdasarkan parameter 'ipmikrotik'
        $ipmikrotik = $request->input('ipmikrotik');
        $data = Mikrotik::where('ipmikrotik', $ipmikrotik)->first();

        // Cek apakah data MikroTik ditemukan
        if (! $data) {
            return redirect()->back()->with('error', 'MikroTik data not found.');
        }

        $username = $data->username; // Ambil username dari database
        $password = $data->password; // Ambil password dari database
        $site = $data->site;

        // Cek data VPN berdasarkan IP address yang diberikan
        $datavpn = Vpn::where('ipaddress', $data->ipmikrotik)->first();

        // Set 'portweb' dari input request atau data VPN (jika ada)
        $portweb = $request->input('portweb') ?? ($datavpn->portweb ?? null);
        // Set 'portapi' dari data VPN jika tersedia
        $portapi = $datavpn->portapi ?? null;

        // Membangun konfigurasi koneksi berdasarkan data yang ada
        if (is_null($portapi)) {
            // Jika 'portapi' tidak ditemukan, gunakan IP publik dan port default
            return redirect()->back()->with('error', 'Untuk Masuk Ke Mikrotik Harus Melalui Jaringan VPN Yang Kami Sediakan');
        } else {
            // Jika data VPN ditemukan, gunakan 'portapi' dari VPN
            $config = [
                'host' => env('MIKROTIK_CHOST').':'.$portapi, // Menggunakan domain VPN dan port API dari data VPN
                'user' => $username,
                'pass' => $password,

            ];

            // Sertakan 'portweb' jika ada
            if ($portweb) {
                $config['port'] = $portweb;
            }
        }

        try {
            // Koneksi ke MikroTik menggunakan konfigurasi yang telah dibuat
            $client = new Client($config);
            $query = (new Query('/ppp/active/print'));
            $response = $client->query($query)->read();

            // Set variabel session untuk menandai bahwa koneksi berhasil
            session([
                'mikrotik_connected' => true,
                'ipmikrotik' => $ipmikrotik,
                'portapi' => $portapi,
            ]);

            // Hapus session 'session_disconnected' jika ada
            session()->forget('session_disconnected');

            // Arahkan ke halaman dashboardmikrotik setelah berhasil terkoneksi
            return redirect()->route('dashboardmikrotik', ['ipmikrotik' => $ipmikrotik]);
        } catch (\Exception $e) {
            // Jika terjadi error saat koneksi, hapus session dan tampilkan pesan error
            session()->forget('mikrotik_connected');
            session(['session_disconnected' => true]);

            return redirect()->back()->with('error', 'Error connecting to MikroTik: '.$e->getMessage());
        }
    }

    public function dashboardmikrotik(Request $request)
    {
        $ipmikrotik = $request->input('ipmikrotik');
        $mikrotik = Mikrotik::get();
        $olt = OLT::get();
        // Ambil data MikroTik berdasarkan IP
        $data = Mikrotik::where('ipmikrotik', $ipmikrotik)->first();
        $datavpn = Vpn::where('ipaddress', $data->ipmikrotik)->first();
        $data = Mikrotik::where('ipmikrotik', $ipmikrotik)->first();

        // Set 'portweb' dari input request atau data VPN (jika ada)
        $portweb = $request->input('portweb') ?? ($datavpn->portweb ?? null);
        // Set 'portapi' dari data VPN jika tersedia
        $portapi = $datavpn->portapi ?? null;

        $client = new Client([
            'host' => env('MIKROTIK_CHOST').':'.$portapi,
            'user' => $data->username,
            'pass' => $data->password,
        ]);

        // dd($client);

        $queryIdentity = new Query('/system/identity/print');
        $identity = $client->query($queryIdentity)->read();

        // Hasilnya array, ambil 'name'
        $site = $identity[0]['name'] ?? 'Unknown';

        $querySchedule = new Query('/system/scheduler/print');
        $sc = $client->query($querySchedule)->read();

        $queryActivePPP = new Query('/ppp/active/print');
        $response3 = $client->query($queryActivePPP)->read();

       foreach ($response3 as &$d) {
    if (isset($d['uptime'])) {
        $u = $d['uptime'];
        if (preg_match('/(\d+)w/', $u, $m))       $d['formatted_uptime'] = $m[1] . ' minggu';
        elseif (preg_match('/(\d+)d/', $u, $m))  $d['formatted_uptime'] = $m[1] . ' hari';
        elseif (preg_match('/(\d+)h/', $u, $m))  $d['formatted_uptime'] = $m[1] . ' jam';
        elseif (preg_match('/(\d+)m/', $u, $m))  $d['formatted_uptime'] = $m[1] . ' menit';
        elseif (preg_match('/(\d+)s/', $u, $m))  $d['formatted_uptime'] = $m[1] . ' detik';
        else $d['formatted_uptime'] = '-';
    } else {
        $d['formatted_uptime'] = '-';
    }

    // ubah uptime ke detik biar bisa disort
    $time = 0;
    if (preg_match('/(\d+)w/', $u, $m)) $time += $m[1] * 604800;
    if (preg_match('/(\d+)d/', $u, $m)) $time += $m[1] * 86400;
    if (preg_match('/(\d+)h/', $u, $m)) $time += $m[1] * 3600;
    if (preg_match('/(\d+)m/', $u, $m)) $time += $m[1] * 60;
    if (preg_match('/(\d+)s/', $u, $m)) $time += $m[1];
    $d['uptime_seconds'] = $time;
}
unset($d);

// urutkan berdasarkan uptime paling kecil (baru nyala di atas)
usort($response3, fn($a, $b) => $a['uptime_seconds'] <=> $b['uptime_seconds']);


        $queryActivePPP4 = new Query('/ppp/active/print');
        $response4 = $client->query($queryActivePPP4)->read();

        return view('Dashboard.depan.mikrotik.dashboardmikrotik', compact('ipmikrotik', 'site', 'mikrotik', 'portweb', 'portapi', 'olt', 'sc', 'response3', 'response4'));

    }

    public function sync($ipmikrotik)
    {
        $mikrotik = Mikrotik::where('ipmikrotik', $ipmikrotik)->first();
        $vpn = Vpn::where('ipaddress', $mikrotik->ipmikrotik)->first();

        if (! $mikrotik) {
            return redirect()->back()->with('error', 'Mikrotik tidak ditemukan.');
        }

        // Hubungkan ke API Mikrotik
        $client = new Client([
            'host' => env('MIKROTIK_CHOST'),
            'user' => $mikrotik->username,
            'pass' => $mikrotik->password,
            'port' => $vpn->portapi,
        ]);

        // Ambil data PPP active dari Mikrotik
        $query = new Query('/ppp/active/print');
        $pppActive = $client->query($query)->read();

        // Ambil semua akun yang sudah ada untuk mikrotik ini
        $aakun = Akun::where('site', $mikrotik->site)->get();

        // Loop data aktif, simpan jika belum ada
        foreach ($pppActive as $item) {
            $namaakun = $item['name'];

            $sudahAda = $aakun->contains('namaakun', $namaakun);

            if (! $sudahAda) {
                Akun::create([
                    'namaakun' => $namaakun,
                    'site' => $mikrotik->site,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Sinkronisasi berhasil.');
    }

    public function getUptime($ipmikrotik)
    {
        $data = Mikrotik::where('ipmikrotik', $ipmikrotik)->first();
        $datavpn = Vpn::where('ipaddress', $data->ipmikrotik)->first();
        if (! $data) {
            return response()->json(['error' => 'Data MikroTik tidak ditemukan.']);
        }

        try {
            $client = new Client([
                'host' => env('MIKROTIK_CHOST').':'.$datavpn->portapi, // Menggunakan domain VPN dan port API dari data VPN

                'user' => $data->username,
                'pass' => $data->password,

            ]);

            $query = new Query('/system/resource/print');
            $response = $client->query($query)->read();

            if (isset($response[0]['uptime'])) {
                return response()->json(['uptime' => $response[0]['uptime']]);
            } else {
                return response()->json(['error' => 'Uptime tidak ditemukan.']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal terhubung ke MikroTik: '.$e->getMessage()]);
        }
    }

    public function addFirewallRule(Request $request)
    {
        $request->validate([
            'ipaddr' => 'required',
            'port' => 'required',
            'ipmikrotik' => 'required',
        ]);

        $ipAddress = $request->input('ipaddr');
        $port = $request->input('port');
        $ipMikrotik = $request->input('ipmikrotik');

        // Ambil data MikroTik berdasarkan IP
        $data = Mikrotik::where('ipmikrotik', $request->ipmikrotik)->first();

        // Cek apakah data MikroTik ditemukan
        if (! $data) {
            return redirect()->back()->with('error', 'MikroTik data not found.');
        }

        $username = $data->username;
        $password = $data->password;
        $site = $data->site;

        // Ambil data VPN terkait berdasarkan IP MikroTik
        $datavpn = Vpn::where('ipaddress', $data->ipmikrotik)->first();

        // Cek apakah data VPN ditemukan
        if (! $datavpn) {
            return redirect()->back()->with('error', 'VPN data not found.');
        }

        // Set 'portapi' dan 'portweb' dari data VPN
        $portapi = $datavpn->portapi ?? '8728'; // Default '8728' jika 'portapi' tidak ditemukan
        $portweb = $datavpn->portweb ?? '80'; // Default '80' jika 'portweb' tidak ditemukan

        try {
            // Konfigurasi client MikroTik API
            $config = [
                'host' => env('MIKROTIK_CHOST').':'.$portapi,
                'user' => $username,
                'pass' => $password,

            ];

            $client = new Client($config);

            // Periksa apakah ada aturan firewall NAT dengan port tertentu
            $query = (new Query('/ip/firewall/nat/print'))
                ->where('dst-port', $portweb);
            $existingRules = $client->query($query)->read();

            if (! empty($existingRules)) {
                // Update aturan NAT yang sudah ada
                $id = $existingRules[0]['.id'];
                $updateQuery = (new Query('/ip/firewall/nat/set'))
                    ->equal('.id', $id)
                    ->equal('dst-port', $portweb)
                    ->equal('to-addresses', $ipAddress)
                    ->equal('to-ports', $port);

                $client->query($updateQuery)->read();
            } else {
                // Tambahkan aturan NAT baru
                $addQuery = (new Query('/ip/firewall/nat/add'))
                    ->equal('chain', 'dstnat')
                    ->equal('protocol', 'tcp')
                    ->equal('dst-port', $portweb)
                    ->equal('action', 'dst-nat')
                    ->equal('to-addresses', $ipAddress)
                    ->equal('to-ports', $port)
                    ->equal('comment', 'Remote-web-new');

                $client->query($addQuery)->read();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function restartmodem(Request $request)
    {
        // Validate request data
        $request->validate([
            'ipaddr' => 'required|ip',
            'port' => 'required|numeric',
            'ipmikrotik' => 'required|ip',
        ]);

        $ipAddress = $request->input('ipaddr');
        $port = $request->input('port');
        $ipMikrotik = $request->input('ipmikrotik');
        // Ambil data MikroTik berdasarkan IP
        $data = Mikrotik::where('ipmikrotik', $request->ipmikrotik)->first();

        // Cek apakah data MikroTik ditemukan
        if (! $data) {
            return redirect()->back()->with('error', 'MikroTik data not found.');
        }

        $username = $data->username;
        $password = $data->password;
        $site = $data->site;

        // Ambil data VPN terkait berdasarkan IP MikroTik
        $datavpn = Vpn::where('ipaddress', $data->ipmikrotik)->first();

        // Cek apakah data VPN ditemukan
        if (! $datavpn) {
            return redirect()->back()->with('error', 'VPN data not found.');
        }

        // Set 'portapi' dan 'portweb' dari data VPN
        $portapi = $datavpn->portapi ?? '8728'; // Default '8728' jika 'portapi' tidak ditemukan
        $portweb = $datavpn->portweb ?? '80'; // Default '80' jika 'portw
        try {
            // MikroTik API client configuration
            $config = [
                'host' => env('MIKROTIK_CHOST').':'.$portapi,
                'user' => $username,
                'pass' => $password,

            ];

            $client = new Client($config);

            // Get the list of active PPPoE connections
            $query = new Query('/ppp/active/print');
            $query->where('address', $ipAddress);

            $pppActiveConnections = $client->query($query)->read();

            if (count($pppActiveConnections) > 0) {
                $pppId = $pppActiveConnections[0]['.id'];

                // Remove the PPP active connection
                $removeQuery = new Query('/ppp/active/remove');
                $removeQuery->equal('.id', $pppId);

                $result = $client->query($removeQuery)->read();

                if (! isset($result['!trap'])) {
                    return response()->json(['success' => true, 'message' => 'PPPoE connection removed successfully.']);
                } else {
                    return response()->json(['success' => false, 'message' => 'Failed to remove PPPoE connection: '.$result['!trap'][0]['message']]);
                }
            } else {
                return response()->json(['success' => false, 'message' => "PPPoE connection with IP address '$ipAddress' not found."]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: '.$e->getMessage()]);
        }
    }

    public function getTrafficData(Request $request)
    {
        $interfaceName = $request->input('interface');
        $ipmikrotikreq = $request->input('ipmikrotik');

        $data = Mikrotik::where('ipmikrotik', $ipmikrotikreq)->first();
        $datavpn = Vpn::where('ipaddress', $data->ipmikrotik)
            ->where('unique_id', auth()->user()->unique_id)
            ->first();

        if (! $data) {
            return response()->json(['error' => 'Data MikroTik tidak ditemukan.'], 404);
        }

        if (! $datavpn) {
            return response()->json(['error' => 'Data VPN tidak ditemukan.'], 404);
        }

        $portapi = $datavpn->portapi ?? null;

        try {
            $client = new Client([
                'host' => env('MIKROTIK_HOST').':'.$portapi,
                'user' => $data->username,
                'pass' => $data->password,

            ]);

            $queryTraffic = (new Query('/interface/monitor-traffic'))
                ->equal('interface', '<pppoe-'.$interfaceName.'>')
                ->equal('once', true);

            $responseTraffic = $client->query($queryTraffic)->read();

            if (empty($responseTraffic)) {
                return response()->json(['error' => 'Tidak ada data traffic yang tersedia.'], 404);
            }

            $traffic = [
                'rx' => isset($responseTraffic[0]['rx-bits-per-second']) ? $responseTraffic[0]['rx-bits-per-second'] : 0,
                'tx' => isset($responseTraffic[0]['tx-bits-per-second']) ? $responseTraffic[0]['tx-bits-per-second'] : 0,
            ];

            return response()->json(['traffic' => $traffic]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal terhubung ke MikroTik: '.$e->getMessage()], 500);
        }
    }

    public function tambaholt(Request $req)
    {
        $ipolt = $req->input('ipolt');
        $portolt = $req->input('portolt');
        $site = $req->input('site');
        $ipvpn = $req->input('ipvpn');
        // Cari port yang tersedia dalam rentang 44001 - 44999
        $usedPorts = OLT::pluck('portvpn')->toArray();
        $availablePort = null;

        for ($port = 44001; $port <= 44999; $port++) {
            if (! in_array($port, $usedPorts)) {
                $availablePort = $port;
                break;
            }
        }

        // Jika tidak ada port tersedia
        if (! $availablePort) {
            session()->flash('error', 'Semua port antara 44001 dan 44999 telah terpakai.');

            return redirect()->back();
        }

        $portvpn = $availablePort;

        // dd($site, $ipolt ,$ipolt, $ipvpn, $portvpn);

        try {
            // Konfigurasi koneksi ke MikroTik
            $client = new Client([
                'host' => env('MIKROTIK_HOST'),
                'user' => env('MIKROTIK_USER'),
                'pass' => env('MIKROTIK_PASS'),
            ]);

            if (empty($ipolt) || empty($portolt) || empty($site) || empty($ipvpn)) {
                session()->flash('error', 'IP OLT, Port OLT, atau Site tidak boleh kosong.');

                return redirect()->back();
            }

            // Mendapatkan data IP MikroTik berdasarkan site
            $ipmikrotik = Vpn::where('ipaddress', $ipvpn)->first();

            if (! $ipmikrotik) {
                session()->flash('error', "IP VPN $ipvpn tidak ditemukan.");

                return redirect()->back();
            }

            // Tentukan aturan NAT untuk IP OLT
            $natQueryOLT = new Query('/ip/firewall/nat/add');
            $natQueryOLT->equal('chain', 'dstnat')
                ->equal('protocol', 'tcp')
                ->equal('dst-port', $portvpn)
                ->equal('dst-address-list', 'ip-public')
                ->equal('action', 'dst-nat')
                ->equal('to-addresses', $ipmikrotik->ipaddress)
                ->equal('to-ports', $portvpn)
                ->equal('comment', 'AQT_'.$site.'_OLT');

            $natResponseOLT = $client->query($natQueryOLT)->read();

            // Cek jika ada kesalahan dalam response NAT
            if (isset($natResponseOLT['!trap'])) {
                session()->flash('error', $natResponseOLT['!trap'][0]['message']);

                return redirect()->back();
            }

            // Menyimpan data ke database
            OLT::create([
                'ipolt' => $ipolt,
                'portolt' => $portolt, // Simpan dstPort yang baru di database
                'ipvpn' => $ipvpn, // Simpan dstPort yang baru di database
                'portvpn' => $portvpn, // Simpan dstPort yang baru di database

                'site' => $site,
            ]);

            session()->flash('success', 'Konfigurasi OLT Berhasil Ditambahkan !');

            return redirect()->back();
        } catch (ClientException $e) {
            session()->flash('error', 'Gagal terhubung ke MikroTik: '.$e->getMessage());

            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());

            return redirect()->back();
        }
    }

    public function hapusvpn(Request $request, $id)
    {
        $username = $request->input('username');

        if (! $username) {
            return response()->json(['error' => 'Username is required.'], 400);
        }

        $client = new Client([
            'host' => env('MIKROTIK_HOST'),
            'user' => env('MIKROTIK_USER'),
            'pass' => env('MIKROTIK_PASS'),
        ]);

        $vpn = VPN::findOrFail($id);

        if ($vpn->username !== $username) {
            return response()->json(['error' => 'Username does not match.'], 400);
        }

        try {
            // Search for PPP Secret by name
            $query = new Query('/ppp/secret/print');
            $response = $client->query($query)->read();

            $matchedSecret = null;
            foreach ($response as $secret) {
                if (isset($secret['name']) && $secret['name'] === $username) {
                    $matchedSecret = $secret;
                    break;
                }
            }

            if ($matchedSecret) {
                $secretId = $matchedSecret['.id'];

                $removeQuery = new Query('/ppp/secret/remove');
                $removeQuery->equal('.id', $secretId);
                $client->query($removeQuery)->read();
            } else {
                // return response()->json(['error' => 'PPP Secret not found.'], 404);
                $vpn->delete();

                return response()->json(['error' => 'PPP Secret not found. Database Delete!']);

            }

            // ============================
            // HAPUS FIREWALL NAT BERDASARKAN COMMENT
            // ============================
            $natComments = [
                'AQT_'.$username.'_API',
                'AQT_'.$username.'_WEB',
                'AQT_'.$username.'_MikroTik',
            ];

            $queryNat = new Query('/ip/firewall/nat/print');
            $responseNat = $client->query($queryNat)->read();

            foreach ($responseNat as $rule) {
                if (! empty($rule['comment']) && in_array($rule['comment'], $natComments)) {
                    $removeNat = new Query('/ip/firewall/nat/remove');
                    $removeNat->equal('.id', $rule['.id']);
                    $client->query($removeNat)->read();
                }
            }

            // ============================
            // HAPUS PPP SECRET & ACTIVE
            // ============================

            // Ambil semua PPP Secret sekali saja
            $queryPPP = new Query('/ppp/secret/print');
            $responsePPP = $client->query($queryPPP)->read();

            $pppComment = 'AQT_'.$username;

            // Hapus Secret
            foreach ($responsePPP as $secret) {
                $comment = $secret['comment'] ?? '';
                $userPPP = $secret['name'] ?? '';

                if ($comment === $pppComment || $userPPP === $username) {
                    $removePPP = new Query('/ppp/secret/remove');
                    $removePPP->equal('.id', $secret['.id']);
                    $client->query($removePPP)->read();
                }
            }

            // Ambil PPP Active terpisah setelah semua secret dihapus
            $queryActive = new Query('/ppp/active/print');
            $responseActive = $client->query($queryActive)->read();

            // Hapus PPP Active user terkait
            foreach ($responseActive as $active) {
                if (($active['name'] ?? '') === $username) {
                    $removeActive = new Query('/ppp/active/remove');
                    $removeActive->equal('.id', $active['.id']);
                    $client->query($removeActive)->read();
                }
            }

            // Log::info();
            // Delete the VPN record from the database
            $vpn->delete();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete PPP Secret or Firewall NAT rules: '.$e->getMessage()], 500);
        }

        return response()->json(['success' => 'Data berhasil dihapus']);
    }

    public function hapusolt($id)
    {
        try {
            // Konfigurasi koneksi ke MikroTik
            $client = new Client([
                'host' => env('MIKROTIK_HOST'),
                'user' => env('MIKROTIK_USER'),
                'pass' => env('MIKROTIK_PASS'),
            ]);

            // Cari data OLT di database berdasarkan ID
            $vpnData = OLT::find($id);

            if (! $vpnData) {
                session()->flash('error', 'Data tidak ditemukan di database.');

                return redirect()->back();
            }

            // Cari aturan NAT di MikroTik berdasarkan 'to-addresses' yang sesuai dengan ipolt
            $findNatQuery = new Query('/ip/firewall/nat/print');
            $findNatQuery->where('dst-port', $vpnData->portvpn);

            $natRules = $client->query($findNatQuery)->read();

            // Hapus aturan NAT jika ditemukan
            if (! empty($natRules)) {
                foreach ($natRules as $rule) {
                    if (isset($rule['.id'])) {
                        $deleteNatQuery = new Query('/ip/firewall/nat/remove');
                        $deleteNatQuery->equal('.id', $rule['.id']);
                        $client->query($deleteNatQuery)->read();
                    }
                }
                session()->flash('success', 'Aturan NAT di MikroTik berhasil dihapus.');
            } else {
                session()->flash('warning', 'Aturan NAT tidak ditemukan di MikroTik.');
            }

            // Hapus data dari database
            $vpnData->delete();

            session()->flash('success', 'Data berhasil dihapus dari database.');

            return redirect()->back();
        } catch (ClientException $e) {
            session()->flash('error', 'Gagal terhubung ke MikroTik: '.$e->getMessage());

            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());

            return redirect()->back();
        }
    }

    public function updateolt(Request $request)
    {

        // Validasi data
        $request->validate([
            'id' => 'required|exists:olt,id', // Pastikan ID ada di tabel olts
            'site' => 'required|string|max:255',
            'ipolt' => 'required|ip', // Validasi IP address
            'portolt' => 'required|numeric',
            'ipvpn' => 'required|ip', // Validasi IP VPN
        ]);

        try {
            // Cari OLT berdasarkan ID
            $olt = OLT::findOrFail($request->id);

            // Update data OLT
            $olt->site = $request->site;
            $olt->ipolt = $request->ipolt;
            $olt->portolt = $request->portolt;
            $olt->ipvpn = $request->ipvpn;
            $olt->save();

            // Redirect dengan pesan sukses
            return redirect()->back()->with('success', 'Data OLT berhasil diperbarui.');
        } catch (\Exception $e) {
            // Redirect dengan pesan error
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data OLT: '.$e->getMessage());
        }
    }

    public function statusPage(Request $request)
    {
        $data = Vpn::get();
        $mikrotik = Mikrotik::get();
        $olt = OLT::get();

        $ip = $request->query('ipmikrotik');
        $interfaceName = $request->query('username');

        $dataMikrotik = Mikrotik::where('ipmikrotik', $ip)->first();
        $datavpn = Vpn::where('ipaddress', $ip)->first();

        $uptime = null;
        $ipAddress = null;
        $macAddress = null;

        if ($dataMikrotik && $datavpn && $interfaceName) {
            try {
                $client = new Client([
                    'host' => env('MIKROTIK_CHOST').':'.$datavpn->portapi,
                    'user' => $dataMikrotik->username,
                    'pass' => $dataMikrotik->password,
                ]);

                // Ambil data dari /ppp/active/print
                $pppQuery = (new Query('/ppp/active/print'))->where('name', $interfaceName);
                $pppResponse = $client->query($pppQuery)->read();

                if (! empty($pppResponse)) {
                    $active = $pppResponse[0];

                    $uptime = $active['uptime'] ?? null;
                    $ipAddress = $active['address'] ?? null;
                    $macAddress = $active['caller-id'] ?? null; // ini adalah MAC address
                }

            } catch (\Exception $e) {
                \Log::error('Gagal mengambil data PPPoE active: '.$e->getMessage());
            }
        }

        return view('Dashboard.depan.mikrotik.traffic', compact(
            'ip', 'interfaceName', 'uptime', 'ipAddress', 'macAddress', 'data', 'mikrotik', 'olt', 'dataMikrotik'
        ));
    }

    public function getTrafficFromIp(Request $request)
    {
        $ip = $request->input('ipmikrotik');
        $username = $request->input('username');

        \Log::info("Minta traffic untuk IP: $ip, Username: $username");

        $dataMikrotik = Mikrotik::where('ipmikrotik', $ip)->first();
        $datavpn = Vpn::where('ipaddress', $ip)->first();

        if (! $dataMikrotik || ! $datavpn) {
            \Log::error("Data Mikrotik atau VPN tidak ditemukan untuk IP: $ip");

            return response()->json(['error' => 'Data Mikrotik atau VPN tidak ditemukan'], 404);
        }

        try {
            $client = new Client([
                'host' => env('MIKROTIK_CHOST').':'.$datavpn->portapi,
                'user' => $dataMikrotik->username,
                'pass' => $dataMikrotik->password,
            ]);

            $response = $client->query(
                (new Query('/interface/monitor-traffic'))
                    ->equal('interface', '<pppoe-'.$username.'>')
                    ->equal('once')
            )->read();

            if (empty($response) || ! isset($response[0]['tx-bits-per-second'], $response[0]['rx-bits-per-second'])) {
                return response()->json(['error' => 'Tidak dapat mengambil data traffic'], 500);
            }

            return response()->json([
                'tx' => (int) ($response[0]['tx-bits-per-second'] ?? 0),
                'rx' => (int) ($response[0]['rx-bits-per-second'] ?? 0),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: '.$e->getMessage()], 500);
        }
    }
}
