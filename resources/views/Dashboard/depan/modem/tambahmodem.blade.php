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
                                <p class="mb-3">Scan QR / Barcode modem untuk membaca SN</p>

                                <!-- Area kamera -->
                                <video id="preview" autoplay muted playsinline style="width:100%; max-width:400px; border-radius:10px; background:#000;"></video>

                                <!-- Upload gambar QR -->
                                <div class="mt-3">
                                    <label class="form-label">Atau upload foto QR/Barcode:</label>
                                    <input type="file" id="qrFile" accept="image/*" class="form-control" onchange="decodeImageFile(this)">
                                </div>

                                <!-- Input SN -->
                                <div class="mt-4">
                                    <label for="serialNumber">Nomor Seri (SN):</label>
                                    <input type="text" id="serialNumber" name="serialNumber" class="form-control text-center" placeholder="Scan atau ketik manual">
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

<!-- Library ZXing -->
<script src="https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const videoElement = document.getElementById('preview');
    const serialInput = document.getElementById('serialNumber');
    const codeReader = new ZXing.BrowserMultiFormatReader();

    // 🔹 Mulai kamera & scan otomatis
    codeReader
        .listVideoInputDevices()
        .then(videoInputDevices => {
            if (videoInputDevices.length === 0) {
                throw new Error("Tidak ada kamera yang terdeteksi");
            }

            const selectedDeviceId = videoInputDevices.length > 1
                ? videoInputDevices[videoInputDevices.length - 1].deviceId
                : videoInputDevices[0].deviceId;

            // Jalankan scanner realtime
            codeReader.decodeFromVideoDevice(selectedDeviceId, videoElement, (result, err) => {
                if (result) {
                    serialInput.value = result.text;
                    codeReader.reset(); // stop kamera setelah berhasil
                    alert("Nomor seri terbaca: " + result.text);
                }
            });
        })
        .catch(err => {
            console.error("Gagal membuka kamera:", err);
            videoElement.insertAdjacentHTML('afterend', "<p class='text-danger mt-2'>Kamera tidak bisa digunakan. Gunakan upload foto QR.</p>");
        });
});

// 🔹 Fungsi baca QR dari file upload
function decodeImageFile(input) {
    if (input.files.length === 0) return;

    const file = input.files[0];
    const reader = new FileReader();
    const serialInput = document.getElementById('serialNumber');
    const codeReader = new ZXing.BrowserMultiFormatReader();

    reader.onload = function() {
        const img = new Image();
        img.onload = function() {
            codeReader.decodeFromImage(img)
                .then(result => {
                    serialInput.value = result.text;
                    alert("Nomor seri terbaca: " + result.text);
                })
                .catch(() => {
                    alert("Gagal membaca QR/barcode dari gambar.");
                });
        };
        img.src = reader.result;
    };
    reader.readAsDataURL(file);
}

// 🔹 Fungsi simpan ke backend Laravel
function submitSN() {
    const sn = document.getElementById("serialNumber").value.trim();
    if (!sn) {
        alert("Nomor seri belum diisi.");
        return;
    }

    // Kirim ke backend Laravel via AJAX
    fetch("", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ sn })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Modem berhasil disimpan!");
        } else {
            alert("Gagal menyimpan modem: " + data.message);
        }
    })
    .catch(err => {
        alert("Terjadi kesalahan koneksi: " + err);
    });
}
</script>

</body>
</html>
