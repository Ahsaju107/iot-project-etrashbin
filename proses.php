<?php 
    include 'koneksi.php';

    if(isset($_POST['aksi'])){
    // FUNGSI LOGIN
    if($_POST['aksi'] == 'login'){
        $username = $_POST['login_username'];
        $password = $_POST['login_password'];

        $query = "SELECT * FROM tb_user WHERE username='$username' AND password='$password'";
        $sql = mysqli_query($conn,$query);
        $result = mysqli_fetch_assoc($sql);
        if($sql){
            if($username == '' || $password == ''){
                header('location: ./views/login.php');
            } else {
                if($username == $result['username'] && $password == $result['password']){
                    session_start();
                    $_SESSION['id_user'] = $result['id_user'];
                    $_SESSION['username'] = $result['username'];
                    header('location: ./index.php');
                    exit();
                } else {
                    header('location: ./views/login.php');
                }
            }
        } else {
            echo "error: ".mysqli_error($conn);
        }
    }

    // FUNGSI UNTUK LOGOUT
    if($_POST['aksi'] == 'logout'){
        session_start();
        unset($_SESSION['id_user']);
        session_destroy();
        header('location: /trashbin/views/login.php');
    }


    // FUNGSI UNTUK MENUJU HALAMAN DASHBOARD DEVICE
    if($_POST['aksi'] == 'device'){
        $device_id = $_POST['device_id'];
        session_start();
        $_SESSION['device_id'] = $device_id;
        header('location: ./index.php');
    }

    // FUNGSI UNTUK MENAMBAHKAN USER
    if($_POST['aksi'] == 'add_user'){
        $username = $_POST['username'];
        $password = $_POST['password'];
        $query = "INSERT INTO tb_user (username,password) VALUES ('$username','$password')";
        $sql = mysqli_query($conn,$query);
        if($sql){
            header('location: ./views/kelola_akun.php');
        } else {
            echo "error: ".mysqli_error($conn);
        }
    }
    // FUNGSI UNTUK MENGEDIT DATA USER
    if($_POST['aksi'] == 'edit_user'){
        $id_user = $_POST['id_user'];
        $username = $_POST['edit_username'];
        $password = $_POST['edit_password'];
        $query = "UPDATE tb_user SET username='$username',password='$password' WHERE id_user='$id_user'";
        $sql = mysqli_query($conn,$query);
        if($sql){
            header('location: ./views/kelola_akun.php');
        } else {
            echo "error: ".mysqli_error($conn);
        }
    }
    // FUNGSI UNTUK MENAMBAHKAN DEVICE TONG SAMPAH
    if($_POST['aksi'] == 'add_device'){
        $device_name = $_POST['add_device_name'];
        $query = "INSERT INTO tb_device (device_name) VALUES ('$device_name')";
        $sql = mysqli_query($conn,$query);
        if($sql){
            header('location: ./views/device_list.php');
        } else {
            echo "error: ".mysqli_error($conn);
        }
    }
    // FUNGSI UNTUK MENGEDIT DATA DEVICE TONG SAMPAH
    if($_POST['aksi'] == 'edit_device'){
        $device_id = $_POST['device_id'];
        $device_name = $_POST['edit_device_name'];
        $query = "UPDATE tb_device SET device_name = '$device_name' WHERE device_id = '$device_id'";
        $sql = mysqli_query($conn,$query);
        if($sql){
            header('location: ./views/device_list.php');
        } else {
            echo "error: ".mysqli_error($conn);
        }
    }

    }
    // FUNGSI HAPUS AKUN
    if(isset($_GET['hapus_akun'])){
        $id_user = $_GET['hapus_akun'];
        $query = "DELETE FROM tb_user WHERE id_user = '$id_user'";
        $sql = mysqli_query($conn,$query);
        if($sql){
            header('location: ./views/kelola_akun.php');
        } else {
            echo "error: ".mysqli_error($conn);
        }
    }
    // FUNGSI HAPUS DATA DEVICE TONG SAMPAH
    if(isset($_GET['hapus_device'])){
        $device_id = $_GET['hapus_device'];
        $query = "DELETE FROM tb_device WHERE device_id = '$device_id'";
        $sql = mysqli_query($conn,$query);
        if($sql){
            header('location: ./views/device_list.php');
        } else {
            echo "error: ".mysqli_error($conn);
        }
    }


?>