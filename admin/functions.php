<?php
// Mostrar mensajes
function ShowMessages(){
    if (isset($_SESSION['success'])){
        echo '<p class="fondo" style="color:green;">'.$_SESSION['success'].'</p>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])){
        echo '<p class="fondo" style="color:red;">'.$_SESSION['error'].'</p>';
        unset($_SESSION['error']);
    }
}
// Redirigir a la pagina principal
function Back($location){
    if (isset($_POST['back'])){
        header('location:'.$location);
    }
}
// Depurar errores
function Debug(){
    echo '$_GET = ';print_r($_GET);echo '</br>';
    echo '$_POST = ';print_r($_POST);echo '</br>';
    echo '$_SESSION = ';print_r($_SESSION);echo '</br>';
}
// Recargar pagina
function Refresh($file, $request_id, $ti_un){
    return $file.'_request.php?request_id='.$request_id.'&ti_un='.$ti_un;
}
// Validar inicio de sesion
function ValidateLogIn() {
    if (!isset($_SESSION['log_in'])) {
        die('Not logged in');
    }
}
// Validar usuario y contraseña
function ValidateUser(){
    if  ( strlen($_POST['username']) < 1 || strlen($_POST['password']) < 1 ) {
        return 'Usuario y contraseña son queridos';
    }
    return true;
}
// Validar GET active and desactive
function ValidateGET(){
    if (!isset($_GET['request_id']) || !isset($_GET['ti_un'])){
        return 'Información incompleta';
    }
    return true;
}
// Validar Información GET active and desactive
function ValidateGETInfo($pdo, $request_id, $ti_un){
    $stm = $pdo->prepare('SELECT * FROM requests 
    WHERE request_id=:r_id AND ti_un=:t_u');
    $stm->execute(array(':r_id'=>$request_id,':t_u'=>$ti_un));
    $request = $stm->fetch(PDO::FETCH_ASSOC);
    if ($request == false){
        return 'Información Invalida';
    }
    return true;
}
// Validar perfil
function ValidateDocument(){
    if (strlen($_POST['ti_un'])<1 && strlen($_POST['document'])<1){
        return 'El codigo ti_un o la cedula son requeridas';
    }
    return true;
}
// Validar GET
function ValidateRequest_id(){
    if (!isset($_GET['request_id'])){
        return 'Solicitud no encontrada';
    }
    return $_GET['request_id'];
}
// Validar Equipo
function ValidateEquipmentPOST(){
    if ( strlen($_POST['code'])<1  || (strlen($_POST['equipment'])<1) || (strlen($_POST['type'])<1) 
    || (strlen($_POST['model'])<1) ||  (strlen($_POST['description'])<1)){
        return 'Todos los campos son necesarios';
    }
    if ($_POST['role'] == '0'){
        return 'Debe seleccionar un rol';
    }
    if ($_POST['consumable'] == '0'){
        return 'Debe seleccionar una opción';
    }
    return true;
}
// Validar codigo
function ValidateCode(){
    if (strlen($_POST['code'])<1){
        return 'Codigo interno requerido';
    }
    return true;
}
// Validar POST estudiante
function ValidatePOSTStudent(){
    if ( strlen($_POST['name'])<1 || (strlen($_POST['last_name'])<1) || (strlen($_POST['email'])<1) || 
    (strlen($_POST['document'])<1) ||  (strlen($_POST['ti_un'])<1) || (strlen($_POST['username'])<1) || 
    (strlen($_POST['pass'])<1) ){
        return 'Todos los campos son necesarios';
    }
    if ($_POST['document_type'] == '0'){
        return 'Debe seleccionar un tipo de documento';
    }
}
// Cargar equipos
function LoadEquipment($pdo, $request_id, $state){
    $stm = $pdo->prepare('SELECT * FROM requests_equipment_codes 
    WHERE request_id=:r_id AND state=:st');
    $stm->execute(array(':r_id'=>$request_id, ':st'=>$state));
    return $stm->fetch(PDO::FETCH_ASSOC);
}
// Cargar usuario
function LoadUser($pdo, $database){
    $stmt = $pdo->prepare('SELECT * FROM '.$database.' WHERE 
    username = :u AND pass = :p');
    $stmt->execute(array(
        ':u' => $_POST['username'],
        ':p' => $_POST['password']));
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
// Cargar solicitud estudiante
function LoadRequestStudent($pdo, $state){
    if (isset($_SESSION['modalidad'])){
        $stmt = $pdo->prepare('SELECT * FROM requests WHERE 
        estado=:st AND ti_un=:t_u AND modalidad=:mo');
        $stmt->execute(array(
            ':st'=>$state, 
            ':t_u'=>$_POST['ti_un'],
            ':mo'=>$_SESSION['modalidad']));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    $stmt = $pdo->prepare('SELECT * FROM requests WHERE 
    estado=:st AND ti_un=:t_u');
    $stmt->execute(array(
        ':st'=>$state, 
        ':t_u'=>$_POST['ti_un']));
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
// Cargar solicitud
function LoadRequest($pdo, $request_id, $state){
    $stmt = $pdo->prepare('SELECT * FROM requests WHERE 
    estado=:st AND request_id=:r_id');
    $stmt->execute(array(':st'=>$state, ':r_id'=>$request_id));
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
// Cargar solicitudes
function LoadRequests($pdo, $state){
    $stmt = $pdo->prepare('SELECT * FROM requests WHERE estado=:st');
    $stmt->execute(array(':st'=>$state));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Cargar deudas e historial
function Load($pdo, $database){
    $stmt = $pdo->query('SELECT * FROM '.$database);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Cargar solicitudes de una modalidad
function LoadRequestsMode($pdo, $state, $mo){
    $stmt = $pdo->prepare('SELECT * FROM requests WHERE 
    estado=:st AND modalidad=:mo');
    $stmt->execute(array(
        ':st'=>$state, 
        ':mo'=>$mo));
    return $stmt->fetchALL(PDO::FETCH_ASSOC);
}
// Cargar ti_un
function Loadti_un($pdo){
    if (strlen($_POST['ti_un'])<1){
        $stm = $pdo->prepare('SELECT * FROM students WHERE document=:d');
        $stm->execute(array(':d'=>$_POST['document']));
        $student = $stm->fetch(PDO::FETCH_ASSOC);
        $_POST['ti_un']=$student['ti_un'];
        return;
    }
}
// Cargar equipments_id en un estado
function LoadEquipment_id($pdo, $state, $request_id){
    $stm = $pdo->prepare('SELECT * FROM requests_equipment_codes JOIN full_equipment
    ON requests_equipment_codes.equipment_id=full_equipment.full_equipment_id 
    WHERE request_id=:r_id AND state=:st');
    $stm->execute(array(
        ':r_id'=>$request_id,
        ':st'=>$state));
    $equipment = $stm->fetchAll(PDO::FETCH_ASSOC); 
    $equipment_id = array();
    foreach($equipment as $equip){
        array_push($equipment_id, $equip['full_equipment_id']);
    }
    return $equipment_id;
}
// Cargar todos los equipment_id
function LoadAllEquipment_id($pdo, $request_id){
    $stm = $pdo->prepare('SELECT * FROM requests_equipment_codes JOIN full_equipment 
    ON requests_equipment_codes.equipment_id=full_equipment.full_equipment_id 
    WHERE request_id=:r_id');
    $stm->execute(array(':r_id'=>$request_id));
    $equipment = $stm->fetchAll(PDO::FETCH_ASSOC); $equipment_id = array();
    foreach($equipment as $equip){
        array_push($equipment_id, $equip['full_equipment_id']);
    }
    return $equipment_id;
}
// Cargar equipos agregados
function LoadEquipmentAdd($pdo, $request_id){
    $stm = $pdo->prepare('SELECT * FROM requests_equipment_codes 
    WHERE request_id=:r_id');
    $stm->execute(array(':r_id'=>$request_id));
    return $stm->fetchAll(PDO::FETCH_ASSOC);
}
// Borrar equipo del prestamo
function DeleteEquipment($pdo){
    $stm = $pdo->prepare('DELETE FROM requests_equipment_codes 
    WHERE requests_equipment_codes.rec_id=:rec_id');
    $stm->execute(array(':rec_id'=>$_POST['rec_id']));
    return;
}
// Buscar equipo
function SearchInsert($pdo, $db, $name){
    $stm = $pdo->prepare('SELECT * FROM '.$db.' WHERE '.$name.'=:n');
    $stm->execute(array(':n'=>$_POST[$name]));
    $result =  $stm->fetch(PDO::FETCH_ASSOC);
    if ($result==false){
        $stm = $pdo->prepare('INSERT INTO '.$db.'('.$name.') VALUES (:v)');
        $stm->execute(array(':v'=>$_POST[$name]));
        return $pdo->lastInsertId();
    }else{
        return $result[$name.'_id'];
    }
}
// Actualizar estado r_e_c
function UpdateState($pdo, $state){
    $stm = $pdo->prepare('UPDATE requests_equipment_codes SET state=:st 
    WHERE requests_equipment_codes.rec_id = :rec_id');
    $stm->execute(array(':st'=>$state, ':rec_id'=>$_POST['rec_id']));
}
// Actualizar estado request
function UpdateEstado($pdo, $estado, $hora, $request_id){
    $stm = $pdo->prepare('UPDATE requests SET estado=:es, '.$hora.'=:h 
    WHERE request_id=:r_id');
    $stm->execute(array(
        ':es'=>$estado,
        ':h'=>date('h:i'),
        ':r_id'=>$request_id));
}
// Insertar en historial
function InsertHistory($pdo, $msg){
    $stm = $pdo->prepare ('INSERT INTO history(comments, rec_id) 
    VALUES (:c, :id)');
    $stm->execute(array(
        ":c" => $_POST['comments'].$msg, 
        ":id" => $_POST['rec_id']));
}


?>

