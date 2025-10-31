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

    if(isset($_GET['type'])){
        // KAPASITAS ORGANIK
        if($_GET['type'] == 'kapasitas_organik'){
            echo $resultData['kapasitas_organik'].'%';
        } elseif($_GET['type'] == 'rotasi_organik'){
            echo $rotasi_organik;
        } 
        // KAPASITAS ANORGANIK
        elseif($_GET['type'] == 'kapasitas_anorganik'){
            echo $resultData['kapasitas_anorganik'].'%';
        } elseif($_GET['type'] == 'rotasi_anorganik'){
            echo $rotasi_anorganik;
        }

        // KAPASITAS LOGAM
        elseif($_GET['type'] == 'kapasitas_logam'){
            echo $resultData['kapasitas_logam'].'%';
        } elseif($_GET['type'] == 'rotasi_logam'){
            echo $rotasi_logam;
        }
    }

?>