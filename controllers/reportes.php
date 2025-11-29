<?php
require_once __DIR__ . '/../config/db.php';

class ReportesController {
    
    // Obtener faltas (con filtros opcionales)
    public static function obtenerFaltas($categoria = null, $desde = null, $hasta = null)
    {
        global $conexion;
        $sql = "SELECT f.*, e.nombre AS estudiante_nombre 
                FROM faltas f 
                JOIN estudiantes e ON f.estudiante_id = e.id
                WHERE 1";

        if ($categoria) {
            $sql .= " AND f.tipo = '$categoria'";
        }

        if ($desde && $hasta) {
            $sql .= " AND DATE(f.fecha) BETWEEN '$desde' AND '$hasta'";
        }

        $sql .= " ORDER BY f.fecha DESC";

        return $conexion->query($sql);
    }

}

// ================================================
//  GENERAR PDF
// ================================================
if (isset($_GET['tipo']) && $_GET['tipo'] == 'pdf') {

    require '../public/libs/fpdf/fpdf.php';

    $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
    $desde     = isset($_GET['desde']) ? $_GET['desde'] : null;
    $hasta     = isset($_GET['hasta']) ? $_GET['hasta'] : null;


    $resultado = ReportesController::obtenerFaltas($categoria, $desde, $hasta);

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(190,10,"Reporte de Faltas",0,1,'C');

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(60,10,"Estudiante",1);
    $pdf->Cell(30,10,"Tipo",1);
    $pdf->Cell(50,10,"Fecha",1);
    $pdf->Cell(50,10,"Detalle",1);
    $pdf->Ln();

    $pdf->SetFont('Arial','',10);

    while($fila = $resultado->fetch_assoc()){
        $pdf->Cell(60,10,$fila['estudiante_nombre'],1);
        $pdf->Cell(30,10,$fila['tipo'],1);
        $pdf->Cell(50,10,$fila['fecha'],1);
        $pdf->Cell(50,10,$fila['detalle'],1);
        $pdf->Ln();
    }

    $pdf->Output();
    exit;
}

// ================================================
//  GENERAR EXCEL (CSV)
// ================================================
if (isset($_GET['tipo']) && $_GET['tipo'] == 'excel') {

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=reporte_faltas.csv");

    $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
    $desde     = isset($_GET['desde']) ? $_GET['desde'] : null;
    $hasta     = isset($_GET['hasta']) ? $_GET['hasta'] : null;


    $resultado = ReportesController::obtenerFaltas($categoria, $desde, $hasta);

    $output = fopen("php://output", "w");
    
    fputcsv($output, ["Estudiante","Tipo","Fecha","Detalle"]);

    while($fila = $resultado->fetch_assoc()){
        fputcsv($output, [
            $fila['estudiante_nombre'],
            $fila['tipo'],
            $fila['fecha'],
            $fila['detalle']
        ]);
    }

    fclose($output);
    exit;
}

?>
