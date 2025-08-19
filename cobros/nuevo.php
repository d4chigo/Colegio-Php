<?php
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireRole(['admin', 'contador', 'secretaria']);

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';

// Obtener estudiantes activos para el select
$estudiantes_query = "SELECT cedula, nombre, apellido1, apellido2 FROM estudiantes WHERE estado = 'Activo' ORDER BY apellido1, apellido2, nombre";
$estudiantes_stmt = $conn->prepare($estudiantes_query);
$estudiantes_stmt->execute();
$estudiantes = $estudiantes_stmt->fetchAll();

// Obtener cursos activos para el select
$cursos_query = "SELECT codigo, nombre FROM cursos WHERE estado = 'Activo' ORDER BY nombre";
$cursos_stmt = $conn->prepare($cursos_query);
$cursos_stmt->execute();
$cursos = $cursos_stmt->fetchAll();

if ($_POST) {
    $estudiante_id = $_POST['estudiante_id'];
    $curso_codigo = $_POST['curso_codigo'] ?? null;
    $concepto = $_POST['concepto'];
    $descripcion = $_POST['descripcion'] ?? null;
    $monto = $_POST['monto'];
    $fecha_cobro = $_POST['fecha_cobro'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $observaciones = $_POST['observaciones'] ?? null;

    try {
        $query = "INSERT INTO cobros (estudiante_id, curso_codigo, concepto, descripcion, monto, fecha_cobro, fecha_vencimiento, observaciones) 
                  VALUES (:estudiante_id, :curso_codigo, :concepto, :descripcion, :monto, :fecha_cobro, :fecha_vencimiento, :observaciones)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':estudiante_id', $estudiante_id);
        $stmt->bindParam(':curso_codigo', $curso_codigo);
        $stmt->bindParam(':concepto', $concepto);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':fecha_cobro', $fecha_cobro);
        $stmt->bindParam(':fecha_vencimiento', $fecha_vencimiento);
        $stmt->bindParam(':observaciones', $observaciones);
        
        if ($stmt->execute()) {
            $success = "Cobro registrado correctamente";
        }
    } catch (PDOException $e) {
        $error = "Error al registrar el cobro: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Cobro - Sistema Escolar</title>
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
                <a class="nav-link" href="index.php">Cobros</a>
                <a class="nav-link" href="../logout.php">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-plus me-2"></i>Nuevo Cobro</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check me-2"></i><?php echo $success; ?>
                                <a href="index.php" class="btn btn-sm btn-outline-success ms-3">Ver Cobros</a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <!-- Información del Cobro -->
                            <h5 class="mb-3"><i class="fas fa-dollar-sign me-2"></i>Información del Cobro</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Estudiante *</label>
                                    <select class="form-select" name="estudiante_id" required>
                                        <option value="">Seleccionar estudiante</option>
                                        <?php foreach ($estudiantes as $estudiante): ?>
                                        <option value="<?php echo $estudiante['cedula']; ?>" 
                                                <?php echo ($_POST['estudiante_id'] ?? '') == $estudiante['cedula'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . 
                                                     $estudiante['apellido1'] . ' ' . 
                                                     ($estudiante['apellido2'] ?? '') . 
                                                     ' - ' . $estudiante['cedula']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Curso (Opcional)</label>
                                    <select class="form-select" name="curso_codigo">
                                        <option value="">Sin curso específico</option>
                                        <?php foreach ($cursos as $curso): ?>
                                        <option value="<?php echo $curso['codigo']; ?>"
                                                <?php echo ($_POST['curso_codigo'] ?? '') == $curso['codigo'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($curso['codigo'] . ' - ' . $curso['nombre']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Concepto *</label>
                                    <input type="text" class="form-control" name="concepto" required
                                           value="<?php echo $_POST['concepto'] ?? ''; ?>"
                                           placeholder="Ej: Mensualidad, Matrícula, Material didáctico">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Monto *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" name="monto" required min="0" step="0.01"
                                               value="<?php echo $_POST['monto'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" name="descripcion" rows="2" 
                                              placeholder="Descripción adicional del cobro"><?php echo $_POST['descripcion'] ?? ''; ?></textarea>
                                </div>
                            </div>

                            <!-- Fechas -->
                            <h5 class="mb-3"><i class="fas fa-calendar me-2"></i>Fechas</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Cobro *</label>
                                    <input type="date" class="form-control" name="fecha_cobro" required
                                           value="<?php echo $_POST['fecha_cobro'] ?? date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Vencimiento *</label>
                                    <input type="date" class="form-control" name="fecha_vencimiento" required
                                           value="<?php echo $_POST['fecha_vencimiento'] ?? date('Y-m-d', strtotime('+30 days')); ?>">
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="mb-4">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" rows="3" 
                                          placeholder="Observaciones adicionales"><?php echo $_POST['observaciones'] ?? ''; ?></textarea>
                            </div>

                            <!-- Conceptos predefinidos -->
                            <div class="mb-4">
                                <label class="form-label">Conceptos Frecuentes</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setConcepto('Mensualidad', 150000)">
                                        Mensualidad ($150,000)
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setConcepto('Matrícula', 200000)">
                                        Matrícula ($200,000)
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setConcepto('Material Didáctico', 50000)">
                                        Material Didáctico ($50,000)
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setConcepto('Uniforme', 80000)">
                                        Uniforme ($80,000)
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setConcepto('Transporte', 100000)">
                                        Transporte ($100,000)
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Cobro
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setConcepto(concepto, monto) {
            document.querySelector('input[name="concepto"]').value = concepto;
            document.querySelector('input[name="monto"]').value = monto;
        }
    </script>
</body>
</html>
