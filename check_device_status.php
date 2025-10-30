<?php 
    include 'koneksi.php';

    $queryStatus = "UPDATE tb_device d INNER JOIN tb_device_status ds ON d.device_id = ds.device_id SET d.status = 0 WHERE d.status = 1 AND ds.last_update < DATE_SUB(NOW(), INTERVAL 60 SECOND)";
    $result = mysqli_query($conn, $queryStatus);

    $queryShow = "SELECT * FROM tb_device";
    $sqlShow = mysqli_query($conn,$queryShow);
    $resultShow = mysqli_fetch_assoc($sqlShow);
    $ifStatus = $resultShow['status'];
    if($ifStatus == 0){
        $query = "UPDATE tb_device_status SET sensor_cam = 0, sensor_ultrasonic = 0, sensor_proximity = 0, servo = 0, lcd = 0, wifi_signal = 0 WHERE device_id = '{$resultShow['device_id']}'";
        $sql = mysqli_query($conn,$query);
    }

    mysqli_close($conn);
?>