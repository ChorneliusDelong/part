<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: index.php');
    exit;
}

$promo_file = 'promotions.json';
$promotions = [];
if (file_exists($promo_file)) {
    $promotions = json_decode(file_get_contents($promo_file), true) ?? [];
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $discount = (float)($_POST['discount'] ?? 0);

        if ($code === '' || $discount <= 0) {
            $errors[] = 'Kode promo dan diskon harus diisi dengan benar.';
        } else {
            // Check if code exists
            foreach ($promotions as $promo) {
                if ($promo['code'] === $code) {
                    $errors[] = 'Kode promo sudah ada.';
                    break;
                }
            }
            if (empty($errors)) {
                $promotions[] = [
                    'code' => $code,
                    'description' => $description,
                    'discount' => $discount,
                ];
                file_put_contents($promo_file, json_encode($promotions, JSON_PRETTY_PRINT));
                $success = 'Promo berhasil ditambahkan.';
            }
        }
    } elseif ($action === 'delete') {
        $code = $_POST['delete_code'] ?? '';
        $promotions = array_filter($promotions, fn($p) => $p['code'] !== $code);
        file_put_contents($promo_file, json_encode($promotions, JSON_PRETTY_PRINT));
        $success = 'Promo berhasil dihapus.';
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pengaturan Promosi - Part Coffee</title>
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
        <h1 class="text-3xl font-bold text-coffee-brown mb-6">Pengaturan Promosi - Part Coffee</h1>
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

        <?php if ($errors): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Tambah Promo Baru</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add" />
                <div>
                    <label for="code" class="block font-semibold mb-1">Kode Promo</label>
                    <input type="text" id="code" name="code" required class="w-full border border-gray-300 rounded px-3 py-2" />
                </div>
                <div>
                    <label for="description" class="block font-semibold mb-1">Deskripsi</label>
                    <textarea id="description" name="description" class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                </div>
                <div>
                    <label for="discount" class="block font-semibold mb-1">Diskon (%)</label>
                    <input type="number" id="discount" name="discount" required min="1" max="100" class="w-full border border-gray-300 rounded px-3 py-2" />
                </div>
                <div>
                    <button type="submit" class="bg-coffee-brown text-white py-2 px-4 rounded hover:bg-coffee-dark transition">Tambah Promo</button>
                </div>
            </form>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-4">Daftar Promo</h2>
            <table class="min-w-full border border-gray-300 rounded-lg overflow-hidden">
                <thead class="bg-coffee-brown text-white">
                    <tr>
                        <th class="py-2 px-4 text-left">Kode Promo</th>
                        <th class="py-2 px-4 text-left">Deskripsi</th>
                        <th class="py-2 px-4 text-left">Diskon (%)</th>
                        <th class="py-2 px-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promotions as $promo): ?>
                    <tr class="border-t border-gray-200">
                        <td class="py-2 px-4"><?= htmlspecialchars($promo['code']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($promo['description']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($promo['discount']) ?></td>
                        <td class="py-2 px-4">
                            <form method="POST" onsubmit="return confirm('Yakin ingin menghapus promo ini?');">
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="delete_code" value="<?= htmlspecialchars($promo['code']) ?>" />
                                <button type="submit" class="bg-red-600 text-white py-1 px-3 rounded hover:bg-red-700 transition">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
