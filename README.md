# Ticketing

Sistem pemesanan tiket online untuk **pesawat**, **kereta**, **kapal**, **penginapan**, dan **kendaraan**.  

> **Catatan:** Sebelum melakukan transaksi, **wajib melakukan registrasi dan login terlebih dahulu**.

---

## 🌐 Alamat Domain Project
Project ini sudah di-deploy dan dapat diakses secara publik di:  
**[https://tiketkuuu.free.nf](https://tiketkuuu.free.nf)**  
*(Atau gunakan link yang tersedia di dokumen proyek)*

---

## ⚙️ Cara Pemasangan (Instalasi Lokal / Pindah Server)

Jika Anda hendak menjalankan project ini di komputer sendiri atau di server lain, ikuti langkah-langkah berikut:

### 1. Salin Source Code
- **Clone** repository (jika tersedia di GitHub/GitLab), atau  
- **Unduh** seluruh file **PHP**, **CSS (Tailwind)**, dan folder **assets**, lalu letakkan di folder root web server:  
  - Untuk **XAMPP**: `htdocs/ticketing`  
  - Untuk **LAMP**: `www/ticketing`

### 2. Siapkan Database
- Buat database baru di **MySQL/MariaDB**:  
  ```sql
  CREATE DATABASE ticketing;

### 3. Konfigurasi Koneksi
- Buka file `config.php` dan sesuaikan parameter koneksi database:

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'ticketing'; // sesuaikan dengan nama database
``` 


### 4. Jalankan Aplikasi
- Lokal: buka di browser `http://localhost/(nama folder)/login.php`
- Server: arahkan domain/subdomain Anda ke folder project.
- Registrasi akun baru, kemudian login untuk mulai memesan tiket.

<br><br>
**Ticketing API Documentation**
Dokumentasi API untuk backend server aplikasi Ticketing (pemesanan tiket pesawat, kereta, kapal, penginapan, dan kendaraan).

---

## 1. Account Management

### Signup / Register

* **Endpoint**: `POST /api/auth/signup`
* **Description**: Mendaftar akun pengguna baru.
* **Body Parameters**:

  * `name` (string)
  * `password` (string)
* **Returns**: Objek User

### Login

* **Endpoint**: `POST /api/auth/login`
* **Description**: Autentikasi pengguna dan menghasilkan access token.
* **Body Parameters**:

  * `name` (string)
  * `password` (string)
* **Returns**: Objek User dan `accessToken`

### Logout

* **Endpoint**: `POST /api/auth/logout`
* **Description**: Menghapus/menonaktifkan session atau token.
* **Headers**:

  * `Authorization: Bearer <accessToken>`
* **Returns**: Pesan sukses

### Reset Password

* **Endpoint**: `POST /api/auth/reset-password`
* **Description**: Mengirim email reset password.
* **Body Parameters**:

  * `email` (string)
* **Returns**: Pesan sukses

### Update Password

* **Endpoint**: `POST /api/auth/reset-password/:token`
* **Description**: Mengubah password menggunakan token.
* **URL Parameters**:

  * `token` (string)
* **Body Parameters**:

  * `password` (string)
* **Returns**: Pesan sukses

---

## 2. Search & Filter

### Search Tickets

* **Endpoint**: `GET /api/tickets/search`
* **Description**: Mencari tiket berdasarkan kata kunci (destinasi, kategori, nama maskapai/hotel).
* **Query Parameters**:

  * `q` (string) – Kata kunci pencarian.
* **Returns**: Array tiket relevan

### Filter & Sort

* **Endpoint**: `GET /api/tickets`
* **Description**: Mendapatkan daftar tiket dengan filter dan sorting.
* **Query Parameters**:

  * `price_min` (number)
  * `price_max` (number)
  * `tags[]` (array string)
  * `facilities[]` (array string)
  * `sort` (string: `price`, `date`, `popularity`)
  * `page` (number)
  * `per_page` (number)
* **Returns**: Objek paginated dengan field `data`, `meta`

---

## 3. Ticket Catalog & Management

### Get All Tickets

* **Endpoint**: `GET /api/tickets`
* **Description**: Mendapatkan daftar semua tiket (CRUD untuk user publik).
* **Query Parameters**: (lihat Filter & Sort)
* **Returns**: Array tiket

### Get Ticket by ID

* **Endpoint**: `GET /api/tickets/:id`
* **Description**: Mendapatkan detail tiket berdasarkan ID.
* **URL Parameters**:

  * `id` (string)
* **Returns**: Objek tiket

### Admin: Create Ticket

* **Endpoint**: `POST /api/admin/tickets`
* **Description**: Menambah data tiket baru.
* **Headers**: `Authorization: Bearer <adminToken>`
* **Body Parameters**: Detail tiket (title, price, date, category, etc.)
* **Returns**: Objek tiket baru

### Admin: Update Ticket

* **Endpoint**: `PUT /api/admin/tickets/:id`
* **Description**: Mengubah data tiket.
* **URL Parameters**: `id` (string)
* **Headers**: `Authorization: Bearer <adminToken>`
* **Body Parameters**: Field yang di-update
* **Returns**: Objek tiket updated

### Admin: Delete Ticket

* **Endpoint**: `DELETE /api/admin/tickets/:id`
* **Description**: Menghapus tiket.
* **URL Parameters**: `id` (string)
* **Headers**: `Authorization: Bearer <adminToken>`
* **Returns**: Pesan sukses

---

## 4. Seat Selection & Booking

### Get Seats

* **Endpoint**: `GET /api/tickets/:ticketId/seats`
* **Description**: Mendapatkan status kursi untuk tiket tertentu.
* **URL Parameters**:

  * `ticketId` (string)
* **Returns**: Array objek kursi

### Lock Seats (Preview)

* **Endpoint**: `POST /api/tickets/:ticketId/seats/lock`
* **Description**: Mengunci kursi sebelum pembayaran.
* **URL Parameters**:

  * `ticketId` (string)
* **Body Parameters**:

  * `seat_ids[]` (array string)
* **Returns**: Objek lock confirmation

### Create Order

* **Endpoint**: `POST /api/orders`
* **Description**: Membuat booking dan commit lock.
* **Body Parameters**:

  * `ticket_id` (string)
  * `seat_ids[]` (array string)
  * `passengers[]` (array objek)
* **Returns**: Objek order

---

## 5. Payment, Refund & Order History

### Top-up Balance

* **Endpoint**: `POST /api/payments/topup`
* **Description**: Inisiasi top-up saldo via payment gateway.
* **Body Parameters**:

  * `amount` (number)
* **Returns**: `paymentUrl` atau `paymentToken`

### Cancel Order & Refund

* **Endpoint**: `POST /api/orders/:orderId/cancel`
* **Description**: Membatalkan order dan memproses refund otomatis.
* **URL Parameters**:

  * `orderId` (string)
* **Returns**: Objek refund

### Complaint

* **Endpoint**: `POST /api/complaints`
* **Description**: Mengajukan keluhan terkait order.
* **Body Parameters**:

  * `order_id` (string)
  * `message` (string)
* **Returns**: Objek complaint

### Order History

* **Endpoint**: `GET /api/history`
* **Description**: Mendapatkan riwayat transaksi (booking, top-up, refund).
* **Query Parameters**:

  * `type` (string: `booking`, `topup`, `refund`)
  * `page` (number)
  * `per_page` (number)
* **Returns**: Paginated list

---

## 6. Reviews & Rating

### Create Review

* **Endpoint**: `POST /api/tickets/:ticketId/reviews`
* **Description**: Memberi ulasan dan rating setelah completed.
* **URL Parameters**:

  * `ticketId` (string)
* **Body Parameters**:

  * `rating` (integer 1–5)
  * `comment` (string)
* **Returns**: Objek review

### Get Reviews

* **Endpoint**: `GET /api/tickets/:ticketId/reviews`
* **Description**: Mendapatkan semua ulasan untuk tiket tertentu.
* **URL Parameters**:

  * `ticketId` (string)
* **Returns**: Array review

### Get Average Rating

* **Endpoint**: `GET /api/tickets/:ticketId/reviews/average`
* **Description**: Mendapatkan rata-rata rating.
* **URL Parameters**:

  * `ticketId` (string)
* **Returns**: Objek `{ averageRating: number }`

---

## 7. Chat / Communication

### Send Message

* **Endpoint**: `POST /api/chats`
* **Description**: Mengirim pesan antara user dan admin.
* **Body Parameters**:

  * `receiver_id` (string)
  * `message` (string)
* **Returns**: Objek message

### Get Chat History

* **Endpoint**: `GET /api/chats/:userId`
* **Description**: Mendapatkan riwayat pesan dengan user tertentu.
* **URL Parameters**:

  * `userId` (string)
* **Returns**: Array message

---

## Error Handling

* **Invalid Route**

  * **Endpoint**: `*`
  * **Returns**: `{ error: "Route not found" }`

## Serve Frontend

* **Endpoint**: `/*`
* **Description**: Menyajikan file `index.html` frontend

---

**Notes**

* Semua endpoint yang memerlukan autentikasi harus menambahkan header `Authorization: Bearer <token>`.
* Gunakan HTTP status codes yang sesuai (200, 201, 400, 401, 404, 500).
* Dokumentasi ini dapat diperbarui seiring penambahan fitur baru.

---

## Database Schema

Berikut struktur tabel utama dalam database `ticketing`:

### users

* `id` INT PK, auto-increment
* `username` VARCHAR, unique
* `password` VARCHAR
* `role` ENUM('admin','user')
* `created_at` TIMESTAMP
* `balance` DECIMAL(10,2)

### tickets

* `id` INT PK, auto-increment
* `type` ENUM('pesawat','kapal','kereta','penginapan','kendaraan')
* `name`, `origin`, `destination`, `address`
* Tanggal/waktu: `depart_date`, `depart_time`, `check_in_date`, `check_out_date`, `rental_start_date`, `rental_end_date`, `return_date`
* `price` DECIMAL
* Fitur tambahan: `with_driver`, `num_guests`, `room_type`, `vehicle_brand`, `vehicle_model`, `vehicle_year`
* `image` VARCHAR
* `total_seats` INT
* `created_at` TIMESTAMP

### seats

* `id` INT PK, auto-increment
* `ticket_id` FK → tickets(id)
* `seat_number` VARCHAR
* `is_booked` TINYINT
* `order_id` FK → orders(id)

### orders

* `id` INT PK, auto-increment
* `user_id` FK → users(id)
* `ticket_id` FK → tickets(id)
* `quantity`, `seat_number`, `customer_name`, `customer_phone`, `customer_email`, `customer_address`
* `total_price_at_purchase` DECIMAL
* Status: `order_status` ENUM('booked','cancelled','completed'), `status` ENUM('pending','confirmed','cancelled')
* `order_date`, `cancelled_at` TIMESTAMP

### balance\_transactions

* `id` INT PK, auto-increment
* `user_id` FK → users(id)
* `transaction_type` ENUM('topup','purchase','refund','adjustment')
* `amount` DECIMAL
* `related_order_id` FK → orders(id)
* `description` TEXT
* `transaction_date` TIMESTAMP

### pengaduan

* `id` INT PK, auto-increment
* `nama_lengkap`, `kontak`, `tanggal_pemesanan`, `jenis_tiket`, `nama_penumpang`, `jenis_pengaduan`, `deskripsi`, `harapan`
* `submitted_at` TIMESTAMP

*Catatan: Semua constraint FK diimplementasikan dengan cascade dan set-null sesuai kebutuhan.*
