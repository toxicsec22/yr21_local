<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6302,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../switchboard/contents.php');
$which=(!isset($_GET['w'])?'lists':$_GET['w']);
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';


echo comboBox($link,'select ActionID,ActionDesc from hr_0personnelaction ','ActionID','ActionDesc','actionlists');

$addcondlist='';
if (allowedToOpen(6304,'1rtc')) {  //OPS Man
	$addcondlist=' AND cp.deptid IN (70,10) AND cp.IDNo<>'.$_SESSION['(ak0)'].'';
} else if (allowedToOpen(6305,'1rtc')){ //SalesM
	$addcondlist=' AND cp.IDNo IN (SELECT TeamLeader FROM attend_1branchgroups WHERE SAM='.$_SESSION['(ak0)'].') AND cp.IDNo<>'.$_SESSION['(ak0)'].'';
} else if (allowedToOpen(array(6303,101),'1rtc')){ //SalesM
	$addcondlist='';
} else { //others or heads
	$addcondlist=' AND (cp.deptheadpositionid='.$_SESSION['&pos'].' OR cp.deptid=(SELECT deptid FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].')) AND cp.IDNo<>'.$_SESSION['(ak0)'].'';
}


echo comboBox($link,'Select IDNo,FullName FROM attend_30currentpositions cp WHERE IDNo>1002 '.$addcondlist.'','IDNo','FullName','employeelists');

include_once('../backendphp/layout/linkstyle.php');
echo'</br>';

switch ($which){
	

	case'lists':
	$title='Request for Personnel Action';
	echo '<title>'.$title.'</title>';

	echo '<br>
	<div style="background-color:#cccccc; width:63%; border: 1px solid black; padding:10px;" >
	 <b>Approval Process:</b><br>
&nbsp; &nbsp; &nbsp; &nbsp; 1. Requester encodes all details, then submits.<br>
&nbsp; &nbsp; &nbsp; &nbsp; 2. Dept Head shall approve/deny request. (No approval if the requester is dept head [Auto-Approved])<br> &nbsp; &nbsp; &nbsp; &nbsp; 3. RCE/JYE shall approve/deny request.<br>&nbsp; &nbsp; &nbsp; &nbsp; 4. HR will serve request.<br><br></div>';

	echo '</br><h3>'.$title.'</h3></br>';
	if(isset($_GET['reset'])){
		echo '<h3><font color="green">Done</font></h3><br>';
	}
	
    echo'<form method="post" action="requestforpa.php?w=add" autocomplete=off>
    Employee: <input type="text" name="Employee" size="15" placeholder="Employee" list="employeelists"> 
				Action: <input type="text" name="ActionID" size="10" placeholder="Action" list="actionlists">
				DateToBeServed: <input type="date" name="Date" value="'.date('Y-m-d').'">
				Reason/Details: <input type="text" name="Reason" size=50 placeholder="">
				<input type="submit" name="submit" value="Request">
				</form>';
	
    //Active
    if(isset($_POST['btnLookup']) OR isset($_REQUEST['Status'])){
        if($_REQUEST['Status']==1){
            $status='Approved By Dept Head';
            $reqstat=1 .' AND Served=0 AND ApprovedByEO=0';
        } else if($_REQUEST['Status']==2){
            $status='Denied By Dept Head';
            $reqstat=2;
        } else if($_REQUEST['Status']==3){
            $status='Served';
			$reqstat=1 .' AND Served=1';
			$addlprocess='requestforpa.php?w=Reset&TxnID='; $addlprocesslabel='Reset';
        } else if($_REQUEST['Status']==4){
            $status='Approved By ExecOfc';
			$reqstat=1 .' AND ApprovedByEO=1 AND Served=0';
        }  else if($_REQUEST['Status']==5){
            $status='Denied By ExecOfc';
			$reqstat=1 .' AND ApprovedByEO=2 AND Served=0';
		} else {
            $status='Pending';
            $reqstat=0;
        }
    } else {
        $status='Pending';
        $reqstat=0;
    }
	$formdesc='</i><br><form action="requestforpa.php" method="POST">
	<b>Filter by:</b> &nbsp; &nbsp;<input type="radio" name="Status" value="0"/> Pending &nbsp; &nbsp; &nbsp;  
	<input type="radio" name="Status" value="1"/> Approved By Dept Head &nbsp; &nbsp; &nbsp; 
	<input type="radio" name="Status" value="2"/> Denied By Dept Head &nbsp; &nbsp; &nbsp; 
	<input type="radio" name="Status" value="4"/> Approved By ExecOfc &nbsp; &nbsp; &nbsp; 
	<input type="radio" name="Status" value="5"/> Denied By ExecOfc &nbsp; &nbsp; &nbsp; 
	<input type="radio" name="Status" value="3"/> Served &nbsp; &nbsp; &nbsp; <input type="submit" name="btnLookup" value="Lookup"></form><br><b>'.$status.'</b><i>';
	
   

if (allowedToOpen(array(100,6303),'1rtc')) {
        if($reqstat==0 AND (allowedToOpen(100,'1rtc'))){
		//deny button
		$editprocess2='requestforpa.php?w=approved&TxnID='; $editprocesslabel2='Approve'; $editprocess2onclick='OnClick="return confirm(\'Are you sure you want to Approve?\');"';

		$addlprocess2='requestforpa.php?w=deny&TxnID='; $addlprocesslabel2='Deny';
	}
	if(isset($_REQUEST['Status']) AND $_REQUEST['Status']==1 AND (allowedToOpen(array(6303,101),'1rtc'))){
		//serve button
		if(allowedToOpen(6303,'1rtc')){
			$editprocess2='requestforpa.php?w=deletebyhr&TxnID='; $editprocesslabel2='Delete';
			$editprocess2onclick='OnClick="return confirm(\'Really Delete This?\');"';
		}
		if(allowedToOpen(101,'1rtc')){
			$editprocess='requestforpa.php?w=approvedeo&TxnID='; $editprocesslabel='Approve'; $editprocessonclick='OnClick="return confirm(\'Are you sure you want to Approve?\');"';

			$addlprocess2='requestforpa.php?w=denyeo&TxnID='; $addlprocesslabel2='Deny';
		}
	}
	if(isset($_REQUEST['Status']) AND $_REQUEST['Status']==4 AND (allowedToOpen(6303,'1rtc'))){
		//serve button

		$addlprocess2='requestforpa.php?w=serve&TxnID='; $addlprocesslabel2='Served?';
	}
	$addlcondi='';
} else {
	$addlcondi=' AND RequestedByNo='.$_SESSION['(ak0)'].'';
}


$sql='SELECT RPAID AS TxnID,DateToBeServed,ActionDesc,
    cp.FullName AS Employee,CONCAT(e2.Nickname," ",e2.Surname) AS RequestedBy,CONCAT(e4.Nickname," ",e4.SurName) AS ApprovedByExecOfc,RequestedTS,Position,IF(deptid IN (2,10),Branch,dept) AS `Branch/Dept`,Reason,CONCAT(e.Nickname," ",e.SurName) AS ApprovedByDHead,CONCAT(e3.Nickname," ",e3.SurName) AS ServedBy,CONCAT(e.Nickname," ",e.SurName) AS DeniedByDhead,CONCAT(e5.Nickname," ",e5.SurName) AS DeniedByExecOfc FROM attend_30currentpositions cp JOIN hr_2requestpa rpa ON cp.IDNo=rpa.IDNo LEFT JOIN 1employees e ON rpa.StatusByNo=e.IDNo LEFT JOIN 1employees e2 ON rpa.RequestedByNo=e2.IDNo LEFT JOIN 1employees e3 ON rpa.ServedByNo=e3.IDNo JOIN hr_0personnelaction pa ON rpa.ActionID=pa.ActionID LEFT JOIN 1employees e4 ON rpa.ApprovedByEONo=e4.IDNo LEFT JOIN 1employees e5 ON rpa.ApprovedByEONo=e5.IDNo WHERE ReqStatus='.$reqstat.' '.$addlcondi.'  '.$addcondlist.' ORDER BY DateToBeServed ASC';

// echo $sql;
	$txnidname='TxnID';
	$columnnames=array('DateToBeServed','Employee','ActionDesc','Branch/Dept','Position','RequestedBy','Reason');
	
	
	$title='';
	if($reqstat==0){
	$delprocess='requestforpa.php?w=delete&TxnID=';
	}
	if($status=='Approved By Dept Head'){
		array_push($columnnames,'ApprovedByDHead');
	}
	if($status=='Approved By ExecOfc'){
		array_push($columnnames,'ApprovedByDHead','ApprovedByExecOfc');
	}
	if($reqstat==2){
		array_push($columnnames,'DeniedByDHead');
	}
	if($status=='Denied By ExecOfc'){
		array_push($columnnames,'ApprovedByDHead','DeniedByExecOfc');
	}
	if($status=='Served'){
		array_push($columnnames,'ApprovedByDHead','ApprovedByExecOfc','ServedBy');
	}
	include('../backendphp/layout/displayastablenosort.php');
	
	
		
	break;
	
	
	case 'approved':
	case 'deny':
		if (allowedToOpen(100,'1rtc')) {
	$txnid=intval($_GET['TxnID']);

	$sql='update hr_2requestpa set ReqStatus=IF("'.$which.'"="approved",1,2),StatusTS=NOW(),StatusByNo='.$_SESSION['(ak0)'].' where RPAID=\''.$txnid.'\' and ReqStatus=0';
	$stmt=$link->prepare($sql); $stmt->execute();
		}
	header("Location:requestforpa.php?Status=0");
	break;

	case 'approvedeo':
	case 'denyeo':
		if (allowedToOpen(101,'1rtc')) {
		$txnid=intval($_GET['TxnID']);
		$sql='update hr_2requestpa set ApprovedByEO=IF("'.$which.'"="approvedeo",1,2),ApprovedByEOTS=NOW(),ApprovedByEONo='.$_SESSION['(ak0)'].' where RPAID=\''.$txnid.'\' and ReqStatus=1';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
	}
		header("Location:requestforpa.php?Status=1");
	break;

	
	case 'serve':
			if (allowedToOpen(6303,'1rtc')) {
		$txnid=intval($_GET['TxnID']);
	
		$sql='update hr_2requestpa set Served=1,ServedTS=NOW(),ServedByNo='.$_SESSION['(ak0)'].' where RPAID=\''.$txnid.'\' and ReqStatus=1';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
			}
		header("Location:requestforpa.php?Status=4");
		break;
	
	
	case 'delete':
	$txnid=intval($_GET['TxnID']);

	$sql='delete from hr_2requestpa where RPAID=\''.$txnid.'\' AND RequestedByNo='.$_SESSION['(ak0)'].' AND ReqStatus=0';
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:requestforpa.php?w=lists");
	break;

	case 'deletebyhr':
	if (allowedToOpen(6303,'1rtc')) {
		$txnid=intval($_GET['TxnID']);
	
		$sql='delete from hr_2requestpa where RPAID=\''.$txnid.'\' AND Served=0';
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:requestforpa.php?w=lists");
	}
		break;
	
	
	case 'Reset':
		if (allowedToOpen(6303,'1rtc')) {
	$txnid=intval($_GET['TxnID']);

	
	$sql='update hr_2requestpa set Served=0,ServedTS=NOW(),ServedByNo=NULL where RPAID=\''.$txnid.'\' and ReqStatus=1 AND Served=1 AND ApprovedByEO=1 AND DateToBeServed>=(CURDATE() - INTERVAL 3 DAY)';
// echo $sql;
		$stmt=$link->prepare($sql); $stmt->execute();
		
		}
	header("Location:requestforpa.php?w=lists&reset=1");
	break;
	
	case 'add':
    
	
    $idno=comboBoxValue($link, 'attend_30currentpositions', 'FullName', $_POST['Employee'], 'IDNo');
	$actionid=comboBoxValue($link, 'hr_0personnelaction', 'ActionDesc', $_POST['ActionID'], 'ActionID');
	
	$addlsql='';
	if (allowedToOpen(100,'1rtc')) {
		$addlsql=',ReqStatus=1,StatusTS=NOW(),StatusByNo='.$_SESSION['(ak0)'].'';
	}

        $sql='INSERT INTO hr_2requestpa set IDNo='.$idno.',ActionID='.$actionid.',RequestedByNo='.$_SESSION['(ak0)'].',DateToBeServed=\''.$_POST['Date'].'\',Reason=\''.$_POST['Reason'].'\',RequestedTS=Now()'.$addlsql.' ';
        
        // echo '<br>'.$sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		if (allowedToOpen(100,'1rtc')) { echo '<br>Request Added.'; exit(); }
		else {
			header("location:requestforpa.php?w=lists");
		}
	break;
	
}

?>