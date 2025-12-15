<?php
$page_title = "Informasi Ujian";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Search & Filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_jenis = isset($_GET['jenis_ujian']) ? $_GET['jenis_ujian'] : '';
$filter_status = isset($_GET['status_ujian']) ? $_GET['status_ujian'] : '';

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (judul_ujian LIKE :search OR lokasi_ujian LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($filter_jenis) {
    $where .= " AND jenis_ujian = :jenis";
    $params[':jenis'] = $filter_jenis;
}
if ($filter_status) {
    $where .= " AND status_ujian = :status";
    $params[':status'] = $filter_status;
}

// Get total records
$total_query = "SELECT COUNT(*) FROM informasi_ujian $where";
$stmt = $db->prepare($total_query);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Get data
$query = "SELECT * FROM informasi_ujian 
          $where
          ORDER BY tanggal_ujian DESC, created_at DESC
          LIMIT $records_per_page OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-info-circle"></i> Informasi Ujian</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Tambah Informasi Ujian
    </a>
</div>

<!-- Filter & Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" 
                       placeholder="Cari judul atau lokasi ujian..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="jenis_ujian" class="form-select">
                    <option value="">-- Semua Jenis Ujian --</option>
                    <option value="TPA" <?php echo $filter_jenis=='TPA'?'selected':''; ?>>TPA</option>
                    <option value="Akademik" <?php echo $filter_jenis=='Akademik'?'selected':''; ?>>Akademik</option>
                    <option value="Wawancara" <?php echo $filter_jenis=='Wawancara'?'selected':''; ?>>Wawancara</option>
                    <option value="Keterampilan" <?php echo $filter_jenis=='Keterampilan'?'selected':''; ?>>Keterampilan</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status_ujian" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="Aktif" <?php echo $filter_status=='Aktif'?'selected':''; ?>>Aktif</option>
                    <option value="Selesai" <?php echo $filter_status=='Selesai'?'selected':''; ?>>Selesai</option>
                    <option value="Dibatalkan" <?php echo $filter_status=='Dibatalkan'?'selected':''; ?>>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Data Cards -->
<div class="row">
    <?php if ($stmt->rowCount() > 0): ?>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-info me-2"><?php echo $row['jenis_ujian']; ?></span>
                        <?php
                        $badge_class = '';
                        switch($row['status_ujian']) {
                            case 'Aktif': $badge_class = 'bg-success'; break;
                            case 'Selesai': $badge_class = 'bg-secondary'; break;
                            case 'Dibatalkan': $badge_class = 'bg-danger'; break;
                        }
                        ?>
                        <span class="badge <?php echo $badge_class; ?>">
                            <?php echo $row['status_ujian']; ?>
                        </span>
                    </div>
                    <small class="text-muted">#<?php echo $row['id_info_ujian']; ?></small>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($row['judul_ujian']); ?></h5>
                    <p class="card-text text-muted">
                        <?php echo htmlspecialchars(substr($row['deskripsi'], 0, 120)); ?>
                        <?php echo strlen($row['deskripsi']) > 120 ? '...' : ''; ?>
                    </p>
                    
                    <hr>
                    
                    <div class="row g-2 small">
                        <div class="col-6">
                            <i class="bi bi-calendar3 text-primary"></i>
                            <strong>Tanggal:</strong><br>
                            <?php echo date('d F Y', strtotime($row['tanggal_ujian'])); ?>
                        </div>
                        <div class="col-6">
                            <i class="bi bi-clock text-primary"></i>
                            <strong>Waktu:</strong><br>
                            <?php echo date('H:i', strtotime($row['waktu_mulai'])); ?> - 
                            <?php echo date('H:i', strtotime($row['waktu_selesai'])); ?>
                        </div>
                        <div class="col-12 mt-2">
                            <i class="bi bi-geo-alt text-primary"></i>
                            <strong>Lokasi:</strong><br>
                            <?php echo htmlspecialchars($row['lokasi_ujian']); ?>
                        </div>
                        <div class="col-6 mt-2">
                            <i class="bi bi-people text-primary"></i>
                            <strong>Kuota:</strong> <?php echo $row['kuota_peserta']; ?> peserta
                        </div>
                        <div class="col-6 mt-2">
                            <i class="bi bi-star text-primary"></i>
                            <strong>Nilai Min:</strong> <?php echo number_format($row['nilai_minimal'], 2); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="edit.php?id=<?php echo $row['id_info_ujian']; ?>" 
                           class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="delete.php?id=<?php echo $row['id_info_ujian']; ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Yakin ingin menghapus informasi ujian ini?')">
                            <i class="bi bi-trash"></i> Hapus
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-3 mb-0">Tidak ada informasi ujian</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&jenis_ujian=<?php echo $filter_jenis; ?>&status_ujian=<?php echo $filter_status; ?>">
                <i class="bi bi-chevron-left"></i> Previous
            </a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&jenis_ujian=<?php echo $filter_jenis; ?>&status_ujian=<?php echo $filter_status; ?>">
                <?php echo $i; ?>
            </a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&jenis_ujian=<?php echo $filter_jenis; ?>&status_ujian=<?php echo $filter_status; ?>">
                Next <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Summary -->
<div class="alert alert-info mt-4">
    <i class="bi bi-info-circle"></i>
    Menampilkan <strong><?php echo $stmt->rowCount(); ?></strong> dari 
    <strong><?php echo $total_records; ?></strong> total informasi ujian
</div>

<?php include '../../includes/footer.php'; ?>