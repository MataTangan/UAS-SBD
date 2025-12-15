<?php
$page_title = "Tambah Pembayaran";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get list pendaftaran yang belum lunas
$pendaftaran_query = "SELECT pd.id_pendaftaran, pd.no_pendaftaran, c.nama_lengkap, pr.nama_prodi
                      FROM pendaftaran pd
                      JOIN camaba c ON pd.id_camaba = c.id_camaba
                      JOIN prodi pr ON pd.id_prodi = pr.id_prodi
                      WHERE pd.status_pendaftaran != 'Ditolak'
                      ORDER BY pd.tanggal_daftar DESC";
$pendaftaran_list = $db->query($pendaftaran_query);

// Generate Invoice Number
function generateInvoice($db) {
    $today = date('Ymd');
    $query = "SELECT COUNT(*) as count FROM pembayaran WHERE DATE(created_at) = CURDATE()";
    $count = $db->query($query)->fetchColumn();
    $number = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    return "INV-{$today}-{$number}";
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $no_invoice = generateInvoice($db);
        $upload_dir = "uploads/";
        $bukti_bayar = null;

        // Handle file upload
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
            
            $file_ext = pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION);
            $bukti_bayar = $no_invoice . '_' . time() . '.' . $file_ext;
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (!move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $upload_dir . $bukti_bayar)) {
                throw new Exception("Gagal upload file.");
            }
        }

        // Insert data
        $query = "INSERT INTO pembayaran (id_pendaftaran, no_invoice, jenis_pembayaran, 
                  jumlah, metode_pembayaran, status_pembayaran, tanggal_bayar, 
                  bukti_bayar, catatan) 
                  VALUES (:id_pendaftaran, :no_invoice, :jenis_pembayaran, :jumlah, 
                  :metode_pembayaran, :status_pembayaran, :tanggal_bayar, :bukti_bayar, :catatan)";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':id_pendaftaran', $_POST['id_pendaftaran']);
        $stmt->bindParam(':no_invoice', $no_invoice);
        $stmt->bindParam(':jenis_pembayaran', $_POST['jenis_pembayaran']);
        $stmt->bindParam(':jumlah', $_POST['jumlah']);
        $stmt->bindParam(':metode_pembayaran', $_POST['metode_pembayaran']);
        $stmt->bindParam(':status_pembayaran', $_POST['status_pembayaran']);
        $stmt->bindParam(':tanggal_bayar', $_POST['tanggal_bayar']);
        $stmt->bindParam(':bukti_bayar', $bukti_bayar);
        $stmt->bindParam(':catatan', $_POST['catatan']);
        
        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Pembayaran berhasil ditambahkan dengan Invoice: {$no_invoice}'
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
    <h2><i class="bi bi-plus-circle"></i> Tambah Pembayaran Baru</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="row">
                <!-- Data Pendaftaran -->
                <div class="col-md-12">
                    <h5 class="mb-3 text-primary"><i class="bi bi-file-earmark-text"></i> Data Pendaftaran</h5>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pilih Pendaftaran <span class="text-danger">*</span></label>
                    <select name="id_pendaftaran" class="form-select" required>
                        <option value="">-- Pilih Pendaftaran --</option>
                        <?php while ($pd = $pendaftaran_list->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?php echo $pd['id_pendaftaran']; ?>">
                                <?php echo $pd['no_pendaftaran']; ?> - <?php echo $pd['nama_lengkap']; ?> 
                                (<?php echo $pd['nama_prodi']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="text-muted">Pilih data pendaftaran yang akan dibayar</small>
                </div>

                <!-- Data Pembayaran -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary"><i class="bi bi-credit-card"></i> Detail Pembayaran</h5>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Pembayaran <span class="text-danger">*</span></label>
                    <select name="jenis_pembayaran" class="form-select" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="Pendaftaran">Pendaftaran</option>
                        <option value="UKT">UKT (Uang Kuliah Tunggal)</option>
                        <option value="Registrasi">Registrasi</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah Bayar <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="jumlah" class="form-control" 
                               placeholder="0" min="0" step="1000" required>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select name="metode_pembayaran" class="form-select" required>
                        <option value="">-- Pilih Metode --</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="Virtual Account">Virtual Account</option>
                        <option value="E-Wallet">E-Wallet (OVO, GoPay, Dana)</option>
                        <option value="Tunai">Tunai</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Pembayaran <span class="text-danger">*</span></label>
                    <select name="status_pembayaran" class="form-select" required>
                        <option value="Pending">Pending (Menunggu Verifikasi)</option>
                        <option value="Berhasil">Berhasil</option>
                        <option value="Gagal">Gagal</option>
                        <option value="Expired">Expired</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Bayar</label>
                    <input type="datetime-local" name="tanggal_bayar" class="form-control" 
                           value="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Upload Bukti Bayar</label>
                    <input type="file" name="bukti_bayar" class="form-control" 
                           accept=".jpg,.jpeg,.png,.pdf">
                    <small class="text-muted">Format: JPG, PNG, PDF (Max 5MB)</small>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="3" 
                              placeholder="Catatan tambahan (opsional)"></textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                <strong>Info:</strong> Nomor invoice akan digenerate otomatis saat data disimpan.
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="reset" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Bukti Bayar -->
<script>
document.querySelector('input[name="bukti_bayar"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileSize = file.size / 1024 / 1024; // MB
        if (fileSize > 5) {
            Swal.fire({
                icon: 'error',
                title: 'File Terlalu Besar',
                text: 'Ukuran file maksimal 5MB'
            });
            e.target.value = '';
        }
    }
});
</script>

<?php include '../../includes/footer.php'; ?>