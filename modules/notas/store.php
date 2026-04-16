<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no permitido.');
}

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
    empty($numero_nota) ||
    empty($empresa) ||
    empty($detalle_cliente) ||
    empty($fecha_nota) ||
    empty($fecha_recibido) ||
    empty($estado)
) {
    die('Error: faltan campos obligatorios.');
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
    $sqlNota = "INSERT INTO notas_trabajo (
                    numero_nota,
                    empresa,
                    detalle_cliente,
                    telefono_cliente,
                    observaciones,
                    fecha_nota,
                    fecha_recibido,
                    fecha_concluido,
                    estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmtNota = $conn->prepare($sqlNota);

    if (!$stmtNota) {
        throw new Exception('Error al preparar la consulta principal: ' . $conn->error);
    }

    $stmtNota->bind_param(
        "sssssssss",
        $numero_nota,
        $empresa,
        $detalle_cliente,
        $telefono_cliente,
        $observaciones,
        $fecha_nota,
        $fecha_recibido,
        $fecha_concluido,
        $estado
    );

    if (!$stmtNota->execute()) {
        throw new Exception('Error al guardar la nota principal: ' . $stmtNota->error);
    }

    $idNota = $conn->insert_id;

    if ($empresa === 'Imagen') {
        $sqlDetalle = "INSERT INTO detalle_imagen (tamano, detalles, id_notas_trabajo)
                       VALUES (?, ?, ?)";

        $stmtDetalle = $conn->prepare($sqlDetalle);

        if (!$stmtDetalle) {
            throw new Exception('Error al preparar el detalle de imagen: ' . $conn->error);
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

            $stmtDetalle->bind_param("ssi", $tamano, $detalleExtra, $idNota);

            if (!$stmtDetalle->execute()) {
                throw new Exception('Error al guardar detalle de imagen: ' . $stmtDetalle->error);
            }
        }
    }

    $conn->commit();
    header("Location: ../../index.php?success=1");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Error al guardar la nota: " . $e->getMessage());
}