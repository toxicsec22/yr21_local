<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
        // check if allowed
$allowed=array(6920,6921);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=$allow+1; } else { $allow=$allow; }}
if ($allow==0) { header("Location:".$_SERVER['HTTP_REFERER']."?denied=true");}
// end of check
        
	include_once('../backendphp/functions/editok.php');
	include_once "../generalinfo/lists.inc";
        include_once $path.'/acrossyrs/dbinit/userinit.php';
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
	 
        
	$whichqry=$_GET['w'];
switch ($whichqry){

case 'Request':
	//to check if editable
	if(($_POST['Date'])<$_SESSION['nb4']  or date('Y', strtotime($_POST['Date']))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	 break; 
	}
	$sqlinsert='INSERT INTO `invty_3extrequest` SET ';
        $sql='';
        $columnstoadd=array('Date','RequestNo','Remarks','DateReq');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo=\''.$_SESSION['bnum'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID from `invty_3extrequest` where BranchNo=\''.$_SESSION['bnum'].'\' and RequestNo=\''.$_POST['RequestNo'].'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	header("Location:addeditext.php?w=Request&TxnID=".$result['TxnID']);
	break;
case 'RequestSubAdd':
	$txnid=intval($_REQUEST['TxnID']);
	$sqlinsert='INSERT INTO `invty_3extrequestsub` SET `TxnID`='.$txnid.', ';
	$sql=''; 
	if (editOk('invty_3extrequest',$txnid,$link,'external')){
		$columnstoadd=array('ItemCode','Qty');
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
	header("Location:addeditext.php?w=Request&TxnID=".$txnid);
	break;
	
case 'RequestMainEdit':
	include('../backendphp/functions/checkeditablemain.php');
	$txnid=intval($_REQUEST['TxnID']);
	if (editOk('invty_3extrequest',$txnid,$link,'external')){
		$columnstoedit=array('Date','RequestNo','Remarks','DateReq');
	} else {
        $columnstoedit=array();
	}	
		
	$sqlupdate='UPDATE `invty_3extrequest` SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where TxnID='.$txnid . ' and Posted=0 and `Date`>'.$_SESSION['nb4']; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditext.php?w=Request&TxnID=".$txnid);
	break;
case 'RequestSubEdit':
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	if (editOk('invty_3extrequest',$txnid,$link,'external')){
		$columnstoedit=array('ItemCode','Qty');
		} else {
			$columnstoedit=array();
		    }
		
	$sqlupdate='UPDATE `invty_3extrequestsub` as s join `invty_3extrequest` as m on m.TxnID=s.TxnID SET ';
	$sql=''; 
		
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' s.EncodedByNo=\''.$_SESSION['(ak0)'].'\', s.TimeStamp=Now() where TxnSubId='.$txnsubid . ' and m.Posted=0 and m.`Date`>\''.$_SESSION['nb4'].'\''; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditext.php?w=Request&TxnID=".$txnid);
	break;
case 'RequestMainDel':
	$txnid=intval($_REQUEST['TxnID']);
	$sql='Delete from `invty_3extrequest` where TxnID='.$txnid;
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:txns_extperday.php?w=Request");
	break;
case 'RequestSubDel':
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	$sql='Delete from `invty_3extrequestsub` where TxnSubId='.$txnsubid;
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditext.php?w=Request&TxnID=".$txnid);
	break;
case 'SendforDistri':
	$txnid=intval($_REQUEST['TxnID']);
	$sql='INSERT INTO `invty_3distributeorders`
(`RequestNo`,`ItemCode`,`Qty`,`DateReq`,`BranchNo`,`UnitCost`,`PriceLevel1`,`PriceLevel2`,`PriceLevel3`,`PriceLevel4`,`PriceLevel5`)
Select p.RequestNo, p.ItemCode, p.Pending as Qty, p.DateRequired as DateReq, p.BranchNo, ifnull(lc.UnitCost,0) as UnitCost,ifnull(m.PriceLevel1,0) as PriceLevel1, ifnull(m.PriceLevel2,0) as PriceLevel2,ifnull(m.PriceLevel3,0) as PriceLevel3, ifnull(m.PriceLevel4,0) as PriceLevel4, ifnull(m.PriceLevel5,0) as PriceLevel5 from `invty_40pendingextrequests` as p 
left join `invty_5latestminprice` as m on p.ItemCode=m.ItemCode 
left join `invty_52latestcost` lc on p.ItemCode=lc.ItemCode where p.TxnID='.$txnid.' and p.Pending<>0';
// echo $sql; exit();
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditext.php?w=SendforDistri&TxnID=".$txnid);
	break;
case 'SendforDistriAdd':
	$txnid=1;
	$suppno=getValue($link,'1suppliers','SupplierName',addslashes($_POST['SupplierNo']),'SupplierNo');
	$companyno=(!is_null($_POST['CompanyNo']) and strlen($_POST['CompanyNo'])>1)?getValue($link,'1companies','Company',addslashes($_POST['CompanyNo']),'CompanyNo'):'null';
	$sqlprice='Select ItemCode, PriceLevel1, PriceLevel2, PriceLevel3, PriceLevel4, PriceLevel5 from `invty_5latestminprice` where ItemCode='.$_POST['ItemCode'];
	$stmtprice=$link->prepare($sqlprice);
	$stmtprice->execute();
	$resultprice=$stmtprice->fetch();
	$sqlinsert='INSERT INTO `invty_3distributeorders` SET SupplierNo='.$suppno.', CompanyNo='.$companyno.', ';
	$sql='';
	$columnstoadd=array('ItemCode','Qty','UnitCost','RequestNo');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo='. $_SESSION['bnum'] .', PriceLevel1=\''.$resultprice['PriceLevel1'].'\', PriceLevel2=\''.$resultprice['PriceLevel2'].'\', PriceLevel3=\''.$resultprice['PriceLevel3'].'\', PriceLevel4=\''.$resultprice['PriceLevel4'].'\', PriceLevel5=\''.$resultprice['PriceLevel5'].'\''; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditext.php?w=SendforDistri&TxnID=".$txnid);
	break;
case 'SendforDistriEdit':
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	$suppno=getValue($link,'1suppliers','SupplierName',addslashes($_POST['SupplierName']),'SupplierNo');
	$companyno=(!is_null($_POST['Company']) and strlen($_POST['Company'])>1)?getValue($link,'1companies','Company',addslashes($_POST['Company']),'CompanyNo'):'null';
	$columnstoedit=array('RequestNo','ItemCode','Qty','DateReq','UnitCost','PriceLevel3','PriceLevel4','BranchNo');
		
	$sqlupdate='UPDATE `invty_3distributeorders` SET ';
	$sql=''; 
		
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' SupplierNo='.$suppno.', CompanyNo='.$companyno.' where TxnSubId='.$txnsubid; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditext.php?w=SendforDistri&TxnID=".$txnid);
	break;
case 'SendforDistriDel':
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	$sql='Delete from `invty_3distributeorders` where TxnSubId='.$txnsubid;
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditext.php?w=SendforDistri&TxnID=".$txnid);
	break;
case 'MakePO':
	$txnid=1;
	$sqlmain='SELECT SupplierNo, RequestNo, BranchNo, DateReq, CompanyNo FROM invty_3distributeorders where SupplierNo <>0 group by SupplierNo, RequestNo, BranchNo';
	$stmtmain=$link->prepare($sqlmain);
	$stmtmain->execute();
	$resultmain=$stmtmain->fetchAll();
	foreach ($resultmain as $main){
		$sql='';
		//TO GET PO NO
		$ponoprefix=str_pad($main['SupplierNo'],3,'0',STR_PAD_LEFT).'-'.str_pad($main['BranchNo'],2,'0',STR_PAD_LEFT).'-'.date('md').'-';
		$sqlpono='SELECT PONo FROM invty_3order where Left(PONo,12)=\''.$ponoprefix.'\' order by PONo desc Limit 1;';
	    $stmt=$link->query($sqlpono);
	    $resultpono=$stmt->fetch();
	    if (is_null($resultpono['PONo'])){
		$pono=$ponoprefix.'1';
	    } else {
		$pono=$ponoprefix.(substr($resultpono['PONo'],-1)+1);
	    } //END OF PO NO
	    
	$sqlinsert='INSERT INTO `invty_3order` SET Date=\''.date("Y-m-d").'\', PONo=\''.$pono.'\', ';
	
	$columnstoadd=array('SupplierNo', 'RequestNo', 'DateReq', 'CompanyNo');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' ' . $field. '=\''.$main[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo='. $_SESSION['bnum']. ', EncodedByNo=\''.$_SESSION['(ak0)'].'\',PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	//END OF INSERT TO MAIN FORM
	$sqlid='Select TxnID from invty_3order where PONo=\''.$pono.'\'';
	$stmt=$link->query($sqlid);
	$result=$stmt->fetch();
	$sqlsub='SELECT '.$result['TxnID'].' as TxnID, d.* FROM invty_3distributeorders d where SupplierNo='.$main['SupplierNo'].' group by SupplierNo, RequestNo, ItemCode';
		$stmtsub=$link->prepare($sqlsub);
		$stmtsub->execute();
		$resultsub=$stmtsub->fetchAll();
		foreach($resultsub as $sub){
			$sqlinsert='INSERT INTO `invty_3ordersub` SET ';
			$sql='';
			// $columnstoadd=array('TxnID','ItemCode','Qty','UnitCost','PriceLevel3','PriceLevel4');
			$columnstoadd=array('TxnID','ItemCode','Qty','UnitCost','PriceLevel1','PriceLevel2','PriceLevel3','PriceLevel4','PriceLevel5');
			foreach ($columnstoadd as $field) {
				$sql=$sql.' ' . $field. '=\''.$sub[$field].'\', '; 
				}
		$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	//END OF SUB ENTRIES
		}
	}
	//DELETE THOSE THAT WERE ORDERED
	$sqldel='Delete d.* FROM invty_3distributeorders d join `invty_3order` om on om.RequestNo=d.RequestNo 
join `invty_3ordersub` os on om.TxnID=os.TxnId and os.ItemCode=d.ItemCode;';
	$stmt=$link->prepare($sqldel);
	$stmt->execute();
	header("Location:txns_extperday.php?w=Order");
	break;

case 'Order':
	//to check if editable
	if(($_POST['Date'])<$_SESSION['nb4']  or date('Y', strtotime($_POST['Date']))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	 break; 
		break;
	}
	
	$suppno=getValue($link,'1suppliers','SupplierName',addslashes($_POST['SupplierNo']),'SupplierNo');
	$companyno=(!is_null($_POST['CompanyNo']) and strlen($_POST['CompanyNo'])>1)?getValue($link,'1companies','Company',addslashes($_POST['CompanyNo']),'CompanyNo'):'null';
	
	//Get Last No
	$sql='SELECT RIGHT(PONo,2) AS LastNo,LEFT(RIGHT(PONo,7),LOCATE("-",RIGHT(PONo,7))-1) AS StartDate FROM invty_3order WHERE BranchNo='.$_POST['BranchNo'].' AND SupplierNo='.$suppno.' HAVING '.date('md').'=StartDate ORDER BY TxnID DESC LIMIT 1;';
	
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
		
	$branchno=((strlen($_POST['BranchNo'])==1)?'0'.$_POST['BranchNo']:$_POST['BranchNo']);
		
	if($stmt->rowCount()>0){
		$newno=$result['LastNo']+1;
		$pono=$suppno.'-'.$branchno.'-'.date('md').'-'.((strlen($newno)==1)?'0'.$newno:$newno);
	
	} else {
		$pono=$suppno.'-'.$branchno.'-'.date('md').'-01';
	}
	
	
	$sqlinsert='INSERT INTO `invty_3order` SET ';
        $sql='';
        $columnstoadd=array('Date','Remarks','DateReq','BranchNo');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' SupplierNo='.$suppno.', PONo="'.$pono.'", CompanyNo='.$companyno.', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	
	// echo $sql; exit();
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	// $sql='Select TxnID from `invty_3order` where PONo=\''.$_POST['PONo'].'\'';
	$sql='Select TxnID from `invty_3order` where PONo=\''.$pono.'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	header("Location:addeditext.php?w=Order&TxnID=".$result['TxnID']);
	break;
case 'OrderSubAdd':
	$txnid=intval($_REQUEST['TxnID']);
	$sqlinsert='INSERT INTO `invty_3ordersub` SET `TxnID`='.$txnid.', ';
	$sql=''; 
	if (editOk('invty_3order',$txnid,$link,'external')){
		$columnstoadd=array('ItemCode','Qty');
		} else {
			$columnstoadd=array();
		    }
	
	//PriceLevels
	$sqlpl='SELECT PriceLevel1,PriceLevel2,PriceLevel3,PriceLevel4,PriceLevel5 FROM invty_5latestminprice WHERE ItemCode='.$_REQUEST['ItemCode'].';';
	$stmtpl=$link->query($sqlpl);
	$resultpl=$stmtpl->fetch();
	
		if($stmtpl->rowCount()<>0){
			$pl1=$resultpl['PriceLevel1'];
			$pl2=$resultpl['PriceLevel2'];
			$pl3=$resultpl['PriceLevel3'];
			$pl4=$resultpl['PriceLevel4'];
			$pl5=$resultpl['PriceLevel5'];
		} else {
			$pl1=0; $pl2=0; $pl3=0; $pl4=0; $pl5=0;
		}
	
		$pl="PriceLevel1='".$pl1."',PriceLevel2='".$pl2."',PriceLevel3='".$pl3."',PriceLevel4='".$pl4."',PriceLevel5='".$pl5."',";
	
	include('sqlphp/costlistperitem.php');
	//LatestCost By Supplier
	$sqlc='Select UnitCost from costlistperitem lc join invty_1items i on i.ItemCode=lc.ItemCode join invty_1category c on c.CatNo=i.CatNo WHERE lc.ItemCode='.$_REQUEST['ItemCode'].' AND SupplierNo='.$_POST['SupplierNo'].'';
	$stmtc=$link->query($sqlc);
	$resultc=$stmtc->fetch();
	
	if($stmtc->rowCount()<>0){
		$unitcost=$resultc['UnitCost'];
	} else {
		$unitcost=0;
	}
		

	
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.$pl.'UnitCost="'.$unitcost.'", EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditext.php?w=Order&TxnID=".$txnid);
	break;
	
case 'OrderMainEdit':
	include('../backendphp/functions/checkeditablemain.php');
	$txnid=intval($_REQUEST['TxnID']);
	if (editOk('invty_3order',$txnid,$link,'external')){
		$columnstoedit=array('Date','PONo','SupplierNo','RequestNo','BranchNo','Remarks','DateReq','CompanyNo');
	} else {
        $columnstoedit=array();
	}	
		
	$sqlupdate='UPDATE `invty_3order` SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where TxnID='.$txnid . ' and Posted=0 and `Date`>'.$_SESSION['nb4']; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditext.php?w=Order&TxnID=".$txnid);
	break;
case 'OrderSubEdit':
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId']; 
	if (editOk('invty_3order',$txnid,$link,'OrderSubEdit')){
			if (allowedToOpen(6921,'1rtc')){
				$columnstoedit=array('ItemCode','Qty','UnitCost','PriceLevel1','PriceLevel2','PriceLevel3','PriceLevel4','PriceLevel5');
			} else {
				$columnstoedit=array('ItemCode','Qty');
			}
		} else {
			$columnstoedit=array();
		    }
	
	$sqlupdate='UPDATE `invty_3ordersub` as s join `invty_3order` as m on m.TxnID=s.TxnID SET ';
	$sql=''; 
		
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' s.EncodedByNo=\''.$_SESSION['(ak0)'].'\', s.TimeStamp=Now() where TxnSubId='.$txnsubid . ' and m.Posted=0 and m.`Date`>\''.$_SESSION['nb4'].'\''; 
        
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditext.php?w=Order&TxnID=".$txnid);
	break;
case 'OrderMainDel':
	$txnid=intval($_REQUEST['TxnID']);
	$sql='Delete from `invty_3order` where TxnID='.$txnid;
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:txns_extperday.php?w=Order");
	break;
case 'OrderSubDel':
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	$sql='Delete from `invty_3ordersub` where TxnSubId='.$txnsubid;
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditext.php?w=Order&TxnID=".$txnid);
	break;

case 'ApprovePO':
	$txnid=intval($_REQUEST['TxnID']); $approval=$_POST['Approval']=='Approve'?1:2;
	$sql='UPDATE `invty_3order` SET Approved='.$approval.', ApprovedByNo='.$_SESSION['(ak0)'].', ApproveTS=Now() WHERE TxnID='.$txnid;
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:".$_SERVER['HTTP_REFERER']);
	break;
        }
 $link=null; $stmt=null;
?>