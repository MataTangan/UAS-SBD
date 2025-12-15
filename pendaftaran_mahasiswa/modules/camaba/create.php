<?php
$page_title = "Tambah Data Camaba";
include '../../includes/header.php';
require_once '../../config/database.php';

// Proses hanya jika form di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    try {
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
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data camaba berhasil ditambahkan'
                }).then(() => {
                    window.location.href = 'index.php';
                });
            </script>";
        }
    } catch (PDOException $e) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Error: " . addslashes($e->getMessage()) . "'
            });
        </script>";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-plus"></i> Tambah Data Calon Mahasiswa Baru</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <!-- Data Akun -->
                <div class="col-md-12">
                    <h5 class="mb-3 text-primary"><i class="bi bi-shield-lock"></i> Data Akun</h5>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required placeholder="contoh@email.com">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required placeholder="Min. 6 karakter">
                </div>

                <!-- Data Pribadi -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary"><i class="bi bi-person"></i> Data Pribadi</h5>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-control" required placeholder="Nama lengkap sesuai KTP">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIK <span class="text-danger">*</span></label>
                    <input type="text" name="nik" class="form-control" maxlength="16" required placeholder="16 digit NIK">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control" placeholder="Kota/Kabupaten">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_lahir" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Agama <span class="text-danger">*</span></label>
                    <select name="agama" class="form-select" required>
                        <option value="">-- Pilih Agama --</option>
                        <option value="Islam">Islam</option>
                        <option value="Kristen">Kristen</option>
                        <option value="Katolik">Katolik</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Buddha">Buddha</option>
                        <option value="Konghucu">Konghucu</option>
                    </select>
                </div>

                <!-- Data Alamat -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary"><i class="bi bi-geo-alt"></i> Data Alamat</h5>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                    <textarea name="alamat" class="form-control" rows="3" required placeholder="Jalan, RT/RW, Kelurahan, Kecamatan"></textarea>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Provinsi</label>
                    <input type="text" name="provinsi" class="form-control" placeholder="Contoh: Jawa Barat">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kota/Kabupaten</label>
                    <input type="text" name="kota" class="form-control" placeholder="Contoh: Bandung">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kode Pos</label>
                    <input type="text" name="kode_pos" class="form-control" maxlength="10" placeholder="Contoh: 40123">
                </div>

                <!-- Data Kontak -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary"><i class="bi bi-telephone"></i> Data Kontak</h5>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. HP <span class="text-danger">*</span></label>
                    <input type="text" name="no_hp" class="form-control" required placeholder="Contoh: 08123456789">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Orang Tua</label>
                    <input type="text" name="nama_ortu" class="form-control" placeholder="Nama Ayah/Ibu">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. HP Orang Tua</label>
                    <input type="text" name="no_hp_ortu" class="form-control" placeholder="Contoh: 08123456789">
                </div>

                <!-- Data Pendidikan -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary"><i class="bi bi-mortarboard"></i> Data Pendidikan</h5>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Asal Sekolah</label>
                    <input type="text" name="asal_sekolah" class="form-control" placeholder="Nama SMA/SMK/MA">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tahun Lulus</label>
                    <input type="number" name="tahun_lulus" class="form-control" placeholder="Contoh: 2024" min="1990" max="2030">
                </div>
            </div>

            <hr class="my-4">

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="reset" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>