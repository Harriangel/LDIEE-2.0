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
  // Comprobar campos vacios
  if(strlen($_POST['name'])<1 || strlen($_POST['group'])<1 || strlen($_POST['groups'])<1 
  || strlen($_POST['hour_ini'])<1 || strlen($_POST['hour_fin'])<1){
    $_SESSION['error']="Debe llenar todos los campos";
      header('location:create_class.php');
      return;
  }
  // Envio de dias
  $days=array(); $string_days='';
  for ($i=1; $i<=6; $i++){
    if (!isset($_POST['days'.$i])) continue;
      $days[] = $_POST['days'.$i];   
  }
  // Comprobar seleccion
  if (count($days)==0){
    $_SESSION['error']='Debe seleccionar al menos un dia';
    header('location:create_class.php');
    return;
  }
  // Generar string
  foreach($days as $day){ $string_days=$string_days.$day.' ';}
  // Validar curso
  $stm = $pdo->prepare('SELECT * FROM courses  WHERE name=:n AND courses.group=:g AND groups=:gs AND hour_ini=:h_i 
  AND hour_fin=:h_f AND  days=:d AND teacher_id=:t_id'); 
  $stm->execute(array(
    ':n' => $_POST['name'],
    ':g' => $_POST['group'],
    ':gs' => $_POST['groups'],
    ':h_i' => $_POST['hour_ini'],
    ':h_f' => $_POST['hour_fin'],
    ':d' => $string_days,
    ':t_id' => $_SESSION['teacher_id']));
  $course = $stm->fetch(PDO::FETCH_ASSOC);
  if ($course!=false){
    $_SESSION['error']='El curso ya existe';
    header('location:create_class.php');
    return;
  }
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
  header('location:create_class.php');
  return;
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" href="images/escudo.png">
    <meta charset="utf-8">
    <title>Crear clase</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='style.css'>
</head> 
<body>
  <!-- Barra de navegacion -->
  <?php require_once 'navbar_teacher.html'; ?>
  <!-- Barra de navegacion -->
  <section class="form-register">
  <form method="post">
  <div align="center"><h2>Crear cursos</h2></div>
    <?=ShowMessages();?>
    <p><label for="name">Nombre:</label></p>
    <input class="form-control" type="text" name="name" placeholder="Ingrese el nombre de la clase">

    <p><label for="group">Grupo:</label></p>
    <input class="form-control" type="text" name="group" placeholder="Ingrese el Grupo">

    <p><label for="groups">Grupos de trabajo:</label></p>
    <input class="form-control" type="text" name="groups" placeholder="Ingrese el Grupo de trabajo">

    <p><label for="hour_ini">Hora de inicio:</label></p>
    <input class="form-control" type="time" name="hour_ini" >

    <p><label for="hour_fin">Hora de finalizacion:</label></p>
    <input class="form-control" type="time" name="hour_fin" >

    <p><label for="days">Dias:</label></br>
    <input class="form-check-input" type="checkbox" name="days1" value="lunes">
    <label class="form-check-label" for="lunes">Lunes</label>
    <input class="form-check-input" type="checkbox" name="days2" value="martes">
    <label class="form-check-label" for="martes">Martes</label>
    <input class="form-check-input" type="checkbox" name="days3" value="miercoles">
    <label class="form-check-label" for="miercoles">Miercoles</label></br>
    <input class="form-check-input" type="checkbox" name="days4" value="jueves">
    <label class="form-check-label" for="jueves">Jueves</label>
    <input class="form-check-input" type="checkbox" name="days5" value="viernes">
    <label class="form-check-label" for="viernes">Viernes</label>
    <input class="form-check-input" type="checkbox" name="days6" value="sabado">
    <label class="form-check-label" for="sabado">Sabado</label></p>
</p><p></p><p><input type="submit" value="Agregar" class="btn btn-success">
    <input type="submit" name="back" value="Regresar" class="btn btn-light"></p>
  </form> 
  </section>
  <?php require_once 'footer.html'; ?>  
  </body>

</html>
