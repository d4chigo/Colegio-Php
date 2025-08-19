<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';

if ($_POST) {
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $apellido1 = $_POST['apellido1'];
    $apellido2 = $_POST['apellido2'] ?? null;
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $direccion = $_POST['direccion'] ?? null;
    $telefono = $_POST['telefono'] ?? null;
    $email = $_POST['email'] ?? null;
    $acudiente_nombre = $_POST['acudiente_nombre'] ?? null;
    $acudiente_telefono = $_POST['acudiente_telefono'] ?? null;
    $acudiente_email = $_POST['acudiente_email'] ?? null;
    $observaciones = $_POST['observaciones'] ?? null;

    try {
        $query = "INSERT INTO estudiantes (cedula, nombre, apellido1, apellido2, fecha_nacimiento, genero, direccion, telefono, email, acudiente_nombre, acudiente_telefono, acudiente_email, observaciones) 
                  VALUES (:cedula, :nombre, :apellido1, :apellido2, :fecha_nacimiento, :genero, :direccion, :telefono, :email, :acudiente_nombre, :acudiente_telefono, :acudiente_email, :observaciones)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido1', $apellido1);
        $stmt->bindParam(':apellido2', $apellido2);
        $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
        $stmt->bindParam(':genero', $genero);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':acudiente_nombre', $acudiente_nombre);
        $stmt->bindParam(':acudiente_telefono', $acudiente_telefono);
        $stmt->bindParam(':acudiente_email', $acudiente_email);
        $stmt->bindParam(':observaciones', $observaciones);
        
        if ($stmt->execute()) {
            $success = "Estudiante registrado correctamente";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Ya existe un estudiante con esa cédula";
        } else {
            $error = "Error al registrar el estudiante: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Estudiante - Sistema Escolar</title>
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
                <a class="nav-link" href="index.php">Estudiantes</a>
                <a class="nav-link" href="../logout.php">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-user-plus me-2"></i>Nuevo Estudiante</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check me-2"></i><?php echo $success; ?>
                                <a href="index.php" class="btn btn-sm btn-outline-success ms-3">Ver Estudiantes</a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <!-- Información Personal -->
                            <h5 class="mb-3"><i class="fas fa-user me-2"></i>Información Personal</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Cédula *</label>
                                    <input type="text" class="form-control" name="cedula" required 
                                           value="<?php echo $_POST['cedula'] ?? ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" name="nombre" required
                                           value="<?php echo $_POST['nombre'] ?? ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Primer Apellido *</label>
                                    <input type="text" class="form-control" name="apellido1" required
                                           value="<?php echo $_POST['apellido1'] ?? ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Segundo Apellido</label>
                                    <input type="text" class="form-control" name="apellido2"
                                           value="<?php echo $_POST['apellido2'] ?? ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fecha de Nacimiento *</label>
                                    <input type="date" class="form-control" name="fecha_nacimiento" required
                                           value="<?php echo $_POST['fecha_nacimiento'] ?? ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Género *</label>
                                    <select class="form-select" name="genero" required>
                                        <option value="">Seleccionar</option>
                                        <option value="Masculino" <?php echo ($_POST['genero'] ?? '') == 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                                        <option value="Femenino" <?php echo ($_POST['genero'] ?? '') == 'Femenino' ? 'selected' : ''; ?>>Femenino</option>
                                        <option value="Otro" <?php echo ($_POST['genero'] ?? '') == 'Otro' ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Información de Contacto -->
                            <h5 class="mb-3"><i class="fas fa-address-book me-2"></i>Información de Contacto</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label">Dirección</label>
                                    <textarea class="form-control" name="direccion" rows="2"><?php echo $_POST['direccion'] ?? ''; ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" name="telefono"
                                           value="<?php echo $_POST['telefono'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email"
                                           value="<?php echo $_POST['email'] ?? ''; ?>">
                                </div>
                            </div>

                            <!-- Información del Acudiente -->
                            <h5 class="mb-3"><i class="fas fa-user-friends me-2"></i>Información del Acudiente</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Nombre del Acudiente</label>
                                    <input type="text" class="form-control" name="acudiente_nombre"
                                           value="<?php echo $_POST['acudiente_nombre'] ?? ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Teléfono del Acudiente</label>
                                    <input type="tel" class="form-control" name="acudiente_telefono"
                                           value="<?php echo $_POST['acudiente_telefono'] ?? ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email del Acudiente</label>
                                    <input type="email" class="form-control" name="acudiente_email"
                                           value="<?php echo $_POST['acudiente_email'] ?? ''; ?>">
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="mb-4">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" rows="3"><?php echo $_POST['observaciones'] ?? ''; ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Estudiante
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
