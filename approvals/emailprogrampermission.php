<?php
$path=$_SERVER['DOCUMENT_ROOT']; 
// ini_set("include_path", ".:/usr/share/php/PHPMailer");
require($path."/acrossyrs/downloadedphp/PHPMailer/class.phpmailer.php");
include_once $path.'/acrossyrs/dbinit/emailpassword.php';
// rtciconpass()


$sql='SELECT ifnull(u.Email,b.Email) as Email, b.Branch, su.Approval FROM `1_gamit`.`1rtcusers` u join `approvals_2progpermission` su on su.EncodedByNo=u.IDNo join `1branches` b on b.BranchNo=su.`BranchNo` where su.BranchNo='.$_REQUEST['BranchNo'];
$stmt=$link->query($sql); $res=$stmt->fetch();
 
$mail = new PHPMailer();
$mail->IsSMTP();  // telling the class to use SMTP
$mail->SMTPDebug = 2; // debugging: 1 = errors and messages, 2 = messages only
$mail->Host = "smtp.gmail.com"; // SMTP server
$mail->Port = '587';//'465';
$mail->IsHTML(true);
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->SMTPSecure = 'tls';//'ssl';
$mail->Username = '1rtcicon@gmail.com';                            // SMTP username
$mail->Password = rtciconpass();                           // SMTP password

$mail->From = '1rtcicon@gmail.com';
$mail->FromName = '1Rotary - The Industry Icon';

$mail->Subject  = "Program permission for ".$res['Branch'];
$mail->WordWrap = 50;

$ip=$_SERVER['REMOTE_ADDR']; $ipnumbers=str_replace('.', '_', $ip);
$hashed=substr($res['Approval'],0,5).'_'.substr($res['Approval'],8,4).'Y73Y'.$ipnumbers.'X64X_'.substr($res['Approval'],5,4).'_'.substr($res['Approval'],20,6).substr($res['Approval'],17,3).'_'.substr($res['Approval'],12,5);//Approval has 25 characters
$server=$_SERVER['SERVER_ADDR'];
$msg='<a href="'.$server.'/'.$url_folder.'/approvals/acceptprogrampermission.php?BranchNo='.$_REQUEST['BranchNo'].'&Y='.$hashed.'">Click here </a> to accept program permission.'; 

//echo $msg; break;

    $mail->AddAddress($res['Email']);
    $mail->Body     = $msg;
    $mail->AltBody     = $msg;

    if(!$mail->Send()) {
echo 'Message was not sent.';
echo 'Mailer error: ' . $mail->ErrorInfo;
} else {
echo 'Message has been sent.'; $done='1';
}

 $mail->ClearAddresses();    
  $link=null; $stmt=null;
?>