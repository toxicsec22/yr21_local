<?php
$path=$_SERVER['DOCUMENT_ROOT'];
require_once $path.'/acrossyrs/logincodes/confirmtoken.php';  
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once "../generalinfo/lists.inc";
include_once('trailquote.php');
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

$user=$_SESSION['(ak0)'];
$group=$_SESSION['&pos'];

function appendtotable($lastfield,$columnnames,$tablename,$link,$user,$catid,$clientno,$branchno){
	$nooffields=count($columnnames);
        $sql=' `BranchNo`='.$branchno.', `ForClientName`='.$clientno.', `Category`='.$catid.', CanvassRequestedByNo='.$user.', CanvassRequestTS=\''.date('Y-m-d h:m:s').'\', ';
        for ($row = 0; $row <  $nooffields; $row++) {
            $sql=$sql. ((is_null($_POST[$columnnames[$row]]) or $_POST[$columnnames[$row]]==='')?null:'`'. $columnnames[$row] . '`=\'' .$_POST[$columnnames[$row]] . '\'' . ($columnnames[$row]==$lastfield?';':', '));
	}
        $sql='insert into `'. $tablename. '` set ' . $sql;
            if ($_SESSION['(ak0)']==1002) {echo $sql;}
	    $stmt=$link->prepare($sql);
        $stmt->execute();
}

function edittable($lastfield,$columnnames,$tablename,$link,$user,$catid,$clientno){
	$nooffields=count($columnnames);
        $sql='`ForClientName`='.$clientno.', `Category`='.$catid.', ';
        for ($row = 0; $row <  $nooffields; $row++) {
            $sql=$sql.'`'. $columnnames[$row] . '`=\'' . ((is_null($_POST[$columnnames[$row]]) or $_POST[$columnnames[$row]]==='')?0:$_POST[$columnnames[$row]]) . '\'' . ($columnnames[$row]==$lastfield?'':', ');
	    //$sql=$sql. ('`'. $columnnames[$row] . '`=\'' .$_POST[$columnnames[$row]] . '\'' . ($columnnames[$row]==$lastfield?';':', '));
            }
	    $sql='update `'. $tablename. '` set '.$sql .' WHERE `CanvassID`='.$_REQUEST['CanvassID'];
	   //if ($_SESSION['(ak0)']==1002) {echo $sql; exit();}
        $stmt=$link->prepare($sql);
        $stmt->execute();
}

switch ($_REQUEST['calledfrom']){
case 1: //newcanvass.php
	$columnnames=array();
	$catid=getValue($link,'invty_1category','Category',$_POST['Category'],'CatNo');
	$clientno=getValue($link,'acctg_1clientsperbranch','ClientName',$_POST['ForClientName'],'ClientNo');
	$branchno=getValue($link,'1branches','Branch',$_POST['ForBranch'],'BranchNo');
	$columnnames=array('CanvassDate','ItemCode','Description');
        appendtotable('Description',$columnnames,'quotations_2canvass',$link,$user,$catid,$clientno,$branchno);
        header("Location:canvassperday.php?edit=1&perday=1");
        break;
case 2: //canvassperday.php edit=2
	$columnnames=array();
	
	$catid=getValue($link,'invty_1category','Category',$_POST['Category'],'CatNo');
	
	if (is_integer($_POST['ForClientName'])){
	$clientno=getValue($link,'acctg_1clientsperbranch','ClientName',$_POST['ForClientName'],'ClientNo');
	} else {
		$clientno=$_POST['ForClientName'];
	}
	
	$branchno=getValue($link,'1branches','Branch',$_POST['ForBranch'],'BranchNo');
	 
        if (allowedToOpen(62691,'1rtc')){
			$columnnames=array('CanvassDate','ItemCode','Description','UnitCost','SellingPrice','QuotedPrice','PONo','SupplierNo','Go','Ordered?','Delivered?','DeliveredByNo','Sold?','InvNo');
        $lastfield='InvNo';}
        elseif (allowedToOpen(62692,'1rtc')){
                    	$columnnames=array('CanvassDate', 'ItemCode','Description','QuotedPrice','Go','Sold?','InvNo');
                        $lastfield='InvNo';}
        elseif (allowedToOpen(62693,'1rtc')){
			$columnnames=array('Go','Sold?');
                        $lastfield='Sold?';}
        elseif (allowedToOpen(62694,'1rtc')){
                $columnnames=array('Delivered?','DeliveredByNo','DeliveryEnteredByNo');
                $lastfield='DeliveryEnteredByNo'; }
        else { $columnnames=array();  }
        
	edittable($lastfield,$columnnames,'quotations_2canvass',$link,$user,$catid,$clientno,$branchno);
	header("Location:canvassperday.php?edit=1&perday=".$_REQUEST['perday']);
	break;
case 3: //canvassperday.php delete
	$txnid=$_GET['CanvassID'];
	$sql='Delete from `quotations_2canvass` where CanvassID='.$txnid;
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:canvassperday.php?edit=1&perday=".$_REQUEST['perday']);
	break;
case 4: //newcanvass.php new quote
	if(date('Y', strtotime($_POST['QuoteDate']))<>$currentyr){header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	break;	}
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        $clientno=comboBoxValue($link,'`1clients`','`ClientName`',$_REQUEST['ClientName'],'ClientNo');
        if($clientno!==false and $clientno<>0 and !empty($clientno)) { $sqlupdate=' `ClientNo`='.$clientno.', ';} else { $sqlupdate='';}
	$sqlinsert='INSERT INTO `quotations_2quotemain` SET ';
	$sql='';
        $columnstoadd=array('QuoteDate','ClientName','ContactPerson','Position','SirMaam','FaxNo','Warranty','Payment','Note1','Note2','Note3');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.$sqlupdate.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();'; 
	// echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select QuoteID from `quotations_2quotemain` where QuoteDate=\''.$_POST['QuoteDate'].'\' and ClientName=\''.$_POST['ClientName'].'\' ORDER BY QuoteID DESC LIMIT 1';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	header("Location:addeditquote.php?QuoteID=".$result['QuoteID']);
        break;
case 5: //addeditquote.php add quotesub
	$txnid=$_REQUEST['QuoteID'];
	$sqldesc='Select c.CatNo, c.Category, i.ItemDesc, i.Unit from `invty_1items` i join `invty_1category` c on c.CatNo=i.CatNo where i.ItemCode='.$_POST['ItemCode'];
	$stmt=$link->query($sqldesc);
	$result=$stmt->fetch();
	$sqlinsert='INSERT INTO `quotations_2quotesub` SET `QuoteID`=\''.$txnid.'\', Category='.$result['CatNo'].', Description=\''.$result['Category'].', '.$result['ItemDesc'].' '.$_POST['Additional_Description'].'\', Unit=\''.$result['Unit'].'\', ';
        $sql='';
        $columnstoadd=array('ItemCode','Qty','UnitPrice');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
	}
	
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
	// echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditquote.php?QuoteID=".$txnid);
		   
	break;
case 6: // addeditquote.php edit main
	if(date('Y', strtotime($_POST['QuoteDate']))<>$currentyr){ header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); break;	}
	$txnid=$_REQUEST['QuoteID'];
        recordtrail($txnid,'quotations_2quotemain',$link,0);
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        $clientno=comboBoxValue($link,'`1clients`','`ClientName`',$_REQUEST['ClientName'],'ClientNo');
        if($clientno!==false and $clientno<>0 and !empty($clientno)) { $sqlclientno=' `ClientNo`='.$clientno.', ';} else { $sqlclientno='';}
	$sqlupdate='UPDATE `quotations_2quotemain` SET  ';
        $sql='';
        $columnstoedit=array('QuoteDate','ClientName','ContactPerson','Position','SirMaam','FaxNo','Warranty','Payment','Note1','Note2','Note3');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.$sqlclientno.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where QuoteID='.$txnid; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:addeditquote.php?QuoteID=".$txnid);
        break;

case 7: // addeditquote.php edit sub
	$txnid=$_REQUEST['QuoteID'];
	$txnsubid=$_REQUEST['QuoteSubID'];
        recordtrail($txnsubid,'quotations_2quotesub',$link,0);
	$sqlupdate='UPDATE `quotations_2quotesub` SET  QuoteID='.$txnid.', ';
        $sql='';
        $columnstoedit=array('Description','Qty','Unit','UnitPrice');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where QuoteSubID='.$txnsubid; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	header("Location:addeditquote.php?QuoteID=".$txnid);
        break;

case 8: // addeditquote.php delete main
	$txnid=$_REQUEST['QuoteID'];
        recordtrail($txnid,'quotations_2quotemain',$link,1);
        recordtrailallsub($txnid,'quotations_2quotemain',$link);
	$sql='Delete from `quotations_2quotemain` where QuoteID='.$txnid;
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:quotespermonth.php");
	break;
case 9: // addeditquote.php delete sub
	$txnid=$_REQUEST['QuoteID'];
	$txnsubid=$_REQUEST['QuoteSubID'];
        recordtrail($txnsubid,'quotations_2quotesub',$link,1);
	$sql='Delete from `quotations_2quotesub` where QuoteSubID='.$txnsubid;
	//echo $sql;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditquote.php?QuoteID=".$txnid);
	break;	
default:
	goto goback;
	break;
}
goback:
     $link=null; $stmt=null;
//header("Location:".$_SERVER['HTTP_REFERER']);
?>