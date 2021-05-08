<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(5271); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=false; include_once('../switchboard/contents.php');
 
 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

//$alternatecolor="ecd9c6";


$which=!isset($_GET['w'])?'List':$_GET['w'];
$filter=!isset($_REQUEST['f'])?'10':$_REQUEST['f'];
$title='Credit Card Enrolled Bills'; 

switch ($filter){
    case 1: $filter=' WHERE (Biller LIKE \''.$_POST['FilterText'].'\') '; $title.=': '.$_POST['FilterText']; break; //biller
    case 2: $filter=' WHERE (eb.CCID LIKE \''.$_POST['FilterText'].'\') '; $title.=': '.$_POST['FilterText']; break;// credit card
    case 3: $filter=' WHERE (Branch LIKE \''.$_POST['FilterText'].'\') '; $title.=': '.$_POST['FilterText']; break;// branch
    case 4: $filter=' WHERE (SubscriberName LIKE \''.$_POST['FilterText'].'\') '; $title.=': '.$_POST['FilterText']; break;// subscriber    
        
    default: $filter='';  break; //show all
}

if (in_array($which,array('List','EditSpecifics'))){
   echo '<style>table {margin-left:100px;}</style>' ;
   echo comboBox($link,'SELECT * FROM `1branches` ORDER BY Branch;','BranchNo','Branch','branches');
   echo comboBox($link,'SELECT * FROM `gen_info_51ccbillers` ORDER BY Biller','BillerID','Biller','billers');
   echo comboBox($link,'SELECT * FROM `acctg_1creditcards` ORDER BY CCID','CreditCardNo','CCID','ccid');
   include_once('../backendphp/layout/showencodedbybutton.php'); echo '<br><br>';
   $sql='SELECT eb.*, Biller, CreditCardNo, CONCAT(Bank," ",AcctName) AS AcctName, Branch, Nickname AS EncodedBy FROM `gen_info_51enrolledbills` eb JOIN `gen_info_51ccbillers` ccb ON ccb.BillerID=eb.BillerID JOIN `acctg_1creditcards` cc ON cc.CCID=eb.CCID JOIN `1branches` b ON b.BranchNo=eb.BranchNo LEFT JOIN `1employees` e ON e.IDNo=eb.EncodedByNo ';
   $columnnames=array('Biller','CCID','AcctName','SubscriberNo','SubscriberName','Branch','Remarks');
   if ($showenc==1) { array_push($columnnames,'EncodedBy','TimeStamp'); } else { $columnnames=$columnnames; }
   $columnsub=$columnnames;
} 

if (in_array($which,array('Add','Edit'))){
   $branchno=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['Branch']),'BranchNo');
   $billerid=comboBoxValue($link,'gen_info_51ccbillers','Biller',addslashes($_POST['Biller']),'BillerID');
   }

if (in_array($which,array('Add','Edit','EditSpecifics'))){ $columnstoadd=array('CCID','SubscriberNo','SubscriberName','Remarks');}
   
switch ($which){
case 'List':
$txnidname='TxnID';
$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' `Biller` ');

$formdesc='</i><br>'        
        .'<form action="creditcardenrolled.php?w=Add" method=post style="display:in-line;border: solid 1px; padding: 10px;">'
        . 'Enter new bills: <a href="creditcardenrolled.php?w=Import">Upload from csv file</a>  OR '
        . 'Biller: <input type=text name="Biller" list="billers"> &nbsp; CCID<input type=text name="CCID" list="ccid"> &nbsp; '
        . 'Subscriber No:<input type=text name="SubscriberNo"> &nbsp; Subscriber Name: <input type=text name="SubscriberName"> &nbsp; '
        . 'Branch:<input type=text name="Branch" list="branches"> &nbsp; Remarks (optional):<input type=text name="Remarks"> &nbsp; '
        . '<input type="hidden" name=action_token value='.$_SESSION['action_token'].'><input type=submit name="submit" value="Add"></form>'
        . '<br><form action="creditcardenrolled.php" method=post style="display:in-line; border: solid 1px; padding: 10px;" >'.str_repeat('&nbsp;', 10)
        . ' &nbsp; <input type=text name=FilterText placeholder="wildcard search: use %" ></input>'.str_repeat('&nbsp;', 10)
        .'<input type="radio" value=1 name="f"> Biller'.str_repeat('&nbsp;', 3)
        .'<input type="radio" value=2 name="f"> Credit Card'.str_repeat('&nbsp;', 3)
        .'<input type="radio" value=3 name="f"> Branch'.str_repeat('&nbsp;', 3)
        .'<input type="radio" value=4 name="f"> Subscriber Name'.str_repeat('&nbsp;', 3)
        . '<input type=submit name="filter1" value="Set filter">'.str_repeat('&nbsp;', 10)
        .'</form><i>';
$editprocesslabel='Edit'; $editprocess='creditcardenrolled.php?w=EditSpecifics&TxnID=';
if (allowedToOpen(1500,'1rtc')){ $delprocess='creditcardenrolled.php?w=Delete&TxnID=';}
$sql.=$filter.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
include('../backendphp/layout/displayastable.php');
   break;
case 'Import':
    $colnames=array('BillerID','CCID','SubscriberNo','SubscriberName','BranchNo','Remarks','EncodedByNo');
$requiredcol=array('BillerID','CCID','SubscriberNo','SubscriberName','BranchNo','EncodedByNo');
$required='';  foreach($requiredcol as $req){ $required=$required.'<li>'.$req.'</li>'; }
$allowed=''; foreach($colnames as $col){ $allowed=$allowed.'<li>'.$col.'</li>'; }
$specific_instruct='<i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
$tblname='gen_info_51enrolledbills'; $firstcolumnname='BillerID';
$DOWNLOAD_DIR="../../uploads/";
include('../backendphp/layout/uploaddata.php');
    break;
case 'Add':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `gen_info_51enrolledbills` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' BranchNo='.$branchno.', BillerID='.$billerid.', TimeStamp=Now()'; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
case 'Delete':
        if (allowedToOpen(1500,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `gen_info_51enrolledbills` WHERE TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;     
case 'EditSpecifics':
         $title='Edit Specifics';
	 $txnid=intval($_GET['TxnID']); 
	 $sql=$sql.'WHERE TxnID='.$txnid;
	 $columnstoedit=$columnstoadd; $columnstoedit[]='Branch'; $columnstoedit[]='Biller';
	 $columnswithlists=array('Branch','Biller');
         $listsname=array('Branch'=>'branches','Biller'=>'billers');
	 $editprocess='creditcardenrolled.php?w=Edit&TxnID='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
         break;
case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `gen_info_51enrolledbills` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' BranchNo='.$branchno.', BillerID='.$billerid.', TimeStamp=Now() WHERE TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:creditcardenrolled.php");
        break;
    
}
noform:
      $link=null; $stmt=null;
?>