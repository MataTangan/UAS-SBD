<?php
$page_title = "Dashboard - Sistem Pendaftaran Mahasiswa";
include 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Hitung statistik
$stats = [
    'total_camaba' => $db->query("SELECT COUNT(*) FROM camaba")->fetchColumn(),
    'total_pendaftaran' => $db->query("SELECT COUNT(*) FROM pendaftaran")->fetchColumn(),
    'total_prodi' => $db->query("SELECT COUNT(*) FROM prodi")->fetchColumn(),
    'total_fakultas' => $db->query("SELECT COUNT(*) FROM fakultas")->fetchColumn()
];
?>

<h2 class="mb-4">Dashboard Sistem Pendaftaran Mahasiswa Baru</h2>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total Camaba</h6>
                        <h2 class="mb-0"><?php echo $stats['total_camaba']; ?></h2>
                    </div>
                    <i class="bi bi-people fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total Pendaftaran</h6>
                        <h2 class="mb-0"><?php echo $stats['total_pendaftaran']; ?></h2>
                    </div>
                    <i class="bi bi-file-earmark-text fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total Prodi</h6>
                        <h2 class="mb-0"><?php echo $stats['total_prodi']; ?></h2>
                    </div>
                    <i class="bi bi-book fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total Fakultas</h6>
                        <h2 class="mb-0"><?php echo $stats['total_fakultas']; ?></h2>
                    </div>
                    <i class="bi bi-building fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Pendaftaran Terbaru</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <?php
                    $query = "SELECT p.no_pendaftaran, c.nama_lengkap, pr.nama_prodi, 
                              p.jalur_masuk, p.status_pendaftaran, p.tanggal_daftar
                              FROM pendaftaran p
                              JOIN camaba c ON p.id_camaba = c.id_camaba
                              JOIN prodi pr ON p.id_prodi = pr.id_prodi
                              ORDER BY p.tanggal_daftar DESC LIMIT 10";
                    $stmt = $db->query($query);
                    ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No. Pendaftaran</th>
                                <th>Nama Lengkap</th>
                                <th>Program Studi</th>
                                <th>Jalur Masuk</th>
                                <th>Status</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['no_pendaftaran']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_prodi']); ?></td>
                                <td><?php echo htmlspecialchars($row['jalur_masuk']); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo htmlspecialchars($row['status_pendaftaran']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_daftar'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>