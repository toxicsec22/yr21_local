<?php
/**
 * Script that handles POST requests
 * This is used for updates for GCash purposes.
 */

 //0idupdate
$path = $_SERVER['DOCUMENT_ROOT'];
include_once $path . '/acrossyrs/dbinit/userinit.php';
include_once $path . '/acrossyrs/logincodes/checkifloggedon.php';

if(!isset($_GET['w'])){
    echo 'NO PERMISSION';
    exit;
}

//function returnInputError(){
//    header('Location: /'.$url_folder.'/index.php?gm=1');
//    exit;
//}

$whichQry = $_GET['w'];

switch($whichQry){
    case 'update':
        if($_SESSION['action_token'] == $_POST['action_token']){
            
            $columnstoedit=array('email', 'streetaddress', 'generaladdress','iceperson', 'relationship', 'icecontactinfo','iceaddress','mobileno','gcashmobile','enroll');
            
            foreach ($columnstoedit as $col){
                if (empty($_POST[$col])) { header('Location: /'.$url_folder.'/index.php?gm=2&col='.strtoupper($col)); exit();}
            }
            
        $link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
        $lockBool = isset($_POST['lock']) and $_POST['lock'] !== null;
		if ($_POST['enroll']=='yes'){
			$enrollBool=1;
		} else if ($_POST['enroll']=='no') {
			$enrollBool=0;
		} else {
			$enrollBool=2;
		}
        // $enrollBool = ((isset($_POST['enroll']) and $_POST['enroll'] === 'yes'));
        $generalAddress = $_POST['generaladdress'];
        //Check if general address actually came from general address
        $generalAddressSQL = 'SELECT CONCAT(ZipCode, " - ", BarangayOrTown, ", " , CityOrProvince) AS PLACE FROM `1_gamit`.`0zipcodes` HAVING PLACE = :generalAddress';
        $prep = $link->prepare($generalAddressSQL);
        $prep->bindValue(':generalAddress', $generalAddress);
        $prep->execute();
        if(!$prep->rowCount() > 0){
            header('Location: /'.$url_folder.'/index.php?gm=1');
        }
        $city = explode(', ', $generalAddress)[1];
        $generalAddress = explode(', ', $generalAddress)[0];

        $zipcode = explode(' - ', $generalAddress, 2)[0];
        $barangay = explode(' - ', $generalAddress, 2)[1];
        
        $updateSQL = 'UPDATE `1_gamit`.`0idinfo`
        SET 
            Email = :email, 
            StreetAddress = :streetAddress, 
            BarangayOrTown = :barangayortown,
            CityOrProvince = :cityorprovince,
            ZipCode = :zipcode, 
            ICEPerson = :iceperson, 
            RelationshipToEmployee = :relationship, 
            ICEContactInfo = :icecontactinfo,
            ICEAddress = :iceaddress,
            LockInfo = :lock, 
            WantEnrollToGCash = :enroll,
            GCashMobileNumber = :gcashno,
            MobileNo = :mobileno,
            EncodedByNo='.$_SESSION['(ak0)'].',
            `TimeStamp`=Now()   
        WHERE IDNo = :id';
        $prep = $link->prepare($updateSQL);
        //transform lock and enroll
        
        $prep->bindValue(':id', $_SESSION['(ak0)']);
        $prep->bindValue(':email', $_POST['email']);
        $prep->bindValue(':streetAddress', $_POST['streetaddress']);
        $prep->bindValue(':barangayortown', $barangay);
        $prep->bindValue(':cityorprovince', $city);
        $prep->bindValue(':zipcode', $zipcode);
        $prep->bindValue(':iceperson', $_POST['iceperson']);
        $prep->bindValue(':relationship', $_POST['relationship']);
        $prep->bindValue(':icecontactinfo', $_POST['icecontactinfo']);
        $prep->bindValue(':iceaddress', $_POST['iceaddress']);
        $prep->bindValue(':mobileno', $_POST['mobileno']);
        $prep->bindValue(':gcashno', $_POST['gcashmobile']);
        $prep->bindValue(':lock', $lockBool);
        $prep->bindValue(':enroll', $enrollBool);

        if($prep->execute()){
            header('Location: /'.$url_folder.'/index.php?gm=0');
            exit;
        }
    }
    else{
        echo 'NO PERMISSION';
        exit;
    }
    break;
}
