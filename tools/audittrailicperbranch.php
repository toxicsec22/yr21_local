<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(8282,'1rtc')) {
   echo 'No permission'; exit;}
$showbranches=true;   
 include_once('../switchboard/contents.php'); include_once('../backendphp/layout/regulartablestyle.php');
 
 include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$which=isset($which)?$_GET['w']:'AuditTrailItem';




?><br><title>Item Audit Trail Per Branch</title>
<h3>Item Audit Trail Per Branch</h3>
&nbsp &nbsp <div><div style="float:left;"><form style="display:inline" action='#' method='POST'>Item Code:&nbsp &nbsp 
    <input type='text' name='itemcode' autocomplete='off' size='10' >
    &nbsp &nbsp <input type='submit' name='submit' value='Look Up'>
<?php
    



switch ($which){

	
CASE 'AuditTrailItem':
  $allowed=8282;
if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit; }
$title='';


if(isset($_POST['submit'])){
    $sqlmain="SELECT itm.TxnID,Date,itm.ControlNo AS ControlNoMain,`SuppNo/ClientNo` AS Client,txndesc AS txntype,`ForPO/Request`,itm.Remarks AS RemarksMain,CONCAT(e1.Nickname,' ',e1.SurName) AS EncodedByMain,itm.TimeStamp AS TimeStampMain,CONCAT(e2.Nickname,' ',e2.SurName) AS PostedBy,IF(itm.EditOrDel=0,'Edit','Del') AS EditOrDelMain,CONCAT(e3.Nickname,' ',e3.SurName) AS EditOrDelByMain,itm.EditOrDelTS AS EditOrDelTSMain,its.ControlNo AS ControlNoSub,Qty,UnitCost,UnitPrice,SerialNo,its.Remarks AS RemarksSub,Defective,CONCAT(e4.Nickname,' ',e4.SurName) AS EncodedBySub,its.TimeStamp AS TimeStampSub,IF(its.EditOrDel=0,'Edit','Del') AS EditOrDelSub,CONCAT(e5.Nickname,' ',e5.SurName) AS EditOrDelBySub,its.EditOrDelTS AS EditOrDelTSSubs FROM invtytxnsmain itm JOIN invtytxnssub its ON itm.TxnID=its.TxnID LEFT JOIN 1clients c ON itm.`SuppNo/ClientNo`=c.ClientNo LEFT JOIN invty_0txntype t ON itm.txntype=t.txntypeid JOIN 1employees e1 ON itm.EncodedByNo=e1.IDNo JOIN 1employees e2 ON itm.PostedByNo=e2.IDNo JOIN 1employees e3 ON itm.EditOrDelByNo=e3.IDNo JOIN 1employees e4 ON its.EncodedByNo=e4.IDNo JOIN 1employees e5 ON its.EditOrDelByNo=e5.IDNo WHERE itm.whichtable='invty_2sale' AND its.whichtable='invty_2salesub' AND ItemCode=".intval($_POST['itemcode'])." AND BranchNo=".$_SESSION['bnum']." ORDER BY its.EditOrDelTS DESC";
	$formdesc='<br></i><h3>Item Code: '.$_POST['itemcode'].', Branch: '.$_SESSION['@brn'].'</h3><i>';
	$sql=$sqlmain;
	$columnnames=array('TxnID','Date','ControlNoMain','Client','txntype','ForPO/Request','RemarksMain','EncodedByMain','TimeStampMain','PostedBy','EditOrDelMain','EditOrDelByMain','EditOrDelTSMain','ControlNoSub','Qty','UnitCost','UnitPrice','SerialNo','RemarksSub','Defective','EncodedBySub','TimeStampSub','EditOrDelSub','EditOrDelBySub','EditOrDelTSSubs');
	include('../backendphp/layout/displayastablenosort.php');

}    
break;
	

}


    
?>