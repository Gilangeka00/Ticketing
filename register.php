<?php
// register.php

include 'config.php';
include 'header.php'; // Diasumsikan header.php sudah memulai session dengan session_start()

// Jika pengguna sudah login, langsung redirect ke index
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $allowedRoles = ['user', 'admin'];

    if (!in_array($role, $allowedRoles)) {
        $error = 'Role tidak valid. Silakan pilih kembali.';
    } elseif ($username !== '' && $password !== '') {
        // Cek apakah username sudah terdaftar
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username sudah terdaftar, silakan pilih yang lain.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (username, password, role, balance) VALUES (?, ?, ?, 0.00)'
            );
            if ($stmt->execute([$username, $hashedPassword, $role])) {
                $_SESSION['register_success'] = 'Registrasi berhasil! Silakan login.';
                header('Location: login.php');
                exit;
            } else {
                $error = 'Registrasi gagal. Silakan coba lagi.';
            }
        }
    } else {
        $error = 'Mohon isi semua field username, password, dan role.';
    }
}
?>

<!-- Tampilan Form -->
<div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-cover bg-center relative"
     style="background-image: url('https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');">

    <div class="absolute inset-0 bg-black opacity-30"></div>

    <div class="relative z-10 max-w-md w-full bg-white p-8 rounded-xl shadow-2xl space-y-6">
        <div>
            <h2 class="text-center text-3xl font-bold text-gray-800">
                Register Akun Baru
            </h2>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md text-sm" role="alert">
                <p class="font-semibold">Registrasi Gagal</p>
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <form class="space-y-6" method="POST" action="register.php">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input id="username" name="username" type="text" autocomplete="username" required
                       class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       placeholder="Masukkan username Anda" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="mt-1 relative">
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                           class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm pr-10" 
                           placeholder="Masukkan password Anda">
                    <button type="button" id="togglePassword" title="Show/Hide password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg id="eyeIconOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg id="eyeIconClosed" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .946-3.006 3.496-5.365 6.458-6.175M15 12a3 3 0 11-6 0 3 3 0 016 0zm6.458 3.175A9.959 9.959 0 0121.542 12c-1.274-4.057-5.064-7-9.542-7S2.458 7.943 1.184 12a10.034 10.034 0 002.39 3.75M3.5 3.5l17 17"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Daftar Sebagai</label>
                <select id="role" name="role" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="" disabled <?= empty($_POST['role']) ? 'selected' : '' ?>>-- Pilih Role --</option>
                    <option value="user" <?= ($_POST['role'] ?? '') === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Register
                </button>
            </div>
        </form>

        <div class="text-sm text-center">
            <p class="text-gray-600">Sudah punya akun?
                <a href="login.php" class="font-medium text-blue-600 hover:text-blue-700">
                    Login di sini
                </a>
            </p>
        </div>
    </div>
</div>

<script>
  const togglePasswordButton = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  const eyeIconOpen = document.getElementById('eyeIconOpen');
  const eyeIconClosed = document.getElementById('eyeIconClosed');

  togglePasswordButton.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    if (type === 'password') {
      eyeIconOpen.style.display = 'none';
      eyeIconClosed.style.display = 'block';
    } else {
      eyeIconOpen.style.display = 'block';
      eyeIconClosed.style.display = 'none';
    }
  });
</script>

<?php include 'footer.php'; ?>
