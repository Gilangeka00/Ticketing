<?php
// edit_ticket.php
include 'config.php';
include 'header.php';

// Cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}
$id = intval($_GET['id']);

// Ambil detail tiket
$stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
$stmt->execute([$id]);
$ticket = $stmt->fetch();
if (!$ticket) {
    echo '<p class="text-red-500 mt-10 text-center">Tiket tidak ditemukan.</p>';
    include 'footer.php';
    exit;
}

// Pre‐fill data lama
$currentName       = $ticket['name'];
$currentOrigin     = $ticket['origin'];
$currentDest       = $ticket['destination'];
$currentDate       = $ticket['depart_date'];
$currentPrice      = $ticket['price'];
$currentImg        = $ticket['image'];
$currentTotalSeats = $ticket['total_seats'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? '');
    $origin       = trim($_POST['origin'] ?? '');
    $destination  = trim($_POST['destination'] ?? '');
    $depart_date  = $_POST['depart_date'] ?? '';
    $depart_time = $_POST['depart_time'] ?? '';
    $price        = $_POST['price'] ?? '';
    $newTotal     = intval($_POST['total_seats'] ?? $currentTotalSeats);
    if ($newTotal < 1) {
        $newTotal = $currentTotalSeats;
    }

    if (!$origin || !$destination || !$depart_date || !$depart_time) {
    $errors[] = 'Asal, tujuan, tanggal dan jam berangkat wajib diisi.';
} else {
        // Upload gambar baru (opsional)
        $newImgName = $currentImg;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg','image/png','image/jpg'];
                $tmp     = $_FILES['image']['tmp_name'];
                $typeImg = mime_content_type($tmp);
                if (!in_array($typeImg, $allowed)) {
                    $error = 'Format gambar hanya JPG/PNG.';
                } else {
                    $ext     = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $newImg  = uniqid('ticket_') . '.' . $ext;
                    $dest    = __DIR__ . '/uploads/' . $newImg;
                    if (!move_uploaded_file($tmp, $dest)) {
                        $error = 'Gagal mengupload gambar baru.';
                    } else {
                        // Hapus gambar lama jika ada
                        if (!empty($currentImg) && file_exists(__DIR__ . '/uploads/' . $currentImg)) {
                            unlink(__DIR__ . '/uploads/' . $currentImg);
                        }
                        $newImgName = $newImg;
                    }
                }
            } else {
                $error = 'Error saat upload gambar baru.';
            }
        }

        if (!isset($error)) {
            // Update tickets
            $stmt2 = $pdo->prepare('
                UPDATE tickets
                SET name = ?, origin = ?, destination = ?, depart_date = ?, price = ?, image = ?, total_seats = ?
                WHERE id = ?
            ');
            $ok = $stmt2->execute([
                $name,
                $origin,
                $destination,
                $depart_date,
                $price,
                $newImgName,
                $newTotal,
                $id
            ]);
            if ($ok) {
                // Tangani perubahan jumlah kursi (total_seats)
                if ($newTotal != $currentTotalSeats) {
                    // Jika total berubah
                    if ($newTotal > $currentTotalSeats) {
                        // Tambah kursi baru
                        $toAdd = $newTotal - $currentTotalSeats;
                        // Cari nomor kursi tertinggi sekarang
                        $stmtMax = $pdo->prepare('SELECT MAX(CAST(seat_number AS UNSIGNED)) AS mx FROM seats WHERE ticket_id = ?');
                        $stmtMax->execute([$id]);
                        $rowMax = $stmtMax->fetch();
                        $startNo = intval($rowMax['mx']) + 1;
                        $stmtIns = $pdo->prepare('INSERT INTO seats (ticket_id, seat_number, is_booked) VALUES (?, ?, 0)');
                        for ($i = 0; $i < $toAdd; $i++) {
                            $seatNo = (string)($startNo + $i);
                            $stmtIns->execute([$id, $seatNo]);
                        }
                    } else {
                        // Hapus kursi berlebih (is_booked = 0)
                        $toRemove = $currentTotalSeats - $newTotal;
                        $stmtDel = $pdo->prepare('
                            DELETE FROM seats
                            WHERE ticket_id = ? AND is_booked = 0
                            ORDER BY CAST(seat_number AS UNSIGNED) DESC
                            LIMIT ?
                        ');
                        $stmtDel->execute([$id, $toRemove]);
                    }
                }
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Gagal update tiket.';
            }
        }
    }
}
?>

<div class="container mx-auto mt-10">
  <div class="max-w-lg mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl mb-4">Edit <?= ucfirst(htmlspecialchars($ticket['type'])) ?></h2>

    <?php if (!empty($error)): ?>
      <p class="text-red-500 mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
      <!-- Field Name -->
      <div class="mb-4">
        <label class="block text-gray-700">Name (Kode/Nama)</label>
        <input type="text" name="name"
               class="w-full border px-3 py-2 rounded"
               required
               value="<?= htmlspecialchars($name ?? $currentName) ?>">
      </div>
      <!-- Origin -->
      <div class="mb-4">
        <label class="block text-gray-700">Origin</label>
        <input type="text" name="origin"
               class="w-full border px-3 py-2 rounded"
               required
               value="<?= htmlspecialchars($origin ?? $currentOrigin) ?>">
      </div>
      <!-- Destination -->
      <div class="mb-4">
        <label class="block text-gray-700">Destination</label>
        <input type="text" name="destination"
               class="w-full border px-3 py-2 rounded"
               required
               value="<?= htmlspecialchars($destination ?? $currentDest) ?>">
      </div>
      <!-- Depart Date -->
      <div class="mb-4">
        <label class="block text-gray-700">Depart Date</label>
        <input type="date" name="depart_date"
               class="w-full border px-3 py-2 rounded"
               required
               value="<?= htmlspecialchars($depart_date ?? $currentDate) ?>">
      </div>
      <!-- Jam Berangkat -->
<div>
  <label for="depart_time" class="block text-sm font-medium text-gray-700">Jam Berangkat</label>
  <input id="depart_time" name="depart_time" type="time" required
    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
    value="<?= htmlspecialchars($depart_time) ?>">
</div>

      <!-- Price -->
      <div class="mb-4">
        <label class="block text-gray-700">Price (Rp)</label>
        <input type="number" name="price" step="0.01"
               class="w-full border px-3 py-2 rounded"
               required
               value="<?= htmlspecialchars($price ?? $currentPrice) ?>">
      </div>
      <!-- Total Seats -->
      <div class="mb-4">
        <label class="block text-gray-700">Total Seats</label>
        <input type="number" name="total_seats" min="1"
               class="w-full border px-3 py-2 rounded"
               required
               value="<?= htmlspecialchars($newTotal ?? $currentTotalSeats) ?>">
      </div>
      <!-- Gambar Lama -->
      <div class="mb-4">
        <label class="block text-gray-700">Gambar Saat Ini</label>
        <?php if (!empty($currentImg) && file_exists(__DIR__ . '/uploads/' . $currentImg)): ?>
          <img src="uploads/<?= htmlspecialchars($currentImg) ?>"
               alt="Current Image"
               class="w-full h-40 object-cover mb-2">
        <?php else: ?>
          <div class="w-full h-40 bg-gray-200 flex items-center justify-center text-gray-500 mb-2">
            <span>No Image</span>
          </div>
        <?php endif; ?>
        <label class="block text-gray-700">Ganti Gambar (opsional)</label>
        <input type="file" name="image"
               accept=".jpg,.jpeg,.png"
               class="w-full border px-3 py-2 rounded">
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded">Update</button>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>
