<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol_id'];
?>

<h2>Bienvenido, <?= htmlspecialchars($nombre) ?></h2>
<p>Rol: 
    <?php
        if ($rol == 1) echo "Administrador";
        elseif ($rol == 2) echo "Docente";
        elseif ($rol == 3) echo "Director";
        elseif ($rol == 4) echo "Estudiante";
    ?>
</p>

<hr>

<!-- MENÚ DINÁMICO SEGÚN ROL -->
<?php if ($rol == 1): ?>
    <h3>Menú Administrador</h3>
    <a href="../controllers/estudiantes.php?action=index">Gestionar Estudiantes</a><br>
    <a href="#">Gestionar Usuarios (próximamente)</a><br>
    <a href="/recreo/controllers/faltas.php?action=index">Gestionar Faltas</a><br>
    <a href="../controllers/alertas.php?action=index">Ver Alertas</a><br>

<?php elseif ($rol == 2): ?>
    <h3>Menú Docente</h3>
    <a href="../controllers/estudiantes.php?action=index">Ver Estudiantes</a><br>
    <a href="/recreo/controllers/faltas.php?action=index">Registrar Faltas</a><br>
    <a href="../controllers/alertas.php?action=index">Ver Alertas</a><br>

<?php elseif ($rol == 3): ?>
    <h3>Menú Director</h3>
    <a href="../controllers/estudiantes.php?action=index">Ver Estudiantes</a><br>
    <a href="#">Reportes de Faltas (próximamente)</a><br>

<?php elseif ($rol == 4): ?>
    <h3>Menú Estudiante</h3>
    <a href="#">Mi historial de faltas (próximamente)</a><br>

<?php endif; ?>

<!-- SECCIÓN DE ALERTAS (Solo para Admin, Docente y Director) -->
<?php if ($_SESSION['rol_id'] == 1 || $_SESSION['rol_id'] == 2 || $_SESSION['rol_id'] == 3): ?>

<hr>
<h2>Alertas Generadas</h2>

<!-- ✅ FILTRO POR CATEGORÍA -->
<form method="GET" action="">
    <label>Filtrar por categoría:</label>
    <select name="tipo" onchange="this.form.submit()">
        <option value="">Todas</option>
        <option value="Tipo 1" <?= (isset($_GET['tipo']) && $_GET['tipo']=='Tipo 1')?'selected':'' ?>>Tipo 1</option>
        <option value="Tipo 2" <?= (isset($_GET['tipo']) && $_GET['tipo']=='Tipo 2')?'selected':'' ?>>Tipo 2</option>
        <option value="Tipo 3" <?= (isset($_GET['tipo']) && $_GET['tipo']=='Tipo 3')?'selected':'' ?>>Tipo 3</option>
    </select>
</form>
<br>

<form method="GET" action="dashboard.php">
    <label>Tipo:</label>
    <select name="tipo">
        <option value="">Todos</option>
        <option value="Tipo 1">Tipo 1</option>
        <option value="Tipo 2">Tipo 2</option>
        <option value="Tipo 3">Tipo 3</option>
    </select>

    <label>Desde:</label>
    <input type="date" name="fecha_inicio">

    <label>Hasta:</label>
    <input type="date" name="fecha_fin">

    <button type="submit">Filtrar</button>
</form>


<?php
// ✅ Cargar conexión y funciones
require_once "../config/database.php";
require_once "../config/functions.php";

// ✅ Obtener filtro y alertas usando la función
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
// $alertas = getAlertas($conn, $tipo);

$tipo = $_GET['tipo'] ?? null;
$fecha_inicio = $_GET['fecha_inicio'] ?? null;
$fecha_fin = $_GET['fecha_fin'] ?? null;

$alertas = getAlertasPorFiltro($conn, $tipo, $fecha_inicio, $fecha_fin);


if ($alertas->num_rows > 0):
?>
    <table border="1" cellspacing="0" cellpadding="5">
        <tr>
            <th>Estudiante</th>
            <th>Tipo</th>
            <th>Mensaje</th>
            <th>Fecha</th>
        </tr>

        <?php while($a = $alertas->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($a['nombre']) ?></td>
                <td><?= htmlspecialchars($a['tipo']) ?></td>
                <td><?= htmlspecialchars($a['mensaje']) ?></td>
                <td><?= htmlspecialchars($a['fecha']) ?></td>
            </tr>
        <?php endwhile; ?>

    </table>

<?php else: ?>
    <p>No hay alertas generadas<?= $tipo ? ' para la categoría seleccionada' : '' ?>.</p>
<?php endif; ?>

<?php endif; ?>

<?php
    // Obtener filtros activos
    $categoria = isset($_GET['tipo']) ? $_GET['tipo'] : '';
    $desde     = isset($_GET['desde']) ? $_GET['desde'] : '';
    $hasta     = isset($_GET['hasta']) ? $_GET['hasta'] : '';
?>

<a href="../controllers/reportes.php?tipo=pdf&categoria=<?php echo $categoria ?>&desde=<?php echo $desde ?>&hasta=<?php echo $hasta ?>" class="btn">
    Descargar PDF
</a>
<br>

<a href="../controllers/reportes.php?tipo=excel&categoria=<?php echo $categoria ?>&desde=<?php echo $desde ?>&hasta=<?php echo $hasta ?>" class="btn">
    Descargar Excel
</a>
<br>

<br>
<a href="../controllers/logout.php">Cerrar sesión</a>