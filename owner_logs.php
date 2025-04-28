<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: index.php');
    exit;
}

$log_file = 'activity.log';
$logs = [];
if (file_exists($log_file)) {
    $logs = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
} else {
    $logs[] = 'No activity logs found.';
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Activity Logs - Part Coffee</title>
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
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-5xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-coffee-brown mb-6">Activity Logs - Part Coffee</h1>
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
            <h2 class="text-xl font-semibold mb-4">Log Aktivitas Pengguna</h2>
            <pre class="bg-gray-100 p-4 rounded border border-gray-300 h-96 overflow-auto"><?= htmlspecialchars(implode("\n", $logs)) ?></pre>
        </section>
    </div>
</body>
</html>
