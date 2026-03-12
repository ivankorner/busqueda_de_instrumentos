<?php
session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit();
}

// Conexión
require_once 'config/database.php';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    $_SESSION['error'] = 'Error de conexión con la base de datos.';
    header('Location: index.php');
    exit();
}

// Utilidad para mensajes
function flash_success($msg){ $_SESSION['success'] = $msg; }
function flash_error($msg){ $_SESSION['error'] = $msg; }

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

// Crear usuario (compat res: si llega POST sin action desde crear_usuario.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'create' || ($action === 'list' && isset($_POST['username'], $_POST['password'])))) {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if ($u === '' || $p === '') {
        flash_error('Usuario y contraseña son obligatorios.');
        header('Location: ?action=create');
        exit();
    }
    $hash = password_hash($p, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO usuarios (username, password_hash) VALUES (?, ?)');
    if (!$stmt) {
        flash_error('Error preparando la consulta.');
        header('Location: ?action=create');
        exit();
    }
    $stmt->bind_param('ss', $u, $hash);
    if ($stmt->execute()) {
        flash_success('Usuario creado correctamente.');
        header('Location: ?action=list');
        exit();
    } else {
        if ($conn->errno === 1062) {
            flash_error('El usuario ya existe.');
        } else {
            flash_error('Error al crear el usuario.');
        }
        header('Location: ?action=create');
        exit();
    }
}

// Actualizar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? ''); // opcional
    if ($id <= 0 || $u === '') {
        flash_error('Datos inválidos.');
        header('Location: ?action=list');
        exit();
    }
    if ($p !== '') {
        $hash = password_hash($p, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE usuarios SET username = ?, password_hash = ? WHERE id = ?');
        if (!$stmt) { flash_error('Error preparando la consulta.'); header('Location: ?action=list'); exit(); }
        $stmt->bind_param('ssi', $u, $hash, $id);
    } else {
        $stmt = $conn->prepare('UPDATE usuarios SET username = ? WHERE id = ?');
        if (!$stmt) { flash_error('Error preparando la consulta.'); header('Location: ?action=list'); exit(); }
        $stmt->bind_param('si', $u, $id);
    }
    if ($stmt->execute()) {
        flash_success('Usuario actualizado correctamente.');
    } else {
        if ($conn->errno === 1062) {
            flash_error('El nombre de usuario ya existe.');
        } else {
            flash_error('Error al actualizar el usuario.');
        }
    }
    header('Location: ?action=list');
    exit();
}

// Eliminar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { flash_error('ID inválido.'); header('Location: ?action=list'); exit(); }
    // Evitar que un usuario se elimine a sí mismo (opcional)
    if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id) {
        flash_error('No puedes eliminar tu propio usuario.');
        header('Location: ?action=list');
        exit();
    }
    $stmt = $conn->prepare('DELETE FROM usuarios WHERE id = ?');
    if (!$stmt) { flash_error('Error preparando la consulta.'); header('Location: ?action=list'); exit(); }
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        flash_success('Usuario eliminado.');
    } else {
        flash_error('No se pudo eliminar el usuario.');
    }
    header('Location: ?action=list');
    exit();
}

// Obtener datos para vistas si aplica
$editingUser = null;
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare('SELECT id, username FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $editingUser = $res->fetch_assoc();
        if (!$editingUser) {
            flash_error('Usuario no encontrado.');
            header('Location: ?action=list');
            exit();
        }
    } else {
        flash_error('ID inválido.');
        header('Location: ?action=list');
        exit();
    }
}

// Listado
$users = [];
if ($action === 'list') {
    $rs = $conn->query('SELECT id, username FROM usuarios ORDER BY username ASC');
    if ($rs) { while ($row = $rs->fetch_assoc()) { $users[] = $row; } }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color:#f8f9fa; }
        .card { margin-top: 2rem; }
    </style>
    </head>
<body>
<div class="container">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4 mb-0">Gestión de Usuarios</h1>
                <div>
                    <a href="?action=list" class="btn btn-secondary btn-sm">Listado</a>
                    <a href="?action=create" class="btn btn-primary btn-sm">Crear usuario</a>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">Inicio</a>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function(){
                        Swal.fire({ icon:'success', title:'Éxito', text: <?php echo json_encode($_SESSION['success']); ?> });
                    });
                </script>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function(){
                        Swal.fire({ icon:'error', title:'Error', text: <?php echo json_encode($_SESSION['error']); ?> });
                    });
                </script>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Usuario</th>
                                <th style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="3" class="text-muted">No hay usuarios.</td></tr>
                            <?php else: foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo (int)$u['id']; ?></td>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo (int)$u['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                        <form method="POST" action="?action=delete" class="d-inline form-delete">
                                            <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($action === 'create'): ?>
                <form method="POST" action="?action=create" autocomplete="off" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Crear</button>
                        <a href="?action=list" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            <?php elseif ($action === 'edit' && $editingUser): ?>
                <form method="POST" action="?action=edit" autocomplete="off" class="row g-3">
                    <input type="hidden" name="id" value="<?php echo (int)$editingUser['id']; ?>">
                    <div class="col-md-6">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($editingUser['username']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nueva contraseña (opcional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Dejar vacío para mantener la actual">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="?action=list" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.form-delete').forEach(function(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            Swal.fire({
                title: '¿Eliminar usuario?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function(result){ if (result.isConfirmed) form.submit(); });
        });
    });
});
</script>

</body>
</html>