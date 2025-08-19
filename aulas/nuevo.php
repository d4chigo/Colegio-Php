<?php
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';

if ($_POST) {
    $numero = $_POST['numero'];
    $nombre = $_POST['nombre'] ?? null;
    $edificio = $_POST['edificio'] ?? null;
    $piso = $_POST['piso'] ?? null;
    $capacidad_estudiantes = $_POST['capacidad_estudiantes'];
    $tipo_aula = $_POST['tipo_aula'] ?? 'Aula Regular';
    $equipamiento = $_POST['equipamiento'] ?? null;
    $observaciones = $_POST['observaciones'] ?? null;

    try {
        $query = "INSERT INTO aulas (numero, nombre, edificio, piso, capacidad_estudiantes, tipo_aula, equipamiento, observaciones) 
                  VALUES (:numero, :nombre, :edificio, :piso, :capacidad_estudiantes, :tipo_aula, :equipamiento, :observaciones)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':numero', $numero);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':edificio', $edificio);
        $stmt->bindParam(':piso', $piso);
        $stmt->bindParam(':capacidad_estudiantes', $capacidad_estudiantes);
        $stmt->bindParam(':tipo_aula', $tipo_aula);
        $stmt->bindParam(':equipamiento', $equipamiento);
        $stmt->bindParam(':observaciones', $observaciones);
        
        if ($stmt->execute()) {
            $success = "Aula registrada correctamente";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Ya existe un aula con ese número";
        } else {
            $error = "Error al registrar el aula: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Aula - Sistema Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-school me-2"></i>Sistema Escolar
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../dashboard.php">Dashboard</a>
                <a class="nav-link" href="index.php">Aulas</a>
                <a class="nav-link" href="../logout.php">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-plus me-2"></i>Nueva Aula</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check me-2"></i><?php echo $success; ?>
                                <a href="index.php" class="btn btn-sm btn-outline-success ms-3">Ver Aulas</a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <!-- Información Básica -->
                            <h5 class="mb-3"><i class="fas fa-door-open me-2"></i>Información Básica</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Número de Aula *</label>
                                    <input type="text" class="form-control" name="numero" required 
                                           value="<?php echo $_POST['numero'] ?? ''; ?>"
                                           placeholder="Ej: 101, A-201">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Nombre del Aula</label>
                                    <input type="text" class="form-control" name="nombre"
                                           value="<?php echo $_POST['nombre'] ?? ''; ?>"
                                           placeholder="Ej: Aula de Matemáticas, Laboratorio de Química">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipo de Aula *</label>
                                    <select class="form-select" name="tipo_aula" required>
                                        <option value="Aula Regular" <?php echo ($_POST['tipo_aula'] ?? 'Aula Regular') == 'Aula Regular' ? 'selected' : ''; ?>>Aula Regular</option>
                                        <option value="Laboratorio" <?php echo ($_POST['tipo_aula'] ?? '') == 'Laboratorio' ? 'selected' : ''; ?>>Laboratorio</option>
                                        <option value="Taller" <?php echo ($_POST['tipo_aula'] ?? '') == 'Taller' ? 'selected' : ''; ?>>Taller</option>
                                        <option value="Auditorio" <?php echo ($_POST['tipo_aula'] ?? '') == 'Auditorio' ? 'selected' : ''; ?>>Auditorio</option>
                                        <option value="Biblioteca" <?php echo ($_POST['tipo_aula'] ?? '') == 'Biblioteca' ? 'selected' : ''; ?>>Biblioteca</option>
                                        <option value="Gimnasio" <?php echo ($_POST['tipo_aula'] ?? '') == 'Gimnasio' ? 'selected' : ''; ?>>Gimnasio</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Capacidad de Estudiantes *</label>
                                    <input type="number" class="form-control" name="capacidad_estudiantes" required min="1" max="100"
                                           value="<?php echo $_POST['capacidad_estudiantes'] ?? '30'; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Estado Inicial</label>
                                    <select class="form-select" name="estado" disabled>
                                        <option value="Disponible" selected>Disponible</option>
                                    </select>
                                    <small class="form-text text-muted">Las aulas nuevas se crean como disponibles</small>
                                </div>
                            </div>

                            <!-- Ubicación -->
                            <h5 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i>Ubicación</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Edificio</label>
                                    <select class="form-select" name="edificio">
                                        <option value="">Seleccionar edificio</option>
                                        <option value="Edificio A" <?php echo ($_POST['edificio'] ?? '') == 'Edificio A' ? 'selected' : ''; ?>>Edificio A</option>
                                        <option value="Edificio B" <?php echo ($_POST['edificio'] ?? '') == 'Edificio B' ? 'selected' : ''; ?>>Edificio B</option>
                                        <option value="Edificio C" <?php echo ($_POST['edificio'] ?? '') == 'Edificio C' ? 'selected' : ''; ?>>Edificio C</option>
                                        <option value="Edificio Principal" <?php echo ($_POST['edificio'] ?? '') == 'Edificio Principal' ? 'selected' : ''; ?>>Edificio Principal</option>
                                        <option value="Edificio Administrativo" <?php echo ($_POST['edificio'] ?? '') == 'Edificio Administrativo' ? 'selected' : ''; ?>>Edificio Administrativo</option>
                                        <option value="Pabellón Deportivo" <?php echo ($_POST['edificio'] ?? '') == 'Pabellón Deportivo' ? 'selected' : ''; ?>>Pabellón Deportivo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Piso</label>
                                    <select class="form-select" name="piso">
                                        <option value="">Seleccionar piso</option>
                                        <option value="1" <?php echo ($_POST['piso'] ?? '') == '1' ? 'selected' : ''; ?>>Piso 1</option>
                                        <option value="2" <?php echo ($_POST['piso'] ?? '') == '2' ? 'selected' : ''; ?>>Piso 2</option>
                                        <option value="3" <?php echo ($_POST['piso'] ?? '') == '3' ? 'selected' : ''; ?>>Piso 3</option>
                                        <option value="4" <?php echo ($_POST['piso'] ?? '') == '4' ? 'selected' : ''; ?>>Piso 4</option>
                                        <option value="0" <?php echo ($_POST['piso'] ?? '') == '0' ? 'selected' : ''; ?>>Planta Baja</option>
                                        <option value="-1" <?php echo ($_POST['piso'] ?? '') == '-1' ? 'selected' : ''; ?>>Sótano</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Equipamiento -->
                            <h5 class="mb-3"><i class="fas fa-tools me-2"></i>Equipamiento y Recursos</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label">Equipamiento Disponible</label>
                                    <textarea class="form-control" name="equipamiento" rows="4"
                                              placeholder="Describe el equipamiento disponible en el aula (proyector, computadoras, pizarra digital, etc.)"><?php echo $_POST['equipamiento'] ?? ''; ?></textarea>
                                </div>
                            </div>

                            <!-- Equipamiento Predefinido -->
                            <div class="mb-4">
                                <label class="form-label">Equipamiento Común</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="pizarra" onclick="addEquipment('Pizarra')">
                                            <label class="form-check-label" for="pizarra">Pizarra</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="proyector" onclick="addEquipment('Proyector')">
                                            <label class="form-check-label" for="proyector">Proyector</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="computador" onclick="addEquipment('Computador')">
                                            <label class="form-check-label" for="computador">Computador</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="aire" onclick="addEquipment('Aire Acondicionado')">
                                            <label class="form-check-label" for="aire">Aire Acondicionado</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="sonido" onclick="addEquipment('Sistema de Sonido')">
                                            <label class="form-check-label" for="sonido">Sistema de Sonido</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="internet" onclick="addEquipment('Internet WiFi')">
                                            <label class="form-check-label" for="internet">Internet WiFi</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="television" onclick="addEquipment('Televisión')">
                                            <label class="form-check-label" for="television">Televisión</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="ventilador" onclick="addEquipment('Ventiladores')">
                                            <label class="form-check-label" for="ventilador">Ventiladores</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="mb-4">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" rows="3"
                                          placeholder="Observaciones adicionales sobre el aula"><?php echo $_POST['observaciones'] ?? ''; ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Aula
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
        function addEquipment(equipment) {
            const textarea = document.querySelector('textarea[name="equipamiento"]');
            const currentValue = textarea.value;
            
            if (currentValue.includes(equipment)) {
                // Remove equipment
                textarea.value = currentValue.replace(equipment + ', ', '').replace(equipment, '').replace(', , ', ', ').trim();
                if (textarea.value.endsWith(', ')) {
                    textarea.value = textarea.value.slice(0, -2);
                }
            } else {
                // Add equipment
                if (currentValue.trim() === '') {
                    textarea.value = equipment;
                } else {
                    textarea.value = currentValue + ', ' + equipment;
                }
            }
        }
    </script>
</body>
</html>
