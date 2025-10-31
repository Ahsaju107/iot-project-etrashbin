<?php
session_start();
include './koneksi.php';

// Cek login
if(!isset($_SESSION['id_user']) || empty($_SESSION['device_id'])){
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Ambil parameter filter (day, week, month, year)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'day';
$device_id = mysqli_real_escape_string($conn, $_SESSION['device_id']);

// Set header untuk JSON response
header('Content-Type: application/json');

// ==========================================
// FILTER: HARI INI (Per Jenis Sampah)
// ==========================================
if ($filter == 'day') {
    $query = "SELECT 
        jenis_sampah,
        SUM(jumlah) as total
        FROM tb_sorting_history 
        WHERE device_id = '$device_id' 
        AND DATE(tanggal) = CURDATE()
        GROUP BY jenis_sampah";
    
    $sql = mysqli_query($conn, $query);
    
    $data = [
        'logam' => 0,
        'organik' => 0,
        'anorganik' => 0
    ];
    
    if($sql){
        while($row = mysqli_fetch_assoc($sql)){
            $data[$row['jenis_sampah']] = (int)$row['total'];
        }
    }
    
    // Ubah urutan menjadi: Organik, Anorganik, Logam
    echo json_encode([
        'labels' => ['Organik', 'Anorganik', 'Logam'],
        'datasets' => [
            [
                'label' => 'Hari Ini',
                'data' => [$data['organik'], $data['anorganik'], $data['logam']],
                'backgroundColor' => ['#10b981', '#3b82f6', '#f59e0b'],
                'borderColor' => ['#10b981', '#3b82f6', '#f59e0b'],
                'borderWidth' => 3
            ]
        ],
        'filter' => 'day'
    ]);
}

// ==========================================
// FILTER: MINGGU INI (Senin - Minggu)
// ==========================================
else if ($filter == 'week') {
    // Array hari dalam bahasa Indonesia
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    
    // Dapatkan hari pertama minggu ini (Senin)
    $start_of_week = date('Y-m-d', strtotime('monday this week'));
    
    // Inisialisasi data untuk 7 hari
    $weekData = [];
    $labels = [];
    
    for ($i = 0; $i < 7; $i++) {
        $current_date = date('Y-m-d', strtotime($start_of_week . " +$i days"));
        $day_name = $hari[date('w', strtotime($current_date))];
        $labels[] = $day_name;
        
        // Query untuk setiap hari
        $query = "SELECT 
            SUM(CASE WHEN jenis_sampah = 'logam' THEN jumlah ELSE 0 END) as logam,
            SUM(CASE WHEN jenis_sampah = 'organik' THEN jumlah ELSE 0 END) as organik,
            SUM(CASE WHEN jenis_sampah = 'anorganik' THEN jumlah ELSE 0 END) as anorganik
            FROM tb_sorting_history 
            WHERE device_id = '$device_id' 
            AND DATE(tanggal) = '$current_date'";
        
        $sql = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($sql);
        
        $weekData[$i] = [
            'logam' => (int)($row['logam'] ?? 0),
            'organik' => (int)($row['organik'] ?? 0),
            'anorganik' => (int)($row['anorganik'] ?? 0)
        ];
    }
    
    // Prepare datasets untuk Chart.js
    $logam_data = array_column($weekData, 'logam');
    $organik_data = array_column($weekData, 'organik');
    $anorganik_data = array_column($weekData, 'anorganik');
    
    // Urutan dataset: Organik, Anorganik, Logam
    echo json_encode([
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Organik',
                'data' => $organik_data,
                'backgroundColor' => '#10b981',
                'borderColor' => '#10b981',
                'borderWidth' => 2,
                'tension' => 0.4
            ],
            [
                'label' => 'Anorganik',
                'data' => $anorganik_data,
                'backgroundColor' => '#3b82f6',
                'borderColor' => '#3b82f6',
                'borderWidth' => 2,
                'tension' => 0.4
            ],
            [
                'label' => 'Logam',
                'data' => $logam_data,
                'backgroundColor' => '#f59e0b',
                'borderColor' => '#f59e0b',
                'borderWidth' => 2,
                'tension' => 0.4
            ]
        ],
        'filter' => 'week'
    ]);
}

// ==========================================
// FILTER: BULAN INI (Per Tanggal 1-31)
// ==========================================
else if ($filter == 'month') {
    $current_month = date('Y-m');
    $days_in_month = date('t'); // Jumlah hari dalam bulan ini
    
    $monthData = [];
    $labels = [];
    
    for ($day = 1; $day <= $days_in_month; $day++) {
        $current_date = $current_month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
        $labels[] = $day; // Label: 1, 2, 3, ... 31
        
        // Query untuk setiap tanggal
        $query = "SELECT 
            SUM(CASE WHEN jenis_sampah = 'logam' THEN jumlah ELSE 0 END) as logam,
            SUM(CASE WHEN jenis_sampah = 'organik' THEN jumlah ELSE 0 END) as organik,
            SUM(CASE WHEN jenis_sampah = 'anorganik' THEN jumlah ELSE 0 END) as anorganik
            FROM tb_sorting_history 
            WHERE device_id = '$device_id' 
            AND DATE(tanggal) = '$current_date'";
        
        $sql = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($sql);
        
        $monthData[$day - 1] = [
            'logam' => (int)($row['logam'] ?? 0),
            'organik' => (int)($row['organik'] ?? 0),
            'anorganik' => (int)($row['anorganik'] ?? 0)
        ];
    }
    
    // Prepare datasets
    $logam_data = array_column($monthData, 'logam');
    $organik_data = array_column($monthData, 'organik');
    $anorganik_data = array_column($monthData, 'anorganik');
    
    // Urutan dataset: Organik, Anorganik, Logam
    echo json_encode([
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Organik',
                'data' => $organik_data,
                'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                'borderColor' => '#10b981',
                'borderWidth' => 2,
                'tension' => 0.4,
                'fill' => true
            ],
            [
                'label' => 'Anorganik',
                'data' => $anorganik_data,
                'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                'borderColor' => '#3b82f6',
                'borderWidth' => 2,
                'tension' => 0.4,
                'fill' => true
            ],
            [
                'label' => 'Logam',
                'data' => $logam_data,
                'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                'borderColor' => '#f59e0b',
                'borderWidth' => 2,
                'tension' => 0.4,
                'fill' => true
            ]
        ],
        'filter' => 'month'
    ]);
}

// ==========================================
// FILTER: TAHUN INI (Januari - Desember)
// ==========================================
else if ($filter == 'year') {
    // Array nama bulan
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $current_year = date('Y');
    $yearData = [];
    $labels = [];
    
    // Loop untuk 12 bulan
    for ($month = 1; $month <= 12; $month++) {
        $labels[] = $bulan[$month];
        
        // Query untuk setiap bulan
        $query = "SELECT 
            SUM(CASE WHEN jenis_sampah = 'logam' THEN jumlah ELSE 0 END) as logam,
            SUM(CASE WHEN jenis_sampah = 'organik' THEN jumlah ELSE 0 END) as organik,
            SUM(CASE WHEN jenis_sampah = 'anorganik' THEN jumlah ELSE 0 END) as anorganik
            FROM tb_sorting_history 
            WHERE device_id = '$device_id' 
            AND YEAR(tanggal) = '$current_year'
            AND MONTH(tanggal) = '$month'";
        
        $sql = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($sql);
        
        $yearData[$month - 1] = [
            'logam' => (int)($row['logam'] ?? 0),
            'organik' => (int)($row['organik'] ?? 0),
            'anorganik' => (int)($row['anorganik'] ?? 0)
        ];
    }
    
    // Prepare datasets
    $logam_data = array_column($yearData, 'logam');
    $organik_data = array_column($yearData, 'organik');
    $anorganik_data = array_column($yearData, 'anorganik');
    
    echo json_encode([
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Organik',
                'data' => $organik_data,
                'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                'borderColor' => '#10b981',
                'borderWidth' => 3,
                'tension' => 0.4,
                'fill' => true
            ],
            [
                'label' => 'Anorganik',
                'data' => $anorganik_data,
                'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                'borderColor' => '#3b82f6',
                'borderWidth' => 3,
                'tension' => 0.4,
                'fill' => true
            ],
            [
                'label' => 'Logam',
                'data' => $logam_data,
                'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                'borderColor' => '#f59e0b',
                'borderWidth' => 3,
                'tension' => 0.4,
                'fill' => true
            ]
        ],
        'filter' => 'year'
    ]);
}

mysqli_close($conn);
?>
