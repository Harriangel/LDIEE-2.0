<?php
// Comenzar sesion
session_start();
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo.png">
    <meta charset="UTF-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Pagina Principal Profesor</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo.css'>
    <script src='main.js'></script>
</head>
<body>
  <!-- Barra de navegacion -->
  <?php require_once 'navbar_teacher.html'; ?>
  <section class="form-register">
  <div align='center'>
    <?php ShowMessages(); ?>
  <img src="images/profesor2.png" width="180"
     height="200"></p>
  <h2>Bienvenid@ <?= $_SESSION['name'] ?></h2>
  </div>
  </section>
  <?php require_once 'footer.html'; ?> 
</body>
</html>