<?php
session_start();
// Recepcion del POST y redireccion
if(isset($_POST['student'])){
    header("location:student/login_student.php");
}elseif(isset($_POST['teacher'])){
    header("location:teacher/login_teacher.php");
}elseif(isset($_POST['admin'])){
    header("location:admin/login_admin.php");
}
?>
<!DOCTYPE html>
<html>
<link rel="shortcut icon" href="teacher/images/escudo.png" />
<meta charset="utf-8">
    <head>
        <title>Pagina principal - LABDIEE</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estiloi.css'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
    <div align="center">
    <h1 style="background-color:#94B43B;"><font face="Calibri"><br>Bienvenidos<br>Pagina de prestamos de equipos del LABDIEE</h1></font>
        <section class="form-register">
                <p>Seleccione su rol dentro de la Universidad:</p>
            <form method="post">
                <button type="submit" name="student">
                <img 
                src="teacher/images/estudiante2.png"
                width="100px"
                height="100px"
                alt="">
                <h6>Estudiante</h6>
                </button>
                <button type="submit" name="teacher">
                <img 
                src="teacher/images/profesor2.png"
                width="100px"
                height="100px"
                alt="">
                <h6>Profesor</h6>
                </button>
                <button type="submit" name="admin">
                <img 
                src="teacher/images/admin.png"
                width="90px"
                height="100px"
                alt="">
                <h6>Administrador</h6>
                </button>
                </div>
            </form>
        </section>
        <?php require_once 'teacher/footer.html'; ?>
    </body>
</html>