<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sistem Pendaftaran Mahasiswa'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white"><i class="bi bi-mortarboard-fill"></i> PMB System</h4>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/index.php">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/modules/pendaftaran/index.php">
                                <i class="bi bi-people"></i> Data Pendaftaran
                            </a>
                        </li>                        
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/modules/camaba/index.php">
                                <i class="bi bi-people"></i> Data Camaba
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/modules/berkas/index.php">
                                <i class="bi bi-people"></i> Berkas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/modules/ujian/index.php">
                                <i class="bi bi-people"></i> Data Ujian
                            </a>
                        </li>
                        <!-- <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/modules/fakultas/index.php">
                                <i class="bi bi-building"></i> Data Fakultas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/modules/prodi/index.php">
                                <i class="bi bi-book"></i> Data Prodi
                            </a>
                        </li> -->
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/modules/pendaftaran/index.php">
                                <i class="bi bi-file-earmark-text"></i> Pendaftaran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/modules/pembayaran/pembayaran_index.php">
                                <i class="bi bi-credit-card"></i> Pembayaran
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="pt-3 pb-2 mb-3">