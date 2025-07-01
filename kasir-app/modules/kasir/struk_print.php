<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

include("../../config/db.php");

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$kode_transaksi = $_GET['kode'] ?? '';

if (empty($kode_transaksi)) {
    die("Kode transaksi tidak ditemukan. Kembali ke halaman utama ya.");
}

$transaksi_q = $koneksi->prepare("
    SELECT
        t.id_transaksi,
        t.kode_transaksi,
        t.tanggal,
        t.total AS total_belanja_setelah_diskon,
        t.bayar AS jumlah_bayar,
        t.kembalian,
        k.nama_kasir
    FROM transaksi t
    LEFT JOIN kasir k ON t.id_kasir = k.id_kasir
    WHERE t.kode_transaksi = ?
");

if (!$transaksi_q) {
    die("Error prepare transaksi: " . $koneksi->error);
}
$transaksi_q->bind_param("s", $kode_transaksi);
$transaksi_q->execute();
$transaksi_result = $transaksi_q->get_result();
$transaksi_data = $transaksi_result->fetch_assoc();
$transaksi_q->close(); 

if (!$transaksi_data) {
    die("Data transaksi tidak ditemukan untuk kode: " . htmlspecialchars($kode_transaksi));
}

$detail_q = $koneksi->prepare("
    SELECT
        dt.qty,
        dt.subtotal,
        p.nama_produk,
        p.harga AS harga_asli_satuan
    FROM detail_transaksi dt
    JOIN produk p ON dt.id_produk = p.id_produk
    WHERE dt.id_transaksi = ?
");
if (!$detail_q) {
    die("Error prepare detail transaksi: " . $koneksi->error);
}
$detail_q->bind_param("i", $transaksi_data['id_transaksi']);
$detail_q->execute();
$detail_result = $detail_q->get_result();
$detail_q->close(); 

$kode_transaksi_tampil = htmlspecialchars($transaksi_data['kode_transaksi']);
$tanggal_tampil = date('d-m-Y H:i:s', strtotime($transaksi_data['tanggal'])); 
$kasir_nama = htmlspecialchars($transaksi_data['nama_kasir'] ?? 'Kasir');
$total_belanja_setelah_diskon = $transaksi_data['total_belanja_setelah_diskon'];
$jumlah_bayar = $transaksi_data['jumlah_bayar'];
$kembalian = $transaksi_data['kembalian'];

$total_harga_asli_semua_item = 0;
$detail_items = []; 

while ($item = $detail_result->fetch_assoc()) {
    $harga_normal_item_ini = $item['harga_asli_satuan'] * $item['qty'];
    $total_harga_asli_semua_item += $harga_normal_item_ini;

    $item['harga_satuan_tampil'] = $item['harga_asli_satuan'];
    $item['subtotal_tampil'] = $item['subtotal'];
    
    $item['is_gratis_display'] = ($item['subtotal'] == 0 && $item['qty'] > 0 && $item['harga_asli_satuan'] > 0);
    
    if ($item['is_gratis_display']) {
        $item['harga_satuan_tampil'] = 0;
    }

    $detail_items[] = $item;
}

$total_diskon = $total_harga_asli_semua_item - $total_belanja_setelah_diskon;
if ($total_diskon < 0) {
    $total_diskon = 0;
}


$current_date = date('Y-m-d');

if (!isset($_SESSION['last_queue_reset_date']) || $_SESSION['last_queue_reset_date'] !== $current_date) {

    $_SESSION['nomor_antrian_terakhir'] = 0; 
    $_SESSION['last_queue_reset_date'] = $current_date;
}


$_SESSION['nomor_antrian_terakhir'] = ($_SESSION['nomor_antrian_terakhir'] ?? 0) + 1;
$nomor_antrian = $_SESSION['nomor_antrian_terakhir'];


$nama_toko = "Baker Old";
$alamat_toko = "309 - Jayaraga Garut";
$no_meja = "-"; 
$mode_transaksi = "TAKEAWAY"; 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Struk Pembelian - <?= $kode_transaksi_tampil ?></title>
    <style>
        /* CSS buat tampilan struk */
        body {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 11px;
            width: 280px; 
            margin: 0 auto;
            padding: 10px;
            box-sizing: border-box;
            color: #000;
        }
        .header-struk { 
            text-align: center; 
            margin-bottom: 10px; 
        }
        .header-struk img { 
            max-width: 100px; 
            margin-bottom: 5px; 
        }
        .header-struk h2 { 
            margin: 0; 
            font-size: 1.2em; 
            font-family: 'Georgia', serif; 
            color: #333; 
        }
        .header-struk p { 
            margin: 0; 
            font-size: 0.9em; 
        }

        .info-transaksi, .summary-transaksi, .payment-info {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        .info-transaksi p, .summary-transaksi p, .payment-info p {
            margin: 2px 0;
            display: flex;
            justify-content: space-between;
        }

        .detail-item {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-bottom: 10px;
        }

        .detail-item .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            line-height: 1.3;
        }
        .item-name { 
            font-weight: bold; 
            flex-basis: 50%; 
            text-align: left; 
        }
        .item-qty-price { 
            flex-basis: 30%; 
            text-align: right; 
            padding-right: 5px; 
        }
        .item-subtotal { 
            flex-basis: 20%; 
            text-align: right; 
        }
        .promo-tag-struk {
            font-size: 0.7em;
            background-color: #2196F3;
            color: white;
            padding: 1px 4px;
            border-radius: 2px;
            margin-left: 5px;
            vertical-align: middle;
        }

        .summary-transaksi .grand-total {
            font-size: 1.2em;
            padding-top: 5px;
            border-top: 1px dashed #000;
        }
        .discount-line-struk {
            color: #8B0000;
            font-weight: bold;
        }


        .payment-info .kembalian {
            font-size: 1.3em;
            color: #000;
            margin-top: 5px;
        }

        .footer-struk {
            text-align: center;
            margin-top: 20px;
            border-top: 1px dashed #000;
            padding-top: 15px;
        }
        .thank-you { 
            font-weight: bold; 
            font-size: 1.1em; 
            margin-bottom: 10px; 
        }
        .queue-number-label { 
            margin-bottom: 5px; 
            font-size: 0.9em; 
        }
        .queue-number { 
            font-size: 2.2em; 
            font-weight: bold; 
            margin-bottom: 10px; 
            line-height: 1; 
        }
        .queue-message { 
            font-size: 0.85em; 
        }

        .no-print {
            display: block; 
            text-align: center; 
            margin-top: 20px; 
        }
        .no-print a {
            padding: 10px 20px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            font-size: 1em;
        }
        .no-print a:hover { 
            background-color: #e0e0e0; 
        }

        @media print {
            body { 
                margin: 0; 
                padding: 0; 
                width: 80mm; 
                font-size: 10px; 
            }
            .header-struk img { 
                max-width: 80px; 
            }
            .no-print { 
                display: none; 
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header-struk">
        <img src="../../images/logo.png" alt="Logo Baker Old">
        <h2><?= htmlspecialchars($nama_toko) ?></h2>
        <p><?= htmlspecialchars($alamat_toko) ?></p>
    </div>

    <div class="info-transaksi">
        <p><span>No      : </span><span><?= $kode_transaksi_tampil ?></span></p>
        <p><span>Tanggal  : </span><span><?= $tanggal_tampil ?></span></p>
        <p><span>No Meja  : </span><span><?= htmlspecialchars($no_meja) ?></span></p>
        <p><span>Mode     : </span><span><?= htmlspecialchars($mode_transaksi) ?></span></p>
        <p><span>Kasir    : </span><span><?= $kasir_nama ?></span></p>
    </div>

    <div class="detail-item">
        <?php
        $total_items_count = 0;
        if (!empty($detail_items)) :
            foreach ($detail_items as $item) :
                $total_items_count += $item['qty'];
        ?>
        <div class="item-row">
            <span class="item-name">
                <?= htmlspecialchars($item['nama_produk']) ?>
                <?php if ($item['is_gratis_display']) : ?>
                    <span class="promo-tag-struk">Gratis!</span>
                <?php endif; ?>
            </span>
            <span class="item-qty-price">
                <?= $item['qty'] ?>x @<?= number_format($item['harga_satuan_tampil'], 0, ',', '.') ?>
            </span>
            <span class="item-subtotal">
                <?= number_format($item['subtotal_tampil'], 0, ',', '.') ?>
            </span>
        </div>
        <?php endforeach; else : ?>
        <p style="text-align: center;">Tidak ada detail item untuk transaksi ini.</p>
        <?php endif; ?>
    </div>

    <div class="summary-transaksi">
        <p><span><?= $total_items_count ?> item</span></p>
        <?php if ($total_diskon > 0) :  ?>
        <p class="discount-line-struk"><span>Diskon</span> <span>- Rp <?= number_format($total_diskon, 0, ',', '.') ?></span></p>
        <?php endif; ?>
        <p><span>Subtotal</span> <span>Rp <?= number_format($total_harga_asli_semua_item, 0, ',', '.') ?></span></p>
        <p class="grand-total"><span>Grand Total</span> <span>Rp <?= number_format($total_belanja_setelah_diskon, 0, ',', '.') ?></span></p>
    </div>

    <div class="payment-info">
        <p><span>CASH</span> <span>Rp <?= number_format($jumlah_bayar, 0, ',', '.') ?></span></p>
        <p class="kembalian"><span>Kembalian</span> <span>Rp <?= number_format($kembalian, 0, ',', '.') ?></span></p>
    </div>

    <div class="footer-struk">
        <p class="thank-you">--- Thank You ---</p>
        <p class="queue-number-label">Nomor antrian</p>
        <p class="queue-number"><?= $nomor_antrian ?></p>
        <p class="queue-message">Tunggu nomor kamu dipanggil</p>
    </div>

    <div class="no-print">
        <a href="transaksi_baru.php">Kembali ke Transaksi Baru</a>
    </div>
</body>
</html>