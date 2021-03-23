<?php
		
	$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
        include_once 'trailgeninfo.php';
		include_once($path.'/acrossyrs/dbinit/userinit.php');
        $link=!isset($link)?connect_db(''.date('Y').'_1rtc',0):$link;
		
	$txnid=intval($_REQUEST['IDNo']);
	
	if ($_REQUEST['edit']==2){
	$columnstoedit=array('Nickname','SurName','FirstName','MiddleName','StreetAddress','PlaceOfBirth','MMName','ZipCode','StreetAddress','CityOrProvince','BarangayOrTown','ZipCode_Provincial','StreetAddress_Provincial','CityOrProvince_Provincial','BarangayOrTown_Provincial','ResTel','MobileNo','Email','DateHired','Birthdate','Resigned?','DateResigned','ResignedWithClearance','ResignReason','ReferredBy','SSSNo','PHICNo','PagIbigNo','TIN','NoofDependents','SpouseName','ChildName1','ChildName2','ChildName3','ChildName4','SpouseBirthdate','ChildBirthdate1','ChildBirthdate2','ChildBirthdate3','ChildBirthdate4','ICEPerson','ICEContactInfo','ICEAddress','CivilStatus'); 	
	$sqlupdate='UPDATE 1_gamit.0idinfo SET '; $table='1_gamit.0idinfo';
	$edit=0;
	} else {
	
	include_once('../backendphp/functions/getnumber.php');
	$co=getNumber('Company',$_REQUEST['RCompanyNo']);
	$sqlupdate='UPDATE 1employees SET '.(!is_null($co)?'RCompanyNo='.$co.', ':'').' '; $table='1employees';
	//'RDateHired',
	$columnstoedit=array('Nickname','SurName','FirstName','MiddleName','Gender','UBPATM','ATM','WithLeaves','WithHMO','Resigned','DateHired','Birthdate','SLBalDecCutoff','DirectOrAgency','PaidSLBenefit','WithSat','PrevEmployerNetTaxable','PrevEmployerTaxWHeld','UniformSize','ShirtSize');
	$edit=1;
	}
	if (!allowedToOpen(array(6711,67111),'1rtc')){ $columnstoedit=array(); }
	
	recordtrail($txnid,$table,$link,0);
        
	$sql='';
	foreach ($columnstoedit as $field) {
		
		$sql=$sql.($_POST[$field]==''?'':' `' . $field. '`=\''.$_POST[$field].'\', '); 
		
	}
	
	$sql=$sqlupdate.$sql.' `EncodedByNo`=\''.$_SESSION['(ak0)'].'\', `TimeStamp`=Now() WHERE `IDNo`=\''.$txnid . '\';'; 
// echo $sql; exit();
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:employeeinfo.php?calledfrom=".$edit);
        
 
	
?>