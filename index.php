<?php
   session_start();
   include './koneksi.php';

   if(!isset($_SESSION['id_user'])){
      header('location: ./views/login.php');
      exit();
   }
  // Jika session device_id belum ada, ambil 1 device dari DB
if (empty($_SESSION['device_id'])) {
    $q = mysqli_query($conn, "SELECT device_id FROM tb_device LIMIT 1");
    $row = ($q && mysqli_num_rows($q) > 0) ? mysqli_fetch_assoc($q) : null;
    // kalau tidak ada device di DB, set jadi string kosong supaya aman
    $_SESSION['device_id'] = $row['device_id'] ?? '';
}

// Ambil data device hanya jika device_id tersedia
$result = null;
if (!empty($_SESSION['device_id'])) {
    $did = mysqli_real_escape_string($conn, $_SESSION['device_id']);
    $r = mysqli_query($conn, "SELECT * FROM tb_device WHERE device_id='$did' LIMIT 1");
    if ($r && mysqli_num_rows($r) > 0) $result = mysqli_fetch_assoc($r);
}

   // Menghitung total perangkat
   $q_total = mysqli_query($conn,"SELECT COUNT(*) AS total_perangkat FROM tb_device");
   $q_result = mysqli_fetch_assoc($q_total);
   $total_perangkat = $q_result['total_perangkat'];

   // Menghitung total perangkat yang online
   $q_perangkat_online = mysqli_query($conn, "SELECT COUNT(*) AS total_online FROM tb_device WHERE status = 1");
   $result_perangkat_online = mysqli_fetch_assoc($q_perangkat_online);
   $total_online = $result_perangkat_online['total_online'];

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
    $resultData = mysqli_fetch_assoc($sql);

    $rotasi_logam = ($resultData['kapasitas_logam'] / 100) * 0.5;
    $rotasi_organik = ($resultData['kapasitas_organik'] / 100) * 0.5;
    $rotasi_anorganik = ($resultData['kapasitas_anorganik'] / 100) * 0.5;


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard <?php echo $_SESSION['device_id'];?></title>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
     <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="./images/logo-iot.png" type="image/x-icon">
    <link rel="stylesheet" href="./css/style.css">
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
            <a href="./index.php" class="nav-link flex items-center p-2 rounded-lg text-emerald-400 bg-emerald-500/10 group active">
               <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                  <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                  <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
               </svg>
               <span class="ms-3">Dashboard</span>
            </a>
         </li>
         <!-- Device List -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <a href="./views/device_list.php" class="nav-link flex items-center p-2 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 group">
               <i class="fa-solid fa-display"></i>
               <span class="ms-3">Device List</span>
            </a>
         </li>
         <!-- Device Status -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <a href="./views/status_perangkat.php" class="nav-link flex items-center p-2 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 group">
               <i class="fa-solid fa-circle-nodes"></i>
               <span class="ms-3">Status Perangkat</span>
            </a>
         </li>
         <!-- Settings -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <a href="./views/pengaturan.php" class="nav-link flex items-center p-2 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 group">
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
            <form action="./proses.php" method="post">
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
       <!-- Konten 1 - Stats Cards -->
        <div class="list-grid grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-5 mb-7">
            <!-- card 1 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="sm:w-1/3 flex items-center">
                  <i class="fa-solid fa-microchip text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200 text-end">
                   <h2 class="text-sm md:text-base text-slate-400">Total Perangkat</h2>
                   <h1 class="text-3xl font-medium text-emerald-400"><?php echo $total_perangkat; ?></h1>
                </div>
            </div>
          
            <!-- card 2 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="flex items-center w-1/3">
                  <i class="fa-solid fa-signal text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200">
                   <h2 class="text-sm md:text-base text-slate-400">Status Sistem</h2>
                   <h1 class="text-lg font-medium text-emerald-400"><?php echo $total_online; ?>/<?php echo $total_perangkat; ?> Online</h1>
                </div>
            </div>
            
            <!-- card 3 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="sm:w-1/3 flex items-center">
                  <i class="fa-solid fa-arrows-rotate text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200 text-end">
                   <h2 class="text-sm md:text-base text-slate-400">Total Pemilahan</h2>
                   <h1 class="text-3xl font-medium text-emerald-400"><?php echo $total_pemilahan; ?></h1>
                </div>
            </div>
            
            <!-- card 4 -->
            <div class="card bg-slate-800 h-24 rounded-xl p-3 lg:py-3 lg:px-2 flex border border-emerald-500/20 hover:border-emerald-500/40 hover:-translate-y-1 lg:hover:-translate-y-2 transition-all duration-200">
               <div class="sm:w-1/3 flex items-center">
                  <i class="fa-solid fa-truck text-4xl text-emerald-400"></i>
               </div>
                <div class="text-slate-200 text-end">
                   <h2 class="text-sm md:text-base text-slate-400">Perlu Dikosongkan</h2>
                   <h1 class="text-3xl font-medium text-emerald-400"><?php echo $perlu_dikosongkan; ?></h1>
                </div>
            </div>
         </div>
         
         <!-- Header Section -->
         <div class="mb-5">
            <div class="flex items-center gap-3 mb-2">
               <svg class="w-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="#10b981" viewBox="0 0 22 21">
                  <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                  <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
               </svg>
               <h1 class="text-4xl font-bold text-slate-200">Dashboard</h1>
            </div>
            <p class="mb-2 text-lg text-emerald-400 font-medium"><?php echo $result['device_name']; ?></p>
            <div class="w-full h-0.5 bg-gradient-to-r from-emerald-500 to-transparent rounded-full"></div>
         </div>

         <!-- Konten 2 - Chart -->
         <div class="w-full bg-slate-800 border border-emerald-500/20 rounded-xl overflow-hidden p-4 mb-5 shadow-xl">
            <!-- Filter Buttons -->
            <div class="flex gap-2 mb-4 justify-end">
               <button onclick="updateChart('day')" id="btn-day" class="flex flex-wrap items-center justify-center lg:gap-1 filter-btn px-4 py-2 rounded-lg font-medium transition-all duration-200 bg-slate-700 text-slate-300 hover:bg-slate-600">
                     <i class="fa-solid fa-calendar-day"></i> Hari
               </button>
               <button onclick="updateChart('week')" id="btn-week" class="filter-btn px-4 py-2 rounded-lg font-medium transition-all duration-200 bg-slate-700 text-slate-300 hover:bg-slate-600">
                     <i class="fa-solid fa-calendar-week"></i> Minggu
               </button>
               <button onclick="updateChart('year')" id="btn-year" class="filter-btn px-4 py-2 rounded-lg font-medium transition-all duration-200 bg-slate-700 text-slate-300 hover:bg-slate-600">
                     <i class="fa-solid fa-calendar-days"></i> Tahun
               </button>
            </div>
            
            <!-- Chart Canvas -->
            <div class="w-full h-80">
               <canvas id="myChart" class="w-full h-full block"></canvas>
            </div>
         </div>

        
        <!-- Konten 3 - Gauge Charts -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 lg:gap-5">
            
            
            <!-- Organik Gauge -->
            <div class="bg-slate-800 border border-emerald-500/20 text-white p-4 h-[200px] lg:h-[240px] rounded-xl shadow-xl hover:border-emerald-500/40 transition-all duration-200">
               <div class="flex items-center gap-2 mb-3">
                  <div class="w-8 h-8 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                     <i class="fa-solid fa-leaf text-emerald-400"></i>
                  </div>
                  <h1 class="font-semibold text-lg text-slate-200">Organik</h1>
               </div>
               <div class="card text-center">
                  <div class="gauge">
                     <div class="gauge__body">
                        <div class="gauge__fill" id="rotasi_organik"></div>
                        <div class="gauge__cover text-white" id="kapasitas_organik"></div>
                     </div>
                  </div>
               </div>
            </div>
            
            <!-- Anorganik Gauge -->
            <div class="bg-slate-800 border border-emerald-500/20 text-white p-4 h-[200px] lg:h-[240px] rounded-xl shadow-xl hover:border-emerald-500/40 transition-all duration-200">
               <div class="flex items-center gap-2 mb-3">
                  <div class="w-8 h-8 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                     <i class="fa-solid fa-bottle-water text-emerald-400"></i>
                  </div>
                  <h1 class="font-semibold text-lg text-slate-200">Anorganik</h1>
               </div>
               <div class="card text-center">
                  <div class="gauge">
                     <div class="gauge__body">
                        <div class="gauge__fill" id="rotasi_anorganik"></div>
                        <div class="gauge__cover text-white" id="kapasitas_anorganik"></div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Logam Gauge -->
            <div class="bg-slate-800 border border-emerald-500/20 text-white p-4 h-[200px] lg:h-[240px] rounded-xl shadow-xl hover:border-emerald-500/40 transition-all duration-200">
               <div class="flex items-center gap-2 mb-3">
                  <div class="w-8 h-8 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                     <i class="fa-solid fa-magnet text-emerald-400"></i>
                  </div>
                  <h1 class="font-semibold text-lg text-slate-200">Logam</h1>
               </div>
               <div class="card text-center">
                  <div class="gauge">
                     <div class="gauge__body">
                        <div class="gauge__fill" id="rotasi_logam"></div>
                        <div class="gauge__cover text-white" id="kapasitas_logam"></div>
                     </div>
                  </div>
               </div>
            </div>
            
        </div>
   </div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="./js/jquery.js"></script>
<script>
      const ctx = document.getElementById('myChart');
      let myChart;

      // Inisialisasi chart
      function initChart(chartData, filter) {
         if (myChart) myChart.destroy();

         // jika filter 'day' => bar, kalau 'week' atau 'year' => line
         let chartType = (filter === 'day') ? 'bar' : 'line';

         myChart = new Chart(ctx, {
            type: chartType,
            data: {
                  labels: chartData.labels,
                  datasets: chartData.datasets
            },
            options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  interaction: { mode: 'index', intersect: false },
                  plugins: {
                     legend: {
                        display: true,
                        position: 'top',
                        labels: {
                              color: '#cbd5e1',
                              font: { size: 13, weight: '500' },
                              padding: 15,
                              usePointStyle: true,
                              boxWidth: 8,
                              boxHeight: 8
                        }
                     },
                     tooltip: {
                        backgroundColor: 'rgba(15,23,42,0.95)',
                        titleColor: '#10b981',
                        bodyColor: '#cbd5e1',
                        borderColor: '#10b981',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                              label: function(context) {
                                 let label = context.dataset.label || '';
                                 if (label) label += ': ';
                                 label += context.parsed.y + ' item' + (context.parsed.y !== 1 ? 's' : '');
                                 return label;
                              },
                              footer: function(tooltipItems) {
                                 let total = 0;
                                 tooltipItems.forEach(item => total += item.parsed.y);
                                 return 'Total: ' + total + ' items';
                              }
                        },
                        footerColor: '#fbbf24'
                     },
                     title: {
                        display: true,
                        text: getTitleText(filter),
                        color: '#cbd5e1',
                        font: { size: 16, weight: 'bold' },
                        padding: { bottom: 20 }
                     }
                  },
                  scales: {
                     x: {
                        grid: { display: true, color: 'rgba(30,41,59,0.5)', drawBorder: false },
                        ticks: { padding: 8, color: '#cbd5e1', font: { size: 11 } }
                     },
                     y: {
                        beginAtZero: true,
                        grid: { display: true, color: 'rgba(30,41,59,0.5)', drawBorder: false },
                        ticks: { padding: 8, color: '#cbd5e1', font: { size: 11 }, stepSize: 1,
                              callback: function(value) { if (Number.isInteger(value)) return value; }
                        }
                     }
                  }
            }
         });
      }

      // Judul chart (hilangkan bulan case)
      function getTitleText(filter) {
         const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
         const today = new Date();
         const currentMonth = monthNames[today.getMonth()];
         const currentYear = today.getFullYear();

         switch(filter) {
            case 'day': return 'Timbulan Sampah Hari Ini';
            case 'week': return 'Timbulan Sampah Minggu Ini';
            case 'year': return 'Timbulan Sampah Tahun Ini';
            default: return 'Timbulan Sampah';
         }
      }

      // Update chart dan tombol aktif
      function updateChart(filter) {
         document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-emerald-500', 'text-white');
            btn.classList.add('bg-slate-700', 'text-slate-300');
         });

         const activeBtn = document.getElementById('btn-' + filter);
         if (activeBtn) {
            activeBtn.classList.remove('bg-slate-700', 'text-slate-300');
            activeBtn.classList.add('active', 'bg-emerald-500', 'text-white');
         }

         const chartContainer = document.getElementById('myChart').parentElement;
         chartContainer.style.opacity = '0.5';

         fetch('get_chart_data.php?filter=' + filter)
            .then(response => {
                  if (!response.ok) throw new Error('Network response not ok');
                  return response.json();
            })
            .then(result => {
                  chartContainer.style.opacity = '1';
                  if (result.error) { console.error('API Error:', result.error); alert('Error: ' + result.error); return; }
                  initChart(result, filter);
            })
            .catch(error => {
                  chartContainer.style.opacity = '1';
                  console.error('Fetch Error:', error);
                  if (myChart) myChart.destroy();
                  new Chart(ctx, {
                     type: 'bar',
                     data: { labels: ['Error'], datasets: [{ label: 'No Data', data: [0], backgroundColor: '#ef4444' }] },
                     options: { plugins: { title: { display: true, text: '❌ Gagal memuat data. Cek koneksi atau API.', color: '#ef4444' } } }
                  });
            });
      }

      // Default load
      document.addEventListener('DOMContentLoaded', function() {
         updateChart('day');
      });
</script>
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
   $(document).ready(function(){
      setInterval(() => {
         // LOAD DATA ORGANIK
         $('#kapasitas_organik').load('show_data.php?type=kapasitas_organik');
         $.get('show_data.php?type=rotasi_organik').done(function(datanya){
            let turns = datanya;
            $('#rotasi_organik').css('transform', `rotate(${turns}turn)`);
         });
         // LOAD DATA ANORGANIK
         $('#kapasitas_anorganik').load('show_data.php?type=kapasitas_anorganik');
         $.get('show_data.php?type=rotasi_anorganik').done(function(datanya){
            let turns = datanya;
            $('#rotasi_anorganik').css('transform', `rotate(${turns}turn)`);
         })
         // LOAD DATA LOGAM
         $('#kapasitas_logam').load('show_data.php?type=kapasitas_logam');
         $.get('show_data.php?type=rotasi_logam').done(function(datanya){
            let turns = datanya;
            $('#rotasi_logam').css('transform', `rotate(${turns}turn)`);
         });
      }, 2000);
   })
</script>
</body>
</html>