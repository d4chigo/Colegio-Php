<?php
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireRole(['admin', 'secretaria']);

$database = new Database();
$conn = $database->getConnection();

// Procesar eliminación
if (isset($_GET['delete'])) {
    $cedula = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM personal WHERE cedula = :cedula");
    $stmt->bindParam(':cedula', $cedula);
    if ($stmt->execute()) {
        $success = "Personal eliminado correctamente";
    } else {
        $error = "Error al eliminar el personal";
    }
}

// Obtener personal con filtros
$search = $_GET['search'] ?? '';
$estado = $_GET['estado'] ?? '';
$cargo = $_GET['cargo'] ?? '';

$query = "SELECT * FROM personal WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (nombre LIKE :search OR apellido1 LIKE :search OR apellido2 LIKE :search OR cedula LIKE :search OR cargo LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($estado) {
    $query .= " AND estado = :estado";
    $params[':estado'] = $estado;
}

if ($cargo) {
    $query .= " AND cargo LIKE :cargo";
    $params[':cargo'] = "%$cargo%";
}

$query .= " ORDER BY apellido1, apellido2, nombre";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$personal = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Personal - Sistema Escolar</title>
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

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-users me-2"></i>Gestión de Personal</h4>
                        <a href="nuevo.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nuevo Personal
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
                                       placeholder="Nombre, apellido, cédula o cargo">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="">Todos</option>
                                    <option value="Activo" <?php echo $estado == 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                    <option value="Inactivo" <?php echo $estado == 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                    <option value="Licencia" <?php echo $estado == 'Licencia' ? 'selected' : ''; ?>>Licencia</option>
                                    <option value="Retirado" <?php echo $estado == 'Retirado' ? 'selected' : ''; ?>>Retirado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cargo</label>
                                <select class="form-select" name="cargo">
                                    <option value="">Todos</option>
                                    <option value="Profesor" <?php echo $cargo == 'Profesor' ? 'selected' : ''; ?>>Profesor</option>
                                    <option value="Coordinador" <?php echo $cargo == 'Coordinador' ? 'selected' : ''; ?>>Coordinador</option>
                                    <option value="Secretaria" <?php echo $cargo == 'Secretaria' ? 'selected' : ''; ?>>Secretaria</option>
                                    <option value="Contador" <?php echo $cargo == 'Contador' ? 'selected' : ''; ?>>Contador</option>
                                    <option value="Director" <?php echo $cargo == 'Director' ? 'selected' : ''; ?>>Director</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">
                                    <i class="fas fa-search me-2"></i>Filtrar
                                </button>
                            </div>
                        </form>

                        <!-- Tabla de personal -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Nombre Completo</th>
                                        <th>Cargo</th>
                                        <th>Departamento</th>
                                        <th>Teléfono</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($personal as $persona): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($persona['cedula']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($persona['nombre'] . ' ' . 
                                                     $persona['apellido1'] . ' ' . 
                                                     ($persona['apellido2'] ?? '')); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($persona['cargo']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['departamento'] ?? 'N/A'); ?></td>
                                        <td><?php echo $persona['telefono'] ?? 'N/A'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $persona['estado'] == 'Activo' ? 'success' : 
                                                    ($persona['estado'] == 'Licencia' ? 'warning' : 'secondary'); 
                                            ?>">
                                                <?php echo $persona['estado']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="ver.php?cedula=<?php echo $persona['cedula']; ?>" 
                                                   class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?cedula=<?php echo $persona['cedula']; ?>" 
                                                   class="btn btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $persona['cedula']; ?>" 
                                                   class="btn btn-danger" title="Eliminar"
                                                   onclick="return confirm('¿Está seguro de eliminar este personal?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($personal)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-3x mb-3"></i><br>
                                            No se encontró personal
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
