<?php
$page_title = "Data Berkas Pendaftaran";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Pagination
$page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page_num - 1) * $records_per_page;

// Search & Filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where = "WHERE 1=1";
if ($search) {
    $search_safe = $db->quote("%$search%");
    $where .= " AND (p.no_pendaftaran LIKE $search_safe OR c.nama_lengkap LIKE $search_safe)";
}
if ($status_filter) {
    $status_safe = $db->quote($status_filter);
    $where .= " AND b.status_berkas = $status_safe";
}

// Get total records
$total_query = "SELECT COUNT(*) FROM berkas b 
                JOIN pendaftaran p ON b.id_pendaftaran = p.id_pendaftaran 
                JOIN camaba c ON p.id_camaba = c.id_camaba $where";
$total_records = $db->query($total_query)->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Get data
$query = "SELECT b.*, p.no_pendaftaran, c.nama_lengkap, pr.nama_prodi
          FROM berkas b
          JOIN pendaftaran p ON b.id_pendaftaran = p.id_pendaftaran
          JOIN camaba c ON p.id_camaba = c.id_camaba
          JOIN prodi pr ON p.id_prodi = pr.id_prodi
          $where
          ORDER BY b.tanggal_upload DESC 
          LIMIT $records_per_page OFFSET $offset";
$stmt = $db->query($query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status_berkas='Belum Upload' THEN 1 ELSE 0 END) as belum_upload,
    SUM(CASE WHEN status_berkas='Menunggu Verifikasi' THEN 1 ELSE 0 END) as menunggu,
    SUM(CASE WHEN status_berkas='Diverifikasi' THEN 1 ELSE 0 END) as terverifikasi,
    SUM(CASE WHEN status_berkas='Ditolak' THEN 1 ELSE 0 END) as ditolak
    FROM berkas";
$stats = $db->query($stats_query)->fetch(PDO::FETCH_ASSOC);
if (!$stats) {
    $stats = ['total' => 0, 'belum_upload' => 0, 'menunggu' => 0, 'terverifikasi' => 0, 'ditolak' => 0];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-arrow-up"></i> Data Berkas Pendaftaran</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Upload Berkas Baru
    </a>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Berkas</h6>
                        <h2 class="mb-0 mt-2"><?php echo $stats['total']; ?></h2>
                    </div>
                    <i class="bi bi-files fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Menunggu Verifikasi</h6>
                        <h2 class="mb-0 mt-2"><?php echo $stats['menunggu']; ?></h2>
                    </div>
                    <i class="bi bi-clock-history fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Diverifikasi</h6>
                        <h2 class="mb-0 mt-2"><?php echo $stats['terverifikasi']; ?></h2>
                    </div>
                    <i class="bi bi-check-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Ditolak</h6>
                        <h2 class="mb-0 mt-2"><?php echo $stats['ditolak']; ?></h2>
                    </div>
                    <i class="bi bi-x-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Search -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-filter"></i> Filter & Pencarian</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Cari Data</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Masukkan No. Pendaftaran atau Nama..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status Berkas</label>
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="Belum Upload" <?php echo $status_filter=='Belum Upload'?'selected':''; ?>>Belum Upload</option>
                    <option value="Menunggu Verifikasi" <?php echo $status_filter=='Menunggu Verifikasi'?'selected':''; ?>>Menunggu Verifikasi</option>
                    <option value="Diverifikasi" <?php echo $status_filter=='Diverifikasi'?'selected':''; ?>>Diverifikasi</option>
                    <option value="Ditolak" <?php echo $status_filter=='Ditolak'?'selected':''; ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Cari Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-table"></i> Daftar Berkas Pendaftaran</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">No. Pendaftaran</th>
                        <th width="20%">Nama Camaba</th>
                        <th width="20%">Program Studi</th>
                        <th width="15%">Status Berkas</th>
                        <th width="15%">Tanggal Upload</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($stmt->rowCount() > 0): ?>
                        <?php 
                        $no = $offset + 1;
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['no_pendaftaran']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><small><?php echo htmlspecialchars($row['nama_prodi']); ?></small></td>
                            <td>
                                <?php
                                $badge_class = [
                                    'Belum Upload' => 'bg-secondary',
                                    'Menunggu Verifikasi' => 'bg-warning text-dark',
                                    'Diverifikasi' => 'bg-success',
                                    'Ditolak' => 'bg-danger'
                                ];
                                $badge_icon = [
                                    'Belum Upload' => 'exclamation-circle',
                                    'Menunggu Verifikasi' => 'clock',
                                    'Diverifikasi' => 'check-circle',
                                    'Ditolak' => 'x-circle'
                                ];
                                ?>
                                <span class="badge <?php echo $badge_class[$row['status_berkas']]; ?>">
                                    <i class="bi bi-<?php echo $badge_icon[$row['status_berkas']]; ?>"></i>
                                    <?php echo $row['status_berkas']; ?>
                                </span>
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($row['tanggal_upload'])); ?>
                                    <br>
                                    <i class="bi bi-clock"></i>
                                    <?php echo date('H:i', strtotime($row['tanggal_upload'])); ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="view.php?id=<?php echo $row['id_berkas']; ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?php echo $row['id_berkas']; ?>" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit Berkas">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($row['status_berkas'] == 'Menunggu Verifikasi'): ?>
                                    <a href="verify.php?id=<?php echo $row['id_berkas']; ?>" 
                                       class="btn btn-sm btn-success" 
                                       title="Verifikasi Berkas">
                                        <i class="bi bi-check-circle"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="delete.php?id=<?php echo $row['id_berkas']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('⚠️ Apakah Anda yakin ingin menghapus berkas ini?\n\nData yang dihapus tidak dapat dikembalikan!')" 
                                       title="Hapus Berkas">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                <p class="text-muted mt-3 mb-0">Tidak ada data berkas ditemukan</p>
                                <?php if ($search || $status_filter): ?>
                                <a href="index.php" class="btn btn-sm btn-primary mt-2">
                                    <i class="bi bi-arrow-clockwise"></i> Reset Filter
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page_num <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page_num - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>
                
                <?php
                $start_page = max(1, $page_num - 2);
                $end_page = min($total_pages, $page_num + 2);
                
                if ($start_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">1</a>
                    </li>
                    <?php if ($start_page > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <li class="page-item <?php echo $page_num == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $total_pages; ?></a>
                    </li>
                <?php endif; ?>
                
                <li class="page-item <?php echo $page_num >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page_num + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="text-center text-muted mt-2">
            <small>Menampilkan halaman <?php echo $page_num; ?> dari <?php echo $total_pages; ?> 
            (Total: <?php echo $total_records; ?> data)</small>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Alert Messages -->
<?php if (isset($_GET['deleted'])): ?>
<script>
    <?php if ($_GET['deleted'] == 'success'): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil Dihapus!',
        text: 'Data berkas telah dihapus dari sistem',
        timer: 2000,
        showConfirmButton: false
    });
    <?php else: ?>
    Swal.fire({
        icon: 'error',
        title: 'Gagal Menghapus!',
        text: 'Terjadi kesalahan saat menghapus data'
    });
    <?php endif; ?>
</script>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>