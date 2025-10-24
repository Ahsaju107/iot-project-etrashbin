<?php 
    //agar JS tahu ini JSON.
    header('Content-Type: application/json');

    //konek ke database
    include 'kirimdata.php';

    //baca isi tabel
    $query = "SELECT * FROM tb_tongsampah";
    $sql = mysqli_query($conn, $query);
    $data = mysqli_fetch_array($sql);

    $persentaseSampah = $data['persen'];
    
   // 5. Output JSON saja
echo json_encode([
    'status'  => 'ok',
    'persen'  => $persentaseSampah
]);
?>