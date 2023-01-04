<?php
// Redirigir a la pagina principal
function Back($location){
    if (isset($_POST['back'])){
        header('location:'.$location);
    }
}
// Mostrar mensajes
function ShowMessages(){
    if (isset($_SESSION['success'])){
        echo '<p class="fondo" style="color:green;" >'.$_SESSION['success'].'</p>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])){
        echo '<p class="fondo" style="color:red;">'.$_SESSION['error'].'</p>';
        unset($_SESSION['error']);
    }
}
// Depurar
function Debug(){
    echo '$_GET = ';print_r($_GET);echo '</br>';
    echo '$_POST = ';print_r($_POST);echo '</br>';
    echo '$_SESSION = ';print_r($_SESSION);echo '</br>';
}
// Recargar pagina
function Refresh($file, $request_id, $state){
    return $file.'_request.php?request_id='.$request_id.'&estado='.$state;
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
// Validar perfil
function ValidateDocument(){
    if (strlen($_POST['ti_un'])<1 && strlen($_POST['document'])<1){
        return 'El codigo ti_un o la cedula son requeridas';
    }
    return true;
}
// Validar GET
function ValidateGET(){
    if (!isset($_GET['request_id']) || !isset($_GET['estado'])){
        return 'Información Incompleta';
    }
    return true;
}
// Validar POST Clase y Practica Libre
function ValidatePOSTClase(){
    if ($_POST['course']=='0'){
        return  'Debe seleccionar un curso';
    }
    if (strlen($_POST['fecha']) < 1){
            return 'Debe seleccionar una fecha';
    }
    if (strlen($_POST['hora_ini']) < 1 || strlen($_POST['hora_fin']) < 1){
        return 'Debe seleccionar un horario';
    }
    return true;
}
// Validar POST Casa
function ValidatePOSTCasa(){
    if ($_POST['course']=='0'){
        return 'Debe seleccionar un curso';
    }
    if (strlen($_POST['fecha_ini']) < 1 || strlen($_POST['fecha_fin']) < 1){
        return 'Debe seleccionar una fecha de inicio y fin';
    }
    return true;
}
// Validar Horarios
function ValidateSchedule($pdo){
    $stm = $pdo->prepare('SELECT * FROM members JOIN courses JOIN students 
    ON members.course_id=courses.course_id AND members.student_id=students.ti_un 
    WHERE courses.name=:c_n AND members.student_id=:s_id');
    $stm->execute(array(':c_n'=>$_POST['course'],':s_id'=>$_SESSION['student_id']));
    $course = $stm->fetch(PDO::FETCH_ASSOC);
    if ($_POST['hora_ini']!=$course['hour_ini'] || $_POST['hora_fin']!=$course['hour_fin'] ){
        return 'El horario solicitado no coincide con el establecido en el curso';
    }
    return true;
}
// Validar perfil
function Validateti_un(){
    if (strlen($_POST['ti_un'])<1){
        return 'El codigo ti_un es requerido';
    }
    return true;
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
// Cargar solicitud
function LoadRequest($pdo, $request_id, $state){
    $stmt = $pdo->prepare('SELECT * FROM requests WHERE 
    estado=:st AND request_id=:r_id');
    $stmt->execute(array(
        ':st'=>$state, 
        ':r_id'=>$request_id));
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
// Cargar deudas e historial
function Load($pdo, $database){
    $stmt = $pdo->query('SELECT * FROM '.$database);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Cargar equipos agregados
function LoadEquipmentAdd($pdo, $request_id){
    $stm = $pdo->prepare('SELECT * FROM requests_equipment_codes 
    WHERE request_id=:r_id');
    $stm->execute(array(':r_id'=>$request_id));
    return $stm->fetchAll(PDO::FETCH_ASSOC);
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
// Borrar equipo del prestamo
function DeleteEquipment($pdo){
    $stm = $pdo->prepare('DELETE FROM requests_equipment_codes 
    WHERE requests_equipment_codes.rec_id=:rec_id');
    $stm->execute(array(':rec_id'=>$_POST['rec_id']));
    return;
}

?>
