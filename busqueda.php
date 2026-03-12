<!DOCTYPE html>
<html lang="en">

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

    <nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
        <div class="container">
            <span class="navbar-brand d-flex align-items-center">
                <img src="./inclusiones/logocde.png" alt="Logo CDE" width="75" height="50" class="me-2">

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
                            <h1 class="mb-4 fs-4">Búsqueda de Instrumentos</h1>
                            <form action="resultadosAdmin.php" method="get">
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
                                            for ($year = $currentYear; $year >= 1973; $year--) {
                                                echo "<option value=\"$year\">$year</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mt-3">Buscar</button>
                            </form>
                             <a href="index.php" class="btn btn-secondary mt-3">Volver</a>
                        </div>
                    </div>
                </div>
            </div>

    </div>
</div>

        <?php endif; ?>


        

    <footer>
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
            <p class="mb-2 mb-md-0">Concejo Deliberante Eldorado &copy; 2025. Todos los derechos reservados.</p>
            <?php if (!$isLoggedIn): ?>
                
            <?php else: ?>
                <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
            <?php endif; ?>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>