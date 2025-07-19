<footer class="bg-black text-white px-6 md:px-16 py-10">
  <style>
    footer a {
      transition: all 0.3s ease;
    }

    footer a:hover {
      color: #60a5fa;
      /* biru muda */
    }

    .payment:hover {
      transform: scale(1.05);
      box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
    }

    .social:hover {
      transform: scale(1.1);
      filter: brightness(1.3);
    }
  </style>

  <div class="grid grid-cols-1 md:grid-cols-5 gap-8">

    <!-- Logo -->
    <div class="space-y-4">
      <div>
        <div class="mb-4">
          <a href="index.php"
            class="text-2xl font-bold text-blue-600 hover:text-blue-700 transition-colors">Ticketing</a>
        </div>
      </div>

      <!-- Partner -->
      <div class="space-y-4 md:col-span-2 pr-6">
        <p class="font-semibold mb-2">Partner Pembayaran</p>
        <div class="grid grid-cols-4 gap-2">
          <!-- Loop icon partner -->
          <a href="#" class="bg-white p-3 rounded shadow flex items-center justify-center">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRmf5rQoOt87qU25VDboIWw9KtxP0rfs6XSqw&s"
              alt="Visa" class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-3 rounded shadow flex items-center justify-center">
            <img
              src="https://blob.cloudcomputing.id/images/be66d44d-378f-49f0-9cff-c5905588bb75/logo-mastercard-l-min.jpg"
              alt="Mastercard" class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-3 rounded shadow flex items-center justify-center">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR-TJ_yiPGULbS6OV-BcN3ZyVBcFscCPoMkYA&s"
              alt="JCB" class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-3 rounded shadow flex items-center justify-center">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTi8Jsq4EATKruHpqE2vwrFaaY0Il1mCdnpnw&s"
              alt="Amex" class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-3 rounded shadow flex items-center justify-center">
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia.svg/2560px-Bank_Central_Asia.svg.png"
              alt="BCA" class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-2 rounded shadow flex items-center justify-center">
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Bank_Mandiri_logo_2016.svg/2560px-Bank_Mandiri_logo_2016.svg.png"
              alt="Mandiri" class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-2 rounded shadow flex items-center justify-center">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQHVgSISwM9A9WhH7aV2RKjSOJT7ML-aB6yPA&s"
              alt="Bank BRI" class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-2 rounded shadow flex items-center justify-center">
            <img src="https://upload.wikimedia.org/wikipedia/id/thumb/5/55/BNI_logo.svg/2560px-BNI_logo.svg.png"
              alt="BNI" class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-3 rounded shadow flex items-center justify-center">
            <img src="https://upload.wikimedia.org/wikipedia/id/e/e8/ATM_Bersama_2016.png" alt="ATM Bersama"
              class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-3 rounded shadow flex items-center justify-center">
            <img src="https://upload.wikimedia.org/wikipedia/id/e/e4/ATM_PRIMA.png" alt="PRIMA"
              class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-3 rounded shadow flex items-center justify-center">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQIGsMM4jERyAOK5IJTvlC1Pzs2kbQmg_6hbQ&s"
              alt="ALTO" class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-2 rounded shadow flex items-center justify-center">
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/86/Gopay_logo.svg/1024px-Gopay_logo.svg.png"
              alt="Gopay" class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-2 rounded shadow flex items-center justify-center">
            <img src="https://upload.wikimedia.org/wikipedia/commons/9/9e/ALFAMART_LOGO_BARU.png" alt="Alfamart"
              class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-3 rounded shadow flex items-center justify-center">
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/eb/Logo_ovo_purple.svg/2560px-Logo_ovo_purple.svg.png"
              alt="OVO" class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-2 rounded shadow flex items-center justify-center">
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/Logo_Indomaret.png/1200px-Logo_Indomaret.png"
              alt="Indomaret" class="h-6 object-contain">
          </a>
          <a href="#" class="bg-white p-2 rounded shadow flex items-center justify-center">
            <img src="https://upload.wikimedia.org/wikipedia/commons/3/38/CIMB_Niaga_logo.svg" alt="CIMB Niaga"
              class="h-6 object-contain">
          </a>
        </div>
      </div>
    </div>

    <!-- Tentang -->
    <div>
      <h3 class="font-semibold mb-2">Tentang Ticketing</h3>
      <ul class="space-y-1 text-sm">
        <li><a href="#">Cara Pesan</a></li>
        <li><a href="#">Hubungi Kami</a></li>
        <li><a href="#">Pusat Bantuan</a></li>
        <li><a href="kebijakan_privasi.php">Tentang Kami</a></li>
        <li><a href="faqs.php">FAQ</a></li>
        <li><a href="#">Karier</a></li>
        <li><a href="#">Cicilan</a></li>
        <li><a href="about_us.php">Tentang Kami</a></li>
        <li><a href="#">Rilisan Fitur Terbaru</a></li>
      </ul>
    </div>

    <!-- Produk -->
    <div>
      <h3 class="font-semibold mb-2">Produk</h3>
      <ul class="space-y-1 text-sm">
        <li><a href="#">Hotel</a></li>
        <li><a href="#">Tiket Pesawat</a></li>
        <li><a href="#">Tiket Kereta Api</a></li>
        <li><a href="#">Tiket Bus & Travel</a></li>
        <li><a href="#">Antar Jemput Bandara</a></li>
        <li><a href="#">Rental Mobil</a></li>
        <li><a href="#">Asuransi</a></li>
        <li><a href="#">Gift Voucher</a></li>
      </ul>
    </div>

    <!-- Lainnya -->
    <div>
      <h3 class="font-semibold mb-2">Lainnya</h3>
      <ul class="space-y-1 text-sm">
        <li><a href="#">Ticketing for Corporates</a></li>
        <li><a href="#">Ticketing Affiliate</a></li>
        <li><a href="#">Blog Ticketing</a></li>
        <li><a href="#">Pemberitahuan Privasi</a></li>
        <li><a href="syarat_dan_ketentuan.php">Syarat dan Ketentuan</a></li>
        <li><a href="#">Ticketing Ads</a></li>
      </ul>
    </div>

    <!-- Sosial -->
    <div>
      <h3 class="font-semibold mb-2">Follow kami di</h3>
      <ul class="space-y-2 text-sm">
        <li>
          <a href="#" class="flex items-center gap-2 social">
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/51/Facebook_f_logo_%282019%29.svg/1280px-Facebook_f_logo_%282019%29.svg.png"
              class="w-6 h-6 object-contain" alt="Facebook">
            Facebook
          </a>
        </li>
        <li>
          <a href="#" class="flex items-center gap-2 social">
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/95/Instagram_logo_2022.svg/960px-Instagram_logo_2022.svg.png"
              class="w-6 h-6 object-contain" alt="Instagram">
            Instagram
          </a>
        </li>
        <li>
          <a href="#" class="flex items-center gap-2 social">
            <img src="https://cdn.pixabay.com/photo/2021/01/30/06/43/tiktok-5962993_1280.png"
              class="w-6 h-6 object-contain" alt="TikTok">
            TikTok
          </a>
        </li>
        <li>
          <a href="#" class="flex items-center gap-2 social">
            <img src="https://upload.wikimedia.org/wikipedia/commons/e/ef/Youtube_logo.png"
              class="w-6 h-6 object-contain" alt="YouTube">
            YouTube
          </a>
        </li>
        <li>
          <a href="#" class="flex items-center gap-2 social">
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6f/Logo_of_Twitter.svg/2491px-Logo_of_Twitter.svg.png"
              class="w-6 h-6 object-contain" alt="Twitter">
            Twitter
          </a>
        </li>
        <li>
          <a href="#" class="flex items-center gap-2 social">
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/82/Telegram_logo.svg/800px-Telegram_logo.svg.png"
              class="w-6 h-6 object-contain" alt="Telegram">
            Telegram
          </a>
        </li>
        <li>
          <a href="#" class="flex items-center gap-2 social">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/WhatsApp.svg/2044px-WhatsApp.svg.png"
              class="w-6 h-6 object-contain" alt="WhatsApp">
            WhatsApp
          </a>
        </li>
      </ul>


      <div class="mt-6">
        <h3 class="font-semibold text-white mb-2">Download Ticketing App</h3>
        <div class="space-y-2">
          <div
            class="w-[160px] h-[52px] flex items-center justify-center rounded overflow-hidden hover:scale-105 transition">
            <img src="https://play.google.com/intl/en_us/badges/images/generic/en_badge_web_generic.png?hl=id"
              alt="Google Play" class="scale-[1.2] h-full object-contain">
          </div>
          <div
            class="w-[160px] h-[52px] flex items-center justify-center rounded overflow-hidden hover:scale-105 transition">
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3c/Download_on_the_App_Store_Badge.svg/2560px-Download_on_the_App_Store_Badge.svg.png"
              alt="App Store" class="scale-[0.9] h-full object-contain">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="text-center text-sm mt-10 border-t border-gray-700 pt-4">
    Copyright &copy; <?= date('Y') ?> Ticketing Website. All rights reserved
  </div>
</footer>