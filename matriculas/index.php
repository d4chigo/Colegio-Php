<?php
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();

// Procesar eliminación
if (isset($_GET['delete'])) {
    $id_matricula = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM matriculas WHERE id_matricula = :id_matricula");
    $stmt->bindParam(':id_matricula', $id_matricula);
    if ($stmt->execute()) {
        $success = "Matrícula eliminada correctamente";
    } else {
        $error = "Error al eliminar la matrícula";
    }
}

// Obtener matrículas con filtros
$search = $_GET['search'] ?? '';
$estado = $_GET['estado'] ?? '';
$periodo = $_GET['periodo'] ?? '';
$jornada = $_GET['jornada'] ?? '';

$query = "SELECT m.*, e.nombre as estudiante_nombre, e.apellido1 as estudiante_apellido1, e.apellido2 as estudiante_apellido2, 
                 c.nombre as curso_nombre, c.nivel, c.grado 
          FROM matriculas m 
          LEFT JOIN estudiantes e ON m.estudiante_id = e.cedula 
          LEFT JOIN cursos c ON m.curso_codigo = c.codigo 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (e.nombre LIKE :search OR e.apellido1 LIKE :search OR e.cedula LIKE :search OR c.nombre LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($estado) {
    $query .= " AND m.estado = :estado";
    $params[':estado'] = $estado;
}

if ($periodo) {
    $query .= " AND m.periodo_escolar = :periodo";
    $params[':periodo'] = $periodo;
}

if ($jornada) {
    $query .= " AND m.jornada = :jornada";
    $params[':jornada'] = $jornada;
}

$query .= " ORDER BY m.fecha_matricula DESC, e.apellido1, e.apellido2, e.nombre";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$matriculas = $stmt->fetchAll();

// Obtener períodos disponibles
$periodos_query = "SELECT DISTINCT periodo_escolar FROM matriculas ORDER BY periodo_escolar DESC";
$periodos_stmt = $conn->prepare($periodos_query);
$periodos_stmt->execute();
$periodos = $periodos_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Matrículas - Sistema Escolar</title>
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
                <a class="nav-link" href="../logout.php">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-clipboard-list me-2"></i>Gestión de Matrículas</h4>
                        <a href="nuevo.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nueva Matrícula
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Filtros -->
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Estudiante, cédula o curso">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="">Todos</option>
                                    <option value="Activa" <?php echo $estado == 'Activa' ? 'selected' : ''; ?>>Activa</option>
                                    <option value="Inactiva" <?php echo $estado == 'Inactiva' ? 'selected' : ''; ?>>Inactiva</option>
                                    <option value="Cancelada" <?php echo $estado == 'Cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                    <option value="Finalizada" <?php echo $estado == 'Finalizada' ? 'selected' : ''; ?>>Finalizada</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Período</label>
                                <select class="form-select" name="periodo">
                                    <option value="">Todos</option>
                                    <?php foreach ($periodos as $per): ?>
                                    <option value="<?php echo $per['periodo_escolar']; ?>" 
                                            <?php echo $periodo == $per['periodo_escolar'] ? 'selected' : ''; ?>>
                                        <?php echo $per['periodo_escolar']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jornada</label>
                                <select class="form-select" name="jornada">
                                    <option value="">Todas</option>
                                    <option value="Mañana" <?php echo $jornada == 'Mañana' ? 'selected' : ''; ?>>Mañana</option>
                                    <option value="Tarde" <?php echo $jornada == 'Tarde' ? 'selected' : ''; ?>>Tarde</option>
                                    <option value="Noche" <?php echo $jornada == 'Noche' ? 'selected' : ''; ?>>Noche</option>
                                    <option value="Completa" <?php echo $jornada == 'Completa' ? 'selected' : ''; ?>>Completa</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Filtrar
                                    </button>
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Tabla de matrículas -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Estudiante</th>
                                        <th>Curso</th>
                                        <th>Período</th>
                                        <th>Jornada</th>
                                        <th>F. Matrícula</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matriculas as $matricula): ?>
                                    <tr>
                                        <td><strong><?php echo $matricula['id_matricula']; ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($matricula['estudiante_nombre'] . ' ' . 
                                                     $matricula['estudiante_apellido1'] . ' ' . 
                                                     ($matricula['estudiante_apellido2'] ?? '')); ?>
                                            <br><small class="text-muted"><?php echo $matricula['estudiante_id']; ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($matricula['curso_nombre'] ?? 'Curso no encontrado'); ?>
                                            <br><small class="text-muted">
                                                <?php echo $matricula['nivel'] . ' - ' . $matricula['grado']; ?>
                                            </small>
                                        </td>
                                        <td><span class="badge bg-info"><?php echo $matricula['periodo_escolar']; ?></span></td>
                                        <td><?php echo $matricula['jornada']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($matricula['fecha_matricula'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $matricula['estado'] == 'Activa' ? 'success' : 
                                                    ($matricula['estado'] == 'Finalizada' ? 'primary' : 
                                                    ($matricula['estado'] == 'Cancelada' ? 'danger' : 'secondary')); 
                                            ?>">
                                                <?php echo $matricula['estado']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="ver.php?id=<?php echo $matricula['id_matricula']; ?>" 
                                                   class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?id=<?php echo $matricula['id_matricula']; ?>" 
                                                   class="btn btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $matricula['id_matricula']; ?>" 
                                                   class="btn btn-danger" title="Eliminar"
                                                   onclick="return confirm('¿Está seguro de eliminar esta matrícula?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($matriculas)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-clipboard-list fa-3x mb-3"></i><br>
                                            No se encontraron matrículas
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
