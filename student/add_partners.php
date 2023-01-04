<?php
// Comenzar sesion
session_start();
// Conexión base de datos
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Envio GET
if (!isset($_GET['course_id']) || !isset($_GET['work_group'])){
    $_SESSION['error']='Información Incompleta';
    header('location:courses.php');
    return;
}else{
    $course_id = $_GET['course_id'];
    $work_group = $_GET['work_group'];
}
// Validar informacion
$stm = $pdo->prepare('SELECT * FROM members WHERE course_id=:c_id AND work_group=:g');
$stm->execute(array(':c_id'=>$course_id, ':g'=>$work_group));
$members = $stm->fetchAll(PDO::FETCH_ASSOC);
if (count($members)==0){
    $_SESSION['error']='Información Invalida';
    header('location:courses.php');
    return;
}
// Comprobar inicio de sesion
ValidateLogIn();
// Cargar miembros
$stm=$pdo->prepare('SELECT * FROM members JOIN students ON members.student_id=students.ti_un 
WHERE student_id!=:t_u AND course_id=:c_id AND work_group=:g');
$stm->execute(array(':t_u'=>$_SESSION['student_id'],':c_id'=>$course_id, ':g'=>$work_group));
$members = $stm->fetchAll(PDO::FETCH_ASSOC);
// Envio ti_un
if (isset($_POST['ti_un']) && isset($_POST['document']) && !isset($_POST['back'])){
  // Validar codigo 
  $msg = ValidateDocument();
  if (is_string($msg)){
      unset($_SESSION['ti_un']);
      $_SESSION['error']=$msg;
      header('location:add_partners.php?course_id='.$course_id.'&work_group='.$work_group);
      return;
  }
  // Cargar ti_un
  Loadti_un($pdo);
  // Buscar estudiante
  $stm=$pdo->prepare('SELECT * FROM students WHERE ti_un=:t_u');
  $stm->execute(array(':t_u'=>$_POST['ti_un']));
  $student=$stm->fetch(PDO::FETCH_ASSOC);
  if($student==false){
    unset($_SESSION['ti_un']);
    $_SESSION['error']='Estudiante no encontrado';
    header('location:add_partners.php?course_id='.$course_id.'&work_group='.$work_group);
    return;
  }
  $_SESSION['ti_un']=$student['ti_un'];
  $_SESSION['success']='Estudiante encontrado';
  header('location:add_partners.php?course_id='.$course_id.'&work_group='.$work_group);
  return;
}
// Agregar compañero al grupo
if(isset($_POST['add'])){
  $stm=$pdo->prepare('SELECT * FROM members WHERE course_id=:c_id AND student_id=:s_id');
  $stm->execute(array(
    ':c_id'=>$course_id,
    ':s_id'=>$_SESSION['ti_un']));
  $member = $stm->fetch(PDO::FETCH_ASSOC);
  if ($member != false){
    $_SESSION['error']='El estudiante ya pertenece al grupo o a otro grupo';
    header('location:add_partners.php?course_id='.$course_id.'&work_group='.$work_group);
    return;
  }  
  $stm=$pdo->prepare('INSERT INTO members(course_id, student_id, work_group) 
  VALUES (:c_id,:s_id, :g)');
  $stm->execute(array(
    ':c_id'=>$course_id,
    ':s_id'=>$_SESSION['ti_un'],
    ':g'=>$work_group));
  unset($_SESSION['ti_un']);
  $_SESSION['success']='Compañero agregado';
  header('location:add_partners.php?course_id='.$course_id.'&work_group='.$work_group);
  return;
}
// Borrar estudiante
if (isset($_POST['delete'])){
  $stm=$pdo->prepare('DELETE FROM members WHERE student_id=:s_id AND course_id=:c_id AND work_group=:g');
  $stm->execute(array(':s_id'=>$_POST['student_id'], ':c_id'=>$course_id, ':g'=>$work_group));
  $_SESSION['success']='Compañero eliminado';
  header('location:add_partners.php?course_id='.$course_id.'&work_group='.$work_group);
  return;
}
// Finalizar 
if (isset($_POST['back'])){
  unset($_SESSION['ti_un']);
  header('location:courses.php');
  return;
}

?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/shield.png">
    <meta charset="utf-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Agregar Compañeros</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo_prestamos.css'>
</head> 
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_student.html';?>
    <section class="form-register">
  <div align='center'>
  <form method="post">
    <h2>Agregar Compañeros</h2>
  <form method="post">
  <div align="center"><h6>A continuacion realice la busqueda con alguna de las opciones:</h6><div>
    <div class="row">
    <div class="col">
    <label for="ti_un">Codigo TI UN:</label>
    <input type="text" name="ti_un" class="form-control" placeholder="Ingrese el codigo universitario">
    </div>
    <div class="col">
    <label for="document">Cedula:</label>
    <input type="text" name="document" class="form-control" placeholder="Ingrese el numero de documento">
    </div>
  </div>
  <br><p><input type="submit" value="Buscar" class="btn btn-success"></p>
  </form>
    <?php ShowMessages();
    if(isset($_SESSION['ti_un'])){
      $stm=$pdo->prepare('SELECT * FROM students WHERE ti_un=:t_u');
      $stm->execute(array(':t_u'=>$_SESSION['ti_un']));
      $student=$stm->fetch(PDO::FETCH_ASSOC);
      echo '<p><table class="table table-dark table-hover">
              <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Opciones</th>
              </tr>
              <tr>
                <td>'.$student['name'].' '.$student['last_name'].'</td>
                <td>'.$student['email'].'</td>
                <td><form method="post">
                      <input type="submit" class="btn btn-success" name="add" value="Agregar">
                    </form></td>
              </tr>
            </table></p>';
    }    
    echo '<h2>Compañeros</h2>';
    if (count($members) > 0){
        $stm=$pdo->prepare('SELECT * FROM members JOIN students ON members.student_id=students.ti_un 
        WHERE student_id!=:t_u AND course_id=:c_id AND work_group=:g');
        $stm->execute(array(':t_u'=>$_SESSION['student_id'],':c_id'=>$course_id, ':g'=>$work_group));
        echo '<p><table class="table table-dark table-hover">
                <tr>
                  <th>Nombre</th>
                  <th>Correo</th>
                  <th>Opciones</th>
                </tr>';
        while ($student=$stm->fetch(PDO::FETCH_ASSOC)){
          echo '<tr>
                  <td>'.$student['name'].' '.$student['last_name'].'</td>
                  <td>'.$student['email'].'</td>
                  <td>
                  <form method="post">
                  <input type="hidden" name="student_id" value="'.$student['ti_un'].'">
                  <input type="submit" class="btn btn-warning" name="delete" value="Eliminar">
                  </form>
                </td>
                </tr>';
        }
        echo '</table></p>';
    }else{
        echo '<p style="color:yellow;">En este momento no tiene compañeros en el grupo</p>';
    }
    ?>
    <form method="post">
      <input type="submit" name="back" value="Regresar" class="btn btn-light">
    </form>
    </form>
    </div>
  </section>
  <?php require_once 'footer.html'; ?> 
    
</body>
</html>