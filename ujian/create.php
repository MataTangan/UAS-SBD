<?php
$page_title = "Tambah Data Ujian Seleksi";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get list pendaftaran yang belum punya ujian atau tambah ujian baru
$pendaftaran_query = "SELECT p.id_pendaftaran, p.no_pendaftaran, c.nama_lengkap, pr.nama_prodi
                      FROM pendaftaran p
                      JOIN camaba c ON p.id_camaba = c.id_camaba
                      JOIN prodi pr ON p.id_prodi = pr.id_prodi
                      WHERE p.status_pendaftaran != 'Ditolak'
                      ORDER BY p.tanggal_daftar DESC";
$stmt_pendaftaran = $db->query($pendaftaran_query);

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Hitung status lulus otomatis
        $nilai = floatval($_POST['nilai']);
        $nilai_minimal = floatval($_POST['nilai_minimal']);
        $status_lulus = $nilai >= $nilai_minimal ? 'Lulus' : 'Tidak Lulus';
        
        $query = "INSERT INTO ujian_seleksi 
                  (id_pendaftaran, jenis_ujian, nilai, nilai_minimal, status_lulus, 
                   tanggal_ujian, lokasi_ujian, catatan) 
                  VALUES 
                  (:id_pendaftaran, :jenis_ujian, :nilai, :nilai_minimal, :status_lulus, 
                   :tanggal_ujian, :lokasi_ujian, :catatan)";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':id_pendaftaran', $_POST['id_pendaftaran']);
        $stmt->bindParam(':jenis_ujian', $_POST['jenis_ujian']);
        $stmt->bindParam(':nilai', $_POST['nilai']);
        $stmt->bindParam(':nilai_minimal', $_POST['nilai_minimal']);
        $stmt->bindParam(':status_lulus', $status_lulus);
        $stmt->bindParam(':tanggal_ujian', $_POST['tanggal_ujian']);
        $stmt->bindParam(':lokasi_ujian', $_POST['lokasi_ujian']);
        $stmt->bindParam(':catatan', $_POST['catatan']);
        
        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data ujian seleksi berhasil ditambahkan',
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
    <h2><i class="bi bi-clipboard-plus"></i> Tambah Data Ujian Seleksi</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="" id="formUjian">
            <div class="row">
                <!-- Pilih Pendaftaran -->
                <div class="col-md-12">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-person-check"></i> Data Pendaftaran
                    </h5>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Pilih Pendaftaran <span class="text-danger">*</span></label>
                    <select name="id_pendaftaran" class="form-select" required id="selectPendaftaran">
                        <option value="">-- Pilih No. Pendaftaran --</option>
                        <?php while ($row = $stmt_pendaftaran->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?php echo $row['id_pendaftaran']; ?>">
                            <?php echo $row['no_pendaftaran']; ?> - 
                            <?php echo htmlspecialchars($row['nama_lengkap']); ?> 
                            (<?php echo htmlspecialchars($row['nama_prodi']); ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="text-muted">Pilih nomor pendaftaran calon mahasiswa</small>
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
                        <option value="">-- Pilih Jenis Ujian --</option>
                        <option value="TPA">TPA (Tes Potensi Akademik)</option>
                        <option value="Akademik">Akademik</option>
                        <option value="Wawancara">Wawancara</option>
                        <option value="Keterampilan">Keterampilan</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Ujian <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_ujian" class="form-control" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Lokasi Ujian</label>
                    <input type="text" name="lokasi_ujian" class="form-control" 
                           placeholder="Contoh: Gedung A Ruang 101">
                    <small class="text-muted">Opsional - Lokasi pelaksanaan ujian</small>
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
                           placeholder="Contoh: 85.50"
                           id="inputNilai">
                    <small class="text-muted">Nilai yang diperoleh (0-100)</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nilai Minimal <span class="text-danger">*</span></label>
                    <input type="number" name="nilai_minimal" class="form-control" 
                           step="0.01" min="0" max="100" required 
                           value="60.00"
                           placeholder="Contoh: 60.00"
                           id="inputNilaiMin">
                    <small class="text-muted">Batas kelulusan ujian</small>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="alert alert-info" id="statusInfo" style="display: none;">
                        <i class="bi bi-info-circle"></i>
                        Status: <strong id="statusText"></strong>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="4" 
                              placeholder="Catatan tambahan tentang hasil ujian (opsional)"></textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="reset" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Data Ujian
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
    
    if (nilai > 0) {
        statusInfo.style.display = 'block';
        if (nilai >= nilaiMin) {
            statusText.textContent = 'LULUS';
            statusInfo.className = 'alert alert-success';
        } else {
            statusText.textContent = 'TIDAK LULUS';
            statusInfo.className = 'alert alert-danger';
        }
    } else {
        statusInfo.style.display = 'none';
    }
}

document.getElementById('inputNilai').addEventListener('input', updateStatus);
document.getElementById('inputNilaiMin').addEventListener('input', updateStatus);
</script>

<?php include '../../includes/footer.php'; ?>