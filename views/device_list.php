<?php
   session_start();
    include '../proses.php';
    $query = "SELECT * FROM tb_device";
    $sql = mysqli_query($conn,$query);
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Device List</title>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
     <script src="https://cdn.tailwindcss.com"></script>
     <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    
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
            <a href="" class="nav-link flex items-center p-2 rounded-lg text-emerald-400 bg-emerald-500/10 group active">
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
            <a href="./pengaturan.html" class="nav-link flex items-center p-2 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 group">
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
            <button type="button" class="flex w-full items-center p-2 rounded-lg text-slate-300 hover:bg-red-500/10 hover:text-red-400 group">
               <i class="fa-solid fa-right-to-bracket"></i>
               <span class="ms-3">Log out</span>
            </button>
         </li>
        
      </ul>

   </div>
</aside>

<!-- KONTEN UTAMA -->
<div class="p-4 sm:ml-64">
   <div class="mt-14 sm:mt-0 rounded-lg">
        <!-- KONTEN 1 START -->
        <div class="mb-7 mt-3">
            <div class="flex items-center gap-3 mb-2">
                <i class="fa-classic fa-display text-emerald-400 text-3xl"></i>
                <h1 class="text-4xl text-slate-200 font-bold">Device List</h1>
            </div>
            <span class="w-full h-0.5 bg-gradient-to-r from-emerald-500 to-transparent rounded-full block"></span>
        </div>
        
        <div class="flex flex-wrap justify-between gap-3 mb-7">
            <a href="./kelola_device.php" class="text-white bg-emerald-500 hover:bg-emerald-600 font-semibold rounded-lg p-3 w-full md:w-44 transition-all duration-200 flex items-center justify-center gap-2 shadow-lg hover:shadow-emerald-500/20">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Device</span>
            </a>
            <!-- Search Input -->
            <div class="w-full md:w-72 py-2 flex items-center bg-slate-800 border border-emerald-500/20 rounded-lg shadow-lg overflow-hidden px-3 gap-2 hover:border-emerald-500/40 transition-all duration-200">
                <i class="fa-solid fa-search text-emerald-400 text-xl"></i>
                <input type="text" placeholder="Cari device..." class="w-full h-full bg-transparent text-slate-200 placeholder:text-slate-500 outline-none">
            </div>
        </div>
        <!-- KONTEN 1 END -->

        <!-- KONTEN 2 START -->
        <div class="grid-list grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <?php
                while($result = mysqli_fetch_assoc($sql)){
            ?>
            <!-- ITEM 2 (Offline Example) -->
            <div class="grid-item bg-slate-800 border border-emerald-500/20 rounded-xl p-4 hover:ring-2 hover:ring-emerald-500/50 hover:-translate-y-1 transition-all duration-200 shadow-lg hover:border-emerald-500/40 group">
               <form action="../proses.php" method="POST" class="flex justify-center">
                  <input type="hidden" value="<?php echo $result['device_id']; ?>" name="device_id">
                  <button type="submit" name="aksi" value="device" class="block w-full">
                       <div class="w-16 h-16 mx-auto mb-3 bg-emerald-500/20 rounded-full flex items-center justify-center group-hover:bg-emerald-500/30 transition-all duration-200">
                           <img src="../images/trashbin-icon.png" alt="trashbin-icon" class="w-6">
                       </div>
                       <h1 class="text-center font-bold text-xl mb-2 text-slate-200"><?php echo $result['device_name'] ?></h1>
                       <?php if($result['is_active'] == 1){ ?>
                       <p class="text-center bg-emerald-500/20 text-emerald-400 py-1.5 px-3 font-medium rounded-full mb-3 text-sm border border-emerald-500/30">
                           <i class="fa-solid fa-circle text-xs animate-pulse"></i> Online
                       </p>
                       <?php 
                       } else { 
                       ?>
                       <p class="text-center bg-slate-700/50 text-slate-400 py-1.5 px-3 font-medium rounded-full mb-3 text-sm border border-slate-600">
                           <i class="fa-solid fa-circle text-xs"></i> Offline
                       </p>
                       <?php
                        } 
                       ?>
                   </button>
               </form> 

                <div class="flex justify-center gap-4 pt-3 border-t border-slate-700">
                    <a href="./kelola_device.php?ubah=<?php echo $result['device_id']; ?>" class="text-slate-400 hover:text-emerald-400 transition-colors duration-200">
                        <i class="fa-solid fa-edit text-lg"></i>
                    </a>
                    <a href="../proses.php?hapus_device=<?php echo $result['device_id']; ?>" class="text-slate-400 hover:text-red-400 transition-colors duration-200">
                        <i class="fa-solid fa-trash text-lg"></i>
                    </a>
                </div>
            </div>
            <!-- ITEM END -->
             <?php
              } 
             ?>
            
            
        <!-- KONTEN 2 END -->
        
   </div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>
</html>