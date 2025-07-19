<?php
// index.php
include 'config.php';
include 'header.php';

// Ambil beberapa contoh tiket terbaru untuk masing-masing kategori
// Pisahkan query untuk setiap jenis transportasi
$latest_pesawat = $pdo->query("SELECT * FROM tickets WHERE type = 'pesawat' ORDER BY created_at DESC LIMIT 3")->fetchAll();
$latest_kapal = $pdo->query("SELECT * FROM tickets WHERE type = 'kapal' ORDER BY created_at DESC LIMIT 3")->fetchAll();
$latest_kereta = $pdo->query("SELECT * FROM tickets WHERE type = 'kereta' ORDER BY created_at DESC LIMIT 3")->fetchAll();

// Query untuk penginapan dan sewa kendaraan tetap sama
$latest_accommodation = $pdo->query("SELECT * FROM tickets WHERE type = 'penginapan' ORDER BY created_at DESC LIMIT 3")->fetchAll();
$latest_rentals = $pdo->query("SELECT * FROM tickets WHERE type = 'kendaraan' ORDER BY created_at DESC LIMIT 3")->fetchAll();

?>

<?php
// Cek apakah sedang melakukan pencarian
$search_results = [];
$is_searching = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search_type']) && $_GET['search_type'] === 'all_transport') {
    $is_searching = true;

    $origin = $_GET['origin'] ?? '';
    $destination = $_GET['destination'] ?? '';
    $depart_date = $_GET['depart_date'] ?? '';

    $whereClauses = ["type IN ('pesawat', 'kapal', 'kereta')"];
    $params = [];

    if (!empty($origin)) {
        $whereClauses[] = "origin LIKE :origin";
        $params[':origin'] = "%$origin%";
    }

    if (!empty($destination)) {
        $whereClauses[] = "destination LIKE :destination";
        $params[':destination'] = "%$destination%";
    }

    if (!empty($depart_date)) {
        $whereClauses[] = "depart_date = :depart_date";
        $params[':depart_date'] = $depart_date;
    }

    $sql = "SELECT * FROM tickets WHERE " . implode(" AND ", $whereClauses) . " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $search_results = $stmt->fetchAll();
}
?>

<div class="bg-cover bg-center h-72"
    style="background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');">
    <div class="bg-black bg-opacity-60 h-full flex items-center justify-center">
        <div class="text-center text-white px-4">
            <h1 class="text-4xl md:text-5xl font-bold">Temukan Perjalanan Impian Anda</h1>
            <p class="mt-3 text-lg md:text-xl">Pesan Tiket Pesawat, Kapal, Kereta, Penginapan, dan Sewa Kendaraan dengan
                Mudah!</p>
        </div>
    </div>
</div>

<div class="max-w-full mx-auto -mt-16 mb-10 px-4 sm:px-6 lg:px-8">

    <div class="bg-white p-6 rounded-xl shadow-2xl">
        <div x-data="{ activeTab: 'transportasi' }" class="mb-4">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'transportasi'"
                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'transportasi', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'transportasi' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Transportasi
                    </button>
                    <button @click="activeTab = 'penginapan'"
                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'penginapan', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'penginapan' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Penginapan
                    </button>
                    <button @click="activeTab = 'kendaraan'"
                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'kendaraan', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'kendaraan' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Sewa Kendaraan
                    </button>
                </nav>
            </div>

            <div x-show="activeTab === 'transportasi'" class="mt-6">
                <form action="index.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <input type="hidden" name="search_type" value="all_transport">
                    <div>
                        <label for="transport_origin" class="block text-sm font-medium text-gray-700">Dari</label>
                        <input type="text" id="transport_origin" name="origin" placeholder="Kota atau Bandara Asal"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="transport_destination" class="block text-sm font-medium text-gray-700">Ke</label>
                        <input type="text" id="transport_destination" name="destination"
                            placeholder="Kota atau Bandara Tujuan"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="transport_depart_date" class="block text-sm font-medium text-gray-700">Tanggal
                            Berangkat</label>
                        <input type="date" id="transport_depart_date" name="depart_date"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            min="<?= date('Y-m-d') ?>">
                    </div>
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-md shadow-md transition duration-150 ease-in-out">
                        Cari Transportasi
                    </button>
                </form>
            </div>

            <div x-show="activeTab === 'penginapan'" class="mt-6">
                <form action="accommodation_search.php" method="GET"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label for="acc_location" class="block text-sm font-medium text-gray-700">Lokasi
                            (Kota/Area)</label>
                        <input type="text" id="acc_location" name="location" placeholder="Contoh: Yogyakarta"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            required>
                    </div>
                    <div>
                        <label for="acc_check_in" class="block text-sm font-medium text-gray-700">Tanggal
                            Check-in</label>
                        <input type="date" id="acc_check_in" name="check_in_date"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div>
                        <label for="acc_check_out" class="block text-sm font-medium text-gray-700">Tanggal
                            Check-out</label>
                        <input type="date" id="acc_check_out" name="check_out_date"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>
                    <div>
                        <label for="acc_num_guests" class="block text-sm font-medium text-gray-700">Jumlah Tamu</label>
                        <input type="number" id="acc_num_guests" name="num_guests" min="1" value="1"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            required>
                    </div>
                    <button type="submit"
                        class="md:col-start-4 w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 px-4 rounded-md shadow-md transition duration-150 ease-in-out">
                        Cari Penginapan
                    </button>
                </form>
            </div>

            <div x-show="activeTab === 'kendaraan'" class="mt-6">
                <form action="vehicle_search.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label for="vhc_location" class="block text-sm font-medium text-gray-700">Lokasi Sewa
                            (Kota)</label>
                        <input type="text" id="vhc_location" name="location" placeholder="Contoh: Bali"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            required>
                    </div>
                    <div>
                        <label for="vhc_start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai
                            Sewa</label>
                        <input type="date" id="vhc_start_date" name="rental_start_date"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div>
                        <label for="vhc_end_date" class="block text-sm font-medium text-gray-700">Tanggal Selesai
                            Sewa</label>
                        <input type="date" id="vhc_end_date" name="rental_end_date"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div>
                        <label for="vhc_with_driver" class="block text-sm font-medium text-gray-700">Dengan
                            Supir?</label>
                        <select id="vhc_with_driver" name="with_driver"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Semua</option>
                            <option value="1">Ya</option>
                            <option value="0">Tidak (Lepas Kunci)</option>
                        </select>
                    </div>
                    <button type="submit"
                        class="md:col-start-4 w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold py-2.5 px-4 rounded-md shadow-md transition duration-150 ease-in-out">
                        Cari Kendaraan
                    </button>
                </form>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    </div>
</div>

<div class="w-full px-4 sm:px-6 lg:px-8 box-border overflow-hidden mb-5">

    <?php if ($is_searching): ?>
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Hasil Pencarian Transportasi</h2>
        <?php if (empty($search_results)): ?>
            <p class="text-gray-600 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md">
                Tidak ditemukan transportasi sesuai pencarian Anda.
            </p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-5">
                <?php foreach ($search_results as $item): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <?php if (!empty($item['image']) && file_exists(__DIR__ . '/uploads/' . $item['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                                class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">
                                <p>No Image Available</p>
                            </div>
                        <?php endif; ?>
                        <div class="p-4">
                            <span
                                class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full uppercase font-semibold"><?= htmlspecialchars($item['type']) ?></span>
                            <h3 class="text-xl font-semibold mt-2 text-gray-900"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="mt-1 text-gray-600 text-sm"><?= htmlspecialchars($item['origin']) ?> &rarr;
                                <?= htmlspecialchars($item['destination']) ?>
                            </p>
                            <p class="mt-1 text-gray-600 text-sm">
                                Berangkat:
                                <strong><?= htmlspecialchars(date("d M Y, H:i", strtotime($item['depart_date'] . ' ' . $item['depart_time']))) ?></strong>
                            </p>
                            <p class="mt-2 text-orange-500 font-bold text-lg">Rp
                                <?= number_format($item['price'], 0, ',', '.') ?>
                            </p>
                            <a href="pemesanan_pesawat.php?id=<?= $item['id'] ?>&type=<?= $item['type'] ?>"
                                class="mt-4 inline-block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <?php if (!$is_searching): ?>
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Tiket Pesawat Populer</h2>
        <?php if (empty($latest_pesawat)): ?>
            <p class="text-gray-600 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md">Belum ada tiket
                pesawat tersedia saat ini.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-5">
                <?php foreach ($latest_pesawat as $item): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <?php if (!empty($item['image']) && file_exists(__DIR__ . '/uploads/' . $item['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                                class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">
                                <p>No Image Available</p>
                            </div>
                        <?php endif; ?>
                        <div class="p-4">
                            <span
                                class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full uppercase font-semibold"><?= htmlspecialchars($item['type']) ?></span>
                            <h3 class="text-xl font-semibold mt-2 text-gray-900"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="mt-1 text-gray-600 text-sm"><?= htmlspecialchars($item['origin']) ?> &rarr;
                                <?= htmlspecialchars($item['destination']) ?>
                            </p>
                            <p class="mt-1 text-gray-600 text-sm">
                                Berangkat:
                                <strong><?= htmlspecialchars(date("d M Y, H:i", strtotime($item['depart_date'] . ' ' . $item['depart_time']))) ?></strong>
                            </p>
                            <p class="mt-2 text-orange-500 font-bold text-lg">Rp <?= number_format($item['price'], 0, ',', '.') ?>
                            </p>
                            <a href="pemesanan_pesawat.php?id=<?= $item['id'] ?>&type=<?= $item['type'] ?>"
                                class="mt-4 inline-block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!$is_searching): ?>
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Tiket Kapal Populer</h2>
        <?php if (empty($latest_kapal)): ?>
            <p class="text-gray-600 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md">Belum ada tiket
                kapal tersedia saat ini.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-5">
                <?php foreach ($latest_kapal as $item): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <?php if (!empty($item['image']) && file_exists(__DIR__ . '/uploads/' . $item['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                                class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">
                                <p>No Image Available</p>
                            </div>
                        <?php endif; ?>
                        <div class="p-4">
                            <span
                                class="text-xs bg-cyan-100 text-cyan-700 px-2 py-1 rounded-full uppercase font-semibold"><?= htmlspecialchars($item['type']) ?></span>
                            <h3 class="text-xl font-semibold mt-2 text-gray-900"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="mt-1 text-gray-600 text-sm"><?= htmlspecialchars($item['origin']) ?> &rarr;
                                <?= htmlspecialchars($item['destination']) ?>
                            </p>
                            <p class="mt-1 text-gray-600 text-sm">
                                Berangkat:
                                <strong><?= htmlspecialchars(date("d M Y, H:i", strtotime($item['depart_date'] . ' ' . $item['depart_time']))) ?></strong>
                            </p>
                            <p class="mt-2 text-orange-500 font-bold text-lg">Rp <?= number_format($item['price'], 0, ',', '.') ?>
                            </p>
                            <a href="pemesanan_kapal.php?id=<?= $item['id'] ?>&type=<?= $item['type'] ?>"
                                class="mt-4 inline-block w-full text-center bg-cyan-600 hover:bg-cyan-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!$is_searching): ?>
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Tiket Kereta Populer</h2>
        <?php if (empty($latest_kereta)): ?>
            <p class="text-gray-600 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md">Belum ada tiket
                kereta tersedia saat ini.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-5">
                <?php foreach ($latest_kereta as $item): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <?php if (!empty($item['image']) && file_exists(__DIR__ . '/uploads/' . $item['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                                class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">
                                <p>No Image Available</p>
                            </div>
                        <?php endif; ?>
                        <div class="p-4">
                            <span
                                class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full uppercase font-semibold"><?= htmlspecialchars($item['type']) ?></span>
                            <h3 class="text-xl font-semibold mt-2 text-gray-900"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="mt-1 text-gray-600 text-sm"><?= htmlspecialchars($item['origin']) ?> &rarr;
                                <?= htmlspecialchars($item['destination']) ?>
                            </p>
                            <p class="mt-1 text-gray-600 text-sm">
                                Berangkat:
                                <strong><?= htmlspecialchars(date("d M Y, H:i", strtotime($item['depart_date'] . ' ' . $item['depart_time']))) ?></strong>
                            </p>
                            <p class="mt-2 text-orange-500 font-bold text-lg">Rp <?= number_format($item['price'], 0, ',', '.') ?>
                            </p>
                            <a href="pemesanan_kereta.php?id=<?= $item['id'] ?>&type=<?= $item['type'] ?>"
                                class="mt-4 inline-block w-full text-center bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<div class="w-full px-4 sm:px-6 lg:px-8 box-border overflow-hidden mb-5">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Penginapan Pilihan</h2>
    <?php if (empty($latest_accommodation)): ?>
        <p class="text-gray-600 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md">Belum ada
            penginapan tersedia.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($latest_accommodation as $item): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <?php if (!empty($item['image']) && file_exists(__DIR__ . '/uploads/' . $item['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                            class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">
                            <p>No Image</p>
                        </div>
                    <?php endif; ?>
                    <div class="p-4">
                        <span
                            class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full uppercase font-semibold"><?= htmlspecialchars($item['type']) ?></span>
                        <h3 class="text-xl font-semibold mt-2 text-gray-900"><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="mt-1 text-gray-600 text-sm">Lokasi: <?= htmlspecialchars($item['destination']) ?></p>
                        <p class="mt-1 text-gray-600 text-sm">Tipe Kamar:
                            <?= htmlspecialchars($item['room_type'] ?? 'Standar') ?>
                        </p>
                        <p class="mt-2 text-orange-500 font-bold text-lg">Rp <?= number_format($item['price'], 0, ',', '.') ?>
                            <span class="text-sm font-normal text-gray-500">/malam</span>
                        </p>
                        <a href="pemesanan_penginapan.php?id=<?= $item['id'] ?>"
                            class="mt-4 inline-block w-full text-center bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-md transition-colors">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="w-full px-4 sm:px-6 lg:px-8 box-border overflow-hidden mb-5">


    <h2 class="text-2xl font-bold mb-4 text-gray-800">Sewa Kendaraan</h2>
    <?php if (empty($latest_rentals)): ?>
        <p class="text-gray-600 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md">Belum ada
            kendaraan untuk disewa.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($latest_rentals as $item): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <?php if (!empty($item['image']) && file_exists(__DIR__ . '/uploads/' . $item['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                            class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">
                            <p>No Image</p>
                        </div>
                    <?php endif; ?>
                    <div class="p-4">
                        <span
                            class="text-xs bg-teal-100 text-teal-700 px-2 py-1 rounded-full uppercase font-semibold"><?= htmlspecialchars($item['type']) ?></span>
                        <h3 class="text-xl font-semibold mt-2 text-gray-900">
                            <?= htmlspecialchars($item['vehicle_brand'] . ' ' . $item['vehicle_model'] . ' (' . $item['vehicle_year'] . ')') ?>
                        </h3>
                        <p class="mt-1 text-gray-600 text-sm">Nama Rental: <?= htmlspecialchars($item['name']) ?></p>
                        <p class="mt-1 text-gray-600 text-sm">Lokasi: <?= htmlspecialchars($item['origin']) ?></p>
                        <p class="mt-1 text-gray-600 text-sm"><?= $item['with_driver'] ? 'Dengan Supir' : 'Lepas Kunci' ?></p>
                        <p class="mt-2 text-orange-500 font-bold text-lg">Rp <?= number_format($item['price'], 0, ',', '.') ?>
                            <span class="text-sm font-normal text-gray-500">/hari</span>
                        </p>
                        <a href="pemesanan_kendaraan.php?id=<?= $item['id'] ?>"
                            class="mt-4 inline-block w-full text-center bg-teal-500 hover:bg-teal-600 text-white font-medium py-2 px-4 rounded-md transition-colors">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>