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

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Validasi email sudah terdaftar atau belum
        $check_email = "SELECT COUNT(*) FROM admin WHERE email = ?";
        $stmt = $db->prepare($check_email);
        $stmt->execute([$_POST['email']]);
        
        if ($stmt->fetchColumn() > 0) {
            $error = "Email sudah terdaftar! Gunakan email lain.";
        } else {
            // Validasi username sudah terdaftar atau belum
            $check_username = "SELECT COUNT(*) FROM admin WHERE username = ?";
            $stmt = $db->prepare($check_username);
            $stmt->execute([$_POST['username']]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = "Username sudah digunakan! Pilih username lain.";
            } else {
                // Insert data admin baru
                $query = "INSERT INTO admin (username, password, nama_admin, email, role) 
                          VALUES (:username, :password, :nama_admin, :email, :role)";
                
                $stmt = $db->prepare($query);
                
                // Hash password
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                
                $stmt->bindParam(':username', $_POST['username']);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':nama_admin', $_POST['nama_admin']);
                $stmt->bindParam(':email', $_POST['email']);
                $stmt->bindParam(':role', $_POST['role']);
                
                if ($stmt->execute()) {
                    $success = "Pendaftaran admin berhasil! Silakan login.";
                }
            }
        }
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Admin - Sistem PMB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        .register-card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            background: white;
            max-width: 600px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .role-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .role-card:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }
        .role-card.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <div class="card-body p-5">
                <!-- Header -->
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock-fill fs-1 text-primary"></i>
                    <h2 class="mt-2 mb-1">Pendaftaran Admin</h2>
                    <p class="text-muted">Buat akun admin untuk mengelola sistem</p>
                </div>

                <?php if ($success): ?>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '<?php echo $success; ?>',
                        confirmButtonColor: '#667eea'
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                </script>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-at"></i></span>
                                <input type="text" name="username" class="form-control" required 
                                       placeholder="username_admin">
                            </div>
                            <small class="text-muted">Tanpa spasi, huruf kecil</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control" required 
                                       placeholder="Min. 6 karakter" minlength="6" id="password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="nama_admin" class="form-control" required 
                                       placeholder="Nama lengkap admin">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" required 
                                       placeholder="admin@email.com">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label mb-3">Role Admin <span class="text-danger">*</span></label>
                            
                            <div class="row g-2">
                                <div class="col-12">
                                    <input type="radio" name="role" value="Verifikator Berkas" 
                                           id="role1" class="d-none" required>
                                    <label for="role1" class="role-card w-100" onclick="selectRole('role1')">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-check fs-3 text-primary me-3"></i>
                                            <div>
                                                <strong>Verifikator Berkas</strong>
                                                <p class="mb-0 small text-muted">Verifikasi berkas pendaftaran</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div class="col-12">
                                    <input type="radio" name="role" value="Keuangan" 
                                           id="role2" class="d-none" required>
                                    <label for="role2" class="role-card w-100" onclick="selectRole('role2')">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-cash-coin fs-3 text-success me-3"></i>
                                            <div>
                                                <strong>Keuangan</strong>
                                                <p class="mb-0 small text-muted">Kelola pembayaran & keuangan</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div class="col-12">
                                    <input type="radio" name="role" value="Super Admin" 
                                           id="role3" class="d-none" required>
                                    <label for="role3" class="role-card w-100" onclick="selectRole('role3')">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-shield-fill-check fs-3 text-danger me-3"></i>
                                            <div>
                                                <strong>Super Admin</strong>
                                                <p class="mb-0 small text-muted">Akses penuh ke semua fitur</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-register btn-primary btn-lg">
                            <i class="bi bi-shield-check me-2"></i>Daftar sebagai Admin
                        </button>
                        <a href="login.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Login
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            Sudah punya akun? <a href="login.php" class="text-primary">Login di sini</a>
                        </small>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectRole(roleId) {
            document.querySelectorAll('.role-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelector(`label[for="${roleId}"]`).classList.add('selected');
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