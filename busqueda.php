<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Instrumentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
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
        <?php if (!$isLoggedIn): ?>

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

            <div class="container-fluid mb-5 d-flex flex-column flex-grow-1">
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
                                                <input type="text" name="name" id="name" class="form-control search-secondary" placeholder="Número de instrumento" inputmode="numeric" pattern="[0-9]*" maxlength="20">
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
                                                <label class="form-label search-secondary d-block">Año</label>
                                                <?php
                                                $currentYear = (int) date('Y');
                                                $startYear = 1973;
                                                ?>
                                                <div class="dropdown w-100">
                                                    <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button" id="yearDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                        Seleccione año
                                                    </button>
                                                    <div class="dropdown-menu w-100 p-3" aria-labelledby="yearDropdown">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="radio" name="year_mode" id="year_mode_single" value="single" checked>
                                                            <label class="form-check-label" for="year_mode_single">Un año</label>
                                                        </div>
                                                        <div id="yearSingleContainer">
                                                            <input type="text" name="year" id="year" class="form-control search-secondary yearpicker" placeholder="Seleccione un año" autocomplete="off">
                                                        </div>

                                                        <div class="form-check mt-3 mb-2">
                                                            <input class="form-check-input" type="radio" name="year_mode" id="year_mode_range" value="range">
                                                            <label class="form-check-label" for="year_mode_range">Varios años</label>
                                                        </div>
                                                        <div id="yearRangeContainer" class="input-daterange row g-2 d-none">
                                                            <div class="col-6">
                                                                <label for="year_from" class="form-label small mb-1">Desde</label>
                                                                <input type="text" name="year_from" id="year_from" class="form-control search-secondary yearpicker-range" placeholder="Desde" autocomplete="off">
                                                            </div>
                                                            <div class="col-6">
                                                                <label for="year_to" class="form-label small mb-1">Hasta</label>
                                                                <input type="text" name="year_to" id="year_to" class="form-control search-secondary yearpicker-range" placeholder="Hasta" autocomplete="off">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row align-items-end">
                                        <div class="col-md-6 mb-3">
                                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <button class="btn btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearch" aria-expanded="false" aria-controls="advancedSearch" title="Más opciones de búsqueda">
                                                + Filtros
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <a href="index.php" class="btn btn-secondary mt-3">Volver</a>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
    <?php require_once 'vistas/Footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/locales/bootstrap-datepicker.es.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var instrumentoChecks = document.querySelectorAll('.instrumento-check');
            var instrumentoDropdown = document.getElementById('instrumentoDropdown');
            var numeroInput = document.getElementById('name');
            var yearInput = document.getElementById('year');
            var yearFrom = document.getElementById('year_from');
            var yearTo = document.getElementById('year_to');
            var yearModeSingle = document.getElementById('year_mode_single');
            var yearModeRange = document.getElementById('year_mode_range');
            var yearSingleContainer = document.getElementById('yearSingleContainer');
            var yearRangeContainer = document.getElementById('yearRangeContainer');
            var yearDropdown = document.getElementById('yearDropdown');

            function actualizarTextoInstrumentos() {
                if (!instrumentoDropdown) return;
                var seleccionados = Array.from(instrumentoChecks)
                    .filter(function(check) { return check.checked; })
                    .map(function(check) { return check.nextElementSibling.textContent.trim(); });
                instrumentoDropdown.textContent = seleccionados.length
                    ? seleccionados.join(', ')
                    : 'Seleccione instrumentos';
            }

            function actualizarModoAnio() {
                if (!yearModeSingle || !yearModeRange || !yearSingleContainer || !yearRangeContainer) {
                    return;
                }

                if (yearModeSingle.checked) {
                    yearSingleContainer.classList.remove('d-none');
                    yearRangeContainer.classList.add('d-none');
                    if (yearFrom) yearFrom.value = '';
                    if (yearTo) yearTo.value = '';
                } else {
                    yearSingleContainer.classList.add('d-none');
                    yearRangeContainer.classList.remove('d-none');
                    if (yearInput) yearInput.value = '';
                }

                actualizarTextoAnio();
            }

            function actualizarTextoAnio() {
                if (!yearDropdown) return;

                var yearValue = yearInput ? yearInput.value.trim() : '';
                var yearFromValue = yearFrom ? yearFrom.value.trim() : '';
                var yearToValue = yearTo ? yearTo.value.trim() : '';

                if (yearModeSingle && yearModeSingle.checked && yearValue) {
                    yearDropdown.textContent = yearValue;
                    return;
                }

                if (yearModeRange && yearModeRange.checked && yearFromValue && yearToValue) {
                    yearDropdown.textContent = yearFromValue + ' - ' + yearToValue;
                    return;
                }

                if (yearModeRange && yearModeRange.checked && (yearFromValue || yearToValue)) {
                    yearDropdown.textContent = (yearFromValue || 'Desde') + ' - ' + (yearToValue || 'Hasta');
                    return;
                }

                yearDropdown.textContent = 'Seleccione año';
            }

            if (window.jQuery && yearInput) {
                var minDate = new Date(<?php echo $startYear; ?>, 0, 1);
                var maxDate = new Date(<?php echo $currentYear; ?>, 11, 31);

                window.jQuery(yearInput).datepicker({
                    format: 'yyyy',
                    language: 'es',
                    autoclose: true,
                    minViewMode: 2,
                    maxViewMode: 2,
                    startView: 2,
                    startDate: minDate,
                    endDate: maxDate
                }).on('changeDate', actualizarTextoAnio);

                window.jQuery('.input-daterange').datepicker({
                    format: 'yyyy',
                    language: 'es',
                    autoclose: true,
                    minViewMode: 2,
                    maxViewMode: 2,
                    startView: 2,
                    startDate: minDate,
                    endDate: maxDate
                }).on('changeDate', function() {
                    actualizarTextoAnio();
                });
            }

            if (numeroInput) {
                numeroInput.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '');
                });
            }

            if (yearModeSingle) {
                yearModeSingle.addEventListener('change', actualizarModoAnio);
            }

            if (yearModeRange) {
                yearModeRange.addEventListener('change', actualizarModoAnio);
            }

            if (yearInput) {
                yearInput.addEventListener('change', actualizarTextoAnio);
            }

            if (yearFrom) {
                yearFrom.addEventListener('change', actualizarTextoAnio);
            }

            if (yearTo) {
                yearTo.addEventListener('change', actualizarTextoAnio);
            }

            instrumentoChecks.forEach(function(check) {
                check.addEventListener('change', actualizarTextoInstrumentos);
            });

            if (yearFrom && yearTo) {
                var validarRango = function() {
                    var minYear = <?php echo $startYear; ?>;
                    var maxYear = <?php echo $currentYear; ?>;

                    if (yearFrom.value && (parseInt(yearFrom.value, 10) < minYear || parseInt(yearFrom.value, 10) > maxYear)) {
                        yearFrom.value = '';
                    }

                    if (yearTo.value && (parseInt(yearTo.value, 10) < minYear || parseInt(yearTo.value, 10) > maxYear)) {
                        yearTo.value = '';
                    }

                    if (yearFrom.value && yearTo.value && parseInt(yearFrom.value, 10) > parseInt(yearTo.value, 10)) {
                        yearTo.value = '';
                    }

                    actualizarTextoAnio();
                };

                yearFrom.addEventListener('change', validarRango);
                yearTo.addEventListener('change', validarRango);
            }

            actualizarTextoInstrumentos();
            actualizarModoAnio();
            actualizarTextoAnio();
        });
    </script>
</body>

</html>
