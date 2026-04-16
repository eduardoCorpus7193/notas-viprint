<?php
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_nota = trim($_POST['numero_nota']);
    $empresa = trim($_POST['empresa']);
    $detalle_cliente = trim($_POST['detalle_cliente']);
    $telefono_cliente = trim($_POST['telefono_cliente']);
    $observaciones = trim($_POST['observaciones']);
    $fecha_nota = trim($_POST['fecha_nota']);
    $fecha_recibido = trim($_POST['fecha_recibido']);
    $fecha_concluido = !empty($_POST['fecha_concluido']) ? trim($_POST['fecha_concluido']) : null;
    $estado = trim($_POST['estado']);

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

    $sql = "INSERT INTO notas_trabajo (
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

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Error al preparar la consulta: ' . $conn->error);
    }

    $stmt->bind_param(
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

    if ($stmt->execute()) {
        header("Location: ../../index.php?success=1");
        exit;
    } else {
        echo "Error al guardar la nota: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Acceso no permitido.";
}