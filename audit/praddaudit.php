<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	include_once('../backendphp/functions/editok.php');
	include_once "../generalinfo/lists.inc";
        if (!allowedToOpen(6401,'1rtc')) { echo 'No permission'; exit;} 
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
	    
        $whichqry=$_GET['w'];

switch ($whichqry){
case 'NewInvCount':
	//to check if editable
	if(($_POST['Date'])<$_SESSION['nb4']  or date('Y', strtotime($_POST['Date']))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed');  exit(); 
	}	
	$sqlinsert='INSERT INTO `audit_2countmain` SET  ';
        $sql='';
        $columnstoadd=array('Date','Remarks');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo=\''.$_SESSION['bnum'].'\',AuditedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\'';
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select CountID, `Date` from `audit_2countmain` where BranchNo=\''.$_SESSION['bnum'].'\' and Date=\''.$_POST['Date'].'\' and AuditedByNo=\''.$_SESSION['(ak0)'].'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$txnid=$result['CountID'];
	header("Location:editaudit.php?w=InvCount&CountID=".$txnid);
        break;
case 'CountInvSubAdd':
	$txnid=$_REQUEST['CountID'];
	$pk='CountID';$table='audit_2countmain';
	//to check if editable
	include('../backendphp/functions/checkeditablesub.php');
			
	$sqlinsert='INSERT INTO `audit_2countsub` SET CountID='.$txnid.', ComputerEndGood=0, ComputerEndDefective=0, ';
        $sql='';
        $columnstoadd=array('ItemCode', 'Count','Remarks');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:editaudit.php?w=InvCount&CountID=".$txnid);
        break;
case 'AddPerCat':
	$txnid=$_REQUEST['CountID'];
	$pk='CountID';$table='audit_2countmain';
	//to check if editable
	include('../backendphp/functions/checkeditablesub.php');
	$sqlcat='Select ItemCode from invty_1items where CatNo='.$_REQUEST['catno'];
	
	$stmt=$link->query($sqlcat);
	$resultcat=$stmt->fetchAll();
	foreach ($resultcat as $item){
	$sql='INSERT INTO `audit_2countsub` SET CountID='.$txnid.', `ItemCode`='.$item['ItemCode'].', ComputerEndGood=0, ComputerEndDefective=0, Count=0, EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
        $stmt=$link->prepare($sql);
	$stmt->execute();
	}
	header("Location:editaudit.php?w=InvCount&CountID=".$txnid);
	break;
case 'UpdateComputerEnd':
	$txnid=$_REQUEST['CountID'];
	$sql='Select c.Date, `BranchNo` from audit_2countmain c where CountID='.$txnid;
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$endinvasofdate=$result['Date'];
	$sql0='CREATE TEMPORARY TABLE endinvasofdate(
		ItemCode smallint(6) not null,
		ComputerEndGood double default 0,
		ComputerEndDefective double default 0
	)
	select `a`.`ItemCode`, SUM(CASE WHEN `a`.`Defective`=0 THEN Qty ELSE 0 END) as ComputerEndGood, SUM(CASE WHEN `a`.`Defective`<>0 THEN Qty ELSE 0 END) as ComputerEndDefective from
        `invty_20uniallposted` `a` where ((`a`.`Date` is not null)
            and (`a`.`Date` <=\''.$endinvasofdate .'\') and `a`.`BranchNo`='.$result['BranchNo'].') 
	    group by `a`.`ItemCode`';// $_SESSION['bnum']
	$stmt=$link->prepare($sql0);
	$stmt->execute();
	//$resultendinv=$stmt->fetchAll();
	$sql='UPDATE audit_2countsub s LEFT JOIN endinvasofdate ei ON s.ItemCode=ei.ItemCode
	SET s.ComputerEndGood=0, s.ComputerEndDefective=0 WHERE (ei.ItemCode IS NULL) AND s.CountID='.$txnid;
        $stmt2=$link->prepare($sql);	$stmt2->execute();
        
	$sql='Update audit_2countsub s join endinvasofdate ei on s.ItemCode=ei.ItemCode
	set s.ComputerEndGood=ei.ComputerEndGood, s.ComputerEndDefective=ei.ComputerEndDefective where s.CountID='.$txnid;	
	$stmt2=$link->prepare($sql);	$stmt2->execute();
        $sql0='DROP TEMPORARY TABLE IF EXISTS `endinvasofdate`;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
	header("Location:editaudit.php?w=InvCount&CountID=".$txnid);
	break;
case 'InvCountMainEdit':
	include('../backendphp/functions/checkeditablemain.php');
	$txnid=$_REQUEST['CountID'];
	if (editOk('audit_2countmain',$txnid,$link,'Audit')){
        $columnstoedit=array('Date','BranchNo','Remarks');
    } else {
        $columnstoedit=array();
    }
	
	$sqlupdate='UPDATE `audit_2countmain` SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' AuditedByNo=\''.$_SESSION['(ak0)'].'\' where CountID='.$txnid . ' and Posted=0 and `Date`>'.$_SESSION['nb4']; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:editaudit.php?w=InvCount&CountID=".$txnid);
	break;
case 'InvCountSubEdit':
	$txnid=$_REQUEST['CountID'];
	$txnsubid=$_REQUEST['CountSubID'];
	if (editOk('audit_2countmain',$txnid,$link,'Audit')){
        $columnstoedit=array('ItemCode','Count');
    } else {
        $columnstoedit=array();
    }
	
	$sqlupdate='UPDATE `audit_2countsub` as s join `audit_2countmain` as m on m.CountID=s.CountID SET s.Remarks=\''.$_POST['Remarks'].'\', ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' s.EncodedByNo=\''.$_SESSION['(ak0)'].'\', s.TimeStamp=Now() where CountSubID='.$txnsubid . ' and m.Posted=0 and m.`Date`>\''.$_SESSION['nb4'].'\''; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:editaudit.php?w=InvCount&CountID=".$txnid);
	break;
case 'InvCountMainDel':
	$txnid=$_REQUEST['CountID'];
	if (editOk('audit_2countmain',$txnid,$link,'Audit')){
	$sql='Delete from `audit_2countmain` where CountID='.$txnid;
	$msg='';
	} else {
		$sql='Select * from `audit_2countmain` where CountID='.$txnid;
		$msg='?closeddata=true';
	}
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:lookupaudit.php?w=InvAuditPerMonth".$msg);
	break;
case 'InvCountSubDel':
	$txnid=$_REQUEST['CountID'];
	$txnsubid=$_REQUEST['CountSubID'];
	if (editOk('audit_2countmain',$txnid,$link,'Audit')){
	$sql='Delete from `audit_2countsub` where CountSubID='.$txnsubid;
	$msg='';
	} else {
		$sql='Select * from `audit_2countmain` where CountID='.$txnid;
		$msg='&closeddata=true';
	}
	
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:editaudit.php?w=InvCount&CountID=".$txnid.$msg);
	break;
case 'InvCountSubScan':
	$txnid=$_REQUEST['CountID'];
	
	$sqlinsert='INSERT INTO `audit_2countsubbarcode` SET ';
	$sql=''; 
	$columnstoadd=array('ItemCode','Count');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql. ' `CountID`='.$txnid; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:editaudit.php?w=ScanItems&CountID=".$txnid);
	break;

case 'InvCountSubScanSend':
	$txnid=$_REQUEST['CountID'];
	
if (editOk('audit_2countmain',$txnid,$link,$whichqry)){
	$sql0='CREATE TEMPORARY TABLE scanneditems (
CountID	int(11)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
Count		double	NOT NULL)

select csb.CountID, csb.ItemCode, Sum(csb.Count) as Count  from audit_2countsubbarcode csb
join audit_2countmain cm on cm.CountID=csb.CountID
where cm.CountID='.$txnid.' group by csb.ItemCode';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
//ADDITIONAL QTY
$sql='Select s.ItemCode,s.Count+cs.Count as Count from scanneditems s join audit_2countsub cs on s.CountID=cs.CountID and s.ItemCode=cs.ItemCode';
$stmt=$link->query($sql);
$result=$stmt->fetchAll();
$resultcount=count($result);

for ($row = 0; $row <  $resultcount; $row++){

	$sqlupdate='UPDATE `audit_2countsub` SET ';
	$sql=''; 
	$sqlend=' EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	$columnstoadd=array('Count');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' ' . $field. '=\''.$result[$row][$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.$sqlend. ' where ItemCode='.$result[$row]['ItemCode'].' and CountID='.$txnid; 
	//echo $sql.' result'.$resultcount;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
}
//NEW ITEMS
$sql='Select s.* from scanneditems s left join audit_2countsub cs on s.CountID=cs.CountID and s.ItemCode=cs.ItemCode where cs.ItemCode is null';
$stmt=$link->query($sql);
$result=$stmt->fetchAll();
$resultcount=count($result);

for ($row = 0; $row <  $resultcount; $row++){
//foreach($result as $row){
	$sqlinsert='INSERT INTO `audit_2countsub` SET `CountID`='.$txnid.', ';
	$sql=''; 
	$sqlend=' EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	$columnstoadd=array('ItemCode','Count');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' ' . $field. '=\''.$result[$row][$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.$sqlend; 
	//echo $sql.' result'.$resultcount;
        $stmt=$link->prepare($sql);
	$stmt->execute();
}

   
} else {
        $columnstoadd=array();
    }
	$sql='Delete from `audit_2countsubbarcode` where `CountID`='.$txnid;
	$stmt=$link->prepare($sql);
	$stmt->execute();
        $sql0='DROP TEMPORARY TABLE IF EXISTS `scanneditems`;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
	header("Location:editaudit.php?w=InvCount&CountID=".$txnid);
	break;


case 'InvCountSubScanDel':
	$txnid=$_REQUEST['CountID'];
	$txnsubid=$_REQUEST['BarcodeID'];
	$sql='Delete from `audit_2countsubbarcode` where BarcodeID='.$txnsubid;
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:editaudit.php?w=ScanItems&CountID=".$txnid.$msg);
	break;
case 'Purge':
	$sql='delete cs.* FROM audit_2countmain cm INNER JOIN audit_2countsub cs ON cm.CountID = cs.CountID
WHERE (((cs.ComputerEndGood+cs.ComputerEndDefective)=0) AND ((cs.Count)=0) AND ((cm.Date)<=\''.$_SESSION['nb4'].'\') AND ((cs.Remarks) Is Null));';
        
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:/'.$url_folder.'/index.php?done=1");
        break;
case 'InvCountAutoAdj':
	include_once('../invty/invlayout/pricelevelcase.php');
	$txnid=$_REQUEST['CountID'];
	
	$sql='Select c.Date, BranchNo from audit_2countmain c where CountID='.$txnid;
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$endinvasofdate=$result['Date'];
        $bnum=$result['BranchNo'];
	if ($endinvasofdate<=$_SESSION['nb4']){
		header("Location:editaudit.php?w=InvCount&closeddata=true&CountID=".$txnid);
	} else {
	/*$sql0='CREATE TEMPORARY TABLE endinvasofdate(
		ItemCode smallint(6) not null,
		ComputerEnd double default 0
	)
	select `a`.`ItemCode`, sum(`a`.`Qty`) AS `EndInvToday` from
        `invty_20uniallposted` `a` where ((`a`.`Date` is not null)
            and (`a`.`Date` <=\''.$endinvasofdate .'\') and `a`.`BranchNo`='.$_SESSION['bnum'].') 
	    group by `a`.`ItemCode`';
	$stmt=$link->prepare($sql0);
	$stmt->execute(); */
	//$resultendinv=$stmt->fetchAll();
	//add adjust 
	$adjnoprefix=str_pad($bnum,2,'0',STR_PAD_LEFT).'-'.date('md').'-';
	
		$sql='SELECT AdjNo FROM invty_4adjust where Left(AdjNo,8)=\''.$adjnoprefix.'\' order by AdjNo desc Limit 1;';
	    $stmt=$link->query($sql);
	    $result=$stmt->fetch();
	    if ($stmt->rowCount()>1){
		header("Location:editaudit.php?w=InvCount&duplicate=true&CountID=".$txnid);
	    } else {
	    
	    $sqlalladj='CREATE TEMPORARY TABLE auditadjust(
		CountID int(11) NOT NULL,
		ItemCode smallint(6)  NOT NULL,
		Qty double  NOT NULL,
		Remarks varchar(50) NULL
	    )
	     SELECT m.CountID, \''.$adjnoprefix.'P\' as adjno, s.ItemCode, (s.Count-s.ComputerEndGood-s.ComputerEndDefective) as Qty, s.Remarks FROM audit_2countmain m join audit_2countsub s on m.CountID=s.CountID
 where m.Date=\''.$endinvasofdate .'\' and m.BranchNo='.$bnum.' and m.Posted<>0 and  ((s.Remarks like \'%palit%\' and (s.Count-s.ComputerEndGood-s.ComputerEndDefective)<>0) or (s.Remarks is null and (s.Count-s.ComputerEndGood-s.ComputerEndDefective)>0)) group by m.CountID,s.ItemCode  union all
	    SELECT m.CountID, \''.$adjnoprefix.'O\' as adjno, s.ItemCode, (s.Count-s.ComputerEndGood-s.ComputerEndDefective) as Qty,s.Remarks FROM audit_2countmain m join audit_2countsub s on m.CountID=s.CountID 
where m.Date=\''.$endinvasofdate .'\' and m.BranchNo='.$bnum.' and m.Posted<>0 and (s.Remarks like \'%over%\') and ((s.Count-s.ComputerEndGood-s.ComputerEndDefective)>0) group by m.CountID,s.ItemCode union all
		SELECT m.CountID, \''.$adjnoprefix.'C\' as adjno, s.ItemCode, (s.Count-s.ComputerEndGood-s.ComputerEndDefective) as Qty,s.Remarks FROM audit_2countmain m join audit_2countsub s on m.CountID=s.CountID 
where m.Date=\''.$endinvasofdate .'\' and m.BranchNo='.$bnum.' and m.Posted<>0 and ((s.Remarks not like \'%palit%\' and (s.Remarks not like \'%over%\') and ((s.Count-s.ComputerEndGood-s.ComputerEndDefective)<0)) or( ((s.Count-s.ComputerEndGood-s.ComputerEndDefective)<0) and s.Remarks is null) or (s.Remarks like \'%charge%\')) group by m.CountID,s.ItemCode';
	// echo $sqlalladj; break;
	$stmt=$link->prepare($sqlalladj);
	$stmt->execute();
	    }
	    
	$sqlm='Select adjno from auditadjust group by adjno';
		$stmt=$link->query($sqlm);
		$result=$stmt->fetchAll();
	
	
	if ($stmt->rowCount()>0){ // append to adjust main
		foreach($result as $row){
			$sql='INSERT INTO `invty_4adjust` (`Date`, `AdjNo`, `BranchNo`,`EncodedByNo`, `PostedByNo`, `TimeStamp`) VALUES (\''.$endinvasofdate.'\',\''.$row['adjno'].'\','.$bnum.','.$_SESSION['(ak0)'].','.$_SESSION['(ak0)'].',Now())';
		$stmt=$link->prepare($sql);
		$stmt->execute();	
		}
		//append to sub
		// if(b.ProvincialBranch=1,lmp.PriceLevel4,lmp.PriceLevel3) as PriceLevel3,
	$sqls='INSERT INTO `invty_4adjustsub` (`TxnID`,`ItemCode`,`Qty`,`UnitPrice`,`Remarks`,`TimeStamp`,`EncodedByNo`)
		Select am.TxnID, aa.ItemCode, aa.Qty, 
		'.$plcase.'
		as UnitPrice,
		aa.Remarks, Now() as TimeStamp, '.$_SESSION['(ak0)'].' as EncodedByNo from `invty_4adjust` am join auditadjust aa on am.AdjNo=aa.AdjNo
join `invty_5latestminprice` lmp on lmp.ItemCode=aa.ItemCode
join `1branches` b on b.BranchNo='.$bnum.'';
		
		$stmt=$link->prepare($sqls);
		$stmt->execute();
	
	}
	}
	$sql0='DROP TEMPORARY TABLE IF EXISTS `auditadjust`;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
	header("Location:editaudit.php?w=InvCount&done=1&CountID=".$txnid);
	break;
        }
 $link=null; $stmt=null;
?>