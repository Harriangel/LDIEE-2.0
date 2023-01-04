<?php
// Comenzar sesion
session_start();
// Conexion con la base de datos 
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Regresar a la pagina principal
Back('http://localhost/proyects/LDIEE/index.php');
// Envio POST
if ( isset($_POST['username']) && isset($_POST['password']) && !isset($_POST['back'])){
    // Error Envio vacio
    $msg = ValidateUser();
    if (is_string($msg)){
        $_SESSION['error'] = $msg;
        header('location:login_admin.php');
        return;
    }
    $admin = LoadUser($pdo, 'admins');
    if ($admin == false){
        $_SESSION['error'] = 'Usuario o contraseña incorrecta';
        header('location:login_admin.php');
        return;
    }
    $_SESSION['log_in'] = 'Logged In';
    $_SESSION['name']=$admin['name'];
    header('location:admin.php');
    return;
}

?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" href="images/escudo3.png">
    <meta charset="utf-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Inicio de Sesión Administrador</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo1.css'>
    <script src='main.js'></script>
</head>
<body>
<section class="form-register">
    <form method="post">
    <div align="center"><h2>Inicio de Sesión</h2></div>
    <?=ShowMessages();?>
        <p><label class="form-label" for="username">Usuario:</label></p>
        <input type="text" name="username" id="" class="form-control" placeholder="Ingrese su usuario sin @unal.edu.co">
        <p><label for="password" class="form-label">Contraseña:</label></p>
        <input type="password" class="form-control" name="password" id="" placeholder="*******"></p>
        <p><input type="submit"  class="btn btn-success" value="Ingresar">
        <input type="submit" name="back" class="btn btn-secondary" value="Regresar"></p>
    </form>
    </section>
</body>
</html>