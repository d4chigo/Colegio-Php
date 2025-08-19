<?php
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();

// Procesar eliminación
if (isset($_GET['delete'])) {
    $id_aula = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM aulas WHERE id_aula = :id_aula");
    $stmt->bindParam(':id_aula', $id_aula);
    if ($stmt->execute()) {
        $success = "Aula eliminada correctamente";
    } else {
        $error = "Error al eliminar el aula";
    }
}

// Obtener aulas con filtros
$search = $_GET['search'] ?? '';
$estado = $_GET['estado'] ?? '';
$tipo = $_GET['tipo'] ?? '';

$query = "SELECT * FROM aulas WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (numero LIKE :search OR nombre LIKE :search OR edificio LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($estado) {
    $query .= " AND estado = :estado";
    $params[':estado'] = $estado;
}

if ($tipo) {
    $query .= " AND tipo_aula = :tipo";
    $params[':tipo'] = $tipo;
}

$query .= " ORDER BY edificio, piso, numero";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$aulas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Aulas - Sistema Escolar</title>
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
                <a class="nav-link" href="../logout.php">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-door-open me-2"></i>Gestión de Aulas</h4>
                        <a href="nuevo.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nueva Aula
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
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Número, nombre o edificio">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="">Todos</option>
                                    <option value="Disponible" <?php echo $estado == 'Disponible' ? 'selected' : ''; ?>>Disponible</option>
                                    <option value="Ocupada" <?php echo $estado == 'Ocupada' ? 'selected' : ''; ?>>Ocupada</option>
                                    <option value="Mantenimiento" <?php echo $estado == 'Mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                                    <option value="Fuera de Servicio" <?php echo $estado == 'Fuera de Servicio' ? 'selected' : ''; ?>>Fuera de Servicio</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" name="tipo">
                                    <option value="">Todos</option>
                                    <option value="Aula Regular" <?php echo $tipo == 'Aula Regular' ? 'selected' : ''; ?>>Aula Regular</option>
                                    <option value="Laboratorio" <?php echo $tipo == 'Laboratorio' ? 'selected' : ''; ?>>Laboratorio</option>
                                    <option value="Taller" <?php echo $tipo == 'Taller' ? 'selected' : ''; ?>>Taller</option>
                                    <option value="Auditorio" <?php echo $tipo == 'Auditorio' ? 'selected' : ''; ?>>Auditorio</option>
                                    <option value="Biblioteca" <?php echo $tipo == 'Biblioteca' ? 'selected' : ''; ?>>Biblioteca</option>
                                    <option value="Gimnasio" <?php echo $tipo == 'Gimnasio' ? 'selected' : ''; ?>>Gimnasio</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">
                                    <i class="fas fa-search me-2"></i>Filtrar
                                </button>
                            </div>
                        </form>

                        <!-- Tabla de aulas -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Número</th>
                                        <th>Nombre</th>
                                        <th>Ubicación</th>
                                        <th>Tipo</th>
                                        <th>Capacidad</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($aulas as $aula): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($aula['numero']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($aula['nombre'] ?? 'Sin nombre'); ?></td>
                                        <td>
                                            <?php 
                                            $ubicacion = [];
                                            if ($aula['edificio']) $ubicacion[] = $aula['edificio'];
                                            if ($aula['piso']) $ubicacion[] = "Piso " . $aula['piso'];
                                            echo implode(' - ', $ubicacion) ?: 'No especificada';
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $aula['tipo_aula'] == 'Aula Regular' ? 'primary' : 
                                                    ($aula['tipo_aula'] == 'Laboratorio' ? 'success' : 
                                                    ($aula['tipo_aula'] == 'Taller' ? 'warning' : 'info')); 
                                            ?>">
                                                <?php echo $aula['tipo_aula']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $aula['capacidad_estudiantes']; ?> estudiantes</td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $aula['estado'] == 'Disponible' ? 'success' : 
                                                    ($aula['estado'] == 'Ocupada' ? 'warning' : 
                                                    ($aula['estado'] == 'Mantenimiento' ? 'info' : 'danger')); 
                                            ?>">
                                                <?php echo $aula['estado']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="ver.php?id=<?php echo $aula['id_aula']; ?>" 
                                                   class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?id=<?php echo $aula['id_aula']; ?>" 
                                                   class="btn btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $aula['id_aula']; ?>" 
                                                   class="btn btn-danger" title="Eliminar"
                                                   onclick="return confirm('¿Está seguro de eliminar esta aula?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($aulas)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-door-open fa-3x mb-3"></i><br>
                                            No se encontraron aulas
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
