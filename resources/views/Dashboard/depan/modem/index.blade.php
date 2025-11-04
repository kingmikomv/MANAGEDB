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
                                <h5 class="card-title mb-0">📋 Data Alat</h5>
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
                                                        @elseif($modem->status === 'rusak')
                                                            <span class="badge badge-warning">Rusak</span>
                                                        @elseif($modem->status === 'return')
                                                            <span class="badge badge-info">Return ke Toko</span>
                                                        @else
                                                            <span class="badge badge-secondary">Belum digunakan</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $modem->pelanggan_aktif ?? '-' }}</td>
                                                    <td>{{ $modem->updated_at ? $modem->updated_at->format('Y-m-d H:i') : '-' }}</td>
                                                    <td>
                                                        <a href="{{ route('modem.history', $modem->id) }}" class="btn btn-info btn-sm">
                                                            🕓 History
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <!-- Tombol aksi jadi dropdown -->
                                                        <div class="dropdown">
                                                            <button class="btn btn-dark btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                                ⚙️ Aksi
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right bg-dark border-0 shadow">
                                                                @if($modem->status === 'terpasang')
                                                                    <a class="dropdown-item text-warning" href="#" onclick="tarikModem('{{ $modem->serial_number }}')">
                                                                        🔄 Tarik Modem
                                                                    </a>
                                                                @elseif(in_array($modem->status, ['ditarik', 'belum digunakan', 'tersedia', null]))
                                                                    <a class="dropdown-item text-success" href="#" onclick="bukaModalPasang('{{ $modem->serial_number }}')">
                                                                        ➕ Pasang Baru
                                                                    </a>
                                                                @endif

                                                                @if(!in_array($modem->status, ['rusak', 'return']))
                                                                    <a class="dropdown-item text-info" href="#" onclick="bukaModalStatus('{{ $modem->serial_number }}')">
                                                                        ⚙️ Update Status
                                                                    </a>
                                                                @endif

                                                                <div class="dropdown-divider bg-secondary"></div>
                                                                <a class="dropdown-item text-danger" href="#" onclick="hapusModem('{{ $modem->serial_number }}')">
                                                                    🗑️ Hapus Modem
                                                                </a>
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

{{-- ========================== --}}
{{-- MODAL UPDATE STATUS --}}
{{-- ========================== --}}
<div class="modal fade" id="modalStatus" tabindex="-1" aria-labelledby="modalStatusLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title">⚙️ Update Status Modem</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="formStatus">
        @csrf
        <div class="modal-body">
          <input type="hidden" id="serial_status" name="serial_number">
          <div class="form-group">
            <label>Pilih Status Baru</label>
            <select name="status" class="form-control" required>
              <option value="">-- Pilih Status --</option>
              <option value="rusak">Rusak</option>
              <option value="return">Return ke Toko</option>
            </select>
          </div>
          <div class="form-group">
            <label>Keterangan</label>
            <textarea class="form-control" name="keterangan" rows="3" placeholder="Opsional"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">💾 Simpan</button>
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

function bukaModalPasang(serial) {
    $('#serial_number').val(serial);
    $('#modalPasang').modal('show');
}

function bukaModalStatus(serial) {
    $('#serial_status').val(serial);
    $('#modalStatus').modal('show');
}

$('#formPasang').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch("{{ route('modem.storePasang') }}", {
        method: "POST",
        headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        Swal.fire(data.success ? 'Berhasil!' : 'Gagal!', data.message, data.success ? 'success' : 'error')
            .then(() => { if (data.success) location.reload(); });
    })
    .catch(() => Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error'));
});

$('#formStatus').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch("{{ route('modem.updateStatus') }}", {
        method: "POST",
        headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        Swal.fire(data.success ? 'Berhasil!' : 'Gagal!', data.message, data.success ? 'success' : 'error')
            .then(() => { if (data.success) location.reload(); });
    })
    .catch(() => Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error'));
});

function tarikModem(serial) {
    Swal.fire({
        title: 'Yakin ingin menarik modem ini?',
        text: "Status modem akan diubah menjadi 'Ditarik'.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, tarik!',
        cancelButtonText: 'Batal'
    }).then((r) => {
        if (r.isConfirmed) {
            fetch("{{ route('modem.tarik') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ serial_number: serial })
            }).then(res => res.json()).then(data => {
                Swal.fire(data.success ? 'Berhasil!' : 'Gagal!', data.message, data.success ? 'success' : 'error')
                    .then(() => { if (data.success) location.reload(); });
            });
        }
    });
}

function hapusModem(serial) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data modem ini akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((r) => {
        if (r.isConfirmed) {
            fetch("{{ route('modem.destroy') }}", {
                method: "DELETE",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ serial_number: serial })
            }).then(res => res.json()).then(data => {
                Swal.fire(data.success ? 'Dihapus!' : 'Gagal!', data.message, data.success ? 'success' : 'error')
                    .then(() => { if (data.success) location.reload(); });
            });
        }
    });
}
</script>

</body>
</html>
