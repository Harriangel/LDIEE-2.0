<?php
session_start();
require_once('pdo.php');
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Regresar a la pagina principal
Back('teacher.php');
// Envio POST
if (isset($_POST['name']) && isset($_POST['group']) && isset($_POST['groups']) 
&& isset($_POST['hour_ini']) && isset($_POST['hour_fin']) && !isset($_POST['back']) ){
  $days = ValidatePOSTCourse();
  if (is_string($days)){
    $_SESSION['error']=$days;
    header('location:create_course.php');
    return;
  }
  $string_days = GenerateString($days);
  // Insertar curso
  $stm = $pdo->prepare('INSERT INTO courses (name, courses.group, groups, hour_ini, hour_fin, days, teacher_id) 
  VALUES (:n, :g, :gs, :h_i, :h_f, :d, :t_id)');
  $stm->execute(array(
    ':n' => $_POST['name'],
    ':g' => $_POST['group'],
    ':gs' => $_POST['groups'],
    ':h_i' => $_POST['hour_ini'],
    ':h_f' => $_POST['hour_fin'],
    ':d' => $string_days,
    ':t_id' => $_SESSION['teacher_id']));
  $_SESSION['success']='Curso creado';
  header('location:create_course.php');
  return;
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo.png">
    <meta charset="UTF-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Crear Curso</title>
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
  <h2>Crear cursos</h2>
  <?=ShowMessages();?>
    <p><label for="name">Nombre:</label></p>
    <input type="text" name="name" class="form-control" placeholder="Ingrese el nombre del curso">
    <p><label for="group">Grupo:</label></p>
    <input type="text" name="group" class="form-control" placeholder="Ingrese el grupo del curso">
    <p><label for="groups">Grupos de trabajo:</label></p>
    <input type="text" name="groups" class="form-control" placeholder="Ingrese el grupo de trabajo">
    <p><label for="hour_ini">Hora de inicio:</label></p>
    <input type="time" name="hour_ini" class="form-control">
    <p><label for="hour_fin">Hora de finalizacion:</label></p>
    <input type="time" name="hour_fin" class="form-control">
    <p><label for="days">Dias:</label></br>
    <input type="checkbox"  class="form-check-input" name="days1" value="lunes">
    <label for="lunes" class="form-check-label">Lunes</label>
    <input type="checkbox"  class="form-check-input" name="days2" value="martes">
    <label for="martes">Martes</label>
    <input type="checkbox"  class="form-check-input" name="days3" value="miercoles">
    <label for="miercoles">Miercoles</label></br>
    <input type="checkbox"  class="form-check-input" name="days4" value="jueves">
    <label for="jueves">Jueves</label>
    <input type="checkbox"  class="form-check-input" name="days5" value="viernes">
    <label for="viernes">Viernes</label>
    <input type="checkbox"  class="form-check-input" name="days6" value="sabado">
    <label for="sabado">Sabado</label></p><br><br><br>
    <p><input type="submit" class="btn btn-success" value="Crear">
    <input type="submit" name="back" class="btn btn-light" value="Regresar"></p>
    </div>
  </section>
  <?php require_once 'footer.html'; ?> 
</body>
</html>
