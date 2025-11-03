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

    $sqlData = mysqli_query($conn,$query);
    $resultData = mysqli_fetch_assoc($sqlData);

    $rotasi_logam = ($resultData['kapasitas_logam'] / 100) * 0.5;
    $rotasi_organik = ($resultData['kapasitas_organik'] / 100) * 0.5;
    $rotasi_anorganik = ($resultData['kapasitas_anorganik'] / 100) * 0.5;

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
    };
    // Hitung selisih waktu last update
    $last_update = strtotime($resultData['last_update']);
    $current_time = time();
    $time_diff = $current_time - $last_update;

    if(isset($_GET['type'])){
        // KAPASITAS ORGANIK
        if($_GET['type'] == 'kapasitas_organik'){
            echo $resultData['kapasitas_organik'].'%';
            exit();
        } elseif($_GET['type'] == 'rotasi_organik'){
            echo $rotasi_organik;
            exit();
        } 
        // KAPASITAS ANORGANIK
        elseif($_GET['type'] == 'kapasitas_anorganik'){
            echo $resultData['kapasitas_anorganik'].'%';
            exit();
        } elseif($_GET['type'] == 'rotasi_anorganik'){
            echo $rotasi_anorganik;
            exit();
        }

        // KAPASITAS LOGAM
        elseif($_GET['type'] == 'kapasitas_logam'){
            echo $resultData['kapasitas_logam'].'%';
            exit();
        } elseif($_GET['type'] == 'rotasi_logam'){
            echo $rotasi_logam;
            exit();
        }
        // data total perangkat
        elseif($_GET['type'] == 'total_perangkat'){
            echo $total_perangkat;
            exit();
        }
        // Data total perangkat yang online
        elseif($_GET['type'] == 'total_online'){
            echo $total_online.'/'.$total_perangkat.' Online';
            exit();
        }
        // Data total pemilahan
        elseif($_GET['type'] == 'total_pemilahan'){
            echo $total_pemilahan;
            exit();
        }
        // Data device yang perlu dikosongkan
        elseif($_GET['type'] == 'perlu_dikosongkan'){
            echo $perlu_dikosongkan;
            exit();
        }
        // Data status device
        elseif($_GET['type'] == 'status_device'){
            $query = "SELECT * FROM tb_device";
            $sql = mysqli_query($conn,$query);
            $data = []; 
            while($result = mysqli_fetch_assoc($sql)){
                $data[] = [
                    'device' => $result['device_id'],
                    'status' => $result['status']
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } 
    
       
    }

?>

<?php if(isset($_GET['history'])){ ?>
    <head>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
     <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body>
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
    </body>
<?php } ?>
