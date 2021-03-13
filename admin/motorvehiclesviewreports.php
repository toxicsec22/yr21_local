<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(8288,'1rtc')) { echo 'No permission'; exit; }

if($_GET['w']=="CRPic" OR $_GET['w']=="ORPic") { goto skipcontents;}
$showbranches=false;
include_once('../switchboard/contents.php');
skipcontents:



 

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
// include_once('motorvehiclesapproval.php');

//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila'); $diraddress='../';

?>

<?php
echo '<br><br>';
include_once('motorvehicleslinks.php');
$which=(!isset($_GET['w'])?'FuelConsumptionComparison':$_GET['w']);

switch ($which)
{
	
case 'FuelConsumptionComparison':

if ((allowedToOpen(8285,'1rtc')) OR (allowedToOpen(8289,'1rtc')) OR (allowedToOpen(8286,'1rtc'))){
$title = 'Fuel Consumption Comparison';

echo '
  <title>'.$title.'</title>';

		echo '<br><br><h3>'.$title.'</h3>';
	  $sqlvehicletype='SELECT VTID, VehicleType FROM admin_0vehicletype';
		$stmtvehicletype = $link->query($sqlvehicletype);
		
		echo '<br/><br/><form method="POST" action="#">';
		echo 'MonthNo: <input type="text" size="5" name="MonthNo" value="'.date('m').'">';
		echo ' Vehicle Type: <select name="VTID">';
			while($rowvehicletype=$stmtvehicletype->fetch()) {
				echo '<option value="'.$rowvehicletype['VTID'].'">'.$rowvehicletype['VehicleType'].'</option>';
			}
			echo '</select>';
		echo ' <input type="submit" name="btnVehicleTypeID" value="Compare">';
		echo '</form><br>';
		
		
  ?>
 
  
  <?php 
  
	  if (isset($_POST['btnVehicleTypeID'])){
		  
		  echo '<h4>MonthNo: '.$_POST['MonthNo'].', VehicleType: '.comboBoxValue($link,'admin_0vehicletype','VTID',$_POST['VTID'],'VehicleType').'</h4>';
		 
		 
		 
			$sql='SELECT Branch,fc.VehicleID AS TxnID, CONCAT("PHP ", FORMAT(SUM(Liter*PriceperLiter),2)) AS ConsumedAmount, CONCAT(Brand," ",Series) AS Model,PlateNo, SUM(PriceperLiter), SUM(Liter), FORMAT(SUM(((KmReading-(SELECT KmReading FROM admin_2fuelconsumption fc2 WHERE fc2.TxnID<fc.TxnID AND fc2.VehicleID = fc.VehicleID ORDER BY Date DESC LIMIT 1)))),2) AS DistanceTrav,FORMAT((SUM(((KmReading-(SELECT KmReading FROM admin_2fuelconsumption fc2 WHERE fc2.TxnID<fc.TxnID AND fc2.VehicleID = fc.VehicleID ORDER BY Date DESC LIMIT 1))))/SUM(Liter)),2) AS `Km/Liter`,FORMAT((SUM(Liter)/SUM(((KmReading-(SELECT KmReading FROM admin_2fuelconsumption fc2 WHERE fc2.TxnID<fc.TxnID AND fc2.VehicleID = fc.VehicleID ORDER BY Date DESC LIMIT 1))))),2) AS `Liter/Km`, FORMAT((SUM(PriceperLiter)/SUM(((KmReading-(SELECT KmReading FROM admin_2fuelconsumption fc2 WHERE fc2.TxnID<fc.TxnID AND fc2.VehicleID = fc.VehicleID ORDER BY Date DESC LIMIT 1))))),2) AS `Peso/Km` FROM admin_2fuelconsumption fc JOIN 1_gamit.0idinfo id ON fc.EncodedByNo=id.IDNo JOIN admin_1vehiclelist vl ON fc.VehicleID=vl.TxnID JOIN admin_0fueltype ft ON vl.FTID=ft.FTID JOIN admin_2vehicleassign va ON vl.TxnID=va.VehicleID JOIN `1branches` b ON va.BranchNo=b.BranchNo WHERE Status=1 AND VTID='.$_POST['VTID'].' AND MONTH(fc.Date)='.$_POST['MonthNo'].' GROUP BY fc.VehicleID ORDER BY Branch;';
			
			
			$columnnameslist=array('Branch','PlateNo','Model','Km/Liter','Liter/Km','Peso/Km','ConsumedAmount');
			
			$title=''; $formdesc=''; $txnid='TxnID';
			
			$columnnames=$columnnameslist;       
			
			$width='100%';
			$editprocess='motorvehicles.php?w=FuelConsumptionPerVehicleLookup&VehicleID='; $editprocesslabel='Lookup';
			
			include('../backendphp/layout/displayastablenosort.php');
		  
		  
		  echo '<br><br><br>';
		  include($path.'/acrossyrs/js/reportcharts/includejscharts.php'); 
$echo='';
$graphtitle="ReportTitle";


$sql0 = 'CREATE TEMPORARY TABLE ConsumedFuel AS SELECT CONCAT("\'",Branch,"\'") AS Label,TRUNCATE(SUM(Liter*PriceperLiter),2) AS DataSet1 FROM admin_2fuelconsumption fc LEFT JOIN admin_1vehiclelist vl ON fc.VehicleID=vl.TxnID LEFT JOIN admin_2vehicleassign va ON vl.TxnID=va.VehicleID JOIN `1branches` b ON va.BranchNo=b.BranchNo WHERE MONTH(Date)='.$_POST['MonthNo'].' AND VTID='.$_POST['VTID'].' GROUP BY fc.VehicleID ORDER BY Branch ASC;';
$stmt0=$link->prepare($sql0); $stmt0->execute();


$sql='SELECT GROUP_CONCAT(Label) AS Label,"Consumed Amount" AS legend1, GROUP_CONCAT(DataSet1) AS DataSet1,"Comparison between vehicles" AS ReportTitle,"" AS xaxis, "" AS yaxis,1 AS GraphID FROM ConsumedFuel';

$stmt=$link->query($sql); $field=$stmt->fetch();


$bwidth="90%";
$displaydiv=''; $newdiv=''; $newentry=''; $last=''; $c=1;
			

include($path.'/acrossyrs/js/reportcharts/vbar.php');


echo $displaydiv;
echo '<script>';
echo 'window.onload = function() {';
echo $echo;
echo '}';	
echo '</script>';

	  }
	} else {
		echo 'No Permission'; exit();
	}
  break;
  
  
  case 'DataErrors':
  
  $sqlgetlastkm = 'SELECT KmReading FROM admin_2fuelconsumption fc2 WHERE fc2.TxnID<fc.TxnID AND fc2.VehicleID = fc.VehicleID ORDER BY Date DESC LIMIT 1';
$sql='CREATE TEMPORARY TABLE DataError AS SELECT fc.Date,KmReading,Liter,PriceperLiter,vl.TxnID, InvoiceNo, VTID, CONCAT(Brand," ",Series,": ",PlateNo) AS ModelPlateNo, Branch, TRUNCATE((Liter*PriceperLiter),2) AS TotalAmt, CONCAT(TRUNCATE(((KmReading-('.$sqlgetlastkm.'))/Liter),2), " KM") AS `Km/Liter`, CONCAT(TRUNCATE(Liter/(KmReading-('.$sqlgetlastkm.')),2)," L") AS `Liter/Km`, CONCAT(TRUNCATE((Liter/(KmReading-('.$sqlgetlastkm.')))*PriceperLiter,2)," Php") AS `Peso/Km`, CONCAT(TRUNCATE((KmReading-('.$sqlgetlastkm.')),2)," KM") AS `DistanceTrav`, FuelType, CONCAT(id.Nickname, " ", id.Surname) AS EncodedBy FROM admin_2fuelconsumption fc JOIN 1_gamit.0idinfo id ON fc.EncodedByNo=id.IDNo JOIN admin_1vehiclelist vl ON fc.VehicleID=vl.TxnID JOIN admin_0fueltype ft ON vl.FTID=ft.FTID JOIN admin_2vehicleassign va ON vl.TxnID=va.VehicleID JOIN `1branches` b ON va.BranchNo=b.BranchNo WHERE Status=1';
$stmt0=$link->prepare($sql); $stmt0->execute();


$columnnameslist=array('Date','ModelPlateNo','Branch','FuelType','KmReading','Liter','PriceperLiter','TotalAmt','InvoiceNo','DistanceTrav','Km/Liter','Liter/Km','Peso/Km');

		$title='Fuel Consumption Data Errors';
		$formdesc='<br><div style="margin-left:20px"></i><ol>WHERE Conditions:<i><i><li>Vehicle Type = Motorcycle,</li><li>Peso/Km < 1 OR Peso/Km > 2.50,</li><li>KmReading = 0,</li><li>Liter = 0,</li><li>PriceperLiter < 40.</li></ol></i></div><br><br>';
		$columnnames=$columnnameslist;       
		
		$width='100%';
		$sql='';
		$editprocess='motorvehicles.php?w=FuelConsumptionPerVehicleLookup&VehicleID='; $editprocesslabel='Lookup';
		
		$sql = 'SELECT * FROM DataError WHERE `Peso/Km` is NOT NULL AND (VTID=1 AND `Peso/Km`<1 OR `Peso/Km`>2.5 OR KmReading=0 OR Liter=0 OR PriceperLiter<40) ORDER BY Date DESC,TxnID DESC';
		
		include('../backendphp/layout/displayastablenosort.php');

		
  break;
  
  
  
}

 $link=null; $stmt=null;
  ?>
  
