<?php
// Comenzar sesion
session_start();
// Conexion con la base de datos 
require_once 'pdo.php';
// Funciones
require_once 'functions.php';
// Comprobar inicio de sesion
ValidateLogIn();
// Envio POST
if (isset($_POST['equipment_id']) && isset($_POST['type_id']) && isset($_POST['model_id']) 
        && !isset($_POST['add'])){
    // Envios vacios
    if (isset($_POST['select_equipment'])){
        if ($_POST['equipment_id']==0){
            unset($_SESSION['equipment_id']);
            $_SESSION['error'] = 'Debe seleccionar un equipo';
            header('location:add_equipment.php');
            return;
        }else{
            unset($_SESSION['type_id']); unset($_SESSION['model_id']);
            $_SESSION['equipment_id']= $_POST['equipment_id'];
            header('location:add_equipment.php');
            return;
        }
    }
    if (isset($_POST['select_type'])){
        if ($_POST['type_id']==0){
            unset($_SESSION['type_id']);
            $_SESSION['error'] = 'Debe seleccionar un tipo';
            $_SESSION['equipment_id']= $_POST['equipment_id'];
            header('location:add_equipment.php');
            return;
        }else{
            unset($_SESSION['model_id']);
            $_SESSION['type_id']= $_POST['type_id'];
            header('location:add_equipment.php');
            return;
        }
    }
    if (isset($_POST['select_model'])){
        if ($_POST['model_id']==0){
        unset($_SESSION['model_id']);
        $_SESSION['error'] = 'Debe seleccionar un modelo';
        $_SESSION['type_id']= $_POST['type_id'];
        header('location:add_equipment.php');
        return;
        }else{
            $_SESSION['model_id']= $_POST['model_id'];
            header('location:add_equipment.php');
            return;
        }
    }
}
// Agregar equipo al prestamo
if (isset($_POST['add'])){
    // Campos vacios
    if ($_POST['equipment_id']==0 || $_POST['type_id']==0 || $_POST['model_id']==0 || strlen($_POST['quantity']) < 1){
        $_SESSION['error'] = 'Todos los campos son requeridos';
        header('location:add_equipment.php');
        return;
    }
    // Buscar id del equipo 
    $stm = $pdo->prepare('SELECT * FROM full_equipment WHERE equipment_id=:e_id AND 
    type_id=:t_id AND model_id=:m_id');
    $stm->execute(array(
        ':e_id' => $_SESSION['equipment_id'],
        ':t_id' => $_SESSION['type_id'],
        ':m_id' => $_SESSION['model_id']));
    $full_equipment = $stm->fetch(PDO::FETCH_ASSOC);
    $full_equipment_id = $full_equipment['full_equipment_id'];
    // Agregar
    for ($i=0; $i < $_POST['quantity']; $i++){
        $stm = $pdo->prepare('INSERT INTO requests_equipment_codes(request_id, ti_un, equipment_id, state) 
        VALUES (:r_id, :t_u, :e_id, "Sin Activar")');
        $stm->execute(array(
            ':r_id' => $_SESSION['request_id'],
            ':t_u' => $_SESSION['student_id'],
            ':e_id' => $full_equipment_id));
    } 
    // Borrar ids del equipo y redireccionar
    unset($_SESSION['equipment_id']); unset($_SESSION['type_id']); unset($_SESSION['model_id']);
    $_SESSION['success'] = 'Equipo/s Agregado/s';
    header('location:add_equipment.php');
    return;
}
// Borrar equipo del prestamo
if (isset($_POST['delete'])){
    DeleteEquipment($pdo);
    unset($_SESSION['equipment_id']); unset($_SESSION['type_id']); unset($_SESSION['model_id']);
    $_SESSION['success'] = 'Equipo Borrado';
    header('location:add_equipment.php');
    return;
}
// Finalizar reserva
if (isset($_POST['end'])){
    unset($_SESSION['request_id']);
    $_SESSION['success'] = 'Reserva creada';
    header('location:student.php');
    return;
}
// Validar si se esta añadiendo equipo a un prestamo ya creado
if (!isset($_SESSION['state']) && isset($_POST['cancel'])){
    // Borrar equipos del prestamo
    $smt = $pdo->prepare('DELETE FROM requests_equipment_codes WHERE request_id=:r_id');
    $smt->execute(array(':r_id'=>$_SESSION['request_id']));
    // Borrar prestamo
    $smt = $pdo->prepare('DELETE FROM requests WHERE request_id=:r_id');
    $smt->execute(array(':r_id'=>$_SESSION['request_id']));
    // Mensaje de confirmación y redirección
    unset($_SESSION['request_id']);
    $_SESSION['success'] = 'Creación de prestamo cancelada';
    header('location:student.php');
    return;
}
// Redirigir a el detalle del prestamo
elseif(isset($_POST['back'])){
    unset($_SESSION['request_id']); unset($_SESSION['state']);
    $redirect = 'detail_request.php?request_id='.$request_id.'&estado='.$state;
    header('location:'.$redirect);
    return; 
}
// Boton de regresar o cancelar 
$action = isset($_SESSION['state']) ? '<input type="submit" class="btn btn-light" name="back" value="Regresar">' : 
'<input type="submit" name="end" class="btn btn-success" value="Finalizar">
<input type="submit" name="cancel" class="btn btn-danger" value="Cancelar prestamo">';
// Cargar equipos agregados
$request_id = $_SESSION['request_id'];
$state= isset($_SESSION['state']) ? $_SESSION['state'] : false;
$equipment_add = LoadEquipmentAdd($pdo, $request_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" href="images/shield.png">
    <meta charset="utf-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge' >
    <title>Añadir Equipos</title>
    <meta name='viewport' content='width=device-width', initial-scale='1.0'>
    <link rel='stylesheet' type='text/css' media='screen' href='estilo_estudiante.css'>
</head>
<body>
    <!-- Barra de navegacion -->
    <?php require_once 'navbar_student.html';?>
    <section class="form-register">
    <div align='center'>
    <form method="post">
    <?php ShowMessages(); ?> 
    <h2>Agregar Equipos</h2>
    <!-- Formulario -->
    <form method="post">
        <!-- Equipos -->
        <p><label for="equipment_id">Equipo :</label>
        <div class="input-group mb-3">
        <select name="equipment_id" class="form-select">
            <option value="0">-- Seleccione un equipo --</option>
            <?php
                $stm = $pdo->query('SELECT * FROM equipment ORDER BY equipment');
                while ($equipment = $stm->fetch(PDO::FETCH_ASSOC)){
                    echo '<option value="'.$equipment['equipment_id'].'"';
                    // Mantener elección
                    if (isset($_SESSION['equipment_id']) && $equipment['equipment_id']==$_SESSION['equipment_id']){
                        echo 'selected';
                    }
                    echo '>'.$equipment['equipment'].'</option>';
                }
            ?>
        </select>
        <input type="submit" name="select_equipment" class="btn btn-success" value="Seleccionar"></div>
        <!-- Tipos -->
        <p><label for="type_id">Tipo :</label>
        <div class="input-group mb-3">
        <select name="type_id" class="form-select">
            <option value="0">-- Seleccione un tipo --</option>
            <?php
            // Validar la seleccion de equipo
            if (isset($_SESSION['equipment_id']) && $_SESSION['equipment_id'] != 0){
                // Equipos del equipo seleccionado
                $stm = $pdo->prepare('SELECT * FROM full_equipment JOIN types ON 
                full_equipment.type_id=types.type_id WHERE 
                full_equipment.equipment_id=:e_id');
                $stm->execute(array(':e_id'=>$_SESSION['equipment_id']));
                $all_types = $stm->fetchAll(PDO::FETCH_ASSOC);
                // Obtener arreglo sin tipos duplicados
                $types = array(); $types_id = array();
                for ($i=0; $i<count($all_types); $i++){
                    array_push($types, $all_types[$i]['type']);
                    array_push($types_id, $all_types[$i]['type_id']);
                }
                $unique_types = array_unique($types); $unique_types_id = array_unique($types_id);
                // Imprimir modelos
                $types=array(); $types_id=array();
                // Imprimir modelos
                foreach ($unique_types as $type){array_push($types, $type);}
                foreach ($unique_types_id as $type_id){array_push($types_id, $type_id);}
                for ($i=0; $i<count($types); $i++){
                    echo '<option value="'.$types_id[$i].'"';
                    // Mantener eleccion
                    if (isset($_SESSION['type_id']) && $types_id[$i]==$_SESSION['type_id']){
                        echo 'selected';
                    }
                    echo '>'.$types[$i].'</option>';
                }
            }
            ?>
        </select>
        <input type="submit" name="select_type" class="btn btn-success" value="Seleccionar"></div>
        <!-- Modelos -->
        <p><label for="model_id">Modelo :</label>
        <div class="input-group mb-3">
        <select name="model_id" class="form-select">
            <option value="0">-- Seleccione un modelo --</option>
            <?php
            // Validar seleccion de tipo
            if (isset($_SESSION['type_id']) && $_SESSION['type_id'] != 0){
                // Modelos para el tipo seleccionado
                $stm = $pdo->prepare('SELECT * FROM full_equipment JOIN  models ON 
                full_equipment.model_id=models.model_id WHERE 
                full_equipment.equipment_id=:e_id AND full_equipment.type_id=:t_id');
                $stm->execute(array(
                    ':e_id'=>$_SESSION['equipment_id'],
                    ':t_id'=>$_SESSION['type_id']));
                $all_models = $stm->fetchAll(PDO::FETCH_ASSOC);
                // Obtener arreglo sin modelos duplicados
                $models = array(); $models_id = array();
                for ($i=0; $i<count($all_models); $i++){
                    array_push($models, $all_models[$i]['model']);
                    array_push($models_id, $all_models[$i]['model_id']);
                }
                $unique_models = array_unique($models); $unique_models_id = array_unique($models_id);
                // Imprimir modelos
                $models=array(); $models_id=array();
                // Imprimir modelos
                foreach ($unique_models as $model){array_push($models, $model);}
                foreach ($unique_models_id as $model_id){array_push($models_id, $model_id);}
                for ($i=0; $i<count($models); $i++){
                    echo '<option value="'.$models_id[$i].'"';
                    // Mantener eleccion
                    if (isset($_SESSION['model_id']) && $models_id[$i]==$_SESSION['model_id']){
                        echo 'selected';
                    }
                    echo '>'.$models[$i].'</option>';
                }
            }
            ?>
        </select>
        <input type="submit" name="select_model" class="btn btn-success" value="Seleccionar"></div>

        <p><label for="quantity">Cantidad: </label>
        <input type="number"  style="width:60px;" name="quantity"></p>

        <!-- Descripción -->
        <p><label for="description">Descripción: </label>
        <?php
        // Validar seleccion de modelo
        if (isset($_SESSION['model_id']) && $_SESSION['model_id'] != 0){
            // Descripcion del modelo seleccionado
            $stm = $pdo->prepare('SELECT * FROM full_equipment JOIN descriptions ON 
            full_equipment.description_id=descriptions.description_id WHERE 
            full_equipment.equipment_id=:e_id AND full_equipment.type_id=:t_id AND 
            full_equipment.model_id=:m_id');
            $stm->execute(array(
                ':e_id'=>$_SESSION['equipment_id'],
                't_id'=>$_SESSION['type_id'],
                'm_id'=>$_SESSION['model_id']));
            $description = $stm->fetch(PDO::FETCH_ASSOC);
            // Imprimir descripción
            echo $description['description'].'</p><br><br>';
        }
        ?>
        </p>
        <p><input type="submit" name="add" class="btn btn-success" value="Agregar"></p>
    </form>

    <h2>Equipos Agregados</h2> 
    <!-- Tabla -->
    <?php
    if (count($equipment_add) > 0){
        echo'
        <p><table class="table table-dark table-hover">
        <tr>
            <th>Cantidad</th>
            <th>Equipo</th>
            <th>Tipo</th>
            <th>Modelo</th>
            <th>Opciones</th>
        </tr>';
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
            echo '<td><form method="post">';
            echo '<input type="hidden" name="rec_id"  value="'.$equip['rec_id'].'">';
            echo '<input type="submit" name="delete" class="btn btn-warning" value="Eliminar"></form></td></tr>';
        }
        echo '</table></p>';
    }else{
        echo '<p style="color:yellow;">Aun no ha agregado ningun equipo</p>';
    }
               
        ?>
    <form method="post">
        <p><?= $action ?></p>
    </form>
    </form>
    </div>
  </section>
  <?php require_once 'footer.html'; ?> 
    
</body>
</html>