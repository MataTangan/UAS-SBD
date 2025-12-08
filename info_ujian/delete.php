<?php
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID tidak ditemukan.');

try {
    $query = "DELETE FROM informasi_ujian WHERE id_info_ujian = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    
    header("Location: index.php?deleted=success");
    exit();
} catch (PDOException $e) {
    header("Location: index.php?deleted=error&message=" . urlencode($e->getMessage()));
    exit();
}
?>