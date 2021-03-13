<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once('../switchboard/contents.php');




if (!allowedToOpen(62692,'1rtc')) {   echo 'No permission'; exit;}   

$txnid=$_REQUEST['QuoteID'];
$title='Edit Quote';
    $minorswitch=$_SERVER['HTTP_REFERER'];
    $minorswitchname='Back';
;
$processblank='';
$processlabelblank='';
switch ($_REQUEST['calledfrom']){
case 6: //edit main
$columnnames=array('QuoteDate','ClientName','ContactPerson','Position','SirMaam','FaxNo','Warranty','Payment','Note1','Note2','Note3');
$columnstoedit=array('QuoteDate','ClientName','ContactPerson','Position','SirMaam','FaxNo','Warranty','Payment','Note1','Note2','Note3');
$columnslist=array();
$listsname=array();
$liststoshow=array();
$method='POST';
$action='praddcanvass.php?calledfrom=6&QuoteID='.$txnid;

$sql='SELECT * from `quotations_2quotemain` where QuoteID='.$txnid;
include('../backendphp/layout/rendersubform.php');
		break;
case 7: //edit sub
$txnsubid=$_REQUEST['QuoteSubID'];
$columnnames=array('Description','Qty','Unit','UnitPrice','Amount');
$columnstoedit=array('Description','Qty','Unit','UnitPrice');
$columnslist=array();
$liststoshow=array();
$listsname=array();
$method='POST';
$action='praddcanvass.php?calledfrom=7&QuoteSubID='.$txnsubid.'&QuoteID='.$txnid;

$sql='Select s.*,s.UnitPrice*s.Qty as Amount from quotations_2quotesub s where QuoteSubID='.$txnsubid;
include('../backendphp/layout/rendersubform.php');
		break;

}
  $link=null; $stmt=null;
?>
