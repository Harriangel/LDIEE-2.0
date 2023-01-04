<?php
// Comenzar sesion
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
// Agregar equipo al prestamo
if (isset($_POST['code']) && isset($_POST['add'])){
    // Validar envio
    $msg = ValidateCode();
    if (is_string($msg)){
        $_SESSION['error'] = $msg;
        header('location:'.Refresh('activate', $request_id, $ti_un));
        return;
    }
    // Buscar equipo
    $stmt = $pdo->prepare('SELECT * FROM equipment_codes JOIN codes JOIN full_equipment
    ON equipment_codes.code_id=codes.code_id AND 
    equipment_codes.equipment_id=full_equipment.full_equipment_id WHERE codes.code=:code');
    $stmt->execute(array(':code'=>$_POST['code']));
    $equipment = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($equipment == false){
        $_SESSION['error']='No se encontro el equipo';
        header('location:'.Refresh('edit_equipment', $request_id, $ti_un));
        return;
    }
    $equipment_id = $equipment['full_equipment_id'];
    $code_id = $equipment['code_id'];
    // Insertar equipo al prestamo
    $stm = $pdo->prepare('INSERT INTO requests_equipment_codes(request_id, ti_un, equipment_id, code_id, state) 
        VALUES (:r_id, :t_u, :e_id, :c_id, "Activo")');
    $stm->execute(array(
        ':r_id' => $request_id,
        ':t_u' => $ti_un,
        ':e_id' => $equipment_id,
        ':c_id' => $code_id));
    $_SESSION['success'] = 'Equipo Agregado';
    header('location:'.Refresh('edit_equipment', $request_id, $ti_un));
    return;
    }
// Borrar equipo del prestamo
if (isset($_POST['delete'])){
    DeleteEquipment($pdo);
    $_SESSION['success'] = 'Equipo Borrado';
    header('location:'.Refresh('edit_equipment', $request_id, $ti_un));
    return;
}
// Regresar a activar o desactivar solicitud
if (isset($_POST['back'])){
    if ($_SESSION['action']=='activate'){
        unset($_SESSION['action']);
        header('location:'.Refresh('activate', $request_id, $ti_un));
        return;
    }else{
        unset($_SESSION['action']);
        header('location:'.Refresh('desactivate', $request_id, $ti_un));
        return;
    }
}

// Cargar equipos agregados
$equipment_add = LoadEquipmentAdd($pdo, $request_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" href="images/escudo3.png">
    <meta charset="utf-8">
    <title>Añadir Equipos</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo.css'>
    <script src='main.js'></script>
</head>
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_admin.htm';?>
    <section class="form-register">
    <form method="post">
      <div align="center">
      <h2>Agregar Equipos</h2>
    <?php ShowMessages(); ?> 
    <!-- Formulario -->
    <form method="post">
        <!-- Equipos -->
        <p><label for="equipment_id">Codigo del equipo: </label></p>
        <input type="text" class="form-control" name="code" placeholder="Ingrese el codigo del equipo"></p>
        <p><input type="submit" name="add" class="btn btn-success" value="Agregar"></p>
    </form>
    <h2>Equipos Agregados</h2> 
    <?php
    if (count($equipment_add) > 0){
        echo'<table class="table table-dark table-hover">
            <tr>
                <th>Cantidad</th>
                <th>Equipo</th>
                <th>Tipo</th>
                <th>Modelo</th>
                <th>Opciones</th>
            </tr>';
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
            echo '<td><form method="post">';
            echo '<input type="hidden" name="rec_id"  value="'.$equip['rec_id'].'">';
            echo '<input type="submit" name="delete" class="btn btn-warning"  value="Eliminar"></form></td></tr>';
        }
        echo '</table></p>';
    }else{
        echo '<p style="color:yellow;">Aun no ha agregado ningun equipo</p>';
    }              
    ?>
    <form method="post">
        <p><input type="submit" name="back" class="btn btn-light" value="Regresar"></p>
    </form>
    </form>
    </section>
    <?php require_once 'footer.html'; ?>  
</body>
</html>