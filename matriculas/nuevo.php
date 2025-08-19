<?php
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';

// Obtener estudiantes activos
$estudiantes_query = "SELECT cedula, nombre, apellido1, apellido2 FROM estudiantes WHERE estado = 'Activo' ORDER BY apellido1, apellido2, nombre";
$estudiantes_stmt = $conn->prepare($estudiantes_query);
$estudiantes_stmt->execute();
$estudiantes = $estudiantes_stmt->fetchAll();

// Obtener cursos activos
$cursos_query = "SELECT codigo, nombre, nivel, grado, seccion FROM cursos WHERE estado = 'Activo' ORDER BY nivel, grado, nombre";
$cursos_stmt = $conn->prepare($cursos_query);
$cursos_stmt->execute();
$cursos = $cursos_stmt->fetchAll();

if ($_POST) {
    $estudiante_id = $_POST['estudiante_id'];
    $curso_codigo = $_POST['curso_codigo'];
    $periodo_escolar = $_POST['periodo_escolar'];
    $jornada = $_POST['jornada'] ?? 'Mañana';
    $modalidad = $_POST['modalidad'] ?? 'Presencial';
    $observaciones = $_POST['observaciones'] ?? null;

    try {
        // Verificar si ya existe una matrícula activa para este estudiante en este período y curso
        $check_query = "SELECT COUNT(*) FROM matriculas WHERE estudiante_id = :estudiante_id AND curso_codigo = :curso_codigo AND periodo_escolar = :periodo_escolar AND estado = 'Activa'";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':estudiante_id', $estudiante_id);
        $check_stmt->bindParam(':curso_codigo', $curso_codigo);
        $check_stmt->bindParam(':periodo_escolar', $periodo_escolar);
        $check_stmt->execute();
        
        if ($check_stmt->fetchColumn() > 0) {
            $error = "El estudiante ya tiene una matrícula activa en este curso para el período seleccionado";
        } else {
            $query = "INSERT INTO matriculas (estudiante_id, curso_codigo, periodo_escolar, jornada, modalidad, observaciones) 
                      VALUES (:estudiante_id, :curso_codigo, :periodo_escolar, :jornada, :modalidad, :observaciones)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':estudiante_id', $estudiante_id);
            $stmt->bindParam(':curso_codigo', $curso_codigo);
            $stmt->bindParam(':periodo_escolar', $periodo_escolar);
            $stmt->bindParam(':jornada', $jornada);
            $stmt->bindParam(':modalidad', $modalidad);
            $stmt->bindParam(':observaciones', $observaciones);
            
            if ($stmt->execute()) {
                $success = "Matrícula registrada correctamente";
            }
        }
    } catch (PDOException $e) {
        $error = "Error al registrar la matrícula: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Matrícula - Sistema Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-school me-2"></i>Sistema Escolar
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../dashboard.php">Dashboard</a>
                <a class="nav-link" href="index.php">Matrículas</a>
                <a class="nav-link" href="../logout.php">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-plus me-2"></i>Nueva Matrícula</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check me-2"></i><?php echo $success; ?>
                                <a href="index.php" class="btn btn-sm btn-outline-success ms-3">Ver Matrículas</a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <!-- Información del Estudiante -->
                            <h5 class="mb-3"><i class="fas fa-user-graduate me-2"></i>Información del Estudiante</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label">Estudiante *</label>
                                    <select class="form-select" name="estudiante_id" required>
                                        <option value="">Seleccionar estudiante</option>
                                        <?php foreach ($estudiantes as $estudiante): ?>
                                        <option value="<?php echo $estudiante['cedula']; ?>" 
                                                <?php echo ($_POST['estudiante_id'] ?? '') == $estudiante['cedula'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . 
                                                     $estudiante['apellido1'] . ' ' . 
                                                     ($estudiante['apellido2'] ?? '') . 
                                                     ' - ' . $estudiante['cedula']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Información del Curso -->
                            <h5 class="mb-3"><i class="fas fa-book me-2"></i>Información del Curso</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label">Curso *</label>
                                    <select class="form-select" name="curso_codigo" required>
                                        <option value="">Seleccionar curso</option>
                                        <?php foreach ($cursos as $curso): ?>
                                        <option value="<?php echo $curso['codigo']; ?>"
                                                <?php echo ($_POST['curso_codigo'] ?? '') == $curso['codigo'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($curso['codigo'] . ' - ' . $curso['nombre'] . 
                                                     ' (' . $curso['nivel'] . ' - ' . $curso['grado'] . 
                                                     ($curso['seccion'] ? ' ' . $curso['seccion'] : '') . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Información de la Matrícula -->
                            <h5 class="mb-3"><i class="fas fa-clipboard-list me-2"></i>Información de la Matrícula</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Período Escolar *</label>
                                    <select class="form-select" name="periodo_escolar" required>
                                        <option value="">Seleccionar período</option>
                                        <option value="2024" <?php echo ($_POST['periodo_escolar'] ?? '2024') == '2024' ? 'selected' : ''; ?>>2024</option>
                                        <option value="2025" <?php echo ($_POST['periodo_escolar'] ?? '') == '2025' ? 'selected' : ''; ?>>2025</option>
                                        <option value="2026" <?php echo ($_POST['periodo_escolar'] ?? '') == '2026' ? 'selected' : ''; ?>>2026</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jornada *</label>
                                    <select class="form-select" name="jornada" required>
                                        <option value="Mañana" <?php echo ($_POST['jornada'] ?? 'Mañana') == 'Mañana' ? 'selected' : ''; ?>>Mañana</option>
                                        <option value="Tarde" <?php echo ($_POST['jornada'] ?? '') == 'Tarde' ? 'selected' : ''; ?>>Tarde</option>
                                        <option value="Noche" <?php echo ($_POST['jornada'] ?? '') == 'Noche' ? 'selected' : ''; ?>>Noche</option>
                                        <option value="Completa" <?php echo ($_POST['jornada'] ?? '') == 'Completa' ? 'selected' : ''; ?>>Completa</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Modalidad *</label>
                                    <select class="form-select" name="modalidad" required>
                                        <option value="Presencial" <?php echo ($_POST['modalidad'] ?? 'Presencial') == 'Presencial' ? 'selected' : ''; ?>>Presencial</option>
                                        <option value="Virtual" <?php echo ($_POST['modalidad'] ?? '') == 'Virtual' ? 'selected' : ''; ?>>Virtual</option>
                                        <option value="Híbrida" <?php echo ($_POST['modalidad'] ?? '') == 'Híbrida' ? 'selected' : ''; ?>>Híbrida</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Información Adicional -->
                            <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Información Adicional</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label">Fecha de Matrícula</label>
                                    <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" disabled>
                                    <small class="form-text text-muted">La fecha de matrícula se establece automáticamente</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Estado Inicial</label>
                                    <input type="text" class="form-control" value="Activa" disabled>
                                    <small class="form-text text-muted">Las matrículas nuevas se crean como activas</small>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="mb-4">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" rows="3"
                                          placeholder="Observaciones adicionales sobre la matrícula"><?php echo $_POST['observaciones'] ?? ''; ?></textarea>
                            </div>

                            <!-- Resumen de Matrícula -->
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Información Importante</h6>
                                <ul class="mb-0">
                                    <li>Verifique que el estudiante esté activo en el sistema</li>
                                    <li>Asegúrese de que el curso tenga cupos disponibles</li>
                                    <li>La matrícula se creará con estado "Activa"</li>
                                    <li>No se pueden crear matrículas duplicadas para el mismo período</li>
                                </ul>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Matrícula
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
