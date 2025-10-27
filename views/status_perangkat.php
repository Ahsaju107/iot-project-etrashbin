<?php 
    session_start();
    include '../koneksi.php';

    $query = "SELECT * FROM tb_device WHERE device_id = '{$_SESSION['device_id']}'";
    $sql = mysqli_query($conn,$query);
    $result = mysqli_fetch_assoc($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Device List</title>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
     <script src="https://cdn.tailwindcss.com"></script>
     <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-900">
    <nav class="bg-slate-800/95 backdrop-blur-sm flex sm:hidden p-3 justify-between items-center text-white w-full z-20 fixed border-b border-emerald-500/20">
      <h1 class="font-extrabold text-xl judul-dashboard text-emerald-400"><i>E-TrashBin</i></h1>
      <button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar" type="button">
         <i class="fa-solid fa-bars-staggered text-lg text-emerald-400"></i>
      </button>
   </nav>
    <main>

<aside id="default-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
   <div class="h-full px-3 py-4 overflow-y-auto bg-slate-800 border-r border-emerald-500/20">
      <h1 class="font-extrabold text-2xl text-emerald-400 mb-4 judul-dashboard text-center italic">E-TrashBin</h1>
   <ul class="font-medium space-y-2">
         <!-- Dashboard -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <a href="../index.php" class="nav-link flex items-center p-2 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 group active">
               <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                  <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                  <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
               </svg>
               <span class="ms-3">Dashboard</span>
            </a>
         </li>
         <!-- Device List -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <a href="./device_list.php" class="nav-link flex items-center p-2 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 group">
               <i class="fa-solid fa-display"></i>
               <span class="ms-3">Device List</span>
            </a>
         </li>
         <!-- Device Status -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <a href="#" class="nav-link flex items-center p-2 rounded-lg bg-emerald-500/10 text-emerald-400 group">
               <i class="fa-solid fa-circle-nodes"></i>
               <span class="ms-3">Status Perangkat</span>
            </a>
         </li>
         <!-- Settings -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <a href="./pengaturan.html" class="nav-link flex items-center p-2 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 group">
               <i class="fa-solid fa-gear"></i>
               <span class="ms-3">Pengaturan</span>
            </a>
         </li>
         <!-- About -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <a href="#" class="nav-link flex items-center p-2 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 group">
               <i class="fa-solid fa-circle-info"></i>
               <span class="ms-3">Tentang</span>
            </a>
         </li>
         <!-- Logout -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <button type="button" class="flex w-full items-center p-2 rounded-lg text-slate-300 hover:bg-red-500/10 hover:text-red-400 group">
               <i class="fa-solid fa-right-to-bracket"></i>
               <span class="ms-3">Log out</span>
            </button>
         </li>
        
      </ul>

   </div>
</aside>
<!-- KONTEN UTAMA -->
<div class="p-4 sm:ml-64">
   <div class="mt-14 sm:mt-0 rounded-lg">
        <!-- Konten 1 -->
        <div class="list-grid grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-5 mb-7">
            <!-- card 1 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="sm:w-1/3 flex items-center">
                  <i class="fa-solid fa-microchip text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200 text-end">
                   <h2 class="text-sm md:text-base text-slate-400">Total Perangkat</h2>
                   <h1 class="text-3xl font-medium text-emerald-400">4</h1>
                </div>
            </div>
          
            <!-- card 2 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="flex items-center w-1/3">
                  <i class="fa-solid fa-signal text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200">
                   <h2 class="text-sm md:text-base text-slate-400">Status Sistem</h2>
                   <h1 class="text-xl font-medium text-emerald-400">1/4 Online</h1>
                </div>
            </div>
            <!-- card 3 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="sm:w-1/3 flex items-center">
                  <i class="fa-solid fa-arrows-rotate text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200 text-end">
                   <h2 class="text-sm md:text-base text-slate-400">Total Pemilahan</h2>
                   <h1 class="text-3xl font-medium text-emerald-400">47</h1>
                </div>
            </div>
            <!-- card 4 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="sm:w-1/3 flex items-center">
                  <i class="fa-solid fa-truck text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200 text-end">
                   <h2 class="text-sm md:text-base text-slate-400">Perlu Dikosongkan</h2>
                   <h1 class="text-3xl font-medium text-emerald-400">2</h1>
                </div>
            </div>
         </div>
         <!-- KONTEN 1 END -->

         <!-- KONTEN 2 -->
          <div class="flex flex-wrap gap-2 w-full mb-8">
            <div>
                <div class="flex gap-2 items-center">
                    <i class="fa-solid fa-circle-nodes text-emerald-400 text-2xl"></i>
                    <h1 class="font-bold text-4xl text-slate-200 drop-shadow-lg">Status Perangkat</h1>
                </div>
                <h2 class="text-lg font-medium text-emerald-400 ml-10"><?php echo $result['device_name']; ?></h2>
            </div>
            <span class="w-full h-0.5 bg-gradient-to-r from-emerald-500 to-transparent rounded-full"></span>
          </div>
          <!-- KONTEN 2 END -->

          <!-- KONTEN 3 -->
          <div class="flex flex-wrap gap-3 mb-8">
            <!-- SUB 1 KONTEN 3 -->
            <div class="w-full md:w-[70%] bg-slate-800 rounded-xl text-white p-4 shadow-xl">
                <h1 class="text-2xl mb-4 font-bold text-slate-200">Status Sensor</h1>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <!-- Status ESP32-CAM -->
                     <div class="bg-slate-700/50 rounded-lg p-4 flex justify-between shadow-lg relative hover:ring-2 hover:ring-emerald-500/50 hover:bg-slate-700 transition-all duration-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <div class="w-9 h-9 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-camera text-emerald-400 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-base text-emerald-400">ESP32-CAM</h2>
                                <p class="text-slate-400 text-sm">Kamera & AI: Aktif</p>
                            </div>
                        </div>
                        <div class="absolute w-2 h-2 rounded-full bg-emerald-500 z-10 right-2 top-2 shadow-lg shadow-emerald-500/50"></div>
                     </div>
                    <!-- Status Sensor Ultrasonik -->
                     <div class="bg-slate-700/50 rounded-lg p-4 flex justify-between shadow-lg relative hover:ring-2 hover:ring-emerald-500/50 hover:bg-slate-700 transition-all duration-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <div class="w-9 h-9 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                <img src="../images/ultrasonic-icon.svg" alt="ultrasonic-icon" class="w-7">
                            </div>
                            <div>
                                <h2 class="font-semibold text-base text-emerald-400">Sensor Ultrasonik</h2>
                                <p class="text-slate-400 text-sm">4 Sensor: Normal</p>
                            </div>
                        </div>
                        <div class="absolute w-2 h-2 rounded-full bg-emerald-500 z-10 right-2 top-2 shadow-lg shadow-emerald-500/50"></div>
                     </div>
                    <!-- Status Proximity Induktif -->
                     <div class="bg-slate-700/50 rounded-lg p-4 flex justify-between shadow-lg relative hover:ring-2 hover:ring-emerald-500/50 hover:bg-slate-700 transition-all duration-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <div class="w-9 h-9 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                <img src="../images/proximity-induktif.svg" alt="proximity-induktif" class="w-8">
                            </div>
                            <div>
                                <h2 class="font-semibold text-base text-emerald-400">Proximity Induktif</h2>
                                <p class="text-slate-400 text-sm">Deteksi: Standby</p>
                            </div>
                        </div>
                        <div class="absolute w-2 h-2 rounded-full bg-emerald-500 z-10 right-2 top-2 shadow-lg shadow-emerald-500/50"></div>
                     </div>
                    <!-- Status Servo Motor -->
                     <div class="bg-slate-700/50 rounded-lg p-4 flex justify-between shadow-lg relative hover:ring-2 hover:ring-emerald-500/50 hover:bg-slate-700 transition-all duration-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <div class="w-9 h-9 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                <img src="../images/servo.svg" alt="servo" class="w-7">
                            </div>
                            <div>
                                <h2 class="font-semibold text-base text-emerald-400">Servo Motor</h2>
                                <p class="text-slate-400 text-sm">Dual-Axis: Normal</p>
                            </div>
                        </div>
                        <div class="absolute w-2 h-2 rounded-full bg-emerald-500 z-10 right-2 top-2 shadow-lg shadow-emerald-500/50"></div>
                     </div>
                    <!-- Status LCD Display -->
                     <div class="bg-slate-700/50 rounded-lg p-4 flex justify-between shadow-lg relative hover:ring-2 hover:ring-emerald-500/50 hover:bg-slate-700 transition-all duration-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <div class="w-9 h-9 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                <img src="../images/lcd-display.svg" alt="lcd-display" class="w-7">
                            </div>
                            <div>
                                <h2 class="font-semibold text-base text-emerald-400">LCD Display</h2>
                                <p class="text-slate-400 text-sm">Tampilan: Aktif</p>
                            </div>
                        </div>
                        <div class="absolute w-2 h-2 rounded-full bg-emerald-500 z-10 right-2 top-2 shadow-lg shadow-emerald-500/50"></div>
                     </div>
                    <!-- Status Power Supply -->
                     <div class="bg-slate-700/50 rounded-lg p-4 flex justify-between shadow-lg relative hover:ring-2 hover:ring-emerald-500/50 hover:bg-slate-700 transition-all duration-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <div class="w-9 h-9 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-plug text-emerald-400 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-base text-emerald-400">Power Supply</h2>
                                <p class="text-slate-400 text-sm">5V 3V: Stabil</p>
                            </div>
                        </div>
                        <div class="absolute w-2 h-2 rounded-full bg-emerald-500 z-10 right-2 top-2 shadow-lg shadow-emerald-500/50"></div>
                     </div>
                </div>
            </div>
            <!-- SUB 1 KONTEN 3 END -->

            <!-- SUB 2 KONTEN 3 -->
            <div class="bg-slate-800 w-full md:w-[27%] rounded-xl text-white shadow-xl p-4 gap-3 grid grid-cols-2 md:grid-cols-1">
                <!-- Item status perangkat -->
                <div class="flex flex-col bg-slate-700/50 w-full items-center hover:ring-2 hover:ring-emerald-500/50 rounded-lg p-4 mx-auto shadow-lg hover:bg-slate-700 transition-all duration-200">
                    <div class="w-10 h-10 rounded-full bg-emerald-500 mb-2 animate-pulse shadow-lg shadow-emerald-500/50"></div>
                    <h2 class="font-semibold text-xl text-slate-200">Online</h2>
                    <p class="text-slate-400">Status</p>
                </div>
                <!-- Item status Sinyal Wifi -->
                <div class="flex flex-col bg-slate-700/50 w-full items-center hover:ring-2 hover:ring-emerald-500/50 rounded-lg p-4 mx-auto shadow-lg hover:bg-slate-700 transition-all duration-200">
                    <i class="fa-solid fa-wifi text-3xl text-emerald-400 mb-2"></i>
                    <h2 class="font-semibold text-xl text-slate-200">-45 dBm</h2>
                    <p class="text-slate-400">Sinyal Wifi</p>
                </div>
                <!-- Item status Update Terakhir kali -->
                <div class="flex flex-col bg-slate-700/50 w-full items-center rounded-lg text-center hover:ring-2 hover:ring-emerald-500/50 p-4 mx-auto shadow-lg hover:bg-slate-700 transition-all duration-200">
                    <i class="fa-solid fa-clock text-3xl text-emerald-400 mb-2"></i>
                    <h2 class="font-semibold text-xl text-slate-200">5 detik terakhir</h2>
                    <p class="text-slate-400">Update Terakhir</p>
                </div>

            </div>
            <!-- SUB 2 KONTEN 3 END -->
          </div>
          <!-- KONTEN 3 END -->
           
          <!-- KONTEN 4 -->
          <div class="w-full p-4 rounded-xl bg-slate-800 text-white shadow-xl">
            <h1 class="text-2xl font-bold mb-4 text-slate-200">History</h1>
            <div class="flex flex-wrap gap-3">
            <!-- ITEM 1 -->
            <div class="w-full flex items-center gap-3 p-3 bg-slate-700/50 rounded-lg shadow-lg hover:ring-2 hover:ring-emerald-500/50 hover:-translate-y-1 hover:bg-slate-700 duration-200 transition-all">
                <i class="fa-solid fa-circle-info text-emerald-400 text-xl"></i>
                <div>
                    <h2 class="font-medium text-emerald-400 text-base">Kapasitas anorganik mencapai 78%</h2>
                    <p class="text-slate-400 text-sm">23 Okt 2025, 09:12:15</p>
                </div>
            </div>
            <!-- ITEM 2 -->
            <div class="w-full flex items-center gap-3 p-3 bg-slate-700/50 rounded-lg shadow-lg hover:ring-2 hover:ring-emerald-500/50 hover:-translate-y-1 hover:bg-slate-700 duration-200 transition-all">
                <i class="fa-solid fa-circle-info text-emerald-400 text-xl"></i>
                <div>
                    <h2 class="font-medium text-emerald-400 text-base">Kapasitas organik mencapai 80%</h2>
                    <p class="text-slate-400 text-sm">23 Okt 2025, 09:12:15</p>
                </div>
            </div>
            <!-- ITEM 3 -->
            <div class="w-full flex items-center gap-3 p-3 bg-slate-700/50 rounded-lg shadow-lg hover:ring-2 hover:ring-emerald-500/50 hover:-translate-y-1 hover:bg-slate-700 duration-200 transition-all">
                <i class="fa-solid fa-circle-info text-emerald-400 text-xl"></i>
                <div>
                    <h2 class="font-medium text-emerald-400 text-base">Kapasitas logam mencapai 96%</h2>
                    <p class="text-slate-400 text-sm">23 Okt 2025, 09:12:15</p>
                </div>
            </div>
            </div>
            
          </div>
          <!-- KONTEN 4 END -->
        
   </div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>
</html>