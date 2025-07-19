<?php
// tampilkan_pengaduan.php
require 'config.php'; // sudah berisi $pdo
include 'header.php';
// ambil semua pengaduan
$stmt = $pdo->query("SELECT * FROM pengaduan ORDER BY submitted_at DESC");
$aduans = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Pengaduan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <header class="bg-white shadow">
    <div class="container mx-auto px-6 py-4">
      <h1 class="text-3xl font-bold text-gray-800">Daftar Pengaduan</h1>
    </div>
  </header>

  <main class="container mx-auto px-6 py-8">
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <span class="text-lg font-medium text-gray-700">Total: <?= count($aduans) ?> Pengaduan</span>
        <button onclick="location.reload()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-500 focus:outline-none">
          Refresh
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Kontak</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Tgl. Pemesanan</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Jenis Tiket</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Jenis Pengaduan</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Deskripsi</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Harapan</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Waktu</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (count($aduans)): ?>
              <?php foreach ($aduans as $row): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= $row['id'] ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($row['kontak']) ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= $row['tanggal_pemesanan'] ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 capitalize"><?= $row['jenis_tiket'] ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 capitalize"><?= $row['jenis_pengaduan'] ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 max-w-xs truncate" title="<?= htmlspecialchars($row['deskripsi']) ?>"><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($row['harapan']) ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?= $row['submitted_at'] ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td class="px-4 py-3 text-center text-sm text-gray-500" colspan="9">Belum ada pengaduan.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>
