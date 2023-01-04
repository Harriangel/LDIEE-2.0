<?php
// Comienzar sesion
session_start();
// Conexion con la base de datos
require_once "pdo.php";
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Regresar a la pagina principal
Back('admin.php');
// Rececepcion de datos del formulario
if ( isset($_POST['code']) && isset($_POST['start']) && isset($_POST['end']) && isset($_POST['equipment']) && isset($_POST['type']) 
&& isset($_POST['model']) && isset($_POST['description'])  && !isset($_POST['back'])){
    // Revision de errores
    $msg = ValidateEquipmentPOST();
    if (is_string($msg)){
        $_SESSION['error']=$msg;
        header('location:add_equipment.php');
        return;
    }
    if (strlen($_POST['start'])<1 || strlen($_POST['end'])<1){
        $_SESSION['error']='Dede selecionar un numero de inicio y de final';
        header('location:add_equipment.php');
        return;
    }
    // Ids caracteristicas equipo
    $equipment_id=SearchInsert($pdo, 'equipment', 'equipment');
    $type_id=SearchInsert($pdo, 'types', 'type');
    $model_id=SearchInsert($pdo, 'models', 'model');
    $consumable_id=$_POST['consumable'];
    $description_id=SearchInsert($pdo, 'descriptions', 'description');
    // Id equipo completo
    $stm = $pdo->prepare('SELECT * FROM full_equipment WHERE equipment_id=:e_id AND 
    type_id=:t_id AND model_id=:m_id AND consumable_id=:c_id AND description_id=:d_id');
    $stm->execute(array(
        ':e_id'=>$equipment_id,
        ':t_id'=>$type_id,
        ':m_id'=>$model_id,
        ':c_id'=>$consumable_id,
        ':d_id'=>$description_id));
    $full_equipment =  $stm->fetch(PDO::FETCH_ASSOC);
    if ($full_equipment==false){
        $stm = $pdo->prepare('INSERT INTO full_equipment(equipment_id, type_id, 
        model_id, consumable_id, description_id) VALUES (:e_id, :t_id, :m_id, :c_id, :d_id)');
        $stm->execute(array(
            ':e_id'=>$equipment_id,
            ':t_id'=>$type_id,
            ':m_id'=>$model_id,
            ':c_id'=>$consumable_id,
            ':d_id'=>$description_id));
        $full_equipment_id = $pdo->lastInsertId();
    }else{$full_equipment_id = $full_equipment['full_equipment_id'];}
    // Generar codigos
    $codes = array();
    for($i=$_POST['start']; $i<=$_POST['end']; $i++){
        $codes[]= $_POST['code'].'-'.$i;
    }
    // Validar codigos e insertarlos
    foreach($codes as $code){
        $stm = $pdo->prepare('SELECT * FROM codes WHERE code=:c');
        $stm->execute(array(':c'=>$code));
        $result =  $stm->fetch(PDO::FETCH_ASSOC);
        if ($result==false){
            $stm = $pdo->prepare('INSERT INTO codes(code) VALUES (:c)');
            $stm->execute(array(':c'=>$code));
            $code_id = $pdo->lastInsertId();
        }else{$code_id =$result['code_id'];}
        $stm = $pdo->prepare('SELECT * FROM equipment_codes 
        WHERE code_id=:c AND equipment_id=:e_id');
        $stm->execute(array(':c'=>$code_id, ':e_id'=>$full_equipment_id));
        $equipment = $stm->fetch(PDO::FETCH_ASSOC);
        if ($equipment==false){
            $stm = $pdo->prepare('INSERT INTO equipment_codes(code_id, equipment_id) 
            VALUES (:c, :e_id)');
            $stm->execute(array(':c'=>$code_id, ':e_id'=>$full_equipment_id));
        }        
    }    
    $_SESSION['success'] = 'Equipos agregados';
    header( 'Location:add_equipment.php' ) ;
    return;
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo3.png">
    <meta charset="utf-8">
    <title>Agregar Equipos</title>
<meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo1.css'>
    <script src='main.js'></script>
</head>
<body>
    <!-- Barra de navegación -->
    <?php require_once 'navbar_admin.htm';  ?>
    <section class="form-register">
    <form method="post">
  <div align="center">
    <h2>Agregar Equipos</h2>
    <!-- Imprimir errores -->
    <?php ShowMessages(); ?>
    <form method="post">
    <div class="row">
    <div class="col">
</div>
    <div class="col">
    <label for="code">Indice Codigo: </label>
        <input type="text"  class="form-control" style="width:70px;" name="code" placeholder="XX">
    </div>
    <div class="col">
    <label for="start">Inicio: </label>
        <input type="text" class="form-control" style="width:70px;" name="start" placeholder="XX">
    </div>
    <div class="col">
    <label for="end">Final: </label>
        <input type="text" class="form-control" style="width:70px;" name="end" placeholder="XX">
    </div>
    <div class="col">
</div>
  </div>
        
        
        
        <p><label for="equipment">Nombre: </label></p>
        <input type="text" class="form-control" name="equipment" placeholder="Ingrese el nombre del equipo">
        <p><label for="type">Tipo: </label></p>
        <input type="text" class="form-control" name="type" placeholder="Ingrese el tipo de equipo">
        <p><label for="model">Modelo: </label></p>
        <input type="text" class="form-control" name="model" placeholder="Ingrese el modelo del equipo">
        <p><label for="consumable">Consumible: </label></p>
        <select name="consumable" class="form-select">
            <option value="0">-- Seleccione una opción --</option>
            <option value="1">SI</option>
            <option value="2">NO</option>
        </select>
        <p><label for="description">Descripción:</label></p>
        <p><textarea name="description" class="form-control" cols="30" rows="3"></textarea></p><br><br>
        <p><input type="submit" class="btn btn-success" value="Agregar">
        <input type="submit" name="back" class="btn btn-light" value="Regresar"></p>
    </form>
    </div>
</form>
</section>
    <?php require_once 'footer.html'; ?>  
</body>
</html>