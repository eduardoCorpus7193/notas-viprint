<?php
include '../../config/database.php';

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

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-1">Editar nota</h1>
                    <p class="text-muted mb-0">Modifica la información del pedido</p>
                </div>
                <a href="../../index.php" class="btn btn-dark-soft">Volver</a>
            </div>

            <form action="update.php" method="POST" class="row g-4">
                <input type="hidden" name="id" value="<?php echo $nota['id']; ?>">

                <div class="col-md-6">
                    <label for="numero_nota" class="form-label fw-semibold">Número de nota *</label>
                    <input
                        type="text"
                        class="form-control"
                        id="numero_nota"
                        name="numero_nota"
                        value="<?php echo htmlspecialchars($nota['numero_nota']); ?>"
                        required>
                </div>

                <div class="col-md-6">
                    <label for="empresa" class="form-label fw-semibold">Empresa *</label>
                    <select class="form-select" id="empresa" name="empresa" required>
                        <option value="">Selecciona una empresa</option>
                        <option value="ViPrint" <?php echo ($nota['empresa'] === 'ViPrint') ? 'selected' : ''; ?>>ViPrint</option>
                        <option value="Imagen" <?php echo ($nota['empresa'] === 'Imagen') ? 'selected' : ''; ?>>Imagen</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="nombre_cliente" class="form-label fw-semibold">Nombre del cliente *</label>
                    <input
                        type="text"
                        class="form-control"
                        id="nombre_cliente"
                        name="nombre_cliente"
                        value="<?php echo htmlspecialchars($nota['nombre_cliente']); ?>"
                        required>
                </div>

                <div class="col-md-6">
                    <label for="telefono_cliente" class="form-label fw-semibold">Teléfono</label>
                    <input
                        type="text"
                        class="form-control"
                        id="telefono_cliente"
                        name="telefono_cliente"
                        value="<?php echo htmlspecialchars($nota['telefono_cliente']); ?>">
                </div>

                <div class="col-12">
                    <label for="observaciones" class="form-label fw-semibold">Observaciones / descripción</label>
                    <textarea
                        class="form-control"
                        id="observaciones"
                        name="observaciones"
                        rows="4"><?php echo htmlspecialchars($nota['observaciones']); ?></textarea>
                </div>

                <div class="col-md-4">
                    <label for="fecha_nota" class="form-label fw-semibold">Fecha de la nota *</label>
                    <input
                        type="date"
                        class="form-control"
                        id="fecha_nota"
                        name="fecha_nota"
                        value="<?php echo htmlspecialchars($nota['fecha_nota']); ?>"
                        required>
                </div>

                <div class="col-md-4">
                    <label for="fecha_recibido" class="form-label fw-semibold">Fecha de recibido *</label>
                    <input
                        type="date"
                        class="form-control"
                        id="fecha_recibido"
                        name="fecha_recibido"
                        value="<?php echo htmlspecialchars($nota['fecha_recibido']); ?>"
                        required>
                </div>

                <div class="col-md-4">
                    <label for="fecha_concluido" class="form-label fw-semibold">Fecha de concluido</label>
                    <input
                        type="date"
                        class="form-control"
                        id="fecha_concluido"
                        name="fecha_concluido"
                        value="<?php echo htmlspecialchars($nota['fecha_concluido']); ?>">
                </div>

                <div class="col-md-6">
                    <label for="estado" class="form-label fw-semibold">Estado *</label>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="">Selecciona un estado</option>
                        <option value="pendiente" <?php echo ($nota['estado'] === 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="en_proceso" <?php echo ($nota['estado'] === 'en_proceso') ? 'selected' : ''; ?>>En proceso</option>
                        <option value="terminado" <?php echo ($nota['estado'] === 'terminado') ? 'selected' : ''; ?>>Terminado</option>
                        <option value="entregado" <?php echo ($nota['estado'] === 'entregado') ? 'selected' : ''; ?>>Entregado</option>
                        <option value="cancelado" <?php echo ($nota['estado'] === 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>

                <div class="col-12 d-flex gap-2 pt-2">
                    <button type="submit" class="btn btn-pink">Guardar cambios</button>
                    <a href="../../index.php" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>