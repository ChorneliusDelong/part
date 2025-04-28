<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: index.php');
    exit;
}

$orders_file = 'orders.json';
$orders = [];
if (file_exists($orders_file)) {
    $orders = json_decode(file_get_contents($orders_file), true) ?? [];
}

// Calculate total income from orders with status 'pending', 'diproses', 'siap', 'dikirim', 'selesai'
$total_income = 0;
foreach ($orders as $order) {
    if (in_array(strtolower($order['status']), ['pending', 'diproses', 'siap', 'dikirim', 'selesai'])) {
        // Calculate order total
        $order_total = 0;
        $menu_file = 'menu.txt';
        $menu = [];
        if (file_exists($menu_file)) {
            $lines = file($menu_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                list($id, $nama, $harga, $deskripsi, $gambar) = explode('|', $line);
                $menu[(int)$id] = (int)$harga;
            }
        }
        foreach ($order['items'] as $itemId) {
            $order_total += $menu[$itemId] ?? 0;
        }
        $total_income += $order_total;
    }
}

// For simplicity, expenses and profit are dummy values
$total_expenses = 10000000; // Example expenses
$profit = $total_income - $total_expenses;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Laporan Keuangan - Part Coffee</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <style>
        .text-coffee-brown {
            color: #6f4e37;
        }
        .bg-coffee-brown {
            background-color: #6f4e37;
        }
        .bg-coffee-dark {
            background-color: #5a3e2b;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-coffee-brown mb-6">Laporan Keuangan - Part Coffee</h1>
        <nav class="mb-8 space-x-4 text-sm font-semibold text-coffee-brown">
            <a href="owner.php" class="hover:underline">Dashboard</a>
            <span>|</span>
            <a href="owner_menu.php" class="hover:underline">Manajemen Menu</a>
            <span>|</span>
            <a href="owner_karyawan.php" class="hover:underline">Manajemen Karyawan</a>
            <span>|</span>
            <a href="owner_laporan.php" class="hover:underline">Laporan Keuangan</a>
            <span>|</span>
            <a href="owner_promo.php" class="hover:underline">Pengaturan Promosi</a>
            <span>|</span>
            <a href="owner_logs.php" class="hover:underline">Activity Logs</a>
            <span>|</span>
            <a href="logout.php" class="hover:underline">Logout</a>
        </nav>

        <section>
            <h2 class="text-xl font-semibold mb-4">Ringkasan Keuangan</h2>
            <ul class="list-disc list-inside space-y-2 text-gray-700">
                <li><strong>Total Pendapatan:</strong> Rp <?= number_format($total_income, 0, ',', '.') ?></li>
                <li><strong>Total Pengeluaran:</strong> Rp <?= number_format($total_expenses, 0, ',', '.') ?></li>
                <li><strong>Profitabilitas:</strong> Rp <?= number_format($profit, 0, ',', '.') ?></li>
            </ul>
        </section>
    </div>
</body>
</html>
