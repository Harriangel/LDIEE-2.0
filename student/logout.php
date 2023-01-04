<?php
session_start();
unset($_SESSION['ti_un']);
session_destroy();
header('location:http://localhost/proyects/LDIEE/index.php');
?>