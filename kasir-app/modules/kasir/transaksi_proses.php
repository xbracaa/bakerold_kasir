<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['id_kasir']) || empty($_SESSION['keranjang'])) {
    $_SESSION['pesan_error'] = "Transaksi tidak valid atau keranjang kosong.";
    header("Location: transaksi_baru.php");
    exit;
}

$keranjang = $_SESSION['keranjang'];
$id_kasir = $_SESSION['id_kasir'];
$bayar = intval($_POST['bayar'] ?? 0);

$total_keranjang_aktual = 0; 
$diskon_item_gratis = 0; 

// Hitung total belanja (udh masuk ngecek item gratis)
foreach ($keranjang as $item) {
    if (isset($item['is_gratis']) && $item['is_gratis'] === true) {
        $diskon_item_gratis += ($item['harga'] * $item['qty']);
    } else {
        $total_keranjang_aktual += ($item['harga'] * $item['qty']);
    }
}


if ($total_keranjang_aktual < 0) {
    $total_keranjang_aktual = 0;
}


if ($bayar < $total_keranjang_aktual) {
    $_SESSION['pesan_error'] = "Uang pembayaran kurang dari total belanja yang harus dibayar (Rp " . number_format($total_keranjang_aktual, 0, ',', '.') . ").";
    header("Location: transaksi_baru.php");
    exit;
}

$kembalian = $bayar - $total_keranjang_aktual;

// Bikin kode transaksi
$kode_transaksi = "TRX" . date("YmdHis");


$stmt = $koneksi->prepare("INSERT INTO transaksi (kode_transaksi, id_kasir, tanggal, total, bayar, kembalian) VALUES (?, ?, NOW(), ?, ?, ?)");
if (!$stmt) {
    die("Prepare statement gagal: " . $koneksi->error);
}
$stmt->bind_param("siiii", $kode_transaksi, $id_kasir, $total_keranjang_aktual, $bayar, $kembalian);
$stmt->execute();

if ($stmt->error) {
    $_SESSION['pesan_error'] = "Gagal nyimpen transaksi: " . $stmt->error;
    header("Location: transaksi_baru.php");
    exit;
}

$id_transaksi = $stmt->insert_id;
$stmt->close();


// yg gratis subtotalnya di DB juga jadi 0
$stmt_detail = $koneksi->prepare("INSERT INTO detail_transaksi (id_transaksi, id_produk, qty, subtotal) VALUES (?, ?, ?, ?)");
if (!$stmt_detail) {
    die("Prepare detail gagal: " . $koneksi->error);
}

foreach ($keranjang as $item) {
    $id_produk = $item['id_produk'];
    $qty = $item['qty'];
    
    $subtotal_detail = (isset($item['is_gratis']) && $item['is_gratis'] === true) ? 0 : ($item['harga'] * $item['qty']);
    
    $stmt_detail->bind_param("iiii", $id_transaksi, $id_produk, $qty, $subtotal_detail);
    $stmt_detail->execute();

    if ($stmt_detail->error) {
        error_log("Error nyimpen detail produk ID " . $id_produk . ": " . $stmt_detail->error);
    }
}
$stmt_detail->close();

$_SESSION['transaksi_selesai'] = [
    'kode_transaksi' => $kode_transaksi,
    'total' => $total_keranjang_aktual, 
    'bayar' => $bayar,
    'kembalian' => $kembalian,
    'keranjang' => $keranjang, 
    'id_kasir' => $_SESSION['id_kasir'],
    'diskon_total' => $diskon_item_gratis 
];

unset($_SESSION['keranjang']);

header("Location: transaksi_selesai.php");
exit;
?>