<?php
session_start();
include 'config.php';

// Cek jika metode request POST dan ada ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM tbl_data WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: rekap.php?msg=deleted");
        exit();
    } else {
        echo "Gagal menghapus data.";
    }
    $stmt->close();
} else {
    echo "Permintaan tidak valid.";
}

$conn->close();
?>
