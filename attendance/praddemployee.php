<?php
$path=$_SERVER['DOCUMENT_ROOT'];
require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(624,'1rtc')){ echo 'No permission'; exit;}
include_once $path.'/acrossyrs/commonfunctions/fxngenrandpass.php';
include_once $path.'/acrossyrs/commonfunctions/hashandcrypt.php';
$user=$_SESSION['(ak0)'];
// include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

function appendtotable($lastfield,$columnnames,$tablename,$linkinfunction){
	$nooffields=count($columnnames);
        $sql='';
        //echo $sql;
        for ($row = 0; $row <  $nooffields; $row++) {
            $sql=$sql. ((is_null($_POST[$columnnames[$row]]) or $_POST[$columnnames[$row]]==='')?null:'`'. $columnnames[$row] . '`=\'' .addslashes($_POST[$columnnames[$row]]) . '\'' . ($columnnames[$row]==$lastfield?';':', '));
            }
        $sql='insert into '. $tablename. ' set ' . $sql;
        
        $stmt=$linkinfunction->prepare($sql);
        $stmt->execute();
}

switch ($_REQUEST['calledfrom']){
case 1: //newemployee.php
        if (!allowedToOpen(624,'1rtc')){ echo 'No permission'; exit;}
		
		
		
		//check comkey
		$sqlcode = "SELECT Pseudobranch FROM 1branches WHERE BranchNo=".$_POST['BranchNo']."";
        $stmt = $link->query($sqlcode); $row = $stmt->fetch();
		if($stmt->rowCount()>0){
			if ($row['Pseudobranch']==0){ //comkey of branch
				$sqlcode = "SELECT ProgCookie FROM 1branches WHERE BranchNo=".$_POST['BranchNo']."";
				$stmt = $link->query($sqlcode); $row = $stmt->fetch();
				$progcookie=$row['ProgCookie'];
			} else if ($row['Pseudobranch']==2){ //comkey of warehouses
				$sqlcode = "SELECT ProgCookie FROM 1_gamit.1rtcusers ru JOIN attend_30currentpositions cp ON ru.IDNo=cp.IDNo WHERE   cp.PositionID=50 AND cp.BranchNo=".$_POST['BranchNo']."";
				$stmt = $link->query($sqlcode); $row = $stmt->fetch();
				$progcookie=$row['ProgCookie'];
			} else { 
					$progcookie=generatePassword(45); //new employee
			}
		} else {
			echo 'Error BranchNo.'; exit();
		}
		//end
		
		
        //add to 0idinfo table:
    
		// $columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','ProvincialAddress','ResTel','MobileNo','Email','DateHired','Birthdate','ReferredBy','SSSNo','PHICNo','PAGIBIGNo','TIN','NoofDependents','SpouseName','ChildName1','ChildName2','ChildName3','ChildName4','SpouseBirthdate','ChildBirthdate1','ChildBirthdate2','ChildBirthdate3','ChildBirthdate4','ICEPerson','RelationshiptoEmployee','ICEContactInfo','ICEAddress','CivilStatus', 'StreetAddress', 'GCashMobileNumber');
		$columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','StreetAddress_Provincial','ResTel','MobileNo','Email','DateHired','Birthdate','ReferredBy','SSSNo','PHICNo','PAGIBIGNo','TIN','NoofDependents','SpouseName','ChildName1','ChildName2','ChildName3','ChildName4','SpouseBirthdate','ChildBirthdate1','ChildBirthdate2','ChildBirthdate3','ChildBirthdate4','ICEPerson','RelationshiptoEmployee','ICEContactInfo','ICEAddress','PlaceOfBirth','MMName','CivilStatus', 'StreetAddress', 'GCashMobileNumber');
	
		//add this array second to the last
		$extractedarray = array('ZipCode','BarangayOrTown','CityOrProvince','ZipCode_Provincial','BarangayOrTown_Provincial','CityOrProvince_Provincial');
		array_splice( $columnnames, -1, 0, $extractedarray );
		
		$generalAddress = $_POST['ZipCode'];
		$city = explode(', ', $generalAddress)[1];
        $generalAddress = explode(', ', $generalAddress)[0];
        $zipcode = explode(' - ', $generalAddress, 2)[0];
        $barangay = explode(' - ', $generalAddress, 2)[1];
		$_POST['BarangayOrTown']=$barangay;
		$_POST['ZipCode']=$zipcode;
		$_POST['CityOrProvince']=$city;
		
		$generalAddress_provincial = $_POST['ZipCode_Provincial'];
		$city_provincial = explode(', ', $generalAddress_provincial)[1];
        $generalAddress_provincial = explode(', ', $generalAddress_provincial)[0];
        $zipcode_provincial = explode(' - ', $generalAddress_provincial, 2)[0];
        $barangay_provincial = explode(' - ', $generalAddress_provincial, 2)[1];
		$_POST['BarangayOrTown_Provincial']=$barangay_provincial;
		$_POST['ZipCode_Provincial']=$zipcode_provincial;
		$_POST['CityOrProvince_Provincial']=$city_provincial;
		
        $lastfield='PrevEmployerTaxWHeld'; 
        appendtotable('GCashMobileNumber',$columnnames,'1_gamit.0idinfo',$link);
		
         //add to 1employees table:
        // $columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','Gender','UBPATM','WithLeaves','DateHired','RCompanyNo','RDateHired','Birthdate','DirectOrAgency','WithSat','RestDay','PrevEmployerNetTaxable','PrevEmployerTaxWHeld');
        $columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','Gender','UBPATM','WithLeaves','DateHired','RCompanyNo','Birthdate','DirectOrAgency','WithSat','RestDay','PrevEmployerNetTaxable','PrevEmployerTaxWHeld');
        appendtotable($lastfield,$columnnames,'1employees',$link);
		
		$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
        //add to defaultbranch:
        // $sql='Insert into `attend_1defaultbranchassign` set `IDNo`='.addslashes($_POST['IDNo']) .', `DefaultBranchAssignNo`='.addslashes($_POST['BranchNo']);
        // $sql='Insert into `attend_1defaultbranchassign` set `IDNo`='.addslashes($_POST['IDNo']) .', `DefaultBranchAssignNo`='.addslashes($_POST['BranchNo']);
        // $stmt=$link->prepare($sql);
       // $stmt->execute();
       //add to users:
       $newsalt=generatePassword();
       $temppass=addslashes($_POST['IDNo']);
       $newhash=generateHash($temppass);
       $saltforid=generateSaltforid(9);
	   
	   	
       $email=($_POST['RTCEmail']<>'@1rotarytrading.com')?$_POST['RTCEmail']:null;
        $sql='Insert into `1_gamit`.`1rtcusers` set `IDNo`='.addslashes($_POST['IDNo']) .',`uphashmayasin`=\''.$newhash.'\', `saltforid`=\''.$saltforid.'\',`ProgCookie`=\''.$progcookie.'\',`ProgCookieOld`=\''.$progcookie.'\',`Email`=\''.$email.'\', EncodedByNo='.$user;
        $stmt=$link->prepare($sql);
       $stmt->execute();
        //add to attend_2changeofpositions table:
         $sql='Insert into `attend_2changeofpositions` set `IDNo`='.addslashes($_POST['IDNo']) .', SupervisorIDNo='.$_POST['Supervisor'].', AssignedBranchNo='.$_POST['BranchNo'].',`DateofChange`=\''.addslashes($_POST['DateHired']) .'\', `NewPositionID`='.addslashes($_POST['PositionID']).',`EncodedByNo`='.$user;
        $stmt=$link->prepare($sql);
        $stmt->execute();
	//CONTINUOUS TO NEXT FUNCTION
case 2: 
    if (!allowedToOpen(624,'1rtc')){ echo 'No permission'; exit;}
	
	$dateto='';
	if(isset($_GET['manual'])){
		$sqlcmin='SELECT DateToday FROM attend_2attendance WHERE IDNo='.$_POST['IDNo'].' ORDER By DateToday ASC LIMIT 1';
		$stmtcmin=$link->query($sqlcmin);
		$rescmin=$stmtcmin->fetch();
		
		if($stmtcmin->rowCount()>0){
			$dateto=' AND DateToday<"'.$rescmin['DateToday'].'"';
		}
		
	}
	
        //add to attend_2attendance table: 
        $sql='Insert into `attend_2attendance` (`IDNo`,`BranchNo`,`HREncby`,`DateToday`,`LeaveNo`)
            Select '.addslashes($_POST['IDNo']) .', '.addslashes($_POST['BranchNo']) .', '.$user . ', `attend_2attendancedates`.`DateToday`, if(TypeOfDayNo=0,if(Weekday(DateToday)='.addslashes($_POST['RestDay']) .',15,18),if(TypeOfDayNo=4,18,TypeOfDayNo+10)) from `attend_2attendancedates` where (`DateToday`>=\''. addslashes($_POST['DateHired']).'\' '.$dateto.')'; 
        $stmt=$link->prepare($sql);
        $stmt->execute();
	if ($_REQUEST['calledfrom']==1){ header("Location:../payroll/addentry.php?w=Rates");} else { header("Location:encodeattend.php?w=AddAttendRecords&IDNo=".$_POST['IDNo']);}
        break;

default:
	goto goback;
	break;
}

goback:
     
?>