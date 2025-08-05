<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\OLT;
use App\Models\Vpn;
use RouterOS\Query;
use App\Models\Akun;
use RouterOS\Client;
use App\Models\Mikrotik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;


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
        $portmkt = $req->input('portmk');
        $akuncomment = "AQT_" . $namaakun;

        try {
            $client = new Client([
                'host' => env('MIKROTIK_HOST'),
                'user' => env('MIKROTIK_USER'),
                'pass' => env('MIKROTIK_PASS'),
            ]);
            // dd($client);
            $queryAllSecrets = new Query('/ppp/secret/print');
            $response = $client->query($queryAllSecrets)->read();

            $existingUsernames = array_column($response, 'name');
            if (in_array($username, $existingUsernames)) {
                session()->flash('error', 'Username sudah ada, silakan gunakan username lain.');
                return redirect()->back();
            }

            // Generate IP address
            $firstOctet = '172';
            $secondOctet = 16;
            $usedThirdOctets = array_map(function ($secret) {
                return isset($secret['local-address']) ? explode('.', $secret['local-address'])[2] : null;
            }, $response);
            $usedThirdOctets = array_filter($usedThirdOctets);
            $thirdOctet = 11;
            while (in_array($thirdOctet, $usedThirdOctets)) {
                $thirdOctet++;
                if ($thirdOctet > 254)
                    throw new \Exception("Tidak ada third octet yang tersedia.");
            }

            $existingCount = count($response);
            $fourthOctetLocal = 1;
            $fourthOctetRemote = 10 + ($existingCount % 255);
            $localIp = "$firstOctet.$secondOctet.$thirdOctet.$fourthOctetLocal";
            $remoteIp = "$firstOctet.$secondOctet.$thirdOctet.$fourthOctetRemote";

            $query = new Query('/ppp/secret/add');
            $query->equal('name', $username)
                ->equal('password', $password)
                ->equal('comment', $akuncomment)
                ->equal('profile', 'default')
                ->equal('local-address', $localIp)
                ->equal('remote-address', $remoteIp);
            $response = $client->query($query)->read();

            if (isset($response['!trap'])) {
                session()->flash('error', $response['!trap'][0]['message']);
                return redirect()->back();
            }

            // Port Rules
            $queryAllNAT = new Query('/ip/firewall/nat/print');
            $natResponse = $client->query($queryAllNAT)->read();
            $usedPorts = [];
            foreach ($natResponse as $natRule) {
                if (isset($natRule['dst-port'])) {
                    $usedPorts[] = (int) $natRule['dst-port'];
                }
            }

            // Mikrotik port manual (jika ada)
            if ($portmkt != null) {
                if ($portmkt < 43000 || $portmkt > 43999 || in_array($portmkt, $usedPorts)) {
                    session()->flash('error', 'Port Mikrotik manual tidak valid atau sudah dipakai.');
                    return redirect()->back();
                }
                $portmikrotik = $portmkt;
            } else {
                $portmikrotik = null;
                for ($i = 43000; $i <= 43999; $i++) {
                    if (!in_array($i, $usedPorts)) {
                        $portmikrotik = $i;
                        break;
                    }
                }
                if (!$portmikrotik)
                    throw new \Exception("Tidak ada port MikroTik tersedia di range 43000-43999.");
            }

            // API Port
            $portapi = null;
            for ($i = 40000; $i <= 41999; $i++) {
                if (!in_array($i, $usedPorts)) {
                    $portapi = $i;
                    break;
                }
            }
            if (!$portapi)
                throw new \Exception("Tidak ada port API tersedia di range 40000-41999.");

            // WEB Port
            $portweb = null;
            for ($i = 42000; $i <= 42999; $i++) {
                if (!in_array($i, $usedPorts)) {
                    $portweb = $i;
                    break;
                }
            }
            if (!$portweb)
                throw new \Exception("Tidak ada port Web tersedia di range 42000-42999.");

            // NAT Rules
            $natQuery1 = new Query('/ip/firewall/nat/add');
            $natQuery1->equal('chain', 'dstnat')
                ->equal('protocol', 'tcp')
                ->equal('dst-port', $portapi)
                ->equal('dst-address-list', 'ip-public')
                ->equal('action', 'dst-nat')
                ->equal('to-addresses', $remoteIp)
                ->equal('to-ports', 9000)
                ->equal('comment', $akuncomment . '_API');
            $natResponse1 = $client->query($natQuery1)->read();
            if (isset($natResponse1['!trap'])) {
                session()->flash('error', $natResponse1['!trap'][0]['message']);
                return redirect()->back();
            }

            $natQuery2 = new Query('/ip/firewall/nat/add');
            $natQuery2->equal('chain', 'dstnat')
                ->equal('protocol', 'tcp')
                ->equal('dst-port', $portweb)
                ->equal('dst-address-list', 'ip-public')
                ->equal('action', 'dst-nat')
                ->equal('to-addresses', $remoteIp)
                ->equal('to-ports', $portweb)
                ->equal('comment', $akuncomment . '_WEB');
            $natResponse2 = $client->query($natQuery2)->read();
            if (isset($natResponse2['!trap'])) {
                session()->flash('error', $natResponse2['!trap'][0]['message']);
                return redirect()->back();
            }

            $natQuery3 = new Query('/ip/firewall/nat/add');
            $natQuery3->equal('chain', 'dstnat')
                ->equal('protocol', 'tcp')
                ->equal('dst-port', $portmikrotik)
                ->equal('dst-address-list', 'ip-public')
                ->equal('action', 'dst-nat')
                ->equal('to-addresses', $remoteIp)
                ->equal('to-ports', $portmkt ?? 8291)
                ->equal('comment', $akuncomment . '_MikroTik');
            $natResponse3 = $client->query($natQuery3)->read();
            if (isset($natResponse3['!trap'])) {
                session()->flash('error', $natResponse3['!trap'][0]['message']);
                return redirect()->back();
            }

            // Simpan ke database
            Vpn::create([
                'namaakun' => $namaakun,
                'username' => $username,
                'password' => $password,
                'ipaddress' => $remoteIp,
                'portapi' => $portapi,
                'portweb' => $portweb,
                'portmikrotik' => $portmikrotik,
                'portwbx' => $portmkt ?? 8291,
            ]);

            session()->flash('success', "Akun VPN Berhasil Dibuat!");
            return redirect()->back();

        } catch (ClientException $e) {
            session()->flash('error', "Gagal terhubung ke MikroTik: " . $e->getMessage());
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('error', "Terjadi kesalahan: " . $e->getMessage());
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

            session()->flash('success', "Mikrotik " . $site . " Berhasil Di Tambahkan");
            return redirect()->back();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save data: ' . $e->getMessage(),
            ]);
        }
    }
    public function editmikrotik($id)
    {
        $mikrotik = Mikrotik::find($id);
        if (!$mikrotik) {
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
                    'host' => env('MIKROTIK_CHOST') . ":" . $dataport->portapi,
                    'user' => $username,
                    'pass' => $password,

                ]);

                // If connection is successful
                session()->flash('success', 'Mikrotik Terhubung');
                return redirect()->back();
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to connect to MikroTik router : ' . $e->getMessage());
                return redirect()->back();
            }
        } else {
            // Case where database entry exists for the IP
            if (is_null($dataport->portapi) == false) {
                try {
                    // Connect with port information from the database
                    $connection = new Client([
                        'host' => env('MIKROTIK_CHOST') . ":" . $dataport->portapi,
                        'user' => $username,
                        'pass' => $password,

                    ]);

                    // If connection is successful
                    session()->flash('success', 'Mikrotik Terhubung');
                    return redirect()->back();
                } catch (\Exception $e) {
                    session()->flash('error', 'Failed to connect to MikroTik router :  ' . $e->getMessage());
                    return redirect()->back();
                }
            } else {
                try {
                    // Connect using the IP without port information
                    $connection = new Client([
                        'host' => env('MIKROTIK_CHOST') . ":" . $dataport->portapi,
                        'user' => $username,
                        'pass' => $password,

                    ]);

                    // If connection is successful
                    session()->flash('success', 'Mikrotik Terhubung tanpa port dari database');
                    return redirect()->back();
                } catch (\Exception $e) {
                    session()->flash('error', 'Failed to connect to MikroTik router: ' . $e->getMessage());
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
        if (!$data) {
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
                'host' => env('MIKROTIK_CHOST') . ":" . $portapi, // Menggunakan domain VPN dan port API dari data VPN
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
                'portapi' => $portapi
            ]);

            // Hapus session 'session_disconnected' jika ada
            session()->forget('session_disconnected');

            // Arahkan ke halaman dashboardmikrotik setelah berhasil terkoneksi
            return redirect()->route('dashboardmikrotik', ['ipmikrotik' => $ipmikrotik]);
        } catch (\Exception $e) {
            // Jika terjadi error saat koneksi, hapus session dan tampilkan pesan error
            session()->forget('mikrotik_connected');
            session(['session_disconnected' => true]);

            return redirect()->back()->with('error', 'Error connecting to MikroTik: ' . $e->getMessage());
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
        $config = [
            'host' => env('MIKROTIK_CHOST') . ":" . $portapi, // Menggunakan domain VPN dan port API dari data VPN
            'user' => $data->username,
            'pass' => $data->password,

        ];



        $client = new Client($config);

        // Query untuk mendapatkan data secret di PPP
        $query = (new Query('/ppp/secret/print'));
        $response = $client->query($query)->read();

        $totaluser = count($response);

        $query2 = (new Query('/ppp/active/print'));
        $response2 = $client->query($query2)->read();

        $totalactive = count($response2);

        // Koneksi ke MikroTik menggunakan konfigurasi yang telah dibuat
        $query3 = (new Query('/ppp/active/print'));
        $response3 = $client->query($query3)->read();

        //dd($response);







        $query4 = new Query('/ppp/active/print');
        $response4 = $client->query($query4)->read();

        function uptimeToSeconds($uptime)
        {
            if (!$uptime)
                return PHP_INT_MAX; // Jika kosong atau null, anggap uptime sangat besar

            // Format uptime bisa seperti "1d2h3m4s", "2h3m4s", "3m4s", "4s", "00:05:23", dsb
            if (strpos($uptime, ':') !== false) {
                // Format jam:menit:detik
                $parts = explode(':', $uptime);
                $count = count($parts);

                // Tambahkan validasi agar hanya angka
                $parts = array_map('intval', $parts);

                if ($count === 3) {
                    return ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
                } elseif ($count === 2) {
                    return ($parts[0] * 60) + $parts[1];
                } elseif ($count === 1) {
                    return $parts[0];
                }
            } else {
                // Format lama seperti "1d2h3m4s"
                preg_match_all('/(\d+)([dhms])/', $uptime, $matches, PREG_SET_ORDER);
                $totalSeconds = 0;
                foreach ($matches as $match) {
                    $value = (int) $match[1];
                    $unit = $match[2];
                    switch ($unit) {
                        case 'd':
                            $totalSeconds += $value * 86400;
                            break;
                        case 'h':
                            $totalSeconds += $value * 3600;
                            break;
                        case 'm':
                            $totalSeconds += $value * 60;
                            break;
                        case 's':
                            $totalSeconds += $value;
                            break;
                    }
                }
                return $totalSeconds;
            }

            return PHP_INT_MAX; // fallback jika gagal parsing
        }

        foreach ($response4 as &$item) {
            $item['uptime_sort'] = uptimeToSeconds($item['uptime']);
        }
        unset($item); // best practice setelah foreach reference

        // optional: jika kamu mau urutkan berdasarkan uptime
        usort($response4, function ($a, $b) {
            return $a['uptime_sort'] <=> $b['uptime_sort'];
        });

























        $query = (new Query('/system/resource/print'));

        // Jalankan query dan baca respons
        $response = $client->query($query)->read();

        $version = $response[0]['version'] ?? 'Unknown version';
        $model = $response[0]['board-name'] ?? 'Unknown model';

        $queryDateTime = (new Query('/system/clock/print'));
        $responseDateTime = $client->query($queryDateTime)->read();

        // Query untuk mengambil daftar interface Ethernet dari MikroTik
        $queryInterfaces = (new Query('/interface/print'));
        $responseInterfaces = $client->query($queryInterfaces)->read();

        $interfaces = [];
        $physicalInterfaces = ['ether', 'sfp', 'wifi', 'bonding']; // Common physical interface types

        foreach ($responseInterfaces as $interface) {
            if (isset($interface['name']) && isset($interface['type'])) {
                // Check if the interface type indicates a physical interface
                if (in_array($interface['type'], $physicalInterfaces)) {
                    $interfaces[] = $interface['name'];
                }
            }
        }

        $queryHotspotUsers = (new Query('/ip/hotspot/user/print'));
        $responseHotspotUsers = $client->query($queryHotspotUsers)->read();

        // Initialize an array to store the hotspot users
        $hotspotUsers = [];

        // Iterate over the response to extract the user data
        foreach ($responseHotspotUsers as $user) {
            if (isset($user['name'])) {
                $hotspotUsers[] = $user;
            }
        }


        $ttuser = count($hotspotUsers);




        $queryActiveHotspotUsers = (new Query('/ip/hotspot/active/print'));

        // Execute the query
        $responseActiveHotspotUsers = $client->query($queryActiveHotspotUsers)->read();

        // Initialize an array to store active hotspot users
        $activeHotspotUsers = [];

        // Iterate over the response to extract user data
        foreach ($responseActiveHotspotUsers as $user) {
            if (isset($user['name'])) {
                $activeHotspotUsers[] = $user;
            }
        }

        // Count the number of active users
        $activeUserCount = count($activeHotspotUsers);


        $akunDb = Akun::where('site', $data->site)->get();
        //dd($akunDb);
        // 3. Ambil data PPP Active dari MikroTik
        $query5 = (new Query('/ppp/active/print'));
        $pppActive = $client->query($query5)->read();

        $activeUsernames = collect($pppActive)->pluck('name')->toArray();

        // 4. Load JSON dari public path
        $filePath = public_path('ppp_down_users.json');

        // Pastikan file ada dan valid
        if (!file_exists($filePath)) {
            file_put_contents($filePath, json_encode([]));
        }

        $json = file_get_contents($filePath);
        $decoded = json_decode($json, true);
        $downLog = is_array($decoded) ? $decoded : [];


        $currentDownUsers = [];


        if (!$akunDb->isEmpty()) {
            foreach ($akunDb as $akun) {
                $username = $akun->namaakun;

                if (!in_array($username, $activeUsernames)) {
                    if (!isset($downLog[$username])) {
                        $downLog[$username] = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
                    }

                    $currentDownUsers[] = [
                        'namaakun' => $akun->namaakun,
                        'detected_at' => $downLog[$username],
                    ];
                } else {
                    unset($downLog[$username]);
                }
            }
        }



        // 5. Simpan file JSON di public
        file_put_contents($filePath, json_encode($downLog, JSON_PRETTY_PRINT));













        Log::info($activeUserCount);



        if (!empty($responseDateTime)) {
            // Ambil date
            $date = isset($responseDateTime[0]['date']) ? $responseDateTime[0]['date'] : 'N/A';


            if (!$data) {
                return redirect()->back()->with('error', 'MikroTik data not found.');
            }


            // Ambil informasi lain yang dibutuhkan untuk ditampilkan di dashboard
            $site = $data->site;
            $username = $data->username;

            // Tampilkan dashboard dengan data yang relevan
            return view('Dashboard.depan.mikrotik.dashboardmikrotik', compact('ipmikrotik', 'site', 'username', 'date', 'interfaces', 'version', 'model', 'ttuser', 'activeUserCount', 'mikrotik', 'response3', 'portweb', 'portapi', 'response4', 'currentDownUsers', 'olt'));
        } else {
            return back()->with('error', 'Data tidak ditemukan.');
        }
    }

    public function sync($ipmikrotik)
    {
        $mikrotik = Mikrotik::where('ipmikrotik', $ipmikrotik)->first();
        $vpn = Vpn::where('ipaddress', $mikrotik->ipmikrotik)->first();

        if (!$mikrotik) {
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

            if (!$sudahAda) {
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
        if (!$data) {
            return response()->json(['error' => 'Data MikroTik tidak ditemukan.']);
        }

        try {
            $client = new Client([
                'host' => env('MIKROTIK_CHOST') . ":" . $datavpn->portapi, // Menggunakan domain VPN dan port API dari data VPN

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
            return response()->json(['error' => 'Gagal terhubung ke MikroTik: ' . $e->getMessage()]);
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
        if (!$data) {
            return redirect()->back()->with('error', 'MikroTik data not found.');
        }

        $username = $data->username;
        $password = $data->password;
        $site = $data->site;

        // Ambil data VPN terkait berdasarkan IP MikroTik
        $datavpn = Vpn::where('ipaddress', $data->ipmikrotik)->first();

        // Cek apakah data VPN ditemukan
        if (!$datavpn) {
            return redirect()->back()->with('error', 'VPN data not found.');
        }

        // Set 'portapi' dan 'portweb' dari data VPN
        $portapi = $datavpn->portapi ?? '8728'; // Default '8728' jika 'portapi' tidak ditemukan
        $portweb = $datavpn->portweb ?? '80'; // Default '80' jika 'portweb' tidak ditemukan

        try {
            // Konfigurasi client MikroTik API
            $config = [
                'host' => env('MIKROTIK_CHOST') . ":" . $portapi,
                'user' => $username,
                'pass' => $password,

            ];

            $client = new Client($config);

            // Periksa apakah ada aturan firewall NAT dengan port tertentu
            $query = (new Query('/ip/firewall/nat/print'))
                ->where('dst-port', $portweb);
            $existingRules = $client->query($query)->read();

            if (!empty($existingRules)) {
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
        if (!$data) {
            return redirect()->back()->with('error', 'MikroTik data not found.');
        }

        $username = $data->username;
        $password = $data->password;
        $site = $data->site;

        // Ambil data VPN terkait berdasarkan IP MikroTik
        $datavpn = Vpn::where('ipaddress', $data->ipmikrotik)->first();

        // Cek apakah data VPN ditemukan
        if (!$datavpn) {
            return redirect()->back()->with('error', 'VPN data not found.');
        }

        // Set 'portapi' dan 'portweb' dari data VPN
        $portapi = $datavpn->portapi ?? '8728'; // Default '8728' jika 'portapi' tidak ditemukan
        $portweb = $datavpn->portweb ?? '80'; // Default '80' jika 'portw
        try {
            // MikroTik API client configuration
            $config = [
                'host' => env('MIKROTIK_CHOST') . ":" . $portapi,
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

                if (!isset($result['!trap'])) {
                    return response()->json(['success' => true, 'message' => 'PPPoE connection removed successfully.']);
                } else {
                    return response()->json(['success' => false, 'message' => 'Failed to remove PPPoE connection: ' . $result['!trap'][0]['message']]);
                }
            } else {
                return response()->json(['success' => false, 'message' => "PPPoE connection with IP address '$ipAddress' not found."]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
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

        if (!$data) {
            return response()->json(['error' => 'Data MikroTik tidak ditemukan.'], 404);
        }

        if (!$datavpn) {
            return response()->json(['error' => 'Data VPN tidak ditemukan.'], 404);
        }

        $portapi = $datavpn->portapi ?? null;

        try {
            $client = new Client([
                'host' => env('MIKROTIK_HOST') . ":" . $portapi,
                'user' => $data->username,
                'pass' => $data->password,

            ]);

            $queryTraffic = (new Query('/interface/monitor-traffic'))
                ->equal('interface', "<pppoe-" . $interfaceName . ">")
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
            return response()->json(['error' => 'Gagal terhubung ke MikroTik: ' . $e->getMessage()], 500);
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
            if (!in_array($port, $usedPorts)) {
                $availablePort = $port;
                break;
            }
        }

        // Jika tidak ada port tersedia
        if (!$availablePort) {
            session()->flash('error', "Semua port antara 44001 dan 44999 telah terpakai.");
            return redirect()->back();
        }

        $portvpn = $availablePort;


        //dd($site, $ipolt ,$ipolt, $ipvpn, $portvpn);

        try {
            // Konfigurasi koneksi ke MikroTik
            $client = new Client([
                'host' => env('MIKROTIK_HOST'),
                'user' => env('MIKROTIK_USER'),
                'pass' => env('MIKROTIK_PASS'),
            ]);

            if (empty($ipolt) || empty($portolt) || empty($site) || empty($ipvpn)) {
                session()->flash('error', "IP OLT, Port OLT, atau Site tidak boleh kosong.");
                return redirect()->back();
            }

            // Mendapatkan data IP MikroTik berdasarkan site
            $ipmikrotik = Vpn::where('ipaddress', $ipvpn)->first();

            if (!$ipmikrotik) {
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
                ->equal('comment', 'AQT_' . $site . '_OLT');

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

            session()->flash('success', "Konfigurasi OLT Berhasil Ditambahkan !");
            return redirect()->back();
        } catch (ClientException $e) {
            session()->flash('error', "Gagal terhubung ke MikroTik: " . $e->getMessage());
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('error', "Terjadi kesalahan: " . $e->getMessage());
            return redirect()->back();
        }
    }
    public function hapusvpn(Request $request, $id)
    {
        $username = $request->input('username');

        if (!$username) {
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

            // Search for and remove Firewall NAT rules by name
            $natComments = ['AQT_' . $username . '_API', 'AQT_' . $username . '_WEB', 'AQT_' . $username . '_MikroTik'];
            foreach ($natComments as $comment) {
                $query = new Query('/ip/firewall/nat/print');
                $response = $client->query($query)->read();

                foreach ($response as $rule) {
                    if (isset($rule['comment']) && $rule['comment'] && $rule['comment'] === $comment) {
                        $ruleId = $rule['.id'];

                        $removeQuery = new Query('/ip/firewall/nat/remove');
                        $removeQuery->equal('.id', $ruleId);
                        $client->query($removeQuery)->read();
                    }
                }
            }
            //Log::info();
            // Delete the VPN record from the database
            $vpn->delete();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete PPP Secret or Firewall NAT rules: ' . $e->getMessage()], 500);
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

            if (!$vpnData) {
                session()->flash('error', "Data tidak ditemukan di database.");
                return redirect()->back();
            }

            // Cari aturan NAT di MikroTik berdasarkan 'to-addresses' yang sesuai dengan ipolt
            $findNatQuery = new Query('/ip/firewall/nat/print');
            $findNatQuery->where('dst-port', $vpnData->portvpn);

            $natRules = $client->query($findNatQuery)->read();

            // Hapus aturan NAT jika ditemukan
            if (!empty($natRules)) {
                foreach ($natRules as $rule) {
                    if (isset($rule['.id'])) {
                        $deleteNatQuery = new Query('/ip/firewall/nat/remove');
                        $deleteNatQuery->equal('.id', $rule['.id']);
                        $client->query($deleteNatQuery)->read();
                    }
                }
                session()->flash('success', "Aturan NAT di MikroTik berhasil dihapus.");
            } else {
                session()->flash('warning', "Aturan NAT tidak ditemukan di MikroTik.");
            }

            // Hapus data dari database
            $vpnData->delete();

            session()->flash('success', "Data berhasil dihapus dari database.");
            return redirect()->back();
        } catch (ClientException $e) {
            session()->flash('error', "Gagal terhubung ke MikroTik: " . $e->getMessage());
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('error', "Terjadi kesalahan: " . $e->getMessage());
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
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data OLT: ' . $e->getMessage());
        }
    }
}
