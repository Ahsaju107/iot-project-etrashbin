<?php
    date_default_timezone_set('Asia/Jakarta');
    //konek ke database
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $db = "db_etrashbin";
    $conn = mysqli_connect($hostname, $username, $password, $db);

    
?>