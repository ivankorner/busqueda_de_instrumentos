<?php
session_start();
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Generar captcha de 4 caracteres (letras y números) solo si no existe
function generarCaptcha($length = 4) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $captcha = '';
    for ($i = 0; $i < $length; $i++) {
        $captcha .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $captcha;
}
// Solo generar un nuevo captcha si no existe uno o si se solicita explícitamente
if (!isset($_SESSION['captcha_busqueda']) || isset($_GET['new_captcha'])) {
    $_SESSION['captcha_busqueda'] = generarCaptcha();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Instrumentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            font-family: 'Arial', sans-serif;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }

        .search-main {
            font-size: 1.2rem;
            padding: 0.75rem;
            border-radius: 10px;
            border: 1px solid #ced4da;
        }

        .search-secondary {
            font-size: 1rem;
            border-radius: 5px;
        }

        .form-label {
            font-weight: bold;
        }

        footer {
            background-color: #343a40;
            color: #ffffff;
            padding: 1rem 0;
            text-align: center;
        }

        @media (max-width: 576px) {
            .search-main {
                font-size: 1rem;
                padding: 0.5rem;
            }

            .search-secondary {
                font-size: 0.9rem;
            }

            .navbar-brand img {
                width: 50px;
                height: 35px;
            }
        }
    </style>
</head>

<body>

    <?php require_once 'vistas/Header.php'; ?>



    <nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
        <div class="container">
            <span class="navbar-brand d-flex align-items-center">
                <img src="./inclusiones/logocde.png" alt="Logo CDE" width="75" height="50" class="me-2">
                
            </span>
            <?php if ($isLoggedIn): ?>
                <span class="text-dark ms-auto">Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container-fluid mt-4 mb-5 d-flex flex-column flex-grow-1" style="min-height: 80vh;">
        <?php if ($isLoggedIn): ?>











            <div class="row g-3 flex-grow-1">
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h1 class="mb-4 fs-4">Cargar Datos</h1>
                            <a href="carga_datos.php" class="btn btn-warning mt-auto">Carga de datos</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h1 class="mb-4 fs-4">Gestión de Usuarios</h1>
                            <a href="procesar_crear_usuario.php" class="btn btn-primary mt-auto">Gestión de Usuarios</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h1 class="mb-4 fs-4">Búsqueda de Datos</h1>
                            <a href="busqueda.php" class="btn btn-secondary mt-auto">Búsqueda</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h1 class="mb-4 fs-4">Búsqueda de Instrumentos</h1>
                            <form action="resultados.php" method="get" id="form-busqueda">
                                <div class="mb-4">
                                    <label for="global_search" class="form-label">Búsqueda General:</label>
                                    <input type="text" name="global_search" id="global_search" class="form-control search-main" placeholder="Ingrese cualquier texto o número">
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-4 mb-3">
                                        <label for="name" class="form-label search-secondary">Número</label>
                                        <input type="text" name="name" id="name" class="form-control search-secondary" placeholder="Número del instrumento">
                                    </div>
                                    <div class="col-12 col-md-4 mb-3">
                                        <label for="instrumento" class="form-label search-secondary">Instrumento:</label>
                                        <select name="instrumento" id="instrumento" class="form-select search-secondary">
                                            <option value="">Seleccione un instrumento</option>
                                            <option value="Ordenanza">Ordenanza</option>
                                            <option value="Resolucion">Resolución</option>
                                            <option value="Declaracion">Declaración</option>
                                            <option value="Comunicacion">Comunicación</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4 mb-3">
                                        <label for="year" class="form-label search-secondary">Año:</label>
                                        <select name="year" id="year" class="form-select search-secondary">
                                            <option value="">Seleccione un año</option>
                                            <?php
                                            $currentYear = (int) date('Y');
                                            $startYear = 1973;
                                            for ($year = $currentYear; $year >= $startYear; $year--) {
                                                echo "<option value=\"$year\">$year</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Captcha:</label>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="captcha-box" style="font-family: 'Courier New', Courier, monospace; font-size: 1.5rem; letter-spacing: 4px; background: #e9ecef; padding: 8px 16px; border-radius: 6px; display: inline-block; user-select: none;">
                                            <?php echo $_SESSION['captcha_busqueda']; ?>
                                        </div>
                                        <a href="?new_captcha=1" class="btn btn-outline-secondary btn-sm" title="Generar nuevo captcha">
                                            <i class="fas fa-sync-alt"></i>
                                        </a>
                                    </div>
                                    <input type="text" name="captcha_input" id="captcha_input" class="form-control mt-2" placeholder="Ingrese el texto mostrado" required pattern="[A-Za-z0-9]{4}">
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mt-3">Buscar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    </div>

    <?php require_once 'vistas/Footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('form-busqueda');
    if (form) {
        form.addEventListener('submit', function(e) {
            var globalSearch = document.getElementById('global_search').value.trim();
            var name = document.getElementById('name').value.trim();
            var instrumento = document.getElementById('instrumento').value;
            var year = document.getElementById('year').value;
            var captchaInput = document.getElementById('captcha_input').value.trim();
            var captchaCorrecto = "<?php echo $_SESSION['captcha_busqueda']; ?>";
            if (!globalSearch && !name && !instrumento && !year) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Por favor, complete al menos uno de los campos para realizar la búsqueda.',
                    confirmButtonColor: '#007bff'
                });
                return;
            }
            if (captchaInput.length !== 4 || captchaInput !== captchaCorrecto) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Captcha incorrecto',
                    text: 'Debe ingresar correctamente los 4 caracteres del captcha para continuar.',
                    confirmButtonColor: '#007bff'
                });
            }
        });
    }
});
</script>
</body>

</html>