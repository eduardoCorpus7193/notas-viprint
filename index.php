<?php
require_once 'config/database.php';
require_once 'includes/helpers.php';

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$empresa = isset($_GET['empresa']) ? trim($_GET['empresa']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$fecha_nota = isset($_GET['fecha_nota']) ? trim($_GET['fecha_nota']) : '';
$fecha_recibido = isset($_GET['fecha_recibido']) ? trim($_GET['fecha_recibido']) : '';
$fecha_concluido = isset($_GET['fecha_concluido']) ? trim($_GET['fecha_concluido']) : '';

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$direction = isset($_GET['direction']) ? strtolower($_GET['direction']) : 'desc';
$per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 5;
$pagina_actual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;

if ($pagina_actual < 1) {
    $pagina_actual = 1;
}

$allowed_per_page = [5, 10, 20];
if (!in_array($per_page, $allowed_per_page)) {
    $per_page = 5;
}

$allowed_sort_columns = [
    'numero_nota' => 'numero_nota',
    'empresa' => 'empresa',
    'detalle_cliente' => 'detalle_cliente',
    'estado' => 'estado',
    'fecha_nota' => 'fecha_nota',
    'fecha_recibido' => 'fecha_recibido',
    'fecha_concluido' => 'fecha_concluido',
    'id' => 'id'
];

if (!array_key_exists($sort, $allowed_sort_columns)) {
    $sort = 'id';
}

if (!in_array($direction, ['asc', 'desc'])) {
    $direction = 'desc';
}

$offset = ($pagina_actual - 1) * $per_page;

$where = " WHERE 1=1";
$params = [];
$types = "";

if (!empty($busqueda)) {
    $where .= " AND (
        numero_nota LIKE ?
        OR detalle_cliente LIKE ?
        OR telefono_cliente LIKE ?
        OR observaciones LIKE ?
    )";
    $busqueda_like = "%" . $busqueda . "%";
    $params[] = $busqueda_like;
    $params[] = $busqueda_like;
    $params[] = $busqueda_like;
    $params[] = $busqueda_like;
    $types .= "ssss";
}

if (!empty($empresa)) {
    $where .= " AND empresa = ?";
    $params[] = $empresa;
    $types .= "s";
}

if (!empty($estado)) {
    $where .= " AND estado = ?";
    $params[] = $estado;
    $types .= "s";
}

if (!empty($fecha_nota)) {
    $where .= " AND fecha_nota = ?";
    $params[] = $fecha_nota;
    $types .= "s";
}

if (!empty($fecha_recibido)) {
    $where .= " AND fecha_recibido = ?";
    $params[] = $fecha_recibido;
    $types .= "s";
}

if (!empty($fecha_concluido)) {
    $where .= " AND fecha_concluido = ?";
    $params[] = $fecha_concluido;
    $types .= "s";
}

$filtros_activos = !empty($empresa) || !empty($estado) || !empty($fecha_nota) || !empty($fecha_recibido) || !empty($fecha_concluido);

$sql_total = "SELECT COUNT(*) as total FROM notas_trabajo" . $where;
$stmt_total = $conn->prepare($sql_total);

if (!$stmt_total) {
    die("Error al preparar conteo: " . $conn->error);
}

if (!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}

$stmt_total->execute();
$resultado_total = $stmt_total->get_result();
$total_filas = $resultado_total->fetch_assoc()['total'];
$total_paginas = max(1, ceil($total_filas / $per_page));

$order_by = $allowed_sort_columns[$sort];

$sql = "SELECT * FROM notas_trabajo" . $where . " ORDER BY $order_by $direction LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}

$params_main = $params;
$types_main = $types . "ii";
$params_main[] = $per_page;
$params_main[] = $offset;

$stmt->bind_param($types_main, ...$params_main);
$stmt->execute();
$resultado = $stmt->get_result();

function buildQuery($overrides = [])
{
    $query = array_merge($_GET, $overrides);
    return http_build_query($query);
}

function nextDirection($currentSort, $column, $currentDirection)
{
    if ($currentSort === $column && $currentDirection === 'asc') {
        return 'desc';
    }
    return 'asc';
}

function sortIcon($currentSort, $column, $currentDirection)
{
    if ($currentSort !== $column) {
        return '<i class="bi bi-arrow-down-up"></i>';
    }

    return $currentDirection === 'asc'
        ? '<i class="bi bi-sort-down-alt"></i>'
        : '<i class="bi bi-sort-down"></i>';
}
?>

<?php include 'includes/header.php'; ?>

<body data-filters-active="<?php echo $filtros_activos ? '1' : '0'; ?>">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid my-5 px-4 px-md-5">
        <div class="card shadow-sm border-0 rounded-4 main-card">
            <div class="card-body p-4 p-md-5">
                <div class="page-accent"></div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h1 class="mb-1">Sistema de Notas de Trabajo</h1>
                        <p class="text-muted mb-0">Gestión interna de pedidos para ViPrint</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" id="toggleFiltersBtn" class="btn btn-dark-soft filters-toggle-btn">
                            Mostrar filtros avanzados
                        </button>
                        <a href="modules/notas/create.php" class="btn btn-pink">
                            <i class="bi bi-plus-lg"></i> Nueva nota
                        </a>
                    </div>
                </div>

                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>La nota se guardó correctamente.</span>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                    <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-trash3-fill"></i>
                        <span>La nota se eliminó correctamente.</span>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
                    <div class="alert alert-info rounded-4 border-0 shadow-sm mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square"></i>
                        <span>La nota se actualizó correctamente.</span>
                    </div>
                <?php endif; ?>

                <div class="soft-panel mb-4">
                    <h5 class="section-title mb-3">Búsqueda general</h5>

                    <form method="GET" action="index.php" class="row g-3">
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                        <input type="hidden" name="direction" value="<?php echo htmlspecialchars($direction); ?>">
                        <input type="hidden" name="per_page" value="<?php echo htmlspecialchars($per_page); ?>">

                        <div class="col-md-8">
                            <input type="text" name="busqueda" class="form-control"
                                placeholder="Buscar por número de nota, cliente, teléfono u observaciones..."
                                value="<?php echo htmlspecialchars($busqueda); ?>">
                        </div>

                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-pink">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>

                        <div class="col-md-2 d-grid">
                            <a href="index.php" class="btn btn-outline-secondary search-clear-btn">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>

                <div id="filtersSection"
                    class="filters-wrapper <?php echo $filtros_activos ? 'filters-active-highlight' : ''; ?>">
                    <div class="soft-panel mb-4 <?php echo $filtros_activos ? 'filters-active-highlight' : ''; ?>">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h5 class="section-title mb-0">Filtros avanzados</h5>
                            <?php if ($filtros_activos): ?>
                                <span class="filters-status-text">
                                    <i class="bi bi-funnel-fill"></i> Hay filtros aplicados
                                </span>
                            <?php endif; ?>
                        </div>

                        <form method="GET" action="index.php" class="row g-3">
                            <input type="hidden" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>">
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                            <input type="hidden" name="direction" value="<?php echo htmlspecialchars($direction); ?>">
                            <input type="hidden" name="per_page" value="<?php echo htmlspecialchars($per_page); ?>">

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Empresa</label>
                                <select name="empresa" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="ViPrint" <?php echo ($empresa === 'ViPrint') ? 'selected' : ''; ?>>
                                        ViPrint</option>
                                    <option value="Imagen" <?php echo ($empresa === 'Imagen') ? 'selected' : ''; ?>>Imagen
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="pendiente" <?php echo ($estado === 'pendiente') ? 'selected' : ''; ?>>
                                        Pendiente</option>
                                    <option value="en_proceso" <?php echo ($estado === 'en_proceso') ? 'selected' : ''; ?>>En proceso</option>
                                    <option value="terminado" <?php echo ($estado === 'terminado') ? 'selected' : ''; ?>>
                                        Terminado</option>
                                    <option value="entregado" <?php echo ($estado === 'entregado') ? 'selected' : ''; ?>>
                                        Entregado</option>
                                    <option value="cancelado" <?php echo ($estado === 'cancelado') ? 'selected' : ''; ?>>
                                        Cancelado</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fecha de nota</label>
                                <input type="date" name="fecha_nota" class="form-control"
                                    value="<?php echo htmlspecialchars($fecha_nota); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha recibido</label>
                                <input type="date" name="fecha_recibido" class="form-control"
                                    value="<?php echo htmlspecialchars($fecha_recibido); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha concluido</label>
                                <input type="date" name="fecha_concluido" class="form-control"
                                    value="<?php echo htmlspecialchars($fecha_concluido); ?>">
                            </div>

                            <div class="col-12 d-flex gap-2 pt-2 flex-wrap">
                                <button type="submit" class="btn btn-pink">
                                    <i class="bi bi-funnel"></i> Aplicar filtros
                                </button>
                                <a href="index.php" class="btn btn-dark-soft">
                                    <i class="bi bi-arrow-clockwise"></i> Limpiar todo
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="soft-panel mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="section-title mb-0">Listado de notas</h5>
                            <span class="note-count-badge"><?php echo $total_filas; ?> resultado(s)</span>
                        </div>

                        <form method="GET" action="index.php" class="d-flex align-items-center gap-2">
                            <input type="hidden" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>">
                            <input type="hidden" name="empresa" value="<?php echo htmlspecialchars($empresa); ?>">
                            <input type="hidden" name="estado" value="<?php echo htmlspecialchars($estado); ?>">
                            <input type="hidden" name="fecha_nota" value="<?php echo htmlspecialchars($fecha_nota); ?>">
                            <input type="hidden" name="fecha_recibido"
                                value="<?php echo htmlspecialchars($fecha_recibido); ?>">
                            <input type="hidden" name="fecha_concluido"
                                value="<?php echo htmlspecialchars($fecha_concluido); ?>">
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                            <input type="hidden" name="direction" value="<?php echo htmlspecialchars($direction); ?>">

                            <label class="results-meta mb-0">Ver</label>
                            <select name="per_page" class="form-select" onchange="this.form.submit()">
                                <option value="5" <?php echo $per_page == 5 ? 'selected' : ''; ?>>5</option>
                                <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="20" <?php echo $per_page == 20 ? 'selected' : ''; ?>>20</option>
                            </select>
                            <label class="results-meta mb-0">registros</label>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <a class="table-sort-link"
                                            href="index.php?<?php echo buildQuery(['sort' => 'numero_nota', 'direction' => nextDirection($sort, 'numero_nota', $direction), 'pagina' => 1]); ?>">
                                            No. Nota <?php echo sortIcon($sort, 'numero_nota', $direction); ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a class="table-sort-link"
                                            href="index.php?<?php echo buildQuery(['sort' => 'empresa', 'direction' => nextDirection($sort, 'empresa', $direction), 'pagina' => 1]); ?>">
                                            Empresa <?php echo sortIcon($sort, 'empresa', $direction); ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a class="table-sort-link"
                                            href="index.php?<?php echo buildQuery(['sort' => 'detalle_cliente', 'direction' => nextDirection($sort, 'detalle_cliente', $direction), 'pagina' => 1]); ?>">
                                            Cliente <?php echo sortIcon($sort, 'detalle_cliente', $direction); ?>
                                        </a>
                                    </th>
                                    <th>Teléfono</th>
                                    <th>
                                        <a class="table-sort-link"
                                            href="index.php?<?php echo buildQuery(['sort' => 'estado', 'direction' => nextDirection($sort, 'estado', $direction), 'pagina' => 1]); ?>">
                                            Estado <?php echo sortIcon($sort, 'estado', $direction); ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a class="table-sort-link"
                                            href="index.php?<?php echo buildQuery(['sort' => 'fecha_nota', 'direction' => nextDirection($sort, 'fecha_nota', $direction), 'pagina' => 1]); ?>">
                                            Fecha nota <?php echo sortIcon($sort, 'fecha_nota', $direction); ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a class="table-sort-link"
                                            href="index.php?<?php echo buildQuery(['sort' => 'fecha_recibido', 'direction' => nextDirection($sort, 'fecha_recibido', $direction), 'pagina' => 1]); ?>">
                                            Fecha recibido <?php echo sortIcon($sort, 'fecha_recibido', $direction); ?>
                                        </a>
                                    </th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultado && $resultado->num_rows > 0): ?>
                                    <?php while ($nota = $resultado->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($nota['numero_nota']); ?></td>
                                            <td><?php echo htmlspecialchars($nota['empresa']); ?></td>
                                            <td><?php echo htmlspecialchars($nota['detalle_cliente']); ?></td>
                                            <td><?php echo htmlspecialchars($nota['telefono_cliente']); ?></td>
                                            <td>
                                                <span class="badge-status <?php echo claseEstado($nota['estado']); ?>">
                                                    <?php echo textoEstado($nota['estado']); ?>
                                                </span>
                                            </td>
                                            <td class="table-date-muted"><?php echo formatearFecha($nota['fecha_nota']); ?></td>
                                            <td class="table-date-muted"><?php echo formatearFecha($nota['fecha_recibido']); ?>
                                            </td>
                                            <td class="table-date-muted"><?php echo formatearFecha($nota['fecha_concluido']); ?>
                                            </td>
                                            <td>
                                                <div class="actions-group">
                                                    <a href="modules/notas/show.php?id=<?php echo $nota['id']; ?>"
                                                        class="btn btn-sm btn-dark-soft icon-btn">
                                                        <i class="bi bi-eye"></i> Ver
                                                    </a>
                                                    <a href="modules/notas/edit.php?id=<?php echo $nota['id']; ?>"
                                                        class="btn btn-sm btn-pink icon-btn">
                                                        <i class="bi bi-pencil-square"></i> Editar
                                                    </a>
                                                    <a href="modules/notas/delete.php?id=<?php echo $nota['id']; ?>"
                                                        class="btn btn-sm btn-outline-danger icon-btn btn-delete-note">
                                                        <i class="bi bi-trash3"></i> Eliminar
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            No se encontraron notas con esos criterios.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_paginas > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center flex-wrap">
                                <?php if ($pagina_actual > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="index.php?<?php echo buildQuery(['pagina' => $pagina_actual - 1]); ?>">Anterior</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                    <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                                        <a class="page-link" href="index.php?<?php echo buildQuery(['pagina' => $i]); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagina_actual < $total_paginas): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="index.php?<?php echo buildQuery(['pagina' => $pagina_actual + 1]); ?>">Siguiente</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>