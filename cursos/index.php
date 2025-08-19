<?php
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();

// Procesar eliminación
if (isset($_GET['delete'])) {
    $codigo = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM cursos WHERE codigo = :codigo");
    $stmt->bindParam(':codigo', $codigo);
    if ($stmt->execute()) {
        $success = "Curso eliminado correctamente";
    } else {
        $error = "Error al eliminar el curso";
    }
}

// Obtener cursos con filtros
$search = $_GET['search'] ?? '';
$nivel = $_GET['nivel'] ?? '';
$estado = $_GET['estado'] ?? '';

$query = "SELECT c.*, p.nombre as profesor_nombre, p.apellido1 as profesor_apellido1, a.numero as aula_numero 
          FROM cursos c 
          LEFT JOIN personal p ON c.profesor_id = p.cedula 
          LEFT JOIN aulas a ON c.aula_id = a.id_aula 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (c.nombre LIKE :search OR c.codigo LIKE :search OR c.grado LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($nivel) {
    $query .= " AND c.nivel = :nivel";
    $params[':nivel'] = $nivel;
}

if ($estado) {
    $query .= " AND c.estado = :estado";
    $params[':estado'] = $estado;
}

$query .= " ORDER BY c.nivel, c.grado, c.nombre";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$cursos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos - Sistema Escolar</title>
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
                        <h4><i class="fas fa-book me-2"></i>Gestión de Cursos</h4>
                        <a href="nuevo.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nuevo Curso
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
                                       placeholder="Nombre, código o grado">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nivel</label>
                                <select class="form-select" name="nivel">
                                    <option value="">Todos</option>
                                    <option value="Preescolar" <?php echo $nivel == 'Preescolar' ? 'selected' : ''; ?>>Preescolar</option>
                                    <option value="Primaria" <?php echo $nivel == 'Primaria' ? 'selected' : ''; ?>>Primaria</option>
                                    <option value="Secundaria" <?php echo $nivel == 'Secundaria' ? 'selected' : ''; ?>>Secundaria</option>
                                    <option value="Media" <?php echo $nivel == 'Media' ? 'selected' : ''; ?>>Media</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="">Todos</option>
                                    <option value="Activo" <?php echo $estado == 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                    <option value="Inactivo" <?php echo $estado == 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                    <option value="Finalizado" <?php echo $estado == 'Finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">
                                    <i class="fas fa-search me-2"></i>Filtrar
                                </button>
                            </div>
                        </form>

                        <!-- Tabla de cursos -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Nivel/Grado</th>
                                        <th>Profesor</th>
                                        <th>Aula</th>
                                        <th>Cupo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cursos as $curso): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($curso['codigo']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($curso['nombre']); ?></td>
                                        <td>
                                            <?php echo $curso['nivel'] . ' - ' . $curso['grado']; ?>
                                            <?php if ($curso['seccion']): ?>
                                                <span class="badge bg-secondary"><?php echo $curso['seccion']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($curso['profesor_nombre']): ?>
                                                <?php echo htmlspecialchars($curso['profesor_nombre'] . ' ' . $curso['profesor_apellido1']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin asignar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($curso['aula_numero']): ?>
                                                <span class="badge bg-info"><?php echo $curso['aula_numero']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Sin asignar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $curso['cupo_maximo']; ?> estudiantes</td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $curso['estado'] == 'Activo' ? 'success' : 
                                                    ($curso['estado'] == 'Finalizado' ? 'primary' : 'secondary'); 
                                            ?>">
                                                <?php echo $curso['estado']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="ver.php?codigo=<?php echo $curso['codigo']; ?>" 
                                                   class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar.php?codigo=<?php echo $curso['codigo']; ?>" 
                                                   class="btn btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $curso['codigo']; ?>" 
                                                   class="btn btn-danger" title="Eliminar"
                                                   onclick="return confirm('¿Está seguro de eliminar este curso?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($cursos)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-book fa-3x mb-3"></i><br>
                                            No se encontraron cursos
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
