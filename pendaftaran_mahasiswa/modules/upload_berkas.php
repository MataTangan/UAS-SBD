<?php
session_start();
// PERBAIKAN: Gunakan '../' (naik 1 level)
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'camaba') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$id_camaba = $_SESSION['user_id'];

// 1. Dapatkan ID Pendaftaran (Dari URL atau ambil yang terakhir)
if (isset($_GET['id_pendaftaran'])) {
    $id_pendaftaran = $_GET['id_pendaftaran'];
} else {
    // Ambil pendaftaran terakhir
    $stmt = $db->prepare("SELECT id_pendaftaran FROM pendaftaran WHERE id_camaba = ? ORDER BY id_pendaftaran DESC LIMIT 1");
    $stmt->execute([$id_camaba]);
    $pendaftaran = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pendaftaran) {
        echo "<script>alert('Silakan daftar prodi terlebih dahulu!'); window.location='daftar_prodi.php';</script>";
        exit();
    }
    $id_pendaftaran = $pendaftaran['id_pendaftaran'];
}

// 2. Cek apakah berkas sudah ada di database
$stmt_berkas = $db->prepare("SELECT * FROM berkas WHERE id_pendaftaran = ?");
$stmt_berkas->execute([$id_pendaftaran]);
$existing_berkas = $stmt_berkas->fetch(PDO::FETCH_ASSOC);

// Proses Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // PERBAIKAN: Path upload juga cukup '../uploads/'
    $upload_dir = "../uploads/berkas/";
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);

    $fields = ['ijazah', 'kk', 'foto', 'akta_kelahiran', 'surat_keterangan_lulus', 'sertifikat_prestasi'];
    $data_file = [];
    
    // Siapkan data file (pakai lama jika tidak ada upload baru)
    foreach ($fields as $f) {
        $data_file[$f] = $existing_berkas[$f] ?? '';
    }

    $upload_ok = true;
    $error_msg = "";

    foreach ($fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $file_ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            
            // Validasi
            if (!in_array($file_ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $upload_ok = false; $error_msg = "Format file salah (harus PDF/JPG)."; break;
            }
            if ($_FILES[$field]['size'] > 5*1024*1024) { // 5MB
                $upload_ok = false; $error_msg = "Ukuran file terlalu besar (Max 5MB)."; break;
            }

            // Nama unik
            $new_name = $id_pendaftaran . "_" . $field . "_" . time() . "." . $file_ext;
            
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $upload_dir . $new_name)) {
                // Hapus file lama fisik jika ada
                if (!empty($data_file[$field]) && file_exists($upload_dir . $data_file[$field])) {
                    unlink($upload_dir . $data_file[$field]);
                }
                $data_file[$field] = $new_name;
            }
        }
    }

    if ($upload_ok) {
        try {
            if ($existing_berkas) {
                // UPDATE
                $sql = "UPDATE berkas SET 
                        ijazah=:ijazah, kk=:kk, foto=:foto, akta_kelahiran=:akta, 
                        surat_keterangan_lulus=:skl, sertifikat_prestasi=:sertif,
                        status_berkas='Menunggu Verifikasi', tanggal_upload=NOW(), catatan_berkas=:catatan
                        WHERE id_berkas=:id";
                $stmt = $db->prepare($sql);
                $params = [
                    ':ijazah' => $data_file['ijazah'], ':kk' => $data_file['kk'], ':foto' => $data_file['foto'],
                    ':akta' => $data_file['akta_kelahiran'], ':skl' => $data_file['surat_keterangan_lulus'],
                    ':sertif' => $data_file['sertifikat_prestasi'], ':catatan' => $_POST['catatan_berkas'],
                    ':id' => $existing_berkas['id_berkas']
                ];
            } else {
                // INSERT
                $sql = "INSERT INTO berkas (id_pendaftaran, ijazah, kk, foto, akta_kelahiran, surat_keterangan_lulus, sertifikat_prestasi, status_berkas, catatan_berkas)
                        VALUES (:id_pend, :ijazah, :kk, :foto, :akta, :skl, :sertif, 'Menunggu Verifikasi', :catatan)";
                $stmt = $db->prepare($sql);
                $params = [
                    ':id_pend' => $id_pendaftaran, ':ijazah' => $data_file['ijazah'], ':kk' => $data_file['kk'], 
                    ':foto' => $data_file['foto'], ':akta' => $data_file['akta_kelahiran'], 
                    ':skl' => $data_file['surat_keterangan_lulus'], ':sertif' => $data_file['sertifikat_prestasi'],
                    ':catatan' => $_POST['catatan_berkas']
                ];
            }
            $stmt->execute($params);
            
            // Refresh data setelah save
            $stmt_berkas->execute([$id_pendaftaran]);
            $existing_berkas = $stmt_berkas->fetch(PDO::FETCH_ASSOC);
            $success_msg = "Berkas berhasil disimpan!";
            
        } catch (PDOException $e) {
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}

// Label Helper
$labels = [
    'ijazah' => 'Ijazah/STTB (Wajib)', 'kk' => 'Kartu Keluarga (Wajib)', 'foto' => 'Pas Foto 3x4 (Wajib)',
    'akta_kelahiran' => 'Akta Kelahiran', 'surat_keterangan_lulus' => 'Surat Keterangan Lulus', 'sertifikat_prestasi' => 'Sertifikat Prestasi'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Berkas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-cloud-arrow-up me-2"></i>Upload Berkas Persyaratan</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if (isset($success_msg)): ?>
                            <script>Swal.fire('Berhasil!', '<?php echo $success_msg; ?>', 'success');</script>
                        <?php endif; ?>
                        
                        <?php if (isset($error_msg) && $error_msg): ?>
                            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                        <?php endif; ?>

                        <?php if ($existing_berkas): ?>
                            <div class="alert alert-warning mb-4">
                                <strong>Status Berkas:</strong> <?php echo $existing_berkas['status_berkas']; ?>
                                <?php if($existing_berkas['catatan_berkas']): ?>
                                    <br><small>Catatan: <?php echo htmlspecialchars($existing_berkas['catatan_berkas']); ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <?php foreach($labels as $key => $label): ?>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold"><?php echo $label; ?></label>
                                    
                                    <?php if ($existing_berkas && !empty($existing_berkas[$key])): ?>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text bg-light text-success"><i class="bi bi-check-circle"></i></span>
                                            <input type="text" class="form-control bg-light" value="File Tersimpan" readonly>
                                            <a href="../uploads/berkas/<?php echo $existing_berkas[$key]; ?>" target="_blank" class="btn btn-outline-primary">Lihat</a>
                                        </div>
                                    <?php endif; ?>

                                    <input type="file" name="<?php echo $key; ?>" class="form-control" accept=".pdf,.jpg,.jpeg,.png"
                                           <?php echo (!$existing_berkas && in_array($key, ['ijazah','kk','foto'])) ? 'required' : ''; ?>>
                                    <small class="text-muted" style="font-size:0.8rem">Format: PDF/JPG/PNG (Max 5MB)</small>
                                </div>
                                <?php endforeach; ?>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Catatan Tambahan</label>
                                    <textarea name="catatan_berkas" class="form-control" rows="2" placeholder="Catatan untuk admin..."><?php echo $existing_berkas['catatan_berkas'] ?? ''; ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-3">
                                <a href="../camaba_dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save me-2"></i>Simpan Berkas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>