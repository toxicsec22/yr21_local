<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
if (!allowedToOpen(7801,'1rtc')) { echo 'No permission'; exit();}
if ($_GET['w']!='print' AND $_GET['w']!='2316'){
include_once('../switchboard/contents.php');} else {
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
}
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=(!isset($_GET['w'])?'Process':$_GET['w']);

if (in_array($which,array('Process','edit'))){
// echo comboBox($link,'SELECT  IDNo,Concat(NickName,\' \',Surname) as FullName FROM 1employees where IDNo not in(1001,1002)','IDNo','FullName','EmployeeList');}
echo comboBox($link,'SELECT  IDNo,Concat(NickName,\' \',Surname) as FullName FROM 1employees where IDNo not in(1001,1002)','FullName','IDNo','EmployeeList');}
echo comboBox($link,'SELECT 0 AS EmpStat,"Probationary" AS EmploymentStatus UNION SELECT 1 AS EmpStat,"Regular" AS EmploymentStatus','EmpStat','EmploymentStatus','empstat');
if (in_array($which,array('insert','editprocess'))){
// $employee=comboBoxValue($link, '1employees', 'Concat(NickName,\' \',Surname)', $_POST['IDNo'], 'IDNo');
	$employee=$_POST['IDNo'];
}
if (in_array($which,array('print','lookup'))){
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link; $link=$link;
	$txnid=intval($_GET['TxnID']);
	$sql='select rp.*,CONCAT(e.FirstName,\' \', e.SurName ) AS EmployeeName,Position,e.DateHired,Branch,department,rp.Date,Company,
	(CASE
		WHEN EmpStatus=0 THEN "Probationary"
		WHEN EmpStatus=1 THEN "Regular"
		ELSE ""
	END) AS EmploymentStatus,
	CONCAT (ehr.Nickname,\' \',ehr.SurName) as EncByHR,
	CONCAT (eadmin.Nickname,\' \',eadmin.SurName) as EncByADMIN ,
	CONCAT (eacctg.Nickname,\' \',eacctg.SurName) as EncByACCTG,
	CONCAT (esales.Nickname,\' \',esales.SurName) as EncBySALES,
	CONCAT (eops.Nickname,\' \',eops.SurName) as EncByOPS,
	CONCAT (efinance.Nickname,\' \',efinance.SurName) as EncByFINANCE,
	CONCAT (esc.Nickname,\' \',esc.SurName) as EncBySC,
	CONCAT (emktg.Nickname,\' \',emktg.SurName) as EncByMKTG,
	Date(ADMINTS) as ADMINDate,
	Date(HRTS) as HRDate,
	Date(ACCTGTS) as ACCTGDate,
	Date(SALESTS) as SALESDate,
	Date(OPSTS) as OPSDate,
	Date(FINANCETS) as FINANCEDate,
	Date(SCTS) as SCDate,
	Date(MKTGTS) as MKTGDate,
	format(lastsalary,2) as lastsalary,
	format(thirteenth,2) as thirteenth,
	format(usl,2) as usl,
	format(tax,2) as tax,
	format(deductions,2) as deductions,
	format((lastsalary+thirteenth+usl+tax),2) as totalhr,
	format(((lastsalary+thirteenth+usl+tax)-deductions),2) as total,
	
	
	lastsalary as lastsalaryvalue,
	thirteenth as thirteenthvalue,
	usl as uslvalue,
	tax as taxvalue,
	deductions as deductionsvalue
	
	from hr_2resignationprocess rp left join 1employees e on e.IDNo=rp.IDNo left join 
    attend_30latestpositionsinclresigned lpir ON rp.IDNo=lpir.IDNo LEFT JOIN 
    attend_1defaultbranchassign dba ON rp.IDNo=dba.IDNo 
    left join attend_0positions p on p.PositionID=lpir.PositionID left join 1branches b on b.BranchNo=dba.DefaultBranchAssignNo left join 1departments d on d.deptid=p.deptid left join 1companies c on e.RcompanyNo=c.CompanyNo
	
	left join 1employees ehr on ehr.IDNo=rp.EncByHR
	left join 1employees eadmin on eadmin.IDNo=rp.EncByADMIN
	left join 1employees eacctg on eacctg.IDNo=rp.EncByACCTG
	left join 1employees esales on esales.IDNo=rp.EncBySALES
	left join 1employees eops on eops.IDNo=rp.EncByOPS
	left join 1employees efinance on efinance.IDNo=rp.EncByFINANCE
	left join 1employees esc on esc.IDNo=rp.EncBySC
	left join 1employees emktg on emktg.IDNo=rp.EncByMKTG
	left join 1employees fd on fd.IDNo=rp.EncByFD
	where rp.TxnID='.$txnid.' GROUP BY rp.TxnID ';
	
	// from hr_2resignationprocess rp left join 1employees e on e.IDNo=rp.IDNo left join (select max(DateofChange),NewPositionID,AssignedBranchNo,IDNo from attend_2changeofpositions Group By IDNo) cp on cp.IDNo=e.IDNo left join attend_0positions p on p.PositionID=cp.NewPositionID left join 1branches b on b.BranchNo=cp.AssignedBranchNo left join 1departments d on d.deptid=p.deptid left join 1companies c on e.RcompanyNo=c.CompanyNo
	// echo $sql; exit();
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	
	$sqladmin='select rps.*,Description,ci.NA,deptid
	from hr_2resignationprocesssub rps join hr_0clearanceitems ci on rps.CIID=ci.CIID
	where rps.TxnID='.$txnid.' and deptid=50 ';
	// echo $sqladmin; exit();
	$stmtadmin=$link->query($sqladmin);
	$resultadmin=$stmtadmin->fetchAll();
	
	$sqlhr='select rps.*,Description,ci.NA,deptid
	from hr_2resignationprocesssub rps join hr_0clearanceitems ci on rps.CIID=ci.CIID
	where rps.TxnID='.$txnid.' and deptid=30 ';
	// echo $sqlaccounting; exit();
	$stmthr=$link->query($sqlhr);
	$resulthr=$stmthr->fetchAll();
	
	$sqlacctg='select rps.*,Description,ci.NA,deptid
	from hr_2resignationprocesssub rps join hr_0clearanceitems ci on rps.CIID=ci.CIID
	where rps.TxnID='.$txnid.' and deptid=20 ';
	// echo $sqlaccounting; exit();
	$stmtacctg=$link->query($sqlacctg);
	$resultacctg=$stmtacctg->fetchAll();
	
	$sqlsales='select rps.*,Description,ci.NA,deptid
	from hr_2resignationprocesssub rps join hr_0clearanceitems ci on rps.CIID=ci.CIID
	where rps.TxnID='.$txnid.' and deptid=11 ';
	// echo $sqlsales; exit();
	$stmtsales=$link->query($sqlsales);
	$resultsales=$stmtsales->fetchAll();

	$sqlops='select rps.*,Description,ci.NA,deptid
	from hr_2resignationprocesssub rps join hr_0clearanceitems ci on rps.CIID=ci.CIID
	where rps.TxnID='.$txnid.' and deptid=70 ';
	// echo $sqlops; exit();
	$stmtops=$link->query($sqlops);
	$resultops=$stmtops->fetchAll();

	$sqlfinance='select rps.*,Description,ci.NA,deptid
	from hr_2resignationprocesssub rps join hr_0clearanceitems ci on rps.CIID=ci.CIID
	where rps.TxnID='.$txnid.' and deptid=60 ';
	// echo $sqlfinance; exit();
	$stmtfinance=$link->query($sqlfinance);
	$resultfinance=$stmtfinance->fetchAll();
	
	$sqlsc='select rps.*,Description,ci.NA,deptid
	from hr_2resignationprocesssub rps join hr_0clearanceitems ci on rps.CIID=ci.CIID
	where rps.TxnID='.$txnid.' and deptid=1 ';
	// echo $sqlsc; exit();
	$stmtsc=$link->query($sqlsc);
	$resultsc=$stmtsc->fetchAll();
	
	$sqlmktg='select rps.*,Description,ci.NA,deptid
	from hr_2resignationprocesssub rps join hr_0clearanceitems ci on rps.CIID=ci.CIID
	where rps.TxnID='.$txnid.' and deptid=15 ';
	// echo $sqlsc; exit();
	$stmtmktg=$link->query($sqlmktg);
	$resultmktg=$stmtmktg->fetchAll();
}
switch ($which){     
	case 'Process':
	include_once('../backendphp/layout/showencodedbybutton.php');
	$date=date('Y-m-d',strtotime("next month"));
 // echo $_SESSION['(ak0)']; exit();
	$sqlr='select PositionID from attend_30currentpositions where IDNo='.$_SESSION['(ak0)'].' ';
	$stmt=$link->query($sqlr); $resultr=$stmt->fetch();
	// $radmin='WHERE ADMINFD<>1';  if(!isset($_POST['filter'])){$racctg='WHERE ACCTGFD<>1';}else{$racctg='';} $rsales='WHERE SALESFD<>1'; $rops='WHERE OPSFD<>1'; $rfinance='WHERE FINANCEFD<>1';
	// $rsc='WHERE SCFD<>1'; $rmktg='WHERE MKTGFD<>1';
	if (allowedToOpen(7810,'1rtc')) {
		$listcondition='WHERE ADMINFD=0';
		$othercondi='WHERE ADMINFD<>0 and FinalDecision=0';
	}elseif(allowedToOpen(7811,'1rtc')) {
		if(!isset($_POST['filter'])){
			$listcondition='WHERE ACCTGFD=0';
			$othercondi='WHERE ACCTGFD<>0 and FinalDecision=0';
		}else{
			$listcondition='';
		}
	}elseif(allowedToOpen(7812,'1rtc')) {
		$listcondition='WHERE SALESFD=0';
		$othercondi='WHERE SALESFD<>0 and FinalDecision=0';
	}elseif(allowedToOpen(7813,'1rtc')) {
		$listcondition='WHERE OPSFD=0';
		$othercondi='WHERE OPSFD<>0 and FinalDecision=0';
	}elseif(allowedToOpen(7814,'1rtc')) {
		$listcondition='WHERE FINANCEFD=0';
		$othercondi='WHERE FINANCEFD<>0 and FinalDecision=0';
	}elseif(allowedToOpen(7815,'1rtc')) {
		$listcondition='WHERE SCFD=0';
		$othercondi='WHERE SCFD<>0 and FinalDecision=0';
	}elseif(allowedToOpen(7818,'1rtc')) {
		$listcondition='WHERE MKTGFD=0';
		$othercondi='WHERE MKTGFD<>0 and FinalDecision=0';
	}

	// echo $resultr['PositionID']; exit();
 
		$title='Resigning Employees';
		if (allowedToOpen(7802,'1rtc') or allowedToOpen(7821,'1rtc')) {
			
			if(!isset($_POST['filter'])){
				if(allowedToOpen(7802,'1rtc')){$conditionf='WHERE FinalDecision=0'; $listcondition='';}else{$conditionf='';}
				// echo $conditionf; exit();
			}else{
				switch($_POST['filtering']){
					case'4': $conditionf='WHERE FinalDecision=3'; break;
					case '3': $conditionf=''; break;
					case '1': $conditionf='WHERE FinalDecision=1'; break;
					case '2': $conditionf='WHERE FinalDecision=2'; break;
					case '0':if(allowedToOpen(7802,'1rtc')){$conditionf='WHERE FinalDecision=0';}else{$conditionf='WHERE ACCTGFD<>1';} break;
					
				}
				$listcondition='';
				
			}
			
		$formdesc='</i></br><form style="display:inline;" method="post" action="resignationprocess.php?w=Process">
									Filtering: <select  name="filtering">
									'.(allowedToOpen(7802,'1rtc')?'
									<option value="3" '.(isset($_POST['filtering'])?' '.(($_POST['filtering']==3)?'selected':'').' ':'').'>All</option>
									<option value="4" '.(isset($_POST['filtering'])?' '.(($_POST['filtering']==4)?'selected':'').' ':'').'>Withdrawn</option>
									<option value="1" '.(isset($_POST['filtering'])?' '.(($_POST['filtering']==1)?'selected':'').' ':'').'>Cleared</option>
									':'').'
									<option value="2" '.(isset($_POST['filtering'])?' '.(($_POST['filtering']==2)?'selected':'').' ':'').'>Denied</option>
									<option value="0" '.((!isset($_POST['filtering']) OR $_POST['filtering']==0)?'selected':'').'>Pending</option>
							</select>
							<input type="submit" name="filter" value="Filter">
							</form>
					';
		if (allowedToOpen(7802,'1rtc')) {		
		$formdesc.='</br></br><form style="display:inline;" method="POST" action="resignationprocess.php?w=insert" >
					<input type="text" name="IDNo"  list="EmployeeList" placeholder="List of Employees" required="true"></input>
					Effectivity Date: <input type="date" name="Date" value="'.$date.'" required="true" ></input>
					<input type="text" name="Remarks" placeholder="Remarks"  ></input>
					<input type="submit" name="submit" value="Submit" OnClick="return confirm(\'Are you sure you want to submit?\');"></input>
		</form>';
			}
		}else{
			$conditionf='';
		}
		if (!allowedToOpen(7802,'1rtc')) {
			if (!allowedToOpen(7802,'1rtc') and !allowedToOpen(7821,'1rtc')) {
				$formdesc='</br>';
			}
			if(!isset($_POST['deptheadfiltering'])){
			$formdesc.='<form style="display:inline;" method="POST" action="resignationprocess.php?w=Process" >
							<input type="submit" name="deptheadfiltering" value="Clearance with Pending Final Decision"></input>
						</form>';
			}else{
			$formdesc.='<form style="display:inline;" method="POST" action="resignationprocess.php?w=Process" >
							<input type="submit" name="ShowDefault" value="ShowDefault"></input>
						</form>';
			}
		}
		$formdesc.='</br>';
		if(isset($_POST['deptheadfiltering'])){
			$conditionf=$othercondi;
			$listcondition='';			
		}
		
		$sql='Select TxnID,Date as EffectivityDate,IF(FinalDecision<>0,"",(DATEDIFF(CURDATE(),`Date`))) As AgeOfClearanceInDays,e1.Nickname as Nickname,Concat(e1.FirstName,\' \',e1.SurName) as FullName,(SELECT IF(PseudoBranch=1,dept,Branch) FROM attend_1defaultbranchassign dba JOIN attend_30latestpositionsinclresigned lpir ON dba.IDNo=lpir.IDNo JOIN 1branches b ON DefaultBranchAssignNo=b.BranchNo JOIN attend_0positions p ON lpir.PositionID=p.PositionID JOIN 1departments d ON p.deptid=d.deptid WHERE lpir.IDNo=e1.IDNo) AS `Dept/Branch`,Remarks,Concat (e.NickName,\' \',e.SurName) as EncodedBy,rp.TimeStamp from hr_2resignationprocess rp join 1employees e on e.IDNo=rp.EncodedByNo join 1employees e1 on e1.IDNo=rp.IDNo '.$conditionf.' '.$listcondition.' ORDER BY `Date` DESC';
		// echo $sql; exit();
		$txnid='TxnID';
		$columnnames=array('EffectivityDate','Nickname','FullName','Dept/Branch','Remarks','AgeOfClearanceInDays');
		if ($showenc==1) { array_push($columnnames,'EncodedBy','TimeStamp');}
		// $width='70%';
		if (allowedToOpen(7802,'1rtc')) {
		$editprocess='resignationprocess.php?w=edit&TxnID=';
		$editprocesslabel='edit';
		$delprocess='resignationprocess.php?w=delete&TxnID=';
		$editprocess3='resignationprocess.php?w=unpost&TxnID=';
		$editprocesslabel3='unpost';
		$addlprocess='resignationprocess.php?w=withdraw&TxnID=';
		$addlprocesslabel='withdraw';
		}
		$editprocess2='resignationprocess.php?w=lookup&TxnID=';
		$editprocesslabel2='lookup';
		include('../backendphp/layout/displayastable.php');
		
		
	break;
	
	case'SetAsIncomplete':
	$txnid=intval($_GET['TxnID']);
	if (allowedToOpen(7819,'1rtc')) {
		$condi='and TIMESTAMPDIFF(month,FDTS,now())<=1';
	}else{
		$condi='';
		
	}
		$sql='UPDATE hr_2resignationprocess SET EncByFD='.$_SESSION['(ak0)'].',FDTS=Now(),FinalDecision=0 where txnid='.$txnid.' '.$condi.'';
          // echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: resignationprocess.php?w=Process');
	break;
	
	
	
	case 'insert':
		$sql='INSERT INTO hr_2resignationprocess SET Date=\''.$_POST['Date'].'\',IDNo=\''.$employee.'\',Remarks=\''.$_POST['Remarks'].'\',EmpStatus=(SELECT EmpStatus FROM 1_gamit.0idinfo WHERE IDNo='.$employee.'), EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=Now() ';
        //   echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		
		$sql='select Branch,Date as EffectivityDate,CONCAT (FirstName,\' \',SurName) as Name,TxnID from hr_2resignationprocess rp left join 1employees e on e.IDNo=rp.IDNo left join (select max(DateofChange),NewPositionID,AssignedBranchNo,IDNo from attend_2changeofpositions Group By IDNo) cp on cp.IDNo=e.IDNo left join 1branches b on b.BranchNo=cp.AssignedBranchNo ORDER BY TxnID DESC limit 1';
		$stmt=$link->query($sql); $result=$stmt->fetch();
		
		$sqlci='select CIID from hr_0clearanceitems ORDER BY CIID ';
		$stmtci=$link->query($sqlci); $resultci=$stmtci->fetchAll();
	
		foreach($resultci as $res){
		$sqlsub='INSERT INTO hr_2resignationprocesssub SET TxnID='.$result['TxnID'].',CIID='.$res['CIID'].'';
		$stmtsub=$link->prepare($sqlsub); $stmtsub->execute();
		}

		// exit();
		// ini_set("include_path", ".:/usr/share/php/PHPMailer");
		// require("../../PHPMailer/class.phpmailer.php");
		
		$path=$_SERVER['DOCUMENT_ROOT']; 
		 require($path."/acrossyrs/downloadedphp/PHPMailer/class.phpmailer.php");
		include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
		include_once($path.'/acrossyrs/dbinit/userinit.php');
		$link=connect_db(''.$currentyr.'_1rtc',0);

		$sql='SELECT ifnull(u.Email,b.Email) as Email, b.Branch, FullName FROM `1_gamit`.`1rtcusers` u join `attend_1defaultbranchassign` db on u.IDNo=db.IDNo join 1branches b on b.BranchNo=db.`DefaultBranchAssignNo` join `attend_30currentpositions` p on u.IDNo=p.IDNo where u.IDNo='.$_SESSION['(ak0)'];
		$stmt=$link->query($sql);
		$res=$stmt->fetch();
		 
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
		$msg='
		Good day.  Please be informed that '.$result['Name'].' of '.$result['Branch'].' is resigning effective '.$result['EffectivityDate'].'. </br></br>
		Link: <a href="http://www.arwan.biz/yr21/hr/resignationprocess.php?w=lookup&TxnID='.$result['TxnID'].'"> http://www.arwan.biz/yr21/hr/resignationprocess.php?w=lookup&TxnID='.$result['TxnID'].' </a>
		
		</br></br>System-generated.  Please do not reply.'; $total=0;

		//echo $msg; break;
		// $id=(isset($_REQUEST['Request']))?1014:1002;
			$sqle='select AllowedPos from permissions_2allprocesses where ProcessID=\'7816\' ';
			$stmte=$link->query($sqle); $resulte=$stmte->fetch();
		
		$sql='SELECT Email FROM `1_gamit`.`1rtcusers` u join `attend_30currentpositions` p on u.IDNo=p.IDNo  where PositionID in ('.$resulte['AllowedPos'].')';
		// echo $sql; exit();
		$stmt=$link->query($sql);
		$res=$stmt->fetchAll();
		foreach ($res as $row) {
			$mail->AddAddress($row['Email']);
			$mail->Body     = $msg;
			$mail->AltBody     = $msg;
		}
			if(!$mail->Send()) {
		echo 'Message was not sent.';
		echo 'Mailer error: ' . $mail->ErrorInfo;
		}

		header('Location: resignationprocess.php?w=Process');
	break;
	
	case 'edit':
		$txnid=intval($_GET['TxnID']);
		$title='Edit';
		$sql='Select Date,IDNo,Remarks,(CASE 
			WHEN EmpStatus=0 THEN "Probationary"
			WHEN EmpStatus=1 THEN "Regular"
			ELSE ""
		END) AS EmploymentStatus from hr_2resignationprocess where TxnID='.$txnid.''; 
			
		$columnswithlists=array('Date','IDNo','Remarks','EmploymentStatus');
		$listsname=array('IDNo'=>'EmployeeList','EmploymentStatus'=>'empstat');
		$columnnames=$columnswithlists;
		$columnstoedit=$columnnames;
		$editprocess='resignationprocess.php?w=editprocess&TxnID='.$txnid;
		include('../backendphp/layout/editspecificsforlists.php'); 
	break;
	
	case 'editprocess':
	$txnid=intval($_GET['TxnID']);
	if($_POST['EmploymentStatus']=="Probationary"){
		$empstat='EmpStatus=0,';
	} else if($_POST['EmploymentStatus']=="Regular"){
		$empstat='EmpStatus=1,';
	} else {
		$empstat='EmpStatus=NULL,';
	}
	
		$sql='UPDATE hr_2resignationprocess SET Date=\''.$_POST['Date'].'\','.$empstat.'IDNo=\''.$employee.'\',Remarks=\''.$_POST['Remarks'].'\', EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=Now() where TxnID='.$txnid.' ';
          // echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: resignationprocess.php?w=Process');
	break;
	
	case 'delete':
	$txnid=intval($_GET['TxnID']);
		
		$sqlsub='delete from hr_2resignationprocesssub where TxnID='.$txnid.' ';
		$stmt=$link->prepare($sqlsub); $stmt->execute();
		$sql='delete from hr_2resignationprocess where TxnID='.$txnid.' AND FinalDecision=0';
          // echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: resignationprocess.php?w=Process');
	break;
	
	case 'lookup':
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	echo comboBox($link,'SELECT CIID, Description FROM hr_0clearanceitems WHERE CIID BETWEEN 1 AND 35 ORDER BY Description','CIID','Description','itemlist');
	$sqll='SELECT deptid,PositionID FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].'';
	$stmt=$link->query($sqll); $resultl=$stmt->fetch();
	
	?>
	<title>Clearance</title>
				<style>
table {
  border-collapse: collapse;
  width: 100%;
  font-size:10pt;
  border:1px solid black;
  background-color:white;
}

th, td {
  text-align: left;
  padding: 8px;
}
tr:hover{
	background-color: #e6e6e6;
}


			</style>
	<?php
	
	echo '<h3 style="display:inline;">Clearance Status</h3>'.((allowedToOpen(7819,'1rtc') or allowedToOpen(7820,'1rtc'))?'<a OnClick="return confirm(\'Are you sure you want to set as incomplete?\');" style="display:inline; float:right;" href="resignationprocess.php?w=SetAsIncomplete&TxnID='.$_GET['TxnID'].'"><b>SetAsIncomplete?</b></a>':'').'</br></br>';
		echo'<div style="background-color:#cccccc; float:right; width: 45%; border: 1px solid black; padding: 25px; margin-top:28px;"><b>INSTRUCTIONS:</b><br>
               (Kindly set N/A if the items enlisted below are not applicable for your department.)</br></br>
				
				<b>STATUS:</b></br>
				ADMIN '.(($result['ADMINFD']==1)?'<b>CLEARED</b>':(($result['ADMINFD']==2)?'<b>DENIED</b>':'<b>PENDING</b>')).'</br>
				HUMAN RESOURCES '.(($result['HRFD']==1)?'<b>CLEARED</b>':(($result['HRFD']==2)?'<b>DENIED</b>':'<b>PENDING</b>')).'</br>
				ACCOUNTING '.(($result['ACCTGFD']==1)?'<b>CLEARED</b>':(($result['ACCTGFD']==2)?'<b>DENIED</b>':'<b>PENDING</b>')).'</br>
				SALES - OFFICE '.(($result['SALESFD']==1)?'<b>CLEARED</b>':(($result['SALESFD']==2)?'<b>DENIED</b>':'<b>PENDING</b>')).'</br>
				OPERATIONS '.(($result['OPSFD']==1)?'<b>CLEARED</b>':(($result['OPSFD']==2)?'<b>DENIED</b>':'<b>PENDING</b>')).'</br>
				FINANCE '.(($result['FINANCEFD']==1)?'<b>CLEARED</b>':(($result['FINANCEFD']==2)?'<b>DENIED</b>':'<b>PENDING</b>')).'</br>
				SUPPLY CHAIN - OFFICE '.(($result['SCFD']==1)?'<b>CLEARED</b>':(($result['SCFD']==2)?'<b>DENIED</b>':'<b>PENDING</b>')).'</br>
				MARKETING '.(($result['MKTGFD']==1)?'<b>CLEARED</b>':(($result['MKTGFD']==2)?'<b>DENIED</b>':'<b>PENDING</b>')).'</br>
				FINAL DECISION '.(($result['FinalDecision']==1)?'<b>CLEARED</b>':(($result['FinalDecision']==2)?'<b>DENIED</b>':'<b>PENDING</b>')).'

             
			 </div>';
	echo '
		<div><table style="width:50%;">
		<caption><font size="5">SEPARATION CLEARANCE FORM</font></caption>
		<tr><td>ID NO. / EMPLOYEE NAME</td><td>POSITION</td><td>DATE HIRED</td></tr>
		<tr><th>'.$result['IDNo'].' / '.$result['EmployeeName'].'</th><th>'.$result['Position'].'</th><th>'.$result['DateHired'].'</th></tr>
		<tr><td>COMPANY / DEPARTMENT / BRANCH</td><td>EMPLOYMENT STATUS</td><td>DATE SEPARATED</td></tr>
		<tr><th>'.$result['Company'].' / '.$result['department'].' / '.$result['Branch'].'</th><th>'.$result['EmploymentStatus'].'</th><th>'.$result['Date'].'</th></tr></table></div>';
		//ADMIN
		if (allowedToOpen(7803,'1rtc')) {
		echo'
		<div><table style="width:50%">
		<tr><th>ADMIN</th><th>CLEARED</th><th>UNCLEARED</th><th>N/A</th></tr>';
		$cnt=1;
		foreach($resultadmin as $rowsadmin){
		echo'
		<form method="post" action="resignationprocess.php?w=insertADMIN&TxnID='.$txnid.'">
		<tr><td>'.$rowsadmin['Description'].'</td><td>
		<input type="radio" name="countr_'.$cnt.'" value="1" '.(($rowsadmin['ClearedOrUncleared']==1)?'checked':'').'></td><td><input type="radio" name="countr_'.$cnt.'" value="2" '.(($rowsadmin['ClearedOrUncleared']==2)?'checked':'').'></td>
		<td>'.(($rowsadmin['NA']!=1)?'<input type="radio" name="countr_'.$cnt.'" value="3" '.(($rowsadmin['ClearedOrUncleared']==3)?'checked':'').'>':'').'</td>
		</tr>
		<input type="hidden" name="TxnSubId'.$cnt.'" value="'.$rowsadmin['TxnSubId'].'">';
		$cnt++;
		}
		echo '
		<table style="width:50%;">
		<tr><th>ClearedBy:'.$result['EncByADMIN'].' Date:'.$result['ADMINDate'].' Other Charges:'.$result['ADMINREMARKS'].' '.$result['ADMINCHARGE'].'</th><th></th><th>'.((allowedToOpen(7810,'1rtc'))?'<input type="submit" value="Submit" OnClick="return confirm(\'Are you sure you want to submit?\');">':'').'</th></tr>
		</form>
		<tr><form method="post" action="resignationprocess.php?w=newrowADMIN&TxnID='.$txnid.'"><td>'.((allowedToOpen(7810,'1rtc'))?'<input type="text" name="items" list="itemlist" placeholder="Items" ><input type="text" name="Description" placeholder="if Other, please specify" > <input type="submit" name="newrow" value="Add New Items"  OnClick="return confirm(\'Are you sure you want to Add a New Item?\');">':'').'</td><td></td><td></td></form></tr>
		</div></table></br>';
		
		echo ''.((allowedToOpen(7810,'1rtc'))?'<form method="post" action="resignationprocess.php?w=finaldecisionADMIN&TxnID='.$txnid.'">
		<table style="width:50%;"><tr><td colspan="3"><b>Other Charges? </b><input type="text" name="ADMINREMARKS" placeholder="Remarks"> <input type="text" name="ADMINCHARGE" placeholder="Amount"></td></tr></table></br>
		<table style="width:50%;">
		<tr><th>FINAL DECISION</th><th></th><th></th></tr>
		
		<tr><th>CLEARED</th><th>DENIED</th><th></th></tr>
		</tr><td><input type="radio" name="decision" value="1" '.(($result['ADMINFD']==1)?'checked':'').'></td>
		<td><input type="radio" name="decision" value="2" '.(($result['ADMINFD']==2)?'checked':'').'></td><td></td></tr>
		<tr><td></td><td></td><td><input type="submit" name="submit"></td></tr>
		<form></table></br>':'</br>').'';
		
		}
		//HR
		if (allowedToOpen(7802,'1rtc')) {
		echo'<div><table style="width:50%">
		<tr><th>HUMAN RESOURCES</th><th>CLEARED</th><th>UNCLEARED</th><th>N/A</th></tr>';
		$cnt=16;
		foreach($resulthr as $rowshr){
		echo'
		<form method="post" action="resignationprocess.php?w=insertHR&TxnID='.$txnid.'">
		<tr><td>'.$rowshr['Description'].'</td><td>
		<input type="radio" name="countr_'.$cnt.'" value="1" '.(($rowshr['ClearedOrUncleared']==1)?'checked':'').'></td><td><input type="radio" name="countr_'.$cnt.'" value="2" '.(($rowshr['ClearedOrUncleared']==2)?'checked':'').'></td>
		<td>'.(($rowshr['NA']!=1)?'<input type="radio" name="countr_'.$cnt.'" value="3" '.(($rowshr['ClearedOrUncleared']==3)?'checked':'').'>':'').'</td>
		</tr>
		<input type="hidden" name="TxnSubId'.$cnt.'" value="'.$rowshr['TxnSubId'].'">';
		$cnt++;
		}
		echo '
		<table style="width:50%;">
		<tr><th>ClearedBy:'.$result['EncByHR'].' Date:'.$result['HRDate'].' Other Charges:'.$result['HRREMARKS'].' '.$result['HRCHARGE'].'</th><th></th><th>'.((allowedToOpen(7809,'1rtc'))?'<input type="submit" value="Submit" OnClick="return confirm(\'Are you sure you want to submit?\');">':'').'</th></tr>
		</form>
		<tr><form method="post" action="resignationprocess.php?w=newrowHR&TxnID='.$txnid.'"><td>'.((allowedToOpen(7809,'1rtc'))?'<input type="text" name="items" list="itemlist" placeholder="Items" ><input type="text" name="Description" placeholder="if Other, please specify" > <input type="submit" name="newrow" value="Add New Items"  OnClick="return confirm(\'Are you sure you want to Add a New Item?\');">':'').'</td><td></td><td></td></form></tr>
		</div></table></br>';
		
		echo ''.((allowedToOpen(7809,'1rtc'))?'<form method="post" action="resignationprocess.php?w=finaldecisionHR&TxnID='.$txnid.'">
		<table style="width:50%;">
		<tr><td colspan="3"><b>Other Charges?</b><input type="text" name="HRREMARKS" placeholder="Remarks"> <input type="text" name="HRCHARGE" placeholder="Amount"></td></tr>
		</table></br>
		<table style="width:50%;">
		<tr><th>FINAL DECISION</th><th></th><th></th></tr>
		<tr><th>CLEARED</th><th>DENIED</th><th></th></tr>
		</tr><td><input type="radio" name="decision" value="1" '.(($result['HRFD']==1)?'checked':'').'></td>
		<td><input type="radio" name="decision" value="2" '.(($result['HRFD']==2)?'checked':'').'></td><td></td></tr>
		<tr><td></td><td></td><td><input type="submit" name="submit"></td></tr>
		<form></table></br>':'</br>').'';
		
		}
		//ACCOUNTING
		if (allowedToOpen(7804,'1rtc')) {
		echo'<div><table style="width:50%">
		<tr><th>ACCOUNTING</th><th>CLEARED</th><th>UNCLEARED</th><th>N/A</th></tr>';
		$cnt=23;
		foreach($resultacctg as $rowsacctg){
		echo'
		<form method="post" action="resignationprocess.php?w=insertACCTG&TxnID='.$txnid.'">
		<tr><td>'.$rowsacctg['Description'].'</td><td>
		<input type="radio" name="countr_'.$cnt.'" value="1" '.(($rowsacctg['ClearedOrUncleared']==1)?'checked':'').'></td><td><input type="radio" name="countr_'.$cnt.'" value="2" '.(($rowsacctg['ClearedOrUncleared']==2)?'checked':'').'></td>
		<td>'.(($rowsacctg['NA']!=1)?'<input type="radio" name="countr_'.$cnt.'" value="3" '.(($rowsacctg['ClearedOrUncleared']==3)?'checked':'').'>':'').'</td>
		</tr>
		<input type="hidden" name="TxnSubId'.$cnt.'" value="'.$rowsacctg['TxnSubId'].'">';
		$cnt++;
		}
		echo '
		<table style="width:50%;">
		<tr><th>ClearedBy:'.$result['EncByACCTG'].' Date:'.$result['ACCTGDate'].' InvtyCharge:'.$result['ACCTGCHARGE'].'</th><th></th><th>'.((allowedToOpen(7811,'1rtc'))?'<input type="submit" value="Submit" OnClick="return confirm(\'Are you sure you want to submit?\');">':'').'</th></tr>
		</form>
		<tr><form method="post" action="resignationprocess.php?w=newrowACCTG&TxnID='.$txnid.'"><td>'.((allowedToOpen(7811,'1rtc'))?'<input type="text" name="items" list="itemlist" placeholder="Items" ><input type="text" name="Description" placeholder="if Other, please specify" > <input type="submit" name="newrow" value="Add New Items"  OnClick="return confirm(\'Are you sure you want to Add a New Item?\');">':'').'</td><td></td><td></td></form></tr>
		</div></table></br>';
		
		echo ''.((allowedToOpen(7811,'1rtc'))?'<form method="post" action="resignationprocess.php?w=finaldecisionACCTG&TxnID='.$txnid.'">
		<table style="width:50%;">
		<tr><td colspan="3"><b>Inventory Charges?</b> <input type="text" name="ACCTGCHARGE" placeholder="Amount"></td></tr></table></br>
		<table style="width:50%;">
		<tr><th>FINAL DECISION</th><th></th><th></th></tr>
		<tr><th>CLEARED</th><th>DENIED</th><th></th></tr>
		</tr><td><input type="radio" name="decision" value="1" '.(($result['ACCTGFD']==1)?'checked':'').'></td>
		<td><input type="radio" name="decision" value="2" '.(($result['ACCTGFD']==2)?'checked':'').'></td><td></td></tr>
		<tr><td></td><td></td><td><input type="submit" name="submit"></td></tr>
		</form></table></br>':'</br>').'';
		
		}
		//SALES
		if (allowedToOpen(7805,'1rtc')) {
		echo'<div><table style="width:50%">
		<tr><th>SALES - OFFICE</th><th>CLEARED</th><th>UNCLEARED</th><th>N/A</th></tr>';
		$cnt=35;
			if($stmtsales->rowCount()!=0){
		foreach($resultsales as $rowssales){
		echo'
		<form method="post" action="resignationprocess.php?w=insertSALES&TxnID='.$txnid.'">
		<tr><td>'.$rowssales['Description'].'</td><td>
		<input type="radio" name="countr_'.$cnt.'" value="1" '.(($rowssales['ClearedOrUncleared']==1)?'checked':'').'></td><td><input type="radio" name="countr_'.$cnt.'" value="2" '.(($rowssales['ClearedOrUncleared']==2)?'checked':'').'></td>
		<td>'.(($rowssales['NA']!=1)?'<input type="radio" name="countr_'.$cnt.'" value="3" '.(($rowssales['ClearedOrUncleared']==3)?'checked':'').'>':'').'</td>
		</tr>
		<input type="hidden" name="TxnSubId'.$cnt.'" value="'.$rowssales['TxnSubId'].'">';
		$cnt++;
		}
			}
		echo '
		<table style="width:50%;">
		<tr><th>ClearedBy:'.$result['EncBySALES'].' Date:'.$result['SALESDate'].' Other Charges:'.$result['SALESREMARKS'].' '.$result['SALESCHARGE'].'</th><th></th><th>'.((allowedToOpen(7812,'1rtc'))?'<input type="submit" value="Submit" OnClick="return confirm(\'Are you sure you want to submit?\');">':'').'</th></tr>
		</form>
		<tr><form method="post" action="resignationprocess.php?w=newrowSALES&TxnID='.$txnid.'"><td>'.((allowedToOpen(7812,'1rtc'))?'<input type="text" name="items" list="itemlist" placeholder="Items" ><input type="text" name="Description" placeholder="if Other, please specify" > <input type="submit" name="newrow" value="Add New Items"  OnClick="return confirm(\'Are you sure you want to Add a New Item?\');">':'').'</td><td></td><td></td></form></tr>
		</div></table></br>';
		
		echo ''.((allowedToOpen(7812,'1rtc'))?'<form method="post" action="resignationprocess.php?w=finaldecisionSALES&TxnID='.$txnid.'">
		<table style="width:50%;">
		<tr><td colspan="3"><b>Other Charges?</b><input type="text" name="SALESREMARKS" placeholder="Remarks"> <input type="text" name="SALESCHARGE" placeholder="Amount"></td></tr>
		</table></br>
		<table style="width:50%;">
		<tr><th>FINAL DECISION</th><th></th><th></th></tr>
		<tr><th>CLEARED</th><th>DENIED</th><th></th></tr>
		</tr><td><input type="radio" name="decision" value="1" '.(($result['SALESFD']==1)?'checked':'').'></td>
		<td><input type="radio" name="decision" value="2" '.(($result['SALESFD']==2)?'checked':'').'></td><td></td></tr>
		<tr><td></td><td></td><td><input type="submit" name="submit"></td></tr>
		<form></table></br>':'</br>').'';
		
		}
		//OPS
		if (allowedToOpen(7806,'1rtc')) {
		echo'<div><table style="width:50%">
		<tr><th>OPERATIONS</th><th>CLEARED</th><th>UNCLEARED</th><th>N/A</th></tr>';
		$cnt=35;
			if($stmtops->rowCount()!=0){
		foreach($resultops as $rowsops){
		echo'
		<form method="post" action="resignationprocess.php?w=insertOPS&TxnID='.$txnid.'">
		<tr><td>'.$rowsops['Description'].'</td><td>
		<input type="radio" name="countr_'.$cnt.'" value="1" '.(($rowsops['ClearedOrUncleared']==1)?'checked':'').'></td><td><input type="radio" name="countr_'.$cnt.'" value="2" '.(($rowsops['ClearedOrUncleared']==2)?'checked':'').'></td>
		<td>'.(($rowsops['NA']!=1)?'<input type="radio" name="countr_'.$cnt.'" value="3" '.(($rowsops['ClearedOrUncleared']==3)?'checked':'').'>':'').'</td>
		</tr>
		<input type="hidden" name="TxnSubId'.$cnt.'" value="'.$rowsops['TxnSubId'].'">';
		$cnt++;
		}
			}
		echo '
		<table style="width:50%;">
		<tr><th>ClearedBy:'.$result['EncByOPS'].' Date:'.$result['OPSDate'].' Other Charges:'.$result['OPSREMARKS'].' '.$result['OPSCHARGE'].'</th><th></th><th>'.((allowedToOpen(7813,'1rtc'))?'<input type="submit" value="Submit" OnClick="return confirm(\'Are you sure you want to submit?\');">':'').'</th></tr>
		</form>
		<tr><form method="post" action="resignationprocess.php?w=newrowOPS&TxnID='.$txnid.'"><td>'.((allowedToOpen(7813,'1rtc'))?'<input type="text" name="items" list="itemlist" placeholder="Items" ><input type="text" name="Description" placeholder="if Other, please specify" > <input type="submit" name="newrow" value="Add New Items"  OnClick="return confirm(\'Are you sure you want to Add a New Item?\');">':'').'</td><td></td><td></td></form></tr>
		</div></table></br>';
		
		echo ''.((allowedToOpen(7813,'1rtc'))?'<form method="post" action="resignationprocess.php?w=finaldecisionOPS&TxnID='.$txnid.'">
		<table style="width:50%;">
		<tr><td colspan="3">Other Charges?<input type="text" name="OPSREMARKS" placeholder="Remarks"> <input type="text" name="OPSCHARGE" placeholder="Amount"></td></tr></table></br>
		<table style="width:50%;">
		<tr><th>FINAL DECISION</th><th></th><th></th></tr>
		<tr><th>CLEARED</th><th>DENIED</th><th></th></tr>
		</tr><td><input type="radio" name="decision" value="1" '.(($result['OPSFD']==1)?'checked':'').'></td>
		<td><input type="radio" name="decision" value="2" '.(($result['OPSFD']==2)?'checked':'').'></td><td></td></tr>
		<tr><td></td><td></td><td><input type="submit" name="submit"></td></tr>
		<form></table></br>':'</br>').'';
		
		}
		//FINANCE
		if (allowedToOpen(7807,'1rtc')) {
		echo'<div><table style="width:50%">
		<tr><th>FINANCE</th><th>CLEARED</th><th>UNCLEARED</th><th>N/A</th></tr>';
		$cnt=35;
			if($stmtfinance->rowCount()!=0){
		foreach($resultfinance as $rowsfinance){
		echo'
		<form method="post" action="resignationprocess.php?w=insertFINANCE&TxnID='.$txnid.'">
		<tr><td>'.$rowsfinance['Description'].'</td><td>
		<input type="radio" name="countr_'.$cnt.'" value="1" '.(($rowsfinance['ClearedOrUncleared']==1)?'checked':'').'></td><td><input type="radio" name="countr_'.$cnt.'" value="2" '.(($rowsfinance['ClearedOrUncleared']==2)?'checked':'').'></td>
		<td>'.(($rowsfinance['NA']!=1)?'<input type="radio" name="countr_'.$cnt.'" value="3" '.(($rowsfinance['ClearedOrUncleared']==3)?'checked':'').'>':'').'</td>
		</tr>
		<input type="hidden" name="TxnSubId'.$cnt.'" value="'.$rowsfinance['TxnSubId'].'">';
		$cnt++;
		}
			}
		echo '
		<table style="width:50%;">
		<tr><th>ClearedBy:'.$result['EncByFINANCE'].' Date:'.$result['FINANCEDate'].' Other Charges:'.$result['FINANCEREMARKS'].' '.$result['FINANCECHARGE'].'</th><th></th><th>'.((allowedToOpen(7814,'1rtc'))?'<input type="submit" value="Submit" OnClick="return confirm(\'Are you sure you want to submit?\');">':'').'</th></tr>
		</form>
		<tr><form method="post" action="resignationprocess.php?w=newrowFINANCE&TxnID='.$txnid.'"><td>'.((allowedToOpen(7814,'1rtc'))?'<input type="text" name="items" list="itemlist" placeholder="Items" ><input type="text" name="Description" placeholder="if Other, please specify" > <input type="submit" name="newrow" value="Add New Items"  OnClick="return confirm(\'Are you sure you want to Add a New Item?\');">':'').'</td><td></td><td></td></form></tr>
		</div></table></br>';
		
		echo ''.((allowedToOpen(7814,'1rtc'))?'<form method="post" action="resignationprocess.php?w=finaldecisionFINANCE&TxnID='.$txnid.'">
		<table style="width:50%;">
		<tr><td colspan="3"><b>Other Charges?</b> <input type="text" name="FINANCEREMARKS" placeholder="Remarks"> <input type="text" name="FINANCECHARGE" placeholder="Amount"></td></tr>
		</table></br>
		
		<table style="width:50%;">
		<tr><th>FINAL DECISION</th><th></th><th></th></tr>
		<tr><th>CLEARED</th><th>DENIED</th><th></th></tr>
		</tr><td><input type="radio" name="decision" value="1" '.(($result['FINANCEFD']==1)?'checked':'').'></td>
		<td><input type="radio" name="decision" value="2" '.(($result['FINANCEFD']==2)?'checked':'').'></td><td></td></tr>
		<tr><td></td><td></td><td><input type="submit" name="submit"></td></tr>
		<form></table></br>':'</br>').'';
		
		}
		//SC
		if (allowedToOpen(7808,'1rtc')) {
		echo'<div><table style="width:50%">
		<tr><th>SUPPLY CHAIN - OFFICE</th><th>CLEARED</th><th>UNCLEARED</th><th>N/A</th></tr>';
		$cnt=35;
			if($stmtsc->rowCount()!=0){
		foreach($resultsc as $rowssc){
		echo'
		<form method="post" action="resignationprocess.php?w=insertSC&TxnID='.$txnid.'">
		<tr><td>'.$rowssc['Description'].'</td><td>
		<input type="radio" name="countr_'.$cnt.'" value="1" '.(($rowssc['ClearedOrUncleared']==1)?'checked':'').'></td><td><input type="radio" name="countr_'.$cnt.'" value="2" '.(($rowssc['ClearedOrUncleared']==2)?'checked':'').'></td>
		<td>'.(($rowssc['NA']!=1)?'<input type="radio" name="countr_'.$cnt.'" value="3" '.(($rowssc['ClearedOrUncleared']==3)?'checked':'').'>':'').'</td>
		</tr>
		<input type="hidden" name="TxnSubId'.$cnt.'" value="'.$rowssc['TxnSubId'].'">';
		$cnt++;
		}
			}
		echo '
		<table style="width:50%;">
		<tr><th>ClearedBy:'.$result['EncBySC'].' Date:'.$result['SCDate'].' Other Charges:'.$result['SCREMARKS'].' '.$result['SCCHARGE'].'</th><th></th><th>'.((allowedToOpen(7815,'1rtc'))?'<input type="submit" value="Submit" OnClick="return confirm(\'Are you sure you want to submit?\');">':'').'</th></tr>
		</form>
		<tr><form method="post" action="resignationprocess.php?w=newrowSC&TxnID='.$txnid.'"><td>'.((allowedToOpen(7815,'1rtc'))?'<input type="text" name="items" list="itemlist" placeholder="Items" ><input type="text" name="Description" placeholder="if Other, please specify" > <input type="submit" name="newrow" value="Add New Items"  OnClick="return confirm(\'Are you sure you want to Add a New Item?\');">':'').'</td><td></td><td></td></form></tr>
		</div></table></br>';
		
		echo ''.((allowedToOpen(7815,'1rtc'))?'<form method="post" action="resignationprocess.php?w=finaldecisionSC&TxnID='.$txnid.'">
		<table style="width:50%;">
		<tr><td colspan="3"><b>Other Charges?</b><input type="text" name="SCREMARKS" placeholder="Remarks"> <input type="text" name="SCCHARGE" placeholder="Amount"></td></tr>
		</table></br>
		<table style="width:50%;">
		<tr><th>FINAL DECISION</th><th></th><th></th></tr>
		<tr><th>CLEARED</th><th>DENIED</th><th></th></tr>
		</tr><td><input type="radio" name="decision" value="1" '.(($result['SCFD']==1)?'checked':'').'></td>
		<td><input type="radio" name="decision" value="2" '.(($result['SCFD']==2)?'checked':'').'></td><td></td></tr>
		<tr><td></td><td></td><td><input type="submit" name="submit"></td></tr>
		<form></table></br>':'</br>').'';
		
		}
		//MKTG
		if (allowedToOpen(7817,'1rtc')) {
		echo'<div><table style="width:50%">
		<tr><th>MARKETING</th><th>CLEARED</th><th>UNCLEARED</th><th>N/A</th></tr>';
		$cnt=35;
			if($stmtmktg->rowCount()!=0){
		foreach($resultmktg as $rowsmktg){
		echo'
		<form method="post" action="resignationprocess.php?w=insertMKTG&TxnID='.$txnid.'">
		<tr><td>'.$rowsmktg['Description'].'</td><td>
		<input type="radio" name="countr_'.$cnt.'" value="1" '.(($rowsmktg['ClearedOrUncleared']==1)?'checked':'').'></td><td><input type="radio" name="countr_'.$cnt.'" value="2" '.(($rowsmktg['ClearedOrUncleared']==2)?'checked':'').'></td>
		<td>'.(($rowsmktg['NA']!=1)?'<input type="radio" name="countr_'.$cnt.'" value="3" '.(($rowsmktg['ClearedOrUncleared']==3)?'checked':'').'>':'').'</td>
		</tr>
		<input type="hidden" name="TxnSubId'.$cnt.'" value="'.$rowsmktg['TxnSubId'].'">';
		$cnt++;
		}
			}
		echo '
		<table style="width:50%;">
		<tr><th>ClearedBy:'.$result['EncByMKTG'].' Date:'.$result['MKTGDate'].' Other Charges:'.$result['MKTGREMARKS'].' '.$result['MKTGCHARGE'].'</th><th></th><th>'.((allowedToOpen(7818,'1rtc'))?'<input type="submit" value="Submit" OnClick="return confirm(\'Are you sure you want to submit?\');">':'').'</th></tr>
		</form>
		<tr><form method="post" action="resignationprocess.php?w=newrowMKTG&TxnID='.$txnid.'"><td>'.((allowedToOpen(7818,'1rtc'))?'<input type="text" name="items" list="itemlist" placeholder="Items" ><input type="text" name="Description" placeholder="if Other, please specify" > <input type="submit" name="newrow" value="Add New Items"  OnClick="return confirm(\'Are you sure you want to Add a New Item?\');">':'').'</td><td></td><td></td></form></tr>
		</div></table></br>';
		
		echo ''.((allowedToOpen(7818,'1rtc'))?'<form method="post" action="resignationprocess.php?w=finaldecisionMKTG&TxnID='.$txnid.'">
		<table style="width:50%;">
		<tr><td colspan="3"><b>Other Charges?</b><input type="text" name="MKTGREMARKS" placeholder="Remarks"> <input type="text" name="MKTGCHARGE" placeholder="Amount"></td></tr>
		</table></br>
		<table style="width:50%;">
		<tr><th>FINAL DECISION</th><th></th><th></th></tr>
		<tr><th>CLEARED</th><th>DENIED</th><th></th></tr>
		</tr><td><input type="radio" name="decision" value="1" '.(($result['MKTGFD']==1)?'checked':'').'></td>
		<td><input type="radio" name="decision" value="2" '.(($result['MKTGFD']==2)?'checked':'').'></td><td></td></tr>
		<tr><td></td><td></td><td><input type="submit" name="submit"></td></tr>
		<form></table></br>':'</br>').'';
		
		}
		//HR and ACCOUNTING
		if (allowedToOpen(7802,'1rtc') OR allowedToOpen(7811,'1rtc')) {
		echo '<table style="width:50%;"><form method="post" action="resignationprocess.php?w=finalpay&TxnID='.$txnid.'">
		<caption>FINAL PAY COMPUTATION</caption>
		<tr><td>Last Salary Covered / Payroll No.:<input name="lastsalary" value="'.$result['lastsalaryvalue'].'" '.((allowedToOpen(7802,'1rtc'))?'type="text"':'type="hidden"').'></td><td>Php '.$result['lastsalary'].'</td></tr>
		<tr><td>13th Month Computation:<input  name="thirteenth" value="'.$result['thirteenthvalue'].'" '.((allowedToOpen(7802,'1rtc'))?'type="text"':'type="hidden"').'></td><td>Php '.$result['thirteenth'].'</td></tr>
		<tr><td>Unused Service Incentive Leave (if any):<input  name="usl" value="'.$result['uslvalue'].'" '.((allowedToOpen(7802,'1rtc'))?'type="text"':'type="hidden"').'></td><td>Php '.$result['usl'].'</td></tr>
		<tr><td>Tax Refund (if any):<input  name="tax" value="'.$result['taxvalue'].'" '.((allowedToOpen(7802,'1rtc'))?'type="text"':'type="hidden"').'></td><td>Php '.$result['tax'].'</td></tr>
		<tr><td>TOTAL (from HR):</td><td><b>Php '.$result['totalhr'].'</b></td></tr>
		<tr><td>DEDUCTIONS<input name="deductions" placeholder="amount" value="'.$result['deductionsvalue'].'" '.((allowedToOpen(7811,'1rtc'))?'type="text"':'type="hidden"').'> <input name="deductionremarks" " value="'.$result['deductionremarks'].'" placeholder="Details" '.((allowedToOpen(7811,'1rtc'))?'type="text"':'type="hidden"').'></td><td>Php - '.$result['deductions'].' '.str_repeat('&nbsp;',3).' Details: '.$result['deductionremarks'].'</td></tr>
		<tr><td>GRAND TOTAL</td><td><b>Php '.$result['total'].'</b></td></tr>
		<tr><td><input type="submit" name="submit"  OnClick="return confirm(\'Are you sure you want to submit\');"></td><td></td></tr>
		</form></table></br>';
		}
		
	if (allowedToOpen(7809,'1rtc')){
		if($result['ADMINFD']!=0 AND $result['HRFD']!=0 AND $result['ACCTGFD']!=0 AND $result['SALESFD']!=0 AND $result['OPSFD']!=0 AND $result['FINANCEFD']!=0 AND $result['SCFD']!=0 AND $result['MKTGFD']!=0){
		echo '<div><form method="post" action="resignationprocess.php?w=finaldecision&TxnID='.$txnid.'"><table style="width:50%;"><caption>FINAL DECISION</caption
		<tr><th>CLEARED</th><th>DENIED</th><th></th></tr>
		</tr><td><input type="radio" name="decision" value="1" '.(($result['FinalDecision']==1)?'checked':'').'></td>
		<td><input type="radio" name="decision" value="2" '.(($result['FinalDecision']==2)?'checked':'').'></td><td></td></tr>
		<tr><td></td><td></td><td><input type="submit" name="submit"></td></tr>
		</table></form></div>';}
	}
		if (allowedToOpen(7802,'1rtc')){
		if($result['FinalDecision']!=0){
		echo '<form method="post" action="resignationprocess.php?w=finished&TxnID='.$txnid.'"><input type="submit" name="submit" value="Post then Print" OnClick="return confirm(\'Are you sure you want to Post then Print?\');"></form>';}
		}
		
		
		
		
		
		
		
	break;
	
	
	
	case 'finaldecisionADMIN':
	$txnid=intval($_GET['TxnID']);
	$sql='UPDATE hr_2resignationprocess set ADMINFD=\''.$_POST['decision'].'\',EncByADMIN='.$_SESSION['(ak0)'].',ADMINTS=Now() '.(($_POST['ADMINCHARGE']!=null)?',ADMINREMARKS=\''.$_POST['ADMINREMARKS'].'\',ADMINCHARGE=\''.$_POST['ADMINCHARGE'].'\' ':'').' where TxnID='.$txnid.'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'finaldecisionHR':
	$txnid=intval($_GET['TxnID']);
	$sql='UPDATE hr_2resignationprocess set HRFD=\''.$_POST['decision'].'\',EncByHR='.$_SESSION['(ak0)'].',HRTS=Now() '.(($_POST['HRCHARGE']!=null)?',HRREMARKS=\''.$_POST['HRREMARKS'].'\',HRCHARGE=\''.$_POST['HRCHARGE'].'\' ':'').' where TxnID='.$txnid.'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'finaldecisionACCTG':
	$txnid=intval($_GET['TxnID']);
	$sql='UPDATE hr_2resignationprocess set ACCTGFD=\''.$_POST['decision'].'\',EncByACCTG='.$_SESSION['(ak0)'].',ACCTGTS=Now() '.(($_POST['ACCTGCHARGE']!=null)?',ACCTGCHARGE=\''.$_POST['ACCTGCHARGE'].'\' ':'').' where TxnID='.$txnid.'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'finaldecisionSALES':
	$txnid=intval($_GET['TxnID']);
	$sql='UPDATE hr_2resignationprocess set SALESFD=\''.$_POST['decision'].'\',EncBySALES='.$_SESSION['(ak0)'].',SALESTS=Now() '.(($_POST['SALESCHARGE']!=null)?',SALESREMARKS=\''.$_POST['SALESREMARKS'].'\',SALESCHARGE=\''.$_POST['SALESCHARGE'].'\' ':'').' where TxnID='.$txnid.'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'finaldecisionOPS':
	$txnid=intval($_GET['TxnID']);
	$sql='UPDATE hr_2resignationprocess set OPSFD=\''.$_POST['decision'].'\',EncByOPS='.$_SESSION['(ak0)'].',OPSTS=Now() '.(($_POST['OPSCHARGE']!=null)?',OPSREMARKS=\''.$_POST['OPSREMARKS'].'\',OPSCHARGE=\''.$_POST['OPSCHARGE'].'\' ':'').' where TxnID='.$txnid.'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'finaldecisionFINANCE':
	$txnid=intval($_GET['TxnID']);
	$sql='UPDATE hr_2resignationprocess set FINANCEFD=\''.$_POST['decision'].'\',EncByFINANCE='.$_SESSION['(ak0)'].',FINANCETS=Now() '.(($_POST['FINANCECHARGE']!=null)?',FINANCEREMARKS=\''.$_POST['FINANCEREMARKS'].'\',FINANCECHARGE=\''.$_POST['FINANCECHARGE'].'\' ':'').' where TxnID='.$txnid.'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'finaldecisionSC':
	$txnid=intval($_GET['TxnID']);
	$sql='UPDATE hr_2resignationprocess set SCFD=\''.$_POST['decision'].'\',EncBySC='.$_SESSION['(ak0)'].',SCTS=Now() '.(($_POST['SCCHARGE']!=null)?',SCREMARKS=\''.$_POST['SCREMARKS'].'\',SCCHARGE=\''.$_POST['SCCHARGE'].'\' ':'').' where TxnID='.$txnid.'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'finaldecisionMKTG':
	$txnid=intval($_GET['TxnID']);
	$sql='UPDATE hr_2resignationprocess set MKTGFD=\''.$_POST['decision'].'\',EncByMKTG='.$_SESSION['(ak0)'].',MKTGTS=Now() '.(($_POST['MKTGCHARGE']!=null)?',MKTGREMARKS=\''.$_POST['MKTGREMARKS'].'\',MKTGCHARGE=\''.$_POST['MKTGCHARGE'].'\' ':'').' where TxnID='.$txnid.'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'finaldecision':
	$txnid=intval($_GET['TxnID']);
	$sql='UPDATE hr_2resignationprocess set FinalDecision=\''.$_POST['decision'].'\',EncByFD='.$_SESSION['(ak0)'].',FDTS=Now() where TxnID='.$txnid.'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');

	
	break;
	
	case 'finalpay':
	$txnid=intval($_GET['TxnID']);
	$sql='UPDATE hr_2resignationprocess set lastsalary=\''.$_POST['lastsalary'].'\',thirteenth=\''.$_POST['thirteenth'].'\',usl=\''.$_POST['usl'].'\',tax=\''.$_POST['tax'].'\',deductions=\''.$_POST['deductions'].'\' ,deductionremarks=\''.$_POST['deductionremarks'].'\'where TxnID='.$txnid.'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');

	
	break;
	
	case 'newrowADMIN':
	$txnid=intval($_GET['TxnID']);
	
	if($_POST['Description']!=null AND $_POST['items']==null){
	$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['Description'].'\',deptid=50 ';
	$stmt=$link->prepare($sql); $stmt->execute();
	// echo $sql; exit();
	}elseif($_POST['Description']==null AND $_POST['items']!=null){
		$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['items'].'\',deptid=50 ';
		// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	}	
	
	
	$sqlciid='select CIID from hr_0clearanceitems order by  ciid desc limit 1';
	$stmt=$link->query($sqlciid); $resultciid=$stmt->fetch();
	
	$sqlinsert='INSERT INTO hr_2resignationprocesssub set TxnID='.$txnid.',CIID='.$resultciid['CIID'].'';
	$stmt=$link->prepare($sqlinsert); $stmt->execute();
	 // echo $sqlinsert; exit();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');

	break;
	
	case 'newrowHR':
	$txnid=intval($_GET['TxnID']);
	
	if($_POST['Description']!=null AND $_POST['items']==null){
	$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['Description'].'\',deptid=30 ';
	$stmt=$link->prepare($sql); $stmt->execute();
	// echo $sql; exit();
	}elseif($_POST['Description']==null AND $_POST['items']!=null){
		$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['items'].'\',deptid=30 ';
		// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	}	
	
	$sqlciid='select CIID from hr_0clearanceitems order by  ciid desc limit 1';
	$stmt=$link->query($sqlciid); $resultciid=$stmt->fetch();
	
	$sqlinsert='INSERT INTO hr_2resignationprocesssub set TxnID='.$txnid.',CIID='.$resultciid['CIID'].'';
	$stmt=$link->prepare($sqlinsert); $stmt->execute();
	 // echo $sqlinsert; exit();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');

	break;
	
	case 'newrowACCTG':
	$txnid=intval($_GET['TxnID']);
	
	if($_POST['Description']!=null AND $_POST['items']==null){
	$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['Description'].'\',deptid=20 ';
	$stmt=$link->prepare($sql); $stmt->execute();
	// echo $sql; exit();
	}elseif($_POST['Description']==null AND $_POST['items']!=null){
		$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['items'].'\',deptid=20 ';
		// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	}	
	
	$sqlciid='select CIID from hr_0clearanceitems order by  ciid desc limit 1';
	$stmt=$link->query($sqlciid); $resultciid=$stmt->fetch();
	
	$sqlinsert='INSERT INTO hr_2resignationprocesssub set TxnID='.$txnid.',CIID='.$resultciid['CIID'].'';
	$stmt=$link->prepare($sqlinsert); $stmt->execute();
	 // echo $sqlinsert; exit();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');

	break;
	
	case 'newrowSALES':
	$txnid=intval($_GET['TxnID']);
	
	if($_POST['Description']!=null AND $_POST['items']==null){
	$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['Description'].'\',deptid=11 ';
	$stmt=$link->prepare($sql); $stmt->execute();
	// echo $sql; exit();
	}elseif($_POST['Description']==null AND $_POST['items']!=null){
		$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['items'].'\',deptid=11 ';
		// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	}	
	
	$sqlciid='select CIID from hr_0clearanceitems order by  ciid desc limit 1';
	$stmt=$link->query($sqlciid); $resultciid=$stmt->fetch();
	
	$sqlinsert='INSERT INTO hr_2resignationprocesssub set TxnID='.$txnid.',CIID='.$resultciid['CIID'].'';
	$stmt=$link->prepare($sqlinsert); $stmt->execute();
	 
	 $sqldelete='DELETE FROM hr_2resignationprocesssub where CIID=36 and TxnID='.$txnid.'';
	$stmt=$link->prepare($sqldelete); $stmt->execute();
	 // echo $sqldelete; exit();
	 
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	
	break;
	
	case 'newrowOPS':
	$txnid=intval($_GET['TxnID']);
	
	if($_POST['Description']!=null AND $_POST['items']==null){
	$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['Description'].'\',deptid=70 ';
	$stmt=$link->prepare($sql); $stmt->execute();
	// echo $sql; exit();
	}elseif($_POST['Description']==null AND $_POST['items']!=null){
		$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['items'].'\',deptid=70 ';
		// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	}	
	
	$sqlciid='select CIID from hr_0clearanceitems order by  ciid desc limit 1';
	$stmt=$link->query($sqlciid); $resultciid=$stmt->fetch();
	
	$sqlinsert='INSERT INTO hr_2resignationprocesssub set TxnID='.$txnid.',CIID='.$resultciid['CIID'].'';
	$stmt=$link->prepare($sqlinsert); $stmt->execute();

	 $sqldelete='DELETE FROM hr_2resignationprocesssub where CIID=37 and TxnID='.$txnid.'';
	$stmt=$link->prepare($sqldelete); $stmt->execute();
	 // echo $sqldelete; exit();
	 
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');

	break;
	
	case 'newrowFINANCE':
	$txnid=intval($_GET['TxnID']);
	
	if($_POST['Description']!=null AND $_POST['items']==null){
	$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['Description'].'\',deptid=60 ';
	$stmt=$link->prepare($sql); $stmt->execute();
	// echo $sql; exit();
	}elseif($_POST['Description']==null AND $_POST['items']!=null){
		$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['items'].'\',deptid=60 ';
		// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	}	
	
	$sqlciid='select CIID from hr_0clearanceitems order by  ciid desc limit 1';
	$stmt=$link->query($sqlciid); $resultciid=$stmt->fetch();
	
	$sqlinsert='INSERT INTO hr_2resignationprocesssub set TxnID='.$txnid.',CIID='.$resultciid['CIID'].'';
	$stmt=$link->prepare($sqlinsert); $stmt->execute();
	
	$sqldelete='DELETE FROM hr_2resignationprocesssub where CIID=38 and TxnID='.$txnid.'';
	$stmt=$link->prepare($sqldelete); $stmt->execute();
	 // echo $sqldelete; exit();

	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');

	break;
	
	case 'newrowSC':
	$txnid=intval($_GET['TxnID']);
	
	if($_POST['Description']!=null AND $_POST['items']==null){
	$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['Description'].'\',deptid=1 ';
	$stmt=$link->prepare($sql); $stmt->execute();
	// echo $sql; exit();
	}elseif($_POST['Description']==null AND $_POST['items']!=null){
		$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['items'].'\',deptid=1 ';
		// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	}	
	
	$sqlciid='select CIID from hr_0clearanceitems order by  ciid desc limit 1';
	$stmt=$link->query($sqlciid); $resultciid=$stmt->fetch();
	
	$sqlinsert='INSERT INTO hr_2resignationprocesssub set TxnID='.$txnid.',CIID='.$resultciid['CIID'].'';
	$stmt=$link->prepare($sqlinsert); $stmt->execute();
	
	$sqldelete='DELETE FROM hr_2resignationprocesssub where CIID=39 and TxnID='.$txnid.'';
	$stmt=$link->prepare($sqldelete); $stmt->execute();
	 // echo $sqldelete; exit();
	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');

	break;
	
	case 'newrowMKTG':
	$txnid=intval($_GET['TxnID']);
	
	if($_POST['Description']!=null AND $_POST['items']==null){
	$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['Description'].'\',deptid=15 ';
	$stmt=$link->prepare($sql); $stmt->execute();
	// echo $sql; exit();
	}elseif($_POST['Description']==null AND $_POST['items']!=null){
		$sql='INSERT INTO hr_0clearanceitems set Description=\''.$_POST['items'].'\',deptid=15 ';
		// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	}	
	
	$sqlciid='select CIID from hr_0clearanceitems order by  ciid desc limit 1';
	$stmt=$link->query($sqlciid); $resultciid=$stmt->fetch();
	
	$sqlinsert='INSERT INTO hr_2resignationprocesssub set TxnID='.$txnid.',CIID='.$resultciid['CIID'].'';
	$stmt=$link->prepare($sqlinsert); $stmt->execute();

	header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');

	break;
	
	case 'insertADMIN':	
	$txnid=intval($_GET['TxnID']);
	$cnt=1;
	foreach($_POST as $post){
		if(!isset($_POST['countr_'.$cnt.''])){
			$_POST['countr_'.$cnt.'']=0;
		}
		if(!isset($_POST['TxnSubId'.$cnt.''])){
			$_POST['TxnSubId'.$cnt.'']=0;
		}
		if($_POST['countr_'.$cnt.'']==3){
			$sql='DELETE FROM hr_2resignationprocesssub where TxnSubId=\''.$_POST['TxnSubId'.$cnt.''].'\'';
			$stmt=$link->prepare($sql); $stmt->execute();
			// echo $sql; exit();
		}
		$sql='UPDATE hr_2resignationprocesssub rps join hr_2resignationprocess rp on rp.TxnID=rps.TxnID SET ClearedOrUncleared=\''.$_POST['countr_'.$cnt.''].'\' where  TxnSubId='.$_POST['TxnSubId'.$cnt.''].' and rps.TxnID='.$txnid.' and Posted=0 ';
		$stmt=$link->prepare($sql); $stmt->execute();
		$cnt++;
	}
	// exit();
		header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	
	case 'insertHR':	
	$txnid=intval($_GET['TxnID']);
	$cnt=16;
	foreach($_POST as $post){
		if(!isset($_POST['countr_'.$cnt.''])){
			$_POST['countr_'.$cnt.'']=0;
		}
		if(!isset($_POST['TxnSubId'.$cnt.''])){
			$_POST['TxnSubId'.$cnt.'']=0;
		}
		
		if($_POST['countr_'.$cnt.'']==3){
			$sql='DELETE FROM hr_2resignationprocesssub where TxnSubId=\''.$_POST['TxnSubId'.$cnt.''].'\'';
			$stmt=$link->prepare($sql); $stmt->execute();
			// echo $sql; exit();
		}
		$sql='UPDATE hr_2resignationprocesssub rps join hr_2resignationprocess rp on rp.TxnID=rps.TxnID SET ClearedOrUncleared=\''.$_POST['countr_'.$cnt.''].'\' where  TxnSubId='.$_POST['TxnSubId'.$cnt.''].' and rps.TxnID='.$txnid.' and Posted=0 ';
		// echo $sql;
		$stmt=$link->prepare($sql); $stmt->execute();
		$cnt++;
	}
	// exit();
		header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'insertACCTG':	
	$txnid=intval($_GET['TxnID']);
	$cnt=23;
	foreach($_POST as $post){
		if(!isset($_POST['countr_'.$cnt.''])){
			$_POST['countr_'.$cnt.'']=0;
		}
		if(!isset($_POST['TxnSubId'.$cnt.''])){
			$_POST['TxnSubId'.$cnt.'']=0;
		}
		
		if($_POST['countr_'.$cnt.'']==3){
			$sql='DELETE FROM hr_2resignationprocesssub where TxnSubId=\''.$_POST['TxnSubId'.$cnt.''].'\'';
			$stmt=$link->prepare($sql); $stmt->execute();
			
			// echo $sql; exit();
		}
		$sql='UPDATE hr_2resignationprocesssub rps join hr_2resignationprocess rp on rp.TxnID=rps.TxnID SET ClearedOrUncleared=\''.$_POST['countr_'.$cnt.''].'\' where  TxnSubId='.$_POST['TxnSubId'.$cnt.''].' and rps.TxnID='.$txnid.' and Posted=0 ';
		// echo $sql;
		$stmt=$link->prepare($sql); $stmt->execute();
		$cnt++;
	}
	// exit();
		header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'insertSALES':	
	$txnid=intval($_GET['TxnID']);
	$cnt=35;
	foreach($_POST as $post){
		if(!isset($_POST['countr_'.$cnt.''])){
			$_POST['countr_'.$cnt.'']=0;
		}
		if(!isset($_POST['TxnSubId'.$cnt.''])){
			$_POST['TxnSubId'.$cnt.'']=0;
		}
		
		if($_POST['countr_'.$cnt.'']==3){
			$sql='DELETE FROM hr_2resignationprocesssub where TxnSubId=\''.$_POST['TxnSubId'.$cnt.''].'\'';
			$stmt=$link->prepare($sql); $stmt->execute();
			
			// echo $sql; exit();
		}
		$sql='UPDATE hr_2resignationprocesssub rps join hr_2resignationprocess rp on rp.TxnID=rps.TxnID SET ClearedOrUncleared=\''.$_POST['countr_'.$cnt.''].'\' where  TxnSubId='.$_POST['TxnSubId'.$cnt.''].' and rps.TxnID='.$txnid.' and Posted=0 ';
		// echo $sql;
		$stmt=$link->prepare($sql); $stmt->execute();
		$cnt++;
	}
	// exit();
		header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'insertOPS':	
	$txnid=intval($_GET['TxnID']);
	$cnt=35;
	foreach($_POST as $post){
		if(!isset($_POST['countr_'.$cnt.''])){
			$_POST['countr_'.$cnt.'']=0;
		}
		if(!isset($_POST['TxnSubId'.$cnt.''])){
			$_POST['TxnSubId'.$cnt.'']=0;
		}
		
		if($_POST['countr_'.$cnt.'']==3){
			$sql='DELETE FROM hr_2resignationprocesssub where TxnSubId=\''.$_POST['TxnSubId'.$cnt.''].'\'';
			$stmt=$link->prepare($sql); $stmt->execute();
		
			// echo $sql; exit();
		}
		$sql='UPDATE hr_2resignationprocesssub rps join hr_2resignationprocess rp on rp.TxnID=rps.TxnID SET ClearedOrUncleared=\''.$_POST['countr_'.$cnt.''].'\' where  TxnSubId='.$_POST['TxnSubId'.$cnt.''].' and rps.TxnID='.$txnid.' and Posted=0 ';
		// echo $sql;
		$stmt=$link->prepare($sql); $stmt->execute();
		$cnt++;
	}
	// exit();
		header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'insertFINANCE':	
	$txnid=intval($_GET['TxnID']);
	$cnt=35;
	foreach($_POST as $post){
		if(!isset($_POST['countr_'.$cnt.''])){
			$_POST['countr_'.$cnt.'']=0;
		}
		if(!isset($_POST['TxnSubId'.$cnt.''])){
			$_POST['TxnSubId'.$cnt.'']=0;
		}
		if($_POST['countr_'.$cnt.'']==3){
			$sql='DELETE FROM hr_2resignationprocesssub where TxnSubId=\''.$_POST['TxnSubId'.$cnt.''].'\'';
			$stmt=$link->prepare($sql); $stmt->execute();

			// echo $sql; exit();
		}
		
		$sql='UPDATE hr_2resignationprocesssub rps join hr_2resignationprocess rp on rp.TxnID=rps.TxnID SET ClearedOrUncleared=\''.$_POST['countr_'.$cnt.''].'\' where  TxnSubId='.$_POST['TxnSubId'.$cnt.''].' and rps.TxnID='.$txnid.' and Posted=0 ';
		// echo $sql;
		$stmt=$link->prepare($sql); $stmt->execute();
		$cnt++;
	}
	// exit();
		header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'insertSC':	
	$txnid=intval($_GET['TxnID']);
	$cnt=35;
	foreach($_POST as $post){
		if(!isset($_POST['countr_'.$cnt.''])){
			$_POST['countr_'.$cnt.'']=0;
		}
		if(!isset($_POST['TxnSubId'.$cnt.''])){
			$_POST['TxnSubId'.$cnt.'']=0;
		}
		
		if($_POST['countr_'.$cnt.'']==3){
			$sql='DELETE FROM hr_2resignationprocesssub where TxnSubId=\''.$_POST['TxnSubId'.$cnt.''].'\'';
			$stmt=$link->prepare($sql); $stmt->execute();
			// echo $sql; exit();
		}
		$sql='UPDATE hr_2resignationprocesssub rps join hr_2resignationprocess rp on rp.TxnID=rps.TxnID SET ClearedOrUncleared=\''.$_POST['countr_'.$cnt.''].'\' where  TxnSubId='.$_POST['TxnSubId'.$cnt.''].' and rps.TxnID='.$txnid.' and Posted=0 ';
		// echo $sql;
		$stmt=$link->prepare($sql); $stmt->execute();
		$cnt++;
	}
	// exit();
		header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'insertMKTG':	
	$txnid=intval($_GET['TxnID']);
	$cnt=35;
	foreach($_POST as $post){
		if(!isset($_POST['countr_'.$cnt.''])){
			$_POST['countr_'.$cnt.'']=0;
		}
		if(!isset($_POST['TxnSubId'.$cnt.''])){
			$_POST['TxnSubId'.$cnt.'']=0;
		}
		
		if($_POST['countr_'.$cnt.'']==3){
			$sql='DELETE FROM hr_2resignationprocesssub where TxnSubId=\''.$_POST['TxnSubId'.$cnt.''].'\'';
			$stmt=$link->prepare($sql); $stmt->execute();
			// echo $sql; exit();
		}
		$sql='UPDATE hr_2resignationprocesssub rps join hr_2resignationprocess rp on rp.TxnID=rps.TxnID SET ClearedOrUncleared=\''.$_POST['countr_'.$cnt.''].'\' where  TxnSubId='.$_POST['TxnSubId'.$cnt.''].' and rps.TxnID='.$txnid.' and Posted=0 ';
		// echo $sql;
		$stmt=$link->prepare($sql); $stmt->execute();
		$cnt++;
	}
	// exit();
		header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'finished':
	$txnid=intval($_GET['TxnID']);
		$sql='UPDATE hr_2resignationprocess SET PostedByNo='.$_SESSION['(ak0)'].',PostedTS=Now(),Posted=1 where txnid='.$txnid.' and Posted=0 ';
          // echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: resignationprocess.php?w=print&TxnID='.$txnid.'');
	break;
	
	case 'unpost':
	$txnid=intval($_GET['TxnID']);
		$sql='UPDATE hr_2resignationprocess SET PostedByNo='.$_SESSION['(ak0)'].',PostedTS=Now(),Posted=0 where txnid='.$txnid.' ';
          // echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: resignationprocess.php?w=lookup&TxnID='.$txnid.'');
	break;
	
	case 'withdraw':
	$txnid=intval($_GET['TxnID']);
		$sql='UPDATE hr_2resignationprocess SET EncByFD='.$_SESSION['(ak0)'].',FDTS=Now(),FinalDecision=3 where txnid='.$txnid.' and FinalDecision=0 ';
          // echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: resignationprocess.php?w=Process');
	break;
	
	case 'print':
	
	?>
	<title>Clearance</title>
	<style>
	table {
  border-collapse: collapse;
  font-family:Arial;
   font-size:8pt;
}

th, td {
  text-align: left;
  padding: 1px;
 
}

 th, td {
  border: 1px solid black;
}
	</style>
	<?php
	
	

	
	echo '<table style=" border-collapse: collapse;
  width:70%;
  margin-left:15%;
  margin-right:15%;
  border:1px solid black;"><caption><font size="4"><b>SEPARATION CLEARANCE FORM</b></font></caption>
	<tr><td>ID NO. / EMPLOYEE NAME</td><td>POSITION</td><td>DATE HIRED</td></tr>
		<tr><th>'.$result['IDNo'].' / '.$result['EmployeeName'].'</th><th>'.$result['Position'].'</th><th>'.$result['DateHired'].'</th></tr>
		<tr><td>COMPANY / DEPARTMENT / BRANCH</td><td>EMPLOYMENT STATUS</td><td>DATE SEPARATED</td></tr>
		<tr><th>'.$result['Company'].' / '.$result['department'].' / '.$result['Branch'].'</th><th>'.$result['EmploymentStatus'].'</th><th>'.$result['Date'].'</th></tr>
	</table>';
	
	echo '
		<div><table style="width:35%; float:right;
	margin-right:15%;
  border:1px solid black;">
		<tr><th>ADMIN</th><th>CLEARED</th><th>UNCLEARED</th></tr>';
		foreach($resultadmin as $rowsadmin){
		echo'
		<tr><td>'.$rowsadmin['Description'].'</td><td style="text-align:center;">
		'.(($rowsadmin['ClearedOrUncleared']==1)?'<b>&check;</b>':'').'</td>
		<td style="text-align:center;">'.(($rowsadmin['ClearedOrUncleared']==2)?'<b>&check;</b>':'').'</td></tr>';
		}
		echo'<tr><td colspan="3" style="padding-top:20px;">Cleared by: <b>'.$result['EncByADMIN'].'</b> '.str_repeat('&nbsp;',5).' Date: <b>'.$result['ADMINDate'].' OtherCharges: '.$result['ADMINREMARKS'].' <b>'.$result['ADMINCHARGE'].'</b></td></tr></table></div>';
	
	echo'<div><table style="width:35%;
	margin-left:15%;
  border:1px solid black;">
		<tr><th>HUMAN RESOURCES</th><th>CLEARED</th><th>UNCLEARED</th></tr>';
		foreach($resulthr as $rowshr){
		echo'
		<tr><td>'.$rowshr['Description'].'</td><td style="text-align:center;">
		'.(($rowshr['ClearedOrUncleared']==1)?'<b>&check;</b>':'').'</td><td style="text-align:center;">'.(($rowshr['ClearedOrUncleared']==2)?'<b>&check;</b>':'').'</td></tr>';

		}
		echo'<tr><td colspan="3" style="padding-top:20px;">Cleared by: <b>'.$result['EncByHR'].'</b> '.str_repeat('&nbsp;',5).' Date: <b>'.$result['HRDate'].' OtherCharges: '.$result['HRREMARKS'].' <b>'.$result['HRCHARGE'].'</b></td></tr></table></div>';
		
		echo'<div><table style="width:35%; margin-left:15%;
  border:1px solid black;">
		<tr><th>ACCOUNTING</th><th>CLEARED</th><th>UNCLEARED</th></tr>';
		foreach($resultacctg as $rowsacctg){
		echo'
		<tr><td>'.$rowsacctg['Description'].'</td><td style="text-align:center;">
		'.(($rowsacctg['ClearedOrUncleared']==1)?'<b>&check;</b>':'').'</td><td style="text-align:center;">'.(($rowsacctg['ClearedOrUncleared']==2)?'<b>&check;</b>':'').'</td></tr>';
		}
		echo'<tr><td colspan="3" style="padding-top:20px;">Cleared by: <b>'.$result['EncByACCTG'].'</b> '.str_repeat('&nbsp;',5).' Date: <b>'.$result['ACCTGDate'].'</b> InvtyCharge: <b>'.$result['ACCTGCHARGE'].'</b></td></tr></table>';
		
		
		echo'<div><table style="width:35%; margin-left:15%;
  border:1px solid black;">
		<tr><th>SALES - OFFICE</th><th>CLEARED</th><th>UNCLEARED</th></tr>';
		foreach($resultsales as $rowssales){
		echo'
		<tr><td>'.$rowssales['Description'].'</td><td style="text-align:center;">
		'.(($result['SALESFD']==1)?'<b>&check;</b>':'').'</td><td style="text-align:center;">'.(($result['SALESFD']==2)?'<b>&check;</b>':'').'</td></tr>';
		}
		echo'<tr><td colspan="3" style="padding-top:20px;">Cleared by: <b>'.$result['EncBySALES'].'</b> '.str_repeat('&nbsp;',5).' Date: <b>'.$result['SALESDate'].' OtherCharges: '.$result['SALESREMARKS'].' <b>'.$result['SALESCHARGE'].'</b></td></tr></table>';
		
		echo'</div>';
		
		echo'<div><table style="width:35%; margin-left:15%;
  border:1px solid black;">
		<tr><th>OPERATIONS</th><th>CLEARED</th><th>UNCLEARED</th></tr>';
		foreach($resultops as $rowsops){
		echo'
		<tr><td>'.$rowsops['Description'].'</td><td style="text-align:center;">
		'.(($result['OPSFD']==1)?'<b>&check;</b>':'').'</td><td style="text-align:center;">'.(($result['OPSFD']==2)?'<b>&check;</b>':'').'</td></tr>';
		}
		echo'<tr><td colspan="3" style="padding-top:19px;">Cleared by: <b>'.$result['EncByOPS'].'</b> '.str_repeat('&nbsp;',5).' Date: '.$result['OPSDate'].' OtherCharges: '.$result['OPSREMARKS'].' <b>'.$result['OPSCHARGE'].'</b></td></tr></table>';
		
		echo'</div>';
		
		echo'<div><table style="width:35%; margin-left:15%;
  border:1px solid black;">
		<tr><th>FINANCE</th><th>CLEARED</th><th>UNCLEARED</th></tr>';
		foreach($resultfinance as $rowsfinance){
		echo'
		<tr><td>'.$rowsfinance['Description'].'</td><td style="text-align:center;">
		'.(($result['FINANCEFD']==1)?'<b>&check;</b>':'').'</td><td style="text-align:center;">'.(($result['FINANCEFD']==2)?'<b>&check;</b>':'').'</td></tr>';
		}
		echo'<tr><td colspan="3" style="padding-top:19px;">Cleared by: <b>'.$result['EncByFINANCE'].'</b> '.str_repeat('&nbsp;',5).' Date: <b>'.$result['FINANCEDate'].' OtherCharges: '.$result['FINANCEREMARKS'].' <b>'.$result['FINANCECHARGE'].'</b></td></tr></table>';
		
		echo'</div>';
		
		echo'<div><table style="width:35%;  margin-left:15%; 
		border:1px solid black;">
		<tr><th>SUPPLY CHAIN - OFFICE</th><th>CLEARED</th><th>UNCLEARED</th></tr>';
		foreach($resultsc as $rowssc){
		echo'
		<tr><td>'.$rowssc['Description'].'</td><td style="text-align:center;">
		'.(($result['SCFD']==1)?'<b>&check;</b>':'').'</td><td style="text-align:center;">'.(($result['SCFD']==2)?'<b>&check;</b>':'').'</td></tr>';
		}
		echo'<tr><td colspan="3" style="padding-top:19px;">Cleared by: <b>'.$result['EncBySC'].'</b> '.str_repeat('&nbsp;',5).' Date: <b>'.$result['SCDate'].' OtherCharges: '.$result['SCREMARKS'].' <b>'.$result['SCCHARGE'].'</b></td></tr></table>';
		
		echo'</div>';
		
		echo'<div><table style="width:35%;  margin-left:15%; 
		border:1px solid black;">
		<tr><th>MARKETING</th><th>CLEARED</th><th>UNCLEARED</th></tr>';
		foreach($resultmktg as $rowsmktg){
		echo'
		<tr><td>'.$rowsmktg['Description'].'</td><td style="text-align:center;">
		'.(($result['MKTGFD']==1)?'<b>&check;</b>':'').'</td><td style="text-align:center;">'.(($result['MKTGFD']==2)?'<b>&check;</b>':'').'</td></tr>';
		}
		echo'<tr><td colspan="3" style="padding-top:19px;">Cleared by: <b>'.$result['EncByMKTG'].'</b> '.str_repeat('&nbsp;',5).' Date: <b>'.$result['MKTGDate'].' OtherCharges: '.$result['MKTGREMARKS'].' <b>'.$result['MKTGCHARGE'].'</b></td></tr></table>';
		
		echo'</div>';



		
		
$sqlp='select Concat(upper(FirstName),\' \',upper(SurName)) as FullName,upper(Position) as Position from attend_30currentpositions cp join 1employees e on e.IDNo=cp.IDNo where cp.IDNo=\''.$result['PostedByNo'].'\'';	
$stmtp=$link->query($sqlp); $resultp=$stmtp->fetch();	
		echo'
<div><table style="border-collapse: collapse;
  width:70%;
  margin-left:15%;
  margin-right:15%;
  border:1px solid black;">
<tr><td colspan="6"><b>FINAL PAY COMPUTATION:</b></td></tr>
<tr><td colspan="3" rowspan="2">Last Salary Covered / Payroll No.:</td><td colspan="3" rowspan="2">Php '.$result['lastsalary'].'</td></tr>
<tr></tr>
<tr><td colspan="3" rowspan="2">13th Month Computation:</td><td colspan="3" rowspan="2">Php '.$result['thirteenth'].'</td></tr>
<tr></tr>
<tr><td colspan="3" rowspan="2">Unused Service Incentive Leave (if any):</td><td colspan="2" rowspan="2">Php '.$result['usl'].'</td></tr>
<tr></tr>
<tr><td colspan="3" rowspan="2">Tax Refund (if any):</td><td colspan="3" rowspan="2">Php '.$result['tax'].'</td></tr>
<tr></tr>
<tr><td colspan="3" rowspan="2">TOTAL (from HR):</td><td colspan="3" rowspan="2"><b>Php '.$result['totalhr'].'</b></td></tr>
<tr></tr>
<tr><td colspan="3" rowspan="2"><b>DEDUCTIONS</b></td><td colspan="3" rowspan="2">Php - '.$result['deductions'].' Details: '.$result['deductionremarks'].'</td></tr>	
<tr></tr>
<tr><td colspan="3" rowspan="2"><b>GRAND TOTAL</b></td><td colspan="3" rowspan="2"><b>Php '.$result['total'].'</b></td></tr>
<tr></tr>
<tr><td colspan="2">Prepared by:</br></br><center><b>'.$resultp['FullName'].'</b></br>'.$resultp['Position'].'</center></td><td colspan="2">Reviewed by:</br></br><center><b>KRISTELLE LABTON</b></br>ACCOUNTING DEPARTMENT</center></td><td colspan="2">Approved by:</br></br><center><b>JENNIFER Y. EUSEBIO</b></br>EXECUTIVE VICE PRESIDENT</center></td></tr></table>
</br><center><font size="2">In consideration of my last pay stated above with '.$result['Company'].' granted pursuant to the terms of my engagement, receipt of which I hereby acknowledge to my complete and full satisfaction.  </br></br></br></br>
<b>'.$result['EmployeeName'].'</b></br> Signature over Printed Name / Date</font></center>';	
	
	break;
	
	
	
	
	
	
	
	
	
	
	
	

}


?>