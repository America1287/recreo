<?php
session_start();
require_once "../config/database.php";
// $result = getAlertas($conn);

if (!isset($_SESSION['user_id'])) {
    header("Location: /recreo/views/login.php");
    exit;
}

$action = $_GET['action'] ?? 'index';

// -----------------------------------------------------
// LISTAR ALERTAS
// -----------------------------------------------------
if ($action == "index") {

    $sql = "SELECT alertas.*, estudiantes.nombre AS estudiante
            FROM alertas
            JOIN estudiantes ON alertas.estudiante_id = estudiantes.id
            ORDER BY fecha DESC";

    $result = $conn->query($sql);

    include "../views/alertas/index.php";
    exit;
}

// -----------------------------------------------------
// ELIMINAR ALERTA
// -----------------------------------------------------
if ($action == "delete") {

    // Validar ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        die("ID inválido");
    }

    $id = (int)$_GET['id'];

    // ✅ Consulta preparada
    $stmt = $conn->prepare("DELETE FROM alertas WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: /recreo/controllers/alertas.php?action=index");
        exit;
    } else {
        die("Error al eliminar la alerta");
    }
}
?>