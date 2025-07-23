<?php
session_start();

// Cek apakah sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Monitoring Suhu dan Kadar Asap pada Ruang Data Center</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" id="theme-style" href="css/light.css">
    <link rel="stylesheet" href="css/dark.css">

    <!-- Library eksternal -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/raphael@2.1.4/raphael-min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/justgage@1.3.0/justgage.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Script utama -->
    <script src="js/script.js"></script>
</head>

<body>
    <script>
        function toggleDarkMode() {
            document.body.classList.toggle("dark-mode");
            localStorage.setItem("darkMode", document.body.classList.contains("dark-mode") ? "enabled" : "disabled");
        }

        document.addEventListener("DOMContentLoaded", function() {
            if (localStorage.getItem("darkMode") === "enabled") {
                document.body.classList.add("dark-mode");
            }
        });
    </script>

    <!-- Bungkus luar agar tetap di pojok kanan atas -->
   <div class="top-right-account">
        <div class="account-container" onclick="toggleDropdown()">
            <div class="account-icon">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            <div class="dropdown" id="accountDropdown">
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <!-- Judul -->
   <h1 ondblclick="toggleDarkMode()" class="clickable-title" title="mode gelap klik 2x">
        Sistem Pemantauan Suhu dan Kadar Asap
        <?php
        if (isset($_GET['lokasi']) && $_GET['lokasi'] != '') {
            echo " pada " . htmlspecialchars($_GET['lokasi']);
        } else {
            echo " pada Ruangan";
        }
        ?>
    </h1>

    <h2 style="text-align: center;">
        <a href="https://www.google.com/maps/place/Daerah+Istimewa+Yogyakarta" target="_blank">DIY</a>
    </h2>

   <!-- Alat ukur -->
<div class="container mt-4">
    <?php
    include 'config.php';
    $query = "SELECT waktu, suhu, kadar_asap, status, lokasi FROM tbl_data ORDER BY waktu DESC LIMIT 1";
    $result = $conn->query($query);

    $last_temp = 0;
    $last_asap = 0;
    $last_status = "Tidak tersedia";
    $last_lokasi = "Tidak diketahui";
    $last_waktu = "-";

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_temp = $row["suhu"];
        $last_asap = $row["kadar_asap"];
        $last_status = $row["status"];
        $last_lokasi = $row["lokasi"];
        $last_waktu = $row["waktu"];
    } else {
        echo "<p class='text-center'>Data tidak tersedia.</p>";
    }
    $conn->close();
    ?>

    <!-- Flex Container untuk alat ukur -->
<div class="instrument-wrapper">
    <!-- Suhu -->
    <div class="instrument-card text-center suhu-card">
        <h4>Suhu (&deg;C)</h4>
        <div id="suhu" class="gauge"></div>
        <div id="suhu-label" class="mt-2"></div>
    </div>

    <!-- Asap -->
    <div class="instrument-card text-center asap-card">
        <h4>Kadar Asap (ppm)</h4>
        <div id="asap" class="gauge"></div>
        <div id="asap-label" class="mt-2"></div>
    </div>
</div>

    <!-- Status Informasi -->
        <div class="status-info">
            Waktu: 
            <?php
            if ($last_waktu && $last_waktu !== "-") {
                $dt = new DateTime($last_waktu);
                echo $dt->format('d-m-Y H:i:s');
            } else {
                echo "-";
            }
            ?> |
            Lokasi: <?= htmlspecialchars($last_lokasi) ?> |
            Status:
            <span class="status-text <?= ($last_status === 'Bahaya') ? 'bahaya' : '' ?>">
            <?= htmlspecialchars($last_status) ?>
            </span>
        </div>
</div>
    <!-- Chart dan Tabel -->
    <div class="layout-wrapper">
        <!-- Chart -->
        <div class="container chart-container">
            <canvas id="dataChart" ></canvas>
        </div>

        <!-- Tabel data -->
       <div class="container table-container">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Waktu</th>
                        <th>Suhu</th>
                        <th>Kadar Asap</th>
                        <th>Status</th>
                        <th class="lokasi-th">
                            Lokasi
                            <button type="button" onclick="toggleFilter()" class="filter-toggle-btn">&#9660;</button>
                            <div id="lokasiFilterContainer" class="lokasi-filter-container">
                                <form method="GET" id="lokasiFilterForm">
                                    <select
                                        name="lokasi" id="lokasi" onchange="document.getElementById('lokasiFilterForm').submit()" class="lokasi-select">
                                        <option value="">Semua</option>
                                        <option value="Ruang IT" <?= (isset($_GET['lokasi']) && $_GET['lokasi'] == 'Ruang IT') ? 'selected' : '' ?>>Ruang IT</option>
                                        <option value="Ruang Data Center" <?= (isset($_GET['lokasi']) && $_GET['lokasi'] == 'Ruang Data Center') ? 'selected' : '' ?>>Ruang Data Center</option>
                                        <option value="Ruang Server" <?= (isset($_GET['lokasi']) && $_GET['lokasi'] == 'Ruang Server') ? 'selected' : '' ?>>Ruang Server</option>
                                    </select>
                                </form>
                            </div>
                        </th>
                    </tr>

                </thead>
                <tbody id="dataTableBody">
                    <?php
                    include 'config.php';

                    // Fungsi format tanggal Indonesia
                    function formatTanggalIndo($datetime)
                    {
                        $hari = [
                            'Sunday' => 'Minggu',
                            'Monday' => 'Senin',
                            'Tuesday' => 'Selasa',
                            'Wednesday' => 'Rabu',
                            'Thursday' => 'Kamis',
                            'Friday' => 'Jumat',
                            'Saturday' => 'Sabtu'
                        ];
                        $bulan = [
                            '01' => 'Januari',
                            '02' => 'Februari',
                            '03' => 'Maret',
                            '04' => 'April',
                            '05' => 'Mei',
                            '06' => 'Juni',
                            '07' => 'Juli',
                            '08' => 'Agustus',
                            '09' => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Desember'
                        ];
                        $dateObj = new DateTime($datetime);
                        $hariStr = $hari[$dateObj->format('l')];
                        $tanggal = $dateObj->format('d');
                        $bulanStr = $bulan[$dateObj->format('m')];
                        $tahun = $dateObj->format('Y');
                        $jam = $dateObj->format('H:i:s');
                        return "$hariStr, $tanggal $bulanStr $tahun $jam";
                    }

                    // Ambil filter
                    $lokasiFilter = isset($_GET['lokasi']) ? $_GET['lokasi'] : '';

                    // Query
                    if ($lokasiFilter !== '') {
                        $stmt = $conn->prepare("SELECT waktu, suhu, kadar_asap, status, lokasi FROM tbl_data WHERE lokasi = ? ORDER BY waktu DESC LIMIT 10");
                        $stmt->bind_param("s", $lokasiFilter);
                    } else {
                        $stmt = $conn->prepare("SELECT waktu, suhu, kadar_asap, status, lokasi FROM tbl_data ORDER BY waktu DESC LIMIT 10");
                    }

                    $stmt->execute();
                    $result = $stmt->get_result();

                    $data = array();
                    if ($result && $result->num_rows > 0) {
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            $highlightClass = ($row['suhu'] > 34 || $row['kadar_asap'] > 500) ? 'highlight' : '';
                            $formattedWaktu = formatTanggalIndo($row['waktu']);

                            echo "<tr class='{$highlightClass}'>
                            <td>{$no}</td>
                            <td>{$formattedWaktu}</td>
                            <td>{$row['suhu']}°C</td>
                            <td>{$row['kadar_asap']}</td>
                            <td>{$row['status']}</td>
                            <td>{$row['lokasi']}</td>
                          </tr>";
                            $data[] = $row;
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='6'>Tidak ada data.</td></tr>";
                    }

                    $stmt->close();
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Data chart -->
    <script>
        var chartData = <?php echo json_encode($data); ?>;
    </script>

    <!-- Status sensor -->
    <?php
    date_default_timezone_set('Asia/Jakarta');
    include 'config.php';

    $sql = "SELECT MAX(waktu) AS waktu_terakhir FROM tbl_data";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $waktuTerakhir = strtotime($row["waktu_terakhir"]); // Konversi ke UNIX timestamp
    } else {
        $waktuTerakhir = 0;
    }

    $conn->close();

    // Waktu sekarang
    $waktuSekarang = time();

    // Selisih waktu dalam detik
    $selisihWaktu = $waktuSekarang - $waktuTerakhir;

    // Tentukan status sensor
    if ($selisihWaktu > 30) {
        $statusSensor = "OFF";
    } else {
        $statusSensor = "ON";
    }
    ?>

  <div class="status-container">
    <div class="status-buttons">
        <button class="button" onclick="window.location.href='rekap.php'">Data Rekap</button>

        
    </div>

    <div id="status-box">
            <?php
            echo "Terakhir Terhubung: " . date("d-m-Y  H:i:s ", $waktuTerakhir); // Tampilkan waktu terakhir
            ?>

            <?php
            // Tampilkan pemberitahuan berdasarkan status sensor
            if ($statusSensor === "OFF") {
                echo "<br><span style='color: red;'>Sensor Mati</span>";
            } else {
                echo "<br><span style='color: green;'>Sensor Hidup</span>";
            }
            ?>
        </div>
        
        <span id="status-sensor"></span>

</div>



    <!-- Variabel suhu dan asap ke JS -->
    <script>
        var suhuValue = <?php echo json_encode($last_temp); ?>;
        var asapValue = <?php echo json_encode($last_asap); ?>;
    </script>



</body>

</html>