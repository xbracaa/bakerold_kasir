<?php
session_start();
include("../../config/db.php");

// Ini buat ngecek kasir udah login apa belum
if (!isset($_SESSION['id_kasir'])) {
    header("Location: ../login.php");
    exit;
}

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ini query buat nampilin riwayat transaksi, udah join sama data kasir sama total item
$query = $koneksi->query("
    SELECT 
        t.id_transaksi, 
        t.kode_transaksi, 
        t.tanggal, 
        k.nama_kasir, 
        t.total,
        SUM(dt.qty) AS jumlah_total_qty_item
    FROM 
        transaksi t
    JOIN 
        kasir k ON t.id_kasir = k.id_kasir
    LEFT JOIN 
        detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    GROUP BY 
        t.id_transaksi, t.kode_transaksi, t.tanggal, k.nama_kasir, t.total
    ORDER BY 
        t.tanggal DESC
");

// ini biar kalo ada error pas ngambil data, langsung dikasih tau
if (!$query) {
    die("Error ngambil data riwayat transaksi: " . $koneksi->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Baker Old</title>

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <style>
        /* CSS buat tampilan tabel dan tombolnya */
        :root {
            --color-primary-brown: #5A3F2B;
            --color-secondary-brown: #8B6F5A;
            --color-light-brown: #D4B29A;
            --color-cream: #FFF8E1;
            --color-yellow: #FFD54F;
            --color-dark-yellow: #FFA000;
            --color-text-dark: #333;
            --color-text-light: #fff;
            --color-success: #66BB6A;
            --color-error: #EF5350;
            --color-info: #2196F3;
        }

        body {
            padding: 30px;
            background-color: var(--color-cream);
            color: var(--color-text-dark);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        h2 {
            margin-bottom: 30px;
            font-weight: bold;
            color: var(--color-primary-brown);
            text-align: center;
        }

        .table {
            background-color: var(--color-text-light);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
        }

        .table thead th {
            background-color: var(--color-primary-brown);
            color: var(--color-text-light);
            vertical-align: middle;
            text-align: center;
            border-bottom: none;
            border-top: none;
        }

        .table th, .table td {
            padding: 15px;
            border-color: var(--color-light-brown);
            border-right: none;
            border-left: none;
        }

        .table tbody tr:nth-child(odd) {
            background-color: var(--color-text-light);
        }
        .table tbody tr:nth-child(even) {
            background-color: var(--color-cream);
        }

        .table td.text-center {
            vertical-align: middle;
        }

        .btn-secondary {
            background-color: var(--color-secondary-brown);
            border-color: var(--color-secondary-brown);
            transition: background-color 0.3s ease, border-color 0.3s ease;
            margin-bottom: 20px;
        }
        .btn-secondary:hover {
            background-color: var(--color-primary-brown);
            border-color: var(--color-primary-brown);
        }

        .btn-info {
            background-color: var(--color-secondary-brown); 
            border-color: var(--color-secondary-brown);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .btn-info:hover {
            background-color: var(--color-primary-brown); 
            border-color: var(--color-primary-brown);
        }
    </style>
</head>
<body>

<div class="container">
    <a href="../home.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    <h2><i class="fas fa-history"></i> Riwayat Transaksi</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID Transaksi</th>
                    <th>Kode Transaksi</th>
                    <th>Tanggal & Waktu</th>
                    <th>Kasir</th>
                    <th>Total Belanja</th>
                    <th>Jumlah Item</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query->num_rows > 0) : ?>
                    <?php while ($row = $query->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center"><?= htmlspecialchars($row['id_transaksi']) ?></td>
                        <td><?= htmlspecialchars($row['kode_transaksi']) ?></td>
                        <td><?= date('d-m-Y H:i:s', strtotime($row['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($row['nama_kasir']) ?></td>
                        <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['jumlah_total_qty_item'] ?? 0) ?></td>
                        <td class="text-center">
                            <a href="../kasir/struk_print.php?kode=<?= urlencode($row['kode_transaksi']) ?>" class="btn btn-info btn-sm" target="_blank">Detail</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">Belum ada riwayat transaksi.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>