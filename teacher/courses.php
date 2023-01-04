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
$stm = $pdo->prepare('SELECT * FROM courses WHERE teacher_id=:t_id');
$stm->execute(array(':t_id'=>$_SESSION['teacher_id']));
$courses = $stm->fetchAll(PDO::FETCH_ASSOC);
// Envio curso
if (isset($_POST['course']) && !isset($_POST['back'])){
    // Validar curso
    if (strlen($_POST['course']) < 1){
        $_SESSION['error']='Debe escribir el nombre de un curso';
        header('location:courses.php');
        return;
    }
    // Cargar curso
    $stm = $pdo->prepare('SELECT * FROM courses WHERE teacher_id=:t_id AND name=:c');
    $stm->execute(array(':t_id'=>$_SESSION['teacher_id'], ':c'=>$_POST['course']));
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
// Editar curso
if(isset($_POST['edit'])){
    $_SESSION['course_id']=$_POST['course_id'];
    header('location:edit_course.php');
    return;
}
// Eliminar curso
if(isset($_POST['delete'])){
    $stm = $pdo->prepare('DELETE FROM courses WHERE course_id=:c_id');
    $stm->execute(array(':c_id'=>$_POST['course_id']));
    $_SESSION['success']='Curso borrado';
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
    <title>Mis Cursos</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='style_courses.css'>
    <script src='main.js'></script>
</head>
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_teacher.html';?>
    <section class="form-register">
  <div align='center'>
  <form method="post">
    <h2>Mis Cursos</h2>
    <!-- Barra de busqueda -->
    <p><form method="post">
        <label for="course">Nombre del curso:</label>
        <input type="text" class="form-control" name="course" placeholder="Ingrese el nombre del curso">
        <input type="submit" class="btn btn-success" value="Buscar">
        <input type="submit" class="btn btn-light" name="back" value="Regresar">
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
                    <th>Grupos de trabajo</th>
                    <th>Opciones</th>
                </tr>';
        $sql = 'SELECT * FROM courses WHERE teacher_id=:t_id';
        // Prestamos de un dia especifico
        if (isset($_SESSION['course'])){
            $sql = $sql.' AND name=:c ORDER BY name';
            $stm = $pdo->prepare($sql);
            $stm->execute(array(
                ':t_id' => $_SESSION['teacher_id'],
                ':c' => $_SESSION['course']));
            unset($_SESSION['course']);
        }
        // Todos los prestamos
        else{
            $sql = $sql.' ORDER BY name';
            $stm = $pdo->prepare($sql);
            $stm->execute(array(':t_id' => $_SESSION['teacher_id']));
        }
        while ( $course = $stm->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr><td>'.$course['name'].'</td>';
            echo '<td>'.$course['group'].'</td>';
            echo '<td>'.$course['hour_ini'].'-'.$course['hour_fin'].'</td>';
            echo '<td>'.$course['days'].'</td>';
            echo '<td>'.$course['groups'].'</td>';
            echo '<td>';
            echo '<form method="post">
                    <input type="hidden" name="course_id" value="'.$course['course_id'].'">
                    <p><input type="submit" name="edit" class="btn btn-success" value="Editar">
                    <input type="submit" name="delete" class="btn btn-warning" value="Borrar"></p>
                </form>';
            echo '</td></tr>';
        }            
        echo '</table></p>';
    }else{
        echo '<p style="color:yellow;">En este momento no tiene ningun curso creado</p>';
    }
    ?>    
     </form>
    </div>
  </section>
  <?php require_once 'footer.html'; ?> 
</body>
</html>