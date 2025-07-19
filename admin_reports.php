<?php
// admin_reports.php
include 'config.php';
include 'header.php';

// Pastikan admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// 1. Ringkasan Umum
// Jumlah total pengguna
$stmtTotalUsers = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmtTotalUsers->fetchColumn();

// Jumlah total item terjual (berdasarkan jumlah baris di orders)
// Hanya yang tidak dibatalkan
$stmtTotalOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'booked' OR order_status = 'completed'"); 
$totalItemsSold = $stmtTotalOrders->fetchColumn();

// Total pendapatan dari penjualan
// Pastikan kolom total_price_at_purchase ada dan terisi dengan benar di tabel orders
$stmtTotalRevenue = $pdo->query("SELECT SUM(total_price_at_purchase) FROM orders WHERE order_status = 'booked' OR order_status = 'completed'");
$totalRevenue = (float)$stmtTotalRevenue->fetchColumn(); // (float) akan mengubah NULL menjadi 0.0

// Total saldo yang di-top-up
// Pastikan ada transaksi dengan type 'topup' dan kolom amount terisi
$stmtTotalTopUp = $pdo->query("SELECT SUM(amount) FROM balance_transactions WHERE transaction_type = 'topup'");
$totalTopUp = (float)$stmtTotalTopUp->fetchColumn(); // (float) akan mengubah NULL menjadi 0.0


// 2. Daftar Penjualan Terbaru (misal, 10 terakhir)
$stmtLatestSales = $pdo->prepare("
    SELECT o.id as order_id, o.order_date, o.total_price_at_purchase, 
           u.username as user_username, 
           t.name as ticket_name, t.type as ticket_type
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN tickets t ON o.ticket_id = t.id
    WHERE o.order_status = 'booked' OR o.order_status = 'completed'
    ORDER BY o.order_date DESC
    LIMIT 10
");
$stmtLatestSales->execute();
$latestSales = $stmtLatestSales->fetchAll(PDO::FETCH_ASSOC);

// 3. Daftar Top-Up Terbaru (misal, 10 terakhir)
$stmtLatestTopUps = $pdo->prepare("
    SELECT bt.transaction_date, bt.amount, bt.description,
           u.username as user_username
    FROM balance_transactions bt
    JOIN users u ON bt.user_id = u.id
    WHERE bt.transaction_type = 'topup'
    ORDER BY bt.transaction_date DESC
    LIMIT 10
");
$stmtLatestTopUps->execute();
$latestTopUps = $stmtLatestTopUps->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mx-auto mt-8 px-4 sm:px-6 lg:px-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Laporan Admin</h1>
        <p class="text-sm text-gray-500">Ringkasan aktivitas dan data penting platform.</p>
    </header>

    <section class="mb-10">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Ringkasan Umum</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white shadow-lg rounded-lg p-6 flex items-center space-x-4">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.084-1.268-.25-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.084-1.268.25-1.857m0 0a5.002 5.002 0 019.5 0M12 10a5 5 0 110-10 5 5 0 010 10z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 truncate">Total Pengguna</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900"><?= number_format($totalUsers) ?></p>
                </div>
            </div>
            <div class="bg-white shadow-lg rounded-lg p-6 flex items-center space-x-4">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                     <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 truncate">Item Terjual</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900"><?= number_format($totalItemsSold) ?></p>
                </div>
            </div>
            <div class="bg-white shadow-lg rounded-lg p-6 flex items-center space-x-4">
                 <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 truncate">Total Pendapatan</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">Rp <?= number_format($totalRevenue, 2, ',', '.') ?></p>
                </div>
            </div>
             <div class="bg-white shadow-lg rounded-lg p-6 flex items-center space-x-4">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 truncate">Total Top-Up Saldo</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">Rp <?= number_format($totalTopUp, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <section class="bg-white shadow-lg rounded-lg">
            <header class="bg-gray-50 border-b border-gray-200 px-6 py-4 rounded-t-lg">
                <h2 class="text-xl font-semibold text-gray-700">Penjualan Tiket/Layanan Terbaru</h2>
            </header>
            <div class="p-6">
                <?php if (empty($latestSales)): ?>
                    <p class="text-gray-500 text-center py-4">Belum ada penjualan.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Order</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Harga (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($latestSales as $sale): ?>
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars(date("d M Y", strtotime($sale['order_date']))) ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-medium"><?= htmlspecialchars($sale['user_username']) ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                            <?= htmlspecialchars($sale['ticket_name']) ?>
                                            <span class="text-xs text-gray-400">(<?= htmlspecialchars(ucfirst($sale['ticket_type'])) ?>)</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-right font-semibold"><?= number_format($sale['total_price_at_purchase'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="bg-white shadow-lg rounded-lg">
            <header class="bg-gray-50 border-b border-gray-200 px-6 py-4 rounded-t-lg">
                <h2 class="text-xl font-semibold text-gray-700">Top-Up Saldo Terbaru</h2>
            </header>
            <div class="p-6">
                <?php if (empty($latestTopUps)): ?>
                    <p class="text-gray-500 text-center py-4">Belum ada top-up saldo.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Top-Up</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($latestTopUps as $topup): ?>
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars(date("d M Y, H:i", strtotime($topup['transaction_date']))) ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-medium"><?= htmlspecialchars($topup['user_username']) ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-green-600 text-right font-semibold"><?= number_format($topup['amount'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php include 'footer.php'; ?>