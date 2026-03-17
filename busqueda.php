<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Instrumentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <span class="navbar-brand d-flex align-items-center">
                <img src="./inclusiones/logocde-b.png" alt="Logo CDE" width="150" height="100" class="me-2">

            </span>
            <?php
            session_start();
            $isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
            ?>
            <?php if ($isLoggedIn): ?>
                <span class="text-dark ms-auto">Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mt-5">
        <?php if ($isLoggedIn): ?>






            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h1 class="mb-4">Cargar Datos</h1>
                    <a href="carga_datos.php" class="btn btn-warning mt-3">Carga de datos</a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="mb-4">Búsqueda de Datos</h1>
                    <a href="busqueda.php" class="btn btn-secondary mt-3">Búsqueda</a>
                </div>
            </div>
        <?php else: ?>

            <div class="container-fluid mt-4 mb-5 d-flex flex-column flex-grow-1" style="min-height: 80vh;">

            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h1 class="mb-4 fs-4 text-center">Búsqueda de Instrumentos</h1>
                            <form action="resultadosAdmin.php" method="get">
                                <div class="mb-4">
                                    <input type="text" name="global_search" id="global_search" class="form-control search-main" placeholder="Ingrese cualquier texto o número">
                                </div>
                                <div class="collapse" id="advancedSearch">
                                    <div class="row mt-3">
                                        <div class="col-12 col-md-4 mb-3">
                                            <label for="name" class="form-label search-secondary">Número</label>
                                            <input type="text" name="name" id="name" class="form-control search-secondary" placeholder="Número del instrumento">
                                        </div>
                                        <div class="col-12 col-md-4 mb-3">
                                            <label class="form-label search-secondary d-block">Instrumento</label>
                                            <div class="dropdown w-100">
                                                <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button" id="instrumentoDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                    Seleccione instrumentos
                                                </button>
                                                <div class="dropdown-menu w-100 p-3" aria-labelledby="instrumentoDropdown">
                                                    <div class="form-check">
                                                        <input class="form-check-input instrumento-check" type="checkbox" name="instrumento[]" value="Ordenanza" id="instrumento_ordenanza" <?php if (isset($_GET['instrumento']) && in_array('Ordenanza', (array)$_GET['instrumento'])) echo 'checked'; ?>>
                                                        <label class="form-check-label" for="instrumento_ordenanza">Ordenanza</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input instrumento-check" type="checkbox" name="instrumento[]" value="Resolucion" id="instrumento_resolucion" <?php if (isset($_GET['instrumento']) && in_array('Resolucion', (array)$_GET['instrumento'])) echo 'checked'; ?>>
                                                        <label class="form-check-label" for="instrumento_resolucion">Resolución</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input instrumento-check" type="checkbox" name="instrumento[]" value="Declaracion" id="instrumento_declaracion" <?php if (isset($_GET['instrumento']) && in_array('Declaracion', (array)$_GET['instrumento'])) echo 'checked'; ?>>
                                                        <label class="form-check-label" for="instrumento_declaracion">Declaración</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input instrumento-check" type="checkbox" name="instrumento[]" value="Comunicacion" id="instrumento_comunicacion" <?php if (isset($_GET['instrumento']) && in_array('Comunicacion', (array)$_GET['instrumento'])) echo 'checked'; ?>>
                                                        <label class="form-check-label" for="instrumento_comunicacion">Comunicación</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 mb-3">
                                            <label for="year" class="form-label search-secondary">Año</label>
                                            <select name="year" id="year" class="form-select search-secondary">
                                                <option value="">Seleccione un año</option>
                                                <?php
                                                $currentYear = (int) date('Y');
                                                for ($year = $currentYear; $year >= 1973; $year--) {
                                                    echo "<option value=\"$year\">$year</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-end">
                                    <div class="col-md-6 mb-3">
                                        <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <button class="btn btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearch" aria-expanded="false" aria-controls="advancedSearch" title="Más opciones de búsqueda">
                                            + filtros
                                        </button>
                                    </div>
                                </div>
                            </form>
                             <a href="index.php" class="btn btn-secondary mt-3">Volver</a>
                        </div>
                    </div>
                </div>
            </div>

    </div>
</div>

        <?php endif; ?>


        

    <?php require_once 'vistas/Footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var instrumentoChecks = document.querySelectorAll('.instrumento-check');
            var instrumentoDropdown = document.getElementById('instrumentoDropdown');

            function actualizarTextoInstrumentos() {
                if (!instrumentoDropdown) return;
                var seleccionados = Array.from(instrumentoChecks)
                    .filter(function(check) { return check.checked; })
                    .map(function(check) { return check.nextElementSibling.textContent.trim(); });

                instrumentoDropdown.textContent = seleccionados.length
                    ? seleccionados.join(', ')
                    : 'Seleccione instrumentos';
            }

            instrumentoChecks.forEach(function(check) {
                check.addEventListener('change', actualizarTextoInstrumentos);
            });

            actualizarTextoInstrumentos();
        });
    </script>
</body>

</html>
