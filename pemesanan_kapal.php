<?php
// pemesanan_Kapal.php
include 'config.php';
include 'header.php';  // Pastikan session_start() hanya sekali di header.php

// 1) Pastikan user ter‐login dan memiliki role 'user'
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// 2) Ambil ticket_id (dari GET di step awal atau POST di step selanjutnya)
if (isset($_GET['id'])) {
    $ticket_id = intval($_GET['id']);
} elseif (isset($_POST['ticket_id'])) {
    $ticket_id = intval($_POST['ticket_id']);
} else {
    header('Location: index.php');
    exit;
}

// 3) Ambil detail tiket Kapal dari DB
$stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ? AND type = "kapal"');
$stmt->execute([$ticket_id]);
$flight = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$flight) {
    echo '<p class="text-red-500 text-center mt-10">Tiket Kapal tidak ditemukan.</p>';
    include 'footer.php';
    exit;
}

// Inisialisasi variabel umum
$step           = 1;            // Default masuk step 1
$quantity       = 1;            // Nilai default quantity
$selectedSeats  = [];           // Menyimpan kursi yang dipilih
$customerData   = [];           // Menyimpan data diri setiap penumpang
$errors         = [];           // Array menampung pesan error
$user_id        = $_SESSION['user']['id']; // User ID dari session
$orderIds       = [];           // Menyimpan order IDs jika booking berhasil
$successMessage = '';

// 4) Proses ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // a) Baca nilai step dari POST
    $posted_step = isset($_POST['step']) ? intval($_POST['step']) : 1;

    // -------------- STEP 1: INPUT JUMLAH TIKET --------------
    if ($posted_step === 1) {
        // Ambil quantity dari POST
        $quantity = intval($_POST['quantity'] ?? 1);

        // Hitung sisa kursi yang benar-benar tersedia
        $stmtAvail = $pdo->prepare('SELECT COUNT(*) FROM seats WHERE ticket_id = ? AND is_booked = 0');
        $stmtAvail->execute([$ticket_id]);
        $availableSeats = intval($stmtAvail->fetchColumn());

        // Validasi jika tersedia = 0, langsung tampilkan error
        if ($availableSeats < 1) {
            $errors[] = 'Maaf, tiket Kapal ini sudah habis terjual.';
            $step = 1;
        }
        // Jika quantity invalid (kurang dari 1 atau melebihi available)
        elseif ($quantity < 1) {
            $errors[] = 'Jumlah tiket minimal 1.';
            $step = 1;
        } elseif ($quantity > $availableSeats) {
            $errors[] = "Jumlah tiket yang diminta melebihi kursi yang tersedia ({$availableSeats}).";
            $step = 1;
        } else {
            $step = 2; // Lanjut ke pemilihan kursi
        }
    }

    // -------------- STEP 2: PILIH KURSI --------------
    elseif ($posted_step === 2) {
        // Ambil quantity & selected_seats dari POST
        $quantity = intval($_POST['quantity'] ?? 1);
        $selectedSeats = $_POST['selected_seats'] ?? [];

        // Validasi jumlah array selected_seats
        if (!is_array($selectedSeats) || count($selectedSeats) !== $quantity) {
            $errors[] = "Anda harus memilih tepat {$quantity} kursi.";
            $step = 2;
        } else {
            // Cek satu per satu apakah kursi masih tersedia
            $stmtCheck = $pdo->prepare('SELECT seat_number FROM seats WHERE ticket_id = ? AND seat_number = ? AND is_booked = 0');
            foreach ($selectedSeats as $sn) {
                $sn = trim($sn);
                $stmtCheck->execute([$ticket_id, $sn]);
                if (!$stmtCheck->fetch()) {
                    $errors[] = "Kursi " . htmlspecialchars($sn) . " sudah tidak tersedia.";
                    break;
                }
            }
            if (empty($errors)) {
                $step = 3; // Lanjut ke pengisian data diri
            } else {
                $step = 2;
            }
        }
    }

    // -------------- STEP 3: ISI DATA DIRI & BAYAR --------------
    elseif ($posted_step === 3) {
        // Ambil quantity & selectedSeats dari POST
        $quantity = intval($_POST['quantity'] ?? 1);
        $selectedSeats = $_POST['selected_seats'] ?? [];

        // Validasi data diri per kursi
        for ($i = 0; $i < $quantity; $i++) {
            $customerData[$i] = [
                'name'    => trim($_POST['customer_name'][$i] ?? ''),
                'phone'   => trim($_POST['customer_phone'][$i] ?? ''),
                'email'   => trim($_POST['customer_email'][$i] ?? ''),
                'address' => trim($_POST['customer_address'][$i] ?? ''),
            ];
            if (
                empty($customerData[$i]['name']) ||
                empty($customerData[$i]['phone']) ||
                empty($customerData[$i]['email']) ||
                empty($customerData[$i]['address'])
            ) {
                $errors[] = "Semua data diri untuk kursi #" . htmlspecialchars($selectedSeats[$i]) . " harus diisi.";
            }
            if (!filter_var($customerData[$i]['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format email untuk kursi #" . htmlspecialchars($selectedSeats[$i]) . " tidak valid.";
            }
        }

        // Jika tidak ada error validasi, lanjut ke cek saldo dan insert ke DB
        if (empty($errors)) {
            $total_price = (float)$flight['price'] * $quantity;

            // Ambil saldo terkini dari DB
            $stmtBalance = $pdo->prepare('SELECT balance FROM users WHERE id = ?');
            $stmtBalance->execute([$user_id]);
            $currentBalance = (float)$stmtBalance->fetchColumn();

            if ($currentBalance < $total_price) {
                $errors[] = "Saldo tidak mencukupi. Saldo Anda: Rp " . number_format($currentBalance, 2, ',', '.') .
                            ". Total pembelian: Rp " . number_format($total_price, 2, ',', '.');
                $step = 3;
            } else {
                // Mulai transaksi
                try {
                    $pdo->beginTransaction();

                    // 1) Buat order satu per satu untuk setiap kursi
                    foreach ($selectedSeats as $idx => $seatNumber) {
                        $seatNumber = trim($seatNumber);
                        $cust = $customerData[$idx];

                        // Insert ke tabel orders
                        $stmtOrder = $pdo->prepare('
                            INSERT INTO orders
                              (user_id, ticket_id, quantity, seat_number, customer_name, customer_phone, customer_email, customer_address, total_price_at_purchase)
                            VALUES (?, ?, 1, ?, ?, ?, ?, ?, ?)
                        ');
                        $stmtOrder->execute([
                            $user_id,
                            $ticket_id,
                            $seatNumber,
                            $cust['name'],
                            $cust['phone'],
                            $cust['email'],
                            $cust['address'],
                            (float)$flight['price']
                        ]);
                        $newOrderId = $pdo->lastInsertId();
                        $orderIds[] = $newOrderId;

                        // Update status kursi: is_booked = 1 dan assign order_id
                        $stmtUpd = $pdo->prepare('
                            UPDATE seats
                            SET is_booked = 1, order_id = ?
                            WHERE ticket_id = ? AND seat_number = ? AND is_booked = 0
                        ');
                        $stmtUpd->execute([$newOrderId, $ticket_id, $seatNumber]);
                        if ($stmtUpd->rowCount() === 0) {
                            throw new Exception("Kursi " . htmlspecialchars($seatNumber) . " telah dipesan oleh orang lain.");
                        }
                    }

                    // 2) Kurangi saldo user
                    $newBalance = $currentBalance - $total_price;
                    $stmtUpdBal = $pdo->prepare('UPDATE users SET balance = ? WHERE id = ?');
                    $stmtUpdBal->execute([$newBalance, $user_id]);

                    // 3) Catat transaksi di balance_transactions
                    $refOrderId = $orderIds[0] ?? null;
                    $desc = "Pembelian {$quantity} tiket Kapal ({$flight['name']}). Kursi: " .
                            implode(', ', array_map('htmlspecialchars', $selectedSeats));
                    $stmtLog = $pdo->prepare('
                        INSERT INTO balance_transactions
                          (user_id, transaction_type, amount, related_order_id, description)
                        VALUES (?, "purchase", ?, ?, ?)
                    ');
                    $stmtLog->execute([$user_id, $total_price, $refOrderId, $desc]);

                    $pdo->commit();

                    // Update saldo di session
                    $_SESSION['user']['balance'] = $newBalance;

                    $step = 4; // Masuk step sukses
                    $successMessage = "Pemesanan {$quantity} tiket berhasil! Saldo Anda telah dikurangi sebesar Rp " .
                                       number_format($total_price, 2, ',', '.');
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $errors[] = "Gagal memproses pemesanan: " . $e->getMessage();
                    // Jika error akibat kursi sudah ter‐book, kembali ke step 2
                    if (strpos($e->getMessage(), 'Kursi') !== false) {
                        $step = 2;
                    } else {
                        $step = 3;
                    }
                }
            }
        } else {
            $step = 3;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pemesanan Tiket kapal</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.2.7/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="container mx-auto py-10">
  <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-xl">
    <h2 class="text-3xl font-semibold mb-6 text-gray-800 border-b pb-4">Pemesanan Tiket Kapal</h2>

    <!-- Tampilkan error jika ada -->
    <?php if (!empty($errors)): ?>
      <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
        <p class="font-bold">Oops! Terjadi kesalahan:</p>
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- -------------- STEP 1: TAMPILAN PILIH QUANTITY -------------- -->
    <?php if ($step === 1): ?>
      <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <h3 class="text-xl font-medium text-blue-800 mb-2"><?= htmlspecialchars($flight['name']) ?></h3>
        <p class="text-gray-700"><strong>Rute:</strong> <?= htmlspecialchars($flight['origin']) ?> &rarr; <?= htmlspecialchars($flight['destination']) ?></p>
        <p class="text-gray-700"><strong>Tanggal:</strong> <?= date("d F Y", strtotime($flight['depart_date'])) ?></p>
        <p class="text-gray-700"><strong>Harga per kursi:</strong> <span class="font-semibold text-orange-600">Rp <?= number_format($flight['price'], 0, ',', '.') ?></span></p>
        <?php
          $stmtAvailInfo = $pdo->prepare('SELECT COUNT(*) FROM seats WHERE ticket_id = ? AND is_booked = 0');
          $stmtAvailInfo->execute([$ticket_id]);
          $availableSeatsInfo = intval($stmtAvailInfo->fetchColumn());
        ?>
        <p class="text-gray-700"><strong>Kursi Tersedia:</strong> <?= $availableSeatsInfo ?></p>
      </div>

      <!-- Jika tidak ada kursi tersisa, tampil pesan dan tidak munculkan form -->
      <?php if ($availableSeatsInfo < 1): ?>
        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md" role="alert">
          <p>Tiket Kapal ini sudah habis terjual. Silakan kembali ke <a href="index.php" class="text-blue-600 hover:underline">Beranda</a>.</p>
        </div>
      <?php else: ?>
        <form method="POST" action="pemesanan_kapal.php?id=<?= $ticket_id ?>">
          <input type="hidden" name="step" value="1">
          <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
          <div class="mb-6">
            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Tiket yang Ingin Dibeli</label>
            <input
              type="number"
              id="quantity"
              name="quantity"
              min="1"
              max="<?= $availableSeatsInfo ?>"
              required
              value="<?= htmlspecialchars($quantity) ?>"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
            >
          </div>
          <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-md shadow transition duration-150">
            Lanjut ke Pilih Kursi
          </button>
        </form>
      <?php endif; ?>
    <?php endif; ?>

    <!-- -------------- STEP 2: PILIH KURSI -------------- -->
    <?php if ($step === 2): ?>
      <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
        <p class="text-gray-700"><strong>Kapal:</strong> <?= htmlspecialchars($flight['name']) ?></p>
        <p class="text-gray-700"><strong>Rute:</strong> <?= htmlspecialchars($flight['origin']) ?> &rarr; <?= htmlspecialchars($flight['destination']) ?></p>
        <p class="text-gray-700"><strong>Pilih <span class="font-bold text-blue-600"><?= $quantity ?></span> kursi:</strong></p>
      </div>

      <form method="POST" action="pemesanan_kapal.php?id=<?= $ticket_id ?>">
        <input type="hidden" name="step" value="2">
        <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
        <input type="hidden" name="quantity" value="<?= $quantity ?>">

        <div class="mb-6">
          <p class="text-sm text-gray-600 mb-2">
            Klik pada kursi berwarna hijau untuk memilih. 
            Kursi berwarna <span class="font-semibold text-yellow-500">kuning</span> menandakan sudah dipilih Anda.
          </p>
          <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 border p-4 rounded-md bg-gray-50">
            <?php
              $stmtSeats = $pdo->prepare('SELECT seat_number, is_booked FROM seats WHERE ticket_id = ? ORDER BY CAST(seat_number AS UNSIGNED) ASC');
              $stmtSeats->execute([$ticket_id]);
              $allSeats = $stmtSeats->fetchAll(PDO::FETCH_ASSOC);
              foreach ($allSeats as $seat) {
                $no = htmlspecialchars($seat['seat_number']);
                $isBooked = (bool)$seat['is_booked'];
                $isSelected = in_array($no, $selectedSeats);

                if ($isBooked) {
                  echo "<div class=\"p-2 bg-red-300 text-red-700 rounded text-center text-sm cursor-not-allowed line-through\" title=\"Booked\">{$no}</div>";
                } else {
                  $btnClass = $isSelected 
                              ? 'bg-yellow-400 hover:bg-yellow-500' 
                              : 'bg-green-500 hover:bg-green-600';
                  echo "<button type=\"button\"
                                data-seat=\"{$no}\" 
                                class=\"seat-btn p-2 text-white rounded text-sm shadow focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-colors {$btnClass}\">"
                             . $no .
                       "</button>";
                }
              }
            ?>
          </div>
        </div>

        <!-- Hidden inputs untuk menyimpan kursi terpilih -->
        <?php for ($i = 0; $i < $quantity; $i++): ?>
          <input 
            type="hidden" 
            name="selected_seats[]" 
            class="selected-seat-input" 
            value="<?= htmlspecialchars($selectedSeats[$i] ?? '') ?>"
          >
        <?php endfor; ?>

        <div class="mt-8">
          <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-md shadow transition duration-150">
            Konfirmasi Kursi & Isi Data Diri
          </button>
        </div>
      </form>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
          const seatButtons = document.querySelectorAll('.seat-btn');
          const hiddenInputs = document.querySelectorAll('.selected-seat-input');
          const maxSelect = <?= $quantity ?>;
          let chosenSeats = [];

          // Isi chosenSeats dengan nilai dari hiddenInputs (jika berasal dari POST sebelumnya)
          hiddenInputs.forEach(function(inp) {
            if (inp.value) chosenSeats.push(inp.value);
          });
          function updateHidden() {
            hiddenInputs.forEach((inp, idx) => {
              inp.value = chosenSeats[idx] || '';
            });
          }
          updateHidden();

          seatButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
              const seatNo = this.getAttribute('data-seat');
              const already = chosenSeats.indexOf(seatNo);

              if (already !== -1) {
                // Batalkan pilihan
                chosenSeats.splice(already, 1);
                this.classList.remove('bg-yellow-400', 'hover:bg-yellow-500');
                this.classList.add('bg-green-500', 'hover:bg-green-600');
              } else {
                if (chosenSeats.length < maxSelect) {
                  chosenSeats.push(seatNo);
                  this.classList.remove('bg-green-500', 'hover:bg-green-600');
                  this.classList.add('bg-yellow-400', 'hover:bg-yellow-500');
                } else {
                  alert('Anda hanya dapat memilih ' + maxSelect + ' kursi.');
                }
              }
              updateHidden();
            });
          });
        });
      </script>
    <?php endif; ?>

    <!-- -------------- STEP 3: ISI DATA DIRI & BAYAR -------------- -->
    <?php if ($step === 3): ?>
      <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
        <p class="text-gray-700"><strong>Kapal:</strong> <?= htmlspecialchars($flight['name']) ?></p>
        <p class="text-gray-700"><strong>Kursi Terpilih (<?= $quantity ?>):</strong> 
          <span class="font-semibold text-blue-600"><?= implode(', ', array_map('htmlspecialchars', $selectedSeats)) ?></span>
        </p>
        <p class="text-gray-700">
          <strong>Total Harga:</strong> 
          <span class="font-bold text-xl text-orange-600">Rp <?= number_format($flight['price'] * $quantity, 0, ',', '.') ?></span>
        </p>
      </div>

      <form method="POST" action="pemesanan_kapal.php?id=<?= $ticket_id ?>">
        <input type="hidden" name="step" value="3">
        <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
        <input type="hidden" name="quantity" value="<?= $quantity ?>">

        <?php foreach ($selectedSeats as $sn): ?>
          <input type="hidden" name="selected_seats[]" value="<?= htmlspecialchars($sn) ?>">
        <?php endforeach; ?>

        <?php for ($i = 0; $i < $quantity; $i++): ?>
          <fieldset class="mb-8 p-4 border border-gray-300 rounded-lg shadow">
            <legend class="text-lg font-semibold text-gray-700 px-2">Data Penumpang untuk Kursi <?= htmlspecialchars($selectedSeats[$i]) ?></legend>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mt-2">
              <div>
                <label for="customer_name_<?= $i ?>" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input 
                  type="text" 
                  id="customer_name_<?= $i ?>" 
                  name="customer_name[<?= $i ?>]" 
                  required
                  value="<?= htmlspecialchars($customerData[$i]['name'] ?? '') ?>"
                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
              </div>
              <div>
                <label for="customer_phone_<?= $i ?>" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                <input 
                  type="tel" 
                  id="customer_phone_<?= $i ?>" 
                  name="customer_phone[<?= $i ?>]" 
                  required
                  pattern="[0-9\s\-\+]+"
                  title="Hanya angka, spasi, tanda hubung, atau plus"
                  value="<?= htmlspecialchars($customerData[$i]['phone'] ?? '') ?>"
                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
              </div>
              <div class="md:col-span-2">
                <label for="customer_email_<?= $i ?>" class="block text-sm font-medium text-gray-700">Email</label>
                <input 
                  type="email" 
                  id="customer_email_<?= $i ?>" 
                  name="customer_email[<?= $i ?>]" 
                  required
                  value="<?= htmlspecialchars($customerData[$i]['email'] ?? '') ?>"
                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
              </div>
              <div class="md:col-span-2">
                <label for="customer_address_<?= $i ?>" class="block text-sm font-medium text-gray-700">Alamat Lengkap</label>
                <textarea 
                  id="customer_address_<?= $i ?>" 
                  name="customer_address[<?= $i ?>]" 
                  required 
                  rows="2"
                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                ><?= htmlspecialchars($customerData[$i]['address'] ?? '') ?></textarea>
              </div>
            </div>
          </fieldset>
        <?php endfor; ?>

        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-md shadow transition duration-150">
          Bayar dengan Saldo & Selesaikan Pemesanan
        </button>
      </form>
    <?php endif; ?>

    <!-- -------------- STEP 4: HALAMAN SUKSES -------------- -->
    <?php if ($step === 4): ?>
      <div class="text-center py-8">
        <svg class="mx-auto h-16 w-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="mt-4 text-2xl font-semibold text-green-700">Pemesanan Berhasil!</h3>
        <p class="mt-2 text-gray-600"><?= htmlspecialchars($successMessage) ?></p>
      </div>

      <div class="bg-gray-50 p-6 rounded-lg shadow-inner">
        <h4 class="text-xl font-semibold text-gray-800 mb-4">Detail Pemesanan:</h4>
        <div class="space-y-4">
          <?php
            if (!empty($orderIds)) {
                $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
                $stmtOrd = $pdo->prepare("
                  SELECT o.*, t.name AS ticket_name, t.origin, t.destination, t.depart_date
                  FROM orders o
                  JOIN tickets t ON o.ticket_id = t.id
                  WHERE o.id IN ($placeholders)
                ");
                $stmtOrd->execute($orderIds);
                $orderedItems = $stmtOrd->fetchAll(PDO::FETCH_ASSOC);

                foreach ($orderedItems as $o):
          ?>
            <div class="p-4 border border-gray-200 rounded-md bg-white">
              <p class="text-sm text-gray-500">Order ID: #<?= htmlspecialchars($o['id']) ?></p>
              <p class="font-semibold text-lg text-blue-700"><?= htmlspecialchars($o['ticket_name']) ?></p>
              <p class="text-gray-600">Kursi: <span class="font-medium"><?= htmlspecialchars($o['seat_number']) ?></span></p>
              <p class="text-gray-600">Rute: <?= htmlspecialchars($o['origin']) ?> &rarr; <?= htmlspecialchars($o['destination']) ?></p>
              <p class="text-gray-600">Tanggal: <?= date("d F Y", strtotime($o['depart_date'])) ?></p>
              <hr class="my-2">
              <p class="text-gray-600">Pemesan: <span class="font-medium"><?= htmlspecialchars($o['customer_name']) ?></span></p>
              <p class="text-gray-600">Telepon: <?= htmlspecialchars($o['customer_phone']) ?></p>
              <p class="text-gray-600">Email: <?= htmlspecialchars($o['customer_email']) ?></p>
              <p class="mt-2 text-orange-600 font-bold text-lg">
                Harga Tiket: Rp <?= number_format($o['total_price_at_purchase'], 0, ',', '.') ?>
              </p>
            </div>
          <?php endforeach;
            } else {
                echo "<p class='text-gray-600'>Tidak dapat menampilkan detail order saat ini.</p>";
            }
          ?>
        </div>

        <p class="mt-6 text-xl font-semibold text-gray-800 text-right">
          Total Bayar: <span class="text-green-600">Rp <?= number_format($flight['price'] * $quantity, 0, ',', '.') ?></span>
        </p>
      </div>

      <div class="mt-8 flex justify-between">
        <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-md shadow transition duration-150">
          Kembali ke Beranda
        </a>
        <a href="my_tickets.php" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-md shadow transition duration-150">
          Lihat Tiket Saya
        </a>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'footer.php'; ?>
