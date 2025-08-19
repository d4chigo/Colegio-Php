<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireRole(['admin', 'contador', 'secretaria']);

$database = new Database();
$conn = $database->getConnection();

// Procesar eliminación de cobro
if (isset($_POST['eliminar_cobro']) && $auth->hasRole(['admin'])) {
    $id_eliminar = $_POST['eliminar_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM cobros WHERE id_cobro = :id");
        $stmt->bindParam(':id', $id_eliminar);
        
        if ($stmt->execute()) {
            $success = "Cobro eliminado correctamente";
        }
    } catch (PDOException $e) {
        $error = "Error al eliminar el cobro: " . $e->getMessage();
    }
}

// Procesar cambio de estado de cobro
if (isset($_POST['cambiar_estado'])) {
    $id_cobro = $_POST['id_cobro'];
    $nuevo_estado = $_POST['nuevo_estado'];
    $fecha_pago = $nuevo_estado == 'Pagado' ? date('Y-m-d') : null;
    
    $stmt = $conn->prepare("UPDATE cobros SET estado = :estado, fecha_pago = :fecha_pago WHERE id_cobro = :id");
    $stmt->bindParam(':estado', $nuevo_estado);
    $stmt->bindParam(':fecha_pago', $fecha_pago);
    $stmt->bindParam(':id', $id_cobro);
    
    if ($stmt->execute()) {
        $success = "Estado del cobro actualizado correctamente";
    }
}

// Filtros
$search = $_GET['search'] ?? '';
$estado = $_GET['estado'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

$query = "SELECT c.*, e.nombre, e.apellido1, e.apellido2 
          FROM cobros c 
          LEFT JOIN estudiantes e ON c.estudiante_id = e.cedula 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (e.nombre LIKE :search OR e.apellido1 LIKE :search OR c.concepto LIKE :search OR c.estudiante_id LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($estado) {
    $query .= " AND c.estado = :estado";
    $params[':estado'] = $estado;
}

if ($fecha_desde) {
    $query .= " AND c.fecha_cobro >= :fecha_desde";
    $params[':fecha_desde'] = $fecha_desde;
}

if ($fecha_hasta) {
    $query .= " AND c.fecha_cobro <= :fecha_hasta";
    $params[':fecha_hasta'] = $fecha_hasta;
}

$query .= " ORDER BY c.fecha_cobro DESC, c.fecha_vencimiento ASC";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$cobros = $stmt->fetchAll();

// Estadísticas
$stats = [];
$stats['pendientes'] = $conn->query("SELECT COUNT(*) FROM cobros WHERE estado = 'Pendiente'")->fetchColumn();
$stats['vencidos'] = $conn->query("SELECT COUNT(*) FROM cobros WHERE estado = 'Vencido'")->fetchColumn();
$stats['pagados'] = $conn->query("SELECT COUNT(*) FROM cobros WHERE estado = 'Pagado'")->fetchColumn();
$stats['total_pendiente'] = $conn->query("SELECT COALESCE(SUM(monto), 0) FROM cobros WHERE estado IN ('Pendiente', 'Vencido')")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cobros - Sistema Escolar</title>
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
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <h4><?php echo $stats['pendientes']; ?></h4>
                        <p class="mb-0">Pendientes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <h4><?php echo $stats['vencidos']; ?></h4>
                        <p class="mb-0">Vencidos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-check fa-2x mb-2"></i>
                        <h4><?php echo $stats['pagados']; ?></h4>
                        <p class="mb-0">Pagados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                        <h4>$<?php echo number_format($stats['total_pendiente'], 0); ?></h4>
                        <p class="mb-0">Total Pendiente</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-dollar-sign me-2"></i>Gestión de Cobros</h4>
                        <a href="nuevo.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nuevo Cobro
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Filtros -->
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Estudiante, concepto o cédula">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="">Todos</option>
                                    <option value="Pendiente" <?php echo $estado == 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="Pagado" <?php echo $estado == 'Pagado' ? 'selected' : ''; ?>>Pagado</option>
                                    <option value="Vencido" <?php echo $estado == 'Vencido' ? 'selected' : ''; ?>>Vencido</option>
                                    <option value="Cancelado" <?php echo $estado == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Desde</label>
                                <input type="date" class="form-control" name="fecha_desde" 
                                       value="<?php echo $fecha_desde; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Hasta</label>
                                <input type="date" class="form-control" name="fecha_hasta" 
                                       value="<?php echo $fecha_hasta; ?>">
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

                        <!-- Tabla de cobros -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Estudiante</th>
                                        <th>Concepto</th>
                                        <th>Monto</th>
                                        <th>F. Cobro</th>
                                        <th>F. Vencimiento</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cobros as $cobro): ?>
                                    <tr>
                                        <td><?php echo $cobro['id_cobro']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($cobro['nombre'] . ' ' . 
                                                     $cobro['apellido1'] . ' ' . 
                                                     ($cobro['apellido2'] ?? '')); ?>
                                            <br><small class="text-muted"><?php echo $cobro['estudiante_id']; ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($cobro['concepto']); ?></td>
                                        <td><strong>$<?php echo number_format($cobro['monto'], 0); ?></strong></td>
                                        <td><?php echo date('d/m/Y', strtotime($cobro['fecha_cobro'])); ?></td>
                                        <td>
                                            <?php 
                                            $fecha_venc = date('d/m/Y', strtotime($cobro['fecha_vencimiento']));
                                            $vencido = strtotime($cobro['fecha_vencimiento']) < time() && $cobro['estado'] == 'Pendiente';
                                            echo $vencido ? "<span class='text-danger'>$fecha_venc</span>" : $fecha_venc;
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $cobro['estado'] == 'Pagado' ? 'success' : 
                                                    ($cobro['estado'] == 'Pendiente' ? 'warning' : 
                                                    ($cobro['estado'] == 'Vencido' ? 'danger' : 'secondary')); 
                                            ?>">
                                                <?php echo $cobro['estado']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($cobro['estado'] == 'Pendiente'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id_cobro" value="<?php echo $cobro['id_cobro']; ?>">
                                                    <input type="hidden" name="nuevo_estado" value="Pagado">
                                                    <button type="submit" name="cambiar_estado" class="btn btn-success" title="Marcar como Pagado">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <a href="ver.php?id=<?php echo $cobro['id_cobro']; ?>" 
                                                   class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?id=<?php echo $cobro['id_cobro']; ?>" 
                                                   class="btn btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($auth->hasRole(['admin'])): ?>
                                                <button type="button" class="btn btn-danger" title="Eliminar" 
                                                        onclick="confirmarEliminacion(<?php echo $cobro['id_cobro']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($cobros)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-dollar-sign fa-3x mb-3"></i><br>
                                            No se encontraron cobros
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
                    <p>¿Está seguro de que desea eliminar este cobro?</p>
                    <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" id="formEliminar" class="d-inline">
                        <input type="hidden" name="eliminar_id" id="eliminarId">
                        <button type="submit" name="eliminar_cobro" class="btn btn-danger">Eliminar</button>
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
