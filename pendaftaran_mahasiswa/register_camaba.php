<?php
session_start();
require_once 'config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] == 'admin') {
        header("Location: index.php");
    } else {
        header("Location: camaba_dashboard.php");
    }
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Validasi email sudah terdaftar atau belum
        $check_email = "SELECT COUNT(*) FROM camaba WHERE email = ?";
        $stmt = $db->prepare($check_email);
        $stmt->execute([$_POST['email']]);
        
        if ($stmt->fetchColumn() > 0) {
            $error = "Email sudah terdaftar! Gunakan email lain.";
        } else {
            // Validasi NIK sudah terdaftar atau belum
            $check_nik = "SELECT COUNT(*) FROM camaba WHERE nik = ?";
            $stmt = $db->prepare($check_nik);
            $stmt->execute([$_POST['nik']]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = "NIK sudah terdaftar!";
            } else {
                // Insert data camaba baru
                $query = "INSERT INTO camaba (email, password, nama_lengkap, nik, tempat_lahir, 
                          tgl_lahir, jenis_kelamin, agama, alamat, provinsi, kota, kode_pos, 
                          no_hp, nama_ortu, no_hp_ortu, asal_sekolah, tahun_lulus) 
                          VALUES (:email, :password, :nama_lengkap, :nik, :tempat_lahir, 
                          :tgl_lahir, :jenis_kelamin, :agama, :alamat, :provinsi, :kota, 
                          :kode_pos, :no_hp, :nama_ortu, :no_hp_ortu, :asal_sekolah, :tahun_lulus)";
                
                $stmt = $db->prepare($query);
                
                // Hash password
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                
                $stmt->bindParam(':email', $_POST['email']);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':nama_lengkap', $_POST['nama_lengkap']);
                $stmt->bindParam(':nik', $_POST['nik']);
                $stmt->bindParam(':tempat_lahir', $_POST['tempat_lahir']);
                $stmt->bindParam(':tgl_lahir', $_POST['tgl_lahir']);
                $stmt->bindParam(':jenis_kelamin', $_POST['jenis_kelamin']);
                $stmt->bindParam(':agama', $_POST['agama']);
                $stmt->bindParam(':alamat', $_POST['alamat']);
                $stmt->bindParam(':provinsi', $_POST['provinsi']);
                $stmt->bindParam(':kota', $_POST['kota']);
                $stmt->bindParam(':kode_pos', $_POST['kode_pos']);
                $stmt->bindParam(':no_hp', $_POST['no_hp']);
                $stmt->bindParam(':nama_ortu', $_POST['nama_ortu']);
                $stmt->bindParam(':no_hp_ortu', $_POST['no_hp_ortu']);
                $stmt->bindParam(':asal_sekolah', $_POST['asal_sekolah']);
                $stmt->bindParam(':tahun_lulus', $_POST['tahun_lulus']);
                
                if ($stmt->execute()) {
                    $success = "Pendaftaran berhasil! Silakan login.";
                }
            }
        }
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Camaba - Sistem PMB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .register-card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            background: white;
            max-width: 900px;
            margin: 0 auto;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .section-title {
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <div class="card-body p-5">
                <!-- Header -->
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus-fill fs-1 text-primary"></i>
                    <h2 class="mt-2 mb-1">Pendaftaran Calon Mahasiswa Baru</h2>
                    <p class="text-muted">Lengkapi data diri Anda untuk mendaftar</p>
                </div>

                <?php if ($success): ?>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '<?php echo $success; ?>',
                        confirmButtonColor: '#667eea'
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                </script>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" action="" id="registerForm">
                    <!-- Data Akun -->
                    <h5 class="section-title"><i class="bi bi-shield-lock me-2"></i>Data Akun</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required 
                                   placeholder="contoh@email.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required 
                                   placeholder="Min. 6 karakter" minlength="6" id="password">
                        </div>
                    </div>

                    <!-- Data Pribadi -->
                    <h5 class="section-title"><i class="bi bi-person me-2"></i>Data Pribadi</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" required 
                                   placeholder="Sesuai KTP">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK <span class="text-danger">*</span></label>
                            <input type="text" name="nik" class="form-control" required 
                                   maxlength="16" placeholder="16 digit NIK">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" 
                                   placeholder="Kota/Kabupaten">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_lahir" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agama <span class="text-danger">*</span></label>
                            <select name="agama" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </select>
                        </div>
                    </div>

                    <!-- Data Alamat -->
                    <h5 class="section-title"><i class="bi bi-geo-alt me-2"></i>Data Alamat</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" class="form-control" rows="2" required 
                                      placeholder="Jalan, RT/RW, Kelurahan, Kecamatan"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Provinsi</label>
                            <input type="text" name="provinsi" class="form-control" placeholder="Jawa Barat">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kota/Kabupaten</label>
                            <input type="text" name="kota" class="form-control" placeholder="Bandung">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kode Pos</label>
                            <input type="text" name="kode_pos" class="form-control" 
                                   maxlength="10" placeholder="40123">
                        </div>
                    </div>

                    <!-- Data Kontak & Pendidikan -->
                    <h5 class="section-title"><i class="bi bi-telephone me-2"></i>Kontak & Pendidikan</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">No. HP <span class="text-danger">*</span></label>
                            <input type="text" name="no_hp" class="form-control" required 
                                   placeholder="08123456789">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Orang Tua</label>
                            <input type="text" name="nama_ortu" class="form-control" 
                                   placeholder="Nama Ayah/Ibu">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. HP Orang Tua</label>
                            <input type="text" name="no_hp_ortu" class="form-control" 
                                   placeholder="08123456789">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Asal Sekolah</label>
                            <input type="text" name="asal_sekolah" class="form-control" 
                                   placeholder="SMA/SMK/MA">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Lulus</label>
                            <input type="number" name="tahun_lulus" class="form-control" 
                                   placeholder="2024" min="1990" max="2030">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-register btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Daftar Sekarang
                        </button>
                        <a href="login.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Login
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            Sudah punya akun? <a href="login.php" class="text-primary">Login di sini</a>
                        </small>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
