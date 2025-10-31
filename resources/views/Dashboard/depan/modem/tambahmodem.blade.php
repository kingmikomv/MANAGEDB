<!DOCTYPE html>
<html lang="en">
<x-head />

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
    <x-nav />
    <x-sidebar :mikrotik="$mikrotik" :olt="$olt"/>

    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Tambah Modem</h5>
                            </div>

                            <div class="card-body text-center">
                                <p class="mb-3">Arahkan kamera ke QR Code modem</p>

                                <!-- Area kamera -->
                                <video id="preview" style="width:100%; max-width:400px; border-radius:10px; background:#000;"></video>

                                <!-- Input fallback -->
                                <div class="mt-4">
                                    <label for="serialNumber">Nomor Seri (SN):</label>
                                    <input type="text" id="serialNumber" name="serialNumber" class="form-control text-center" placeholder="Scan QR atau ketik manual">
                                </div>

                                <button class="btn btn-success mt-3" onclick="submitSN()">Simpan</button>
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

<!-- Gunakan versi browser ZXing yang benar -->
<script src="https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const videoElement = document.getElementById('preview');
    const serialInput = document.getElementById('serialNumber');
    const codeReader = new ZXing.BrowserMultiFormatReader();

    // Mulai kamera dan baca QR otomatis
    codeReader
        .listVideoInputDevices()
        .then(videoInputDevices => {
            if (videoInputDevices.length === 0) {
                throw new Error("Tidak ada kamera yang terdeteksi");
            }

            // Pilih kamera belakang (biasanya terakhir di list)
            const selectedDeviceId = videoInputDevices.length > 1
                ? videoInputDevices[videoInputDevices.length - 1].deviceId
                : videoInputDevices[0].deviceId;

            // Jalankan scanner realtime
            codeReader.decodeFromVideoDevice(selectedDeviceId, videoElement, (result, err) => {
                if (result) {
                    serialInput.value = result.text;
                    codeReader.reset(); // Hentikan kamera setelah terbaca
                    alert("Nomor seri terbaca: " + result.text);
                }
            });
        })
        .catch(err => {
            console.error("Gagal membuka kamera:", err);
            videoElement.insertAdjacentHTML(
                'afterend',
                "<p class='text-danger mt-2'>Kamera tidak bisa digunakan. Silakan ketik SN manual.</p>"
            );
        });
});

function submitSN() {
    const sn = document.getElementById("serialNumber").value.trim();
    if (!sn) {
        alert("Nomor seri belum diisi.");
        return;
    }
    alert("Nomor seri disimpan: " + sn);
    // TODO: kirim ke controller Laravel via fetch('/tambahmodem', { method:'POST', body: JSON.stringify({ sn }) })
}
</script>

</body>
</html>
