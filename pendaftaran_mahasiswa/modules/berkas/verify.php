<?php
$page_title = "Verifikasi Berkas";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get ID
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID tidak ditemukan.');

// Get berkas data
$query = "SELECT b.*, p.no_pendaftaran, c.nama_lengkap, pr.nama_prodi
          FROM berkas b
          JOIN pendaftaran p ON b.id_pendaftaran = p.id_pendaftaran
          JOIN camaba c ON p.id_camaba = c.id_camaba
          JOIN prodi pr ON p.id_prodi = pr.id_prodi
          WHERE b.id_berkas = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('ERROR: Data tidak ditemukan.');
}

// Process verification
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status_berkas'];
    $catatan = $_POST['catatan_berkas'];
    
    try {
        $query = "UPDATE berkas SET 
                  status_berkas = :status_berkas,
                  catatan_berkas = :catatan_berkas,
                  tanggal_verifikasi = CURRENT_TIMESTAMP
                  WHERE id_berkas = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status_berkas', $status);
        $stmt->bindParam(':catatan_berkas', $catatan);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $message = $status == 'Diverifikasi' ? 'diverifikasi' : 'ditolak';
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Berkas berhasil $message'
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

// File list
$files = [
    'ijazah' => 'Ijazah/STTB',
    'kk' => 'Kartu Keluarga',
    'foto' => 'Pas Foto',
    'akta_kelahiran' => 'Akta Kelahiran',
    'surat_keterangan_lulus' => 'Surat Keterangan Lulus',
    'sertifikat_prestasi' => 'Sertifikat Prestasi'
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-check-circle"></i> Verifikasi Berkas Pendaftaran</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<!-- Info Camaba -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-person-badge"></i> Informasi Pendaftar</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <td width="150"><strong>No. Pendaftaran</strong></td>
                        <td><?php echo htmlspecialchars($row['no_pendaftaran']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Nama Lengkap</strong></td>
                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <td width="150"><strong>Program Studi</strong></td>
                        <td><?php echo htmlspecialchars($row['nama_prodi']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status Saat Ini</strong></td>
                        <td>
                            <span class="badge bg-warning"><?php echo $row['status_berkas']; ?></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Preview Berkas -->
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-files"></i> Preview Berkas</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($files as $field => $label): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <strong><?php echo $label; ?></strong>
                        </div>
                        <div class="card-body text-center p-2">
                            <?php if (!empty($row[$field])): ?>
                                <?php
                                $file_path = "../../uploads/berkas/" . $row[$field];
                                $file_ext = strtolower(pathinfo($row[$field], PATHINFO_EXTENSION));
                                ?>
                                
                                <?php if (in_array($file_ext, ['jpg', 'jpeg', 'png'])): ?>
                                    <!-- Image Preview -->
                                    <img src="<?php echo $file_path; ?>" 
                                         class="img-fluid mb-2" 
                                         style="max-height: 150px; object-fit: contain;"
                                         alt="<?php echo $label; ?>">
                                <?php else: ?>
                                    <!-- PDF Icon -->
                                    <div class="mb-2">
                                        <i class="bi bi-file-pdf" style="font-size: 60px; color: #dc3545;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <a href="<?php echo $file_path; ?>" 
                                   class="btn btn-sm btn-primary w-100" 
                                   target="_blank">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                                <div class="form-check mt-2">
                                    <input class="form-check-input check-file" 
                                           type="checkbox" 
                                           id="check_<?php echo $field; ?>">
                                    <label class="form-check-label" for="check_<?php echo $field; ?>">
                                        <small>Sudah diperiksa</small>
                                    </label>
                                </div>
                            <?php else: ?>
                                <div class="text-muted py-3">
                                    <i class="bi bi-x-circle" style="font-size: 40px;"></i>
                                    <p class="mb-0 mt-2"><small>Tidak ada file</small></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Form Verifikasi -->
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Hasil Verifikasi</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label"><strong>Status Verifikasi <span class="text-danger">*</span></strong></label>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status_berkas" 
                                   id="status_terverifikasi" value="Diverifikasi" required>
                            <label class="form-check-label" for="status_terverifikasi">
                                <i class="bi bi-check-circle text-success"></i> 
                                <strong>Diverifikasi</strong> - Semua berkas valid dan lengkap
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status_berkas" 
                                   id="status_ditolak" value="Ditolak" required>
                            <label class="form-check-label" for="status_ditolak">
                                <i class="bi bi-x-circle text-danger"></i> 
                                <strong>Ditolak</strong> - Ada berkas yang tidak sesuai
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label"><strong>Catatan Verifikasi <span class="text-danger">*</span></strong></label>
                <textarea name="catatan_berkas" class="form-control" rows="4" required 
                          placeholder="Tulis catatan hasil verifikasi...&#10;Contoh:&#10;- Semua berkas sudah sesuai dan lengkap&#10;- Atau sebutkan berkas yang perlu diperbaiki"><?php echo htmlspecialchars($row['catatan_berkas']); ?></textarea>
                <small class="text-muted">Catatan ini akan dilihat oleh pendaftar</small>
            </div>

            <hr>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
                <button type="submit" class="btn btn-success" id="btnSubmit">
                    <i class="bi bi-check-circle"></i> Simpan Verifikasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Optional: Check if all files are checked before submitting
document.querySelectorAll('.check-file').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const allChecked = document.querySelectorAll('.check-file:checked').length;
        const totalFiles = document.querySelectorAll('.check-file').length;
        
        if (allChecked === totalFiles) {
            document.getElementById('btnSubmit').classList.remove('btn-secondary');
            document.getElementById('btnSubmit').classList.add('btn-success');
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
