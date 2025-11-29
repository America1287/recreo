<?php
/**
 * Funciones reutilizables del sistema Recreo
 */

/**
 * Obtener alertas con filtro opcional
 * @param mysqli $conn Conexión a la base de datos
 * @param string $tipo Tipo de falta para filtrar (opcional)
 * @return mysqli_result Resultado de la consulta
 */
function getAlertas($conn, $tipo = '') {
    
    if ($tipo != '') {
        // Con filtro - Consulta preparada
        $stmt = $conn->prepare("
            SELECT alertas.*, estudiantes.nombre 
            FROM alertas
            INNER JOIN estudiantes ON alertas.estudiante_id = estudiantes.id
            WHERE alertas.tipo = ?
            ORDER BY alertas.fecha DESC
        ");
        $stmt->bind_param("s", $tipo);
        $stmt->execute();
        return $stmt->get_result();
    } else {
        // Sin filtro - Todas las alertas
        return $conn->query("
            SELECT alertas.*, estudiantes.nombre 
            FROM alertas
            INNER JOIN estudiantes ON alertas.estudiante_id = estudiantes.id
            ORDER BY alertas.fecha DESC
        ");
    }
}

/**
 * Obtener estudiantes
 * @param mysqli $conn Conexión a la base de datos
 * @return mysqli_result Resultado de la consulta
 */
function getEstudiantes($conn) {
    return $conn->query("SELECT * FROM estudiantes ORDER BY nombre ASC");
}

/**
 * Obtener faltas con filtro opcional
 * @param mysqli $conn Conexión a la base de datos
 * @param string $tipo Tipo de falta para filtrar (opcional)
 * @return mysqli_result Resultado de la consulta
 */
function getFaltas($conn, $tipo = '') {
    
    if ($tipo != '') {
        $stmt = $conn->prepare("
            SELECT f.*, e.nombre AS estudiante_nombre 
            FROM faltas f 
            JOIN estudiantes e ON f.estudiante_id = e.id 
            WHERE f.tipo = ?
            ORDER BY f.fecha DESC
        ");
        $stmt->bind_param("s", $tipo);
        $stmt->execute();
        return $stmt->get_result();
    } else {
        return $conn->query("
            SELECT f.*, e.nombre AS estudiante_nombre 
            FROM faltas f 
            JOIN estudiantes e ON f.estudiante_id = e.id
            ORDER BY f.fecha DESC
        ");
    }
}

function getAlertasPorFiltro($conn, $tipo = null, $fecha_inicio = null, $fecha_fin = null) {
    $sql = "SELECT a.*, e.nombre AS nombre 
            FROM alertas a
            JOIN estudiantes e ON a.estudiante_id = e.id
            WHERE 1=1";

    if ($tipo) {
        $sql .= " AND a.tipo = '$tipo'";
    }

    if (!empty($fecha_inicio)) {
        $sql .= " AND DATE(a.fecha) >= '$fecha_inicio'";
    }

    if (!empty($fecha_fin)) {
        $sql .= " AND DATE(a.fecha) <= '$fecha_fin'";
    }

    $sql .= " ORDER BY a.fecha DESC";

    return $conn->query($sql);
}

?>