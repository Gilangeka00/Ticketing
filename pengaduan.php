<?php
// pengaduan.php
require 'config.php';  // sudah berisi $pdo

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ambil & sanitasi
    $nama_lengkap       = $_POST['nama_lengkap'];
    $kontak             = $_POST['kontak'];
    $tanggal_pemesanan  = $_POST['tanggal_pemesanan'];
    $jenis_tiket        = $_POST['jenis_tiket'];
    $nama_penumpang     = $_POST['nama_penumpang'];
    $jenis_pengaduan    = $_POST['jenis_pengaduan'];
    $deskripsi          = $_POST['deskripsi'];
    $harapan            = $_POST['harapan'];

    // insert via PDO
    $sql = "INSERT INTO pengaduan
        (nama_lengkap, kontak, tanggal_pemesanan, jenis_tiket, nama_penumpang, jenis_pengaduan, deskripsi, harapan)
        VALUES
        (:nama, :kontak, :tgl, :jenis_tiket, :penumpang, :jenis_aduan, :desk, :harapan)";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([
        ':nama'          => $nama_lengkap,
        ':kontak'        => $kontak,
        ':tgl'           => $tanggal_pemesanan,
        ':jenis_tiket'   => $jenis_tiket,
        ':penumpang'     => $nama_penumpang,
        ':jenis_aduan'   => $jenis_pengaduan,
        ':desk'          => $deskripsi,
        ':harapan'       => $harapan,
    ]);

    $message = $ok
        ? 'Pengaduan berhasil dikirim. Terima kasih.'
        : 'Terjadi kesalahan saat menyimpan data.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Form Pengaduan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
  <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-6xl">
    <h1 class="text-3xl font-bold mb-6 text-gray-900 text-center">Form Pengaduan</h1>
    <?php if ($message): ?>
      <div class="mb-6 p-4 bg-gray-100 text-gray-900 rounded">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>
    <form method="post" action="" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Kolom Kiri -->
      <div class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-900 mb-1">Nama Lengkap</label>
          <input type="text" name="nama_lengkap" required class="w-full border-2 border-gray-400 bg-white rounded-md p-2 focus:border-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-200">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-900 mb-1">Email / No. HP</label>
          <input type="text" name="kontak" required class="w-full border-2 border-gray-400 bg-white rounded-md p-2 focus:border-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-200">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-900 mb-1">Tanggal Pemesanan</label>
          <input type="date" name="tanggal_pemesanan" required class="w-full border-2 border-gray-400 bg-white rounded-md p-2 focus:border-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-200">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-900 mb-1">Jenis Tiket</label>
          <select name="jenis_tiket" required class="w-full border-2 border-gray-400 bg-white rounded-md p-2 focus:border-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-200">
            <option value="pesawat">Pesawat</option>
            <option value="kapal">Kapal</option>
            <option value="kereta">Kereta</option>
            <option value="penginapan">Penginapan</option>
            <option value="kendaraan">Kendaraan</option>
          </select>
        </div>
      </div>

      <!-- Kolom Kanan -->
      <div class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-900 mb-1">Nama Penumpang / Peserta</label>
          <input type="text" name="nama_penumpang" required class="w-full border-2 border-gray-400 bg-white rounded-md p-2 focus:border-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-200">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-900 mb-1">Jenis Pengaduan</label>
          <select name="jenis_pengaduan" required class="w-full border-2 border-gray-400 bg-white rounded-md p-2 focus:border-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-200">
            <option value="Tiket tidak diterima">Tiket tidak diterima</option>
            <option value="Pembayaran gagal tapi saldo terpotong">Pembayaran gagal tapi saldo terpotong</option>
            <option value="Kesalahan data tiket">Kesalahan data tiket</option>
            <option value="Perubahan jadwal sepihak">Perubahan jadwal sepihak</option>
            <option value="Pembatalan sepihak">Pembatalan sepihak</option>
            <option value="Tiket tidak dapat digunakan saat hari H">Tiket tidak dapat digunakan saat hari H</option>
            <option value="Layanan CS tidak responsif">Layanan CS tidak responsif</option>
            <option value="Lainnya (Kenyamanan dan Keamanan)">Lainnya (Kenyamanan dan Keamanan)</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-900 mb-1">Deskripsi Pengaduan</label>
          <textarea name="deskripsi" rows="4" required class="w-full border-2 border-gray-400 bg-white rounded-md p-2 focus:border-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-200"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-900 mb-1">Harapan / Permintaan Penyelesaian</label>
          <input type="text" name="harapan" required class="w-full border-2 border-gray-400 bg-white rounded-md p-2 focus:border-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-200">
        </div>
      </div>

      <!-- Tombol Kirim Menjulang di Bawah -->
      <div class="lg:col-span-2 text-center mt-4">
        <button type="submit" class="inline-flex items-center px-8 py-3 bg-black text-white font-medium rounded-md shadow hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-600">
          Kirim Pengaduan
        </button>
      </div>
    </form>
  </div>
</body>
</html>