<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$result = $conn->query("SELECT id, username, role FROM tbl_admin");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
            margin: 0;
        }
        
        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        /* Desktop table styles */
        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }
        
        th, td {
            padding: 12px 8px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }
        
        th {
            background: #007bff;
            color: white;
            font-weight: bold;
            font-size: 13px;
        }
        
        /* Mobile table styling */
        .mobile-table {
            display: none;
            overflow-x: auto;
            margin-bottom: 20px;
        }
        
        .mobile-table table {
            min-width: 100%;
            font-size: 12px;
        }
        
        .mobile-table th {
            padding: 8px 4px;
            font-size: 11px;
            white-space: nowrap;
        }
        
        .mobile-table td {
            padding: 8px 4px;
            font-size: 11px;
            word-break: break-word;
        }
        
        .mobile-table .col-id {
            width: 15%;
        }
        
        .mobile-table .col-username {
            width: 35%;
        }
        
        .mobile-table .col-role {
            width: 20%;
        }
        
        .mobile-table .col-action {
            width: 30%;
        }
        
        .mobile-table a {
            font-size: 10px;
            padding: 4px 6px;
            background: #dc3545;
            color: white;
            border-radius: 3px;
            text-decoration: none;
            display: inline-block;
        }
        
        .mobile-table a:hover {
            background: #c82333;
            text-decoration: none;
        }
        
        tr:hover {
            background-color: #f1f1f1;
        }
        
        /* Mobile table adjustments */
        .mobile-table {
            display: none;
        }
        
        a {
            color: #dc3545;
            text-decoration: none;
            font-weight: 500;
        }
        
        a:hover {
            text-decoration: underline;
        }
        
        .button {
            display: inline-block;
            padding: 12px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .button:hover {
            background-color: #5a6268;
        }
        
        /* Media queries for responsive design */
        @media screen and (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .container {
                padding: 15px;
            }
            
            h2 {
                font-size: 1.3rem;
            }
            
            /* Hide desktop table on mobile */
            .table-container {
                display: none;
            }
            
            /* Show mobile table */
            .mobile-table {
                display: block;
            }
            
            .button {
                width: 100%;
                padding: 15px;
                font-size: 16px;
            }
        }
        
        @media screen and (max-width: 480px) {
            .container {
                padding: 10px;
            }
            
            h2 {
                font-size: 1.2rem;
                margin-bottom: 15px;
            }
            
            .mobile-table table {
                font-size: 10px;
            }
            
            .mobile-table th {
                padding: 6px 2px;
                font-size: 9px;
            }
            
            .mobile-table td {
                padding: 6px 2px;
                font-size: 9px;
            }
            
            .mobile-table a {
                font-size: 8px;
                padding: 3px 4px;
            }
        }
        
        /* Status styling */
        .status-gray {
            color: #6c757d;
            font-style: italic;
            font-size: 11px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Kelola User</h2>

    <!-- Desktop Table View -->
    <div class="table-container">
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= $row['role'] ?></td>
                    <td>
                        <?php if ($_SESSION['role'] === 'admin' && $row['username'] !== $_SESSION['username']): ?>
                            <a href="hapus_data.php?id=<?= $row['id'] ?>&deleted_by=<?= urlencode($_SESSION['username']) ?>" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
                        <?php elseif ($row['username'] === $_SESSION['username']): ?>
                            <span class="status-gray">(akun Anda)</span>
                        <?php else: ?>
                            <span class="status-gray">(tidak bisa hapus)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Mobile Table View -->
    <div class="mobile-table">
        <table>
            <tr>
                <th class="col-id">ID</th>
                <th class="col-username">Username</th>
                <th class="col-role">Role</th>
                <th class="col-action">Aksi</th>
            </tr>
            <?php 
            // Reset result pointer for mobile table
            $result = $conn->query("SELECT id, username, role FROM tbl_admin");
            while ($row = $result->fetch_assoc()): 
            ?>
                <tr>
                    <td class="col-id"><?= $row['id'] ?></td>
                    <td class="col-username"><?= htmlspecialchars($row['username']) ?></td>
                    <td class="col-role"><?= $row['role'] ?></td>
                    <td class="col-action">
                        <?php if ($_SESSION['role'] === 'admin' && $row['username'] !== $_SESSION['username']): ?>
                            <a href="hapus_data.php?id=<?= $row['id'] ?>&deleted_by=<?= urlencode($_SESSION['username']) ?>" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
                        <?php elseif ($row['username'] === $_SESSION['username']): ?>
                            <span class="status-gray">Anda</span>
                        <?php else: ?>
                            <span class="status-gray">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <button onclick="window.location.href='index.php'" class="button">Kembali ke Dashboard</button>
</div>

</body>
</html>

<?php $conn->close(); ?>