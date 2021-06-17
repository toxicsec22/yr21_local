<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
        // check if allowed
        $allowed=array(999,5971,515,5151,5153,514,5992,5993,5931,595);$allow=0;
        foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
        if ($allow==0) { echo 'No permission'; exit;}
        allowed:
        // end of check
        
	 
	
	include_once('../backendphp/functions/editok.php');
	include_once('../backendphp/functions/getnumber.php');
	include_once 'trailacctg.php';
         $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
        
        $txnid=intval($_GET['TxnID']);
        $whichqry=$_GET['w'];
switch ($whichqry){
	
case 'SaleMainEdit':
	if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5971;}
        if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;}
        $table='acctg_2salemain';
	include('../backendphp/functions/checkeditablemainacctg.php');
	//to check if editable
	if (editOk($table,$txnid,$link,$whichqry)){
	recordtrail($txnid,$table,$link,0);
	$sqlupdate='UPDATE `'.$table.'` SET ';
        $sql='';
        $columnstoedit=array('Date','BranchNo','Remarks','TeamLeader');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where Posted=0 and TxnID='.$txnid; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	} 
	header("Location:addeditclientside.php?w=Sale&TxnID=".$txnid);
        break;

case 'SaleSubEdit':
	if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5971;}
        if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;}
	//to check if editable
	if (editOk('acctg_2salemain',$txnid,$link,$whichqry)){
	$txnsubid=$_GET['TxnSubId']; $table='acctg_2salesub';
        recordtrail($txnsubid,$table,$link,0);
	// to get client no
	$clientno=getNumber('ClientEmployee',substr(addslashes($_POST['ClientName']),0,20));
	$acctdrid=getNumber('Account',addslashes($_POST['DebitAccount'])); $acctcrid=getNumber('Account',addslashes($_POST['CreditAccount']));
	
	$sqlupdate='UPDATE `acctg_2salesub` SET  TxnID='.$txnid.', ClientNo='.$clientno.', DebitAccountID='.$acctdrid.',  CreditAccountID='.$acctcrid.', ';
        $sql='';
	
	$columnstoedit=array('Particulars','Amount');	
	
	foreach ($columnstoedit as $field) { $sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; }
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where TxnSubId='.$txnsubid; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	}
	header("Location:addeditclientside.php?w=Sale&TxnID=".$txnid);
        break;

case 'CollectMainEdit':
        if (!allowedToOpen(5151,'1rtc')) { echo 'No permission'; exit; }
        prcollectmainedit:
        $columnstoedit=array('Date','CheckBank','CheckNo','CheckBRSTN','ClientCheckBankAccountNo','DateofCheck','Remarks','ReceivedBy');
        $table='acctg_2collectmain'; if(!in_array($_POST['Type'],array(1,2,3,4,5))){$type=1;} else {$type=$_POST['Type']; $typefield=' Type='.$type.', ';}
        array_unshift($columnstoedit,'CollectNo');
        
		// $date = $_POST['Date']; echo $date;
        // print_r($_POST);
        //to check if editable
	include ('acctglayout/checkifacctgeditable.php');
        //to check if editable
	if (editOk($table,$txnid,$link,$whichqry)){
            recordtrail($txnid,$table,$link,0);
	// to get client no
	$clientno=getNumber('Client',substr(addslashes($_POST['Client']),0,20));
        $sql0='SELECT Terms FROM `1clients` WHERE ClientNo='.$clientno;
        $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
		$addldays=($res0['Terms']<=1?0:7);
        if(($_POST['DateofCheck']=='') OR ($stmt0->rowCount()==0)){ $dateofcheck=date("Y-m-d"); } 
        // elseif ($_POST['DateofCheck']>date('Y-m-d', strtotime($date. ' + '.($res0['Terms']+7).' days'))){  
        elseif ($_POST['DateofCheck']>date('Y-m-d', strtotime(date('Y-m-d'). ' + '.($res0['Terms']+$addldays).' days'))){  
            if (allowedToOpen(5154,'1rtc')) { $dateofcheck=$_POST['DateofCheck'];}
            else {
            echo '<title>Error!</title><h4><font color=red>The date of check is beyond terms.</font></h4></head>'
            . '<a href='.$_SERVER['HTTP_REFERER'].'>Go back</a>'; exit; }
            }
        else { $dateofcheck=$_POST['DateofCheck'];}
      
	$acctid=getNumber('Account',addslashes($_POST['DebitAccount']));
        
	$sqlupdate='UPDATE `'.$table.'` SET  ClientNo='.$clientno.', DebitAccountID='.$acctid.', '.$typefield;
        $sql='';
        
       if (allowedToOpen(6003,'1rtc')) { $columnstoedit[]='BranchSeriesNo'; $sqlcondition=''; } else { $sqlcondition=' AND EncodedByNo=\''.$_SESSION['(ak0)'].'\'';}
	foreach ($columnstoedit as $field) { $sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; }
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where Posted=0 and TxnID='.$txnid.$sqlcondition; 
	if($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
	} 
	header("Location:addeditclientside.php?w=".substr($whichqry,0,-8)."&TxnID=".$txnid);
        break;

case 'ProvMainDel':
    $canvass=explode(",",$_GET['Canvass']);
            foreach($canvass as $canvassid){
            $sqlupdate='UPDATE `quotations_2canvass` SET Downpayment=0, DPAmtByNo='.$_SESSION['(ak0)'].', DPAmtTimeStamp=Now() WHERE CanvassID='.$canvassid;
            if($_SESSION['(ak0)']==1002) {echo $sqlupdate; }
            $link->query($sqlupdate); 
            
            }
        
        header("Location:txnsperday.php?perday=0&w=Prov");
    break;
    
case 'ProvSubDel':
         $sqlupdate='UPDATE `quotations_2canvass` SET Downpayment=0, DPAmtByNo='.$_SESSION['(ak0)'].', DPAmtTimeStamp=Now() WHERE CanvassID='.$_GET['CanvassID'];
            echo $sql1.'<br>'.$sqlupdate;  $link->query($sqlupdate);
        header("Location:addeditclientside.php?w=Prov&TxnID=".$txnid);
    break;
        
        
case 'CollectSubEdit':
case 'CollectDeductSubEdit':
    if (!allowedToOpen(5151,'1rtc')) { echo 'No permission'; exit; }
    
	//to check if editable
	if (editOk('acctg_2collectmain',$txnid,$link,$whichqry)){
	if($whichqry=='CollectDeductSubEdit'){
		$table='acctg_2collectsubdeduct'; $columnstoedit=array('DeductDetails','Amount'); $acct='DebitAccount';
		}else { $table='acctg_2collectsub'; $columnstoedit=array('OtherORDetails','Amount'); $acct='CreditAccount';}
	$txnsubid=$_GET['TxnSubId']; recordtrail($txnsubid,$table,$link,0);
        
		//Downpayment in canvass
		$sql0='SELECT Type FROM `acctg_2collectmain` WHERE TxnID='.$txnid; $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
        if($res0['Type']==5) { $columnstoedit=array('Amount');
            $sqlupdate='UPDATE `quotations_2canvass` SET Downpayment='.$_POST['Amount'].', DPAmtByNo='.$_SESSION['(ak0)'].', DPAmtTimeStamp=Now() WHERE CanvassID=(SELECT REPLACE(OtherORDetails,"CanvassID ","") AS CanvassID  FROM acctg_2collectsub WHERE TxnSubId='.$txnsubid.')';
			$link->query($sqlupdate);
        }
		
		
	$sqlupdate='UPDATE `'.$table.'` SET  ';
        $sql=''; 
	if (allowedToOpen(5152,'1rtc')){
		$columnstoedit[]='BranchNo'; 
                
                if (allowedToOpen(5992,'1rtc')){ $acctid=getNumber('Account',addslashes($_POST[$acct])); $sqlupdate.=$acct.'ID='.$acctid.', '; }
                
		$sqlcondition='';
                } else { $sqlcondition=' AND EncodedByNo=\''.$_SESSION['(ak0)'].'\'';}
        foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where TxnSubId='.$txnsubid.$sqlcondition; 
	if($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
        
	}
	header("Location:addeditclientside.php?w=Collect&TxnID=".$txnid);
        break;

case 'DepMainEdit':
	if (!allowedToOpen(514,'1rtc')) { echo 'No permission'; exit; }
	include ('acctglayout/checkifacctgeditable.php');
	//to check if editable
        $table='acctg_2depositmain';
	if (editOk($table,$txnid,$link,$whichqry)){
            recordtrail($txnid,$table,$link,0);
	$acctid=getNumber('Account',addslashes($_POST['DebitAccount']));
	$sqlupdate='UPDATE `'.$table.'` SET  DebitAccountID='.$acctid.', ';
        $sql='';
        $columnstoedit=array('DepositNo','Date','Remarks');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where Posted=0 and TxnID='.$txnid; 
	//echo $sql;
        $stmt=$link->prepare($sql); $stmt->execute();
	}
	header("Location:addeditdep.php?TxnID=".$txnid);
        break;

case 'DepSubEdit':
    if (!allowedToOpen(514,'1rtc')) { echo 'No permission'; exit; }
	//to check if editable
	if (editOk('acctg_2depositmain',$txnid,$link,$whichqry)){
		
	$clientno=isset($_POST['Client'])?getNumber('ClientEmployee',substr(($_POST['Client']),0,20)):10000;
	$txnsubid=$_REQUEST['TxnSubId']; recordtrail($txnsubid,'acctg_2depositsub',$link,0);
	$acctid=isset($_POST['CreditAccount'])?getNumber('Account',addslashes($_POST['CreditAccount'])):100;
	$acctid=empty($_POST['ForChargeInvNo'])?$acctid:200;
	$invno=empty($_POST['ForChargeInvNo'])?'null':'\''.$_POST['ForChargeInvNo'].'\'';
	$sql='Select CreditAccountID, Type from acctg_2depositsub where TxnSubId='.$txnsubid;
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	if ($result['CreditAccountID']==201 or $result['Type']==1){
		$credit=','; $branchno='';
		$columnstoedit=array('DepDetails');
	} else {
		$credit=', ClientNo='.$clientno.', CreditAccountID='.$acctid.', ForChargeInvNo='.$invno.',';
		if (allowedToOpen(5992,'1rtc')){
		$columnstoedit=array('DepDetails','Type','CheckNo','Amount','Forex');
                $branchno=', BranchNo='.(getNumber('Branch',$_POST['Branch']));
				
		} else {
			$columnstoedit=array('Amount'); $branchno='';
		}
	}
	
	$sqlupdate='UPDATE `acctg_2depositsub` SET  TxnID='.$txnid.$branchno.$credit;
        $sql='';
               
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	// print_r($_POST);
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where TxnSubId='.$txnsubid; 
	// echo $sql; exit();
	 if($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql);
	$stmt->execute();
	}
	header("Location:addeditdep.php?TxnID=".$txnid);
        break;

case 'DepEncashSubEdit':
    if (!allowedToOpen(514,'1rtc')) { echo 'No permission'; exit; }
	
    	//to check if editable
	if (editOk('acctg_2depositmain',$txnid,$link,$whichqry)){
		
	$acctid=getNumber('Account',addslashes($_POST['DebitAccount']));
	$txnsubid=$_REQUEST['TxnSubId']; recordtrail($txnsubid,'acctg_2depencashsub',$link,0);
	$sqllookup='Select ApprovalNo from `acctg_2depencashsub` where not isnull(ApprovalNo) and ApprovalNo<>0 and TxnSubId='.$txnsubid; $stmt=$link->query($sqllookup);
	$resultlookup=$stmt->fetch();
	
	if ($stmt->rowCount()>0){	
	$approvalno=$resultlookup['ApprovalNo'];
	
	} else {
		$sqllookup2='Select TypeID from `acctg_2depencashsub` where (isnull(ApprovalNo) or ApprovalNo=0) and TxnSubId='.$txnsubid; $stmt2=$link->query($sqllookup2);
		$resultlookup2=$stmt2->fetch();
		if ($stmt2->rowCount()>0){$approvalno=0;$typeid=$resultlookup2['TypeID'];}
	}
	if (isset($_POST['TIN']) AND !empty($_POST['TIN'])){$tin=' TIN=\''.str_replace("-","",$_POST['TIN']).'\', ';} else { $tin='';}
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	
	$fbosql='';
	if(allowedToOpen(5994,'1rtc'))
	{
		$frombudgetof=companyandbranchValue($link, 'acctg_1budgetentities', 'Entity', $_POST['FromBudgetOf'], 'EntityID');
		$fbosql=',FromBudgetOf='.$frombudgetof.'';
	}
		
		$sqlbd='';
		if($_REQUEST['BudgetDesc']<>''){
			$type=comboBoxValue($link, 'acctg_1branchpreapprovedbudgetlist', 'BudgetDesc', $_REQUEST['BudgetDesc'], 'TypeID');
			$sqlbd='TypeID=\''.$type.'\',';
		}
		 $branchno=getNumber('Branch',addslashes($_POST['Branch']));
	$sqlupdate='UPDATE `acctg_2depencashsub` SET  TxnID='.$txnid.', '.$sqlbd.'DebitAccountID='.$acctid.',BranchNo='.$branchno.', '.$tin;
        $sql='';
        $columnstoedit=array('EncashDetails','Amount');
        if (allowedToOpen(5993,'1rtc')){
		$encode=true;
		}else {require ('sqlphp/checkapprovedamtandbudget.php'); }
		
	if ($encode==true){ goto encode;} else {goto skip;}
	encode:
		
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()  '.$fbosql.' where TxnSubId='.$txnsubid; 
	// echo $sql; exit();
        $stmt=$link->prepare($sql);
	$stmt->execute();
	}
	skip:
	header("Location:addeditdep.php?TxnID=".$txnid.(isset($msg)?"&msg=".$msg:""));
        break;


case 'BouncedfromCREdit':
    if (!allowedToOpen(5931,'1rtc')) { echo 'No permission'; exit; }
	if(isset($_GET['fromlast'])){
		$tbname='acctg_3undepositedpdcfromlastperiodbounced'; $pk='UndepPDCId'; 
		$addlurl='&fromlast=1';
	} else {
		$tbname='acctg_2collectsubbounced'; $pk='TxnID'; $addlurl='';
	}
	
    if (editOk($tbname,$txnid,$link,$whichqry)){
	$txnid=$_GET['TxnID']; recordtrail($txnid,$tbname,$link,0);
        $sql=''; 
        $columnstoedit=array('DateBounced','CreditAccountID','Remarks','DateofFirstInv');
		
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql='UPDATE `'.$tbname.'` SET  '.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() WHERE '.$pk.'='.$txnid;
        $stmt=$link->prepare($sql); $stmt->execute();
	}
    header("Location:addeditclientside.php?w=BouncedfromCR".$addlurl."&TxnID=".$txnid);
    break;
        
 case 'TxfrMainEdit':
	if (!allowedToOpen(5951,'1rtc')) { echo 'No permission'; exit; }
	include('../backendphp/functions/checkeditablemainacctg.php');
        $table='acctg_2txfrmain';
	//to check if editable
	if (editOk($table,$txnid,$link,$whichqry)){
        recordtrail($txnid,$table,$link,0);
	$acctid=getNumber('Account',addslashes($_POST['CreditAccount']));	
	$sqlupdate='UPDATE `acctg_2txfrmain` SET  CreditAccountID='.$acctid.', ';
        $sql='';
        $columnstoedit=array('Date','FromBranchNo','Remarks');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where Posted=0 and TxnID='.$txnid; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	} 
	header("Location:addeditclientside.php?w=Interbranch&TxnID=".$txnid);
        break;

case 'TxfrSubEdit':
	if (!allowedToOpen(5951,'1rtc')) { echo 'No permission'; exit; }
	//to check if editable
	if (editOk('acctg_2txfrmain',$txnid,$link,$whichqry)){
	$acctid=getNumber('Account',addslashes($_POST['DebitAccount']));
	$txnsubid=$_GET['TxnSubId']; recordtrail($txnsubid,'acctg_2txfrsub',$link,0);
	$sqlupdate='UPDATE `acctg_2txfrsub` SET  TxnID='.$txnid.', DebitAccountID='.$acctid.', ';
        $sql='';
	
        $columnstoedit=array('ClientBranchNo','Particulars','DateIN','Amount','Remarks');
	
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' OUTEncodedByNo=\''.$_SESSION['(ak0)'].'\', OUTTimeStamp=Now() where TxnSubId='.$txnsubid; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	}
	header("Location:addeditclientside.php?w=Interbranch&TxnID=".$txnid);
        break;
  
        }
 $link=null; $stmt=null;
?>