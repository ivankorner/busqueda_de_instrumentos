<?php
// Conexión a la base de datos
require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}

// Eliminar registro si se solicita
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // Obtener la ruta del archivo antes de eliminar el registro
    $stmt = $pdo->prepare("SELECT file_path FROM datos WHERE id = :id");
    $stmt->execute([':id' => $deleteId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file && !empty($file['file_path']) && file_exists($file['file_path'])) {
        unlink($file['file_path']); // Eliminar el archivo del servidor
    }

    // Eliminar el registro de la base de datos
    $stmt = $pdo->prepare("DELETE FROM datos WHERE id = :id");
    $stmt->execute([':id' => $deleteId]);

    header("Location: ver_datos.php"); // Redirigir para evitar reenvío del formulario
    exit;
}

// Obtener todos los registros
$stmt = $pdo->query("SELECT * FROM datos");
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Datos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Datos Cargados</h1>

        <?php if (count($datos) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Archivo</th>
                        <th>Año</th>
                        <th>Letra</th>
                        <th>Archivo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($datos as $dato): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dato['id']); ?></td>
                            <td><?php echo htmlspecialchars($dato['name']); ?></td>
                            <td><?php echo htmlspecialchars($dato['year']); ?></td>
                            <td><?php echo htmlspecialchars($dato['letter']); ?></td>
                            <td>
                                <?php if (!empty($dato['file_path'])): ?>
                                    <a href="<?php echo htmlspecialchars($dato['file_path']); ?>" target="_blank" class="btn btn-success btn-sm">Ver PDF</a>
                                <?php else: ?>
                                    <span class="text-muted">No disponible</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="ver_datos.php?delete_id=<?php echo $dato['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este registro?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-danger">No hay datos cargados.</p>
        <?php endif; ?>

        <a href="index.php" class="btn btn-primary mt-3">Volver al Inicio</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once 'vistas/Footer.php'; ?>
</body>
</html>
