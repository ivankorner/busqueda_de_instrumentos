<?php
session_start();

// Generar captcha alfanumérico de 6 caracteres en cada recarga
function generarCaptcha($length = 6) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $captcha = '';
    for ($i = 0; $i < $length; $i++) {
        $captcha .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $captcha;
}
$_SESSION['captcha'] = generarCaptcha();
?>
<?php if (isset($_SESSION['error'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: <?php echo json_encode($_SESSION['error']); ?>,
                confirmButtonColor: '#007bff'
            });
        });
    </script>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>
<body>
    <?php require_once 'vistas/Header.php'; ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 mt-3">
                <div class="card">
                    <div class="card-body">
                        <h1 class="text-center mb-4">Iniciar Sesión</h1>
                        <form action="process_login.php" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" name="username" id="username" class="form-control" placeholder="Ingrese su usuario" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Ingrese su contraseña" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Captcha</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="captcha-box mb-0"><?php echo $_SESSION['captcha']; ?></div>
                                    <input type="text" name="captcha_input" id="captcha_input" class="form-control" placeholder="Ingrese el texto mostrado" required pattern="[A-Za-z0-9]{6}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once 'vistas/Footer.php'; ?>
</body>
</html>
