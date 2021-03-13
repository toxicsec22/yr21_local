<?php

include_once('../backendphp/layout/linkstyle.php');
	if($_GET['w']<>"CRPic" AND $_GET['w']<>"ORPic"){
		echo '<div>';
				if (allowedToOpen(8285,'1rtc')) {
					echo '<a id=\'link\' href="motorvehicles.php?w=VehicleTypeList">Vehicle Type</a> ';
					echo '<a id=\'link\' href="motorvehicles.php?w=FuelTypeList">Fuel Type</a> ';
					echo '<a id=\'link\' href="motorvehicles.php?w=RepairShopList">Accredited Repair Shops</a> ';
				}
				// if ((allowedToOpen(8285,'1rtc')) OR (allowedToOpen(8286,'1rtc'))){
				if ((allowedToOpen(8288,'1rtc'))){
					echo '<a id=\'link\' href="motorvehicles.php?w=VehicleList">Vehicle List</a> ';
					echo '<a id=\'link\' href="motorvehicles.php?w=VehiclesRegistrationSummary">Vehicle Registration Summary</a> ';
					echo '<a id=\'link\' href="motorvehicles.php?w=VehicleAssign">Vehicle Assign</a> ';
					echo str_repeat('&nbsp;',10);
				}
				if (allowedToOpen(8288,'1rtc')){
					echo '<a id=\'link\' href="motorvehicles.php?w=RequestRepair">Request a Repair</a> ';
					echo '<a id=\'link\' href="motorvehicles.php?w=ListOfRepairs">List of Request Repairs</a> ';
					if ((allowedToOpen(8285,'1rtc')) OR (allowedToOpen(8286,'1rtc'))){
						echo '<a id=\'link\' href="motorvehicles.php?w=RepairHistory">Repair History</a> ';
					}
					if ((allowedToOpen(8287,'1rtc'))){
							echo str_repeat('&nbsp;',10);
					} else {
						echo '<br><br>';
					}
					echo '<a id=\'link\' href="motorvehicles.php?w=EncodeFuelConsumption">Encode Fuel Consumption</a> ';
					echo '<a id=\'link\' href="motorvehicles.php?w=FuelConsumption">Fuel Consumption</a> ';
					if ((allowedToOpen(8285,'1rtc')) OR (allowedToOpen(8286,'1rtc'))){
						echo '<a id=\'link\' href="motorvehiclesviewreports.php?w=FuelConsumptionComparison">Fuel Consumption Comparison</a> ';
						echo '<a id=\'link\' href="motorvehiclesviewreports.php?w=DataErrors">Data Errors</a> ';
						echo '<a id=\'link\' href="motorvehicles.php?w=FuelConsumptionLogs">Fuel Consumption Delete Logs</a> ';
					}
				}
		echo '</div><br/>';
	}
?>