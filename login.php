<?php
// login.php

include 'config.php';
include 'header.php'; // Diasumsikan header.php sudah memulai session dengan session_start()

// Jika pengguna sudah login, redirect langsung berdasarkan role
if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'admin') {
        header('Location: dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '') {
        // Ambil user beserta saldo (password tersimpan dalam bentuk plain text)
        $stmt = $pdo->prepare('SELECT id, username, password, role, balance FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Cek user dan bandingkan password plain text
        // Cek user dan verifikasi password hash
if ($user && password_verify($password, $user['password'])) {
    // Simpan data user ke dalam session
    $_SESSION['user'] = [
        'id'       => $user['id'],
        'username' => $user['username'],
        'role'     => $user['role'],
        'balance'  => (float)$user['balance']
    ];

    // Redirect berdasarkan role
    if ($user['role'] === 'admin') {
        header('Location: dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
} else {
    $error = 'Username atau password salah.';
}

    } else {
        $error = 'Mohon isi semua field.';
    }
}
?>

<div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-cover bg-center relative"
     style="background-image: url('https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');">
    
    <div class="absolute inset-0 bg-black opacity-30"></div>

    <div class="relative z-10 max-w-md w-full bg-white p-8 rounded-xl shadow-2xl space-y-6">
        <div>
          <h2 class="text-center text-3xl font-bold text-gray-800">
            Login
          </h2>
        </div>

        <?php if (isset($_SESSION['register_success'])): ?>
          <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md text-sm" role="alert">
            <p class="font-semibold">Registrasi Berhasil</p>
            <p><?= htmlspecialchars($_SESSION['register_success']) ?></p>
          </div>
          <?php unset($_SESSION['register_success']); ?>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md text-sm" role="alert">
            <p class="font-semibold">Login Gagal</p>
            <p><?= htmlspecialchars($error) ?></p>
          </div>
        <?php endif; ?>

        <form class="space-y-6" method="POST" action="login.php">
          <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input id="username" name="username" type="text" autocomplete="username" required
                  class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  placeholder="Masukkan username Anda" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="mt-1 relative">
              <input id="password" name="password" type="password" autocomplete="current-password" required
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
            <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              Login
            </button>
          </div>
        </form>

        <div class="text-sm text-center">
          <p class="text-gray-600">Belum punya akun? 
            <a href="register.php" class="font-medium text-blue-600 hover:text-blue-700">
              Daftar di sini
            </a>
          </p>
        </div>
        
        <div class="relative flex pt-4 items-center">
            <div class="flex-grow border-t border-gray-300"></div>
            <span class="flex-shrink mx-4 text-gray-400 text-xs">Atau login dengan</span>
            <div class="flex-grow border-t border-gray-300"></div>
        </div>
        <div class="flex justify-center space-x-3 pt-3">
            <button type="button" title="Login dengan Google (belum berfungsi)" class="p-2.5 border border-gray-300 rounded-full hover:bg-gray-100 focus:outline-none transition-colors duration-150">
                <!-- Ikon Google -->
                <svg class="w-5 h-5" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"><path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/></svg>
            </button>
            <button type="button" title="Login dengan Facebook (belum berfungsi)" class="p-2.5 border border-gray-300 rounded-full hover:bg-gray-100 focus:outline-none transition-colors duration-150">
                <!-- Ikon Facebook -->
                <svg class="w-5 h-5 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.676 0H1.324C.593 0 0 .593 0 1.324v21.352C0 23.407.593 24 1.324 24h11.494v-9.294H9.692v-3.622h3.126V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116c.73 0 1.323-.593 1.323-1.324V1.324C24 .593 23.407 0 22.676 0z"/></svg>
            </button>
        </div>
         <p class="mt-3 text-center text-xs text-gray-500">
            Login sosial belum berfungsi, hanya tampilan.
        </p>

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
