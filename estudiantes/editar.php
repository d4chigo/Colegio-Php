<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();

$cedula = $_GET['id'] ?? null;
if (!$cedula) {
    header('Location: index.php');
    exit;
}

// Obtener datos del estudiante
$stmt = $conn->prepare("SELECT * FROM estudiantes WHERE cedula = :cedula");
$stmt->bindParam(':cedula', $cedula);
$stmt->execute();
$estudiante = $stmt->fetch();

if (!$estudiante) {
    header('Location: index.php');
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido1 = $_POST['apellido1'];
    $apellido2 = $_POST['apellido2'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $estado = $_POST['estado'];
    $acudiente_nombre = $_POST['acudiente_nombre'];
    $acudiente_telefono = $_POST['acudiente_telefono'];
    $acudiente_email = $_POST['acudiente_email'];

    try {
        $stmt = $conn->prepare("UPDATE estudiantes SET 
                                nombre = :nombre,
                                apellido1 = :apellido1,
                                apellido2 = :apellido2,
                                fecha_nacimiento = :fecha_nacimiento,
                                genero = :genero,
                                direccion = :direccion,
                                telefono = :telefono,
                                email = :email,
                                estado = :estado,
                                acudiente_nombre = :acudiente_nombre,
                                acudiente_telefono = :acudiente_telefono,
                                acudiente_email = :acudiente_email
                                WHERE cedula = :cedula");
        
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido1', $apellido1);
        $stmt->bindParam(':apellido2', $apellido2);
        $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
        $stmt->bindParam(':genero', $genero);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':acudiente_nombre', $acudiente_nombre);
        $stmt->bindParam(':acudiente_telefono', $acudiente_telefono);
        $stmt->bindParam(':acudiente_email', $acudiente_email);
        $stmt->bindParam(':cedula', $cedula);
        
        if ($stmt->execute()) {
            header('Location: ver.php?id=' . $cedula . '&success=1');
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error al actualizar el estudiante: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Estudiante - Sistema Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
    </style>
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
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-edit me-2"></i>Editar Estudiante</h4>
                        <a href="ver.php?id=<?php echo $estudiante['cedula']; ?>" class="btn btn-secondary">
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
                                <div class="col-12 mb-3">
                                    <h5 class="text-primary"><i class="fas fa-user me-2"></i>Información Personal</h5>
                                    <hr>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="cedula" class="form-label">Cédula</label>
                                    <input type="text" class="form-control" id="cedula" 
                                           value="<?php echo $estudiante['cedula']; ?>" readonly>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="nombre" class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?php echo htmlspecialchars($estudiante['nombre']); ?>" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="apellido1" class="form-label">Primer Apellido *</label>
                                    <input type="text" class="form-control" id="apellido1" name="apellido1" 
                                           value="<?php echo htmlspecialchars($estudiante['apellido1']); ?>" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="apellido2" class="form-label">Segundo Apellido</label>
                                    <input type="text" class="form-control" id="apellido2" name="apellido2" 
                                           value="<?php echo htmlspecialchars($estudiante['apellido2']); ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                           value="<?php echo $estudiante['fecha_nacimiento']; ?>" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="genero" class="form-label">Género *</label>
                                    <select class="form-select" id="genero" name="genero" required>
                                        <option value="Masculino" <?php echo $estudiante['genero'] == 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                                        <option value="Femenino" <?php echo $estudiante['genero'] == 'Femenino' ? 'selected' : ''; ?>>Femenino</option>
                                    </select>
                                </div>

                                <div class="col-md-8 mb-3">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input type="text" class="form-control" id="direccion" name="direccion" 
                                           value="<?php echo htmlspecialchars($estudiante['direccion']); ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="estado" class="form-label">Estado *</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="Activo" <?php echo $estudiante['estado'] == 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="Inactivo" <?php echo $estudiante['estado'] == 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                        <option value="Graduado" <?php echo $estudiante['estado'] == 'Graduado' ? 'selected' : ''; ?>>Graduado</option>
                                        <option value="Retirado" <?php echo $estudiante['estado'] == 'Retirado' ? 'selected' : ''; ?>>Retirado</option>
                                    </select>
                                </div>

                                <div class="col-12 mb-3 mt-3">
                                    <h5 class="text-primary"><i class="fas fa-address-book me-2"></i>Información de Contacto</h5>
                                    <hr>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                                           value="<?php echo htmlspecialchars($estudiante['telefono']); ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($estudiante['email']); ?>">
                                </div>

                                <div class="col-12 mb-3 mt-3">
                                    <h5 class="text-primary"><i class="fas fa-users me-2"></i>Información del Acudiente</h5>
                                    <hr>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="acudiente_nombre" class="form-label">Nombre del Acudiente</label>
                                    <input type="text" class="form-control" id="acudiente_nombre" name="acudiente_nombre" 
                                           value="<?php echo htmlspecialchars($estudiante['acudiente_nombre']); ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="acudiente_telefono" class="form-label">Teléfono del Acudiente</label>
                                    <input type="tel" class="form-control" id="acudiente_telefono" name="acudiente_telefono" 
                                           value="<?php echo htmlspecialchars($estudiante['acudiente_telefono']); ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="acudiente_email" class="form-label">Email del Acudiente</label>
                                    <input type="email" class="form-control" id="acudiente_email" name="acudiente_email" 
                                           value="<?php echo htmlspecialchars($estudiante['acudiente_email']); ?>">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="ver.php?id=<?php echo $estudiante['cedula']; ?>" class="btn btn-secondary">
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
</body>
</html>
