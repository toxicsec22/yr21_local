<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	include_once('../backendphp/functions/editok.php');
	include_once "../generalinfo/lists.inc";
        if (!allowedToOpen(6411,'1rtc')) { echo 'No permission'; exit;} 

        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
	 
        
        $whichqry=$_GET['w'];

switch ($whichqry){
case 'NewCashCount':
	//to check if editable
	if(($_POST['DateCounted'])<$_SESSION['nb4']){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	 break; 
	}	
	$sqlinsert='INSERT INTO `audit_2countcash` SET  ';
        $sql='';
	
        $columnstoadd=array('DateCounted','Remarks','NoOfUsedReceipts','1000','500','200','100','50','20','10','5','1','025','010','005');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo=\''.$_SESSION['bnum'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', `TimeStamp`=Now()';
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	// $sql='Select CashCountID, `DateCounted` from `audit_2countcash` where BranchNo=\''.$_SESSION['bnum'].'\' and DateCounted=\''.$_POST['DateCounted'].'\' and EncodedByNo=\''.$_SESSION['(ak0)'].'\'';
	$sql='SELECT LAST_INSERT_ID() AS CashCountID;';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$txnid=$result['CashCountID'];
	header("Location:editcash.php?w=CashCount&CashCountID=".$txnid);
        break;

case 'CashCountMainEdit':
	include('../backendphp/functions/checkeditablemain.php');
	$txnid=$_REQUEST['CountID'];
	if (editOk('audit_2countcash',$txnid,$link,'countcash')){
        $columnstoedit=array('DateCounted','BranchNo','NoOfUsedReceipts','Remarks','1000','500','200','100','50','20','10','5','1','025','010','005');
    } else {
        $columnstoedit=array();
    }
	
	$sqlupdate='UPDATE `audit_2countcash` SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\' where CashCountID='.$txnid . ' and Posted=0 and `DateCounted`>'.$_SESSION['nb4']; 
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:editcash.php?w=CashCount&CashCountID=".$txnid);
	break;

case 'CashCountMainDel':
	$txnid=$_REQUEST['CashCountID'];
	if (editOk('audit_2countcash',$txnid,$link,'countcash')){
	$sql='Delete from `audit_2countcash` where CashCountID='.$txnid;
	$msg='';
	} else {
		$sql='Select * from `audit_2countcash` where CashCountID='.$txnid;
		$msg='?closeddata=true';
	}
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:lookupaudit.php?w=CashAuditPerMonth".$msg);
	break;

case 'CountCashSubAdd': 
	$txnid=$_REQUEST['CashCountID'];
	$pk='CashCountID';$table='audit_2countcash';$date='DateCounted';
	//to check if editable
	include('../backendphp/functions/checkeditablesub.php');
			
	$sqlinsert='INSERT INTO `audit_2countcashsub` SET CashCountID='.$txnid.', ';
        $sql='';
        $columnstoadd=array('InvandPRCollectNo', 'Amount');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:editcash.php?w=CashCount&CashCountID=".$txnid);
        break;

case 'CashCountSubEdit': 
	$txnid=$_REQUEST['CashCountID'];
	$txnsubid=$_REQUEST['CashCountSubID'];
	if (editOk('audit_2countcash',$txnid,$link,'countcash')){
        $columnstoedit=array('InvandPRCollectNo','Amount');
    } else {
        $columnstoedit=array();
    }
	
	$sqlupdate='UPDATE `audit_2countcashsub` as s join `audit_2countcash` as m on m.CashCountID=s.CashCountID SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' s.EncodedByNo=\''.$_SESSION['(ak0)'].'\', s.TimeStamp=Now() where CashCountSubID='.$txnsubid . ' and m.Posted=0 and m.`DateCounted`>\''.$_SESSION['nb4'].'\''; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:editcash.php?w=CashCount&CashCountID=".$txnid);
	break;

case 'CashCountSubDel':
	$txnid=$_REQUEST['CashCountID'];
	$txnsubid=$_REQUEST['CashCountSubID'];
	if (editOk('audit_2countcash',$txnid,$link,'countcash')){
	$sql='Delete from `audit_2countcashsub` where CashCountSubID='.$txnsubid;
	$msg='';
	} else {
		$sql='Select * from `audit_2countcash` where CashCountID='.$txnid;
		$msg='&closeddata=true';
	}
	
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:editcash.php?w=CashCount&CashCountID=".$txnid.$msg);
	break;


case 'NewToolsCount':
	//to check if editable
	if(($_POST['Date'])<$_SESSION['nb4']  or date('Y', strtotime($_POST['Date']))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	 break; 
	}
	
	$sql='SELECT Date as dateoflasttoolsaudit FROM audit_2toolscountmain m where BranchNo='.$_SESSION['bnum'].' order by Date desc limit 1;';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	if ($stmt->rowCount()>0){
	$dateoflast=$result['dateoflasttoolsaudit'];	
	} else {
		$dateoflast=date(''.$currentyr.'-1-1');
	}
	
	
	$sqlinsert='INSERT INTO `audit_2toolscountmain` SET  ';
        $sql='';
	
        $columnstoadd=array('Date','Remarks');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' `DateofLastCount`=\''. $dateoflast.'\', BranchNo=\''.$_SESSION['bnum'].'\',AuditedByNo=\''.$_SESSION['(ak0)'].'\',PostedByNo=\''.$_SESSION['(ak0)'].'\'';
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select CountID from `audit_2toolscountmain` where BranchNo=\''.$_SESSION['bnum'].'\' and Date=\''.$_POST['Date'].'\' and AuditedByNo=\''.$_SESSION['(ak0)'].'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$txnid=$result['CountID'];
	
	$sql='INSERT INTO audit_2toolscountsub ( CountID, ToolID, EncodedByNo, `TimeStamp`)
SELECT '.$txnid.' AS CountID, s.ToolID, \''.$_SESSION['(ak0)'].'\' AS `EncodedByNo`, Now() AS `TimeStamp` 
FROM audit_2toolscountmain m INNER JOIN audit_2toolscountsub s ON m.CountID = s.CountID 
WHERE (((m.BranchNo)=\''.$_SESSION['bnum'].'\') AND ((m.Date)=\''. $dateoflast.'\'))';
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:editcash.php?w=Tools&CountID=".$txnid);
        break;

case 'ToolsCountMainEdit':
	include('../backendphp/functions/checkeditablemain.php');
	$txnid=$_REQUEST['CountID'];
	if (editOk('audit_2toolscountmain',$txnid,$link,'countTools')){
        $columnstoedit=array('Date','BranchNo','Remarks');
    } else {
        $columnstoedit=array();
    }
	
	$sqlupdate='UPDATE `audit_2toolscountmain` SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' AuditedByNo=\''.$_SESSION['(ak0)'].'\' where CountID='.$txnid . ' and Posted=0 and `Date`>'.$_SESSION['nb4']; 
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:editcash.php?w=Tools&CountID=".$txnid);
	break;

case 'ToolsCountMainDel':
	$txnid=$_REQUEST['CountID'];
	if (editOk('audit_2toolscountmain',$txnid,$link,'countTools')){
	$sql='Delete from `audit_2toolscountmain` where CountID='.$txnid;
	$msg='';
	} else {
		$sql='Select * from `audit_2toolscountmain` where CountID='.$txnid;
		$msg='?closeddata=true';
	}
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:lookupaudit.php?w=Tools".$msg);
	break;

case 'CountToolsSubAdd': 
	$txnid=$_REQUEST['CountID'];
	$pk='CountID';$table='audit_2toolscountmain';$date='Date';
	//to check if editable
	include('../backendphp/functions/checkeditablesub.php');
			
	$sqlinsert='INSERT INTO `audit_2toolscountsub` SET CountID='.$txnid.', ';
        $sql='';
        $columnstoadd=array('ToolID','Count','Remarks');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:editcash.php?w=Tools&CountID=".$txnid);
        break;

case 'ToolsCountSubEdit': 
	$txnid=$_REQUEST['CountID'];
	$txnsubid=$_REQUEST['CountSubID'];
	if (editOk('audit_2toolscountmain',$txnid,$link,'countTools')){
        $columnstoedit=array('ToolID','Count','Remarks');
    } else {
        $columnstoedit=array();
    }
	
	$sqlupdate='UPDATE `audit_2toolscountsub` as s join `audit_2toolscountmain` as m on m.CountID=s.CountID SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' s.' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' s.EncodedByNo=\''.$_SESSION['(ak0)'].'\', s.TimeStamp=Now() where CountSubID='.$txnsubid . ' and m.Posted=0 and m.`Date`>\''.$_SESSION['nb4'].'\''; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:editcash.php?w=Tools&CountID=".$txnid);
	break;

case 'ToolsCountSubDel':
	$txnid=$_REQUEST['CountID'];
	$txnsubid=$_REQUEST['CountSubID'];
	if (editOk('audit_2toolscountmain',$txnid,$link,'countTools')){
	$sql='Delete from `audit_2toolscountsub` where CountSubID='.$txnsubid;
	$msg='';
	} else {
		$sql='Select * from `audit_2toolscountmain` where CountID='.$txnid;
		$msg='&closeddata=true';
	}
	
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:editcash.php?w=Tools&CountID=".$txnid.$msg);
	break;

        }

?>