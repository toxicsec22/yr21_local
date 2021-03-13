<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6300,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../switchboard/contents.php');
$which=(!isset($_GET['w'])?'lists':$_GET['w']);

echo'</br>';

switch ($which){
	
	case'lists':
	$title='Incorrect Payroll Reports';
	echo '<title>'.$title.'</title>';
	echo '<br>
	   <div style="background-color:#cccccc; width:63%; border: 1px solid black; padding:10px;" >
		<b>Approval Process:</b><br>
&nbsp; &nbsp; &nbsp; &nbsp; 1. Requester encodes all details, then submits.<br><font style="font-size:9pt;"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Possible cases: (kulang ng araw / kulang ng OT / over deduction of loan / over deduction of invty charge / sobra ng sweldo)</font><br>
&nbsp; &nbsp; &nbsp; &nbsp; 2. Dept Head shall approve/deny request.<br>&nbsp; &nbsp; &nbsp; &nbsp; 3. HR shall adjust/deny request.<br><br>&nbsp; &nbsp; &nbsp; &nbsp; * All denied requests must have explanations.<br></div>';

	echo '</br><h3>'.$title.'</h3></br>';
	if(isset($_GET['reset'])){
		echo '<h3><font color="green">Done</font></h3><br>';
	}
		
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	echo comboBox($link,'SELECT PayrollID, concat(PayrollID, " : ", FromDate, " - ", ToDate) as PayPeriod FROM payroll_1paydates','PayPeriod','PayrollID','payperiods');

	echo'<form method="post" action="payadjrequest.php?w=add" autocomplete=off>
				PayrollID: <input type="text" name="PayrollID" value="" list="payperiods" size="5">
				Details: <input type="text" name="Details" size=50 placeholder="">
				<input type="submit" name="submit" value="Request">
				</form>';
	


				if (allowedToOpen(array(100,63001),'1rtc')) {   
					if(isset($_POST['btnLookup'])){
						if($_POST['Status']==1){
							$status='Adjusted';
							$reqstat=1;
							$reqcondi=' DeptHeadStat=1 AND HRStat=1 ';
							$addlprocess='payadjrequest.php?w=Reset&TxnID='; $addlprocesslabel='Reset';
						} else if($_POST['Status']==2){
							$status='Denied By DeptHead';
							$reqstat=2;
							$reqcondi=' DeptHeadStat=2 AND HRStat=0 ';
						} else if($_POST['Status']==3){
							$status='Denied By HR';
							$reqstat=3;
							$reqcondi=' DeptHeadStat=1 AND HRStat=2 ';
						} else if($_POST['Status']==4){
							$status='Pending For HR';
							$reqstat=4;
							$reqcondi=' DeptHeadStat=1 AND HRStat=0 ';
						} else {
							$status='Pending';
							$reqstat=0;
							if (allowedToOpen(63001,'1rtc')){
								$reqcondi=' DeptHeadStat=1 AND HRStat=0 ';
							} else {
								$reqcondi=' DeptHeadStat=0 AND HRStat=0 ';
							}
						}
					} else {
						$status='Pending';
						$reqstat=0;
						if (allowedToOpen(63001,'1rtc')){
							$reqcondi=' DeptHeadStat=1 AND HRStat=0 ';
						} else {
							$reqcondi=' DeptHeadStat=0 AND HRStat=0 ';
						}
					}


					$formdesc='</i><br><form action="payadjrequest.php" method="POST"><input type="radio" name="Status" value="0"> Pending &nbsp; &nbsp; &nbsp;'.((allowedToOpen(100,'1rtc'))?'<input type="radio" name="Status" value="4"> Pending For HR &nbsp; &nbsp; &nbsp;':'').'  <input type="radio" name="Status" value="1"> Adjusted &nbsp; &nbsp; &nbsp; 
					'.((allowedToOpen(100,'1rtc'))?'<input type="radio" name="Status" value="2"> Denied By DeptHead &nbsp; &nbsp; &nbsp;':'').' <input type="radio" name="Status" value="3"> Denied By HR &nbsp; &nbsp; &nbsp; <input type="submit" name="btnLookup" value="Lookup"></form><br><h3>'.$status.'</h3><i>';
				   
					
					
				
				
				
					if($reqstat==0){
						if (allowedToOpen(100,'1rtc')) {
						$addlprocess='payadjrequest.php?w=Read1&TxnID='; $addlprocesslabel='Go';
						$columnstoedit=array('DeptHeadResponse');
						$editprocess='payadjrequest.php?w=deny1&TxnID='; $editprocesslabel='No Go';
						} else {
							$addlprocess='payadjrequest.php?w=Read2&TxnID='; $addlprocesslabel='Adjusted';
							$columnstoedit=array('HRResponse');
							$editprocess='payadjrequest.php?w=deny2&TxnID='; $editprocesslabel='No Go';
						}
						
					}
					$addlcondi='';
				}


$sqlmain='SELECT PAID AS TxnID,PayrollID,
    cp.FullName AS RequestedBy,par.`TimeStamp`,Position,IF(deptid IN (2,10),Branch,dept) AS `Branch/Dept`,Details,DeptHeadResponse,HRResponse,e.NickName AS ReadBy,e.NickName AS DeniedByDeptHead,e2.NickName AS AdjustedBy,e2.NickName AS DeniedByHR FROM attend_30currentpositions cp JOIN approvals_5payadjreq par ON cp.IDNo=par.EncodedByNo LEFT JOIN 1employees e ON par.DeptHeadNo=e.IDNo LEFT JOIN 1employees e2 ON par.HRNo=e2.IDNo ';

// echo $sqlmain;
	$txnid='TxnID'; $title='';
	$columnnames=array('PayrollID','Details','RequestedBy','Position','Branch/Dept');
	if (allowedToOpen(100,'1rtc')){
		array_push($columnnames,'DeptHeadResponse');
	} else {
		array_push($columnnames,'HRResponse');
	}
	$delprocess='payadjrequest.php?w=delete&TxnID=';

	if (allowedToOpen(array(100,63001),'1rtc')) {
			if($reqstat==1){
				array_push($columnnames,'AdjustedBy');
			}
			if($reqstat==2){
				array_push($columnnames,'DeniedByDeptHead');
			}
			if($reqstat==3){
				array_push($columnnames,'DeniedByHR');
			}
			if($reqstat==4){
				array_push($columnnames,'ReadBy');
			}
			if (allowedToOpen(63001,'1rtc')){
				$handlesql='';
			} else {
				$handlesql=' AND cp.deptheadpositionid='.$_SESSION['&pos'].'';
			}



			$sql=$sqlmain.' WHERE '.$reqcondi.' '.$addlcondi.' '.$handlesql.' ORDER BY PayrollID DESC';
			// echo $sql;
			if($reqstat==0){
			include_once('../backendphp/layout/displayastableeditcells.php');
			} else {
				include_once('../backendphp/layout/displayastable.php');
			}
	}
	
	
	
	
		
	break;
	
	
	
	case 'deny1':
	case 'Read1':
		if (allowedToOpen(100,'1rtc')) {
	$txnid=intval($_GET['TxnID']);

	
	$sql='update approvals_5payadjreq set '.(isset($_POST['DeptHeadResponse'])?'DeptHeadResponse="'.addslashes($_POST['DeptHeadResponse']).'",':'').' DeptHeadStat=IF("'.$which.'"="Read1",1,2),DeptHeadTS=NOW(),DeptHeadNo='.$_SESSION['(ak0)'].' where PAID=\''.$txnid.'\' and DeptHeadStat=0 AND HRStat=0';
	$stmt=$link->prepare($sql); $stmt->execute();
		}
	header("Location:payadjrequest.php?w=lists");
	break;
	

	case 'Reset':
		if (allowedToOpen(63001,'1rtc')) {
	$txnid=intval($_GET['TxnID']);

	
	$sql='update approvals_5payadjreq set HRResponse=NULL, HRStat=0,HRTS=NOW(),HRNo=NULL where PAID=\''.$txnid.'\' and DeptHeadStat=1 AND HRStat=1 AND DATE(HRTS)>=(CURDATE() - INTERVAL 3 DAY)';

		$stmt=$link->prepare($sql); $stmt->execute();
		
		}
	header("Location:payadjrequest.php?w=lists&reset=1");
	break;


	case 'deny2':
		case 'Read2':
			if (allowedToOpen(63001,'1rtc')) {
		$txnid=intval($_GET['TxnID']);
	
		
		$sql='update approvals_5payadjreq set '.(isset($_POST['HRResponse'])?'HRResponse="'.addslashes($_POST['HRResponse']).'",':'').' HRStat=IF("'.$which.'"="Read2",1,2),HRTS=NOW(),HRNo='.$_SESSION['(ak0)'].' where PAID=\''.$txnid.'\' and DeptHeadStat=1 AND HRStat=0';
		$stmt=$link->prepare($sql); $stmt->execute();
			}
		header("Location:payadjrequest.php?w=lists");
		break;
	
	
	
}

?>