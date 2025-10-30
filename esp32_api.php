<?php 
    include 'koneksi.php';

    // Set header untuk json response
    header('Content-Type: application/json');

    // cek apakah request method POST
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        echo json_encode([
            'status' => 'error',
            'message' => 'method tidak diizinkan. gunakan POST'
        ]);
    exit();
    }

    // Ambil data dari esp32
    $device_id = isset($_POST['device_id']) ? mysqli_real_escape_string($conn, $_POST['device_id']) : '';
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // validasi device id
    if(empty($device_id)){
        echo json_encode([
            'status' => 'error',
            'message' => 'device_id dibutuhkan'
        ]);
    exit();
    }

    // cek apakah device_id ada di database
    $check_device = mysqli_query($conn, "SELECT device_id FROM tb_device WHERE device_id = '$device_id'");
    if(mysqli_num_rows($check_device) == 0){
        echo json_encode([
            'status' => 'error',
            'message' => 'device_id tidak terdaftar'
        ]);
        exit();
    }

    // ==========================================
    // ACTION: UPDATE STATUS DEVICE (HEARTBEAT)  
    // ==========================================
    if($action == 'heartbeat'){
        $wifi_signal = isset($_POST['wifi_signal']) ? (int)$_POST['wifi_signal'] : 0;

        // Update status device menjadi online
        $update_device = mysqli_query($conn, "UPDATE tb_device SET status=1 WHERE device_id = '$device_id'");

        // Update device status
        $update_status = mysqli_query($conn, "UPDATE tb_device_status SET wifi_signal = '$wifi_signal', last_update = NOW() WHERE device_id = '$device_id'");

        if($update_device && $update_status){
            echo json_encode([
                'status' => 'success',
                'message' => 'data berhasil diupdate',
                'device_id' => $device_id
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'gagal update'
            ]);
        }
    }
    // ==========================================
    // ACTION: UPDATE SENSOR STATUS  
    // ==========================================
   elseif ($action == 'update_sensors') {
        $sensor_cam = isset($_POST['sensor_cam']) ? (int)$_POST['sensor_cam'] : 0;
        $sensor_ultrasonic = isset($_POST['sensor_ultrasonic']) ? (int)$_POST['sensor_ultrasonic'] : 0;
        $sensor_proximity = isset($_POST['sensor_proximity']) ? (int)$_POST['sensor_proximity'] : 0;
        $servo = isset($_POST['servo']) ? (int)$_POST['servo'] : 0;
        $lcd = isset($_POST['lcd']) ? (int)$_POST['lcd'] : 0;

        $query = "UPDATE tb_device_status
         SET sensor_cam = '$sensor_cam',
          sensor_ultrasonic = '$sensor_ultrasonic',
          sensor_proximity = '$sensor_proximity',
          servo = '$servo', 
          lcd = '$lcd' 
          WHERE device_id = '$device_id'";
        $sql = mysqli_query($conn,$query);
        if($sql){
            echo json_encode([
                'status' => 'success',
                'message' => 'sensor berhasil diperbarui',
                'device_id' => $device_id
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'sensor gagal diperbarui'
            ]);
        }
    }
    // ===========================================================
    // ACTION: KAPASITAS TEMPAT SAMPAH (ORGANIK, ANORGANIK, LOGAM)  
    // ===========================================================
    elseif($action == 'update_kapasitas'){
        $kapasitas_organik = isset($_POST['kapasitas_organik']) ? (int)$_POST['kapasitas_organik'] : 0;
        $kapasitas_anorganik = isset($_POST['kapasitas_anorganik']) ? (int)$_POST['kapasitas_anorganik'] : 0;
        $kapasitas_logam = isset($_POST['kapasitas_logam']) ? (int)$_POST['kapasitas_logam'] : 0;

        $query = "UPDATE tb_device_status SET kapasitas_organik = '$kapasitas_organik', kapasitas_anorganik = '$kapasitas_anorganik', kapasitas_logam = '$kapasitas_logam' WHERE device_id = '$device_id'";
        $sql = mysqli_query($conn,$query);
        if($sql){
            echo json_encode([
                'status' => 'success',
                'message' => 'kapasitas sukses diperbarui',
                'device_id' => $device_id
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'kapasitas gagal diperbarui'
            ]);
        }
    }
    // ===========================================================
    // ACTION:   CATAT SORTING (Simpan ke history)
    // ===========================================================
    elseif($action == 'add_sorting'){
        $jenis_sampah = isset($_POST['jenis_sampah']) ? mysqli_escape_string($conn,$_POST['jenis_sampah']) : '';

        // Validasi jenis sampah
        if(!in_array($jenis_sampah, ['organik', 'anorganik', 'logam'])){
            echo json_encode([
                'status' => 'error',
                'message' => 'jenis sampah tidak valid. gunakan: organik, anorganik, logam'
            ]);
        }

        // Insert history
        $insert_history = mysqli_query($conn, 
            "INSERT INTO tb_sorting_history (device_id,jenis_sampah,jumlah,tanggal) 
            VALUES('$device_id','$jenis_sampah',1,NOW())");
        
        // Update history 
        $update_history = mysqli_query($conn, 
        "UPDATE tb_device_status
         SET sorting_today = sorting_today + 1,
         last_update = NOW()
         WHERE device_id = '$device_id'");

        if($insert_history && $update_history){
            echo json_encode([
                'status' => 'success',
                'message' => 'data berhasil diperbarui',
                'device_id' => $device_id
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'data gagal diperbarui'
            ]);
        }
    }
    else {
        echo json_encode([
            'status' => 'error',
            'message' => 'action tidak valid. tersedia: heartbeat, add_sorting, update_kapasitas, update_device, update_sensors'
        ]);
    }

mysqli_close($conn);
?>