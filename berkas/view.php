<?php
$page_title = "Detail Berkas";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get ID
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID tidak ditemukan.');

// Get berkas data
$query = "SELECT b.*, p.no_pendaftaran, c.nama_lengkap, c.nik, c.no_hp, 
          pr.nama_prodi, f.nama_fakultas
          FROM berkas b
          JOIN pendaftaran p ON b.id_pendaftaran = p.id_pendaftaran
          JOIN camaba c ON p.id_camaba = c.id_camaba
          JOIN prodi pr ON p.id_prodi = pr.id_prodi
          JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
          WHERE b.id_berkas = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('ERROR: Data tidak ditemukan.');
}

// File list
$files = [
    'ijazah' => 'Ijazah/STTB',
    'kk' => 'Kartu Keluarga',
    'foto' => 'Pas Foto',
    'akta_kelahiran' => 'Akta Kelahiran',
    'surat_keterangan_lulus' => 'Surat Keterangan Lulus',
    'sertifikat_prestasi' => 'Sertifikat Prestasi'
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-text"></i> Detail Berkas Pendaftaran</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<!-- Status Badge -->
<div class="alert alert-<?php 
    echo $row['status_berkas'] == 'Diverifikasi' ? 'success' : 
         ($row['status_berkas'] == 'Ditolak' ? 'danger' : 
         ($row['status_berkas'] == 'Menunggu Verifikasi' ? 'warning' : 'secondary')); 
?>">
    <h5 class="mb-0">
        <i class="bi bi-<?php 
            echo $row['status_berkas'] == 'Diverifikasi' ? 'check-circle' : 
                 ($row['status_berkas'] == 'Ditolak' ? 'x-circle' : 
                 ($row['status_berkas'] == 'Menunggu Verifikasi' ? 'clock' : 'exclamation-circle')); 
        ?>"></i> 
        Status: <?php echo $row['status_berkas']; ?>
    </h5>
    <?php if ($row['tanggal_verifikasi']): ?>
    <small>Diverifikasi pada: <?php echo date('d/m/Y H:i', strtotime($row['tanggal_verifikasi'])); ?></small>
    <?php endif; ?>
</div>

<div class="row">
    <!-- Data Pendaftaran -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person"></i> Data Camaba</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>No. Pendaftaran</strong></td>
                        <td><?php echo htmlspecialchars($row['no_pendaftaran']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Nama Lengkap</strong></td>
                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>NIK</strong></td>
                        <td><?php echo htmlspecialchars($row['nik']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>No. HP</strong></td>
                        <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Fakultas</strong></td>
                        <td><?php echo htmlspecialchars($row['nama_fakultas']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Program Studi</strong></td>
                        <td><?php echo htmlspecialchars($row['nama_prodi']); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Catatan -->
        <?php if ($row['catatan_berkas']): ?>
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-chat-left-text"></i> Catatan</h6>
            </div>
            <div class="card-body">
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($row['catatan_berkas'])); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Files Preview -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-files"></i> Dokumen yang Diupload</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($files as $field => $label): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <strong><?php echo $label; ?></strong>
                                </div>
                                <div class="card-body text-center">
                                    <?php if (!empty($row[$field])): ?>
                                        <?php
                                        $file_path = "../../uploads/berkas/" . $row[$field];
                                        $file_ext = strtolower(pathinfo($row[$field], PATHINFO_EXTENSION));
                                        ?>
                                        
                                        <?php if (in_array($file_ext, ['jpg', 'jpeg', 'png'])): ?>
                                            <!-- Image Preview -->
                                            <img src="<?php echo $file_path; ?>" 
                                                 class="img-fluid mb-3" 
                                                 style="max-height: 200px; object-fit: contain;"
                                                 alt="<?php echo $label; ?>">
                                        <?php else: ?>
                                            <!-- PDF Icon -->
                                            <div class="mb-3">
                                                <i class="bi bi-file-pdf" style="font-size: 80px; color: #dc3545;"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-grid gap-2">
                                            <a href="<?php echo $file_path; ?>" 
                                               class="btn btn-sm btn-primary" 
                                               target="_blank">
                                                <i class="bi bi-eye"></i> Lihat File
                                            </a>
                                            <a href="<?php echo $file_path; ?>" 
                                               class="btn btn-sm btn-success" 
                                               download>
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <?php echo strtoupper($file_ext) . " • " . number_format(filesize($file_path)/1024, 2) . " KB"; ?>
                                        </small>
                                    <?php else: ?>
                                        <div class="text-muted">
                                            <i class="bi bi-x-circle" style="font-size: 60px;"></i>
                                            <p class="mt-2">Tidak ada file</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card mt-3">
            <div class="card-body">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="edit.php?id=<?php echo $row['id_berkas']; ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit Berkas
                    </a>
                    <?php if ($row['status_berkas'] == 'Menunggu Verifikasi'): ?>
                    <a href="verify.php?id=<?php echo $row['id_berkas']; ?>" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Verifikasi
                    </a>
                    <?php endif; ?>
                    <a href="delete.php?id=<?php echo $row['id_berkas']; ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('Yakin ingin menghapus berkas ini?')">
                        <i class="bi bi-trash"></i> Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
