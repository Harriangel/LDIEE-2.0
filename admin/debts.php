<?php
// Comenzar sesion
session_start();
// Conexion con la base de datos
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Zona horaria
date_default_timezone_set('America/Bogota');
// Comprobar inicio de sesion
ValidateLogIn();
// Regresar a todas las deudas
Back('debts.php');
// Envio POST
if (isset($_POST['ti_un']) && isset($_POST['document']) && !isset($_POST['back'])){
    // Validar codigo 
    $msg = ValidateDocument();
    if (is_string($msg)){
        unset($_SESSION['ti_un']);
        $_SESSION['error']=$msg;
        header('location:debts.php');
        return;
    }
    // Cargar ti_un
    Loadti_un($pdo);
    // Cargar deuda
    $stm = $pdo->prepare('SELECT * FROM debts JOIN requests_equipment_codes JOIN requests 
    ON debts.rec_id=requests_equipment_codes.rec_id 
    AND requests_equipment_codes.request_id=requests.request_id AND requests.ti_un = :id');
    $stm->execute(array(":id"=>$_POST['ti_un']));
    $debt = $stm->fetch(PDO::FETCH_ASSOC);
    // Validar deuda
    if ($debt == false){
        $_SESSION['error']='El estudiante no tiene ninguna deuda';
        header('location:debts.php');
        return;
    }
    $_SESSION['success']='Deuda encontrada';
    $_SESSION['ti_un'] = $_POST['ti_un'];
    header('location:debts.php');
    return;     
}
// Borrar deuda
if (isset($_POST['update'])){
    // Actualizar estado de la solicitud
    UpdateState($pdo, 'Desactivado');
    // Actualizar estado de la deuda
    $stm = $pdo->prepare('UPDATE debts SET state="Saldada" WHERE debt_id=:d_id');
    $stm->execute(array(':d_id'=>$_POST['debt_id']));
    // Mensaje deuda saldada
    $message= ' (Deuda Saldada: '.date('d-m-y').')';
    $stm = $pdo->prepare('UPDATE history SET comments=:co WHERE rec_id=:rec_id');
    $stm->execute(array(':co'=>$_POST['comments'].$message,':rec_id'=>$_POST['rec_id']));
    $_SESSION['success'] = 'Deuda Saldada';
    header( 'Location:debts.php' ) ;
    return;
}
if (isset($_POST['delete'])){
    $stm = $pdo->prepare('DELETE FROM debts WHERE debt_id=:d_id');
    $stm->execute(array(':d_id'=>$_POST['debt_id']));
    $_SESSION['success'] = 'Deuda Borrada';
    header( 'Location:debts.php' ) ;
    return;
}
// Cargar las deudas
$debts = Load($pdo, 'debts');
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo3.png">
    <meta charset="utf-8">
    <title>Deudas Activas</title>
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
    <h2>Deudas</h2></div>
    <p><form method="post">
    </p><div align="center"><h6>A continuacion realice la busqueda con alguna de las opciones:</h6><div>
        <p><label for="ti_un">Codigo TI UN:</label></p>
        <input type="text" name="ti_un" class="form-control" placeholder="Ingrese el codigo universitario">
        <p><label for="document">Cedula:</label></p>
        <input type="text" name="document" class="form-control" placeholder="Ingrese el numero de documento"></p>
        <p><input type="submit" class="btn btn-success" value="Buscar">
       <input type="submit" class="btn btn-light" name="back" value="Regresar">
    </form></p>

    <?php
    // Imprimir mensajes
    ShowMessages();
    // Revisar si hay historial
    if (count($debts) > 0){
        // Mostar las Deudas
        echo '<table class="table table-dark table-hover">';
        echo '<tr><th>ti_un</th>';
        echo '<th>Estudiante</th>';
        echo '<th>Modalidad</th>';
        echo '<th>Curso</th>';
        echo '<th>Equipo</th>';
        echo '<th>Comentarios</th>';
        echo '<th>Estado</th>';
        echo '<th>Opciones</th></tr>';
        $sql = 'SELECT * from requests_equipment_codes JOIN debts JOIN students JOIN requests JOIN full_equipment 
            JOIN equipment JOIN types JOIN models JOIN codes ON requests_equipment_codes.ti_un = requests.ti_un
            AND requests.ti_un = students.ti_un AND 
            requests_equipment_codes.rec_id= debts.rec_id AND
            requests_equipment_codes.code_id = codes.code_id AND 
            requests_equipment_codes.request_id=requests.request_id AND 
            requests_equipment_codes.equipment_id=full_equipment.full_equipment_id AND 
            full_equipment.equipment_id=equipment.equipment_id AND full_equipment.type_id=types.type_id AND 
            full_equipment.model_id=models.model_id';
        // Deudas del usuario
        if (isset($_SESSION['ti_un'])){
            $sql = $sql.' WHERE requests.ti_un=:r_tu';
            $stm = $pdo->prepare($sql);
            $stm->execute(array(':r_tu'=>$_SESSION['ti_un']));
            unset($_SESSION['ti_un']);
        }
        // Todos los prestamos
        else{
            $stm = $pdo->query($sql);
        }
        while ( $debt = $stm->fetch(PDO::FETCH_ASSOC) ) {
            echo '<tr><td>'.$debt['ti_un'].'</td>';
            echo '<td>'.$debt['name'].' '.$debt['last_name'].'</td>';
            echo '<td>'.$debt['modalidad'].'</td>';
            echo '<td>'.$debt['curso'].'</td>';
            echo '<td>'.$debt['equipment'].' | '.$debt['type'].' | '.$debt['model'].' | '.$debt['code'].'</td>';
            echo '<td>'.$debt['comments'].'</td>';
            echo '<td>'.$debt['state'].'</td>';
            if ($debt['state']=='Pendiente'){
                echo '<td><form method="post">
                <input type="hidden" name="debt_id" value="'.$debt['debt_id'].'">
                <input type="hidden" name="rec_id" value="'.$debt['rec_id'].'">
                <input type="hidden" name="comments" value="'.$debt['comments'].'">
                <input type="submit" class="btn btn-success" name="update" value="Saldar">
                </form></td>';
            }else{
                echo '<td><form method="post">
                <input type="hidden" name="debt_id" value="'.$debt['debt_id'].'">
                <input type="hidden" name="rec_id" value="'.$debt['rec_id'].'">
                <input type="hidden" name="comments" value="'.$debt['comments'].'">
                <input type="submit" class="btn btn-warning" name="delete" value="Borrar">
                </form></td>';
            }
            echo '</tr>';
        }
        echo '</table>';  
    }else{
        echo '<p style="color:yellow;">En este momento no hay deudas activas</p>';  
    }
      
    ?>
        </form>
</section>
    <?php require_once 'footer.html'; ?>  
</body>
</html>



