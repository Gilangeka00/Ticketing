<?php
// my_tickets.php
include 'config.php';
include 'header.php';

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = $_SESSION['user']['id'];

// Notifikasi pembatalan
$success_message = '';
$error_message = '';
if (isset($_GET['cancel_status'])) {
    if ($_GET['cancel_status'] === 'success') {
        $success_message = "Tiket berhasil dibatalkan dan saldo telah dikembalikan.";
    } elseif ($_GET['cancel_status'] === 'error') {
        $error_message = $_SESSION['cancel_error'] ?? "Gagal membatalkan tiket.";
        unset($_SESSION['cancel_error']);
    }
    echo '<script>history.replaceState(null, null, window.location.pathname);</script>';
}

// Ambil semua pesanan milik user, gunakan kolom orders.check_in, orders.check_out
$stmt = $pdo->prepare(
    'SELECT
        o.id AS order_id,
        o.seat_number,
        o.customer_name,
        o.customer_email,
        o.customer_phone,
        o.total_price_at_purchase,
        o.order_date,
        o.order_status,
        t.name AS ticket_name,
        t.type AS ticket_type,
        t.origin,
        t.destination,
        t.depart_date,
        o.check_in AS check_in_date,
        o.check_out AS check_out_date,
        o.rental_start AS rental_start_date,
        o.rental_end   AS rental_end_date,
        t.image AS ticket_image,
        s.id AS seat_id
    FROM orders o
    LEFT JOIN seats s ON s.order_id = o.id
    JOIN tickets t ON o.ticket_id = t.id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC'
);
$stmt->execute([$user_id]);
$my_orders = $stmt->fetchAll();

define('CANCELLATION_LIMIT_HOURS', 24);
?>

<div class="container mx-auto mt-10 mb-10 px-4">
    <h2 class="text-3xl font-bold text-gray-800 mb-8">Tiket Saya</h2>

    <?php if ($success_message): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-700 border border-green-300 rounded-md" role="alert">
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="mb-6 p-4 bg-red-100 text-red-700 border border-red-300 rounded-md" role="alert">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($my_orders)): ?>
        <div class="text-center py-12 bg-white shadow-lg rounded-lg">
            <h3 class="mt-2 text-sm font-medium text-gray-900">Anda belum memiliki tiket.</h3>
            <p class="mt-1 text-sm text-gray-500">Silakan <a href="index.php" class="text-blue-600 hover:underline">cari tiket</a> dan lakukan pemesanan.</p>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($my_orders as $order): ?>
                <?php
                    // Tentukan waktu mulai untuk batas pembatalan
                    $start_date = null;
                    switch ($order['ticket_type']) {
                        case 'kereta':
                        case 'pesawat':
                        case 'kapal':
                            $start_date = $order['depart_date'];
                            break;
                        case 'penginapan':
                            $start_date = $order['check_in_date'];
                            break;
                        case 'kendaraan':
                            $start_date = $order['rental_start_date'];
                            break;
                    }
                    $can_cancel = false;
                    if ($order['order_status'] === 'booked' && $start_date) {
                        $hours_before = (strtotime($start_date) - time()) / 3600;
                        if ($hours_before >= CANCELLATION_LIMIT_HOURS) {
                            $can_cancel = true;
                        }
                    }
                ?>
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="md:flex">
                        <div class="md:flex-shrink-0">
                            <?php if (!empty($order['ticket_image']) && file_exists(__DIR__ . '/uploads/' . $order['ticket_image'])): ?>
                                <img class="h-full w-full object-cover md:w-48" src="uploads/<?= htmlspecialchars($order['ticket_image']) ?>" alt="<?= htmlspecialchars($order['ticket_name']) ?>">
                            <?php else: ?>
                                <div class="h-48 w-full md:h-full md:w-48 bg-gray-200 flex items-center justify-center text-gray-500">
                                    <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-6 flex-grow">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="uppercase tracking-wide text-sm <?php 
                                            if ($order['order_status'] === 'booked') echo 'text-indigo-600';
                                            elseif ($order['order_status'] === 'cancelled') echo 'text-red-600';
                                            else echo 'text-green-600';
                                        ?> font-semibold">
                                        <?= htmlspecialchars(ucfirst($order['ticket_type'])) ?> - Order #<?= htmlspecialchars($order['order_id']) ?>
                                        (<?= htmlspecialchars(ucfirst($order['order_status'])) ?>)
                                    </div>
                                    <h3 class="mt-1 text-xl font-bold text-black"><?= htmlspecialchars($order['ticket_name']) ?></h3>
                                </div>
                                <span class="text-xs text-gray-500 mt-1"><?= date("d M Y, H:i", strtotime($order['order_date'])) ?></span>
                            </div>

                            <?php if (in_array($order['ticket_type'], ['kereta','pesawat','kapal'])): ?>
                                <p class="mt-2 text-gray-600">Rute: <span class="font-medium"><?= htmlspecialchars($order['origin']) ?></span> &rarr; <span class="font-medium"><?= htmlspecialchars($order['destination']) ?></span></p>
                                <p class="mt-1 text-gray-600">Keberangkatan: <span class="font-medium"><?= date("D, d F Y", strtotime($order['depart_date'])) ?></span></p>
                                <p class="mt-1 text-gray-600">Kursi: <span class="bg-gray-200 px-2 py-0.5 rounded text-sm"><?= htmlspecialchars($order['seat_number']) ?></span></p>
                            <?php elseif ($order['ticket_type'] === 'penginapan'): ?>
                                <p class="mt-2 text-gray-600">Check-In: <span class="font-medium"><?= date("D, d F Y", strtotime($order['check_in_date'])) ?></span></p>
                                <p class="mt-1 text-gray-600">Check-Out: <span class="font-medium"><?= date("D, d F Y", strtotime($order['check_out_date'])) ?></span></p>
                            <?php elseif ($order['ticket_type'] === 'kendaraan'): ?>
                                <p class="mt-2 text-gray-600">Sewa Mulai: <span class="font-medium"><?= date("D, d F Y, H:i", strtotime($order['rental_start_date'])) ?></span></p>
                                <p class="mt-1 text-gray-600">Sewa Sampai: <span class="font-medium"><?= date("D, d F Y, H:i", strtotime($order['rental_end_date'])) ?></span></p>
                            <?php endif; ?>

                            <p class="mt-3 text-lg font-semibold text-orange-600">Harga: Rp <?= number_format($order['total_price_at_purchase'], 0, ',', '.') ?></p>
                            <?php if ($can_cancel): ?>
                                <div class="mt-4">
                                    <form method="POST" action="cancel_ticket.php" onsubmit="return confirm('Anda yakin ingin membatalkan tiket ini?');">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <input type="hidden" name="seat_id" value="<?= $order['seat_id'] ?>">
                                        <input type="hidden" name="refund_amount" value="<?= $order['total_price_at_purchase'] ?>">
                                        <button type="submit" class="text-sm bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-md">Batalkan Tiket</button>
                                    </form>
                                </div>
                            <?php elseif ($order['order_status'] === 'booked'): ?>
                                <p class="mt-4 text-xs text-yellow-700 italic">Batas waktu pembatalan tersisa kurang dari <?= CANCELLATION_LIMIT_HOURS ?> jam.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
