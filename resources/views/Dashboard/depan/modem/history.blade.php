<!DOCTYPE html>
<html lang="en">
<x-head />

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <x-nav />

    <!-- Sidebar -->
    <x-sidebar :mikrotik="$mikrotik" :olt="$olt" />

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <x-cheader />

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">

                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    🕓 History Pemasangan Modem
                                </h5>
                               
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="historyTable" class="table table-bordered table-hover table-striped text-center">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th width="5%">#</th>
                                                <th>Nomor Seri</th>
                                                <th>Pelanggan</th>
                                                <th>Status</th>
                                                <th>Tanggal Pasang</th>
                                                <th>Tanggal Tarik</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($history as $i => $data)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $data->modem->serial_number ?? '-' }}</td>
                                                    <td>{{ $data->pelanggan ?? '-' }}</td>
                                                    <td>
    @if(empty($data->tanggal_tarik))
        <span class="badge bg-success">Terpasang</span>
    @else
        <span class="badge bg-danger">Ditarik</span>
    @endif


                                                    </td>
                                                    <td>{{ $data->tanggal_pasang ? \Carbon\Carbon::parse($data->tanggal_pasang)->format('d-m-Y H:i') : '-' }}</td>
                                                    <td>{{ $data->tanggal_tarik ? \Carbon\Carbon::parse($data->tanggal_tarik)->format('d-m-Y H:i') : '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">
                                                        Tidak ada riwayat pemasangan modem.
                                                    </td>
                                                </tr>
                                            @endforelse
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

    <x-footer />
</div>

<x-script />

<script>
$(document).ready(function() {
    $('#historyTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'asc']],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: {
                previous: "Sebelumnya",
                next: "Berikutnya"
            }
        }
    });
});
</script>

</body>
</html>
