// var dataChart;

// function updateChartAndTable(data) {
//     var labels = data.labels;
//     var suhuData = data.suhuData;
//     var kadar_asapData = data.kadar_asapData;
//     var suhuBerbahaya = [];

//     // Periksa apakah grafik sudah ada, jika ada perbarui datanya
//     if (dataChart) {
//         dataChart.data.labels = labels;
//         dataChart.data.datasets[0].data = suhuData;
//         dataChart.data.datasets[1].data = kadar_asapData;
//         dataChart.update();
//     } else {
//         var ctx = document.getElementById("dataChart").getContext("2d");
//         dataChart = new Chart(ctx, {
//             type: 'line',
//             data: {
//                 labels: labels,
//                 datasets: [
//                     {
//                         label: "(°C)",
//                         data: suhuData,
//                         borderColor: 'rgba(75, 192, 192, 1)',
//                         borderWidth: 2,
//                         fill: false
//                     },
//                     {
//                         label: "Kadar Asap (ppm)",
//                         data: kadar_asapData,
//                         borderColor: 'rgba(255, 99, 132, 1)',
//                         borderWidth: 2,
//                         fill: false
//                     }
//                 ]
//             },
//             options: {
//                 responsive: true,
//                 scales: {
//                     x: {
//                         type: 'category'
//                     }
//                 }
//             }
//         });
//     }

//     var dataTableBody = document.getElementById("dataTableBody");
//     dataTableBody.innerHTML = "";

//     for (var i = 0; i < labels.length; i++) {
//         var row = document.createElement("tr");
//         var waktuCell = document.createElement("td");
//         var suhuCell = document.createElement("td");
//         var asapCell = document.createElement("td");

//         waktuCell.textContent = labels[i];
//         suhuCell.textContent = suhuData[i];
//         asapCell.textContent = kadar_asapData[i];

//         if (suhuData[i] > 33) {
//             row.style.backgroundColor = "red";
//             row.style.color = "white";
//             suhuBerbahaya.push({ suhu: suhuData[i], waktu: labels[i] });
//         }

//         row.appendChild(waktuCell);
//         row.appendChild(suhuCell);
//         row.appendChild(asapCell);
//         dataTableBody.appendChild(row);
//     }

//     if (suhuBerbahaya.length > 0) {
//         sendWhatsAppMessage(suhuBerbahaya);
//     }
// }

// function fetchDataAndUpdate() {
//     $.ajax({
//         url: "/data.php",
//         type: "GET",
//         dataType: "json",
//         success: function (data) {
//             updateChartAndTable(data);
//         },
//         error: function () {
//             console.error("Gagal mengambil data dari server");
//         }
//     });
// }

// function updateLatestData() {
//     $.ajax({
//         url: "/data.php",
//         type: "GET",
//         dataType: "json",
//         success: function (data) {
//             document.getElementById("latestsuhu").textContent = "Suhu: " + data.latestsuhu + "°C";
//             document.getElementById("latestkadar_asap").textContent = "Kadar Asap: " + data.latestkadar_asap + "ppm";
//         },
//         error: function () {
//             console.error("Gagal memperbarui data terbaru");
//         }
//     });
// }

// function updateStatusSensor() {
//     $.ajax({
//         url: "/get_status_sensor.php",
//         type: "GET",
//         dataType: "json",
//         success: function (response) {
//             document.getElementById("status-sensor").textContent = response.status_sensor;
//         },
//         error: function () {
//             console.error("Gagal mengambil status sensor");
//         }
//     });
// }

// // Fetch initial data and update the chart and table
// fetchDataAndUpdate();

// // Fetch the initial status of the sensor
// updateStatusSensor();


// // Set an interval to periodically update the latest data (e.g., latest temperature and smoke level) every 20 seconds
// setInterval(updateLatestData, 20000);

// // Set an interval to periodically update the sensor status every 1 second
// setInterval(updateStatusSensor, 1000);
