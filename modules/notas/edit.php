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
| Cargar detalles de imagen si aplica
|--------------------------------------------------------------------------
*/
$detallesImagen = [];

if ($nota['empresa'] === 'Imagen') {
    $sqlDetalles = "SELECT * FROM detalle_imagen WHERE id_notas_trabajo = ? ORDER BY id ASC";
    $stmtDetalles = $conn->prepare($sqlDetalles);

    if (!$stmtDetalles) {
        die('Error al preparar la consulta de detalles: ' . $conn->error);
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

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="mb-1">Editar nota</h1>
                        <p class="text-muted mb-0">Modifica la información del pedido</p>
                    </div>
                    <a href="show.php?id=<?php echo $nota['id']; ?>" class="btn btn-dark-soft">Volver al detalle</a>
                </div>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'numero_duplicado'): ?>
                    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>Ya existe otra nota con ese número. Usa un número diferente.</span>
                    </div>
                <?php endif; ?>

                <form action="update.php" method="POST" class="row g-4" id="formEditarNota">
                    <input type="hidden" name="id" value="<?php echo $nota['id']; ?>">

                    <div class="col-md-6">
                        <label for="numero_nota" class="form-label fw-semibold">Número de nota *</label>
                        <input type="text" class="form-control" id="numero_nota" name="numero_nota"
                            value="<?php echo htmlspecialchars($nota['numero_nota']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="empresa" class="form-label fw-semibold">Empresa *</label>
                        <select class="form-select" id="empresa" name="empresa" required>
                            <option value="">Selecciona una empresa</option>
                            <option value="ViPrint" <?php echo ($nota['empresa'] === 'ViPrint') ? 'selected' : ''; ?>>
                                ViPrint</option>
                            <option value="Imagen" <?php echo ($nota['empresa'] === 'Imagen') ? 'selected' : ''; ?>>Imagen
                            </option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="detalle_cliente" class="form-label fw-semibold">Detalle del cliente / pedido
                            *</label>
                        <input type="text" class="form-control" id="detalle_cliente" name="detalle_cliente"
                            value="<?php echo htmlspecialchars($nota['detalle_cliente']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="telefono_cliente" class="form-label fw-semibold">Teléfono</label>
                        <input type="text" class="form-control" id="telefono_cliente" name="telefono_cliente"
                            value="<?php echo htmlspecialchars($nota['telefono_cliente']); ?>">
                    </div>

                    <div class="col-12">
                        <label for="observaciones" class="form-label fw-semibold">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones"
                            rows="4"><?php echo htmlspecialchars($nota['observaciones']); ?></textarea>
                    </div>

                    <div class="col-md-4">
                        <label for="fecha_nota" class="form-label fw-semibold">Fecha de la nota *</label>
                        <input type="date" class="form-control" id="fecha_nota" name="fecha_nota"
                            value="<?php echo htmlspecialchars($nota['fecha_nota']); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="fecha_recibido" class="form-label fw-semibold">Fecha de recibido *</label>
                        <input type="date" class="form-control" id="fecha_recibido" name="fecha_recibido"
                            value="<?php echo htmlspecialchars($nota['fecha_recibido']); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="fecha_concluido" class="form-label fw-semibold">Fecha de concluido</label>
                        <input type="date" class="form-control" id="fecha_concluido" name="fecha_concluido"
                            value="<?php echo htmlspecialchars($nota['fecha_concluido']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="estado" class="form-label fw-semibold">Estado *</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="">Selecciona un estado</option>
                            <option value="pendiente" <?php echo ($nota['estado'] === 'pendiente') ? 'selected' : ''; ?>>
                                Pendiente</option>
                            <option value="en_proceso" <?php echo ($nota['estado'] === 'en_proceso') ? 'selected' : ''; ?>>En proceso</option>
                            <option value="terminado" <?php echo ($nota['estado'] === 'terminado') ? 'selected' : ''; ?>>
                                Terminado</option>
                            <option value="entregado" <?php echo ($nota['estado'] === 'entregado') ? 'selected' : ''; ?>>
                                Entregado</option>
                            <option value="cancelado" <?php echo ($nota['estado'] === 'cancelado') ? 'selected' : ''; ?>>
                                Cancelado</option>
                        </select>
                    </div>

                    <!-- BLOQUE SOLO PARA IMAGEN -->
                    <div class="col-12 <?php echo ($nota['empresa'] === 'Imagen') ? '' : 'd-none'; ?>"
                        id="bloqueImagen">
                        <div class="soft-panel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="section-title mb-1">Tamaños para Imagen</h5>
                                    <p class="text-muted mb-0">Puedes modificar uno o varios tamaños para esta nota.</p>
                                </div>
                                <button type="button" class="btn btn-pink btn-sm" id="btnAgregarTamano">
                                    + Agregar tamaño
                                </button>
                            </div>

                            <div id="contenedorTamanos">
                                <?php if (!empty($detallesImagen)): ?>
                                    <?php foreach ($detallesImagen as $detalle): ?>
                                        <div class="row g-3 item-tamano mb-3">
                                            <div class="col-md-5">
                                                <label class="form-label fw-semibold">Tamaño</label>
                                                <select name="tamano[]" class="form-select tamano-select">
                                                    <option value="">Selecciona un tamaño</option>
                                                    <option value="Grande IMG 3.2" <?php echo ($detalle['tamano'] === 'Grande IMG 3.2') ? 'selected' : ''; ?>>Grande IMG 3.2</option>
                                                    <option value="Mediana IMG 2.7" <?php echo ($detalle['tamano'] === 'Mediana IMG 2.7') ? 'selected' : ''; ?>>Mediana IMG 2.7</option>
                                                    <option value="Jumbo IMG 3.4" <?php echo ($detalle['tamano'] === 'Jumbo IMG 3.4') ? 'selected' : ''; ?>>Jumbo IMG 3.4</option>
                                                    <option value="Otro" <?php echo ($detalle['tamano'] === 'Otro') ? 'selected' : ''; ?>>Otro</option>
                                                </select>
                                            </div>

                                            <div class="col-md-5">
                                                <label class="form-label fw-semibold">Detalles adicionales</label>
                                                <input type="text" name="detalles[]" class="form-control detalle-input"
                                                    value="<?php echo htmlspecialchars($detalle['detalles']); ?>"
                                                    placeholder="Solo si elegiste 'Otro' o deseas especificar algo">
                                            </div>

                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-outline-danger w-100 btnEliminarTamano">
                                                    Quitar
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="row g-3 item-tamano mb-3">
                                        <div class="col-md-5">
                                            <label class="form-label fw-semibold">Tamaño</label>
                                            <select name="tamano[]" class="form-select tamano-select">
                                                <option value="">Selecciona un tamaño</option>
                                                <option value="Grande IMG 3.2">Grande IMG 3.2</option>
                                                <option value="Mediana IMG 2.7">Mediana IMG 2.7</option>
                                                <option value="Jumbo IMG 3.4">Jumbo IMG 3.4</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>

                                        <div class="col-md-5">
                                            <label class="form-label fw-semibold">Detalles adicionales</label>
                                            <input type="text" name="detalles[]" class="form-control detalle-input"
                                                placeholder="Solo si elegiste 'Otro' o deseas especificar algo">
                                        </div>

                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-outline-danger w-100 btnEliminarTamano">
                                                Quitar
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 d-flex gap-2 pt-2">
                        <button type="submit" class="btn btn-pink">Guardar cambios</button>
                        <a href="show.php?id=<?php echo $nota['id']; ?>" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const empresaSelect = document.getElementById("empresa");
            const bloqueImagen = document.getElementById("bloqueImagen");
            const btnAgregarTamano = document.getElementById("btnAgregarTamano");
            const contenedorTamanos = document.getElementById("contenedorTamanos");

            function toggleBloqueImagen() {
                if (empresaSelect.value === "Imagen") {
                    bloqueImagen.classList.remove("d-none");
                } else {
                    bloqueImagen.classList.add("d-none");
                }
            }

            empresaSelect.addEventListener("change", toggleBloqueImagen);
            toggleBloqueImagen();

            if (btnAgregarTamano) {
                btnAgregarTamano.addEventListener("click", function () {
                    const nuevoItem = document.createElement("div");
                    nuevoItem.className = "row g-3 item-tamano mb-3";
                    nuevoItem.innerHTML = `
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Tamaño</label>
                    <select name="tamano[]" class="form-select tamano-select">
                        <option value="">Selecciona un tamaño</option>
                        <option value="Grande IMG 3.2">Grande IMG 3.2</option>
                        <option value="Mediana IMG 2.7">Mediana IMG 2.7</option>
                        <option value="Jumbo IMG 3.4">Jumbo IMG 3.4</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label fw-semibold">Detalles adicionales</label>
                    <input type="text" name="detalles[]" class="form-control detalle-input" placeholder="Solo si elegiste 'Otro' o deseas especificar algo">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger w-100 btnEliminarTamano">
                        Quitar
                    </button>
                </div>
            `;
                    contenedorTamanos.appendChild(nuevoItem);
                });
            }

            if (contenedorTamanos) {
                contenedorTamanos.addEventListener("click", function (e) {
                    if (e.target.classList.contains("btnEliminarTamano")) {
                        const items = contenedorTamanos.querySelectorAll(".item-tamano");

                        if (items.length > 1) {
                            e.target.closest(".item-tamano").remove();
                        } else {
                            const item = e.target.closest(".item-tamano");
                            item.querySelector(".tamano-select").value = "";
                            item.querySelector(".detalle-input").value = "";
                        }
                    }
                });
            }
        });
    </script>

    <?php include '../../includes/footer.php'; ?>