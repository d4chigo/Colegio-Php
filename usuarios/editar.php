<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireRole(['admin']);

$database = new Database();
$conn = $database->getConnection();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$usuario = $stmt->fetch();

if (!$usuario) {
    header('Location: index.php');
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    $estado = $_POST['estado'];
    $password = $_POST['password'];

    try {
        // Verificar si el username ya existe (excepto el actual)
        $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE username = :username AND id_usuario != :id");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $error = "El nombre de usuario ya existe";
        } else {
            // Verificar si el email ya existe (excepto el actual)
            $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email AND id_usuario != :id");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                $error = "El email ya está registrado";
            } else {
                // Actualizar usuario
                if (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE usuarios SET 
                                            username = :username,
                                            password = :password,
                                            nombre = :nombre,
                                            email = :email,
                                            rol = :rol,
                                            estado = :estado
                                            WHERE id_usuario = :id");
                    $stmt->bindParam(':password', $password_hash);
                } else {
                    $stmt = $conn->prepare("UPDATE usuarios SET 
                                            username = :username,
                                            nombre = :nombre,
                                            email = :email,
                                            rol = :rol,
                                            estado = :estado
                                            WHERE id_usuario = :id");
                }
                
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':rol', $rol);
                $stmt->bindParam(':estado', $estado);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    header('Location: index.php?success=updated');
                    exit;
                }
            }
        }
    } catch (PDOException $e) {
        $error = "Error al actualizar el usuario: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Sistema Escolar</title>
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
                        <h4><i class="fas fa-user-edit me-2"></i>Editar Usuario</h4>
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
                                           value="<?php echo htmlspecialchars($usuario['username']); ?>" 
                                           required minlength="3" maxlength="50">
                                    <div class="form-text">Mínimo 3 caracteres, sin espacios</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Nueva Contraseña</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" 
                                               minlength="6" placeholder="Dejar en blanco para no cambiar">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Dejar vacío para mantener la contraseña actual</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre Completo *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?php echo htmlspecialchars($usuario['nombre']); ?>" 
                                           required maxlength="100">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($usuario['email']); ?>" 
                                           required maxlength="100">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="rol" class="form-label">Rol *</label>
                                    <select class="form-select" id="rol" name="rol" required>
                                        <option value="">Seleccionar rol</option>
                                        <option value="admin" <?php echo $usuario['rol'] == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                        <option value="secretaria" <?php echo $usuario['rol'] == 'secretaria' ? 'selected' : ''; ?>>Secretaria</option>
                                        <option value="profesor" <?php echo $usuario['rol'] == 'profesor' ? 'selected' : ''; ?>>Profesor</option>
                                        <option value="contador" <?php echo $usuario['rol'] == 'contador' ? 'selected' : ''; ?>>Contador</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="estado" class="form-label">Estado *</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="Activo" <?php echo $usuario['estado'] == 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="Inactivo" <?php echo $usuario['estado'] == 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Información del usuario:</strong><br>
                                        <strong>ID:</strong> <?php echo $usuario['id_usuario']; ?><br>
                                        <strong>Fecha de creación:</strong> <?php echo date('d/m/Y H:i', strtotime($usuario['created_at'] ?? $usuario['fecha_creacion'] ?? 'now')); ?><br>
                                        <strong>Último acceso:</strong> <?php echo $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'Nunca'; ?>
                                    </div>
                                </div>

                                <?php if ($usuario['id_usuario'] == $_SESSION['user_id']): ?>
                                <div class="col-12 mb-3">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Atención:</strong> Estás editando tu propio usuario. Ten cuidado al cambiar el rol o estado.
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Cambios
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
