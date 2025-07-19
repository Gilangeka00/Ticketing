<?php
// dashboard.php
include 'config.php';
include 'header.php'; // Diasumsikan session_start() ada di dalam header.php

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Direktori tempat menyimpan file gambar
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Jika folder uploads belum ada, coba buat
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

/**
 * ============================================================
 * BAGIAN 1: PROSES PENAMBAHAN (ADD) TIKET
 * ============================================================
 * Kita buat 1 form dinamis berdasarkan ?type=..., 
 * misalnya ?type=pesawat, ?type=kapal, ?type=kereta, ?type=penginapan, ?type=kendaraan.
 * Jika form disubmit, kita proses $_POST + $_FILES.
 */
$tipe_baru = $_GET['type'] ?? null;
$allowed_types = ['pesawat', 'kapal', 'kereta', 'penginapan', 'kendaraan'];
$add_error = '';
if ($tipe_baru && in_array($tipe_baru, $allowed_types) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dasar dari form (silakan tambahkan field sesuai kebutuhan tipe)
    // Semua tipe minimal memiliki: name, price, total_seats. Khusus tipe transportasi: origin, destination, depart_date.
    // Contoh minimal (Anda bisa menyesuaikan input di form di bawah):
    $name      = trim($_POST['name'] ?? '');
    $price     = floatval($_POST['price'] ?? 0);
    $total_seats = intval($_POST['total_seats'] ?? 0);

    // Field khusus tipe:
    $origin       = trim($_POST['origin'] ?? '');
    $destination  = trim($_POST['destination'] ?? '');
    $depart_date  = trim($_POST['depart_date'] ?? '');  
    $depart_time  = trim($_POST['depart_time'] ?? '');     // untuk pesawat/kapal/kereta
    $check_in_date  = trim($_POST['check_in_date'] ?? '');   // untuk penginapan
    $rental_start_date = trim($_POST['rental_start_date'] ?? ''); // untuk kendaraan

    // Validasi sederhana
    if ($name === '' || $price <= 0 || $total_seats <= 0) {
        $add_error = 'Pastikan nama, harga, dan jumlah total (total_seats) terisi dengan benar.';
    }

    // PROSES UPLOAD GAMBAR
    $image_filename = ''; // akan disimpan ke DB
    if (empty($add_error) && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Cek error upload
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $add_error = 'Error saat meng‐upload gambar. Silakan coba lagi.';
        } else {
            $tmp_name = $_FILES['image']['tmp_name'];
            $orig_name = basename($_FILES['image']['name']);
            // Pastikan ekstensi yang diizinkan
            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($ext, $allowed_ext)) {
                $add_error = 'Hanya file gambar (jpg, jpeg, png, gif) yang diizinkan.';
            } else {
                // Buat nama file unik: misal ticket_<timestamp>_<random>.<ext>
                $new_filename = 'ticket_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
                $dest_path = UPLOAD_DIR . $new_filename;
                if (!move_uploaded_file($tmp_name, $dest_path)) {
                    $add_error = 'Gagal memindahkan file gambar ke folder uploads/.';
                } else {
                    $image_filename = $new_filename;
                }
            }
        }
    }

    // Jika tidak ada error, simpan ke database
    if (empty($add_error)) {
        // Siapkan query INSERT disesuaikan dengan kolom yang ada
        // – Kolom umum: type, name, origin, destination, price, total_seats, image
        // – Tambahan kolom spesifik tipe: depart_date (untuk transportasi), check_in_date (penginapan), rental_start_date (kendaraan)
        $fields   = ['type', 'name', 'price', 'total_seats', 'image'];
        $placeholders = ['?', '?', '?', '?', '?'];
        $values   = [$tipe_baru, $name, $price, $total_seats, $image_filename];

        if (in_array($tipe_baru, ['pesawat', 'kapal', 'kereta'])) {
            $fields[]       = 'origin';
    $fields[]       = 'destination';
    $fields[]       = 'depart_date';
    $fields[]       = 'depart_time';      // <<< tambahan
    $placeholders[] = '?';
    $placeholders[] = '?';
    $placeholders[] = '?';
    $placeholders[] = '?';                // <<< tambahan
    $values[]       = $origin;
    $values[]       = $destination;
    $values[]       = $depart_date ?: null;
    $values[]       = $depart_time ?: null; // <<< tambahan
        } elseif ($tipe_baru === 'penginapan') {
            $fields[] = 'destination';       // sebagai lokasi penginapan
            $fields[] = 'address';
            $fields[] = 'check_in_date';
            $placeholders[] = '?';
            $placeholders[] = '?';
            $placeholders[] = '?';
            $values[] = $destination;        // gunakan kolom destination untuk menyimpan lokasi
            $values[] = trim($_POST['address'] ?? '');
            $values[] = $check_in_date ?: null;
        } elseif ($tipe_baru === 'kendaraan') {
            $fields[] = 'origin';            // sebagai lokasi pickup
            $fields[] = 'vehicle_brand';
            $fields[] = 'vehicle_model';
            $fields[] = 'vehicle_year';
            $fields[] = 'rental_start_date';
            $placeholders = array_merge($placeholders, array_fill(0, 5, '?'));
            $values[] = $origin;             // lokasi pickup
            $values[] = trim($_POST['vehicle_brand'] ?? '');
            $values[] = trim($_POST['vehicle_model'] ?? '');
            $values[] = intval($_POST['vehicle_year'] ?? 0);
            $values[] = $rental_start_date ?: null;
        }

        // Bangun query INSERT
        $sql = 'INSERT INTO tickets (' . implode(',', $fields) . ') VALUES (' . implode(',', $placeholders) . ')';
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($values)) {

            // Dapatkan ID tiket yang baru saja dimasukkan
$ticket_id   = $pdo->lastInsertId();
$total_seats = intval($_POST['total_seats']); // atau variabel yang sudah ada

// Persiapkan statement sekali
$insertSeatStmt = $pdo->prepare(
    "INSERT INTO seats (ticket_id, seat_number, is_booked) VALUES (?, ?, 0)"
);

// Loop untuk membuat setiap kursi
for ($i = 1; $i <= $total_seats; $i++) {
    $insertSeatStmt->execute([$ticket_id, (string)$i]);
}


            // Setelah berhasil insert, redirect ke dashboard tanpa parameter type agar form hilang
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'Item baru berhasil ditambahkan.'
            ];
            header('Location: dashboard.php');
            exit;
        } else {
            $add_error = 'Gagal menyimpan data ke database.';
            // Jika insert gagal, hapus file gambar agar tidak menumpuk
            if ($image_filename && file_exists(UPLOAD_DIR . $image_filename)) {
                unlink(UPLOAD_DIR . $image_filename);
            }
        }
    }
}

/**
 * ============================================================
 * BAGIAN 2: PROSES HAPUS (DELETE) ITEM
 * ============================================================
 */
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    // Cari nama file gambar lama dulu
    $stmtOld = $pdo->prepare('SELECT image FROM tickets WHERE id = ?');
    $stmtOld->execute([$del_id]);
    $rowOld = $stmtOld->fetch(PDO::FETCH_ASSOC);
    if ($rowOld && !empty($rowOld['image'])) {
        $oldFile = UPLOAD_DIR . $rowOld['image'];
        if (file_exists($oldFile)) {
            unlink($oldFile);
        }
    }
    // Hapus row dari tabel tickets
    $stmtDel = $pdo->prepare('DELETE FROM tickets WHERE id = ?');
    $stmtDel->execute([$del_id]);

    $_SESSION['flash_message'] = [
        'type' => 'success',
        'text' => 'Item berhasil dihapus.'
    ];
    header('Location: dashboard.php');
    exit;
}

/**
 * ============================================================
 * BAGIAN 3: AMBIL DAFTAR ITEM PER TYPE UNTUK DITAMPILKAN
 * ============================================================
 */
$types = ['pesawat', 'kapal', 'kereta', 'penginapan', 'kendaraan'];
$all_items = [];
foreach ($types as $type) {
    // Tentukan ORDER BY default
    $orderBy = 'ORDER BY id DESC';

    // Cek kolom mana yang ada
    $sampleItemCheck = null;
    try {
        $checkStmt = $pdo->query("SELECT * FROM tickets WHERE type = '{$type}' LIMIT 1");
        $sampleItemCheck = $checkStmt ? $checkStmt->fetch(PDO::FETCH_ASSOC) : null;
    } catch (PDOException $e) {
        $sampleItemCheck = null;
    }
    $columnsExist = $sampleItemCheck ? array_keys($sampleItemCheck) : [];

    if (in_array($type, ['pesawat', 'kapal', 'kereta'])) {
        if (in_array('depart_date', $columnsExist)) {
            $orderBy = 'ORDER BY depart_date DESC, id DESC';
        }
    } elseif ($type === 'penginapan') {
        if (in_array('check_in_date', $columnsExist)) {
            $orderBy = 'ORDER BY check_in_date DESC, id DESC';
        }
    } elseif ($type === 'kendaraan') {
        if (in_array('rental_start_date', $columnsExist)) {
            $orderBy = 'ORDER BY rental_start_date DESC, id DESC';
        }
    }

    $stmt = $pdo->prepare("SELECT * FROM tickets WHERE type = ? {$orderBy}");
    $stmt->execute([$type]);
    $all_items[$type] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Ambil flash message jika ada
 */
$flash_message = null;
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <!-- Sertakan TailwindCSS atau CSS lain jika diperlukan -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.2.7/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <div class="container mx-auto mt-8 px-4 sm:px-6 lg:px-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
            <p class="text-sm text-gray-500">Kelola semua jenis layanan: Transportasi, Penginapan, dan Sewa Kendaraan.</p>
        </header>

        <!-- Tampilkan pesan sukses / error jika ada -->
        <?php if ($flash_message): ?>
            <div class="mb-6 p-4 rounded-md shadow-sm
            <?= $flash_message['type'] === 'success'
                ? 'bg-green-100 text-green-700 border border-green-300'
                : 'bg-red-100 text-red-700 border border-red-300' ?>"
                role="alert">
                <?= htmlspecialchars($flash_message['text']) ?>
            </div>
        <?php endif; ?>

        <!-- Jika ada parameter ?type=..., tampilkan form penambahan tiket baru -->
        <?php if ($tipe_baru && in_array($tipe_baru, $allowed_types)): ?>
            <div class="mb-8 bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-2xl font-semibold text-gray-700 capitalize">Tambah <?= htmlspecialchars($tipe_baru) ?> Baru</h2>
                </div>
                <div class="p-6">
                    <?php if (!empty($add_error)): ?>
                        <div class="mb-4 p-3 rounded-md bg-red-100 text-red-700 border border-red-300" role="alert">
                            <?= htmlspecialchars($add_error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="dashboard.php?type=<?= urlencode($tipe_baru) ?>" enctype="multipart/form-data" class="space-y-6">
                        <!-- Nama Layanan / Produk -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama <?= ucfirst($tipe_baru) ?></label>
                            <input id="name" name="name" type="text" required
                                class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Masukkan nama <?= $tipe_baru ?>" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>

                        <!-- Harga -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                            <input id="price" name="price" type="number" step="0.01" min="0" required
                                class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Masukkan harga" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                        </div>

                        <!-- Total Seats / Unit -->
                        <div>
                            <label for="total_seats" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Total (Total/Avail.)</label>
                            <input id="total_seats" name="total_seats" type="number" min="1" required
                                class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Masukkan jumlah total" value="<?= htmlspecialchars($_POST['total_seats'] ?? '') ?>">
                        </div>

                        <!-- Field tambahan berdasarkan tipe -->
                        <?php if (in_array($tipe_baru, ['pesawat', 'kapal', 'kereta'])): ?>
                            <div>
    <label for="origin" class="block text-sm font-medium text-gray-700 mb-1">Asal</label>
    <input id="origin" name="origin" type="text" required
      class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500"
      placeholder="Masukkan kota asal"
      value="<?= htmlspecialchars($_POST['origin'] ?? '') ?>">
  </div>

  <!-- Tujuan -->
  <div>
    <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Tujuan</label>
    <input id="destination" name="destination" type="text" required
      class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500"
      placeholder="Masukkan kota tujuan"
      value="<?= htmlspecialchars($_POST['destination'] ?? '') ?>">
  </div>

  <!-- Tanggal Berangkat -->
  <div>
    <label for="depart_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Berangkat</label>
    <input id="depart_date" name="depart_date" type="date" required
      class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500"
      value="<?= htmlspecialchars($_POST['depart_date'] ?? '') ?>">
  </div>

  <!-- **Jam Berangkat** -->
  <div>
    <label for="depart_time" class="block text-sm font-medium text-gray-700 mb-1">Jam Berangkat</label>
    <input id="depart_time" name="depart_time" type="time" required
      class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500"
      value="<?= htmlspecialchars($_POST['depart_time'] ?? '') ?>">
  </div>
                        <?php elseif ($tipe_baru === 'penginapan'): ?>
                            <div>
                                <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Lokasi Penginapan</label>
                                <input id="destination" name="destination" type="text" required
                                    class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    placeholder="Masukkan lokasi penginapan" value="<?= htmlspecialchars($_POST['destination'] ?? '') ?>">
                            </div>
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Alamat lengkap (opsional)</label>
                                <textarea id="address" name="address" rows="2"
                                    class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    placeholder="Masukkan alamat lengkap"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                            </div>
                            <div>
                                <label for="check_in_date" class="block text-sm font-medium text-gray-700 mb-1">Default Check-in</label>
                                <input id="check_in_date" name="check_in_date" type="date" required
                                    class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    value="<?= htmlspecialchars($_POST['check_in_date'] ?? '') ?>">
                            </div>
                        <?php elseif ($tipe_baru === 'kendaraan'): ?>
                            <div>
                                <label for="origin" class="block text-sm font-medium text-gray-700 mb-1">Lokasi Pickup</label>
                                <input id="origin" name="origin" type="text" required
                                    class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    placeholder="Masukkan lokasi pickup" value="<?= htmlspecialchars($_POST['origin'] ?? '') ?>">
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label for="vehicle_brand" class="block text-sm font-medium text-gray-700 mb-1">Merek</label>
                                    <input id="vehicle_brand" name="vehicle_brand" type="text" required
                                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="Contoh: Toyota" value="<?= htmlspecialchars($_POST['vehicle_brand'] ?? '') ?>">
                                </div>
                                <div>
                                    <label for="vehicle_model" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                                    <input id="vehicle_model" name="vehicle_model" type="text" required
                                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="Contoh: Avanza" value="<?= htmlspecialchars($_POST['vehicle_model'] ?? '') ?>">
                                </div>
                                <div>
                                    <label for="vehicle_year" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                                    <input id="vehicle_year" name="vehicle_year" type="number" min="1900" max="<?= date('Y') ?>" required
                                        class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="Contoh: 2020" value="<?= htmlspecialchars($_POST['vehicle_year'] ?? '') ?>">
                                </div>
                            </div>
                            <div>
                                <label for="rental_start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai Sewa</label>
                                <input id="rental_start_date" name="rental_start_date" type="datetime-local" required
                                    class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    value="<?= htmlspecialchars($_POST['rental_start_date'] ?? '') ?>">
                            </div>
                        <?php endif; ?>

                        <!-- Upload Gambar -->
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Upload Gambar (jpg/png/gif, max 2MB)</label>
                            <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.gif"
                                class="block w-full text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 py-2">
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Simpan <?= ucfirst($tipe_baru) ?>
                            </button>
                            <a href="dashboard.php" class="ml-4 text-gray-600 hover:underline">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- DAFTAR TIAP TYPE (LISTING) -->
        <?php foreach ($all_items as $type => $list): ?>
            <section class="mb-12 bg-white shadow-lg rounded-lg overflow-hidden">
                <header class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-semibold text-gray-700 capitalize"><?= htmlspecialchars($type) ?></h2>
                        <a href="dashboard.php?type=<?= urlencode($type) ?>"
                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                            <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Tambah <?= ucfirst($type) ?>
                        </a>
                    </div>
                </header>
                <div class="p-6">
                    <?php if (empty($list)): ?>
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data untuk <?= htmlspecialchars($type) ?></h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan item baru.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto shadow border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <?php
                                            if (in_array($type, ['pesawat', 'kapal', 'kereta'])) echo 'Rute';
                                            elseif ($type === 'penginapan') echo 'Lokasi';
                                            elseif ($type === 'kendaraan') echo 'Lokasi Pickup';
                                            else echo 'Detail';
                                            ?>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <?php
                                            if (in_array($type, ['pesawat', 'kapal', 'kereta'])) echo 'Tgl Berangkat';
                                            elseif ($type === 'penginapan') echo 'Default Check-in';
                                            elseif ($type === 'kendaraan') echo 'Default Mulai Sewa';
                                            else echo 'Tanggal';
                                            ?>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga (Rp)</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <?php
                                            if (in_array($type, ['pesawat', 'kapal', 'kereta'])) echo 'Kursi (Total/Avail.)';
                                            elseif ($type === 'penginapan') echo 'Kamar (Total/Avail.)';
                                            elseif ($type === 'kendaraan') echo 'Unit (Total/Avail.)';
                                            else echo 'Jumlah (Total/Avail.)';
                                            ?>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($list as $item): ?>
                                        <?php
                                        $bookedCount = 0;
                                        if (in_array($item['type'], ['pesawat', 'kapal', 'kereta'])) {
                                            $stmtBooked = $pdo->prepare('SELECT COUNT(*) AS cnt FROM seats WHERE ticket_id = ? AND is_booked = 1');
                                            $stmtBooked->execute([$item['id']]);
                                            $rowBooked = $stmtBooked->fetch(PDO::FETCH_ASSOC);
                                            $bookedCount = intval($rowBooked['cnt']);
                                        }
                                        $available = intval($item['total_seats']) - $bookedCount;
                                        ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $item['id'] ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if (!empty($item['image']) && file_exists(UPLOAD_DIR . $item['image'])): ?>
                                                    <img src="uploads/<?= htmlspecialchars($item['image']) ?>"
                                                        alt="<?= htmlspecialchars($item['name']) ?>"
                                                        class="w-20 h-12 object-cover rounded shadow-sm">
                                                <?php else: ?>
                                                    <div class="w-20 h-12 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs italic shadow-sm">
                                                        No Image
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                <?= htmlspecialchars($item['name']) ?>
                                                <?php if ($item['type'] === 'kendaraan'): ?>
                                                    <br>
                                                    <span class="text-xs text-gray-500">
                                                        <?= htmlspecialchars($item['vehicle_brand'] ?? '') . ' ' .
                                                            htmlspecialchars($item['vehicle_model'] ?? '') . ' (' .
                                                            htmlspecialchars($item['vehicle_year'] ?? '') . ')' ?>
                                                    </span>
                                                <?php elseif ($item['type'] === 'penginapan'): ?>
                                                    <br>
                                                    <span class="text-xs text-gray-500">
                                                        Tipe: <?= htmlspecialchars($item['room_type'] ?? 'N/A') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php
                                                if (in_array($item['type'], ['pesawat', 'kapal', 'kereta'])) {
                                                    echo htmlspecialchars($item['origin'] ?? 'N/A') . ' &rarr; ' . htmlspecialchars($item['destination'] ?? 'N/A');
                                                } elseif ($item['type'] === 'penginapan') {
                                                    echo htmlspecialchars($item['destination'] ?? 'N/A');
                                                    if (!empty($item['address'])) {
                                                        echo "<br><span class='text-xs text-gray-400'>" . htmlspecialchars(substr($item['address'], 0, 30)) . "...</span>";
                                                    }
                                                } elseif ($item['type'] === 'kendaraan') {
                                                    echo htmlspecialchars($item['origin'] ?? 'N/A');
                                                }
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php
                                                $date_to_display = 'N/A';
                                               if (in_array($item['type'], ['pesawat','kapal','kereta'])
        && !empty($item['depart_date'])
        && !empty($item['depart_time'])) {
        $dt = $item['depart_date'] . ' ' . $item['depart_time'];
        $date_to_display = date("d M Y, H:i", strtotime($dt));
    }
                                                echo htmlspecialchars($date_to_display);
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Rp <?= number_format($item['price'], 0, ',', '.') ?>
                                                <?php if ($item['type'] === 'penginapan'): ?>
                                                    <span class="text-xs">/malam</span>
                                                <?php elseif ($item['type'] === 'kendaraan'): ?>
                                                    <span class="text-xs">/hari</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                <?= $item['total_seats'] ?> /
                                                <span class="font-semibold <?= $available > 0 ? 'text-green-600' : 'text-red-600' ?>">
                                                    <?= $available ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($available > 0): ?>
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Tersedia
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Habis/Full Booked
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                                <!-- Link edit bisa diarahkan ke edit_ticket.php (jika ada) -->
                                                <a href="edit_ticket.php?id=<?= $item['id'] ?>&type=<?= $item['type'] ?>"
                                                    class="text-indigo-600 hover:text-indigo-900 transition duration-150 ease-in-out" title="Edit">
                                                    <svg class="inline w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                                    </svg>
                                                </a>
                                                <a href="dashboard.php?delete_id=<?= $item['id'] ?>"
                                                    class="text-red-600 hover:text-red-900 transition duration-150 ease-in-out"
                                                    title="Delete"
                                                    onclick="return confirm('Anda yakin ingin menghapus item ini: <?= htmlspecialchars(addslashes($item['name'])) ?>?');">
                                                    <svg class="inline w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <?php include 'footer.php'; ?>