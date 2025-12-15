<!-- ========================================
     FILE 1: login.php
     Halaman Login (Admin & Camaba)
========================================= -->
<?php
session_start();
require_once 'config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] == 'admin') {
        header("Location: index.php");
    } else {
        header("Location: camaba_dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    try {
        if ($user_type == 'camaba') {
            // Login sebagai Camaba
            $query = "SELECT * FROM camaba WHERE email = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id_camaba'];
                $_SESSION['user_type'] = 'camaba';
                $_SESSION['nama'] = $user['nama_lengkap'];
                $_SESSION['email'] = $user['email'];
                header("Location: camaba_dashboard.php");
                exit();
            } else {
                $error = "Email atau password salah!";
            }
        } else {
            // Login sebagai Admin
            $query = "SELECT * FROM admin WHERE email = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id_admin'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['nama'] = $user['nama_admin'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Email atau password salah!";
            }
        }
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan sistem!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem PMB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
        }
        .login-left {
            background: white;
            padding: 3rem;
        }
        .login-right {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .user-type-btn {
            border: 2px solid #e0e0e0;
            padding: 1rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .user-type-btn:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }
        .user-type-btn.active {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card login-card border-0">
                    <div class="row g-0">
                        <!-- Left Side - Form -->
                        <div class="col-md-6 login-left">
                            <div class="text-center mb-4">
                                <i class="bi bi-mortarboard-fill fs-1 text-primary"></i>
                                <h3 class="mt-2 mb-1">Selamat Datang!</h3>
                                <p class="text-muted">Masuk ke Sistem PMB</p>
                            </div>

                            <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle"></i> <?php echo $error; ?>
                            </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <!-- Pilih Tipe User -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Masuk Sebagai:</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="radio" name="user_type" value="camaba" 
                                                   id="type_camaba" class="d-none" required checked>
                                            <label for="type_camaba" class="user-type-btn w-100 text-center active" 
                                                   onclick="selectType('camaba')">
                                                <i class="bi bi-person-circle fs-2 d-block mb-2"></i>
                                                <strong>Camaba</strong>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <input type="radio" name="user_type" value="admin" 
                                                   id="type_admin" class="d-none" required>
                                            <label for="type_admin" class="user-type-btn w-100 text-center" 
                                                   onclick="selectType('admin')">
                                                <i class="bi bi-shield-lock fs-2 d-block mb-2"></i>
                                                <strong>Admin</strong>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-envelope"></i>
                                        </span>
                                        <input type="email" name="email" class="form-control" 
                                               placeholder="Masukkan email" required>
                                    </div>
                                </div>

                                <!-- Password -->
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-lock"></i>
                                        </span>
                                        <input type="password" name="password" class="form-control" 
                                               placeholder="Masukkan password" required id="password">
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="togglePassword()">
                                            <i class="bi bi-eye" id="eyeIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-login btn-primary w-100 mb-3">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Masuk
                                </button>

                                <div class="text-center">
                                    <small class="text-muted" id="registerLink">
                                        Belum punya akun? 
                                        <a href="register_camaba.php" class="text-primary" id="registerLinkHref">
                                            Daftar Sebagai Camaba
                                        </a>
                                    </small>
                                </div>
                            </form>
                        </div>

                        <!-- Right Side - Info -->
                        <div class="col-md-6 login-right">
                            <h2 class="mb-4">Sistem Pendaftaran Mahasiswa Baru</h2>
                            <p class="mb-4">Portal terintegrasi untuk proses penerimaan mahasiswa baru yang efisien dan modern.</p>
                            
                            <div class="d-flex align-items-start mb-3">
                                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                                <div>
                                    <h6>Pendaftaran Online</h6>
                                    <small>Daftar kapan saja, dimana saja dengan mudah</small>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-start mb-3">
                                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                                <div>
                                    <h6>Tracking Status Real-time</h6>
                                    <small>Pantau progress pendaftaran Anda secara langsung</small>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                                <div>
                                    <h6>Pembayaran Terintegrasi</h6>
                                    <small>Berbagai metode pembayaran yang aman dan cepat</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectType(type) {
            document.querySelectorAll('.user-type-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`label[for="type_${type}"]`).classList.add('active');
            
            // Update register link based on user type
            const registerLinkHref = document.getElementById('registerLinkHref');
            const registerLink = document.getElementById('registerLink');
            
            if (type === 'camaba') {
                registerLinkHref.href = 'register_camaba.php';
                registerLink.innerHTML = 'Belum punya akun? <a href="register_camaba.php" class="text-primary" id="registerLinkHref">Daftar Sebagai Camaba</a>';
            } else {
                registerLinkHref.href = 'register_admin.php';
                registerLink.innerHTML = 'Belum punya akun? <a href="register_admin.php" class="text-primary" id="registerLinkHref">Daftar Sebagai Admin</a>';
            }
        }

        function togglePassword() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>
