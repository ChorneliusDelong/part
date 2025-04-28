<?php
session_start();

// Load menu data dari menu.txt
$menu = [];
$lines = file('menu.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    list($id, $nama, $harga, $deskripsi, $gambar) = explode('|', $line);
    $menu[] = [
        'id' => (int)$id,
        'nama' => $nama,
        'harga' => (int)$harga,
        'deskripsi' => $deskripsi,
        'gambar' => $gambar,
    ];
}

// Inisialisasi keranjang belanja di sesi
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Proses permintaan POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $id = (int)$_POST['menu_id'];
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $quantity;
        
        // Tambahkan respons AJAX sederhana jika ini adalah permintaan AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'cartCount' => array_sum($_SESSION['cart'])]);
            exit;
        }
    } elseif (isset($_POST['remove_from_cart'])) {
        $id = (int)$_POST['menu_id'];
        unset($_SESSION['cart'][$id]);
        
        // Jika ini adalah permintaan AJAX untuk modul keranjang
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true]);
            exit;
        }
    } elseif (isset($_POST['checkout'])) {
        // Data dari form checkout
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $table_number = trim($_POST['table_number'] ?? '');
        $payment_method = $_POST['payment_method'] ?? '';

        // Validasi sederhana
        $errors = [];
        if ($name === '') $errors[] = 'Nama harus diisi.';
        if ($phone === '') $errors[] = 'Nomor WhatsApp harus diisi.';
        if ($table_number === '') $errors[] = 'Nomor meja harus diisi.';
        if (empty($_SESSION['cart'])) $errors[] = 'Keranjang belanja kosong.';
        if (!in_array($payment_method, ['bank_transfer', 'e_wallet', 'cod'])) $errors[] = 'Metode pembayaran tidak valid.';

        if (empty($errors)) {
            // Simpan pesanan ke file orders.json
            $order = [
                'id' => uniqid('order_'),
                'name' => $name,
                'phone' => $phone,
                'table_number' => $table_number,
                'payment_method' => $payment_method,
                'items' => $_SESSION['cart'],
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $orders_file = 'orders.json';
            $orders = file_exists($orders_file) ? (json_decode(file_get_contents($orders_file), true) ?? []) : [];
            $orders[] = $order;
            file_put_contents($orders_file, json_encode($orders, JSON_PRETTY_PRINT));

            // Kirim link WhatsApp
            $whatsapp_number = '6281234567890'; // ganti sesuai kebutuhan
            $message = "Pesanan baru diterima!\nID: {$order['id']}\nNama: {$order['name']}\nWhatsApp: {$order['phone']}\nMeja: {$order['table_number']}\nPembayaran: {$order['payment_method']}\nTotal Item: " . array_sum($order['items']);
            $wa_url = "https://wa.me/{$whatsapp_number}?text=" . urlencode($message);

            echo "<script>window.open('{$wa_url}', '_blank');</script>";

            // Reset keranjang
            $_SESSION['cart'] = [];
            $orderPlaced = true;
            $orderId = $order['id'];
        }
    }
}

// Hitung total items dan harga keranjang
$cartItemCount = array_sum($_SESSION['cart'] ?? []);
$cartTotal = 0;
foreach ($_SESSION['cart'] as $cartId => $qty) {
    $item = array_filter($menu, fn($m) => $m['id'] === $cartId);
    if (!empty($item)) {
        $item = reset($item);
        $cartTotal += $item['harga'] * $qty;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Menu Pelanggan - Part Coffee</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .text-coffee-brown { color: #6f4e37; }
        .bg-coffee-brown { background-color: #6f4e37; }
        .bg-coffee-dark { background-color: #5a3e2b; }
        .cart-dropdown {
            display: none;
            position: absolute;
            right: 0;
            width: 320px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 50;
            max-height: 80vh;
            overflow-y: auto;
        }
        .cart-icon:hover .cart-dropdown,
        .cart-dropdown:hover {
            display: block;
        }
        @media (max-width: 640px) {
            .cart-dropdown {
                width: calc(100vw - 2rem);
                left: 1rem;
                right: 1rem;
            }
        }
    </style>
    <script defer src="script.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">



<div class="max-w-6xl mx-auto p-4 sm:p-6 md:p-8">

    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-10">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
        <h1 class="text-2xl font-extrabold text-coffee-brown">Part Coffee</h1>
        
        <div class="flex items-center space-x-6">
            <nav class="hidden sm:flex space-x-6 text-sm font-semibold text-coffee-brown">
                <a href="customer.php" class="hover:underline">Menu</a>
                <a href="logout.php" class="hover:underline">Keluar</a>
            </nav>
            
            <!-- Cart Icon with Dropdown -->
            <div class="cart-icon relative">
                <button class="flex items-center text-coffee-brown focus:outline-none">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <?php if ($cartItemCount > 0): ?>
                    <span class="bg-red-500 text-white rounded-full text-xs px-2 py-1 ml-1"><?= $cartItemCount ?></span>
                    <?php endif; ?>
                </button>
                
                <!-- Cart Dropdown -->
                <div class="cart-dropdown p-4">
                    <h3 class="font-bold text-lg border-b pb-2 mb-3">Keranjang Belanja</h3>
                    
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <ul class="space-y-3 mb-4">
                            <?php
                            foreach ($_SESSION['cart'] as $cartId => $qty):
                                $item = array_filter($menu, fn($m) => $m['id'] === $cartId);
                                if (!empty($item)) {
                                    $item = reset($item);
                            ?>
                            <li class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <div>
                                    <span class="font-medium"><?= htmlspecialchars($item['nama']) ?></span>
                                    <div class="text-sm text-gray-600">
                                        <span><?= $qty ?> x Rp <?= number_format($item['harga'], 0, ',', '.') ?></span>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <span class="font-semibold">Rp <?= number_format($item['harga'] * $qty, 0, ',', '.') ?></span>
                                    <form method="POST" class="ml-2">
                                        <input type="hidden" name="menu_id" value="<?= $item['id'] ?>">
                                        <button type="submit" name="remove_from_cart" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>
                            <?php 
                                }
                            endforeach; 
                            ?>
                        </ul>
                        <div class="border-t border-gray-200 pt-3 mb-4">
                            <div class="flex justify-between font-bold text-lg">
                                <span>Total:</span>
                                <span>Rp <?= number_format($cartTotal, 0, ',', '.') ?></span>
                            </div>
                        </div>
                        <button id="checkoutBtn" class="w-full bg-coffee-brown text-white py-2 rounded-lg hover:bg-coffee-dark">
                            Checkout
                        </button>
                    <?php else: ?>
                        <p class="text-gray-600 text-center py-4">Keranjang kosong.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Mobile menu button -->
            <button class="sm:hidden text-coffee-brown focus:outline-none" id="mobile-menu-button">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </div>
    
    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden sm:hidden bg-white border-t border-gray-200 px-4 py-2">
        <nav class="flex flex-col space-y-2 text-sm font-semibold text-coffee-brown">
            <a href="customer.php" class="py-2 hover:underline">Menu</a>
            <a href="logout.php" class="py-2 hover:underline">Keluar</a>
        </nav>
    </div>
</header>
    <!-- Pencarian Menu -->
    <section class="mb-6">
        <input type="text" id="menuSearch" onkeyup="filterMenu()" placeholder="Cari menu..." class="w-full border border-gray-300 rounded px-3 py-2">
    </section>

    <!-- Daftar Menu -->
    <section class="menu-list mb-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
            <?php foreach ($menu as $item): ?>
            <div class="menu-item border rounded-lg p-6 shadow hover:shadow-xl transition flex flex-col bg-white">
                <img src="images/<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama']) ?>" class="w-full h-48 object-cover rounded mb-5 shadow-sm">
                <h3 class="menu-name text-xl font-semibold mb-2"><?= htmlspecialchars($item['nama']) ?></h3>
                <p class="text-gray-700 mb-4 flex-grow"><?= htmlspecialchars($item['deskripsi']) ?></p>
                <p class="font-bold text-lg mb-4">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                <form method="POST" class="mt-auto flex flex-col space-y-2 add-to-cart-form">
                    <input type="hidden" name="menu_id" value="<?= $item['id'] ?>">
                    <div class="flex items-center space-x-2">
                        <label for="quantity_<?= $item['id'] ?>" class="font-semibold">Jumlah:</label>
                        <input type="number" id="quantity_<?= $item['id'] ?>" name="quantity" value="1" min="1" class="border border-gray-300 rounded px-3 py-1 w-20">
                    </div>
                    <button type="submit" name="add_to_cart" class="w-full bg-coffee-brown text-white py-3 rounded-lg hover:bg-coffee-dark">
                        <i class="fas fa-cart-plus mr-2"></i>Tambah ke Keranjang
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Modal Checkout -->
    <div id="checkoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg max-w-lg w-full max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Checkout</h2>
                <button id="closeCheckoutModal" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label for="name" class="block font-semibold mb-1">Nama</label>
                    <input type="text" id="name" name="name" required class="w-full border rounded px-3 py-2" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div>
                    <label for="phone" class="block font-semibold mb-1">Nomor WhatsApp</label>
                    <input type="text" id="phone" name="phone" required class="w-full border rounded px-3 py-2" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div>
                    <label for="table_number" class="block font-semibold mb-1">Nomor Meja</label>
                    <input type="text" id="table_number" name="table_number" required class="w-full border rounded px-3 py-2" value="<?= htmlspecialchars($_POST['table_number'] ?? '') ?>">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Metode Pembayaran</label>
                    <select name="payment_method" required class="w-full border rounded px-3 py-2">
                        <option value="">Pilih metode pembayaran</option>
                        <option value="bank_transfer" <?= (($_POST['payment_method'] ?? '') === 'bank_transfer') ? 'selected' : '' ?>>Transfer Bank</option>
                        <option value="e_wallet" <?= (($_POST['payment_method'] ?? '') === 'e_wallet') ? 'selected' : '' ?>>E-Wallet</option>
                        <option value="cod" <?= (($_POST['payment_method'] ?? '') === 'cod') ? 'selected' : '' ?>>Bayar di Tempat</option>
                    </select>
                </div>
                <div>
                    <button type="submit" name="checkout" class="w-full bg-coffee-brown text-white py-2 rounded hover:bg-coffee-dark">Konfirmasi Pesanan</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($orderPlaced)): ?>
        <!-- Order Success Modal -->
        <div id="orderSuccessModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-md w-full">
                <div class="text-center">
                    <i class="fas fa-check-circle text-green-500 text-4xl mb-4"></i>
                    <h2 class="text-xl font-semibold mb-2">Pesanan Berhasil!</h2>
                    <p class="mb-4">Terima kasih! Pesanan Anda telah diterima.</p>
                    <p class="font-semibold mb-6">ID Pesanan Anda: <?= htmlspecialchars($orderId) ?></p>
                    <button id="closeSuccessModal" class="w-full bg-coffee-brown text-white py-2 rounded hover:bg-coffee-dark">
                        Kembali ke Menu
                    </button>
                </div>
            </div>
        </div>
        <script>
            document.getElementById('closeSuccessModal').addEventListener('click', function() {
                document.getElementById('orderSuccessModal').classList.add('hidden');
            });
        </script>
    <?php endif; ?>

    <!-- Lacak Pesanan -->
    <section class="order-status mb-8 bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Lacak Status Pesanan</h2>
        <form id="trackOrderForm" class="space-y-4">
            <div>
                <label for="order_id" class="block font-semibold mb-1">Masukkan ID Pesanan</label>
                <input type="text" id="order_id" name="order_id" required class="w-full border border-gray-300 rounded px-3 py-2">
            </div>
            <div>
                <button type="submit" class="w-full bg-coffee-brown text-white py-2 rounded hover:bg-coffee-dark">Lacak Pesanan</button>
            </div>
        </form>
        <div id="orderStatusResult" class="mt-4 p-4 border border-gray-300 rounded bg-gray-50 hidden"></div>
    </section>

</div>

<script>
    // Filter menu
    function filterMenu() {
        const input = document.getElementById('menuSearch').value.toLowerCase();
        document.querySelectorAll('.menu-item').forEach(item => {
            const name = item.querySelector('.menu-name').textContent.toLowerCase();
            item.style.display = name.includes(input) ? '' : 'none';
        });
    }
    
    // Mobile menu toggle
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenu.classList.toggle('hidden');
    });
    
    // Checkout modal
    const checkoutBtn = document.getElementById('checkoutBtn');
    const checkoutModal = document.getElementById('checkoutModal');
    const closeCheckoutModal = document.getElementById('closeCheckoutModal');
    
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            checkoutModal.classList.remove('hidden');
        });
    }
    
    if (closeCheckoutModal) {
        closeCheckoutModal.addEventListener('click', function() {
            checkoutModal.classList.add('hidden');
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === checkoutModal) {
            checkoutModal.classList.add('hidden');
        }
    });
    
    // Track order form
    document.getElementById('trackOrderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const orderId = document.getElementById('order_id').value.trim();
        const resultDiv = document.getElementById('orderStatusResult');

        if (!orderId) {
            resultDiv.textContent = 'Mohon masukkan ID pesanan.';
            resultDiv.classList.remove('hidden');
            return;
        }

        fetch('order_status_api.php?order_id=' + encodeURIComponent(orderId))
            .then(response => response.json())
            .then(data => {
                resultDiv.innerHTML = data.error ? data.error : '<strong>Status Pesanan:</strong> ' + data.status;
                resultDiv.classList.remove('hidden');
            })
            .catch(() => {
                resultDiv.textContent = 'Terjadi kesalahan saat mengambil status pesanan.';
                resultDiv.classList.remove('hidden');
            });
    });
    
    // AJAX add to cart (could be implemented for smoother experience)
    /*
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('customer.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    // This would require additional cart count element in the header
                }
            });
        });
    });
    */
</script>

</body>
</html>