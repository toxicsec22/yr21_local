<?php
	$path=$_SERVER['DOCUMENT_ROOT']; 
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
	date_default_timezone_set('Asia/Manila');
	
	// ini_set("include_path", ".:/usr/share/php/PHPMailer");
	// require("../../PHPMailer/class.phpmailer.php");
	require($path."/acrossyrs/downloadedphp/PHPMailer/class.phpmailer.php");
	$heads = array(5,20,31,45,70,150,171,110); 

  // print_r($heads); exit();
//ADMIN

	foreach($heads as $h){
		switch($h){
			case'5': $condition='where ADMINFD=0'; break;
			case'20': $condition='where SCFD=0'; break;
			case'31': $condition='where SALESFD=0'; break;
			case'45': $condition='where HRFD=0'; break;
			case'70': $condition='where OPSFD=0'; break;
			case'150': $condition='where FINANCEFD=0'; break;
			case'171': $condition='where ACCTGFD=0'; break;
			case'110': $condition='where MKTGFD=0'; break;
			
		}

	$sql='select *,CONCAT (FirstName,\' \',SurName) as Name,Date as EffectivityDate,DATEDIFF(CURDATE(),date(rp.TimeStamp)) as DateDiff from hr_2resignationprocess rp left join 1employees e on e.IDNo=rp.IDNo '.$condition.'';
	// echo $sql; 
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	$table='';
	if($stmt->rowCount()!=0){
		$table.='Good morning.  The following are resigned employees that have not been processed by your department:</br></br>
		<table><tr><th>EmployeeName</th><th>Resignation Effective Date</th><th>Link</th></tr>';
		 // $total=0;
		}
		 
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
		$mail->FromName = '1Rotary - The Industry Icon';

		$mail->Subject  = "Pending Clearances of Resigned Employees";
		$mail->WordWrap = 50;

		$date=date('Y-m-d');
		$ip=$_SERVER['REMOTE_ADDR'];
		foreach($result as $res){

			if($res['DateDiff']>=10){
				$table.='<tr><td><b><font color="red">URGENT!</font></b> '.$res['Name'].'</td><td>'.$res['EffectivityDate'].'</td><td><a href="http://www.arwan.biz/'.$url_folder.'/hr/resignationprocess.php?w=lookup&TxnID='.$res['TxnID'].'">http://www.arwan.biz/'.$url_folder.'/hr/resignationprocess.php?w=lookup&TxnID='.$res['TxnID'].'</a></td></tr>';
		 // $total=0;
			}else{
				$table.='<tr><td>'.$res['Name'].'</td><td>'.$res['EffectivityDate'].'</td><td><a href="http://www.arwan.biz/'.$url_folder.'/hr/resignationprocess.php?w=lookup&TxnID='.$res['TxnID'].'">http://www.arwan.biz/'.$url_folder.'/hr/resignationprocess.php?w=lookup&TxnID='.$res['TxnID'].'</a></td></tr>';
		 // $total=0;
			}
			
		}
		if($stmt->rowCount()!=0){
		$table.='</table></br></br>System-generated.  Please do not reply.';
		}
			$sql='SELECT Email FROM `1_gamit`.`1rtcusers` u join `attend_30currentpositions` p on u.IDNo=p.IDNo where PositionID=\''.$h.'\'';
			// echo $sql;
			$stmt=$link->query($sql);
			$res=$stmt->fetch();

			$mail->AddAddress($res['Email']);
			$mail->Body     = $table;
			$mail->AltBody     = $table;
			
			if(!$mail->Send()) {
		echo 'Message was not sent.';
		echo 'Mailer error: ' . $mail->ErrorInfo;
		}
	

	}
	exit();
		
?>