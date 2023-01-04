<?php
// Comenzar session
session_start();
// Conexion con la base de datos
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Zona horaria
date_default_timezone_set('America/Bogota');
// Comprobar inicio de sesion
ValidateLogIn();
// Regresar a reservas
Back('reservations.php');
// Validar GET
$msg = ValidateGET();
if (is_string($msg)){
    $_SESSION['error']= $msg;
    header('location:requests.php');
    return;
}
$request_id = $_GET['request_id'];
$ti_un = $_GET['ti_un'];
// Validar información GET
$msg = ValidateGETInfo($pdo, $request_id, $ti_un);
if (is_string($msg)){
    $_SESSION['error']= $msg;
    header('location:requests.php');
    return;
}
// Cargar equipos
$equipments = LoadEquipment($pdo, $request_id, 'Sin Activar');
// Actualizar codigo de los equipos
if (isset($_POST['rec_id']) && isset($_POST['code'])){
    // Validar envio
    $msg = ValidateCode();
    if (is_string($msg)){
        $_SESSION['error'] = $msg;
        header('location:'.Refresh('activate', $request_id, $ti_un));
        return;
    }
    // Validar codigo
    $stm = $pdo->prepare('SELECT * FROM equipment_codes JOIN codes ON 
    equipment_codes.code_id=codes.code_id WHERE equipment_id=:e_id AND code=:c');
    $stm->execute(array(':e_id'=>$_POST['equipment_id'], ':c'=>$_POST['code']));
    $code = $stm->fetch(PDO::FETCH_ASSOC); 
    if ($code == false ){
        $_SESSION['error'] = 'El codigo no existe o no correspone al equipo';
        header('location:'.Refresh('activate', $request_id, $ti_un));
        return;
    }
    $code_id=$code['code_id'];
    $stm = $pdo->prepare('UPDATE requests_equipment_codes SET code_id=:c_id, 
    state="Activo" WHERE requests_equipment_codes.rec_id = :rec_id');
    $stm->execute(array(':c_id'=>$code_id, ':rec_id'=>$_POST['rec_id']));
    $_SESSION['success']='Establecido';
    header('location:'.Refresh('activate', $request_id, $ti_un));
    return;
}
// Activar reserva
if (isset($_POST['activate'])){
    UpdateEstado($pdo, 'Activo', 'hora_ini', $request_id);
    require_once 'emails/email_activation.php';
    header('location:reservations.php');
    return;
}
// Redirigir a agregar equipos
if (isset($_POST['edit'])){
    $_SESSION['action']='activate';
    header('location:'.Refresh('edit_equipment', $request_id, $ti_un));
    return;
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo3.png">
    <meta charset="utf-8">
    <title>Activar Reservas</title>
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
    <h2>Activar prestamo</h2>
    <?php ShowMessages();
    if ($equipments !=false){
        echo '<table class="table table-dark table-hover">
            <tr>
                <th>Cantidad</th>    
                <th>Equipo</th>
                <th>Tipo</th>
                <th>Modelo</th>
                <th>Codigo</th>
            </tr>';
        $equipment_id = LoadEquipment_id($pdo, 'Sin Activar', $request_id);
        $quantity = array_count_values($equipment_id); 
        $equipment_id = array_unique($equipment_id);
        foreach ($equipment_id as $equip_id){
            $stm = $pdo->prepare('SELECT * FROM requests_equipment_codes JOIN 
            full_equipment JOIN equipment JOIN types JOIN models ON 
            requests_equipment_codes.equipment_id=full_equipment.full_equipment_id 
            AND full_equipment.equipment_id=equipment.equipment_id AND 
            full_equipment.type_id=types.type_id AND full_equipment.model_id=models.model_id 
            WHERE requests_equipment_codes.equipment_id=:e_id AND state="Sin Activar"');
            $stm->execute(array(':e_id'=>$equip_id));
            $equip = $stm->fetch(PDO::FETCH_ASSOC);
            echo '<tr><td>'.$quantity[$equip_id].'</td>';
            echo '<td>'.$equip['equipment'].'</td>';
            echo '<td>'.$equip['type'].'</td>';
            echo '<td>'.$equip['model'].'</td>';
            echo '<td><form method="post">
                        <input type="hidden" name="equipment_id" value="'.$equip_id.'">            
                        <input type="hidden" name="rec_id" value="'.$equip['rec_id'].'">
                        <input type="text" name="code"><input type="submit" value="Enviar">
                    </form></td></tr>';
            }
        echo '</table>';
    }else{
        echo '<p style="color:yellow;">Todos los equipos de la reserva han sido asignados</p>';
    }
    ?>    
    <form  method="post">
        <input type="submit" class="btn btn-success" name="activate" value="Activar">
        <input type="submit" name="edit" class="btn btn-warning" value="Agregar Equipos">
        <input type="submit" name="back" class="btn btn-light" value="Regresar">
    </form>
    </div>
    </form>
    </section>
    <?php require_once 'footer.html'; ?>  
</body>
</html>