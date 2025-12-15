<?php
$page_title = "Edit Informasi Ujian";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get ID
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID tidak ditemukan.');

// Get existing data
$query = "SELECT * FROM informasi_ujian WHERE id_info_ujian = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('ERROR: Data tidak ditemukan.');
}

// Update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $query = "UPDATE informasi_ujian SET 
                  judul_ujian = :judul_ujian,
                  jenis_ujian = :jenis_ujian,
                  deskripsi = :deskripsi,
                  tanggal_ujian = :tanggal_ujian,
                  waktu_mulai = :waktu_mulai,
                  waktu_selesai = :waktu_selesai,
                  lokasi_ujian = :lokasi_ujian,
                  kuota_peserta = :kuota_peserta,
                  nilai_minimal = :nilai_minimal,
                  persyaratan = :persyaratan,
                  status_ujian = :status_ujian
                  WHERE id_info_ujian = :id";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':judul_ujian', $_POST['judul_ujian']);
        $stmt->bindParam(':jenis_ujian', $_POST['jenis_ujian']);
        $stmt->bindParam(':deskripsi', $_POST['deskripsi']);
        $stmt->bindParam(':tanggal_ujian', $_POST['tanggal_ujian']);
        $stmt->bindParam(':waktu_mulai', $_POST['waktu_mulai']);
        $stmt->bindParam(':waktu_selesai', $_POST['waktu_selesai']);
        $stmt->bindParam(':lokasi_ujian', $_POST['lokasi_ujian']);
        $stmt->bindParam(':kuota_peserta', $_POST['kuota_peserta']);
        $stmt->bindParam(':nilai_minimal', $_POST['nilai_minimal']);
        $stmt->bindParam(':persyaratan', $_POST['persyaratan']);
        $stmt->bindParam(':status_ujian', $_POST['status_ujian']);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Informasi ujian berhasil diupdate',
                    showConfirmButton: false,
                    timer: 1500
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
    <h2><i class="bi bi-pencil-square"></i> Edit Informasi Ujian</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <!-- Informasi Dasar -->
                <div class="col-md-12">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-info-circle"></i> Informasi Dasar Ujian
                    </h5>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Judul Ujian <span class="text-danger">*</span></label>
                    <input type="text" name="judul_ujian" class="form-control" required 
                           value="<?php echo htmlspecialchars($row['judul_ujian']); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Ujian <span class="text-danger">*</span></label>
                    <select name="jenis_ujian" class="form-select" required>
                        <option value="TPA" <?php echo $row['jenis_ujian']=='TPA'?'selected':''; ?>>
                            TPA (Tes Potensi Akademik)
                        </option>
                        <option value="Akademik" <?php echo $row['jenis_ujian']=='Akademik'?'selected':''; ?>>
                            Akademik
                        </option>
                        <option value="Wawancara" <?php echo $row['jenis_ujian']=='Wawancara'?'selected':''; ?>>
                            Wawancara
                        </option>
                        <option value="Keterampilan" <?php echo $row['jenis_ujian']=='Keterampilan'?'selected':''; ?>>
                            Keterampilan
                        </option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Ujian <span class="text-danger">*</span></label>
                    <select name="status_ujian" class="form-select" required>
                        <option value="Aktif" <?php echo $row['status_ujian']=='Aktif'?'selected':''; ?>>
                            Aktif
                        </option>
                        <option value="Selesai" <?php echo $row['status_ujian']=='Selesai'?'selected':''; ?>>
                            Selesai
                        </option>
                        <option value="Dibatalkan" <?php echo $row['status_ujian']=='Dibatalkan'?'selected':''; ?>>
                            Dibatalkan
                        </option>
                    </select>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"><?php echo htmlspecialchars($row['deskripsi']); ?></textarea>
                </div>

                <!-- Jadwal -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-calendar-event"></i> Jadwal Ujian
                    </h5>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Ujian <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_ujian" class="form-control" required
                           value="<?php echo $row['tanggal_ujian']; ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                    <input type="time" name="waktu_mulai" class="form-control" required
                           value="<?php echo $row['waktu_mulai']; ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                    <input type="time" name="waktu_selesai" class="form-control" required
                           value="<?php echo $row['waktu_selesai']; ?>">
                </div>

                <!-- Lokasi & Kapasitas -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-geo-alt"></i> Lokasi & Kapasitas
                    </h5>
                </div>

                <div class="col-md-8 mb-3">
                    <label class="form-label">Lokasi Ujian <span class="text-danger">*</span></label>
                    <input type="text" name="lokasi_ujian" class="form-control" required 
                           value="<?php echo htmlspecialchars($row['lokasi_ujian']); ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Kuota Peserta <span class="text-danger">*</span></label>
                    <input type="number" name="kuota_peserta" class="form-control" required 
                           min="1" value="<?php echo $row['kuota_peserta']; ?>">
                </div>

                <!-- Penilaian -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-star"></i> Standar Penilaian
                    </h5>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Nilai Minimal Kelulusan <span class="text-danger">*</span></label>
                    <input type="number" name="nilai_minimal" class="form-control" required 
                           step="0.01" min="0" max="100" 
                           value="<?php echo $row['nilai_minimal']; ?>">
                </div>

                <!-- Persyaratan -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-list-check"></i> Persyaratan & Ketentuan
                    </h5>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Persyaratan Peserta</label>
                    <textarea name="persyaratan" class="form-control" rows="6"><?php echo htmlspecialchars($row['persyaratan']); ?></textarea>
                </div>

                <!-- Info Tambahan -->
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-6">
                                <i class="bi bi-clock-history"></i>
                                <strong>Dibuat:</strong> 
                                <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                            </div>
                            <div class="col-md-6">
                                <i class="bi bi-pencil"></i>
                                <strong>Diupdate:</strong> 
                                <?php echo date('d/m/Y H:i', strtotime($row['updated_at'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
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