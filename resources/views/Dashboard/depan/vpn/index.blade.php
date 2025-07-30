<!DOCTYPE html>
<html lang="en">


<x-head />


<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        <x-preload />
        <!-- Navbar -->
        <x-nav />
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <x-sidebar :mikrotik="$mikrotik"/>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <x-cheader />
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Info boxes -->
                    <!-- /.row -->

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Virtual Private Network ( VPN )</h5>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <button type="button" class="btn btn-primary mr-2" data-toggle="modal"
                                        data-target="#addVpnModal">
                                        <i class="fas fa-plus"></i> Tambah VPN
                                    </button>

                                    <!-- Button to Trigger Info Modal -->
                                    <button type="button" class="btn btn-primary" data-toggle="modal"
                                        data-target="#info">
                                        <i class="fas fa-info"></i> Informasi dan Cara Penggunaan
                                    </button>
                                    <table id="vpnTable"
                                        class="table  mt-3 table-striped table-bordered display nowrap">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Name</th>
                                                <th>Username</th>
                                                <th>Password</th>
                                                <th>IP Address</th>
                                                <th>PORT Winbox</th>
                                                <th>VPN MikroTik</th>
                                                <th>Skrip Mikrotik</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $no = 1; @endphp
                                            @foreach ($data as $item)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $item->namaakun }}</td>
                                                    <td>{{ $item->username }}</td>
                                                    <td>{{ $item->password }}</td>
                                                    <td>{{ $item->ipaddress }}</td>
                                                    <td>{{ $item->portwbx }}</td>
                                                    <!-- <td>{{ 'akses.aqtnetwork.my.id:' . $item->portmikrotik }}</td> -->
                                                    <td>
                                                        <!-- Address MikroTik -->
                                                        <span
                                                            id="mikrotikAddress{{ $item->id }}">akses.aqtnetwork.my.id:{{ $item->portmikrotik }}</span>
                                                        <!-- Tombol Copy -->
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-primary"
                                                            data-toggle="modal" data-target="#vpnInfoModal">
                                                            <i class="fas fa-info"></i> Info
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-delete"
                                                            data-id="{{ $item->id }}"
                                                            data-username="{{ $item->username }}">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                        <!-- /.col -->

                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">MikroTik</h5>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <button type="button" class="btn btn-primary mr-2" data-toggle="modal"
                                        data-target="#addMikrotikModal">
                                        <i class="fas fa-plus"></i> Tambah MikroTik
                                    </button>
                                     <div class="table-responsive">
                                    <table id="mikrotikTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>IP Mikrotik</th>
                                                <th>Site</th>
                                                <th>Username</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $no = 1; @endphp
                                            @foreach($mikrotik as $item)
                                            <tr>
                                                <td>{{ $no++ }}</td>
                                                <td>{{ $item->ipmikrotik }}</td>
                                                <td>{{ $item->site }}</td>
                                                <td>{{ $item->username }}</td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                                            Action
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="{{ route('vpn.aksesmikrotik', [
                                                                'ipmikrotik' => $item->ipmikrotik,
                                                                'username' => $item->username,
                                                                'password' => $item->password
                                                            ]) }}"><i class="fas fa-bolt"></i> Cek Akses</a>
                                                            {{-- <a class="dropdown-item" href="{{ route('masukmikrotik', [
                                                                'ipmikrotik' => $item->ipmikrotik,
                                                                'portweb' => $item->portweb
                                                            ]) }}"><i class="fas fa-sign-in-alt"></i> Masuk</a> --}}
                                                            <a class="dropdown-item editMikrotik" href="javascript:void(0)" data-id="{{ $item->id }}"><i class="fas fa-edit"></i> Edit</a>
                                                            <a class="dropdown-item deleteMikrotik" href="javascript:void(0)" data-id="{{ $item->id }}"><i class="fas fa-trash"></i> Hapus</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </section>

        </div>

        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->

        <!-- Main Footer -->
        <x-footer />
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->
    <!-- jQuery -->
    <x-script />


    <!-- Modal for Add VPN -->
    <div class="modal fade" id="addVpnModal" tabindex="-1" role="dialog" aria-labelledby="addVpnModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addVpnModalLabel">Tambah VPN</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="vpnForm" action="{{ route('vpn.upload') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="namaAkun">Nama Akun</label>
                            <input type="text" class="form-control" placeholder="Nama Akun" name="namaakun"
                                id="namaAkunInput">
                        </div>

                        <div class="form-group">
                            <label for="username">User</label>
                            <input type="text" class="form-control" placeholder="Username" name="username">
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" placeholder="Password" name="password">
                        </div>

                        <div class="form-group">
                            <label for="password">Port Winbox ( OPTIONAL )</label>
                            <input type="number" class="form-control" placeholder="Default : 8291" name="portmk">
                        </div>

                        <div class="form-group">
                            <input type="submit" class="btn btn-success" value="Buat VPN">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for VPN Info -->
    <div class="modal fade" id="info" tabindex="-1" role="dialog" aria-labelledby="vpnInfoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vpnInfoModalLabel">VPN Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <p style="font-size: 20px;">VPN digunakan untuk menghubungkan Router MikroTik anda dengan Router
                        kami melalui jaringan internet/public.
                        Radius server kami tidak dapat meneruskan paket request dari router anda jika router anda tidak
                        mempunyai IP Public atau tidak dalam satu jaringan. Setelah router MikroTik anda terhubung
                        dengan router kami, otomatis radius server akan merespond paket request anda melalui IP Private
                        dari VPN.
                    </p>
                    <hr>
                    <p class="mb-0" style="font-size: 20px;">Jika Router MikroTik anda tidak mempunyai IP Public,
                        silahkan buat account vpn pada form yang sudah di siapkan. Gratis tanpa ada biaya tambahan dan
                        boleh lebih dari satu.</p>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="vpnInfoModal" tabindex="-1" role="dialog" aria-labelledby="vpnInfoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vpnInfoModalLabel">VPN Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- VPN Usage Tips -->
                    <ol class="list-unstyled mb-4">
                        <li>
                            <h5><i class="fa fa-info-circle"></i> Tips - Cara penggunaan</h5>
                            <ol>
                                <li>Pilih salah satu mode yang akan digunakan.</li>
                                <li>Salin / Copy seluruh isi script pada kolom mode yang dipilih.</li>
                                <li>Login mikrotik melalui Winbox, buka menu <strong>New Terminal</strong> kemudian
                                    Tempel / Paste script yang sudah di salin / copy sebelumnya, lanjut tekan tombol
                                    Enter di keyboard.</li>
                                <li>Buka menu <strong>PPP > Interface</strong> jika langkah di atas sudah berhasil, maka
                                    akan tampil interface VPN baru sesuai mode yang dipilih.</li>
                                <li>Lihat status interface VPN, jika belum terhubung / Connected silahkan coba
                                    menggunakan mode yang lain. Jika sudah terhubung / connected (cirinya ada icon huruf
                                    <b>R</b> di samping interface VPN).</li>
                                <li>Gagal terhubung / Connected biasanya karna mode yang anda pilih di blok oleh ISP
                                    anda.</li>
                            </ol>
                        </li>
                    </ol>

                    <!-- VPN Script Section -->
                    <div class="form-group">
                        <label for="scriptL2tp">Mode L2TP</label>
                        <div class="copy-script p-1" data-id="scriptL2tp">
                            <button type="button" class="btn btn-sm btn-secondary">Copy</button>
                        </div>
                        <textarea class="form-control pt-3" rows="5" readonly id="scriptL2tp"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="scriptPptp">Mode PPTP</label>
                        <div class="copy-script p-1" data-id="scriptPptp">
                            <button type="button" class="btn btn-sm btn-secondary">Copy</button>
                        </div>
                        <textarea class="form-control pt-3" rows="5" readonly id="scriptPptp"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>






                <!-- Edit MikroTik Modal -->
                <div class="modal fade" id="editMikrotikModal" tabindex="-1" role="dialog" aria-labelledby="editMikrotikModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editMikrotikModalLabel">Edit MikroTik</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="editMikrotikForm" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="edit_ipmikrotik">IP VPN / IP Public</label>
                                        <input type="text" class="form-control" id="edit_ipmikrotik" name="ipmikrotik" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_site">Site / Nama Mikrotik</label>
                                        <input type="text" class="form-control" id="edit_site" name="site" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_username">Username</label>
                                        <input type="text" class="form-control" id="edit_username" name="username" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_password">Password</label>
                                        <input type="password" class="form-control" id="edit_password" name="password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Update Mikrotik</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
        <!-- Modal Tambah Mikrotik -->
        <div class="modal fade" id="addMikrotikModal" tabindex="-1" role="dialog" aria-labelledby="addMikrotikModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMikrotikModalLabel">Tambah Mikrotik</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="addMikrotikForm" action="{{ route('vpn.tambahmikrotik') }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="ipmikrotik">IP VPN / IP Public</label>
                                <input type="text" class="form-control" placeholder="172.160.x.x" name="ipmikrotik" id="ipmikrotik" required>
                            </div>
                            <div class="form-group">
                                <label for="site">Site / Nama Mikrotik</label>
                                <input type="text" class="form-control" placeholder="Site Indramayu" name="site" id="site" required>
                            </div>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" placeholder="Username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" placeholder="Password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-success">Tambah Mikrotik</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Pemberitahuan -->
        <div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notificationModalLabel">Pemberitahuan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p style="font-size: 20px;">Pada halaman ini berfungsi sebagai halaman penambahan mikrotik, entah itu dari Mikrotik yang sudah terhubung dengan VPN yang telah dibuat di halaman <a href="{{ route('vpn.index') }}">Data VPN</a> atau data mikrotik Anda yang sudah memiliki IP Public sendiri.</p>
                        <hr>
                        <p class="mb-0" style="font-size: 20px;">Jika Router MikroTik Anda tidak mempunyai IP Public, silakan buat akun <a href="{{ route('vpn.index') }}">VPN</a> pada form yang sudah disiapkan. Gratis tanpa biaya tambahan dan boleh lebih dari satu.</p>
                    </div>
                </div>
            </div>
        </div>





    <script>
        $(document).ready(function() {
            // Initialize DataTable with options
            $('#vpnTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                lengthChange: true,
                responsive: true,
                scrollX: true // Enables horizontal scrolling
            });

            // Handle the Info button click
            $('#vpnTable').on('click', '.btn-primary', function() {
                // Get the data from the row
                var row = $(this).closest('tr');
                var namaAkun = row.find('td:eq(1)').text();
                var username = row.find('td:eq(2)').text();
                var password = row.find('td:eq(3)').text();
                var ipAddress = row.find('td:eq(4)').text();
                var wbx = row.find('td:eq(5)').text();

                // Generate the MikroTik L2TP script dynamically
                var skripL2tp =
                    `
/interface l2tp-client remove [find comment="AQTVPN"]
/ip service set api port=9000          
/ip service set winbox port=${wbx}
/interface l2tp-client add name="AQTNetwork_VPN" connect-to="akses.aqtnetwork.my.id" user="${username}" password="${password}" comment="AQTVPN" disabled=no`;

                // Generate the MikroTik PPTP script 
                var skripPptp =
                    `
/interface pptp-client remove [find comment="AQTVPN"]
/ip service set api port=9000
/ip service set winbox port=${wbx}
/interface pptp-client add name="AQTNetwork_VPN" connect-to="akses.aqtnetwork.my.id" user="${username}" password="${password}" comment="AQTVPN" disabled=no`;

                // Set the data in the textareas
                $('#scriptL2tp').val(skripL2tp);
                $('#scriptPptp').val(skripPptp);

                // Show the modal
                $('#vpnInfoModal').modal('show');
            });

            // Handle the Copy button click for L2TP
            $('.copy-script[data-id="scriptL2tp"] button').click(function() {
                var skripMikrotik = $('#scriptL2tp').val();
                navigator.clipboard.writeText(skripMikrotik).then(function() {
                    alert('Script L2TP copied to clipboard!');
                }, function(err) {
                    console.error('Failed to copy script: ', err);
                });
            });

            // Handle the Copy button click for PPTP
            $('.copy-script[data-id="scriptPptp"] button').click(function() {
                var skripMikrotik = $('#scriptPptp').val();
                navigator.clipboard.writeText(skripMikrotik).then(function() {
                    alert('Script PPTP copied to clipboard!');
                }, function(err) {
                    console.error('Failed to copy script: ', err);
                });
            });

            // Handle delete button click
            $('#vpnTable').on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                var username = $(this).data('username');

                // Replace the confirm dialog with SweetAlert
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data ini tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // AJAX request to delete the data
                        $.ajax({
                            url: 'datavpn/' + id,
                            type: 'DELETE',
                            data: {
                                "_token": "{{ csrf_token() }}",
                                "username": username // Include the username in the data
                            },
                            success: function(response) {
                                // Replace the alert with SweetAlert success message
                                Swal.fire(
                                    'Terhapus!',
                                    'Data berhasil dihapus.',
                                    'success'
                                ).then(() => {
                                    location
                                .reload(); // Reload the page to update the table
                                });
                            },
                            error: function(xhr) {
                                // Replace the alert with SweetAlert error message
                                Swal.fire(
                                    'Gagal!',
                                    'Gagal menghapus data: ' + xhr.responseText,
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Prevent spaces from being entered in the Nama Akun field
            document.getElementById('namaAkunInput').addEventListener('keydown', function(event) {
                if (event.key === ' ') {
                    event.preventDefault(); // Prevent space from being entered
                }
            });
        });

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: true
            });
        @elseif (session('error'))
            Swal.fire({
                icon: 'error',
                title: '{{ session('error') }}',
                showConfirmButton: true
            });
        @endif

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Input Error',
                text: '{{ implode(', ', $errors->all()) }}',
                confirmButtonText: 'OK'
            });
        @endif
    </script>
    <!-- Tambahkan script untuk copy ke clipboard -->
    <script>
        function copyToClipboard(elementId) {
            // Ambil teks dari elemen span berdasarkan ID
            var copyText = document.getElementById(elementId).innerText;

            // Buat elemen textarea sementara untuk menyalin teks
            var tempInput = document.createElement("textarea");
            tempInput.value = copyText;
            document.body.appendChild(tempInput);

            // Salin teks dari textarea sementara
            tempInput.select();
            document.execCommand("copy");

            // Hapus elemen sementara setelah penyalinan
            document.body.removeChild(tempInput);

            // Tampilkan notifikasi
            alert("Copied: " + copyText);
        }
    </script>

    <script>
  $(document).ready(function() {
    $('#mikrotikTable').DataTable();

    // Handle Edit
    $('.editMikrotik').click(function() {
        var id = $(this).data('id');
        $.get('{{ route('vpn.editmikotik', '') }}/' + id, function(data) {
            if (data.error) {
                Swal.fire('Error', data.error, 'error');
                return;
            }
            $('#editMikrotikModal').modal('show');
            $('#editMikrotikForm').attr('action', '{{ url("/home/datamikrotik/") }}/' + id + '/update');
            $('#edit_ipmikrotik').val(data.ipmikrotik);
            $('#edit_site').val(data.site);
            $('#edit_username').val(data.username);
            $('#edit_password').val(data.password);
        }).fail(function(jqXHR, textStatus, errorThrown) {
            Swal.fire('Error', 'Gagal memuat data: ' + textStatus, 'error');
        });
    });

    // Handle Delete
    $('.deleteMikrotik').click(function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('vpn.destroymikrotik', '') }}/' + id,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Swal.fire('Dihapus!', 'Data Mikrotik telah dihapus.', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal!', 'Data Mikrotik gagal dihapus.', 'error');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data: ' + textStatus, 'error');
                    }
                });
            }
        });
    });
  });
</script>
</body>

</html>
