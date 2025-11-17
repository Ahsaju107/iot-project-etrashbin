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
    $query_history = "SELECT * FROM tb_history WHERE device_id = '{$_SESSION['device_id']}' ORDER BY id DESC LIMIT 10";
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
      <img src="../images/logo_etrashbin_mobile.png" alt="logo logo_etrashbin" class="w-32">
    <button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar" type="button">
         <i class="fa-solid fa-bars-staggered text-lg text-emerald-400"></i>
      </button>
   </nav>
    <main>

<aside id="default-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
   <div class="h-full px-3 py-4 overflow-y-auto bg-slate-800 border-r border-emerald-500/20">
      <img src="../images/logo_etrashbin.png" alt="logo logo_etrashbin" class="mx-auto mb-2">
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
                   <h1 id="total_perangkat" class="text-3xl font-medium text-emerald-400"><?php echo $totalPerangkat['total_perangkat'];?></h1>
                </div>
            </div>
          
            <!-- card 2 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="flex items-center w-1/3">
                  <i class="fa-solid fa-signal text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200">
                   <h2 class="text-sm md:text-base text-slate-400">Status Sistem</h2>
                   <h1 id="total_online" class="text-xl font-medium text-emerald-400"><?php echo $perangkatOnline['perangkat_online'];?>/<?php echo $totalPerangkat['total_perangkat'];?> Online</h1>
                </div>
            </div>
            <!-- card 3 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="sm:w-1/3 flex items-center">
                  <i class="fa-solid fa-arrows-rotate text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200 text-end">
                   <h2 class="text-sm md:text-base text-slate-400">Total Pemilahan</h2>
                   <h1 id="total_pemilahan" class="text-3xl font-medium text-emerald-400"><?php echo $total_pemilahan;?></h1>
                </div>
            </div>
            <!-- card 4 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="sm:w-1/3 flex items-center">
                  <i class="fa-solid fa-truck text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200 text-end">
                   <h2 class="text-sm md:text-base text-slate-400">Perlu Dikosongkan</h2>
                   <h1 id="perlu_dikosongkan" class="text-3xl font-medium text-emerald-400"><?php echo $perlu_dikosongkan;?></h1>
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
          <div id="status_perangkat"></div>
          <!-- KONTEN 3 END -->
           
          <!-- KONTEN 4 -->
          <div class="w-full p-4 rounded-xl bg-slate-800 text-white shadow-xl">
            <div class="flex justify-between w-full">
                <h1 class="text-2xl font-bold mb-4 text-slate-200">History</h1>
                <a href="../proses.php?hapus_history=<?php echo $_SESSION['device_id']; ?>" class="del-btn text-red-500 hover:underline hover:text-red-600 rounded-md mr-2">Hapus semua</a>
            </div>
            <div id="list_history">
          
            
            
          </div>
          <!-- KONTEN 4 END -->
        
   </div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
<script src="../js/jquery.js"></script>
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
<script>
    $(document).ready(()=>{
        $.ajaxSetup({ cache: false });
        $('#status_perangkat').load('../show_data.php?status_sensor');
        $('#list_history').load('../show_data.php?history');


        setInterval(()=>{
            // LOAD TOTAL PERANGKAT
            $('#total_perangkat').load('../show_data.php?type=total_perangkat');
            // LOAD TOTAL PERANGKAT YANG ONLINE
            $('#total_online').load('../show_data.php?type=total_online');
            // LOAD TOTAL PEMILAHAN 
            $('#total_pemilahan').load('../show_data.php?type=total_pemilahan');
            // LOAD PERLU DIKOSONGKAN
            $('#perlu_dikosongkan').load('../show_data.php?type=perlu_dikosongkan');
            // LOAD HISTORY
           $('#list_history').load('../show_data.php?history');
        }, 5000);
        setInterval(() =>{
            $('#status_perangkat').load('../show_data.php?status_sensor')
        }, 15000)
    });
  
</script>
</body>
</html>