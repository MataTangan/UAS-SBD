<?php
$page_title = "Edit Pembayaran";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get ID
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID tidak ditemukan.');

// Get existing data
$query = "SELECT p.*, pd.no_pendaftaran, c.nama_lengkap, pr.nama_prodi
          FROM pembayaran p
          JOIN pendaftaran pd ON p.id_pendaftaran = pd.id_pendaftaran
          JOIN camaba c ON pd.id_camaba = c.id_camaba
          JOIN prodi pr ON pd.id_prodi = pr.id_prodi
          WHERE p.id_pembayaran = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('ERROR: Data pembayaran tidak ditemukan.');
}

// Update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $upload_dir = "uploads/";
        $bukti_bayar = $row['bukti_bayar']; // Keep existing file

        // Handle new file upload
        if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
            $file_type = $_FILES['bukti_bayar']['type'];
            $file_size = $_FILES['bukti_bayar']['size'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Tipe file tidak diizinkan. Hanya JPG, PNG, dan PDF.");
            }
            
            if ($file_size > 5000000) { // 5MB
                throw new Exception("Ukuran file terlalu besar. Maksimal 5MB.");
            }
            
            // Delete old file if exists
            if ($row['bukti_bayar'] && file_exists($upload_dir . $row['bukti_bayar'])) {
                unlink($upload_dir . $row['bukti_bayar']);
            }
            
            $file_ext = pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION);
            $bukti_bayar = $row['no_invoice'] . '_' . time() . '.' . $file_ext;
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (!move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $upload_dir . $bukti_bayar)) {
                throw new Exception("Gagal upload file.");
            }
        }

        // Update query
        $query = "UPDATE pembayaran SET 
                  jenis_pembayaran = :jenis_pembayaran,
                  jumlah = :jumlah,
                  metode_pembayaran = :metode_pembayaran,
                  status_pembayaran = :status_pembayaran,
                  tanggal_bayar = :tanggal_bayar,
                  bukti_bayar = :bukti_bayar,
                  catatan = :catatan
                  WHERE id_pembayaran = :id";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':jenis_pembayaran', $_POST['jenis_pembayaran']);
        $stmt->bindParam(':jumlah', $_POST['jumlah']);
        $stmt->bindParam(':metode_pembayaran', $_POST['metode_pembayaran']);
        $stmt->bindParam(':status_pembayaran', $_POST['status_pembayaran']);
        $stmt->bindParam(':tanggal_bayar', $_POST['tanggal_bayar']);
        $stmt->bindParam(':bukti_bayar', $bukti_bayar);
        $stmt->bindParam(':catatan', $_POST['catatan']);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data pembayaran berhasil diupdate'
                }).then(() => {
                    window.location.href = 'index.php';
                });
            </script>";
        }
    } catch (Exception $e) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '" . addslashes($e->getMessage()) . "'
            });
        </script>";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil-square"></i> Edit Pembayaran</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="row">
                <!-- Info Pendaftaran (Read Only) -->
                <div class="col-md-12">
                    <h5 class="mb-3 text-primary"><i class="bi bi-info-circle"></i> Informasi Pendaftaran</h5>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Invoice</label>
                    <input type="text" class="form-control" value="<?php echo $row['no_invoice']; ?>" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Pendaftaran</label>
                    <input type="text" class="form-control" value="<?php echo $row['no_pendaftaran']; ?>" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Mahasiswa</label>
                    <input type="text" class="form-control" value="<?php echo $row['nama_lengkap']; ?>" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Program Studi</label>
                    <input type="text" class="form-control" value="<?php echo $row['nama_prodi']; ?>" readonly>
                </div>

                <!-- Edit Data Pembayaran -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary"><i class="bi bi-pencil"></i> Edit Detail Pembayaran</h5>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Pembayaran <span class="text-danger">*</span></label>
                    <select name="jenis_pembayaran" class="form-select" required>
                        <option value="Pendaftaran" <?php echo $row['jenis_pembayaran']=='Pendaftaran'?'selected':''; ?>>Pendaftaran</option>
                        <option value="UKT" <?php echo $row['jenis_pembayaran']=='UKT'?'selected':''; ?>>UKT (Uang Kuliah Tunggal)</option>
                        <option value="Registrasi" <?php echo $row['jenis_pembayaran']=='Registrasi'?'selected':''; ?>>Registrasi</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah Bayar <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="jumlah" class="form-control" 
                               value="<?php echo $row['jumlah']; ?>" min="0" step="1000" required>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select name="metode_pembayaran" class="form-select" required>
                        <option value="Transfer Bank" <?php echo $row['metode_pembayaran']=='Transfer Bank'?'selected':''; ?>>Transfer Bank</option>
                        <option value="Virtual Account" <?php echo $row['metode_pembayaran']=='Virtual Account'?'selected':''; ?>>Virtual Account</option>
                        <option value="E-Wallet" <?php echo $row['metode_pembayaran']=='E-Wallet'?'selected':''; ?>>E-Wallet</option>
                        <option value="Tunai" <?php echo $row['metode_pembayaran']=='Tunai'?'selected':''; ?>>Tunai</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Pembayaran <span class="text-danger">*</span></label>
                    <select name="status_pembayaran" class="form-select" required>
                        <option value="Pending" <?php echo $row['status_pembayaran']=='Pending'?'selected':''; ?>>Pending</option>
                        <option value="Berhasil" <?php echo $row['status_pembayaran']=='Berhasil'?'selected':''; ?>>Berhasil</option>
                        <option value="Gagal" <?php echo $row['status_pembayaran']=='Gagal'?'selected':''; ?>>Gagal</option>
                        <option value="Expired" <?php echo $row['status_pembayaran']=='Expired'?'selected':''; ?>>Expired</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Bayar</label>
                    <input type="datetime-local" name="tanggal_bayar" class="form-control" 
                           value="<?php echo $row['tanggal_bayar'] ? date('Y-m-d\TH:i', strtotime($row['tanggal_bayar'])) : ''; ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Bukti Bayar</label>
                    <?php if ($row['bukti_bayar']): ?>
                        <div class="mb-2">
                            <a href="uploads/<?php echo $row['bukti_bayar']; ?>" target="_blank" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Lihat Bukti Saat Ini
                            </a>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="bukti_bayar" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    <small class="text-muted">Upload file baru untuk mengganti bukti bayar (Max 5MB)</small>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="3"><?php echo htmlspecialchars($row['catatan']); ?></textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>