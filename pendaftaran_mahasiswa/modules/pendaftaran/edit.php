<?php
$page_title = "Edit Data Pendaftar";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get ID
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID tidak ditemukan.');

// Get existing data
$query = "SELECT * FROM camaba WHERE id_camaba = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('ERROR: Data tidak ditemukan.');
}

// Update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $query = "UPDATE camaba SET 
                  email = :email,
                  nama_lengkap = :nama_lengkap,
                  nik = :nik,
                  tempat_lahir = :tempat_lahir,
                  tgl_lahir = :tgl_lahir,
                  jenis_kelamin = :jenis_kelamin,
                  agama = :agama,
                  alamat = :alamat,
                  provinsi = :provinsi,
                  kota = :kota,
                  kode_pos = :kode_pos,
                  no_hp = :no_hp,
                  nama_ortu = :nama_ortu,
                  no_hp_ortu = :no_hp_ortu,
                  asal_sekolah = :asal_sekolah,
                  tahun_lulus = :