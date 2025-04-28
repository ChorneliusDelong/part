<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header('Location: index.php');
    exit;
}

$orders_file = 'orders.json';
$orders = [];
if (file_exists($orders_file)) {
    $orders = json_decode(file_get_contents($orders_file), true) ?? [];
}

$stock_file = 'stock.json';
$stock = [];
if (file_exists($stock_file)) {
    $stock = json_decode(file_get_contents($stock_file), true) ?? [];
} else {
    // Default stock if file not found
    $stock = [
        ['item' => 'Biji Kopi', 'stok' => 20],
        ['item' => 'Susu', 'stok' => 15],
        ['item' => 'Gula', 'stok' => 30],
    ];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Karyawan - Part Coffee</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="script.js" defer></script>
</head>
<body class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-5xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-coffee-brown mb-6">Dashboard Karyawan - Part Coffee</h1>
        <nav class="mb-8 space-x-4 text-sm font-semibold text-coffee-brown">
            <a href="employee.php" class="hover:underline">Pesanan</a>
            <span>|</span>
            <a href="employee_stock.php" class="hover:underline">Manajemen Stok</a>
            <span>|</span>
            <a href="employee_chat.php" class="hover:underline">Komunikasi Internal</a>
            <span>|</span>
            <a href="logout.php" class="hover:underline">Logout</a>
        </nav>
        <section class="orders mb-8">
            <h2 class="text-xl font-semibold mb-4">Pesanan Masuk</h2>
            <table class="min-w-full border border-gray-300 rounded-lg overflow-hidden">
                <thead class="bg-coffee-brown text-white">
                    <tr>
                        <th class="py-2 px-4 text-left">ID Pesanan</th>
                        <th class="py-2 px-4 text-left">Menu</th>
                        <th class="py-2 px-4 text-left">Status</th>
                        <th class="py-2 px-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr class="border-t border-gray-200">
                        <td class="py-2 px-4"><?= htmlspecialchars($order['id']) ?></td>
                        <td class="py-2 px-4">
                            <?php
                            // Load menu names for items
                            $menu = [];
                            $lines = file('menu.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                            foreach ($lines as $line) {
                                list($id, $nama, $harga, $deskripsi, $gambar) = explode('|', $line);
                                $menu[(int)$id] = $nama;
                            }
                            $itemNames = [];
                            foreach ($order['items'] as $itemId) {
                                $itemNames[] = $menu[$itemId] ?? 'Unknown Item';
                            }
                            echo htmlspecialchars(implode(', ', $itemNames));
                            ?>
                        </td>
                        <td class="py-2 px-4"><?= htmlspecialchars($order['status']) ?></td>
                        <td class="py-2 px-4">
                            <form method="POST" action="update_order.php">
                                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                                <select name="status" class="border rounded px-2 py-1">
                                    <option value="Diproses" <?php if ($order['status'] === 'Diproses') echo 'selected'; ?>>Diproses</option>
                                    <option value="Siap" <?php if ($order['status'] === 'Siap') echo 'selected'; ?>>Siap</option>
                                    <option value="Dikirim" <?php if ($order['status'] === 'Dikirim') echo 'selected'; ?>>Dikirim</option>
                                </select>
                                <button type="submit" class="ml-2 bg-coffee-brown text-white px-3 py-1 rounded hover:bg-coffee-dark transition">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <section class="stock">
            <h2 class="text-xl font-semibold mb-4">Stok Bahan</h2>
            <table class="min-w-full border border-gray-300 rounded-lg overflow-hidden">
                <thead class="bg-coffee-brown text-white">
                    <tr>
                        <th class="py-2 px-4 text-left">Item</th>
                        <th class="py-2 px-4 text-left">Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stock as $item): ?>
                    <tr class="border-t border-gray-200">
                        <td class="py-2 px-4"><?= htmlspecialchars($item['item']) ?></td>
                        <td class="py-2 px-4"><?= $item['stok'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
    .bg-coffee-dark {
        background-color: #5a3e2b;
    }
</style>
