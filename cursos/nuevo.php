<?php
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';

// Obtener profesores para el select
$profesores_query = "SELECT cedula, nombre, apellido1, apellido2 FROM personal WHERE estado = 'Activo' AND cargo LIKE '%Profesor%' ORDER BY apellido1, apellido2, nombre";
$profesores_stmt = $conn->prepare($profesores_query);
$profesores_stmt->execute();
$profesores = $profesores_stmt->fetchAll();

// Obtener aulas disponibles
$aulas_query = "SELECT id_aula, numero, nombre FROM aulas WHERE estado = 'Disponible' ORDER BY numero";
$aulas_stmt = $conn->prepare($aulas_query);
$aulas_stmt->execute();
$aulas = $aulas_stmt->fetchAll();

if ($_POST) {
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'] ?? null;
    $nivel = $_POST['nivel'];
    $grado = $_POST['grado'];
    $seccion = $_POST['seccion'] ?? null;
    $horas_semanales = $_POST['horas_semanales'] ?? 1;
    $modalidad = $_POST['modalidad'] ?? 'Presencial';
    $profesor_id = $_POST['profesor_id'] ?? null;
    $aula_id = $_POST['aula_id'] ?? null;
    $cupo_maximo = $_POST['cupo_maximo'] ?? 30;
    $horario = $_POST['horario'] ?? null;
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;

    try {
        $query = "INSERT INTO cursos (codigo, nombre, descripcion, nivel, grado, seccion, horas_semanales, modalidad, profesor_id, aula_id, cupo_maximo, horario, fecha_inicio, fecha_fin) 
                  VALUES (:codigo, :nombre, :descripcion, :nivel, :grado, :seccion, :horas_semanales, :modalidad, :profesor_id, :aula_id, :cupo_maximo, :horario, :fecha_inicio, :fecha_fin)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':nivel', $nivel);
        $stmt->bindParam(':grado', $grado);
        $stmt->bindParam(':seccion', $seccion);
        $stmt->bindParam(':horas_semanales', $horas_semanales);
        $stmt->bindParam(':modalidad', $modalidad);
        $stmt->bindParam(':profesor_id', $profesor_id);
        $stmt->bindParam(':aula_id', $aula_id);
        $stmt->bindParam(':cupo_maximo', $cupo_maximo);
        $stmt->bindParam(':horario', $horario);
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
        
        if ($stmt->execute()) {
            $success = "Curso registrado correctamente";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Ya existe un curso con ese código";
        } else {
            $error = "Error al registrar el curso: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Curso - Sistema Escolar</title>
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
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-school me-2"></i>Sistema Escolar
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../dashboard.php">Dashboard</a>
                <a class="nav-link" href="index.php">Cursos</a>
                <a class="nav-link" href="../logout.php">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-plus me-2"></i>Nuevo Curso</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check me-2"></i><?php echo $success; ?>
                                <a href="index.php" class="btn btn-sm btn-outline-success ms-3">Ver Cursos</a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <!-- Información Básica -->
                            <h5 class="mb-3"><i class="fas fa-book me-2"></i>Información Básica</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Código *</label>
                                    <input type="text" class="form-control" name="codigo" required 
                                           value="<?php echo $_POST['codigo'] ?? ''; ?>"
                                           placeholder="Ej: MAT-6A">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Nombre del Curso *</label>
                                    <input type="text" class="form-control" name="nombre" required
                                           value="<?php echo $_POST['nombre'] ?? ''; ?>"
                                           placeholder="Ej: Matemáticas 6° Grado">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" name="descripcion" rows="2"
                                              placeholder="Descripción del curso"><?php echo $_POST['descripcion'] ?? ''; ?></textarea>
                                </div>
                            </div>

                            <!-- Clasificación Académica -->
                            <h5 class="mb-3"><i class="fas fa-graduation-cap me-2"></i>Clasificación Académica</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Nivel *</label>
                                    <select class="form-select" name="nivel" required>
                                        <option value="">Seleccionar nivel</option>
                                        <option value="Preescolar" <?php echo ($_POST['nivel'] ?? '') == 'Preescolar' ? 'selected' : ''; ?>>Preescolar</option>
                                        <option value="Primaria" <?php echo ($_POST['nivel'] ?? '') == 'Primaria' ? 'selected' : ''; ?>>Primaria</option>
                                        <option value="Secundaria" <?php echo ($_POST['nivel'] ?? '') == 'Secundaria' ? 'selected' : ''; ?>>Secundaria</option>
                                        <option value="Media" <?php echo ($_POST['nivel'] ?? '') == 'Media' ? 'selected' : ''; ?>>Media</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Grado *</label>
                                    <select class="form-select" name="grado" required>
                                        <option value="">Seleccionar grado</option>
                                        <option value="Jardín" <?php echo ($_POST['grado'] ?? '') == 'Jardín' ? 'selected' : ''; ?>>Jardín</option>
                                        <option value="Transición" <?php echo ($_POST['grado'] ?? '') == 'Transición' ? 'selected' : ''; ?>>Transición</option>
                                        <option value="1" <?php echo ($_POST['grado'] ?? '') == '1' ? 'selected' : ''; ?>>1°</option>
                                        <option value="2" <?php echo ($_POST['grado'] ?? '') == '2' ? 'selected' : ''; ?>>2°</option>
                                        <option value="3" <?php echo ($_POST['grado'] ?? '') == '3' ? 'selected' : ''; ?>>3°</option>
                                        <option value="4" <?php echo ($_POST['grado'] ?? '') == '4' ? 'selected' : ''; ?>>4°</option>
                                        <option value="5" <?php echo ($_POST['grado'] ?? '') == '5' ? 'selected' : ''; ?>>5°</option>
                                        <option value="6" <?php echo ($_POST['grado'] ?? '') == '6' ? 'selected' : ''; ?>>6°</option>
                                        <option value="7" <?php echo ($_POST['grado'] ?? '') == '7' ? 'selected' : ''; ?>>7°</option>
                                        <option value="8" <?php echo ($_POST['grado'] ?? '') == '8' ? 'selected' : ''; ?>>8°</option>
                                        <option value="9" <?php echo ($_POST['grado'] ?? '') == '9' ? 'selected' : ''; ?>>9°</option>
                                        <option value="10" <?php echo ($_POST['grado'] ?? '') == '10' ? 'selected' : ''; ?>>10°</option>
                                        <option value="11" <?php echo ($_POST['grado'] ?? '') == '11' ? 'selected' : ''; ?>>11°</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Sección</label>
                                    <input type="text" class="form-control" name="seccion"
                                           value="<?php echo $_POST['seccion'] ?? ''; ?>"
                                           placeholder="A, B, C...">
                                </div>
                            </div>

                            <!-- Configuración del Curso -->
                            <h5 class="mb-3"><i class="fas fa-cogs me-2"></i>Configuración del Curso</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Horas Semanales</label>
                                    <input type="number" class="form-control" name="horas_semanales" min="1" max="40"
                                           value="<?php echo $_POST['horas_semanales'] ?? '1'; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Modalidad</label>
                                    <select class="form-select" name="modalidad">
                                        <option value="Presencial" <?php echo ($_POST['modalidad'] ?? 'Presencial') == 'Presencial' ? 'selected' : ''; ?>>Presencial</option>
                                        <option value="Virtual" <?php echo ($_POST['modalidad'] ?? '') == 'Virtual' ? 'selected' : ''; ?>>Virtual</option>
                                        <option value="Híbrida" <?php echo ($_POST['modalidad'] ?? '') == 'Híbrida' ? 'selected' : ''; ?>>Híbrida</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cupo Máximo</label>
                                    <input type="number" class="form-control" name="cupo_maximo" min="1" max="50"
                                           value="<?php echo $_POST['cupo_maximo'] ?? '30'; ?>">
                                </div>
                            </div>

                            <!-- Asignaciones -->
                            <h5 class="mb-3"><i class="fas fa-user-tie me-2"></i>Asignaciones</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Profesor</label>
                                    <select class="form-select" name="profesor_id">
                                        <option value="">Sin asignar</option>
                                        <?php foreach ($profesores as $profesor): ?>
                                        <option value="<?php echo $profesor['cedula']; ?>"
                                                <?php echo ($_POST['profesor_id'] ?? '') == $profesor['cedula'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($profesor['nombre'] . ' ' . 
                                                     $profesor['apellido1'] . ' ' . 
                                                     ($profesor['apellido2'] ?? '') . 
                                                     ' - ' . $profesor['cedula']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Aula</label>
                                    <select class="form-select" name="aula_id">
                                        <option value="">Sin asignar</option>
                                        <?php foreach ($aulas as $aula): ?>
                                        <option value="<?php echo $aula['id_aula']; ?>"
                                                <?php echo ($_POST['aula_id'] ?? '') == $aula['id_aula'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($aula['numero'] . 
                                                     ($aula['nombre'] ? ' - ' . $aula['nombre'] : '')); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Fechas y Horario -->
                            <h5 class="mb-3"><i class="fas fa-calendar me-2"></i>Fechas y Horario</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Inicio</label>
                                    <input type="date" class="form-control" name="fecha_inicio"
                                           value="<?php echo $_POST['fecha_inicio'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Fin</label>
                                    <input type="date" class="form-control" name="fecha_fin"
                                           value="<?php echo $_POST['fecha_fin'] ?? ''; ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Horario</label>
                                    <textarea class="form-control" name="horario" rows="3"
                                              placeholder="Ej: Lunes a Viernes 8:00 AM - 9:00 AM"><?php echo $_POST['horario'] ?? ''; ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Curso
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
