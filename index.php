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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>

<body>

    <?php require_once 'vistas/Header.php'; ?>

    <main class="flex-grow-1">
        <div class="container-fluid mt-4 mb-4">
        <?php if ($isLoggedIn): ?>

            <div class="row g-3 flex-grow-1">
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex flex-column" style="padding: 4px;">
                            <a href="carga_datos.php" class="btn btn-warning mt-auto" style="font-size: 20px;font-weight: bold;padding: 40px 40px;">CARGA DE DATOS</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex flex-column" style="padding: 4px;">
                            <a href="procesar_crear_usuario.php" class="btn btn-primary mt-auto" style="font-size: 20px;font-weight: bold;padding: 40px 40px;">GESTIÓN DE USUARIOS</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex flex-column" style="padding: 4px;">
                            <a href="busqueda.php" class="btn btn-secondary mt-auto" style="font-size: 20px;font-weight: bold;padding: 40px 40px;">BÚSQUEDA DE DATOS</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h1 class="mb-4 fs-4 text-center">Búsqueda de Instrumentos
                            <?php
                                $tituloTexto = "💡 ¿Cómo buscar? <span class='btn-close float-end' id='close-popover'></span>";
                                $titulo = htmlspecialchars($tituloTexto, ENT_QUOTES, 'UTF-8');
                                $ayudaTexto = "🔍 <b>Búsqueda:</b> Escribe una palabra o el número que buscas en el cuadro principal.<br>" .
                                              "Tambien puedes usar el botón &quot;+ Filtros&quot; para elegir el número, año o tipo de documento (como Ordenanza o Resolución).<br><br>" .
                                              "🤖 <b>Seguridad:</b> Antes de terminar, completa el Captcha (el cuadro de verificación de seguridad).<br><br>" .
                                              "✅ <b>Finalizar:</b> Haz clic en el botón &quot;Buscar&quot; para ver los resultados.";
                                    // Usamos ENT_QUOTES para asegurar que las comillas se escapen correctamente
                                $ayuda = htmlspecialchars($ayudaTexto, ENT_QUOTES, 'UTF-8');
                            ?>
                            <button 
                                type="button" 
                                class="btn btn-link p-0 ms-1 text-decoration-none" 
                                data-bs-toggle="popover" 
                                data-bs-html="true"
                                data-bs-title="<?php echo $titulo; ?>"
                                data-bs-content="<?php echo $ayuda; ?>"
                                style="line-height: 1;">
                                <i class="fa-regular fa-circle-question" style="font-size: 1rem;"></i>
                            </button>
                            </h1>
                            <form action="resultados.php" method="get" id="form-busqueda">
                                <div class="mb-4">
                                    <input type="text" name="global_search" id="global_search" class="form-control search-main" placeholder="Ingrese cualquier texto o número">
                                </div>
                                <div class="collapse-container">
                                    <div class="collapse" id="advancedSearch">
                                        <div class="row mt-3">
                                        <div class="col-12 col-md-4 mb-3">
                                            <label for="name" class="form-label search-secondary">Número</label>
                                            <input type="text" name="name" id="name" class="form-control search-secondary" placeholder="Número del instrumento" inputmode="numeric" pattern="[0-9]*" maxlength="20">
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
                                <div class="row align-items-center">
                                    <div class="col-md-3 mb-3">
                                        <div class="captcha-container border p-3 rounded text-center">
                                            <label class="form-label w-100">Complete el captcha</label>
                                            <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                                                <div class="captcha-box">
                                                    <?php echo $_SESSION['captcha_busqueda']; ?>
                                                </div>
                                                <a href="?new_captcha=1" class="btn btn-outline-secondary btn-sm" title="Generar nuevo captcha">
                                                    <i class="fas fa-sync-alt"></i>
                                                </a>
                                            </div>
                                            <input type="text" name="captcha_input" id="captcha_input" class="form-control" placeholder="Ingrese el texto" required pattern="[A-Za-z0-9]{4}" maxlength="4">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <button class="btn btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearch" aria-expanded="false" aria-controls="advancedSearch" title="Más opciones de búsqueda">
                                            + Filtros
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        </div>
    </main>

    <?php require_once 'vistas/Footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/locales/bootstrap-datepicker.es.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('form-busqueda');
    var numeroInput = document.getElementById('name');
    var yearInput = document.getElementById('year');
    var yearFrom = document.getElementById('year_from');
    var yearTo = document.getElementById('year_to');
    var yearModeSingle = document.getElementById('year_mode_single');
    var yearModeRange = document.getElementById('year_mode_range');
    var yearSingleContainer = document.getElementById('yearSingleContainer');
    var yearRangeContainer = document.getElementById('yearRangeContainer');
    var yearDropdown = document.getElementById('yearDropdown');

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

    actualizarModoAnio();

    if (form) {
        form.addEventListener('submit', function(e) {
            var globalSearch = document.getElementById('global_search').value.trim();
            var name = document.getElementById('name').value.trim();
            var instrumento = document.querySelectorAll('.instrumento-check:checked').length;
            var year = yearInput ? yearInput.value.trim() : '';
            var yearFromValue = yearFrom ? yearFrom.value.trim() : '';
            var yearToValue = yearTo ? yearTo.value.trim() : '';
            var tieneFiltroAnio = yearModeSingle && yearModeSingle.checked ? !!year : (!!yearFromValue || !!yearToValue);
            var captchaInput = document.getElementById('captcha_input').value.trim();
            var captchaCorrecto = "<?php echo $_SESSION['captcha_busqueda']; ?>";

            if (!globalSearch && !name && !instrumento && !tieneFiltroAnio) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Por favor, complete al menos uno de los campos para realizar la búsqueda.',
                    confirmButtonColor: '#007bff'
                });
                return;
            }

            var minYear = <?php echo $startYear; ?>;
            var maxYear = <?php echo $currentYear; ?>;

            if (yearModeSingle && yearModeSingle.checked && year && (parseInt(year, 10) < minYear || parseInt(year, 10) > maxYear)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Año inválido',
                    text: 'El año debe estar entre ' + minYear + ' y ' + maxYear + '.',
                    confirmButtonColor: '#007bff'
                });
                return;
            }

            if (yearModeRange && yearModeRange.checked) {
                if ((yearFromValue && (parseInt(yearFromValue, 10) < minYear || parseInt(yearFromValue, 10) > maxYear)) ||
                    (yearToValue && (parseInt(yearToValue, 10) < minYear || parseInt(yearToValue, 10) > maxYear))) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rango de años inválido',
                        text: 'Los años deben estar entre ' + minYear + ' y ' + maxYear + '.',
                        confirmButtonColor: '#007bff'
                    });
                    return;
                }

                if (yearFromValue && yearToValue && parseInt(yearFromValue, 10) > parseInt(yearToValue, 10)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rango de años inválido',
                        text: 'El año "desde" no puede ser mayor que el año "hasta".',
                        confirmButtonColor: '#007bff'
                    });
                    return;
                }
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
    actualizarTextoAnio();
});
</script>
<script>
  // Inicializar todos los popovers
  const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
  const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl, {
    trigger: 'focus' // Esto hace que se cierre al hacer clic fuera
  }))
  $(document).on("click", "#close-popover", function() {
    $("#miBuscadorAyuda").popover("hide");
});
</script>
</body>

</html>
