<?php
$page_title = "Verifikasi Pembayaran";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID tidak ditemukan.');

// Get data
$query = "SELECT p.*, pd.no_pendaftaran, c.nama_lengkap, c.email, c.no_hp, 
          pr.nama_prodi, f.nama_fakultas
          FROM pembayaran p
          JOIN pendaftaran pd ON p.id_pendaftaran = pd.id_pendaftaran
          JOIN camaba c ON pd.id_camaba = c.id_camaba
          JOIN prodi pr ON pd.id_prodi = pr.id_prodi
          JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
          WHERE p.id_pembayaran = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) die('Data tidak ditemukan');

// Process verification
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $status = $_POST['status_verifikasi'];
        $catatan = $_POST['catatan_verifikasi'];
        $id_verifikator = 1; // Ganti dengan session admin
        
        $query = "UPDATE pembayaran SET status_pembayaran=:status, tanggal_verifikasi=NOW(), 
                  id_verifikator=:verifikator, catatan=:catatan WHERE id_pembayaran=:id";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':status' => $status,
            ':verifikator' => $id_verifikator,
            ':catatan' => $catatan,
            ':id' => $id
        ]);
        
        $msg = $status == 'Berhasil' ? 'diverifikasi dan disetujui' : 'ditolak';
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Pembayaran berhasil {$msg}',
                confirmButtonColor: '#667eea'
            }).then(() => window.location.href = 'index.php');
        </script>";
    } catch (Exception $e) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '" . addslashes($e->getMessage()) . "',
                confirmButtonColor: '#dc3545'
            });
        </script>";
    }
}
?>

<style>
    .verify-card {
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .detail-item {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 0.75rem;
        transition: all 0.3s;
    }
    .detail-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    .detail-label {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }
    .detail-value {
        color: #212529;
        font-weight: 600;
        font-size: 1.1rem;
    }
    .bukti-container {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        position: sticky;
        top: 20px;
    }
    .verify-form {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        padding: 2rem;
    }
    .form-control-custom {
        border-radius: 15px;
        border: 2px solid rgba(255,255,255,0.3);
        background: rgba(255,255,255,0.1);
        color: white;
        padding: 1rem;
    }
    .form-control-custom::placeholder {
        color: rgba(255,255,255,0.6);
    }
    .form-control-custom:focus {
        background: rgba(255,255,255,0.2);
        border-color: white;
        color: white;
    }
    .badge-amount {
        font-size: 1.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 15px;
    }
    .status-pending {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 1rem;
        border-radius: 15px;
        text-align: center;
        margin-bottom: 1.5rem;
    }
</style>

<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-shield-check"></i> Verifikasi Pembayaran</h2>
            <p class="text-muted mb-0">Proses verifikasi pembayaran mahasiswa</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary" style="border-radius:15px;">
            <i class="bi bi-arrow-left me-2"></i> Kembali
        </a>
    </div>

    <div class="row g-4">
        <!-- Left Column - Detail -->
        <div class="col-lg-7">
            <!-- Status Badge -->
            <?php if ($row['status_pembayaran'] == 'Pending'): ?>
            <div class="status-pending">
                <i class="bi bi-hourglass-split fs-2 d-block mb-2"></i>
                <h5 class="mb-0">Status: Menunggu Verifikasi</h5>
            </div>
            <?php endif; ?>

            <!-- Invoice Info -->
            <div class="card verify-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0"><i class="bi bi-receipt-cutoff me-2"></i>Informasi Invoice</h5>
                        <span class="badge badge-amount bg-primary">
                            <?php echo $row['no_invoice']; ?>
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label"><i class="bi bi-person me-1"></i> Nama Mahasiswa</div>
                                <div class="detail-value"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label"><i class="bi bi-envelope me-1"></i> Email</div>
                                <div class="detail-value"><?php echo htmlspecialchars($row['email']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label"><i class="bi bi-book me-1"></i> Program Studi</div>
                                <div class="detail-value"><?php echo htmlspecialchars($row['nama_prodi']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($row['nama_fakultas']); ?></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label"><i class="bi bi-phone me-1"></i> No. Handphone</div>
                                <div class="detail-value"><?php echo htmlspecialchars($row['no_hp']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="card verify-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-4"><i class="bi bi-credit-card me-2"></i>Detail Pembayaran</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">No. Pendaftaran</div>
                                <div class="detail-value"><?php echo $row['no_pendaftaran']; ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">Jenis Pembayaran</div>
                                <div class="detail-value">
                                    <span class="badge bg-info"><?php echo $row['jenis_pembayaran']; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="detail-item" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                                <div class="detail-label text-white">Jumlah Pembayaran</div>
                                <div class="detail-value text-white display-6">
                                    Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">Metode Pembayaran</div>
                                <div class="detail-value"><?php echo $row['metode_pembayaran']; ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">Tanggal Bayar</div>
                                <div class="detail-value">
                                    <?php echo $row['tanggal_bayar'] ? date('d M Y, H:i', strtotime($row['tanggal_bayar'])) : '-'; ?>
                                </div>
                            </div>
                        </div>
                        <?php if ($row['catatan']): ?>
                        <div class="col-12">
                            <div class="detail-item">
                                <div class="detail-label">Catatan</div>
                                <div class="detail-value"><?php echo htmlspecialchars($row['catatan']); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Form Verifikasi -->
            <form method="POST" class="verify-form">
                <h5 class="mb-4"><i class="bi bi-clipboard-check me-2"></i>Form Verifikasi</h5>
                
                <div class="mb-4">
                    <label class="form-label">Keputusan Verifikasi <span class="text-warning">*</span></label>
                    <select name="status_verifikasi" class="form-control form-control-custom" required>
                        <option value="">-- Pilih Keputusan --</option>
                        <option value="Berhasil">✓ Terima & Verifikasi Pembayaran</option>
                        <option value="Gagal">✗ Tolak Pembayaran</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Catatan Verifikasi <span class="text-warning">*</span></label>
                    <textarea name="catatan_verifikasi" class="form-control form-control-custom" 
                              rows="4" required 
                              placeholder="Berikan catatan detail mengenai hasil verifikasi..."></textarea>
                </div>

                <div class="alert alert-light border-0 mb-4" style="border-radius:15px;">
                    <h6 class="text-dark"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Perhatian</h6>
                    <ul class="small text-dark mb-0 ps-3">
                        <li>Pastikan bukti pembayaran telah diperiksa dengan teliti</li>
                        <li>Verifikasi nominal sesuai dengan yang tertera</li>
                        <li>Keputusan verifikasi bersifat final</li>
                    </ul>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-light btn-lg" style="border-radius:15px;font-weight:600;">
                        <i class="bi bi-check-circle me-2"></i> Proses Verifikasi
                    </button>
                    <a href="index.php" class="btn btn-outline-light" style="border-radius:15px;">
                        <i class="bi bi-x-circle me-2"></i> Batal
                    </a>
                </div>
            </form>
        </div>

        <!-- Right Column - Bukti -->
        <div class="col-lg-5">
            <div class="bukti-container">
                <div class="card border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h5 class="mb-0"><i class="bi bi-image me-2"></i>Bukti Pembayaran</h5>
                    </div>
                    <div class="card-body p-4 text-center">
                        <?php if ($row['bukti_bayar']): ?>
                            <?php 
                            $ext = strtolower(pathinfo($row['bukti_bayar'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png'])): 
                            ?>
                                <img src="uploads/<?php echo $row['bukti_bayar']; ?>" 
                                     class="img-fluid rounded shadow mb-3" 
                                     alt="Bukti"
                                     style="max-height: 600px;">
                                <div class="d-grid gap-2">
                                    <a href="uploads/<?php echo $row['bukti_bayar']; ?>" 
                                       target="_blank" 
                                       class="btn btn-primary btn-lg" 
                                       style="border-radius:15px;">
                                        <i class="bi bi-zoom-in me-2"></i> Lihat Full Size
                                    </a>
                                    <a href="uploads/<?php echo $row['bukti_bayar']; ?>" 
                                       download
                                       class="btn btn-outline-primary" 
                                       style="border-radius:15px;">
                                        <i class="bi bi-download me-2"></i> Download Bukti
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-file-earmark-pdf display-1 text-danger mb-3"></i>
                                    <h5>File PDF</h5>
                                    <p class="text-muted mb-4">Klik tombol di bawah untuk membuka</p>
                                    <a href="uploads/<?php echo $row['bukti_bayar']; ?>" 
                                       target="_blank" 
                                       class="btn btn-danger btn-lg" 
                                       style="border-radius:15px;">
                                        <i class="bi bi-file-pdf me-2"></i> Buka PDF
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-file-x display-1 text-muted mb-3"></i>
                                <h5 class="text-muted">Tidak Ada Bukti</h5>
                                <p class="text-muted">Bukti pembayaran belum diupload</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>