<?php
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID tidak ditemukan.');

try {
    $query = "DELETE FROM camaba WHERE id_camaba = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    
    header("Location: index.php?deleted=success");
} catch (PDOException $e) {
    header("Location: index.php?deleted=error");
}
?>-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <!-- Data Pribadi -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">Data Pribadi</h5>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIK <span class="text-danger">*</span></label>
                    <input type="text" name="nik" class="form-control" maxlength="16" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_lahir" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="">Pilih...</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Agama <span class="text-danger">*</span></label>
                    <select name="agama" class="form-select" required>
                        <option value="">Pilih...</option>
                        <option value="Islam">Islam</option>
                        <option value="Kristen">Kristen</option>
                        <option value="Katolik">Katolik</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Buddha">Buddha</option>
                        <option value="Konghucu">Konghucu</option>
                    </select>
                </div>

                <!-- Data Alamat -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">Data Alamat</h5>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                    <textarea name="alamat" class="form-control" rows="3" required></textarea>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Provinsi</label>
                    <input type="text" name="provinsi" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kota/Kabupaten</label>
                    <input type="text" name="kota" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kode Pos</label>
                    <input type="text" name="kode_pos" class="form-control" maxlength="10">
                </div>

                <!-- Data Kontak -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">Data Kontak</h5>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. HP <span class="text-danger">*</span></label>
                    <input type="text" name="no_hp" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Orang Tua</label>
                    <input type="text" name="nama_ortu" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. HP Orang Tua</label>
                    <input type="text" name="no_hp_ortu" class="form-control">
                </div>

                <!-- Data Pendidikan -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3 text-primary">Data Pendidikan</h5>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Asal Sekolah</label>
                    <input type="text" name="asal_sekolah" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form