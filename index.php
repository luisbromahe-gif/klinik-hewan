<?php
// 1. MENGAKTIFKAN SESSION DAN ERROR REPORTING
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. KONEKSI DATABASE
$host = "localhost";
$user = "root";
$pass = "";
$db   = "klinik_hewan";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("<div style='color:red; padding:20px; text-align:center;'><h3>Koneksi Database Gagal!</h3> Pastikan MySQL di XAMPP sudah dinyalakan dan database <u>$db</u> sudah dibuat. Error: " . $conn->connect_error . "</div>");
}

// 3. PROSES LOGOUT
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}

// 4. PROSES LOGIN
$error_login = "";
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; 

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['nama']     = $user_data['nama'];
        $_SESSION['role']     = $user_data['role'];
        $_SESSION['user_id']  = $user_data['id'];
        header("Location: index.php?page=dashboard");
        exit();
    } else {
        $error_login = "Username atau Password salah!";
    }
}

// 5. PROSES CRUD GLOBAL
if (isset($_SESSION['username'])) {
    
    // Tambah / Edit Pasien
    if (isset($_POST['save_pasien'])) {
        $nama_hewan  = $_POST['nama_hewan'];
        $jenis_hewan = $_POST['jenis_hewan'];
        $pemilik     = $_POST['pemilik'];
        $telepon     = $_POST['telepon'];
        $alamat      = $_POST['alamat'];
        
        if ($_POST['id'] == "") {
            $conn->query("INSERT INTO pasien_hewan (nama_hewan, jenis_hewan, pemilik, telepon, alamat) VALUES ('$nama_hewan', '$jenis_hewan', '$pemilik', '$telepon', '$alamat')");
        } else {
            $id = $_POST['id'];
            $conn->query("UPDATE pasien_hewan SET nama_hewan='$nama_hewan', jenis_hewan='$jenis_hewan', pemilik='$pemilik', telepon='$telepon', alamat='$alamat' WHERE id=$id");
        }
        header("Location: index.php?page=pasien");
        exit();
    }

    // Hapus Pasien
    if (isset($_GET['del_pasien'])) {
        $conn->query("DELETE FROM pasien_hewan WHERE id=" . $_GET['del_pasien']);
        header("Location: index.php?page=pasien");
        exit();
    }

    // Tambah / Edit Obat
    if (isset($_POST['save_obat'])) {
        $nama_obat = $_POST['nama_obat'];
        $stok      = $_POST['stok'];
        $harga     = $_POST['harga'];
        
        if ($_POST['id'] == "") {
            $conn->query("INSERT INTO obat_hewan (nama_obat, stok, harga) VALUES ('$nama_obat', '$stok', '$harga')");
        } else {
            $id = $_POST['id'];
            $conn->query("UPDATE obat_hewan SET nama_obat='$nama_obat', stok='$stok', harga='$harga' WHERE id=$id");
        }
        header("Location: index.php?page=obat");
        exit();
    }

    // Hapus Obat
    if (isset($_GET['del_obat'])) {
        $conn->query("DELETE FROM obat_hewan WHERE id=" . $_GET['del_obat']);
        header("Location: index.php?page=obat");
        exit();
    }

    // Tambah Transaksi Pembayaran
    if (isset($_POST['save_pembayaran'])) {
        $pasien_id     = $_POST['pasien_id'];
        $total_bayar   = $_POST['total_bayar'];
        $tanggal_bayar = date('Y-m-d');
        $status        = $_POST['status'];
        
        $conn->query("INSERT INTO pembayaran (pasien_id, total_bayar, tanggal_bayar, status) VALUES ('$pasien_id', '$total_bayar', '$tanggal_bayar', '$status')");
        header("Location: index.php?page=pembayaran");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI Klinik Hewan Kemiri Sentani</title>
    
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; color: #333; line-height: 1.6; }
        
        /* Header */
        .header { background: linear-gradient(135deg, #1e3c72, #2a5298); color: white; padding: 30px 20px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header h2 { font-size: 26px; letter-spacing: 1px; }
        .header h3 { font-size: 16px; font-weight: 300; opacity: 0.9; margin-top: 5px; }
        
        /* Navigasi */
        .nav { background-color: #2c3e50; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .nav-links { display: flex; }
        .nav a { color: #f5f6fa; padding: 15px 20px; text-decoration: none; font-weight: 500; font-size: 15px; transition: background 0.3s; }
        .nav a:hover, .nav a.active { background-color: #34495e; color: #3498db; border-bottom: 3px solid #3498db; }
        .logout-btn { color: #e74c3c !important; font-weight: bold; }
        .logout-btn:hover { background-color: #c0392b !important; color: white !important; border: none !important; }
        
        /* Container Konten */
        .container { padding: 30px; max-width: 1200px; margin: 30px auto; background: white; min-height: 450px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-radius: 8px; }
        
        /* Form Login */
        .login-container { display: flex; justify-content: center; align-items: center; min-height: 70vh; }
        .login-box { width: 100%; max-width: 400px; padding: 35px; background: white; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #3498db; }
        .login-box h3 { text-align: center; margin-bottom: 25px; color: #2c3e50; font-size: 22px; }
        
        /* Form Element */
        label { display: block; margin-top: 15px; margin-bottom: 5px; font-weight: 600; color: #4a5568; font-size: 14px; }
        input[type="text"], input[type="password"], input[type="number"], select, textarea { width: 100%; padding: 12px; margin-bottom: 5px; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 14px; background-color: #fff; transition: border 0.3s; }
        input:focus, select:focus, textarea:focus { border-color: #3498db; outline: none; box-shadow: 0 0 5px rgba(52,152,219,0.3); }
        
        /* Tombol */
        button, .btn { background-color: #2ecc71; color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: bold; text-decoration: none; display: inline-block; text-align: center; transition: background 0.3s; }
        button:hover { background-color: #27ae60; }
        .btn-danger { background-color: #e74c3c; }
        .btn-danger:hover { background-color: #c0392b; }
        .btn-warning { background-color: #f39c12; }
        .btn-warning:hover { background-color: #d35400; }
        
        /* Tabel */
        table { width: 100%; border-collapse: collapse; margin-top: 25px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-radius: 6px; overflow: hidden; }
        table th, table td { padding: 14px 18px; text-align: left; font-size: 14px; }
        table th { background-color: #f8f9fa; color: #2c3e50; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        table tr { border-bottom: 1px solid #edf2f7; }
        table tr:nth-child(even) { background-color: #fcfcfc; }
        table tr:hover { background-color: #f1f5f9; }
        
        /* Alert/Notifikasi */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 6px; font-size: 14px; font-weight: 500; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* Badge */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; color: white; font-weight: bold; text-transform: uppercase; }
        .bg-success { background-color: #2ecc71; }
        .bg-warning { background-color: #f1c40f; color: #333; }
        
        /* Grid Layout */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
        /* Footer */
        .footer { text-align: center; color: #7f8c8d; margin-top: 60px; padding: 25px; font-size: 13px; border-top: 1px solid #dcdde1; }
    </style>
</head>
<body>

<div class="header">
    <h2>SISTEM INFORMASI KLINIK HEWAN</h2>
    <h3>Klinik Hewan Kemiri Sentani</h3>
</div>

<?php if (!isset($_SESSION['username'])): ?>
    <div class="login-container">
        <div class="login-box">
            <h3>LOGIN SISTEM</h3>
            <?php if($error_login != "") echo "<div class='alert alert-danger'>$error_login</div>"; ?>
            <form action="index.php" method="POST">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Masukkan username Anda...">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Masukkan password Anda...">
                <button type="submit" name="login" style="width:100%; margin-top:20px;">Masuk Aplikasi</button>
            </form>
            <div style="font-size: 11px; color: #7f8c8d; margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 15px; line-height: 1.5;">
                <strong>Akun Demo Uji Coba:</strong><br>
                • Admin: <code>admin</code> / <code>admin123</code><br>
                • Dokter Hewan: <code>dokter</code> / <code>dokter123</code><br>
                • Direktur: <code>direktur</code> / <code>direktur123</code>
            </div>
        </div>
    </div>

<?php else: ?>
    <?php $current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; ?>
    <div class="nav">
        <div class="nav-links">
            <a href="index.php?page=dashboard" class="<?= $current_page=='dashboard'?'active':'' ?>">Dashboard</a>
            <?php if ($_SESSION['role'] == 'Admin'): ?>
                <a href="index.php?page=pasien" class="<?= $current_page=='pasien'?'active':'' ?>">Kelola Pasien</a>
                <a href="index.php?page=obat" class="<?= $current_page=='obat'?'active':'' ?>">Kelola Obat</a>
                <a href="index.php?page=pembayaran" class="<?= $current_page=='pembayaran'?'active':'' ?>">Kasir & Pembayaran</a>
            <?php elseif ($_SESSION['role'] == 'Dokter Hewan'): ?>
                <a href="index.php?page=rekam_medis" class="<?= $current_page=='rekam_medis'?'active':'' ?>">Input Rekam Medis</a>
            <?php elseif ($_SESSION['role'] == 'Direktur Klinik'): ?>
                <a href="index.php?page=laporan" class="<?= $current_page=='laporan'?'active':'' ?>">Laporan Klinik</a>
            <?php endif; ?>
        </div>
        <div>
            <a href="index.php?action=logout" class="logout-btn">Logout (<?= $_SESSION['username'] ?>)</a>
        </div>
    </div>

    <div class="container">
        <?php
        // --- 1. SUB-HALAMAN: DASHBOARD ---
        if ($current_page == 'dashboard') {
            echo "<h2 style='color:#2c3e50; margin-bottom:10px;'>Selamat Datang, " . $_SESSION['nama'] . "</h2>";
            echo "<p style='color:#7f8c8d;'>Hak Akses Aktif Anda Saat Ini: <strong style='color:#2980b9;'>" . $_SESSION['role'] . "</strong></p>";
            echo "<div style='background:#f8f9fa; border-left:4px solid #3498db; padding:15px; margin-top:20px; border-radius:4px;'>Silakan gunakan menu navigasi di atas untuk memuat form manajemen data klinis hewan secara real-time.</div>";
        }

        // --- 2. SUB-HALAMAN: FORM KELOLA PASIEN (ADMIN) ---
        elseif ($current_page == 'pasien' && $_SESSION['role'] == 'Admin') {
            $id = ""; $n_h = ""; $j_h = ""; $pem = ""; $tel = ""; $alm = "";
            if (isset($_GET['edit_pasien'])) {
                $res = $conn->query("SELECT * FROM pasien_hewan WHERE id=" . $_GET['edit_pasien']);
                if ($row = $res->fetch_assoc()) {
                    $id = $row['id']; $n_h = $row['nama_hewan']; $j_h = $row['jenis_hewan']; $pem = $row['pemilik']; $tel = $row['telepon']; $alm = $row['alamat'];
                }
            }
            ?>
            <h3 style="color:#2c3e50; border-bottom:2px solid #3498db; padding-bottom:8px;">Form Input & Kelola Data Pasien</h3>
            <form action="index.php?page=pasien" method="POST" style="margin-top:20px;">
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="grid-2">
                    <div>
                        <label>Nama Hewan Pasien</label>
                        <input type="text" name="nama_hewan" value="<?= $n_h ?>" required placeholder="Contoh: Meow, Blacky">
                    </div>
                    <div>
                        <label>Jenis Hewan</label>
                        <input type="text" name="jenis_hewan" value="<?= $j_h ?>" required placeholder="Kucing, Anjing, Burung, dll">
                    </div>
                </div>
                <label>Nama Pemilik</label>
                <input type="text" name="pemilik" value="<?= $pem ?>" required placeholder="Masukkan nama pemilik hewan...">
                <label>No. Telepon Pemilik</label>
                <input type="text" name="telepon" value="<?= $tel ?>" placeholder="Contoh: 081234xxxx">
                <label>Alamat Rumah Pemilik</label>
                <textarea name="alamat" rows="3" placeholder="Alamat lengkap pemilik..."><?= $alm ?></textarea>
                <button type="submit" name="save_pasien" style="margin-top:15px;">Simpan Data Pasien</button>
            </form>

            <h3 style="color:#2c3e50; margin-top:40px;">Daftar Pasien Terdaftar</h3>
            <table>
                <thead>
                    <tr><th>ID Pasien</th><th>Nama Hewan</th><th>Jenis</th><th>Pemilik</th><th>Telepon</th><th>Alamat</th><th style="width:150px;">Aksi</th></tr>
                </thead>
                <tbody>
                <?php
                $res = $conn->query("SELECT * FROM pasien_hewan ORDER BY id DESC");
                while ($row = $res->fetch_assoc()) {
                    echo "<tr>
                        <td><strong>PSN-0{$row['id']}</strong></td>
                        <td>{$row['nama_hewan']}</td>
                        <td>{$row['jenis_hewan']}</td>
                        <td>{$row['pemilik']}</td>
                        <td>{$row['telepon']}</td>
                        <td>{$row['alamat']}</td>
                        <td>
                            <a class='btn btn-warning' style='padding:6px 12px; font-size:12px;' href='index.php?page=pasien&edit_pasien={$row['id']}'>Edit</a>
                            <a class='btn btn-danger' style='padding:6px 12px; font-size:12px;' href='index.php?page=pasien&del_pasien={$row['id']}' onclick='return confirm(\"Hapus pasien?\")'>Hapus</a>
                        </td>
                    </tr>";
                }
                ?>
                </tbody>
            </table>
            <?php
        }

        // --- 3. SUB-HALAMAN: FORM KELOLA DATA OBAT (ADMIN) ---
        elseif ($current_page == 'obat' && $_SESSION['role'] == 'Admin') {
            $id = ""; $n_o = ""; $stk = ""; $hrg = "";
            if (isset($_GET['edit_obat'])) {
                $res = $conn->query("SELECT * FROM obat_hewan WHERE id=" . $_GET['edit_obat']);
                if ($row = $res->fetch_assoc()) {
                    $id = $row['id']; $n_o = $row['nama_obat']; $stk = $row['stok']; $hrg = $row['harga'];
                }
            }
            ?>
            <h3 style="color:#2c3e50; border-bottom:2px solid #3498db; padding-bottom:8px;">Form Input Stok Obat Hewan</h3>
            <form action="index.php?page=obat" method="POST" style="margin-top:20px;">
                <input type="hidden" name="id" value="<?= $id ?>">
                <label>Nama Obat</label>
                <input type="text" name="nama_obat" value="<?= $n_o ?>" required placeholder="Contoh: Vitamin Drops, Amoxicillin">
                <div class="grid-2">
                    <div>
                        <label>Jumlah Stok</label>
                        <input type="number" name="stok" value="<?= $stk ?>" required placeholder="0">
                    </div>
                    <div>
                        <label>Harga Jual (Rp)</label>
                        <input type="number" name="harga" value="<?= $hrg ?>" required placeholder="0">
                    </div>
                </div>
                <button type="submit" name="save_obat" style="margin-top:15px;">Simpan Data Obat</button>
            </form>

            <h3 style="color:#2c3e50; margin-top:40px;">Gudang Farmasi / Stok Obat</h3>
            <table>
                <thead>
                    <tr><th>ID Obat</th><th>Nama Obat</th><th>Stok Tersedia</th><th>Harga Satuan</th><th style="width:150px;">Aksi</th></tr>
                </thead>
                <tbody>
                <?php
                $res = $conn->query("SELECT * FROM obat_hewan");
                while ($row = $res->fetch_assoc()) {
                    echo "<tr>
                        <td><strong>OBT-{$row['id']}</strong></td>
                        <td>{$row['nama_obat']}</td>
                        <td>{$row['stok']} Unit</td>
                        <td>Rp " . number_format($row['harga']) . "</td>
                        <td>
                            <a class='btn btn-warning' style='padding:6px 12px; font-size:12px;' href='index.php?page=obat&edit_obat={$row['id']}'>Edit</a>
                            <a class='btn btn-danger' style='padding:6px 12px; font-size:12px;' href='index.php?page=obat&del_obat={$row['id']}' onclick='return confirm(\"Hapus obat?\")'>Hapus</a>
                        </td>
                    </tr>";
                }
                ?>
                </tbody>
            </table>
            <?php
        }

        // --- 4. SUB-HALAMAN: KASIR & TRANSAKSI PEMBAYARAN (ADMIN) ---
        elseif ($current_page == 'pembayaran' && $_SESSION['role'] == 'Admin') {
            ?>
            <h3 style="color:#2c3e50; border-bottom:2px solid #3498db; padding-bottom:8px;">Input Transaksi Pembayaran Layanan</h3>
            <form action="index.php?page=pembayaran" method="POST" style="margin-top:20px;">
                <label>Pilih Pasien Hewan</label>
                <select name="pasien_id" required>
                    <option value="">-- Pilih Hewan Pasien --</option>
                    <?php
                    $p = $conn->query("SELECT * FROM pasien_hewan");
                    while($r = $p->fetch_assoc()) echo "<option value='{$r['id']}'>{$r['nama_hewan']} (Pemilik: {$r['pemilik']})</option>";
                    ?>
                </select>
                <label>Total Tagihan / Biaya Tindakan (Rp)</label>
                <input type="number" name="total_bayar" required placeholder="Contoh: 150000">
                <label>Status Bayar</label>
                <select name="status">
                    <option value="Lunas">Lunas</option>
                    <option value="Belum Bayar">Belum Bayar</option>
                </select>
                <button type="submit" name="save_pembayaran" style="margin-top:15px;">Simpan Transaksi Kasir</button>
            </form>

            <h3 style="color:#2c3e50; margin-top:40px;">Riwayat Invoice / Pembayaran</h3>
            <table>
                <thead>
                    <tr><th>No. Nota</th><th>Nama Hewan</th><th>Tanggal Invoice</th><th>Total Pembayaran</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php
                $res = $conn->query("SELECT p.*, h.nama_hewan FROM pembayaran p JOIN pasien_hewan h ON p.pasien_id = h.id ORDER BY p.id DESC");
                while ($row = $res->fetch_assoc()) {
                    $status_badge = ($row['status'] == 'Lunas') ? "<span class='badge bg-success'>Lunas</span>" : "<span class='badge bg-warning'>Belum Lunas</span>";
                    echo "<tr>
                        <td><strong>INV-00{$row['id']}</strong></td>
                        <td>{$row['nama_hewan']}</td>
                        <td>{$row['tanggal_bayar']}</td>
                        <td>Rp " . number_format($row['total_bayar']) . "</td>
                        <td>$status_badge</td>
                    </tr>";
                }
                ?>
                </tbody>
            </table>
            <?php
        }

        // --- 5. SUB-HALAMAN: FORM REKAM MEDIS (DOKTER HEWAN) ---
        elseif ($current_page == 'rekam_medis' && $_SESSION['role'] == 'Dokter Hewan') {
            
            if (isset($_POST['save_rm'])) {
                $pasien_id = $_POST['pasien_id'];
                $dokter_id = $_SESSION['user_id']; 
                $tanggal   = date('Y-m-d');
                $keluhan   = mysqli_real_escape_string($conn, $_POST['keluhan']);
                $diagnosa  = mysqli_real_escape_string($conn, $_POST['diagnosa']);
                $tindakan  = mysqli_real_escape_string($conn, $_POST['tindakan']);
                
                if(empty($dokter_id)){ $dokter_id = 2; }

                $query_simpan = "INSERT INTO rekam_medis (pasien_id, dokter_id, tanggal, keluhan, diagnosa, tindakan) 
                                 VALUES ('$pasien_id', '$dokter_id', '$tanggal', '$keluhan', '$diagnosa', '$tindakan')";
                
                if ($conn->query($query_simpan)) {
                    echo "<div class='alert alert-success'>✔ Data medis berhasil disimpan ke histori di bawah!</div>";
                } else {
                    echo "<div class='alert alert-danger'>❌ Gagal menyimpan data: " . $conn->error . "</div>";
                }
            }
            ?>
            <h3 style="color:#2c3e50; border-bottom:2px solid #3498db; padding-bottom:8px;">Input Hasil Pemeriksaan Klinis (Rekam Medis)</h3>
            <form action="index.php?page=rekam_medis" method="POST" style="margin-top:20px;">
                <label>Pilih Pasien Hewan</label>
                <select name="pasien_id" required>
                    <option value="">-- Pilih Hewan --</option>
                    <?php
                    $p = $conn->query("SELECT * FROM pasien_hewan");
                    while($r = $p->fetch_assoc()) {
                        echo "<option value='{$r['id']}'>{$r['nama_hewan']} - [Pemilik: {$r['pemilik']}]</option>";
                    }
                    ?>
                </select>
                <label>Keluhan Utama / Gejala Fisik</label>
                <textarea name="keluhan" rows="2" required placeholder="Misal: Lemas, nafsu makan berkurang, muntah..."></textarea>
                <label>Hasil Diagnosa Dokter</label>
                <textarea name="diagnosa" rows="2" required placeholder="Misal: Suspek virus Parvo, Infeksi pencernaan..."></textarea>
                <label>Tindakan Medis & Resep Obat</label>
                <textarea name="tindakan" rows="2" required placeholder="Misal: Injeksi vitamin neuro, pemberian obat Amoxicillin drops..."></textarea>
                <button type="submit" name="save_rm" style="background:#2980b9; margin-top:15px;">Simpan Log Medis</button>
            </form>

            <h3 style="color:#2c3e50; margin-top:40px;">Histori Rekam Medis Pasien</h3>
            <table>
                <thead>
                    <tr><th>Tanggal</th><th>Nama Hewan</th><th>Keluhan</th><th>Diagnosa</th><th>Tindakan Medis</th><th>Dokter</th></tr>
                </thead>
                <tbody>
                <?php
                $res = $conn->query("SELECT rm.*, h.nama_hewan, u.nama AS nama_dokter 
                                     FROM rekam_medis rm 
                                     LEFT JOIN pasien_hewan h ON rm.pasien_id = h.id 
                                     LEFT JOIN users u ON rm.dokter_id = u.id 
                                     ORDER BY rm.id DESC");
                
                if ($res && $res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        $nama_hewan = !empty($row['nama_hewan']) ? $row['nama_hewan'] : "Hewan Terhapus";
                        $nama_dokter = !empty($row['nama_dokter']) ? $row['nama_dokter'] : "drh. Rey Suebu";
                        echo "<tr>
                            <td>{$row['tanggal']}</td>
                            <td><strong style='color:#2c3e50;'>{$nama_hewan}</strong></td>
                            <td>{$row['keluhan']}</td>
                            <td>{$row['diagnosa']}</td>
                            <td>{$row['tindakan']}</td>
                            <td>{$nama_dokter}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; color:#7f8c8d;'>Belum ada histori rekam medis yang tercatat.</td></tr>";
                }
                ?>
                </tbody>
            </table>
            <?php
        }

        // --- 6. SUB-HALAMAN: LAPORAN (DIREKTUR KLINIK) ---
        elseif ($current_page == 'laporan' && $_SESSION['role'] == 'Direktur Klinik') {
            ?>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom:2px solid #3498db; padding-bottom:8px;">
                <h3 style="color:#2c3e50;">Laporan Eksekutif Pendapatan & Layanan Klinik</h3>
                <button onclick="window.print()" style="background:#34495e;">Cetak Laporan (Print)</button>
            </div>
            <p style="margin-top:10px; color:#7f8c8d;">Berikut adalah rekapitulasi data manajerial real-time Klinik Hewan Kemiri Sentani.</p>
            
            <h4 style="margin-top:30px; color:#2c3e50;">1. Rekapitulasi Keuangan & Kasir</h4>
            <table>
                <thead>
                    <tr><th>ID Invoice</th><th>Tanggal Transaksi</th><th>Hewan</th><th>Total Omset Masuk</th><th>Status Penjualan</th></tr>
                </thead>
                <tbody>
                <?php
                $total_omset = 0;
                $res = $conn->query("SELECT p.*, h.nama_hewan FROM pembayaran p JOIN pasien_hewan h ON p.pasien_id = h.id");
                while ($row = $res->fetch_assoc()) {
                    if($row['status'] == 'Lunas') $total_omset += $row['total_bayar'];
                    echo "<tr>
                        <td><strong>INV-00{$row['id']}</strong></td>
                        <td>{$row['tanggal_bayar']}</td>
                        <td>{$row['nama_hewan']}</td>
                        <td>Rp " . number_format($row['total_bayar']) . "</td>
                        <td><span class='badge bg-success'>{$row['status']}</span></td>
                    </tr>";
                }
                ?>
                <tr style="font-weight:bold; background:#e1f5fe; color:#1e3c72;">
                    <td colspan="3" style="text-align:right;">TOTAL PENDAPATAN BERSIH (LUNAS):</td>
                    <td colspan="2">Rp <?= number_format($total_omset) ?></td>
                </tr>
                </tbody>
            </table>
            <?php
        }
        ?>
    </div>
<?php endif; ?>

<div class="footer">
    &copy; 2026 Aplikasi SI Klinik Hewan Kemiri Sentani - Dikembangkan Oleh: <?= isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Reiliely Adriano Suebu' ?>
</div>

</body>
</html>