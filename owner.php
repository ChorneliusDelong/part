<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: index.php');
    exit;
}

// Dummy data for demonstration (to be replaced with JSON or session data)
$salesData = [
    'harian' => 1500000,
    'mingguan' => 10500000,
    'bulanan' => 45000000,
];

$karyawanPerformance = [
    ['nama' => 'Budi', 'penjualan' => 120],
    ['nama' => 'Sari', 'penjualan' => 95],
    ['nama' => 'Andi', 'penjualan' => 80],
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner - Part Coffee</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="script.js" defer></script>
</head>
<body class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-5xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-coffee-brown mb-6">Dashboard Pemilik - Part Coffee</h1>
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
        <section class="dashboard-stats space-y-8">
            <div>
                <h2 class="text-xl font-semibold mb-4">Statistik Penjualan</h2>
                <ul class="list-disc list-inside space-y-1 text-gray-700">
                    <li>Penjualan Harian: Rp <?= number_format($salesData['harian'], 0, ',', '.') ?></li>
                    <li>Penjualan Mingguan: Rp <?= number_format($salesData['mingguan'], 0, ',', '.') ?></li>
                    <li>Penjualan Bulanan: Rp <?= number_format($salesData['bulanan'], 0, ',', '.') ?></li>
                </ul>
            </div>
            <div>
                <h2 class="text-xl font-semibold mb-4">Performa Karyawan</h2>
                <table class="min-w-full border border-gray-300 rounded-lg overflow-hidden">
                    <thead class="bg-coffee-brown text-white">
                        <tr>
                            <th class="py-2 px-4 text-left">Nama</th>
                            <th class="py-2 px-4 text-left">Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($karyawanPerformance as $karyawan): ?>
                        <tr class="border-t border-gray-200">
                            <td class="py-2 px-4"><?= htmlspecialchars($karyawan['nama']) ?></td>
                            <td class="py-2 px-4"><?= $karyawan['penjualan'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>
</html>

<style>
    .text-coffee-brown {
        color: #6f4e37;
    }
    .bg-coffee-brown {
        background-color: #6f4e37;
    }
</style>
