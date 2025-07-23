<?php
        $servername = "localhost"; 
        $username = "root"; 
        $password = ""; 
        $dbname = "monitor_db";
        
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Koneksi ke database gagal: " . $conn->connect_error);
        }

        // Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Memeriksa apakah database sudah ada
$result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");

if ($result->num_rows == 0) {
    // Database tidak ditemukan, mencoba membuatnya
    $sql = "CREATE DATABASE $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "Database berhasil dibuat.<br>";
    } else {
        die("Error membuat database: " . $conn->error);
    }
}

// Memilih database
$conn->select_db($dbname);


?>

    