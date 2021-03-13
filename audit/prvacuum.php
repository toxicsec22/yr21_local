<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	include_once('../backendphp/functions/editok.php');
        if (!allowedToOpen(6531,'1rtc')) { echo 'No permission'; exit;}  
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

        
        $whichqry=$_GET['w'];

switch ($whichqry){
case 'AddVacuum':
	//to check if editable
	if(($_POST['Date'])<$_SESSION['nb4']  or date('Y', strtotime($_POST['Date']))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	 break; 
	}
	//$empid=$branchno=getValue($link,'1employees','Branch',$_POST['ForBranch'],'BranchNo');
	$sqlinsert='INSERT INTO `audit_3vacuum` SET  ';
        $sql='';
	
        $columnstoadd=array('Date','SerialNo','Vacuum','VacuumedBy');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' PostedByNo=\''.$_SESSION['(ak0)'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', `TimeStamp`=Now()';
	// echo $sql;break;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$txnid=$_POST['CountID'];
	header("Location:vacuum.php");
        break;

case 'VacuumEdit':
	$txnid=$_REQUEST['CountID'];
	if (editOk('audit_3vacuum',$txnid,$link,'vacuum')){
        $columnstoedit=array('Date','SerialNo','Vacuum','VacuumedBy','TotalSoldPerTank');
    } else {
        $columnstoedit=array();
    }
	
	$sqlupdate='UPDATE `audit_3vacuum` SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\' where CountID='.$txnid . ' and Posted=0 and `Date`>'.$_SESSION['nb4']; 
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:vacuum.php");
	break;

case 'VacuumDel':
	$txnid=$_REQUEST['CountID'];
	if (editOk('audit_3vacuum',$txnid,$link,'vacuum')){
	$sql='Delete from `audit_3vacuum` where CountID='.$txnid;
	$msg='';
	} else {
		$sql='Select * from `audit_3vacuum` where CountID='.$txnid;
		$msg='?closeddata=true';
	}
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:vacuum.php".$msg);
	break;
	
case 'TotalSold':
	$txnid=$_REQUEST['CountID'];
	$sql='SELECT ss.SerialNo, Sum(ss.Qty) AS TotalSoldPerTank
FROM `invty_1items` i INNER JOIN invty_2salesub ss ON i.ItemCode = ss.ItemCode
join `audit_3vacuum` v on v.SerialNo=ss.SerialNo
WHERE i.CatNo=90 and v.CountID='.$txnid.'
GROUP BY ss.SerialNo';
	
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$sql='UPDATE `audit_3vacuum` SET `TotalSoldPerTank`=\''.$result['TotalSoldPerTank'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\' where CountID='.$txnid . ' and Posted=0 and `Date`>'.$_SESSION['nb4']; 
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:vacuum.php");
	break;

case 'TransfertoCentral':
	include_once '../backendphp/functions/getnumber.php';
	$date=$_REQUEST['Date'];
	include ('unionserialnoswithlastyr.php');
	
	$stmt=$link->prepare($sql0);
	$stmt->execute();
	//echo $sql0.'<br><br>'; 
	$sql0='create temporary table vacuumperday(
Vacuum double not null,
SerialNo varchar(50) null
)
SELECT Vacuum, ss.ItemCode, ss.BranchNo FROM audit_3vacuum v
join serialnos ss on v.SerialNo=ss.SerialNo
 WHERE v.Date=\''.$date.'\' and v.Posted<>0 group by v.SerialNo;';
//echo $sql0; break;

	$stmt=$link->prepare($sql0);
	$stmt->execute();
	
	$sql='Create temporary table txfrvacuum (
		Vacuum double not null,
		ItemCode smallint(6) not null,
		BranchNo smallint(6) not null
	)
	SELECT Sum(Vacuum) as Vacuum, ItemCode, BranchNo FROM vacuumperday v group by ItemCode, BranchNo;';
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sqlbranch='Select BranchNo from txfrvacuum group by BranchNo';
	$stmt=$link->query($sqlbranch);
	$resultbranch=$stmt->fetchAll();
	
	foreach($resultbranch as $branch){
	$branchno=$branch['BranchNo'];
		//To get Vacuum Number
	$txnnoprefix='v-'.str_pad($branchno,2,'0',STR_PAD_LEFT).'-';
	$txnno=getAutoTxnNo($txnnoprefix,5,'TransferNo','invty_2transfer',$link);
	//insert main form
	$sqlinsert='INSERT INTO `invty_2transfer` SET `TransferNo`=\''.$txnno.'\', `DateOut`=\''.$date.'\', `DateIn`=\''.$date.'\', `ToBranchNo`=0, `ForRequestNo`=\'vacuum\', txntype=21, BranchNo='.$branchno.', FROMEncodedByNo=\''.$_SESSION['(ak0)'].'\',TOEncodedByNo=\''.$_SESSION['(ak0)'].'\', Posted=1, PostedByNo=\''.$_SESSION['(ak0)'].'\',FROMTimeStamp=Now(),TOTimeStamp=Now()';
	
	$stmt=$link->prepare($sqlinsert);
	$stmt->execute();
	
	//get txnid
	$sql='Select TxnID from `invty_2transfer` where BranchNo='.$branchno.' and TransferNo=\''.$txnno.'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$txnid=$result['TxnID'];
	
	$sqlvacuum='Select * from txfrvacuum where BranchNo='.$branchno;
	$stmt=$link->query($sqlvacuum);
	$resultvacuum=$stmt->fetchAll();
	
	foreach($resultvacuum as $vacuum){
	
	// get minprice
	$sql='Select MinPrice from invty_5latestminprice where ItemCode='.$vacuum['ItemCode'];
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$minprice=$result['MinPrice'];
			
	$sqlinsert='INSERT INTO `invty_2transfersub` SET `TxnID`='.$txnid.', `ItemCode`='.$vacuum['ItemCode'].', `QtySent`='.$vacuum['Vacuum'].', `QtyReceived`='.$vacuum['Vacuum'].', `UnitCost`='.$minprice.', `UnitPrice`='.$minprice.', FROMEncodedByNo=\''.$_SESSION['(ak0)'].'\',FROMTimeStamp=Now(), TOEncodedByNo=\''.$_SESSION['(ak0)'].'\',TOTimeStamp=Now()';
				
	$stmt=$link->prepare($sqlinsert);
	$stmt->execute();
			
		}
	}
	
	header("Location:../invty/txnsinterperday.php?w=Transfers&perday=1");
         $link=null; $stmt=null;
	break;	
	
        }
 $link=null; $stmt=null;
?>