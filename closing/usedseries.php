<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(5693,5694); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=false; include_once('../switchboard/contents.php');
 

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$alternatecolor="ecd9c6";
$columnnameslist=array('Month','BranchNo','MinSaleNo','MaxSaleNo','CountShouldBe','ActualCount','TxnType','Remarks');
$columnsub=$columnnameslist;


$which=!isset($_GET['w'])?'List':$_GET['w'];
$month=(!isset($_REQUEST['Month']) or empty($_REQUEST['Month']))?(date('m')-1):$_REQUEST['Month'];
$title='Used Series of Forms and Receipts'; 

$filter='';
if (isset($_REQUEST['filter1'])) {
    $title.='<br>';
    if(!empty($_REQUEST['Month'])) { $filter.=(empty($filter)?' HAVING ':' AND ').' Month='.$_REQUEST['Month']; 
        $title.=' Month : '.(date('F',strtotime(''.$currentyr.'-'.$month.'-1'))).'<br>';} 
    if(!empty($_REQUEST['Branch'])) { $filter.=(empty($filter)?' HAVING ':' AND ').' BranchNo='.comboBoxValue($link, '`1branches`', 'Branch', $_REQUEST['Branch'], 'BranchNo'); 
    $title.=' Branch : '.$_REQUEST['Branch'].str_repeat('&nbsp;', 3).'<br>';} 
    if(!empty($_REQUEST['TxnType'])) { $filter.=(empty($filter)?' HAVING ':' AND ').' txntypeid='.comboBoxValue($link, '`invty_0txntype`', 'txndesc', $_REQUEST['TxnType'], 'txntypeid'); 
    $title.=' Transaction Type: '.$_REQUEST['TxnType'].str_repeat('&nbsp;', 3).'<br>'; } 
} else { $filter=''; $title.=': ALL';}

if (in_array($which,array('List','EditRemarks'))){
  $columnnames=$columnnameslist;
  $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' `Month`,`Branch`,`TxnType` ');
include_once('../backendphp/layout/showencodedbybutton.php'); echo '<br><br>';
    if ($showenc==1) { array_push($columnnames,'EncodedBy','TimeStamp','DataUpdatedBy','DataUpdatedTS');}
  $sql='SELECT su.*, txndesc AS TxnType, e.Nickname AS EncodedBy, e.Nickname AS DataUpdatedBy, Branch FROM `closing_2seriesused` su LEFT JOIN `invty_0txntype` tt ON su.txntypeid=tt.txntypeid LEFT JOIN `1employees` e ON e.IDNo=su.EncodedByNo JOIN `1employees` e1 ON e1.IDNo=su.DataUpdatedByNo JOIN `1branches` b ON b.BranchNo=su.BranchNo '; 

}

if (in_array($which,array('EditRemarks','PrEditRemarks'))){ $txnid=intval($_REQUEST['TxnID']); $columnstoedit=array('Remarks');}
if (in_array($which,array('List','EditRemarks'))){
   echo '<style>table {margin-left:100px;}</style>' ;
   echo comboBox($link,'SELECT * FROM `1branches` ORDER BY Branch;','BranchNo','Branch','branches');
   echo comboBox($link,'SELECT * FROM `invty_0txntype` ORDER BY txndesc;','txntypeid','txndesc','txntypes');
}
switch ($which){
case 'List':
if (!allowedToOpen(5693,'1rtc')) { echo 'No permission'; exit; }
$txnidname='TxnID';
$sql.=$filter.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');

$formdesc='<br><br>Note: Explain in Remarks any questionable usage of forms.  Data is updated together with update of static data.</i><br><br><form action="usedseries.php" method=post style="display:in-line; border: solid 1px; padding: 10px;" >Filter by: '.str_repeat('&nbsp;', 10)
        . 'Month (1-12) &nbsp; <input type=text size=5 name=Month ></input>'.str_repeat('&nbsp;', 10)
        . 'Branch &nbsp; <input type=text size=12 name=Branch list="branches"></input>'.str_repeat('&nbsp;', 10)
        . 'Transation Type &nbsp; <input type=text name=TxnType list="txntypes" ></input>'.str_repeat('&nbsp;', 10)
        . '<input type=submit name="filter1" value="Set as filter"></form><i>';

if (allowedToOpen(5694,'1rtc')) { $editprocesslabel='Edit'; $editprocess='usedseries.php?w=EditRemarks&TxnID=';}
include('../backendphp/layout/displayastable.php');
   break;
case 'EditRemarks':
    if (!allowedToOpen(5694,'1rtc')) { echo 'No permission'; exit; }
    $sql=$sql.'WHERE TxnID='.$txnid;
    $editprocess='usedseries.php?w=PrEditRemarks&TxnID='.$txnid; 
    include('../backendphp/layout/editspecificsforlists.php');
    break;
  
case 'PrEditRemarks':
    if (allowedToOpen(5694,'1rtc')) { 
        $sql='UPDATE `closing_2seriesused` SET Remarks=\''. addslashes($_POST['Remarks']).'\', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now() WHERE TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:usedseries.php");
        
    }
    
    break;
}
noform:
      $link=null; $stmt=null;
?>