<?php
$page_title = "Edit Data Ujian Seleksi";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get ID
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID tidak ditemukan.');

// Get existing data
$query = "SELECT u.*, p.no_pendaftaran, c.nama_lengkap, pr.nama_prodi
          FROM ujian_seleksi u
          JOIN pendaftaran p ON u.id_pendaftaran = p.id_pendaftaran
          JOIN camaba c ON p.id_camaba = c.id_camaba
          JOIN prodi pr ON p.id_prodi = pr.id_prodi
          WHERE u.id_ujian = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('ERROR: Data ujian tidak ditemukan.');
}

// Update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Hitung status lulus otomatis
        $nilai = floatval($_POST['nilai']);
        $nilai_minimal = floatval($_POST['nilai_minimal']);
        $status_lulus = $nilai >= $nilai_minimal ? 'Lulus' : 'Tidak Lulus';
        
        $query = "UPDATE ujian_seleksi SET 
                  jenis_ujian = :jenis_ujian,
                  nilai = :nilai,
                  nilai_minimal = :nilai_minimal,
                  status_lulus = :status_lulus,
                  tanggal_ujian = :tanggal_ujian,
                  lokasi_ujian = :lokasi_ujian,
                  catatan = :catatan
                  WHERE id_ujian = :id";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':jenis_ujian', $_POST['jenis_ujian']);
        $stmt->bindParam(':nilai', $_POST['nilai']);
        $stmt->bindParam(':nilai_minimal', $_POST['nilai_minimal']);
        $stmt->bindParam(':status_lulus', $status_lulus);
        $stmt->bindParam(':tanggal_ujian', $_POST['tanggal_ujian']);
        $stmt->bindParam(':lokasi_ujian', $_POST['lokasi_ujian']);
        $stmt->bindParam(':catatan', $_POST['catatan']);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data ujian seleksi berhasil diupdate',
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
    <h2><i class="bi bi-pencil-square"></i> Edit Data Ujian Seleksi</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <!-- Data Pendaftaran (Read Only) -->
                <div class="col-md-12">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-person-check"></i> Data Pendaftaran
                    </h5>
                </div>
                
                <div class="col-md-12 mb-3">
                    <div class="alert alert-secondary">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>No. Pendaftaran:</strong><br>
                                <?php echo htmlspecialchars($row['no_pendaftaran']); ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Nama Camaba:</strong><br>
                                <?php echo htmlspecialchars($row['nama_lengkap']); ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Program Studi:</strong><br>
                                <?php echo htmlspecialchars($row['nama_prodi']); ?>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> Data pendaftaran tidak dapat diubah
                    </small>
                </div>

                <!-- Data Ujian -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-clipboard-data"></i> Informasi Ujian
                    </h5>
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
                    <label class="form-label">Tanggal Ujian <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_ujian" class="form-control" 
                           value="<?php echo $row['tanggal_ujian']; ?>" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Lokasi Ujian</label>
                    <input type="text" name="lokasi_ujian" class="form-control" 
                           value="<?php echo htmlspecialchars($row['lokasi_ujian']); ?>"
                           placeholder="Contoh: Gedung A Ruang 101">
                </div>

                <!-- Penilaian -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-star"></i> Penilaian
                    </h5>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nilai <span class="text-danger">*</span></label>
                    <input type="number" name="nilai" class="form-control" 
                           step="0.01" min="0" max="100" required 
                           value="<?php echo $row['nilai']; ?>"
                           id="inputNilai">
                    <small class="text-muted">Nilai yang diperoleh (0-100)</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nilai Minimal <span class="text-danger">*</span></label>
                    <input type="number" name="nilai_minimal" class="form-control" 
                           step="0.01" min="0" max="100" required 
                           value="<?php echo $row['nilai_minimal']; ?>"
                           id="inputNilaiMin">
                    <small class="text-muted">Batas kelulusan ujian</small>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="alert" id="statusInfo">
                        <i class="bi bi-info-circle"></i>
                        Status Saat Ini: 
                        <strong id="statusText">
                            <?php echo $row['status_lulus']; ?>
                        </strong>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="4" 
                              placeholder="Catatan tambahan tentang hasil ujian"><?php echo htmlspecialchars($row['catatan']); ?></textarea>
                </div>

                <!-- Info Tambahan -->
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="bi bi-clock-history"></i>
                        <strong>Dibuat pada:</strong> 
                        <?php echo date('d/m/Y H:i:s', strtotime($row['created_at'])); ?>
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

<script>
// Auto calculate status kelulusan
function updateStatus() {
    const nilai = parseFloat(document.getElementById('inputNilai').value) || 0;
    const nilaiMin = parseFloat(document.getElementById('inputNilaiMin').value) || 0;
    const statusInfo = document.getElementById('statusInfo');
    const statusText = document.getElementById('statusText');
    
    if (nilai >= nilaiMin) {
        statusText.textContent = 'LULUS';
        statusInfo.className = 'alert alert-success';
    } else {
        statusText.textContent = 'TIDAK LULUS';
        statusInfo.className = 'alert alert-danger';
    }
}

// Initialize
updateStatus();

document.getElementById('inputNilai').addEventListener('input', updateStatus);
document.getElementById('inputNilaiMin').addEventListener('input', updateStatus);
</script>

<?php include '../../includes/footer.php'; ?>