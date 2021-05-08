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
} else if (allowedToOpen(6303,'1rtc')){ //SalesM
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
&nbsp; &nbsp; &nbsp; &nbsp; 2. Dept Head shall approve/deny request. (No approval if the requester is dept head [Auto-Approved])<br>&nbsp; &nbsp; &nbsp; &nbsp; 3. HR will serve request.<br><br>&nbsp; &nbsp; &nbsp; &nbsp; * All denied requests must have explanations.<br></div>';

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
    if(isset($_POST['btnLookup'])){
        if($_POST['Status']==1){
            $status='Approved';
            $reqstat=1 .' AND Served=0';
        } else if($_POST['Status']==2){
            $status='Denied';
            $reqstat=2;
        } else if($_POST['Status']==3){
            $status='Served';
			$reqstat=1 .' AND Served=1';
			$addlprocess='requestforpa.php?w=Reset&TxnID='; $addlprocesslabel='Reset';
        } else {
            $status='Pending';
            $reqstat=0;
        }
    } else {
        $status='Pending';
        $reqstat=0;
    }
	$formdesc='</i><br><form action="requestforpa.php" method="POST">
	<input type="radio" name="Status" value="0"/> Pending &nbsp; &nbsp; &nbsp;  
	<input type="radio" name="Status" value="1"/> Approved &nbsp; &nbsp; &nbsp; 
	<input type="radio" name="Status" value="2"/> Denied &nbsp; &nbsp; &nbsp; 
	<input type="radio" name="Status" value="3"/> Served &nbsp; &nbsp; &nbsp; <input type="submit" name="btnLookup" value="Lookup"></form><br><b>'.$status.'</b><i>';
	
   
	
	



if (allowedToOpen(array(100,6303),'1rtc')) {
        if($reqstat==0 AND (allowedToOpen(100,'1rtc'))){
		//deny button
		$editprocess2='requestforpa.php?w=approved&TxnID='; $editprocesslabel2='Approve'; $editprocess2onclick='OnClick="return confirm(\'Are you sure you want to Approve?\');"';

		$addlprocess2='requestforpa.php?w=deny&TxnID='; $addlprocesslabel2='Deny';
	}
	if(isset($_POST['Status']) AND $_POST['Status']==1 AND (allowedToOpen(6303,'1rtc'))){
		//serve button
		$editprocess2='requestforpa.php?w=deletebyhr&TxnID='; $editprocesslabel2='Delete';
		$editprocess2onclick='OnClick="return confirm(\'Really Delete This?\');"';

		$addlprocess2='requestforpa.php?w=serve&TxnID='; $addlprocesslabel2='Served?';
	}
	$addlcondi='';
} else {
	$addlcondi=' AND RequestedByNo='.$_SESSION['(ak0)'].'';
}


$sql='SELECT RPAID AS TxnID,DateToBeServed,ActionDesc,
    cp.FullName AS Employee,CONCAT(e2.NickName," ",e2.Surname) AS RequestedBy ,RequestedTS,Position,IF(deptid IN (2,10),Branch,dept) AS `Branch/Dept`,Reason,e.NickName AS ApprovedBy,e3.NickName AS ServedBy,e.NickName AS DeniedBy FROM attend_30currentpositions cp JOIN hr_2requestpa rpa ON cp.IDNo=rpa.IDNo LEFT JOIN 1employees e ON rpa.StatusByNo=e.IDNo LEFT JOIN 1employees e2 ON rpa.RequestedByNo=e2.IDNo LEFT JOIN 1employees e3 ON rpa.ServedByNo=e3.IDNo JOIN hr_0personnelaction pa ON rpa.ActionID=pa.ActionID WHERE ReqStatus='.$reqstat.' '.$addlcondi.'  '.$addcondlist.' ORDER BY DateToBeServed ASC';

// echo $sql;
	$txnidname='TxnID';
	$columnnames=array('DateToBeServed','Employee','ActionDesc','Branch/Dept','Position','RequestedBy','Reason');
	
	
	$title='';
	if($reqstat==0){
	$delprocess='requestforpa.php?w=delete&TxnID=';
	}
	if($status=='Approved'){
		array_push($columnnames,'ApprovedBy');
	}
	if($reqstat==2){
		array_push($columnnames,'DeniedBy');
	}
	if($status=='Served'){
		array_push($columnnames,'ApprovedBy','ServedBy');
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
	header("Location:requestforpa.php?w=lists");
	break;
	
	case 'serve':
			if (allowedToOpen(6303,'1rtc')) {
		$txnid=intval($_GET['TxnID']);
	
		$sql='update hr_2requestpa set Served=1,ServedTS=NOW(),ServedByNo='.$_SESSION['(ak0)'].' where RPAID=\''.$txnid.'\' and ReqStatus=1';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
			}
		header("Location:requestforpa.php?w=lists");
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

	
	$sql='update hr_2requestpa set Served=0,ServedTS=NOW(),ServedByNo=NULL where RPAID=\''.$txnid.'\' and ReqStatus=1 AND Served=1 AND DateToBeServed>=(CURDATE() - INTERVAL 3 DAY)';

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