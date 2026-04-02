<?php
include '../../config/database.php';

function formatearFecha($fecha)
{
    if (empty($fecha) || $fecha === '0000-00-00') {
        return '—';
    }

    return date('d/m/Y', strtotime($fecha));
}

function claseEstado($estado)
{
    return match ($estado) {
        'pendiente' => 'status-pendiente',
        'en_proceso' => 'status-en_proceso',
        'terminado' => 'status-terminado',
        'entregado' => 'status-entregado',
        'cancelado' => 'status-cancelado',
        default => 'status-pendiente'
    };
}

function textoEstado($estado)
{
    return match ($estado) {
        'en_proceso' => 'En proceso',
        default => ucfirst(str_replace('_', ' ', $estado))
    };
}

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
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="container my-5">
    <div class="card shadow-sm border-0 rounded-4 main-card">
        <div class="card-body p-4 p-md-5">
            <div class="page-accent"></div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="mb-1">Detalle de la nota</h1>
                    <p class="text-muted mb-0">Consulta completa del pedido registrado</p>
                </div>

                <div class="d-flex gap-2">
                    <a href="../../index.php" class="btn btn-dark-soft">Volver</a>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="soft-panel h-100">
                        <h5 class="section-title mb-3">Información general</h5>

                        <p><strong>Número de nota:</strong><br>
                            <?php echo htmlspecialchars($nota['numero_nota']); ?>
                        </p>

                        <p><strong>Empresa:</strong><br>
                            <?php echo htmlspecialchars($nota['empresa']); ?>
                        </p>

                        <p><strong>Nombre del cliente:</strong><br>
                            <?php echo htmlspecialchars($nota['nombre_cliente']); ?>
                        </p>

                        <p class="mb-0"><strong>Teléfono:</strong><br>
                            <?php echo htmlspecialchars($nota['telefono_cliente']); ?>
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="soft-panel h-100">
                        <h5 class="section-title mb-3">Seguimiento del trabajo</h5>

                        <p><strong>Estado:</strong><br>
                            <span class="badge-status <?php echo claseEstado($nota['estado']); ?>">
                                <?php echo textoEstado($nota['estado']); ?>
                            </span>
                        </p>

                        <p><strong>Fecha de la nota:</strong><br>
                            <?php echo formatearFecha($nota['fecha_nota']); ?>
                        </p>

                        <p><strong>Fecha de recibido:</strong><br>
                            <?php echo formatearFecha($nota['fecha_recibido']); ?>
                        </p>

                        <p class="mb-0"><strong>Fecha de concluido:</strong><br>
                            <?php echo formatearFecha($nota['fecha_concluido']); ?>
                        </p>
                    </div>
                </div>

                <div class="col-12">
                    <div class="soft-panel">
                        <h5 class="section-title mb-3">Observaciones / descripción</h5>

                        <p class="mb-0">
                            <?php echo !empty($nota['observaciones']) ? nl2br(htmlspecialchars($nota['observaciones'])) : 'Sin observaciones registradas.'; ?>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>