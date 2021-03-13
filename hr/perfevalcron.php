<?php
	$path=$_SERVER['DOCUMENT_ROOT']; 
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$currentyr=date('Y');
	$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
	date_default_timezone_set('Asia/Manila');
	
	$path=$_SERVER['DOCUMENT_ROOT']; 
	require($path."/acrossyrs/downloadedphp/PHPMailer/class.phpmailer.php");
	
	
   $month=date('m');
   //insert main
      $sql0='SELECT e.`IDNo`, `PositionID`, `DefaultBranchAssignNo`, DATE_ADD(`e`.`DateHired`, INTERVAL ';
      $sql1=' DAY) AS `EvalDueDate`, ';
      $sql2=' AS `EvalAfterDays`, 0 AS `EncodedByNo`, Now() AS `Timestamp`
	    FROM `1employees` e JOIN `attend_30currentpositions` p ON e.IDNo=p.IDNo
	    JOIN `attend_1defaultbranchassign` dba ON e.IDNo=dba.IDNo
	    HAVING MONTH(`EvalDueDate`)='.$month.' AND YEAR(`EvalDueDate`)='.$currentyr.''; 
      $sql='INSERT IGNORE INTO `hr_82perfevalmain` (`IDNo`,`CurrentPositionID`,`CurrentBranchNo`,`EvalDueDate`, `EvalAfterDays`,`HREncodedByNo`,`HRTimestamp`) '.
	    $sql0.'90'.$sql1.'90'.$sql2.' UNION  '.$sql0.'150'.$sql1.'150'.$sql2.' UNION  '.$sql0.'365'.$sql1.'365'.$sql2;
		
      $stmt=$link->prepare($sql); $stmt->execute();
    
//update main
      $sql='UPDATE hr_82perfevalmain pf JOIN attend_30currentpositions cp ON pf.IDNo=cp.IDNo SET 
      SIDNo=(IF((cp.deptid<>10),(SELECT cp3.LatestSupervisorIDNo FROM `attend_30currentpositions` cp3 WHERE cp3.IDNo=pf.IDNo),(SELECT OpsSpecialist FROM attend_30currentpositions cp4 JOIN attend_1branchgroups bg ON cp4.BranchNo=bg.BranchNo WHERE cp4.IDNo=pf.IDNo))),
      DIDNo=(SELECT cp2.IDNo FROM `attend_30currentpositions` cp2 WHERE cp2.PositionID=(SELECT cp2.PositionID FROM `attend_30currentpositions` cp2 
      WHERE cp2.PositionID=(SELECT IF((cp1.deptheadpositionid=cp1.PositionID),cp1.supervisorpositionid,cp1.deptheadpositionid) FROM `attend_30currentpositions` cp1 WHERE cp1.IDNo=pf.IDNo))) WHERE DATE(`HRTimestamp`)=CURDATE();';
        
            $stmt=$link->prepare($sql); $stmt->execute();
            

    $sqlpopultatedtoday='SELECT pem.TxnID,
    
    (SELECT FormID FROM hr_81perfevalforms WHERE FIND_IN_SET(cp.PositionID,Positions))
    
     AS FormID
      FROM attend_30currentpositions cp JOIN hr_82perfevalmain pem ON cp.IDNo=pem.IDNo WHERE DATE(HRTimestamp)=CURDATE()';
    $stmt=$link->query($sqlpopultatedtoday); $res=$stmt->fetchAll();
		foreach ($res AS $row){
            $sql='INSERT IGNORE INTO `hr_82perfevalsub` (`TxnID`,`CID`) SELECT '.$row['TxnID'].',CID FROM hr_81corecompetencies WHERE FormID="'.$row['FormID'].'";'; 
              $stmt=$link->prepare($sql); $stmt->execute();  
        }


        $sql='INSERT IGNORE INTO hr_82perfevalmonthlymain (IDNo,MonthNo,SIDNo,EncodedByNo,TimeStamp) select cp.IDNo,'.$month.',(IF((cp.deptid<>10),(SELECT cp3.LatestSupervisorIDNo FROM `attend_30currentpositions` cp3 WHERE cp3.IDNo=cp.IDNo),(SELECT OpsSpecialist FROM attend_30currentpositions cp4 JOIN attend_1branchgroups bg ON cp4.BranchNo=bg.BranchNo WHERE cp4.IDNo=cp.IDNo))),0,NOW() from attend_30currentpositions cp JOIN attend_howlongwithus h ON cp.IDNo=h.IDNo WHERE InYears>=.3 UNION SELECT cp.IDNo,'.date('m').',(IF((cp.deptid<>10),(SELECT cp3.LatestSupervisorIDNo FROM `attend_30currentpositions` cp3 WHERE cp3.IDNo=cp.IDNo),(SELECT OpsSpecialist FROM attend_30currentpositions cp4 JOIN attend_1branchgroups bg ON cp4.BranchNo=bg.BranchNo WHERE cp4.IDNo=cp.IDNo))),0,NOW() from attend_30currentpositions cp JOIN hr_82perfevalmain pem ON cp.IDNo=pem.IDNo WHERE MONTH(EvalDueDate)='.$month.'';
        $stmt=$link->prepare($sql); $stmt->execute();
          
  
      $sqlpopultatedtoday='SELECT pemm.TxnID,
      
      (SELECT FID FROM hr_82fcmain WHERE FIND_IN_SET(cp.PositionID,DefaultPositions))
      
       AS FID
        FROM attend_30currentpositions cp JOIN hr_82perfevalmonthlymain pemm ON cp.IDNo=pemm.IDNo';
  
      $stmt=$link->query($sqlpopultatedtoday); $res=$stmt->fetchAll();
              foreach ($res AS $row){
              $sql='INSERT IGNORE INTO `hr_82perfevalmonthlysub` (`TxnID`,`FCID`,`Weight`) SELECT '.$row['TxnID'].',FCID,DefaultWeight FROM hr_82fcsub WHERE FID="'.$row['FID'].'";'; 
                $stmt=$link->prepare($sql); $stmt->execute(); 
          }
      
	  
// 	  $sqlheads='select DISTINCT(DeptHeadIDNo) AS DeptHeads FROM hr_2perfevalmain WHERE MONTH(EvalDueDate)>='.date('m').' AND DeptHeadConfirm=0';
	
	
// 	$stmtheads=$link->query($sqlheads); $resultheads=$stmtheads->fetchAll();
	
// 	foreach($resultheads AS $resulthead){
		
// 			$arrayheads[] = $resulthead['DeptHeads'];
// 		} 
	
	

// 	foreach($arrayheads as $h){
		
// 		$sql='select pem.TxnID,CONCAT (FirstName,\' \',SurName) as Name,EvalDueDate,IFNULL((SELECT SUM(Weight) FROM hr_2perfevalsub pes JOIN hr_1positionstatement ps ON pes.PSID=ps.PSID WHERE TxnID=pem.TxnID),0) AS TotWeight FROM hr_2perfevalmain pem left join 1employees e on e.IDNo=pem.IDNo WHERE MONTH(EvalDueDate)>='.date('m').' AND DeptHeadConfirm=0 AND DeptHeadIDNo='.$h.'';
		
		
// 	$stmt=$link->query($sql); $result=$stmt->fetchAll();
// 	$table='';
// 	if($stmt->rowCount()!=0){
		
// 		$table.='Good day. The following employees have pending evaluations:</br></br>
// 		<table><tr><th>EmployeeName</th><th>EvalDueDate</th><th>'.(($res['TotWeight']<>100)?"Statements":"").'<th><th>Link</th></tr>';
		 
// 		}
		 
// 		$mail = new PHPMailer();
// 		$mail->IsSMTP();  // telling the class to use SMTP
// 		$mail->SMTPDebug = 2; // debugging: 1 = errors and messages, 2 = messages only
// 		$mail->Host = "smtp.gmail.com"; // SMTP server
// 		$mail->Port = '587';//'465';
// 		$mail->IsHTML(true);
// 		$mail->SMTPAuth = true;                               // Enable SMTP authentication
// 		$mail->SMTPSecure = 'tls';//'ssl';
// 		$mail->Username = '1rtcicon@gmail.com';                            // SMTP username
// 		$mail->Password = '1RotaRy1003$';                           // SMTP password

// 		$mail->From = '1rtcicon@gmail.com';
// 		$mail->FromName = 'Performance Evaluation';

// 		$mail->Subject  = "Pending Performance Evaluation";
// 		$mail->WordWrap = 50;

		
// 		foreach($result as $res){
// 			$address='https://www.arwan.biz/yr'.date('y').'/hr/perfevalentryform.php?TxnID='.$res['TxnID'];
// 				$table.='<tr><td>'.$res['Name'].'</td><td>'.$res['EvalDueDate'].'</td><td>'.(($res['TotWeight']<100)?"<font color='red'>Incomplete</font>":(($res['TotWeight']>100)?"<font color='red'>Excess</font>":"")).'</td><td><a href="'.$address.'">Lookup</a></td></tr>';
// 		}
// 		if($stmt->rowCount()!=0){
// 		$table.='</table></br></br>System-generated.  Please do not reply.';
// 		}
// 			$sql='SELECT Email FROM `1_gamit`.`1rtcusers` where IDNo=\''.$h.'\'';
			
// 			$stmt=$link->query($sql);
// 			$res=$stmt->fetch();

// 			$mail->AddAddress($res['Email']);
// 			$mail->Body     = $table;
// 			$mail->AltBody     = $table; 
// 			if(!$mail->Send()) {
// echo 'Message was not sent.';
// echo 'Mailer error: ' . $mail->ErrorInfo;
// } else {
// echo 'Message has been sent.';
// }
// 			 $mail->ClearAddresses(); 
	

// 	}
// 	exit();
		
?>