<?php
$page_title = "Tambah Informasi Ujian";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $query = "INSERT INTO informasi_ujian 
                  (judul_ujian, jenis_ujian, deskripsi, tanggal_ujian, waktu_mulai, 
                   waktu_selesai, lokasi_ujian, kuota_peserta, nilai_minimal, 
                   persyaratan, status_ujian) 
                  VALUES 
                  (:judul_ujian, :jenis_ujian, :deskripsi, :tanggal_ujian, :waktu_mulai, 
                   :waktu_selesai, :lokasi_ujian, :kuota_peserta, :nilai_minimal, 
                   :persyaratan, :status_ujian)";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':judul_ujian', $_POST['judul_ujian']);
        $stmt->bindParam(':jenis_ujian', $_POST['jenis_ujian']);
        $stmt->bindParam(':deskripsi', $_POST['deskripsi']);
        $stmt->bindParam(':tanggal_ujian', $_POST['tanggal_ujian']);
        $stmt->bindParam(':waktu_mulai', $_POST['waktu_mulai']);
        $stmt->bindParam(':waktu_selesai', $_POST['waktu_selesai']);
        $stmt->bindParam(':lokasi_ujian', $_POST['lokasi_ujian']);
        $stmt->bindParam(':kuota_peserta', $_POST['kuota_peserta']);
        $stmt->bindParam(':nilai_minimal', $_POST['nilai_minimal']);
        $stmt->bindParam(':persyaratan', $_POST['persyaratan']);
        $stmt->bindParam(':status_ujian', $_POST['status_ujian']);
        
        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Informasi ujian berhasil ditambahkan',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'index.php';
                });
            </script>";
        }
    } catch (PDOException $e) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Error: " . addslashes($e->getMessage()) . "'
            });
        </script>";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle"></i> Tambah Informasi Ujian</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <!-- Informasi Dasar -->
                <div class="col-md-12">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-info-circle"></i> Informasi Dasar Ujian
                    </h5>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Judul Ujian <span class="text-danger">*</span></label>
                    <input type="text" name="judul_ujian" class="form-control" required 
                           placeholder="Contoh: Ujian TPA Gelombang 1 - 2024">
                    <small class="text-muted">Berikan judul yang jelas dan informatif</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Ujian <span class="text-danger">*</span></label>
                    <select name="jenis_ujian" class="form-select" required>
                        <option value="">-- Pilih Jenis Ujian --</option>
                        <option value="TPA">TPA (Tes Potensi Akademik)</option>
                        <option value="Akademik">Akademik</option>
                        <option value="Wawancara">Wawancara</option>
                        <option value="Keterampilan">Keterampilan</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Ujian <span class="text-danger">*</span></label>
                    <select name="status_ujian" class="form-select" required>
                        <option value="Aktif" selected>Aktif</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Dibatalkan">Dibatalkan</option>
                    </select>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3" 
                              placeholder="Deskripsi lengkap tentang ujian ini..."></textarea>
                </div>

                <!-- Jadwal -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-calendar-event"></i> Jadwal Ujian
                    </h5>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Ujian <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_ujian" class="form-control" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                    <input type="time" name="waktu_mulai" class="form-control" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                    <input type="time" name="waktu_selesai" class="form-control" required>
                </div>

                <!-- Lokasi & Kapasitas -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-geo-alt"></i> Lokasi & Kapasitas
                    </h5>
                </div>

                <div class="col-md-8 mb-3">
                    <label class="form-label">Lokasi Ujian <span class="text-danger">*</span></label>
                    <input type="text" name="lokasi_ujian" class="form-control" required 
                           placeholder="Contoh: Gedung A Lantai 2 - Ruang 201-205">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Kuota Peserta <span class="text-danger">*</span></label>
                    <input type="number" name="kuota_peserta" class="form-control" required 
                           min="1" value="50" placeholder="Jumlah peserta">
                </div>

                <!-- Penilaian -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-star"></i> Standar Penilaian
                    </h5>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Nilai Minimal Kelulusan <span class="text-danger">*</span></label>
                    <input type="number" name="nilai_minimal" class="form-control" required 
                           step="0.01" min="0" max="100" value="60.00" 
                           placeholder="Contoh: 60.00">
                    <small class="text-muted">Nilai minimal untuk dinyatakan lulus (0-100)</small>
                </div>

                <!-- Persyaratan -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-list-check"></i> Persyaratan & Ketentuan
                    </h5>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Persyaratan Peserta</label>
                    <textarea name="persyaratan" class="form-control" rows="6" 
                              placeholder="Tuliskan persyaratan yang harus dipenuhi peserta, contoh:&#10;- Membawa kartu peserta&#10;- KTP asli&#10;- Alat tulis&#10;- Datang 30 menit sebelum ujian"></textarea>
                    <small class="text-muted">Pisahkan setiap persyaratan dengan baris baru (Enter)</small>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="reset" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Informasi Ujian
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>