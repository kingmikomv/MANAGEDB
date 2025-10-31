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
                                <video id="preview"
                                       style="width:100%; max-width:400px; border-radius:10px; background:#000;"
                                       autoplay
                                       muted
                                       playsinline></video>

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

<!-- ✅ Gunakan versi ZXing stabil -->
<script src="https://unpkg.com/@zxing/library@0.20.0"></script>

<script>
document.addEventListener("DOMContentLoaded", async function() {
    const videoElement = document.getElementById('preview');
    const serialInput = document.getElementById('serialNumber');
    const codeReader = new ZXing.BrowserMultiFormatReader();
    let isDetected = false; // untuk mencegah pembacaan ganda

    try {
        const devices = await codeReader.listVideoInputDevices();
        if (devices.length === 0) {
            throw new Error("Tidak ada kamera yang terdeteksi");
        }

        // Pilih kamera belakang kalau tersedia
        const selectedDeviceId = devices.find(d => d.label.toLowerCase().includes("back"))?.deviceId || devices[devices.length - 1].deviceId;

        console.log("Memulai kamera:", selectedDeviceId);

        codeReader.decodeFromVideoDevice(selectedDeviceId, videoElement, (result, err) => {
            if (result && !isDetected) {
                isDetected = true;
                const sn = result.text.trim();
                serialInput.value = sn;
                alert("✅ Nomor seri terbaca: " + sn);
                codeReader.reset(); // stop kamera
            }
        });
    } catch (err) {
        console.error("❌ Gagal membuka kamera:", err);
        videoElement.insertAdjacentHTML(
            'afterend',
            "<p class='text-danger mt-2'>Kamera tidak bisa digunakan. Silakan ketik SN secara manual.</p>"
        );
    }
});

function submitSN() {
    const sn = document.getElementById("serialNumber").value.trim();
    if (!sn) {
        alert("⚠️ Nomor seri belum diisi.");
        return;
    }

    alert("💾 Nomor seri disimpan: " + sn);

    // Contoh kirim ke Laravel pakai fetch:
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
    .then(data => console.log('Disimpan:', data))
    .catch(console.error);
    */
}
</script>

</body>
</html>
