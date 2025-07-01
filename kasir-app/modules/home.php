<?php
session_start();
include("../config/db.php"); // Ensure the path to db.php is correct

// Check if the user is logged in
if (!isset($_SESSION['id_kasir'])) {
    header("Location: akun/login.php");
    exit;
}

// Ensure database connection is established
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Get today's date
$today = date('Y-m-d');

// Query to get total transactions for today
$query_total_transaksi = $koneksi->query("
    SELECT COUNT(id_transaksi) AS total_transaksi_hari_ini
    FROM transaksi
    WHERE DATE(tanggal) = '$today'
");
$data_total_transaksi = $query_total_transaksi->fetch_assoc();
$total_transaksi_hari_ini = $data_total_transaksi['total_transaksi_hari_ini'] ?? 0; // Default to 0 if null

// Query to get total revenue for today
$query_total_omset = $koneksi->query("
    SELECT SUM(total) AS total_omset_hari_ini
    FROM transaksi
    WHERE DATE(tanggal) = '$today'
");
$data_total_omset = $query_total_omset->fetch_assoc();
$total_omset_hari_ini = $data_total_omset['total_omset_hari_ini'] ?? 0; // Default to 0 if null

// Format revenue for display
$formatted_omset = number_format($total_omset_hari_ini, 0, ',', '.');

// Dapatkan nama kasir dari sesi untuk ditampilkan di top nav
$nama_kasir_login = $_SESSION['nama_kasir'] ?? 'Kasir';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir - Baker Old</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-brown': '#5A3F2B',
                        'secondary-brown': '#8B6F5A',
                        'light-brown': '#D4B29A',
                        'cream': '#FFF8E1',
                        'yellow-accent': '#FFD54F',
                        'dark-yellow-accent': '#FFA000',
                        'text-dark': '#333',
                        'text-light': '#fff',
                        'success-green': '#66BB6A',
                        'error-red': '#EF5350',
                        'info-blue': '#1E88E5',
                        // These custom gray colors might not be needed anymore if using primary-brown for sidebar
                        'gray-800-custom': '#2D3748', 
                        'gray-700-custom': '#4A5568',
                        'gray-400-custom': '#A0AEC0',
                    }
                }
            }
        }
    </script>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* Base styles from previous CSS, adapted to Tailwind classes */
        #sidebar {
            transition: all 0.3s ease;
        }
        
        #sidebar.collapsed {
            margin-left: -250px;
        }
        
        #main-content {
            transition: margin-left 0.3s ease;
        }
        
        #main-content.expanded {
            margin-left: 0;
        }
        
        /* Active menu item styling, using custom Tailwind colors */
        .menu-item.active {
            background-color: var(--tw-colors-secondary-brown); /* Using secondary brown as active */
            color: var(--tw-colors-text-light);
        }
        
        /* Adjusted hover for menu items when sidebar is primary-brown */
        .menu-item:hover {
            background-color: var(--tw-colors-secondary-brown); /* Slightly lighter brown on hover */
        }
        
        .menu-item.active:hover {
            background-color: var(--tw-colors-dark-yellow-accent); /* Darker yellow on hover for active */
        }

        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                z-index: 1000;
                height: 100vh;
            }
            
            #sidebar.collapsed {
                margin-left: -250px;
            }
            
            #main-content {
                margin-left: 0 !important; /* Override default margin on mobile */
            }
            
            #overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0,0,0,0.5);
                z-index: 900;
            }
            
            #overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body class="bg-cream font-sans antialiased">
    <div id="overlay"></div>
    
    <nav class="bg-primary-brown text-text-light p-4 fixed top-0 left-0 w-full z-50 shadow-lg">
        <div class="container mx-auto flex justify-between items-center px-4 md:px-0">
            <div class="flex items-center space-x-4">
                <button id="sidebar-toggle" class="text-text-light focus:outline-none md:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h1 class="text-xl font-bold flex items-center">
                    <i class="fas fa-cash-register mr-3"></i>
                    Baker OLD 309 - Jayaraga Garut
                </h1>
            </div>
            <div>
                <span class="mr-4 hidden md:inline">Halo, <?= htmlspecialchars($nama_kasir_login) ?></span>
                <a href="akun/logout.php" onclick="return confirm('Yakin ingin logout?')" class="bg-error-red hover:bg-red-700 px-4 py-2 rounded-md transition duration-200 ease-in-out text-sm">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <aside id="sidebar" class="bg-primary-brown text-text-light w-64 fixed top-16 left-0 h-[calc(100vh-4rem)] shadow-xl z-40">
        <div class="p-4 border-b border-secondary-brown flex items-center justify-center">
            <div class="flex items-center">
                <img src="../images/profil.jpg" alt="Foto Profil Admin" 
                     class="rounded-full mr-3 border-2 border-yellow-accent w-10 h-10 object-cover">
                <div>
                    <p class="font-medium text-lg"><?= htmlspecialchars($nama_kasir_login) ?></p>
                    <p class="text-xs text-light-brown">Kasir</p>
                </div>
            </div>
        </div>
        
        <nav class="p-2">
            <ul>
                <li>
                    <a href="home.php" class="menu-item active flex items-center p-3 rounded-lg transition duration-200 hover:bg-secondary-brown my-1">
                        <i class="fas fa-home w-6 text-center mr-3"></i>
                        <span>Beranda</span>
                    </a>
                </li>
                <li>
                    <a href="kasir/transaksi_baru.php" class="menu-item flex items-center p-3 rounded-lg transition duration-200 hover:bg-secondary-brown my-1">
                        <i class="fas fa-shopping-cart w-6 text-center mr-3"></i>
                        <span>Transaksi Baru</span>
                    </a>
                </li>
                <li>
                    <a href="produk/produk.php" class="menu-item flex items-center p-3 rounded-lg transition duration-200 hover:bg-secondary-brown my-1">
                        <i class="fas fa-bread-slice w-6 text-center mr-3"></i>
                        <span>Data Produk</span>
                    </a>
                </li>
                <li>
                    <a href="detail transaksi/transaksi.php" class="menu-item flex items-center p-3 rounded-lg transition duration-200 hover:bg-secondary-brown my-1">
                        <i class="fas fa-history w-6 text-center mr-3"></i>
                        <span>Riwayat Transaksi</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="absolute bottom-0 w-full p-4 border-t border-secondary-brown">
            <div class="text-center text-xs text-light-brown">
                &copy; Baker Old <?= date('Y') ?>
            </div>
        </div>
    </aside>

    <main id="main-content" class="ml-64 mt-16 p-8 transition-all duration-300 bg-cream min-h-[calc(100vh-4rem)]">
        <div class="container mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8 border-t-4 border-yellow-accent">
                <h2 class="text-2xl font-bold text-text-dark mb-3 flex items-center">
                    <i class="fas fa-chart-line text-info-blue mr-3 text-3xl"></i>
                    Dashboard Kasir
                </h2>
                <p class="text-gray-600 mb-4">Selamat datang kembali! Pantau performa toko Anda hari ini.</p>
                <div class="flex flex-wrap items-center text-sm text-gray-500">
                    <div class="flex items-center mr-6 mb-2 md:mb-0">
                        <i class="fas fa-calendar-day mr-2 text-primary-brown"></i>
                        <span id="current-date" class="font-medium"></span>
                    </div>
                    <div class="flex items-center mb-2 md:mb-0">
                        <i class="fas fa-clock mr-2 text-primary-brown"></i>
                        <span id="current-time" class="font-medium"></span>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden border-b-4 border-info-blue">
                    <div class="bg-info-blue p-4 text-text-light flex items-center justify-between">
                        <h3 class="font-bold text-lg">Total Transaksi Hari Ini</h3>
                        <i class="fas fa-receipt text-2xl opacity-75"></i>
                    </div>
                    <div class="p-6 text-center">
                        <p class="text-5xl font-extrabold text-text-dark mb-2 leading-tight"><?= $total_transaksi_hari_ini ?></p>
                        <p class="text-gray-500 text-lg">Transaksi</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-lg overflow-hidden border-b-4 border-success-green">
                    <div class="bg-success-green p-4 text-text-light flex items-center justify-between">
                        <h3 class="font-bold text-lg">Total Omset Hari Ini</h3>
                        <i class="fas fa-dollar-sign text-2xl opacity-75"></i>
                    </div>
                    <div class="p-6 text-center">
                        <p class="text-5xl font-extrabold text-text-dark mb-2 leading-tight">Rp <?= $formatted_omset ?></p>
                        <p class="text-gray-500 text-lg">Rupiah</p>
                    </div>
                </div>
            </div>

            </div>
    </main>

    <script>
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const overlay = document.getElementById('overlay');
        
        let sidebarCollapsed = false;
        
        function toggleSidebar() {
            sidebarCollapsed = !sidebarCollapsed;
            
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                overlay.classList.add('show');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                overlay.classList.remove('show');
            }
        }
        
        // Event listeners for sidebar toggle and overlay click
        sidebarToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
        
        // Highlight active menu item (basic implementation for 'home.php')
        document.addEventListener('DOMContentLoaded', () => {
            const currentPath = window.location.pathname.split('/').pop();
            document.querySelectorAll('.menu-item').forEach(item => {
                const linkPath = item.getAttribute('href').split('/').pop();
                if (currentPath === linkPath || (currentPath === '' && linkPath === 'home.php')) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        });

        // Update current date and time
        function updateDateTime() {
            const dateElement = document.getElementById('current-date');
            const timeElement = document.getElementById('current-time');

            // Initial update
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            dateElement.textContent = now.toLocaleDateString('id-ID', options);
            timeElement.textContent = now.toLocaleTimeString('id-ID');
            
            // Update time every second
            setInterval(() => {
                const now = new Date();
                timeElement.textContent = now.toLocaleTimeString('id-ID');
            }, 1000);
        }
        
        updateDateTime();
        
        // Handle window resize for sidebar behavior
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                overlay.classList.remove('show');
                sidebarCollapsed = false;
            }
        });
    </script>
</body>
</html>