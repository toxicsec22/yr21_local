<?php
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$path=$_SERVER['DOCUMENT_ROOT'];
include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

if(isset($_POST['SameAddress'])){
	$permanentaddress=',Street_Permanent="'.addslashes($_POST['StreetPresent']).'",CPID_Permanent='.addslashes($_POST['CPID_Present']).',BTID_Permanent='.addslashes($_POST['BTID_Present']).'';
} else {
	$permanentaddress=',Street_Permanent="'.addslashes($_POST['StreetPermanent']).'",CPID_Permanent='.addslashes($_POST['CPID_Permanent']).',BTID_Permanent='.addslashes($_POST['BTID_Permanent']).'';
}
$sqlinsert='INSERT INTO 0employeeinfo SET IDNo='.$_SESSION['(ak0)'].',Street_Present="'.addslashes($_POST['StreetPresent']).'",CPID_Present='.addslashes($_POST['CPID_Present']).',BTID_Present='.addslashes($_POST['BTID_Present']).''.$permanentaddress.',MobileNo="'.addslashes($_POST['MobileNo']).'",CivilStatus="'.addslashes($_POST['CivilStatus']).'",Email="'.addslashes($_POST['Email']).'",ICEPerson="'.addslashes($_POST['ICEPerson']).'",RelationshiptoEmployee="'.addslashes($_POST['RelationshiptoEmployee']).'",ICEContactInfo="'.addslashes($_POST['ICEContactInfo']).'",ICEAddress="'.addslashes($_POST['ICEAddress']).'";';
$stmtinsert=$link->prepare($sqlinsert); $stmtinsert->execute();
header('Location:/'.$url_folder.'/index.php?done=1');
?>