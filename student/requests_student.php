<?php
// Comenzar sesion
session_start();
// Conexion con la base de datos 
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Cargar todos los prestamos
$stm = $pdo->prepare('SELECT * FROM requests WHERE ti_un=:t_u');
$stm->execute(array(':t_u'=>$_SESSION['student_id']));
$requests = $stm->fetchAll(PDO::FETCH_ASSOC);
// Envio POST
if (isset($_POST['course']) && !isset($_POST['back'])){
    // Validar course
    if (strlen($_POST['course']) < 1){
        $_SESSION['error']='Debe escribir un curso';
        header('location:requests_student.php');
        return;
    }
    // Cargar prestamo
    $stm = $pdo->prepare('SELECT * FROM requests WHERE ti_un=:t_u AND curso=:cu');
    $stm->execute(array(':t_u' => $_SESSION['student_id'],':cu' => $_POST['course']));
    $request = $stm->fetch(PDO::FETCH_ASSOC);
    // Validar  prestamo
    if ($request == false){
        $_SESSION['error']='No ha solicitado prestamos para '.$_POST['course'];
        header('location:requests_student.php');
        return;    
    }
    // Mensaje de confirmación
    $_SESSION['success']='Prestamo/s encontrado/s';
    $_SESSION['course'] = $_POST['course'];
    header('location:requests_student.php');
    return;   
}
// Regresar a todos los prestamos
Back('requests_student.php');
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/shield.png">
    <meta charset="utf-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Mis Prestamos</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo_prestamos.css'>
</head>
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_student.html';?>
    <section class="form-register">
  <div align='center'>
  <form method="post">
    <h2>Mis prestamos</h2>
    <!-- Barra de busqueda -->
    <p><form method="post">
        <label for="course">Curso:</label>
        <input type="text" name="course" class="form-control" placeholder="Ingrese el nombre del curso">
        <input type="submit" value="Buscar" class="btn btn-success">
        <input type="submit" name="back" value="Regresar" class="btn btn-light">
    </form></p>
    <?php
    // Imprimir mensajes
    ShowMessages();
    // Validar la exitencia de prestamos
    if (count($requests)>0){
        echo '<p><table class="table table-dark table-hover">
                <tr>
                    <th>Fecha de solicitud</th>
                    <th>Curso</th>    
                    <th>Modalidad</th>
                    <th>Fecha</th>
                    <th>Fecha Inicial</th>
                    <th>Fecha Final</th>
                    <th>Hora Inicial</th>
                    <th>Hora Final</th>
                    <th>Estado</th>
                    <th>Opciones</th>
                </tr>';
        $sql = 'SELECT * FROM requests WHERE ti_un=:t_u';
        // Prestamos de un dia especifico
        if (isset($_SESSION['course'])){
            $sql = $sql.' AND curso=:cu ORDER BY fecha_solicitud DESC';
            $stm = $pdo->prepare($sql);
            $stm->execute(array(':t_u' => $_SESSION['student_id'],':cu' => $_SESSION['course']));
            unset($_SESSION['course']);
        }
        // Todos los prestamos
        else{
            $sql = $sql.' ORDER BY fecha_solicitud DESC';
            $stm = $pdo->prepare($sql);
            $stm->execute(array(':t_u' => $_SESSION['student_id']));
        }
        while ( $request = $stm->fetch(PDO::FETCH_ASSOC)) {
            if ($request['modalidad']=='Clase' || $request['modalidad']=='Practica Libre'){
                $request['fecha_ini'] = '--';
                $request['fecha_fin'] = '--';
            }
            if ($request['modalidad']=='Casa'){
                $request['fecha'] = '--';
                $request['hora_ini'] = '--';
                $request['hora_fin'] = '--';
            }
            echo '<tr><td>'.$request['fecha_solicitud'].'</td>';
            echo '<td>'.$request['curso'].'</td>';
            echo '<td>'.$request['modalidad'].'</td>';
            echo '<td>'.$request['fecha'].'</td>';
            echo '<td>'.$request['fecha_ini'].'</td>';
            echo '<td>'.$request['fecha_fin'].'</td>';
            echo '<td>'.$request['hora_ini'].'</td>';
            echo '<td>'.$request['hora_fin'].'</td>';          
            echo '<td>'.$request['estado'].'</td>';
            echo '<td>';
            echo '<a href="detail_request.php?request_id='.$request['request_id'].
            '& estado='.$request['estado'].'">Mas</a>';
            echo '</td></tr>';
        }            
        echo '</table></p>';
    }else{
        echo '<p style="color:yellow;">En este momento no tiene reservas</p>';
    }
    ?>
   </form>
    </div>
  </section>
  <?php require_once 'footer.html'; ?> 
    
</body>
</html>