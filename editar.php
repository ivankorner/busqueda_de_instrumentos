<?php
session_start();

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica si está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Conexión a la base de datos
require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}

// Procesar el formulario si se envió por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $descripcion = $_POST['descripcion'];
    $instrumento = $_POST['instrumento'];
    $year = $_POST['year'];
    
    // Directorio de subida
    $uploadDir = __DIR__ . '/uploads/';
    $uploadPathRel = 'uploads/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    // Validación básica
    if (!$id || !$name || !$descripcion || !$instrumento || !$year) {
        $_SESSION['error'] = "Todos los campos son obligatorios.";
        header('Location: editar.php?id=' . urlencode($id));
        exit;
    }

    // Obtener archivo actual para posible reemplazo
    $stmtCur = $pdo->prepare("SELECT file_path FROM datos WHERE id = :id");
    $stmtCur->execute([':id' => $id]);
    $oldMainPath = $stmtCur->fetchColumn();

    // Validación y reemplazo de archivo principal (opcional)
    $newMainPath = null;
    if (!empty($_FILES['archivo']['name']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $origName = $_FILES['archivo']['name'];
        $tmpName = $_FILES['archivo']['tmp_name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $_SESSION['error'] = 'Solo se permiten archivos PDF para el archivo principal.';
            header('Location: editar.php?id=' . urlencode($id));
            exit;
        }
        $safeBase = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($origName));
        $destName = uniqid('', true) . '_' . $safeBase;
        $destAbs = $uploadDir . $destName;
        if (!move_uploaded_file($tmpName, $destAbs)) {
            $_SESSION['error'] = 'No se pudo subir el archivo principal.';
            header('Location: editar.php?id=' . urlencode($id));
            exit;
        }
        $newMainPath = $uploadPathRel . $destName;
    }

    if ($newMainPath) {
        // Actualizar incluyendo file_path
        $stmt = $pdo->prepare("UPDATE datos SET name = :name, descripcion = :descripcion, instrumento = :instrumento, year = :year, file_path = :file_path WHERE id = :id");
        $stmt->execute([
            ':name' => $name,
            ':descripcion' => $descripcion,
            ':instrumento' => $instrumento,
            ':year' => $year,
            ':file_path' => $newMainPath,
            ':id' => $id
        ]);
        // Eliminar archivo anterior si existía y estaba en uploads
        if ($oldMainPath && strpos($oldMainPath, 'uploads/') === 0) {
            $oldAbs = __DIR__ . '/' . $oldMainPath;
            if (is_file($oldAbs)) { @unlink($oldAbs); }
        }
    } else {
        // Actualizar sin tocar file_path
        $stmt = $pdo->prepare("UPDATE datos SET name = :name, descripcion = :descripcion, instrumento = :instrumento, year = :year WHERE id = :id");
        $stmt->execute([
            ':name' => $name,
            ':descripcion' => $descripcion,
            ':instrumento' => $instrumento,
            ':year' => $year,
            ':id' => $id
        ]);
    }

    // Gestionar eliminación de anexos marcados
    if (!empty($_POST['eliminar_anexos']) && is_array($_POST['eliminar_anexos'])) {
        $stmtDel = $pdo->prepare("DELETE FROM anexos WHERE expediente_id = :id AND file_path = :fp");
        foreach ($_POST['eliminar_anexos'] as $fp) {
            $fp = (string)$fp;
            $stmtDel->execute([':id' => $id, ':fp' => $fp]);
            if (strpos($fp, 'uploads/') === 0) {
                $abs = __DIR__ . '/' . $fp;
                if (is_file($abs)) { @unlink($abs); }
            }
        }
    }

    // Manejar nuevos anexos subidos (opcional, múltiples)
    if (!empty($_FILES['anexos']) && is_array($_FILES['anexos']['name'])) {
        $stmtIns = $pdo->prepare("INSERT INTO anexos (expediente_id, file_path) VALUES (:id, :fp)");
        $names = $_FILES['anexos']['name'];
        $tmps = $_FILES['anexos']['tmp_name'];
        $errs = $_FILES['anexos']['error'];
        for ($i = 0; $i < count($names); $i++) {
            if (empty($names[$i]) || $errs[$i] !== UPLOAD_ERR_OK) { continue; }
            $orig = $names[$i];
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            if ($ext !== 'pdf') { continue; }
            $safeBase = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($orig));
            $destName = uniqid('', true) . '_' . $safeBase;
            $destAbs = $uploadDir . $destName;
            if (!move_uploaded_file($tmps[$i], $destAbs)) { continue; }
            $rel = $uploadPathRel . $destName;
            $stmtIns->execute([':id' => $id, ':fp' => $rel]);
        }
    }

    $_SESSION['success'] = "Registro actualizado correctamente.";
    header('Location: resultados.php');
    exit;
}

// Si llegamos por GET, mostramos el formulario con los datos actuales
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de registro no válido.";
    header('Location: resultados.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM datos WHERE id = :id");
$stmt->execute([':id' => $_GET['id']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $_SESSION['error'] = "Registro no encontrado.";
    header('Location: resultados.php');
    exit;
}

// Obtener anexos existentes del registro
$anexos = [];
try {
    $stmtAx = $pdo->prepare("SELECT file_path FROM anexos WHERE expediente_id = :id");
    $stmtAx->execute([':id' => $_GET['id']]);
    $anexos = $stmtAx->fetchAll(PDO::FETCH_COLUMN) ?: [];
} catch (Exception $e) {
    $anexos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1 class="mt-4">Editar Registro</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
        <div class="mb-3">
            <label class="form-label">Número de Instrumento</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" name="descripcion" class="form-control" value="<?php echo htmlspecialchars($row['descripcion']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Tipo de Instrumento</label>
            <select name="instrumento" class="form-select" required>
                <?php
                $instrumentos = ['Ordenanza','Resolucion','Declaracion','Comunicacion'];
                $instActual = (string)$row['instrumento'];
                echo '<option value="">Seleccione un instrumento</option>';
                foreach ($instrumentos as $inst) {
                    $sel = ($instActual === $inst) ? ' selected' : '';
                    echo '<option value="'.htmlspecialchars($inst).'"'.$sel.'>'.htmlspecialchars($inst).'</option>';
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Año</label>
            <select name="year" class="form-select" required>
                <option value="">Seleccione un año</option>
                <?php
                $currentYear = (int) date('Y');
                $startYear = 1973;
                $yearActual = (string)$row['year'];
                for ($y = $currentYear; $y >= $startYear; $y--) {
                    $sel = ($yearActual == (string)$y) ? ' selected' : '';
                    echo '<option value="'.$y.'"'.$sel.'>'.$y.'</option>';
                }
                ?>
            </select>
        </div>

        <hr>
        <div class="mb-3">
            <label class="form-label">Archivo principal (PDF)</label>
            <?php if (!empty($row['file_path'])): ?>
                <div class="mb-2">
                    <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">Ver actual</a>
                </div>
            <?php endif; ?>
            <input type="file" name="archivo" accept="application/pdf,.pdf" class="form-control">
            <small class="text-muted">Dejar vacío para mantener el archivo actual.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Anexos (PDF)</label>
            <?php if (!empty($anexos)): ?>
                <ul class="list-group mb-2">
                    <?php foreach ($anexos as $ax): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?php echo htmlspecialchars($ax); ?>" target="_blank">Ver anexo</a>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="eliminar_anexos[]" value="<?php echo htmlspecialchars($ax); ?>" id="del_<?php echo md5($ax); ?>">
                                <label class="form-check-label" for="del_<?php echo md5($ax); ?>">Eliminar</label>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">No hay anexos cargados.</p>
            <?php endif; ?>
            <input type="file" name="anexos[]" accept="application/pdf,.pdf" class="form-control" multiple>
            <small class="text-muted">Puedes seleccionar uno o varios archivos PDF para agregar como anexos.</small>
        </div>

        <button type="submit" class="btn btn-success">Guardar Cambios</button>
        <a href="busqueda.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
    <?php require_once 'vistas/Footer.php'; ?>
</body>
</html>