<?php
include '../../config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID de nota no proporcionado.');
}

$id = (int) $_GET['id'];

$sql = "DELETE FROM notas_trabajo WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Error al preparar la consulta: ' . $conn->error);
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../../index.php?deleted=1");
    exit;
} else {
    die('Error al eliminar la nota: ' . $stmt->error);
}