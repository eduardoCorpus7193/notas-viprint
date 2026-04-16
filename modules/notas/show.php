<?php
require_once '../../config/database.php';
require_once '../../includes/helpers.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID de nota no proporcionado.');
}

$id = (int) $_GET['id'];

$sql = "SELECT * FROM notas_trabajo WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Error al preparar la consulta: ' . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();
$nota = $resultado->fetch_assoc();

if (!$nota) {
    die('La nota no existe.');
}

/*
|--------------------------------------------------------------------------
| Consultar tamaños relacionados si la empresa es Imagen
|--------------------------------------------------------------------------
*/
$detallesImagen = [];

if ($nota['empresa'] === 'Imagen') {
    $sqlDetalles = "SELECT * FROM detalle_imagen WHERE id_notas_trabajo = ? ORDER BY id ASC";
    $stmtDetalles = $conn->prepare($sqlDetalles);

    if (!$stmtDetalles) {
        die('Error al preparar la consulta de detalles de imagen: ' . $conn->error);
    }

    $stmtDetalles->bind_param("i", $id);
    $stmtDetalles->execute();

    $resultadoDetalles = $stmtDetalles->get_result();

    while ($fila = $resultadoDetalles->fetch_assoc()) {
        $detallesImagen[] = $fila;
    }
}
?>

<?php include '../../includes/header.php'; ?>

<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container-fluid my-5 px-4 px-xl-5">
        <div class="card shadow-sm border-0 rounded-4 main-card">
            <div class="card-body p-4 p-md-5">
                <div class="page-accent"></div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h1 class="mb-1">Detalle de la nota</h1>
                        <p class="text-muted mb-0">Consulta completa del pedido registrado</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="edit.php?id=<?php echo $nota['id']; ?>" class="btn btn-sm btn-pink icon-btn">
                            <i class="bi bi-pencil-square"></i> Editar
                        </a>

                        <div class="d-flex gap-2">
                            <a href="../../index.php" class="btn btn-sm btn-dark-soft icon-btn">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="soft-panel h-100">
                            <h5 class="section-title mb-3">Información general</h5>

                            <p>
                                <strong>Número de nota:</strong><br>
                                <?php echo htmlspecialchars($nota['numero_nota']); ?>
                            </p>

                            <p>
                                <strong>Empresa:</strong><br>
                                <?php echo htmlspecialchars($nota['empresa']); ?>
                            </p>

                            <p>
                                <strong>Cliente / pedido:</strong><br>
                                <?php echo htmlspecialchars($nota['detalle_cliente']); ?>
                            </p>

                            <p class="mb-0">
                                <strong>Teléfono:</strong><br>
                                <?php echo !empty($nota['telefono_cliente']) ? htmlspecialchars($nota['telefono_cliente']) : '—'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="soft-panel h-100">
                            <h5 class="section-title mb-3">Seguimiento del trabajo</h5>

                            <p>
                                <strong>Estado:</strong><br>
                                <span class="badge-status <?php echo claseEstado($nota['estado']); ?>">
                                    <?php echo textoEstado($nota['estado']); ?>
                                </span>
                            </p>

                            <p>
                                <strong>Fecha de la nota:</strong><br>
                                <?php echo formatearFecha($nota['fecha_nota']); ?>
                            </p>

                            <p>
                                <strong>Fecha de recibido:</strong><br>
                                <?php echo formatearFecha($nota['fecha_recibido']); ?>
                            </p>

                            <p class="mb-0">
                                <strong>Fecha de concluido:</strong><br>
                                <?php echo formatearFecha($nota['fecha_concluido']); ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="soft-panel">
                            <h5 class="section-title mb-3">Observaciones</h5>

                            <p class="mb-0">
                                <?php echo !empty($nota['observaciones']) ? nl2br(htmlspecialchars($nota['observaciones'])) : 'Sin observaciones registradas.'; ?>
                            </p>
                        </div>
                    </div>

                    <?php if ($nota['empresa'] === 'Imagen'): ?>
                        <div class="col-12">
                            <div class="soft-panel">
                                <h5 class="section-title mb-3">Tamaños asignados para Imagen</h5>

                                <?php if (!empty($detallesImagen)): ?>
                                    <div class="detalle-imagen-list">
                                        <?php foreach ($detallesImagen as $index => $detalle): ?>
                                            <div class="detalle-imagen-item">
                                                <div class="detalle-imagen-top">
                                                    <span class="detalle-imagen-index">
                                                        Tamaño #<?php echo $index + 1; ?>
                                                    </span>

                                                    <span class="badge-imagen">
                                                        <?php echo htmlspecialchars($detalle['tamano']); ?>
                                                    </span>
                                                </div>

                                                <p class="detalle-imagen-notes">
                                                    <strong>Detalles adicionales:</strong>
                                                    <?php echo !empty($detalle['detalles']) ? htmlspecialchars($detalle['detalles']) : '—'; ?>
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="mb-0 text-muted">Esta nota no tiene tamaños registrados.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>F
                </div>

            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>