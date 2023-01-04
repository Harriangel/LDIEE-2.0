<?php
// Comenzar sesion
session_start();
// Conexion con la base de datos
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Envio Modalidad
if (isset($_POST['modalidad'])){
    if ($_POST['modalidad'] == '0'){
        unset($_SESSION['modalidad']);
        $_SESSION['error'] = 'Debe seleccionar una modalidad';
        header('location:requests.php');
        return;
    }
    $_SESSION['modalidad']=$_POST['modalidad'];
    header('location:requests.php');
    return;
}
// Envio ti_un
if (isset($_POST['ti_un']) && isset($_POST['document']) && !isset($_POST['back'])){
    // Validar codigo 
    $msg = ValidateDocument();
    if (is_string($msg)){
        unset($_SESSION['ti_un']);
        $_SESSION['error']=$msg;
        header('location:requests.php');
        return;
    }
    // Cargar ti_un
    Loadti_un($pdo);
    // Cargar prestamo
    $request = LoadRequestStudent($pdo, 'Activo');
    if ($request == false){
        $_SESSION['error']='El estudiante no tiene ningun prestamo activo';
        header('location:requests.php');
        return;
    }
    $_SESSION['success']='Prestamo encontrado';
    $_SESSION['ti_un'] = $_POST['ti_un'];
    header('location:requests.php');
    return;     
}
// Regresar a prestamos
Back('requests.php');
// Cargar los prestamos
$requests = LoadRequests($pdo, 'Activo');
$requests_clase = LoadRequestsMode($pdo, 'Activo', 'Clase');
$requests_libre = LoadRequestsMode($pdo, 'Activo', 'Practica Libre');
$requests_casa = LoadRequestsMode($pdo, 'Activo', 'Casa');
// Mantener modalidad
$clase = (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Clase') ? 'selected' : false;
$libre = (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Practica Libre') ? 'selected' : false;
$casa = (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Casa') ? 'selected' : false;

?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo3.png">
    <meta charset="utf-8">
    <title>Prestamos activos</title>
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
    <h2>Prestamos</h2></div>
    <form method="post">
        <p><label class="form-label" for="modalidad">Modalidad: </label></p>
        <div class="input-group mb-3">
      <select name="modalidad" class="form-select" id=""></p>
            <option value="0">-- Seleccione una modalidad --</option>
            <option value="Clase" <?=$clase?>>Clase</option>
            <option value="Practica Libre" <?=$libre?>>Practica Libre</option>
            <option value="Casa" <?=$casa?>>Casa</option>
            </select>
            <input type="submit" class="btn btn-success" value="Seleccionar">        </div>
    </form>
    <form method="post">
<div align="center"><h6>A continuacion realice la busqueda con alguna de las opciones:</h6><div>
    <p><label for="ti_un">Código TI UN:</label></p>
        <input class="form-control" type="text" name="ti_un" placeholder="Ingrese el codigo universitario">
        <p><label for="document">Cedula:</label></p>
        <input class="form-control" type="text" name="document" placeholder="Ingrese el numero de documento"></p>
        <p><input type="submit" class="btn btn-success" value="Buscar">
        <input type="submit" class="btn btn-light" name="back" value="Regresar"></p>
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
            $sql = 'SELECT * FROM requests WHERE estado="Activo"';
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
                echo '<td><a href="desactivate_request.php?request_id='.$id.'&ti_un='.$ti_un.'">Opciones</a></td>';
                echo '</tr>';
            }
            echo '</table></p>';
        }else{
            echo '<p style="color:yellow;">En este momento no hay prestamos activos</p>';
        }
    }
    if (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Clase'){
        if (count($requests_clase)>0){
            // Prestamos
            echo '<p><table class="table table-dark table-hover">';
            echo '<tr><th>Fecha de solicitud</th>';
            echo '<th>Modalidad</th>';
            echo '<th>Curso</th>';
            echo '<th>Fecha</th>';
            echo '<th>Hora Inicial</th>';
            echo '<th>Hora Final</th>';
            echo '<th></th></tr>';
            $sql = 'SELECT * FROM requests WHERE estado="Activo" AND modalidad="Clase"';
            // Prestamos del usuario
            if (isset($_SESSION['ti_un'])){
                $sql = $sql.' AND ti_un = :id ORDER BY fecha DESC';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(":id"=>$_SESSION['ti_un']));
                unset($_SESSION['ti_un']);
            }
            // Todos los prestamos
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
                echo '<td><a href="desactivate_request.php?request_id='.$id.'&ti_un='.$ti_un.'">Opciones</a></td>';
                echo '</tr>';
            }
            echo '</table></p>';
        }else{
            echo '<p style="color:yellow;">En este momento no hay prestamos activos para clase</p>';
        }
        
    }
    if (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Practica Libre'){
        if (count($requests_libre)>0){
            // Prestamos
            echo '<p><table class="table table-dark table-hover">';
            echo '<tr><th>Fecha de solicitud</th>';
            echo '<th>Modalidad</th>';
            echo '<th>Curso</th>';
            echo '<th>Fecha</th>';
            echo '<th>Hora Inicial</th>';
            echo '<th>Hora Final</th>';
            echo '<th></th></tr>';
            $sql = 'SELECT * FROM requests WHERE estado="Activo" AND modalidad="Practica Libre"';
            // Prestamos del usuario
            if (isset($_SESSION['ti_un'])){
                $sql = $sql.' AND ti_un = :id ORDER BY fecha DESC';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(":id"=>$_SESSION['ti_un']));
                unset($_SESSION['ti_un']);
            }
            // Todos los prestamos
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
                echo '<td><a href="desactivate_request.php?request_id='.$id.'&ti_un='.$ti_un.'">Opciones</a></td>';
                echo '</tr>';
            }
            echo '</table></p>';
        }else{
            echo '<p style="color:yellow;">En este momento no hay prestamos activos para practica libre</p>';
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
            echo '<th></th></tr>';
            $sql = 'SELECT * FROM requests WHERE estado = "Activo" AND modalidad="Casa"';
            // Prestamos del usuario
            if (isset($_SESSION['ti_un'])){
                $sql = $sql.' AND ti_un = :id ORDER BY fecha_ini DESC';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(":id"=>$_SESSION['ti_un']));
                unset($_SESSION['ti_un']);
            }
            // Todos los prestamos
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
                echo '<td><a href="desactivate_request.php?request_id='.$id.'&ti_un='.$ti_un.'">Opciones</a></td>';
                echo '</tr>';
            }
            echo '</table></p>';
        }else{
            echo '<p style="color:yellow;">En este momento no hay prestamos activos para la casa</p>';
        }
        
    }
    ?>
    </form>
</section>
    <?php require_once 'footer.html'; ?>  
</body>
</html>
