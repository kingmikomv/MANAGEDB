<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR / Barcode Modem</title>

    <!-- ZXing library -->
    <script src="https://unpkg.com/@zxing/browser@0.1.3"></script>
    <script src="https://unpkg.com/@zxing/library@0.20.0"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f3f3;
            padding: 15px;
            text-align: center;
        }
        video {
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 0 10px #999;
        }
        input[type="file"] {
            display: none;
        }
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background: #0056b3;
        }
        #result {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            color: green;
        }
    </style>
</head>
<body>
    <h2>Scan QR / Barcode SN Modem</h2>

    <video id="video" autoplay></video>

    <div>
        <button class="btn" id="startScan">Mulai Scan</button>
        <button class="btn" id="refocus">Refocus Kamera</button>
        <label for="fileUpload" class="btn">Upload Gambar QR/Barcode</label>
        <input type="file" id="fileUpload" accept="image/*">
    </div>

    <div id="result">Menunggu hasil scan...</div>

    <form>
        <input type="text" id="serial_number" name="serial_number" placeholder="SN otomatis muncul di sini" readonly style="margin-top:10px; padding:8px; width:80%; border-radius:8px; border:1px solid #ccc;">
    </form>

    <script>
        const codeReader = new ZXing.BrowserMultiFormatReader();
        let selectedDeviceId = null;

        // Fungsi mulai kamera
        async function startCamera() {
            try {
                const devices = await codeReader.listVideoInputDevices();
                const backCamera = devices.find(device =>
                    device.label.toLowerCase().includes('back')
                ) || devices[0];

                selectedDeviceId = backCamera.deviceId;

                codeReader.decodeFromVideoDevice(selectedDeviceId, 'video', (result, err) => {
                    if (result) {
                        document.getElementById('result').textContent = "SN: " + result.text;
                        document.getElementById('serial_number').value = result.text;
                        // Stop setelah berhasil
                        codeReader.reset();
                    }
                });
            } catch (error) {
                console.error(error);
                document.getElementById('result').textContent = "Kamera tidak tersedia atau izin ditolak.";
            }
        }

        // Tombol mulai
        document.getElementById('startScan').addEventListener('click', startCamera);

        // Tombol refocus (restart kamera)
        document.getElementById('refocus').addEventListener('click', () => {
            codeReader.reset();
            startCamera();
        });

        // Upload gambar QR/Barcode
        document.getElementById('fileUpload').addEventListener('change', async (event) => {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = async (e) => {
                try {
                    const img = new Image();
                    img.src = e.target.result;
                    img.onload = async () => {
                        const result = await codeReader.decodeFromImage(img);
                        if (result) {
                            document.getElementById('result').textContent = "SN: " + result.text;
                            document.getElementById('serial_number').value = result.text;
                        } else {
                            document.getElementById('result').textContent = "QR/Barcode tidak terbaca.";
                        }
                    };
                } catch (err) {
                    document.getElementById('result').textContent = "Gagal membaca gambar.";
                }
            };
            reader.readAsDataURL(file);
        });
    </script>
</body>
</html>
