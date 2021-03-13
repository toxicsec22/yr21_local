<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(8190,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../switchboard/contents.php');
$which=(!isset($_GET['w'])?'lists':$_GET['w']);
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

	$sqlb='select BranchNo from attend_30currentpositions where PositionID in (32,81)';
	$stmtb=$link->query($sqlb); $resultb=$stmtb->fetchAll();
		$branchno='';
	foreach($resultb as $resb){
		$branchno.=$resb['BranchNo'].',';
		
	}
	$branchno=substr($branchno, 0, -1);

echo comboBox($link,'SELECT IDNo, FullName FROM attend_30currentpositions where PositionID=37 and BranchNo not in ('.$branchno.') ORDER BY FullName','IDNo','FullName','employees'); 
echo comboBox($link,'SELECT BranchNo, Branch FROM 1branches where Active=1 ORDER BY Branch','BranchNo','Branch','branches');
include_once('../backendphp/layout/linkstyle.php');
echo'</br>
<a id="link" href="requestoicallowance.php">Request OIC Allowance</a>
<a id="link" href="requestoicallowance.php?w=OICCancellation">OIC Cancellation</a></br>';
switch ($which){
	case'OICCancellation':
	
		echo comboBox($link,'select roa.IDNo,FullName from payroll_2requestoicallowance roa left join attend_30currentpositions cp on cp.IDNo=roa.IDNo left join 1branches b on b.BranchNo=roa.BranchNo Where Valid=1 and curdate()<=Date_ADD(`Date`,Interval Duration month) Order By b.Branch','IDNo','FullName','activeoic');
		echo '<title>OIC Cancellation</title></br><h3>OIC Cancellation</h3>';
		echo'</br><form method="post" action="requestoicallowance.php?w=OICCancellation">
				Employee:<input type="text" name="Employee" size="10" placeholder="Employee" list="activeoic" required>
				<input type="submit" name="submit">
			</form>';
			
			
if(isset($_REQUEST['submit'])){
		$idno=comboBoxValue($link, 'attend_30currentpositions', 'FullName', $_REQUEST['Employee'], 'IDNo');
		echo'</i></br><b>'.$_REQUEST['Employee'].'</b><br></br>
		<form method="post" action="requestoicallowance.php?w=canceloic">
		<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
		<input type="hidden" name="Employee" value="'.$_REQUEST['Employee'].'">
			<input OnClick="return confirm(\'Are you sure you want to Cancel OIC Allowance?\');" type="submit" name="submit" value="Cancel OIC Allowance">
		</form>';	
			
	$sqlf='Select ExecStatus,FullName,`Date` AS DateOfEffectivity,Branch,roa.IDNo,Date,Amount,Duration,roa.Remarks,roa.BranchNo,Valid from payroll_2requestoicallowance roa left join attend_30currentpositions cp on cp.IDNo=roa.IDNo Where Valid=1 and curdate()<=Date_ADD(`Date`,Interval Duration month) and roa.IDNo='.$idno.' ';
		$stmtf=$link->query($sqlf); $resultf=$stmtf->fetch();

			$sqlt='create temporary table adjustment (`TxnID` INT(11) NOT NULL AUTO_INCREMENT,PRIMARY KEY (`TxnID`),PayrollID SMALLINT(6),Amount DOUBLE,AdjustTypeNo SMALLINT(6))';
			$stmtt=$link->prepare($sqlt); $stmtt->execute();
			
		$sqls='select PayrollID from payroll_1paydates where \''.$resultf['Date'].'\' between FromDate and ToDate';
		// echo $sqls; exit();
		$stmts=$link->query($sqls); $results=$stmts->fetch();
		
		$payrollid=$results['PayrollID'];
		$c=1;
		$counter=$resultf['Duration']*2;
		$amount=$resultf['Amount']/2;
		while($c<=$counter){
		
			$sqli='INSERT INTO adjustment set PayrollID=\''.$payrollid.'\',Amount=\''.$amount.'\',AdjustTypeNo=\'41\'';
			// echo $sqli; exit();
			$stmti=$link->prepare($sqli); $stmti->execute();
			
			$c++;
			$payrollid++;
			
			if($payrollid>24 and $payrollid==25){
				$payrollid=1;
			}
			
		}
		
		$title='';
		$formdesc='';
		
		$sql='select a.*,if(spda.PayrollID is null and '.$resultf['Valid'].'=1,"Cancelled",Amount) AS OICAllowance,pda.AdjustAmt AS ActualAmount from adjustment a left join payroll_0acctid ai on ai.AdjustTypeNo=a.AdjustTypeNo LEFT JOIN payroll_21paydayadjustments pda ON a.PayrollID=pda.PayrollID AND pda.AdjustTypeNo=41 AND pda.IDNo='.$resultf['IDNo'].'
		LEFT JOIN payroll_21scheduledpaydayadjustments spda ON a.PayrollID=spda.PayrollID AND spda.AdjustTypeNo=41 AND spda.IDNo='.$resultf['IDNo'].'';

		$columnnames=array('PayrollID','OICAllowance','ActualAmount');
		include('../backendphp/layout/displayastablenosort.php');
}
	
	break;
	
	case'canceloic':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$idno=comboBoxValue($link, 'attend_30currentpositions', 'FullName', $_REQUEST['Employee'], 'IDNo');
		$sql='delete from payroll_21scheduledpaydayadjustments where IDNo=\''.$idno.'\' and AdjustTypeNo=\'41\' and PayrollID not in (select PayrollID from payroll_21paydayadjustments where IDNo=\''.$idno.'\' and AdjustTypeNo=\'41\')';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:requestoicallowance.php?w=OICCancellation&submit=1&Employee='.$_REQUEST['Employee'].'');
	break;

	case'lists':
	
	echo '<title>Request OIC Allowance</title>';
	echo '</br><h3>Request OIC Allowance</h3></br>';
		
	if (allowedToOpen(8192,'1rtc')) {
	echo'<form method="post" action="requestoicallowance.php?w=add">
				Employee:<input type="text" name="Employee" size="10" placeholder="Employee" list="employees">
				Branch:<input type="text" name="Branch" size="10" placeholder="Branch" list="branches">
				AmountPerMonth:<input type="text" name="Amount" placeholder="Amount" size="10">
				DurationInMonths:<input type="text" name="Duration" placeholder="Duration" size="10">
				DateOfEffectivity: <input type="date" name="Date" value="'.date('Y-m-d').'">
				Remarks: <input type="text" name="Remarks">
				<input type="submit" name="submit">
				</form>';
	}
	
	$title='';
	$sql='select *, Date as DateOfEffectivity,Amount as AmountPerMonth,FullName,Duration as `DurationInMonths`,roa.Remarks,case when ExecStatus=0 then "Pending" when ExecStatus=1 then "Approved" when ExecStatus=2 then "Rejected" end as ExecStatus,case when OpsStatus=0 then "Pending" when OpsStatus=1 then "Approved" when OpsStatus=2 then "Rejected" end as OpsStatus,b.Branch,CASE WHEN Valid=\'0\' then "ToValidate" WHEN Valid=\'1\' then "Validated" WHEN Valid=\'2\' then "Invalid" end as Valid from payroll_2requestoicallowance roa left join attend_30currentpositions cp on cp.IDNo=roa.IDNo left join 1branches b on b.BranchNo=roa.BranchNo Where Valid<>2 and curdate()<=Date_ADD(`Date`,Interval Duration month) Order By b.Branch';
	// echo $sql; exit();
	$txnid='TxnID';
if (allowedToOpen(8192,'1rtc')) {
	//delete button
	$delprocess='requestoicallowance.php?w=delete&TxnID=';
	$addlprocess='requestoicallowance.php?w=edit&TxnID=';
	$addlprocesslabel='Edit';
}

if (allowedToOpen(8191,'1rtc')) {
	//approve button
	$editprocess='requestoicallowance.php?w=approve&TxnID='; $editprocesslabel='Approve'; $editprocessonclick='OnClick="return confirm(\'Are you sure you want to Approve?\');"';
	//reject button
	$editprocess2='requestoicallowance.php?w=reject&TxnID='; $editprocesslabel2='Reject'; $editprocess2onclick='OnClick="return confirm(\'Are you sure you want to Reject?\');"';
}
if (allowedToOpen(8194,'1rtc')) {
	//OPSapprove button
	$editprocess4='requestoicallowance.php?w=opsapprove&TxnID='; $editprocesslabel4='Approve'; $editprocess4onclick='OnClick="return confirm(\'Are you sure you want to Approve?\');"';
	//OPSreject button
	$editprocess5='requestoicallowance.php?w=opsreject&TxnID='; $editprocesslabel5='Reject'; $editprocess5onclick='OnClick="return confirm(\'Are you sure you want to Reject?\');"';
	
}

if (allowedToOpen(8193,'1rtc')) {
		//lookup button
	$editprocess3='requestoicallowance.php?w=lookup&TxnID='; $editprocesslabel3='Lookup';
}
	//Active
	$formdesc='</i><b>Active</b>';
	$columnnames=array('FullName','Branch','AmountPerMonth','DurationInMonths','DateOfEffectivity','Remarks','OpsStatus','ExecStatus','Valid');
	include('../backendphp/layout/displayastablenosort.php');
	
	//Rejected
	$title='';
	$formdesc='</br></i><b>Rejected</b>';
	$sql='select *, Date as DateOfEffectivity,Amount as AmountPerMonth,FullName,Duration as `DurationInMonths`,roa.Remarks,case when ExecStatus=0 then "Pending" when ExecStatus=1 then "Approved" when ExecStatus=2 then "Rejected" end as ExecStatus,case when OpsStatus=0 then "Pending" when OpsStatus=1 then "Approved" when OpsStatus=2 then "Rejected" end as OpsStatus,b.Branch,CASE WHEN Valid=\'0\' then "ToValidate" WHEN Valid=\'1\' then "Validated" WHEN Valid=\'2\' then "Invalid" end as Valid from payroll_2requestoicallowance roa left join attend_30currentpositions cp on cp.IDNo=roa.IDNo left join 1branches b on b.BranchNo=roa.BranchNo Where Valid=2 Order By b.Branch';
	include('../backendphp/layout/displayastablenosort.php');
	
	
	//Expired
	$title='';
	$formdesc='</br></i><b>Expired</b>';
	$sql='select *, Date as DateOfEffectivity,Amount as AmountPerMonth,FullName,Duration as `DurationInMonths`,roa.Remarks,case when ExecStatus=0 then "Pending" when ExecStatus=1 then "Approved" when ExecStatus=2 then "Rejected" end as ExecStatus,case when OpsStatus=0 then "Pending" when OpsStatus=1 then "Approved" when OpsStatus=2 then "Rejected" end as OpsStatus,b.Branch,CASE WHEN Valid=\'0\' then "ToValidate" WHEN Valid=\'1\' then "Validated" WHEN Valid=\'2\' then "Invalid" end as Valid from payroll_2requestoicallowance roa left join attend_30currentpositions cp on cp.IDNo=roa.IDNo left join 1branches b on b.BranchNo=roa.BranchNo Where curdate()>Date_ADD(`Date`,Interval Duration month) Order By b.Branch';
	include('../backendphp/layout/displayastablenosort.php');
		
	break;
	
	case'lookup':
			
		$txnid=intval($_GET['TxnID']);
		$sqlf='Select ExecStatus,FullName,`Date` AS DateOfEffectivity,Branch,roa.IDNo,Date,Amount,Duration,roa.Remarks,roa.BranchNo,Valid from payroll_2requestoicallowance roa left join attend_30currentpositions cp on cp.IDNo=roa.IDNo where TxnID=\''.$txnid.'\' ';
		$stmtf=$link->query($sqlf); $resultf=$stmtf->fetch();
		if($resultf['ExecStatus']==1){
			$sqlt='create temporary table adjustment (`TxnID` INT(11) NOT NULL AUTO_INCREMENT,PRIMARY KEY (`TxnID`),PayrollID SMALLINT(6),Amount DOUBLE,AdjustTypeNo SMALLINT(6),Date date)';
			$stmtt=$link->prepare($sqlt); $stmtt->execute();
			
		$sqls='select PayrollID from payroll_1paydates where \''.$resultf['Date'].'\' between FromDate and ToDate';
		// echo $sqls; exit();
		$stmts=$link->query($sqls); $results=$stmts->fetch();
		
		$payrollid=$results['PayrollID'];
		$c=1;
		$counter=$resultf['Duration']*2;
		$amount=$resultf['Amount']/2;
		$payrollidchecker=$results['PayrollID'];
		while($c<=$counter){
		//added payrollidchecker	
			if($payrollidchecker>24){
				$payrollidcheckervalue=1;
			}else{
				$payrollidcheckervalue=0;
			}
				if($payrollidcheckervalue==1){
					$datevalue=date('Y-m-d', strtotime('+1 year'));
				}else{
					$datevalue=date('Y-m-d');
				}
		//
		
			$sqli='INSERT INTO adjustment set PayrollID=\''.$payrollid.'\',Amount=\''.$amount.'\',AdjustTypeNo=\'41\',Date=\''.$datevalue.'\'';
			// echo $sqli; exit();
			$stmti=$link->prepare($sqli); $stmti->execute();
			
			$c++;
			$payrollid++;
			$payrollidchecker++;
			
			if($payrollid>24 and $payrollid==25){
				$payrollid=1;
			}
			
		}
		// echo $payrollid;
		// exit();
		
		$title='OIC Allowance';
		$formdesc='</i><br><b>'.$resultf['FullName'].' ('.$resultf['Branch'].') </b><br>';
		$formdesc.='<br>Date Of Effectivity: '.$resultf['DateOfEffectivity'].', Duration: '.$resultf['Duration'].' Months';
		if($resultf['Valid']==0){
		$formdesc.='<br><form style="display:inline;" method="post" action="requestoicallowance.php?w=lookup&TxnID='.$txnid.'&message=Successfully Validated"><input type="submit" name="validate" value="Validate"  OnClick="return confirm(\'Are you sure you want to validate?\');"></form>'.(isset($_GET['message'])?$_GET['message']:'').'';
		}
		
		
		$sql='select a.*,a.Date,if(spda.PayrollID is null and '.$resultf['Valid'].'=1,"Cancelled",Amount) AS OICAllowance,if(year(a.Date)!='.$nextyr.',pda.AdjustAmt,"year '.$nextyr.'") AS ActualAmount from adjustment a left join payroll_0acctid ai on ai.AdjustTypeNo=a.AdjustTypeNo LEFT JOIN payroll_21paydayadjustments pda ON a.PayrollID=pda.PayrollID AND pda.AdjustTypeNo=41 AND pda.IDNo='.$resultf['IDNo'].'
		'.($resultf['Valid']==0?'LEFT':'').' JOIN payroll_21scheduledpaydayadjustments spda ON a.PayrollID=spda.PayrollID AND spda.AdjustTypeNo=41 AND spda.IDNo='.$resultf['IDNo'].'';
		// echo $sql;
		if(isset($_POST['validate'])){
			$stmt=$link->query($sql); $result=$stmt->fetchAll();
			
			foreach($result as $res){
				if($res['Date']==$currentyr){
				$sqlit='INSERT INTO payroll_21scheduledpaydayadjustments set PayrollID=\''.$res['PayrollID'].'\',IDNo=\''.$resultf['IDNo'].'\',AdjustTypeNo=\''.$res['AdjustTypeNo'].'\',AdjustAmt=\''.$res['Amount'].'\',Remarks=\''.$resultf['Remarks'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\',BranchNo=\''.$resultf['BranchNo'].'\'';
				$stmtit=$link->prepare($sqlit); $stmtit->execute();
				}
				
			}
			
			$sqlv='UPDATE payroll_2requestoicallowance set valid=1 where TxnID=\''.$txnid.'\'';
			// echo $sqlv; exit();
				$stmtv=$link->prepare($sqlv); $stmtv->execute();
			
		}
		// $coltototal='Amount';
		// $showgrandtotal=false;
		
		// $columnnames=array('PayrollID','Amount','AdjustType');
		$columnnames=array('PayrollID','OICAllowance','ActualAmount');
		include('../backendphp/layout/displayastablenosort.php');
		}elseif($resultf['ExecStatus']==0){
			echo'</br>Pending';
		}else{
			echo '</br>Rejected';
		}
	
	break;
	
	case'editprocess':

	$sqlc='select * from attend_30currentpositions where FullName=\''.$_REQUEST['FullName'].'\' and PositionID=37 and BranchNo not in ('.$branchno.')';
	$stmtc=$link->query($sqlc);
	if($stmtc->rowCount()==0){
		echo 'Only OIC Employee without Branch Head or Junior Branch Head.'; exit();
	}
	
	$txnid=intval($_GET['TxnID']);
	$idno=comboBoxValue($link, 'attend_30currentpositions', 'FullName', $_REQUEST['FullName'], 'IDNo');
	$branchno=companyandbranchValue($link, '1branches', 'Branch', $_REQUEST['Branch'], 'BranchNo');
	$sql='update payroll_2requestoicallowance set  IDNo=\''.$idno.'\',BranchNo=\''.$branchno.'\',Amount=\''.$_POST['AmountPerMonth'].'\',Duration=\''.$_POST['DurationInMonths'].'\',Date=\''.$_POST['DateOfEffectivity'].'\',Remarks=\''.$_POST['Remarks'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where TxnID=\''.$txnid.'\' AND ExecStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:requestoicallowance.php?w=lists");
	break;
	
	case 'edit':
         $title='Edit';
	 $txnid=intval($_GET['TxnID']);
	 $sql='select *, Date as DateOfEffectivity,FullName,Amount as AmountPerMonth,Duration as `DurationInMonths`,roa.Remarks,b.Branch from payroll_2requestoicallowance roa left join attend_30currentpositions cp on cp.IDNo=roa.IDNo left join 1branches b on b.BranchNo=roa.BranchNo where roa.TxnID=\''.$txnid.'\' ';
	 // echo $sql; exit();
	 $columnnames=array('FullName','Branch','Amount','DurationInMonths','DateOfEffectivity','Remarks');
	 $columnswithlists=array('FullName','Branch');
	 $listsname=array('FullName'=>'employees','Branch'=>'branches');
     $columnstoedit=array('FullName','Branch','AmountPerMonth','DurationInMonths','DateOfEffectivity','Remarks');
     $editprocess='"requestoicallowance.php?w=editprocess&TxnID='.$txnid.'"'; 
     include('../backendphp/layout/editspecificsforlists.php');
    break;
	
	case'reject':
	$txnid=intval($_GET['TxnID']);

	$sql='update payroll_2requestoicallowance set ExecStatus=2,Valid=2 where TxnID=\''.$txnid.'\' and ExecStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:requestoicallowance.php?w=lists");
	break;
	
	case'approve':
	$txnid=intval($_GET['TxnID']);

	$sql='update payroll_2requestoicallowance set ExecStatus=1 where TxnID=\''.$txnid.'\' and ExecStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	
	header("Location:requestoicallowance.php?w=lists");
	break;
	
	case'opsreject':
	$txnid=intval($_GET['TxnID']);

	$sql='update payroll_2requestoicallowance set OpsStatus=2,Valid=2 where TxnID=\''.$txnid.'\' and OpsStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:requestoicallowance.php?w=lists");
	break;
	
	case'opsapprove':
	$txnid=intval($_GET['TxnID']);

	$sql='update payroll_2requestoicallowance set OpsStatus=1 where TxnID=\''.$txnid.'\' and OpsStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	
	header("Location:requestoicallowance.php?w=lists");
	break;
	
	case'delete':
	$txnid=intval($_GET['TxnID']);

	$sql='delete from payroll_2requestoicallowance where TxnID=\''.$txnid.'\' AND ExecStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:requestoicallowance.php?w=lists");
	break;
	
	
	break;
	
	case'add':
	$sqlc='select * from attend_30currentpositions where FullName=\''.$_REQUEST['Employee'].'\' and PositionID=37 and BranchNo not in ('.$branchno.')';
	// echo $sqlc; exit();
	$stmtc=$link->query($sqlc);
	if($stmtc->rowCount()==0){
		echo 'Only OIC Employee without Branch Head or Junior Branch Head.'; exit();
	}
	
	$idno=comboBoxValue($link, 'attend_30currentpositions', 'FullName', $_REQUEST['Employee'], 'IDNo');
	$branchno=companyandbranchValue($link, '1branches', 'Branch', $_REQUEST['Branch'], 'BranchNo');
	$amount=str_replace(',','',$_POST['Amount']);
		$sql='INSERT INTO payroll_2requestoicallowance set IDNo=\''.$idno.'\',BranchNo=\''.$branchno.'\',Amount=\''.$_POST['Amount'].'\',Duration=\''.$_POST['Duration'].'\',Date=\''.$_POST['Date'].'\',Remarks=\''.$_POST['Remarks'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() ';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("location:requestoicallowance.php?w=lists");
	break;
	
}