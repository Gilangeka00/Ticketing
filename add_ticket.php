<?php
// add_ticket.php
include 'config.php';
include 'header.php';

// Pastikan hanya admin yang dapat akses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Ambil param tipe tiket dari query string (pesawat/kapal/kereta/penginapan/kendaraan)
$allowed_types = ['pesawat', 'kapal', 'kereta', 'penginapan', 'kendaraan'];
$type = isset($_GET['type']) && in_array($_GET['type'], $allowed_types)
        ? $_GET['type']
        : 'pesawat'; // Default

$error = '';
$success = '';

// Variabel untuk menyimpan nilai input jika ada error dan form di-reload
$input = $_POST; // Simpan semua post data untuk prefill jika ada error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil tipe dari hidden field jika ada (lebih aman saat POST)
    $type = $_POST['type'] ?? $type;

    // --- VALIDASI DAN PENGAMBILAN DATA UMUM ---
    $name         = trim($_POST['name'] ?? '');
    $price        = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
    $total_seats  = filter_var($_POST['total_seats'] ?? 1, FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "default"=>1]]); // Untuk penginapan/mobil: jumlah unit tersedia

    if (empty($name) || $price === false || $price <= 0) {
        $error = 'Nama, Harga, dan Jumlah Unit/Kursi harus diisi dengan benar.';
    }

    // --- PENGAMBILAN DATA SPESIFIK BERDASARKAN TIPE ---
    $origin = null; $destination = null; $depart_date = null; // Transportasi
    $check_in_date = null; $check_out_date = null; $num_guests = null; $room_type = null; $address = null; // Penginapan
    $rental_start_date = null; $rental_end_date = null; $vehicle_brand = null; $vehicle_model = null; $vehicle_year = null; $with_driver = null; // Kendaraan

    if ($type === 'pesawat' || $type === 'kapal' || $type === 'kereta') {
        $origin       = trim($_POST['origin'] ?? '');
        $destination  = trim($_POST['destination'] ?? '');
        $depart_date  = $_POST['depart_date'] ?? '';
        if (empty($origin) || empty($destination) || empty($depart_date)) {
            $error = 'Untuk transportasi, Origin, Destination, dan Tanggal Berangkat wajib diisi.';
        }
    } elseif ($type === 'penginapan') {
        $destination = trim($_POST['location'] ?? ''); // Lokasi penginapan disimpan di 'destination'
        $address = trim($_POST['address'] ?? '');
        $check_in_date = $_POST['check_in_date'] ?? '';
        $check_out_date = $_POST['check_out_date'] ?? '';
        $num_guests = filter_var($_POST['num_guests'] ?? 1, FILTER_VALIDATE_INT, ["options" => ["min_range"=>1]]);
        $room_type = trim($_POST['room_type'] ?? '');
        if (empty($destination) || empty($address) || empty($check_in_date) || empty($check_out_date) || !$num_guests || empty($room_type)) {
            $error = 'Untuk penginapan, semua field spesifik wajib diisi.';
        } elseif (strtotime($check_out_date) <= strtotime($check_in_date)) {
            $error = 'Tanggal Check-out harus setelah Tanggal Check-in.';
        }
    } elseif ($type === 'kendaraan') {
        $origin = trim($_POST['pickup_location'] ?? ''); // Lokasi pickup disimpan di 'origin'
        $rental_start_date = $_POST['rental_start_date'] ?? '';
        $rental_end_date = $_POST['rental_end_date'] ?? '';
        $vehicle_brand = trim($_POST['vehicle_brand'] ?? '');
        $vehicle_model = trim($_POST['vehicle_model'] ?? '');
        $vehicle_year = filter_var($_POST['vehicle_year'] ?? date('Y'), FILTER_VALIDATE_INT);
        $with_driver = isset($_POST['with_driver']) ? (bool)$_POST['with_driver'] : false;
         if (empty($origin) || empty($rental_start_date) || empty($rental_end_date) || empty($vehicle_brand) || empty($vehicle_model) || !$vehicle_year) {
            $error = 'Untuk kendaraan, semua field spesifik wajib diisi.';
        } elseif (strtotime($rental_end_date) <= strtotime($rental_start_date)) {
            $error = 'Tanggal Selesai Sewa harus setelah Tanggal Mulai Sewa.';
        }
    }

    // --- Proses upload gambar (sama seperti sebelumnya) ---
    $imgName = null;
    if (empty($error) && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // ... (logika upload gambar Anda) ...
        } else {
            $error = 'Terjadi kesalahan saat upload gambar.';
        }
    }

    // --- Jika tidak ada error, simpan ke DB ---
    if (empty($error)) {
        $sql = "INSERT INTO tickets (
                    type, name, price, total_seats, image, 
                    origin, destination, depart_date, 
                    check_in_date, check_out_date, num_guests, room_type, address,
                    rental_start_date, rental_end_date, vehicle_brand, vehicle_model, vehicle_year, with_driver
                ) VALUES (
                    :type, :name, :price, :total_seats, :image, 
                    :origin, :destination, :depart_date, 
                    :check_in_date, :check_out_date, :num_guests, :room_type, :address,
                    :rental_start_date, :rental_end_date, :vehicle_brand, :vehicle_model, :vehicle_year, :with_driver
                )";
        $stmt = $pdo->prepare($sql);
        
        $params = [
            ':type' => $type,
            ':name' => $name,
            ':price' => $price,
            ':total_seats' => $total_seats,
            ':image' => $imgName,
            ':origin' => $origin,
            ':destination' => $destination,
            ':depart_date' => ($type === 'pesawat' || $type === 'kapal' || $type === 'kereta') ? $depart_date : null,
            ':check_in_date' => ($type === 'penginapan') ? $check_in_date : null,
            ':check_out_date' => ($type === 'penginapan') ? $check_out_date : null,
            ':num_guests' => ($type === 'penginapan') ? $num_guests : null,
            ':room_type' => ($type === 'penginapan') ? $room_type : null,
            ':address' => ($type === 'penginapan') ? $address : null,
            ':rental_start_date' => ($type === 'kendaraan') ? $rental_start_date : null,
            ':rental_end_date' => ($type === 'kendaraan') ? $rental_end_date : null,
            ':vehicle_brand' => ($type === 'kendaraan') ? $vehicle_brand : null,
            ':vehicle_model' => ($type === 'kendaraan') ? $vehicle_model : null,
            ':vehicle_year' => ($type === 'kendaraan') ? $vehicle_year : null,
            ':with_driver' => ($type === 'kendaraan') ? $with_driver : null,
        ];

        try {
            $stmt->execute($params);
            $ticket_id = $pdo->lastInsertId();

            // Jika tipe adalah transportasi, dan Anda masih menggunakan tabel seats untuk ketersediaan per kursi
            // Anda mungkin perlu logika tambahan di sini.
            // Untuk penginapan/kendaraan dengan total_seats > 1 (unit tersedia), 
            // tabel 'seats' mungkin tidak langsung digunakan, atau Anda buat sistem ketersediaan berbeda.
            // Untuk sederhana, kita tidak populate 'seats' untuk penginapan/kendaraan di sini.

            $_SESSION['flash_success'] = ucfirst($type) . " '" . htmlspecialchars($name) . "' berhasil ditambahkan.";
            header('Location: dashboard.php'); // Redirect kembali ke dashboard
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menambahkan ke database: ' . $e->getMessage();
        }
    }
}
?>

<div class="container mx-auto mt-10 px-4">
  <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-2xl">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Tambah Item Baru</h2>
        <select id="typeSelector" name="type_selector" class="border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <?php foreach ($allowed_types as $t_opt): ?>
            <option value="<?= $t_opt ?>" <?= $type === $t_opt ? 'selected' : '' ?>><?= ucfirst($t_opt) ?></option>
            <?php endforeach; ?>
        </select>
    </div>


    <?php if (!empty($error)): ?>
      <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-300 rounded-md" role="alert">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
     <?php if (!empty($success)): // Jarang dipakai jika redirect ?>
      <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-300 rounded-md" role="alert">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="add_ticket.php" enctype="multipart/form-data">
      <input type="hidden" name="type" id="formType" value="<?= htmlspecialchars($type) ?>">

      <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700">Nama Layanan/Tiket</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($input['name'] ?? '') ?>" required 
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        <p class="text-xs text-gray-500 mt-1">Cth: Garuda GA-201, Hotel Melati, Sewa Avanza Harian.</p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label for="price" class="block text-sm font-medium text-gray-700">Harga (Rp)</label>
            <input type="number" name="price" id="price" value="<?= htmlspecialchars($input['price'] ?? '') ?>" required step="1000" min="0"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
             <p class="text-xs text-gray-500 mt-1" id="price_help_text">Per tiket/malam/hari.</p>
        </div>
        <div>
            <label for="total_seats" class="block text-sm font-medium text-gray-700">Jumlah Unit/Kamar/Kursi</label>
            <input type="number" name="total_seats" id="total_seats" value="<?= htmlspecialchars($input['total_seats'] ?? 1) ?>" required min="1"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <p class="text-xs text-gray-500 mt-1" id="total_seats_help_text">Total unit/kamar/kursi yang tersedia.</p>
        </div>
      </div>
       <div class="mb-6">
        <label for="image" class="block text-sm font-medium text-gray-700">Gambar Utama (Opsional)</label>
        <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png"
               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
      </div>

      <fieldset id="transportFields" class="space-y-4 border-t border-gray-200 pt-4 mt-6 <?= !in_array($type, ['pesawat','kapal','kereta']) ? 'hidden' : '' ?>">
        <legend class="text-base font-medium text-gray-900 mb-2">Detail Transportasi</legend>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="origin" class="block text-sm font-medium text-gray-700">Origin</label>
                <input type="text" name="origin" id="origin" value="<?= htmlspecialchars($input['origin'] ?? '') ?>" class="mt-1 block w-full input-field">
            </div>
            <div>
                <label for="destination_transport" class="block text-sm font-medium text-gray-700">Destination</label>
                <input type="text" name="destination" id="destination_transport" value="<?= htmlspecialchars($input['destination'] ?? '') ?>" class="mt-1 block w-full input-field">
            </div>
        </div>
        <div>
            <label for="depart_date" class="block text-sm font-medium text-gray-700">Tanggal Berangkat</label>
            <input type="date" name="depart_date" id="depart_date" value="<?= htmlspecialchars($input['depart_date'] ?? '') ?>" class="mt-1 block w-full input-field" min="<?= date('Y-m-d') ?>">
        </div>
      </fieldset>

      <fieldset id="accommodationFields" class="space-y-4 border-t border-gray-200 pt-4 mt-6 <?= $type !== 'penginapan' ? 'hidden' : '' ?>">
        <legend class="text-base font-medium text-gray-900 mb-2">Detail Penginapan</legend>
        <div>
            <label for="location_accommodation" class="block text-sm font-medium text-gray-700">Lokasi (Kota/Area)</label>
            <input type="text" name="location" id="location_accommodation" value="<?= htmlspecialchars($input['location'] ?? ($input['destination'] ?? '')) ?>" class="mt-1 block w-full input-field" placeholder="Cth: Yogyakarta">
        </div>
        <div>
            <label for="address" class="block text-sm font-medium text-gray-700">Alamat Lengkap Penginapan</label>
            <textarea name="address" id="address" rows="2" class="mt-1 block w-full input-field"><?= htmlspecialchars($input['address'] ?? '') ?></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="check_in_date" class="block text-sm font-medium text-gray-700">Tanggal Check-in (Default)</label>
                <input type="date" name="check_in_date" id="check_in_date" value="<?= htmlspecialchars($input['check_in_date'] ?? '') ?>" class="mt-1 block w-full input-field" min="<?= date('Y-m-d') ?>">
                <p class="text-xs text-gray-500 mt-1">Ini bisa jadi tanggal default ketersediaan awal.</p>
            </div>
            <div>
                <label for="check_out_date" class="block text-sm font-medium text-gray-700">Tanggal Check-out (Default)</label>
                <input type="date" name="check_out_date" id="check_out_date" value="<?= htmlspecialchars($input['check_out_date'] ?? '') ?>" class="mt-1 block w-full input-field" min="<?= date('Y-m-d') ?>">
            </div>
        </div>
         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="num_guests" class="block text-sm font-medium text-gray-700">Kapasitas Tamu per Unit</label>
                <input type="number" name="num_guests" id="num_guests" value="<?= htmlspecialchars($input['num_guests'] ?? 2) ?>" min="1" class="mt-1 block w-full input-field">
            </div>
            <div>
                <label for="room_type" class="block text-sm font-medium text-gray-700">Tipe Kamar/Unit</label>
                <input type="text" name="room_type" id="room_type" value="<?= htmlspecialchars($input['room_type'] ?? 'Standard Room') ?>" class="mt-1 block w-full input-field">
            </div>
        </div>
      </fieldset>

      <fieldset id="vehicleFields" class="space-y-4 border-t border-gray-200 pt-4 mt-6 <?= $type !== 'kendaraan' ? 'hidden' : '' ?>">
        <legend class="text-base font-medium text-gray-900 mb-2">Detail Kendaraan</legend>
        <div>
            <label for="pickup_location" class="block text-sm font-medium text-gray-700">Lokasi Pickup Kendaraan</label>
            <input type="text" name="pickup_location" id="pickup_location" value="<?= htmlspecialchars($input['pickup_location'] ?? ($input['origin'] ?? '')) ?>" class="mt-1 block w-full input-field" placeholder="Cth: Bandara YIA">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="rental_start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai Tersedia (Default)</label>
                <input type="datetime-local" name="rental_start_date" id="rental_start_date" value="<?= htmlspecialchars(isset($input['rental_start_date']) ? date('Y-m-d\TH:i', strtotime($input['rental_start_date'])) : '') ?>" class="mt-1 block w-full input-field" min="<?= date('Y-m-d\TH:i') ?>">
            </div>
            <div>
                <label for="rental_end_date" class="block text-sm font-medium text-gray-700">Tanggal Selesai Tersedia (Default)</label>
                <input type="datetime-local" name="rental_end_date" id="rental_end_date" value="<?= htmlspecialchars(isset($input['rental_end_date']) ? date('Y-m-d\TH:i', strtotime($input['rental_end_date'])) : '') ?>" class="mt-1 block w-full input-field" min="<?= date('Y-m-d\TH:i') ?>">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="vehicle_brand" class="block text-sm font-medium text-gray-700">Merek Kendaraan</label>
                <input type="text" name="vehicle_brand" id="vehicle_brand" value="<?= htmlspecialchars($input['vehicle_brand'] ?? '') ?>" class="mt-1 block w-full input-field" placeholder="Cth: Toyota">
            </div>
            <div>
                <label for="vehicle_model" class="block text-sm font-medium text-gray-700">Model Kendaraan</label>
                <input type="text" name="vehicle_model" id="vehicle_model" value="<?= htmlspecialchars($input['vehicle_model'] ?? '') ?>" class="mt-1 block w-full input-field" placeholder="Cth: Avanza">
            </div>
            <div>
                <label for="vehicle_year" class="block text-sm font-medium text-gray-700">Tahun Kendaraan</label>
                <input type="number" name="vehicle_year" id="vehicle_year" value="<?= htmlspecialchars($input['vehicle_year'] ?? date('Y')) ?>" min="1990" max="<?= date('Y') + 1 ?>" class="mt-1 block w-full input-field">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Opsi Supir</label>
            <div class="mt-2 space-y-2">
                <div class="flex items-center">
                    <input id="with_driver_yes" name="with_driver" type="radio" value="1" <?= (isset($input['with_driver']) && $input['with_driver'] == 1) ? 'checked' : '' ?> class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                    <label for="with_driver_yes" class="ml-3 block text-sm font-medium text-gray-700">Dengan Supir</label>
                </div>
                <div class="flex items-center">
                    <input id="with_driver_no" name="with_driver" type="radio" value="0" <?= (!isset($input['with_driver']) || (isset($input['with_driver']) && $input['with_driver'] == 0)) ? 'checked' : '' ?> class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                    <label for="with_driver_no" class="ml-3 block text-sm font-medium text-gray-700">Tanpa Supir (Lepas Kunci)</label>
                </div>
            </div>
        </div>
      </fieldset>
      
      <style>
  .input-field {
    @apply border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm;
  }
</style>

      <div class="mt-8">
        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-md shadow-md transition duration-150 ease-in-out">
          Tambah <?= ucfirst(htmlspecialchars($type)) ?>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelector = document.getElementById('typeSelector');
    const formTypeInput = document.getElementById('formType');
    const transportFields = document.getElementById('transportFields');
    const accommodationFields = document.getElementById('accommodationFields');
    const vehicleFields = document.getElementById('vehicleFields');
    const submitButton = document.querySelector('button[type="submit"]');
    const priceHelpText = document.getElementById('price_help_text');
    const totalSeatsHelpText = document.getElementById('total_seats_help_text');

    function updateFormFields(selectedType) {
        formTypeInput.value = selectedType; // Update hidden input type

        // Sembunyikan semua fieldset spesifik
        transportFields.classList.add('hidden');
        accommodationFields.classList.add('hidden');
        vehicleFields.classList.add('hidden');

        // Tandai semua input spesifik sebagai tidak required dulu
        [transportFields, accommodationFields, vehicleFields].forEach(fieldset => {
            fieldset.querySelectorAll('input, textarea, select').forEach(input => input.required = false);
        });

        // Tampilkan fieldset yang relevan dan set inputnya sebagai required
        if (selectedType === 'pesawat' || selectedType === 'kapal' || selectedType === 'kereta') {
            transportFields.classList.remove('hidden');
            transportFields.querySelectorAll('#origin, #destination_transport, #depart_date').forEach(input => input.required = true);
            priceHelpText.textContent = 'Harga per tiket.';
            totalSeatsHelpText.textContent = 'Total kursi yang tersedia.';
        } else if (selectedType === 'penginapan') {
            accommodationFields.classList.remove('hidden');
            accommodationFields.querySelectorAll('#location_accommodation, #address, #check_in_date, #check_out_date, #num_guests, #room_type').forEach(input => input.required = true);
            priceHelpText.textContent = 'Harga per malam.';
            totalSeatsHelpText.textContent = 'Total kamar/unit tipe ini yang tersedia.';
        } else if (selectedType === 'kendaraan') {
            vehicleFields.classList.remove('hidden');
            vehicleFields.querySelectorAll('#pickup_location, #rental_start_date, #rental_end_date, #vehicle_brand, #vehicle_model, #vehicle_year').forEach(input => input.required = true);
            // Radio button untuk 'with_driver' sudah punya validasi bawaan jika salah satu harus dipilih
            priceHelpText.textContent = 'Harga sewa per hari.';
            totalSeatsHelpText.textContent = 'Total unit kendaraan tipe ini yang tersedia.';
        }
        submitButton.textContent = 'Tambah ' + selectedType.charAt(0).toUpperCase() + selectedType.slice(1);
    }

    typeSelector.addEventListener('change', function() {
        updateFormFields(this.value);
    });

    // Panggil sekali saat load untuk menyesuaikan dengan ?type= di URL
    updateFormFields(typeSelector.value);
});
</script>

<?php include 'footer.php'; ?>