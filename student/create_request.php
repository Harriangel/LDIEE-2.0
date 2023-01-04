<?php
// Comenzar sesion
session_start();
// Conexion con la base de datos 
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Zona horaria
date_default_timezone_set('America/Bogota');
// Comprobar inicio de sesion
ValidateLogIn();
// Regresar a la pagina principal
Back('student.php');
// Validar deudas del estudiante
$stmt = $pdo->prepare('SELECT * FROM debts JOIN requests_equipment_codes ON debts.rec_id=requests_equipment_codes.rec_id 
WHERE requests_equipment_codes.ti_un=:s_id AND debts.state="Pendiente"');
$stmt->execute(array(':s_id'=>$_SESSION['student_id']));
$debts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['modalidad'])){
    if ($_POST['modalidad'] == '0'){
        unset($_SESSION['modalidad']);
        $_SESSION['error'] = 'Debe seleccionar una modalidad';
        header('location:create_request.php');
        return;
    }
    $_SESSION['modalidad']=$_POST['modalidad'];
    header('location:create_request.php');
    return;
}

// Envio POST
if ( isset($_POST['fecha']) && isset($_POST['hora_ini']) 
&& isset($_POST['hora_fin']) && !isset($_POST['back'])){
    // Validar POST
    $msg = ValidatePOSTClase();
    if (is_string($msg)){
        $_SESSION['error']=$msg;
        header('location:create_request.php');
        return;
    }
    // Validar horario
    if ($_SESSION['modalidad']=='Clase'){
        $msg = ValidateSchedule($pdo);
        if (is_string($msg)){
            $_SESSION['error']=$msg;
            header('location:create_request.php');
            return;
        }
    }
    // Crear prestamo
    $stm = $pdo->prepare('INSERT INTO requests(modalidad,fecha,curso,hora_ini,hora_fin,estado,fecha_solicitud, ti_un) 
    VALUES (:mo,:fe,:c,:h_i,:h_f,:st,:fe_s,:id)');
    $stm->execute(array(
        ':mo' => $_SESSION['modalidad'],
        ':fe' => $_POST['fecha'],
        ':c' => $_POST['course'],
        ':h_i' => $_POST['hora_ini'],
        ':h_f' => $_POST['hora_fin'],
        ':st' => 'Sin Activar',
        ':fe_s' => date('y-m-d'),
        ':id' => $_SESSION['student_id']
    ));
    // Recuperar id del prestamo y redireccion
    unset($_SESSION['modalidad']);
    $_SESSION['request_id'] = $pdo->lastInsertId();
    header('location:add_equipment.php');
    return;
}

if (isset($_POST['fecha_ini']) && isset($_POST['fecha_fin']) 
&& !isset($_POST['back'])){
    // Validar POST
    $msg = ValidatePOSTCasa();
    if (is_string($msg)){
        $_SESSION['error']=$msg;
        header('location:create_request.php');
        return;
    }
    // Crear prestamo
    $stm = $pdo->prepare('INSERT INTO requests(modalidad,fecha_ini,fecha_fin,curso,estado,fecha_solicitud,ti_un) 
    VALUES (:mo,:fe_i,:fe_f,:c,:st,:fe_s,:id)');
    $stm->execute(array(
        ':mo' => $_SESSION['modalidad'],
        ':fe_i' => $_POST['fecha_ini'],
        ':fe_f' => $_POST['fecha_fin'],
        ':c' => $_POST['course'],
        ':st' => 'Sin Activar',
        ':fe_s' => date('y-m-d'),
        ':id' => $_SESSION['student_id']
    ));
    // Recuperar id del prestamo y redireccion
    unset($_SESSION['modalidad']);
    $_SESSION['request_id'] = $pdo->lastInsertId();
    header('location:add_equipment.php');
    return;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" href="images/shield.png">
    <meta charset="utf-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Solicitar Prestamos</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo_estudiante.css'>
</head>
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_student.html';?>
    <section class="form-register">
  <div align='center'>
  <form method="post">
    <h2>Solicitar prestamo</h2>
    <?php
    if (count($debts)==0){
        ShowMessages();
    echo'<form method="post">';
            // Modalidades
        echo'<p><label for="modalidad">Modalidad:</label>
            <div class="input-group mb-3">
            <select name="modalidad" class="form-select">
                <option value="0">-- Seleccione una modalidad --</option>
                <option value="Clase"';
                if (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Clase'){
                    echo'selected';}
                echo'>Clase</option>
                <option value="Practica Libre"';
                if (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Practica Libre'){
                    echo'selected';}
                echo'>Practica Libre</option>
                <option value="Casa"';
                if (isset($_SESSION['modalidad']) && $_SESSION['modalidad']=='Casa'){
                    echo'selected';}
                echo'>Casa</option>
            </select>
            <input type="submit" name="select" class="btn btn-success" value="Seleccionar"></div>
        </form>';
        if (isset($_SESSION['modalidad'])){
        // Cursos
        echo'<form method="post">
            <p><label for="course">Curso:</label></p>
            <select name="course" class="form-select">
                <option value="0">-- Seleccione un curso --</option>';
                $stm = $pdo->prepare('SELECT * FROM courses JOIN members ON courses.course_id=members.course_id 
                WHERE members.student_id=:s_id ORDER BY courses.name');
                $stm->execute(array('s_id'=>$_SESSION['student_id']));
                while ($course = $stm->fetch(PDO::FETCH_ASSOC)){
                    echo '<option value="'.$course['name'].'">'.$course['name'].' Gr '.$course['group'].'</option>';
                }
        echo'</select>';
            if ($_SESSION['modalidad']=='Casa'){
                // Fecha Inicial
            echo'<p><label for="fecha_ini">Fecha Inicial:</label></p><input type="date" class="form-control" name="fecha_ini">';
            echo'<p><label for="fecha_fin">Fecha Final:</label></p><input type="date" class="form-control" name="fecha_fin">';
            }else{
            // Fecha
            echo'<p><label for="fecha">Fecha:</label></p><input type="date" class="form-control" name="fecha">';
            // Horario
            echo'<p><label for="fecha">Hora Inicial:</label></p><input type="time" class="form-control" name="hora_ini">
                <p><label for="fecha">Hora Final:</label></p><input type="time" class="form-control" name="hora_fin"></p>';
            }
            // Botones
            echo'<p><input type="submit" class="btn btn-success" value="Crear"> <input type="submit" name="back" class="btn btn-light" value="Regresar"></p>
            </form>';
        }
    }else{
        echo '<p style="color:yellow;">Usted posee una deuda, No puede solicitar prestamos </p>';
    }
    ?>
        
        </form>
    </div>
  </section>
  <?php require_once 'footer.html'; ?>  
</body>
</html>