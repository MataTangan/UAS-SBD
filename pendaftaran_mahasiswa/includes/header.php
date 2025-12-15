<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sistem Pendaftaran Mahasiswa'; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
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
        /* Navbar Top Fix */
        .navbar-top {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 0.8rem 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar position-fixed">
                <div class="position-sticky pt-4">
                    <div class="text-center mb-4">
                        <h4 class="text-white fw-bold"><i class="bi bi-mortarboard-fill"></i> PMB System</h4>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/index.php">
                                <i class="bi bi-house-door me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/modules/pendaftaran/index.php">
                                <i class="bi bi-person-lines-fill me-2"></i> Data Pendaftaran
                            </a>
                        </li>                        
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/modules/camaba/index.php">
                                <i class="bi bi-people me-2"></i> Data Camaba
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/modules/berkas/index.php">
                                <i class="bi bi-folder2-open me-2"></i> Berkas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/modules/ujian/index.php">
                                <i class="bi bi-pencil-square me-2"></i> Data Ujian
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pendaftaran_mahasiswa/modules/pembayaran/pembayaran_index.php">
                                <i class="bi bi-credit-card me-2"></i> Pembayaran
                            </a>
                        </li>
                        <li class="nav-item mt-5 pt-3 border-top border-light d-md-none">
                            <a class="nav-link text-white" href="#" onclick="confirmLogout()">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-10 ms-sm-auto px-md-4 bg-light min-vh-100">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <div class="d-block d-md-none">
                        <h5 class="text-primary fw-bold">PMB System</h5>
                    </div>
                    <div class="ms-auto">
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center link-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <strong>Admin</strong>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end text-small shadow border-0" aria-labelledby="dropdownUser1">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="confirmLogout()">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="content-wrapper">
                    
    <script>
    function confirmLogout() {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Anda akan keluar dari sesi ini.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Logout!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Arahkan ke file logout.php di folder root (sesuaikan path jika perlu)
                window.location.href = '/pendaftaran_mahasiswa/logout.php'; 
            }
        })
    }
    </script>