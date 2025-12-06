<?php
$page_title = "Data Calon Mahasiswa Baru";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if ($search) {
    $where = "WHERE nama_lengkap LIKE '%$search%' OR email LIKE '%$search%' OR nik LIKE '%$search%'";
}

// Get total records
$total_query = "SELECT COUNT(*) FROM camaba $where";
$total_records = $db->query($total_query)->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Get data
$query = "SELECT * FROM camaba $where ORDER BY created_at DESC LIMIT $records_per_page OFFSET $offset";
$stmt = $db->query($query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> Data Calon Mahasiswa Baru</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Tambah Camaba
    </a>
</div>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" 
                       placeholder="Cari berdasarkan nama, email, atau NIK..." 
                       value="<?php echo htmlspecialchars($search); ?>">
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
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>NIK</th>
                        <th>Email</th>
                        <th>No. HP</th>
                        <th>Jenis Kelamin</th>
                        <th>Tanggal Lahir</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($stmt->rowCount() > 0): ?>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo $row['id_camaba']; ?></td>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($row['nik']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                            <td>
                                <?php echo $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['tgl_lahir'])); ?></td>
                            <td>
                                <?php if ($row['is_verified']): ?>
                                    <span class="badge bg-success">Terverifikasi</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Belum Verifikasi</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $row['id_camaba']; ?>" 
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $row['id_camaba']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Yakin ingin menghapus data ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data</td>
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
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>">
                        Previous
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>">
                        Next
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
