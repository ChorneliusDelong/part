<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: index.php');
    exit;
}

// Load menu from menu.txt
$menu_file = 'menu.txt';
$menu = [];
if (file_exists($menu_file)) {
    $lines = file($menu_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
}

// Handle add, edit, delete actions
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $nama = trim($_POST['nama'] ?? '');
        $harga = (int)($_POST['harga'] ?? 0);
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $gambar = trim($_POST['gambar'] ?? '');

        if ($nama === '' || $harga <= 0) {
            $errors[] = 'Nama dan harga harus diisi dengan benar.';
        } else {
            $new_id = 1;
            if (!empty($menu)) {
                $ids = array_column($menu, 'id');
                $new_id = max($ids) + 1;
            }
            $menu[] = [
                'id' => $new_id,
                'nama' => $nama,
                'harga' => $harga,
                'deskripsi' => $deskripsi,
                'gambar' => $gambar,
            ];
            // Save to file
            $lines = [];
            foreach ($menu as $item) {
                $lines[] = implode('|', [$item['id'], $item['nama'], $item['harga'], $item['deskripsi'], $item['gambar']]);
            }
            file_put_contents($menu_file, implode("\n", $lines));
            $success = 'Menu berhasil ditambahkan.';
        }
    } elseif ($action === 'delete') {
        $delete_id = (int)($_POST['delete_id'] ?? 0);
        $menu = array_filter($menu, fn($item) => $item['id'] !== $delete_id);
        // Save to file
        $lines = [];
        foreach ($menu as $item) {
            $lines[] = implode('|', [$item['id'], $item['nama'], $item['harga'], $item['deskripsi'], $item['gambar']]);
        }
        file_put_contents($menu_file, implode("\n", $lines));
        $success = 'Menu berhasil dihapus.';
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manajemen Menu - Part Coffee</title>
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
    <div class="max-w-5xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-coffee-brown mb-6">Manajemen Menu - Part Coffee</h1>
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
            <h2 class="text-xl font-semibold mb-4">Tambah Menu Baru</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add" />
                <div>
                    <label for="nama" class="block font-semibold mb-1">Nama</label>
                    <input type="text" id="nama" name="nama" required class="w-full border border-gray-300 rounded px-3 py-2" />
                </div>
                <div>
                    <label for="harga" class="block font-semibold mb-1">Harga (Rp)</label>
                    <input type="number" id="harga" name="harga" required class="w-full border border-gray-300 rounded px-3 py-2" min="1" />
                </div>
                <div>
                    <label for="deskripsi" class="block font-semibold mb-1">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                </div>
                <div>
                    <label for="gambar" class="block font-semibold mb-1">Nama File Gambar</label>
                    <input type="text" id="gambar" name="gambar" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="contoh: espresso.jpg" />
                </div>
                <div>
                    <button type="submit" class="bg-coffee-brown text-white py-2 px-4 rounded hover:bg-coffee-dark transition">Tambah Menu</button>
                </div>
            </form>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-4">Daftar Menu</h2>
            <table class="min-w-full border border-gray-300 rounded-lg overflow-hidden">
                <thead class="bg-coffee-brown text-white">
                    <tr>
                        <th class="py-2 px-4 text-left">ID</th>
                        <th class="py-2 px-4 text-left">Nama</th>
                        <th class="py-2 px-4 text-left">Harga</th>
                        <th class="py-2 px-4 text-left">Deskripsi</th>
                        <th class="py-2 px-4 text-left">Gambar</th>
                        <th class="py-2 px-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menu as $item): ?>
                    <tr class="border-t border-gray-200">
                        <td class="py-2 px-4"><?= htmlspecialchars($item['id']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($item['nama']) ?></td>
                        <td class="py-2 px-4">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($item['deskripsi']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($item['gambar']) ?></td>
                        <td class="py-2 px-4">
                            <form method="POST" onsubmit="return confirm('Yakin ingin menghapus menu ini?');">
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="delete_id" value="<?= htmlspecialchars($item['id']) ?>" />
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
