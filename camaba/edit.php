<?php
$page_title = "Edit Data Camaba";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get ID
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID tidak ditemukan.');

// Get existing data
$query = "SELECT * FROM camaba WHERE id_camaba = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('ERROR: Data tidak ditemukan.');
}

// Update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $query = "UPDATE camaba SET 
                  email = :email,
                  nama_lengkap = :nama_lengkap,
                  nik = :nik,
                  tempat_lahir = :tempat_lahir,
                  tgl_lahir = :tgl_lahir,
                  jenis_kelamin = :jenis_kelamin,
                  agama = :agama,
                  alamat = :alamat,
                  provinsi = :provinsi,
                  kota = :kota,
                  kode_pos = :kode_pos,
                  no_hp = :no_hp,
                  nama_ortu = :nama_ortu,
                  no_hp_ortu = :no_hp_ortu,
                  asal_sekolah = :asal_sekolah,
                  tahun_lulus = :tahun_lulus
                  WHERE id_camaba = :id";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':email', $_POST['email']);
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
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data camaba berhasil diupdate'
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
                text: 'Error: " . $e->getMessage() . "'
            });
        </script>";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil-square"></i> Edit Data Camaba</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($row['email']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" 
                           value="<?php echo htmlspecialchars($row['nama_lengkap']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIK</label>
                    <input type="text" name="nik" class="form-control" 
                           value="<?php echo htmlspecialchars($row['nik']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control" 
                           value="<?php echo htmlspecialchars($row['tempat_lahir']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tgl_lahir" class="form-control" 
                           value="<?php echo $row['tgl_lahir']; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="L" <?php echo $row['jenis_kelamin']=='L'?'selected':''; ?>>Laki-laki</option>
                        <option value="P" <?php echo $row['jenis_kelamin']=='P'?'selected':''; ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Agama</label>
                    <select name="agama" class="form-select" required>
                        <?php
                        $agama_list = ['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'];
                        foreach($agama_list as $agama) {
                            $selected = $row['agama']==$agama ? 'selected' : '';
                            echo "<option value='$agama' $selected>$agama</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3" required><?php echo htmlspecialchars($row['alamat']); ?></textarea>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Provinsi</label>
                    <input type="text" name="provinsi" class="form-control" 
                           value="<?php echo htmlspecialchars($row['provinsi']); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kota</label>
                    <input type="text" name="kota" class="form-control" 
                           value="<?php echo htmlspecialchars($row['kota']); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kode Pos</label>
                    <input type="text" name="kode_pos" class="form-control" 
                           value="<?php echo htmlspecialchars($row['kode_pos']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" class="form-control" 
                           value="<?php echo htmlspecialchars($row['no_hp']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Orang Tua</label>
                    <input type="text" name="nama_ortu" class="form-control" 
                           value="<?php echo htmlspecialchars($row['nama_ortu']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. HP Orang Tua</label>
                    <input type="text" name="no_hp_ortu" class="form-control" 
                           value="<?php echo htmlspecialchars($row['no_hp_ortu']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Asal Sekolah</label>
                    <input type="text" name="asal_sekolah" class="form-control" 
                           value="<?php echo htmlspecialchars($row['asal_sekolah']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tahun Lulus</label>
                    <input type="text" name="tahun_lulus" class="form-control" 
                           value="<?php echo htmlspecialchars($row['tahun_lulus']); ?>">
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Data
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
