<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
if (!allowedToOpen(array(64982,64983),'1rtc')){ echo 'No Permission'; exit(); }
include_once('../switchboard/contents.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila'); $diraddress='../';


include_once('../backendphp/layout/linkstyle.php');


?>

<br><div id="section" style="display: block;">

<?php
if (allowedToOpen(array(64982,64983),'1rtc')){
	echo "<a id='link' href='idinfo.php?w=List'>List of employees with updated information</a>";
}
if (allowedToOpen(6229,'1rtc')){
	echo " <a id='link' href='../systools/preexistingconditions.php'>Declaration of Pre-Existing Conditions</a><br><br>";
}

$which=(!isset($_GET['w'])?'List':$_GET['w']);

switch ($which)
{
	case 'List':
	if (allowedToOpen(array(64982,64983),'1rtc')) {
		$sql='SELECT ei.*,UniformSize,ShirtSize,ei.SpouseName,ei.IDNo AS TxnID,IDExpiry,CONCAT(id.Nickname," ",id.SurName) AS Name,ei.StreetAddress,ei.BarangayOrTown,ei.CityOrProvince,ei.StreetAddress_Provincial,ei.BarangayOrTown_Provincial,ei.CityOrProvince_Provincial,
IF(ei.CivilStatus="S","Single","Married") AS CivilStatus,ei.TimeStamp FROM 0employeeinfo ei JOIN 1employees e ON ei.IDNo=e.IDNo JOIN 1_gamit.0idinfo id ON e.IDNo=id.IDNo ';

		$title='List of employees with updated information';
		if (allowedToOpen(64982,'1rtc')){
          $columnnames=array('IDNo','Name','StreetAddress','CityOrProvince','BarangayOrTown','StreetAddress_Provincial','CityOrProvince_Provincial','BarangayOrTown_Provincial','MobileNo','CivilStatus','SpouseName','Email','ICEPerson','RelationshiptoEmployee','ICEContactInfo','ICEAddress','CTCNo','CTCDateOfIssue','CTCPlaceOfIssue','CTCAmountPaid','UniformSize','ShirtSize','TimeStamp');
		} else {
			$columnnames=array('IDNo','Name','CTCNo','CTCDateOfIssue','CTCPlaceOfIssue','CTCAmountPaid');
		}
		$width='100%';
		if (allowedToOpen(64982,'1rtc')){
			 $editprocess='idinfo.php?w=CheckBeforeUpdate&TxnID='; $editprocesslabel='Update ID Information';
		}
		$sql.=' ORDER BY ei.`TimeStamp` DESC';
		include('../backendphp/layout/displayastable.php');
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	
	
	
// 	case 'UpdateUniformShirtSize':
// 	$sql='UPDATE `1employees` SET UniformSize="'.$_POST['UniformSize'].'",ShirtSize="'.$_POST['ShirtSize'].'",EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE IDNo='.$_SESSION['(ak0)'];
// // echo $sql; exit();
// 	$stmt=$link->prepare($sql); $stmt->execute();
// 	header('Location:idinfo.php?w=MyPersonalInfo');
	
// 	break;
	
	
	
	
	case 'UpdateIDInfo':
	$sqlc='SELECT (SELECT GROUP_CONCAT(COLUMN_NAME) FROM information_schema.columns WHERE table_schema = "1_gamit" AND table_name = "0idinfo" AND column_name NOT IN ("Resigned?")) AS allfields;';
	$stmtc=$link->query($sqlc); $rowc=$stmtc->fetch();
	$allfields=$rowc['allfields'];
	
	$sqlt='INSERT INTO '.$currentyr.'_trail.idinfoedits ('.$allfields.',`Resigned?`,EditOrDel,EditOrDelByNo,EditOrDelTS) SELECT '.$allfields.',`Resigned?`,0,'.$_SESSION['(ak0)'].',NOW() FROM 1_gamit.0idinfo WHERE IDNo='.intval($_GET['TxnID']).'';
	$stmtt=$link->prepare($sqlt); $stmtt->execute();
//end insert into trail
	
	
$sqlinsert='UPDATE 1_gamit.0idinfo id JOIN 0employeeinfo ei ON id.IDNo=ei.IDNo SET 
id.StreetAddress=IFNULL(ei.StreetAddress,id.StreetAddress),
id.CityOrProvince=IFNULL(ei.CityOrProvince,id.CityOrProvince),
id.BarangayOrTown=IFNULL(ei.BarangayOrTown,id.BarangayOrTown),
id.CityOrProvince_Provincial=IFNULL(ei.CityOrProvince_Provincial,id.CityOrProvince_Provincial),
id.StreetAddress_Provincial=IFNULL(ei.StreetAddress_Provincial,id.StreetAddress_Provincial),
id.BarangayOrTown_Provincial=IFNULL(ei.BarangayOrTown_Provincial,id.BarangayOrTown_Provincial),
id.MobileNo=IFNULL(ei.MobileNo,id.MobileNo),
id.CivilStatus=IFNULL(ei.CivilStatus,id.CivilStatus),
id.SpouseName=IFNULL(ei.SpouseName,id.SpouseName),
id.Email=IFNULL(ei.Email,id.Email),
id.ICEPerson=IFNULL(ei.ICEPerson,id.ICEPerson),
id.RelationshiptoEmployee=IFNULL(ei.RelationshiptoEmployee,id.RelationshiptoEmployee),
id.ICEContactInfo=IFNULL(ei.ICEContactInfo,id.ICEContactInfo),
id.ICEAddress=IFNULL(ei.ICEAddress,id.ICEAddress)

 WHERE id.IDNo='.intval($_GET['TxnID']).';';

$stmtinsert=$link->prepare($sqlinsert); $stmtinsert->execute();
	header('Location:idinfo.php?w=List');
	break;

	


	case 'CheckBeforeUpdate':
	echo '<title>Update ID Information</title>';
	$sql='SELECT * FROM 1_gamit.0idinfo WHERE IDNo='.intval($_GET['TxnID']);
	$stmt=$link->query($sql); $resid=$stmt->fetch();
	
	echo '<br><h2>Employee: '.$resid['Nickname'].' '.$resid['SurName'].'</h2><br>';

	echo '<table width="100%">';
	echo '<tr>';
		echo '<td style="padding:5px;width:50%;background-color:#FBF489;"><b>Original ID Information</b><br><br>';
		echo 'StreetAddress: <u>'.$resid['StreetAddress'].'</u><br>';
		echo 'CityOrProvince: <u>'.$resid['CityOrProvince'].'</u><br>';
		echo 'BarangayOrTown: <u>'.$resid['BarangayOrTown'].'</u><br>';
		echo 'CityOrProvince_Provincial: <u>'.$resid['CityOrProvince_Provincial'].'</u><br>';
		echo 'StreetAddress_Provincial: <u>'.$resid['StreetAddress_Provincial'].'</u><br>';
		echo 'BarangayOrTown_Provincial: <u>'.$resid['BarangayOrTown_Provincial'].'</u><br>';
		echo 'MobileNo: <u>'.$resid['MobileNo'].'</u><br>';
		echo 'CivilStatus: <u>'.$resid['CivilStatus'].'</u><br>';
		echo 'SpouseName: <u>'.$resid['SpouseName'].'</u><br>';
		echo 'Email: <u>'.$resid['Email'].'</u><br>';
		echo 'ICEPerson: <u>'.$resid['ICEPerson'].'</u><br>';
		echo 'RelationshiptoEmployee: <u>'.$resid['RelationshiptoEmployee'].'</u><br>';
		echo 'ICEContactInfo: <u>'.$resid['ICEContactInfo'].'</u><br>';
		echo 'ICEAddress: <u>'.$resid['ICEAddress'].'</u><br>';
		echo '</td>';
		$sql='SELECT * FROM 0employeeinfo WHERE IDNo='.intval($_GET['TxnID']);
		$stmt=$link->query($sql); $resei=$stmt->fetch();
		
		echo '<td style="padding:5px;width:50%;background-color:#B3B3FF;"><b>To Copy</b><br><br>';
		echo 'StreetAddress: <u>'.$resei['StreetAddress'].'</u><br>';
		echo 'CityOrProvince: <u>'.$resei['CityOrProvince'].'</u><br>';
		echo 'BarangayOrTown: <u>'.$resei['BarangayOrTown'].'</u><br>';
		echo 'CityOrProvince_Provincial: <u>'.$resei['CityOrProvince_Provincial'].'</u><br>';
		echo 'StreetAddress_Provincial: <u>'.$resei['StreetAddress_Provincial'].'</u><br>';
		echo 'BarangayOrTown_Provincial: <u>'.$resei['BarangayOrTown_Provincial'].'</u><br>';
		echo 'MobileNo: <u>'.$resei['MobileNo'].'</u><br>';
		echo 'CivilStatus: <u>'.$resei['CivilStatus'].'</u><br>';
		echo 'SpouseName: <u>'.$resei['SpouseName'].'</u><br>';
		echo 'Email: <u>'.$resei['Email'].'</u><br>';
		echo 'ICEPerson: <u>'.$resei['ICEPerson'].'</u><br>';
		echo 'RelationshiptoEmployee: <u>'.$resei['RelationshiptoEmployee'].'</u><br>';
		echo 'ICEContactInfo: <u>'.$resei['ICEContactInfo'].'</u><br>';
		echo 'ICEAddress: <u>'.$resei['ICEAddress'].'</u><br>';
		echo '</td>';
		echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '<br><center><form action="idinfo.php?w=UpdateIDInfo&TxnID='.$_GET['TxnID'].'" method="POST"><input type="submit" value="Copy Information" name="btnCopy" style="background-color:blue;padding:7px;font-size:15pt;color:white;width:500px;"  OnClick="return confirm(\'Are you SURE you want to copy information?\');"></form></center>';
	break;



}


 $link=null; $stmt=null;
?>
</div> <!-- end section -->
