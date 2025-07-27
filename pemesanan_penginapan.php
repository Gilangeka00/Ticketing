<?php
// pemesanan_penginapan.php
include 'config.php';
include 'header.php';  // session_start() ada di sini

// 1) Pastikan user ter‐login dan role 'user'
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$step = 1;
$orderIds = [];
$successMessage = $errorMessage = '';

// 2) Migrasi database: pastikan Anda telah menambahkan kolom ke tabel `orders`:
// ALTER TABLE orders ADD COLUMN check_in datetime NULL, ADD COLUMN check_out datetime NULL;

// 3) Ambil ticket_id dan data penginapan
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$ticket_id = intval($_GET['id']);

$stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ? AND type = "penginapan"');
$stmt->execute([$ticket_id]);
$hotel = $stmt->fetch();
if (!$hotel) {
    echo "<div class='p-4 bg-red-100 text-red-800'>Penginapan tidak ditemukan.</div>";
    include 'footer.php';
    exit;
}

// 4) Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity         = max(1, intval($_POST['quantity']));
    $check_in_date    = $_POST['check_in_date'];
    $check_out_date   = $_POST['check_out_date'];
    $customer_name    = trim($_POST['customer_name']);
    $customer_phone   = trim($_POST['customer_phone']);
    $customer_email   = trim($_POST['customer_email']);
    $customer_address = trim($_POST['customer_address']);

    // Validasi tanggal
    if (!$check_in_date || !$check_out_date || strtotime($check_out_date) <= strtotime($check_in_date)) {
        $errorMessage = "Tanggal Check‑Out harus setelah Check‑In.";
    } else {
        $nights = (new DateTime($check_out_date))->diff(new DateTime($check_in_date))->days;
        $total  = $hotel['price'] * $quantity * $nights;

        try {
            $pdo->beginTransaction();

            // Insert order termasuk check_in dan check_out
            $stmtOrder = $pdo->prepare(
                'INSERT INTO orders
                    (user_id, ticket_id, quantity, seat_number,
                     customer_name, customer_phone, customer_email, customer_address,
                     total_price_at_purchase, check_in, check_out)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmtOrder->execute([
                $_SESSION['user']['id'],
                $ticket_id,
                $quantity,
                'N/A',
                $customer_name,
                $customer_phone,
                $customer_email,
                $customer_address,
                $total,
                $check_in_date,
                $check_out_date
            ]);
            $newId = $pdo->lastInsertId();

            // Catat transaksi saldo
            $desc = "Penginapan \"{$hotel['name']}\" — {$quantity} kamar, {$nights} malam ({$check_in_date}→{$check_out_date})";
            $pdo->prepare(
                'INSERT INTO balance_transactions
                   (user_id, transaction_type, amount, related_order_id, description)
                 VALUES (?, "purchase", ?, ?, ?)'
            )->execute([
                $_SESSION['user']['id'],
                $total,
                $newId,
                $desc
            ]);

            // Update saldo user
            $pdo->prepare('UPDATE users SET balance = balance - ? WHERE id = ?')
                ->execute([$total, $_SESSION['user']['id']]);

            $pdo->commit();

            // Setup success
            $step = 4;
            $orderIds = [$newId];
            $successMessage = "Pemesanan penginapan berhasil! Total Rp " . number_format($total,0,',','.') . ".";
            $_SESSION['user']['balance'] -= $total;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errorMessage = "Gagal melakukan pemesanan: " . $e->getMessage();
        }
    }
}
?>

<div class="max-w-lg mx-auto bg-white p-6 rounded-xl shadow space-y-6">
  <?php if ($step === 1): ?>
    <h2 class="text-2xl font-bold">Pemesanan Penginapan</h2>
    <?php if ($errorMessage): ?>
      <div class="p-3 bg-red-100 text-red-800"><?= $errorMessage ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
      <!-- Nama & harga -->
      <div>
        <label class="block font-medium">Penginapan:</label>
        <div><?= htmlspecialchars($hotel['name']) ?></div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block font-medium">Check‑In:</label>
          <input type="date" name="check_in_date" value="<?= date('Y-m-d') ?>" required class="w-full border rounded p-2"/>
        </div>
        <div>
          <label class="block font-medium">Check‑Out:</label>
          <input type="date" name="check_out_date" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required class="w-full border rounded p-2"/>
        </div>
      </div>
      <div>
        <label class="block font-medium">Harga / Malam:</label>
        <div>Rp <?= number_format($hotel['price'],0,',','.') ?></div>
      </div>
      <div>
        <label class="block font-medium">Jumlah Kamar (1 kamar maksimal 4 orang):</label>
        <input type="number" name="quantity" min="1" value="1" class="w-full border rounded p-2"/>
      </div>
      <hr>
      <!-- Data pemesan -->
      <h3 class="font-semibold">Data Pemesan</h3>
      <input type="text" name="customer_name" placeholder="Nama Lengkap" required class="w-full border rounded p-2"/>
      <input type="text" name="customer_phone" placeholder="No. Telepon" required class="w-full border rounded p-2"/>
      <input type="email" name="customer_email" placeholder="Email" required class="w-full border rounded p-2"/>
      <textarea name="customer_address" rows="3" placeholder="Alamat" required class="w-full border rounded p-2"></textarea>
      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">Bayar & Konfirmasi</button>
    </form>

  <?php elseif ($step === 4): ?>
    <!-- STEP 4: Halaman Sukses -->
    <div class="text-center py-8">
      <svg class="mx-auto h-16 w-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <h3 class="mt-4 text-2xl font-semibold text-green-700">Pemesanan Berhasil!</h3>
      <p class="mt-2 text-gray-600"><?= htmlspecialchars($successMessage) ?></p>
    </div>

    <div class="bg-gray-50 p-6 rounded-lg shadow-inner">
      <h4 class="text-xl font-semibold text-gray-800 mb-4">Detail Pemesanan:</h4>
      <div class="space-y-4">
        <?php
          $ph = implode(',', array_fill(0, count($orderIds), '?'));
          $stmtD = $pdo->prepare(
            "SELECT o.*, t.name AS ticket_name, o.check_in AS check_in_date, o.check_out AS check_out_date
             FROM orders o
             JOIN tickets t ON o.ticket_id = t.id
             WHERE o.id IN ($ph)"
          );
          $stmtD->execute($orderIds);
          foreach ($stmtD->fetchAll(PDO::FETCH_ASSOC) as $o):
        ?>
          <div class="p-4 border rounded-md bg-white">
            <p class="text-sm text-gray-500">Order ID: #<?= htmlspecialchars($o['id']) ?></p>
            <p class="font-semibold text-lg text-gray-800"><?= htmlspecialchars($o['ticket_name']) ?></p>
            <p class="mt-2 text-gray-600">Check‑In: <span class="font-medium"><?= date("d F Y", strtotime($o['check_in_date'])) ?></span></p>
            <p class="text-gray-600">Check‑Out: <span class="font-medium"><?= date("d F Y", strtotime($o['check_out_date'])) ?></span></p>
            <p class="text-gray-600">Pemesan: <span class="font-medium"><?= htmlspecialchars($o['customer_name']) ?></span></p>
            <p class="text-gray-600">Email: <span class="font-medium"><?= htmlspecialchars($o['customer_email']) ?></span></p>
            <p class="text-gray-600">Telepon: <span class="font-medium"><?= htmlspecialchars($o['customer_phone']) ?></span></p>
            <p class="mt-2 text-orange-600 font-bold text-lg">Harga: Rp <?= number_format($o['total_price_at_purchase'],0,',','.') ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="mt-6 flex justify-between">
        <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded">Beranda</a>
        <a href="my_tickets.php" class="bg-green-600 hover:bg-green-700 text-white py-2 px-6 rounded">Tiket Saya</a>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
