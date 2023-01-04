<?php
// Comenzar session
session_start();
// Conexion con la base de datos
require_once "pdo.php";
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Regresar al historial
Back('history.php');
// Validar GET
$request_id = ValidateRequest_id();
if (!is_numeric($request_id)){
    $_SESSION['error']=$request_id;
    header('location:history.php');
    return;
}
// Validar request_id
$request = LoadRequest($pdo, $request_id, 'Desactivado');
if ( $request == false ) {
    $_SESSION['error'] = 'No se pudo cargar la solicitud';
    header( 'Location: history.php' ) ;
    return;
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo3.png">
    <meta charset="utf-8">
    <title>Detalle de prestamo</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo.css'>
    <script src='main.js'></script></head>
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_admin.htm'; ?>
    <section class="form-register">
    <form method="post">
  <div align="center">
    <h2>Detalle del prestamo</h2>
    <table class="table table-dark table-hover">
        <tr><th>Estudiante</th>
        <th>Modalidad</th>
        <th>Fecha de Solicitud</th>
        <th>Curso</th>
        <?php
        if ($request['modalidad']!='Casa'){
            echo '<th>Fecha</th>';
            echo '<th>Hora Inicial</th>';
            echo '<th>Hora Final</th>';
        }else{
            echo '<th>Fecha Inicial</th>';
            echo '<th>Fecha Final</th>';
        }
        echo '<th>Grupo de Trabajo</th></tr>';
        // Cargar prestamo
        $stm = $pdo->prepare('SELECT * FROM requests JOIN students 
        ON requests.ti_un=students.ti_un WHERE requests.request_id=:r_id');
        $stm->execute(array(':r_id'=>$request_id));
        $request = $stm->fetch(PDO::FETCH_ASSOC);
        echo '<tr>';
        echo '<td>'.$request['name'].' '.$request['last_name'].'</td>';
        echo '<td>'.$request['modalidad'].'</td>';
        echo '<td>'.$request['fecha_solicitud'].'</td>';
        echo '<td>'.$request['curso'].'</td>';
        if ($request['modalidad']!='Casa'){
            echo '<td>'.$request['fecha'].'</td>';
            echo '<td>'.$request['hora_ini'].'</td>';
            echo '<td>'.$request['hora_fin'].'</td>';
        }else{
            echo '<td>'.$request['fecha_ini'].'</td>';
            echo '<td>'.$request['fecha_fin'].'</td>';
        }        
        echo '<td><ul>';
        // Grupo de trabajo
        $stm = $pdo->prepare('SELECT * FROM members JOIN courses ON members.course_id=courses.course_id 
        WHERE name=:c AND student_id=:s_id');
        $stm->execute(array(':c'=>$request['curso'], ':s_id'=>$request['ti_un']));
        $member = $stm->fetch(PDO::FETCH_ASSOC); 
        $work_group = $member['work_group']; 
        $course_id=$member['course_id'];
        // Compañeros
        $stm = $pdo->prepare('SELECT * FROM members JOIN students ON members.student_id=students.ti_un 
        WHERE course_id=:c_id AND work_group=:g');
        $stm->execute(array(':c_id'=>$course_id, ':g'=>$work_group));
        while ($partner = $stm->fetch(PDO::FETCH_ASSOC)){
            echo '<li>'.$partner['name'].' '.$partner['last_name'].'</li>';
        }
        echo '</ul></td></tr>';
        ?>
    </table>
    <h2>Equipo Entregado</h2>
    <table class="table table-dark table-hover">
        <tr><th>Cantidad</th>
        <th>Equipo</th>
        <th>Tipo</th>
        <th>Modelo</th>
        <th>Codigo</th>
        <th>Comentarios</th></tr>
        <?php 
        // Equipos del prestamo
        $equipment_id = LoadAllEquipment_id($pdo, $request_id);
        $quantity = array_count_values($equipment_id); 
        $equipment_id = array_unique($equipment_id);
        foreach ($equipment_id as $equip_id){
            $stm = $pdo->query('SELECT * FROM requests_equipment_codes JOIN 
            full_equipment JOIN equipment JOIN types JOIN models ON 
            requests_equipment_codes.equipment_id=full_equipment.full_equipment_id 
            AND full_equipment.equipment_id=equipment.equipment_id AND 
            full_equipment.type_id=types.type_id AND full_equipment.model_id=models.model_id 
            WHERE requests_equipment_codes.equipment_id='.$equip_id);
            $equip = $stm->fetch(PDO::FETCH_ASSOC);
            echo '<tr><td>'.$quantity[$equip_id].'</td>';
            echo '<td>'.$equip['equipment'].'</td>';
            echo '<td>'.$equip['type'].'</td>';
            echo '<td>'.$equip['model'].'</td>';
            echo '<td><ul>';
            $stm = $pdo->query('SELECT * FROM requests_equipment_codes JOIN full_equipment JOIN codes 
            ON requests_equipment_codes.equipment_id=full_equipment.full_equipment_id AND 
            requests_equipment_codes.code_id=codes.code_id WHERE requests_equipment_codes.equipment_id='.$equip_id.' 
            AND request_id='.$request_id);
            while ($code = $stm->fetch(PDO::FETCH_ASSOC)){
                echo '<li>'.$code['code'].'</li>';
            }
            echo '</ul></td>';
            // Comentarios
            echo '<td><ul>';
            $stm = $pdo->query('SELECT * FROM history JOIN requests_equipment_codes 
            ON history.rec_id=requests_equipment_codes.rec_id 
            WHERE equipment_id='.$equip_id.' AND request_id='.$request_id);
            while ($history = $stm->fetch(PDO::FETCH_ASSOC)){
                echo '<li>'.$history['comments'].'</li>';
            }
            echo '</ul></td></tr>';
        }
        ?>
    </table>
    <form  method="post">
        <input type="submit" name="back" class="btn btn-light" value="Regresar">
    </form>
    </div>
    </form>
</section>
    <?php require_once 'footer.html'; ?>  
</body>
</html>