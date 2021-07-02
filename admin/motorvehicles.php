<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(8288,'1rtc')) { echo 'No permission'; exit; }
// if (!allowedToOpen(5230,'1rtc')) { echo 'No permission'; exit; }

if($_GET['w']=="CRPic" OR $_GET['w']=="ORPic") { goto skipcontents;}
$showbranches=false;
include_once('../switchboard/contents.php');
skipcontents:



 

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
// include_once('motorvehiclesapproval.php');

//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila'); $diraddress='../';

?>

<br><div id="section" style="display: block;">

<?php
include_once('motorvehicleslinks.php');
$which=(!isset($_GET['w'])?'VehicleTypeList':$_GET['w']); //8285 GenAdmin
// $which=(!isset($_GET['w'])?((allowedToOpen(8285,'1rtc'))?'ListOfRepairs':'FuelConsumption'):$_GET['w']);

if (in_array($which,array('VehicleTypeList','EditSpecificsVehicleType'))){
   $sql='SELECT vt.*, VTID AS TxnID, CONCAT(Nickname," ",Surname) AS EncodedBy FROM admin_0vehicletype vt LEFT JOIN 1_gamit.0idinfo id ON vt.EncodedByNo=id.IDNo';
   $columnnameslist=array('VehicleType', 'EncodedBy', 'Timestamp');
   $columnstoadd=array('VehicleType');
}

if (in_array($which,array('AddVehicleType','EditVehicleType'))){
   $columnstoadd=array('VehicleType');
}

if (in_array($which,array('ORPic','CRPic'))){
	
	// header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	// header("Cache-Control: post-check=0, pre-check=0", false);
	// header("Pragma: no-cache");
	
	$title = $_GET['w'];
	echo '<title>'.$title.'</title>';
   echo '<img src="orcrpics/'.$_GET['TxnID'].$title.'.jpg" alt="No Uploaded '.$title.'."/>';
}
if (in_array($which,array('VehicleList','VehicleRegistration'))){
	if($_GET['w']=='VehicleList'){
		$pictype='CRPic';
		$txnval='';
		$show='text';
		$table='admin_1vehiclelist';
	} else {
		$pictype='ORPic';
		$txnval=$_GET['TxnID'];
		$show='hidden';
		$table='admin_1vehiclelistsub';
	}
	if (allowedToOpen(8285,'1rtc')){
	echo '<br/><br/><a id=\'link\' href="motorvehicles.php?w=ManagePic">Manage Pics</a> ';
	echo '<br/><br/><form action="motorvehicleuploadorcrpic.php" method="POST" enctype="multipart/form-data">
				<font size="small">Upload Photo of '.$pictype.' for VehicleID? (Only *.jpg files allowed.) <input type="'.$show.'" name="UploadID" size=4 autocomplete="off" list="vehicleinfolist" value='.$txnval.'> <input type="hidden" name="addlname" value="'.$pictype.'"><input type="hidden" name="table" value="'.$table.'">
                '.str_repeat('&nbsp;',10).' <input type="file" name="userfile" accept="image/jpg">  
				<input type="submit" name="submit" value="Submit"> 
                </font> </form><br/><br/>';
	}
}

if (in_array($which,array('FuelTypeList','EditSpecificsFuelType'))){
   $sql='SELECT ft.*, FTID AS TxnID, CONCAT(Nickname," ",Surname) AS EncodedBy FROM admin_0fueltype ft LEFT JOIN 1_gamit.0idinfo id ON ft.EncodedByNo=id.IDNo';
 $columnnameslist=array('FuelType', 'EncodedBy', 'Timestamp');
   $columnstoadd=array('FuelType');
}

if (in_array($which,array('AddFuelType','EditFuelType'))){
   $columnstoadd=array('FuelType');
}

if (in_array($which,array('RepairShopList','EditSpecificsRepairShop'))){
	include_once('../backendphp/layout/showencodedbybutton.php');
   $sql='SELECT s.*, SupplierNo AS TxnID, SupplierNo AS ShopID, CONCAT(Nickname," ",Surname) AS EncodedBy FROM `1suppliers` s LEFT JOIN 1_gamit.0idinfo id ON s.EncodedByNo=id.IDNo';
 $columnnameslist=array('ShopID','SupplierName','ContactPerson','TIN','Address','TelNo1','SupplierSince');
   $columnstoadd=array('ShopID','SupplierName','ContactPerson','TIN','Address','TelNo1','SupplierSince');
   if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
}

if (in_array($which,array('AddRepairShop','EditRepairShop'))){
    $columnstoadd=array('SupplierName','ContactPerson','TIN','Address','TelNo1','SupplierSince');
}

if (in_array($which,array('VehicleAssign','EditSpecificsVehicleAssign'))){
	include_once('../backendphp/layout/showencodedbybutton.php');
	$sqlall='SELECT va.*, CONCAT(Brand," ",Series) AS Model, PlateNo, CONCAT(e.Firstname," ",e.Surname) AS EncodedBy, Fullname AS CurrentlyAssignedTo, Fullname AS RecentlyAssignedTo, b.Branch AS AssignedBranch FROM admin_2vehicleassign va LEFT JOIN `1employees` e ON va.EncodedByNo=e.IDNo JOIN `1branches` b ON va.BranchNo=b.BranchNo JOIN `attend_30currentpositions` cp ON va.CurrentlyAssignedIDNo=cp.IDNo JOIN admin_1vehiclelist vl ON va.VehicleID=vl.TxnID'; //echo $sqlall;
	echo comboBox($link,'SELECT CONCAT(Brand," ",Series,": ",PlateNo) AS Model, TxnID FROM `admin_1vehiclelist`','Model','TxnID','vehicleinfolist');
	
	echo comboBox($link,'SELECT IDNo, FullName FROM attend_30currentpositions','IDNo','FullName','riderlist'); // WHERE PositionID=38; not just riders
	
	$columnnameslist=array('DateAssigned','Model', 'PlateNo', 'AssignedBranch', 'CurrentlyAssignedTo');
	$columnstoadd=array('DateAssigned','VehicleID', 'PlateNo', 'AssignedBranch', 'CurrentlyAssignedTo');
	
	if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
}

if (in_array($which,array('RequestRepair','EditSpecificsListOfRepairs'))){
	echo comboBox($link,'SELECT SupplierNo, SupplierName FROM `1suppliers` WHERE InvtySupplier=2','SupplierNo','SupplierName','shoplist');
}

if (in_array($which,array('VehicleAssign','EditSpecificsVehicleAssign','RequestRepair'))){
	if (allowedToOpen(8287,'1rtc')) {$br=' WHERE BranchNo='.$_SESSION['bnum'];} else {$br='';}
	echo comboBox($link,'SELECT Branch, BranchNo FROM `1branches`'.$br.'','BranchNo','Branch','branchlist');
}

if (in_array($which,array('AddVehicleAssign','EditVehicleAssign'))){
	$vehicle=addslashes($_POST['VehicleID']);
  
   $branchno=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['AssignedBranch']),'BranchNo');
   $currentlyassign=comboBoxValue($link,'attend_30currentpositions','Fullname',addslashes($_POST['CurrentlyAssignedTo']),'IDNo');
}

if (in_array($which,array('VehicleList','EditSpecificsVehicleList'))){
	include_once('../backendphp/layout/showencodedbybutton.php');
	echo comboBox($link,'SELECT VehicleType, VTID FROM `admin_0vehicletype`','VTID','VehicleType','vehicletypelist');
	echo comboBox($link,'SELECT FuelType,FTID FROM admin_0fueltype;','FTID','FuelType','fueltypelist');
	$sql='SELECT vl.*, vl.TxnID, VehicleType, ORNo, ORDate, FuelType, CONCAT(id.Nickname," ",id.Surname) AS EncodedBy FROM admin_1vehiclelist vl JOIN admin_0vehicletype vt ON vl.VTID=vt.VTID JOIN admin_0fueltype ft ON vl.FTID=ft.FTID JOIN 1_gamit.0idinfo id ON vl.EncodedByNo=id.IDNo LEFT JOIN admin_1vehiclelistsub vls ON vl.TxnID=vls.TxnID'; 
	
	$columnnameslist=array('VehicleType', 'Brand', 'Series', 'PlateNo', 'CRNo', 'CRDate', 'FuelType', 'Color','DueMonth');
	$columnstoadd=array('VehicleType', 'Brand', 'Series', 'PlateNo', 'CRNo', 'CRDate', 'FuelType', 'Color','DueMonth');
	if ($showenc==1) { array_push($columnnameslist,'EncodedBy','Timestamp'); }
	
}

if (in_array($which,array('VehicleList','EncodeFuelConsumption'))){
	// echo comboBox($link,'SELECT CONCAT(Brand," ",Series,": ",PlateNo) AS ModelPlateNo, vl.TxnID FROM `admin_1vehiclelist` vl LEFT JOIN admin_2vehicleassign va ON vl.TxnID=va.VehicleID','ModelPlateNo','TxnID','vehicleinfolist');
	// echo comboBox($link,'SELECT CONCAT(Brand," ",Series,": ",PlateNo) AS ModelPlateNo, TxnID FROM `admin_1vehiclelist`','ModelPlateNo','TxnID','vehicleinfolist');
	echo comboBox($link,'SELECT CONCAT("(",Branch,") ",Brand," ",Series,": ",PlateNo) AS ModelPlateNo, vl.TxnID FROM `admin_1vehiclelist` vl LEFT JOIN admin_2vehicleassign va ON vl.TxnID=va.VehicleID JOIN `1branches` b ON va.BranchNo=b.BranchNo '.((allowedToOpen(8287,'1rtc'))?' WHERE va.BranchNo='.$_SESSION['bnum'].' AND Active=1 AND Status=1':' WHERE Active=1 AND Status=1').' ORDER BY Branch','ModelPlateNo','TxnID','vehicleinfolist');
}


if (in_array($which,array('AddVehicleList','EditVehicleList'))){
	$vehicletype=comboBoxValue($link,'admin_0vehicletype','VehicleType',addslashes($_POST['VehicleType']),'VTID');
	$ftid=comboBoxValue($link,'admin_0fueltype','FuelType',addslashes($_POST['FuelType']),'FTID');
	$columnstoadd=array('Brand', 'Series', 'PlateNo', 'CRNo', 'CRDate', 'Color','DueMonth');
}


if (in_array($which,array('VehicleRegistration','EditSpecificsVehicleRegistration'))){
	include_once('../backendphp/layout/showencodedbybutton.php');
	$sql='SELECT vls.*,DueMonth, vls.TxnID, CONCAT(Brand," ",Series) AS Model, PlateNo, CONCAT(id.Nickname," ",id.Surname) AS EncodedBy FROM admin_1vehiclelistsub vls JOIN admin_1vehiclelist vl ON vls.TxnID=vl.TxnID LEFT JOIN 1_gamit.0idinfo id ON vl.EncodedByNo=id.IDNo'; 
	
	$columnnameslist=array('Model', 'PlateNo','DueMonth', 'ORDate', 'ORNo');
	$columnstoadd=array('ORDate', 'ORNo');
	if ($showenc==1) { array_push($columnnameslist,'EncodedBy','Timestamp'); }
	
}
if (in_array($which,array('AddRegistrationInfo','EditRegistrationInfo'))){
        if((substr($_POST['ORDate'],0,4)<=2015) OR (substr($_POST['ORDate'],0,4)>$currentyr)){ echo 'Please enter the correct date.  This is '.$currentyr.' data.'; exit;}
	$columnstoadd=array('ORDate', 'ORNo');
}

if (in_array($which,array('EditSpecificsVehicleRegistration','EditRegistrationInfo','DeleteVehicleRegistration'))){
        $txnid=intval($_GET['TxnID']);
        $txnsubid=intval($_GET['TxnSubID']);
}
            
if (in_array($which,array('RequestRepair','Lookup'))){
	if (!isset($_GET['Print'])){ $printstyle='';
    ?>
    <div style='background-color: #e6e6e6;
  width: 700px;
  border: 2px solid grey;
  padding: 25px;
  margin: 25px;'>
    <b>Process:</b><br/><br/>    
    <ol>
        <li>Requester encodes all details, and sets request as complete.</li>
        <li>Ops Head approves/denies.</li>
        <li>If approved by Ops Head, Gen Admin Head approves/denies.</li>
        <li>If approved by Gen Admin Head, Requester acknowledges his request.</li>
        <li>After repair, requester sets the request with invoice number as complete.</li>
    </ol><br/>
	<ul><li>
	Note: Each step can set previous step as incomplete in order to edit the request, if necessary.</li></ul>
    </div>
    <?php
	} else {
		$printstyle = 'style="background-color:transparent;border-color:transparent;color:black;text-align: right;"'; 
	}
}

if (in_array($which,array('Lookup','Print'))){
	
	$txnid=$_GET['TxnID'];
	// $sql = 'SELECT rr.*, CONCAT(id.Nickname, " ", id.Surname) AS PreparedBy, CONCAT(id2.Nickname, " ", id2.Surname) AS CurrentlyAssignedTo, CONCAT(id3.Nickname, " ", id3.Surname) AS ApprovedBy, CONCAT(id4.Nickname, " ", id4.Surname) AS Approved2By, CONCAT(Brand," ",Series) AS Model, vl.PlateNo, Branch FROM admin_2repairrequest rr LEFT JOIN 1_gamit.0idinfo id ON rr.RequestedByNo=id.IDNo JOIN admin_2vehicleassign va ON rr.BranchNo=va.BranchNo JOIN `1branches` b ON rr.BranchNo=b.BranchNo JOIN admin_1vehiclelist vl ON va.VehicleID=vl.TxnID LEFT JOIN 1_gamit.0idinfo id2 ON rr.CurrentlyAssignedIDNo=id2.IDNo LEFT JOIN 1_gamit.0idinfo id3 ON rr.ApprovedByNo=id3.IDNo LEFT JOIN 1_gamit.0idinfo id4 ON rr.Approved2ByNo=id4.IDNo WHERE rr.TxnID = '.$txnid;
	$sql = 'SELECT rr.*, CONCAT(id.Nickname, " ", id.Surname) AS PreparedBy, CONCAT(id2.Nickname, " ", id2.Surname) AS CurrentlyAssignedTo, CONCAT(id3.Nickname, " ", id3.Surname) AS ApprovedBy, CONCAT(id4.Nickname, " ", id4.Surname) AS Approved2By, CONCAT(Brand," ",Series) AS Model, vl.PlateNo, Branch FROM admin_2repairrequest rr LEFT JOIN 1_gamit.0idinfo id ON rr.RequestedByNo=id.IDNo JOIN admin_2vehicleassign va ON rr.VehicleID=va.VehicleID JOIN `1branches` b ON rr.BranchNo=b.BranchNo JOIN admin_1vehiclelist vl ON va.VehicleID=vl.TxnID LEFT JOIN 1_gamit.0idinfo id2 ON rr.CurrentlyAssignedIDNo=id2.IDNo LEFT JOIN 1_gamit.0idinfo id3 ON rr.ApprovedByNo=id3.IDNo LEFT JOIN 1_gamit.0idinfo id4 ON rr.Approved2ByNo=id4.IDNo WHERE rr.TxnID = '.$txnid.' AND va.Status=1';
	
	// $sql = 'SELECT rr.*, CONCAT(id.Nickname, " ", id.Surname) AS PreparedBy, CONCAT(id2.Nickname, " ", id2.Surname) AS CurrentlyAssignedTo, CONCAT(id3.Nickname, " ", id3.Surname) AS ApprovedBy, CONCAT(id4.Nickname, " ", id4.Surname) AS Approved2By, CONCAT(Brand," ",Series) AS Model, vl.PlateNo, Branch FROM admin_2repairrequest rr LEFT JOIN 1_gamit.0idinfo id ON rr.RequestedByNo=id.IDNo JOIN admin_2vehicleassign va ON rr.BranchNo=va.BranchNo JOIN `1branches` b ON rr.BranchNo=b.BranchNo JOIN admin_1vehiclelist vl ON va.VehicleID=vl.TxnID LEFT JOIN 1_gamit.0idinfo id2 ON rr.CurrentlyAssignedIDNo=id2.IDNo LEFT JOIN 1_gamit.0idinfo id3 ON rr.ApprovedByNo=id3.IDNo LEFT JOIN 1_gamit.0idinfo id4 ON rr.Approved2ByNo=id4.IDNo WHERE rr.TxnID = '.$txnid.' AND va.Status=1';
	$stmt=$link->query($sql); $res=$stmt->fetch(); //echo $sql;
	
	$title='Request for Vehicle Repair';
	
	
}

if (in_array($which,array('FuelConsumption','EncodeFuelConsumption','FuelConsumptionPerVehicleLookup'))){
	// $sqlgetlastkm = 'SELECT KmReading FROM admin_2fuelconsumption fc2 WHERE IF(fc2.Date=fc.Date, fc2.TxnID<fc.TxnID, fc.Date < fc.Date) AND fc2.VehicleID = fc.VehicleID ORDER BY Date DESC LIMIT 1';
	$sqlgetlastkm = 'SELECT KmReading FROM admin_2fuelconsumption fc2 WHERE fc2.TxnID<fc.TxnID AND fc2.VehicleID = fc.VehicleID ORDER BY Date DESC LIMIT 1';
}


if (in_array($which,array('FuelConsumption','EncodeFuelConsumption','FuelConsumptionPerVehicleLookup'))){
		include_once('../backendphp/layout/showencodedbybutton.php');
		if ($_GET['w']=='EncodeFuelConsumption' OR $_GET['w']=='FuelConsumption'){
			$addsq='';
		} else {
			$addsq=' AND fc.VehicleID='.intval($_GET['VehicleID']).'';
		}
		
		$sql='SELECT fc.*, CONCAT(Brand," ",Series,": ",PlateNo) AS ModelPlateNo, Branch, TRUNCATE((Liter*PriceperLiter),2) AS TotalAmt, CONCAT(TRUNCATE(((KmReading-('.$sqlgetlastkm.'))/Liter),2), " KM") AS `Km/Liter`, CONCAT(TRUNCATE(Liter/(KmReading-('.$sqlgetlastkm.')),2)," L") AS `Liter/Km`, CONCAT(TRUNCATE((Liter/(KmReading-('.$sqlgetlastkm.')))*PriceperLiter,2)," Php") AS `Peso/Km`, CONCAT(TRUNCATE((KmReading-('.$sqlgetlastkm.')),2)," KM") AS `DistanceTrav`, FuelType, CONCAT(id.Nickname, " ", id.Surname) AS EncodedBy FROM admin_2fuelconsumption fc JOIN 1_gamit.0idinfo id ON fc.EncodedByNo=id.IDNo JOIN admin_1vehiclelist vl ON fc.VehicleID=vl.TxnID JOIN admin_0fueltype ft ON vl.FTID=ft.FTID JOIN admin_2vehicleassign va ON vl.TxnID=va.VehicleID JOIN `1branches` b ON va.BranchNo=b.BranchNo WHERE Status=1';
		// echo $sql;
		$columnnameslist=array('Date','ModelPlateNo','Branch','FuelType','KmReading','Liter','PriceperLiter','TotalAmt','InvoiceNo','DistanceTrav','Km/Liter','Liter/Km','Peso/Km','Remarks','EncodedBy','TimeStamp');
		
		
		if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
}

if (in_array($which,array('ListOfRepairs','EditSpecificsListOfRepairs'))){
	echo '<br>';
	
   $sql='SELECT rr.*, (CASE
		WHEN Approved = 1 THEN "Approved"
		WHEN Approved = 2 THEN "Denied"
		ELSE "Pending"
	END) AS OpsORHeadApproval, (CASE
		WHEN Approved2 = 1 THEN "Approved"
		WHEN Approved2 = 2 THEN "Denied"
		ELSE "Pending"
	END) AS GenAdminApproval, (CASE
		WHEN Acknowledged = 1 THEN "Done"
		WHEN Acknowledged = 2 THEN "Denied"
		ELSE "Pending"
	END) AS Acknowledgement, (CASE
		WHEN Finished = 1 THEN "Finished"
		ELSE "Not Yet"
	END) AS RepairFinished, IF(RequestCompleted<>0,"Done","Pending") AS RequestCompleted, SupplierName AS RepairShop, b.Branch, CONCAT(e.Nickname, " ", e.Surname) AS RequestedBy FROM admin_2repairrequest rr JOIN `1branches` b ON rr.BranchNo=b.BranchNo JOIN `1employees` e ON rr.RequestedByNo=e.IDNo JOIN `1suppliers` s ON rr.RSID=s.SupplierNo';
   
   $columnnameslist=array('Branch', 'RequestedBy', 'DateRequest', 'RepairShop', 'Particulars', 'Amount', 'RequestCompleted', 'OpsORHeadApproval', 'GenAdminApproval', 'Acknowledgement','RepairFinished','InvoiceNo');
   $columnstoadd=array('Branch', 'RequestedBy', 'DateRequest', 'Particulars', 'Amount', 'RequestCompleted');
}

switch ($which)
{
	case 'VehicleTypeList':
	if (allowedToOpen(8285,'1rtc')) {
		$title='Vehicle Type'; 
                
              //  $formdesc='Add Vehicle Type Menu.';
				$method='post';
				$columnnames=array(
				array('field'=>'VehicleType','type'=>'text','size'=>25,'required'=>true));
							
		$action='motorvehicles.php?w=AddVehicleType'; $fieldsinrow=4; $liststoshow=array();
		
		include('../backendphp/layout/inputmainform.php');
		
		$delprocess='motorvehicles.php?w=DeleteVehicleType&VTID=';
		$editprocess='motorvehicles.php?w=EditSpecificsVehicleType&VTID='; $editprocesslabel='Edit';
     
		$title=''; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='70%';
		
		include('../backendphp/layout/displayastable.php');
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	case 'AddVehicleType':
	if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='';
		$sql='INSERT INTO `admin_0vehicletype` SET VehicleType="'.$_POST['VehicleType'].'", EncodedByNo='.$_SESSION['(ak0)'];
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	case 'EditSpecificsVehicleType':
        if (allowedToOpen(8285,'1rtc')) { //header('Location:motorvehicles.php?denied=true'); }
			$title='Edit Specifics';
			$txnid=intval($_GET['VTID']);

			$sql=$sql.' WHERE VTID='.$txnid;
			$columnstoedit=$columnstoadd;
			
			$columnnames=$columnnameslist;
			
			$editprocess='motorvehicles.php?w=EditVehicleType&VTID='.$txnid;
			
			include('../backendphp/layout/editspecificsforlists.php');
		} else {
			echo 'No permission'; exit;
		}
	break;
	
	case 'EditVehicleType':
		if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['VTID']);
		$sql='';
		
		$sql='UPDATE `admin_0vehicletype` SET VehicleType="'.$_REQUEST['VehicleType'].'", EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now() WHERE VTID='.$txnid;
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:motorvehicles.php?w=VehicleTypeList");
		} else {
		echo 'No permission'; exit;
		}
		
		
    break;
	
	case 'DeleteVehicleType':
	if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `admin_0vehicletype` WHERE VTID='.intval($_GET['VTID']);
		
		$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:".$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}

    break;
	
	
	case 'FuelTypeList':
	if (allowedToOpen(8285,'1rtc')) {
		$title='Fuel Type'; 
                
               // $formdesc='Add Fuel Type Menu.';
				$method='post';
				$columnnames=array(
				array('field'=>'FuelType','type'=>'text','size'=>25,'required'=>true));
							
		$action='motorvehicles.php?w=AddFuelType'; $fieldsinrow=4; $liststoshow=array();
		
		include('../backendphp/layout/inputmainform.php');
		
		$delprocess='motorvehicles.php?w=DeleteFuelType&FTID=';
		$editprocess='motorvehicles.php?w=EditSpecificsFuelType&FTID='; $editprocesslabel='Edit';
     
		$title=''; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='70%';
		
		include('../backendphp/layout/displayastable.php'); 
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	
	case 'AddFuelType':
	if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='';
		$sql='INSERT INTO `admin_0fueltype` SET FuelType="'.$_POST['FuelType'].'", EncodedByNo='.$_SESSION['(ak0)'];
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	
	case 'EditSpecificsFuelType':
        if (allowedToOpen(8285,'1rtc')) { //header('Location:motorvehicles.php?denied=true'); }
		$title='Edit Specifics';
		$txnid=intval($_GET['FTID']);

		$sql=$sql.' WHERE FTID='.$txnid; 
		$columnstoedit=$columnstoadd;
		
		$columnnames=$columnnameslist;
		
		$editprocess='motorvehicles.php?w=EditFuelType&FTID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'EditFuelType':
		
		if (allowedToOpen(8285,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$txnid = intval($_GET['FTID']);
		$sql='';
		
		$sql='UPDATE `admin_0fueltype` SET FuelType="'.$_REQUEST['FuelType'].'", EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now() WHERE FTID='.$txnid;
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:motorvehicles.php?w=FuelTypeList");
		} else {
		echo 'No permission'; exit;
		}
		
		
    break;
	
	case 'DeleteFuelType':
	if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `admin_0fueltype` WHERE FTID='.intval($_GET['FTID']);
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
	header("Location:".$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}
    break;
	
	
	case 'RepairShopList':
	if (allowedToOpen(8285,'1rtc')) {
		$title='Accredited Repair Shops'; 
                $sql.=' WHERE InvtySupplier=2';
                //$formdesc='Add Repair Shop Menu.';
				$method='post';
				 $columnnames=array(
                       array('field'=>'ShopID','caption'=>'ShopID (500-700 Only)','type'=>'text','size'=>20,'required'=>true),
                       array('field'=>'SupplierName','caption'=>'ShopName','type'=>'text','size'=>20,'required'=>true),
                       array('field'=>'ContactPerson','type'=>'text','size'=>20,'required'=>true),
			array('field'=>'TIN', 'type'=>'text','size'=>20, 'required'=>true),
                       array('field'=>'Address', 'type'=>'text','size'=>20, 'rows'=>'3','cols'=>'30','required'=>true),
                        array('field'=>'TelNo1','type'=>'text','size'=>20, 'required'=>true),
			array('field'=>'SupplierSince', 'caption'=>'ShopSince', 'type'=>'date','size'=>20,'value'=>date('Y-m-d'),'required'=>true),
                    array('field'=>'InvtySupplier', 'type'=>'hidden','size'=>10,'value'=>2)
		      );
							
		$action='motorvehicles.php?w=AddRepairShop'; $fieldsinrow=3; $liststoshow=array();
		
		include('../backendphp/layout/inputmainform.php');
		
		$delprocess='motorvehicles.php?w=DeleteRepairShop&TxnID=';
		$editprocess='motorvehicles.php?w=EditSpecificsRepairShop&TxnID='; $editprocesslabel='Edit';
     
		$title=''; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='100%';
		
		include('../backendphp/layout/displayastable.php'); 
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'AddRepairShop':
	if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='';
		array_push($columnstoadd,'InvtySupplier');
		
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		
		$supplierno=$_POST['ShopID']; //echo $supplierno; 
		
		if ($supplierno<500 OR $supplierno>700){
			echo 'Invalid Shop ID. (500-700 only with no duplicates.)'; exit();
		}
	
		$sql='INSERT INTO `1suppliers` SET '.$sql.' TimeStamp=Now(), SupplierNo='.$supplierno.', EncodedByNo='.$_SESSION['(ak0)']; 
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	
	case 'EditSpecificsRepairShop':
        if (allowedToOpen(8285,'1rtc')) { //header('Location:motorvehicles.php?denied=true'); }
		$title='Edit Specifics';
		$txnid=intval($_GET['TxnID']);

		$sql=$sql.' WHERE SupplierNo='.$txnid;
		$columnstoedit=$columnstoadd;
		
		$columnnames=$columnnameslist;
		
		$editprocess='motorvehicles.php?w=EditRepairShop&TxnID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'EditRepairShop':
		
		if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$txnid = intval($_GET['TxnID']);
		$supplierno=$_POST['ShopID'];
		if ($supplierno<500 OR $supplierno>700){
			echo 'Invalid Shop ID. (500-700 only with no duplicates.)'; exit();
		}
		
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='UPDATE `1suppliers` SET '.$sql.' EncodedByNo='.$_SESSION['(ak0)'].', SupplierNo='.$supplierno.', Timestamp=Now() WHERE SupplierNo='.$txnid;
		
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:motorvehicles.php?w=RepairShopList");
		} else {
		echo 'No permission'; exit;
		}
		
    break;
	
	case 'DeleteRepairShop':
	if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `0repairshop` WHERE RSID='.intval($_GET['RSID']);
		
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:".$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}
	
    break;
	
	
	case 'VehicleAssign':
	if ((allowedToOpen(8285,'1rtc')) OR (allowedToOpen(8286,'1rtc')) OR (allowedToOpen(8287,'1rtc')) OR (allowedToOpen(82871,'1rtc'))){
		$title='Vehicle Assignments'; 
        $sql=$sqlall.' WHERE Status=1';
		if (((!allowedToOpen(8287,'1rtc')) AND (!allowedToOpen(82871,'1rtc'))) OR (allowedToOpen(8285,'1rtc'))){
                $formdesc='</i><br><a href="motorvehicles.php?w=UploadVehiclesAssign">Upload Data</a><i>';
				$method='post';
				$columnnames=array(
				array('field'=>'DateAssigned', 'type'=>'date','size'=>15,'required'=>true,'value'=>date('Y-m-d')),
				array('field'=>'VehicleID', 'caption'=>'Model / PlateNo', 'type'=>'text','list'=>'vehicleinfolist','size'=>15,'required'=>true),
				array('field'=>'AssignedBranch','type'=>'text','list'=>'branchlist','size'=>15,'required'=>true),
				array('field'=>'CurrentlyAssignedTo','type'=>'text','list'=>'riderlist','size'=>15,'required'=>true)
				);
		
		$action='motorvehicles.php?w=AddVehicleAssign'; $fieldsinrow=5; $liststoshow=array();
		
		
			
		include('../backendphp/layout/inputmainform.php');
		 $title=''; $formdesc=''; $txnidname='TxnID';
		$delprocess='motorvehicles.php?w=DeleteVehicleAssign&TxnID=';
		$editprocess='motorvehicles.php?w=EditSpecificsVehicleAssign&TxnID='; $editprocesslabel='Edit';
		
		}
		// if (allowedToOpen(8287,'1rtc')){
			// $title=$;
		// }
		
		
		$columnnames=$columnnameslist;       
		
		$width='70%';
		
		include('../backendphp/layout/displayastable.php');
		
		echo '<br/><br/>';
		echo '<h3>Vehicle Assignment History</h3>';
		$sql=$sqlall.' WHERE Status=0 ORDER BY BranchNo, DateAssigned DESC';
		$columnnameslist=array('DateAssigned', 'Model', 'PlateNo', 'AssignedBranch', 'RecentlyAssignedTo');
		$title=''; $formdesc=''; $txnidname='TxnID';
		
		unset($delprocess, $editprocess,$editprocesslabel);
		
		$columnnames=$columnnameslist;       
		
		$width='70%';
		
		include('../backendphp/layout/displayastable.php'); 
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'AddVehicleAssign':
	if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		// $sqlcnt = 'SELECT VehicleID, COUNT(VehicleID) AS cnt FROM admin_2vehicleassign WHERE VehicleID='.$vehicle.' AND Status=1'; //echo $sqlcnt;
		// $stmtcnt = $link->query($sqlcnt);
		// $row = $stmtcnt->fetch();
		
		// if ($row['cnt']>0){
			// $sql='';
			// $sql='UPDATE admin_2vehicleassign SET EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now(), Status=0 WHERE VehicleID='.$row['VehicleID'];
			
			// $stmt=$link->prepare($sql); $stmt->execute();
		// }
		// $sqlcnt = 'SELECT VehicleID, TxnID FROM admin_2vehicleassign WHERE VehicleID='.$vehicle.' AND Status=1'; //echo $sqlcnt;
		// $stmtcnt = $link->query($sqlcnt);
		// $row = $stmtcnt->fetch();
		
		// if ($stmtcnt->rowCount()>0){
			// $sql='';
			// $sql='UPDATE admin_2vehicleassign SET Status=0 WHERE VehicleID='.$vehicle;
			// $stmt=$link->prepare($sql); $stmt->execute();
		// }
		$sql='UPDATE admin_2vehicleassign SET Status=0 WHERE VehicleID='.$vehicle.' AND Status=1';
			$stmt=$link->prepare($sql); $stmt->execute();
			
			
		$sql='';
		$sql='INSERT INTO `admin_2vehicleassign` SET EncodedByNo='.$_SESSION['(ak0)'].', DateAssigned="'.$_POST['DateAssigned'].'", VehicleID='.$vehicle.', CurrentlyAssignedIDNo='.$currentlyassign.', BranchNo='.$branchno.', TimeStamp=Now()'; 
      
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	
	case 'EditSpecificsVehicleAssign':
        if (allowedToOpen(8285,'1rtc')) { //header('Location:motorvehicles.php?denied=true'); }
		$title='Edit Specifics';
		
		$txnid=intval($_GET['TxnID']);

		$sql=$sqlall.' WHERE va.TxnID='.$txnid.''; 
		$columnstoedit=$columnstoadd;
		
		$columnswithlists=array('VehicleID','AssignedBranch','CurrentlyAssignedTo');
		$listsname=array('VehicleID'=>'vehicleinfolist','AssignedBranch'=>'branchlist','CurrentlyAssignedTo'=>'riderlist');
		
		$columnnames=$columnswithlists;
		
		$editprocess='motorvehicles.php?w=EditVehicleAssign&TxnID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	
	case 'EditVehicleAssign':
		if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$txnid = intval($_GET['TxnID']);
		$sql='';
		
		$sql='UPDATE `admin_2vehicleassign` SET VehicleID="'.$vehicle.'", BranchNo="'.$branchno.'", DateAssigned="'.$_POST['DateAssigned'].'", CurrentlyAssignedIDNo="'.$currentlyassign.'", EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now() WHERE TxnID='.$txnid;
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:motorvehicles.php?w=VehicleAssign");
		} else {
		echo 'No permission'; exit;
		}
    break;
	
	case 'DeleteVehicleAssign':
	if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `admin_2vehicleassign` WHERE TxnID='.intval($_GET['TxnID']);
		
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:".$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}
	
    break;
	
	case 'VehicleList':
	if ((allowedToOpen(8285,'1rtc')) OR (allowedToOpen(8286,'1rtc')) OR (allowedToOpen(8287,'1rtc')) OR (allowedToOpen(82871,'1rtc'))) {
		$title='Add Vehicle';
		
                $formdesc='</i><br><a href="motorvehicles.php?w=UploadVehicleInfo">Upload Data</a><i>';
				$method='post';
				$columnnames=array(
				array('field'=>'VehicleType','type'=>'text','list'=>'vehicletypelist','size'=>15,'required'=>true),
				array('field'=>'Brand','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'Series','type'=>'text','size'=>15,'required'=>true),
                array('field'=>'PlateNo','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'CRNo','type'=>'text','size'=>20,'required'=>true),
				array('field'=>'CRDate','type'=>'date','size'=>20,'value'=>date('Y-m-d'),'required'=>true),
				array('field'=>'FuelType','type'=>'text','size'=>20,'required'=>true,'list'=>'fueltypelist'),
				array('field'=>'Color','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'DueMonth','type'=>'text','size'=>15,'required'=>true)
				);
					
		$action='motorvehicles.php?w=AddVehicleList'; $fieldsinrow=5; $liststoshow=array();
		if ((allowedToOpen(8285,'1rtc'))) {
		include('../backendphp/layout/inputmainform.php');
		}
		if ((allowedToOpen(8285,'1rtc')) OR (allowedToOpen(8289,'1rtc'))) {
		$addlprocess='motorvehicles.php?w=VehicleRegistration&TxnID='; $addlprocesslabel='Registration Info';
		$delprocess='motorvehicles.php?w=DeleteVehicleList&TxnID=';
		$editprocess='motorvehicles.php?w=EditSpecificsVehicleList&TxnID='; $editprocesslabel='Edit';
		}
		if (((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(82871,'1rtc'))) AND (!(allowedToOpen(8285,'1rtc')))) {
			$addlprocess='motorvehicles.php?w=ORPic&TxnID='; $addlprocesslabel='Print OR';
		}			
		$addlprocess2='motorvehicles.php?w=CRPic&TxnID='; $addlprocesslabel2='Print CR';
     
		$title=''; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		
		if (((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(82871,'1rtc'))) AND (!(allowedToOpen(8285,'1rtc')))) {
			$title='Vehicle List';
			array_splice($columnnames,6,0,'ORNo');
			array_splice($columnnames,7,0,'ORDate');
		}
		$width='100%';
		
		include('../backendphp/layout/displayastable.php'); 
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'CRPic':
	break;
	
	case 'ORPic':
	break;
	
	
        
        // case 'UploadVehicleInfo':
		case 'UploadVehicleInfo':
                if (allowedToOpen(8285,'1rtc')){
             $title='Upload List of Vehicles';
        $colnames=array('VTID','Brand','Series','CRNo','PlateNo','FTID','Color','EncodedByNo');
        $requiredcol=array('VTID','Brand','Series','CRNo','PlateNo','FTID','Color','EncodedByNo');
        $required='';  foreach($requiredcol as $req){ $required.='<li>'.$req.'</li>'; }
        $allowed=''; foreach($colnames as $col){ $allowed.='<li>'.$col.'</li>'; }
        $specific_instruct='Note: VTID = Vehicle Type ID, FTID = Fuel Type ID '
                . '<br><br><i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
        $tblname='admin_1vehiclelist'; $firstcolumnname='VTID';
        $DOWNLOAD_DIR="../../uploads/"; $link=$link;
        include('../backendphp/layout/uploaddata.php');
        if(($row-1)>0){ echo '<a href="motorvehicles.php?w=VehicleList" target="_blank">Lookup Newly Imported Data</a>';}
                } else {
                        echo 'No permission'; exit;
                }
        break;
		
        case 'UploadVehiclesAssign':
		if (allowedToOpen(8285,'1rtc')){
             $title='Upload List of Assigned Vehicles';
        $colnames=array('VehicleID','DateAssigned','BranchNo','CurrentlyAssignedIDNo','EncodedByNo');
        $requiredcol=array('VehicleID','DateAssigned','BranchNo','CurrentlyAssignedIDNo','EncodedByNo');
        $required='';  foreach($requiredcol as $req){ $required=$required.'<li>'.$req.'</li>'; }
        $allowed=''; foreach($colnames as $col){ $allowed=$allowed.'<li>'.$col.'</li>'; }
        $specific_instruct=' '
                . '<br><br><i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
        $tblname='admin_2vehicleassign'; $firstcolumnname='VehicleID';
        $DOWNLOAD_DIR="../../uploads/"; $link=$link;
        include('../backendphp/layout/uploaddata.php');
        if(($row-1)>0){ echo '<a href="motorvehicles.php?w=VehicleAssign" target="_blank">Lookup Newly Imported Data</a>';}
		} else {
		echo 'No permission'; exit;
		}
        break;
	
	case 'AddVehicleList':
	if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='INSERT INTO `admin_1vehiclelist` SET FTID='.$ftid.', EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' VTID='.$vehicletype.', TimeStamp=Now()'; 
         // echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'EditSpecificsVehicleList':
        if (allowedToOpen(8285,'1rtc')) {
			$title='Edit Specifics';
			$txnid=intval($_GET['TxnID']);

			$sql=$sql.' WHERE vl.TxnID='.$txnid; 
			$columnstoedit=$columnstoadd;
			
			$columnswithlists=array('VehicleType','FuelType');
			$listsname=array('VehicleType'=>'vehicletypelist','FuelType'=>'fueltypelist');
			
			$columnnames=$columnnameslist;
			
			$editprocess='motorvehicles.php?w=EditVehicleList&TxnID='.$txnid;
			
			include('../backendphp/layout/editspecificsforlists.php');
		} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'EditVehicleList':
		
		if (allowedToOpen(8285,'1rtc')){
		
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TxnID']);
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='UPDATE `admin_1vehiclelist` SET VTID="'.$vehicletype.'", FTID='.$ftid.', '.$sql.' EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now() WHERE TxnID='.$txnid;
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:motorvehicles.php?w=VehicleList");
		} else {
			echo 'No permission'; exit;
		}
    break;
	
	
	case 'DeleteVehicleList':
		
		if (allowedToOpen(8285,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$txnid = intval($_GET['TxnID']);
			$sql='DELETE FROM `admin_1vehiclelist` WHERE TxnID='.$txnid;
			$stmt=$link->prepare($sql);
			$stmt->execute();
			header("Location:motorvehicles.php?w=VehicleList");
		} else {
			echo 'No permission'; exit;
		}
    break;
	
	case 'VehicleRegistration':
	if (allowedToOpen(8288,'1rtc')) {
		$title='Add Registration Info'; 
                $txnid = intval($_GET['TxnID']);
				
				$sqllatestplateno='SELECT TxnID,PlateNo,DueMonth,RegisteredThisYr FROM admin_1vehiclelist vl WHERE vl.TxnID='.$txnid.'';
				$stmtlatestplateno=$link->query($sqllatestplateno); $reslatestplateno=$stmtlatestplateno->fetch();
				
				IF(($reslatestplateno['DueMonth']==substr($reslatestplateno['PlateNo'],-1))){
					if($reslatestplateno['RegisteredThisYr']==0){
						echo '<br><br><form method="POST" action="motorvehicles.php?action_token='.$_SESSION['action_token'].'&w=SetDueMonth&TxnID='.$reslatestplateno['TxnID'].'"><input style="background-color:yellow;" type="submit" value="Set As Registered?" name="btnRegister"></form>';
					} else {
						echo '<br><br><font color="orange">Registered This Year.</font>';
					}
				} else {
						echo '<br><br><span style="background-color:black;color:red;">Plate number and due month doesn\'t match!</span>';
					}
				
				
                $formdesc='Registration for current year';
				$method='post';
				$columnnames=array(
					array('field'=>'ORDate','type'=>'date','size'=>15,'required'=>true,'value'=>date('Y-m-d')),
					array('field'=>'ORNo','type'=>'text','size'=>20,'required'=>true)
				);
							
		$action='motorvehicles.php?w=AddRegistrationInfo&TxnID='.$txnid.''; $fieldsinrow=6; $liststoshow=array();
		// echo $sql;
		$sql .= ' WHERE vls.TxnID='.$txnid.' ORDER BY ORDate DESC';
		
		
		include('../backendphp/layout/inputmainform.php');
		$txnidname='TxnSubID';
		$delprocess='motorvehicles.php?w=DeleteVehicleRegistration&TxnID='.$txnid.'&TxnSubID=';
		$addlprocess2='motorvehicles.php?w=ORPic&TxnID='.$txnid.'&TxnSubID='; $addlprocesslabel2='Print OR';
		$editprocess='motorvehicles.php?w=EditSpecificsVehicleRegistration&TxnID='.$txnid.'&TxnSubID='; $editprocesslabel='Edit';
     
		$title=''; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='70%';
		
		include('../backendphp/layout/displayastable.php'); 
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	
	case 'SetDueMonth':
	if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid=intval($_GET['TxnID']);
		$sql='';
		$sql='UPDATE `admin_1vehiclelist` SET RegisteredThisYr=1,EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now() WHERE TxnID='.$txnid;
	
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:motorvehicles.php?w=VehicleRegistration&TxnID=".$txnid);
		} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'VehiclesRegistrationSummary':
	$sql = ' SELECT *,CONCAT(Brand," ",Series) AS Model,IF(RegisteredThisYr=1,"Yes","") AS `Registered?` FROM admin_1vehiclelist ORDER BY DueMonth ASC';
		
		$title='Vehicles Registration Summary';
		$editprocess='motorvehicles.php?w=VehicleRegistration&action_toke='.$_SESSION['action_token'].'&TxnID='; $editprocesslabel='Lookup'; 
		 $formdesc=''; $txnidname='TxnID';
		$columnnames=array('Model','PlateNo','DueMonth','Registered?');       
		
		$width='70%';
		
		include('../backendphp/layout/displayastablenosort.php'); 
		
	break;
	
	
	case 'EditSpecificsVehicleRegistration':
        if (allowedToOpen(8288,'1rtc')) { //header('Location:motorvehicles.php?denied=true'); }
		$title='Edit Specifics';
		
		$sql=$sql.' WHERE vls.TxnSubID='.$txnsubid; 
		$columnstoedit=$columnstoadd;
		
		$columnnames=$columnnameslist;
		
		$editprocess='motorvehicles.php?w=EditRegistrationInfo&TxnID='.$txnid.'&TxnSubID='.$txnsubid;
		
		include('../backendphp/layout/editspecificsforlists.php');
		} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'EditRegistrationInfo':
		if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='UPDATE `admin_1vehiclelistsub` SET '.$sql.' EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now() WHERE TxnSubID='.$txnsubid;
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:motorvehicles.php?w=VehicleRegistration&TxnID=".$txnid);
		} else {
		echo 'No permission'; exit;
		}
		
		
    break;
    
        case 'DeleteVehicleRegistration':
		if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='DELETE FROM `admin_1vehiclelistsub` WHERE TxnSubID='.$txnsubid;
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:motorvehicles.php?w=VehicleRegistration&TxnID=".$txnid);
		} else {
		echo 'No permission'; exit;
		}
		
		
    break;
	
	case 'AddRegistrationInfo':
	if (allowedToOpen(8285,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='INSERT INTO `admin_1vehiclelistsub` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TxnID='.intval($_GET['TxnID']).', TimeStamp=Now()'; 
         // echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'RequestRepair':
	if (!allowedToOpen(8288,'1rtc')){ 'No permission'; exit; }
	// if (allowedToOpen(8288,'1rtc')){
	// }
	// $sqlcnt = 'SELECT COUNT(BranchNo) AS BranchNo FROM admin_2repairrequest WHERE BranchNo = '.$_SESSION['bnum'].' AND Finished=0';
	
	// $stmtcnt=$link->query($sqlcnt); $rescnt=$stmtcnt->fetch();

	$title='New Request for Repair';
	echo '<title>'.$title.'</title>';
	
		// echo comboBox($link,'SELECT SupplierNo, SupplierName FROM `1suppliers` WHERE InvtySupplier=2','SupplierNo','SupplierName','shoplist');
		
		?><title><?php  echo $title; ?></title>
			<br><br><h4><?php  echo $title; ?></h4><br>
			<br><i>If repair shop is not in the list, please choose OTHERS.</i>
			<style>.hoverTable tr:hover {
					background-color: transparent;</style><div>
					
			<form method='POST' action='motorvehicles.php?w=AddRepairRequest' style='display: inline;' >
				<table class="hoverTable">
										<tr><td></td><td align="right">Date of Request <input type='date' name='DateRequest' size=5 required=true value='<?php echo date('Y-m-d')?>'><td></tr>
				<tr><td style="padding:20px;"></td></tr>
				
				
				<?php
				
				$sql='SELECT vl.TxnID, Branch, CONCAT(Brand," ",Series, " [", PlateNo, "]") AS ModelPlateNo FROM admin_1vehiclelist vl JOIN admin_2vehicleassign va ON vl.TxnID=va.VehicleID JOIN `1branches` b ON va.BranchNo=b.BranchNo '.(allowedToOpen(8287,'1rtc')?'WHERE va.BranchNo='.$_SESSION['bnum'].' AND Active=1':'').' ORDER BY Branch';
				
				$stmt = $link->query($sql);
				
				echo '<tr><td>Model/PlateNo: <select name="VehicleID">';
				echo '<option value="invalid">--Please Select--</option>';
				while($row= $stmt->fetch()) {
					echo '<option value="'.$row['TxnID'].'">('.strtoupper($row['Branch']). ') ' .$row['ModelPlateNo'].'</option>';
				}
				echo '</select></td><td></td></tr>'; 
				?>
				
				<!--<tr><td>Branch <input type='text' name='Branch' size=30 required=true list='branchlist' autocomplete='off'></td><td></td></tr>-->
				<tr><td>Repair Shop Name <input type='text' name='ShopName' size=30 required=true list='shoplist' autocomplete='off'></td><td></td></tr>
				<tr><td style="padding:15px"></td></tr>
										<tr><td>Particulars<br><textarea type='text' name='Particulars' required=true rows="6" cols="65"></textarea></td><td valign="top" align="right">Amount <input type='number' name='Amount' size=10 min="0" step="any" required=true></td></tr>
		<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>">
		<tr><td></td><td align="right"><input type='submit' name='submit' value='Submit Request'></td></tr></table> </form></div>
			<?php
			// }
		break;
		
		case 'AddRepairRequest':
		if (allowedToOpen(8288,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			
			$repairshopid=comboBoxValue($link,'`1suppliers`','SupplierName',addslashes($_POST['ShopName']),'SupplierNo');
			// $branchid=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['Branch']),'BranchNo');
			
			$sql='';
			$columnstoadd=array('DateRequest', 'Particulars', 'Amount');
			foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
			
			// $sqlb = 'SELECT CurrentlyAssignedIDNo FROM admin_2vehicleassign WHERE BranchNo='.$branchid.''; 
			
			$sqlcnt = 'SELECT COUNT(VehicleID) AS VehicleCNT FROM admin_2repairrequest WHERE VehicleID = '.$_POST['VehicleID'].' AND Finished=0';
	
			$stmtcnt=$link->query($sqlcnt); $rescnt=$stmtcnt->fetch();
			
		if ($rescnt['VehicleCNT']>0) {
			echo 'You have a pending request.';
		}
		else {
			$sqlb = 'SELECT BranchNo, CurrentlyAssignedIDNo FROM admin_2vehicleassign WHERE VehicleID='.$_POST['VehicleID'].' AND Status=1'; 
			$stmtb=$link->query($sqlb); $resultb=$stmtb->fetch();
			
			
			$sql='INSERT INTO `admin_2repairrequest` SET VehicleID='.$_POST['VehicleID'].', BranchNo='.$resultb['BranchNo'].', CurrentlyAssignedIDNo='.$resultb['CurrentlyAssignedIDNo'].', RSID='.$repairshopid.', '.$sql.' RequestTS=Now(), RequestedByNo=\''.$_SESSION['(ak0)'].'\''; //echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute(); 
					$sql='SELECT TxnID FROM `admin_2repairrequest` WHERE RequestedByNo='.$_SESSION['(ak0)'].' AND RSID='.$repairshopid.' AND DATE_FORMAT(RequestTS,"%Y-%m-%d") LIKE \''.date('Y-m-d').'\' ORDER BY RequestTS DESC';
					$stmt=$link->query($sql);$result=$stmt->fetch();
			header('Location:motorvehicles.php?w=Lookup&TxnID='.$result['TxnID']);
		}
		}else {
			echo 'No permission'; exit;
		}
		break;
		
		
		case 'Lookup':
		if (!allowedToOpen(8288,'1rtc')){ header('Location:motorvehicles.php?denied=true'); }
		$txnid=intval($_GET['TxnID']);
	    ?><title><?php  echo $title; ?></title>
		
		
		<div>
		<div style="float:left;">
                <h4><?php  echo $title;?></h4><br>
                <style>.hoverTable tr:hover {
                        background-color: transparent;
						tr.border_bottom td {
						  border-bottom:1pt solid black;
						}
				</style><div>
	
					<table class="hoverTable">
					<tr><td><h5>Model: <?php echo $res['Model'];?></h5></td></tr>
					<tr><td><h5>PlateNo: <?php echo $res['PlateNo'];?></h5></td></tr>
					<tr><td><h5>Branch: <?php echo $res['Branch'];?></h5></td></tr>
					<tr><td><h5>CurrentlyAssignedTo: <?php echo $res['CurrentlyAssignedTo'];?></h5></td></tr>
					<tr><td><h5>RepairShop: <?php echo comboBoxValue($link,'`1suppliers`','SupplierNo',$res['RSID'],'SupplierName');?></h5><br/><br/></td></tr>
					<tr><td></td><td align="right">Date of Request: <input <?php echo $printstyle;?> type='text' name='DateRequest' size=16 value="<?php echo date('Y-m-d' , strtotime($res['DateRequest']));?>" disabled><td></tr>
                    
					<tr><td style="padding:15px"></td></tr>
					<tr class="border_bottom"><td>Particulars:<br><textarea type='text' name='Particulars' required=true rows="6" cols="55" disabled><?php echo $res['Particulars'];?></textarea></td><td valign="top" align="right">Amount: <input <?php echo $printstyle;?> type='text' name='Amount' size=20 value="<?php echo number_format($res['Amount'],2);?>" disabled></td></tr>
					<tr><td style="padding:10px;"></td></tr>
					<tr><td>Prepared By: <?php echo $res['PreparedBy'];?></td><td align="right"><?php if ($res['Approved']<>0) {
							if ($res['Approved2']==1) {
								echo "Approved"; 
							} else {
									if ($res['Approved2']==2) {
										echo "Denied";
									} else {
										echo '';
									}
							} echo ' By (GenAdmin): ' . ($res['Approved2']<>0?$res['Approved2By']:''); 
						} else {echo '';}?></td></tr><tr><td style="padding:10px;"></td></tr>
					
			<?php
			if ($res['Approved']<>0){
				echo '<tr><td>'.($res['Approved']==1?"Approved":"Denied") .' By (Ops): ' . $res['ApprovedBy'].'</td><td>'.($res['Acknowledged']==1?"<font color='darkgreen'>Acknowledged</font>":"") .'</td></tr>
				
				'.($res['Finished']<>0?'<tr><td style="padding:30px;"></td><td>Finished with InvoiceNo: '.$res['InvoiceNo']:'').'</td></tr>';
				
				if ((!empty($res['ApprovedRemarks'])) OR (!empty($res['Approved2Remarks'])) OR (!empty($res['CheckIssuedRemarks'])) OR (!empty($res['ReceiptReceivedRemarks']))){
				
					echo '<tr><td style="color:blue;"><br/><b>Remark(s)</b></td><td style="padding:30px;"></td></tr>';
					echo (!empty($res['ApprovedRemarks']) ? '<tr><td><font color="maroon">Operations/Deptheads:</font> '.$res['ApprovedRemarks'].'</td><td style="padding:15px;"></td></tr>':'');
					echo (!empty($res['Approved2Remarks']) ? '<tr><td><font color="maroon">Gen Admin:</font> '.$res['Approved2Remarks'].'</td><td style="padding:15px;"></td></tr>':'');
				}
				
			}
			//Requester
			if ((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(8289,'1rtc')) OR (allowedToOpen(8286,'1rtc')) OR (allowedToOpen(82861,'1rtc')) OR $res['RequestedByNo']==$_SESSION['(ak0)']){
				if ($res['Approved']==0 AND $res['RequestCompleted']==0){
				echo '<tr><td><a href="motorvehicles.php?w=RequestCompleted&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Set_Request_As_Completed</a></td><td><a href="motorvehicles.php?w=EditSpecificsListOfRepairs&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Edit Request</a></td></tr>';}
			}
			if (!isset($_GET['Print'])){
				if ($res['RequestCompleted']==1){
					
					// if ($res['Approved']==0 AND $res['Approved2ByNo']==0){
					if ($res['Approved']==0 AND $res['Approved2']==0){
						if ((allowedToOpen(array(82862,8289,8290),'1rtc'))){ //To approve by sir jeck /heads
							echo '<tr><td align="left">';
							echo '<form action="motorvehicles.php?w=Approve" method="POST"><br/>Remarks:<br/><textarea name="ApprovedRemarks" rows="2" cols="50" placeholder="Leave empty if no remarks.">'.$res['ApprovedRemarks'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="submit" name="btnApprove" value="APPROVE"></div><div style="margin-left:30%;"><input type="submit" name="btnDeny" value="DENY"></div></div></form>';
							
							echo '</td><td valign="bottom" align="right"><a href="motorvehicles.php?w=SetIncOps&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Set Incomplete?</a></td></tr>';
							
						}
					// } else if (($res['Approved']==1 AND $res['Approved2ByNo']==0)) {
					} else if (($res['Approved']==1 AND $res['Approved2']==0)) {
						if (allowedToOpen(8290,'1rtc')){ //To approve by Ma'am jen
							echo '<tr><td align="left">';
							echo '<form action="motorvehicles.php?w=Approve2" method="POST"><br/>Remarks:<br/><textarea name="Approved2Remarks" rows="2" cols="50" placeholder="Leave empty if no remarks.">'.$res['Approved2Remarks'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="submit" name="btnApprove2" value="APPROVE"></div><div style="margin-left:30%;"><input type="submit" name="btnDeny2" value="DENY"></div></div></form>';
							
							echo '</td><td valign="bottom" align="right"><a href="motorvehicles.php?w=SetIncGenAdmin&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Set Incomplete?</a></td></tr>';
						}
					} else if (($res['Approved2']==1) AND ($res['Acknowledged']==0)) {
						if ((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(8289,'1rtc')) OR (allowedToOpen(8286,'1rtc')) OR (allowedToOpen(82861,'1rtc'))){ //To Acknowledge by requester
							echo '<tr><td>';
							
							echo '<form action="motorvehicles.php?w=Ack" method="POST"><br/><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><div><div style="float:left;"><input type="submit" name="btnAck" value="Acknowledge"></div></div></form>';
							
							echo '</td><td valign="bottom" align="right"><a href="motorvehicles.php?w=SetIncAck&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Set Incomplete?</a></td></tr>';
						}
					} else if ($res['Acknowledged']==1 AND $res['Finished']==0) {
						if ((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(8289,'1rtc')) OR (allowedToOpen(8286,'1rtc')) OR (allowedToOpen(82861,'1rtc'))){ //To Set Done by requester.
							echo '<tr><td>';
							
							echo '<form action="motorvehicles.php?w=FinishedRequest" method="POST"><br/>Invoice Number:<br/><textarea name="InvoiceNo" rows="2" cols="50" placeholder="Correct Invoice Number." required>'.$res['InvoiceNo'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="submit" name="btnFinishedRequest" value="SET REQUEST AS COMPLETE/DONE REPAIR"></div></div></form>';
							
							echo '</td><td valign="bottom" align="right"><a href="motorvehicles.php?w=SetIncFinal&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Set Incomplete?</a></td></tr>';
						}
					}
					
				}
			}
			echo '</table></div>';
			echo '</div><div style="margin-left:60%;"><h4>Repair History</h4><br/>';
			
			if (!isset($_GET['Print'])){
				echo ShowHistory($res['VehicleID']);
			}
			echo '</div></div>';
			
	break;
	
	
	
	case 'EditSpecificsListOfRepairs':
	if ((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(8290,'1rtc')) OR (allowedToOpen(8289,'1rtc')) OR (allowedToOpen(8286,'1rtc')) OR (allowedToOpen(82861,'1rtc'))){
	    $txnid= intval($_GET['TxnID']);
		$title='Edit Specifics';

		$sql=$sql.' AND TxnID='.$txnid;
		
		$columnstoedit=array_diff($columnnameslist,array('Branch','RequestedBy','RequestCompleted','OpsORHeadApproval','GenAdminApproval', 'Acknowledgement', 'RepairFinished','InvoiceNo'));	
		
		
		$columnswithlists=array('RepairShop');
		$listsname=array('RepairShop'=>'shoplist');
		
		$columnnames=$columnswithlists;
		
		
		
        $columnnames=$columnnameslist;
		
		
		$editprocess='motorvehicles.php?w=EditRepairRequest&TxnID='.$txnid;
		echo '<i>Only Gen Admin Head can edit within seven days after repair is finished.</i>';
		include('../backendphp/layout/editspecificsforlists.php');
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'EditRepairRequest':
	// if (allowedToOpen(8287,'1rtc')){
		
	if ((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(8290,'1rtc')) OR (allowedToOpen(8289,'1rtc')) OR (allowedToOpen(8286,'1rtc')) OR (allowedToOpen(82861,'1rtc'))){
		$txnid= $_GET['TxnID'];
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='';
		$columnstoadd=array('DateRequest','Particulars','Amount');
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; }
		$rsid=comboBoxValue($link,'`1suppliers`','SupplierName',addslashes($_POST['RepairShop']),'SupplierNo'); 
		
		// $sql='UPDATE `admin_2repairrequest` SET '.$sql.' RSID='.$rsid.' WHERE '.(((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(8289,'1rtc')))?'RequestCompleted=0 AND Approved=0 AND Approved2=0 AND Acknowledged=0 '.((allowedToOpen(8287,'1rtc'))?'AND RequestedByNo='.$_SESSION['(ak0)'].'':'').' AND TxnID='.$txnid.'':'(FinishedTS="0000-00-00" OR (CURDATE()-FinishedTS<=5)) AND TxnID='.$txnid); //5days
		
		$sql='UPDATE `admin_2repairrequest` SET '.$sql.' RSID='.$rsid.' WHERE '.(((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(8289,'1rtc')))?'RequestCompleted=0 AND Approved=0 AND Approved2=0 AND Acknowledged=0 '.((allowedToOpen(8287,'1rtc'))?'AND RequestedByNo='.$_SESSION['(ak0)'].'':'').' AND TxnID='.$txnid.'':(allowedToOpen(3000,'1rtc'))?'  TxnID='.$txnid:'(FinishedTS="0000-00-00" OR DATEDIFF(CURDATE(),FinishedTS)<=7) AND TxnID='.$txnid); //7days
		
		
		// echo $sql; exit();

		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:motorvehicles.php?w=Lookup&TxnID='.$txnid);
	} else {
		echo 'No permission'; exit;
		}
		break;
	
	
	case 'ListOfRepairs':
	if (allowedToOpen(8288,'1rtc')){
		$formdesc='</i><form action="#" method="POST">Filter By:<select name="filterby">
		<option value="0">Unfinished Requests</option>
		<option value="1">Denied Requests</option>
		<option value="2">Pending Approvals - Ops</option>
		<option value="3">Pending Approvals - GenAdmin</option>
		<option value="4">Done Requests</option>
		<option value="5">All Requests</option></select>
		<input type="submit" name="btnSubmit" value="Filter"></form><i>'; $txnidname='TxnID';
		$defaultfilter = ' WHERE (Approved=0 OR Approved2=0 OR Acknowledged=0 OR Finished=0)';
		if(!isset($_POST['btnSubmit'])){$_POST['filterby']=''; $filter = $defaultfilter; $_POST['filterby']=0;}
		else {
			if ($_POST['filterby']==0){ $subtitle1 = ' (Unfinished Requests)'; $filter = $defaultfilter;}
			else if ($_POST['filterby']==1){$subtitle1 = ' (Denied Requests)'; $filter = ' WHERE (Approved=2 OR Approved2=2 OR Acknowledged=2) GROUP BY TxnID';}
			else if ($_POST['filterby']==2){$subtitle1 = ' (Pending Approvals - Ops)'; $filter = ' WHERE RequestCompleted=1 AND (Approved=0)';}
			else if ($_POST['filterby']==3){$subtitle1 = ' (Pending Approvals - GenAdmin)'; $filter = ' WHERE RequestCompleted=1 AND Approved=1 AND Approved2=0';}
			else if ($_POST['filterby']==4){$subtitle1 = ' (Done Requests)'; $filter = ' WHERE Finished=1';}
			else if ($_POST['filterby']==5){$subtitle1 = ' (All Requests)'; $filter = '';}
		}
		
		$title='List of Repair Requests' . (isset($_POST['btnSubmit'])?$subtitle1:'');
		$columnnames=$columnnameslist;
		
		if (allowedToOpen(8287,'1rtc')) { //branch/wh supervisor see branch 32/37/50/81
			$addcon = ' AND (rr.RequestedByNo='.$_SESSION['(ak0)'].' OR rr.BranchNo=(SELECT BranchNo FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].'))'; goto repskip;
		}
		if (allowedToOpen(8289,'1rtc')) { //branch see branch OpsHead/SupplyChainHead
			
			$addcon = ' AND ((SELECT deptheadpositionid FROM attend_30currentpositions WHERE IDNo=rr.RequestedByNo)=(SELECT PositionID FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].')) OR RequestedByNo='.$_SESSION['(ak0)'].''; goto repskip;
		}
		if (allowedToOpen(8290,'1rtc')) { //Gen Admin Only
			$addcon = ''; goto repskip;
		}
		if (allowedToOpen(82862,'1rtc')) { //branch see branch FieldSpecialist
			$addcon = ' AND rr.RequestedByNo IN (SELECT IDNo FROM attend_30currentpositions WHERE supervisorpositionid='.$_SESSION['&pos'].')'; goto repskip;
		}
		if (allowedToOpen(8286,'1rtc')) { //branch see branch FieldSpecialist
			$addcon = ' AND  (rr.RequestedByNo='.$_SESSION['(ak0)'].' OR rr.BranchNo IN (SELECT BranchNo FROM attend_1branchgroups WHERE '.$_SESSION['(ak0)'].' IN (FieldSpecialist,BranchSupport,BranchCoordinator) OR OpsManager='.$_SESSION['(ak0)'].'))'; goto repskip;
		}
		
		repskip:
		
		if (allowedToOpen(3000,'1rtc')) { //JYE
			$addcon = '';
		}
		
		$sql .= $filter . $addcon ;
		
		echo '<br><br>';
		
		// echo $sql;
		if (allowedToOpen(8288,'1rtc')) {
			$fieldsinrow=3; $liststoshow=array();
			
			$delprocess='motorvehicles.php?w=DeleteListOfRepairs&TxnID=';
			$addlprocess='motorvehicles.php?w=EditSpecificsListOfRepairs&filterby='.$_POST['filterby'].'&TxnID='; $addlprocesslabel='Edit';
			$editprocess='motorvehicles.php?w=Lookup&TxnID='; $editprocesslabel='Lookup';
		}
		include('../backendphp/layout/displayastable.php');
	} else {
		echo 'No permission'; exit;
		}
	break; //End of Case List
	
	
	
	case 'DeleteListOfRepairs':
	
        if (allowedToOpen(8288,'1rtc')){
			$txnid = intval($_GET['TxnID']);
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			
			$sql='DELETE FROM `admin_2repairrequest` WHERE RequestCompleted=0 AND Approved=0 AND Approved2=0 AND Acknowledged=0 AND '.((allowedToOpen(array(8289,8286),'1rtc'))?'':'RequestedByNo='.$_SESSION['(ak0)'].' AND').' TxnID='.$txnid;
			
			$stmt=$link->prepare($sql); $stmt->execute();
			
			header("Location:".$_SERVER['HTTP_REFERER']);
		} else {
		echo 'No permission'; exit;
		}
        
    
	break;
	
	
	case 'RepairHistory':
	if ((allowedToOpen(8285,'1rtc')) OR (allowedToOpen(8286,'1rtc'))){
		$title='Repair History';
		$sql='SELECT rr.*, CONCAT(Brand," ",Series,": ",PlateNo) AS ModelPlateNo, CONCAT(Nickname, " ", Surname) AS AssignedTo, FinishedTS AS Date, Branch FROM admin_1vehiclelist vl JOIN admin_2repairrequest rr ON vl.TxnID=rr.VehicleID JOIN `1branches` b ON rr.BranchNo=b.BranchNo JOIN 1_gamit.0idinfo id ON rr.CurrentlyAssignedIDNo=id.IDNo WHERE Finished=1;';
		
		$fieldsinrow=3; $liststoshow=array();
		$columnnameslist=array('Date','ModelPlateNo','Branch','AssignedTo','Particulars','InvoiceNo','Amount');
		
		$columnnames=$columnnameslist;
		
		$editprocess='motorvehicles.php?w=Lookup&TxnID='; $editprocesslabel='Lookup';
		
		$width='80%';
		include('../backendphp/layout/displayastable.php'); 
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	
	
	case 'RequestCompleted':
	if ((allowedToOpen(8288,'1rtc'))){
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnid = $_GET['TxnID'];
	$sql='';
	$sql='UPDATE `admin_2repairrequest` SET RequestCompleted=1, RequestCompletedTS=Now() WHERE RequestedByNo='.$_SESSION['(ak0)'].' AND TxnID='.$txnid.''; 
	$stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:motorvehicles.php?w=ListOfRepairs');
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'Approve':
    if (allowedToOpen(array(8289,82862,8290),'1rtc')){
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = $_POST['TxnID'];
	if(isset($_POST['btnApprove'])){
		$sql='UPDATE `admin_2repairrequest` SET Approved=1, ApprovedRemarks="'.$_POST['ApprovedRemarks'].'", ApprovedByNo='.$_SESSION['(ak0)'].', ApprovedTS=Now() WHERE TxnID='.$txnid.'';
	} else {
		$sql='UPDATE `admin_2repairrequest` SET Approved=2, ApprovedByNo='.$_SESSION['(ak0)'].', ApprovedRemarks="'.$_POST['ApprovedRemarks'].'", ApprovedTS=Now(),Approved2=2,Acknowledged=2,Finished=2 WHERE TxnID='.$txnid.''; 
	}
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:motorvehicles.php?w=Lookup&TxnID='.$txnid);
	} else {
		echo 'No permission'; exit;
		}
	
	break;
	
	case 'Approve2':
	$txnid = $_POST['TxnID'];
	if (allowedToOpen(8290,'1rtc')){
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
    $sql='';
	
	if(isset($_POST['btnApprove2'])){
		$sql='UPDATE `admin_2repairrequest` SET Approved2=1, Approved2Remarks="'.$_POST['Approved2Remarks'].'", Approved2ByNo='.$_SESSION['(ak0)'].', Approved2TS=Now() WHERE TxnID='.$txnid.'';
	}
	else {
		$sql='UPDATE `admin_2repairrequest` SET Approved2=2, Approved2Remarks="'.$_POST['Approved2Remarks'].'", Approved2ByNo='.$_SESSION['(ak0)'].', Approved2TS=Now(),Acknowledged=2,Finished=2 WHERE TxnID='.$txnid.''; 
	}
	// echo $sql; exit();
	
    $stmt=$link->prepare($sql); $stmt->execute();
	
	header('Location:motorvehicles.php?w=Lookup&TxnID='.$txnid);
	} else {
		echo 'No permission'; exit;
		}
	
	break;
	
	case 'Ack':
	if ((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(8289,'1rtc')) OR (allowedToOpen(8286,'1rtc')) OR (allowedToOpen(82861,'1rtc'))){
	$txnid=$_POST['TxnID'];
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `admin_2repairrequest` SET Acknowledged=1, AcknowledgedTS=Now() WHERE TxnID='.$txnid.'';  //echo $sql; exit();
        $stmt=$link->prepare($sql); $stmt->execute();
		
		header('Location:motorvehicles.php?w=Notif');
		} else {
		echo 'No permission'; exit;
		}

	break;	
	
	case 'Notif':
	if ((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(8289,'1rtc')) OR (allowedToOpen(8286,'1rtc')) OR (allowedToOpen(82861,'1rtc'))){
	echo '<br/><br/>Once the repair is done, you can set your request as COMPLETE with invoice number (List of Request Repairs>>Lookup>>DONE REPAIR).<br/><br/>';
	echo '<a href="motorvehicles.php?w=ListOfRepairs">Back to List of Request Repairs</a>';
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'FinishedRequest':
        if ((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(8289,'1rtc')) OR (allowedToOpen(8286,'1rtc')) OR (allowedToOpen(82861,'1rtc'))){
			$txnid = $_POST['TxnID'];
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `admin_2repairrequest` SET Finished=1, InvoiceNo="'.$_POST['InvoiceNo'].'", FinishedTS=Now() WHERE TxnID='.$txnid.''; //echo $sql; exit();
        $stmt=$link->prepare($sql); $stmt->execute();
		} else {
		echo 'No permission'; exit;
		}
	header('Location:motorvehicles.php?w=Lookup&TxnID='.$txnid);
	break;
	
	case 'SetIncOps':
      if ((allowedToOpen(8289,'1rtc')) OR (allowedToOpen(82862,'1rtc'))){
		  $txnid=intval($_GET['TxnID']);
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `admin_2repairrequest` SET RequestCompleted=0 WHERE TxnID='.$txnid.''; 
	
    $stmt=$link->prepare($sql); $stmt->execute();} else {
		echo 'No permission'; exit;
		}
	header('Location:motorvehicles.php?w=Lookup&TxnID='.$txnid);
	break;
	
	case 'SetIncAck':
      if ((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(8289,'1rtc')) OR (allowedToOpen(8286,'1rtc'))){
		  $txnid=intval($_GET['TxnID']);
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `admin_2repairrequest` SET Approved2=0 WHERE TxnID='.$txnid.''; 
	
    $stmt=$link->prepare($sql); $stmt->execute();} else {
		echo 'No permission'; exit;
		}
	header('Location:motorvehicles.php?w=Lookup&TxnID='.$txnid);
	break;
	
	case 'SetIncFinal':
     if ((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(8289,'1rtc')) OR (allowedToOpen(8286,'1rtc'))){
		  $txnid=intval($_GET['TxnID']);
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `admin_2repairrequest` SET Approved2=0, Acknowledged=0 WHERE TxnID='.$txnid.''; 
	
    $stmt=$link->prepare($sql); $stmt->execute();} else {
		echo 'No permission'; exit;
		}
	header('Location:motorvehicles.php?w=Lookup&TxnID='.$txnid);
	break;
	
	case 'SetIncGenAdmin':
        if (allowedToOpen(8290,'1rtc')){
		$txnid=intval($_GET['TxnID']);
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `admin_2repairrequest` SET Approved=0,ApprovedByNo=0 WHERE TxnID='.$txnid.''; 
        $stmt=$link->prepare($sql); $stmt->execute();} else {
		echo 'No permission'; exit;
		}
	header('Location:motorvehicles.php?w=Lookup&TxnID='.$txnid);
	break;
	
	case 'EncodeFuelConsumption':
	if ((allowedToOpen(8285,'1rtc')) OR (allowedToOpen(8286,'1rtc')) OR (allowedToOpen(8287,'1rtc')) OR (allowedToOpen(82871,'1rtc'))){
	// if ((allowedToOpen(5230,'1rtc'))){
		
		$title='Encode Fuel Consumption'; 
                $formdesc='';
				$method='post';
				$columnnames=array(
				array('field'=>'Date', 'type'=>'date','size'=>15,'value'=>date('Y-m-d'),'required'=>true),
				array('field'=>'VehicleID', 'caption'=>'Model/PlateNo', 'type'=>'text','list'=>'vehicleinfolist','size'=>15,'required'=>true),
				// array('field'=>'FuelType', 'type'=>'text','size'=>15,'required'=>true,'list'=>'fueltypelist'),
				array('field'=>'KmReading','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'Liter','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'PriceperLiter','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'InvoiceNo','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'Remarks','type'=>'text','size'=>25,'required'=>false)
				);
		
		$action='motorvehicles.php?w=AddFuelConsumption'; $fieldsinrow=5; $liststoshow=array();
		
		include('../backendphp/layout/inputmainform.php');
		
		
		$delprocess='motorvehicles.php?w=DeleteFuelConsumption&TxnID=';
		
		$title=''; $formdesc=''; $txnidname='TxnID';
		$columnnameslist = array_diff($columnnameslist,array('DistanceTrav','Km/Liter','Liter/Km','Peso/Km'));
		$columnnames=$columnnameslist;       
		
		$width='100%';
		
		
		$datetoday=date("Y-m-d"); 
		
		if ((allowedToOpen(8287,'1rtc'))){
			$newcondi=' AND va.BranchNo='.$_SESSION['bnum'].'';
		} else {
			$newcondi='';
		}
		
		$sql .= $newcondi . ' AND (Date="'.$datetoday.'" OR LEFT(fc.TimeStamp,10)="'.date('Y-m-d').'") ORDER BY Date DESC,TxnID DESC';
		
		include('../backendphp/layout/displayastable.php');
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'FuelConsumption':
	if ((allowedToOpen(8285,'1rtc')) OR (allowedToOpen(8286,'1rtc')) OR (allowedToOpen(8287,'1rtc')) OR (allowedToOpen(82871,'1rtc'))){
		
		if (allowedToOpen(8286,'1rtc')){
			$delprocess='motorvehicles.php?w=DeleteFuelConsumption&TxnID=';
		}
		$title1='Fuel Consumption'; $formdesc=''; $txnidname='TxnID';
		echo '<title>'.$title1.'</title>';
		echo '<h3>'.$title1.'</h3>';
		$columnnames=$columnnameslist;       
		
		$width='100%';
		
		if ((allowedToOpen(8287,'1rtc'))){
			$cond = ' AND BranchNo='.$_SESSION['bnum'].'';
		} else {
			$cond = '';
		}
		
		if ((!allowedToOpen(8287,'1rtc'))){
		// $sqlvehicle='SELECT vl.TxnID, CONCAT("(",Branch,") ",Brand," ",Series," ",PlateNo) AS ModelPlateNo FROM admin_1vehiclelist vl LEFT JOIN admin_2vehicleassign va ON vl.TxnID=va.VehicleID JOIN `1branches` b ON va.BranchNo=b.BranchNo WHERE va.Status=1 ORDER BY Branch';
		$sqlvehicle='SELECT vl.TxnID, CONCAT("(",Branch,") ",Brand," ",Series," ",PlateNo) AS ModelPlateNo FROM admin_1vehiclelist vl LEFT JOIN admin_2vehicleassign va ON vl.TxnID=va.VehicleID JOIN `1branches` b ON va.BranchNo=b.BranchNo WHERE va.Status=1 ORDER BY Branch';
		$stmtvehicle = $link->query($sqlvehicle);
		echo '<br/><br/>';
		echo '<form method="POST" action="motorvehicles.php?w=FuelConsumption">';
		echo ' Vehicle: <select name="VehicleID">';
				
					echo '<option value="All">All</option>';
				
				while($rowvehicle=$stmtvehicle->fetch()) {
					echo '<option value="'.$rowvehicle['TxnID'].'">'.$rowvehicle['ModelPlateNo'].'</option>';
				}
				echo '</select>';
		echo '<input type="submit" name="btnFilterbyVehicle" value="Filter by Vehicle">';
		echo '</form>';
		
		
		$sqlbranch='SELECT BranchNo, Branch FROM `1branches` WHERE Active=1'.$cond.'';
		$stmtbranch = $link->query($sqlbranch);
				
		echo ' <form method="POST" action="motorvehicles.php?w=FuelConsumption">';
		echo ' Branch: <select name="BranchNo">';
				// if ((!allowedToOpen(8287,'1rtc'))){
					echo '<option value="All">All</option>';
				// }
				while($rowbranch=$stmtbranch->fetch()) {
					echo '<option value="'.$rowbranch['BranchNo'].'">'.$rowbranch['Branch'].'</option>';
				}
				echo '</select>';
		echo '<input type="submit" name="btnFilterbyBranch" value="Filter by Branch">';
		echo '</form>';
		}
		$ytoday=$currentyr;
		
		if ((allowedToOpen(8287,'1rtc'))){
			$addsqlvid=''; $addsql=' AND va.BranchNo='.$_SESSION['bnum'].'';
			goto noform;
		}
		if (isset($_POST['btnFilterbyVehicle'])){
			if ($_POST['VehicleID']=='All'){
				$addsqlvid = '';
			} else {
				$addsqlvid = ' AND va.VehicleID='.$_POST['VehicleID'].'';
			}
		} else {
			$addsqlvid = '';
		}
		
		if (isset($_POST['btnFilterbyBranch'])){
			if ($_POST['BranchNo']=='All'){
				$addsql = '';
			} else {
				$addsql = ' AND va.BranchNo='.$_POST['BranchNo'].'';
			}
		} else {
			$addsql = '';
		}
		noform:
		
		$title='';
		$sql .= $addsqlvid.$addsql . ' ORDER BY VehicleID,Date DESC,TxnID DESC';
		// echo $sql;
		// include('../backendphp/layout/displayastable.php');
		
		if ((isset($_POST['btnFilterbyBranch'])) OR (isset($_POST['btnFilterbyVehicle'])) OR (allowedToOpen(8287,'1rtc'))){
			echo '';
			include('../backendphp/layout/displayastable.php');
		}
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	
	case 'FuelConsumptionPerVehicleLookup':
	if ((allowedToOpen(8285,'1rtc')) OR (allowedToOpen(8289,'1rtc')) OR (allowedToOpen(8286,'1rtc'))){
		
		$sqlmodel = 'SELECT CONCAT(Brand, "-",Series, " [", PlateNo, "]") AS ModelPlateNo,b.Branch,FullName FROM admin_1vehiclelist vl JOIN admin_2vehicleassign va ON vl.TxnID=va.VehicleID JOIN `1branches` b ON va.BranchNo=b.BranchNo JOIN attend_30currentpositions cp ON va.CurrentlyAssignedIDNo=cp.IDNo WHERE vl.TxnID='.intval($_GET['VehicleID']).''; 
		$stmtmodel = $link->query($sqlmodel);
		$rowmodel = $stmtmodel->fetch();
		
		$columnnameslist = array_diff($columnnameslist, array('ModelPlateNo','Branch','FuelType','Remarks'));
		
		echo '<br/>';
		$title='Fuel Consumption for: '; $formdesc='</i><h3>'.$rowmodel['ModelPlateNo'].'<br>'.$rowmodel['Branch'].', '.$rowmodel['FullName'].'</h3><i>'; $txnidname='TxnID';
		
		$columnnames=$columnnameslist;       
		
		$width='100%';
		
		$sql .= ' AND fc.VehicleID='.intval($_GET['VehicleID']).' ORDER BY Date DESC,TxnID DESC';
		
		include('../backendphp/layout/displayastablenosort.php');
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	
	
	case 'AddFuelConsumption':
	if ((allowedToOpen(8287,'1rtc')) OR (allowedToOpen(8286,'1rtc')) OR (allowedToOpen(82871,'1rtc'))){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		
		$sql='';
		$columnstoadd=array('Date','VehicleID','KmReading', 'Liter','PriceperLiter','InvoiceNo','Remarks');
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		
		$sql='INSERT INTO `admin_2fuelconsumption` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'TimeStamp=Now()'; 
        // echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'DeleteFuelConsumption':
		
        if (allowedToOpen(8288,'1rtc')){
			$txnid = intval($_GET['TxnID']);
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			
			$columnstoadd = array('Date', 'VehicleID', 'KmReading', 'Liter', 'PriceperLiter', 'InvoiceNo', 'Remarks', 'EncodedByNo', 'TimeStamp');
			
			$sql2='';
			$fieldarr = array();
			$sql2='SELECT * FROM `admin_2fuelconsumption` WHERE TxnID='.$txnid.'';
			$stmt = $link->query($sql2);
			$row = $stmt->fetch();
			
			$sql2 ='';
			foreach ($columnstoadd as $field) {$sql2.=' `' . $field. '`=\''.addslashes($row[$field]).'\', ';}
			
			if (allowedToOpen(8287,'1rtc') /*Branch*/ OR allowedToOpen(8286,'1rtc') /*Ops*/ OR (allowedToOpen(82871,'1rtc')) ){
				if ((allowedToOpen(8287,'1rtc'))){
					$condi = ' AND `Date`="'.date("Y-m-d").'"';
				} else if ((allowedToOpen(8285,'1rtc')) OR (allowedToOpen(8286,'1rtc'))){
					$condi ='';
				} else if (allowedToOpen(82871,'1rtc')){
					$condi =' AND EncodedByNo='.$_SESSION['(ak0)'].'';
				} else {
					echo 'No Permission'; exit();
				}
				$sql = 'DELETE FROM `admin_2fuelconsumption` WHERE `Date`>"'.$_SESSION['nb4A'].'" AND TxnID='.$txnid.$condi;
				$stmt=$link->prepare($sql); $stmt->execute();
				
				$sql3='';
				$sql3='SELECT COUNT(TxnID) AS cnt FROM `admin_2fuelconsumption` WHERE TxnID='.$txnid.'';
				$stmt3 = $link->query($sql3);
				$row3 = $stmt3->fetch();
				
				if($row3['cnt']==0){
					$sql2='INSERT INTO `admin_2fuelconsumptionlogs` SET '.$sql2.' DeletedByNo='.$_SESSION['(ak0)'].''; //echo $sql2; exit();
					$stmt=$link->prepare($sql2); $stmt->execute();
				}
			}
			header("Location:".$_SERVER['HTTP_REFERER']);
		} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'FuelConsumptionLogs':
	if ((allowedToOpen(8285,'1rtc')) OR (allowedToOpen(8286,'1rtc'))) {
		$sqlgetlastkmlogs = 'SELECT KmReading FROM admin_2fuelconsumptionlogs fcl2 WHERE IF(fcl2.Date=fcl.Date, fcl2.TxnID<fcl.TxnID, fcl.Date < fcl.Date) AND fcl2.VehicleID = fcl.VehicleID ORDER BY Date DESC LIMIT 1';
		
		$sql='SELECT fcl.*, CONCAT(Brand," ",Series,": ",PlateNo) AS ModelPlateNo, Branch, TRUNCATE((Liter*PriceperLiter),2) AS TotalAmt,  CONCAT(TRUNCATE(((KmReading-('.$sqlgetlastkmlogs.'))/Liter),2), " KM") AS `Km/Liter`, CONCAT(TRUNCATE(Liter/(KmReading-('.$sqlgetlastkmlogs.')),2)," L") AS `Liter/Km`, CONCAT((Liter/(KmReading-('.$sqlgetlastkmlogs.')))*PriceperLiter," Php") AS `Peso/Km`, FuelType, CONCAT(id.Nickname, " ", id.Surname) AS EncodedBy, CONCAT(id2.Nickname, " ", id2.Surname) AS DeletedBy FROM admin_2fuelconsumptionlogs fcl JOIN 1_gamit.0idinfo id ON fcl.EncodedByNo=id.IDNo JOIN admin_1vehiclelist vl ON fcl.VehicleID=vl.TxnID JOIN admin_0fueltype ft ON vl.FTID=ft.FTID JOIN admin_2vehicleassign va ON vl.TxnID=va.VehicleID JOIN `1branches` b ON va.BranchNo=b.BranchNo JOIN 1_gamit.0idinfo id2 ON fcl.DeletedByNo=id2.IDNo WHERE Status=1;';
		
		$columnnameslist=array('Date','ModelPlateNo','Branch','FuelType','KmReading','Liter','PriceperLiter','TotalAmt','InvoiceNo','Remarks','EncodedBy','TimeStamp','DeletedBy');
		
		$title='Fuel Consumption Logs'; 
                $formdesc='';
				
		$liststoshow=array();
		
		
		$formdesc=''; $txnidname='TxnID';
		
		$columnnames=$columnnameslist;       
		
		$width='100%';
		
		include('../backendphp/layout/displayastable.php');
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	
	case 'ManagePic':
	if (!allowedToOpen(8285,'1rtc')){ echo 'No Permission'; exit(); }
	$title = 'Manage Images';
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3><br/>';
	echo '<table border="0">
    <tr>
        <td>'; ?>    
            <?php
			if (array_key_exists('delete_file', $_POST)) {
			  $filename = $_POST['delete_file'];
			  if (file_exists($filename)) {
				unlink($filename);
				echo 'File '.str_replace('orcrpics/','',$filename).' has been deleted.';
			  } else {
				echo 'Could not delete '.str_replace('orcrpics/','',$filename).', file does not exist.';
			  }
			}

            $files = glob("orcrpics/*");
            foreach ($files as $filename) {
                echo '<form method="post">';
				echo '<input type="hidden" value="'.$filename.'" name="delete_file" />';
				echo 'Filename: '.str_replace('orcrpics/','',$filename).'<br/>';
				echo 'DateUploaded: '.str_replace('orcrpics/','',date ("F d Y H:i:s.", filemtime($filename))).'<br/>';
				echo '<a href="'.$filename.'"><img width="100px" height="100px" src="'.$filename.'"/></a><br/>';
				echo '<input type="submit" OnClick="return confirm(\'Really delete this? This action cannot be undone.\');" value="Delete image" />';
				echo '</form>';
            }
            ?>
        </td>
		</tr>   
	</table>
	<?php
	break;
	
}

function ShowHistory($vehicleid){
	global $currentyr;
	  $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
	  
	$sql='SELECT rr.*, CONCAT(Nickname, " ", Surname) AS AssignedTo FROM admin_2repairrequest rr LEFT JOIN 1_gamit.0idinfo id ON rr.CurrentlyAssignedIDNo=id.IDNo WHERE Finished=1 AND VehicleID='.$vehicleid.' ORDER BY FinishedTS DESC';
	$stmt = $link->query($sql);
	echo '<table style="border:1px solid;"><th>Date</th><th>Particulars</th><th>AssignedTo</th>';
	while($row=$stmt->fetch()) {
		echo '<tr><td>'.$row['FinishedTS'].'</td><td>'.$row['Particulars'].'</td><td>'.$row['AssignedTo'].'</td></tr>';
	}
	echo '</table>';
}

 $link=null; $stmt=null;
?>
</div> <!-- end section -->
