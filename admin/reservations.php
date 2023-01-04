<?php
// Comenzar sesion
session_start();
// Conexion con la base de datos
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Envio modalidad
if (isset($_POST['modalidad'])){
    if ($_POST['modalidad'] == '0'){
        unset($_SESSION['modalidad']);
        $_SESSION['error'] = 'Debe seleccionar una modalidad';
        header('location:reservations.php');
        return;
    }
    $_SESSION['modalidad']=$_POST['modalidad'];
    header('location:reservations.php');
    return;
}
// Envio ti_un
if (isset($_POST['ti_un']) && isset($_POST['document']) && !isset($_POST['back'])){
    // Validar codigo 
    $msg = ValidateDocument();
    if (is_string($msg)){
        $_SESSION['error']=$msg;
        header('location:reservations.php');
        return;
    }
    // Cargar ti_un
    Loadti_un($pdo);
    // Cargar reserva
    $request = LoadRequestStudent($pdo, 'Sin Activar');
    if ($request == false){
        $_SESSION['error']='El estudiante no tiene ninguna reserva activa';
        header('location:reservations.php');
        return;
    }
    $_SESSION['success']='Reserva encontrada';
    $_SESSION['ti_un'] = $_POST['ti_un'];
    header('location:reservations.php');
    return;     
}
// Regresar a reservas
Back('reservations.php');
// Cargar las reservas
$requests = LoadRequests($pdo, 'Sin Activar');
$requests_clase = LoadRequestsMode($pdo, 'Sin Activar', 'Clase');
$requests_libre = LoadRequestsMode($pdo, 'Sin Activar', 'Practica Libre');
$requests_casa = LoadRequestsMode($pdo, 'Sin Activar', 'Casa');
// Mantener modalidad
$clase = (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Clase') ? 'selected' : false;
$libre = (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Practica Libre') ? 'selected' : false;
$casa = (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Casa') ? 'selected' : false;
?>
<!DOCTYPE html>
<html lang="en">
<html>
<head>
    <link rel="icon" href="images/escudo3.png">
    <meta charset="utf-8">
    <title>Reservas activas</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo.css'>
    <script src='main.js'></script>
</head>
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_admin.htm'; ?>
<section class="form-register">
<form method="post">
  <div align="center">
    <h2>Reservas</h2></div>
    <form method="post">

    <p><label class="form-label" for="modalidad">Modalidad: </label></p>
     
    <div class="input-group mb-3">
    <select name="modalidad" class="form-select" id=""></p>
            <option value="0">-- Seleccione una modalidad --</option>
            <option value="Clase" <?=$clase?>>Clase</option>
            <option value="Practica Libre" <?=$libre?>>Practica Libre</option>
            <option value="Casa" <?=$casa?>>Casa</option>
            </select>
            <input type="submit" class="btn btn-success" value="Seleccionar">
  </div>
    </form>
  <form method="post">
    <div align="center"><h6>A continuacion realice la busqueda con alguna de las opciones:</h6><div>
        <p><label for="ti_un">Codigo TI UN:</label></p>
        <input type="text" class="form-control" name="ti_un" placeholder="Ingrese el codigo universitario">
        <p><label for="document">Cedula:</label></p>
        <input type="text"  class="form-control" placeholder="Ingrese el numero de documento" name="document"></p>
        <p><input type="submit" name="libre" class="btn btn-success" value="Buscar">
        <input type="submit" name="back" class="btn btn-light" value="Regresar"></p>
    </form>
    <?php
    ShowMessages();
    if (!isset($_SESSION['modalidad'])){
        if (count($requests)>0){
            // Reservas
            echo '<p><table class="table table-dark table-hover">';
            echo '<tr><th>Fecha de solicitud</th>';
            echo '<th>Modalidad</th>';
            echo '<th>Curso</th>';
            echo '<th>Fecha</th>';
            echo '<th>Fecha Inicial</th>';
            echo '<th>Fecha Final</th>';
            echo '<th>Hora Inicial</th>';
            echo '<th>Hora Final</th>';
            echo '<th>Opciones</th></tr>';
            $sql = 'SELECT * FROM requests WHERE estado="Sin Activar"';
            // Reservas del usuario
            if (isset($_SESSION['ti_un'])){
                $sql = $sql.' AND ti_un = :id ORDER BY fecha DESC';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(":id"=>$_SESSION['ti_un']));
                unset($_SESSION['ti_un']);
            }
            // Todas las reservas
            else{
                $sql = $sql.' ORDER BY fecha DESC';
                $stmt = $pdo->query($sql);
            }
            while ( $request = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $id = $request['request_id'];
                $ti_un = $request['ti_un'];
                if ($request['modalidad']=='Clase' || $request['modalidad']=='Practica Libre'){
                    $request['fecha_ini'] = '--';
                    $request['fecha_fin'] = '--';
                }
                if ($request['modalidad']=='Casa'){
                    $request['fecha'] = '--';
                    $request['hora_ini'] = '--';
                    $request['hora_fin'] = '--';
                }
                echo '<tr>';
                echo '<td>'.$request['fecha_solicitud'].'</td>';
                echo '<td>'.$request['modalidad'].'</td>';
                echo '<td>'.$request['curso'].'</td>';
                echo '<td>'.$request['fecha'].'</td>';
                echo '<td>'.$request['fecha_ini'].'</td>';
                echo '<td>'.$request['fecha_fin'].'</td>'; 
                echo '<td>'.$request['hora_ini'].'</td>';
                echo '<td>'.$request['hora_fin'].'</td>';    
                echo '<td><a href="activate_request.php?request_id='.$id.'&ti_un='.$ti_un.'">Activar</a></td>';
                echo '</tr>';
            }
            echo '</table></p>';
        }else{
            echo '<p style="color:yellow;">En este momento no hay reservas activas</p>';
        }
    }
    if (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Clase'){
        if (count($requests_clase)>0){
            // Reservas
            echo '<p><table class="table table-dark table-hover">';
            echo '<tr><th>Fecha de solicitud</th>';
            echo '<th>Modalidad</th>';
            echo '<th>Curso</th>';
            echo '<th>Fecha</th>';
            echo '<th>Hora Inicial</th>';
            echo '<th>Hora Final</th>';
            echo '<th>Opciones</th></tr>';
            $sql = 'SELECT * FROM requests WHERE estado="Sin Activar" AND modalidad="Clase"';
            // Reservas del usuario
            if (isset($_SESSION['ti_un'])){
                $sql = $sql.' AND ti_un = :id ORDER BY fecha DESC';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(":id"=>$_SESSION['ti_un']));
                unset($_SESSION['ti_un']);
            }
            // Todas las reservas
            else{
                $sql = $sql.' ORDER BY fecha DESC';
                $stmt = $pdo->query($sql);
            }
            while ( $request = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $id = $request['request_id'];
                $ti_un = $request['ti_un'];
                echo '<tr>';
                echo '<td>'.$request['fecha_solicitud'].'</td>';
                echo '<td>'.$request['modalidad'].'</td>';
                echo '<td>'.$request['curso'].'</td>';
                echo '<td>'.$request['fecha'].'</td>';
                echo '<td>'.$request['hora_ini'].'</td>';
                echo '<td>'.$request['hora_fin'].'</td>';    
                echo '<td><a href="activate_request.php?request_id='.$id.'&ti_un='.$ti_un.'">Activar</a></td>';
                echo '</tr>';
            }
            echo '</table></p>';
        }else{
            echo '<p style="color:yellow;">En este momento no hay reservas activas para clase</p>';
        }       
    }
    if (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Practica Libre'){
        if (count($requests_libre)>0){
            // Reservas
            echo '<p><table class="table table-dark table-hover">';
            echo '<tr><th>Fecha de solicitud</th>';
            echo '<th>Modalidad</th>';
            echo '<th>Curso</th>';
            echo '<th>Fecha</th>';
            echo '<th>Hora Inicial</th>';
            echo '<th>Hora Final</th>';
            echo '<th>Opciones</th></tr>';
            $sql = 'SELECT * FROM requests WHERE estado="Sin Activar" AND modalidad="Practica Libre"';
            // Reservas del usuario
            if (isset($_SESSION['ti_un'])){
                $sql = $sql.' AND ti_un = :id ORDER BY fecha DESC';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(":id"=>$_SESSION['ti_un']));
                unset($_SESSION['ti_un']);
            }
            // Todas las reservas
            else{
                $sql = $sql.' ORDER BY fecha DESC';
                $stmt = $pdo->query($sql);
            }
            while ( $request = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $id = $request['request_id'];
                $ti_un = $request['ti_un'];
                echo '<tr>';
                echo '<td>'.$request['fecha_solicitud'].'</td>';
                echo '<td>'.$request['modalidad'].'</td>';
                echo '<td>'.$request['curso'].'</td>';
                echo '<td>'.$request['fecha'].'</td>';
                echo '<td>'.$request['hora_ini'].'</td>';
                echo '<td>'.$request['hora_fin'].'</td>';    
                echo '<td><a href="activate_request.php?request_id='.$id.'&ti_un='.$ti_un.'">Activar</a></td>';
                echo '</tr>';
            }
            echo '</table></p>';
        }else{
            echo '<p style="color:yellow;">En este momento no hay reservas activas para practica libre</p>';
        }
        
    }
    if (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Casa'){
        if (count($requests_casa)>0){
            echo '<p><table class="table table-dark table-hover">';
            echo '<tr><th>Fecha de solicitud</th>';
            echo '<th>Modalidad</th>';
            echo '<th>Curso</th>';
            echo '<th>Fecha Inicial</th>';
            echo '<th>Fecha Final</th>';
            echo '<th>Opciones</th></tr>';
            $sql = 'SELECT * FROM requests WHERE estado = "Sin Activar" AND modalidad="Casa"';
            // reservas del usuario
            if (isset($_SESSION['ti_un'])){
                $sql = $sql.' AND ti_un = :id ORDER BY fecha_ini DESC';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(":id"=>$_SESSION['ti_un']));
                unset($_SESSION['ti_un']);
            }
            // Todos los reservas
            else{
                $sql = $sql.' ORDER BY fecha_ini DESC';
                $stmt = $pdo->query($sql);
            }
            while ( $request = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $id = $request['request_id'];
                $ti_un = $request['ti_un'];
                echo '<tr>';
                echo '<td>'.$request['fecha_solicitud'].'</td>';
                echo '<td>'.$request['modalidad'].'</td>';
                echo '<td>'.$request['curso'].'</td>';
                echo '<td>'.$request['fecha_ini'].'</td>';
                echo '<td>'.$request['fecha_fin'].'</td>'; 
                echo '<td><a href="activate_request.php?request_id='.$id.'&ti_un='.$ti_un.'">Activar</a></td>';
                echo '</tr>';
            }
            echo '</table></p>';
        }else{
            echo '<p style="color:yellow;">En este momento no hay reservas activas para la casa</p>';
        }
        
    }
    ?>
    </form>
</section>
    <?php require_once 'footer.html'; ?>  
</body>
</html>
