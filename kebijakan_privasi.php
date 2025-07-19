<?php
// kebijakan_privasi.php
include 'header.php';
?>

<div class="bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-xl p-8 md:p-10">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2 text-center">Kebijakan Privasi</h1>
            <p class="text-center text-sm text-gray-500 mb-8">Tanggal Efektif: 8 Juli 2025</p>

            <div class="prose max-w-none text-gray-700">
                <p>Selamat datang di Ticketing! Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, melindungi, dan membagikan informasi pribadi Anda saat Anda menggunakan layanan kami. Dengan mendaftar dan menggunakan platform kami, Anda menyetujui praktik yang dijelaskan dalam kebijakan ini.</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">1. Informasi yang Kami Kumpulkan</h2>
                <p>Kami mengumpulkan beberapa jenis informasi untuk menyediakan dan meningkatkan layanan kami kepada Anda:</p>
                <ul>
                    <li><strong>Informasi Pendaftaran Akun:</strong> Saat Anda membuat akun, kami mengumpulkan Username, Password (disimpan dalam format terenkripsi/hash), dan Peran yang Anda pilih ('user' atau 'admin').</li>
                    <li><strong>Informasi Pemesanan:</strong> Saat Anda melakukan pemesanan tiket transportasi, kami mengumpulkan data untuk setiap penumpang, yang meliputi Nama Lengkap, Nomor Telepon, Alamat Email, dan Alamat Lengkap.</li>
                    <li><strong>Informasi Transaksi Keuangan:</strong> Kami mencatat semua aktivitas yang terkait dengan Saldo akun Anda, termasuk jumlah top-up, detail pembelian, dan detail pengembalian dana (refund).</li>
                    <li><strong>Informasi Pengaduan:</strong> Jika Anda mengirimkan pengaduan, kami mengumpulkan informasi yang Anda berikan dalam formulir, seperti nama, kontak, detail pemesanan, serta deskripsi pengaduan.</li>
                </ul>

                <h2 class="text-2xl font-semibold mt-8 mb-4">2. Bagaimana Kami Menggunakan Informasi Anda</h2>
                <p>Informasi yang kami kumpulkan digunakan untuk tujuan berikut:</p>
                <ul>
                    <li><strong>Penyediaan Layanan:</strong> Untuk membuat dan mengelola akun Anda, memproses transaksi pemesanan tiket, dan menampilkan tiket yang telah Anda beli.</li>
                    <li><strong>Komunikasi:</strong> Untuk mengirimkan konfirmasi atau informasi penting terkait pemesanan Anda.</li>
                    <li><strong>Manajemen Keuangan:</strong> Untuk mengelola Saldo akun Anda, memproses pembayaran, menangani refund, dan mencatat riwayat top-up.</li>
                    <li><strong>Layanan Pelanggan:</strong> Untuk menanggapi dan mengelola pengaduan atau pertanyaan Anda.</li>
                    <li><strong>Keamanan:</strong> Untuk mengautentikasi login Anda dan melindungi platform dari penyalahgunaan.</li>
                </ul>

                <h2 class="text-2xl font-semibold mt-8 mb-4">3. Keamanan Data</h2>
                <p>Kami berkomitmen untuk melindungi informasi Anda. Langkah-langkah keamanan yang kami terapkan meliputi:</p>
                <ul>
                    <li><strong>Enkripsi Kata Sandi:</strong> Kami tidak pernah menyimpan kata sandi Anda dalam bentuk teks biasa. Semua kata sandi dienkripsi menggunakan algoritma hashing yang kuat.</li>
                    <li><strong>Pencegahan SQL Injection:</strong> Kami menggunakan <em>prepared statements</em> (PDO) untuk semua interaksi database untuk melindungi sistem dari serangan injeksi SQL.</li>
                    <li><strong>Perlindungan Data Output:</strong> Kami menggunakan `htmlspecialchars` untuk menampilkan data, yang membantu mencegah serangan <em>Cross-Site Scripting</em> (XSS).</li>
                </ul>

                <h2 class="text-2xl font-semibold mt-8 mb-4">4. Pembagian Informasi</h2>
                <p>Kami tidak menjual atau menyewakan informasi pribadi Anda. Kami hanya membagikan informasi Anda dalam keadaan terbatas, seperti kepada penyedia layanan akhir (misalnya, maskapai) untuk penerbitan tiket atau jika diwajibkan oleh hukum.</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">5. Hak Anda</h2>
                <p>Anda memiliki hak untuk mengakses data profil dan riwayat tiket Anda melalui akun Anda. Anda juga berhak membatalkan tiket yang memenuhi syarat sesuai dengan syarat dan ketentuan yang berlaku.</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">6. Perubahan pada Kebijakan Privasi</h2>
                <p>Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Versi terbaru akan selalu tersedia di platform kami dengan tanggal efektif yang diperbarui.</p>

                <h2 class="text-2xl font-semibold mt-8 mb-4">7. Hubungi Kami</h2>
                <p>Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini, silakan hubungi kami melalui [Alamat Email Kontak Anda].</p>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>