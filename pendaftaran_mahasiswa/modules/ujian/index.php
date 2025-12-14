<?php
$page_title = "Data Ujian Seleksi";
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
$filter_status = isset($_GET['status_lulus']) ? $_GET['status_lulus'] : '';

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (p.no_pendaftaran LIKE :search OR c.nama_lengkap LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($filter_jenis) {
    $where .= " AND u.jenis_ujian = :jenis";
    $params[':jenis'] = $filter_jenis;
}
if ($filter_status) {
    $where .= " AND u.status_lulus = :status";
    $params[':status'] = $filter_status;
}

// Get total records
$total_query = "SELECT COUNT(*) FROM ujian_seleksi u 
                JOIN pendaftaran p ON u.id_pendaftaran = p.id_pendaftaran 
                JOIN camaba c ON p.id_camaba = c.id_camaba 
                $where";
$stmt = $db->prepare($total_query);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Get data
$query = "SELECT u.*, p.no_pendaftaran, c.nama_lengkap, pr.nama_prodi
          FROM ujian_seleksi u
          JOIN pendaftaran p ON u.id_pendaftaran = p.id_pendaftaran
          JOIN camaba c ON p.id_camaba = c.id_camaba
          JOIN prodi pr ON p.id_prodi = pr.id_prodi
          $where
          ORDER BY u.tanggal_ujian DESC, u.created_at DESC
          LIMIT $records_per_page OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clipboard-check"></i> Data Ujian Seleksi</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Tambah Data Ujian
    </a>
</div>

<!-- Filter & Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" 
                       placeholder="Cari No. Pendaftaran atau Nama..." 
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
                <select name="status_lulus" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="Lulus" <?php echo $filter_status=='Lulus'?'selected':''; ?>>Lulus</option>
                    <option value="Tidak Lulus" <?php echo $filter_status=='Tidak Lulus'?'selected':''; ?>>Tidak Lulus</option>
                    <option value="Belum Dinilai" <?php echo $filter_status=='Belum Dinilai'?'selected':''; ?>>Belum Dinilai</option>
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

<!-- Data Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>No. Pendaftaran</th>
                        <th>Nama Camaba</th>
                        <th>Prodi</th>
                        <th>Jenis Ujian</th>
                        <th>Nilai</th>
                        <th>Nilai Minimal</th>
                        <th>Status</th>
                        <th>Tanggal Ujian</th>
                        <th>Lokasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($stmt->rowCount() > 0): ?>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo $row['id_ujian']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['no_pendaftaran']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><small><?php echo htmlspecialchars($row['nama_prodi']); ?></small></td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo $row['jenis_ujian']; ?>
                                </span>
                            </td>
                            <td>
                                <strong class="<?php echo $row['nilai'] >= $row['nilai_minimal'] ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo number_format($row['nilai'], 2); ?>
                                </strong>
                            </td>
                            <td><?php echo number_format($row['nilai_minimal'], 2); ?></td>
                            <td>
                                <?php
                                $badge_class = '';
                                switch($row['status_lulus']) {
                                    case 'Lulus': $badge_class = 'bg-success'; break;
                                    case 'Tidak Lulus': $badge_class = 'bg-danger'; break;
                                    default: $badge_class = 'bg-warning';
                                }
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo $row['status_lulus']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_ujian'])); ?></td>
                            <td>
                                <small><?php echo htmlspecialchars($row['lokasi_ujian'] ?: '-'); ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit.php?id=<?php echo $row['id_ujian']; ?>" 
                                       class="btn btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $row['id_ujian']; ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Yakin ingin menghapus data ujian ini?')"
                                       title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Tidak ada data ujian</p>
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
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&jenis_ujian=<?php echo $filter_jenis; ?>&status_lulus=<?php echo $filter_status; ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&jenis_ujian=<?php echo $filter_jenis; ?>&status_lulus=<?php echo $filter_status; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&jenis_ujian=<?php echo $filter_jenis; ?>&status_lulus=<?php echo $filter_status; ?>">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Info Summary -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Menampilkan <strong><?php echo $stmt->rowCount(); ?></strong> dari 
            <strong><?php echo $total_records; ?></strong> total data ujian seleksi
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>