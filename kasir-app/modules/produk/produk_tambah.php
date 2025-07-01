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

function tambahProduk($koneksi, $nama_produk, $harga, $kategori) {
    $stmt = $koneksi->prepare("INSERT INTO produk (nama_produk, harga, kategori) VALUES (?, ?, ?)");
    if (!$stmt) {
        $_SESSION['pesan_error'] = "Gagal prepare tambah: " . $koneksi->error;
        return false;
    }
    $stmt->bind_param("sis", $nama_produk, $harga, $kategori);
    if ($stmt->execute()) {
        $_SESSION['pesan_sukses'] = "Produk '" . htmlspecialchars($nama_produk) . "' berhasil ditambahkan!";
        return true;
    } else {
        $_SESSION['pesan_error'] = "Gagal menambah produk: " . $stmt->error;
        return false;
    }
    $stmt->close();
}

$kategori_q = $koneksi->query("SELECT DISTINCT kategori FROM produk ORDER BY kategori ASC");
if (!$kategori_q) {
    die("Error mengambil data kategori: " . $koneksi->error);
}
$daftar_kategori = [''];
while ($row = $kategori_q->fetch_assoc()) {
    $daftar_kategori[] = htmlspecialchars($row['kategori']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $harga = (float)($_POST['harga'] ?? 0);
    $kategori = trim($_POST['kategori'] ?? '');

    if ($kategori === 'Lainnya' && !empty(trim($_POST['kategori_baru'] ?? ''))) {
        $kategori = trim($_POST['kategori_baru']);
    }

    if (empty($nama_produk) || empty($harga) || empty($kategori)) {
        $_SESSION['pesan_error'] = "Nama, Harga, dan Kategori wajib diisi!";
    } else {
        tambahProduk($koneksi, $nama_produk, $harga, $kategori);
    }
    header("Location: produk.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk Baru - Baker Old</title>
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
            --color-add: #28A745;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background-color: var(--color-cream);
            color: var(--color-text-dark);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        h2 {
            margin-bottom: 20px;
            color: var(--color-primary-brown);
            text-align: center;
            font-size: 2em;
            font-family: 'Georgia', serif;
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

        .form-container {
            background-color: var(--color-text-light);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border: 1px solid var(--color-light-brown);
            margin-bottom: 30px;
            text-align: left;
            max-width: 500px;
            width: 100%;
        }
        .form-container h3 {
            color: var(--color-primary-brown);
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.5em;
            border-bottom: 1px dashed var(--color-light-brown);
            padding-bottom: 10px;
            text-align: center;
        }
        .form-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--color-secondary-brown);
        }
        .form-container input[type="text"],
        .form-container input[type="number"],
        .form-container select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid var(--color-light-brown);
            border-radius: 5px;
            font-size: 1em;
            background-color: var(--color-cream);
        }
        .form-container button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }
        .form-container button.submit-btn {
            background-color: var(--color-add);
            color: var(--color-text-light);
        }
        .form-container button.submit-btn:hover {
            background-color: #218838;
        }
        .form-container button.cancel-btn {
            background-color: #ccc;
            color: #333;
            margin-left: 10px;
        }
        .form-container button.cancel-btn:hover {
            background-color: #bbb;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h3>Tambah Produk Baru</h3>

        <?php
        if ($pesan_sukses) : ?>
            <div class='notification success'><?= htmlspecialchars($pesan_sukses) ?></div>
        <?php endif;
        if ($pesan_error) : ?>
            <div class='notification error'><?= htmlspecialchars($pesan_error) ?></div>
        <?php endif; ?>

        <form action="produk_tambah.php" method="post">
            <label for="nama_produk">Nama Produk:</label>
            <input type="text" id="nama_produk" name="nama_produk" required>

            <label for="harga">Harga:</label>
            <input type="number" id="harga" name="harga" min="0" step="100" required>

            <label for="kategori">Kategori:</label>
            <select id="kategori" name="kategori" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($daftar_kategori as $cat_option) : ?>
                    <?php if (!empty($cat_option)) : ?>
                        <option value="<?= $cat_option ?>"><?= $cat_option ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
                <option value="Lainnya">Lainnya (Tulis Baru)</option>
            </select>
            <input type="text" id="kategori_baru" name="kategori_baru" placeholder="Kategori baru..." style="display:none; margin-top: -10px; margin-bottom: 15px;">

            <button type="submit" class="submit-btn">Tambah Produk</button>
            <button type="button" class="cancel-btn" onclick="window.location.href='produk.php'">Batal</button>
        </form>
    </div>

    <script>
        document.getElementById('kategori').addEventListener('change', function() {
            const kategoriBaruInput = document.getElementById('kategori_baru');
            if (this.value === 'Lainnya') {
                kategoriBaruInput.style.display = 'block';
                kategoriBaruInput.setAttribute('required', 'required');
            } else {
                kategoriBaruInput.style.display = 'none';
                kategoriBaruInput.removeAttribute('required');
                kategoriBaruInput.value = '';
            }
        });

        document.querySelector('form').addEventListener('submit', function() {
            const kategoriSelect = document.getElementById('kategori');
            const kategoriBaruInput = document.getElementById('kategori_baru');
            if (kategoriSelect.value === 'Lainnya' && kategoriBaruInput.value.trim() !== '') {
                kategoriSelect.value = kategoriBaruInput.value.trim();
            }
        });
    </script>
</body>
</html>