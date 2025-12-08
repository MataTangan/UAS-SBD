<?php
$page_title = "Edit Berkas";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get ID
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID tidak ditemukan.');

// Get existing data
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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $catatan_berkas = $_POST['catatan_berkas'];
    
    // Upload directory
    $upload_dir = "../../uploads/berkas/";
    
    // Array untuk menyimpan nama file (gunakan file lama jika tidak ada upload baru)
    $files = [
        'ijazah' => $row['ijazah'],
        'kk' => $row['kk'],
        'foto' => $row['foto'],
        'akta_kelahiran' => $row['akta_kelahiran'],
        'surat_keterangan_lulus' => $row['surat_keterangan_lulus'],
        'sertifikat_prestasi' => $row['sertifikat_prestasi']
    ];
    
    // Allowed file types
    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    $upload_success = true;
    $error_message = "";
    
    // Process each file only if new file is uploaded
    foreach ($files as $field => $old_file) {
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
            
            // Delete old file if exists
            if ($old_file && file_exists($upload_dir . $old_file)) {
                unlink($upload_dir . $old_file);
            }
            
            // Generate unique filename
            $new_filename = $row['id_pendaftaran'] . "_" . $field . "_" . time() . "." . $file_ext;
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
    
    // Update database if upload success
    if ($upload_success) {
        try {
            $query = "UPDATE berkas SET 
                      ijazah = :ijazah,
                      kk = :kk,
                      foto = :foto,
                      akta_kelahiran = :akta_kelahiran,
                      surat_keterangan_lulus = :surat_keterangan_lulus,
                      sertifikat_prestasi = :sertifikat_prestasi,
                      catatan_berkas = :catatan_berkas,
                      status_berkas = 'Menunggu Verifikasi',
                      tanggal_upload = CURRENT_TIMESTAMP
                      WHERE id_berkas = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':ijazah', $files['ijazah']);
            $stmt->bindParam(':kk', $files['kk']);
            $stmt->bindParam(':foto', $files['foto']);
            $stmt->bindParam(':akta_kelahiran', $files['akta_kelahiran']);
            $stmt->bindParam(':surat_keterangan_lulus', $files['surat_keterangan_lulus']);
            $stmt->bindParam(':sertifikat_prestasi', $files['sertifikat_prestasi']);
            $stmt->bindParam(':catatan_berkas', $catatan_berkas);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Berkas berhasil diupdate'
                    }).then(() => {
                        window.location.href = 'view.php?id=$id';
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

// File list
$files_list = [
    'ijazah' => 'Ijazah/STTB',
    'kk' => 'Kartu Keluarga',
    'foto' => 'Pas Foto',
    'akta_kelahiran' => 'Akta Kelahiran',
    'surat_keterangan_lulus' => 'Surat Keterangan Lulus',
    'sertifikat_prestasi' => 'Sertifikat Prestasi'
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil-square"></i> Edit Berkas Pendaftaran</h2>
    <a href="view.php?id=<?php echo $id; ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<!-- Info Pendaftaran -->
<div class="alert alert-info">
    <h5><i class="bi bi-info-circle"></i> Informasi Pendaftaran</h5>
    <table class="table table-sm mb-0">
        <tr>
            <td width="150"><strong>No. Pendaftaran</strong></td>
            <td><?php echo htmlspecialchars($row['no_pendaftaran']); ?></td>
        </tr>
        <tr>
            <td><strong>Nama</strong></td>
            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
        </tr>
        <tr>
            <td><strong>Program Studi</strong></td>
            <td><?php echo htmlspecialchars($row['nama_prodi']); ?></td>
        </tr>
    </table>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> 
                <strong>Perhatian:</strong> Jika Anda mengupload file baru, file lama akan diganti. 
                Kosongkan jika tidak ingin mengganti file.
            </div>

            <div class="row">
                <?php foreach ($files_list as $field => $label): ?>
                <div class="col-md-6 mb-4">
                    <label class="form-label"><strong><?php echo $label; ?></strong></label>
                    
                    <!-- Current File Preview -->
                    <?php if (!empty($row[$field])): ?>
                    <div class="card mb-2">
                        <div class="card-body p-2 bg-light">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="bi bi-file-earmark-check text-success"></i>
                                    <small class="ms-2">File saat ini: <strong><?php echo $row[$field]; ?></strong></small>
                                </div>
                                <a href="../../uploads/berkas/<?php echo $row[$field]; ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-secondary py-2 mb-2">
                        <small><i class="bi bi-x-circle"></i> Belum ada file</small>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Upload New File -->
                    <input type="file" name="<?php echo $field; ?>" class="form-control">
                    <small class="text-muted">Upload file baru untuk mengganti (Max: 2MB, Format: PDF/JPG/PNG)</small>
                </div>
                <?php endforeach; ?>

                <!-- Catatan -->
                <div class="col-md-12 mb-3">
                    <label class="form-label"><strong>Catatan</strong></label>
                    <textarea name="catatan_berkas" class="form-control" rows="3" 
                              placeholder="Tambahkan catatan jika diperlukan..."><?php echo htmlspecialchars($row['catatan_berkas']); ?></textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Berkas
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
