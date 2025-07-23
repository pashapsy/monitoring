<?php
session_start();

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "monitor_db");

// Periksa koneksi database
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Cek jika form dikirimkan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi input tidak boleh kosong
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Semua kolom harus diisi!";
        header("Location: register.php");
        exit();
    }

    // Cek apakah username sudah ada
    $check_user = $conn->prepare("SELECT id FROM tbl_admin WHERE username = ?");
    $check_user->bind_param("s", $username);
    $check_user->execute();
    $check_user->store_result();

    if ($check_user->num_rows > 0) {
        $_SESSION['error'] = "Username sudah digunakan.";
        header("Location: register.php");
        exit();
    }

    $check_user->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Simpan data
    $stmt = $conn->prepare("INSERT INTO tbl_admin (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);


    if ($stmt->execute()) {
        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
        $stmt->close();
        $conn->close();
        header("Location: register.php");
        exit();
    } else {
        $_SESSION['error'] = "Terjadi kesalahan saat menyimpan data.";
        header("Location: register.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #bbdefb, #64b5f6);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 15px;
            /* Tambahan padding untuk layar kecil */
            margin: 0;
        }

        .register-form {
            width: 100%;
            max-width: 420px;
            padding: 30px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            box-sizing: border-box;
        }

        .form-control {
            border-radius: 5px;
            font-size: 16px;
            padding: 12px;
        }

        .btn {
            background: #1e88e5;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            color: white;
            font-size: 16px;
            padding: 12px;
        }

        .btn:hover {
            background: #1565c0;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .register-form {
                padding: 20px;
            }

            h3 {
                font-size: 20px;
            }

            .form-control {
                font-size: 14px;
                padding: 10px;
            }

            .btn {
                font-size: 14px;
                padding: 10px;
            }

            .register-form p {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="register-form">
        <h3>Register</h3>

        <?php
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        } elseif (isset($_SESSION['success'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
            unset($_SESSION['success']);
        }
        ?>

        <form action="register.php" method="post">
            <input type="text" class="form-control mb-3" name="username" placeholder="Username" required>
            <input type="password" class="form-control mb-3" name="password" placeholder="Password" required>
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>

        <p class="mt-3">Sudah punya akun? <a href="login.php">Login</a></p>
    </div>
</body>

</html>