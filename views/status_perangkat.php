<?php 
    session_start();
    include '../koneksi.php';

    // CEK LOGIN
   if(!isset($_SESSION['id_user'])){
      header('location: ./login.php');
      exit();
   }

    //TOTAL PERANGKAT
    $q_total = mysqli_query($conn, "SELECT COUNT(*) AS total_perangkat FROM tb_device");
    $totalPerangkat = mysqli_fetch_assoc($q_total);

    //TOTAL PERANGKAT YANG ONLINE
    $q_online = mysqli_query($conn, "SELECT COUNT(*) AS perangkat_online FROM tb_device WHERE status = 1");
    $perangkatOnline = mysqli_fetch_assoc($q_online);
    

   // Menghitung total pemilahan
   $q_total_pemilahan = mysqli_query($conn,"SELECT SUM(sorting_today) AS total_pemilahan FROM tb_device_status");
   $result_total_pemilahan = mysqli_fetch_assoc($q_total_pemilahan);
   $total_pemilahan = $result_total_pemilahan['total_pemilahan'];

   // Menghitung device yang perlu dikosongkan
   $q_perlu_dikosongkan = mysqli_query(
      $conn,
      "SELECT COUNT(*) 
      AS perlu_dikosongkan 
      FROM tb_device_status 
      WHERE GREATEST(
         COALESCE(kapasitas_logam,0),
         COALESCE(kapasitas_organik,0),
         COALESCE(kapasitas_anorganik,0)
      ) >= 80");
   $result_perlu_dikosongkan = mysqli_fetch_assoc($q_perlu_dikosongkan);
   $perlu_dikosongkan = $result_perlu_dikosongkan['perlu_dikosongkan'];


    $query = "SELECT 
        d.device_id,
        d.device_name,
        d.status,
        ds.wifi_signal,
        ds.last_update,
        ds.sensor_cam,
        ds.sensor_ultrasonic,
        ds.sensor_proximity,
        ds.servo,
        ds.lcd,
        ds.kapasitas_organik,
        ds.kapasitas_anorganik,
        ds.kapasitas_logam,
        ds.sorting_today
        FROM tb_device d 
        LEFT JOIN tb_device_status ds ON d.device_id = ds.device_id
        WHERE d.device_id = '{$_SESSION['device_id']}'";

    $sql = mysqli_query($conn,$query);
    $result = mysqli_fetch_assoc($sql);

    // QUERY HISTORY
    $query_history = "SELECT * FROM tb_history WHERE device_id = '{$_SESSION['device_id']}' LIMIT 10";
    $sql_history = mysqli_query($conn,$query_history);

    // Fungsi format waktu relatif
        function timeAgo($input){
        // Jika input numeric => anggap sudah selisih detik
        if (is_numeric($input)) {
            $detik = (int)$input;
        } else {
            // Jika string (datetime), hitung selisih dari sekarang
            $ts = strtotime($input);
            if ($ts === false) {
                // Jika tidak bisa di-parse, kembalikan teks fallback
                return 'unknown';
            }
            $detik = time() - $ts;
        }

        if($detik < 60){
            return $detik . " detik yang lalu";
        } elseif($detik < 3600){
            $menit = floor($detik / 60);
            return $menit . " menit yang lalu";
        } elseif($detik < 86400){
            $jam = floor($detik / 3600);
            return $jam . " jam yang lalu";
        } else {
            $hari = floor($detik / 86400);
            return $hari . " hari yang lalu";
        }
    }


    // Hitung selisih waktu last update
    $last_update = strtotime($result['last_update']);
    $current_time = time();
    $time_diff = $current_time - $last_update;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Status Perangkat</title>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
     <script src="https://cdn.tailwindcss.com"></script>
     <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="../images/logo-iot.png" type="image/x-icon">

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
            <a href="./pengaturan.php" class="nav-link flex items-center p-2 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 group">
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
            <form action="../proses.php" method="post">
                <button type="submit" name="aksi" value="logout" onclick="return confirm('apakah kamu yakin ingin keluar?')" class="flex w-full items-center p-2 rounded-lg text-slate-300 hover:bg-red-500/10 hover:text-red-400 group">
                   <i class="fa-solid fa-right-to-bracket"></i>
                   <span class="ms-3">Log out</span>
                </button>
            </form>
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
                   <h1 class="text-3xl font-medium text-emerald-400"><?php echo $totalPerangkat['total_perangkat'];?></h1>
                </div>
            </div>
          
            <!-- card 2 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="flex items-center w-1/3">
                  <i class="fa-solid fa-signal text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200">
                   <h2 class="text-sm md:text-base text-slate-400">Status Sistem</h2>
                   <h1 class="text-xl font-medium text-emerald-400"><?php echo $perangkatOnline['perangkat_online'];?>/<?php echo $totalPerangkat['total_perangkat'];?> Online</h1>
                </div>
            </div>
            <!-- card 3 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="sm:w-1/3 flex items-center">
                  <i class="fa-solid fa-arrows-rotate text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200 text-end">
                   <h2 class="text-sm md:text-base text-slate-400">Total Pemilahan</h2>
                   <h1 class="text-3xl font-medium text-emerald-400"><?php echo $total_pemilahan;?></h1>
                </div>
            </div>
            <!-- card 4 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="sm:w-1/3 flex items-center">
                  <i class="fa-solid fa-truck text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200 text-end">
                   <h2 class="text-sm md:text-base text-slate-400">Perlu Dikosongkan</h2>
                   <h1 class="text-3xl font-medium text-emerald-400"><?php echo $perlu_dikosongkan;?></h1>
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
                     <!-- JIKA AKTIF HIJAU -->
                     <?php
                        if($result['sensor_cam'] == 1){
                     ?>
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
                     <!-- JIKA OFFLINE MERAH -->
                     <?php
                      } else {
                     ?>
                     <div class="bg-slate-700/50 rounded-lg p-4 flex justify-between shadow-lg relative hover:ring-2 hover:ring-red-500/50 hover:bg-slate-700 transition-all duration-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <div class="w-9 h-9 bg-red-500/20 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-camera text-red-400 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-base text-red-400">ESP32-CAM</h2>
                                <p class="text-slate-400 text-sm">Kamera & AI: Error</p>
                            </div>
                        </div>
                        <div class="absolute w-2 h-2 rounded-full bg-red-500 z-10 right-2 top-2 shadow-lg shadow-red-500/50"></div>
                     </div>
                     <?php
                      } 
                     ?>
                    
                    <!-- Status Sensor Ultrasonik -->
                      <!-- JIKA AKTIF HIJAU -->
                     <?php 
                        if($result['sensor_ultrasonic'] == 1){
                     ?>
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
                     <?php 
                        } else {
                     ?>
                    <div class="bg-slate-700/50 rounded-lg p-4 flex justify-between shadow-lg relative hover:ring-2 hover:ring-red-500/50 hover:bg-slate-700 transition-all duration-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <div class="w-9 h-9 bg-red-500/20 rounded-lg flex items-center justify-center">
                                <img src="../images/ultrasonic-icon.svg" alt="ultrasonic-icon" class="w-7 red-svg" style="filter: invert(60%) sepia(94%) saturate(4288%) hue-rotate(340deg) brightness(97%) contrast(97%);">
                            </div>
                            <div>
                                <h2 class="font-semibold text-base text-red-400">Sensor Ultrasonik</h2>
                                <p class="text-slate-400 text-sm">4 Sensor: Error</p>
                            </div>
                        </div>
                        <div class="absolute w-2 h-2 rounded-full bg-red-500 z-10 right-2 top-2 shadow-lg shadow-red-500/50"></div>
                     </div>
                     <?php
                      } 
                     ?>
                     
                    <!-- Status Proximity Induktif -->
                     <!-- JIKA AKTIF HIJAU -->
                     <?php 
                        if($result['sensor_proximity'] == 1){
                     ?>
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
                     <?php 
                        } else {
                     ?>
                    <div class="bg-slate-700/50 rounded-lg p-4 flex justify-between shadow-lg relative hover:ring-2 hover:ring-red-500/50 hover:bg-slate-700 transition-all duration-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <div class="w-9 h-9 bg-red-500/20 rounded-lg flex items-center justify-center">
                                <img src="../images/proximity-induktif.svg" alt="proximity-induktif" class="w-8" style="filter: invert(60%) sepia(94%) saturate(4288%) hue-rotate(340deg) brightness(97%) contrast(97%);">
                            </div>
                            <div>
                                <h2 class="font-semibold text-base text-red-400">Proximity Induktif</h2>
                                <p class="text-slate-400 text-sm">Deteksi: Error</p>
                            </div>
                        </div>
                        <div class="absolute w-2 h-2 rounded-full bg-red-500 z-10 right-2 top-2 shadow-lg shadow-red-500/50"></div>
                     </div>
                     <?php
                      } 
                     ?>
                     
                    <!-- Status Servo Motor -->
                     <!-- JIKA AKTIF HIJAU -->
                     <?php 
                        if($result['servo'] == 1){
                     ?>
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
                     <?php 
                        } else {
                     ?>
                    <div class="bg-slate-700/50 rounded-lg p-4 flex justify-between shadow-lg relative hover:ring-2 hover:ring-red-500/50 hover:bg-slate-700 transition-all duration-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <div class="w-9 h-9 bg-red-500/20 rounded-lg flex items-center justify-center" style="filter: invert(60%) sepia(94%) saturate(4288%) hue-rotate(340deg) brightness(97%) contrast(97%);">
                                <img src="../images/servo.svg" alt="servo" class="w-7">
                            </div>
                            <div>
                                <h2 class="font-semibold text-base text-red-400">Servo Motor</h2>
                                <p class="text-slate-400 text-sm">Dual-Axis: Error</p>
                            </div>
                        </div>
                        <div class="absolute w-2 h-2 rounded-full bg-red-500 z-10 right-2 top-2 shadow-lg shadow-red-500/50"></div>
                     </div>
                     <?php
                      } 
                     ?>
                     
                    <!-- Status LCD Display -->
                     <!-- JIKA AKTIF HIJAU -->
                     <?php 
                        if($result['lcd'] == 1){
                     ?>
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
                     <?php 
                        } else {
                     ?>
                    <div class="bg-slate-700/50 rounded-lg p-4 flex justify-between shadow-lg relative hover:ring-2 hover:ring-red-500/50 hover:bg-slate-700 transition-all duration-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <div class="w-9 h-9 bg-red-500/20 rounded-lg flex items-center justify-center">
                                <img src="../images/lcd-display.svg" alt="lcd-display" class="w-7" style="filter: invert(60%) sepia(94%) saturate(4288%) hue-rotate(340deg) brightness(97%) contrast(97%);">
                            </div>
                            <div>
                                <h2 class="font-semibold text-base text-red-400">LCD Display</h2>
                                <p class="text-slate-400 text-sm">Tampilan: Error</p>
                            </div>
                        </div>
                        <div class="absolute w-2 h-2 rounded-full bg-red-500 z-10 right-2 top-2 shadow-lg shadow-red-500/50"></div>
                     </div>
                     <?php
                      } 
                     ?>
                     
                    <!-- Status Power Supply -->
                     <!-- JIKA AKTIF HIJAU -->
                     <?php 
                        if($result['status'] == 1){
                     ?>
                    <div class="bg-slate-700/50 rounded-lg p-4 flex justify-between shadow-lg relative hover:ring-2 hover:ring-emerald-500/50 hover:bg-slate-700 transition-all duration-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <div class="w-9 h-9 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-plug text-emerald-400 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-base text-emerald-400">Power Supply</h2>
                                <p class="text-slate-400 text-sm">5V 3V: Normal</p>
                            </div>
                        </div>
                        <div class="absolute w-2 h-2 rounded-full bg-emerald-500 z-10 right-2 top-2 shadow-lg shadow-emerald-500/50"></div>
                     </div>
                     <?php 
                        } else {
                     ?>
                    <div class="bg-slate-700/50 rounded-lg p-4 flex justify-between shadow-lg relative hover:ring-2 hover:ring-red-500/50 hover:bg-slate-700 transition-all duration-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <div class="w-9 h-9 bg-red-500/20 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-plug text-red-400 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-base text-red-400">Power Supply</h2>
                                <p class="text-slate-400 text-sm">5V 3V: Mati</p>
                            </div>
                        </div>
                        <div class="absolute w-2 h-2 rounded-full bg-red-500 z-10 right-2 top-2 shadow-lg shadow-red-500/50"></div>
                     </div>
                     <?php
                      } 
                     ?>
                     

                </div>
            </div>
            <!-- SUB 1 KONTEN 3 END -->

            <!-- SUB 2 KONTEN 3 -->
            <div class="bg-slate-800 w-full md:w-[27%] rounded-xl text-white shadow-xl p-4 gap-3 grid grid-cols-2 md:grid-cols-1">
                <!-- Item status perangkat -->
                 <!-- JIKA AKTIF HIJAU -->
                     <?php 
                        if($result['status'] == 1){
                     ?>
                        <div class="flex flex-col bg-slate-700/50 w-full items-center hover:ring-2 hover:ring-emerald-500/50 rounded-lg p-4 mx-auto shadow-lg hover:bg-slate-700 transition-all duration-200">
                            <div class="w-10 h-10 rounded-full bg-emerald-500 mb-2 animate-pulse shadow-lg shadow-emerald-500/50"></div>
                            <h2 class="font-semibold text-xl text-slate-200">Online</h2>
                            <p class="text-slate-400">Status</p>
                        </div>
                     <?php 
                        } else {
                     ?>
                        <div class="flex flex-col bg-slate-700/50 w-full items-center hover:ring-2 hover:ring-red-500/50 rounded-lg p-4 mx-auto shadow-lg hover:bg-slate-700 transition-all duration-200">
                            <div class="w-10 h-10 rounded-full bg-red-500 mb-2 animate-pulse shadow-lg shadow-red-500/50"></div>
                            <h2 class="font-semibold text-xl text-slate-200">Offline</h2>
                            <p class="text-slate-400">Status</p>
                        </div>
                     <?php
                      } 
                     ?>
                
                <!-- Item status Sinyal Wifi -->
                <div class="flex flex-col bg-slate-700/50 w-full items-center hover:ring-2 hover:ring-emerald-500/50 rounded-lg p-4 mx-auto shadow-lg hover:bg-slate-700 transition-all duration-200">
                    <i class="fa-solid fa-wifi text-3xl text-emerald-400 mb-2"></i>
                    <h2 class="font-semibold text-xl text-slate-200">-<?php echo $result['wifi_signal'] ?> dBm</h2>
                    <p class="text-slate-400">Sinyal Wifi</p>
                </div>
                <!-- Item status Update Terakhir kali -->
                <div class="flex flex-col bg-slate-700/50 w-full items-center rounded-lg text-center hover:ring-2 hover:ring-emerald-500/50 p-4 mx-auto shadow-lg hover:bg-slate-700 transition-all duration-200">
                    <i class="fa-solid fa-clock text-3xl text-emerald-400 mb-2"></i>
                    <h2 class="font-semibold text-xl text-slate-200"><?php echo timeAgo($result['last_update']); ?></h2>
                    <p class="text-slate-400">Update Terakhir</p>
                </div>

            </div>
            <!-- SUB 2 KONTEN 3 END -->
          </div>
          <!-- KONTEN 3 END -->
           
          <!-- KONTEN 4 -->
          <div class="w-full p-4 rounded-xl bg-slate-800 text-white shadow-xl">
            <div class="flex justify-between w-full">
                <h1 class="text-2xl font-bold mb-4 text-slate-200">History</h1>
                <form action="../proses.php" method="post">
                    <a href="../proses.php?hapus_history" class="del-btn text-red-500 hover:underline hover:text-red-600 rounded-md mr-2">Hapus semua</a>
                </form>
            </div>
            <div class="flex flex-wrap gap-3">
            <?php
                if(mysqli_num_rows($sql_history) > 0){
            ?>
                <?php 
                    while($history = mysqli_fetch_assoc($sql_history)){
                ?>
                        <!-- ITEM 1 -->
                    <div class="w-full flex items-center gap-3 p-3 bg-slate-700/50 rounded-lg shadow-lg hover:ring-2 hover:ring-emerald-500/50 hover:-translate-y-1 hover:bg-slate-700 duration-200 transition-all">
                        <i class="fa-solid fa-circle-info text-emerald-400 text-xl"></i>
                        <div>
                            <h2 class="font-medium text-emerald-400 text-base"><?php echo $history['message']; ?></h2>
                            <p class="text-slate-400 text-sm"><?php echo $history['created_at']; ?></p>   
                        </div>
                    </div>
                <?php } ?>
            
            <?php
             } else {
            ?>
                <div class="w-full p-1 text-slate-500/70 flex justify-center">
                    <h1>Tidak ada history</h1>
                </div>
            <?php 
             }
            ?>
            
            
          </div>
          <!-- KONTEN 4 END -->
        
   </div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
<script>
   function checkDeviceStatus() {
      fetch('../check_device_status.php')
         .then(response => response.json())
         .then(data => {
               console.log('Device Status Check:', data);
               
               // Jika ada device yang berubah jadi offline, reload page
               if (data.status === 'success' && data.message.includes('set to offline')) {
                  console.log('⚠️ Device status changed! Reloading...');
                  location.reload();
               }
         })
         .catch(error => {
               console.error('Status check error:', error);
         });
   }

   // Jalankan pertama kali saat page load
   checkDeviceStatus();

   // Jalankan setiap 30 detik
   setInterval(checkDeviceStatus, 30000);
</script>

</body>
</html>