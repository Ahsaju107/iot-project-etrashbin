<?php
session_start();
 include '../koneksi.php';
   // CEK LOGIN
   if(!isset($_SESSION['id_user'])){
      header('location: ./login.php');
      exit();
   }

 if(isset($_GET['ubah'])){
   $id_user = $_GET['ubah'];
   $query = "SELECT * FROM tb_user WHERE id_user = '$id_user'";
   $sql = mysqli_query($conn,$query);
   $result = mysqli_fetch_assoc($sql);

   $username = $result['username'];
   $password = $result['password'];
 }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Manajemen Akun</title>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
     <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="../images/logo-iot.png" type="image/x-icon">

    
</head>
<body class="bg-slate-900">
    <nav class="bg-slate-800/95 backdrop-blur-sm flex sm:hidden p-3 justify-between items-center text-white w-full z-20 fixed border-b border-emerald-500/20">
      <img src="../images/logo_etrashbin_mobile.png" alt="logo logo_etrashbin" class="w-32">
    <button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar" type="button">
         <i class="fa-solid fa-bars-staggered text-lg text-emerald-400"></i>
      </button>
   </nav>
    <main>

<aside id="default-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
   <div class="h-full px-3 py-4 overflow-y-auto bg-slate-800 border-r border-emerald-500/20">
      <img src="../images/logo_etrashbin.png" alt="logo logo_etrashbin" class="mx-auto mb-2">
   <ul class="font-medium space-y-2">
         <!-- Dashboard -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <a href="../index.php" class="nav-link flex items-center p-2 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 group">
               <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                  <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                  <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
               </svg>
               <span class="ms-3">Dashboard</span>
            </a>
         </li>
         <!-- Device List -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <a href="./device_list.php" class="nav-link flex items-center p-2 rounded-lg text-slate-300 hover:text-emerald-400 hover:bg-emerald-500/10 group active">
               <i class="fa-solid fa-display"></i>
               <span class="ms-3">Device List</span>
            </a>
         </li>
         <!-- Device Status -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <a href="./status_perangkat.php" class="nav-link flex items-center p-2 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 group">
               <i class="fa-solid fa-circle-nodes"></i>
               <span class="ms-3">Status Perangkat</span>
            </a>
         </li>
         <!-- Settings -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <a href="./pengaturan.php" class="nav-link flex items-center p-2 rounded-lg bg-emerald-500/10 text-emerald-400 group">
               <i class="fa-solid fa-gear"></i>
               <span class="ms-3">Pengaturan</span>
            </a>
         </li>
         
         <!-- Logout -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <form action="../proses.php" method="post">
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
        <!-- KONTEN 1 -->
         <?php
            if(isset($_GET['ubah'])){
         ?>
         <section class="flex flex-wrap justify-center items-center w-full min-h-screen px-4">
            <div class="w-[400px] h-[500px] bg-slate-800 shadow drop-shadow-lg text-white text-center p-4">
                <h1 class="font-bold text-2xl mb-24 mt-10">Update <span class="text-emerald-500">Pengguna</span></h1>
                <form action="../proses.php" method="POST" class="w-full">
                     <input type="hidden" value="<?php echo $id_user;?>" name="id_user">
                    <div class="flex flex-wrap gap-1 mb-5">
                        <label for="edit_username" class="text-emerald-400">Nama:</label>
                        <input type="text" name="edit_username" id="edit_username" value="<?php echo $username; ?>" required class="w-full px-3 rounded-full h-8 focus:outline-none bg-slate-700/50 border border-emerald-500/25 shadow-sm focus:shadow-emerald-500">
                    </div>
                    <div class="flex flex-wrap gap-1 mb-10">
                        <label for="edit_password" class="text-emerald-400">Password:</label>
                        <input type="password" name="edit_password" id="edit_password" value="<?php echo $password; ?>" required class="w-full px-3 rounded-full h-8 focus:outline-none bg-slate-700/50 border border-emerald-500/25 shadow-sm focus:shadow-emerald-500">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" name="aksi" value="edit_user" class="bg-emerald-500 w-1/2 h-9 rounded flex gap-1 items-center justify-center text-white font-semibold border border-emerald-500 hover:text-emerald-400 hover:bg-transparent hover:shadow-sm hover:shadow-emerald-500"><i class="fa-solid fa-floppy-disk"></i>Update</button>
                        <a href="./kelola_akun.php" class="bg-red-500 w-1/2 h-9 flex items-center justify-center text-white font-semibold gap-1 rounded hover:bg-transparent border border-red-500 hover:text-red-500 hover:shadow-sm hover:shadow-red-500"><i class="fa-solid fa-reply"></i>Kembali</a>
                    </div>
                </form>
            </div>

         </section>
        <!-- KONTEN 1 END -->
         <?php
          } else {
         ?>
<section class="flex flex-wrap justify-center items-center w-full min-h-screen px-4">
            <div class="w-[400px] h-[500px] bg-slate-800 shadow drop-shadow-lg text-white text-center p-4">
                <h1 class="font-bold text-2xl mb-24 mt-10">Tambah <span class="text-emerald-500">Pengguna</span></h1>
                <form action="../proses.php" method="POST" class="w-full">
                    <div class="flex flex-wrap gap-1 mb-5">
                        <label for="username" class="text-emerald-400">Nama:</label>
                        <input type="text" name="username" id="username" required class="w-full px-3 rounded-full h-8 focus:outline-none bg-slate-700/50 border border-emerald-500/25 shadow-sm focus:shadow-emerald-500">
                    </div>
                    <div class="flex flex-wrap gap-1 mb-10">
                        <label for="password" class="text-emerald-400">Password:</label>
                        <input type="password" name="password" id="password" required class="w-full px-3 rounded-full h-8 focus:outline-none bg-slate-700/50 border border-emerald-500/25 shadow-sm focus:shadow-emerald-500">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" name="aksi" value="add_user" class="bg-emerald-500 w-1/2 h-9 rounded flex gap-1 items-center justify-center text-white font-semibold border border-emerald-500 hover:text-emerald-400 hover:bg-transparent hover:shadow-sm hover:shadow-emerald-500"><i class="fa-solid fa-floppy-disk"></i>Tambah</button>
                        <a href="./kelola_akun.php" class="bg-red-500 w-1/2 h-9 flex items-center justify-center text-white font-semibold gap-1 rounded hover:bg-transparent border border-red-500 hover:text-red-500 hover:shadow-sm hover:shadow-red-500"><i class="fa-solid fa-reply"></i>Kembali</a>
                    </div>
                </form>
            </div>

         </section>
         <?php
          } 
         ?>
   </div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>
</html>