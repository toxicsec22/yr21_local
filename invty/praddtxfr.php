<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php'; 
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
        include_once('invlayout/pricelevelcase.php'); 
        
        // check if allowed
        $allowed=array(703,704,705,706,707,7611);
        $allow=0;
        foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
        if ($allow==0) { echo 'No permission'; exit;}
        allowed:
        // end of check
        
	include_once('../backendphp/functions/editok.php');
	include_once "../generalinfo/lists.inc";
        
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
	 
        
        $txntype=$_GET['txntype'];
	$whichqry=$_GET['w'];
switch ($whichqry){
case 'Out':
	if (!allowedToOpen(705,'1rtc')) {   echo 'No permission'; exit;}
$txntype=4;

	//to check if editable
	if(($_POST['DateOut'])<$_SESSION['nb4']){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	 break; 
	}
	// check if existing
	$sql='Select TransferNo from `invty_2transfer` where TransferNo=\''.addslashes($_POST['TransferNo']).'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	// check if branch is correct
	$sqlbranch='SELECT BranchNo FROM `1branches` WHERE Branch LIKE "'.$_POST['ToBranch'].'" AND Active=1 AND PseudoBranch<>1';
	$stmtbranch=$link->query($sqlbranch); $resbranch=$stmtbranch->fetch();
	if ($stmtbranch->rowCount()==0) { $msg='&NoSuchBranch=true'; header("Location:addtxfrmain.php?w=Out&txntype=Transfer".$msg); break;}
	// confirm RequestNo
	$sql1='select ud.RequestNo, ReqTxnID from invty_44undeliveredreq as ud where SendBal<>0 and RequestNo=\''. $_POST['ForRequestNo'] . '\' and SupplierBranchNo='.$_SESSION['bnum'] . ' group by ud.SupplierBranchNo, ud.RequestNo';
	//echo $sql1;
	$stmt1=$link->query($sql1);
	$result1=$stmt1->fetch();
	if ($stmt->rowCount()==0 and $stmt1->rowCount()>0 and $result1['ReqTxnID']<>0){
	$sqlinsert='INSERT INTO `invty_2transfer` SET ';
        $sql='';
        $columnstoadd=array('DateOut','TransferNo','ForRequestNo','Remarks','txntype');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' ReqTxnID=\''.$result1['ReqTxnID'].'\', ToBranchNo='.$resbranch['BranchNo'].', BranchNo=\''.$_SESSION['bnum'].'\', FROMEncodedByNo=\''.$_SESSION['(ak0)'].'\',TOEncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',FROMTimeStamp=Now(),TOTimeStamp=Now()';
        if($_SESSION['(ak0)']==1002){ echo $sql;}
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	} else {
		$msg=($stmt->rowCount()>0?'&duplicate=true':'&norequest=true');
		header("Location:addtxfrmain.php?w=Out&txntype=Transfer".$msg);
		break;
	
	}
	//echo $sql;
        	
	$sql='Select TxnID from `invty_2transfer` where BranchNo=\''.$_SESSION['bnum'].'\' and TransferNo=\''.$_POST['TransferNo'].'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	header("Location:addedittxfr.php?w=Transfers&txntype=".$txntype."&TxnID=".$result['TxnID']);
        break;
	
case 'In':
	if (!allowedToOpen(704,'1rtc')) {   echo 'No permission'; exit;}
	//to check if editable
	if(($_POST['DateIn'])<$_SESSION['nb4']){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	 break; 
	}
	$txntype=4;
	
	// check if existing
	$sql='Select TransferNo from `invty_2transfer` where TransferNo=\''.addslashes($_POST['TransferNo']).'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	// confirm RequestNo
	$sql1='select ud.RequestNo from invty_44undeliveredreq as ud where RcvBal<>0 and RequestNo=\''. $_POST['ForRequestNo'] . '\' and BranchNo='.$_SESSION['bnum'] . ' group by ud.BranchNo, ud.RequestNo';
		
	$stmt1=$link->query($sql1);
	$result=$stmt1->fetch();
	if ($stmt->rowCount()==0 and $stmt1->rowCount()>0){
	$sqlinsert='INSERT INTO `invty_2transfer` SET ';
        $sql='';
        $columnstoadd=array('DateOut','DateIN','TransferNo','BranchNo','ForRequestNo','Remarks','txntype');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' ToBranchNo=\''.$_SESSION['bnum'].'\',FROMEncodedByNo=\''.$_SESSION['(ak0)'].'\',TOEncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',FROMTimeStamp=Now(),TOTimeStamp=Now()';
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID from `invty_2transfer` where BranchNo=\''.$_SESSION['bnum'].'\' and TransferNo=\''.$_POST['TransferNo'].'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	
	} else {
		$msg=($stmt->rowCount()>0?'&duplicate=true':'&norequest=true');
		header("Location:addtxfrmain.php?w=In&txntype=Transfer".$msg);
		break;
	}
	//echo $sql;
        
	header("Location:addedittxfr.php?w=Transfers&txntype=".$txntype."&TxnID=".$result['TxnID']);
        break;

case 'AcceptIn':
	if (!allowedToOpen(704,'1rtc')) {
   echo 'No permission'; exit;
}
	$sql='Select TxnID, PostedIn, IFNULL(DateIn,0) AS DateIn from `invty_2transfer` where ToBranchNo=\''.$_SESSION['bnum'].'\' and TransferNo=\''.$_REQUEST['TransferNo'].'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	$txnid=$result['TxnID'];
        if ($result['PostedIn']<>0 OR $result['DateIn']<>0){ goto skipaccept;}
	
	$sql='Update `invty_2transfer` Set `DateIN`=curdate(), TOEncodedByNo=\''.$_SESSION['(ak0)'].'\', TOTimeStamp=Now() where (`DateIN` IS NULL) AND TxnID='.$txnid;
	// echo $sql; exit();
	$stmt=$link->prepare($sql);
	$stmt->execute();
	skipaccept:
	header("Location:addedittxfr.php?w=Transfers&txntype=".$txntype."&TxnID=".$txnid);
        break;
case 'TxfrMainEdit':
	$txnid=intval($_REQUEST['TxnID']);
	
	$sqlupdate='UPDATE `invty_2transfer` SET ';
	$sql=''; $sqlend='';
	if (editOk('invty_2transfer',$txnid,$link,$txntype) ){  //and $stmt1->rowCount()>0
            include_once 'trailinvty.php'; recordtrail($txnid,'invty_2transfer',$link,0);
        if ($txntype=='Out') {
		if((addslashes($_POST['DateOUT']))<$_SESSION['nb4'] or date('Y',  strtotime($_POST['DateOUT']))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); }
		// check if branch is correct
	$sqlbranch='SELECT BranchNo FROM `1branches` WHERE BranchNo='.$_POST['ToBranchNo'].' AND Active=1 AND PseudoBranch<>1';
	$stmtbranch=$link->query($sqlbranch);
	if ($stmtbranch->rowCount()==0) {
		$msg='&NoSuchBranch=true'; header("Location:addedittxfr.php?w=Transfers&txntype=".$_REQUEST['txntype']."&TxnID=".$txnid.$msg); break;}
		$columnstoedit=array('DateOUT','TransferNo','ToBranchNo','Remarks'); //'ForRequestNo',    ReqTxnID=\''.$result1['ReqTxnID'].'\', 
		if (allowedToOpen(7611,'1rtc')){$columnstoedit[]='Waybill';}
		$sqlend=' BranchNo=\''.$_SESSION['bnum'].'\',FROMEncodedByNo=\''.$_SESSION['(ak0)'].'\',FROMTimeStamp=Now()';
		} elseif ($txntype=='In') {
			if((($_POST['DateIN']))<$_SESSION['nb4'] or date('Y',  strtotime($_POST['DateIN']))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 
		break;
	}
			$columnstoedit=array('DateIN','Remarks'); //REMOVED EDIT OF 'ForRequestNo',      ReqTxnID=\''.$result1['ReqTxnID'].'\', 
			$sqlend=' ToBranchNo=\''.$_SESSION['bnum'].'\',TOEncodedByNo=\''.$_SESSION['(ak0)'].'\',TOTimeStamp=Now()';
		} elseif ($txntype=='Repack') {
			if((($_POST['DateOUT']))<$_SESSION['nb4'] or date('Y',strtotime($_POST['DateOUT']))<>$currentyr){ 
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 
		break;
	}
			$columnstoedit=array('DateOUT','TransferNo','Remarks');
			$sqlend=' BranchNo=\''.$_SESSION['bnum'].'\',FROMEncodedByNo=\''.$_SESSION['(ak0)'].'\',FROMTimeStamp=Now(), ToBranchNo=\''.$_SESSION['bnum'].'\',TOEncodedByNo=\''.$_SESSION['(ak0)'].'\',TOTimeStamp=Now()';	
		} else {
			$columnstoedit=array();
		}
	} else {
        	$msg='&closeddata=true';//($stmt1->rowCount()==0?'&norequest=true':'&closeddata=true');
		header("Location:addedittxfr.php?w=Transfers&txntype=".$_REQUEST['txntype']."&TxnID=".$txnid.$msg);
	break;
	}
	
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	
	$sql=$sqlupdate.$sql.$sqlend. ' where TxnID='.$txnid . ' and Posted=0 and `DateOUT`>'.$_SESSION['nb4']; 
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addedittxfr.php?w=Transfers&txntype=".$_REQUEST['txntype']."&TxnID=".$txnid);
	break;

case 'TxfrSubAdd':
	if (!allowedToOpen(705,'1rtc')) {
   echo 'No permission'; exit; }
	$txnid=intval($_REQUEST['TxnID']); $txntype=$_REQUEST['txntype'];         
        
	// confirm RequestNo
	if ($txntype<>'Repack'){
            include('maketables/getasofmonth.php');
            include('maketables/undeliveredint.php');
			// if (b.ProvincialBranch=0, mp.PriceLevel3,mp.PriceLevel4)
	$sql1='select ud.ItemCode, ud.'.($txntype=='Out'?' SendBal':' RcvBal').', 
	
	
			'.$plcase.'


		as UnitPrice, RequestNo from undeliveredreq as ud join invty_2transfer t on ud.ReqTxnID=t.ReqTxnID
	join `invty_5latestminprice` lmp on ud.ItemCode=lmp.ItemCode
join `1branches` b on b.BranchNo=t.ToBranchNo
where ud.'.($txntype=='Out'?' SendBal':' RcvBal').'>='.$_POST['QtySent'] .' and ud.ItemCode='.$_POST['ItemCode'] .' and t.TxnID='.$txnid;	
	} else {
	$sql1='Select lmp.ItemCode,0, 
	
	(SELECT 
						'.$plcase.'
					FROM `1branches` b1 where b1.BranchNo='.$_SESSION['bnum'].'
				)



		as UnitPrice from invty_5latestminprice lmp where lmp.ItemCode='.$_POST['BulkItemCode'];
	} 
	$stmt1=$link->query($sql1);
	$result=$stmt1->fetch(); //echo $sql1.'<br>'. $stmt1->rowCount();break;
	
	if ($stmt1->rowCount()>0){	
	if (($txntype<>'Repack') AND (substr($result['RequestNo'],0,1)=='d')){ $defectiveorinter=1;} elseif (substr($result['RequestNo'],0,1)=='c') { $defectiveorinter=2;} else { $defectiveorinter=0;}
	$sqlinsert='INSERT INTO `invty_2transfersub` SET `TxnID`='.$txnid.', ';
	$sql=''; 
	$sqlend=' Defective='.$defectiveorinter.', FROMEncodedByNo=\''.$_SESSION['(ak0)'].'\',FROMTimeStamp=Now(), TOEncodedByNo=\''.$_SESSION['(ak0)'].'\',TOTimeStamp=Now()';
	if (editOk('invty_2transfer',$txnid,$link,$txntype) and $stmt1->rowCount()>0){
        if ($txntype=='Out') {
	$columnstoadd=array('ItemCode','QtySent','SerialNo');
	$price=' `UnitPrice`='.$result['UnitPrice'].',  `UnitCost`='.$result['UnitPrice'].', ';
	
    } elseif ($txntype=='In') {
	 $columnstoadd=array('ItemCode','QtyReceived','SerialNo');
	 $price='';
    
    } elseif ($txntype=='Repack') {
	 $columnstoadd=array();
	 $price=' `ItemCode`='.$_POST['BulkItemCode'].', `QtySent`='.$_POST['QtySent'].', `UnitPrice`='.$result['UnitPrice'].', `QtyReceived`=0, `UnitCost`=0, `SerialNo`=\''.$_POST['SerialNo'].'\', ';
	 // FIRST RUN for Bulk
	// foreach ($columnstoadd as $field) {
	//	$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	//}
	$sqlbulk=$sqlinsert.$sql.$price.$sqlend; 
	// echo $sqlbulk;break;
        $stmt=$link->prepare($sqlbulk);
	$stmt->execute();
	//$columnstoadd=array();
	// SECOND RUN for Repack
	$sqlrepackitem='Select RepackItemCode, RepackQtyPerBulkUnit from invty_1itemsforrepack where BulkItemCode='.$_POST['BulkItemCode'];
	$stmtrepack=$link->query($sqlrepackitem);
	$resultrepack=$stmtrepack->fetch();
	$price=' `ItemCode`='.$resultrepack['RepackItemCode'].', `QtySent`=0,`UnitPrice`=0, `QtyReceived`='.($resultrepack['RepackQtyPerBulkUnit']*$_POST['QtySent']).', `UnitCost`=round('.(($result['UnitPrice']*$_POST['QtySent'])/($resultrepack['RepackQtyPerBulkUnit']*$_POST['QtySent'])).',2), `SerialNo`=\''.$_POST['SerialNo'].'\', ';
    }else {
	 $columnstoadd=array();
    }
    } else {
	
	$msg=($stmt1->rowCount()==0?'&norequest=true':'&closeddata=true');
		header("Location:addedittxfr.php?w=Transfers&TxnID=".$txnid.$msg);
		break;
    }
	
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.$price.$sqlend; 
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addedittxfr.php?w=Transfers&TxnID=".$txnid);
	} else { 
	header("Location:addedittxfr.php?w=Transfers&noitem=true&TxnID=".$txnid);	
	}
	break;

case 'TxfrSubEdit':
	if (!allowedToOpen(705,'1rtc')) {
   echo 'No permission'; exit;
}
	$txnid=intval($_REQUEST['TxnID']); $txnsubid=$_REQUEST['TxnSubId'];
	// confirm RequestNo
	if ($txntype=='Out'){
            include('maketables/getasofmonth.php');
            include('maketables/undeliveredint.php');
			// if (b.ProvincialBranch=0, mp.PriceLevel3,mp.PriceLevel4)
	$sql1='select ud.ItemCode, ud.SendBal, 
		'.$plcase.'
	as UnitPrice from undeliveredreq as ud join invty_2transfer t on ud.ReqTxnID=t.ReqTxnID
	join `invty_5latestminprice` lmp on ud.ItemCode=lmp.ItemCode
join `1branches` b on b.BranchNo=t.ToBranchNo
where ud.SendBal>='.$_POST['QtySent'] .' and ud.ItemCode='.$_POST['ItemCode'];
	} else {
		$sql1='select s.ItemCode from  invty_2transfersub s where s.TxnSubId='.$txnsubid;
	}
	$stmt1=$link->query($sql1);
	$result=$stmt1->fetch();
	
	if ($stmt1->rowCount()>0){
	$sqlupdate='UPDATE `invty_2transfersub` as s join `invty_2transfer` as m on m.TxnID=s.TxnID SET ';
	$sql=''; $sqlend='';
	if (editOk('invty_2transfer',$txnid,$link,$txntype)){
        if ($txntype=='Out') {
	$columnstoedit=array('ItemCode','QtySent','SerialNo');
	$sqlend='  `UnitPrice`='.$result['UnitPrice'].', `UnitCost`='.$result['UnitPrice'].', s.FROMEncodedByNo=\''.$_SESSION['(ak0)'].'\',s.FROMTimeStamp=Now()  where TxnSubId='.$txnsubid . ' and m.Posted=0 and m.`DateOUT`>\''.$_SESSION['nb4'].'\'';
    } elseif ($txntype=='In') {
	 $columnstoedit=array('QtyReceived');
	 $sqlend='  s.TOEncodedByNo=\''.$_SESSION['(ak0)'].'\',s.TOTimeStamp=Now()  where TxnSubId='.$txnsubid . ' and m.PostedIn=0 and m.`DateOUT`>\''.$_SESSION['nb4'].'\'';
    } elseif ($txntype=='Repack') {
        if(($_SESSION['bnum']==40) ){ //only oil items in Central warehouse are allowed to be edited
            $sqlend='  s.TOEncodedByNo=\''.$_SESSION['(ak0)'].'\',s.TOTimeStamp=Now()  where TxnSubId='.$txnsubid . ' and m.Posted=0 and m.`DateOUT`>\''.$_SESSION['nb4'].'\' and m.BranchNo=40 AND m.ToBranchNo=40';   
            if (in_array($_POST['ItemCode'],array(1808,1816,1812,3062))){ 
                $columnstoedit=array('QtySent','SerialNo');
                
                foreach ($columnstoedit as $field) { $sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', ';  }
                $sql=$sqlupdate.$sql.$sqlend;  if($_SESSION['(ak0)']==1002){ echo $sql; }
                include_once 'trailinvty.php'; recordtrail($txnsubid,'invty_2transfersub',$link,0);
                $stmt=$link->prepare($sql); $stmt->execute();
                
                $sqlrepackitem='Select RepackItemCode, RepackQtyPerBulkUnit, UnitPrice, (SELECT TxnSubId FROM invty_2transfersub WHERE TxnID='.$txnid.' AND ItemCode=ip.RepackItemCode) AS RepackTxnSubId from invty_1itemsforrepack ip JOIN invty_2transfersub ts ON ip.BulkItemCode=ts.ItemCode WHERE ts.TxnID='.$txnid.' AND BulkItemCode='.$_POST['ItemCode'];
                $stmtrepack=$link->query($sqlrepackitem); 	$resultrepack=$stmtrepack->fetch();
                $sql=' `QtySent`=0,`UnitPrice`=0, `QtyReceived`='.($resultrepack['RepackQtyPerBulkUnit']*$_POST['QtySent']).', `UnitCost`=round('.(($resultrepack['UnitPrice']*$_POST['QtySent'])/($resultrepack['RepackQtyPerBulkUnit']*$_POST['QtySent'])).',2), s.TOEncodedByNo=\''.$_SESSION['(ak0)'].'\',s.TOTimeStamp=Now()  where TxnSubId='.$resultrepack['RepackTxnSubId'] . ' and m.Posted=0 and m.`DateOUT`>\''.$_SESSION['nb4'].'\'';
                $sqlend=''; if($_SESSION['(ak0)']==1002){ echo $sql; }
                $columnstoedit=array();
                
            } 
            else if (in_array($_POST['ItemCode'],array(2426,1811,1815))) { $columnstoedit=array('QtyReceived','SerialNo');} 
            else { $columnstoedit=array();}
	 
        } else { 
            $columnstoedit=array('SerialNo');   
            $sqlend='  s.TOEncodedByNo=\''.$_SESSION['(ak0)'].'\',s.TOTimeStamp=Now()  where TxnSubId='.$txnsubid . ' and m.Posted=0 and m.`DateOUT`>\''.$_SESSION['nb4'].'\''; }
	 
    } else {
	 $columnstoedit=array();
    }
    } else {
        $columnstoedit=array();
    }
	
	
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.$sqlend; //if($_SESSION['(ak0)']==1002){ echo $sql; break;}
	
        include_once 'trailinvty.php'; recordtrail($txnsubid,'invty_2transfersub',$link,0);
        
        $stmt=$link->prepare($sql); $stmt->execute();
	
	header("Location:addedittxfr.php?w=Transfers&txntype=".$_REQUEST['txntype']."&TxnID=".$txnid);
	} else {
	header("Location:addedittxfr.php?w=Transfers&noitem=true&TxnID=".$txnid);	
	}
	break;

case 'TxfrSubAccept':
	if (!allowedToOpen(705,'1rtc')) {
   echo 'No permission'; exit;
}
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	
	$sqlupdate='';
	$sql=''; $sqlend='';
	if (editOk('invty_2transfer',$txnid,$link,'In')){
        
	 $sql='UPDATE `invty_2transfersub` as s join `invty_2transfer` as m on m.TxnID=s.TxnID SET s.QtyReceived=s.QtySent, s.UnitCost=s.UnitPrice, s.TOEncodedByNo=\''.$_SESSION['(ak0)'].'\',s.TOTimeStamp=Now() where TxnSubId='.$txnsubid . ' and m.PostedIn=0 and m.`DateIN`>\''.$_SESSION['nb4'].'\''; 
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addedittxfr.php?w=Transfers&txntype=".$_REQUEST['txntype']."&TxnID=".$txnid);
	} else {
	header("Location:addedittxfr.php?w=Transfers&noitem=true&TxnID=".$txnid);	
	}
	break;

case 'TxfrSubScan':
	if (!allowedToOpen(705,'1rtc')) {
   echo 'No permission'; exit;
}
	$txnid=intval($_REQUEST['TxnID']);
	// confirm RequestNo
        include('maketables/getasofmonth.php');
        include('maketables/undeliveredint.php');
	$sql1='select ud.ItemCode, ud.SendBal from undeliveredreq as ud join invty_2transfer t on ud.ReqTxnID=t.ReqTxnID
	where ud.SendBal>='.$_POST['QtySent'] .' and ud.ItemCode='.$_POST['ItemCode'].' AND `TxnID`='.$txnid;
	$stmt1=$link->query($sql1);
	$result=$stmt1->fetch();
	// check if item is undelivered
//	$sql1='select ud.ItemCode from invty_44undeliveredreq as ud
//                where ud.Posted<>0 and ReqBal<>0 and ItemCode=\''. $_POST['ItemCode'] . '\' and RequestNo=\''. $_POST['RequestNo'] . '\' and '.($txntype=='Out'?' SupplierBranchNo=':' BranchNo=').$_SESSION['bnum'];
	$stmt1=$link->query($sql1);
	$result=$stmt1->fetch();
	if ($stmt1->rowCount()>0){
	$sqlinsert='INSERT INTO `invty_2transferbarcodesub` SET `TxnID`='.$txnid.', ';
	$sql=''; 
	$columnstoadd=array('ItemCode','QtySent');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' SerialNo=\'' . $_POST['SerialNo']. '\''; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addedittxfr.php?w=ScanItems&TxnID=".$txnid);
	} else {
	header("Location:addedittxfr.php?w=ScanItems&noitem=true&TxnID=".$txnid);	
	}
	break;
case 'TxfrSubScanDel':
	if (!allowedToOpen(705,'1rtc')) {
   echo 'No permission'; exit;
}
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	$sql='Delete from `invty_2transferbarcodesub` where TxnSubId='.$txnsubid;
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addedittxfr.php?w=ScanItems&TxnID=".$txnid);
	break;
case 'TxfrSubScanSend':
	if (!allowedToOpen(705,'1rtc')) {
   echo 'No permission'; exit;
}
	$txnid=intval($_REQUEST['TxnID']);
	
if (editOk('invty_2transfer',$txnid,$link,$txntype)){
	$sql0='CREATE TEMPORARY TABLE scanneditems (
TxnID	int(11)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
SerialNo	varchar(45)	NULL,
QtySent		double	NOT NULL,
UnitPrice	double	NOT NULL)

select tbs.TxnID, tbs.ItemCode, tbs.SerialNo, Sum(tbs.QtySent) as QtySent, 



	'.$plcase.'

	as UnitPrice,

	
	'.$plcase.'

		as UnitCost from invty_2transferbarcodesub tbs
join invty_2transfer tm on tm.TxnID=tbs.TxnID
join `invty_5latestminprice` lmp on tbs.ItemCode=lmp.ItemCode
join `1branches` b on b.BranchNo=tm.ToBranchNo
where tm.TxnID='.$txnid.' group by tbs.ItemCode';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
//ADDITIONAL QTY
$sql='Select s.ItemCode,s.QtySent+ts.QtySent as QtySent, s.UnitPrice, concat(ts.SerialNo,\' \',s.SerialNo) as SerialNo from scanneditems s join invty_2transfersub ts on s.TxnID=ts.TxnID and s.ItemCode=ts.ItemCode';
$stmt=$link->query($sql);
$result=$stmt->fetchAll();
$resultcount=count($result);

for ($row = 0; $row <  $resultcount; $row++){

	$sqlupdate='UPDATE `invty_2transfersub` SET ';
	$sql=''; 
	$sqlend=' FROMEncodedByNo=\''.$_SESSION['(ak0)'].'\',FROMTimeStamp=Now(), TOEncodedByNo=\''.$_SESSION['(ak0)'].'\',TOTimeStamp=Now()';
	$columnstoadd=array('QtySent','UnitPrice','SerialNo');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' ' . $field. '=\''.$result[$row][$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.$sqlend. ' where ItemCode='.$result[$row]['ItemCode'].' and TxnID='.$txnid; 
	//echo $sql.' result'.$resultcount;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
}
//NEW ITEMS
$sql='Select s.* from scanneditems s left join invty_2transfersub ts on s.TxnID=ts.TxnID and s.ItemCode=ts.ItemCode where ts.ItemCode is null';
$stmt=$link->query($sql);
$result=$stmt->fetchAll();
$resultcount=count($result);//$stmt->rowCount();
//foreach($result as $row){
//	echo 'result: '.$row['ItemCode'].'  price: '.$row['UnitPrice'];
//	 
//}
for ($row = 0; $row <  $resultcount; $row++){
//foreach($result as $row){
	$sqlinsert='INSERT INTO `invty_2transfersub` SET `TxnID`='.$txnid.', ';
	$sql=''; 
	$sqlend=' FROMEncodedByNo=\''.$_SESSION['(ak0)'].'\',FROMTimeStamp=Now(), TOEncodedByNo=\''.$_SESSION['(ak0)'].'\',TOTimeStamp=Now()';
	$columnstoadd=array('ItemCode','QtySent','UnitPrice','UnitCost','SerialNo');
	
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
	$sql='Delete from `invty_2transferbarcodesub` where `TxnID`='.$txnid;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addedittxfr.php?w=Transfers&TxnID=".$txnid);
	break;

case 'Request':
	//to check if editable
	if(($_POST['Date'])<$_SESSION['nb4']  or date('Y', strtotime($_POST['Date']))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	 break; 
	}
	//to get request number  removed: elseif ($_POST['Defective']==0) { $defectiveorinter='g'; } 
	if ($_POST['Defective']==1){ $defectiveorinter='d'; } elseif ($_POST['Defective']==2) { $defectiveorinter='c'; } else { $defectiveorinter='i'; }
	$reqnoprefix=$defectiveorinter.str_pad($_SESSION['bnum'],2,'0',STR_PAD_LEFT).'-'.date('md').'-';
$charsreq=9;
if($_SESSION['bnum']>=100){
	$charsreq=10;	
}
$sql='SELECT RequestNo FROM invty_3branchrequest where Left(RequestNo,'.$charsreq.')=\''.$reqnoprefix.'\' order by RequestNo desc Limit 1;';
	    $stmt=$link->query($sql);
	    $result=$stmt->fetch();
	    if (is_null($result['RequestNo'])){
		$reqno=$reqnoprefix.'1';
	    } else {
		$reqno=$reqnoprefix.(substr($result['RequestNo'],-1)+1);
	    }
	
	//echo substr($result['RequestNo'],-1)+1; echo $reqno;
	$sqlinsert='INSERT INTO `invty_3branchrequest` SET `RequestNo`=\''.$reqno.'\', ';
        $sql='';
        $columnstoadd=array('Date','Remarks','DateReq');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	$branchno=companyandbranchValue($link,'1branches','Branch',addslashes($_POST['SupplierBranchNo']),'BranchNo');
	$sql=$sqlinsert.$sql.' BranchNo=\''.$_SESSION['bnum'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now(),SupplierBranchNo=\''.$branchno.'\'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID from `invty_3branchrequest` where BranchNo=\''.$_SESSION['bnum'].'\' and RequestNo=\''.$reqno.'\'';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	header("Location:addedittxfr.php?w=Request&txntype=".$txntype."&TxnID=".$result['TxnID']);
	break;
case 'RequestSubAdd':
	$txnid=intval($_REQUEST['TxnID']);
	$sql='Select RequestNo from `invty_3branchrequest` where TxnID='.$txnid; $stmt=$link->query($sql); $result=$stmt->fetch();
	$sqlinsert='INSERT INTO `invty_3branchrequestsub` SET `TxnID`='.$txnid.', ';
	$sql=''; 
	if (editOk('invty_3branchrequest',$txnid,$link,$txntype)){
		$columnstoadd=array('ItemCode','RequestQty');
		} else {
			$columnstoadd=array();
		    }
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addedittxfr.php?w=Request&TxnID=".$txnid);
	break;
	
case 'RequestMainEdit':
	include('../backendphp/functions/checkeditablemain.php');
	$txnid=intval($_REQUEST['TxnID']);
	if (editOk('invty_3branchrequest',$txnid,$link,$txntype)){
		$columnstoedit=array('Date','SupplierBranchNo','RequestNo','Remarks','DateReq');
	} else {
        $columnstoedit=array();
	}	
		
	$sqlupdate='UPDATE `invty_3branchrequest` SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where TxnID='.$txnid . ' and Posted=0 and `Date`>'.$_SESSION['nb4']; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addedittxfr.php?w=Request&txntype=".$_REQUEST['txntype']."&TxnID=".$txnid);
	break;
case 'RequestSubEdit':
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	if (editOk('invty_3branchrequest',$txnid,$link,$txntype)){
		$columnstoedit=array('ItemCode','RequestQty');
		} else {
			$columnstoedit=array();
		    }
		
	$sqlupdate='UPDATE `invty_3branchrequestsub` as s join `invty_3branchrequest` as m on m.TxnID=s.TxnID SET ';
	$sql=''; 
		
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' s.EncodedByNo=\''.$_SESSION['(ak0)'].'\', s.TimeStamp=Now() where TxnSubId='.$txnsubid . ' and m.Posted=0 and m.`Date`>\''.$_SESSION['nb4'].'\''; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addedittxfr.php?w=Request&txntype=".$_REQUEST['txntype']."&TxnID=".$txnid);
	break;
case 'RequestMainDel':
	$txnid=intval($_REQUEST['TxnID']);
	$sql='Delete from `invty_3branchrequest` where TxnID='.$txnid;
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:txnsinterperday.php?w=Request&perday=1&BranchNo=".$_SESSION['(ak0)']);
	break;
case 'RequestSubDel':
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	$sql='Delete from `invty_3branchrequestsub` where TxnSubId='.$txnsubid;
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addedittxfr.php?w=Request&txntype=".$_REQUEST['txntype']."&TxnID=".$txnid);
	break;

case 'Repack':
	if (!allowedToOpen(705,'1rtc')) {
   echo 'No permission'; exit;
}
	        //to check if editable
	if(($_POST['DateofRepack'])<$_SESSION['nb4'] OR date('Y',  strtotime($_POST['DateofRepack']))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	 break; 
	}
	
	//To get Repack Number
	include_once '../backendphp/functions/getnumber.php';
	
	$txnnoprefix='r-'.str_pad($_SESSION['bnum'],2,'0',STR_PAD_LEFT).'-';
		$charsrepack=5;
	if($_SESSION['bnum']>=100){
		$charsrepack=6;
	}
	$txnno=getAutoTxnNo($txnnoprefix,$charsrepack,'TransferNo','invty_2transfer',$link);
	
	$sqlinsert='INSERT INTO `invty_2transfer` SET `DateOut`=\''.$_POST['DateofRepack'].'\', `DateIn`=\''.$_POST['DateofRepack'].'\', TransferNo=\''.$txnno.'\',';
        $sql='';
        $columnstoadd=array('txntype');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' ForRequestNo=\'Repack\', BranchNo=\''.$_SESSION['bnum'].'\', ToBranchNo=\''.$_SESSION['bnum'].'\',FROMEncodedByNo=\''.$_SESSION['(ak0)'].'\',TOEncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',FROMTimeStamp=Now(),TOTimeStamp=Now()';
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID from `invty_2transfer` where BranchNo=\''.$_SESSION['bnum'].'\' and TransferNo=\''.$txnno.'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	header("Location:addedittxfr.php?w=Transfers&TxnID=".$result['TxnID']);
        break;

        }
 $stmt=null; $link=null;
?>