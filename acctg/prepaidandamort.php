<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(584,'1rtc')) { echo 'No permission'; exit; }
$showbranches=true; include_once('../switchboard/contents.php');

	include_once('../backendphp/functions/editok.php');
	include_once "../generalinfo/lists.inc"; //include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	include_once('../backendphp/functions/getnumber.php');
 $which=$_GET['w'];  $method='POST';


switch($which){
    case 'NewPrepaid':
          if (!allowedToOpen(583,'1rtc')) { echo 'No permission'; exit; }     
    $title='Record New Prepaid'; $fieldsinrow=4;
    $columnnames=array(
                    array('field'=>'Branch', 'type'=>'text','size'=>15, 'required'=>true, 'list'=>'branchnames', 'value'=>$_SESSION['@brn']),
		    array('field'=>'DatePaid', 'type'=>'date','size'=>15,'required'=>true,'value'=>date('Y-m-d')),
		    array('field'=>'PrepaidDesc', 'caption'=>'Prepaid Description', 'type'=>'text','size'=>20, 'required'=>true),
		    //array('field'=>'PrepaidAccount', 'type'=>'text','size'=>15, 'required'=>true, 'list'=>'accounts'),
		    array('field'=>'PrepaidAccountID', 'type'=>'hidden', 'size'=>0,'value'=>'151'),
		    array('field'=>'ExpenseAccount', 'caption'=>'Expense Account', 'type'=>'text','size'=>15, 'required'=>true, 'list'=>'accounts'),
		    array('field'=>'Amount', 'caption'=>'Prepaid Amount', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'AmortInMonths', 'type'=>'text','size'=>10, 'required'=>true)                   
                    );
    $action='prepaidandamort.php?w=RecordPrepaid';
    $liststoshow=array('branchnames');
    $whichotherlist='acctg'; $listcondition=' '; $otherlist=array('accounts');
     include('../backendphp/layout/inputmainform.php');

        break;

case 'RecordPrepaid':
        if (!allowedToOpen(583,'1rtc')) { echo 'No permission'; exit; }     
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php'; 
        //to check if editable TEMPORARILY REMOVED THIS RESTRICTION
	// if((($_POST['DatePaid'])<$_SESSION['nb4A'])  or (intval($_POST['DatePaid'])<>$currentyr)){	header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); break; }
        $branchno=getValue($link,'1branches','Branch',addslashes($_POST['Branch']),'BranchNo');
	//$acctid=getNumber('Account',addslashes($_POST['PrepaidAccount']));
	$amortacctid=getNumber('Account',addslashes($_POST['ExpenseAccount']));
        $sql='';
        $columnstoadd=array('DatePaid','PrepaidDesc', 'PrepaidAccountID', 'Amount', 'AmortInMonths');
	foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; }
	$sql='INSERT INTO `acctg_2prepaid` SET BranchNo='.$branchno.', '.$sql.'EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; //PrepaidAccountID='.$acctid.', 
	$stmt=$link->prepare($sql); $stmt->execute();	
        //get txnid
        $sql='SELECT PrepaidID,DatePaid FROM `acctg_2prepaid` WHERE PrepaidDesc LIKE \''.$_POST['PrepaidDesc'].'\' AND BranchNo='.$branchno.' AND DatePaid=\''.$_POST['DatePaid'].'\'';
        $stmt=$link->query($sql); $result=$stmt->fetch(); $txnid=$result['PrepaidID']; //echo $sql;       break;
	
	$amortdate=substr($result['DatePaid'],0,4).'-'.(substr($result['DatePaid'],5,2)+((substr($result['DatePaid'],8,2)<16)?0:1)).'-'.substr($result['DatePaid'],8,2);
	$amortvalue=round($_POST['Amount']/$_POST['AmortInMonths'],2);
	$counter=0;
	while ($counter<$_POST['AmortInMonths']) {
	    $sql1='INSERT INTO `acctg_2prepaidamort` (`PrepaidID`,`ExpenseAccountID`,`AmortDate`,`Amount`, `EncodedByNo`) SELECT '.$txnid.', '.$amortacctid.', LAST_DAY(date_add(\''.$amortdate.'\', INTERVAL '.$counter.' MONTH)), '.$amortvalue.','.$_SESSION['(ak0)']; 
	    $stmt=$link->prepare($sql1); $stmt->execute();
	    $counter=$counter+1;
	}
        header('Location:prepaidandamort.php?w=PrepaidandAmort&TxnID='.$txnid);
        
    break;
case 'PrepaidExpense':
case 'PrepaidandAmort':
    $txnid=intval($_REQUEST['TxnID']); $title='Prepaid and Amortization'; 
    $formdesc='</i><a href="prepaidandamort.php?w=NewPrepaid">Add New Prepaid</a>'.  str_repeat('&nbsp;', 10).'<a href="prepaidandamort.php?w=Sched">Prepaid Sched</a><i>';
    $sqlmain='SELECT Branch, a.*, ca.ShortAcctID as PrepaidAccount, concat(e.`Nickname`," ",e.`SurName`) as EncodedBy, FORMAT(a.`Amount`,2) AS Amount FROM acctg_2prepaid a
    JOIN `1branches` b ON b.BranchNo=a.BranchNo
LEFT JOIN `1employees` e ON e.IDNo=a.EncodedByNo
JOIN acctg_1chartofaccounts ca on ca.AccountID=a.PrepaidAccountID 
WHERE a.PrepaidID='.$txnid;
    $stmt=$link->query($sqlmain); $result=$stmt->fetch();

    $sqlsub='SELECT d.AmortID,`AmortDate`, ShortAcctID as ExpenseAccount, d.`Amount`, concat(e.`Nickname`," ",e.`SurName`) as EncodedBy, d.TimeStamp FROM acctg_2prepaid a
    JOIN acctg_2prepaidamort d on a.PrepaidID=d.PrepaidID JOIN acctg_1chartofaccounts ca on ca.AccountID=d.ExpenseAccountID
    LEFT JOIN `1employees` e ON e.IDNo=d.EncodedByNo WHERE d.PrepaidID='.$txnid;
    
    $columnnamesmain=array('Branch', 'DatePaid', 'PrepaidDesc', 'PrepaidAccount', 'Amount', 'AmortInMonths', 'EncodedBy', 'TimeStamp');
    $columnsub=array('AmortDate', 'ExpenseAccount', 'Amount', 'EncodedBy', 'TimeStamp');
    
    $main=''; $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%5==0?'</tr><tr>':'');
    }
    
    if((($result['DatePaid'])>$_SESSION['nb4A']  or date('Y',  strtotime($result['DatePaid']))==$currentyr) AND ($result['Posted']==0)){
        $editok=true; $editsub=true;
	$editprocessmain='prepaidandamort.php?w=EditMain&TxnID='; $editprocesslabelmain='Enter'; 	
	$delprocessmain='prepaidandamort.php?w=DeleteMain&TxnID='; 
	$columnstoeditmain=array('Branch','DatePaid','PrepaidDesc', 'PrepaidAccount', 'Amount', 'AmortInMonths');
	$colwithlistmain=array('Branch','PrepaidAccount', 'CreditAccount');
	$listsmain=array('Branch'=>'branchnames','PrepaidAccount'=>'accounts');
	$liststoshow=array('branchnames');		      
        $columnstoedit=array('AmortDate','ExpenseAccount','Amount');
	$editsub=true; $editprocess='prepaidandamort.php?PrepaidID='.$txnid.'&w=EditSub&AmortID='; $editprocesslabel='Enter';
	$delprocess='prepaidandamort.php?PrepaidID='.$txnid.'&w=DeleteSub&AmortID=';
	$colwithlistsub=array('ExpenseAccount');
        } else {
            $editok=false; $editsub=false; $columnstoedit=array(); $editprocessmain=''; $editprocess =''; $columnstoeditmain=array(); $liststoshow=array();
            }
    
    $main='<table><tr>'.$main.'<tr></table>';
    $main=$main.'<br>'.(isset($_GET['msg'])?'<br><b><font color="maroon">'.strtoupper($_GET['msg']).'</font></b><br>':'');
     $txnsubid='AmortID'; 
    $withsub=true; $coltototal='Amount'; $runtotal=true;
    // info for posting:
    $postvalue='1'; $table='acctg_2prepaid';  $txntype='Prepaid';
    //to add records in sub
    $columnnames=array(
                    array('field'=>'AmortDate', 'type'=>'date','size'=>15,'required'=>true,'autofocus'=>true),
                    array('field'=>'ExpenseAccount', 'type'=>'text','size'=>10, 'required'=>false, 'list'=>'accounts'),
                    array('field'=>'Amount', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'PrepaidID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
        
    $action='prepaidandamort.php?w=AddSub&PrepaidID='.$txnid;
    $whichotherlist='acctg'; $listcondition=' WHERE AccountType BETWEEN 14 AND 18 '; $otherlist=array('accounts');
    // end add records in sub
    $txnidcol='PrepaidID'; 
    include('../backendphp/layout/inputsubeditmain.php');
    break;

case 'AddSub':
    $txnid=$_REQUEST['PrepaidID'];
    $amortacctid=getNumber('Account',addslashes($_POST['ExpenseAccount']));
        $sql1='INSERT INTO `acctg_2prepaidamort` (`PrepaidID`,`ExpenseAccountID`,`AmortDate`,`Amount`, `EncodedByNo`) SELECT '.$txnid.', '.$amortacctid.', \''.$_POST['AmortDate'].'\', '.$_POST['Amount'].','.$_SESSION['(ak0)'];  
    $stmt=$link->prepare($sql1); $stmt->execute();
    header('Location:prepaidandamort.php?w=PrepaidandAmort&TxnID='.$txnid);
    break;

case 'Sched':
    $title='Prepaid Expense Schedule'; $thisyr=$currentyr; $showbranches=true; $formdesc='<a href="prepaidandamort.php?w=NewPrepaid">Add New Prepaid</a>';
    include_once('../switchboard/contents.php');
    include('../backendphp/layout/showallbranchesbutton.php');
    if ($show==0) { $branchcondition=' WHERE BranchNo='.$_SESSION['bnum']; $title=$title.'  Per Branch';} else {$branchcondition=''; $title=$title.'  - All Branches';}
    //include_once('../backendphp/layout/clickontabletoedithead.php');
    $monthsarray=array(1,2,3,4,5,6,7,8,9,10,11,12); $sql='';
    $columnnames=array('PrepaidDesc', 'PrepaidAccount', 'Amount', 'DatePaid', 'AmortInMonths','PreviousYrs');  
    if ($show==0) { $branchcondition=' WHERE a.BranchNo='.$_SESSION['bnum']; $title=$title.'  Per Branch';} 
    else {$branchcondition=''; $title=$title.'  - All Branches'; array_unshift($columnnames,'Branch') ;}
    foreach ($monthsarray as $month){
	$monthname=date('M',strtotime($thisyr.'-'.$month.'-01'));
	$columnnames[]=$monthname;
	$sql=$sql.' FORMAT(SUM(IFNULL(CASE WHEN Year(`AmortDate`)='.$thisyr.' AND MONTH(`AmortDate`)='.$month.' THEN  d.`Amount` END,0)),2) AS `'.$monthname.'`, ';
    }
    $columnnames[]='TotalAmortizationAsOfThisYr';  $columnnames[]='NetValueThisYr';

$sql0='CREATE TEMPORARY TABLE Prepaid AS SELECT BranchNo, a.`PrepaidID` as TxnID, `PrepaidDesc`, PrepaidAccountID, TRUNCATE(a.`Amount`,2) AS `Amount`, `DatePaid`, `AmortInMonths`, TRUNCATE(SUM(IFNULL(CASE WHEN Year(`AmortDate`)<'.$thisyr.' THEN  d.`Amount` END,0)),2) AS `PreviousYrs`, '.$sql.' TRUNCATE(SUM(IFNULL(CASE WHEN Year(`AmortDate`)<='.$thisyr.' THEN  d.`Amount` END,0)),2) AS `TotalAmortizationAsOfThisYr`, TRUNCATE((a.`Amount`-SUM(IFNULL(CASE WHEN Year(`AmortDate`)<='.$thisyr.' THEN  d.`Amount` END,0))),2)  AS `NetValueThisYr`
FROM `acctg_2prepaid` a LEFT JOIN `acctg_2prepaidamort` d ON a.PrepaidID=d.PrepaidID '.$branchcondition. ' GROUP BY a.PrepaidID;';    
$stmt=$link->prepare($sql0); $stmt->execute();

$sql='SELECT a.*, Branch, ShortAcctID AS PrepaidAccount, FORMAT(`Amount`,2) AS `Amount`, `DatePaid`, `AmortInMonths`, FORMAT(`PreviousYrs`,2) AS `PreviousYrs`, FORMAT(`TotalAmortizationAsOfThisYr`,2) AS `TotalAmortizationAsOfThisYr`, FORMAT(`NetValueThisYr`,2) AS `NetValueThisYr`
FROM `Prepaid` a JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=a.PrepaidAccountID '
        . ' JOIN `1branches` b ON b.BranchNo=a.BranchNo '.$branchcondition
        . ' GROUP BY a.TxnID;'; //echo $sql;
$txnidname='TxnID'; $coltototal='NetValueThisYr';
$editprocess='prepaidandamort.php?w=PrepaidandAmort&TxnID=';
$editprocesslabel='Lookup';
$sqlsum='SELECT Branch, ShortAcctID AS PrepaidAccount, FORMAT(SUM(`PreviousYrs`),2) AS `PreviousYrs`, FORMAT(SUM(`TotalAmortizationAsOfThisYr`),2) AS `TotalAmortizationAsOfThisYr`, FORMAT(SUM(`NetValueThisYr`),2) AS `NetValueThisYr`
FROM `Prepaid` a LEFT JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=a.PrepaidAccountID '
        . ' JOIN `1branches` b ON b.BranchNo=a.BranchNo '.$branchcondition
        . ' GROUP BY a.BranchNo, ca.AccountID;';
$stmtsum=$link->query($sqlsum); $ressum=$stmtsum->fetchAll();
$totalstext=''; $totaltitle=''; $columnnamestotal=array('Branch','PrepaidAccount','PreviousYrs','TotalAmortizationAsOfThisYr','NetValueThisYr');
foreach($columnnamestotal as $col){ $totaltitle=$totaltitle.'<td>'.$col.'</td>';  }

foreach ($ressum as $sum){
    $total=''; 
    foreach($columnnamestotal as $col){$total=$total.'<td>'.$sum[$col].'</td>';    }  
    $totalstext=$totalstext.'<tr>'.$total.'</tr>';
}
$totalstext='<table><tr>'.$totaltitle.'</tr>'.$totalstext.'</table>';

include_once('../backendphp/layout/displayastable.php');
    break;
    
case 'DeleteMain':
    $txnid=intval($_REQUEST['TxnID']);
	$sql='Delete from `acctg_2prepaid` where PrepaidID='.$txnid; $stmt=$link->prepare($sql); $stmt->execute();
	header("Location:prepaidandamort.php?w=Amort");
    break;


case 'EditMain':
    $txnid=intval($_REQUEST['TxnID']);
    $branchno=getValue($link,'1branches','Branch',addslashes($_POST['Branch']),'BranchNo');
    $acctid=getNumber('Account',addslashes($_POST['PrepaidAccount']));
    $columnstoedit=array('DatePaid','PrepaidDesc', 'Amount', 'AmortInMonths');
    $sql=''; 		
	foreach ($columnstoedit as $field) {$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; }
	$sql='UPDATE `acctg_2prepaid` a SET BranchNo='.$branchno.', PrepaidAccountID='.$acctid.', '.$sql.' a.EncodedByNo=\''.$_SESSION['(ak0)'].'\', a.TimeStamp=Now() where PrepaidID='.$txnid . '  and a.`DatePaid`>\''.$_SESSION['nb4A'].'\''; 	
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:prepaidandamort.php?w=PrepaidandAmort&TxnID='.$txnid);
    break;
    
case 'DeleteSub':
    $txnid=$_REQUEST['PrepaidID']; $txnsubid=$_REQUEST['AmortID'];
	$sql='Delete from `acctg_2prepaidamort` where AmortID='.$txnsubid;$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:prepaidandamort.php?w=PrepaidandAmort&TxnID='.$txnid);
    break;

case 'EditSub':
    $txnid=$_REQUEST['PrepaidID']; $txnsubid=$_REQUEST['AmortID'];
    $columnstoedit=array('AmortDate','Amount');
    $amortacctid=getNumber('Account',addslashes($_POST['ExpenseAccount']));
    $sql=''; 		
	foreach ($columnstoedit as $field) {$sql=$sql.' d.' . $field. '=\''.$_POST[$field].'\', '; }
	$sql='UPDATE `acctg_2prepaidamort` d JOIN `acctg_2prepaid` a on a.PrepaidID=d.PrepaidID SET ExpenseAccountID='.$amortacctid.', '.$sql.' d.EncodedByNo=\''.$_SESSION['(ak0)'].'\', d.TimeStamp=Now() where AmortID='.$txnsubid . '  and a.`DatePaid`>\''.$_SESSION['nb4'].'\''; 	
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:prepaidandamort.php?w=PrepaidandAmort&TxnID='.$txnid);
    break;
}
  $link=null; $stmt=null;