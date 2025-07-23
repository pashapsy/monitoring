// Tema gelap dan terang untuk halaman web
function toggleMode() {
    const themeStyle = document.getElementById('theme-style');
    if (themeStyle.getAttribute('href') === 'css/light.css') {
        themeStyle.setAttribute('href', 'css/dark.css');
    } else {
        themeStyle.setAttribute('href', 'css/light.css');
    }
}

// Function untuk memperbarui status sensor
function updateStatusSensor() {
    $.ajax({
        url: 'get_status_sensor.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            var statusSensor = response.status_sensor;
            var waktuTerakhir = response.waktu_terakhir;

            var statusSensorElement = document.getElementById("status-sensor");
            var waktuElement = document.getElementById("waktu-terakhir");

            if (statusSensorElement) {
                statusSensorElement.textContent = (statusSensor === "ON") ? "Sensor Hidup" : "Sensor Mati";
                statusSensorElement.classList.toggle("sensor-on", statusSensor === "ON");
                statusSensorElement.classList.toggle("sensor-off", statusSensor === "OFF");
            }

            if (waktuElement) {
                waktuElement.textContent = waktuTerakhir;
            }
        }
    });


    $.ajax({
        url: 'get_status_sensor.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            var statusSensor = response.status_sensor;
            // Update status sensor
            var statusSensorElement = document.getElementById("status-sensor");
            if (statusSensorElement) {
                statusSensorElement.textContent = statusSensor;
            }
        }
    });
}

// Function untuk memuat data dari database
function loadDataFromDatabase() {
    $.ajax({
        url: 'get_status_sensor.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            var waktuTerakhir = response.waktu_terakhir;
            var statusSensor = response.status_sensor;

            var waktuElement = document.getElementById("waktu-terakhir");
            var statusElement = document.getElementById("status-sensor");

            if (waktuElement) {
                waktuElement.textContent = waktuTerakhir;
            }

            if (statusElement) {
                if (statusSensor === "OFF") {
                    statusElement.textContent = "Sensor Mati";
                    statusElement.classList.remove("sensor-on");
                    statusElement.classList.add("sensor-off");
                } else {
                    statusElement.textContent = "Sensor Hidup";
                    statusElement.classList.remove("sensor-off");
                    statusElement.classList.add("sensor-on");
                }
            }
        }
    });
}


// Alat ukur suhu dan asap
$(document).ready(function () {
    var suhuMeter = new JustGage({
        id: "suhu",
        value: suhuValue, // Variable suhuValue harus didefinisikan sebelumnya
        min: 0,
        max: 100,
        title: "Suhu",
        label: "°C",
        gaugeWidthScale: 0.8,
        levelColors: ['#0000FF'],
        counter: true
    });

    var asapMeter = new JustGage({
        id: "asap",
        value: asapValue, // Variable asapValue harus didefinisikan sebelumnya
        min: 0,
        max: 1000,
        title: "Kadar Asap",
        label: "ppm",
        gaugeWidthScale: 0.8,
        levelColors: ['#00ff00'],
        counter: true
    });

    // Memuat data awal dari database
    loadDataFromDatabase();
});

// Function untuk mengirim pesan ke Telegram
const TELEGRAM_BOT_TOKEN = "8144240581:AAF-0aaErL-en0FA9_m40s_bOzlWWLgk_kU";
const TELEGRAM_CHAT_ID = "-4795936771";

function sendTelegramMessage(message) {
    const url = `https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage`;

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            chat_id: TELEGRAM_CHAT_ID,
            text: message,
            parse_mode: "Markdown"
        })
    })
        .then(response => response.json())
        .then(data => {
            if (!data.ok) {
                console.error("Gagal kirim pesan Telegram:", data.description);
            }
        })
        .catch(error => console.error("Error:", error));
}

// Fungsi untuk kirim peringatan suhu
function sendTemperatureWarning(suhuData) {
    if (!suhuData || suhuData.length === 0) return;

    let message = "⚠️ *PERINGATAN! Suhu Berbahaya!* ⚠️\n\n";
    suhuData.forEach((data, index) => {
        message += `${index + 1}. 🌡 Suhu: ${data.suhu}°C\n🕒 Waktu: ${data.waktu}\n\n`;
    });
    message += "Mohon segera ditindaklanjuti! 🚨";

    sendTelegramMessage(message);
}

// Fungsi untuk kirim peringatan asap
function sendSmokeWarning(asapData) {
    if (!asapData || asapData.length === 0) return;

    let message = "⚠️ *PERINGATAN DARURAT!* ⚠️\n\n🚨 *Kadar Asap Berbahaya!* 🚨\n";
    message += "Mohon segera lakukan tindakan pencegahan!\n\n";

    const uniqueAsapLevels = new Set();

    asapData.forEach((data) => {
        if (data.kadar_asap > 400 && !uniqueAsapLevels.has(data.kadar_asap)) {
            uniqueAsapLevels.add(data.kadar_asap);
            message += `💨 Kadar Asap: ${data.kadar_asap} ppm\n`;
            message += `🕒 Waktu: ${data.waktu}\n`;
            message += "🚨 *BAHAYA! SEGERA AMANKAN AREA!* 🚨\n\n";
        }
    });

    if (uniqueAsapLevels.size > 0) {
        message += "‼️ *Segera ambil tindakan yang diperlukan!* ‼️";
        sendTelegramMessage(message);
    }
}

// Buat chart suhu dan asap
function initChart(data) {
    const labels = data.map(e => e.waktu);
    const suhuData = data.map(e => e.suhu);
    const asapData = data.map(e => e.kadar_asap);

    const ctx = document.getElementById('dataChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Suhu (°C)',
                    data: suhuData,
                    borderColor: 'rgb(250, 14, 65)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: false
                },
                {
                    label: 'Kadar Asap (ppm)',
                    data: asapData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Waktu'
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Kadar Asap'
                    }
                }]
            }
        }
    });
}

// Jalankan saat dokumen siap
document.addEventListener("DOMContentLoaded", function () {
    if (typeof chartData !== "undefined") {
        initChart(chartData);
    }
});




// Perbarui tabel
var dataTableBody = document.getElementById("dataTableBody");
if (dataTableBody) {
    dataTableBody.innerHTML = ""; // Kosongkan tabel sebelum isi ulang

    for (var i = 0; i < labels.length; i++) {
        var row = document.createElement("tr");

        // Kolom Waktu
        var waktuCell = document.createElement("td");
        waktuCell.textContent = labels[i];
        row.appendChild(waktuCell);

        // Kolom Suhu
        var suhuCell = document.createElement("td");
        suhuCell.textContent = suhuData[i] + "°C";
        row.appendChild(suhuCell);

        // Kolom Asap
        var asapCell = document.createElement("td");
        asapCell.textContent = kadar_asapData[i] + " ppm";
        row.appendChild(asapCell);

        // Kolom Status
        var statusCell = document.createElement("td");
        var status = "Aman";

        if (suhuData[i] > 34 || kadar_asapData[i] > 500) { // Cek batas
            status = "Bahaya";

            // Ubah warna baris menjadi merah
            row.style.backgroundColor = "red";
            row.style.color = "white";
        }

        statusCell.textContent = status;
        row.appendChild(statusCell);

        // Tambahkan baris ke body tabel
        dataTableBody.appendChild(row);
    }
}


// Kirim notifikasi jika suhu atau kadar asap melebihi batas
const suhuPeringatan = [];
const asapPeringatan = [];

for (var i = 0; i < labels.length; i++) {
    if (suhuData[i] > 34) {
        suhuPeringatan.push({
            suhu: suhuData[i],
            waktu: labels[i]
        });
    }

    if (kadar_asapData[i] > 500) {
        asapPeringatan.push({
            kadar_asap: kadar_asapData[i],
            waktu: labels[i]
        });
    }
}

if (suhuPeringatan.length > 0) {
    sendTemperatureWarning(suhuPeringatan);
}

if (asapPeringatan.length > 0) {
    sendSmokeWarning(asapPeringatan);
}


// Mengambil dan memperbarui data terbaru
function updateLatestData() {
    $.ajax({
        url: "data.php",
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (document.getElementById("latestsuhu")) {
                document.getElementById("latestsuhu").textContent = "Suhu: " + data.latestsuhu + "°C";
            }

            if (document.getElementById("latestkadar_asap")) {
                document.getElementById("latestkadar_asap").textContent = "Kadar Asap: " + data.latestkadar_asap + " ppm";
            }

            // Update gauges
            if (window.suhuMeter && data.latestsuhu) {
                window.suhuMeter.refresh(data.latestsuhu);
            }

            if (window.asapMeter && data.latestkadar_asap) {
                window.asapMeter.refresh(data.latestkadar_asap);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error updating latest data:", error);
        }
    });
}

// Fetch data and update chart and table
function fetchDataAndUpdate() {
    $.ajax({
        url: "data.php",
        type: "GET",
        dataType: "json",
        success: function (data) {
            updateChartAndTable(data);
        }
    });
}

// Login form interaction
document.addEventListener("DOMContentLoaded", function () {
    const inputs = document.querySelectorAll("input");

    inputs.forEach(input => {
        input.addEventListener("focus", function () {
            this.style.borderColor = "#3498db";
        });

        input.addEventListener("blur", function () {
            this.style.borderColor = "#ccc";
        });
    });
});

// Toggle dropdown akun
function toggleDropdown() {
    const dropdown = document.getElementById("accountDropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

// Tutup dropdown jika klik di luar
window.addEventListener("click", function (event) {
    const dropdown = document.getElementById("accountDropdown");
    if (!event.target.closest(".account-container")) {
        dropdown.style.display = "none";
    }
});


function toggleFilter() {
        var container = document.getElementById('lokasiFilterContainer');
        container.style.display = (container.style.display === 'none') ? 'block' : 'none';
        
    }

    // Untuk menutup dropdown saat klik di luar
    document.addEventListener('click', function(event) {
        var filterButton = event.target.closest('button');
        var container = document.getElementById('lokasiFilterContainer');
        if (!event.target.closest('#lokasiFilterContainer') && !filterButton) {
            container.style.display = 'none';
        }
    });

    document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("searchInput");
    const filterColumn = document.getElementById("filterColumn");
    const tableBody = document.querySelector("#dataTableBody");

    const filterTable = () => {
        const searchTerm = searchInput.value.trim().toLowerCase();
        const colIndex = parseInt(filterColumn.value);

        if (!tableBody) return;

        // Loop tiap baris di tbody
        [...tableBody.rows].forEach(row => {
            const cell = row.cells[colIndex];
            if (!cell) {
                row.style.display = "none";
                return;
            }

            const text = cell.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? "" : "none";
        });
    };

    searchInput.addEventListener("input", filterTable);
    filterColumn.addEventListener("change", filterTable);
});




