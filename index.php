<?php
session_start();

$error = '';
$showLoginForm = false;
$role = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['role'])) {
        $role = $_POST['role'];
        if ($role === 'customer') {
            $_SESSION['role'] = 'customer';
            header('Location: customer.php');
            exit;
        } elseif ($role === 'owner' || $role === 'employee') {
            $showLoginForm = true;
        }
    } elseif (isset($_POST['username'], $_POST['password'], $_POST['login_role'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $login_role = $_POST['login_role'];

        $accounts = file('accounts.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $valid = false;
        foreach ($accounts as $line) {
            list($accUser, $accPass, $accRole) = explode('|', $line);
            if ($username === $accUser && $password === $accPass && $login_role === $accRole) {
                $valid = true;
                break;
            }
        }
        if ($valid) {
            $_SESSION['role'] = $login_role;
            $_SESSION['username'] = $username;
            header('Location: ' . ($login_role === 'owner' ? 'owner.php' : 'employee.php'));
            exit;
        } else {
            $error = 'Username atau password salah.';
            $showLoginForm = true;
            $role = $login_role;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Part Coffee - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-coffee-light to-coffee-dark min-h-screen flex items-center justify-center p-4">

<div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md text-center animate-fade-in">
    <div class="mb-6">
        <img src="logo.jpg" alt="Logo" class="mx-auto w-24 h-24 object-cover rounded-full shadow-md">
    </div>
    <h1 class="text-3xl font-bold text-coffee-brown mb-2">Selamat Datang</h1>
    <p class="text-gray-600 mb-6">di <strong>Part Coffee</strong></p>

    <?php if (!$showLoginForm): ?>
    <form method="POST" class="space-y-4">
        <button type="submit" name="role" value="owner" class="w-full py-3 bg-coffee-brown text-white rounded-xl hover:bg-coffee-dark transition transform hover:scale-105">Pemilik (Owner)</button>
        <button type="submit" name="role" value="employee" class="w-full py-3 bg-coffee-brown text-white rounded-xl hover:bg-coffee-dark transition transform hover:scale-105">Karyawan</button>
        <button type="submit" name="role" value="customer" class="w-full py-3 bg-coffee-brown text-white rounded-xl hover:bg-coffee-dark transition transform hover:scale-105">Pelanggan</button>
    </form>
    <?php else: ?>
    <div class="mb-4 text-gray-600">
        <p>Masuk sebagai <strong><?= htmlspecialchars($role) ?></strong></p>
    </div>
    <?php if ($error): ?>
    <div class="mb-4 text-red-500 font-semibold">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4 text-left">
        <input type="hidden" name="login_role" value="<?= htmlspecialchars($role) ?>">
        <div>
            <label for="username" class="block mb-1 font-semibold">Username</label>
            <input type="text" id="username" name="username" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-coffee-brown">
        </div>
        <div>
            <label for="password" class="block mb-1 font-semibold">Password</label>
            <input type="password" id="password" name="password" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-coffee-brown">
        </div>
        <button type="submit" class="w-full py-3 bg-coffee-brown text-white rounded-xl hover:bg-coffee-dark transition transform hover:scale-105">Masuk</button>
        <button type="submit" name="cancel" value="1" class="w-full py-3 border border-gray-300 rounded-xl hover:bg-gray-100 transition transform hover:scale-105 mt-2">Batal</button>
    </form>
    <?php endif; ?>
</div>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: scale(0.95);}
        to { opacity: 1; transform: scale(1);}
    }
    .animate-fade-in {
        animation: fade-in 0.5s ease-out forwards;
    }
    .text-coffee-brown {
        color: #6f4e37;
    }
    .bg-coffee-brown {
        background-color: #6f4e37;
    }
    .hover\:bg-coffee-dark:hover {
        background-color: #5a3e2b;
    }
    .bg-coffee-dark {
        background-color: #5a3e2b;
    }
    .from-coffee-light {
        --tw-gradient-from: #d7ccc8;
        --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(215, 204, 200, 0));
    }
    .to-coffee-dark {
        --tw-gradient-to: #5a3e2b;
    }
</style>

</body>
</html>
