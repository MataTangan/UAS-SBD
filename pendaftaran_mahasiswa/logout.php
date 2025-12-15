<?php
session_start();
require_once 'config/database.php'; // Pastikan path ini sesuai lokasi file logout.php

// 1. LOGGING KE DATABASE (Sesuai permintaan Anda)
// Cek apakah ada user yang sedang login sebelum dihancurkan sesinya
if (isset($_SESSION['user_id']) || isset($_SESSION['email'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // CONTOH: Jika Anda punya tabel 'log_aktivitas'
        // $query = "INSERT INTO log_aktivitas (user_id, aktivitas, waktu) VALUES (:uid, 'Logout', NOW())";
        // $stmt = $db->prepare($query);
        // $stmt->bindParam(':uid', $_SESSION['user_id']);
        // $stmt->execute();
        
        // ATAU CONTOH: Update status 'last_login' atau 'is_online' di tabel user
        // $query = "UPDATE users SET last_activity = NOW() WHERE id = :uid";
        // $stmt = $db->prepare($query);
        // $stmt->bindParam(':uid', $_SESSION['user_id']);
        // $stmt->execute();

    } catch (PDOException $e) {
        // Biarkan kosong agar error database tidak menghalangi user untuk logout
    }
}

// 2. HAPUS SEMUA VARIABEL SESSION
$_SESSION = array();

// 3. HAPUS COOKIE SESSION (Penting untuk keamanan)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. HANCURKAN SESSION
session_destroy();

// 5. REDIRECT KE LOGIN
header("Location: login.php?pesan=logout");
exit();
?>