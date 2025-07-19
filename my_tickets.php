<?php
// my_tickets.php
include 'config.php';
include 'header.php'; // session sudah dimulai di sini

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = $_SESSION['user']['id'];

// Notifikasi dari proses pembatalan
$success_message = '';
$error_message = '';
if (isset($_GET['cancel_status'])) {
    if ($_GET['cancel_status'] === 'success') {
        $success_message = "Tiket berhasil dibatalkan dan saldo telah dikembalikan.";
    } elseif ($_GET['cancel_status'] === 'error') {
        $error_message = $_SESSION['cancel_error'] ?? "Gagal membatalkan tiket.";
        unset($_SESSION['cancel_error']);
    }
    // Hapus query string untuk mencegah notifikasi muncul lagi saat refresh
    echo '<script>history.replaceState(null, null, window.location.pathname);</script>';
}


// Ambil semua order milik user yang login
$stmt = $pdo->prepare('
    SELECT 
        o.id as order_id, 
        o.seat_number, 
        o.customer_name, 
        o.total_price_at_purchase,
        o.order_date,
        o.order_status, -- Tambahkan order_status
        t.name as ticket_name, 
        t.type as ticket_type,
        t.origin, 
        t.destination, 
        t.depart_date,
        t.image as ticket_image,
        s.id as seat_id -- Ambil seat_id untuk proses pembatalan
    FROM orders o
    JOIN tickets t ON o.ticket_id = t.id
    JOIN seats s ON o.ticket_id = s.ticket_id AND o.seat_number = s.seat_number -- Join ke seats
    WHERE o.user_id = ?
    ORDER BY t.depart_date DESC, o.id DESC
');
$stmt->execute([$user_id]);
$my_orders = $stmt->fetchAll();

// Batas waktu pembatalan (misal, 24 jam sebelum keberangkatan)
define('CANCELLATION_LIMIT_HOURS', 24);

?>

<div class="container mx-auto mt-10 mb-10 px-4">
    <h2 class="text-3xl font-bold text-gray-800 mb-8">Tiket Saya</h2>

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

    <?php if (empty($my_orders)): ?>
        <div class="text-center py-12 bg-white shadow-lg rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Anda belum memiliki tiket.</h3>
            <p class="mt-1 text-sm text-gray-500">Silakan <a href="index.php" class="text-blue-600 hover:underline">cari tiket</a> dan lakukan pemesanan.</p>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($my_orders as $order): ?>
                <?php
                    $can_cancel = false;
                    if ($order['order_status'] === 'booked') {
                        $departure_timestamp = strtotime($order['depart_date']);
                        $current_timestamp = time();
                        $hours_before_departure = ($departure_timestamp - $current_timestamp) / 3600;
                        if ($hours_before_departure >= CANCELLATION_LIMIT_HOURS) {
                            $can_cancel = true;
                        }
                    }
                ?>
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="md:flex">
                        <div class="md:flex-shrink-0">
                            <?php if (!empty($order['ticket_image']) && file_exists(__DIR__ . '/uploads/' . $order['ticket_image'])): ?>
                                <img class="h-full w-full object-cover md:w-48" src="uploads/<?= htmlspecialchars($order['ticket_image']) ?>" alt="Gambar <?= htmlspecialchars($order['ticket_name']) ?>">
                            <?php else: ?>
                                <div class="h-48 w-full md:h-full md:w-48 bg-gray-200 flex items-center justify-center text-gray-500">
                                    <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-6 flex-grow">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="uppercase tracking-wide text-sm 
                                        <?php 
                                            if ($order['order_status'] === 'booked') echo 'text-indigo-600';
                                            elseif ($order['order_status'] === 'cancelled') echo 'text-red-600';
                                            else echo 'text-green-600'; // completed
                                        ?> font-semibold">
                                        <?= htmlspecialchars(ucfirst($order['ticket_type'])) ?> - Order #<?= htmlspecialchars($order['order_id']) ?>
                                        (<?= htmlspecialchars(ucfirst($order['order_status'])) ?>)
                                    </div>
                                    <h3 class="block mt-1 text-xl leading-tight font-bold text-black"><?= htmlspecialchars($order['ticket_name']) ?></h3>
                                </div>
                                <span class="text-xs text-gray-500 mt-1"><?= htmlspecialchars(date("d M Y, H:i", strtotime($order['order_date']))) ?></span>
                            </div>
                            <p class="mt-2 text-gray-600">Rute: <span class="font-medium"><?= htmlspecialchars($order['origin']) ?></span> &rarr; <span class="font-medium"><?= htmlspecialchars($order['destination']) ?></span></p>
                            <p class="mt-1 text-gray-600">Keberangkatan: <span class="font-medium"><?= htmlspecialchars(date("D, d F Y", strtotime($order['depart_date']))) ?></span></p>
                            <p class="mt-1 text-gray-600">Kursi: <span class="font-medium bg-gray-200 px-2 py-0.5 rounded text-sm"><?= htmlspecialchars($order['seat_number']) ?></span></p>
                            <p class="mt-1 text-gray-600">Pemesan: <span class="font-medium"><?= htmlspecialchars($order['customer_name']) ?></span></p>
                            <p class="mt-3 text-lg font-semibold text-orange-600">Harga: Rp <?= number_format($order['total_price_at_purchase'], 0, ',', '.') ?></p>
                            
                            <?php if ($can_cancel): ?>
                            <div class="mt-4">
                                <form method="POST" action="cancel_ticket.php" onsubmit="return confirm('Anda yakin ingin membatalkan tiket ini? Sejumlah dana akan dikembalikan ke saldo Anda.');">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <input type="hidden" name="seat_id" value="<?= $order['seat_id'] ?>"> <input type="hidden" name="refund_amount" value="<?= $order['total_price_at_purchase'] ?>"> <button type="submit" class="text-sm bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-md shadow-sm transition duration-150 ease-in-out">
                                        Batalkan Tiket
                                    </button>
                                </form>
                            </div>
                            <?php elseif($order['order_status'] === 'booked'): ?>
                            <p class="mt-4 text-xs text-yellow-700 italic">Batas waktu pembatalan tiket ini telah lewat (kurang dari <?= CANCELLATION_LIMIT_HOURS ?> jam sebelum keberangkatan).</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>