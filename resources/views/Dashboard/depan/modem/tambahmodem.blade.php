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
                                <p class="mb-3">Arahkan kamera ke QR Code di modem atau gunakan alat scanner barcode USB.</p>

                                <!-- AREA SCANNER KAMERA -->
                                <div id="reader" 
                                    style="width:300px; height:300px; margin:auto; border:2px solid #555; border-radius:10px;">
                                </div>

                                <div class="mt-3">
                                    <button id="startScan" class="btn btn-success">Mulai Kamera</button>
                                    <button id="stopScan" class="btn btn-danger d-none">Stop Kamera</button>
                                </div>

                                <hr class="my-4">

                                <!-- ALTERNATIF: SCAN DENGAN SCANNER BARCODE USB -->
                                <h6>Atau Scan Pakai Alat Barcode (USB)</h6>
                                <input type="text" id="barcodeInput" class="form-control text-center"
                                       placeholder="Arahkan scanner barcode ke sini" autofocus>

                                <div id="result" class="alert alert-info mt-4 d-none"></div>
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

<!-- Library QR Scanner -->
<script src="https://unpkg.com/html5-qrcode@2.3.8"></script>

<script>
    let reader;
    let isScanning = false;
    const resultDiv = document.getElementById("result");
    const barcodeInput = document.getElementById("barcodeInput");
    const startButton = document.getElementById("startScan");
    const stopButton = document.getElementById("stopScan");

    // Fungsi tampil hasil
    function showResult(text) {
        resultDiv.classList.remove("d-none");
        resultDiv.innerHTML = `<b>SN / Kode Modem:</b> ${text}`;
        console.log("QR/Barcode detected:", text);

        // Kirim ke server (opsional)
        fetch("", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ kode_modem: text })
        })
        .then(res => res.json())
        .then(data => console.log("Server response:", data))
        .catch(err => console.error(err));
    }

    // Fungsi mulai kamera
    function startCamera() {
        if (isScanning) return;

        reader = new Html5Qrcode("reader");
        isScanning = true;
        startButton.classList.add("d-none");
        stopButton.classList.remove("d-none");

        reader.start(
            { facingMode: "environment" },
            {
                fps: 30,       // lebih responsif
                qrbox: 300,    // area scan lebih besar
                aspectRatio: 1.0,
                disableFlip: true
            },
            decodedText => {
                if (decodedText) {
                    showResult(decodedText);
                    stopCamera();
                }
            },
            error => {
                // tetap scanning
            }
        ).catch(err => {
            alert("❌ Gagal membuka kamera: " + err);
            isScanning = false;
            startButton.classList.remove("d-none");
            stopButton.classList.add("d-none");
        });
    }

    // Fungsi stop kamera
    function stopCamera() {
        if (reader && isScanning) {
            reader.stop().then(() => {
                console.log("Kamera dimatikan.");
            }).catch(err => console.error(err));
        }
        isScanning = false;
        startButton.classList.remove("d-none");
        stopButton.classList.add("d-none");
    }

    // Tombol event
    startButton.addEventListener("click", startCamera);
    stopButton.addEventListener("click", stopCamera);

    // Input manual via alat barcode USB
    barcodeInput.addEventListener("change", function () {
        if (this.value.trim() !== "") {
            showResult(this.value.trim());
            this.value = "";
        }
    });
</script>

</body>
</html>
