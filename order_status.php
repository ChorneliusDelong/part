<?php
// order_status.php
// Live order status tracking page

$orderId = $_GET['id'] ?? '';
$orders_file = 'orders.json';
$order = null;

if ($orderId && file_exists($orders_file)) {
    $orders = json_decode(file_get_contents($orders_file), true) ?? [];
    foreach ($orders as $o) {
        if ($o['id'] === $orderId) {
            $order = $o;
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Status Pesanan - Part Coffee</title>
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
    <script>
        function fetchStatus() {
            fetch('order_status_api.php?id=<?= htmlspecialchars($orderId) ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        document.getElementById('order-status').textContent = data.status;
                    }
                });
        }
        setInterval(fetchStatus, 5000); // refresh every 5 seconds
        window.onload = fetchStatus;
    </script>
</head>
<body class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-coffee-brown mb-6">Status Pesanan Anda</h1>
        <?php if (!$order): ?>
            <p class="text-red-600 font-semibold">Pesanan dengan ID tersebut tidak ditemukan.</p>
        <?php else: ?>
            <p><strong>ID Pesanan:</strong> <?= htmlspecialchars($order['id']) ?></p>
            <p><strong>Nama:</strong> <?= htmlspecialchars($order['name']) ?></p>
            <p><strong>Telepon:</strong> <?= htmlspecialchars($order['phone']) ?></p>
            <p><strong>Alamat:</strong> <?= htmlspecialchars($order['address']) ?></p>
            <p><strong>Metode Pembayaran:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
            <p><strong>Status:</strong> <span id="order-status"><?= htmlspecialchars($order['status']) ?></span></p>
            <h2 class="text-xl font-semibold mt-6 mb-2">Daftar Item</h2>
            <ul class="list-disc list-inside text-gray-700">
                <?php
                // Load menu for item names
                $menu = [];
                $lines = file('menu.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    list($id, $nama, $harga, $deskripsi, $gambar) = explode('|', $line);
                    $menu[(int)$id] = $nama;
                }
                foreach ($order['items'] as $itemId):
                    $itemName = $menu[$itemId] ?? 'Unknown Item';
                ?>
                <li><?= htmlspecialchars($itemName) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
