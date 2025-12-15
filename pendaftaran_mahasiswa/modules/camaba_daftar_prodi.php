<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'camaba') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$id_camaba = $_SESSION['user_id'];

// Get data prodi dengan fakultas
$query_prodi = "SELECT p.*, f.nama_fakultas 
                FROM prodi p
                JOIN fakultas f ON p.id_fakultas = f.id_fakultas
                WHERE p.is_active = 1
                ORDER BY f.nama_fakultas, p.nama_prodi";
$stmt_prodi = $db->query($query_prodi);

// Generate nomor pendaftaran
function generateNoPendaftaran($db) {
    $tahun = date('Y');
    $query = "SELECT COUNT(*) FROM pendaftaran WHERE YEAR(tanggal_daftar) = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$tahun]);
    $count = $stmt->fetchColumn() + 1;
    return "PMB-" . $tahun . "-" . str_pad($count, 5, '0', STR_PAD_LEFT);
}

// Process form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $no_pendaftaran = generateNoPendaftaran($db);
        $tahun_akademik = date('Y') . "/" . (date('Y') + 1);
        
        $query = "INSERT INTO pendaftaran 
                  (id_camaba, id_prodi, no_pendaftaran, jalur_masuk, tahun_akademik, 
                   semester, status_pendaftaran) 
                  VALUES 
                  (:id_camaba, :id_prodi, :no_pendaftaran, :jalur_masuk, :tahun_akademik, 
                   :semester, 'Diajukan')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_camaba', $id_camaba);
        $stmt->bindParam(':id_prodi', $_POST['id_prodi']);
        $stmt->bindParam(':no_pendaftaran', $no_pendaftaran);
        $stmt->bindParam(':jalur_masuk', $_POST['jalur_masuk']);
        $stmt->bindParam(':tahun_akademik', $tahun_akademik);
        $stmt->bindParam(':semester', $_POST['semester']);
        
        if ($stmt->execute()) {
            $last_id = $db->lastInsertId();
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: 'Pendaftaran berhasil!<br>No. Pendaftaran: <strong>$no_pendaftaran</strong>',
                    confirmButtonColor: '#667eea'
                }).then(() => {
                    window.location.href = 'camaba_upload_berkas.php?id_pendaftaran=$last_id';
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

include 'includes/header_camaba.php';
?>

<style>
    .prodi-card {
        border: 2px solid #e0e0e0;
        border-radius: 15px;
        transition: all 0.3s;
        cursor: pointer;
        height: 100%;
    }
    .prodi-card:hover {
        border-color: #667eea;
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
    }
    .prodi-card.selected {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.05);
    }
    .prodi-card input[type="radio"] {
        display: none;
    }
</style>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="camaba_dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Daftar Program Studi</li>
        </ol>
    </nav>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-clipboard-plus me-2"></i>Pendaftaran Program Studi</h4>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Informasi:</strong> Pilih program studi yang Anda inginkan, kemudian lengkapi data pendaftaran di bawah ini.
            </div>

            <form method="POST" action="" id="formDaftar">
                <!-- Pilih Prodi -->
                <h5 class="mb-3"><i class="bi bi-book me-2"></i>Pilih Program Studi</h5>
                <div class="row g-3 mb-4">
                    <?php 
                    $current_fakultas = '';
                    while ($prodi = $stmt_prodi->fetch(PDO::FETCH_ASSOC)): 
                        if ($current_fakultas != $prodi['nama_fakultas']):
                            if ($current_fakultas != '') echo '</div></div>';
                            $current_fakultas = $prodi['nama_fakultas'];
                    ?>
                        <div class="col-12">
                            <h6 class="text-muted mt-3"><i class="bi bi-building me-2"></i><?php echo htmlspecialchars($prodi['nama_fakultas']); ?></h6>
                            <div class="row g-3">
                    <?php endif; ?>
                    
                        <div class="col-md-6">
                            <input type="radio" name="id_prodi" value="<?php echo $prodi['id_prodi']; ?>" 
                                   id="prodi_<?php echo $prodi['id_prodi']; ?>" required>
                            <label for="prodi_<?php echo $prodi['id_prodi']; ?>" class="prodi-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($prodi['nama_prodi']); ?></h6>
                                            <span class="badge bg-primary"><?php echo $prodi['jenjang']; ?></span>
                                        </div>
                                        <i class="bi bi-check-circle-fill fs-4 text-success" style="display:none;"></i>
                                    </div>
                                    <hr>
                                    <div class="row g-2 small">
                                        <div class="col-6">
                                            <i class="bi bi-people text-primary me-1"></i>
                                            Kuota: <strong><?php echo $prodi['kuota']; ?></strong>
                                        </div>
                                        <div class="col-6">
                                            <i class="bi bi-cash text-success me-1"></i>
                                            Rp <?php echo number_format($prodi['biaya_pendaftaran'], 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    
                    <?php endwhile; ?>
                            </div>
                        </div>
                </div>

                <hr class="my-4">

                <!-- Data Pendaftaran -->
                <h5 class="mb-3"><i class="bi bi-pencil-square me-2"></i>Data Pendaftaran</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Jalur Masuk <span class="text-danger">*</span></label>
                        <select name="jalur_masuk" class="form-select" required>
                            <option value="">-- Pilih Jalur --</option>
                            <option value="Reguler">Reguler</option>
                            <option value="Prestasi">Prestasi</option>
                            <option value="Mandiri">Mandiri</option>
                            <option value="KIP">KIP (Kartu Indonesia Pintar)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Semester <span class="text-danger">*</span></label>
                        <select name="semester" class="form-select" required>
                            <option value="">-- Pilih Semester --</option>
                            <option value="Ganjil">Ganjil (Agustus - Januari)</option>
                            <option value="Genap">Genap (Februari - Juli)</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Perhatian:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Pastikan data yang Anda isi sudah benar</li>
                                <li>Nomor pendaftaran akan digenerate otomatis</li>
                                <li>Setelah mendaftar, Anda akan diarahkan untuk upload berkas</li>
                                <li>Biaya pendaftaran harus dibayarkan sesuai program studi yang dipilih</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between">
                    <a href="camaba_dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Daftar Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Handle prodi card selection
document.querySelectorAll('.prodi-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.prodi-card').forEach(c => {
            c.classList.remove('selected');
            c.querySelector('.bi-check-circle-fill').style.display = 'none';
        });
        this.classList.add('selected');
        this.querySelector('.bi-check-circle-fill').style.display = 'block';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
