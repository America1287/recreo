<?php
session_start();
require_once "../config/database.php";

// VERIFICAR SESIÓN
if (!isset($_SESSION['user_id'])) {
    header("Location: /recreo/views/login.php");
    exit;
}

$action = $_GET['action'] ?? 'index';

// -------------------------------------------------------------
// LISTAR FALTAS (CON FILTRO OPCIONAL)
// -------------------------------------------------------------
if ($action == "index") {
    
    $filtro = isset($_GET['tipo']) ? $_GET['tipo'] : '';

    if ($filtro != '') {
        // Consulta con filtro (preparada)
        $stmt = $conn->prepare("SELECT f.*, e.nombre AS estudiante_nombre 
                                FROM faltas f 
                                JOIN estudiantes e ON f.estudiante_id = e.id 
                                WHERE f.tipo = ?
                                ORDER BY f.fecha DESC");
        $stmt->bind_param("s", $filtro);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // Consulta sin filtro
        $sql = "SELECT f.*, e.nombre AS estudiante_nombre 
                FROM faltas f 
                JOIN estudiantes e ON f.estudiante_id = e.id
                ORDER BY f.fecha DESC";
        $result = $conn->query($sql);
    }

    include "../views/faltas/index.php";
    exit;
}

// -------------------------------------------------------------
// FORMULARIO CREAR
// -------------------------------------------------------------
if ($action == "create") {

    // Obtener listado de estudiantes
    $estudiantes = $conn->query("SELECT * FROM estudiantes");

    include "../views/faltas/create.php";
    exit;
}

// -------------------------------------------------------------
// GUARDAR FALTA
// -------------------------------------------------------------
if ($action == "store") {

    // Validar que existen los datos
    if (empty($_POST['estudiante_id']) || empty($_POST['tipo']) || empty($_POST['descripcion'])) {
        die("Todos los campos son obligatorios");
    }

    $estudiante_id = (int)$_POST['estudiante_id'];
    $tipo = $_POST['tipo'];
    $descripcion = $_POST['descripcion'];

    // 1. ✅ Guardar falta con consulta preparada
    $stmt = $conn->prepare("INSERT INTO faltas (estudiante_id, tipo, descripcion) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $estudiante_id, $tipo, $descripcion);
    
    if (!$stmt->execute()) {
        die("Error al crear la falta");
    }

    // 2. ✅ Contar cuántas faltas tiene del mismo tipo
    $stmtCount = $conn->prepare("SELECT COUNT(*) AS total 
                                 FROM faltas 
                                 WHERE estudiante_id = ? 
                                 AND tipo = ?");
    $stmtCount->bind_param("is", $estudiante_id, $tipo);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $total_faltas_tipo = $resultCount->fetch_assoc()['total'];

    // 3. ✅ Si supera 3 faltas del mismo tipo, generar alerta
    if ($total_faltas_tipo >= 3) {

        // Verificar si ya existe una alerta previa para este tipo
        $stmtCheck = $conn->prepare("SELECT * FROM alertas 
                                     WHERE estudiante_id = ? 
                                     AND tipo = ?");
        $stmtCheck->bind_param("is", $estudiante_id, $tipo);
        $stmtCheck->execute();
        $checkResult = $stmtCheck->get_result();

        if ($checkResult->num_rows == 0) {
            
            // Crear alerta nueva
            $mensaje = "El estudiante ha acumulado $total_faltas_tipo faltas de tipo: $tipo";

            $stmtInsert = $conn->prepare("INSERT INTO alertas (estudiante_id, tipo, mensaje, cantidad) 
                                          VALUES (?, ?, ?, ?)");
            $stmtInsert->bind_param("issi", $estudiante_id, $tipo, $mensaje, $total_faltas_tipo);
            $stmtInsert->execute();

        } else {
            
            // Actualizar alerta existente
            $mensaje = "El estudiante ha acumulado $total_faltas_tipo faltas de tipo: $tipo";

            $stmtUpdate = $conn->prepare("UPDATE alertas 
                                          SET cantidad = ?,
                                              mensaje = ?,
                                              fecha = CURRENT_TIMESTAMP
                                          WHERE estudiante_id = ?
                                          AND tipo = ?");
            $stmtUpdate->bind_param("isis", $total_faltas_tipo, $mensaje, $estudiante_id, $tipo);
            $stmtUpdate->execute();
        }
    }

    header("Location: /recreo/controllers/faltas.php?action=index");
    exit;
}

// -------------------------------------------------------------
// FORMULARIO EDITAR
// -------------------------------------------------------------

if ($action == "edit") {

    // Validar que existe el ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        die("ID inválido");
    }

    $id = (int)$_GET['id'];

    // ✅ Consulta preparada
    $stmt = $conn->prepare("SELECT * FROM faltas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $falta = $result->fetch_assoc();

    if (!$falta) {
        die("Falta no encontrada");
    }

    $estudiantes = $conn->query("SELECT * FROM estudiantes");

    include "../views/faltas/edit.php";
    exit;
}

// -------------------------------------------------------------
// ACTUALIZAR FALTA
// -------------------------------------------------------------
if ($action == "update") {

    // Validar datos
    if (empty($_POST['id']) || empty($_POST['estudiante_id']) || empty($_POST['tipo']) || empty($_POST['descripcion'])) {
        die("Todos los campos son obligatorios");
    }

    $id = (int)$_POST['id'];
    $estudiante_id = (int)$_POST['estudiante_id'];
    $tipo = $_POST['tipo'];
    $descripcion = $_POST['descripcion'];

    // ✅ Consulta preparada
    $stmt = $conn->prepare("UPDATE faltas SET estudiante_id = ?, tipo = ?, descripcion = ? WHERE id = ?");
    $stmt->bind_param("issi", $estudiante_id, $tipo, $descripcion, $id);

    if ($stmt->execute()) {
        header("Location: /recreo/controllers/faltas.php?action=index");
        exit;
    } else {
        die("Error al actualizar la falta");
    }
}

// -------------------------------------------------------------
// ELIMINAR FALTA
// -------------------------------------------------------------
if ($action == "delete") {

    // Validar ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        die("ID inválido");
    }

    $id = (int)$_GET['id'];

    // ✅ Consulta preparada
    $stmt = $conn->prepare("DELETE FROM faltas WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: /recreo/controllers/faltas.php?action=index");
        exit;
    } else {
        die("Error al eliminar la falta");
    }
}


?>

