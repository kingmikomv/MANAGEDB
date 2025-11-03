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
                    <div class="col-md-8">

                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                <h5 class="card-title mb-0">📦 Tambah Modem</h5>
                            </div>

                            <div class="card-body">
                                <form id="modemForm" onsubmit="event.preventDefault(); submitSN();">

                                    <!-- Nomor Seri -->
                                    <div class="form-group">
                                        <label class="form-label">Nomor Seri (SN)</label>
                                        <input type="text" id="serial_number" name="serial_number"
                                               class="form-control text-center"
                                               placeholder="Scan atau ketik SN di sini" autofocus>
                                    </div>

                                    <!-- Tanggal -->
                                    <div class="form-group mt-3">
                                        <label class="form-label">Tanggal</label>
                                        <input type="text" id="lokasi" name="lokasi"
                                               class="form-control text-center" readonly>
                                    </div>

                                    <!-- Tombol Upload -->
                                    <button id="uploadBtn" class="btn btn-success btn-block mt-4" type="submit">
                                        💾 Upload Data
                                    </button>
                                </form>
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

<!-- ✅ Tambahkan SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const lokasiInput = document.getElementById('lokasi');
    const snInput = document.getElementById('serial_number');

    // Isi tanggal otomatis (hari ini)
    lokasiInput.value = new Date().toISOString().split('T')[0];
    snInput.focus();

    // Jika user scan barcode dan scanner kirim ENTER → langsung kirim
    snInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            submitSN();
        }
    });
});

async function submitSN() {
    const sn = document.getElementById('serial_number').value.trim();
    const lokasi = document.getElementById('lokasi').value.trim();
    const btn = document.getElementById('uploadBtn');

    if (!sn) {
        Swal.fire({
            icon: "warning",
            title: "Nomor seri kosong!",
            text: "Silakan isi atau scan nomor seri terlebih dahulu.",
            confirmButtonColor: "#3085d6"
        });
        return;
    }

    btn.disabled = true;
    btn.innerHTML = "⏳ Mengunggah...";

    try {
        const res = await fetch("{{ route('modem.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ serial_number: sn, lokasi: lokasi })
        });

        const data = await res.json();

        if (data.exists) {
            Swal.fire({
                icon: "error",
                title: "Duplikat Data!",
                text: "Nomor seri sudah terdaftar di database.",
                confirmButtonColor: "#d33"
            });
        } else if (data.success) {
            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: "Nomor seri berhasil disimpan.",
                showConfirmButton: false,
                timer: 2000
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Gagal!",
                text: "Terjadi kesalahan saat menyimpan data.",
                confirmButtonColor: "#d33"
            });
        }

        // Kosongkan input & fokus ulang
        document.getElementById('serial_number').value = '';
        setTimeout(() => document.getElementById('serial_number').focus(), 200);

    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Gagal!",
            text: "Tidak dapat terhubung ke server.",
            confirmButtonColor: "#d33"
        });
        console.error(err);
    } finally {
        btn.disabled = false;
        btn.innerHTML = "💾 Upload Data";
    }
}
</script>

</body>
</html>
