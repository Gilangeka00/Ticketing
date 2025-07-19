<?php
// index.php
include 'config.php';
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About Us</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
/* animasi muncul */
.fade-up {
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.8s ease;
}
.fade-up.show {
  opacity: 1;
  transform: translateY(0);
}

/* animasi hover nama & nim */
.team-name {
  transition: transform 0.3s ease, color 0.3s ease;
}
.team-name:hover {
  transform: scale(1.1);
  color: #2563EB; /* blue-600 */
}

.team-nim {
  transition: transform 0.3s ease, color 0.3s ease;
}
.team-nim:hover {
  transform: translateX(5px);
  color: #1E40AF; /* blue-800 */
}

/* animasi border & shadow kartu */
.team-card {
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
  border: 2px solid #e5e7eb; /* default border-gray-200 */
}
.team-card:hover {
  border-color: #3B82F6; /* blue-500 */
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}
</style>
</head>
<body class="bg-white text-gray-800">

<!-- BANNER -->
<section class="relative h-[500px] overflow-hidden">
  <div class="absolute inset-0 h-full w-full">
    <img src="uploads/Banner/Banner1.jpg" alt="Banner 1"
         class="w-full h-full object-cover absolute inset-0 opacity-100 transition-opacity duration-1000 banner">
    <img src="uploads/Banner/Banner2.jpg" alt="Banner 2"
         class="w-full h-full object-cover absolute inset-0 opacity-0 transition-opacity duration-1000 banner">
    <img src="uploads/Banner/Banner3.jpg" alt="Banner 3"
         class="w-full h-full object-cover absolute inset-0 opacity-0 transition-opacity duration-1000 banner">
  </div>

  <div class="absolute inset-0 bg-black/50 flex flex-col justify-center items-center text-center px-4 z-10">
    <h1 class="text-4xl md:text-5xl font-bold text-white drop-shadow-lg">
      Ticketing
    </h1>
    <p class="text-white mt-6 mb-4 max-w-2xl text-sm md:text-base leading-relaxed">
      Ticketing adalah platform pemesanan tiket online yang memudahkan para traveler untuk mengakses berbagai pilihan produk perjalanan secara praktis dan cepat. 
      Melalui situs web kami, pengguna dapat menemukan dan membeli tiket pesawat, kereta, kapal, penginapan, hingga kendaraan sewa – semuanya dalam satu tempat. 
      Kami berkomitmen untuk memberikan pengalaman pemesanan yang nyaman, aman, dan terpercaya bagi setiap perjalanan Anda.
    </p>
  </div>
</section>

<section class="bg-white -mt-12 relative z-10 rounded-t-[2rem] pt-10 pb-20">
  <div class="text-center mb-12">
    <h2 class="text-3xl font-bold text-blue-600">Our Team</h2>
    <p class="text-gray-700 mt-2">Kenali orang-orang hebat di balik project ini</p>
  </div>

  <div class="grid gap-8 md:grid-cols-3 px-6 md:px-12">
    <!-- Anggota 1 -->
    <div class="text-center rounded-lg shadow-md p-6 fade-up team-card">
      <img src="uploads/Foto Profil/Profil ANGGI.jpg" alt="Anggota 1"
           class="mx-auto w-40 h-40 rounded-full object-cover shadow mb-4 hover:scale-105 transition-transform duration-300">
      <h3 class="text-xl font-semibold text-blue-500 team-name">RIZKY NANDA ANGGIA</h3>
      <p class="text-gray-600 team-nim"><b>NIM:</b> 22.11.4825</p>
    </div>

    <!-- Anggota 2 -->
    <div class="text-center rounded-lg shadow-md p-6 fade-up team-card">
      <img src="uploads/Foto Profil/Profil GILANG.jpg" alt="Anggota 2"
           class="mx-auto w-40 h-40 rounded-full object-cover shadow mb-4 hover:scale-105 transition-transform duration-300">
      <h3 class="text-xl font-semibold text-blue-500 team-name">GILANG EKAYANDA</h3>
      <p class="text-gray-600 team-nim"><b>NIM:</b> 22.11.4833</p>
    </div>

    <!-- Anggota 3 -->
    <div class="text-center rounded-lg shadow-md p-6 fade-up team-card">
      <img src="uploads/Foto Profil/Profil ADIB.jpg" alt="Anggota 3"
           class="mx-auto w-40 h-40 rounded-full object-cover shadow mb-4 hover:scale-105 transition-transform duration-300"
           style="object-position: center 10%;">
      <h3 class="text-xl font-semibold text-blue-500 team-name">ADIB RAMADHAN</h3>
      <p class="text-gray-600 team-nim"><b>NIM:</b> 22.11.4873</p>
    </div>
  </div>

  <div class="grid gap-8 md:grid-cols-2 mt-12 md:w-2/3 mx-auto px-6 md:px-12">
    <!-- Anggota 4 -->
    <div class="text-center rounded-lg shadow-md p-6 fade-up team-card">
      <img src="uploads/Foto Profil/Profil CORNEL.jpg" alt="Anggota 4"
           class="mx-auto w-40 h-40 rounded-full object-cover shadow mb-4 hover:scale-105 transition-transform duration-300"
           style="object-position: center 30%;">
      <h3 class="text-xl font-semibold text-blue-500 team-name">CORNELIA</h3>
      <p class="text-gray-600 team-nim"><b>NIM:</b> 22.11.4874</p>
    </div>

    <!-- Anggota 5 -->
    <div class="text-center rounded-lg shadow-md p-6 fade-up team-card">
      <img src="uploads/Foto Profil/Profil PUTU.jpg" alt="Anggota 5"
           class="mx-auto w-40 h-40 rounded-full object-cover shadow mb-4 hover:scale-105 transition-transform duration-300">
      <h3 class="text-xl font-semibold text-blue-500 team-name">NI PUTU SUMERTIANI</h3>
      <p class="text-gray-600 team-nim"><b>NIM:</b> 22.11.4875</p>
    </div>
  </div>
</section>

<script>
// animasi banner
const banners = document.querySelectorAll('.banner');
let current = 0;

setInterval(() => {
  banners[current].classList.remove('opacity-100');
  banners[current].classList.add('opacity-0');

  current = (current + 1) % banners.length;

  banners[current].classList.remove('opacity-0');
  banners[current].classList.add('opacity-100');
}, 5000);

// animasi muncul team card
const teamCards = document.querySelectorAll('.team-card');

const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('show');
    }
  });
}, { threshold: 0.2 });

teamCards.forEach(card => observer.observe(card));
</script>

<?php include 'footer.php'; ?>
</body>
</html>
