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
                                <p class="mb-3">Arahkan kamera ke QR / Barcode modem atau upload fotonya</p>

                                <!-- Kamera -->
                                <video id="preview" style="width:100%; max-width:400px; border-radius:10px; background:#000;"></video>

                                <div class="mt-3">
                                    <input type="file" id="fileInput" accept="image/*" class="form-control-file mt-2">
                                </div>

                                <!-- Input fallback -->
                                <div class="mt-4">
                                    <label for="serialNumber">Nomor Seri (SN):</label>
                                    <input type="text" id="serialNumber" name="serialNumber" class="form-control text-center" placeholder="Scan QR / Barcode atau ketik manual">
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

<script src="https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const videoElement = document.getElementById('preview');
    const serialInput = document.getElementById('serialNumber');
    const fileInput = document.getElementById('fileInput');
    const codeReader = new ZXing.BrowserMultiFormatReader();

    // Jalankan kamera
    codeReader.listVideoInputDevices().then(videoInputDevices => {
        if (videoInputDevices.length === 0) throw new Error("Tidak ada kamera");

        const selectedDeviceId = videoInputDevices.length > 1
            ? videoInputDevices[videoInputDevices.length - 1].deviceId
            : videoInputDevices[0].deviceId;

        codeReader.decodeFromVideoDevice(selectedDeviceId, videoElement, (result, err) => {
            if (result) {
                handleResult(result.text);
                codeReader.reset();
            }
        });
    }).catch(err => {
        console.error("Gagal buka kamera:", err);
    });

    // Deteksi upload gambar QR/barcode
    fileInput.addEventListener("change", async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const imgURL = URL.createObjectURL(file);
        try {
            const result = await codeReader.decodeFromImageUrl(imgURL);
            handleResult(result.text);
        } catch (err) {
            alert("Gagal membaca QR/barcode dari gambar. Pastikan gambar jelas.");
            console.error(err);
        } finally {
            URL.revokeObjectURL(imgURL);
        }
    });

    function handleResult(text) {
        serialInput.value = text;
        alert("Terbaca: " + text);
    }
});

function submitSN() {
    const sn = document.getElementById("serialNumber").value.trim();
    if (!sn) {
        alert("Nomor seri belum diisi.");
        return;
    }
    alert("Nomor seri disimpan: " + sn);
    // TODO: fetch('/tambahmodem', { method:'POST', body: JSON.stringify({ sn }) })
}
</script>
</body>
</html>
