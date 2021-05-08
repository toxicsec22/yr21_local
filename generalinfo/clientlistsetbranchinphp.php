<?php
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$setbranch='14'; //Branch To Edit
if (!allowedToOpen(911,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false; 

include_once('../switchboard/contents.php');        
include_once 'trailgeninfo.php';

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=(!isset($_GET['w'])?'List':$_GET['w']);

$title='List of Clients';
$txnidname='ClientNo';

if (in_array($which,array('EditSpecifics','List'))){
	$columnnames=array('ClientName', 'TelNo1', 'TelNo2', 'Mobile','ContactPerson','EmailAddress','TIN');
}
    
switch ($which){
   case 'List': 
       $formdesc='';
       $sql='SELECT `c`.*, `c`.ClientNo AS TxnID FROM `1clients` c JOIN `gen_info_1branchesclientsjxn` ON `c`.ClientNo = `gen_info_1branchesclientsjxn`.ClientNo
      WHERE (((`c`.ClientNo)>99) AND (`c`.ClientNo NOT IN (10000,10001,10004)) AND ((`gen_info_1branchesclientsjxn`.BranchNo)='.$setbranch.')) ORDER BY ClientName;';
	  
    
$editprocess='clientlistsetbranchinphp.php?w=EditSpecifics&ClientNo='; $editprocesslabel='Edit';
$columnstoedit=array('TIN');     

include('../backendphp/layout/displayastable.php');

break;
  
case 'EditSpecifics':

   $title='Edit Client Specifics';
	 $txnid=intval($_GET['ClientNo']); $main='1clients';
	 $sql='SELECT `c`.* FROM `1clients` c WHERE ClientNo='.$txnid;
        
         $columnstoedit=array('TIN');
echo '<b>Note:</b> <br>TIN must be <b>12</b> chars (if 9 chars, add three (3) zeros at the end. [111111111<b>000</b>])';
	 $editprocess='clientlistsetbranchinphp.php?w=Edit&ClientNo='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');   
   break;
   
case 'Edit':
	IF(strlen($_POST['TIN'])<>12) {
		echo 'TIN MUST be 12 chars.'; exit();
	}
    $txnid=$_REQUEST['ClientNo'];
	
	$columnstoedit=array('TIN');
	    
	$table='1clients';
	recordtrail($txnid,$table,$link,0);
		
	$sqlupdate='UPDATE 1clients SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.addslashes($_POST[$field]).'\', ';
		
	}
	
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() WHERE ClientNo=\''.$txnid . '\';'; 
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:clientlistsetbranchinphp.php?per=branch");
  
    break;
	
}

$link=null; $stmt=null;
 ?>
	
</body>
</html>