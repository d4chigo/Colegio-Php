<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireRole(['admin', 'contador', 'secretaria']);

$database = new Database();
$conn = $database->getConnection();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Obtener datos del cobro
$stmt = $conn->prepare("SELECT c.*, e.nombre, e.apellido1, e.apellido2, e.telefono, e.email 
                        FROM cobros c 
                        LEFT JOIN estudiantes e ON c.estudiante_id = e.cedula 
                        WHERE c.id_cobro = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$cobro = $stmt->fetch();

if (!$cobro) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Cobro - Sistema Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .info-label { font-weight: bold; color: #495057; }
        .info-value { color: #212529; }
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
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-eye me-2"></i>Detalles del Cobro #<?php echo $cobro['id_cobro']; ?></h4>
                        <div>
                            <a href="editar.php?id=<?php echo $cobro['id_cobro']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Editar
                            </a>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Información del Cobro -->
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="fas fa-dollar-sign me-2"></i>Información del Cobro</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">ID Cobro:</td>
                                        <td class="info-value"><?php echo $cobro['id_cobro']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Concepto:</td>
                                        <td class="info-value"><?php echo htmlspecialchars($cobro['concepto']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Monto:</td>
                                        <td class="info-value"><strong>$<?php echo number_format($cobro['monto'], 0); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Estado:</td>
                                        <td class="info-value">
                                            <span class="badge bg-<?php 
                                                echo $cobro['estado'] == 'Pagado' ? 'success' : 
                                                    ($cobro['estado'] == 'Pendiente' ? 'warning' : 
                                                    ($cobro['estado'] == 'Vencido' ? 'danger' : 'secondary')); 
                                            ?> fs-6">
                                                <?php echo $cobro['estado']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Fecha de Cobro:</td>
                                        <td class="info-value"><?php echo date('d/m/Y', strtotime($cobro['fecha_cobro'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Fecha de Vencimiento:</td>
                                        <td class="info-value">
                                            <?php 
                                            $fecha_venc = date('d/m/Y', strtotime($cobro['fecha_vencimiento']));
                                            $vencido = strtotime($cobro['fecha_vencimiento']) < time() && $cobro['estado'] == 'Pendiente';
                                            echo $vencido ? "<span class='text-danger'>$fecha_venc (Vencido)</span>" : $fecha_venc;
                                            ?>
                                        </td>
                                    </tr>
                                    <?php if ($cobro['fecha_pago']): ?>
                                    <tr>
                                        <td class="info-label">Fecha de Pago:</td>
                                        <td class="info-value text-success">
                                            <i class="fas fa-check me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($cobro['fecha_pago'])); ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>

                            <!-- Información del Estudiante -->
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="fas fa-user-graduate me-2"></i>Información del Estudiante</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Cédula:</td>
                                        <td class="info-value"><?php echo $cobro['estudiante_id']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Nombre Completo:</td>
                                        <td class="info-value">
                                            <?php echo htmlspecialchars($cobro['nombre'] . ' ' . 
                                                     $cobro['apellido1'] . ' ' . 
                                                     ($cobro['apellido2'] ?? '')); ?>
                                        </td>
                                    </tr>
                                    <?php if ($cobro['telefono']): ?>
                                    <tr>
                                        <td class="info-label">Teléfono:</td>
                                        <td class="info-value">
                                            <i class="fas fa-phone me-1"></i>
                                            <?php echo htmlspecialchars($cobro['telefono']); ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($cobro['email']): ?>
                                    <tr>
                                        <td class="info-label">Email:</td>
                                        <td class="info-value">
                                            <i class="fas fa-envelope me-1"></i>
                                            <?php echo htmlspecialchars($cobro['email']); ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </table>

                                <!-- Acciones adicionales -->
                                <?php if ($cobro['estado'] == 'Pendiente'): ?>
                                <div class="mt-4">
                                    <h6>Acciones Disponibles:</h6>
                                    <form method="POST" action="index.php" class="d-inline">
                                        <input type="hidden" name="id_cobro" value="<?php echo $cobro['id_cobro']; ?>">
                                        <input type="hidden" name="nuevo_estado" value="Pagado">
                                        <button type="submit" name="cambiar_estado" class="btn btn-success">
                                            <i class="fas fa-check me-2"></i>Marcar como Pagado
                                        </button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Descripción adicional -->
                        <?php if ($cobro['descripcion']): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5><i class="fas fa-file-text me-2"></i>Descripción</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <?php echo nl2br(htmlspecialchars($cobro['descripcion'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
