<meta charset="utf-8">
<?php
//https://myaccount.google.com/lesssecureapps?pli=1&rapt=AEjHL4MobtnVSnUiJ20d3JFmSFxVMvddM7NXeOv9nD9mMNLFkkF40ig9oDPvJWmuzlTsxmhXHZ92o61BOJktlZMh4oJgPAHrtQ
//Conexion PHPMailer
  // Archivos
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';
  // Objetos
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
//Creacion del objeto PHPMailer
$mail = new PHPMailer();
//$mail->SMTPDebug = 2;
$mail->IsSMTP(); 
//Configuracion servidor
$mail->Host = 'smtp.gmail.com'; // servidor smtp
$mail->SMTPAuth = true;
$mail->SMTPSecure = 'tls'; //seguridad
$mail->Port = 587; //puerto
// Información remitente
$mail->Username ='hangelp@unal.edu.co';
$mail->Password = 'Skaten.1';
$mail->setFrom('hangelp@unal.edu.co');
// Correo destinatario
$stm = $pdo->prepare('SELECT * FROM requests JOIN students ON 
requests.ti_un=students.ti_un WHERE request_id=:r_id');
$stm->execute(array(':r_id'=>$request_id));
$result = $stm->fetch(PDO::FETCH_ASSOC);
$email = $result['email'];
// Información del prestamo
$stm = $pdo->prepare('SELECT * FROM requests JOIN students 
ON requests.ti_un=students.ti_un WHERE requests.request_id=:r_id');
$stm->execute(array(':r_id'=>$request_id));
$request = $stm->fetch(PDO::FETCH_ASSOC);
$info_request = array();
$info_request[] = '<tr><td>'.$request['modalidad'].'</td>';
$info_request[] = '<td>'.$request['fecha_solicitud'].'</td>';
$info_request[] = '<td>'.$request['curso'].'</td>';
if ($request['modalidad']!='Casa'){
  $info_request[] = '<td>'.$request['fecha'].'</td>';
  $info_request[] = '<td>'.$request['hora_ini'].'</td>';
  $info_request[] = '<td>'.$request['hora_fin'].'</td>';
}else{
  $info_request[] = '<td>'.$request['fecha_ini'].'</td>';
  $info_request[] = '<td>'.$request['fecha_fin'].'</td>';
} 
$info_request[] = '<td><ul>';   
// Grupo de trabajo
$stm = $pdo->prepare('SELECT * FROM members JOIN courses ON members.course_id=courses.course_id 
WHERE name=:c AND student_id=:s_id');
$stm->execute(array(':c'=>$request['curso'], ':s_id'=>$request['ti_un']));
$member = $stm->fetch(PDO::FETCH_ASSOC); 
$work_group = $member['work_group']; $course_id=$member['course_id'];
// Compañeros
$stm = $pdo->prepare('SELECT * FROM members JOIN students ON members.student_id=students.ti_un 
WHERE course_id=:c_id AND work_group=:g');
$stm->execute(array(':c_id'=>$course_id, ':g'=>$work_group));
while ($partner = $stm->fetch(PDO::FETCH_ASSOC)){
  $info_request[] = '<li>'.$partner['name'].' '.$partner['last_name'].'</li>';
}
$info_request[] = '</ul></td></tr>';
$info_request = implode(' ',$info_request);
$request_table='<table border="1">
                  <tr><th>Modalidad</th>
                  <th>Fecha de Solicitud</th>
                  <th>Curso</th>';
if ($request['modalidad']!='Casa'){
  $request_table .='<th>Fecha</th>
                  <th>Hora Inicial</th>
                  <th>Hora Final</th>';
}else{
  $request_table .='<th>Fecha Inicial</th>
                  <th>Fecha Final</th>';
}
$request_table .='<th>Grupo de Trabajo</th></tr>
                  '.$info_request.'
                </table>';
// Equipo solicitado
$equipment_id = LoadAllEquipment_id($pdo, $request_id);
$quantity = array_count_values($equipment_id); 
$equipment_id = array_unique($equipment_id);
$info_equipment = array();
foreach ($equipment_id as $equip_id){
  $stm = $pdo->query('SELECT * FROM requests_equipment_codes JOIN 
  full_equipment JOIN equipment JOIN types JOIN models ON 
  requests_equipment_codes.equipment_id=full_equipment.full_equipment_id 
  AND full_equipment.equipment_id=equipment.equipment_id AND 
  full_equipment.type_id=types.type_id AND full_equipment.model_id=models.model_id 
  WHERE requests_equipment_codes.equipment_id='.$equip_id);
  $equip = $stm->fetch(PDO::FETCH_ASSOC);
  $info_equipment[] = '<tr><td>'.$quantity[$equip_id].'</td>';
  $info_equipment[] = '<td>'.$equip['equipment'].'</td>';
  $info_equipment[] = '<td>'.$equip['type'].'</td>';
  $info_equipment[] = '<td>'.$equip['model'].'</td>';
  $info_equipment[] = '<td><ul>';
  $stm = $pdo->query('SELECT * FROM requests_equipment_codes JOIN full_equipment JOIN codes 
  ON requests_equipment_codes.equipment_id=full_equipment.full_equipment_id AND 
  requests_equipment_codes.code_id=codes.code_id WHERE requests_equipment_codes.equipment_id='.$equip_id.' 
  AND request_id='.$request_id);
  while ($code = $stm->fetch(PDO::FETCH_ASSOC)){
    $info_equipment[] = '<li>'.$code['code'].'</li>';
  }
  $info_equipment[] = '</ul></td></tr>';
}
$info_equipment = implode(' ',$info_equipment);
$equipment_table='<table border="1">
                    <tr><th>Cantidad</th>
                    <th>Equipo</th>
                    <th>Tipo</th>
                    <th>Modelo</th>
                    <th>Codigo</th></tr>
                    '.$info_equipment.'
                  </table>';
// Cuerpo del mensaje
$body = '<h2>Se ha activado su prestamo</h2>
          <h3>Detalle del prestamo: </h3>
          '.$request_table.'
          <h3>Equipo Entregado: </h3>
          '.$equipment_table;
//Agregar destinatario
$mail->Subject = 'Activacion de prestamo';
$mail->isHTML(true);
$mail->Body = $body;
$mail->AddAddress($email);
// Validar envio de email
$_SESSION['success'] = $mail->send() ? 'Prestamo Activado' : 'Correo no enviado';
// Cerrar conexion
$mail->smtpClose();
?>