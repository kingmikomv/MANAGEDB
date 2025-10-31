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
                                <p class="mb-3">Arahkan kamera ke QR Code modem atau unggah foto QR-nya</p>

                                <!-- Area kamera -->
                                <video id="preview"
                                       style="width:100%; max-width:400px; border-radius:10px; background:#000;"
                                       autoplay
                                       muted
                                       playsinline></video>

                                <!-- Tombol upload foto -->
                                <div class="mt-3">
                                    <label for="qrImage" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Unggah Gambar QR
                                    </label>
                                    <input type="file" id="qrImage" accept="image/*" style="display:none;">
                                </div>

                                <!-- Input fallback -->
                                <div class="mt-4">
                                    <label for="serialNumber">Nomor Seri (SN):</label>
                                    <input type="text"
                                           id="serialNumber"
                                           name="serialNumber"
                                           class="form-control text-center"
                                           placeholder="Scan QR atau ketik manual">
                                </div>

                                <button class="btn btn-success mt-3" onclick="submitSN()">Simpan</button>

                                <canvas id="qrCanvas" style="display:none;"></canvas>
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

<!-- ✅ Gunakan ZXing versi stabil -->
<script src="https://unpkg.com/@zxing/library@0.20.0"></script>

<script>
document.addEventListener("DOMContentLoaded", async function() {
    const videoElement = document.getElementById('preview');
    const serialInput = document.getElementById('serialNumber');
    const imageInput = document.getElementById('qrImage');
    const codeReader = new ZXing.BrowserMultiFormatReader();
    let isDetected = false;

    // ===== SCAN DARI KAMERA =====
    try {
        const devices = await codeReader.listVideoInputDevices();
        if (devices.length === 0) throw new Error("Tidak ada kamera terdeteksi");

        const selectedDeviceId = devices.find(d => d.label.toLowerCase().includes("back"))?.deviceId
            || devices[devices.length - 1].deviceId;

        codeReader.decodeFromVideoDevice(selectedDeviceId, videoElement, (result, err) => {
            if (result && !isDetected) {
                isDetected = true;
                const sn = result.text.trim();
                serialInput.value = sn;
                alert("✅ Nomor seri terbaca: " + sn);
                codeReader.reset();
            }
        });
    } catch (err) {
        console.error("❌ Kamera gagal diakses:", err);
        videoElement.insertAdjacentHTML(
            'afterend',
            "<p class='text-danger mt-2'>Kamera tidak bisa digunakan. Silakan ketik SN atau unggah QR.</p>"
        );
    }

    // ===== SCAN DARI GAMBAR UPLOAD =====
    imageInput.addEventListener("change", async function(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = async function(e) {
            const img = new Image();
            img.onload = async function() {
                const canvas = document.getElementById('qrCanvas');
                const ctx = canvas.getContext('2d');
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0, img.width, img.height);

                try {
                    const result = await codeReader.decodeFromCanvas(canvas);
                    const sn = result.text.trim();
                    serialInput.value = sn;
                    alert("✅ Nomor seri dari gambar: " + sn);
                } catch (decodeErr) {
                    alert("⚠️ Gagal membaca QR dari gambar. Pastikan QR jelas dan tidak buram.");
                }
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
});

function submitSN() {
    const sn = document.getElementById("serialNumber").value.trim();
    if (!sn) {
        alert("⚠️ Nomor seri belum diisi.");
        return;
    }

    alert("💾 Nomor seri disimpan: " + sn);

    // Contoh AJAX ke Laravel
    /*
    fetch('/tambahmodem', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ sn })
    })
    .then(res => res.json())
    .then(data => Swal.fire('Berhasil', 'Data modem disimpan', 'success'))
    .catch(console.error);
    */
}
</script>

</body>
</html>
