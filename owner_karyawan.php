<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: index.php');
    exit;
}

$accounts_file = 'accounts.txt';
$employees = [];
if (file_exists($accounts_file)) {
    $lines = file($accounts_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        list($username, $password, $role) = explode('|', $line);
        if ($role !== 'owner') {
            $employees[] = [
                'username' => $username,
                'password' => $password,
                'role' => $role,
            ];
        }
    }
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = $_POST['role'] ?? '';

        if ($username === '' || $password === '' || $role === '') {
            $errors[] = 'Semua field harus diisi.';
        } else {
            // Check if username exists
            foreach ($employees as $emp) {
                if ($emp['username'] === $username) {
                    $errors[] = 'Username sudah ada.';
                    break;
                }
            }
            if (empty($errors)) {
                $employees[] = [
                    'username' => $username,
                    'password' => $password,
                    'role' => $role,
                ];
                // Save to file
                $lines = [];
                foreach ($employees as $emp) {
                    $lines[] = implode('|', [$emp['username'], $emp['password'], $emp['role']]);
                }
                file_put_contents($accounts_file, implode("\n", $lines));
                $success = 'Karyawan berhasil ditambahkan.';
            }
        }
    } elseif ($action === 'delete') {
        $username = $_POST['delete_username'] ?? '';
        $employees = array_filter($employees, fn($emp) => $emp['username'] !== $username);
        // Save to file
        $lines = [];
        foreach ($employees as $emp) {
            $lines[] = implode('|', [$emp['username'], $emp['password'], $emp['role']]);
        }
        file_put_contents($accounts_file, implode("\n", $lines));
        $success = 'Karyawan berhasil dihapus.';
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manajemen Karyawan - Part Coffee</title>
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
        <h1 class="text-3xl font-bold text-coffee-brown mb-6">Manajemen Karyawan - Part Coffee</h1>
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
            <h2 class="text-xl font-semibold mb-4">Tambah Karyawan Baru</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add" />
                <div>
                    <label for="username" class="block font-semibold mb-1">Username</label>
                    <input type="text" id="username" name="username" required class="w-full border border-gray-300 rounded px-3 py-2" />
                </div>
                <div>
                    <label for="password" class="block font-semibold mb-1">Password</label>
                    <input type="password" id="password" name="password" required class="w-full border border-gray-300 rounded px-3 py-2" />
                </div>
                <div>
                    <label for="role" class="block font-semibold mb-1">Role</label>
                    <select id="role" name="role" required class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">Pilih role</option>
                        <option value="kasir">Kasir</option>
                        <option value="barista">Barista</option>
                        <option value="kitchen">Kitchen</option>
                        <option value="manajer">Manajer</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-coffee-brown text-white py-2 px-4 rounded hover:bg-coffee-dark transition">Tambah Karyawan</button>
                </div>
            </form>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-4">Daftar Karyawan</h2>
            <table class="min-w-full border border-gray-300 rounded-lg overflow-hidden">
                <thead class="bg-coffee-brown text-white">
                    <tr>
                        <th class="py-2 px-4 text-left">Username</th>
                        <th class="py-2 px-4 text-left">Role</th>
                        <th class="py-2 px-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                    <tr class="border-t border-gray-200">
                        <td class="py-2 px-4"><?= htmlspecialchars($emp['username']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($emp['role']) ?></td>
                        <td class="py-2 px-4">
                            <form method="POST" onsubmit="return confirm('Yakin ingin menghapus karyawan ini?');">
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="delete_username" value="<?= htmlspecialchars($emp['username']) ?>" />
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
