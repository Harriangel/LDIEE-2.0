<?php
// Comienzar sesion
session_start();
// Conexion con la base de datos
require_once "pdo.php";
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Regresar a la pagina principal
Back('admin.php');
// Rececepcion de datos del formulario
if ( isset($_POST['name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['document'])
 && isset($_POST['ti_un']) && isset($_POST['username'])  && isset($_POST['pass']) && !isset($_POST['back'])){
    // Revision de errores
    $msg = ValidatePOSTStudent();
    if (is_string($msg)){
        $_SESSION['error']=$msg;
        header('location:add_users.php');
        return;
    }
    // Agregar administrador
    if ($_POST['role'] == 'admins'){
        if (!isset($_POST['admin_id']) || $_POST['admin_id']<1){
            $_SESSION['error']='admin_id requerido';
            header('location:add_users.php');
            return;
        }
        $sql = 'INSERT INTO '.$_POST['role'].'(name, last_name, email, document_type, 
        document, ti_un, admin_id, username, pass) VALUES (:n, :l_n, :e, 
        :d_t, :d, :t_u, :a_i, :u, :p);';    
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':n' => $_POST['name'],
            ':l_n' => $_POST['last_name'],
            ':e' => $_POST['email'],
            ':d_t' => $_POST['document_type'],
            ':d' => $_POST['document'],
            ':t_u' => $_POST['ti_un'],
            ':a_i' => $_POST['admin_id'],
            ':u' => $_POST['username'],
            ':p' => $_POST['pass']));      
    }
    // Agregar estudiante o profesor
    else{
        $sql = 'INSERT INTO '.$_POST['role'].'(name, last_name, email, document_type, 
        document, ti_un, username, pass) VALUES (:n, :l_n, :e, :d_t, :d, :t_u, 
        :u, :p);';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':n' => $_POST['name'],
            ':l_n' => $_POST['last_name'],
            ':e' => $_POST['email'],
            ':d_t' => $_POST['document_type'],
            ':d' => $_POST['document'],
            ':t_u' => $_POST['ti_un'],
            ':u' => $_POST['username'],
            ':p' => $_POST['pass']));
    }
    $_SESSION['success'] = 'Usuario agregado';
    header( 'Location:add_users.php' ) ;
    return;        
}


?>

<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo3.png">
    <meta charset="utf-8">
    <title>Agregar Usuarios</title>
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
    <h2>Agregar usuarios</h2>
    <!-- Imprimir errores -->
    <?php ShowMessages(); ?>
    <form method="post">
        <p><label for="role">Rol: </label></p>
        <select name="role" class="form-select" id="">
            <option value="0">--Seleccionar--</option>
            <option value="students">Estudiante</option>
            <option value="teachers">Profesor</option>
            <option value="admins">Administrator</option>
        </select>
        <p><label for="name">Nombre: </label></p>
        <input type="text" name="name" id="" class="form-control" placeholder="Ingrese los nombres del usuario">
        <p><label for="last_name">Apellidos: </label></p>
        <input type="text" name="last_name" id="" class="form-control" placeholder="Ingrese los apellidos del usuario">
        <p><label for="email">Correo institucional: </label></p>
        <input type="text" name="email" id="" class="form-control" placeholder="Ingrese el correo universitario">
        <p><label for="document_type">Tipo de documento:</label></p>
        <select name="document_type" id="" class="form-select">
            <option value="0">--Seleccionar--</option>
            <option value="cedula">Cedula de ciudadania</option>
            <option value="tarjeta de indentidad">Tarjeta de identidad</option>
            <option value="cedula extanjera">Cedula de extranjeria</option>
        </select>
        <p><label for="document">Numero de documento: </label></p>
        <input type="text" name="document" id="" class="form-control" placeholder="Ingrese el Numero de documento">
        <p><label for="ti_un">Codigo TI UN: </label></p>
        <input type="text" name="ti_un" id="" class="form-control" placeholder="Ingrese el codigo universitario">
        <p><label for="admin_id">Codigo Administrador: </label></p>
        <input type="text" name="admin_id" id="" class="form-control" placeholder="Ingrese el codigo del administrador">
        <p><label for="username">Usuario: </label></p>
        <input type="text" name="username" id="" class="form-control" placeholder="Ingrese el usuario sin @unal.edu.co">
        <p><label for="pass">Contraseña: </label></p>
        <input type="text" name="pass" id="" class="form-control" placeholder="Ingrese la contraseña"></p>
        <p><input type="submit" class="btn btn-success" value="Agregar">
        <input type="submit" name="back" class="btn btn-light" value="Regresar"></p>
    </form>  
</div>
</form>
</section>
    <?php require_once 'footer.html'; ?>  
</body>
</html>