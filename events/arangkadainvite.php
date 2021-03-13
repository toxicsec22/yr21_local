<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(4010,'1rtc')) { echo 'No permission'; exit; }
$showbranches=true;

include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=!isset($_GET['w'])?'lists':$_GET['w'];

$emailmsg='<br><br>
You\'ve probably heard the news and you\'ve heard it right! We will be giving away 5 HONDA TMX 125 ALPHA motorcycles nationwide! 
<br><br>
Don\'t miss the chance to be one of the 5 lucky winners! What are you waiting for? Register and submit your entries at <a href="https://bit.ly/2E8NJr7">https://bit.ly/2E8NJr7</a>.<br><br>';

switch ($which){
	case'lists':
	$title='Arangkada sa Bagong Dekada';
	echo'<title>'.$title.'</title>';
	echo'<h3>'.$title.' Email Invitation</h3><br>';
	if(isset($_GET['msg'])){
		echo '<br><div style="text-align:center;background-color:green;color:white;">Clients Invited Successfully.</div>';
	}	
	echo '<table style="width:100%"><tr><td style="width:35%;"><h4>Mail Content</h4><div style="background-color:white;border:1px solid black;padding:5px;">Subject: Arangkada Motorcycle Raffle Promo, Join Now!<br>';
	echo 'Message:<br>Good day!'.$emailmsg.'<img src="pics/emailinvitation.jpg" height="483px" width="360px"><br><br>System-generated. Please do not reply.</div></td>';
	
	
	$sql='SELECT bcj.ClientNo,ClientName,ContactPerson,EmailAddress FROM gen_info_1branchesclientsjxn bcj JOIN 1clients c ON bcj.ClientNo=c.ClientNo WHERE EmailAddress LIKE "%@%" AND EmailAddress LIKE "%.%" AND BranchNo='.$_SESSION['bnum'].';';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
	
	echo'<td valign="top" style="padding:10px;"><form action="arangkadainvite.php?w=PrEmailInvite" method="POST"><table style="font-size:9.5pt;width:100%;background-color:white;padding:5px;">
 <tr><th width="50px">All? <input type="checkbox" class="chk_boxes" onclick="toggle(this);" /></th><th>Client Name</th><th>Contact Person</th><th>Email Address</th></tr>';
		foreach($result as $res){
			echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td style="text-align:right;"><input type="checkbox" value="'.$res['ClientNo'].'" name="clientno[]" /></td><td>'.$res['ClientName'].'</td><td>'.$res['ContactPerson'].'</td><td style="width:200px;">'.$res['EmailAddress'].'</td></tr>';
			$colorcount++;
		}
		echo '<tr><td colspan=4 align="center"><input style="background-color:green;color:white;width:200px" type="submit" value="Invite Clients" name="btnInvite" OnClick="return confirm(\'Send Invitation?\');"></td></tr></table></form></td></tr></table>';
	break;
	
	
	
	case 'PrEmailInvite':
	 require($path."/acrossyrs/downloadedphp/PHPMailer/class.phpmailer.php");
	
		
		if (isset($_REQUEST['clientno'])){
				foreach ($_REQUEST['clientno'] AS $clientno){
					
					 $sqlemail='SELECT EmailAddress,ClientName FROM 1clients WHERE ClientNo='.$clientno.'';
					$stmtemail=$link->query($sqlemail); $resultemail=$stmtemail->fetch();
					
					$mail = new PHPMailer();
				$mail->IsSMTP();  // telling the class to use SMTP
				$mail->SMTPDebug = 2; // debugging: 1 = errors and messages, 2 = messages only
				$mail->Host = "smtp.gmail.com"; // SMTP server
				$mail->Port = '587';//'465';
				$mail->IsHTML(true);
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
				$mail->SMTPSecure = 'tls';//'ssl';
				$mail->Username = '1rtcicon@gmail.com';                            // SMTP username
				$mail->Password = '1RotaRy1003$';                           // SMTP password

				$mail->From = '1rtcicon@gmail.com';
				$mail->FromName = '1Rotary';

				$mail->Subject  = "Arangkada Motorcycle Raffle Promo, Join Now!";
				$mail->AddEmbeddedImage('pics/emailinvitation.jpg', 'logo_2u');
				$mail->WordWrap = 50;

				
				$table='Good day!
				'.$emailmsg.'<img src="cid:logo_2u"><br><br>System-generated.  Please do not reply.';
				
					$mail->AddAddress(strtolower($resultemail['EmailAddress']));
					$mail->Body     = $table;
					$mail->AltBody     = $table; 
					if(!$mail->Send()) {
					echo 'Message was not sent.';
					echo 'Mailer error: ' . $mail->ErrorInfo;
					$msgval=0;
					} else {
					echo 'Message has been sent.';
					$msgval=1;
					}
				 $mail->ClearAddresses();
					
				}
			
			}
			
		header("Location:arangkadainvite.php?w=lists&msg=done");
		exit();
		
	break;
	
}

?>

<script>
	function toggle(source) {
		var checkboxes = document.querySelectorAll('input[type="checkbox"]');
		for (var i = 0; i < checkboxes.length; i++) {
			if (checkboxes[i] != source)
				checkboxes[i].checked = source.checked;
		}
	}
</script>