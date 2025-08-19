<?php
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();

// Si ya está logueado, redirigir al dashboard
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$success = '';
$error = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $rol = $_POST['rol'] ?? 'secretaria';
    
    // Validaciones
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido';
    } else {
        // Intentar crear el usuario
        if ($auth->createUser($username, $email, $password, $rol)) {
            $success = 'Usuario registrado correctamente. Ya puedes iniciar sesión.';
        } else {
            $error = 'Error al registrar el usuario. Es posible que el usuario o email ya existan.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema de Gestión Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="register-card">
                    <div class="register-header">
                        <i class="fas fa-user-plus fa-3x mb-3"></i>
                        <h3>Crear Cuenta</h3>
                        <p class="mb-0">Sistema de Gestión Escolar</p>
                    </div>
                    <div class="register-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="login.php" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$success): ?>
                        <form method="POST" id="registerForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-2"></i>Usuario
                                </label>
                                <input type="text" class="form-control" id="username" name="username" required
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                       placeholder="Nombre de usuario único">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                       placeholder="correo@ejemplo.com">
                            </div>
                            
                            <div class="mb-3">
                                <label for="rol" class="form-label">
                                    <i class="fas fa-user-tag me-2"></i>Rol
                                </label>
                                <select class="form-select" id="rol" name="rol" required>
                                    <option value="secretaria" <?php echo ($_POST['rol'] ?? 'secretaria') == 'secretaria' ? 'selected' : ''; ?>>Secretaria</option>
                                    <option value="profesor" <?php echo ($_POST['rol'] ?? '') == 'profesor' ? 'selected' : ''; ?>>Profesor</option>
                                    <option value="contador" <?php echo ($_POST['rol'] ?? '') == 'contador' ? 'selected' : ''; ?>>Contador</option>
                                    <option value="admin" <?php echo ($_POST['rol'] ?? '') == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                                <div class="form-text">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        <strong>Secretaria:</strong> Gestión de estudiantes y matrículas<br>
                                        <strong>Profesor:</strong> Consulta de cursos y estudiantes<br>
                                        <strong>Contador:</strong> Gestión de cobros y pagos<br>
                                        <strong>Admin:</strong> Acceso completo al sistema
                                    </small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Contraseña
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required
                                       placeholder="Mínimo 6 caracteres" onkeyup="checkPasswordStrength()">
                                <div class="password-strength" id="passwordStrength"></div>
                                <div class="form-text" id="passwordHelp">
                                    <small>La contraseña debe tener al menos 6 caracteres</small>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Confirmar Contraseña
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                                       placeholder="Repite la contraseña" onkeyup="checkPasswordMatch()">
                                <div class="form-text" id="passwordMatch"></div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-register w-100">
                                <i class="fas fa-user-plus me-2"></i>Crear Cuenta
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">
                                ¿Ya tienes cuenta? 
                                <a href="login.php" class="text-decoration-none">
                                    <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
                                </a>
                            </p>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="index.html" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-home me-1"></i>Volver al Inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('passwordStrength');
            const helpText = document.getElementById('passwordHelp');
            
            if (password.length === 0) {
                strengthBar.className = 'password-strength';
                helpText.innerHTML = '<small>La contraseña debe tener al menos 6 caracteres</small>';
                return;
            }
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'password-strength';
            if (strength <= 1) {
                strengthBar.classList.add('strength-weak');
                helpText.innerHTML = '<small class="text-danger">Contraseña débil</small>';
            } else if (strength <= 2) {
                strengthBar.classList.add('strength-medium');
                helpText.innerHTML = '<small class="text-warning">Contraseña media</small>';
            } else {
                strengthBar.classList.add('strength-strong');
                helpText.innerHTML = '<small class="text-success">Contraseña fuerte</small>';
            }
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchText.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchText.innerHTML = '<small class="text-success"><i class="fas fa-check me-1"></i>Las contraseñas coinciden</small>';
            } else {
                matchText.innerHTML = '<small class="text-danger"><i class="fas fa-times me-1"></i>Las contraseñas no coinciden</small>';
            }
        }
    </script>
</body>
</html>
