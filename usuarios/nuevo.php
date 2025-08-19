<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireRole(['admin']);

$database = new Database();
$conn = $database->getConnection();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    $estado = $_POST['estado'];

    try {
        // Verificar si el username ya existe
        $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $error = "El nombre de usuario ya existe";
        } else {
            // Verificar si el email ya existe
            $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                $error = "El email ya está registrado";
            } else {
                // Insertar nuevo usuario
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO usuarios (username, password, nombre, email, rol, estado, fecha_creacion) 
                                        VALUES (:username, :password, :nombre, :email, :rol, :estado, NOW())");
                
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $password_hash);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':rol', $rol);
                $stmt->bindParam(':estado', $estado);
                
                if ($stmt->execute()) {
                    header('Location: index.php?success=1');
                    exit;
                }
            }
        }
    } catch (PDOException $e) {
        $error = "Error al crear el usuario: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Usuario - Sistema Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-school me-2"></i>Sistema Escolar
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../dashboard.php">Dashboard</a>
                <a class="nav-link" href="../logout.php">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-user-plus me-2"></i>Nuevo Usuario</h4>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Nombre de Usuario *</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                           required minlength="3" maxlength="50">
                                    <div class="form-text">Mínimo 3 caracteres, sin espacios</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" 
                                               required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Mínimo 6 caracteres</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre Completo *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" 
                                           required maxlength="100">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                           required maxlength="100">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="rol" class="form-label">Rol *</label>
                                    <select class="form-select" id="rol" name="rol" required>
                                        <option value="">Seleccionar rol</option>
                                        <option value="admin" <?php echo ($_POST['rol'] ?? '') == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                        <option value="secretaria" <?php echo ($_POST['rol'] ?? '') == 'secretaria' ? 'selected' : ''; ?>>Secretaria</option>
                                        <option value="profesor" <?php echo ($_POST['rol'] ?? '') == 'profesor' ? 'selected' : ''; ?>>Profesor</option>
                                        <option value="contador" <?php echo ($_POST['rol'] ?? '') == 'contador' ? 'selected' : ''; ?>>Contador</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="estado" class="form-label">Estado *</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="Activo" <?php echo ($_POST['estado'] ?? 'Activo') == 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="Inactivo" <?php echo ($_POST['estado'] ?? '') == 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Permisos por rol:</strong><br>
                                        <strong>Administrador:</strong> Acceso completo al sistema<br>
                                        <strong>Secretaria:</strong> Gestión de estudiantes, personal, matrículas y cobros<br>
                                        <strong>Profesor:</strong> Solo estudiantes y cursos<br>
                                        <strong>Contador:</strong> Estudiantes, cursos y cobros
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Crear Usuario
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }

        // Validar username en tiempo real
        document.getElementById('username').addEventListener('input', function() {
            this.value = this.value.replace(/\s/g, '').toLowerCase();
        });
    </script>
</body>
</html>
