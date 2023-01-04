<?php
// Comenzar sesion
session_start();
// Conexion con la base de datos 
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Cargar deudas
$stmt = $pdo->prepare('SELECT * from requests_equipment_codes JOIN debts JOIN requests JOIN full_equipment 
JOIN equipment JOIN types JOIN models JOIN codes ON requests_equipment_codes.rec_id= debts.rec_id AND
requests_equipment_codes.code_id = codes.code_id AND requests_equipment_codes.request_id=requests.request_id 
AND requests_equipment_codes.equipment_id=full_equipment.full_equipment_id AND 
full_equipment.equipment_id=equipment.equipment_id AND full_equipment.type_id=types.type_id AND 
full_equipment.model_id=models.model_id WHERE requests.ti_un=:s_id');
$stmt->execute(array(':s_id'=>$_SESSION['student_id']));
$debts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/shield.png">
    <meta charset="utf-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Mis Deudas</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo_prestamos.css'>
</head>
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_student.html';?>
    <section class="form-register">
  <div align='center'>
  <form method="post">
    <h2>Mis deudas</h2>
    <?php
    // Validar existencia de deudas
    if (count($debts)>0){
        // Tabla
        echo '<p><table class="table table-dark table-hover">
                <tr>
                    <th>Modalidad</th>
                    <th>Fecha</th>
                    <th>Curso</th>
                    <th>Equipo</th>
                    <th>Comentarios</th>
                    <th>Estado</th>
                </tr>';
        //Imprimir deudas
        for($i=0; $i<count($debts); $i++){
            echo '<tr><td>'.$debts[$i]['modalidad'].'</td>';
            echo '<td>'.$debts[$i]['fecha'].'</td>';
            echo '<td>'.$debts[$i]['curso'].'</td>';
            echo '<td>'.$debts[$i]['equipment'].' | '.$debts[$i]['type'].' | '.
            $debts[$i]['model'].' | '.$debts[$i]['code'].'</td>';
            echo '<td>'.$debts[$i]['comments'].'</td>';
            echo '<td>'.$debts[$i]['state'].'</td></tr>';
        }            
        echo '</table></p>';
    }else{
        echo '<p style="color:yellow;">En este momento no tiene deudas</p>';
    }
    ?>
 </form>
    </div>
  </section>
  <?php require_once 'footer.html'; ?> 
       
</body>
</html>