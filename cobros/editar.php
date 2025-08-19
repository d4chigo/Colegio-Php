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
$stmt = $conn->prepare("SELECT * FROM cobros WHERE id_cobro = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$cobro = $stmt->fetch();

if (!$cobro) {
    header('Location: index.php');
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $estudiante_id = $_POST['estudiante_id'];
    $concepto = $_POST['concepto'];
    $monto = $_POST['monto'];
    $fecha_cobro = $_POST['fecha_cobro'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $estado = $_POST['estado'];
    $descripcion = $_POST['descripcion'];
    $fecha_pago = $estado == 'Pagado' ? ($_POST['fecha_pago'] ?: date('Y-m-d')) : null;

    try {
        $stmt = $conn->prepare("UPDATE cobros SET 
                                estudiante_id = :estudiante_id,
                                concepto = :concepto,
                                monto = :monto,
                                fecha_cobro = :fecha_cobro,
                                fecha_vencimiento = :fecha_vencimiento,
                                estado = :estado,
                                descripcion = :descripcion,
                                fecha_pago = :fecha_pago
                                WHERE id_cobro = :id");
        
        $stmt->bindParam(':estudiante_id', $estudiante_id);
        $stmt->bindParam(':concepto', $concepto);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':fecha_cobro', $fecha_cobro);
        $stmt->bindParam(':fecha_vencimiento', $fecha_vencimiento);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':fecha_pago', $fecha_pago);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            header('Location: ver.php?id=' . $id . '&success=1');
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error al actualizar el cobro: " . $e->getMessage();
    }
}

// Obtener estudiantes para el select
$estudiantes = $conn->query("SELECT cedula, nombre, apellido1, apellido2 FROM estudiantes WHERE estado = 'Activo' ORDER BY nombre")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cobro - Sistema Escolar</title>
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

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-edit me-2"></i>Editar Cobro #<?php echo $cobro['id_cobro']; ?></h4>
                        <a href="ver.php?id=<?php echo $cobro['id_cobro']; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="estudiante_id" class="form-label">Estudiante *</label>
                                    <select class="form-select" id="estudiante_id" name="estudiante_id" required>
                                        <option value="">Seleccionar estudiante</option>
                                        <?php foreach ($estudiantes as $estudiante): ?>
                                            <option value="<?php echo $estudiante['cedula']; ?>" 
                                                    <?php echo $estudiante['cedula'] == $cobro['estudiante_id'] ? 'selected' : ''; ?>>
                                                <?php echo $estudiante['cedula'] . ' - ' . 
                                                          $estudiante['nombre'] . ' ' . 
                                                          $estudiante['apellido1'] . ' ' . 
                                                          ($estudiante['apellido2'] ?? ''); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="concepto" class="form-label">Concepto *</label>
                                    <select class="form-select" id="concepto" name="concepto" required>
                                        <option value="">Seleccionar concepto</option>
                                        <option value="Matrícula" <?php echo $cobro['concepto'] == 'Matrícula' ? 'selected' : ''; ?>>Matrícula</option>
                                        <option value="Pensión" <?php echo $cobro['concepto'] == 'Pensión' ? 'selected' : ''; ?>>Pensión</option>
                                        <option value="Uniforme" <?php echo $cobro['concepto'] == 'Uniforme' ? 'selected' : ''; ?>>Uniforme</option>
                                        <option value="Materiales" <?php echo $cobro['concepto'] == 'Materiales' ? 'selected' : ''; ?>>Materiales</option>
                                        <option value="Transporte" <?php echo $cobro['concepto'] == 'Transporte' ? 'selected' : ''; ?>>Transporte</option>
                                        <option value="Alimentación" <?php echo $cobro['concepto'] == 'Alimentación' ? 'selected' : ''; ?>>Alimentación</option>
                                        <option value="Actividades" <?php echo $cobro['concepto'] == 'Actividades' ? 'selected' : ''; ?>>Actividades</option>
                                        <option value="Otros" <?php echo $cobro['concepto'] == 'Otros' ? 'selected' : ''; ?>>Otros</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="monto" class="form-label">Monto *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="monto" name="monto" 
                                               value="<?php echo $cobro['monto']; ?>" min="0" step="1000" required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="estado" class="form-label">Estado *</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="Pendiente" <?php echo $cobro['estado'] == 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="Pagado" <?php echo $cobro['estado'] == 'Pagado' ? 'selected' : ''; ?>>Pagado</option>
                                        <option value="Vencido" <?php echo $cobro['estado'] == 'Vencido' ? 'selected' : ''; ?>>Vencido</option>
                                        <option value="Cancelado" <?php echo $cobro['estado'] == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="fecha_cobro" class="form-label">Fecha de Cobro *</label>
                                    <input type="date" class="form-control" id="fecha_cobro" name="fecha_cobro" 
                                           value="<?php echo $cobro['fecha_cobro']; ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento *</label>
                                    <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" 
                                           value="<?php echo $cobro['fecha_vencimiento']; ?>" required>
                                </div>

                                <div class="col-md-6 mb-3" id="fecha_pago_group" style="display: <?php echo $cobro['estado'] == 'Pagado' ? 'block' : 'none'; ?>;">
                                    <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                                    <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" 
                                           value="<?php echo $cobro['fecha_pago']; ?>">
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                              placeholder="Información adicional sobre el cobro"><?php echo htmlspecialchars($cobro['descripcion']); ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="ver.php?id=<?php echo $cobro['id_cobro']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Cambios
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
        // Mostrar/ocultar fecha de pago según el estado
        document.getElementById('estado').addEventListener('change', function() {
            const fechaPagoGroup = document.getElementById('fecha_pago_group');
            const fechaPagoInput = document.getElementById('fecha_pago');
            
            if (this.value === 'Pagado') {
                fechaPagoGroup.style.display = 'block';
                if (!fechaPagoInput.value) {
                    fechaPagoInput.value = new Date().toISOString().split('T')[0];
                }
            } else {
                fechaPagoGroup.style.display = 'none';
                fechaPagoInput.value = '';
            }
        });
    </script>
</body>
</html>
