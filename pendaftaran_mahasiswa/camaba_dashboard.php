<?php
session_start();
require_once 'config/database.php';

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'camaba') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$id_camaba = $_SESSION['user_id'];

// Get data camaba
$query_camaba = "SELECT * FROM camaba WHERE id_camaba = ?";
$stmt = $db->prepare($query_camaba);
$stmt->execute([$id_camaba]);
$camaba = $stmt->fetch(PDO::FETCH_ASSOC);

// Get pendaftaran
$query_pendaftaran = "SELECT p.*, pr.nama_prodi, f.nama_fakultas, pr.biaya_pendaftaran
                      FROM pendaftaran p
                      LEFT JOIN prodi pr ON p.id_prodi = pr.id_prodi
                      LEFT JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
                      WHERE p.id_camaba = ?
                      ORDER BY p.tanggal_daftar DESC";
$stmt_pendaftaran = $db->prepare($query_pendaftaran);
$stmt_pendaftaran->execute([$id_camaba]);

// Get berkas
$query_berkas = "SELECT b.*, p.no_pendaftaran 
                 FROM berkas b
                 JOIN pendaftaran p ON b.id_pendaftaran = p.id_pendaftaran
                 WHERE p.id_camaba = ?";
$stmt_berkas = $db->prepare($query_berkas);
$stmt_berkas->execute([$id_camaba]);

// Get ujian
$query_ujian = "SELECT u.*, p.no_pendaftaran 
                FROM ujian_seleksi u
                JOIN pendaftaran p ON u.id_pendaftaran = p.id_pendaftaran
                WHERE p.id_camaba = ?
                ORDER BY u.tanggal_ujian DESC";
$stmt_ujian = $db->prepare($query_ujian);
$stmt_ujian->execute([$id_camaba]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Camaba - <?php echo htmlspecialchars($camaba['nama_lengkap']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #f5f7fa;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .profile-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            height: 100%;
            width: 2px;
            background: #e0e0e0;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #667eea;
            border: 3px solid white;
            box-shadow: 0 0 0 3px #667eea;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-mortarboard-fill me-2"></i>
                PMB System - Camaba
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['nama']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#profileSection">
                                <i class="bi bi-person me-2"></i>Profil Saya
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="row">
            <!-- Sidebar Profile -->
            <div class="col-md-3 mb-4">
                <div class="card profile-card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-person-circle" style="font-size: 80px;"></i>
                        </div>
                        <h5 class="mb-1"><?php echo htmlspecialchars($camaba['nama_lengkap']); ?></h5>
                        <p class="mb-3 small opacity-75"><?php echo htmlspecialchars($camaba['email']); ?></p>
                        
                        <div class="bg-white bg-opacity-10 rounded p-3 mb-3 text-start">
                            <div class="mb-2">
                                <small class="opacity-75">NIK</small><br>
                                <strong><?php echo htmlspecialchars($camaba['nik']); ?></strong>
                            </div>
                            <div class="mb-2">
                                <small class="opacity-75">No. HP</small><br>
                                <strong><?php echo htmlspecialchars($camaba['no_hp']); ?></strong>
                            </div>
                            <div>
                                <small class="opacity-75">Status</small><br>
                                <?php if ($camaba['is_verified']): ?>
                                    <span class="badge bg-success">✓ Terverifikasi</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">⏳ Belum Verifikasi</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <a href="#profileSection" class="btn btn-light w-100">
                            <i class="bi bi-pencil me-2"></i>Edit Profil
                        </a>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="bi bi-graph-up me-2"></i>Statistik
                        </h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Pendaftaran:</span>
                            <strong><?php echo $stmt_pendaftaran->rowCount(); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Berkas Upload:</span>
                            <strong><?php echo $stmt_berkas->rowCount(); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Ujian Diikuti:</span>
                            <strong><?php echo $stmt_ujian->rowCount(); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Welcome Banner -->
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <h4><i class="bi bi-emoji-smile me-2"></i>Selamat Datang, <?php echo htmlspecialchars(explode(' ', $camaba['nama_lengkap'])[0]); ?>!</h4>
                        <p class="mb-3">Kelola pendaftaran dan pantau status seleksi Anda di sini.</p>
                        <div class="d-flex gap-2">
                            <a href="camaba_daftar_prodi.php" class="btn btn-light">
                                <i class="bi bi-plus-circle me-2"></i>Daftar Program Studi Baru
                            </a>
                            <a href="camaba_upload_berkas.php" class="btn btn-outline-light">
                                <i class="bi bi-cloud-upload me-2"></i>Upload Berkas
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#pendaftaran">
                            <i class="bi bi-file-earmark-text me-1"></i>Pendaftaran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#berkas">
                            <i class="bi bi-files me-1"></i>Berkas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#ujian">
                            <i class="bi bi-clipboard-check me-1"></i>Ujian
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#profile">
                            <i class="bi bi-person me-1"></i>Profil
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Tab Pendaftaran -->
                    <div class="tab-pane fade show active" id="pendaftaran">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-file-earmark-text me-2"></i>Riwayat Pendaftaran
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php 
                                $stmt_pendaftaran->execute([$id_camaba]); // Reset cursor
                                if ($stmt_pendaftaran->rowCount() > 0): 
                                ?>
                                    <?php while ($pend = $stmt_pendaftaran->fetch(PDO::FETCH_ASSOC)): ?>
                                    <div class="card mb-3 border">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($pend['no_pendaftaran']); ?></h6>
                                                    <p class="text-muted mb-0">
                                                        <i class="bi bi-building me-1"></i>
                                                        <?php echo htmlspecialchars($pend['nama_fakultas']); ?> - 
                                                        <?php echo htmlspecialchars($pend['nama_prodi']); ?>
                                                    </p>
                                                </div>
                                                <?php
                                                $status_class = [
                                                    'Draft' => 'secondary',
                                                    'Diajukan' => 'info',
                                                    'Diverifikasi' => 'success',
                                                    'Ditolak' => 'danger',
                                                    'Diterima' => 'success'
                                                ];
                                                $badge = $status_class[$pend['status_pendaftaran']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $badge; ?> status-badge">
                                                    <?php echo $pend['status_pendaftaran']; ?>
                                                </span>
                                            </div>
                                            
                                            <div class="row g-3 small">
                                                <div class="col-md-4">
                                                    <i class="bi bi-calendar3 text-primary me-1"></i>
                                                    <strong>Jalur:</strong> <?php echo $pend['jalur_masuk']; ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <i class="bi bi-calendar-check text-primary me-1"></i>
                                                    <strong>Tanggal:</strong> 
                                                    <?php echo date('d/m/Y', strtotime($pend['tanggal_daftar'])); ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <i class="bi bi-cash text-primary me-1"></i>
                                                    <strong>Biaya:</strong> 
                                                    Rp <?php echo number_format($pend['biaya_pendaftaran'], 0, ',', '.'); ?>
                                                </div>
                                            </div>

                                            <?php if ($pend['catatan_verifikasi']): ?>
                                            <div class="alert alert-info mt-3 mb-0">
                                                <strong>Catatan:</strong> <?php echo htmlspecialchars($pend['catatan_verifikasi']); ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-inbox" style="font-size: 60px; color: #ccc;"></i>
                                        <p class="text-muted mt-3">Belum ada data pendaftaran</p>
                                        <button class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-2"></i>Daftar Sekarang
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Berkas -->
                    <div class="tab-pane fade" id="berkas">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-files me-2"></i>Status Berkas
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php 
                                $stmt_berkas->execute([$id_camaba]);
                                if ($stmt_berkas->rowCount() > 0): 
                                    while ($berkas = $stmt_berkas->fetch(PDO::FETCH_ASSOC)): 
                                ?>
                                    <div class="card mb-3 border">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0"><?php echo $berkas['no_pendaftaran']; ?></h6>
                                                <?php
                                                $berkas_status = [
                                                    'Belum Upload' => 'secondary',
                                                    'Menunggu Verifikasi' => 'warning',
                                                    'Diverifikasi' => 'success',
                                                    'Ditolak' => 'danger'
                                                ];
                                                $badge_berkas = $berkas_status[$berkas['status_berkas']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $badge_berkas; ?>">
                                                    <?php echo $berkas['status_berkas']; ?>
                                                </span>
                                            </div>

                                            <div class="row g-2">
                                                <?php
                                                $files = [
                                                    'ijazah' => 'Ijazah',
                                                    'kk' => 'KK',
                                                    'foto' => 'Foto',
                                                    'akta_kelahiran' => 'Akta',
                                                    'surat_keterangan_lulus' => 'SKL',
                                                    'sertifikat_prestasi' => 'Sertifikat'
                                                ];
                                                foreach ($files as $key => $label):
                                                ?>
                                                <div class="col-4 col-md-2">
                                                    <div class="text-center p-2 border rounded">
                                                        <?php if ($berkas[$key]): ?>
                                                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                                            <br><small><?php echo $label; ?></small>
                                                        <?php else: ?>
                                                            <i class="bi bi-x-circle text-muted fs-4"></i>
                                                            <br><small class="text-muted"><?php echo $label; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <?php if ($berkas['catatan_berkas']): ?>
                                            <div class="alert alert-info mt-3 mb-0">
                                                <strong>Catatan:</strong> <?php echo htmlspecialchars($berkas['catatan_berkas']); ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php 
                                    endwhile;
                                else: 
                                ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-inbox" style="font-size: 60px; color: #ccc;"></i>
                                        <p class="text-muted mt-3">Belum ada berkas terupload</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Ujian -->
                    <div class="tab-pane fade" id="ujian">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-clipboard-check me-2"></i>Hasil Ujian Seleksi
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php 
                                $stmt_ujian->execute([$id_camaba]);
                                if ($stmt_ujian->rowCount() > 0): 
                                ?>
                                    <div class="timeline">
                                        <?php while ($ujian = $stmt_ujian->fetch(PDO::FETCH_ASSOC)): ?>
                                        <div class="timeline-item">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="mb-1"><?php echo $ujian['jenis_ujian']; ?></h6>
                                                            <small class="text-muted">
                                                                <?php echo $ujian['no_pendaftaran']; ?>
                                                            </small>
                                                        </div>
                                                        <?php
                                                        $status_ujian = [
                                                            'Lulus' => 'success',
                                                            'Tidak Lulus' => 'danger',
                                                            'Belum Dinilai' => 'warning'
                                                        ];
                                                        $badge_ujian = $status_ujian[$ujian['status_lulus']] ?? 'secondary';
                                                        ?>
                                                        <span class="badge bg-<?php echo $badge_ujian; ?>">
                                                            <?php echo $ujian['status_lulus']; ?>
                                                        </span>
                                                    </div>

                                                    <div class="row g-2 small">
                                                        <div class="col-md-3">
                                                            <strong>Nilai:</strong> 
                                                            <span class="<?php echo $ujian['status_lulus']=='Lulus'?'text-success':'text-danger'; ?>">
                                                                <?php echo number_format($ujian['nilai'], 2); ?>
                                                            </span>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong>Min:</strong> <?php echo number_format($ujian['nilai_minimal'], 2); ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Tanggal:</strong> 
                                                            <?php echo date('d F Y', strtotime($ujian['tanggal_ujian'])); ?>
                                                        </div>
                                                    </div>

                                                    <?php if ($ujian['catatan']): ?>
                                                    <div class="alert alert-light mt-2 mb-0">
                                                        <small><?php echo htmlspecialchars($ujian['catatan']); ?></small>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-inbox" style="font-size: 60px; color: #ccc;"></i>
                                        <p class="text-muted mt-3">Belum ada ujian yang diikuti</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Profile -->
                    <div class="tab-pane fade" id="profile" id="profileSection">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-person me-2"></i>Data Profil Lengkap
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="text-muted small">Nama Lengkap</label>
                                        <p class="fw-bold"><?php echo htmlspecialchars($camaba['nama_lengkap']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">NIK</label>
                                        <p class="fw-bold"><?php echo htmlspecialchars($camaba['nik']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Email</label>
                                        <p class="fw-bold"><?php echo htmlspecialchars($camaba['email']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">No. HP</label>
                                        <p class="fw-bold"><?php echo htmlspecialchars($camaba['no_hp']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Tempat, Tanggal Lahir</label>
                                        <p class="fw-bold">
                                            <?php echo htmlspecialchars($camaba['tempat_lahir']); ?>, 
                                            <?php echo date('d F Y', strtotime($camaba['tgl_lahir'])); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Jenis Kelamin</label>
                                        <p class="fw-bold">
                                            <?php echo $camaba['jenis_kelamin']=='L'?'Laki-laki':'Perempuan'; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Agama</label>
                                        <p class="fw-bold"><?php echo htmlspecialchars($camaba['agama']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Asal Sekolah</label>
                                        <p class="fw-bold"><?php echo htmlspecialchars($camaba['asal_sekolah']); ?></p>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="text-muted small">Alamat</label>
                                        <p class="fw-bold">
                                            <?php echo htmlspecialchars($camaba['alamat']); ?><br>
                                            <?php echo htmlspecialchars($camaba['kota'] . ', ' . $camaba['provinsi'] . ' ' . $camaba['kode_pos']); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <button class="btn btn-primary" disabled>
                                    <i class="bi bi-pencil me-2"></i>Edit Profil (Coming Soon)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

