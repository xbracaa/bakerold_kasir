<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['id_kasir'])) {
    header("Location: ../login.php");
    exit;
}

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$pesan_sukses = $_SESSION['pesan_sukses'] ?? '';
$pesan_error = $_SESSION['pesan_error'] ?? '';
unset($_SESSION['pesan_sukses']);
unset($_SESSION['pesan_error']);

function hapusProduk($koneksi, $id_produk) {
    $stmt = $koneksi->prepare("DELETE FROM produk WHERE id_produk = ?");
    if (!$stmt) {
        $_SESSION['pesan_error'] = "Gagal prepare hapus: " . $koneksi->error;
        return false;
    }
    $stmt->bind_param("i", $id_produk);
    if ($stmt->execute()) {
        $_SESSION['pesan_sukses'] = "Produk berhasil dihapus!";
        return true;
    } else {
        $_SESSION['pesan_error'] = "Gagal menghapus produk: " . $stmt->error;
        return false;
    }
    $stmt->close();
}

if (isset($_GET['action']) && $_GET['action'] === 'hapus') {
    $id_produk = (int)($_GET['id'] ?? 0);
    if ($id_produk > 0) {
        hapusProduk($koneksi, $id_produk);
    } else {
        $_SESSION['pesan_error'] = "ID produk tidak valid untuk dihapus.";
    }
    header("Location: produk.php");
    exit;
}

$kategori_terpilih = $_GET['kategori'] ?? 'Semua';

$kategori_q = $koneksi->query("SELECT DISTINCT kategori FROM produk ORDER BY kategori ASC");
if (!$kategori_q) {
    die("Error mengambil data kategori: " . $koneksi->error);
}
$daftar_kategori = ['Semua'];
while ($row = $kategori_q->fetch_assoc()) {
    $daftar_kategori[] = htmlspecialchars($row['kategori']);
}

$sql_produk = "SELECT id_produk, nama_produk, harga, kategori FROM produk";
if ($kategori_terpilih !== 'Semua') {
    $kategori_terpilih_sql = $koneksi->real_escape_string($kategori_terpilih);
    $sql_produk .= " WHERE kategori = '$kategori_terpilih_sql'";
}
$sql_produk .= " ORDER BY nama_produk ASC";
$produk_q = $koneksi->query($sql_produk);

if (!$produk_q) {
    die("Error mengambil data produk: " . $koneksi->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Produk - Baker Old</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
            --color-delete: #DC3545;
            --color-edit: #FFC107;
            --color-add: #28A745;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background-color: var(--color-cream);
            color: var(--color-text-dark);
            margin: 0;
            position: relative;
        }

        h2 {
            margin-bottom: 20px;
            color: var(--color-primary-brown);
            text-align: center;
            font-size: 2em;
            font-family: 'Georgia', serif;
        }

        .btn-kembali {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: var(--color-secondary-brown);
            color: var(--color-text-light);
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 7px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .btn-kembali:hover {
            background-color: var(--color-primary-brown);
        }

        .category-nav {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--color-light-brown);
            overflow-x: auto;
            white-space: nowrap;
        }
        .category-nav a {
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            color: var(--color-primary-brown);
            background-color: var(--color-light-brown);
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
            flex-shrink: 0;
        }
        .category-nav a:hover {
            background-color: var(--color-secondary-brown);
            color: var(--color-text-light);
        }
        .category-nav a.active {
            background-color: var(--color-yellow);
            color: var(--color-primary-brown);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            pointer-events: none;
        }

        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .notification.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .notification.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .add-product-btn-wrapper {
            margin-bottom: 20px;
            padding-left: 20px;
        }
        .add-product-btn {
            background-color: var(--color-secondary-brown);
            color: var(--color-text-light);
            padding: 10px 20px;
            border: none;
            border-radius: 7px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .add-product-btn:hover {
            background-color: var(--color-primary-brown);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--color-text-light);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        table thead {
            background-color: var(--color-primary-brown);
            color: var(--color-text-light);
        }

        table th, table td {
            border: 1px solid var(--color-light-brown);
            padding: 12px;
            text-align: left;
        }

        table th {
            font-weight: bold;
            font-size: 1.1em;
        }

        table tbody tr:nth-child(even) {
            background-color: var(--color-cream);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .action-buttons .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
            text-decoration: none;
            color: white;
        }
        .action-buttons .btn-edit {
            background-color: var(--color-edit);
        }
        .action-buttons .btn-edit:hover {
            background-color: #E0A800;
        }
        .action-buttons .btn-delete {
            background-color: var(--color-delete);
        }
        .action-buttons .btn-delete:hover {
            background-color: #C82333;
        }

        @media screen and (max-width: 768px) {
            .category-nav { justify-content: flex-start; }
            table, thead, tbody, th, td, tr { display: block; }
            thead tr {
                position: absolute; top: -9999px; left: -9999px;
            }
            tr { border: 1px solid var(--color-light-brown); margin-bottom: 10px; border-radius: 8px; }
            td {
                border: none; border-bottom: 1px solid var(--color-light-brown);
                position: relative; padding-left: 50%; text-align: right;
            }
            td:before {
                position: absolute; top: 6px; left: 6px; width: 45%;
                padding-right: 10px; white-space: nowrap; content: attr(data-label);
                font-weight: bold; text-align: left; color: var(--color-secondary-brown);
            }
            td:last-child { border-bottom: none; }
            .action-buttons { justify-content: flex-end; }
        }
    </style>
</head>
<body>

    <a href="../home.php" class="btn-kembali">← Kembali</a>

    <h2>Data Produk</h2>

    <?php
    if ($pesan_sukses) : ?>
        <div class='notification success'><?= htmlspecialchars($pesan_sukses) ?></div>
    <?php endif;
    if ($pesan_error) : ?>
        <div class='notification error'><?= htmlspecialchars($pesan_error) ?></div>
    <?php endif; ?>

    <div class="add-product-btn-wrapper">
        <a href="produk_tambah.php" class="add-product-btn">
            <i class="fas fa-plus-circle"></i> Tambah Produk Baru
        </a>
    </div>

    <div class="category-nav">
        <?php foreach ($daftar_kategori as $cat) : 
            $active_class = ($cat == $kategori_terpilih) ? 'active' : '';
            $category_link = "produk.php";
            if ($cat !== 'Semua') {
                $category_link .= "?kategori=" . urlencode($cat);
            }
        ?>
            <a href="<?= $category_link ?>" class="<?= $active_class ?>"><?= htmlspecialchars($cat) ?></a>
        <?php endforeach; ?>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Produk</th>
                <th>Harga</th>
                <th>Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($produk_q->num_rows > 0) : ?>
                <?php while ($p = $produk_q->fetch_assoc()): ?>
                <tr>
                    <td data-label="ID"><?= htmlspecialchars($p['id_produk']) ?></td>
                    <td data-label="Nama Produk"><?= htmlspecialchars($p['nama_produk']) ?></td>
                    <td data-label="Harga">Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                    <td data-label="Kategori"><?= htmlspecialchars($p['kategori'] ?? '-') ?></td>
                    <td data-label="Aksi">
                        <div class="action-buttons">
                            <a href="produk_edit.php?id=<?= $p['id_produk'] ?>" class="btn btn-edit">Edit</a>
                            <a href="produk.php?action=hapus&id=<?= $p['id_produk'] ?>" class="btn btn-delete" onclick="return confirm('Yakin mau hapus produk ini? Data akan hilang permanen!')">Hapus</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: var(--color-secondary-brown);">Gak ada data produk di kategori ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>