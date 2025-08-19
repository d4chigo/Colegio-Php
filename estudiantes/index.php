<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();

// Procesar eliminación
if (isset($_POST['eliminar_estudiante']) && $auth->hasRole(['admin'])) {
    $cedula = $_POST['eliminar_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM estudiantes WHERE cedula = :cedula");
        $stmt->bindParam(':cedula', $cedula);
        
        if ($stmt->execute()) {
            $success = "Estudiante eliminado correctamente";
        }
    } catch (PDOException $e) {
        $error = "Error al eliminar el estudiante: " . $e->getMessage();
    }
}

// Obtener estudiantes con filtros
$search = $_GET['search'] ?? '';
$estado = $_GET['estado'] ?? '';

$query = "SELECT * FROM estudiantes WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (nombre LIKE :search OR apellido1 LIKE :search OR apellido2 LIKE :search OR cedula LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($estado) {
    $query .= " AND estado = :estado";
    $params[':estado'] = $estado;
}

$query .= " ORDER BY apellido1, apellido2, nombre";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$estudiantes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Estudiantes - Sistema Escolar</title>
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
                <a class="nav-link" href="../dashboard.php">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-user-graduate me-2"></i>Gestión de Estudiantes</h4>
                        <a href="nuevo.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nuevo Estudiante
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
                            <div class="col-md-6">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Nombre, apellido o cédula">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="">Todos</option>
                                    <option value="Activo" <?php echo $estado == 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                    <option value="Inactivo" <?php echo $estado == 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                    <option value="Graduado" <?php echo $estado == 'Graduado' ? 'selected' : ''; ?>>Graduado</option>
                                    <option value="Retirado" <?php echo $estado == 'Retirado' ? 'selected' : ''; ?>>Retirado</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">
                                    <i class="fas fa-search me-2"></i>Filtrar
                                </button>
                            </div>
                        </form>

                        <!-- Tabla de estudiantes -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Nombre Completo</th>
                                        <th>Fecha Nacimiento</th>
                                        <th>Género</th>
                                        <th>Teléfono</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($estudiantes as $estudiante): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($estudiante['cedula']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . 
                                                     $estudiante['apellido1'] . ' ' . 
                                                     ($estudiante['apellido2'] ?? '')); ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])); ?></td>
                                        <td><?php echo $estudiante['genero']; ?></td>
                                        <td><?php echo $estudiante['telefono'] ?? 'N/A'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $estudiante['estado'] == 'Activo' ? 'success' : 
                                                    ($estudiante['estado'] == 'Graduado' ? 'primary' : 'secondary'); 
                                            ?>">
                                                <?php echo $estudiante['estado']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="editar.php?id=<?php echo $estudiante['cedula']; ?>" 
                                                   class="btn btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($auth->hasRole(['admin'])): ?>
                                                <button type="button" class="btn btn-danger" title="Eliminar" 
                                                        onclick="confirmarEliminacion('<?php echo $estudiante['cedula']; ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($estudiantes)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-user-graduate fa-3x mb-3"></i><br>
                                            No se encontraron estudiantes
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

    <!-- Modal de confirmación para eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar este estudiante?</p>
                    <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" id="formEliminar" class="d-inline">
                        <input type="hidden" name="eliminar_id" id="eliminarId">
                        <button type="submit" name="eliminar_estudiante" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminacion(cedula) {
            document.getElementById('eliminarId').value = cedula;
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }
    </script>
</body>
</html>
