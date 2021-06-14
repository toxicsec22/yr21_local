<?php
include_once('monitorinvlinks.php');
// echo $_SESSION['bnum'];


switch($which){
    case 'ReceiveNewPrint': 
        if (!allowedToOpen(784,'1rtc')) { echo 'No permission'; exit;}    
    $title='Receive Newly Printed'; $formdesc='<br><font color=blue>Series Number</font> - actual printed number on every receipt/form. <br><font color=blue>Booklet Number</font> - number written outside the booklet for monitoring and counting.<br>'; $fieldsinrow=3;
    $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>15,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'Supplier','type'=>'text','size'=>20,'required'=>true,'list'=>'suppliers', 'value'=>"'A & C Printers'"),
                    array('field'=>'InvType', 'caption'=>'Invoice Type:', 'type'=>'text','size'=>15,'required'=>true,'list'=>'InvType'),
                    array('field'=>'Prefix?', 'type'=>'text','size'=>10, 'required'=>false, 'value'=>null),
		    array('field'=>'SeriesFrom', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'SeriesTo', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'BookletNo', 'caption'=>'Start of booklet number', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'Branch', 'type'=>'text','size'=>15, 'required'=>true, 'list'=>'branchnames'),
                    array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false)                    
                    );
    echo comboBox($link,'SELECT `txntypeid`,`txndesc` FROM `invty_0txntype` WHERE `txntypeid` IN (1,2, 4,5,10,11,29,30,41);','txntypeid','txndesc','InvType');
    $action='fromsupplier.php?which=Receive';
    $liststoshow=array('suppliers','branchnames');
     include('../backendphp/layout/inputmainform.php');

        break;

case 'Receive':  
case 'AddlReceive': 
        if (!allowedToOpen(784,'1rtc')) { echo 'No permission'; exit;}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/commonfunctions/extractnumfromstring.php';
        //to check if editable
	if(($_POST['Date'])<$_SESSION['nb4A']  or date('Y', strtotime($_POST['Date']))<>$currentyr){	header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); break; }
		$invtype=getValue($link,'invty_0txntype','txndesc',urldecode($_POST['InvType']),'txntypeid');
        if ($which=='AddlReceive'){
        $txnid=$_REQUEST['TxnID']; 
        } else {
		$suppno=getValue($link,'1suppliers','SupplierName',addslashes($_POST['Supplier']),'SupplierNo');
        $branchno=getValue($link,'1branches','Branch',addslashes($_POST['Branch']),'BranchNo');
        $sql='';
        $columnstoadd=array('Date','Remarks');
	foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; }
	$sql='INSERT INTO `monitor_2fromsuppliermain` SET SupplierNo='.$suppno.',BranchNo='.$branchno.', InvType='.$invtype.', '.$sql.'EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';  //echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();	
        //get txnid
        $sql='SELECT TxnID FROM `monitor_2fromsuppliermain` WHERE SupplierNo='.$suppno.' AND BranchNo='.$branchno.' AND InvType='.$invtype.' AND Date=\''.$_POST['Date'].'\'';
        $stmt=$link->query($sql); $result=$stmt->fetch(); $txnid=$result['TxnID']; //echo $sql;       
        }
        $firstseries=is_numeric($_POST['SeriesFrom'])?$_POST['SeriesFrom']:extractnumfromstring($_POST['SeriesFrom']); $lastseries=is_numeric($_POST['SeriesTo'])?$_POST['SeriesTo']:extractnumfromstring($_POST['SeriesTo']); //echo (ceil(100/50)*50).'<br>'.$lastseries; break;
        $prefix=$_POST['Prefix?'];
        $encodeseries=$firstseries; $bookletno=$_POST['BookletNo'];
        while ($encodeseries<$lastseries){
            $sql='INSERT INTO `monitor_2fromsuppliersub` SET TxnID='.$txnid.', `SeriesFrom`="'.$prefix.$encodeseries.'", `BookletNo`='.$bookletno.', InvType='.$invtype.', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; //echo $sql;
            $stmt=$link->prepare($sql); $stmt->execute(); 
            $encodeseries=(ceil(($encodeseries+1)/50)*50)+1;
            $bookletno=$bookletno+1;
        } 
        header('Location:fromsupplier.php?which=ListReceived&TxnID='.$txnid);
        
    break;

case 'ListReceived':
	if (!allowedToOpen(784,'1rtc')) { echo 'No permission'; exit;}
    $txnid=intval($_REQUEST['TxnID']); $title='Received Booklets'; $formdesc='<br><font color=blue>Series Number</font> - actual printed number on every receipt/form. <br><font color=blue>Booklet Number</font> - number written outside the booklet for monitoring and counting.<br><br><a href="fromsupplier.php?which=ReceiveNewPrint"  target=_blank>Receive Newly Printed</a><i>';
    $sqlmain='SELECT `TxnID`, `Date`, `SupplierName`, `Branch`, `txndesc` as InvoiceType, `Remarks`, concat(e.`Nickname`," ",e.`SurName`) as EncodedBy, fm.`TimeStamp`
FROM `monitor_2fromsuppliermain` fm JOIN `1suppliers` s ON s.SupplierNo=fm.SupplierNo
JOIN `1branches` b ON fm.BranchNo=b.BranchNo
JOIN `1employees` e ON e.IDNo=fm.EncodedByNo 
JOIN `invty_0txntype` tt on tt.txntypeid=fm.InvType
WHERE TxnID='.$txnid;
$stmt=$link->query($sqlmain); $result=$stmt->fetch();
    $sqlsub='SELECT TxnSubId, `SeriesFrom`, CEIL(`SeriesFrom`/50)*50 AS SeriesTo, `BookletNo`, `DateTransferred`, `DateIssued`, b.Branch as `IssuedToBranch`, concat(e.`Nickname`," ",e.`SurName`) as EncodedBy, fs.`TimeStamp`, `DateAccepted`,
    concat(e1.`Nickname`," ",e1.`SurName`) as IssuedBy, IssuedTS, `txndesc` AS InvoiceType
FROM `monitor_2fromsuppliersub` fs JOIN `1employees` e ON e.IDNo=fs.EncodedByNo JOIN `invty_0txntype` tt on tt.txntypeid=fs.InvType
LEFT JOIN `1employees` e1 ON e1.IDNo=fs.IssuedByNo
LEFT JOIN `1branches` b ON fs.IssuedTo=b.BranchNo WHERE TxnID='.$txnid.' ORDER BY BookletNo';
    $columnnamesmain=array('Date', 'SupplierName', 'Branch', 'InvoiceType', 'Remarks', 'EncodedBy', 'TimeStamp');
    $columnsub=array('SeriesFrom','SeriesTo','BookletNo','InvoiceType','DateTransferred','DateIssued', 'IssuedToBranch', 'DateAccepted', 'EncodedBy', 'TimeStamp', 'IssuedBy', 'IssuedTS');
    
    $editsub=true; $nopost=true; $withsub=true; $editprocess='fromsupplier.php?TxnID='.$txnid.'&which=EditSub&TxnSubId='; $editprocesslabel='Enter'; 
	
    $delprocess='fromsupplier.php?TxnID='.$txnid.'&which=DeleteSub&TxnSubId=';
    $txnsubid='TxnSubId';
    $columnnames=array(
                     array('field'=>'Prefix?', 'type'=>'text','size'=>10, 'required'=>false, 'value'=>null),
		     array('field'=>'SeriesFrom', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'SeriesTo', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'BookletNo', 'caption'=>'Start of booklet number', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'TxnID', 'type'=>'hidden','size'=>0, 'value'=>$txnid)
                    );
    
    $action='fromsupplier.php?which=AddlReceive';
    $liststoshow=array();
    $main=''; $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':''); 
    }
		array_push($columnnames,
			array('field'=>'InvType', 'type'=>'hidden','size'=>20, 'value'=>''.urlencode($result['InvoiceType']).'')
		);
		
    if(($result['Date'])>$_SESSION['nb4A']  or date('Y', strtotime($result['Date']))==$currentyr){
        $editok=true; $editmain='<td><a href=fromsupplier.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&which=EditSpecifics >Edit</a>&nbsp; &nbsp;&nbsp;<a href=fromsupplier.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&which=DeleteMain OnClick="return confirm(\'Really delete this? All entries in subform will be deleted\');">Delete</a></td>';
        $columnstoedit=array('SeriesFrom','BookletNo');
        } else { $editok=false; $editmain=''; $columnstoedit=array(); }
    
    $main='<table><tr>'.$main.$editmain.'<tr></table>';
    $main=$main.'<br>'.(isset($_GET['msg'])?'<br><b><font color="maroon">'.strtoupper($_GET['msg']).'</font></b><br>':'');
	
	include('../backendphp/layout/inputsubform.php');
	
	include_once('monitorinvnum.php');
	
    break;

case 'ReceivedSummary':

if (!allowedToOpen(783,'1rtc')) { echo 'No permission'; exit;}
    $title='Summary of Received Invoices';$formdesc='<a href="fromsupplier.php?which=ReceiveNewPrint"  target=_blank>Receive Newly Printed</a>';
	
    include_once('../backendphp/layout/clickontabletoedithead.php');
	$sql='SELECT `txntypeid`,`txndesc` FROM `invty_0txntype` WHERE `txntypeid` IN (1,2, 4,5,10,11,29,30,41) ORDER BY txndesc;';
	$stmt = $link->query($sql);
		
	$chooseinvtype=' Invoice Type: <select name="InvType"><option value="All">All Types</option>';
	while($row= $stmt->fetch()) {
		$chooseinvtype.='<option value="'.$row['txntypeid'].'">'.$row['txndesc'].'</option>';
	}
	$chooseinvtype.='</select>';
	
	echo '<form method="POST" action="fromsupplier.php?which=ReceivedSummary">';
	echo 'Branch: <select name="FilterBranch"><option value="1">All Branches</option><option value="0">Per Branch</option></select>';
	echo $chooseinvtype;
	
	echo ' Show Transferred?: <select name="Transferred"><option value="0">No</option><option value="1">Yes</option></select>';
	echo ' <input type="submit" name="btnSubmitMonth" value="Lookup">';
	echo '</form>';
	
	
	if (isset($_POST['btnSubmitMonth'])){
		$show = $_POST['FilterBranch'];
		$sqlcondition=($show==1?'':' WHERE fm.`BranchNo`='.$_SESSION['bnum'].' ');
		
		if ($_POST['InvType']<>'All'){
			$sqlcondition.=' AND InvType='.$_POST['InvType'].'';
		}
		
		$columnnames=array('Date','SupplierName','Branch', 'Remarks', 'InvoiceType', 'SeriesFrom', 'SeriesTo','BookletNoFrom','BookletNoTo','BookletStatus');  
		$sql='SELECT fm.`TxnID`,`Date`,`SupplierName`,`Branch`, `InvType`,`Remarks`, `txndesc` as `InvoiceType`,
		(SELECT COUNT(TxnSubId) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID AND TransferredToCentralWarehouse=0) AS ShowCount,		
		(SELECT Min(SeriesFrom) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID) AS SeriesFrom,
		(SELECT MAX(CEIL(`NumericOnly`(SeriesFrom)/50)*50) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID) AS SeriesTo, 
		(SELECT Min(BookletNo) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID) AS BookletNoFrom,
		(SELECT Max(BookletNo) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID) AS BookletNoTo,(SELECT GROUP_CONCAT(DISTINCT(
(CASE 
	WHEN Expired=0 THEN "Active"
    WHEN Expired=1 THEN "Expired"
    WHEN Expired=2 THEN "Retired"
    WHEN Expired=3 THEN "Discarded"
END)
) ORDER BY Expired) FROM monitor_2fromsuppliersub WHERE TxnID=fm.TxnID AND BookletNo BETWEEN (SELECT BookletNoFrom) AND (SELECT BookletNoTo)) AS BookletStatus,
		concat(`Nickname`," ",`SurName`) as `EncodedBy`, fm.`TimeStamp` FROM `monitor_2fromsuppliermain` fm
		JOIN `1branches` b ON b.BranchNo=fm.BranchNo
		JOIN `1employees` e ON e.IDNo=fm.EncodedByNo
		JOIN `1suppliers` s ON s.SupplierNo=fm.SupplierNo
		JOIN `invty_0txntype` tt ON tt.txntypeid=fm.InvType '.$sqlcondition.' '.($_POST['Transferred']==0?'HAVING ShowCount>0':'').';';

		
		$txnidname='TxnID'; $fieldname='TxnID';
		$process1='fromsupplier.php?which=ListReceived&';
		$processlabel1='Lookup';
		$process2='monitorinvsteps.php?which=TransferToCentralWarehouse&';
		$processlabel2='Transfer To Central';
		$title='';
		echo '<br/><h3>'.($show==1?'All Branches':'Per Branch').'</h3>';
		include_once('../backendphp/layout/clickontabletoeditbody.php');
		
	} 
    
    break;
	
case 'ReceivedSummaryCentral':

if (!allowedToOpen(78831,'1rtc') AND !allowedToOpen(78832,'1rtc')) { echo 'No permission'; exit;}
    // $title='Summary of Received Invoices';$formdesc='';
    $title='Issue To Branch';$formdesc='';
	
	
    include_once('../backendphp/layout/clickontabletoedithead.php');
	
	 include_once('../backendphp/layout/clickontabletoedithead.php');
	 $sqlbranch='SELECT `BranchNo`,`Branch` FROM `1branches` WHERE Active<>0 ORDER BY Branch;';
	$stmtbranch = $link->query($sqlbranch);
		
	$choosebranch=' Branches: <select name="BranchNo"><option value="All">All Branches</option>';
	while($row= $stmtbranch->fetch()) {
		$choosebranch.='<option value="'.$row['BranchNo'].'">'.$row['Branch'].'</option>';
	}
	$choosebranch.='</select>';
	
	$sql='SELECT `txntypeid`,`txndesc` FROM `invty_0txntype` WHERE `txntypeid` IN (1,2, 4,5,10,11,29,30,41) ORDER BY txndesc;';
	$stmt = $link->query($sql);
		
	$chooseinvtype=' Invoice Type: <select name="InvType"><option value="All">All Types</option>';
	while($row= $stmt->fetch()) {
		$chooseinvtype.='<option value="'.$row['txntypeid'].'">'.$row['txndesc'].'</option>';
	}
	$chooseinvtype.='</select>';
	
	echo '<form method="POST" action="fromsupplier.php?which=ReceivedSummaryCentral">';
	// echo 'Branch: <select name="FilterBranch"><option value="1">All Branches</option><option value="0">Per Branch</option></select>';
	echo $choosebranch;
	echo $chooseinvtype;
	echo ' <input type="submit" name="btnSubmitMonth" value="Lookup">';
	echo '</form>';
	
	
	if (isset($_POST['btnSubmitMonth'])){

		$show = $_POST['BranchNo'];
		$sqlcondition=($show=="All"?' WHERE 1=1':' WHERE fm.`BranchNo`='.$_POST['BranchNo'].' ');
		
		if ($_POST['InvType']<>'All'){
			$sqlcondition.=' AND InvType='.$_POST['InvType'].'';
		}
		
		
		$columnnames=array('Date','SupplierName','Branch', 'Remarks', 'InvoiceType', 'SeriesFrom', 'SeriesTo','BookletNoFrom','BookletNoTo','BookletStatus');  
		
		$sql='SELECT fm.`TxnID`,`Date`,`SupplierName`,`Branch`, `InvType`,`Remarks`, `txndesc` as `InvoiceType`,
		(SELECT COUNT(TxnSubId) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID AND ((IssuedByNo=0 OR IssuedByNo IS NULL) AND TransferredToCentralWarehouse<>0)) AS ShowCount,		
		(SELECT Min(SeriesFrom) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID) AS SeriesFrom,
		(SELECT MAX(CEIL(`NumericOnly`(SeriesFrom)/50)*50) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID) AS SeriesTo, 
		(SELECT Min(BookletNo) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID) AS BookletNoFrom,
		(SELECT Max(BookletNo) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID) AS BookletNoTo,
		(SELECT GROUP_CONCAT(DISTINCT(
(CASE 
	WHEN Expired=0 THEN "Active"
    WHEN Expired=1 THEN "Expired"
    WHEN Expired=2 THEN "Retired"
    WHEN Expired=3 THEN "Discarded"
END)
) ORDER BY Expired) FROM monitor_2fromsuppliersub WHERE TxnID=fm.TxnID AND BookletNo BETWEEN (SELECT BookletNoFrom) AND (SELECT BookletNoTo)) AS BookletStatus,
		concat(`Nickname`," ",`SurName`) as `EncodedBy`, fm.`TimeStamp` FROM `monitor_2fromsuppliermain` fm
		JOIN `1branches` b ON b.BranchNo=fm.BranchNo
		JOIN `1employees` e ON e.IDNo=fm.EncodedByNo
		JOIN `1suppliers` s ON s.SupplierNo=fm.SupplierNo
		JOIN `invty_0txntype` tt ON tt.txntypeid=fm.InvType '.$sqlcondition.' HAVING ShowCount>0;';
		$txnidname='TxnID'; $fieldname='TxnID';
		$process1='monitorinvsteps.php?which=IssueToBranch&';
		$processlabel1='Issue To Branch';
		
		$title='';
		include_once('../backendphp/layout/clickontabletoeditbody.php');
		
	} 
    
    break;
	
  case 'ReceivedSummaryBranch':
  
  if ((!allowedToOpen(array(781,78835,78836),'1rtc'))) { echo 'No permission'; exit;}
    $title='Accept Printed Invoices';$formdesc='';
	echo '<title>'.$title.'</title>';
		$columnnames=array('InvoiceType','SeriesFrom','SeriesTo');
		

		$sqlcondi='';
		if((allowedToOpen(array(78836,78836,78839),'1rtc'))){
			$sqlcondi=' AND fm.InvType IN (2,30,11)'; //Charge,SI,CR
		} else if((allowedToOpen(78835,'1rtc'))){
			$sqlcondi=' '; //CR
		}

		$sql='SELECT fm.`TxnID`,SeriesFrom,TxnSubId,IssuedTo,`Remarks`,`txndesc` as `InvoiceType`,CEIL(`SeriesFrom`/50)*50 AS SeriesTo FROM monitor_2fromsuppliermain fm JOIN `1branches` b ON b.BranchNo=fm.BranchNo JOIN `1employees` e ON e.IDNo=fm.EncodedByNo JOIN `1suppliers` s ON s.SupplierNo=fm.SupplierNo JOIN `invty_0txntype` tt ON tt.txntypeid=fm.InvType JOIN monitor_2fromsuppliersub fs ON fm.TxnID=fs.TxnID WHERE IssuedTo='.$_SESSION['bnum'].' AND (AcceptedByNo=0 OR AcceptedByNo IS NULL) '.$sqlcondi.' ORDER BY Date DESC';
		$txnidname='TxnSubId'; $fieldname='TxnSubId';
	
		$process1='monitorinvsteps.php?which=Accept&';
		$processlabel1='Accept';
		
		include_once('../backendphp/layout/clickontabletoeditbody.php');
		
    break;
	
	case 'SpecialTransfer':
	
	if (!allowedToOpen(784,'1rtc')) { echo 'No permission'; exit;}
    // $title='Summary of Received Invoices';$formdesc='';
    $title='Special Transfer';$formdesc='';
	
    include_once('../backendphp/layout/clickontabletoedithead.php');
	
	echo '<form method="POST" action="fromsupplier.php?which=SpecialTransfer">';
	// echo 'Month: <input type="number" min="1" max="12" name="Month" value="'.date("m").'"/>';
	echo 'Date FROM: <input type="date" name="DateFrom" value="'.date("Y-m-d").'"/> ';
	echo 'Date TO: <input type="date" name="DateTo" value="'.date("Y-m-d").'"/>';
	echo ' <input type="submit" name="btnSubmitDate" value="Lookup">';
	echo '</form>';
	
	if (isset($_POST['btnSubmitDate'])){

		$datefrom = $_POST['DateFrom'];
		$dateto = $_POST['DateTo'];

		$columnnames=array('Date','SupplierName','Branch', 'Remarks', 'InvoiceType', 'SeriesFrom', 'SeriesTo','BookletNoFrom','BookletNoTo');  
		$sql='SELECT fm.`TxnID`,`Date`,`SupplierName`,`Branch`, `InvType`,`Remarks`, `txndesc` as `InvoiceType`,	
		(SELECT Min(SeriesFrom) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID) AS SeriesFrom,
		(SELECT MAX(CEIL(`NumericOnly`(SeriesFrom)/50)*50) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID) AS SeriesTo, 
		(SELECT Min(BookletNo) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID) AS BookletNoFrom,
		(SELECT Max(BookletNo) FROM `monitor_2fromsuppliersub` fs WHERE fm.TxnID=fs.TxnID) AS BookletNoTo,
		concat(`Nickname`," ",`SurName`) as `EncodedBy`, fm.`TimeStamp` FROM `monitor_2fromsuppliermain` fm
		JOIN `1branches` b ON b.BranchNo=fm.BranchNo
		JOIN `1employees` e ON e.IDNo=fm.EncodedByNo
		JOIN `1suppliers` s ON s.SupplierNo=fm.SupplierNo
		JOIN `invty_0txntype` tt ON tt.txntypeid=fm.InvType WHERE fm.BranchNo='.$_SESSION['bnum'].' AND Date BETWEEN "'.$_POST['DateFrom'].'" AND "'.$_POST['DateTo'].'";';

		// JOIN `invty_0txntype` tt ON tt.txntypeid=fm.InvType WHERE fm.BranchNo='.$_SESSION['bnum'].' AND (Date>="'.$currentyr.'-'.$monthno.'-01'.'" AND Date<=LAST_DAY("'.$currentyr.'-'.$monthno.'-01'.'"));';
	
		$txnidname='TxnID'; $fieldname='TxnID';
		$process1='monitorinvsteps.php?which=SpecialTransferToBranch&';
		$processlabel1='Special Transfer';
		$title='';
		include_once('../backendphp/layout/clickontabletoeditbody.php');
		
	}
	
	break;

case 'DeleteMain':
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $txnid=$_REQUEST['TxnID'];
	$sql='Delete from `monitor_2fromsuppliermain` where TxnID='.$txnid; $stmt=$link->prepare($sql); $stmt->execute();
	header("Location:fromsupplier.php?which=ReceivedSummary");
    break;

case 'EditSpecifics':
    $txnid=$_REQUEST['TxnID'];
    $title='Edit Specifics'; $columnnames=array('Date', 'SupplierName', 'Branch', 'InvoiceType', 'Remarks', 'EncodedBy', 'TimeStamp'); 
    $sql='SELECT `TxnID`, `Date`, `SupplierName`, `Branch`, `txndesc` as InvoiceType, `Remarks`, concat(e.`Nickname`," ",e.`SurName`) as EncodedBy, fm.`TimeStamp`
FROM `monitor_2fromsuppliermain` fm JOIN `1suppliers` s ON s.SupplierNo=fm.SupplierNo
JOIN `1branches` b ON fm.BranchNo=b.BranchNo
JOIN `1employees` e ON e.IDNo=fm.EncodedByNo 
JOIN `invty_0txntype` tt on tt.txntypeid=fm.InvType
WHERE TxnID='.$txnid;
    $columnstoedit=array('Date', 'SupplierName', 'Branch', 'InvoiceType', 'Remarks');
    
    echo comboBox($link, 'SELECT `SupplierName`, SupplierNo FROM `1suppliers` WHERE Inactive=0 ORDER BY SupplierName', 'SupplierNo', 'SupplierName', 'suppliers');
    echo comboBox($link, 'SELECT BranchNo,Branch from `1branches` where Active<>0 ORDER BY Branch', 'BranchNo' , 'Branch', 'branchnames');
    echo comboBox($link,'SELECT `txntypeid`,`txndesc` FROM `invty_0txntype` WHERE `txntypeid` IN (1,2, 4,5,10,11,30,41);','txntypeid','txndesc','InvType');
    $columnswithlists=array('SupplierName', 'Branch', 'InvoiceType');
    $listsname=array('SupplierName'=>'suppliers', 'Branch'=>'branchnames', 'InvoiceType'=>'InvType');
    $editprocess='fromsupplier.php?which=EditMain&TxnID='.$txnid; 
    include('../backendphp/layout/editspecificsforlists.php');
    break;

case 'EditMain':
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $txnid=$_REQUEST['TxnID'];
	$columnstoedit=array('Date','InvType','Remarks');
        $sql=''; 		
	foreach ($columnstoedit as $field) {$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; }
        $suppno=comboBoxValue($link,'`1suppliers`','SupplierName',addslashes($_POST['SupplierName']),'SupplierNo');
        $branchno=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['Branch']),'BranchNo');
        $invtype=comboBoxValue($link,'`invty_0txntype`','txndesc',addslashes($_POST['InvoiceType']),'txntypeid');
	$sql='UPDATE `monitor_2fromsuppliermain` SET '.$sql.' SupplierNo='.$suppno.', BranchNo='.$branchno.', InvType='.$invtype.', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where TxnID='.$txnid . '  and `Date`>\''.$_SESSION['nb4'].'\''; // echo $sql;	
	$stmt=$link->prepare($sql); $stmt->execute();
        $sql='UPDATE `monitor_2fromsuppliersub` s JOIN `monitor_2fromsuppliermain` m on m.TxnID=s.TxnID SET s.InvType='.$invtype.' WHERE s.TxnID='.$txnid;
        $stmt=$link->prepare($sql); $stmt->execute();
	header('Location:fromsupplier.php?which=ListReceived&TxnID='.$txnid);
    break;

case 'DeleteSub':
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $txnid=$_REQUEST['TxnID']; $txnsubid=$_REQUEST['TxnSubId'];
	$sql='Delete from `monitor_2fromsuppliersub` where TxnSubId='.$txnsubid;$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:fromsupplier.php?which=ListReceived&TxnID='.$txnid);
    break;

case 'EditSub':
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $txnid=$_REQUEST['TxnID']; $txnsubid=$_REQUEST['TxnSubId'];
    $columnstoedit=array('SeriesFrom','BookletNo');
    $sql=''; 		
	foreach ($columnstoedit as $field) {$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; }
	$sql='UPDATE `monitor_2fromsuppliersub` s JOIN `monitor_2fromsuppliermain` m on m.TxnID=s.TxnID SET '.$sql.' s.EncodedByNo=\''.$_SESSION['(ak0)'].'\', s.TimeStamp=Now() where TxnSubId='.$txnsubid . '  and m.`Date`>\''.$_SESSION['nb4'].'\''; 	
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:fromsupplier.php?which=ListReceived&TxnID='.$txnid);
    break;
}

  $stmt=null;

?>