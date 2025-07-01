<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['id_kasir'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: produk.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $kategori = $_POST['kategori'];

    if ($nama && $harga) {
        $stmt = $koneksi->prepare("UPDATE produk SET nama_produk = ?, harga = ?, kategori = ? WHERE id_produk = ?");
        $stmt->bind_param("sisi", $nama, $harga, $kategori, $id);
        $stmt->execute();
        header("Location: produk.php");
        exit;
    } else {
        $error = "Nama dan harga wajib diisi.";
    }
}

$produk = $koneksi->query("SELECT * FROM produk WHERE id_produk = $id")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Baker Old</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- Warna Dasar (sesuai tema Baker Old) --- */
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

        /* --- Global Styles --- */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background-color: var(--color-cream);
            color: var(--color-text-dark);
            margin: 0;
            display: flex; /* Biar formnya di tengah */
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        /* --- Form Container --- */
        .form-container {
            background-color: var(--color-text-light);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border: 1px solid var(--color-light-brown);
            margin-bottom: 30px;
            text-align: left;
            max-width: 500px; /* Lebar maksimal form */
            width: 100%;
        }

        /* --- Headings --- */
        h2 {
            color: var(--color-primary-brown);
            font-family: 'Georgia', serif;
            font-size: 2em; /* Lebih besar dari sebelumnya */
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 2px solid var(--color-light-brown);
            padding-bottom: 10px;
        }

        /* --- Form Group Styling (Label & Input/Select) --- */
        .form-group {
            margin-bottom: 15px; /* Spasi antar grup form */
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--color-secondary-brown);
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select {
            width: calc(100% - 22px); /* Penyesuaian lebar untuk padding & border */
            padding: 10px;
            border: 1px solid var(--color-light-brown);
            border-radius: 5px;
            font-size: 1em;
            background-color: var(--color-cream);
            color: var(--color-text-dark);
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--color-secondary-brown);
            box-shadow: 0 0 0 3px rgba(139, 111, 90, 0.2);
        }

        /* --- Error Message Styling --- */
        .error-message {
            color: var(--color-error);
            background-color: rgba(239, 83, 80, 0.1);
            border: 1px solid var(--color-error);
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95em;
            font-weight: bold;
            text-align: center;
        }

        /* --- Button Styling --- */
        .button-group {
            margin-top: 25px; /* Spasi di atas tombol */
            text-align: center;
        }
        .button-group button,
        .button-group .btn-batal { /* Styling untuk link Batal */
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1em;
            transition: background-color 0.3s ease, transform 0.1s;
            display: inline-block; /* Biar bisa diatur margin-right */
            text-decoration: none; /* Untuk link */
            color: var(--color-text-light); /* Warna teks default untuk tombol */
        }
        .button-group button:hover,
        .button-group .btn-batal:hover {
            transform: translateY(-2px);
        }

        .button-group .submit-btn {
            background-color: var(--color-yellow);
            color: var(--color-primary-brown);
            margin-right: 10px; /* Jarak antara tombol Simpan dan Batal */
        }
        .button-group .submit-btn:hover {
            background-color: var(--color-dark-yellow);
        }

        .button-group .btn-batal {
            background-color: #ccc;
            color: var(--color-text-dark);
        }
        .button-group .btn-batal:hover {
            background-color: #bbb;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Produk</h2>
        <?php if (isset($error)) : ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="nama_produk"><i class="fas fa-bread-slice"></i> Nama Produk:</label>
                <input type="text" id="nama_produk" name="nama" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="harga"><i class="fas fa-dollar-sign"></i> Harga:</label>
                <input type="number" id="harga" name="harga" value="<?= htmlspecialchars($produk['harga']) ?>" required min="0" step="100">
            </div>
            
            <div class="form-group">
                <label for="kategori"><i class="fas fa-tags"></i> Kategori:</label>
                <select id="kategori" name="kategori" required>
                    <option value="Semua" <?= $produk['kategori'] === 'Semua' ? 'selected' : '' ?>>Semua</option>
                    <option value="Makanan" <?= $produk['kategori'] === 'Makanan' ? 'selected' : '' ?>>Makanan</option>
                    <option value="Dessert" <?= $produk['kategori'] === 'Dessert' ? 'selected' : '' ?>>Dessert</option>
                    <option value="Packaging" <?= $produk['kategori'] === 'Packaging' ? 'selected' : '' ?>>Packaging</option>
                    <option value="Lainnya" <?= $produk['kategori'] === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </div>

            <div class="button-group">
                <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Simpan</button>
                <a href="produk.php" class="btn-batal"><i class="fas fa-times-circle"></i> Batal</a>
            </div>
        </form>
    </div>
</body>
</html>