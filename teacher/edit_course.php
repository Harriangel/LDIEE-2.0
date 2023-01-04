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
if (isset($_SESSION['course_id'])){
    $stm = $pdo->prepare('SELECT * FROM courses WHERE course_id=:c_id');
    $stm->execute(array(':c_id'=>$_SESSION['course_id']));
    $course = $stm->fetch(PDO::FETCH_ASSOC);
    if ($course == false){
        $_SESSION['error']='Curso no encontrado';
        header('location:courses.php');
        return;
    }
    $name= $course['name'];
    $group= $course['group'];
    $groups= $course['groups'];
    $hour_ini= $course['hour_ini'];
    $hour_fin= $course['hour_fin'];
    $days = strlen($course['days'])>9 ? explode(' ', $course['days']) : $course['days'];
}else{
    $_SESSION['error']='Curso no encontrado';
    header('location:courses.php');
    return;
}

// Envio POST
if (isset($_POST['name']) && isset($_POST['group']) && isset($_POST['groups']) 
&& isset($_POST['hour_ini']) && isset($_POST['hour_fin']) && !isset($_POST['back']) ){
  $days = ValidatePOSTCourse();
  if (is_string($days)){
    $_SESSION['error']=$days;
    header('location:edit_course.php');
    return;
  }
  $string_days = GenerateString($days);
  // Editar curso
  $stm = $pdo->prepare('UPDATE courses SET name=:n, courses.group=:g, groups=:gs, 
  hour_ini=:h_i, hour_fin=:h_f, days=:d WHERE course_id=:c_id');
  $stm->execute(array(
    ':n' => $_POST['name'],
    ':g' => $_POST['group'],
    ':gs' => $_POST['groups'],
    ':h_i' => $_POST['hour_ini'],
    ':h_f' => $_POST['hour_fin'],
    ':d' => $string_days,
    ':c_id' => $_SESSION['course_id']));
  unset($_SESSION['course_id']);
  $_SESSION['success']='Curso editado';
  header('location:courses.php');
  return;
}
if (isset($_POST['back'])){
  unset($_SESSION['course_id']);
  header('location:courses.php');
  return;
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo.png">
    <meta charset="UTF-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Editar Curso</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='style_teacher.css'>
    <script src='main.js'></script>
</head> 
<body>
  <!-- Barra de navegacion -->
  <?php require_once 'navbar_teacher.html'; ?>
  <!-- Barra de navegacion -->
  <section class="form-register">
  <div align='center'>
  <form method="post">
  <?=ShowMessages();?>
  <h2>Editar Curso</h2>
  <form method="post">
    <p><label for="name">Nombre:</label></p>
    <input type="text" class="form-control" name="name" value="<?=$name?>">
    <p><label for="group">Grupo:</label></p>
    <input type="text" class="form-control" name="group" value="<?=$group?>">
    <p><label for="groups">Grupos de trabajo:</label></p>
    <input type="text" class="form-control" name="groups" value="<?=$groups?>">
    <p><label for="hour_ini">Hora de inicio:</label></p>
    <input type="time" class="form-control" name="hour_ini" value="<?=$hour_ini?>">
    <p><label for="hour_fin">Hora de finalizacion:</label></p>
    <input type="time" class="form-control"  name="hour_fin" value="<?=$hour_fin?>">
    <p><label for="days">Dias:</label></br>
    <input type="checkbox" class="form-check-input" name="days1" value="lunes" <?php SearchDay($days, 'lunes');?>>
    <label for="lunes">Lunes</label>
    <input type="checkbox" class="form-check-input" name="days2" value="martes" <?php SearchDay($days, 'martes');?>>
    <label for="martes">Martes</label>
    <input type="checkbox" class="form-check-input" name="days3" value="miercoles" <?php SearchDay($days, 'miercoles');?>>
    <label for="miercoles">Miercoles</label></br>
    <input type="checkbox" class="form-check-input" name="days4" value="jueves" <?php SearchDay($days, 'jueves');?>>
    <label for="jueves">Jueves</label>
    <input type="checkbox" class="form-check-input" name="days5" value="viernes" <?php SearchDay($days, 'viernes');?>>
    <label for="viernes">Viernes</label>
    <input type="checkbox" class="form-check-input" name="days6" value="sabado" <?php SearchDay($days, 'sabado');?>>
    <label for="sabado">Sabado</label></p><br><br><br>
    <p><input type="submit" class="btn btn-success" value="Editar">
    <input type="submit" name="back" class="btn btn-light" value="Regresar"></p>
    </div>
</form>
  </section>
  <?php require_once 'footer.html'; ?> 
</body>
</html>