<?php
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $numero_nota = trim($_POST['numero_nota']);
    $empresa = trim($_POST['empresa']);
    $nombre_cliente = trim($_POST['nombre_cliente']);
    $telefono_cliente = trim($_POST['telefono_cliente']);
    $observaciones = trim($_POST['observaciones']);
    $fecha_nota = trim($_POST['fecha_nota']);
    $fecha_recibido = trim($_POST['fecha_recibido']);
    $fecha_concluido = !empty($_POST['fecha_concluido']) ? trim($_POST['fecha_concluido']) : null;
    $estado = trim($_POST['estado']);

    if (
        $id <= 0 ||
        empty($numero_nota) ||
        empty($empresa) ||
        empty($nombre_cliente) ||
        empty($fecha_nota) ||
        empty($fecha_recibido) ||
        empty($estado)
    ) {
        die('Error: faltan campos obligatorios o el ID es inválido.');
    }

    $sql = "UPDATE notas_trabajo SET
                numero_nota = ?,
                empresa = ?,
                nombre_cliente = ?,
                telefono_cliente = ?,
                observaciones = ?,
                fecha_nota = ?,
                fecha_recibido = ?,
                fecha_concluido = ?,
                estado = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Error al preparar la consulta: ' . $conn->error);
    }

    $stmt->bind_param(
        "sssssssssi",
        $numero_nota,
        $empresa,
        $nombre_cliente,
        $telefono_cliente,
        $observaciones,
        $fecha_nota,
        $fecha_recibido,
        $fecha_concluido,
        $estado,
        $id
    );

    if ($stmt->execute()) {
        header("Location: ../../index.php?updated=1");
        exit;
    } else {
        echo "Error al actualizar la nota: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Acceso no permitido.";
}