<?php
require_once "../config/database.php";
session_start();

// Verificar que el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: /recreo/views/login.php");
    exit;
}

$action = $_GET['action'] ?? 'index';

// --- LISTAR ESTUDIANTES ---
if ($action === 'index') {
    $result = $conn->query("SELECT * FROM estudiantes");
    include "../views/estudiantes/index.php";
}

// --- CREAR ---
if ($action === 'create') {
    include "../views/estudiantes/create.php";
}

if ($action === 'store') {
    // Validar que existen los datos
    if (empty($_POST['nombre']) || empty($_POST['grado'])) {
        die("Todos los campos son obligatorios");
    }
    
    $nombre = $_POST['nombre'];
    $grado = $_POST['grado'];

    // ✅ Usar consultas preparadas (SEGURO)
    $stmt = $conn->prepare("INSERT INTO estudiantes (nombre, grado) VALUES (?, ?)");
    $stmt->bind_param("ss", $nombre, $grado);
    
    if ($stmt->execute()) {
        header("Location: /recreo/controllers/estudiantes.php?action=index");
        exit;
    } else {
        die("Error al crear estudiante");
    }
}

// --- EDITAR ---
if ($action === 'edit') {
    // Validar que existe el ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        die("ID inválido");
    }
    
    $id = (int)$_GET['id'];
    
    // ✅ Consulta preparada
    $stmt = $conn->prepare("SELECT * FROM estudiantes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $est = $result->fetch_assoc();
    
    if (!$est) {
        die("Estudiante no encontrado");
    }
    
    include "../views/estudiantes/edit.php";
}

if ($action === 'update') {
    // Validar datos
    if (empty($_POST['id']) || empty($_POST['nombre']) || empty($_POST['grado'])) {
        die("Todos los campos son obligatorios");
    }
    
    $id = (int)$_POST['id'];
    $nombre = $_POST['nombre'];
    $grado = $_POST['grado'];

    // ✅ Consulta preparada
    $stmt = $conn->prepare("UPDATE estudiantes SET nombre = ?, grado = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nombre, $grado, $id);
    
    if ($stmt->execute()) {
        header("Location: /recreo/controllers/estudiantes.php?action=index");
        exit;
    } else {
        die("Error al actualizar estudiante");
    }
}

// --- ELIMINAR ---
if ($action === 'delete') {
    // Validar ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        die("ID inválido");
    }
    
    $id = (int)$_GET['id'];
    
    // ✅ Consulta preparada
    $stmt = $conn->prepare("DELETE FROM estudiantes WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: /recreo/controllers/estudiantes.php?action=index");
        exit;
    } else {
        die("Error al eliminar estudiante");
    }
}
// ===============================
// VER CONSOLIDADO DE UN ESTUDIANTE
// ===============================
if ($action === 'consolidado') {
    // asegúrate de estar usando la conexión $conn que viene de config/database.php
    // si al inicio del archivo tienes: require_once "../config/database.php";
    // entonces $conn ya está disponible (mysqli)

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        header("Location: estudiantes.php?action=index");
        exit;
    }

    // Datos del estudiante
    $sqlEst = "SELECT * FROM estudiantes WHERE id = $id";
    $resEst = $conn->query($sqlEst);
    $estudiante = $resEst->fetch_assoc();

    // Faltas por tipo (Tipo 1/2/3)
    $sqlFaltas = "
        SELECT tipo, COUNT(*) AS total
        FROM faltas
        WHERE estudiante_id = $id
        GROUP BY tipo
    ";
    $resFaltas = $conn->query($sqlFaltas);
    $resumen_faltas = [];
    while ($r = $resFaltas->fetch_assoc()) {
        $resumen_faltas[] = $r;
    }

    // Alertas generadas
    $sqlAlertas = "SELECT * FROM alertas WHERE estudiante_id = $id ORDER BY fecha DESC";
    $resAlertas = $conn->query($sqlAlertas);
    $alertas = [];
    while ($a = $resAlertas->fetch_assoc()) {
        $alertas[] = $a;
    }

    include "../views/estudiantes/consolidado.php";
    exit;
}



?>