<?php

include 'config.php';

// Pagination setup
$limit = 20; // Tampil 20 data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Filter dan search setup
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchColumn = isset($_GET['column']) ? $_GET['column'] : 'semua';

// Validasi kolom yang diizinkan untuk mencegah SQL injection
$allowedColumns = ['semua', 'waktu', 'suhu', 'kadar_asap', 'status', 'lokasi'];
if (!in_array($searchColumn, $allowedColumns)) {
    $searchColumn = 'semua';
}

// Base query
$baseQuery = "SELECT id, waktu, suhu, kadar_asap, status, lokasi FROM tbl_data";
$countQuery = "SELECT COUNT(*) AS total FROM tbl_data";

// Tambahkan kondisi WHERE jika ada pencarian
$whereClause = "";
$params = [];
$types = "";

if (!empty($searchTerm)) {
    if ($searchColumn === 'semua') {
        // Filter untuk semua kolom dengan pencarian yang lebih sederhana dan efektif
        $whereClause = " WHERE (
            DATE_FORMAT(waktu, '%d/%m/%Y') LIKE ? OR
            DATE_FORMAT(waktu, '%d-%m-%Y') LIKE ? OR
            DATE_FORMAT(waktu, '%Y-%m-%d') LIKE ? OR
            DATE_FORMAT(waktu, '%H:%i:%s') LIKE ? OR
            DATE_FORMAT(waktu, '%d/%m/%Y %H:%i:%s') LIKE ? OR
            DATE_FORMAT(waktu, '%W, %d %M %Y %H:%i:%s') LIKE ? OR
            YEAR(waktu) LIKE ? OR
            MONTH(waktu) LIKE ? OR
            DAY(waktu) LIKE ? OR
            HOUR(waktu) LIKE ? OR
            MINUTE(waktu) LIKE ? OR
            SECOND(waktu) LIKE ? OR
            DAYNAME(waktu) LIKE ? OR
            MONTHNAME(waktu) LIKE ? OR
            CASE 
                WHEN DAYNAME(waktu) = 'Monday' THEN 'Senin'
                WHEN DAYNAME(waktu) = 'Tuesday' THEN 'Selasa'
                WHEN DAYNAME(waktu) = 'Wednesday' THEN 'Rabu'
                WHEN DAYNAME(waktu) = 'Thursday' THEN 'Kamis'
                WHEN DAYNAME(waktu) = 'Friday' THEN 'Jumat'
                WHEN DAYNAME(waktu) = 'Saturday' THEN 'Sabtu'
                WHEN DAYNAME(waktu) = 'Sunday' THEN 'Minggu'
            END LIKE ? OR
            CASE 
                WHEN MONTHNAME(waktu) = 'January' THEN 'Januari'
                WHEN MONTHNAME(waktu) = 'February' THEN 'Februari'
                WHEN MONTHNAME(waktu) = 'March' THEN 'Maret'
                WHEN MONTHNAME(waktu) = 'April' THEN 'April'
                WHEN MONTHNAME(waktu) = 'May' THEN 'Mei'
                WHEN MONTHNAME(waktu) = 'June' THEN 'Juni'
                WHEN MONTHNAME(waktu) = 'July' THEN 'Juli'
                WHEN MONTHNAME(waktu) = 'August' THEN 'Agustus'
                WHEN MONTHNAME(waktu) = 'September' THEN 'September'
                WHEN MONTHNAME(waktu) = 'October' THEN 'Oktober'
                WHEN MONTHNAME(waktu) = 'November' THEN 'November'
                WHEN MONTHNAME(waktu) = 'December' THEN 'Desember'
            END LIKE ? OR
            CAST(suhu AS CHAR) LIKE ? OR
            CAST(kadar_asap AS CHAR) LIKE ? OR
            status LIKE ? OR
            lokasi LIKE ?
        )";
        $searchPattern = '%' . $searchTerm . '%';
        $params = array_fill(0, 20, $searchPattern);
        $types = str_repeat('s', 20);
    } elseif ($searchColumn === 'waktu') {
        // Filter waktu yang lebih komprehensif
        $whereClause = " WHERE (
            DATE_FORMAT(waktu, '%d/%m/%Y') LIKE ? OR
            DATE_FORMAT(waktu, '%d-%m-%Y') LIKE ? OR
            DATE_FORMAT(waktu, '%Y-%m-%d') LIKE ? OR
            DATE_FORMAT(waktu, '%H:%i:%s') LIKE ? OR
            DATE_FORMAT(waktu, '%d/%m/%Y %H:%i:%s') LIKE ? OR
            DATE_FORMAT(waktu, '%W, %d %M %Y %H:%i:%s') LIKE ? OR
            YEAR(waktu) LIKE ? OR
            MONTH(waktu) LIKE ? OR
            DAY(waktu) LIKE ? OR
            HOUR(waktu) LIKE ? OR
            MINUTE(waktu) LIKE ? OR
            SECOND(waktu) LIKE ? OR
            DAYNAME(waktu) LIKE ? OR
            MONTHNAME(waktu) LIKE ? OR
            CASE 
                WHEN DAYNAME(waktu) = 'Monday' THEN 'Senin'
                WHEN DAYNAME(waktu) = 'Tuesday' THEN 'Selasa'
                WHEN DAYNAME(waktu) = 'Wednesday' THEN 'Rabu'
                WHEN DAYNAME(waktu) = 'Thursday' THEN 'Kamis'
                WHEN DAYNAME(waktu) = 'Friday' THEN 'Jumat'
                WHEN DAYNAME(waktu) = 'Saturday' THEN 'Sabtu'
                WHEN DAYNAME(waktu) = 'Sunday' THEN 'Minggu'
            END LIKE ? OR
            CASE 
                WHEN MONTHNAME(waktu) = 'January' THEN 'Januari'
                WHEN MONTHNAME(waktu) = 'February' THEN 'Februari'
                WHEN MONTHNAME(waktu) = 'March' THEN 'Maret'
                WHEN MONTHNAME(waktu) = 'April' THEN 'April'
                WHEN MONTHNAME(waktu) = 'May' THEN 'Mei'
                WHEN MONTHNAME(waktu) = 'June' THEN 'Juni'
                WHEN MONTHNAME(waktu) = 'July' THEN 'Juli'
                WHEN MONTHNAME(waktu) = 'August' THEN 'Agustus'
                WHEN MONTHNAME(waktu) = 'September' THEN 'September'
                WHEN MONTHNAME(waktu) = 'October' THEN 'Oktober'
                WHEN MONTHNAME(waktu) = 'November' THEN 'November'
                WHEN MONTHNAME(waktu) = 'December' THEN 'Desember'
            END LIKE ?
        )";
        $searchPattern = '%' . $searchTerm . '%';
        $params = array_fill(0, 16, $searchPattern);
        $types = str_repeat('s', 16);
    } elseif ($searchColumn === 'suhu') {
        if (is_numeric($searchTerm)) {
            $whereClause = " WHERE suhu = ?";
            $params[] = (float)$searchTerm;
            $types .= 'd';
        } else {
            $whereClause = " WHERE CAST(suhu AS CHAR) LIKE ?";
            $params[] = '%' . $searchTerm . '%';
            $types .= 's';
        }
    } elseif ($searchColumn === 'kadar_asap') {
        if (is_numeric($searchTerm)) {
            $whereClause = " WHERE kadar_asap = ?";
            $params[] = (float)$searchTerm;
            $types .= 'd';
        } else {
            $whereClause = " WHERE CAST(kadar_asap AS CHAR) LIKE ?";
            $params[] = '%' . $searchTerm . '%';
            $types .= 's';
        }
    } else {
        // Untuk status dan lokasi
        $whereClause = " WHERE $searchColumn LIKE ?";
        $params[] = '%' . $searchTerm . '%';
        $types .= 's';
    }
}

// Query untuk mengambil data dengan prepared statement
$query = $baseQuery . $whereClause . " ORDER BY waktu DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Query untuk menghitung total data yang sesuai dengan filter
$totalQuery = $countQuery . $whereClause;
$totalStmt = $conn->prepare($totalQuery);
if (!empty($searchTerm)) {
    // Bind parameter yang sama untuk count query (tanpa limit dan offset)
    $countTypes = substr($types, 0, -2);
    $countParams = array_slice($params, 0, -2);
    if (!empty($countParams)) {
        $totalStmt->bind_param($countTypes, ...$countParams);
    }
}
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$totalData = $totalRow['total'];
$totalPages = ceil($totalData / $limit);

// Fungsi untuk membangun URL dengan parameter yang sudah ada
function buildUrl($page, $column = null, $search = null)
{
    $params = [];
    $params['page'] = $page;

    if ($column !== null) {
        $params['column'] = $column;
    } elseif (isset($_GET['column'])) {
        $params['column'] = $_GET['column'];
    }

    if ($search !== null) {
        $params['search'] = $search;
    } elseif (isset($_GET['search'])) {
        $params['search'] = $_GET['search'];
    }

    return '?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" id="theme-style" href="css/light.css">
    <link rel="stylesheet" href="css/dark.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Rekap</title>
</head>

<body>
    <h1 ondblclick="toggleDarkMode()" title="mode gelap klik 2x">
        Data Rekap
    </h1>
    <div class="top-bar-container">
        <div class="top-bar">
            <a href="index.php" class="button">⬅ Kembali</a>
            <form method="GET" class="filter-group">
                <label for="filterColumn">Filter:</label>
                <select name="column" id="filterColumn">
                    <option value="semua" <?php if ($searchColumn === 'semua') echo 'selected'; ?>>Semua</option>
                    <option value="waktu" <?php if ($searchColumn === 'waktu') echo 'selected'; ?>>Waktu</option>
                    <option value="suhu" <?php if ($searchColumn === 'suhu') echo 'selected'; ?>>Suhu</option>
                    <option value="kadar_asap" <?php if ($searchColumn === 'kadar_asap') echo 'selected'; ?>>Kadar asap</option>
                    <option value="status" <?php if ($searchColumn === 'status') echo 'selected'; ?>>Status</option>
                    <option value="lokasi" <?php if ($searchColumn === 'lokasi') echo 'selected'; ?>>Lokasi</option>
                </select>
                <input type="text" name="search" id="searchInput" 
                       placeholder="<?php 
                       if($searchColumn === 'semua') {
                           echo 'Cari di semua kolom...';
                       } elseif($searchColumn === 'waktu') {
                           echo 'Cari berdasarkan tanggal, bulan, tahun, atau jam...';
                       } else {
                           echo 'Ketik untuk mencari...';
                       }
                       ?>" 
                       value="<?= htmlspecialchars($searchTerm) ?>">

                <button type="submit" class="btn-cari">Cari</button>
                <?php if (!empty($searchTerm)): ?>
                    <a href="?" class="button" style="margin-left: 10px;">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if (!empty($searchTerm)): ?>
        <div class="search-info">
            <strong>Pencarian:</strong> "<?= htmlspecialchars($searchTerm) ?>" pada kolom "<?= ucfirst(str_replace('_', ' ', $searchColumn)) ?>"
            (<?= $totalData ?> hasil ditemukan)
        </div>
    <?php endif; ?>

    <?php if ($searchColumn === 'waktu'): ?>
    <?php elseif ($searchColumn === 'semua'): ?>
    <?php endif; ?>

    <div class="table">
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Waktu</th>
                <th>Suhu</th>
                <th>Kadar asap</th>
                <th>Status</th>
                <th>Lokasi</th>
                <th>Hapus</th>
            </tr>
        </thead>
        <tbody id="dataTableBody">
            <?php
            if ($result === false) {
                die("Error: " . $conn->error);
            }

            if ($result->num_rows > 0) {
                $no = $offset + 1;
                while ($row = $result->fetch_assoc()) {
                    $suhu = $row["suhu"];
                    $kadar_asap = $row["kadar_asap"];
                    $rowClass = "";

                    if ($suhu > 32 || $kadar_asap > 500) {
                        $rowClass = "class='highlight danger-row'";
                    }

                    echo "<tr $rowClass>";
                    echo "<td>" . $no++ . "</td>";

                    $datetime = strtotime($row["waktu"]);
                    $formatted_time = date('l, d F Y  H:i:s', $datetime);

                    $search = [
                        'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday',
                        'January','February','March','April','May','June','July','August','September','October','November','December'
                    ];

                    $replace = [
                        'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu',
                        'Januari', 'Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'
                    ];

                    $formatted_time = str_replace($search, $replace, $formatted_time);

                    echo "<td>$formatted_time</td>";

                    $suhuClass = ($suhu > 32) ? "class='danger-cell'" : "";
                    $asapClass = ($kadar_asap > 500) ? "class='danger-cell'" : "";

                    echo "<td $suhuClass>" . $row["suhu"] . "°C</td>";
                    echo "<td $asapClass>" . $row["kadar_asap"] . "</td>";
                    echo "<td>" . $row["status"] . "</td>";
                    echo "<td>" . $row["lokasi"] . "</td>";
                    echo "<td>
                            <form method='POST' action='hapus_data.php' onsubmit='return confirm(\"Yakin ingin menghapus data ini?\")'>
                                <input type='hidden' name='id' value='" . $row["id"] . "'>
                                <button type='submit' class='hapus-link'>Hapus</button>
                            </form>
                          </td>";

                    echo "</tr>";
                }
            } else {
                $colspan = 7;
                if (!empty($searchTerm)) {
                    echo "<tr><td colspan='$colspan'>Tidak ada data yang sesuai dengan pencarian \"" . htmlspecialchars($searchTerm) . "\".</td></tr>";
                } else {
                    echo "<tr><td colspan='$colspan'>Tidak ada data.</td></tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>



    <!-- Pagination Button -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="<?= buildUrl($page - 1) ?>" class="pagination-link">⬅ Prev</a>
        <?php endif; ?>

        <span class="pagination-info">Halaman <?php echo $page; ?> dari <?php echo $totalPages; ?></span>

        <?php if ($page < $totalPages): ?>
            <a href="<?= buildUrl($page + 1) ?>" class="pagination-link">Next ➡</a>
        <?php endif; ?>
    </div>

    <!-- Script untuk mode -->
    <script>
        // Fungsi untuk toggle mode dan simpan status ke localStorage
        function toggleMode() {
            document.body.classList.toggle("dark-mode");
            const isDarkMode = document.body.classList.contains("dark-mode");
            localStorage.setItem("darkMode", isDarkMode ? "enabled" : "disabled");
        }

        // Fungsi untuk reset ke tampilan awal
        function resetToInitialView() {
            // Hapus semua parameter URL dan redirect ke halaman bersih
            window.location.href = window.location.pathname;
        }

        // Deteksi refresh page
        function detectPageRefresh() {
            // Cek apakah ada parameter pencarian di URL
            const urlParams = new URLSearchParams(window.location.search);
            const hasSearchParams = urlParams.has('search') || urlParams.has('column') || urlParams.has('page');
            
            // Cek apakah ini adalah refresh dengan menggunakan performance navigation
            const isRefresh = performance.navigation.type === performance.navigation.TYPE_RELOAD;
            
            // Jika refresh dan ada parameter pencarian, reset ke tampilan awal
            if (isRefresh && hasSearchParams) {
                resetToInitialView();
                return;
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            detectPageRefresh();

            // Cek localStorage dan dark mode jika perlu
            const darkModeSetting = localStorage.getItem("darkMode");
            if (darkModeSetting === "enabled") {
                document.body.classList.add("dark-mode");
            }

            // Auto-submit form saat user mengetik 
            const searchInput = document.getElementById("searchInput");
            const filterColumn = document.getElementById("filterColumn");
            let timeout;

            function submitForm() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    document.querySelector('.filter-group').submit();
                }, 2000);
            }

            if (searchInput) {
                searchInput.addEventListener("input", submitForm);
            }

            if (filterColumn) {
                filterColumn.addEventListener("change", function() {
                    // Update placeholder saat kolom berubah
                    if (filterColumn.value === 'semua') {
                        searchInput.placeholder = 'Cari di semua kolom...';
                    } else if (filterColumn.value === 'waktu') {
                        searchInput.placeholder = 'Contoh: 2024, januari, senin, 15/01, 18:39:23, 15';
                    } else {
                        searchInput.placeholder = 'Ketik untuk mencari...';
                    }
                    document.querySelector('.filter-group').submit();
                });
            }

            // Event listener untuk tombol F5 dan Ctrl+R
            document.addEventListener('keydown', function(event) {
                if (event.key === 'F5' || (event.ctrlKey && event.key === 'r')) {
                    const urlParams = new URLSearchParams(window.location.search);
                    const hasSearchParams = urlParams.has('search') || urlParams.has('column') || urlParams.has('page');
                    // Jika ada parameter pencarian, reset ke tampilan awal
                    if (hasSearchParams) {
                        event.preventDefault();
                        resetToInitialView();
                    }
                }
            });
        });
    </script>

</body>

</html>