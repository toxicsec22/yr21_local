<?php
include_once('../backendphp/layout/linkstyle.php');
echo '<br>

    <div>';
	if(allowedToOpen(6706,'1rtc')){
	echo '<a id="link" href="mapsetting.php">Existing Branches</a> ';
	}
	if(allowedToOpen(6707,'1rtc')){
    echo '<a id="link" href="mapsetting.php?w=ExistingClients">Existing Clients</a> ';
	}
	if(allowedToOpen(array(6708,6709),'1rtc')){
     echo '<a id="link" href="mapsetting.php?w=PendingPotentialCompetitors">Potential Branch/Clients, Competitors</a> ';
	}
	if(allowedToOpen(array(6708,6709,6707),'1rtc')){
	 echo '&nbsp; &nbsp; &nbsp; &nbsp; 
		<a id="link" href="mapview.php"><b>View Map Per Branch</b></a>';
		
		echo '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
		<a id="link" href="mapsetting.php?w=DuplicateEntries">Duplicate Coordinates</a>';
	}
	if(allowedToOpen(6708,'1rtc')){
		echo '&nbsp; &nbsp; &nbsp; &nbsp; <a id="link" href="mapsetting.php?w=ImportExport">Manage Temporary Data</a> ';
		echo '<a id="link" href="mapnosession.php">Outside Map</a> ';
	}
		
		echo '
    </div><br/><br/>';
	
	?>