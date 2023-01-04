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
    $msg = ValidateUser();
    if (is_string($msg)){
        $_SESSION['error'] = $msg;
        header('location:login_student.php'); 
        return;
    }
    $student = LoadUser($pdo, 'students');
    if ($student == false){
        $_SESSION['error'] = 'Usuario o contraseña incorrecta';
        header('location:login_student.php');
        return;
    }
    $_SESSION['student_id']=$student['ti_un'];
    $_SESSION['name']=$student['name'];
    $_SESSION['log_in']='Logged In';
    header('location:student.php');
    return;
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/shield.png">
    <meta charset="utf-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Inicio de Sesión Estudiante</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo_estudiante.css'>
    <script src='main.js'></script>
</head>
<body>
<section class="form-register">
    <form method="post">
    <div align="center">
    <h2>Inicio de Sesión</h2>
    <?=ShowMessages();?>
    <form method="post">
        <p><label class="form-label" for="username">Usuario:</label></p>
        <input type="text" name="username" id="" class="form-control" placeholder="Ingrese su usuario sin @unal.edu.co">
        <p><label class="form-label" for="username">Contraseña:</label></p>
        <input type="password" name="password" id="" class="form-control" placeholder="*******"></p>
        <p><input type="submit" value="Ingresar" class="btn btn-success">
        <input type="submit" name="back" value="Regresar" class="btn btn-secondary"></p>
    </form>
    </form>
    </section>
</body>
</html>