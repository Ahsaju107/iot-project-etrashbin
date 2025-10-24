<?php
    //konek ke database
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $db = "dbtrashbinlevel";
    $conn = mysqli_connect($hostname, $username, $password, $db);

    //baca nilai persen
    $persen = $_GET['persen'];

    //simpan / update tabel tb_tongsampah
    mysqli_query($conn, "UPDATE tb_tongsampah SET persen='$persen'");
?>