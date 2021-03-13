<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
         
        $allowed=((isset($_GET['auditreturn']))?699:array(700,728,999,2021,6927,6928,6929));
        if (!allowedToOpen($allowed,'1rtc')) {   echo 'No permission'; exit;}
        
        if (allowedToOpen(2201,'1rtc')){
        error_reporting(E_ALL);
	ini_set('display_errors', 1);
}
            
	include_once('../backendphp/functions/getnumber.php');
	include_once('../backendphp/functions/editok.php');
	include_once "../generalinfo/lists.inc";
     include_once('invlayout/pricelevelcase.php');   
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
	 
        
        $whichqry=$_GET['w'];

switch ($whichqry){
case 'SaleMain':
        if($_SESSION['bnum']==999) { if (!allowedToOpen(999,'1rtc')) {   echo 'No permission'; exit;}           
        } else { if (!allowedToOpen(array(699,700),'1rtc')) {   echo 'No permission'; exit;}}
	
	//to check if editable
	include('../backendphp/functions/checkeditablemain.php');
	
	$txntype=$_REQUEST['txntype'];
	
	// to get client no
	$clientno=getNumber((((isset($_GET['auditreturn'])) AND allowedToOpen(699,'1rtc'))?'Employee':'ClientEmployee'),addslashes($_POST['Client']));
	// to get sold by
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        if ($_REQUEST['txntype']==2){
        $clientno=comboBoxValueWithSql($link,'SELECT ClientNo FROM 1clients WHERE ARClientType<>0 AND ClientNo='.$clientno.' AND '.$clientno.' NOT IN (SELECT ClientNo FROM `acctg_34holdstatus` WHERE ((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0) AND (HoldonRecord<>2) AND ClientNo<>10004) UNION SELECT 10001 ','ClientNo');
        }

		
        $soldbyno=in_array($txntype,array(3,5))?0:comboBoxValue ($link,'`1employees`','concat(`Nickname`," ", `SurName`)',$_POST['SoldBy'],'IDNo'); 
	$sqlinsert='INSERT INTO `invty_2sale` SET ClientNo='.$clientno.', SoldByNo='.$soldbyno.', '; 
        $sql='';
        $columnstoadd=array('Date','Remarks','PaymentType','CheckDetails','DateofCheck','PONo','txntype');
		$repsaleno=str_replace(' ','',$_POST['SaleNo']);
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo=\''.$_SESSION['bnum'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\',SaleNo=\''.$repsaleno.'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	// echo $sql; exit();
        // if ($_SESSION['(ak0)']==1002) {echo $sql;}
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID,ClientNo,txntype from `invty_2sale` where BranchNo=\''.$_SESSION['bnum'].'\' and txntype=\''.$_POST['txntype'].'\' and SaleNo=\''.$repsaleno.'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
		
	header("Location:addeditsale.php?txntype=".$_REQUEST['txntype']."&c&TxnID=".$result['TxnID']);
        break;
		
		case'UpdateRemarks':
		$remarks='';
		// $counter=0;
		foreach($_POST['Remarks'] as $remark){
			$remarks.=''.$remark.',';
			// $counter++;
			
		};
		$remarks=substr($remarks, 0, -1);
		
			$sql='Update invty_2sale set Remarks=\''.$remarks.'\' where TxnID=\''.$_GET['TxnID'].'\'';
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
			
		header('Location:addeditsale.php?txntype='.$_GET['txntype'].'&TxnID='.$_GET['TxnID'].'');
		break;
		
case 'SaleSub':
	if($_SESSION['bnum']==999) { if (!allowedToOpen(999,'1rtc')) {   echo 'No permission'; exit;}           
        } else { if (!allowedToOpen(array(699,700),'1rtc')) {   echo 'No permission'; exit;}}
	$txnid=intval($_REQUEST['TxnID']);
	$pk='TxnID';$table='invty_2sale';
	//to check if editable
	include('../backendphp/functions/checkeditablesub.php');
	//to check minprice
	// if('.$_SESSION['bnum'].' in (Select BranchNo from 1branches b where b.ProvincialBranch=0), PriceLevel3, PriceLevel4)
	$sql='Select 
	(SELECT 
						'.$plcase.'
					FROM `1branches` b1 where b1.BranchNo='.$_SESSION['bnum'].'
				)
		as SellPrice from `invty_5latestminprice` lmp where ItemCode='.$_REQUEST['ItemCode'];
		$stmt=$link->query($sql);
		$result=$stmt->fetch();
	$msg=$result['SellPrice']>$_REQUEST['UnitPrice']?'&msg=Check selling price or request for special price.':'';
	$price=$result['SellPrice']>$_REQUEST['UnitPrice']?$result['SellPrice']:$_REQUEST['UnitPrice'];
	// end remarks for minprice
	
	$sqls='Select ClientNo from invty_2sale where TxnID='.$txnid.'';
	$stmts=$link->query($sqls);
	$results=$stmts->fetch();
	
	$sqlc='select CreditLimit from 1clients c where c.ClientNo=\''.$results['ClientNo'].'\' ';
	$stmtc=$link->query($sqlc);
	$resultc=$stmtc->fetch();
	
	$sqlh='select Hold from comments_5clientsonhold h where h.ClientNo=\''.$results['ClientNo'].'\' order by TxnId Desc limit 1 ';
	$stmth=$link->query($sqlh);
	$resulth=$stmth->fetch();
	
	// echo $sqlh; exit();
	
	if(($_GET['txntype']==2 ) AND $resulth['Hold']!=4 AND $resultc['CreditLimit']!=1 AND $results['ClientNo']!=10001 AND $results['ClientNo']!=10004 ){
	$sqlcl='Select CreditLimit-IFNULL((sum(InvBalance)),0) AS Climit,(select CreditLimit-IFNULL((sum(InvBalance)),0)-sum(Qty*UnitPrice) from invty_2salesub ss join invty_2sale s on s.TxnID=ss.TxnID where s.ClientNo=\''.$results['ClientNo'].'\' and txntype in(2,5) and DateofCheck=CURDATE()) as Available from acctg_unpaidinv ui join 1clients c on c.ClientNo=ui.ClientNo where c.ClientNo=\''.$results['ClientNo'].'\' ';
	$stmtcl=$link->query($sqlcl);
	$resultcl=$stmtcl->fetch();
	
	
	// echo $sqlcl; exit();;
	// echo $resultcl['Climit']; echo '</br>';
	// echo $resultcl['Available']; echo '</br>';
	// echo $_POST['Qty']*$price; exit();
	
		
		if(($_POST['Qty']*$price)<=$resultcl['Climit'] AND ((($_POST['Qty']*$price)<=$resultcl['Available']) XOR ($resultcl['Available']===NULL))){
	
	$sqlinsert='INSERT INTO `invty_2salesub` SET TxnID='.$txnid.', UnitPrice='.$price.', QtySign='.($_POST['Qty']>=0?'1':'-1').', ';
        $sql='';
        $columnstoadd=array('ItemCode', 'Qty', 'UnitCost', 'SerialNo');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	}else{echo 'Credit Limit Exceeded'; exit();}
	header("Location:addeditsale.php?txntype=".$_GET['txntype']."&TxnID=".$txnid.$msg);
		
	}else{
	
	
	
	$sqlinsert='INSERT INTO `invty_2salesub` SET TxnID='.$txnid.', UnitPrice='.$price.', QtySign='.($_POST['Qty']>=0?'1':'-1').', ';
        $sql='';
        $columnstoadd=array('ItemCode', 'Qty', 'UnitCost', 'SerialNo');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	
	header("Location:addeditsale.php?txntype=".$_GET['txntype']."&TxnID=".$txnid.$msg);}
        break;
case 'SaleMainEdit':
	if($_SESSION['bnum']==999) { if (!allowedToOpen(999,'1rtc')) {   echo 'No permission'; exit;}           
        } else { if (!allowedToOpen(700,'1rtc') and !allowedToOpen(2021,'1rtc')) {   echo 'No permission'; exit;}}
		include('../backendphp/functions/checkeditablemain.php');	
	
	$txnid=intval($_REQUEST['TxnID']);
	$clientno=getNumber('Client',(addslashes(substr($_POST['ClientName'],0,20))));
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        if ($_REQUEST['txntype']==2){
        // $clientno=comboBoxValueWithSql($link,'SELECT ClientNo FROM 1clients WHERE ARClient=1 AND ClientNo='.$clientno.' AND '.$clientno.' NOT IN (SELECT ClientNo FROM `acctg_34holdstatus` WHERE ((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0) OR HoldonRecord=2) UNION SELECT 10001 ','ClientNo');
        $clientno=comboBoxValueWithSql($link,'SELECT ClientNo FROM 1clients WHERE ARClientType<>0 AND ClientNo='.$clientno.' AND '.$clientno.' NOT IN (SELECT ClientNo FROM `acctg_34holdstatus` WHERE ((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0) AND (HoldonRecord<>2) AND ClientNo<>10004) UNION SELECT 10001 ','ClientNo');
        }
        $soldbyno=in_array($_REQUEST['txntype'],array(3,5))?0:comboBoxValue ($link,'`1employees`','concat(`Nickname`," ", `SurName`)',$_POST['SoldBy'],'IDNo'); 
	
		
	if (allowedToOpen(2021,'1rtc')) {
		//revised editOk function to remove posted condition
		$sqlmain='Select s.`Date`, s.Posted from `invty_2sale` as s where TxnID='.$txnid;
		$stmt=$link->query($sqlmain);
		$result=$stmt->fetch();
		if ($result['Date']>$_SESSION['nb4']){
			$columnstoedit=array('Remarks','PaymentType','CheckDetails','DateofCheck','PONo');
			$condition=' and (`Date`>\''.$_SESSION['nb4'].'\')';
			} else {  $columnstoedit=array(); }
	} else {
	if (editOk('invty_2sale',$txnid,$link,$_REQUEST['txntype'])){
        $columnstoedit=array('Date','SaleNo','Remarks','CheckDetails','DateofCheck','PONo');
		$condition=' and (Posted=0) and (`Date`>\''.$_SESSION['nb4'].'\')';
		} else {
        $columnstoedit=array();
		 }
	}
	$sqlupdate='UPDATE `invty_2sale` SET `ClientNo`='.$clientno.', `SoldByNo`='.$soldbyno.', ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where (TxnID='.$txnid.')' . $condition; 
	// echo $sql; break;
        include_once 'trailinvty.php'; recordtrail($txnid,'invty_2sale',$link,0);
	$stmt=$link->prepare($sql); $stmt->execute();
        header("Location:addeditsale.php?txntype=".$_REQUEST['txntype']."&TxnID=".$txnid);
	break;
case 'ChangeTeamLeader':
	if (!allowedToOpen(719,'1rtc')) { echo 'No permission'; exit;}	
	$txnid=intval($_REQUEST['TxnID']);
	$sql='UPDATE `invty_2sale` SET `TeamLeader`=\''.$_POST['TeamLeaderID'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where `Date`>\''.$_SESSION['nb4'].'\' and TxnID='.$txnid; 
	// echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:lookupgeninv.php?w=ChangeTeamLeader");
	break;
case 'SaleSubEdit':
	if($_SESSION['bnum']==999) { if (!allowedToOpen(999,'1rtc')) {   echo 'No permission'; exit;}           
        } else { if (!allowedToOpen(array(699,700),'1rtc') and !allowedToOpen(6929,'1rtc')) {   echo 'No permission'; exit;}}
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	
	//to check minprice
	$sql='Select 
	(SELECT 
						'.$plcase.'
					FROM `1branches` b1 where b1.BranchNo='.$_SESSION['bnum'].'
				)

		as SellPrice from `invty_5latestminprice` lmp where ItemCode='.$_REQUEST['ItemCode'];
		$stmt=$link->query($sql);
		$result=$stmt->fetch();
	$msg=$result['SellPrice']>$_REQUEST['UnitPrice']?'&msg=Check selling price or request for special price.':'';
	
	// end remarks for minprice
	
	if (editOk('invty_2sale',$txnid,$link,$_REQUEST['txntype'])){
		
		//new condition
		if(!empty($_POST['SpecPriceRequest'])){
			$sqlc='select * from invty_7specdisctapproval where TxnID=\''.$txnid.'\' and ItemCode=\''.$_POST['ItemCode'].'\'';
			$stmtc=$link->query($sqlc);
			
			if($stmtc->rowCount()!=0){
				$sqlu='Update invty_7specdisctapproval set ItemCode=\''.$_POST['ItemCode'].'\',BranchRemarks=\''.$_POST['BranchRemarks'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now(),SpecPriceRequest=\''.$_POST['SpecPriceRequest'].'\' where TxnID=\''.$txnid.'\' and ItemCode=\''.$_POST['ItemCode'].'\' and Approved=0';
				// echo $sqlu; exit();
				$stmtu=$link->prepare($sqlu); $stmtu->execute();
			}else{
			$sqli='insert into invty_7specdisctapproval set TxnID=\''.$txnid.'\',ItemCode=\''.$_POST['ItemCode'].'\',BranchRemarks=\''.$_POST['BranchRemarks'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now(),SpecPriceRequest=\''.$_POST['SpecPriceRequest'].'\'';
			// echo $sqli; exit();
			$stmti=$link->prepare($sqli); $stmti->execute();
			}
		}
		//
		
        $columnstoedit=array('ItemCode','Qty','SerialNo');
		if($_REQUEST['withspecprice']){
$specprice='&specprice=1';			
			$columnstoeditsp=(allowedToOpen(6929,'1rtc'))?array('SpecPriceApproved','SCRemarks'):array('SpecPriceRequest','BranchRemarks');
			$setapproved=(allowedToOpen(6929,'1rtc'))?'Approved=1':'';
			$encodedbyno=(allowedToOpen(6929,'1rtc'))?'ApprovedByNo':'EncodedByNo';
			$ts=(allowedToOpen(6929,'1rtc'))?'ApprovedTS':'TimeStamp';
			$sqlupdate='UPDATE `invty_7specdisctapproval` SET '; $sql='';
			foreach ($columnstoeditsp as $field) {
				$sql=$sql.' ' . $field. '=\''.addslashes($_POST[$field]).'\', '; 
				}
			$sql=$sqlupdate.$sql.$encodedbyno.'=\''.$_SESSION['(ak0)'].'\', '.$ts.'=Now() where ItemCode='.$_POST['ItemCode'] . ' and TxnID='.$txnid.' and Approved=0';
			//echo $sql;break;
			$stmt=$link->prepare($sql); $stmt->execute();
                        $sqlsp='SELECT SpecPriceApproved FROM `invty_7specdisctapproval` WHERE ItemCode='.$_POST['ItemCode'] . ' and TxnID='.$txnid.' AND Approved<>0';
                        $stmtsp=$link->query($sqlsp); $resultsp=$stmtsp->fetch();
						
			if($stmtsp->rowCount()==1){			
				$price=$_REQUEST['UnitPrice']>$resultsp['SpecPriceApproved']?$_REQUEST['UnitPrice']:$resultsp['SpecPriceApproved'];
			}
			else {
				$price=$result['SellPrice'];
			}
                        $columnstoedit=array('Qty','SerialNo');
		} else {
$specprice='';			
			$price=$result['SellPrice']>$_REQUEST['UnitPrice']?$result['SellPrice']:$_REQUEST['UnitPrice'];
		}
    } else {
        $columnstoedit=array();
    }
	
	$sqls='Select ClientNo from invty_2sale where TxnID='.$txnid.'';
	$stmts=$link->query($sqls);
	$results=$stmts->fetch();
	
	$sqlc='select CreditLimit from 1clients c where c.ClientNo=\''.$results['ClientNo'].'\' ';
	$stmtc=$link->query($sqlc);
	$resultc=$stmtc->fetch();
	
	$sqlh='select Hold from comments_5clientsonhold h where h.ClientNo=\''.$results['ClientNo'].'\' order by TxnId Desc limit 1 ';
	$stmth=$link->query($sqlh);
	$resulth=$stmth->fetch();
	
	
	if(($_GET['txntype']==2 ) AND $resulth['Hold']!=4 AND $resultc['CreditLimit']!=1 AND $results['ClientNo']!=10001 AND $results['ClientNo']!=10004 ){
		$sqlcl='Select c.CreditLimit-IFNULL((sum(InvBalance)),0) AS Climit,(select  c.CreditLimit-IFNULL((sum(ui.InvBalance)),0)-sum(ss.Qty*ss.UnitPrice) from invty_2salesub ss join invty_2sale s on ss.TxnID=s.TxnID where ss.TxnSubId<>\''.$_GET['TxnSubId'].'\' and s.ClientNo=\''.$results['ClientNo'].'\' and txntype in(2,5) and DateofCheck=CURDATE()) as Available from acctg_unpaidinv ui join 1clients c on c.ClientNo=ui.ClientNo where c.ClientNo=\''.$results['ClientNo'].'\'';
	$stmtcl=$link->query($sqlcl);
	$resultcl=$stmtcl->fetch();
	
	// echo $sqlcl; echo '<br>';
	// echo $resultcl['Climit']; echo '<br>';
	// echo ($_POST['Qty'])*$price; echo '<br>';
	// echo $resultcl['Available']; echo '<br>'; exit();
	
	if(($_POST['Qty']*$price)<=$resultcl['Climit'] AND (($_POST['Qty']*$price)<=$resultcl['Available'] XOR $resultcl['Available']===NULL) ){
	
	$sqlupdate='UPDATE `invty_2salesub` as s join `invty_2sale` as m on m.TxnID=s.TxnID SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' UnitPrice='.$price.', s.EncodedByNo=\''.$_SESSION['(ak0)'].'\', s.TimeStamp=Now() where TxnSubId='.$txnsubid . ' and m.Posted=0 and m.`Date`>\''.$_SESSION['nb4'].'\''; 
	// echo $sql;break;
        include_once 'trailinvty.php'; recordtrail($txnsubid,'invty_2salesub',$link,0);
	$stmt=$link->prepare($sql); $stmt->execute();
	}else{echo 'Credit Limit Exceeded'; exit();}
	
        header("Location:addeditsale.php?txntype=".$_REQUEST['txntype']."".$specprice."&TxnID=".$txnid.$msg);
		
	}else{
		
	$sqlupdate='UPDATE `invty_2salesub` as s join `invty_2sale` as m on m.TxnID=s.TxnID SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' UnitPrice='.$price.', s.EncodedByNo=\''.$_SESSION['(ak0)'].'\', s.TimeStamp=Now() where TxnSubId='.$txnsubid . ' and m.Posted=0 and m.`Date`>\''.$_SESSION['nb4'].'\''; 
	// echo $sql;break;
        include_once 'trailinvty.php'; recordtrail($txnsubid,'invty_2salesub',$link,0);
	$stmt=$link->prepare($sql); $stmt->execute();
	

	
	header("Location:addeditsale.php?txntype=".$_REQUEST['txntype']."".$specprice."&TxnID=".$txnid.$msg);}
	break;
case 'SaleSubDistriEdit':
	if (!allowedToOpen(6928,'1rtc')) { echo 'No permission'; exit;}
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	if (editOk('invty_2sale',$txnid,$link,$_REQUEST['txntype'])){
	$chargetoidno=getNumber('Employee',addslashes($_POST['ChargeTo']));
    } else {
        $columnstoedit=array();
    }
	
	$sql='UPDATE `invty_2salesubauditdistri` as s join `invty_2sale` as m on m.TxnID=s.TxnID SET ChargeToIDNo='.$chargetoidno.', ChargeAmount='.$_POST['ChargeAmount'].', s.EncodedByNo=\''.$_SESSION['(ak0)'].'\', s.TimeStamp=Now() where TxnSubId='.$txnsubid . ' and m.Posted=0 and m.`Date`>\''.$_SESSION['nb4'].'\''; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditsale.php?txntype=".$_REQUEST['txntype']."&TxnID=".$txnid);
	break;

case 'SaleSubDistriDel':
	if (!allowedToOpen(6928,'1rtc')) { echo 'No permission'; exit;}
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	if (editOk('invty_2sale',$txnid,$link,$_REQUEST['txntype'])){
	$sql='Delete from `invty_2salesubauditdistri` where TxnSubId='.$txnsubid;
	$msg='';
	} else {
		$sql='Select * from `invty_2sale` where TxnID='.$txnid;
		$msg='&closeddata=true';
	}
	
        if ($_SESSION['(ak0)']==1002) {echo $sql;}
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditsale.php?txntype=".$_REQUEST['txntype']."&TxnID=".$txnid.$msg);
	break;
case 'AddLostSale':
	$clientno=getNumber('Client',addslashes($_POST['Client']));
	$sqlinsert='INSERT INTO `invty_6lostsales` SET  ClientNo='.$clientno.', ';
        $sql='';
        $columnstoadd=array('ItemCode', 'Qty', 'Remarks');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.'Date=\'' . date("Y-m-d"). '\', BranchNo=\''.$_SESSION['bnum'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:lostsaleentry.php?edit=0");
        break;
case 'EditLostSale':
	$txnid=intval($_REQUEST['TxnID']);
	$clientno=getNumber('Client',addslashes($_POST['Client']));
	$sqlupdate='UPDATE `invty_6lostsales` SET  ClientNo='.$clientno.', ';
        $sql='';
        $columnstoadd=array('ItemCode', 'Qty', 'Remarks');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() WHERE TxnID='.$txnid;
	
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:lostsaleentry.php?edit=0");
        break;
case 'DelLostSale':
	$txnid=intval($_REQUEST['TxnID']);
	
	$sql='Delete from `invty_6lostsales` where TxnID='.$txnid.' and `Date`=\'' . date("Y-m-d"). '\' and BranchNo=\''.$_SESSION['bnum'].'\' and EncodedByNo=\''.$_SESSION['(ak0)'].'\'';
	echo "<font color='red'>Deletions can be done for today's entries only, and by the person who entered it.</font>";
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:lostsaleentry.php?edit=0");
        break;



case 'ApproveSP':
	if(!allowedToOpen(6929,'1rtc')){ echo 'No permission'; exit;}
	$txnid=intval($_REQUEST['TxnID']); $txnsubid=$_REQUEST['TxnSubId'];
	$stmt=$link->query('Select ItemCode from invty_2salesub where TxnSubId='.$txnsubid);
	$resultitem=$stmt->fetch();
	$sql='Update invty_7specdisctapproval Set Approved=1, SpecPriceApproved=SpecPriceRequest, ApprovedByNo=\''.$_SESSION['(ak0)'].'\', ApprovedTS=Now() where ItemCode='.$resultitem['ItemCode'] . ' and TxnID='.$txnid.' and Approved=0 ';
	//echo $sql;
	$stmt=$link->prepare($sql); $stmt->execute();
	$sql='Update invty_2salesub s join invty_7specdisctapproval a on s.TxnID=a.TxnID and s.ItemCode=a.ItemCode Set s.UnitPrice=a.SpecPriceApproved, s.EncodedByNo=\''.$_SESSION['(ak0)'].'\', s.TimeStamp=Now() where s.TxnSubId='.$_REQUEST['TxnSubId']; 
	//echo $sql;
        $stmt=$link->prepare($sql); $stmt->execute(); 
        include_once 'trailinvty.php'; recordtrail($txnsubid,'invty_2salesub',$link,0); 
	header("Location:addeditsale.php?txntype=".$_GET['txntype']."&specprice=1&TxnID=".$txnid.$msg);
	break;
case 'RemoveSP':
	if(!allowedToOpen(6929,'1rtc')){ echo 'No permission'; exit;}
	$txnid=intval($_REQUEST['TxnID']);
	if (editOk('invty_2sale',$txnid,$link,$_REQUEST['txntype'])){
	$sql='Delete from `invty_7specdisctapproval` where TxnID='.$txnid;
	} else {
		$sql='Select * from `invty_2sale` where TxnID='.$txnid;
		$msg='?closeddata=true';
	}
	//echo $sql; break;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditsale.php?txntype=".$_GET['txntype']."&TxnID=".$txnid.$msg);
	break;
	
        }
 $stmt=null; $link=null;
?>