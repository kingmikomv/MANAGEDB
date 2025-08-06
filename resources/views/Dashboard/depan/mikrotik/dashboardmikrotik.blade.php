<!DOCTYPE html>
<html lang="en">


<x-head />


<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">

        <!-- Preloader -->
     
        <!-- Navbar -->
        <x-nav />
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <x-sidebar :mikrotik="$mikrotik" :olt="$olt"/>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <x-cheader />

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Info boxes -->
                    <!-- /.row -->
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-cloud"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Site</span>
                                    <span class="info-box-number">
                                        {{ $site }}
                                    </span>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">CPU Model</span>
                                    <span class="info-box-number">
                                        RO: {{ $model }} - Ver: {{ $version }}
                                    </span>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </div>
                        <!-- /.col -->
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box mb-3">
                                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-bolt"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Status Server</span>
                                    <span class="info-box-number" id="uptime">
                                        Uptime:
                                    </span>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </div>
                        <!-- /.col -->

                        <!-- /.col -->
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Down</h5>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <a href="{{ route('sync', ['ipmikrotik' => $ipmikrotik]) }}"
                                            class="btn btn-primary mb-3">Sync</a>
                                        @if ($currentDownUsers)
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Nama Akun</th>
                                                        <th>Waktu DOWN</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $noo = 1; @endphp
                                                    @foreach ($currentDownUsers as $user)
                                                        <tr>
                                                            <td>{{$noo++}}</td>
                                                            <td>{{ $user['namaakun'] }}</td>

                                                            <td>{{ $user['detected_at'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <p>Semua akun aktif, Nyantaaaiii Bestiiihh </p>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Active Connection</h5>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="myTable" class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Client</th>
                                                    <th>Action</th>
                                                    <th>Address</th>
                                                    <th>Mac</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $no = 1; @endphp
                                                @foreach ($response3 as $data => $d)
                                                    <tr>
                                                        <td>{{ $no++ }}</td>
                                                        <td>{{ $d['name'] }}</td>
                                                        <td>
                                                            <div class="dropdown">
                                                                <button class="btn btn-primary dropdown-toggle btn-sm"
                                                                    type="button"
                                                                    id="dropdownMenuButton{{ $d['.id'] }}"
                                                                    data-toggle="dropdown" aria-haspopup="true"
                                                                    aria-expanded="false">
                                                                    Action
                                                                </button>
                                                                <div class="dropdown-menu"
                                                                    aria-labelledby="dropdownMenuButton{{ $d['.id'] }}">
                                                                    <a class="dropdown-item remote-modem" href="#"
                                                                        data-ip="{{ $d['address'] }}"
                                                                        data-port="{{ $portweb }}"><i
                                                                            class="fas fa-bolt"></i> Remote Modem</a>
                                                                    <a class="dropdown-item restart-modem"
                                                                        href="#" data-ip="{{ $d['address'] }}"
                                                                        data-port="{{ $portweb }}"><i
                                                                            class="fas fa-sync-alt"></i> Restart
                                                                        Modem</a>
                                                                    <a class="dropdown-item" href="{{ route('mikrotik.status', ['ipmikrotik' => $ipmikrotik, 'username' => $d['name']]) }}">
                                                                        <i class="fas fa-eye"></i> Pantau Traffik
                                                                    </a>
                                                                    <a class="dropdown-item copy-btn" href="#"><i
                                                                            class="fas fa-copy"></i> Copy IP Address</a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td id="text-to-copy">{{ $d['address'] }}</td>
                                                        <td id="text-to-copy">{{ $d['caller-id'] }}</td>

                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">New Online</h5>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="table-responsive">

                                        <table id="myTable2" class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Client</th>
                                                    <th>Address</th>
                                                    <th>Uptime</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $no = 1; @endphp

                                                @foreach ($response4 as $data => $d)
                                                    <tr>
                                                        <td>{{ $no++ }}</td>
                                                        <td>{{ $d['name'] }}</td>
                                                        <td>{{ $d['address'] }}</td>
                                                        <td data-order="{{ $d['uptime_sort'] }}">{{ $d['uptime'] }}
                                                        </td>

                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- /.col -->
                    </div>

                </div>
            </section>

        </div>

        <!-- Remote Modem Modal -->
        <div class="modal fade" id="RemoteModem" tabindex="-1" role="dialog" aria-labelledby="RemoteModemLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="RemoteModemLabel">Remote Modem</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="post" id="remoteModemForm" action="">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="ipAddress">IP Address</label>
                                <input type="text" class="form-control" id="remote-ip-address" name="ipaddr"
                                    placeholder="127.x.x.x" readonly="true">
                            </div>
                            <div class="form-group">
                                <label for="exampleFormControlSelect1">PORT</label>
                                <select class="form-control" id="toport" name="toport">
                                    <option disabled selected value>- PILIH PORT -</option>
                                    <option value="443">443</option>
                                    <option value="80">80</option>
                                    <option value="8080">8080</option>
                                </select>
                                <small class="mini-text">Modem : 80, Tenda : 80</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
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


    <script>
        function fetchUptime() {
            $.ajax({
                url: '/mikrotik/uptime/{{ $ipmikrotik }}',
                method: 'GET',
                success: function(response) {
                    if (response.error) {
                        $('#uptime').text('Uptime: Error');
                    } else {
                        $('#uptime').text('Uptime: ' + response.uptime);
                    }
                },
                error: function() {
                    $('#uptime').text('Uptime: Error');
                }
            });
        }

        // Fetch uptime immediately and then every 5 minutes
        fetchUptime();
        setInterval(fetchUptime, 300000); // Refresh uptime every 5 minutes (300000 milliseconds)
    </script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable with responsive design
            var table = $('#myTable').DataTable({
                responsive: true, // Membuat tabel responsif
                autoWidth: false, // Menonaktifkan penyesuaian otomatis lebar kolom
                paging: $('#myTable tbody tr').length >=
                    60, // Aktifkan pagination jika jumlah baris 60 atau lebih
                pageLength: 30, // Tampilkan 50 baris per halaman jika pagination aktif
                columnDefs: [{
                        targets: "_all",
                        className: "text-center"
                    } // Center align semua kolom
                ]
            });
            var table = $('#myTable2').DataTable({
                responsive: true,
                autoWidth: false,
                paging: $('#myTable2 tbody tr').length >= 60,
                pageLength: 30,
                columnDefs: [{
                    targets: "_all",
                    className: "text-center"
                }]
                // Jangan pakai "order" di sini kalau data sudah diurutkan dari backend
            });
            


            // Function to get query parameter from URL
            function getQueryParam(param) {
                let urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(param);
            }

            // Get the ipMikrotik parameter from the URL
            let ipMikrotik = getQueryParam('ipmikrotik');

            // Variable to store data-port from the .remote-modem button
            var currentDataPort;

            // Handle click event on the "remote-modem" buttons using event delegation
            $('#myTable').on('click', '.remote-modem', function(event) {
                event.preventDefault(); // Prevent default link behavior

                var ipAddress = $(this).data('ip'); // Get IP address from the data attribute
                currentDataPort = $(this).data('port'); // Store data-port value from the data attribute

                // Set the IP address in the modal
                $('#remote-ip-address').val(ipAddress);
                $('#toport').val(currentDataPort);
                $('#RemoteModem').modal('show'); // Show the modal
            });

            // Handle form submission for adding/updating firewall rule
            $('#remoteModemForm').on('submit', function(event) {
                event.preventDefault(); // Prevent default form submission

                var ipAddress = $('#remote-ip-address').val();
                var toPort = $('#toport').val();

                if (toPort) {
                    $.ajax({
                        url: '{{ route('addFirewallRule') }}', // Laravel route to handle the request
                        type: 'POST',
                        data: {
                            ipaddr: ipAddress,
                            port: toPort,
                            ipmikrotik: ipMikrotik,
                            _token: '{{ csrf_token() }}' // Include CSRF token
                        },
                        success: function(response) {
                            if (response.success) {
                                var newTabUrl =
                                    `http://akses.aqtnetwork.my.id:${currentDataPort}`; // Use currentDataPort

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: 'Firewall rule added or updated successfully!',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Open a new tab with the constructed URL
                                        window.open(newTabUrl, '_blank');
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to add firewall rule: ' + response
                                        .error,
                                });
                            }
                            $('#RemoteModem').modal('hide'); // Hide the modal
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while adding the firewall rule.',
                            });
                            console.log('Error Details:', xhr.responseText);
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Please select a port.',
                    });
                }
            });

            // Handle click event on the "restart-modem" buttons using event delegation
            $('#myTable').on('click', '.restart-modem', function(event) {
                event.preventDefault(); // Prevent default link behavior

                var ipAddress = $(this).data('ip'); // Get IP address from the data attribute
                var dataPort = $(this).data('port'); // Get data-port value from the data attribute

                // Show confirmation dialog
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to restart the modem connection?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, restart it!',
                    cancelButtonText: 'No, cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Perform the restart action
                        $.ajax({
                            url: '{{ route('restartmodem') }}', // Laravel route to handle the restart
                            type: 'POST',
                            data: {
                                ipaddr: ipAddress,
                                port: dataPort,
                                ipmikrotik: ipMikrotik,
                                _token: '{{ csrf_token() }}' // Include CSRF token
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: 'Modem restarted successfully!',
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Failed to restart modem: ' +
                                            response.error,
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while restarting the modem.',
                                });
                                console.log('Error Details:', xhr.responseText);
                            }
                        });
                    }
                });
            });

            // Copy IP address to clipboard using event delegation
            $('#myTable').on('click', '.copy-btn', function() {
                var textToCopy = $(this).closest('tr').find('#text-to-copy').text();
                navigator.clipboard.writeText(textToCopy).then(function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Copied!',
                        text: 'IP Address copied to clipboard.',
                    });
                }, function(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to copy IP Address.',
                    });
                });
            });
        });
    </script>
   
</body>

</html>
