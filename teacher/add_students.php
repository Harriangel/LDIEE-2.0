<?php
// Comenzar sesion
session_start();
// Conexión base de datos
require_once('pdo.php');
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Envio course_id
if(isset($_POST['course_id'])){
  if($_POST['course_id']==0){
    $_SESSION['error']='Debe seleccionar un curso';
    unset($_SESSION['course_id']);
    header('location:add_students.php');
    return;
  }
  $_SESSION['success']='curso seleccionado';
  $_SESSION['course_id'] = $_POST['course_id'];
  header('location:add_students.php');
  return;
}
// Cargar estudiantes
if (isset($_SESSION['course_id'])){
  $stm=$pdo->prepare('SELECT * FROM members JOIN students ON members.student_id=students.ti_un 
  WHERE course_id=:c_id ORDER BY work_group ASC');
  $stm->execute(array(':c_id'=>$_SESSION['course_id']));
  $students = $stm->fetchAll(PDO::FETCH_ASSOC);
}
// Envio grupo
if(isset($_POST['group'])){
  if($_POST['group']==0){
    $_SESSION['error']='Debe seleccionar un grupo';
    unset($_SESSION['group']);
    header('location:add_students.php');
    return;
  }
  $_SESSION['success']='grupo seleccionado';
  $_SESSION['group'] = $_POST['group'];
  header('location:add_students.php');
  return;
}
// Envio ti_un
if (isset($_POST['ti_un']) && isset($_POST['document']) && !isset($_POST['back'])){
  // Validar codigo 
  $msg = ValidateDocument();
  if (is_string($msg)){
    unset($_SESSION['student_id']);
    $_SESSION['error']=$msg;
    header('location:add_students.php');
    return;
  }
  // Cargar ti_un
  Loadti_un($pdo);
  // Buscar estudiante
  $stm=$pdo->prepare('SELECT * FROM students WHERE ti_un=:codigo');
  $stm->execute(array(':codigo'=>$_POST['ti_un']));
  $student=$stm->fetch(PDO::FETCH_ASSOC);
  if($student==false){
    unset($_SESSION['student_id']);
    $_SESSION['error']='Estudiante no encontrado';
    header('location:add_students.php');
    return;
  }
  $_SESSION['student_id']=$student['ti_un'];
  $_SESSION['success']='Estudiante encontrado';
  header('location:add_students.php');
  return;
}
// Agregar estudiante al curso
if(isset($_POST['add'])){
  if (!isset($_SESSION['group'])){
    $_SESSION['error']='Debe seleccionar un grupo de trabajo';
    header('location:add_students.php');
    return;
  }
  // Validar estudiante inscrito
  $stm=$pdo->prepare('SELECT * FROM courses JOIN members ON courses.course_id=members.course_id 
  WHERE teacher_id=:t_id AND courses.course_id=:c_id AND student_id=:s_id');
  $stm->execute(array(
    ':t_id'=>$_SESSION['teacher_id'],
    ':c_id'=>$_SESSION['course_id'],
    ':s_id'=>$_SESSION['student_id']));
  $member = $stm->fetch(PDO::FETCH_ASSOC);
  if ($member != false){
    $_SESSION['error']='El estudiante ya pertenece al grupo o a otro grupo';
    header('location:add_students.php');
    return;
  }  
  $stm=$pdo->prepare('INSERT INTO members(course_id, student_id, work_group) 
  VALUES (:c_id,:s_id, :g)');
  $stm->execute(array(
    ':c_id'=>$_SESSION['course_id'],
    ':s_id'=>$_SESSION['student_id'],
    ':g'=>$_SESSION['group']));
  unset($_SESSION['group']);
  unset($_SESSION['student_id']);
  $_SESSION['success']='Estudiante agregado';
  header('location:add_students.php');
  return;
}
// Borrar estudiante
if (isset($_POST['delete'])){
  $stm=$pdo->prepare('DELETE FROM members WHERE student_id=:s_id AND members.course_id=:c_id');
  $stm->execute(array(':c_id'=>$_SESSION['course_id'], ':s_id'=>$_POST['student_id']));
  $_SESSION['success']='Estudiante eliminado';
  header('location:add_students.php');
  return;
}
// Finalizar 
if (isset($_POST['back'])){
  unset($_SESSION['course_id']);
  unset($_SESSION['group']);
  unset($_SESSION['student_id']);
  header('location:add_students.php');
  return;
}

?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo.png">
    <meta charset="UTF-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Agregar estudiantes</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='style_teacher.css'>
    <script src='main.js'></script>
</head> 
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_teacher.html'; ?>
    <section class="form-register">
  <div align='center'>
  <form method="post">
    <h2>Agregar Estudiantes</h2>
    <?php ShowMessages(); ?>
  <form method="post">
    <p><label for="course_id">Curso: </label>
    <div class="input-group mb-3">
    <select name="course_id" id="" class="form-select">
      <option value="0">--- Selecione un curso --</option>
      <?php
      $stm=$pdo->prepare('SELECT * FROM courses WHERE teacher_id=:t_id ORDER BY name');
      $stm->execute(array(':t_id'=>$_SESSION['teacher_id']));
      while ($course=$stm->fetch(PDO::FETCH_ASSOC)) {
        echo '<option value="'.$course['course_id'].'"';
        if (isset($_SESSION['course_id']) && ($course['course_id'] == $_SESSION['course_id'])){
          echo 'selected';
        }
        echo '>'.$course['name'].' Gr '.$course['group'].'</option>';
      }
      ?>      
    </select>
    <input type="submit" class="btn btn-success" value="Seleccionar"></div>   
  </form>
  <form method="post">
    <p><label for="group">Grupo de trabajo:</label>
    <div class="input-group mb-3">
    <select name="group" class="form-select">
      <option value="0">--Seleccione un grupo--</option>
      <?php
      if (isset($_SESSION['course_id'])){
        $stm=$pdo->prepare('SELECT * FROM courses WHERE course_id=:c_id');
        $stm->execute(array(':c_id'=>$_SESSION['course_id']));
        $course=$stm->fetch(PDO::FETCH_ASSOC);
        $groups = $course['groups'];
        for ($i=1; $i<=$groups; $i++){
          echo '<option value="'.$i.'"';
          if (isset($_SESSION['group']) && ($i == $_SESSION['group'])){
            echo 'selected';
          }
          echo '>'.$i.'</option>';
        }
      }
      ?>  
    </select>
    <input type="submit" class="btn btn-success" value="Seleccionar"></div>   
  </form>
  <form method="post">
  <div align="center"><h6>A continuacion realice la busqueda con alguna de las opciones:</h6><div>
    <p><label for="ti_un">Codigo TI UN:</label></p>
    <input type="text" name="ti_un" class="form-control" placeholder="Ingrese el codigo universiario">
    <p><label for="document">Cedula:</label></p>
        <input type="text" name="document" class="form-control" placeholder="Ingrese el numero de documento"></p>
    <p><input type="submit" class="btn btn-success" value="Buscar"></p>
  </form>
    <?php
    if(isset($_SESSION['student_id'])){
      $stm=$pdo->prepare('SELECT * FROM students WHERE ti_un=:t_u');
      $stm->execute(array(':t_u'=>$_SESSION['student_id']));
      $student=$stm->fetch(PDO::FETCH_ASSOC);
      echo '<p><table class="table table-dark table-hover">
              <tr>
                <th>ti_un</th>
                <th>Nombre</th>
                <th>Correo</th>
              </tr>
              <tr>
                <td>'.$student['ti_un'].'</td>
                <td>'.$student['name'].' '.$student['last_name'].'</td>
                <td>'.$student['email'].'</td>
                <td><form method="post">
                <p><input type="submit" class="btn btn-light" name="add" value="Agregar"></p>
                </form></td>               
              </tr>
            </table></p>';
    }
    if(isset($_SESSION['course_id'])){
      echo '<h2>Estudiantes Agregados</h2>';
      if (count($students) > 0){
        $stm=$pdo->prepare('SELECT * FROM members JOIN students ON members.student_id=students.ti_un 
        WHERE course_id=:c_id ORDER BY work_group ASC');
        $stm->execute(array(':c_id'=>$_SESSION['course_id']));
        echo '<p><table class="table table-dark table-hover">
                <tr>
                  <th>ti_un</th>
                  <th>Nombre</th>
                  <th>Correo</th>
                  <th>Grupo</th>
                  <th>Opciones</th>
                </tr>';
        while ($student=$stm->fetch(PDO::FETCH_ASSOC)){
          echo '<tr>
                  <td>'.$student['ti_un'].'</td>
                  <td>'.$student['name'].' '.$student['last_name'].'</td>
                  <td>'.$student['email'].'</td>
                  <td>'.$student['work_group'].'</td>
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
        echo '<p style="color:yellow;">En este momento no hay estudiantes agregados al curso</p>';
      }
    }
    ?>
    <form method="post">
      <p><input type="submit" name="back" class="btn btn-light" value="Regresar"></p>
    </form>
    </form>
    </div>
  </section>
  <?php require_once 'footer.html'; ?> 
</body>
</html>