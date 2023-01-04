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
// Envio codigo equipo
if (isset($_POST['search_code']) && !isset($_POST['back'])){
    // Validar codigo equipo
    if (strlen($_POST['search_code'])<1){
        unset($_SESSION['code']);
        $_SESSION['error']='Codigo interno requerido';
        header('location:edit_equipment.php');
        return;
    }
    // Buscar equipo
    $stmt = $pdo->prepare('SELECT * FROM equipment_codes JOIN codes 
    ON equipment_codes.code_id=codes.code_id WHERE codes.code=:code');
    $stmt->execute(array(':code'=>$_POST['search_code']));
    $equipment = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($equipment == false){
        unset($_SESSION['code']);
        $_SESSION['error']='No se encontro el equipo';
        header('location:edit_equipment.php');
        return;
    }else{
        $_SESSION['success']='Equipo encontrado';
        $_SESSION['code'] = $_POST['search_code'];
        header('location:edit_equipment.php');
        return;
    }   
}
// Cargar caracteristicas del equipoS
if (isset($_SESSION['code'])){
    $stmt = $pdo->prepare('SELECT * FROM equipment_codes JOIN codes JOIN full_equipment JOIN equipment 
    JOIN types JOIN models JOIN consumables JOIN descriptions ON equipment_codes.code_id=codes.code_id 
    AND equipment_codes.equipment_id=full_equipment.full_equipment_id AND 
    full_equipment.equipment_id=equipment.equipment_id AND full_equipment.type_id=types.type_id AND 
    full_equipment.model_id=models.model_id AND full_equipment.consumable_id=consumables.consumable_id 
    AND full_equipment.description_id=descriptions.description_id WHERE codes.code=:code');
    $stmt->execute(array(':code'=>$_SESSION['code']));
    $equipment = $stmt->fetch(PDO::FETCH_ASSOC);
    $code = $equipment['code'];$equipment_name = $equipment['equipment'];$type = $equipment['type'];
    $model = $equipment['model'];$consumable = $equipment['consumable'];$description = $equipment['description'];
    $code_id = $equipment['code_id'];$equipment_id = $equipment['full_equipment_id'];
}else{
    $code = false;$equipment_name = false;$type = false;$model = false;$consumable = false;$description = false;
}
// Envio POST
if ( isset($_POST['code']) && isset($_POST['equipment']) && isset($_POST['type']) 
&& isset($_POST['model']) && isset($_POST['description'])  && !isset($_POST['back'])){
    // Revision de errores
    $msg = ValidateEquipmentPOST();
    if (is_string($msg)){
        $_SESSION['error']=$msg;
        header('location:edit_equipment.php');
        return;
    }
    if (isset($_POST['edit'])){
        // Modificacion del equipo
    $stm = $pdo->prepare('UPDATE equipment_codes JOIN codes JOIN full_equipment 
    JOIN equipment JOIN types JOIN models JOIN consumables JOIN descriptions ON 
    equipment_codes.equipment_id=full_equipment.full_equipment_id AND 
    equipment_codes.code_id=codes.code_id AND full_equipment.equipment_id=equipment.equipment_id 
    AND full_equipment.type_id=types.type_id AND full_equipment.model_id=models.model_id 
    AND full_equipment.consumable_id=consumables.consumable_id AND 
    full_equipment.description_id=descriptions.description_id SET codes.code=:c, 
    equipment.equipment=:e, types.type=:t, models.model=:m, consumables.consumable=:co, 
    descriptions.description=:d WHERE equipment_codes.equipment_id=:e_id AND equipment_codes.code_id=:c_id');
    $stm->execute(array(
        ':c'=>$_POST['code'],
        ':e'=>$_POST['equipment'], 
        ':t'=>$_POST['type'], 
        ':m'=>$_POST['model'],  
        ':co'=>$_POST['consumable'],  
        ':d'=>$_POST['description'],  
        ':e_id'=>$equipment_id,  
        ':c_id'=>$code_id));
    unset($_SESSION['code']);
    $_SESSION['success'] = 'Equipo modificado';
    header( 'Location:edit_equipment.php' ) ;
    return;
    }
    if (isset($_POST['delete'])){
        $stm = $pdo->prepare('DELETE FROM equipment_codes 
        WHERE code_id=:c_id AND equipment_id=:e_id');
        $stm->execute(array(':e_id'=>$equipment_id, ':c_id'=>$code_id));
        unset($_SESSION['code']);
        $_SESSION['success'] = 'Equipo borrado';
        header( 'Location:edit_equipment.php' ) ;
        return;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="images/escudo3.png">
    <meta charset="utf-8">
    <title>Modificar Equipos</title>
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
    <h2>Modificar Equipos</h2>
    <form method="post">
        <p><label for="search_code">Codigo:</label></p>
        <div class="input-group mb-3">
        <input type="text" name="search_code" class="form-control" placeholder="Ingrese el codigo">
        <input type="submit" class="btn btn-success" value="Buscar"></div>
    </form>
    <!-- Imprimir errores -->
    <?php ShowMessages(); ?>
    <form method="post">
        <p><label for="code">Codigo: </label></p>
        <input type="text" class="form-control" placeholder="Ingrese el codigo" name="code" value=<?=$code?>>
        <p><label for="equipment">Nombre: </label></p>
        <input type="text" class="form-control" placeholder="Ingrese el nombre" name="equipment" value=<?=$equipment_name?>>
        <p><label for="type">Tipo: </label></p>
        <input type="text" class="form-control" placeholder="Ingrese el tipo" name="type" value=<?=$type?>>
        <p><label for="model">Modelo: </label></p>
        <input type="text" class="form-control" placeholder="Ingrese el modelo" name="model" value=<?=$model?>>
        <p><label for="consumable">Consumible: </label></p>
        <select class="form-select" name="consumable">
            <option value="0">-- Seleccione una opción --</option>
            <option value="SI"<?php if($consumable=='SI') echo 'selected';?>>SI</option>
            <option value="NO"<?php if($consumable=='NO') echo 'selected';?>>NO</option>
        </select>
        <p><label for="description">Descripción:</label></p>
        <p><textarea name="description" class="form-control" cols="30" rows="3"><?=$description?></textarea></p><br><br>
        <p><input type="submit" class="btn btn-success" name="edit" value="Modificar">
        <input type="submit" class="btn btn-warning" name="delete" value="Borrar">
        <input type="submit" class="btn btn-light" name="back" value="Regresar"></p>
    </form>
    </div>
</form>
</section>
    <?php require_once 'footer.html'; ?>  
</body>
</html>