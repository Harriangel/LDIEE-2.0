<?php
// Comenzar sesion
session_start();
// Conexion con la base de datos 
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Cargar todos los cursos
$stm = $pdo->prepare('SELECT * FROM members WHERE student_id=:s_id');
$stm->execute(array(':s_id'=>$_SESSION['student_id']));
$courses = $stm->fetchAll(PDO::FETCH_ASSOC);
// Envio POST
if (isset($_POST['course']) && !isset($_POST['back'])){
    // Validar curso
    if (strlen($_POST['course']) < 1){
        $_SESSION['error']='Debe escribir el nombre de un curso';
        header('location:courses.php');
        return;
    }
    // Cargar curso
    $stm = $pdo->prepare('SELECT * FROM members WHERE student_id=:s_id');
    $stm->execute(array(':s_id' => $_SESSION['student_id']));
    $course = $stm->fetch(PDO::FETCH_ASSOC);
    // Validar  prestamo
    if ($course == false){
        $_SESSION['error']='Curso no encontrado';
        header('location:courses.php');
        return;    
    }
    // Mensaje de confirmación
    $_SESSION['success']='Curso encontrado';
    $_SESSION['course'] = $_POST['course'];
    header('location:courses.php');
    return;   
}
// Regresar a todos los prestamos
Back('courses.php');
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/shield.png">
    <meta charset="utf-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Mis Cursos</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo_prestamos.css'>
</head>
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_student.html';?>
    <section class="form-register">
  <div align='center'>
  <form method="post">
    <h2>Mis Cursos</h2>
    <!-- Barra de busqueda -->
    <p><form method="post">
        <label for="course">Nombre del curso:</label>
        <input type="text" name="course" class="form-control" placeholder="Ingrese el nombre del curso">
        <input type="submit" value="Buscar" class="btn btn-success">
        <input type="submit" name="back" value="Regresar" class="btn btn-light">
    </form></p>
    <?php
    // Imprimir mensajes
    ShowMessages();
    // Validar la exitencia de prestamos
    if (count($courses)>0){
        echo '<p><table class="table table-dark table-hover">
                <tr>
                    <th>Curso</th>
                    <th>Grupo</th>
                    <th>Horario</th>
                    <th>Dias</th>
                    <th>Grupo de trabajo</th>
                    <th>Opciones</th>
                </tr>';
        $sql = 'SELECT * FROM members JOIN courses ON members.course_id=courses.course_id 
        WHERE student_id=:s_id';
        // Prestamos de un dia especifico
        if (isset($_SESSION['course'])){
            $sql = $sql.' AND name=:c';
            $stm = $pdo->prepare($sql);
            $stm->execute(array(
                ':s_id' => $_SESSION['student_id'],
                ':c' => $_SESSION['course']));
            unset($_SESSION['course']);
        }
        // Todos los prestamos
        else{
            $sql = $sql.' ORDER BY name';
            $stm = $pdo->prepare($sql);
            $stm->execute(array(':s_id' => $_SESSION['student_id']));
        }
        while ( $course = $stm->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr><td>'.$course['name'].'</td>';
            echo '<td>'.$course['group'].'</td>';
            echo '<td>'.$course['hour_ini'].'-'.$course['hour_fin'].'</td>';
            echo '<td>'.$course['days'].'</td>';
            echo '<td>'.$course['work_group'].'</td>';
            echo '<td>';
            echo '<a href="add_partners.php?course_id='.$course['course_id'].
            '&work_group='.$course['work_group'].'">Agregar Compañeros</a>';
            echo '</td></tr>';
        }            
        echo '</table></p>';
    }else{
        echo '<p style="color:yellow;">En este momento no esta inscrito a ningun curso</p>';
    }
    ?>
   </form>
    </div>
  </section>
  <?php require_once 'footer.html'; ?> 
    
</body>
</html>