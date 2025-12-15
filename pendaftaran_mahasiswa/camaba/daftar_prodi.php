<?php
session_start();
// PERBAIKAN: Gunakan '../' (naik 1 level), bukan '../../'
require_once '../config/database.php';

// Cek Login Camaba
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'camaba') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$id_camaba = $_SESSION['user_id'];

// Ambil data Program Studi
$query_prodi = "SELECT p.*, f.nama_fakultas 
                FROM prodi p
                JOIN fakultas f ON p.id_fakultas = f.id_fakultas
                ORDER BY f.nama_fakultas, p.nama_prodi";
$stmt_prodi = $db->query($query_prodi);

// Fungsi Generate No Pendaftaran (Format: PMB-YYYY-XXXXX)
function generateNoPendaftaran($db) {
    $tahun = date('Y');
    $query = "SELECT COUNT(*) FROM pendaftaran WHERE YEAR(tanggal_daftar) = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$tahun]);
    $count = $stmt->fetchColumn() + 1;
    return "PMB-" . $tahun . "-" . str_pad($count, 5, '0', STR_PAD_LEFT);
}

// Proses Form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $no_pendaftaran = generateNoPendaftaran($db);
        $tahun_akademik = date('Y') . "/" . (date('Y') + 1);
        
        $query = "INSERT INTO pendaftaran 
                  (id_camaba, id_prodi, no_pendaftaran, jalur_masuk, tahun_akademik, semester, status_pendaftaran) 
                  VALUES 
                  (:id_camaba, :id_prodi, :no_pendaftaran, :jalur_masuk, :tahun_akademik, :semester, 'Diajukan')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_camaba', $id_camaba);
        $stmt->bindParam(':id_prodi', $_POST['id_prodi']);
        $stmt->bindParam(':no_pendaftaran', $no_pendaftaran);
        $stmt->bindParam(':jalur_masuk', $_POST['jalur_masuk']);
        $stmt->bindParam(':tahun_akademik', $tahun_akademik);
        $stmt->bindParam(':semester', $_POST['semester']);
        
        if ($stmt->execute()) {
            // Redirect ke upload berkas dengan ID pendaftaran yang baru dibuat
            $last_id = $db->lastInsertId();
            header("Location: upload_berkas.php?id_pendaftaran=" . $last_id);
            exit();
        }
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Program Studi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Daftar Program Studi Baru</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Pilih Program Studi</label>
                                <select name="id_prodi" class="form-select" required>
                                    <option value="">-- Pilih Prodi --</option>
                                    <?php while($row = $stmt_prodi->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $row['id_prodi']; ?>">
                                            <?php echo $row['nama_fakultas'] . " - " . $row['nama_prodi'] . " (" . $row['jenjang'] . ")"; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Jalur Masuk</label>
                                    <select name="jalur_masuk" class="form-select" required>
                                        <option value="Reguler">Reguler</option>
                                        <option value="Prestasi">Prestasi</option>
                                        <option value="Beasiswa">Beasiswa</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Semester</label>
                                    <select name="semester" class="form-select" required>
                                        <option value="Ganjil">Ganjil</option>
                                        <option value="Genap">Genap</option>
                                    </select>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Setelah mendaftar, Anda akan diarahkan otomatis ke halaman upload berkas.
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="../camaba_dashboard.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Lanjut & Simpan <i class="bi bi-arrow-right ms-2"></i>
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