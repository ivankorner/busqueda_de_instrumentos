<?php
session_start();
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Conexión a la base de datos
require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}


// Construir la consulta de búsqueda
$sql = "SELECT * FROM datos WHERE 1=1";
$params = [];

// Búsqueda general
if (!empty($_GET['global_search'])) {
    $globalSearch = '%' . $_GET['global_search'] . '%';
    $sql .= " AND (name LIKE :global_search 
                OR descripcion LIKE :global_search 
                OR instrumento LIKE :global_search 
                OR year LIKE :global_search)";
    $params[':global_search'] = $globalSearch;
}

// Búsqueda específica por campos
if (!empty($_GET['name'])) {
    $sql .= " AND name LIKE :name";
    $params[':name'] = '%' . $_GET['name'] . '%';
}
if (!empty($_GET['descripcion'])) {
    $sql .= " AND descripcion = :descripcion";
    $params[':descripcion'] = $_GET['descripcion'];
}
if (!empty($_GET['instrumento'])) {
    $sql .= " AND instrumento = :instrumento";
    $params[':instrumento'] = $_GET['instrumento'];
}
if (!empty($_GET['year'])) {
    $sql .= " AND year = :year";
    $params[':year'] = $_GET['year'];
}

// Parámetros de paginación
$perPageOptions = [300, 400, 500, 600, 700];
$perPage = isset($_GET['per_page']) && in_array((int)$_GET['per_page'], $perPageOptions) ? (int)$_GET['per_page'] : 300;
$page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Contar total de resultados
$countSql = "SELECT COUNT(*) FROM datos WHERE 1=1";
$countParams = $params;
if (!empty($_GET['global_search'])) {
    $countSql .= " AND (name LIKE :global_search 
                OR descripcion LIKE :global_search 
                OR instrumento LIKE :global_search 
                OR year LIKE :global_search)";
}
if (!empty($_GET['name'])) {
    $countSql .= " AND name LIKE :name";
}
if (!empty($_GET['descripcion'])) {
    $countSql .= " AND descripcion = :descripcion";
}
if (!empty($_GET['instrumento'])) {
    $countSql .= " AND instrumento = :instrumento";
}
if (!empty($_GET['year'])) {
    $countSql .= " AND year = :year";
}
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalResults = $countStmt->fetchColumn();
$totalPages = ceil($totalResults / $perPage);

// Ordenar por número de instrumento (numéricamente) y luego por año descendente
$sql .= " ORDER BY CAST(name AS UNSIGNED) ASC, CAST(year AS UNSIGNED) DESC";

// Modificar consulta principal para paginación
$sql .= " LIMIT :limit OFFSET :offset";
$params[':limit'] = $perPage;
$params[':offset'] = $offset;

$stmt = $pdo->prepare($sql);
foreach ($params as $key => &$val) {
    if ($key === ':limit' || $key === ':offset') {
        $stmt->bindValue($key, $val, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $val);
    }
}
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cargar anexos para los resultados (si hay)
$anexosByExpediente = [];
if (!empty($results)) {
    $ids = array_column($results, 'id');
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sqlAnexos = "SELECT expediente_id, file_path FROM anexos WHERE expediente_id IN ($placeholders)";
        $stmtAnexos = $pdo->prepare($sqlAnexos);
        $stmtAnexos->execute($ids);
        while ($ax = $stmtAnexos->fetch(PDO::FETCH_ASSOC)) {
            $anexosByExpediente[$ax['expediente_id']][] = $ax['file_path'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Búsqueda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #bd2130;
            border-color: #b21f2d;
        }
        footer {
            background-color: #343a40;
            color: #ffffff;
            padding: 1rem 0;
            text-align: center;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
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



    <div class="container mt-4 mb-5" >
        <h1 class="mb-4">Resultados de Búsqueda</h1>
        <!-- Selector de cantidad de resultados -->
        <form method="get" class="mb-3 d-flex align-items-center" style="gap: 1rem;">
            <?php foreach ($_GET as $key => $value): ?>
                <?php if ($key !== 'per_page' && $key !== 'page'): ?>
                    <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <label for="per_page" class="mb-0">Mostrar</label>
            <select name="per_page" id="per_page" class="form-select w-auto" onchange="this.form.submit()">
                <?php foreach ($perPageOptions as $option): ?>
                    <option value="<?php echo $option; ?>" <?php if ($perPage == $option) echo 'selected'; ?>><?php echo $option; ?></option>
                <?php endforeach; ?>
            </select>
            <span>resultados por página</span>
        </form>
        <!-- Paginador -->
        <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                            <a class="page-link" href="?<?php
                                $query = $_GET;
                                $query['page'] = $i;
                                $query['per_page'] = $perPage;
                                echo http_build_query($query);
                            ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
        <!-- Mostrar mensajes de éxito o error -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($results)): ?>
            <p class="text-muted">No se encontraron resultados.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Descripción</th>
                            <th>Instrumento</th>
                            <th>Año</th>
                            <th></th>
                            <th>Anexos</th>
                            <?php if ($isLoggedIn): ?>
                                <th>Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($row['instrumento']); ?></td>
                                <td><?php echo htmlspecialchars($row['year']); ?></td>
                                <td>
                                    <?php if (!empty($row['file_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="btn btn-primary btn-sm" title="Ver archivo">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($anexosByExpediente[$row['id']])): ?>
                                        <?php foreach ($anexosByExpediente[$row['id']] as $idx => $anexoPath): ?>
                                            <a href="<?php echo htmlspecialchars($anexoPath); ?>" target="_blank" class="btn btn-secondary btn-sm me-1" title="Ver anexo <?php echo $idx + 1; ?>">
                                                <i class="fas fa-paperclip"></i> <?php echo $idx + 1; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <?php if ($isLoggedIn): ?>
                                    <td>
                                        <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="eliminar.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este registro?');" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <a href="index.php" class="btn btn-secondary mt-3">Volver a la Búsqueda</a>
    </div>

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