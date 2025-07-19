<?php
include 'config.php';

// Ambil semua tiket
$stmt = $pdo->query('SELECT id, total_seats FROM tickets');
$tickets = $stmt->fetchAll();

foreach ($tickets as $ticket) {
    $ticket_id    = $ticket['id'];
    $total_seats  = intval($ticket['total_seats']);

    // Hitung sudah ada berapa baris seats
    $stmtCount = $pdo->prepare('SELECT COUNT(*) AS cnt FROM seats WHERE ticket_id = ?');
    $stmtCount->execute([$ticket_id]);
    $rowCount = $stmtCount->fetch();
    $existing = intval($rowCount['cnt']);

    // Jika belum lengkap, tambahkan sisanya
    if ($existing < $total_seats) {
        $insert = $pdo->prepare('INSERT INTO seats (ticket_id, seat_number, is_booked) VALUES (?, ?, 0)');
        for ($i = $existing + 1; $i <= $total_seats; $i++) {
            $insert->execute([$ticket_id, (string)$i]);
        }
    }
}

echo "Populate seats selesai.\n";
