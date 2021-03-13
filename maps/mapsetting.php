<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6705,'1rtc')) { echo 'No permission'; exit; }

$which=!isset($_GET['w'])?((!allowedToOpen(6706,'1rtc'))?'ExistingClients':'ExistingBranch'):$_GET['w'];

if(allowedToOpen(6710,'1rtc')){
	header('Location:mapview.php?eb=1');
	exit();
}

if($which=='ExistingBranch'){
	$showbranches=false; 
} else {
	$showbranches=true;
}
include_once('../switchboard/contents.php');
include_once('maplinks.php');

?>
<script>
	function showDiv(divId, element)
	{
		document.getElementById(divId).style.display = (element.value == 3 || element.value == 4 || element.value == 5 || element.value == 7) ? 'block' : 'none';
	}
</script>


<?php


switch($which){
	case'ExistingBranch':
	if (!allowedToOpen(6706,'1rtc')) { echo 'No permission'; exit; }
		$title='Branches and Coordinates';
		
		echo '<title>'.$title.'</title>';
		echo'<h3>'.$title.'</h3></br>
		';
		echo '<a href="mapview.php?eb=1" target="_blank">Tag Existing Branches</a><br><br>';
		
		$sql='select BranchNo,Branch,RegisteredAddress,lat,lng,radiusKM FROM 1branches WHERE Active=1 AND BranchNo NOT IN (95) ORDER BY Branch';
		$stmt=$link->query($sql); $rows=$stmt->fetchAll();
		
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
		
		
		echo '<table>';
		echo '<tr><th>Branch</th><th>Address</th><th>Latitude</th><th>Longitude</th><th>Radius in KM</th><th></th></tr>';
		foreach($rows AS $row){
			echo '<form action="mapsetting.php?w=UpdateLatLng&BranchNo='.$row['BranchNo'].'&action_token='.$_SESSION['action_token'].'" method="POST" autocomplete=off><tr bgcolor='. $rcolor[$colorcount%2].'><td>'.$row['Branch'].'</td><td>'.$row['RegisteredAddress'].'</td><td><input type="text" name="lat" value="'.$row['lat'].'" size="10"></td><td><input type="text" name="lng" value="'.$row['lng'].'" size="10"></td><td><input type="text" name="radiusKM" value="'.$row['radiusKM'].'" size="5"></td><td><input type="submit" value="Update" name="btnUpdate" OnClick="return confirm(\'Are you sure you want to update?\');"></td></tr></form>';
			$colorcount++;
		}
		echo '</table>';
	break;
	
	
	case 'UpdateLatLng':
	if (!allowedToOpen(6706,'1rtc')) { echo 'No permission'; exit; }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$branchno=intval($_GET['BranchNo']);
			
			if($_POST['lat']=="" OR $_POST['lat']==0){
				$lat='NULL';
			} else {
				$lat=$_POST['lat'];
			}
			
			if($_POST['lng']=="" OR $_POST['lng']==0){
				$lng='NULL';
			} else {
				$lng=$_POST['lng'];
			}
			
			
			$sql='update 1branches set lat='.$lat.',lng='.$lng.',radiusKM="'.$_POST['radiusKM'].'" where BranchNo='.$branchno.'';
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:mapsetting.php?w=ExistingBranch");
	break;
	
	case 'ExistingClients':
	if (!allowedToOpen(6707,'1rtc')) { echo 'No permission'; exit; }
	$title='Existing Clients and Coordinates';
		
		echo '<title>'.$title.'</title>';
		echo'<h3>'.$title.'</h3></br>';
		
		echo '<a href="mapview.php?tec=1">Tag Existing Clients</a><br><br>'; //tec tag existing client
	$sql='SELECT bcj.ClientNo,ClientName,lat,lng FROM gen_info_1branchesclientsjxn bcj JOIN 1clients c ON bcj.ClientNo=c.ClientNo WHERE BranchNo='.$_SESSION['bnum'].' AND bcj.ClientNo>10004';
	$stmt=$link->query($sql); $rows=$stmt->fetchAll();
	
	$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
		
		
		echo '<table>';
		echo '<tr><th>Client</th><th>Latitude</th><th>Longitude</th><th></th></tr>';
		foreach($rows AS $row){
			echo '<form action="mapsetting.php?w=UpdateLatLngClient&ClientNo='.$row['ClientNo'].'&action_token='.$_SESSION['action_token'].'" method="POST" autocomplete=off><tr bgcolor='. $rcolor[$colorcount%2].'><td>'.$row['ClientName'].'</td><td><input type="text" name="lat" value="'.$row['lat'].'" size="10"></td><td><input type="text" name="lng" value="'.$row['lng'].'" size="10"></td><td><input type="submit" value="Update" name="btnUpdate" OnClick="return confirm(\'Are you sure you want to update?\');"></td></tr></form>';
			$colorcount++;
		}
		echo '<tr><td colspan=4>Total records: '.$colorcount.'</td></tr>';
		echo '</table>';
	
	break;
	
	case 'UpdateLatLngClient':
	if (!allowedToOpen(6707,'1rtc')) { echo 'No permission'; exit; }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$clientno=intval($_GET['ClientNo']);
			
			if($_POST['lat']=="" OR $_POST['lat']==0){
				$lat='NULL';
			} else {
				$lat=$_POST['lat'];
			}
			
			if($_POST['lng']=="" OR $_POST['lng']==0){
				$lng='NULL';
			} else {
				$lng=$_POST['lng'];
			}
			
			
			$sql='update gen_info_1branchesclientsjxn set lat='.$lat.',lng='.$lng.' where ClientNo='.$clientno.' AND BranchNo='.$_SESSION['bnum'].'';
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:mapsetting.php?w=ExistingClients");
	break;
	
	case 'PendingPotentialCompetitors':
	if (!allowedToOpen(array(6708,6709),'1rtc')) { echo 'No permission'; exit; }
		$title='Potential Branch/Clients, Competitors and their coordinates';
		
		echo '<title>'.$title.'</title>';
		echo'<h3>'.$title.'</h3></br>
		';
		
		echo '<form action="mapsetting.php?w=AddNewPotential&action_token='.$_SESSION['action_token'].'" method="POST" autocomplete=off>Branch/Client/Competitor: <input type="text" value="" name="description"> Latitude: <input type="text" name="lat" value="" size="10"> Longitude: <input type="text" name="lng" value="" size="10"> Category: <select name="Category"><option value="0">Unclassified</option>'.((allowedToOpen(array(6708),'1rtc'))?'<option value="3">Potential Branch</option>':'').'<option value="4">Potential Client (Car Aircon)</option><option value="7">Potential Client (Refrigeration)</option><option value="5">Competitor</option></select> <input type="submit" value="Add New" name="btnAddNewPotential"></form><br>';
		
		$sql='select id,description,lat,lng,location_status,EncodedByNo FROM maps_2potential WHERE BranchNo='.$_SESSION['bnum'].' ORDER BY `location_status`,description';
		$stmt=$link->query($sql); $rows=$stmt->fetchAll();
		
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
		
		
		echo '<table>';
		echo '<tr><th>Branch/Clients</th><th>Latitude</th><th>Longitude</th><th>Category</th><th></th></tr>';
		foreach($rows AS $row){
			echo '<form action="mapsetting.php?w=UpdateLatLngPotential&id='.$row['id'].'&action_token='.$_SESSION['action_token'].'" method="POST" autocomplete=off><tr bgcolor='. $rcolor[$colorcount%2].'><td style="padding:3px;"><input type="text" value="'.$row['description'].'" name="description"></td><td><input type="text" name="lat" value="'.$row['lat'].'" size="10"></td><td><input type="text" name="lng" value="'.$row['lng'].'" size="10"></td><td><select name="Category"><option value="0" '.($row['location_status']==0?'selected':'').'>Unclassified</option>'.((allowedToOpen(array(6708),'1rtc'))?'<option value="3" '.($row['location_status']==3?'selected':'').'>Potential Branch</option>':'').'<option value="4" '.($row['location_status']==4?'selected':'').'>Potential Client (Car Aircon)</option><option value="7" '.($row['location_status']==7?'selected':'').'>Potential Client (Refrigeration)</option><option value="5" '.($row['location_status']==5?'selected':'').'>Competitor</option></select></td><td>'.($row['EncodedByNo']==$_SESSION['(ak0)']?'<input type="submit" value="Update" name="btnUpdate" OnClick="return confirm(\'Are you sure you want to update?\');"> <a href="mapsetting.php?w=DelPotential&id='.$row['id'].'&action_token='.$_SESSION['action_token'].'" OnClick="return confirm(\'Are you sure you want to DELETE?\');">Del</a>':'').'</td></tr></form>';
			$colorcount++;
		}
		echo '</table>';
	
	break;
	
	
	case 'AddNewPotential':
	if (!allowedToOpen(array(6708,6709),'1rtc')) { echo 'No permission'; exit; }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			
			if($_POST['lat']=="" OR $_POST['lat']==0){
				$lat='NULL';
			} else {
				$lat=$_POST['lat'];
			}
			
			if($_POST['lng']=="" OR $_POST['lng']==0){
				$lng='NULL';
			} else {
				$lng=$_POST['lng'];
			}
			
			
			$sql='INSERT INTO maps_2potential set description="'.$_POST['description'].'",location_status='.$_POST['Category'].',lat='.$lat.',lng='.$lng.',EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW(),BranchNo='.$_SESSION['bnum'].';';
			
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:mapsetting.php?w=PendingPotentialCompetitors");
	break;
	
	case 'UpdateLatLngPotential':
	if (!allowedToOpen(array(6708,6709),'1rtc')) { echo 'No permission'; exit; }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$id=intval($_GET['id']);
			
			if($_POST['lat']=="" OR $_POST['lat']==0){
				$lat='NULL';
			} else {
				$lat=$_POST['lat'];
			}
			
			if($_POST['lng']=="" OR $_POST['lng']==0){
				$lng='NULL';
			} else {
				$lng=$_POST['lng'];
			}
			
			
			$sql='update maps_2potential set description="'.$_POST['description'].'",location_status='.$_POST['Category'].',lat='.$lat.',lng='.$lng.' where id='.$id.' AND EncodedByNo='.$_SESSION['(ak0)'].'';
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:mapsetting.php?w=PendingPotentialCompetitors");
	break;
	
	case 'DelPotential':
	if (!allowedToOpen(array(6708,6709),'1rtc')) { echo 'No permission'; exit; }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$id=intval($_GET['id']);
			
			
			$sql='delete from maps_2potential where id='.$id.' AND EncodedByNo='.$_SESSION['(ak0)'];
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:mapsetting.php?w=PendingPotentialCompetitors");
	break;
	
	
	case 'DuplicateEntries':
	if (!allowedToOpen(array(6708,6709),'1rtc')) { echo 'No permission'; exit; }
	$sql0='CREATE TEMPORARY TABLE combinedtables AS SELECT Branch AS Description,lat,lng,"ExistingBranch" AS `Category` FROM 1branches WHERE lat IS NOT NULL AND lng IS NOT NULL
UNION ALL
SELECT ClientName AS Description,lat,lng,"ExistingClient" AS `Category` FROM gen_info_1branchesclientsjxn bcj JOIN 1clients c ON bcj.ClientNo=c.ClientNo WHERE lat IS NOT NULL AND lng IS NOT NULL
UNION ALL
SELECT description AS Description,lat,lng,"Potential_Branch_Clients_Competitors" AS `Category` FROM maps_2potential WHERE lat IS NOT NULL AND lng IS NOT NULL;';
	$stmt0=$link->prepare($sql0); $stmt0->execute();
	
	$title='Duplicate Coordinates';
		
		echo '<title>'.$title.'</title>';
		echo'<h3>'.$title.'</h3></br>
		';
		
		$sql='SELECT 
    GROUP_CONCAT(Description SEPARATOR ",<br>") AS Description,GROUP_CONCAT(Category SEPARATOR ",<br>") AS Category,
    lat,  COUNT(lat),
    lng,      COUNT(lng)
FROM
    combinedtables
GROUP BY 
    lat , 
    lng
HAVING  COUNT(lat) > 1
    AND COUNT(lng) > 1;';
		$stmt=$link->query($sql); $rows=$stmt->fetchAll();
		
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
		
		
		echo '<table>';
		echo '<tr><th>Branch/Clients</th><th>From</th><th>Latitude</th><th>Longitude</th></tr>';
		foreach($rows AS $row){
			echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td>'.$row['Description'].'</td><td>'.$row['Category'].'</td><td>'.$row['lat'].'</td><td>'.$row['lng'].'</td></tr>';
			$colorcount++;
		}
		echo '</table>';
	
	break;
	
	
	case 'ImportExport':
	if (!allowedToOpen(6708,'1rtc')) { echo 'No permission'; exit; }
	// print_r($_SESSION);
		$title='Manage Temporary Data';
		include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
		echo comboBox($link,'SELECT BranchNo,Branch FROM 1branches WHERE Active=1 AND Pseudobranch=0 UNION SELECT ClientNo,ClientName FROM 1clients WHERE ClientNo>10004 AND Inactive=0','BranchNo','Branch','existinglists');
		echo '<title>'.$title.'</title>';
		echo'<h3>'.$title.'</h3></br>
		';
		
		
		$sql='select id,description,td.lat,td.lng,location_status,IFNULL(Branch,IFNULL(ClientName,"")) AS ClientBranch from maps_tempdata td LEFT JOIN 1branches b ON td.ClientNoBranchNo=b.BranchNo LEFT JOIN 1clients c ON td.ClientNoBranchNo=c.ClientNo ORDER BY description';
		$stmt=$link->query($sql); $rows=$stmt->fetchAll();
		
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
		
		
		echo '<table>';
		echo '<tr><th>Encoded Branch/Clients</th><th>Latitude</th><th>Longitude</th><th>Category</th><th>System Branch/Clients</th><th></th></tr>';
		// $cnts=0;
		foreach($rows AS $row){
			echo '<style>
				#hidden_div'.$colorcount.' {
					display: none;
				}
			</style>';
			echo '<form action="mapsetting.php?w=UpdateOutside&id='.$row['id'].'&action_token='.$_SESSION['action_token'].'" method="POST" autocomplete=off><tr bgcolor='. $rcolor[$colorcount%2].'><td style="padding:3px;"><input type="text" value="'.$row['description'].'" name="description"></td><td><input type="text" name="lat" value="'.$row['lat'].'" size="10"></td><td><input type="text" name="lng" value="'.$row['lng'].'" size="10"></td><td><select name="Category" onchange="showDiv(\'hidden_div'.$colorcount.'\', this)"><option value="-1" '.($row['location_status']==-1?'selected':'').'>Temporary</option><option value="2" '.($row['location_status']==2?'selected':'').'>EXISTING Client</option><option value="3" '.($row['location_status']==3?'selected':'').'>Potential Branch</option><option value="4" '.($row['location_status']==4?'selected':'').'>Potential Client (Car Aircon)</option><option value="7" '.($row['location_status']==7?'selected':'').'>Potential Client (Refrigeration)</option><option value="5" '.($row['location_status']==5?'selected':'').'>Competitor</option></select></td><td><span id="hidden_div'.$colorcount.'">AssignedToBranch: </span> <input type="text" size="30" value="'.($row['ClientBranch']<>''?$row['ClientBranch']:$_SESSION['@brn']).'" name="Existing" list="existinglists"></td><td>'.'<input type="submit" value="Update" name="btnUpdate" OnClick="return confirm(\'Are you sure you want to update?\');"> <a href="mapsetting.php?w=DelOutside&id='.$row['id'].'&action_token='.$_SESSION['action_token'].'" OnClick="return confirm(\'Are you sure you want to DELETE?\');">Del</a>'.'</td></tr></form>';
			$colorcount++;
		}
		echo '</table>';
		
		echo '<br><div><div style="float:left;"><form action="mapsetting.php?w=UpdateNow&action_token='.$_SESSION['action_token'].'" method="POST"><input style="color:white;background-color:green;" type="submit" value="Import Now" OnClick="return confirm(\'Are you sure you want to import data?\');"></form><i>* Temporary data will not be imported.</i></div><div style="margin-left:50%;"><form action="mapsetting.php?w=TruncateData&action_token='.$_SESSION['action_token'].'" method="POST"><input type="submit" style="color:white;background-color:red;" value="Delete All Temporary Data" OnClick="return confirm(\'Are you sure you want to DELETE data?\');"></form></div></div>';
	
	break;
	
	
	case 'DelOutside':
	if (!allowedToOpen(6708,'1rtc')) { echo 'No permission'; exit; }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$id=intval($_GET['id']);
			
			$sql='delete from maps_tempdata where id='.$id.'';
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:mapsetting.php?w=ImportExport");
	
	break;
	
	
	case 'UpdateOutside':
	if (!allowedToOpen(6708,'1rtc')) { echo 'No permission'; exit; }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$id=intval($_GET['id']);
			
			if($_POST['lat']=="" OR $_POST['lat']==0){
				$lat='NULL';
			} else {
				$lat=$_POST['lat'];
			}
			
			if($_POST['lng']=="" OR $_POST['lng']==0){
				$lng='NULL';
			} else {
				$lng=$_POST['lng'];
			}
			
			$sql01='SELECT BranchNo FROM 1branches WHERE Branch="'.$_POST['Existing'].'"';
			$stmt01=$link->query($sql01); $row01=$stmt01->fetch();
			
			
			if($stmt01->rowCount()==0){
				$sql01='SELECT ClientNo FROM 1clients WHERE ClientName="'.$_POST['Existing'].'"';
				$stmt01=$link->query($sql01); $row01=$stmt01->fetch();
				$clientnobranchno=$row01['ClientNo'];
			} else {
				$clientnobranchno=$row01['BranchNo'];
			}
			
			if($clientnobranchno<>''){
				$con=',ClientNoBranchNo='.$clientnobranchno.'';
			} else {
				$con=',ClientNoBranchNo=NULL';
			}
			$sql='update maps_tempdata set description="'.$_POST['description'].'",location_status='.$_POST['Category'].',lat='.$lat.',lng='.$lng.$con.' where id='.$id.'';
			
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:mapsetting.php?w=ImportExport");
	break;
	
	case 'UpdateNow':
	//update existing 1clients
	// $sql='UPDATE maps_tempdata td JOIN 1clients c ON td.ClientNoBranchNo=c.ClientNo SET c.lat=td.lat,c.lng=td.lng  WHERE location_status=2 ;';
	$sql='UPDATE maps_tempdata td JOIN gen_info_1branchesclientsjxn bcj ON td.ClientNoBranchNo=bcj.ClientNo SET bcj.lat=td.lat,bcj.lng=td.lng  WHERE location_status=2 AND bcj.BranchNo='.$_SESSION['bnum'].';';
	$stmt=$link->prepare($sql); $stmt->execute();
	
	//insert outside data
	$sql='INSERT IGNORE INTO maps_2potential (lat,lng,description,location_status,BranchNo,EncodedByNo,TimeStamp) SELECT lat,lng,description,location_status,ClientNoBranchNo,'.$_SESSION['(ak0)'].',TimeStamp FROM maps_tempdata WHERE location_status IN (3,4,5,7) AND ClientNoBranchNo IS NOT NULL';
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:mapsetting.php?w=ImportExport");
	break;
	
	case 'TruncateData':
	if (!allowedToOpen(6708,'1rtc')) { echo 'No permission'; exit; }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			
			$sql='delete from maps_tempdata';
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:mapsetting.php?w=ImportExport");
	
	break;
	
}
?>
