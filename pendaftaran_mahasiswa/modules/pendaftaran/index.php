<?php
$page_title = "Data Pendaftaran Publik";
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
$filter_verified = isset($_GET['verified']) ? $_GET['verified'] : '';

$where = "WHERE 1=1";
if ($search) {
    $where .= " AND (nama_lengkap LIKE '%$search%' OR email LIKE '%$search%' OR nik LIKE '%$search%')";
}
if ($filter_verified !== '') {
    $where .= " AND is_verified = " . (int)$filter_verified;
}

// Get total records
$total_query = "SELECT COUNT(*) FROM camaba $where";
$total_records = $db->query($total_query)->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Get data
$query = "SELECT * FROM camaba $where ORDER BY created_at DESC LIMIT $records_per_page OFFSET $offset";
$stmt = $db->query($query);

// Statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_verified=1 THEN 1 ELSE 0 END) as verified,
    SUM(CASE WHEN is_verified=0 THEN 1 ELSE 0 END) as unverified
    FROM camaba";
$stats = $db->query($stats_query)->fetch(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-plus"></i> Data Pendaftaran Camaba</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Pendaftaran Baru
    </a>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Pendaftar</h6>
                        <h2 class="mb-0 mt-2"><?php echo $stats['total']; ?></h2>
                    </div>
                    <i class="bi bi-people fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Terverifikasi</h6>
                        <h2 class="mb-0 mt-2"><?php echo $stats['verified']; ?></h2>
                    </div>
                    <i class="bi bi-check-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Belum Verifikasi</h6>
                        <h2 class="mb-0 mt-2"><?php echo $stats['unverified']; ?></h2>
                    </div>
                    <i class="bi bi-hourglass-split fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-7">
                <input type="text" name="search" class="form-control" 
                       placeholder="Cari berdasarkan nama, email, atau NIK..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="verified" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="1" <?php echo $filter_verified==='1'?'selected':''; ?>>Terverifikasi</option>
                    <option value="0" <?php echo $filter_verified==='0'?'selected':''; ?>>Belum Verifikasi</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-table"></i> Daftar Pendaftar</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="5%">ID</th>
                        <th width="20%">Nama Lengkap</th>
                        <th width="15%">Email</th>
                        <th width="12%">NIK</th>
                        <th width="10%">No. HP</th>
                        <th width="8%">Jenis Kelamin</th>
                        <th width="10%">Status</th>
                        <th width="12%">Tanggal Daftar</th>
                        <th width="8%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($stmt->rowCount() > 0): ?>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo $row['id_camaba']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong></td>
                            <td><small><?php echo htmlspecialchars($row['email']); ?></small></td>
                            <td><?php echo htmlspecialchars($row['nik']); ?></td>
                            <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $row['jenis_kelamin']=='L'?'info':'danger'; ?>">
                                    <?php echo $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['is_verified']): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Terverifikasi
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock"></i> Belum Verifikasi
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($row['created_at'])); ?>
                                    <br>
                                    <i class="bi bi-clock"></i>
                                    <?php echo date('H:i', strtotime($row['created_at'])); ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="view.php?id=<?php echo $row['id_camaba']; ?>" 
                                       class="btn btn-info" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?php echo $row['id_camaba']; ?>" 
                                       class="btn btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $row['id_camaba']; ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Yakin ingin menghapus data ini?')"
                                       title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                <p class="text-muted mt-3 mb-0">Tidak ada data pendaftar</p>
                                <?php if ($search || $filter_verified !== ''): ?>
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
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&verified=<?php echo $filter_verified; ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1&search=<?php echo urlencode($search); ?>&verified=<?php echo $filter_verified; ?>">1</a>
                    </li>
                    <?php if ($start_page > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&verified=<?php echo $filter_verified; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&verified=<?php echo $filter_verified; ?>"><?php echo $total_pages; ?></a>
                    </li>
                <?php endif; ?>
                
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&verified=<?php echo $filter_verified; ?>">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="text-center text-muted mt-2">
            <small>Menampilkan halaman <?php echo $page; ?> dari <?php echo $total_pages; ?> 
            (Total: <?php echo $total_records; ?> data)</small>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Alert Messages -->
<?php if (isset($_GET['success'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?php 
            switch($_GET['success']) {
                case 'create': echo 'Data pendaftar berhasil ditambahkan'; break;
                case 'update': echo 'Data pendaftar berhasil diupdate'; break;
                case 'delete': echo 'Data pendaftar berhasil dihapus'; break;
                default: echo 'Operasi berhasil';
            }
        ?>',
        timer: 2000,
        showConfirmButton: false
    });
</script>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
