<!-- <?php session_start(); ?> -->
<!DOCTYPE html>
<html>
<head>
    <title>Consolidado del Estudiante</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        h2 { margin-bottom: 5px; }
        table { width: 60%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        a.btn { padding: 8px 12px; background: #0066cc; color: #fff; text-decoration: none; border-radius: 4px; }
        a.btn:hover { background: #004c99; }
    </style>
</head>
<body>

<h2>Consolidado del Estudiante</h2>

<p><strong>Nombre:</strong> <?php echo $estudiante['nombre']; ?></p>
<!-- <p><strong>Documento:</strong> <?php echo $estudiante['documento']; ?></p> -->

<h3>Faltas Registradas</h3>
<table>
    <tr>
        <th>Tipo de Falta</th>
        <th>Total</th>
    </tr>

    <?php foreach ($resumen_faltas as $fila): ?>
        <tr>
            <td><?php echo $fila['tipo']; ?></td>
            <td><?php echo $fila['total']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h3>Alertas Generadas</h3>
<table>
    <tr>
        <th>Tipo</th>
        <th>Cantidad</th>
        <th>Fecha</th>
    </tr>

    <?php foreach ($alertas as $al): ?>
        <tr>
            <td><?php echo $al['tipo']; ?></td>
            <td><?php echo $al['cantidad']; ?></td>
            <td><?php echo $al['fecha']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<br>

<!-- Botones para reportes -->
<a class="btn" href="../../controllers/reportes.php?tipo=pdf_estudiante&id=<?php echo $estudiante['id']; ?>">Descargar PDF Individual</a>
<a class="btn" href="../../controllers/reportes.php?tipo=excel_estudiante&id=<?php echo $estudiante['id']; ?>">Descargar Excel Individual</a>


<br><br>
<a href="javascript:history.back();" class="btn">Volver</a>

</body>
</html>
