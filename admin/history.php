<?php
// Comenzar sesion
session_start();
// Conexion con la base de datos
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Envio codigo 
if (isset($_POST['code']) && !isset($_POST['back'])){
    // Validar envio
    $msg = ValidateCode();
    if (is_string($msg)){
        $_SESSION['error'] = $msg;
        header('location:activate_request.php?request_id='.$request_id.'&ti_un='.$ti_un);
        return;
    }
    $stmt = $pdo->prepare('SELECT * FROM requests JOIN students JOIN requests_equipment_codes 
    JOIN codes ON requests.ti_un=students.ti_un AND 
    requests.request_id=requests_equipment_codes.request_id AND requests_equipment_codes.code_id=codes.code_id  
    WHERE requests.estado="Desactivado" AND codes.code=:c');
    $stmt->execute(array(':c'=>$_POST['code']));
    $history = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($history == false){
        $_SESSION['error']='No se encontro historial del equipo';
        header('location:history.php');
        return;
    }
    $_SESSION['success']='Historial encontrado';
    $_SESSION['code'] = $_POST['code'];
    header('location:history.php');
    return;
}
// Regresar al historial
Back('history.php');
// Cargar el historial
$history = Load($pdo, 'history');

?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo3.png">
    <meta charset="utf-8">
    <title>Historial de prestamos</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo.css'>
    <script src='main.js'></script>
</head>
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_admin.htm'; ?>
    <section class="form-register">
    <form method="post">
  <div align="center">
    <h2>Historial de prestamos</h2></div>
    <p><form method="post">
        <p><label for="code" class="form-label">Codigo Interno:</label></p>
        <input  class="form-control" type="text" name="code" placeholder="Ingrese el codigo interno"></p>
        <p><input type="submit" class="btn btn-success" value="Buscar">
        <input type="submit" class="btn btn-light" name="back" value="Regresar"></p>
    </form></p>
    <table>
    <?php
    // Imprimir errores
    ShowMessages();
    if (count($history)>0){
        // Prestamos
        echo '<table class="table table-dark table-hover">';
        echo '<tr><th>Estudiante</th>';
        echo '<th>Modalidad</th>';
        echo '<th>Fecha</th>';
        echo '<th>Fecha Inicial</th>';
        echo '<th>Fecha Final</th>';
        echo '<th>Curso</th>';
        echo '<th>Hora Inicial</th>';
        echo '<th>Hora Final</th>';
        echo '<th>Opciones</th></tr>';
        // HIstorial del equipo
        if (isset($_SESSION['code'])){
            $stmt = $pdo->prepare('SELECT * FROM requests JOIN students JOIN 
            requests_equipment_codes JOIN codes ON requests.ti_un=students.ti_un 
            AND requests.request_id=requests_equipment_codes.request_id AND 
            requests_equipment_codes.code_id=codes.code_id WHERE estado="Desactivado" 
            AND codes.code=:c ORDER BY fecha DESC');            
            $stmt->execute(array(':c'=>$_SESSION['code']));
            unset($_SESSION['code']);
        }
        // Todo el historial<
        else{
            $stmt = $pdo->query('SELECT * FROM requests JOIN students  ON 
            requests.ti_un=students.ti_un WHERE estado="Desactivado" 
            ORDER BY fecha DESC');
        }
        while ( $history = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($history['modalidad']=='Clase' || $history['modalidad']=='Practica Libre'){
                $history['fecha_ini'] = '--';
                $history['fecha_fin'] = '--';
            }
            if ($history['modalidad']=='Casa'){
                $history['fecha'] = '--';
                $history['hora_ini'] = '--';
                $history['hora_fin'] = '--';
            }
            echo '<tr>';
            echo '<td>'.$history['name'].' '.$history['last_name'].'</td>';
            echo '<td>'.$history['modalidad'].'</td>';
            echo '<td>'.$history['fecha'].'</td>';
            echo '<td>'.$history['fecha_ini'].'</td>';            
            echo '<td>'.$history['fecha_fin'].'</td>';
            echo '<td>'.$history['curso'].'</td>';            
            echo '<td>'.$history['hora_ini'].'</td>';
            echo '<td>'.$history['hora_fin'].'</td>';
            echo '<td><a href="detail_history.php?request_id='.$history['request_id'].'">Mas</a></td>';
            echo '</tr>';
        }
        echo '</table></p></td>';
    }else{
        echo '<p style="color:yellow;">En este momento el historial de prestamos esta vacio</p>';
    }
    ?>
    </table>
    </form>
</section>
    <?php require_once 'footer.html'; ?>  
</body>
</html>



