<?php
// cancel_ticket.php
include 'config.php';
// Mulai session untuk mengakses user_id dan menyimpan pesan error
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Pastikan ini adalah POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my_tickets.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$seat_id = filter_input(INPUT_POST, 'seat_id', FILTER_VALIDATE_INT); // ID dari tabel seats
$refund_amount = filter_input(INPUT_POST, 'refund_amount', FILTER_VALIDATE_FLOAT);

if (!$order_id || !$seat_id || $refund_amount === false || $refund_amount <= 0) {
    $_SESSION['cancel_error'] = "Data pembatalan tidak valid.";
    header('Location: my_tickets.php?cancel_status=error');
    exit;
}

// Ambil detail order untuk verifikasi dan info tiket
$stmtOrder = $pdo->prepare("
    SELECT o.*, t.depart_date, t.name as ticket_name 
    FROM orders o 
    JOIN tickets t ON o.ticket_id = t.id
    WHERE o.id = ? AND o.user_id = ? AND o.order_status = 'booked'
");
$stmtOrder->execute([$order_id, $user_id]);
$order = $stmtOrder->fetch();

if (!$order) {
    $_SESSION['cancel_error'] = "Order tidak ditemukan, sudah dibatalkan, atau bukan milik Anda.";
    header('Location: my_tickets.php?cancel_status=error');
    exit;
}

// Verifikasi batas waktu pembatalan lagi di server-side (lebih aman)
if (!defined('CANCELLATION_LIMIT_HOURS')) {
    define('CANCELLATION_LIMIT_HOURS', 24); // Default jika belum di-define
}
$departure_timestamp = strtotime($order['depart_date']);
$current_timestamp = time();
$hours_before_departure = ($departure_timestamp - $current_timestamp) / 3600;

if ($hours_before_departure < CANCELLATION_LIMIT_HOURS) {
    $_SESSION['cancel_error'] = "Batas waktu pembatalan untuk tiket ini telah lewat.";
    header('Location: my_tickets.php?cancel_status=error');
    exit;
}

// Asumsi refund amount adalah harga tiket (bisa ada logika potongan biaya admin di sini)
// Untuk contoh ini, kita refund 100% dari total_price_at_purchase
$actual_refund_amount = (float)$order['total_price_at_purchase']; 
// Jika Anda mengirim refund_amount dari form, pastikan itu sesuai dengan harga asli
// $actual_refund_amount = $refund_amount;


try {
    $pdo->beginTransaction();

    // 1. Update status order menjadi 'cancelled'
    $stmtUpdateOrder = $pdo->prepare("UPDATE orders SET order_status = 'cancelled', cancelled_at = NOW() WHERE id = ? AND user_id = ?");
    $stmtUpdateOrder->execute([$order_id, $user_id]);

    // 2. Update status kursi menjadi tidak terpesan (is_booked = 0) dan hapus order_id dari seat
    $stmtUpdateSeat = $pdo->prepare("UPDATE seats SET is_booked = 0, order_id = NULL WHERE id = ?"); 
    // Kita menggunakan seat_id yang didapat dari join di my_tickets.php
    // Atau bisa juga: WHERE ticket_id = ? AND seat_number = ?
    $stmtUpdateSeat->execute([$seat_id]);


    // 3. Tambahkan saldo ke pengguna
    $stmtUpdateUserBalance = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmtUpdateUserBalance->execute([$actual_refund_amount, $user_id]);

    // 4. Catat transaksi refund di balance_transactions
    $description = "Refund untuk pembatalan tiket: " . htmlspecialchars($order['ticket_name']) . " (Order ID: #{$order_id})";
    $stmtLogRefund = $pdo->prepare("
        INSERT INTO balance_transactions 
          (user_id, transaction_type, amount, related_order_id, description) 
        VALUES (?, 'refund', ?, ?, ?)
    ");
    $stmtLogRefund->execute([$user_id, $actual_refund_amount, $order_id, $description]);

    $pdo->commit();

    // Update saldo di session
    $_SESSION['user']['balance'] += $actual_refund_amount;

    header('Location: my_tickets.php?cancel_status=success');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    // error_log("Cancellation Error: " . $e->getMessage()); // Catat error di server log
    $_SESSION['cancel_error'] = "Terjadi kesalahan saat proses pembatalan: " . $e->getMessage();
    header('Location: my_tickets.php?cancel_status=error');
    exit;
}

?>