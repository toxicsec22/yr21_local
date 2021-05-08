<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(5360,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../switchboard/contents.php');
$which=(!isset($_GET['w'])?'lists':$_GET['w']);
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';


echo comboBox($link,'SELECT 0 AS FormTypeID,"COE" AS FormType UNION SELECT 1 AS FormTypeID,"Payslip" AS FormType','FormTypeID','FormType','formlists');
include_once('../backendphp/layout/linkstyle.php');
echo'</br>';

switch ($which){
	

	case'lists':
	$title='Request for Certificate of Employment / Printed Payslips';
	echo '<title>'.$title.'</title>';

	echo '<br>
	<div style="background-color:#cccccc; width:63%; border: 1px solid black; padding:10px;" >
	 <b>Approval Process:</b><br>
&nbsp; &nbsp; &nbsp; &nbsp; 1. Requester encodes all details, then submits.<br>&nbsp; &nbsp; &nbsp; &nbsp; 2. HR shall adjust/deny request.<br><br>&nbsp; &nbsp; &nbsp; &nbsp; * All denied requests must have explanations.<br>&nbsp; &nbsp; &nbsp; &nbsp; * For <b><font color="red">LOAN PURPOSES</font></b> only.<br></div>';


	echo '</br><h3>'.$title.'</h3></br>';
		
	
	echo'<form method="post" action="formrequest.php?w=add" autocomplete=off>
				Form:<input type="text" name="FormTypeID" size="10" placeholder="Form" list="formlists">
				DateNeeded: <input type="date" name="Date" value="'.date('Y-m-d').'">
				Reason/Details: <input type="text" name="Reason" size=50 placeholder="pls indicate month if payslip">
				<input type="submit" name="submit" value="Request">
				</form>';
	

				if (allowedToOpen(53601,'1rtc')) {   
    if(isset($_POST['btnLookup'])){
        if($_POST['Status']==1){
            $status='Delivered';
            $reqstat=1;
        } else if($_POST['Status']==2){
            $status='Denied';
            $reqstat=2;
        } else {
            $status='Pending';
            $reqstat=0;
        }
    } else {
        $status='Pending';
        $reqstat=0;
    }
	$formdesc='</i><br><form action="formrequest.php" method="POST"><input type="radio" name="Status" value="0"> Pending &nbsp; &nbsp; &nbsp;  <input type="radio" name="Status" value="1"> Delivered &nbsp; &nbsp; &nbsp; <input type="radio" name="Status" value="2"> Denied &nbsp; &nbsp; &nbsp; <input type="submit" name="btnLookup" value="Lookup"></form><br><b>'.$status.'</b><i>';
   
	
	



	if($reqstat==0){
		//delivered button
		$addlprocess='formrequest.php?w=delivered&TxnID='; $addlprocesslabel='Delivered'; 
		//deny button
		// $editprocess2='formrequest.php?w=deny&TxnID='; $editprocesslabel2='Deny';
		$columnstoedit=array('Response');
		$editprocess='formrequest.php?w=deny&TxnID='; $editprocesslabel='Deny?';
		
		
	}
	$addlcondi='';
} else {
	$addlcondi=' AND RequestedByNo='.$_SESSION['(ak0)'].'';
}


$sqlmain='SELECT ERFID AS TxnID,DateNeeded,
    (CASE 
    WHEN RequestTypeNo=0 THEN "COE"
    ELSE "Payslip"
    END) 
    AS Form,
	cp.FullName AS RequestedBy,Response,RequestedTS,Position,IF(deptid IN (2,10),Branch,dept) AS `Branch/Dept`,Reason,e.NickName AS DeliveredBy,e.NickName AS DeniedBy FROM attend_30currentpositions cp JOIN hr_2employeerequestform erf ON cp.IDNo=erf.RequestedByNo LEFT JOIN 1employees e ON erf.StatusByNo=e.IDNo ';
	


	$txnidname='TxnID';
	$columnnames=array('DateNeeded','RequestedBy','Form','Position','Branch/Dept','Reason','Response');
	
	
	$title='';
	// if($reqstat==0){
	$delprocess='formrequest.php?w=delete&TxnID=';
	// }
	if (allowedToOpen(53601,'1rtc')) { 
		if($reqstat==1){
			array_push($columnnames,'DeliveredBy');
		}
		if($reqstat==2){
			array_push($columnnames,'DeniedBy');
		}
	
		$sql=$sqlmain.' WHERE ReqStatus='.$reqstat.' '.$addlcondi.' ORDER BY DateNeeded';
		if($reqstat==0){
		include_once('../backendphp/layout/displayastableeditcells.php');
		} else {
			include_once('../backendphp/layout/displayastable.php');
		}
} else {
	$title='Pending';
	$sql=$sqlmain.' WHERE ReqStatus=0 '.$addlcondi.' ORDER BY DateNeeded';
	include('../backendphp/layout/displayastablenosort.php');
	echo '<br><br>';
	unset($delprocess);
	$title='Delivered';
	array_push($columnnames,'DeliveredBy');
	$sql=$sqlmain.' WHERE ReqStatus=1 '.$addlcondi.' ORDER BY DateNeeded';
	include('../backendphp/layout/displayastablenosort.php');
	echo '<br><br>';
	$columnnames=array_diff($columnnames,array('DeliveredBy'));
	array_push($columnnames,'DeniedBy');
	$title='Denied';
	$sql=$sqlmain.' WHERE ReqStatus=2 '.$addlcondi.' ORDER BY DateNeeded';
	include('../backendphp/layout/displayastablenosort.php');
}
	
		
	break;
	
	
	
	case 'deny':
	case 'delivered':
		if (allowedToOpen(53601,'1rtc')) {
	$txnid=intval($_GET['TxnID']);

	
	$sql='update hr_2employeerequestform set '.(isset($_POST['Response'])?'Response="'.addslashes($_POST['Response']).'",':'').' ReqStatus=IF("'.$which.'"="delivered",1,2),StatusTS=NOW(),StatusByNo='.$_SESSION['(ak0)'].' where ERFID=\''.$txnid.'\' and ReqStatus=0';
	$stmt=$link->prepare($sql); $stmt->execute();
		}
	header("Location:formrequest.php?w=lists");
	break;
	
	
	
	case 'delete':
	$txnid=intval($_GET['TxnID']);

	$sql='delete from hr_2employeerequestform where ERFID=\''.$txnid.'\' AND RequestedByNo='.$_SESSION['(ak0)'].' AND ReqStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:formrequest.php?w=lists");
	break;
	
	
	
	case 'add':
    
	if($_POST['FormTypeID']=='COE'){
        $formtypeid=0;
    } elseif($_POST['FormTypeID']=='Payslip'){
        $formtypeid=1;
    } else {
        exit();
    }
    // print_r($_POST); exit();
    
        $sql='INSERT INTO hr_2employeerequestform set RequestTypeNo='.$formtypeid.',RequestedByNo='.$_SESSION['(ak0)'].',DateNeeded=\''.$_POST['Date'].'\',Reason=\''.$_POST['Reason'].'\',RequestedTS=Now() ';
        
        // echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("location:formrequest.php?w=lists");
	break;
	
}

?>