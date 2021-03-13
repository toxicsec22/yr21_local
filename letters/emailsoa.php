<html>
<head>
    <title>Email SOA</title>
</head>
<body>
<?php
ob_start();
$path=$_SERVER['DOCUMENT_ROOT']; 
// ini_set("include_path", ".:/usr/share/php/PHPMailer");
require($path."/acrossyrs/downloadedphp/PHPMailer/class.phpmailer.php");
$dbprefix=date('Y');
$path=$_SERVER['DOCUMENT_ROOT']; 
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=connect_db(date('Y').'_1rtc',0);



$mail = new PHPMailer();
$mail->IsSMTP();  // telling the class to use SMTP
$mail->SMTPDebug = 2; // debugging: 1 = errors and messages, 2 = messages only
// $mail->Host = "smtp.gmail.com"; // SMTP server
$mail->Host = "mail.1rotarytrading.com"; // SMTP server
$mail->Port = '587';//'465';
$mail->IsHTML(true);
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->SMTPSecure = 'tls';//'ssl';
$mail->Username = ''.$_POST['Sender'].'';                            // SMTP username
$mail->Password = ''.$_POST['Password'].'';                           // SMTP password

$mail->From = ''.$_POST['Sender'].'';
$mail->FromName = ''.$_POST['From'].'';

$mail->Subject  = "Statement Of Account";
$mail->WordWrap = 50;

$mail->AddCC($_POST['Sender']);






$msg=''.$_POST['msg'].''; 



    // $mail->AddAddress(''.$_POST['Sender'].'');
    // $mail->AddAddress('mark.ferrer@1rotarytrading.com');
    $mail->AddAddress(''.$_POST['Receiver'].'');
    $mail->Body     = $msg;
    $mail->AltBody     = $msg;


    if(!$mail->Send()) {
	echo 'Mailer error: ' . $mail->ErrorInfo;
	$done='0';
} else {
	echo 'Message has been sent.'; 
	$done='1';
}
$mail->ClearAddresses(); 
header("Location: /yr".date('y')."/letters/viewletters.php?done=".$done);
?>
 
</body>
</html>