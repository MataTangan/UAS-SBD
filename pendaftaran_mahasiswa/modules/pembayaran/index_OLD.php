<?php
$page_title = "Data Pembayaran";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Filter & Search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where = "WHERE 1=1";
if ($search) {
    $where .= " AND (p.no_invoice LIKE '%$search%' OR c.nama_lengkap LIKE '%$search%')";
}
if ($status_filter) {
    $where .= " AND p.status_pembayaran = '$status_filter'";
}

// Get total records
$total_query = "SELECT COUNT(*) FROM pembayaran p 
                JOIN pendaftaran pd ON p.id_pendaftaran = pd.id_pendaftaran
                JOIN camaba c ON pd.id_camaba = c.id_camaba
                $where";
$total_records = $db->query($total_query)->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Get data with JOIN
$query = "SELECT p.*, pd.no_pendaftaran, c.nama_lengkap, pr.nama_prodi
          FROM pembayaran p
          JOIN pendaftaran pd ON p.id_pendaftaran = pd.id_pendaftaran
          JOIN camaba c ON pd.id_camaba = c.id_camaba
          JOIN prodi pr ON pd.id_prodi = pr.id_prodi
          $where
          ORDER BY p.created_at DESC 
          LIMIT $records_per_page OFFSET $offset";
$stmt = $db->query($query);

// Get statistics
$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM pembayaran")->fetchColumn(),
    'pending' => $db->query("SELECT COUNT(*) FROM pembayaran WHERE status_pembayaran='Pending'")->fetchColumn(),
    'berhasil' => $db->query("SELECT COUNT(*) FROM pembayaran WHERE status_pembayaran='Berhasil'")->fetchColumn(),
    'total_nominal' => $db->query("SELECT SUM(jumlah) FROM pembayaran WHERE status_pembayaran='Berhasil'")->fetchColumn() ?: 0
];
?>

<style>
    .stat-card {
        border-radius: 15px;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .stat-icon {
        font-size: 3rem;
        opacity: 0.2;
        position: absolute;
        right: 15px;
        top: 15px;
    }
    .table-card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    .badge-custom {
        padding: 0.5em 1em;
        font-size: 0.85em;
        border-radius: 20px;
    }
    .btn-action {
        width: 35px;
        height: 35px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin: 0 2px;
    }
    .search-box {
        border-radius: 10px;
        border: 2px solid #e0e0e0;
        transition: border-color 0.3s;
    }
    .search-box:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
</style>

<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-credit-card-2-back"></i> Manajemen Pembayaran</h2>
            <p class="text-muted mb-0">Kelola data pembayaran mahasiswa baru</p>
        </div>
        <a href="create.php" class="btn btn-primary btn-lg shadow-sm">
            <i class="bi bi-plus-circle me-2"></i> Tambah Pembayaran
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body position-relative">
                    <i class="bi bi-receipt stat-icon"></i>
                    <h6 class="text-white-50 text-uppercase mb-2">Total Pembayaran</h6>
                    <h2 class="mb-0 fw-bold"><?php echo number_format($stats['total']); ?></h2>
                    <div class="mt-2">
                        <small><i class="bi bi-arrow-up"></i> Semua transaksi</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body position-relative">
                    <i class="bi bi-hourglass-split stat-icon"></i>
                    <h6 class="text-white-50 text-uppercase mb-2">Pending</h6>
                    <h2 class="mb-0 fw-bold"><?php echo number_format($stats['pending']); ?></h2>
                    <div class="mt-2">
                        <small><i class="bi bi-clock"></i> Menunggu verifikasi</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body position-relative">
                    <i class="bi bi-check-circle stat-icon"></i>
                    <h6 class="text-white-50 text-uppercase mb-2">Berhasil</h6>
                    <h2 class="mb-0 fw-bold"><?php echo number_format($stats['berhasil']); ?></h2>
                    <div class="mt-2">
                        <small><i class="bi bi-graph-up"></i> Terverifikasi</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card text-white" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body position-relative">
                    <i class="bi bi-cash-stack stat-icon"></i>
                    <h6 class="text-white-50 text-uppercase mb-2">Total Nominal</h6>
                    <h2 class="mb-0 fw-bold">Rp <?php echo number_format($stats['total_nominal']/1000000, 1); ?>jt</h2>
                    <div class="mt-2">
                        <small><i class="bi bi-wallet2"></i> Total pendapatan</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Section -->
    <div class="card table-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="bi bi-funnel me-1"></i> Status</label>
                    <select name="status" class="form-select search-box">
                        <option value="">Semua Status</option>
                        <option value="Pending" <?php echo $status_filter=='Pending'?'selected':''; ?>>⏳ Pending</option>
                        <option value="Berhasil" <?php echo $status_filter=='Berhasil'?'selected':''; ?>>✓ Berhasil</option>
                        <option value="Gagal" <?php echo $status_filter=='Gagal'?'selected':''; ?>>✗ Gagal</option>
                        <option value="Expired" <?php echo $status_filter=='Expired'?'selected':''; ?>>⏰ Expired</option>
                    </select>
                </div>
                <div class="col-md-7">
                    <label class="form-label fw-semibold"><i class="bi bi-search me-1"></i> Pencarian</label>
                    <input type="text" name="search" class="form-control search-box" 
                           placeholder="Cari invoice atau nama mahasiswa..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card table-card">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-semibold"><i class="bi bi-table me-2"></i>Daftar Pembayaran</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No. Invoice</th>
                            <th>Mahasiswa</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Bukti</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stmt->rowCount() > 0): ?>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td class="ps-4">
                                    <strong class="text-primary"><?php echo htmlspecialchars($row['no_invoice']); ?></strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width:40px;height:40px;">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['nama_prodi']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-custom bg-secondary bg-opacity-10 text-secondary">
                                        <?php echo $row['jenis_pembayaran']; ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-success">Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></strong>
                                </td>
                                <td><?php echo $row['metode_pembayaran']; ?></td>
                                <td>
                                    <?php
                                    $status_config = [
                                        'Pending' => ['class' => 'warning', 'icon' => 'hourglass-split'],
                                        'Berhasil' => ['class' => 'success', 'icon' => 'check-circle-fill'],
                                        'Gagal' => ['class' => 'danger', 'icon' => 'x-circle-fill'],
                                        'Expired' => ['class' => 'secondary', 'icon' => 'clock-fill']
                                    ];
                                    $config = $status_config[$row['status_pembayaran']] ?? ['class' => 'secondary', 'icon' => 'circle'];
                                    ?>
                                    <span class="badge badge-custom bg-<?php echo $config['class']; ?>">
                                        <i class="bi bi-<?php echo $config['icon']; ?> me-1"></i>
                                        <?php echo $row['status_pembayaran']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $row['tanggal_bayar'] ? date('d M Y', strtotime($row['tanggal_bayar'])) : '-'; ?>
                                    <?php if ($row['tanggal_bayar']): ?>
                                        <br><small class="text-muted"><?php echo date('H:i', strtotime($row['tanggal_bayar'])); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['bukti_bayar']): ?>
                                        <a href="uploads/<?php echo $row['bukti_bayar']; ?>" target="_blank" 
                                           class="btn btn-sm btn-info btn-action" title="Lihat Bukti">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <?php if ($row['status_pembayaran'] == 'Pending'): ?>
                                            <a href="verify.php?id=<?php echo $row['id_pembayaran']; ?>" 
                                               class="btn btn-success btn-action" title="Verifikasi">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="edit.php?id=<?php echo $row['id_pembayaran']; ?>" 
                                           class="btn btn-warning btn-action" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $row['id_pembayaran']; ?>" 
                                           class="btn btn-danger btn-action" title="Hapus"
                                           onclick="return confirm('Yakin ingin menghapus pembayaran ini?')">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="bi bi-inbox display-1 text-muted d-block mb-3"></i>
                                    <h5 class="text-muted">Tidak ada data pembayaran</h5>
                                    <p class="text-muted">Silakan tambahkan pembayaran baru</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="card-footer bg-white border-0">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&status=<?php echo $status_filter; ?>">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                    </li>
                    <?php 
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    for ($i = $start; $i <= $end; $i++): 
                    ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&status=<?php echo $status_filter; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&status=<?php echo $status_filter; ?>">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center mt-2">
                <small class="text-muted">
                    Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?> 
                    (Total: <?php echo $total_records; ?> data)
                </small>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>