<?php
// Conexión a la base de datos
require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}

// Procesar el formulario
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $instrumento = isset($_POST['instrumento']) ? trim($_POST['instrumento']) : '';
    $year = isset($_POST['year']) ? trim($_POST['year']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $pdfFile = $_FILES['pdf_file'] ?? null;
    $anexos = $_FILES['anexos'] ?? null;

    if (!empty($name) && !empty($instrumento) && !empty($year) && !empty($descripcion) && $pdfFile) {
        // Información de depuración temporal
        error_log("DEBUG: Archivo recibido - Nombre: " . $pdfFile['name'] . ", Tipo: " . $pdfFile['type'] . ", Error: " . $pdfFile['error'] . ", Tamaño: " . $pdfFile['size']);
        
        // Verificar si el archivo se subió correctamente
        if ($pdfFile['error'] === UPLOAD_ERR_OK) {
            // Verificar si es un PDF por extensión y tipo MIME
            $fileExtension = strtolower(pathinfo($pdfFile['name'], PATHINFO_EXTENSION));
            $allowedTypes = ['application/pdf', 'application/octet-stream'];
            
            error_log("DEBUG: Extensión del archivo: " . $fileExtension);
            error_log("DEBUG: Tipo MIME del archivo: " . $pdfFile['type']);
            
            if ($fileExtension === 'pdf' && (in_array($pdfFile['type'], $allowedTypes) || $pdfFile['type'] === '')) {
                // Guardar el archivo principal
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Generar nombre único para evitar conflictos
                $fileName = uniqid() . '_' . basename($pdfFile['name']);
                $filePath = $uploadDir . $fileName;
                
                error_log("DEBUG: Intentando mover archivo a: " . $filePath);
                
                if (move_uploaded_file($pdfFile['tmp_name'], $filePath)) {
                    error_log("DEBUG: Archivo movido exitosamente");
                    // Insertar los datos principales en la base de datos
                    $sql = "INSERT INTO datos (name, instrumento, year, descripcion, file_path) 
                            VALUES (:name, :instrumento, :year, :descripcion, :file_path)";
                    $stmt = $pdo->prepare($sql);

                    try {
                        $stmt->execute([
                            ':name' => $name,
                            ':instrumento' => $instrumento,
                            ':year' => $year,
                            ':descripcion' => $descripcion,
                            ':file_path' => $filePath,
                        ]);
                        $expedienteId = $pdo->lastInsertId();
                        error_log("DEBUG: Datos insertados en BD, ID: " . $expedienteId);

                        // Procesar anexos si existen
                        $checkboxMarcado = isset($_POST['hasAnexos']);
                        if ($checkboxMarcado && $anexos && is_array($anexos['error'])) {
                            foreach ($anexos['error'] as $index => $error) {
                                if ($error === UPLOAD_ERR_OK) {
                                    $anexoExtension = strtolower(pathinfo($anexos['name'][$index], PATHINFO_EXTENSION));
                                    if ($anexoExtension === 'pdf') {
                                        $anexoFileName = uniqid() . '_' . basename($anexos['name'][$index]);
                                        $anexoPath = $uploadDir . $anexoFileName;
                                        if (move_uploaded_file($anexos['tmp_name'][$index], $anexoPath)) {
                                            $sqlAnexo = "INSERT INTO anexos (expediente_id, file_path) VALUES (:expediente_id, :file_path)";
                                            $stmtAnexo = $pdo->prepare($sqlAnexo);
                                            $stmtAnexo->execute([
                                                ':expediente_id' => $expedienteId,
                                                ':file_path' => $anexoPath,
                                            ]);
                                        }
                                    }
                                }
                            }
                        }

                        $message = 'Datos cargados exitosamente.';
                    } catch (PDOException $e) {
                        $message = 'Error al cargar los datos: ' . $e->getMessage();
                        error_log("DEBUG: Error en BD: " . $e->getMessage());
                        // Eliminar el archivo si falla la inserción en BD
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                } else {
                    $message = 'Error al subir el archivo principal. Verifique los permisos del directorio uploads/.';
                    error_log("DEBUG: Error al mover archivo - tmp_name: " . $pdfFile['tmp_name'] . ", destino: " . $filePath);
                }
            } else {
                $message = 'Por favor, suba un archivo PDF válido. Extensión: ' . $fileExtension . ', Tipo: ' . $pdfFile['type'];
                error_log("DEBUG: Archivo rechazado - Extensión: " . $fileExtension . ", Tipo: " . $pdfFile['type']);
            }
        } else {
            // Manejar errores específicos de subida
            switch ($pdfFile['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $message = 'El archivo excede el tamaño máximo permitido por el servidor.';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $message = 'El archivo excede el tamaño máximo permitido por el formulario.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = 'El archivo se subió parcialmente.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = 'No se seleccionó ningún archivo.';
                    break;
                default:
                    $message = 'Error desconocido al subir el archivo.';
                    break;
            }
            error_log("DEBUG: Error de subida: " . $pdfFile['error']);
        }
    } else {
        $message = 'Por favor, complete todos los campos requeridos.';
        error_log("DEBUG: Campos faltantes - name: " . (!empty($name) ? 'OK' : 'FALTA') . 
                 ", instrumento: " . (!empty($instrumento) ? 'OK' : 'FALTA') . 
                 ", year: " . (!empty($year) ? 'OK' : 'FALTA') . 
                 ", descripcion: " . (!empty($descripcion) ? 'OK' : 'FALTA') . 
                 ", pdfFile: " . ($pdfFile ? 'OK' : 'FALTA'));
    }
}


// Variable para el estado de login (temporal)
$isLoggedIn = false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carga de Datos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>
<body>
    

     <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <span class="navbar-brand d-flex align-items-center">
                <img src="./inclusiones/logocde-b.png" alt="Logo CDE" width="120" height="80" class="me-2">    
            </span>
            <?php
            session_start();
            $isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
            ?>
            <?php if ($isLoggedIn): ?>
                <span class="text-dark ms-auto">Bienvenido, <?php echo htmlspecialchars(strtoupper($_SESSION['username'])); ?>!</span>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="container-fluid mb-5 d-flex flex-column flex-grow-1">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h1 class="mb-4 fs-4 text-center">Carga de Datos</h1>
                            <?php if (!empty($message)): ?>
                                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        Swal.fire({
                                            icon: <?php echo (strpos($message, 'exitosamente') !== false) ? "'success'" : "'error'"; ?>,
                                            title: <?php echo (strpos($message, 'exitosamente') !== false) ? "'Éxito'" : "'Error'"; ?>,
                                            text: <?php echo json_encode($message); ?>,
                                            confirmButtonColor: '#007bff'
                                        });
                                    });
                                </script>
                            <?php endif; ?>

                            <form action="" method="post" enctype="multipart/form-data" class="row g-3">

                                <div class="col-md-4">
                                    <label for="instrumento" class="form-label">Instrumento</label>
                                    <select name="instrumento" id="instrumento" class="form-select" required>
                                        <option value="">Seleccione un instrumento</option>
                                        <option value="Ordenanza">Ordenanza</option>
                                        <option value="Resolucion">Resolución</option>
                                        <option value="Declaracion">Declaración</option>
                                        <option value="Comunicacion">Comunicación</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="name" class="form-label">Número</label>
                                    <input type="text" name="name" id="name" class="form-control" placeholder="Ingrese el número del instrumento" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="year" class="form-label">Año</label>
                                    <input type="text" name="year" id="year" class="form-control" placeholder="Ingrese el año" required>
                                </div>
                                <div class="col-12">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea name="descripcion" id="descripcion" class="form-control" placeholder="Ingrese una descripción (máximo 140 caracteres)" maxlength="140" required></textarea>
                                </div>
  
                        <div class="col-12">
                            <div class="border rounded p-3 bg-dark-subtle">
                                <div class="col-12">
                                    <label for="pdf_file" class="form-label">Archivo PDF</label>
                                    <input type="file" name="pdf_file" id="pdf_file" class="form-control" accept="application/pdf" required>
                                </div>
                                <div class="col-12">
                                    <div class="form-check mt-3">
                                        <input type="checkbox" name="hasAnexos" id="hasAnexos" class="form-check-input" onclick="toggleAnexos()">
                                        <label for="hasAnexos" class="form-check-label">Incluir anexos</label>
                                    </div>
                                </div>
                                <div id="anexosSection" class="col-12" style="display: none;">
                                    <div class="mb-0">
                                        <label for="anexos" class="form-label mt-3">Cargar Anexos</label>
                                        <input type="file" name="anexos[]" id="anexos" class="form-control" accept="application/pdf" multiple>
                                        <small class="text-muted">Puedes seleccionar uno o varios archivos PDF para agregar como anexos.</small>
                                    </div>
                                </div>

                            </div>
                        </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">Cargar Datos</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <a href="index.php" class="btn btn-secondary mt-3">Volver</a>
                </div>
            </div>
        </div>
    </div>
<?php require_once 'vistas/Footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleAnexos() {
            const anexosSection = document.getElementById('anexosSection');
            const hasAnexosCheckbox = document.getElementById('hasAnexos');
            anexosSection.style.display = hasAnexosCheckbox.checked ? 'block' : 'none';
        }
    </script>
</body>
</html>
