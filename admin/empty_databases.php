<?php
// Comenzar session
session_start();
// Conexion con la base de datos
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Envio POST
if (isset($_POST['reservations'])){
    $stm = $pdo->query('DELETE FROM requests WHERE estado="Sin Activar"');
    $_SESSION['success']='Reservaciones borradas';
    header('location:empty_databases.php');
    return;
}
if (isset($_POST['requests'])){
    $stm = $pdo->query('DELETE FROM requests WHERE estado="Activo"');
    $_SESSION['success']='Prestamos borrados';
    header('location:empty_databases.php');
    return;
}
if (isset($_POST['debts'])){
    $stm = $pdo->query('DELETE FROM debts WHERE state="Saldada"');
    $_SESSION['success']='Deudas borradas';
    header('location:empty_databases.php');
    return;
}
if (isset($_POST['history'])){
    $stm = $pdo->query('DELETE FROM requests_equipment_codes WHERE state="Desactivado"');
    $stm = $pdo->query('TRUNCATE history');
    $_SESSION['success']='Historial borrado';
    header('location:empty_databases.php');
    return;
}
// Regresar a pagina principal
Back('admin.php');

?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo3.png">
    <meta charset="utf-8">
    <title>Vaciar bases de datos</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo1.css'>
    <script src='main.js'></script>
</head>
<body>
    <!-- Barra de navegación -->
    <?php require_once 'navbar_admin.htm';  ?>
    <section class="form-register">
    <form method="post">
  <div align="center">
    <h2>Vaciar Bases de datos</h2>
    <!-- Imprimir errores -->
    <?php ShowMessages(); ?>
    <form method="post">
        <p><input type="submit" class="btn btn-warning" name ="reservations" value="Reservas">
        <input type="submit" class="btn btn-warning" name= "requests" value="Prestamos">
        <input type="submit" class="btn btn-warning" name="debts" value="Deudas Saldadas">
        <input type="submit" class="btn btn-warning" name="history" value="Historial"></p>
        <p><input type="submit" name="back" class="btn btn-light" value="Regresar"></p>
    </form>
</body>
</html>