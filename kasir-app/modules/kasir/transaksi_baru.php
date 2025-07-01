<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include("../../config/db.php");

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}


$nama_kasir = $_SESSION['nama_kasir'] ?? 'Kasir Baker Old'; 

// Waktu sekarang, buat dipajang di header
$tanggal_sekarang = date('d F Y');
$waktu_sekarang = date('H:i:s');

// --- Bagian nampilin produk dan kategori ---
// Kategori dipilih dari URL, kalo ga ada ya defaultnya 'Semua'
$kategori_terpilih = $_GET['kategori'] ?? 'Semua';

// Ambil semua kategori unik dari database, terus tambahin 'Semua' di awal
$kategori_q = $koneksi->query("SELECT DISTINCT kategori FROM produk ORDER BY kategori ASC");
if (!$kategori_q) {
    die("Error mengambil data kategori: " . $koneksi->error);
}
$daftar_kategori = ['Semua'];
while ($row = $kategori_q->fetch_assoc()) {
    $daftar_kategori[] = htmlspecialchars($row['kategori']);
}

// Ambil daftar produk, kalo ada filter kategori ya disaring
$sql_produk = "SELECT * FROM produk";
if ($kategori_terpilih !== 'Semua') {
    $kategori_terpilih_sql = $koneksi->real_escape_string($kategori_terpilih);
    $sql_produk .= " WHERE kategori = '$kategori_terpilih_sql'";
}
$produk_q = $koneksi->query($sql_produk);

if (!$produk_q) {
    die("Error mengambil data produk: " . $koneksi->error);
}


$daftar_produk_array = [];
while ($row = $produk_q->fetch_assoc()) {
    $daftar_produk_array[$row['id_produk']] = $row;
}

// Inisialisasi keranjang sama total-totalan
$keranjang = $_SESSION['keranjang'] ?? [];
$total_sebelum_diskon = 0;
$diskon_total = 0; // Ini buat nyimpen total diskon dari item yang digratisin

/**
 * Ini fungsi buat ngasih gratisan satu unit produk di keranjang.
 * Dia nandaian item di sesi keranjang pake 'is_gratis' = true.
 */
function gratiskanProdukDiKeranjang($id_produk_digratiskan, $jumlah_gratis = 1) {
    if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])) {
        $_SESSION['pesan_error'] = "Keranjang kosong.";
        return false;
    }

    $keranjang_baru = [];
    $unit_telah_digratiskan = 0;

    foreach ($_SESSION['keranjang'] as $item) {
        if ($item['id_produk'] == $id_produk_digratiskan && (!isset($item['is_gratis']) || !$item['is_gratis']) && $unit_telah_digratiskan < $jumlah_gratis) {
            $item['is_gratis'] = true; 
            $unit_telah_digratiskan++;
        }
        $keranjang_baru[] = $item;
    }
    
    if ($unit_telah_digratiskan > 0) {
        $_SESSION['keranjang'] = $keranjang_baru;
        $_SESSION['pesan_sukses'] = "Berhasil menggratiskan " . $unit_telah_digratiskan . " unit produk!";
        return true;
    } else {
        $_SESSION['pesan_error'] = "Produk tidak ditemukan di keranjang atau sudah semua digratiskan.";
        return false;
    }
}

// --- Kalo ada request buat gratisin produk, jalanin fungsi di atas ---
if (isset($_GET['gratis_produk_id'])) {
    $id_produk_untuk_digratiskan = (int)$_GET['gratis_produk_id'];
    gratiskanProdukDiKeranjang($id_produk_untuk_digratiskan, 1);
    $redirect_url = "transaksi_baru.php";
    if ($kategori_terpilih !== 'Semua') {
        $redirect_url .= "?kategori=" . urlencode($kategori_terpilih);
    }
    header("Location: " . $redirect_url); 
    exit();
}

// Hitung ulang total keranjang setelah ada potensi perubahan (misal ada yang digratisin)
foreach ($keranjang as $index => $item) {
    $subtotal_item = $item['harga'] * $item['qty'];
    
    // Ini buat nentuin harga final item di keranjang
    if (isset($item['is_gratis']) && $item['is_gratis'] === true) {
        $diskon_total += $subtotal_item; 
        $keranjang[$index]['harga_final'] = 0; // Harga di keranjang jadi nol
        $keranjang[$index]['subtotal_final'] = 0;
        $keranjang[$index]['status_promo'] = 'Gratis'; 
    } else {
        $keranjang[$index]['harga_final'] = $item['harga'];
        $keranjang[$index]['subtotal_final'] = $subtotal_item;
        $keranjang[$index]['status_promo'] = 'Normal';
    }
    $total_sebelum_diskon += $subtotal_item; 
}

$total_setelah_promo = $total_sebelum_diskon - $diskon_total;

if ($total_setelah_promo < 0) {
    $total_setelah_promo = 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir Baker Old</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styling umum & layout, ini CSS-nya, biarin aja gak usah diotak-atik kalau gak perlu */
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
            --color-promo: #1E88E5;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0; padding: 0;
            background-color: var(--color-cream);
            color: var(--color-text-dark);
            font-size: 16px;
        }

        .header {
            background-color: var(--color-primary-brown); color: var(--color-text-light);
            padding: 20px 40px; text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2); margin-bottom: 20px;
            position: relative;
        }
        .header h1 { margin: 0; font-size: 2.5em; font-family: 'Georgia', serif; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); }
        .header p { margin: 5px 0 0; font-size: 1.1em; opacity: 0.9; }
        .btn-kembali {
            position: absolute; top: 20px; left: 20px;
            background-color: var(--color-secondary-brown); color: var(--color-text-light);
            padding: 8px 16px; text-decoration: none; border-radius: 5px;
            font-weight: bold; transition: background-color 0.3s ease;
        }
        .btn-kembali:hover { background-color: var(--color-primary-brown); }

        .container {
            display: flex; gap: 25px;
            max-width: 1300px; margin: 0 auto 30px auto;
            align-items: flex-start;
        }
        .left-panel, .right-panel {
            background-color: var(--color-text-light); padding: 25px;
            border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border: 1px solid var(--color-light-brown);
        }
        .left-panel { flex: 2; }
        .right-panel { flex: 1; position: sticky; top: 20px; }

        h2 {
            color: var(--color-primary-brown); border-bottom: 2px solid var(--color-light-brown);
            padding-bottom: 12px; margin-top: 0; margin-bottom: 20px;
            font-size: 1.8em; font-family: 'Georgia', serif;
        }

        .notification {
            padding: 15px; margin-bottom: 20px; border-radius: 8px;
            font-size: 0.95em; font-weight: bold; display: flex; align-items: center;
        }
        .notification i { margin-right: 8px; }
        .notification.error { background-color: #ffe0e0; color: var(--color-error); border: 1px solid var(--color-error); }
        .notification.success { background-color: #e0ffe0; color: var(--color-success); border: 1px solid var(--color-success); }

        .category-nav {
            display: flex; justify-content: flex-start; gap: 10px;
            margin-bottom: 20px; padding-bottom: 10px;
            border-bottom: 1px solid var(--color-light-brown);
            overflow-x: auto; white-space: nowrap;
            padding-top: 5px; padding-bottom: 15px;
        }
        .category-nav a {
            padding: 8px 18px; border-radius: 25px; text-decoration: none;
            color: var(--color-primary-brown); background-color: var(--color-light-brown);
            font-weight: bold; transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
            flex-shrink: 0; font-size: 0.95em;
        }
        .category-nav a:hover { background-color: var(--color-secondary-brown); color: var(--color-text-light); }
        .category-nav a.active {
            background-color: var(--color-yellow); color: var(--color-primary-brown);
            box-shadow: 0 3px 8px rgba(0,0,0,0.25); pointer-events: none;
        }

        .product-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px; margin-top: 20px;
        }
        .product-card {
            background-color: var(--color-cream); border: 1px solid var(--color-light-brown);
            border-radius: 10px; padding: 18px; text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .product-card:hover { transform: translateY(-7px); box-shadow: 0 6px 15px rgba(0,0,0,0.15); }
        .product-card img {
            max-width: 100%; height: 140px; object-fit: cover;
            border-radius: 8px; margin-bottom: 15px; border: 2px solid var(--color-secondary-brown);
        }
        .product-card h3 { margin: 10px 0 8px; font-size: 1.3em; color: var(--color-primary-brown); line-height: 1.3; }
        .product-card p { font-size: 1.2em; color: var(--color-secondary-brown); font-weight: bold; margin-bottom: 18px; }
        .product-card form { display: flex; flex-direction: column; gap: 10px; margin-top: auto; }
        .product-card input[type="number"] {
            width: 80px; padding: 10px; border: 1px solid var(--color-secondary-brown);
            border-radius: 6px; text-align: center; margin: 0 auto;
            font-size: 1em; background-color: var(--color-cream);
        }
        .product-card button {
            background-color: var(--color-yellow); color: var(--color-primary-brown);
            padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer;
            font-size: 1.1em; font-weight: bold; transition: background-color 0.3s ease, transform 0.1s;
        }
        .product-card button:hover { background-color: var(--color-dark-yellow); transform: translateY(-1px); }

        /* Tabel keranjang */
        table {
            width: 100%; border-collapse: collapse; margin-top: 20px;
            border-radius: 8px; overflow: hidden; border: 1px solid var(--color-light-brown);
            font-size: 0.95em;
        }
        table th, table td { border: 1px solid var(--color-light-brown); padding: 12px; text-align: left; }
        table th {
            background-color: var(--color-light-brown); color: var(--color-primary-brown);
            font-weight: bold; font-size: 1em;
        }
        table tbody tr:nth-child(even) { background-color: var(--color-cream); }
        table tfoot td {
            font-weight: bold; background-color: var(--color-secondary-brown);
            font-size: 1.2em; color: var(--color-text-light);
        }
        table tfoot tr:last-child td {
            background-color: var(--color-primary-brown); font-size: 1.3em;
        }
        .discount-row {
            background-color: #fcebeb !important; color: var(--color-error); font-weight: bold;
        }

        /* Tombol aksi di keranjang */
        .cart-action-buttons-group { display: flex; gap: 5px; justify-content: flex-start; }
        .cart-action-button, .gratis-button {
            padding: 8px 12px; border: none; border-radius: 5px; cursor: pointer;
            font-size: 0.8em; font-weight: bold;
            transition: background-color 0.3s ease, transform 0.1s;
            display: flex; align-items: center; justify-content: center;
        }
        .cart-action-button i, .gratis-button i { margin-right: 4px; }
        .cart-action-button { background-color: var(--color-error); color: var(--color-text-light); }
        .cart-action-button:hover { background-color: #d32f2f; transform: translateY(-1px); }
        .gratis-button { background-color: var(--color-promo); color: var(--color-text-light); }
        .gratis-button:hover { background-color: #1565C0; transform: translateY(-1px); }
        .promo-tag {
            background-color: var(--color-promo); color: var(--color-text-light);
            font-size: 0.7em; padding: 2px 5px; border-radius: 4px; margin-left: 5px;
            font-weight: bold; vertical-align: middle; display: inline-block;
        }

        /* Form checkout */
        .checkout-form {
            margin-top: 30px; padding-top: 20px;
            border-top: 1px dashed var(--color-light-brown);
        }
        .checkout-form label {
            display: block; margin-bottom: 10px; font-weight: bold;
            color: var(--color-primary-brown); font-size: 1.1em;
        }
        .checkout-form input[type="number"] {
            width: calc(100% - 24px); padding: 12px; margin-bottom: 18px;
            border: 1px solid var(--color-secondary-brown); border-radius: 6px;
            font-size: 1.1em; background-color: var(--color-cream); color: var(--color-text-dark);
        }
        .checkout-form input[type="number"]::placeholder { color: var(--color-secondary-brown); opacity: 0.8; }
        .checkout-form button {
            background-color: var(--color-yellow); color: var(--color-primary-brown);
            padding: 14px 25px; border: none; border-radius: 8px; cursor: pointer;
            font-size: 1.3em; font-weight: bold; width: 100%;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 10px rgba(255, 213, 79, 0.4);
        }
        .checkout-form button:hover {
            background-color: var(--color-dark-yellow); transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 213, 79, 0.6);
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="../home.php" class="btn-kembali"><i class="fas fa-arrow-left mr-2"></i>Kembali</a>
        <h1>Sistem Kasir Baker Old</h1>
        <p>Kasir: <?= htmlspecialchars($nama_kasir) ?> | Tanggal: <?= $tanggal_sekarang ?> | Waktu: <span id="realtime-clock"><?= $waktu_sekarang ?></span></p>
    </div>

    <div class="container">
        <div class="left-panel">
            <?php
            // Buat nampilin pesan sukses/error di atas menu
            if (isset($_SESSION['pesan_error'])) : ?>
                <div class='notification error'><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($_SESSION['pesan_error']) ?></div>
                <?php unset($_SESSION['pesan_error']);
            endif;
            if (isset($_SESSION['pesan_sukses'])) : ?>
                <div class='notification success'><i class="fas fa-check-circle"></i><?= htmlspecialchars($_SESSION['pesan_sukses']) ?></div>
                <?php unset($_SESSION['pesan_sukses']);
            endif;
            ?>

            <h2>Menu</h2>

            <div class="category-nav">
                <?php foreach ($daftar_kategori as $cat) :
                    $active_class = ($cat == $kategori_terpilih) ? 'active' : '';
                    $category_link = "transaksi_baru.php";
                    if ($cat !== 'Semua') {
                        $category_link .= "?kategori=" . urlencode($cat);
                    }
                ?>
                    <a href="<?= $category_link ?>" class="<?= $active_class ?>"><?= htmlspecialchars($cat) ?></a>
                <?php endforeach; ?>
            </div>
            <div class="product-grid">
                <?php if (!empty($daftar_produk_array)) : ?>
                    <?php foreach ($daftar_produk_array as $p) :
                        // Logic buat nampilin gambar produk, cek urutan: .jpg, .png, kalo gak ada pake default
                        $image_folder = '../../images/';
                        $clean_product_name = strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $p['nama_produk'])));
                        
                        $image_path = $image_folder . $clean_product_name . '.jpg';
                        if (!file_exists($image_path) || is_dir($image_path)) {
                            $image_path = $image_folder . $clean_product_name . '.png';
                        }
                        if (!file_exists($image_path) || is_dir($image_path)) {
                            $image_path = $image_folder . 'default.jpg';
                        }
                    ?>
                        <div class="product-card">
                            <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>">
                            <h3><?= htmlspecialchars($p['nama_produk']) ?></h3>
                            <p>Rp <?= number_format($p['harga'], 0, ',', '.') ?></p>
                            <form action="produk_tambah.php" method="post">
                                <input type="hidden" name="id_produk" value="<?= $p['id_produk'] ?>">
                                <input type="number" name="qty" value="1" min="1" required>
                                <button type="submit"><i class="fas fa-cart-plus mr-2"></i>Tambah</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p style="grid-column: 1 / -1; text-align: center; color: var(--color-secondary-brown);">Maaf, gak ada produk di kategori ini.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="right-panel">
            <h2>Keranjang Pesanan</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($keranjang)) : ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: var(--color-secondary-brown);">Keranjang kamu kosong. Yuk, tambahin roti!</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($keranjang as $index => $item) :
                            $harga_tampil = $item['harga_final'];
                            $subtotal_tampil = $item['subtotal_final'];
                            $label_promo = '';

                            if (isset($item['status_promo']) && $item['status_promo'] == 'Gratis') {
                                $label_promo = '<span class="promo-tag">Gratis!</span>';
                            }
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nama_produk']) ?> <?= $label_promo ?></td>
                                <td>Rp <?= number_format($harga_tampil, 0, ',', '.') ?></td>
                                <td><?= $item['qty'] ?></td>
                                <td>Rp <?= number_format($subtotal_tampil, 0, ',', '.') ?></td>
                                <td>
                                    <div class="cart-action-buttons-group">
                                        <form action="keranjang_hapus.php" method="post" style="display:inline;">
                                            <input type="hidden" name="index" value="<?= $index ?>">
                                            <button type="submit" class="cart-action-button" onclick="return confirm('Yakin mau hapus roti ini?')"><i class="fas fa-trash-alt"></i>Hapus</button>
                                        </form>
                                        <?php
                                        if ((!isset($item['is_gratis']) || !$item['is_gratis']) && $item['qty'] > 0) :
                                        ?>
                                            <form action="transaksi_baru.php" method="get" style="display:inline;">
                                                <input type="hidden" name="gratis_produk_id" value="<?= $item['id_produk'] ?>">
                                                <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori_terpilih) ?>">
                                                <button type="submit" class="gratis-button" onclick="return confirm('Yakin mau gratisin produk ini?')"><i class="fas fa-gift"></i>Free</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Total Pembayaran</td>
                        <td colspan="2">Rp <?= number_format($total_setelah_promo, 0, ',', '.') ?></td>
                    </tr>
                    <?php if ($diskon_total > 0): ?>
                    <tr class="discount-row">
                        <td colspan="3">Diskon</td>
                        <td colspan="2">- Rp <?= number_format($diskon_total, 0, ',', '.') ?></td>
                    </tr>
                    <?php endif; ?>
                </tfoot>
            </table>

            <div class="checkout-form">
                <h2>Pembayaran</h2>
                <form action="transaksi_proses.php" method="post">
                    <label for="bayar">Jumlah Uang Diterima:</label>
                    <input type="number" name="bayar" id="bayar" required min="<?= $total_setelah_promo ?>" placeholder="Masukkan uang yang dibayarkan pelanggan">
                    <button type="submit"><i class="fas fa-dollar-sign mr-2"></i>Selesaikan Transaksi</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateRealtimeClock() {
            const clockElement = document.getElementById('realtime-clock');
            if (clockElement) {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                clockElement.textContent = `${hours}:${minutes}:${seconds}`;
            }
        }

        setInterval(updateRealtimeClock, 1000);
        updateRealtimeClock(); 
    </script>
</body>
</html>