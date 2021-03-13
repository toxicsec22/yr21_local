<?php
	$path=$_SERVER['DOCUMENT_ROOT']; 
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$currentyr=date('Y');
	$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
	date_default_timezone_set('Asia/Manila');
	
		$path=$_SERVER['DOCUMENT_ROOT']; 
		 require($path."/acrossyrs/downloadedphp/PHPMailer/class.phpmailer.php");
	
	 
	  
	  
	  
	  $sqlheads='select DISTINCT(DeptHeadIDNo) AS DeptHeads FROM hr_2perfevalmain WHERE MONTH(EvalDueDate)>='.date('m').' AND DeptHeadConfirm=0';
	
	
	$stmtheads=$link->query($sqlheads); $resultheads=$stmtheads->fetchAll();
	
	foreach($resultheads AS $resulthead){
		
			$arrayheads[] = $resulthead['DeptHeads'];
		} 
	
	

	foreach($arrayheads as $h){
		
		$sql='select pem.TxnID,CONCAT (FirstName,\' \',SurName) as Name,EvalDueDate,IFNULL((SELECT SUM(Weight) FROM hr_2perfevalsub pes JOIN hr_1positionstatement ps ON pes.PSID=ps.PSID WHERE TxnID=pem.TxnID),0) AS TotWeight FROM hr_2perfevalmain pem left join 1employees e on e.IDNo=pem.IDNo WHERE MONTH(EvalDueDate)>='.date('m').' AND DeptHeadConfirm=0 AND DeptHeadIDNo='.$h.'';
		
		
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	$table='';
	if($stmt->rowCount()!=0){
		
		$table.='Good day. The following employees have pending evaluations:</br></br>
		<table><tr><th>EmployeeName</th><th>EvalDueDate</th><th>'.(($res['TotWeight']<>100)?"Statements":"").'<th><th>Link</th></tr>';
		 
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
		$mail->FromName = 'Performance Evaluation';

		$mail->Subject  = "Pending Performance Evaluation";
		$mail->WordWrap = 50;

		
		foreach($result as $res){
			$address='https://www.arwan.biz/yr'.date('y').'/hr/perfevalentryform.php?TxnID='.$res['TxnID'];
				$table.='<tr><td>'.$res['Name'].'</td><td>'.$res['EvalDueDate'].'</td><td>'.(($res['TotWeight']<100)?"<font color='red'>Incomplete</font>":(($res['TotWeight']>100)?"<font color='red'>Excess</font>":"")).'</td><td><a href="'.$address.'">Lookup</a></td></tr>';
		}
		if($stmt->rowCount()!=0){
		$table.='</table></br></br>System-generated.  Please do not reply.';
		}
			$sql='SELECT Email FROM `1_gamit`.`1rtcusers` where IDNo=\''.$h.'\'';
			
			$stmt=$link->query($sql);
			$res=$stmt->fetch();

			$mail->AddAddress($res['Email']);
			$mail->Body     = $table;
			$mail->AltBody     = $table; 
			if(!$mail->Send()) {
echo 'Message was not sent.';
echo 'Mailer error: ' . $mail->ErrorInfo;
} else {
echo 'Message has been sent.';
}
			 $mail->ClearAddresses(); 
	

	}
	exit();
		
?>