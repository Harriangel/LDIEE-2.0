<?php
// Comenzar sesion
session_start();
// Conexion con la base de datos 
require_once 'pdo.php';
// Funciones
require_once 'functions.php';

// Comprobar inicio de sesion
ValidateLogIn();
// Validar GET

$msg = ValidateGET();
if (is_string($msg)){
    $_SESSION['error']=$msg;
    header('location:requests_student.php');
    return;
}
// Asignar variables del GET
$request_id=$_GET['request_id'];
$state = $_GET['estado'];
// Validar request_id
$request = LoadRequest($pdo, $request_id, $state);
if ( $request == false ) {
    $_SESSION['error'] = 'No se pudo cargar la solicitud';
    header('location:requests_student.php');
    return;
}
// Asignar valores para imprimir en el formulario
$modalidad = $request['modalidad'];
$fecha = $request['fecha'];
$fecha_ini = $request['fecha_ini'];
$fecha_fin = $request['fecha_fin'];
$curso = $request['curso'];
$hora_ini = $request['hora_ini'];
$hora_fin = $request['hora_fin'];
// Redireccionar
$redirect = Refresh('detail', $request_id, $state);
$refresh = Refresh('edit', $request_id, $state);
// Envio POST Clase
if (isset($_POST['fecha']) && isset($_POST['hora_ini']) 
&& isset($_POST['hora_fin']) && !isset($_POST['back'])){
    // Validar POST
    
    // Validar horario
    if($modalidad=='Clase'){
        $msg = ValidateSchedule($pdo);
        if (is_string($msg)){
            $_SESSION['error']=$msg;
            header('location:'.$refresh);
            return;
        }
    }
    // Actualizar prestamo
    $stm = $pdo->prepare('UPDATE requests SET fecha=:fe ,curso=:c ,hora_ini=:h_i ,hora_fin=:h_f 
    WHERE request_id=:r_id');
    $stm->execute(array(
        ':fe' => $_POST['fecha'],
        ':c' => $_POST['course'],
        ':h_i' => $_POST['hora_ini'],
        ':h_f' => $_POST['hora_fin'],
        'r_id' => $request_id
    ));
    // Redirigir a agregar equipos
    $_SESSION['request_id'] = $request_id;
    $_SESSION['state'] = $state;
    header('location:add_equipment.php');
    return;
}
// Envio POST Casa
if ( isset($_POST['fecha_ini']) && isset($_POST['fecha_fin']) 
&& !isset($_POST['back'])){
    // Validar POST
    
    // Editar prestamo
    $stm = $pdo->prepare('UPDATE requests SET fecha_ini=:fe_i,fecha_fin=:fe_f,curso=:c WHERE request_id=:r_id');
    $stm->execute(array(
        ':fe_i' => $_POST['fecha_ini'],
        ':fe_f' => $_POST['fecha_fin'],
        ':c' => $_POST['course'],
        'r_id' => $request_id
    ));
    // Recuperar id del prestamo y redireccion
    $_SESSION['request_id'] = $request_id;
    $_SESSION['state'] = $state;
    header('location:add_equipment.php');
    return;
}
// Regresar al detalle del prestamo
Back($redirect);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/shield.png">
    <meta charset="utf-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Editar Prestamo</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo_estudiante.css'>
</head>
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_student.html';?>
    <section class="form-register">
  <div align='center'>
  <form method="post">
  <?php ShowMessages(); ?>
    <h2>Editar prestamo</h2>
    <!-- Formulario -->
    <form method="post">
        <!-- Cursos -->
        <p><label for="course">Curso:</label></p>
        <select name="course" class="form-select">
            <option value="0">-- Seleccione un curso --</option>
            <?php
                $stm = $pdo->prepare('SELECT * FROM courses JOIN members ON courses.course_id=members.course_id 
                WHERE members.student_id=:s_id ORDER BY courses.name');
                $stm->execute(array('s_id'=>$_SESSION['student_id']));
                while ($course = $stm->fetch(PDO::FETCH_ASSOC)){
                    echo '<option value="'.$course['name'].'"';
                    // ImpriMantener seleccion de curso
                    if ($course['name'] == $curso){echo 'selected';}
                    echo '>'.$course['name'].' Gr '.$course['group'].'</option>';
                }
            ?>
        </select>
        <?php
        if ($modalidad == 'Casa'){
            echo '<p><label for="fecha_ini">Fecha Inicial:</label></p><input type="date" class="form-control" name="fecha_ini" value="'.$fecha_ini.'">
                <p><label for="fecha_fin">Fecha Final:</label></p><input type="date" class="form-control" name="fecha_fin" value="'.$fecha_fin.'"></p>';
        }else{
            echo '<p><label for="fecha">Fecha:</label></p><input type="date" class="form-control" name="fecha" value="'.$fecha.'">
                <p><label for="hora_ini">Hora Inicial:</label></p><input type="time" class="form-control" name="hora_ini" value="'.$hora_ini.'">
                <p><label for="hora_fin">Hora Final:</label></p><input type="time" class="form-control" name="hora_fin" value="'.$hora_fin.'"></p>';
        }
        ?>
        <p><input type="submit" value="Editar" class="btn btn-success"> <input type="submit" name="back" value="Regresar" class="btn btn-light" ></p>
    </form>
    </form>
    </div>
  </section>
  <?php require_once 'footer.html'; ?> 
    
    
</body>
</html>