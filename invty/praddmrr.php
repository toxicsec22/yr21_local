<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	include_once('../backendphp/functions/editok.php');
	include_once '../generalinfo/lists.inc'; 
        // check if allowed
        $allowed=array(695,6924,697,717);
        $allow=0;
        foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=$allow+1; } else { $allow=$allow; }}
        if ($allow==0) { echo 'No permission'; exit;}
        // end of check
		include_once $path.'/acrossyrs/dbinit/userinit.php';
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
        
	 
       
        $txntype=$_GET['txntype'];
	$whichqry=$_GET['w'];
switch ($whichqry){
case 'MRR':
        if (!allowedToOpen(695,'1rtc')) { echo 'No permission'; exit;}
	//to check if editable
	if(($_POST['Date'])<$_SESSION['nb4']  or date('Y', strtotime($_POST['Date']))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	 break; 
	}
	// choose open po's only
	//include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        $sql0='SELECT SupplierNo,`PONo` FROM `invty_41supplierundelivered` WHERE `PONo` LIKE \''.$_POST['ForPONo'].'\'';
        $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
        $pono=$res0['PONo'];
        $suppno=$res0['SupplierNo'];//getValue($link,'1suppliers','SupplierName',addslashes($_POST['SupplierNo']),'SupplierNo');
	//$pono=comboBoxValueWithSql ($link,'SELECT SupplierNo,`PONo` FROM `invty_41supplierundelivered` WHERE `PONo` LIKE \''.$_POST['ForPONo'].'\'','PONo');
	ECHO '\''.$pono.'\''; 
	if ($pono=='' or is_null($pono)){ header("Location:".$_SERVER['HTTP_REFERER']);}
	
	//
	$sqlterms='Select SupplierNo, Terms from `1suppliers` where SupplierNo='.$suppno;
	$stmt=$link->query($sqlterms); $resultterms=$stmt->fetch();
	$terms=$resultterms['Terms'];
	$sqlinsert='INSERT INTO `invty_2mrr` SET txntype=6,SupplierNo='.$suppno.',Terms='.$terms.', ForPONo=\''.$pono.'\',SuppDRNo=\''.$_POST['SuppDRNo'].'\',SuppDRDate=\''.$_POST['SuppDRDate'].'\', ';
        $sql='';
        $columnstoadd=array('Date','MRRNo','SuppInvNo','SuppInvDate','Remarks');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo=\''.$_SESSION['bnum'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	// echo $sql; exit();
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID, txntype from `invty_2mrr` where BranchNo=\''.$_SESSION['bnum'].'\' and MRRNo=\''.$_POST['MRRNo'].'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	header("Location:addeditmrr.php?w=".$whichqry."&TxnID=".$result['TxnID']."&txntype=".$result['txntype']);
	break;
case 'MRRAutoEnter':
    if (!allowedToOpen(6924,'1rtc')) { echo 'No permission'; exit;}
	$txnid=intval($_REQUEST['TxnID']);
	$txntype=$_REQUEST['txntype'];
	if (editOk('invty_2mrr',$txnid,$link,$txntype)){
		//$sqllookup='Select SupplierUndelivered, UnitCost from `invty_41supplierundelivered` ud
		//where ud.SupplierUndelivered<>0 and ud.PONo=\''.$_GET['po'].'\' and ud.ItemCode=\''.$_GET['ItemCode'].'\'';
		//$stmt=$link->query($sqllookup);
		//$resultlookup=$stmt->fetch();
		
		$sql='INSERT INTO `invty_2mrrsub` (`TxnID`, `ItemCode`, `Qty`, `TimeStamp`, `EncodedByNo`, `UnitCost`)
Select '.$txnid.' as `TxnID`, \''.$_GET['ItemCode'].'\' as `ItemCode`, ud.`SupplierUndelivered` as `Qty`, Now() as `TimeStamp`, \''.$_SESSION['(ak0)'].'\' as `EncodedByNo`, ud.UnitCost from `invty_41supplierundelivered` ud
		where ud.SupplierUndelivered<>0 and ud.PONo=\''.$_GET['po'].'\' and ud.ItemCode=\''.$_GET['ItemCode'].'\'';
		} else {
			$sql='Select 0;';
		    }
		
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditmrr.php?w=MRR&TxnID=".$txnid);
	break;
case 'MRRSubAdd':
    if (!allowedToOpen(695,'1rtc')) { echo 'No permission'; exit;}
	$txnid=intval($_REQUEST['TxnID']);
	$txntype=$_REQUEST['txntype'];
	if (editOk('invty_2mrr',$txnid,$link,$txntype)){
		$sqlcost=($txntype==6?'invty_3order o join invty_3ordersub c on o.TxnID=c.TxnID  and c.ItemCode=\''.$_POST['ItemCode'].'\' where o.PONo=\''.$_GET['po'].'\'':'`invty_52latestcost` c where c.ItemCode=\''.$_POST['ItemCode'].'\'');
		/*$addlfield=$txntype==8?',`Defective`':'';
		$addlfieldvalue=$txntype==8?','.$_POST['Defective']:'';*/  //'.$addlfield.' '.$addlfieldvalue.'
		$sql='INSERT INTO `invty_2mrrsub` (`TxnID`, `ItemCode`, `Qty`, `SerialNo`, `TimeStamp`, `EncodedByNo`, `UnitCost`)
Select '.$txnid.' as `TxnID`, \''.$_POST['ItemCode'].'\' as `ItemCode`, \''.($txntype<>9?$_POST['Qty']:(abs($_POST['Qty'])*-1)).'\' as `Qty`,  \''.$_POST['SerialNo'].'\' as `SerialNo`, Now() as `TimeStamp`, \''.$_SESSION['(ak0)'].'\' as `EncodedByNo`, c.UnitCost from '.$sqlcost;
		} else {
			$sql='Select 0;';
		    }
	//echo $sql;break;	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	/*if ($txntype==8){
            $sql0='SELECT TxnSubId, Qty FROM `invty_2mrrsub` WHERE TxnID='.$txnid.' AND ItemCode=\''.$_POST['ItemCode'].'\'';
            $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
            
        
		$txnSubId=$res0['TxnSubId'];
		$sqlinsert='INSERT INTO invty_2prdecision set TxnSubId='.$txnSubId.',Qty='.$res0['Qty'].', EncodedByNo=\''.$_SESSION['(ak0)'].'\'';
		$stmt=$link->prepare($sqlinsert);
		$stmt->execute(); 
        }*/	
	if ($txntype==9){header("Location:addmrrmain.php?w=StoreUsed&TxnID=".$txnid);} else {
		header("Location:addeditmrr.php?w=".$whichqry."&TxnID=".$txnid);
	}
	
	break;
	
case 'MRRMainEdit':
    if (!allowedToOpen(695,'1rtc') AND !allowedToOpen(6951,'1rtc')) { echo 'No permission'; exit;}
	include('../backendphp/functions/checkeditablemain.php');
	$txnid=intval($_REQUEST['TxnID']);
	$txntype=$_REQUEST['txntype'];  
	if (editOk('invty_2mrr',$txnid,$link,$txntype)){
		switch ($txntype){
			case 'MRR':
			case 6: //mrr
				// choose open po's only
				include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
				if (allowedToOpen(695,'1rtc')){
				$suppno=$_POST['SupplierNo'];
				$sqlupdate='SupplierNo='.$suppno.', ';
				
                                $columnstoedit=array('Date','MRRNo','SuppInvNo','SuppDRNo','SuppDRDate','SuppInvDate','Terms','Remarks');
					}
				if (!empty($_POST['CompanyName'])){$co=comboBoxValue ($link,'`1companies`','Company',$_POST['CompanyName'],'CompanyNo');} else {$co='null';}
				$sqlupdate=$sqlupdate.' RCompany='.$co.', ';  
			break;
			/*case 'PurchaseReturn':
			case 8: //purchase return
                            include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
				$suppno=$_POST['SupplierNo']; //getValue($link,'1suppliers','SupplierName',addslashes($_POST['SupplierNo']),'SupplierNo');
				$columnstoedit=array('Date','MRRNo','Remarks');	$sqlupdate='SupplierNo='.$suppno.', ';
                                if (!empty($_POST['CompanyName'])){$co=comboBoxValue ($link,'`1companies`','Company',$_POST['CompanyName'],'CompanyNo');} else {$co='null';}
				$sqlupdate=$sqlupdate.' RCompany='.$co.', ';
			break;*/
			case 'StoreUsed':
			case 9: //store used
				$suppno=$_SESSION['bnum'];
				$columnstoedit=array('Date','MRRNo','Remarks');	$sqlupdate='SupplierNo='.$suppno.', ';
			break;
			default:
				$columnstoedit=array();	$sqlupdate='';
		}
		
	} else {
        $columnstoedit=array();$sqlupdate='';
	}	
		
        $sqlupdate='UPDATE `invty_2mrr` SET '.$sqlupdate;  
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where TxnID='.$txnid . ' and Posted=0 and `Date`>'.$_SESSION['nb4']; 
	
        include_once 'trailinvty.php'; recordtrail($txnid,'invty_2mrr',$link,0);
	$stmt=$link->prepare($sql); $stmt->execute();
        header("Location:addeditmrr.php?w=".$whichqry."&TxnID=".$txnid);
	break;
case 'MRRSubEdit':
	if (!allowedToOpen(695,'1rtc')) { echo 'No permission'; exit;}
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	$txntype=$_REQUEST['txntype'];
	if (editOk('invty_2mrr',$txnid,$link,$txntype)){
		/*if ($txntype==8){ // Purch Return
			if (!allowedToOpen(6924,'1rtc')){ echo 'No permission'; exit;} else { $columnstoedit=array('ItemCode','SerialNo');}
		} else {*/ 
            $columnstoedit=array('ItemCode','SerialNo');//}
		//added this condition
		if (allowedToOpen(69241,'1rtc')){
			array_push($columnstoedit,'UnitCost');
		}
		
	} else { $columnstoedit=array();  }
		
	$sqlupdate='UPDATE `invty_2mrrsub` as s join `invty_2mrr` as m on m.TxnID=s.TxnID SET Qty='.($txntype<>9?$_POST['Qty']:(abs($_POST['Qty'])*-1)).', ';
	$sql=''; 
		
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' s.EncodedByNo=\''.$_SESSION['(ak0)'].'\', s.TimeStamp=Now() where TxnSubId='.$txnsubid . ' and m.Posted=0 and m.`Date`>\''.$_SESSION['nb4'].'\''; 
	/* echo $sql;
		exit(); */
        include_once 'trailinvty.php'; recordtrail($txnsubid,'invty_2mrrsub',$link,0);
	$stmt=$link->prepare($sql); $stmt->execute();
        header("Location:addeditmrr.php?w=".$whichqry."&TxnID=".$txnid);
	break;

case 'StoreUsedRequestApp':
        if (!allowedToOpen(697,'1rtc')) { echo 'No permission'; exit;}
	$sql='Insert into `approvals_2storeused` (`BranchNo`,`ItemCode`,`Qty`,`Reason`,`SerialNo`,`EncodedByNo`,`TimeStamp`) VALUES (\''.$_SESSION['bnum'].'\', '.$_POST['ItemCode'].', ' .$_POST['Qty'].', \''.$_POST['Reason'].'\', \''.$_POST['SerialNo'].'\',\''.$_SESSION['(ak0)'].'\',Now())';
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addmrrmain.php?w=StoreUsed&txntype=9");
	break;

case 'AcceptMRR':
	if (!allowedToOpen(717,'1rtc')){ echo 'No permission'; exit; }
        $invtytxnid=$_GET['TxnID'];
		$sqlcheck='SELECT SuppInvNo FROM invty_2mrr WHERE TxnID='.$invtytxnid;
		$stmtcheck=$link->query($sqlcheck); $rescheck=$stmtcheck->fetch();
		if($rescheck['SuppInvNo']<>'' OR $rescheck['SuppInvNo']<>0){
			goto proceed;
		} else {
			echo 'Please check mrr invoice number.';
			exit();
		}
		proceed:
	// to add to purchase main
	$sql0='Select RCompany, m.SupplierNo, ifnull(m.SuppInvNo,m.MRRNo) as SupplierInv, m.BranchNo, m.Date, ifnull(m.SuppInvDate,m.Date) as DateofInv, m.MRRNo, s.Terms, m.Remarks from invty_2mrr m join `1suppliers` s on m.SupplierNo=s.SupplierNo where m.SenttoAcctg=0 and m.TxnID='.$invtytxnid;
	
	$stmt=$link->query($sql0);
	$result=$stmt->fetch();
	$sqlinsert='INSERT INTO `acctg_2purchasemain` SET `CreditAccountID`=400, '; //`DebitAccountID`=300, 'Amount', ; '.($result['BranchNo']==999?330:400).'
        $columnstoadd=array('SupplierNo','SupplierInv','BranchNo','Date','DateofInv','MRRNo','Terms','Remarks');
	$rinv=0;
	if (!is_null($result['RCompany'])) { $columnstoadd[]='RCompany'; $rinv=1;}
	$sql='';
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$result[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\', Posted=1, TimeStamp=Now()';
	// echo $sql;//break;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	// purchase main added
	//get txnid of purchase
	
	$sql='Select TxnID from `acctg_2purchasemain` where  MRRNo=\''.$result['MRRNo'].'\''; //BranchNo=\''.$_SESSION['bnum'].'\' and
	//echo $sql; break;
	$stmt=$link->query($sql);
	$resultpurchase=$stmt->fetch();
	$txnid=$resultpurchase['TxnID'];
	// to add purchase sub
	$sql0='Select ifnull(sum(ms.Qty*ms.UnitCost),0) as Amount, Defective from invty_2mrr m join invty_2mrrsub ms on m.TxnID=ms.TxnID join `1suppliers` s on m.SupplierNo=s.SupplierNo where m.SenttoAcctg=0 and m.TxnID='.$invtytxnid. ' group by m.TxnID'; // echo $sql0; break;	
	$stmt=$link->query($sql0);
	$result=$stmt->fetch(); $amt=$result['Amount']; $defective=$result['Defective'];
//	if ($rinv==1){ INPUT VAT WILL BE ENCODED AT MONTH END FOR ACTUAL FIGURES.
//		$vat=round(($amt*(0.12/1.12)),2);$inv=($amt-$vat); 
//		$sql='INSERT INTO `acctg_2purchasesub` SET TxnID='.$txnid.',`DebitAccountID`=300, Amount='.$inv.', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
//		//echo $sql; break;
//		$stmt=$link->prepare($sql);
//		$stmt->execute();
//		$sql='INSERT INTO `acctg_2purchasesub` SET TxnID='.$txnid.',`DebitAccountID`=510, Amount='.$vat.', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
//	} else {
	$sql='INSERT INTO `acctg_2purchasesub` SET TxnID='.$txnid.',`DebitAccountID`=IF('.$defective.'=1,331,300), Amount=round('.$amt.',2), EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	//}
	// echo $sql; break;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	// purchase sub added
	// set as Sent
	$sql='update invty_2mrr set SenttoAcctg=1, Posted=1 where `TxnID`='.$invtytxnid;
	$stmt=$link->prepare($sql);
	$result=$stmt->execute();
	//echo $sql; break;
	//$sql='update invty_2mrr set SenttoAcctg=1, Posted=1, PostedByNo=\''.$_SESSION['(ak0)'].'\' where `TxnID`='.$txnid;
	//$stmt=$link->prepare($sql);
	//$result=$stmt->execute();
	header("Location:lookupgeninv.php?w=AcceptMRR");
	break;
//end of acceptrmrr
case 'AcceptPR':
	if (!allowedToOpen(717,'1rtc')){ echo 'No permission'; exit; }
        $invtytxnid=$_GET['TxnID'];
	// to add to purchase main
	$sql0='Select RCompany, p.SupplierNo, p.BranchNo, p.Date, p.Date AS DateofInv, p.PRNo,  p.Remarks from invty_2pr p join `1suppliers` s on p.SupplierNo=s.SupplierNo where p.SenttoAcctg=0 and p.TxnID='.$invtytxnid;
	
	$stmt=$link->query($sql0);
	$result=$stmt->fetch();
	$sqlinsert='INSERT INTO `acctg_2purchasemain` SET `CreditAccountID`=400, '; //`DebitAccountID`=300, 'Amount', ; '.($result['BranchNo']==999?330:400).'
        $columnstoadd=array('SupplierNo','BranchNo','Date','Remarks','DateofInv');
	$rinv=0;
	if (!is_null($result['RCompany']) AND $result['RCompany']<>0) { $columnstoadd[]='RCompany'; $rinv=1;}
	$sql='';
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$result[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\',MRRNo=\''.$result['PRNo'].'\',SupplierInv=\''.$result['PRNo'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\', Posted=1, TimeStamp=Now()';
	// echo $sql; exit();
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	
	$sql='Select TxnID from `acctg_2purchasemain` where  MRRNo=\''.$result['PRNo'].'\''; //BranchNo=\''.$_SESSION['bnum'].'\' and
	
	$stmt=$link->query($sql);
	$resultpurchase=$stmt->fetch();
	$txnid=$resultpurchase['TxnID'];
	
	$sql0='Select ifnull(sum(ps.Qty*ps.UnitCost),0) as Amount, Defective from invty_2pr p join invty_2prsub ps on p.TxnID=ps.TxnID join `1suppliers` s on p.SupplierNo=s.SupplierNo where p.SenttoAcctg=0 and p.TxnID='.$invtytxnid. ' group by p.TxnID'; // echo $sql0; break;	
	$stmt=$link->query($sql0);
	$result=$stmt->fetch(); $amt=$result['Amount']; $defective=$result['Defective'];

	$sql='INSERT INTO `acctg_2purchasesub` SET TxnID='.$txnid.',`DebitAccountID`=IF('.$defective.'=1,331,300), Amount=round('.$amt.',2), EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='update invty_2pr set SenttoAcctg=1, Posted=1 where `TxnID`='.$invtytxnid;
	$stmt=$link->prepare($sql);
	$result=$stmt->execute();
	
	header("Location:lookupgeninv.php?w=AcceptMRR");
	break;
        }
$stmt=null; $link=null;
?>