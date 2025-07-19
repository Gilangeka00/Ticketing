<?php
// kereta.php
include 'config.php';
session_start();

// Ambil parameter pencarian (jika ada), kalau tidak pakai wildcard '%'
$origin      = isset($_GET['origin']) && $_GET['origin'] !== '' ? '%' . $_GET['origin'] . '%' : '%';
$destination = isset($_GET['destination']) && $_GET['destination'] !== '' ? '%' . $_GET['destination'] . '%' : '%';
$depart_date = isset($_GET['depart_date']) && $_GET['depart_date'] !== '' ? $_GET['depart_date'] : null;

// Membangun query
$sql    = 'SELECT * FROM tickets WHERE type = "kereta" AND origin LIKE ? AND destination LIKE ?';
$params = [$origin, $destination];
if ($depart_date) {
    $sql    .= ' AND depart_date = ?';
    $params[] = $depart_date;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$flights = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>

<div class="container mx-auto mt-10">
  <h2 class="text-2xl font-bold mb-4">Hasil Pencarian kereta</h2>

  <?php if (empty($flights)): ?>
    <p class="text-gray-600">Tidak ada hasil ditemukan.</p>
  <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php foreach ($flights as $flight): ?>
        <div class="bg-white rounded shadow p-4">
          <h3 class="text-xl font-semibold"><?= htmlspecialchars($flight['name']) ?></h3>
          <p class="mt-2"><?= htmlspecialchars($flight['origin']) ?> ↔ <?= htmlspecialchars($flight['destination']) ?></p>
          <p class="mt-1">Tanggal: <?= htmlspecialchars($flight['depart_date']) ?></p>
          <p class="mt-1 text-orange-500 font-bold">
            Rp <?= number_format($flight['price'], 0, ',', '.') ?>
          </p>
          <a href="pemesanan_kereta.php?id=<?= $flight['id'] ?>" 
             class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded">
            Pesan Sekarang
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
