<?php 
   session_start();
   include '../koneksi.php';

   

   $query = "SELECT * FROM tb_user";
   $sql = mysqli_query($conn,$query);
   $nomor = 0;
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
      <h1 class="font-extrabold text-xl judul-dashboard text-emerald-400"><i>E-TrashBin</i></h1>
      <button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar" type="button">
         <i class="fa-solid fa-bars-staggered text-lg text-emerald-400"></i>
      </button>
   </nav>
    <main>

<aside id="default-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
   <div class="h-full px-3 py-4 overflow-y-auto bg-slate-800 border-r border-emerald-500/20">
      <h1 class="font-extrabold text-2xl text-emerald-400 mb-4 judul-dashboard text-center italic">E-TrashBin</h1>
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
         <!-- About -->
         <li class="hover:-translate-y-1 duration-100 transition-all">
            <a href="#" class="nav-link flex items-center p-2 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 group">
               <i class="fa-solid fa-circle-info"></i>
               <span class="ms-3">Tentang</span>
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
        <div class="flex text-white items-center gap-3 mb-8">
            <i class="fa-solid fa-users text-emerald-400 text-3xl"></i>
            <h1 class="text-3xl font-bold">Manajemen Akun</h1>
        </div>
        <!-- KONTEN 1 END -->

        <!-- KONTEN 2 -->
        <div class="flex flex-wrap justify-between gap-3 mb-6">
            <a href="./kelola_akun_action.php" class="flex items-center text-white bg-emerald-500 w-full md:w-48 h-12 justify-center rounded-lg p-2 font-semibold shadow drop-shadow-md hover:shadow-emerald-600 hover:bg-transparent border-0 border-emerald-500 hover:border hover:text-emerald-400"><i class="fa-solid fa-plus"></i>Tambah Data</a>
            <div class="flex w-full md:w-72 items-center bg-slate-700/50 rounded-lg px-4 border border-emerald-500/20 hover:border-emerald-500/50 shadow-sm hover:shadow-emerald-500/50">
                <i class="fa-solid fa-search text-lg text-emerald-500"></i>
                <input type="text" class="w-full h-full focus:outline-none bg-transparent py-3 px-4 text-white">
            </div>
        </div>
         <!-- KONTEN 2 END -->
        <!-- KONTEN 3 -->
         <div class="relative overflow-x-auto w-full border border-emerald-500/50 shadow-lg drop-shadow-lg rounded-lg"> 
            <table class="w-full text-white">
                <thead class="bg-emerald-500 text-slate-900">
                    <tr>
                        <th class="p-2">NO</th>
                        <th class="p-2">NAMA</th>
                        <th class="p-2">ACTION</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                  <?php
                     while($result = mysqli_fetch_assoc($sql)){
                  ?>
                    <tr class="hover:bg-slate-700/50">
                        <td class="p-2"><?php echo ++$nomor; ?></td>
                        <td class="p-2"><?php echo $result['username']; ?></td>
                        <td class="p-2 flex justify-center gap-2">
                            <a href="./kelola_akun_action.php?ubah=<?php echo $result['id_user']; ?>" class="bg-emerald-400 rounded-lg flex w-10 h-10 justify-center items-center hover:bg-transparent border hover:border border-emerald-500/50 shadow hover:shadow-emerald-500 group"><i class="fa-solid fa-edit text-slate-900 text-xl group-hover:text-emerald-500"></i></a>
                            <a href="../proses.php?hapus_akun=<?php echo $result['id_user']; ?>" class="bg-red-400 rounded-lg flex w-10 h-10 justify-center items-center hover:bg-transparent border hover:border border-red-400 shadow hover:shadow-red-500 group"><i class="fa-solid fa-trash text-slate-900 text-xl group-hover:text-red-500"></i></a>
                        </td>
                    </tr>
                    <?php
                     }
                    ?>
                    
                </tbody>
            </table>
         </div>
         <!-- KONTEN 3 END -->

   </div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>
</html>