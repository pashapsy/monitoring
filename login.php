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
    // Bersihkan dan validasi input
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi input tidak boleh kosong
    if (empty($username) || empty($password)) {
        header("Location: login.php");
        exit();
    }

    // Siapkan statement untuk mencegah SQL Injection
    $stmt = $conn->prepare("SELECT username, password FROM tbl_admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Jika username ditemukan
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Periksa apakah password cocok
        if (password_verify($password, $user['password'])) { 
            // Regenerasi session ID untuk keamanan
            session_regenerate_id(true);

            // Simpan sesi pengguna
            $_SESSION['username'] = $user['username'];
            $_SESSION['login'] = true;

            // Tutup statement dan koneksi
            $stmt->close();
            $conn->close();

            // Redirect ke halaman utama setelah berhasil login
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Password salah! Silakan coba lagi.";
        }
    } else {
        $_SESSION['error'] = "Username tidak ditemukan!";
    }

    // Tutup statement dan koneksi
    $stmt->close();
    $conn->close();

    // Redirect kembali ke login.php jika gagal login
    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
<style>
body {
    background: linear-gradient(135deg, #e0f7fa, #64b5f6);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    padding: 15px; /* supaya tidak mepet di layar kecil */
    margin: 0;
}

.login-form {
    width: 100%;
    max-width: 450px;
    padding: 30px;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    text-align: center;
    box-sizing: border-box;
}

.avatar {
    width: 100px;
    height: 100px;
    margin: -50px auto 15px;
    border-radius: 50%;
    background: #42a5f5;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.avatar img {
    width: 100%;
}

.form-control {
    border-radius: 5px;
    border: 1px solid #ddd;
}

.btn {
    background: #1e88e5;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    color: white;
}

.btn:hover {
    background: #1565c0;
}

small a {
    color: #1e88e5;
}

small a:hover {
    text-decoration: underline;
}

/* RESPONSIVE */
@media (max-width: 576px) {
    .login-form {
        padding: 20px;
    }

    .avatar {
        width: 80px;
        height: 80px;
        margin-top: -40px;
    }

    .avatar img {
        width: 60%;
    }

    h3 {
        font-size: 20px;
    }

    .btn {
        font-size: 14px;
        padding: 10px;
    }

    .form-control {
        font-size: 16px;
        padding: 10px;
    }

    .login-form p {
        font-size: 14px;
    }
}

</style>
</head>
<body>
<div class="login-form">
    <div class="avatar">
        <img src="img/foto.png" alt="Avatar">
    </div>
    <h3>Login Sistem Pemantauan Suhu dan Asap </h3>
    <?php
    // Tampilkan pesan error jika ada
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger text-center">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    ?>
    <form action="login.php" method="post">
        <input type="text" class="form-control mb-3" name="username" placeholder="Username" required>
        <input type="password" class="form-control mb-3" name="password" placeholder="Password" required>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
    <p class="mt-3">Belum punya akun? <a href="register.php">Buat akun</a></p>
</div>
</body>
</html>
