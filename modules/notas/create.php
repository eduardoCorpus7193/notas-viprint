<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="container my-5">
    <div class="card shadow-sm border-0 rounded-4 main-card">
        <div class="card-body p-4 p-md-5">
            <div class="page-accent"></div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-1">Nueva nota de trabajo</h1>
                    <p class="text-muted mb-0">Captura la información del pedido del cliente</p>
                </div>
                <a href="../../index.php" class="btn btn-dark-soft">Volver</a>
            </div>

            <form action="store.php" method="POST" class="row g-4">
                <div class="col-md-6">
                    <label for="numero_nota" class="form-label fw-semibold">Número de nota *</label>
                    <input type="text" class="form-control" id="numero_nota" name="numero_nota" required>
                </div>

                <div class="col-md-6">
                    <label for="empresa" class="form-label fw-semibold">Empresa *</label>
                    <select class="form-select" id="empresa" name="empresa" required>
                        <option value="">Selecciona una empresa</option>
                        <option value="ViPrint">ViPrint</option>
                        <option value="Imagen">Imagen</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="nombre_cliente" class="form-label fw-semibold">Nombre del cliente *</label>
                    <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" required>
                </div>

                <div class="col-md-6">
                    <label for="telefono_cliente" class="form-label fw-semibold">Teléfono</label>
                    <input type="text" class="form-control" id="telefono_cliente" name="telefono_cliente">
                </div>

                <div class="col-12">
                    <label for="observaciones" class="form-label fw-semibold">Observaciones / descripción</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="4"></textarea>
                </div>

                <div class="col-md-4">
                    <label for="fecha_nota" class="form-label fw-semibold">Fecha de la nota *</label>
                    <input type="date" class="form-control" id="fecha_nota" name="fecha_nota" required>
                </div>

                <div class="col-md-4">
                    <label for="fecha_recibido" class="form-label fw-semibold">Fecha de recibido *</label>
                    <input type="date" class="form-control" id="fecha_recibido" name="fecha_recibido" required>
                </div>

                <div class="col-md-4">
                    <label for="fecha_concluido" class="form-label fw-semibold">Fecha de concluido</label>
                    <input type="date" class="form-control" id="fecha_concluido" name="fecha_concluido">
                </div>

                <div class="col-md-6">
                    <label for="estado" class="form-label fw-semibold">Estado *</label>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="">Selecciona un estado</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_proceso">En proceso</option>
                        <option value="terminado">Terminado</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>

                <div class="col-12 d-flex gap-2 pt-2">
                    <button type="submit" class="btn btn-pink">Guardar nota</button>
                    <a href="../../index.php" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>