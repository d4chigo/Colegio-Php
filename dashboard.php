<?php
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

// Obtener estadísticas básicas
$database = new Database();
$conn = $database->getConnection();

$stats = [];
$stats['estudiantes'] = $conn->query("SELECT COUNT(*) FROM estudiantes WHERE estado = 'Activo'")->fetchColumn();
$stats['personal'] = $conn->query("SELECT COUNT(*) FROM personal WHERE estado = 'Activo'")->fetchColumn();
$stats['cursos'] = $conn->query("SELECT COUNT(*) FROM cursos WHERE estado = 'Activo'")->fetchColumn();
$stats['cobros_pendientes'] = $conn->query("SELECT COUNT(*) FROM cobros WHERE estado = 'Pendiente'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Gestión Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar {
            background: white;
            min-height: calc(100vh - 76px);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .nav-link {
            color: #495057;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
        }
        .nav-link:hover {
            background-color: #e9ecef;
            color: #667eea;
        }
        .nav-link.active {
            background-color: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-school me-2"></i>
                Sistema de Gestión Escolar
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?php echo $_SESSION['username']; ?>
                        <span class="badge bg-light text-dark ms-1"><?php echo ucfirst($_SESSION['rol']); ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i>Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="estudiantes/">
                                <i class="fas fa-user-graduate me-2"></i>Estudiantes
                            </a>
                        </li>
                        <?php if ($auth->hasRole(['admin', 'secretaria'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="personal/">
                                <i class="fas fa-users me-2"></i>Personal
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cursos/">
                                <i class="fas fa-book me-2"></i>Cursos
                            </a>
                        </li>
                        <?php if ($auth->hasRole(['admin', 'secretaria'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="aulas/">
                                <i class="fas fa-door-open me-2"></i>Aulas
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($auth->hasRole(['admin', 'secretaria'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="matriculas/">
                                <i class="fas fa-clipboard-list me-2"></i>Matrículas
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($auth->hasRole(['admin', 'contador', 'secretaria'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cobros/">
                                <i class="fas fa-dollar-sign me-2"></i>Cobros
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($auth->hasRole(['admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="usuarios/">
                                <i class="fas fa-user-cog me-2"></i>Usuarios
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Dashboard</h2>
                        <span class="text-muted">Bienvenido, <?php echo $_SESSION['username']; ?></span>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-<?php echo $auth->hasRole(['admin', 'contador', 'secretaria']) ? '3' : '4'; ?> mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-graduate fa-3x mb-3"></i>
                                    <h3><?php echo $stats['estudiantes']; ?></h3>
                                    <p class="mb-0">Estudiantes Activos</p>
                                </div>
                            </div>
                        </div>
                        <?php if ($auth->hasRole(['admin', 'secretaria'])): ?>
                        <div class="col-md-<?php echo $auth->hasRole(['admin', 'contador', 'secretaria']) ? '3' : '4'; ?> mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <h3><?php echo $stats['personal']; ?></h3>
                                    <p class="mb-0">Personal Activo</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-<?php echo $auth->hasRole(['admin', 'contador', 'secretaria']) ? '3' : '4'; ?> mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-book fa-3x mb-3"></i>
                                    <h3><?php echo $stats['cursos']; ?></h3>
                                    <p class="mb-0">Cursos Activos</p>
                                </div>
                            </div>
                        </div>
                        <?php if ($auth->hasRole(['admin', 'contador', 'secretaria'])): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                    <h3><?php echo $stats['cobros_pendientes']; ?></h3>
                                    <p class="mb-0">Cobros Pendientes</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-plus me-2"></i>Acciones Rápidas</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="estudiantes/nuevo.php" class="btn btn-outline-primary">
                                            <i class="fas fa-user-plus me-2"></i>Nuevo Estudiante
                                        </a>
                                        <?php if ($auth->hasRole(['admin', 'secretaria'])): ?>
                                        <a href="matriculas/nuevo.php" class="btn btn-outline-success">
                                            <i class="fas fa-clipboard-list me-2"></i>Nueva Matrícula
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($auth->hasRole(['admin', 'contador', 'secretaria'])): ?>
                                        <a href="cobros/nuevo.php" class="btn btn-outline-warning">
                                            <i class="fas fa-dollar-sign me-2"></i>Nuevo Cobro
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($auth->hasRole(['admin', 'secretaria'])): ?>
                                        <a href="personal/nuevo.php" class="btn btn-outline-info">
                                            <i class="fas fa-user-tie me-2"></i>Nuevo Personal
                                        </a>
                                        <?php endif; ?>
                                        <a href="cursos/nuevo.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-book me-2"></i>Nuevo Curso
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-bar me-2"></i>Resumen del Sistema</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Rol:</strong> <?php echo ucfirst($_SESSION['rol']); ?></p>
                                    <p><strong>Último acceso:</strong> <?php echo date('d/m/Y H:i'); ?></p>
                                    <p><strong>Estado del sistema:</strong> <span class="badge bg-success">Operativo</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
