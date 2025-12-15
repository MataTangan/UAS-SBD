<?php
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID tidak ditemukan.');

try {
    // Get file names before deleting
    $query = "SELECT * FROM berkas WHERE id_berkas = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        // Delete physical files
        $upload_dir = "../../uploads/berkas/";
        $files = ['ijazah', 'kk', 'foto', 'akta_kelahiran', 'surat_keterangan_lulus', 'sertifikat_prestasi'];
        
        foreach ($files as $field) {
            if (!empty($row[$field]) && file_exists($upload_dir . $row[$field])) {
                unlink($upload_dir . $row[$field]);
            }
        }
        
        // Delete database record
        $query = "DELETE FROM berkas WHERE id_berkas = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        header("Location: index.php?deleted=success");
    } else {
        header("Location: index.php?deleted=notfound");
    }
} catch (PDOException $e) {
    header("Location: index.php?deleted=error");
}
?>
