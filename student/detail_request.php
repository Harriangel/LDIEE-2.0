<?php
// Comenzar session
session_start();
// Conexion con la base de datos
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Validar GET
$msg = ValidateGET();
if (is_string($msg)){
    $_SESSION['error']=$msg;
    header('location:requests_student.php');
    return;
}
$request_id=$_GET['request_id'];
$state = $_GET['estado'];
// Cargar solicitud
$request = LoadRequest($pdo,$request_id,$state);
if ( $request == false ) {
    $_SESSION['error'] = 'Información Invalida';
    header('location:requests_student.php');
    return;
}
// Acciones para prestamos sin activar o activos
$edit = $state =='Sin Activar' ? '<input type="submit" class="btn btn-success" name="edit" value="Editar">' : false ;
$delete = $state =='Sin Activar' ? ' <input type="submit" class="btn btn-warning " name="delete" value="Cancelar Prestamo">' : false ;
// Redirigir a editar prestamo
if (isset($_POST['edit'])){
    header('location:edit_request.php?request_id='.$request_id.'&estado='.$state);
    return;
}
// Cancelar prestamo
if (isset($_POST['delete'])){
    // Borrar equipos del prestamo
    $smt = $pdo->prepare('DELETE FROM requests_equipment_codes WHERE request_id=:r_id');
    $smt->execute(array(':r_id'=>$request_id));
    // Borrar prestamo
    $smt = $pdo->prepare('DELETE FROM requests WHERE request_id=:r_id');
    $smt->execute(array(':r_id'=>$request_id));
    // Mensaje de confirmación y redirección
    $_SESSION['success'] = 'Prestamo cancelado';
    header('location:requests_student.php');
    return;
}
// Regresar a todos los prestamos
Back('requests_student.php');
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/shield.png">
    <meta charset="utf-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Detalle del prestamo</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo_prestamos.css'>
</head>
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_student.html'; ?>
    <section class="form-register">
    <div align='center'>
    <form method="post">
    <h2>Detalle del prestamo</h2>
    <?php ShowMessages(); ?>
    <table class="table table-dark table-hover">
        <tr><th>Modalidad</th>
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
        $stm->execute(array(':c'=>$request['curso'], ':s_id'=>$_SESSION['student_id']));
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
    <?php
    if ($state == 'Sin Activar') echo '<h2>Equipo Solicitado</h2>';
    else echo '<h2>Equipo Entregado</h2>';
    ?>
    <table class="table table-dark table-hover">
        <tr><th>Cantidad</th>
        <th>Equipo</th>
        <th>Tipo</th>
        <th>Modelo</th>
        <?php
        if ($state == 'Activo') echo '<th>Codigo</th>';
        elseif ($state == 'Desactivado') echo '<th>Codigo</th><th>Comentarios</th>';
        ?>
        </tr>
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
            if ($state != 'Sin Activar'){
                echo '<td><ul>';
                $stm = $pdo->query('SELECT * FROM requests_equipment_codes JOIN full_equipment JOIN codes 
                ON requests_equipment_codes.equipment_id=full_equipment.full_equipment_id AND 
                requests_equipment_codes.code_id=codes.code_id WHERE requests_equipment_codes.equipment_id='.$equip_id.' 
                AND request_id='.$request_id);
                while ($code = $stm->fetch(PDO::FETCH_ASSOC)){
                    echo '<li>'.$code['code'].'</li>';
                }
                echo '</ul></td>';
            }
            if ($state == 'Desactivado'){
                // Comentarios
            echo '<td><ul>';
            $stm = $pdo->query('SELECT * FROM history JOIN requests_equipment_codes 
            ON history.rec_id=requests_equipment_codes.rec_id 
            WHERE equipment_id='.$equip_id.' AND request_id='.$request_id);
            while ($history = $stm->fetch(PDO::FETCH_ASSOC)){
                echo '<li>'.$history['comments'].'</li>';
            }
            echo '</ul></td>';
            }
            echo '</tr>';
            
        }
        ?>
    </table>   
    <form  method="post">
        <p><?= $edit ?><?= $delete ?>
        <input type="submit" name="back" class="btn btn-light" value="Regresar"></p>
        
    </form>
    </form>
    </div>
  </section>
  <?php require_once 'footer.html'; ?> 
    
</body>
</html>