<!DOCTYPE html>
<html lang="en">
<x-head />

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
    <x-nav />
    <x-sidebar :mikrotik="$mikrotik" :olt="$olt" />

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

                                <!-- AREA SCAN -->
                                <div id="reader" style="width:300px; margin:auto;"></div>

                                <hr class="my-4 text-secondary">

                                <!-- INPUT ALTERNATIF (UNTUK SCANNER USB) -->
                                <div class="form-group">
                                    <label for="barcodeInput">Atau Scan pakai alat barcode (otomatis masuk sini):</label>
                                    <input type="text" id="barcodeInput" class="form-control text-center"
                                           placeholder="Arahkan scanner barcode ke sini" autofocus>
                                </div>

                                <hr class="my-4">

                                <div id="result" class="alert alert-info d-none"></div>

                                <button id="stopScan" class="btn btn-danger mt-3">Stop Kamera</button>
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

<!-- Tambahkan library html5-qrcode -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    const reader = new Html5Qrcode("reader");
    const resultDiv = document.getElementById("result");
    const inputBarcode = document.getElementById("barcodeInput");
    const stopButton = document.getElementById("stopScan");

    // Fungsi untuk menampilkan hasil
    function showResult(text) {
        resultDiv.classList.remove("d-none");
        resultDiv.innerHTML = "<b>QR / Barcode Terdeteksi:</b> " + text;

        // Kirim ke server pakai AJAX (opsional)
        fetch("", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ kode_modem: text })
        })
        .then(res => res.json())
        .then(data => console.log(data))
        .catch(err => console.error(err));
    }

    // Jalankan scanner kamera
    reader.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: 250
        },
        (decodedText) => {
            showResult(decodedText);
            reader.stop(); // otomatis berhenti setelah scan sukses
        },
        (error) => {
            // bisa diabaikan
        }
    ).catch(err => {
        alert("Gagal membuka kamera: " + err);
    });

    // Untuk scanner barcode fisik (input text)
    inputBarcode.addEventListener("change", function () {
        showResult(this.value);
        this.value = ""; // reset
    });

    // Tombol stop kamera
    stopButton.addEventListener("click", function () {
        reader.stop().then(() => {
            alert("Kamera dimatikan");
        }).catch(err => console.error(err));
    });
</script>

</body>
</html>
