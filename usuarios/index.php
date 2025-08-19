<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireRole(['admin']);

$database = new Database();
$conn = $database->getConnection();

// Procesar eliminación
if (isset($_POST['eliminar_usuario']) && $auth->hasRole(['admin'])) {
    $id_usuario = $_POST['eliminar_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
        $stmt->bindParam(':id', $id_usuario);
        
        if ($stmt->execute()) {
            $success = "Usuario eliminado correctamente";
        }
    } catch (PDOException $e) {
        $error = "Error al eliminar el usuario: " . $e->getMessage();
    }
}

// Obtener usuarios con filtros
$search = $_GET['search'] ?? '';
$rol = $_GET['rol'] ?? '';
$estado = $_GET['estado'] ?? '';

$query = "SELECT * FROM usuarios WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (username LIKE :search OR email LIKE :search OR nombre LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($rol) {
    $query .= " AND rol = :rol";
    $params[':rol'] = $rol;
}

if ($estado) {
    if ($estado == 'Activo') {
        $query .= " AND activo = 1";
    } else {
        $query .= " AND activo = 0";
    }
}

$query .= " ORDER BY id DESC";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$usuarios = $stmt->fetchAll();

// Estadísticas
$stats = [];
$stats['total'] = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$stats['activos'] = $conn->query("SELECT COUNT(*) FROM usuarios WHERE activo = 1")->fetchColumn();
$stats['admin'] = $conn->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'")->fetchColumn();
$stats['profesores'] = $conn->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'profesor'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
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

    <div class="container-fluid mt-4">
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h4><?php echo $stats['total']; ?></h4>
                        <p class="mb-0">Total Usuarios</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-user-check fa-2x mb-2"></i>
                        <h4><?php echo $stats['activos']; ?></h4>
                        <p class="mb-0">Activos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <i class="fas fa-user-shield fa-2x mb-2"></i>
                        <h4><?php echo $stats['admin']; ?></h4>
                        <p class="mb-0">Administradores</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                        <h4><?php echo $stats['profesores']; ?></h4>
                        <p class="mb-0">Profesores</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-users me-2"></i>Gestión de Usuarios</h4>
                        <a href="nuevo.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nuevo Usuario
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
                                       placeholder="Usuario, email o nombre">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Rol</label>
                                <select class="form-select" name="rol">
                                    <option value="">Todos los roles</option>
                                    <option value="admin" <?php echo $rol == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                    <option value="secretaria" <?php echo $rol == 'secretaria' ? 'selected' : ''; ?>>Secretaria</option>
                                    <option value="profesor" <?php echo $rol == 'profesor' ? 'selected' : ''; ?>>Profesor</option>
                                    <option value="contador" <?php echo $rol == 'contador' ? 'selected' : ''; ?>>Contador</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="">Todos los estados</option>
                                    <option value="Activo" <?php echo $estado == 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                    <option value="Inactivo" <?php echo $estado == 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-2">
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

                        <!-- Tabla de usuarios -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo $usuario['id'] ?? $usuario['id_usuario']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($usuario['username']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($usuario['nombre'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['email'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $usuario['rol'] == 'admin' ? 'danger' : 
                                                    ($usuario['rol'] == 'secretaria' ? 'primary' : 
                                                    ($usuario['rol'] == 'profesor' ? 'info' : 'warning')); 
                                            ?>">
                                                <?php echo ucfirst($usuario['rol']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $usuario['activo'] == 1 ? 'success' : 'secondary'; ?>">
                                                <?php echo $usuario['activo'] == 1 ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($usuario['created_at'] ?? $usuario['fecha_creacion'] ?? 'now')); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="editar.php?id=<?php echo $usuario['id'] ?? $usuario['id_usuario']; ?>" 
                                                   class="btn btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if (($usuario['id'] ?? $usuario['id_usuario']) != $_SESSION['user_id']): ?>
                                                <button type="button" class="btn btn-danger" title="Eliminar" 
                                                        onclick="confirmarEliminacion(<?php echo $usuario['id'] ?? $usuario['id_usuario']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-3x mb-3"></i><br>
                                            No se encontraron usuarios
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
                    <p>¿Está seguro de que desea eliminar este usuario?</p>
                    <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" id="formEliminar" class="d-inline">
                        <input type="hidden" name="eliminar_id" id="eliminarId">
                        <button type="submit" name="eliminar_usuario" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminacion(id) {
            document.getElementById('eliminarId').value = id;
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }
    </script>
</body>
</html>
