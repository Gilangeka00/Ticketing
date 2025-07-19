<?php
// faq.php
include 'header.php';
?>

<div class="bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-xl p-8 md:p-10">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8 text-center">Pertanyaan yang Sering Diajukan (FAQ)</h1>
            
            <div class="space-y-8">
                <div>
                    <h2 class="text-2xl font-semibold border-b pb-2 mb-4">Pendaftaran & Akun</h2>
                    <div class="space-y-6">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">T: Bagaimana cara membuat akun?</h3>
                            <p class="mt-1 text-gray-600">J: Anda dapat membuat akun dengan mengklik tombol "Register" di halaman utama. Anda akan diminta untuk mengisi username, password, dan memilih peran sebagai "User".</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">T: Apakah data saya aman?</h3>
                            <p class="mt-1 text-gray-600">J: Ya. Kami sangat serius dalam menjaga keamanan data Anda. Kata sandi Anda dilindungi menggunakan enkripsi hash yang kuat, dan platform kami dilindungi dari serangan umum seperti SQL Injection.</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">T: Saya lupa kata sandi, apa yang harus saya lakukan?</h3>
                            <p class="mt-1 text-gray-600">J: Saat ini, sistem kami belum memiliki fitur reset kata sandi otomatis. Silakan hubungi layanan pelanggan kami di [Alamat Email Kontak Anda] untuk bantuan verifikasi dan pemulihan akun.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-2xl font-semibold border-b pb-2 mb-4">Saldo & Pembayaran</h2>
                    <div class="space-y-6">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">T: Apa itu "Saldo"?</h3>
                            <p class="mt-1 text-gray-600">J: Saldo adalah dompet digital di dalam akun Anda yang digunakan untuk semua transaksi, seperti membeli tiket atau menerima pengembalian dana.</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">T: Bagaimana cara mengisi Saldo?</h3>
                            <p class="mt-1 text-gray-600">J: Anda dapat menuju ke halaman "My Balance" (atau "Saldo Saya") dan menggunakan fitur "Top Up Saldo". Cukup masukkan jumlah yang Anda inginkan dan saldo akan ditambahkan secara otomatis (saat ini bersifat simulasi).</p>
                        </div>
                         <div>
                            <h3 class="font-semibold text-lg text-gray-800">T: Mengapa pembayaran saya gagal?</h3>
                            <p class="mt-1 text-gray-600">J: Penyebab paling umum adalah Saldo Anda tidak mencukupi. Pastikan Saldo Anda lebih besar dari total harga tiket yang akan Anda beli.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-2xl font-semibold border-b pb-2 mb-4">Pemesanan & Pembatalan</h2>
                    <div class="space-y-6">
                         <div>
                            <h3 class="font-semibold text-lg text-gray-800">T: Bagaimana saya bisa melihat tiket yang sudah saya beli?</h3>
                            <p class="mt-1 text-gray-600">J: Semua tiket yang berhasil Anda pesan akan muncul di halaman "Tiket Saya" (`my_tickets.php`).</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">T: Bisakah saya membatalkan tiket saya?</h3>
                            <p class="mt-1 text-gray-600">J: Ya, dengan syarat tertentu. Pembatalan hanya berlaku untuk tiket transportasi (pesawat, kapal, kereta) yang masih berstatus "booked" dan harus dilakukan minimal 24 jam sebelum jadwal keberangkatan.</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">T: Kemana dana refund saya akan dikembalikan?</h3>
                            <p class="mt-1 text-gray-600">J: Jika pembatalan Anda berhasil, dana akan dikembalikan sepenuhnya ke Saldo akun Anda di platform kami, bukan ke rekening bank. Anda dapat melihat penambahan saldo ini di riwayat transaksi.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>