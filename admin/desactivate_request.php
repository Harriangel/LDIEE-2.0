<?php
// Comenzar session
session_start();
// Conexion con la base de datos
require_once "pdo.php";
// Funciones
require_once 'functions.php';
// Zona horaria
date_default_timezone_set('America/Bogota');
// Comprobar inicio de sesion
ValidateLogIn();
// Regresar a prestamos
Back('requests.php');
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
$equipments = LoadEquipment($pdo, $request_id, 'Activo');
// Envio comentarios
if (isset($_POST['comments'])){
    // Imponer deuda
    if (isset($_POST['debt'])){
        // Actualizar estado de la solicitud
        UpdateState($pdo, 'En Deuda');
        // Insertar en  historial
        $msg = ' (Deuda Impuesta: '.date('d-m-y').')';
        InsertHistory($pdo, $msg);
        // Insertar deuda     
        $stm = $pdo->prepare('INSERT INTO debts(comments, rec_id, state) VALUES (:c, :id, "Pendiente")');
        $stm->execute(array( ":c" => $_POST['comments'], ":id" => $_POST['rec_id']));
        $_SESSION['success']='Deuda impuesta';
        header('location:'.Refresh('desactivate', $request_id, $ti_un));
        return;
    }
    // Actualizar estado de la solicitud
    UpdateState($pdo, 'Desactivado');
    // Insertar en  historial
    InsertHistory($pdo, false);
    $_SESSION['success']='Archivado';
    header('location:'.Refresh('desactivate', $request_id, $ti_un));
    return;
}
// Desactivar prestamo
if (isset($_POST['desactivate'])){
    UpdateEstado($pdo, 'Desactivado', 'hora_fin', $request_id );
    require_once 'emails/email_desactivation.php';
    header('location:requests.php');
    return;
}
// Redirigir a agregar equipos
if (isset($_POST['edit'])){
    $_SESSION['action']='desactivate';
    header('location:'.Refresh('edit_equipment', $request_id, $ti_un));
    return;
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo3.png">
    <meta charset="utf-8">
    <title>Desactivar Prestamos</title>
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
    <h2>Desactivar prestamo</h2>
    <?php ShowMessages(); ?>
        <?php
        if ($equipments !=false){
            echo '<table class="table table-dark table-hover">
                    <tr>
                        <th>Cantidad</th>    
                        <th>Equipo</th>
                        <th>Tipo</th>
                        <th>Modelo</th>
                        <th>Codigo</th>
                        <th>Comentarios</th>
                        <th>Opciones</th>
                    </tr>';
            $equipment_id = LoadEquipment_id($pdo, 'Activo', $request_id);
            $quantity = array_count_values($equipment_id); 
            $equipment_id = array_unique($equipment_id);
            foreach ($equipment_id as $equip_id){
                $stm = $pdo->prepare('SELECT * FROM requests_equipment_codes JOIN 
                full_equipment JOIN equipment JOIN types JOIN models JOIN codes ON 
                requests_equipment_codes.equipment_id=full_equipment.full_equipment_id 
                AND full_equipment.equipment_id=equipment.equipment_id AND 
                full_equipment.type_id=types.type_id AND full_equipment.model_id=models.model_id 
                AND requests_equipment_codes.code_id=codes.code_id WHERE 
                requests_equipment_codes.equipment_id=:e_id AND state="Activo"');
                $stm->execute(array(':e_id'=>$equip_id));
                $equip = $stm->fetch(PDO::FETCH_ASSOC);
                echo '<tr><td>'.$quantity[$equip_id].'</td>';
                echo '<td>'.$equip['equipment'].'</td>';
                echo '<td>'.$equip['type'].'</td>';
                echo '<td>'.$equip['model'].'</td>';
                echo '<td>'.$equip['code'].'</td>';
                echo '<td><form method="post">
                            <input type="hidden" name="rec_id" value= "'.$equip["rec_id"].'">
                            <p><textarea class="form-control" name="comments" id="" cols="30" rows="3"></textarea></p>
                    </td>
                            <td><p><input type="checkbox" class="form-check-input" name="debt"> Imponer deuda</p>
                            <p><input type="submit" class="btn btn-success" value="Enviar"></p>
                        </form></td></tr>';
            }
            echo '</table>';
        }else{
            echo '<p style="color:yellow;">Todos los equipos del prestamo han sido registrados</p>';
        }
        ?>
    </table>
    <form  method="post">
        <input type="submit" class="btn btn-success" name="desactivate" value="Desactivar">
        <input type="submit" name="edit" class="btn btn-warning" value="Agregar Equipos">
        <input type="submit" name="back" class="btn btn-light" value="Regresar">
    </form>
    </form>
    </section>
    <?php require_once 'footer.html'; ?>  
</body>
</html>