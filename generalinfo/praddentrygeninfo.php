<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php'; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
        // check if allowed
$allowed=array(1,2,3,4,5);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;       
	 
        
        $whichqry=$_GET['w'];
        switch ($whichqry){
        case 'Client':
		include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
		$arclient=comboBoxValue($link, 'gen_info_0arclienttype', 'ARClientDesc', $_REQUEST['ARClientType'], 'ARClientTypeID');	
		$client=comboBoxValue($link, 'gen_info_0clienttype', 'ClientDesc', $_REQUEST['ClientType'], 'ClientTypeID');	
		if (!allowedToOpen(2,'1rtc')) { header('Location:../index.php'); }
	    // $sql='SELECT ClientNo FROM 1clients  order by ClientNo desc Limit 1;'; //where ClientNo<>13000
	    $sql='SELECT ClientNo FROM 1clients UNION SELECT ClientNo FROM hist_incus.purgedclients order by ClientNo desc Limit 1;'; //where ClientNo<>13000
	    $stmt=$link->query($sql);
	    $result=$stmt->fetch();
	    $clientno=$result['ClientNo']+1;
	    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	    $vattypeno=comboBoxValue($link,'`gen_info_1vattype`','VatType',addslashes($_POST['VatType']),'VatTypeNo');
	$sqlinsert='INSERT INTO `1clients` SET `ClientSince`=CURDATE(),`ClientNo`='.$clientno.', `VatTypeNo`='.$vattypeno.', ';
        $sql='';
        $columnstoadd=array('ClientName','ContactPerson','EmailAddress','StreetAddress','Barangay','TownOrCity','Province','TelNo1','TelNo2','Mobile','Terms','CreditLimit','Remarks','PORequired','TIN');
//company tin condition
	$sqlchecker='select CompanyName,TIN from 1companies where TIN=\''.$_POST['TIN'].'\'';
	$stmtchecker=$link->query($sqlchecker); $resultchecker=$stmtchecker->fetch();
	if($stmtchecker->rowCount()!=0){
	 echo'This is the TIN of '.$resultchecker['CompanyName'].'.  Please check again.'; exit();
	}
//
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes(str_replace("\"","'",$_POST[$field])).'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\',  TimeStamp=Now(),ARClientType=\''.$arclient.'\',ClientType=\''.$client.'\';';  //echo $sql; exit();
	// echo $sql; exit(); 
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addentrygeninfo.php?w=Client&done=1");
        break;
      
      case 'ClientBranch':
	if (!allowedToOpen(3,'1rtc')) { header('Location:../index.php'); }
      include_once "../generalinfo/lists.inc";
        $clientno=getValue($link,'1clients','Left(`ClientName`,20)',addslashes($_POST['ClientName']),'ClientNo');
        //$branchno=getValue($link,'1branches','Branch',addslashes($_POST['BranchNo']),'BranchNo');
	$sql='INSERT INTO `gen_info_1branchesclientsjxn` SET `ClientNo`='.$clientno.', `BranchNo`='.intval($_POST['BranchNo']);
        if($_SESSION['(ak0)']==1002){ echo $sql; }
        $stmt=$link->prepare($sql);
	$stmt->execute();
    header("Location:addentrygeninfo.php?w=ClientBranch&done=1");
        break;
      
      case 'Supplier':
	if (!allowedToOpen(4,'1rtc')) { header('Location:../index.php'); }
//	    $sql='SELECT SupplierNo FROM 1suppliers where SupplierNo<800 or SupplierNo>1003 order by SupplierNo desc Limit 1;';
//	    $stmt=$link->query($sql);
//	    $result=$stmt->fetch();
//	    $supplierno=$result['SupplierNo']+1;
	$sqlinsert='INSERT INTO `1suppliers` SET  '; //`SupplierNo`='.$supplierno.',
        $sql='';
        $columnstoadd=array('SupplierNo','SupplierName','ContactPerson','TIN','Address','TelNo1','TelNo2','Terms','SupplierSince','NameonCheck','Inactive');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\',  TimeStamp=Now();'; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addentrygeninfo.php?w=Supplier&done=1");
        break;
      
      case 'SupplierBranch':
	if (!allowedToOpen(5,'1rtc')) { header('Location:../index.php'); }
	$sql='INSERT INTO `gen_info_1branchessuppliersjxn` SET `SupplierNo`='.$_POST['SupplierNo'].', `BranchNo`='.$_POST['BranchNo'];
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
    header("Location:addentrygeninfo.php?w=SupplierBranch&done=1");
        break;
        }
 $link=null; $stmt=null;
?>