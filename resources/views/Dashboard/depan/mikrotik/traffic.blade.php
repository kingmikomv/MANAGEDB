<!DOCTYPE html>
<html lang="en">

<x-head />

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        <x-nav />
        <x-sidebar :mikrotik="$mikrotik" :olt="$olt" />

        <div class="content-wrapper">
            <x-cheader />
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">TRAFFIC PPPoE</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-bordered text-white">
                                                <tbody>
                                                    <tr>
                                                        <td width="150px">PPPOE</td>
                                                        <td width="10px">:</td>
                                                        <td>{{ $interfaceName }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>IP Address</td>
                                                        <td>:</td>
                                                        <td>{{ $ipAddress ?? 'Tidak tersedia' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>MAC Address</td>
                                                        <td>:</td>
                                                        <td>{{ $macAddress ?? 'Tidak tersedia' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Uptime</td>
                                                        <td>:</td>
                                                        <td>{{ $uptime ?? 'Tidak tersedia' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <table class="table table-bordered text-white">
                                                <tbody>
                                                    <tr>
                                                        <td width="150px">Download (RX)</td>
                                                        <td width="10px">:</td>
                                                        <td><span id="rx-value">Loading...</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Upload (TX)</td>
                                                        <td>:</td>
                                                        <td><span id="tx-value">Loading...</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <a href="{{ route('masukmikrotik', [
                                    'ipmikrotik' => $ip,
                                    'portweb' => $dataMikrotik->portweb,
                                ]) }}" class="btn btn-block btn-warning"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        </div>
                                        <div class="col-md-6">

                                            <div id="trafficChart" class="bg-white p-2 rounded"></div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <aside class="control-sidebar control-sidebar-dark"></aside>
        <x-footer />
    </div>

    <x-script />
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const ipmikrotik = "{{ $ip }}";
        const username = "{{ $interfaceName }}";

        function formatSpeed(bps) {
            if (bps >= 1_000_000_000) return (bps / 1_000_000_000).toFixed(2) + ' Gbps';
            if (bps >= 1_000_000) return (bps / 1_000_000).toFixed(2) + ' Mbps';
            if (bps >= 1_000) return (bps / 1_000).toFixed(2) + ' Kbps';
            return bps + ' bps';
        }

        let rxData = [];
        let txData = [];

        const chart = new ApexCharts(document.querySelector("#trafficChart"), {
            chart: {
                type: 'line',
                height: 350,
                background: '#ffffff', // White background
                animations: {
                    enabled: true,
                    easing: 'linear',
                    dynamicAnimation: {
                        speed: 1000
                    }
                },
                toolbar: {
                    show: true
                },
                zoom: {
                    enabled: false
                },
                foreColor: '#000000' // Black text for labels/axis in white bg
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#007bff', '#e74c3c'], // RX (blue), TX (red)
            series: [{
                    name: 'RX (bps)',
                    data: rxData
                },
                {
                    name: 'TX (bps)',
                    data: txData
                }
            ],
            xaxis: {
                type: 'datetime',
                labels: {
                    datetimeUTC: false,
                    format: 'HH:mm:ss'
                },
                title: {
                    text: 'Waktu',
                    style: {
                        color: '#000'
                    }
                }
            },
            yaxis: {
                title: {
                    text: 'Bandwidth',
                    style: {
                        color: '#000'
                    }
                },
                labels: {
                    formatter: function(val) {
                        if (val >= 1_000_000_000) return (val / 1_000_000_000).toFixed(1) + 'G';
                        if (val >= 1_000_000) return (val / 1_000_000).toFixed(1) + 'M';
                        if (val >= 1_000) return (val / 1_000).toFixed(0) + 'K';
                        return val;
                    }
                }
            },
            tooltip: {
                theme: 'light',
                x: {
                    format: 'HH:mm:ss'
                },
                y: {
                    formatter: formatSpeed
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                labels: {
                    colors: '#000'
                }
            },
            grid: {
                borderColor: '#ccc'
            }
        });

        chart.render();

        function fetchTraffic() {
            fetch(`{{ route('get.traffic') }}?ipmikrotik=${ipmikrotik}&username=${username}`)
                .then(response => response.json())
                .then(data => {
                    const now = new Date().getTime();

                    if (data.rx !== undefined && data.tx !== undefined) {
                        document.getElementById('rx-value').textContent = formatSpeed(data.rx);
                        document.getElementById('tx-value').textContent = formatSpeed(data.tx);

                        rxData.push({
                            x: now,
                            y: data.rx
                        });
                        txData.push({
                            x: now,
                            y: data.tx
                        });

                        if (rxData.length > 60) rxData.shift();
                        if (txData.length > 60) txData.shift();

                        chart.updateSeries([{
                                name: 'RX (bps)',
                                data: rxData
                            },
                            {
                                name: 'TX (bps)',
                                data: txData
                            }
                        ]);
                    } else {
                        document.getElementById('rx-value').textContent = 'Data tidak tersedia';
                        document.getElementById('tx-value').textContent = 'Data tidak tersedia';
                    }
                })
                .catch(err => {
                    document.getElementById('rx-value').textContent = 'Error';
                    document.getElementById('tx-value').textContent = 'Error';
                    console.error("Gagal mengambil data:", err);
                });
        }

        setInterval(fetchTraffic, 2000);
    </script>

</body>

</html>
