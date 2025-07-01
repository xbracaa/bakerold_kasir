<?php
session_start();
if (!isset($_SESSION['id_kasir'])) {
    header("Location: modules/akun/login.php");
    exit;
} else {
    header("Location: modules/home.php"); // atau index dashboard kamu
    exit;
}
