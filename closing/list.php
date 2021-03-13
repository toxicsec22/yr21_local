<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(569); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=FALSE;
include_once('../switchboard/contents.php');



$which=!isset($_REQUEST['w'])?'List':$_REQUEST['w'];

$title='Closing Data';
if (in_array($which,array('List'))) {
$action='list.php'; include_once('filters.php');
$orderby=' ORDER BY Month, AccountID, Branch';
}

if (in_array($which,array('Details', 'EditSpecifics'))) {
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    echo comboBox($link, 'SELECT * FROM `closing_1wheretxn`', 'WhereTxnID', 'WhereTxn', 'wheretxn');
    $sqldetails='SELECT cs.*, WhereTxn, CONCAT(e.Nickname," ",e.Surname) AS EncodedBy, FORMAT(Amount,2) AS Amount FROM `closing_2closesub` cs JOIN `closing_1wheretxn` w ON w.WhereTxnID=cs.WhereTxnID LEFT JOIN `1employees` e ON e.IDNo=cs.EncodedByNo ';
    $columnnameslistsub=array('WhereTxn','ControlNo','Link','Details','Amount','HowToSettle');
}

if (in_array($which,array('Add', 'Edit','Delete','Details','EditSpecifics'))) {
    $sql0='SELECT cm.Month FROM `closing_2closemain` cm WHERE CloseID='.$_REQUEST['CloseID'];
    $stmt0=$link->query($sql0); $res0=$stmt0->fetch();    
    $editok=true; //TEMPORARILY ALLOWED FOR FIRST ENCODING
//    $editok=(strtotime(''.$currentyr.'-'.$res0['Month'].'-1')>strtotime($_SESSION['nb4A']))?true:false;
}

if (in_array($which,array('Add', 'Edit'))) {
    if ($editok==false) {goto skipedit;} 
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    $where=comboBoxValue($link, 'closing_1wheretxn', 'WhereTxn', $_POST['WhereTxn'], 'WhereTxnID');
    $columnstoadd=array('ControlNo','Link','Details','Amount','HowToSettle');
    $sqladd='';
    foreach ($columnstoadd as $col){ $sqladd.='`'.$col.'`=\''.addslashes($_POST[$col]).'\', '; }
    if($which=='Add'){ $sql='INSERT INTO `closing_2closesub` SET '.$sqladd.' `CloseID`='.$_POST['CloseID'].', `WhereTxnID`='.$where.', `EncodedByNo`='.$_SESSION['(ak0)'].', `TimeStamp`=Now()'; } 
    else {
        include_once 'trailclosing.php';
        recordtrail($_REQUEST['CloseSubID'],'closing_2closesub',$link,0);
        $sql='UPDATE `closing_2closesub` SET '.$sqladd.' `WhereTxnID`='.$where.', `EncodedByNo`='.$_SESSION['(ak0)'].', `TimeStamp`=Now() WHERE `CloseSubID`='.$_REQUEST['CloseSubID'];} 
  //  if($_SESSION['(ak0)']==1002){ echo $sql; break;}
    $stmt=$link->prepare($sql); $stmt->execute(); 
    skipedit:
    header('Location:list.php?w=Details&CloseID='.$_REQUEST['CloseID']);
}

$columnnameslist=array('CloseID','Month','AccountID','Account','BranchNo','Branch','DataEndBalance','Accounted','Difference');

switch ($which){ 
case 'List':
    include_once('../backendphp/layout/showencodedbybutton.php'); echo '<br><br>';
	if (allowedToOpen(5695,'1rtc')){
    $formdesc.='<br></i><a href="edittrail.php">Look up edit audit trail</a>'.  str_repeat('&nbsp;', 15).'<a href="../acctg/lookupgenacctg.php?w=SendCurrtoClosing">Add balances for open month</a><i><br><br>';
	}
    $columnnames=$columnnameslist;
if ($showenc==1) { array_push($columnnames,'EncodedBy','TimeStamp');}
include('listsqls.php');

$editprocesslabel='Lookup'; $editprocess='list.php?w=Details&CloseID='; $txnidname='CloseID';
       //  if($_SESSION['(ak0)']==1002){ echo $sql0;}
         include('../backendphp/layout/displayastable.php');
    break;
    
case 'Details':
    $title='Details';  $method='Post'; $txnid=$_REQUEST['CloseID']; $formdesc='</i><br><a href="list.php">Back to List</a><br><i>'; 
    $sql='SELECT cm.*, ShortAcctID AS Account, Branch, FORMAT(EndBal,2) AS DataEndBalance, CONCAT(e.Nickname," ",e.Surname) AS EncodedBy, FORMAT((EndBal-(SELECT IFNULL(SUM(Amount),0) FROM closing_2closesub WHERE CloseID='.$txnid.')),2) AS Difference, (SELECT FORMAT(IFNULL(SUM(Amount),0),2) FROM closing_2closesub WHERE CloseID='.$txnid.') AS Accounted FROM closing_2closemain cm LEFT JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=cm.AccountID JOIN `1branches` b ON b.BranchNo=cm.BranchNo LEFT JOIN `1employees` e ON e.IDNo=cm.EncodedByNo WHERE cm.CloseID='.$txnid;
    include_once('../backendphp/layout/showencodedbybutton.php'); echo '<br><br>';
    $hidecount=true; $columnnames=$columnnameslist; if ($showenc==1) { array_push($columnnames,'EncodedBy','TimeStamp');}
    include('../backendphp/layout/displayastable.php'); unset($formdesc);
    $txnid=$_REQUEST['CloseID'];
    $fieldsinrow=8; $liststoshow=array();
    $columnnames=array(
                       array('field'=>'WhereTxn','type'=>'text','size'=>20,'required'=>true,'list'=>'wheretxn'), 
                       array('field'=>'ControlNo','type'=>'text','size'=>4,'required'=>true),
                       array('field'=>'Link','type'=>'text','size'=>40,'required'=>true),
                       array('field'=>'Details','type'=>'text','size'=>40,'required'=>true), 
                       array('field'=>'HowToSettle','type'=>'text','size'=>40,'required'=>false), 
                       array('field'=>'Amount','type'=>'text','size'=>8,'required'=>true), 
                       array('field'=>'CloseID','type'=>'hidden','size'=>1,'value'=>$txnid)
        );
    $title='';$txnidname='CloseSubID';
    if ($editok==true) {
    $action='list.php?w=Add';
	if (allowedToOpen(5695,'1rtc')){
    include('../backendphp/layout/inputmainform.php'); $editprocess='list.php?w=EditSpecifics&CloseID='.$txnid.'&CloseSubID=';$editprocesslabel='Edit'; $delprocess='list.php?w=Delete&CloseID='.$txnid.'&CloseSubID=';} }
	if (allowedToOpen(5695,'1rtc')){
    $addlprocess='list.php?w=Carryover&CloseID='.$txnid.'&CloseSubID='; $addlprocesslabel='Carryover to open month';}
    $sql=$sqldetails.' WHERE CloseID='.$_REQUEST['CloseID']; $columnnames=$columnnameslistsub; unset($hidecount);
    if ($showenc==1) { array_push($columnnames,'EncodedBy','TimeStamp');}
    include('../backendphp/layout/displayastable.php');
    
    break;

case 'EditSpecifics':
    if ($editok==false){ header('Location:list.php?w=Details&CloseID='.$_REQUEST['CloseID']);}
    $title='Edit Specifics'; $columnnames=$columnnameslistsub; $columnstoedit=array('WhereTxn','ControlNo','Link','Details','Amount','HowToSettle');
    $sql=$sqldetails.' WHERE CloseSubID='.$_REQUEST['CloseSubID'];
    $columnswithlists=array('WhereTxn');
    $listsname=array('WhereTxn'=>'wheretxn');
    $editprocess='list.php?w=Edit&CloseID='.$_REQUEST['CloseID'].'&CloseSubID='.$_REQUEST['CloseSubID']; 
    include('../backendphp/layout/editspecificsforlists.php');
    break;

Case 'Delete':
    if($editok==true){ 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once 'trailclosing.php';
        recordtrail($_REQUEST['CloseSubID'],'closing_2closesub',$link,1);
        $sql='DELETE FROM `closing_2closesub` WHERE CloseSubID='.$_REQUEST['CloseSubID']; 
        $stmt=$link->prepare($sql); $stmt->execute(); 
    }
    header('Location:list.php?w=Details&CloseID='.$_REQUEST['CloseID']);
    break;
    
case 'Carryover':
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $sql0='SELECT cm.BranchNo, cm.AccountID FROM closing_2closemain cm JOIN closing_2closesub cs ON cm.CloseID=cs.CloseID WHERE cs.CloseSubID='.$_REQUEST['CloseSubID'];
    $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); 
    $sql0='SELECT cm.CloseID FROM closing_2closemain cm WHERE cm.Month=('.(date('m',(strtotime($_SESSION['nb4A'])==12?0:strtotime($_SESSION['nb4A'])))).'+1) AND BranchNo='.$res0['BranchNo'].' AND AccountID='.$res0['AccountID']; if($_SESSION['(ak0)']==1002){ echo $sql0; }
    $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); 
    $sqladd='`WhereTxnID`,`ControlNo`,`Link`,`Details`,`Amount`,`HowToSettle`';
    $sql='INSERT INTO `closing_2closesub` ('.$sqladd.', CloseID, EncodedByNo, TimeStamp) SELECT '.$sqladd.', '.$res0['CloseID'].', '.$_SESSION['(ak0)'].', Now() FROM closing_2closesub WHERE CloseSubID='.$_REQUEST['CloseSubID']; 
    $stmt=$link->prepare($sql); $stmt->execute();
    header("Location:".$_SERVER['HTTP_REFERER']);
    break;    
  
    
}
  $link=null; $stmt=null;