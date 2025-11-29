<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: views/login.php");
    exit;
}

echo "Bienvenido, " . $_SESSION['nombre'] . "<br>";

switch ($_SESSION['rol_id']) {

    case 1:
        echo "Eres ADMIN";
        break;

    case 2:
        echo "Eres DOCENTE";
        break;

    case 3:
        echo "Eres DIRECTOR";
        break;

    case 4:
        echo "Eres ESTUDIANTE";
        break;
}

echo "<br><a href='controllers/logout.php'>Cerrar sesión</a>";
?>
