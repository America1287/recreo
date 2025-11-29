<h2>Listado de Estudiantes</h2>

<a href="/recreo/controllers/estudiantes.php?action=create"> Nuevo Estudiante</a>

<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Grado</th>
        <th>Acciones</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['grado']) ?></td>
            <td>
                <a href="/recreo/controllers/estudiantes.php?action=edit&id=<?= $row['id'] ?>">✏ Editar</a>
                <br>
                <a href="/recreo/controllers/estudiantes.php?action=delete&id=<?= $row['id'] ?>"
                   onclick="return confirm('¿Eliminar este estudiante?')">🗑 Eliminar</a>
                <br>
                <a href="/recreo/controllers/estudiantes.php?action=consolidado&id=<?= $row['id'] ?>">Consolidado</a>



            </td>
        </tr>
    <?php endwhile ?>
</table>

 <br><a href="/recreo/views/dashboard.php">⬅ Volver</a>