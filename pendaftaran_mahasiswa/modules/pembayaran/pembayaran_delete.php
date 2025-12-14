<?php
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID tidak ditemukan.');

try {
    // Get bukti_bayar filename first
    $query = "SELECT bukti_bayar FROM pembayaran WHERE id_pembayaran = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Delete the record
    $query = "DELETE FROM pembayaran WHERE id_pembayaran = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    
    // Delete file if exists
    if ($row && $row['bukti_bayar']) {
        $file_path = "uploads/" . $row['bukti_bayar'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    header("Location: index.php?deleted=success");
} catch (PDOException $e) {
    header("Location: index.php?deleted=error&msg=" . urlencode($e->getMessage()));
}
?>