<?php
// saldo.php (My Balance Page)
include 'config.php';
include 'header.php'; // Session sudah dimulai di sini

// Pastikan user sudah login dan bukan admin (admin punya dashboard sendiri)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] === 'admin') {
    // Jika admin, redirect ke dashboard admin. Jika belum login, redirect ke login.
    if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
        header('Location: dashboard.php');
    } else {
        header('Location: login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
    }
    exit;
}

$user_id = $_SESSION['user']['id'];
$current_balance = $_SESSION['user']['balance'] ?? 0.00; // Ambil dari session untuk tampilan cepat

// Variabel untuk notifikasi
$success_message = '';
$error_message = '';

// Proses form Top-Up Saldo (Simulasi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['topup_amount'])) {
    $topup_amount = filter_var($_POST['topup_amount'], FILTER_VALIDATE_FLOAT);

    if ($topup_amount === false || $topup_amount <= 0) {
        $error_message = 'Jumlah top-up tidak valid. Harap masukkan angka positif.';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Update saldo pengguna di tabel 'users'
            $new_balance_after_topup = $current_balance + $topup_amount;
            $stmtUpdateBalance = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmtUpdateBalance->execute([$new_balance_after_topup, $user_id]);

            // 2. Catat transaksi top-up di 'balance_transactions'
            $description = 'Top-up saldo sebesar Rp ' . number_format($topup_amount, 2, ',', '.');
            $stmtLogTopup = $pdo->prepare("
                INSERT INTO balance_transactions 
                  (user_id, transaction_type, amount, description) 
                VALUES (?, 'topup', ?, ?)
            ");
            $stmtLogTopup->execute([$user_id, $topup_amount, $description]);

            $pdo->commit();

            // Update saldo di session
            $_SESSION['user']['balance'] = $new_balance_after_topup;
            $current_balance = $new_balance_after_topup; // Update variabel lokal juga

            $success_message = 'Top-up saldo sebesar Rp ' . number_format($topup_amount, 2, ',', '.') . ' berhasil!';

        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = 'Gagal melakukan top-up: ' . $e->getMessage();
        }
    }
}


// Ambil riwayat transaksi saldo untuk pengguna yang login
// Urutkan berdasarkan tanggal transaksi terbaru
$stmt_transactions = $pdo->prepare("
    SELECT transaction_type, amount, description, transaction_date, related_order_id 
    FROM balance_transactions 
    WHERE user_id = ? 
    ORDER BY transaction_date DESC, id DESC
    LIMIT 50 -- Batasi jumlah transaksi yang ditampilkan untuk performa
");
$stmt_transactions->execute([$user_id]);
$balance_history = $stmt_transactions->fetchAll();

?>

<div class="container mx-auto mt-8 mb-10 px-4 sm:px-6 lg:px-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">My Balance</h1>
        <p class="text-sm text-gray-500">View your current balance, transaction history, and top up your account.</p>
    </header>

    <?php if ($success_message): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-700 border border-green-300 rounded-md shadow-sm" role="alert">
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="mb-6 p-4 bg-red-100 text-red-700 border border-red-300 rounded-md shadow-sm" role="alert">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 space-y-8">
            <section class="bg-white shadow-lg rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-2">Current Balance</h2>
                <p class="text-4xl font-bold text-green-600 mb-4">
                    Rp <?= number_format($current_balance, 2, ',', '.') ?>
                </p>
                <p class="text-xs text-gray-500">This is your current available balance for ticket purchases.</p>
            </section>

            <section class="bg-white shadow-lg rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Top Up Balance (Simulation)</h2>
                <form method="POST" action="saldo.php">
                    <div class="mb-4">
                        <label for="topup_amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (Rp)</label>
                        <input type="number" id="topup_amount" name="topup_amount" min="10000" step="1000" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2"
                               placeholder="e.g., 50000">
                        <p class="mt-1 text-xs text-gray-500">Minimum top-up Rp 10.000. No payment gateway integrated (simulation only).</p>
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-md shadow-md transition duration-150 ease-in-out flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v2.5a1.5 1.5 0 01-3 0V8a1 1 0 00-1-1H9.5V6.5a1.5 1.5 0 011.5-1.5zm-3.5-2a1.5 1.5 0 00-3 0V4a1 1 0 01-1 1H1a1 1 0 00-1 1v2.5a1.5 1.5 0 003 0V8a1 1 0 011-1h1.5V1.5z" />
                          <path d="M3.5 9.5A1.5 1.5 0 012 11v4.5a1.5 1.5 0 003 0V15a1 1 0 011-1h7a1 1 0 011 1v.5a1.5 1.5 0 003 0V11a1.5 1.5 0 01-1.5-1.5V9.25A.75.75 0 0017 8.5h-2.5a.75.75 0 00-.75.75V9.5zm11.5 0V9.25a.75.75 0 00-.75-.75H13a.75.75 0 00-.75.75V9.5A1.5 1.5 0 0110.5 11v4.5a1.5 1.5 0 003 0V15a1 1 0 011-1h.5a1.5 1.5 0 011.5 1.5v.5a1.5 1.5 0 003 0V11a1.5 1.5 0 01-1.5-1.5z" />
                        </svg>
                        Top Up Now
                    </button>
                </form>
            </section>
        </div>

        <section class="lg:col-span-2 bg-white shadow-lg rounded-lg">
            <header class="bg-gray-50 border-b border-gray-200 px-6 py-4 rounded-t-lg">
                <h2 class="text-xl font-semibold text-gray-700">Transaction History</h2>
            </header>
            <div class="p-6">
                <?php if (empty($balance_history)): ?>
                    <div class="text-center py-10">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No transaction history</h3>
                        <p class="mt-1 text-sm text-gray-500">Your balance transactions will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            <?php foreach ($balance_history as $index => $transaction): ?>
                                <li>
                                    <div class="relative pb-8">
                                        <?php if ($index !== count($balance_history) - 1): // Jangan tampilkan garis untuk item terakhir ?>
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <?php endif; ?>
                                        <div class="relative flex space-x-3 items-start">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-4 ring-white 
                                                    <?php 
                                                        if ($transaction['transaction_type'] === 'topup') echo 'bg-green-500';
                                                        else if ($transaction['transaction_type'] === 'purchase') echo 'bg-red-500';
                                                        else if ($transaction['transaction_type'] === 'refund') echo 'bg-blue-500';
                                                        else echo 'bg-gray-400';
                                                    ?>">
                                                    <?php if ($transaction['transaction_type'] === 'topup'): ?>
                                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" /></svg>
                                                    <?php elseif ($transaction['transaction_type'] === 'purchase'): ?>
                                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" /></svg>
                                                    <?php elseif ($transaction['transaction_type'] === 'refund'): ?>
                                                         <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 10l1.293-1.293z" clip-rule="evenodd" /></svg>
                                                    <?php else: // adjustment ?>
                                                         <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex justify-between items-center">
                                                    <p class="text-sm font-medium text-gray-900">
                                                        <?php
                                                            $transaction_title = ucfirst($transaction['transaction_type']);
                                                            if ($transaction['transaction_type'] === 'topup') {
                                                                $transaction_title = 'Saldo Top Up';
                                                            } elseif ($transaction['transaction_type'] === 'purchase') {
                                                                $transaction_title = 'Pembelian Tiket';
                                                            } elseif ($transaction['transaction_type'] === 'refund') {
                                                                $transaction_title = 'Pengembalian Dana Tiket';
                                                            }
                                                            echo htmlspecialchars($transaction_title);
                                                        ?>
                                                    </p>
                                                    <time datetime="<?= date("Y-m-d\TH:i:s", strtotime($transaction['transaction_date'])) ?>" class="flex-shrink-0 whitespace-nowrap text-xs text-gray-500">
                                                        <?= htmlspecialchars(date("d M Y, H:i", strtotime($transaction['transaction_date']))) ?>
                                                    </time>
                                                </div>
                                                <p class="mt-0.5 text-xs text-gray-500">
                                                    <?php
                                                        if (!empty($transaction['description'])) {
                                                            echo htmlspecialchars($transaction['description']);
                                                        } elseif ($transaction['transaction_type'] === 'purchase' && !empty($transaction['related_order_id'])) {
                                                            echo 'Order ID: #' . htmlspecialchars($transaction['related_order_id']);
                                                        }
                                                    ?>
                                                </p>
                                                <div class="mt-1 text-sm font-semibold
                                                    <?php 
                                                        if ($transaction['transaction_type'] === 'topup' || $transaction['transaction_type'] === 'refund') echo 'text-green-600';
                                                        else if ($transaction['transaction_type'] === 'purchase') echo 'text-red-600';
                                                        else echo 'text-gray-700';
                                                    ?>">
                                                    <?php 
                                                        $prefix = '';
                                                        if ($transaction['transaction_type'] === 'topup' || $transaction['transaction_type'] === 'refund') $prefix = '+ ';
                                                        else if ($transaction['transaction_type'] === 'purchase') $prefix = '- ';
                                                        echo $prefix . 'Rp ' . number_format($transaction['amount'], 2, ',', '.');
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php if(count($balance_history) >= 50): ?>
                        <p class="mt-6 text-xs text-gray-500 text-center">Menampilkan 50 transaksi terakhir.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php include 'footer.php'; ?>