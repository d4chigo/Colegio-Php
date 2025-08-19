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

// Obtener matrículas del estudiante
$stmt = $conn->prepare("SELECT m.*, c.nombre as curso_nombre, c.nivel 
                        FROM matriculas m 
                        LEFT JOIN cursos c ON m.curso_id = c.id_curso 
                        WHERE m.estudiante_id = :cedula 
                        ORDER BY m.fecha_matricula DESC");
$stmt->bindParam(':cedula', $cedula);
$stmt->execute();
$matriculas = $stmt->fetchAll();

// Obtener cobros del estudiante
$stmt = $conn->prepare("SELECT * FROM cobros WHERE estudiante_id = :cedula ORDER BY fecha_cobro DESC LIMIT 5");
$stmt->bindParam(':cedula', $cedula);
$stmt->execute();
$cobros = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Estudiante - Sistema Escolar</title>
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
                        <h4><i class="fas fa-user-graduate me-2"></i>Perfil del Estudiante</h4>
                        <div>
                            <a href="editar.php?id=<?php echo $estudiante['cedula']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Editar
                            </a>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Información Personal -->
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="fas fa-user me-2"></i>Información Personal</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Cédula:</td>
                                        <td class="info-value"><?php echo $estudiante['cedula']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Nombre:</td>
                                        <td class="info-value"><?php echo htmlspecialchars($estudiante['nombre']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Primer Apellido:</td>
                                        <td class="info-value"><?php echo htmlspecialchars($estudiante['apellido1']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Segundo Apellido:</td>
                                        <td class="info-value"><?php echo htmlspecialchars($estudiante['apellido2'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Fecha de Nacimiento:</td>
                                        <td class="info-value"><?php echo date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Género:</td>
                                        <td class="info-value"><?php echo $estudiante['genero']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Estado:</td>
                                        <td class="info-value">
                                            <span class="badge bg-<?php echo $estudiante['estado'] == 'Activo' ? 'success' : 'danger'; ?> fs-6">
                                                <?php echo $estudiante['estado']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Información de Contacto -->
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="fas fa-address-book me-2"></i>Información de Contacto</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Dirección:</td>
                                        <td class="info-value"><?php echo htmlspecialchars($estudiante['direccion'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Teléfono:</td>
                                        <td class="info-value">
                                            <?php if ($estudiante['telefono']): ?>
                                                <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($estudiante['telefono']); ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Email:</td>
                                        <td class="info-value">
                                            <?php if ($estudiante['email']): ?>
                                                <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($estudiante['email']); ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>

                                <h5 class="mb-3 mt-4"><i class="fas fa-users me-2"></i>Información del Acudiente</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Nombre:</td>
                                        <td class="info-value"><?php echo htmlspecialchars($estudiante['acudiente_nombre'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Teléfono:</td>
                                        <td class="info-value">
                                            <?php if ($estudiante['acudiente_telefono']): ?>
                                                <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($estudiante['acudiente_telefono']); ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Email:</td>
                                        <td class="info-value">
                                            <?php if ($estudiante['acudiente_email']): ?>
                                                <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($estudiante['acudiente_email']); ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Matrículas -->
                        <?php if (!empty($matriculas)): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5><i class="fas fa-clipboard-list me-2"></i>Historial de Matrículas</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Curso</th>
                                                <th>Nivel</th>
                                                <th>Fecha Matrícula</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($matriculas as $matricula): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($matricula['curso_nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($matricula['nivel']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($matricula['fecha_matricula'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $matricula['estado'] == 'Activa' ? 'success' : 'secondary'; ?>">
                                                        <?php echo $matricula['estado']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Cobros Recientes -->
                        <?php if (!empty($cobros)): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5><i class="fas fa-dollar-sign me-2"></i>Cobros Recientes</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Concepto</th>
                                                <th>Monto</th>
                                                <th>Fecha Vencimiento</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cobros as $cobro): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cobro['concepto']); ?></td>
                                                <td>$<?php echo number_format($cobro['monto'], 0); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($cobro['fecha_vencimiento'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $cobro['estado'] == 'Pagado' ? 'success' : 
                                                            ($cobro['estado'] == 'Pendiente' ? 'warning' : 'danger'); 
                                                    ?>">
                                                        <?php echo $cobro['estado']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
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
