<?php
session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Crear Nuevo Usuario</h2>
        <?php
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($_GET['error']) . '</div>';
        }
        if (isset($_GET['success'])) {
            echo '<div class="alert alert-success">Usuario creado exitosamente.</div>';
        }
        ?>
        <form action="procesar_crear_usuario.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Usuario</label>
                <input type="text" class="form-control" name="username" id="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Crear</button>
        </form>
        <a href="index.php" class="btn btn-secondary mt-3">Volver</a>
    </div>
    <?php require_once 'vistas/Footer.php'; ?>
</body>
</html>