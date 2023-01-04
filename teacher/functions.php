<?php
// Redirigir 
function Back($location){
    if (isset($_POST['back'])){
        header('location:'.$location);
    }
}
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
// Depurar
function Debug(){
    echo '$_GET = ';print_r($_GET);echo '</br>';
    echo '$_POST = ';print_r($_POST);echo '</br>';
    echo '$_SESSION = ';print_r($_SESSION);echo '</br>';
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
        return 'Debe escribir un codigo ti_un o un numero de cedula';
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
// Validar POST curso
function ValidatePOSTCourse(){
    if(strlen($_POST['name'])<1 || strlen($_POST['group'])<1 || strlen($_POST['groups'])<1 
    || strlen($_POST['hour_ini'])<1 || strlen($_POST['hour_fin'])<1){
      return 'Debe llenar todos los campos';
    }
    $days=array();
    for ($i=1; $i<=6; $i++){
        if (!isset($_POST['days'.$i])) continue;
        $days[] = $_POST['days'.$i];   
    }
    if (count($days)==0){
        return 'Debe seleccionar al menos un dia';
    }
    return $days;
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
// Cargar solicitud
function LoadRequestStudent($pdo, $state){
    $stmt = $pdo->prepare('SELECT * FROM requests WHERE 
    estado=:st AND ti_un=:t_u');
    $stmt->execute(array(':st'=>$state, ':t_u'=>$_POST['ti_un']));
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
// Buscar dia 
function SearchDay($days, $dia){
    if (is_array($days)){
        foreach ($days as $day){
            if ($day == $dia) echo 'checked';
        }
    }else{
        if ($days==$dia)echo 'checked';
    }
}
// Generar string dias
function GenerateString($days){
    $string_days='';
    if (count($days)>1){
        foreach($days as $day){ $string_days .= $day.' ';}
    }else{
        $string_days=$days[0]; 
    }
    return $string_days;
}
?>
