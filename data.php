<?php 
    session_start();
    //agar JS tahu ini JSON.
    header('Content-Type: application/json');

    //konek ke database
    include 'koneksi.php';

    //baca isi tabel
    $query = "SELECT * FROM tb_device_status WHERE device_id = '{$_SESSION['device_id']}'";
    $sql = mysqli_query($conn, $query);
    $data = mysqli_fetch_array($sql);

    $kapasitasLogam = $data['kapasitas_logam'];
    $kapasitasOrganik = $data['kapasitas_organik'];
    $kapasitasAnorganik = $data['kapasitas_anorganik'];
    
   // 5. Output JSON saja
echo json_encode([
    'device_id' => $_SESSION['device_id'],
    'status'  => 'ok',
    'kapasitas_logam'  => $kapasitasLogam,
    'kapasitas_organik' => $kapasitasOrganik,
    'kapasitas_anorganik' => $kapasitasAnorganik
]);
?>