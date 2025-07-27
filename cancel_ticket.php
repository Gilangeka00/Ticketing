<?php
// cancel_ticket.php
include 'config.php';
// Mulai session untuk akses user & message
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1) Autentikasi
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user']['id'];

// 2) Hanya POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my_tickets.php');
    exit;
}

// 3) Ambil input
$order_id      = filter_input(INPUT_POST,   'order_id',    FILTER_VALIDATE_INT);
$seat_id_raw   = filter_input(INPUT_POST,   'seat_id',     FILTER_VALIDATE_INT);
$refund_amount = filter_input(INPUT_POST,   'refund_amount', FILTER_VALIDATE_FLOAT);

if (!$order_id || $refund_amount === false || $refund_amount <= 0) {
    $_SESSION['cancel_error'] = "Data pembatalan tidak valid.";
    header('Location: my_tickets.php?cancel_status=error');
    exit;
}
// Biarkan $seat_id null jika bukan transport
$seat_id = $seat_id_raw ?: null;

// 4) Ambil detail order + tiket (termasuk jenis & tanggal terkait)
$stmtOrder = $pdo->prepare("
    SELECT 
      o.*, 
      t.type,
      t.depart_date, 
      t.check_in_date,
      t.rental_start_date,
      t.name AS ticket_name 
    FROM orders o
    JOIN tickets t ON o.ticket_id = t.id
    WHERE o.id = ? 
      AND o.user_id = ? 
      AND o.order_status = 'booked'
");
$stmtOrder->execute([$order_id, $user_id]);
$order = $stmtOrder->fetch();

if (!$order) {
    $_SESSION['cancel_error'] = "Order tidak ditemukan atau tidak dapat dibatalkan.";
    header('Location: my_tickets.php?cancel_status=error');
    exit;
}

// 5) Verifikasi batas waktu pembatalan
if (!defined('CANCELLATION_LIMIT_HOURS')) {
    define('CANCELLATION_LIMIT_HOURS', 24);
}

// Tentukan tanggal mulai sesuai tipe tiket
switch ($order['type']) {
    case 'kereta':
    case 'pesawat':
    case 'kapal':
        $start_datetime = $order['depart_date'];
        break;
    case 'penginapan':
        $start_datetime = $order['check_in_date'];
        break;
    case 'kendaraan':
        $start_datetime = $order['rental_start_date'];
        break;
    default:
        $start_datetime = $order['depart_date'];
}

$hours_before = (strtotime($start_datetime) - time()) / 3600;
if ($hours_before < CANCELLATION_LIMIT_HOURS) {
    $_SESSION['cancel_error'] = "Batas waktu pembatalan untuk tiket ini telah lewat.";
    header('Location: my_tickets.php?cancel_status=error');
    exit;
}

// 6) Mulai transaksi pembatalan
try {
    $pdo->beginTransaction();

    // a) Update orders
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET order_status = 'cancelled', cancelled_at = NOW() 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);

    // b) Jika ada seat_id (transport), reset seat
    if ($seat_id) {
        $stmt = $pdo->prepare("
            UPDATE seats 
            SET is_booked = 0, order_id = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$seat_id]);
    }

    // c) Refund saldo user
    $stmt = $pdo->prepare("
        UPDATE users 
        SET balance = balance + ? 
        WHERE id = ?
    ");
    $stmt->execute([$refund_amount, $user_id]);

    // d) Catat transaksi refund
    $description = "Refund pembatalan: {$order['ticket_name']} (Order #{$order_id})";
    $stmt = $pdo->prepare("
        INSERT INTO balance_transactions
          (user_id, transaction_type, amount, related_order_id, description)
        VALUES
          (?, 'refund', ?, ?, ?)
    ");
    $stmt->execute([$user_id, $refund_amount, $order_id, $description]);

    $pdo->commit();

    // Update session balance
    $_SESSION['user']['balance'] += $refund_amount;

    header('Location: my_tickets.php?cancel_status=success');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['cancel_error'] = "Terjadi kesalahan: " . $e->getMessage();
    header('Location: my_tickets.php?cancel_status=error');
    exit;
}
