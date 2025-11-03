<!DOCTYPE html>
<html lang="en">
<x-head />

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">

    <x-nav />
    <x-sidebar :mikrotik="$mikrotik" :olt="$olt"/>

    <div class="content-wrapper">
        <x-cheader />

        <section class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-12">

                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">📋 Data Modem</h5>
                            </div>

                            <div class="card-body">
                                
                                <div class="table-responsive">
                                    <table id="modemTable" class="table table-bordered table-hover table-striped text-center">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Nomor Seri</th>
                                                <th>Status</th>
                                                <th>Pemilik Sekarang</th>
                                                <th>Tanggal Terakhir</th>
                                                <th>History</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($modems as $i => $modem)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $modem->serial_number }}</td>
                                                    <td>
                                                        @if($modem->status === 'terpasang')
                                                            <span class="badge badge-success">Terpasang</span>
                                                        @elseif($modem->status === 'ditarik')
                                                            <span class="badge badge-danger">Ditarik</span>
                                                        @else
                                                            <span class="badge badge-secondary">Belum digunakan</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $modem->pelanggan_aktif ?? '-' }}</td>
                                                    <td>{{ $modem->updated_at ? $modem->updated_at->format('Y-m-d H:i') : '-' }}</td>
                                                    <td> <a href="{{ route('modem.history', $modem->id) }}" class="btn btn-info btn-sm">
                                                                🕓 History
                                                            </a></td>
                                                    <td>
                                                        <div class="btn-group">
                                                           

                                                            @if($modem->status === 'terpasang')
                                                                <button class="btn btn-warning btn-sm ml-2" onclick="tarikModem('{{ $modem->serial_number }}')">
                                                                    🔄 Tarik
                                                                </button>
                                                            @elseif(in_array($modem->status, ['ditarik', 'belum digunakan', 'tersedia', null]))
                                                                <button class="btn btn-success btn-sm ml-2" 
                                                                    onclick="bukaModalPasang('{{ $modem->serial_number }}')">
                                                                    ➕ Pelanggan Baru
                                                                </button>
                                                            @endif
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

    <x-footer />
</div>

{{-- ========================== --}}
{{-- MODAL PASANG MODEM --}}
{{-- ========================== --}}
<div class="modal fade" id="modalPasang" tabindex="-1" aria-labelledby="modalPasangLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title">➕ Pasang Modem ke Pelanggan Baru</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="formPasang">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Serial Number</label>
            <input type="text" class="form-control" id="serial_number" name="serial_number" readonly>
          </div>
          <div class="form-group">
            <label>Nama Pelanggan</label>
            <input type="text" class="form-control" name="nama_pelanggan" placeholder="Masukkan nama pelanggan" required>
          </div>
          <div class="form-group">
            <label>Lokasi / Alamat Pemasangan</label>
            <textarea class="form-control" name="lokasi" rows="3" placeholder="Masukkan alamat lengkap" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">✅ Simpan & Pasang</button>
        </div>
      </form>
    </div>
  </div>
</div>

<x-script />

<script>
$(document).ready(function() {
    $('#modemTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'asc']]
    });
});

// Buka modal pasang
function bukaModalPasang(serial) {
    $('#serial_number').val(serial);
    $('#modalPasang').modal('show');
}

// Simpan data pelanggan baru
$('#formPasang').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("{{ route('modem.storePasang') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Berhasil!', data.message, 'success').then(() => {
                $('#modalPasang').modal('hide');
                location.reload();
            });
        } else {
            Swal.fire('Gagal!', data.message, 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
        console.error(err);
    });
});

// Tarik modem
function tarikModem(serial_number) {
    Swal.fire({
        title: 'Yakin ingin menarik modem ini?',
        text: "Status modem akan diubah menjadi 'Ditarik'.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, tarik!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("{{ route('modem.tarik') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ serial_number: serial_number })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                console.error(err);
            });
        }
    });
}
</script>

</body>
</html>
