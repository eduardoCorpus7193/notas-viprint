<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no permitido.');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$numero_nota = trim($_POST['numero_nota'] ?? '');
$empresa = trim($_POST['empresa'] ?? '');
$detalle_cliente = trim($_POST['detalle_cliente'] ?? '');
$telefono_cliente = trim($_POST['telefono_cliente'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');
$fecha_nota = trim($_POST['fecha_nota'] ?? '');
$fecha_recibido = trim($_POST['fecha_recibido'] ?? '');
$fecha_concluido = !empty($_POST['fecha_concluido']) ? trim($_POST['fecha_concluido']) : null;
$estado = trim($_POST['estado'] ?? '');

$tamanos = $_POST['tamano'] ?? [];
$detalles = $_POST['detalles'] ?? [];

if (
    $id <= 0 ||
    empty($numero_nota) ||
    empty($empresa) ||
    empty($detalle_cliente) ||
    empty($fecha_nota) ||
    empty($fecha_recibido) ||
    empty($estado)
) {
    die('Error: faltan campos obligatorios o el ID es inválido.');
}

/*
|--------------------------------------------------------------------------
| Validar número de nota único al editar
|--------------------------------------------------------------------------
*/
$sqlValidarNumero = "SELECT id FROM notas_trabajo WHERE numero_nota = ? AND id != ? LIMIT 1";
$stmtValidarNumero = $conn->prepare($sqlValidarNumero);

if (!$stmtValidarNumero) {
    die('Error al preparar validación de número de nota: ' . $conn->error);
}

$stmtValidarNumero->bind_param("si", $numero_nota, $id);
$stmtValidarNumero->execute();
$resultadoValidarNumero = $stmtValidarNumero->get_result();

if ($resultadoValidarNumero->num_rows > 0) {
    header("Location: edit.php?id=" . $id . "&error=numero_duplicado");
    exit;
}

if ($empresa === 'Imagen') {
    $hayTamanoValido = false;

    foreach ($tamanos as $tamano) {
        if (!empty(trim($tamano))) {
            $hayTamanoValido = true;
            break;
        }
    }

    if (!$hayTamanoValido) {
        die('Error: para la empresa Imagen debes agregar al menos un tamaño.');
    }
}

$conn->begin_transaction();

try {
    /*
    |--------------------------------------------------------------------------
    | 1. Actualizar nota principal
    |--------------------------------------------------------------------------
    */
    $sqlNota = "UPDATE notas_trabajo SET
                    numero_nota = ?,
                    empresa = ?,
                    detalle_cliente = ?,
                    telefono_cliente = ?,
                    observaciones = ?,
                    fecha_nota = ?,
                    fecha_recibido = ?,
                    fecha_concluido = ?,
                    estado = ?
                WHERE id = ?";

    $stmtNota = $conn->prepare($sqlNota);

    if (!$stmtNota) {
        throw new Exception('Error al preparar la actualización principal: ' . $conn->error);
    }

    $stmtNota->bind_param(
        "sssssssssi",
        $numero_nota,
        $empresa,
        $detalle_cliente,
        $telefono_cliente,
        $observaciones,
        $fecha_nota,
        $fecha_recibido,
        $fecha_concluido,
        $estado,
        $id
    );

    if (!$stmtNota->execute()) {
        throw new Exception('Error al actualizar la nota principal: ' . $stmtNota->error);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Borrar detalles anteriores
    |--------------------------------------------------------------------------
    */
    $sqlDeleteDetalles = "DELETE FROM detalle_imagen WHERE id_notas_trabajo = ?";
    $stmtDeleteDetalles = $conn->prepare($sqlDeleteDetalles);

    if (!$stmtDeleteDetalles) {
        throw new Exception('Error al preparar borrado de detalles: ' . $conn->error);
    }

    $stmtDeleteDetalles->bind_param("i", $id);

    if (!$stmtDeleteDetalles->execute()) {
        throw new Exception('Error al borrar detalles anteriores: ' . $stmtDeleteDetalles->error);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Insertar nuevos detalles solo si es Imagen
    |--------------------------------------------------------------------------
    */
    if ($empresa === 'Imagen') {
        $sqlInsertDetalle = "INSERT INTO detalle_imagen (tamano, detalles, id_notas_trabajo)
                             VALUES (?, ?, ?)";

        $stmtInsertDetalle = $conn->prepare($sqlInsertDetalle);

        if (!$stmtInsertDetalle) {
            throw new Exception('Error al preparar inserción de detalles: ' . $conn->error);
        }

        for ($i = 0; $i < count($tamanos); $i++) {
            $tamano = trim($tamanos[$i] ?? '');
            $detalleExtra = trim($detalles[$i] ?? '');

            if (empty($tamano)) {
                continue;
            }

            if ($tamano !== 'Otro' && $detalleExtra === '') {
                $detalleExtra = null;
            }

            if ($tamano === 'Otro' && $detalleExtra === '') {
                $detalleExtra = 'Sin detalle adicional';
            }

            $stmtInsertDetalle->bind_param("ssi", $tamano, $detalleExtra, $id);

            if (!$stmtInsertDetalle->execute()) {
                throw new Exception('Error al insertar detalle actualizado: ' . $stmtInsertDetalle->error);
            }
        }
    }

    $conn->commit();
    header("Location: ../../index.php?updated=1");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Error al actualizar la nota: " . $e->getMessage());
}