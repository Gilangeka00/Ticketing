<?php
// header.php
// PASTIKAN TIDAK ADA TEKS LAIN SEBELUM BARIS INI

// Hanya panggil session_start() sekali di sini
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Logika untuk memastikan 'balance' ada di session
if (isset($_SESSION['user']) && !isset($_SESSION['user']['balance'])) {
  // Opsi: default ke 0, atau coba fetch dari DB jika $pdo tersedia dari config.php
  // Ini adalah contoh, pastikan $pdo tersedia jika Anda uncomment
  /*
  if (file_exists('config.php')) {
      include_once 'config.php';
  } elseif (file_exists('../config.php')) {
      include_once '../config.php';
  }
  if (isset($pdo)) {
      $stmtBal = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
      $stmtBal->execute([$_SESSION['user']['id']]);
      $bal = $stmtBal->fetchColumn();
      $_SESSION['user']['balance'] = $bal !== false ? (float)$bal : 0.00;
  } else {
      $_SESSION['user']['balance'] = 0.00; // Fallback
  }
  */
  $_SESSION['user']['balance'] = $_SESSION['user']['balance'] ?? 0.00; // PHP 7.0+
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Ticketing Website</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes gradient-move {
      0% {
        background-position: 0% center;
      }

      100% {
        background-position: 100% center;
      }
    }

    .animate-gradient {
      animation: gradient-move 3s linear infinite alternate;
    }
  </style>

</head>

<body class="bg-gray-100 <?= !$isAuthPage ? 'pt-[50px]' : '' ?>">

  <nav class="bg-white shadow-md fixed top-0 left-0 right-0 z-50">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center py-3">

        <div>
          <a href="index.php"
             class="text-2xl font-bold bg-gradient-to-r from-blue-900 to-blue-300 bg-[length:200%_auto] bg-clip-text text-transparent animate-gradient">
            Ticketing</a>
        </div>

        <div class="flex items-center space-x-3 md:space-x-4">

          <?php
          $currentPage = basename($_SERVER['PHP_SELF']);
          $isAuthPage = in_array($currentPage, ['login.php', 'register.php']);
          ?>

          <?php if (isset($_SESSION['user'])): ?>
            <div class="hidden sm:flex items-center space-x-3 md:space-x-4">
              <span class="text-sm text-gray-700">
                Hello, <strong class="font-medium"><?= htmlspecialchars($_SESSION['user']['username']) ?></strong>
              </span>

              <span class="text-sm text-gray-700 bg-green-100 px-3 py-1 rounded-full shadow-sm">
                Balance: <strong class="font-semibold text-green-700">Rp
                  <?= number_format($_SESSION['user']['balance'] ?? 0.00, 2, ',', '.') ?></strong>
              </span>
            </div>

            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
              <a href="dashboard.php"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors px-2 py-1 rounded hover:bg-blue-50 whitespace-nowrap">Ticket
                Dashboard</a>
              <a href="admin_manage_users.php"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors px-2 py-1 rounded hover:bg-blue-50 hidden md:inline whitespace-nowrap">Manage
                Users</a>
              <a href="admin_reports.php"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors px-2 py-1 rounded hover:bg-blue-50 hidden md:inline">Laporan
                Transaksi</a>
              <a href="pengaduan_tampilkan.php"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors px-2 py-1 rounded hover:bg-blue-50 hidden md:inline">Laporan
                Pengguna</a>
            <?php else: ?>
              <a href="about_us.php"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors px-2 py-1 rounded hover:bg-blue-50">About
                Us</a>
              <a href="saldo.php"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors px-2 py-1 rounded hover:bg-blue-50">My
                Balance</a>
              <a href="my_tickets.php"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors px-2 py-1 rounded hover:bg-blue-50">My
                Tickets</a>
              <a href="pengaduan.php"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors px-2 py-1 rounded hover:bg-blue-50">Pengaduan</a>
            <?php endif; ?>

            <a href="logout.php"
              class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md text-sm font-medium transition-colors shadow hover:shadow-md">Logout</a>
          <?php else: ?>
            <?php if (!$isAuthPage): ?>
              <a href="saldo.php" class="text-sm text-gray-400 cursor-not-allowed px-2 py-1"
                title="Login to access your balance">My Balance</a>
              <a href="my_tickets.php" class="text-sm text-gray-400 cursor-not-allowed px-2 py-1 hidden sm:inline"
                title="Login to see your tickets">My Tickets</a>
              <a href="pengaduan_tampilkan.php" class="text-sm text-gray-400 cursor-not-allowed px-2 py-1 hidden sm:inline"
                title="Login to see your tickets">Pengaduan</a>
            <?php endif; ?>
            <a href="login.php"
              class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors px-2 py-1 rounded hover:bg-blue-50">Login</a>
            <a href="register.php"
              class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium transition-colors shadow hover:shadow-md">Register</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>