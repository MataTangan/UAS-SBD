<?php
$page_title = "Upload Berkas";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get list pendaftaran yang belum upload berkas
$query_pendaftaran = "SELECT p.id_pendaftaran, p.no_pendaftaran, c.nama_lengkap, pr.nama_prodi
                      FROM pendaftaran p
                      JOIN camaba c ON p.id_camaba = c.id_camaba
                      JOIN prodi pr ON p.id_prodi = pr.id_prodi
                      WHERE p.id_pendaftaran NOT IN (SELECT id_pendaftaran FROM berkas)
                      ORDER BY p.tanggal_daftar DESC";
$stmt_pendaftaran = $db->query($query_pendaftaran);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pendaftaran = $_POST['id_pendaftaran'];
    $catatan_berkas = $_POST['catatan_berkas'];
    
    // Upload directory
    $upload_dir = "../../uploads/berkas/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Array untuk menyimpan nama file
    $files = [
        'ijazah' => '',
        'kk' => '',
        'foto' => '',
        'akta_kelahiran' => '',
        'surat_keterangan_lulus' => '',
        'sertifikat_prestasi' => ''
    ];
    
    // Allowed file types
    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    $upload_success = true;
    $error_message = "";
    
    // Process each file
    foreach ($files as $field => $value) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $file_tmp = $_FILES[$field]['tmp_name'];
            $file_name = $_FILES[$field]['name'];
            $file_size = $_FILES[$field]['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validate file type
            if (!in_array($file_ext, $allowed_types)) {
                $upload_success = false;
                $error_message = "File $field harus berformat PDF, JPG, JPEG, atau PNG";
                break;
            }
            
            // Validate file size
            if ($file_size > $max_size) {
                $upload_success = false;
                $error_message = "Ukuran file $field maksimal 2MB";
                break;
            }
            
            // Generate unique filename
            $new_filename = $id_pendaftaran . "_" . $field . "_" . time() . "." . $file_ext;
            $target_file = $upload_dir . $new_filename;
            
            // Upload file
            if (move_uploaded_file($file_tmp, $target_file)) {
                $files[$field] = $new_filename;
            } else {
                $upload_success = false;
                $error_message = "Gagal mengupload file $field";
                break;
            }
        }
    }
    
    // Insert to database if upload success
    if ($upload_success) {
        try {
            $query = "INSERT INTO berkas (id_pendaftaran, ijazah, kk, foto, akta_kelahiran, 
                      surat_keterangan_lulus, sertifikat_prestasi, status_berkas, catatan_berkas) 
                      VALUES (:id_pendaftaran, :ijazah, :kk, :foto, :akta_kelahiran, 
                      :surat_keterangan_lulus, :sertifikat_prestasi, 'Menunggu Verifikasi', :catatan_berkas)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_pendaftaran', $id_pendaftaran);
            $stmt->bindParam(':ijazah', $files['ijazah']);
            $stmt->bindParam(':kk', $files['kk']);
            $stmt->bindParam(':foto', $files['foto']);
            $stmt->bindParam(':akta_kelahiran', $files['akta_kelahiran']);
            $stmt->bindParam(':surat_keterangan_lulus', $files['surat_keterangan_lulus']);
            $stmt->bindParam(':sertifikat_prestasi', $files['sertifikat_prestasi']);
            $stmt->bindParam(':catatan_berkas', $catatan_berkas);
            
            if ($stmt->execute()) {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Berkas berhasil diupload dan menunggu verifikasi'
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
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Upload Gagal!',
                text: '$error_message'
            });
        </script>";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cloud-upload"></i> Upload Berkas Pendaftaran</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="row">
                <!-- Pilih Pendaftaran -->
                <div class="col-md-12 mb-4">
                    <h5 class="text-primary"><i class="bi bi-person-check"></i> Data Pendaftaran</h5>
                    <hr>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Pilih Pendaftaran <span class="text-danger">*</span></label>
                    <select name="id_pendaftaran" class="form-select" required>
                        <option value="">-- Pilih Nomor Pendaftaran --</option>
                        <?php while ($row = $stmt_pendaftaran->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?php echo $row['id_pendaftaran']; ?>">
                            <?php echo $row['no_pendaftaran'] . " - " . $row['nama_lengkap'] . " (" . $row['nama_prodi'] . ")"; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="text-muted">Pilih nomor pendaftaran yang akan diupload berkasnya</small>
                </div>

                <!-- Upload Files -->
                <div class="col-md-12 mb-4 mt-3">
                    <h5 class="text-primary"><i class="bi bi-file-earmark-arrow-up"></i> Upload Berkas</h5>
                    <hr>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Ketentuan Upload:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Format file: PDF, JPG, JPEG, atau PNG</li>
                            <li>Maksimal ukuran file: 2MB per file</li>
                            <li>File yang wajib: Ijazah, KK, dan Foto</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Ijazah/STTB <span class="text-danger">*</span></label>
                    <input type="file" name="ijazah" class="form-control" required>
                    <small class="text-muted">Upload scan/foto ijazah terakhir</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Kartu Keluarga (KK) <span class="text-danger">*</span></label>
                    <input type="file" name="kk" class="form-control" required>
                    <small class="text-muted">Upload scan/foto kartu keluarga</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Pas Foto 3x4 <span class="text-danger">*</span></label>
                    <input type="file" name="foto" class="form-control" required>
                    <small class="text-muted">Upload pas foto ukuran 3x4 (Background merah/biru)</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Akta Kelahiran</label>
                    <input type="file" name="akta_kelahiran" class="form-control">
                    <small class="text-muted">Upload scan/foto akta kelahiran (opsional)</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Surat Keterangan Lulus</label>
                    <input type="file" name="surat_keterangan_lulus" class="form-control">
                    <small class="text-muted">Untuk siswa kelas 12 yang belum lulus (opsional)</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Sertifikat Prestasi</label>
                    <input type="file" name="sertifikat_prestasi" class="form-control">
                    <small class="text-muted">Upload sertifikat prestasi jika ada (opsional)</small>
                </div>

                <!-- Catatan -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan_berkas" class="form-control" rows="3" 
                              placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="reset" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-cloud-upload"></i> Upload Berkas
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
