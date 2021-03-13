<!DOCTYPE html>
<html>
<head>
    <title>Branch Map</title>
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
</head>
<body>
<style>

    /* Optional: Makes the sample page fill the window. */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }
 /* Always set the map height explicitly to define the size of the div
 * element that contains the map. */
    #map {
        height: 100%;
    }
</style>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; 
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/js/includesscripts.php';
if (!allowedToOpen(6705,'1rtc')) { echo 'No permission'; exit; }
if(isset($_GET['eb'])){
	$showbranches=false; 
} else {
	$showbranches=true;
}
include_once('../switchboard/contents.php');
include_once('maplinks.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT bcj.ClientNo,ClientName FROM gen_info_1branchesclientsjxn bcj JOIN 1clients c ON bcj.ClientNo=c.ClientNo WHERE BranchNo='.$_SESSION['bnum'].' AND bcj.ClientNo>10004 AND (lat IS NULL OR lng IS NULL)','ClientName','ClientNo','clientlist');
echo comboBox($link,'SELECT BranchNo,Branch FROM 1branches WHERE Active=1 AND Pseudobranch=0','Branch','BranchNo','branchlist');

if(isset($_GET['add_location'])) {
    add_location();
}

function add_location(){
	if (allowedToOpen(6710,'1rtc')) {
        exit();
    }
    global $link;
	
    $lat = $_GET['lat'];
    $lng = $_GET['lng'];
    $description =$_GET['description'];
	
	if(isset($_GET['eb'])){
		$sqlll='select BranchNo FROM 1branches WHERE BranchNo='.$description;
		$stmt=$link->query($sqlll);
		if($stmt->rowCount()>0){
			$sql='UPDATE 1branches set lat='.$lat.',lng='.$lng.' WHERE BranchNo='.$description;
			$stmt=$link->prepare($sql); $stmt->execute();
		} else {
			exit();
		}
	} else if(isset($_GET['tec'])){
		$sqlll='select ClientNo FROM gen_info_1branchesclientsjxn WHERE ClientNo='.$description.' AND BranchNo='.$_SESSION['bnum'].'';
		$stmt=$link->query($sqlll);
		if($stmt->rowCount()>0){
			$sql='UPDATE gen_info_1branchesclientsjxn set lat='.$lat.',lng='.$lng.' WHERE ClientNo='.$description.' AND BranchNo='.$_SESSION['bnum'].'';
			$stmt=$link->prepare($sql); $stmt->execute();
		} else {
			exit();
		}
	} else {
	
		$sql='INSERT INTO maps_2potential set lat='.$lat.',lng='.$lng.',description="'.$description.'",BranchNo='.$_SESSION['bnum'].',EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW()';
		$stmt=$link->prepare($sql); $stmt->execute();
	
	}
    echo"Inserted Successfully";
    
}

function get_confirmed_locations(){
	global $link;
	
	$branchcond=''; $union='';
	if(!isset($_GET['eb'])){
		// $branchcond='AND BranchNo='.$_SESSION['bnum'];
		// $union='UNION SELECT BranchNo,lat,lng,ClientName,2 AS isconfirmed FROM gen_info_1branchesclientsjxn bcj JOIN 1clients c ON bcj.ClientNo=c.ClientNo WHERE lat IS NOT NULL AND lng IS NOT NULL '.$branchcond.' ';
		$union='UNION SELECT BranchNo,lat,lng,ClientName,IF(ClientType IN (1,2),2,6) AS isconfirmed FROM gen_info_1branchesclientsjxn bcj JOIN 1clients c ON bcj.ClientNo=c.ClientNo JOIN gen_info_0clienttype ct ON c.ClientType=ct.ClientTypeID WHERE lat IS NOT NULL AND lng IS NOT NULL '.$branchcond.' ';
		if(!isset($_GET['tec'])){
			$union.='UNION select BranchNo,lat,lng,description,location_status AS isconfirmed from maps_2potential WHERE lat IS NOT NULL AND lng IS NOT NULL '.$branchcond.'';
		}
	}
	
     $sqldata='select BranchNo,lat,lng,Branch,1 as isconfirmed
from 1branches WHERE lat IS NOT NULL AND lng IS NOT NULL '.$branchcond.' '.$union.''; 


// echo $sqldata;

$stmt=$link->query($sqldata); 
$rows = array();
	
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)){
	 $rows[] = $r;
}

 
    $indexed = array_map('array_values', $rows);

    echo json_encode($indexed);
    if (!$rows) {
        return null;
    }
}
$existingbranch_icon='branchlogo.png';
$pending_icon='http://maps.google.com/mapfiles/ms/icons/purple-dot.png';
$existingclient_icon='green-dotec.png'; //car aircon
$existingclient_icon2='yellow-dotec.png'; //refrigeration
$potentialbranch_icon='http://maps.google.com/mapfiles/ms/icons/blue-dot.png';
$potentialclient_icon='http://maps.google.com/mapfiles/ms/icons/green-dot.png'; //car aircon
$potentialclient_icon2='http://maps.google.com/mapfiles/ms/icons/yellow-dot.png'; //refrigeration
$competitor_icon='http://maps.google.com/mapfiles/ms/icons/orange-dot.png';

if(isset($_GET['eb'])){
	echo '<div style="background-color:white;color:blue;padding:3px;text-align:center;"><b>Tag Existing Branches</b></div>';
}
if(isset($_GET['tec'])){
	echo '<div style="background-color:white;color:green;padding:3px;text-align:center;"><b>Tag Existing Clients</b></div>';
}

if(!isset($_GET['eb']) AND !isset($_GET['tec'])){
$caraircon='Technicians,Car Aircon Shop';
$refrigeration='Unknown,Contractors,Retailer,Corporate,Individual';
$spanstyle='background-color:white;border:1px solid black;padding:12px;font-size:8.5pt;';
echo '<b>Legend: </b><br>';
echo ' <span style="'.$spanstyle.'">Existing Branch: <img src="'.$existingbranch_icon.'"></span>';
echo ' <span style="'.$spanstyle.'">Unclassified: <img src="'.$pending_icon.'"></span>';
echo ' <span style="'.$spanstyle.'">Existing Client: ('.$caraircon.') <img src="'.$existingclient_icon.'"></span>';
echo ' <span style="'.$spanstyle.'">Existing Client: ('.$refrigeration.') <img src="'.$existingclient_icon2.'"></span>';
echo ' <span style="'.$spanstyle.'">Potential Branch: <img src="'.$potentialbranch_icon.'"></span><br><br>';
echo ' <span style="'.$spanstyle.'">Potential Client: ('.$caraircon.') <img src="'.$potentialclient_icon.'"></span>';
echo ' <span style="'.$spanstyle.'">Potential Client: ('.$refrigeration.') <img src="'.$potentialclient_icon2.'"></span>';
echo ' <span style="'.$spanstyle.'">Competitor: <img src="'.$competitor_icon.'"></span><br><br>';
} else {
	echo '<b>Existing Branches Map</b><br>';
}
?>

<script type="text/javascript" 
            src="https://maps.googleapis.com/maps/api/js?language=en&key=AIzaSyBnjAxq9wnyBnP3h63qdb8Yui4q0WL5Dqo">
    </script>

    <div id="map"></div>
    <script>
        /**
         * Create new map
         */
        var InfoWindow;
        var map;
        var existingbranch_icon =  <?php echo "'".$existingbranch_icon."'";?> ; // 1
        var pending_icon =  <?php echo "'".$pending_icon."'";?> ; // 0
        var existingclient_icon =  <?php echo "'".$existingclient_icon."'";?> ; //2
        var existingclient_icon2 =  <?php echo "'".$existingclient_icon2."'";?> ; //6
        var potentialbranch_icon =  <?php echo "'".$potentialbranch_icon."'";?> ; //3
        var potentialclient_icon =  <?php echo "'".$potentialclient_icon."'";?> ; //4
        var potentialclient_icon2 =  <?php echo "'".$potentialclient_icon2."'";?> ; //7
        var competitor_icon = <?php echo "'".$competitor_icon."'";?>  ; //5
        var locations = <?php get_confirmed_locations() ?>;
        var myOptions = {
			<?php
			
			if(!isset($_GET['eb'])){
				
				     $sqlll='select lat,lng,1 as isconfirmed
					from 1branches WHERE BranchNo='.$_SESSION['bnum'].' LIMIT 1';
					$stmt=$link->query($sqlll);
					$rows=$stmt->fetch();
					
				$lat=$rows['lat'];
				$lng=$rows['lng'];
				$zoom=12;
				
			} else {
				$lat=14.6091;
				$lng=121.0223;
				$zoom=8;
			}
				
			?>
            zoom: <?php echo $zoom; ?>,
			
            center: new google.maps.LatLng(<?php echo $lat; ?>,<?php echo $lng; ?>),
            mapTypeId: 'roadmap'
        };
        map = new google.maps.Map(document.getElementById('map'), myOptions);
		infoWindow = new google.maps.InfoWindow();
		
		const locationButton = document.createElement("button");
		  locationButton.textContent = "Current Location";
		  locationButton.classList.add("custom-map-control-button");
		  map.controls[google.maps.ControlPosition.TOP_CENTER].push(locationButton);
		  locationButton.addEventListener("click", () => {
			// Try HTML5 geolocation.
			if (navigator.geolocation) {
			  navigator.geolocation.getCurrentPosition(
				(position) => {
				  const pos = {
					lat: position.coords.latitude,
					lng: position.coords.longitude,
				  };
				  infoWindow.setPosition(pos);
				  infoWindow.setContent("Location found.");
				  infoWindow.open(map);
				  map.setCenter(pos);
				},
				() => {
				  handleLocationError(true, infoWindow, map.getCenter());
				}
			  );
			} else {
			  // Browser doesn't support Geolocation
			  handleLocationError(false, infoWindow, map.getCenter());
			}
		  });
		  
		  
		
		 const branchmap = {
		<?php 
			$sqlr='select Branch,lat,lng,IF(BranchNo='.$_SESSION['bnum'].',"#FF0000","#FFFFFF") AS color,radiusKM from 1branches WHERE lat is not null and lng is not NULL';
					$stmtr=$link->query($sqlr);
					$rowrs=$stmtr->fetchAll();
					$search  = array(0,1,2,3,4,5,6,7,8,9);
					$replace = array('zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine');

					foreach($rowrs AS $rowr){
						
						echo str_replace($search,$replace,str_replace(' ','_',$rowr['Branch'])).': { center: { lat: '.$rowr['lat'].', lng: '.$rowr['lng'].' }, color: "'.$rowr['color'].'", radius: '.$rowr['radiusKM'].' }, ';
					}
		?> 
		
		};
		  
		  for (const city in branchmap) {
			// Add the circle for this city to the map.
			const cityCircle = new google.maps.Circle({
			  strokeColor: "#FF0000",
			  strokeOpacity: 0.8,
			  strokeWeight: 2,
			  fillColor: branchmap[city].color,
			  fillOpacity: 0.35,
			  map,
			  center: branchmap[city].center,
			  radius: branchmap[city].radius * 1000,
			});
		  }
		  function handleLocationError(browserHasGeolocation, infoWindow, pos) {
			  infoWindow.setPosition(pos);
			  infoWindow.setContent(
				browserHasGeolocation
				  ? "Error: The Geolocation service failed."
				  : "Error: Your browser doesn't support geolocation."
			  );
			  infoWindow.open(map);
			}
        /**
         * Global marker object that holds all markers.
         * @type {Object.<string, google.maps.LatLng>}
         */
        var markers = {};

        /**
         * Concatenates given lat and lng with an underscore and returns it.
         * This id will be used as a key of marker to cache the marker in markers object.
         * @param {!number} lat Latitude.
         * @param {!number} lng Longitude.
         * @return {string} Concatenated marker id.
         */
        var getMarkerUniqueId= function(lat, lng) {
            return lat + '_' + lng;
        };

        /**
         * Creates an instance of google.maps.LatLng by given lat and lng values and returns it.
         * This function can be useful for getting new coordinates quickly.
         * @param {!number} lat Latitude.
         * @param {!number} lng Longitude.
         * @return {google.maps.LatLng} An instance of google.maps.LatLng object
         */
        var getLatLng = function(lat, lng) {
            return new google.maps.LatLng(lat, lng);
        };

        /**
         * Binds click event to given map and invokes a callback that appends a new marker to clicked location.
         */
		 
		<?php if (!allowedToOpen(6710,'1rtc')){ ?>
        var addMarker = google.maps.event.addListener(map, 'click', function(e) {
            var lat = e.latLng.lat(); // lat of clicked point
            var lng = e.latLng.lng(); // lng of clicked point
            var markerId = getMarkerUniqueId(lat, lng); // an that will be used to cache this marker in markers object.
			<?php if(isset($_GET['eb'])) { ?>
            var marker = new google.maps.Marker({
                position: getLatLng(lat, lng),
                map: map,
                animation: google.maps.Animation.DROP,
                id: 'marker_' + markerId,
                html: "    <div id='info_"+markerId+"'>\n" +
                "        <table class=\"map1\">\n" +
                "            <tr>\n" +
                "                <td><a>BranchNo:</a></td>\n" +
                "                <td><input type='text' id='manual_description' list='branchlist'></td></tr>\n" +
                "            <tr><td></td><td><input type='button' value='Save' onclick='saveData("+lat+","+lng+")'/></td></tr>\n" +
                "        </table>\n" +
                "    </div>"
            });
			<?php } else if(isset($_GET['tec'])) { ?>
            var marker = new google.maps.Marker({
                position: getLatLng(lat, lng),
                map: map,
                animation: google.maps.Animation.DROP,
                id: 'marker_' + markerId,
                html: "    <div id='info_"+markerId+"'>\n" +
                "        <table class=\"map1\">\n" +
                "            <tr>\n" +
                "                <td><a>ClientNo:</a></td>\n" +
                "                <td><input type='text' id='manual_description' list='clientlist'></td></tr>\n" +
                "            <tr><td></td><td><input type='button' value='Save' onclick='saveData("+lat+","+lng+")'/></td></tr>\n" +
                "        </table>\n" +
                "    </div>"
            });
			<?php } else { ?>
			var marker = new google.maps.Marker({
                position: getLatLng(lat, lng),
                map: map,
                animation: google.maps.Animation.DROP,
                id: 'marker_' + markerId,
                html: "    <div id='info_"+markerId+"'>\n" +
                "        <table class=\"map1\">\n" +
                "            <tr>\n" +
                "                <td><a>Description:</a></td>\n" +
                "                <td><textarea  id='manual_description' placeholder='Description'></textarea></td></tr>\n" +
                "            <tr><td></td><td><input type='button' value='Save' onclick='saveData("+lat+","+lng+")'/></td></tr>\n" +
                "        </table>\n" +
                "    </div>"
            });
			<?php } ?>
            markers[markerId] = marker; // cache marker in markers object
            bindMarkerEvents(marker); // bind right click event to marker
            bindMarkerinfo(marker); // bind infowindow with click event to marker
        });
        <?php } ?>
        /**
         * Binds  click event to given marker and invokes a callback function that will remove the marker from map.
         * @param {!google.maps.Marker} marker A google.maps.Marker instance that the handler will binded.
         */
        var bindMarkerinfo = function(marker) {
            google.maps.event.addListener(marker, "click", function (point) {
                var markerId = getMarkerUniqueId(point.latLng.lat(), point.latLng.lng()); // get marker id by using clicked point's coordinate
                var marker = markers[markerId]; // find marker
                infowindow = new google.maps.InfoWindow();
                infowindow.setContent(marker.html);
                infowindow.open(map, marker);
                // removeMarker(marker, markerId); // remove it
            });
        };

        /**
         * Binds right click event to given marker and invokes a callback function that will remove the marker from map.
         * @param {!google.maps.Marker} marker A google.maps.Marker instance that the handler will binded.
         */
        var bindMarkerEvents = function(marker) {
            google.maps.event.addListener(marker, "rightclick", function (point) {
                var markerId = getMarkerUniqueId(point.latLng.lat(), point.latLng.lng()); // get marker id by using clicked point's coordinate
                var marker = markers[markerId]; // find marker
                removeMarker(marker, markerId); // remove it
            });
        };

        /**
         * Removes given marker from map.
         * @param {!google.maps.Marker} marker A google.maps.Marker instance that will be removed.
         * @param {!string} markerId Id of marker.
         */
        var removeMarker = function(marker, markerId) {
            marker.setMap(null); // set markers setMap to null to remove it from map
            delete markers[markerId]; // delete marker instance from markers object
        };


        /**
         * loop through (pdomysql) dynamic locations to add markers to map.
         */
        var i ; var confirmed = 0;
        for (i = 0; i < locations.length; i++) {
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                map: map,
                icon :   (((locations[i][4] === 1 ?  existingbranch_icon  : (locations[i][4] === 2 ?  existingclient_icon  : (locations[i][4] === 3 ?  potentialbranch_icon : (locations[i][4] === 4 ?  potentialclient_icon : (locations[i][4] === 5 ?  competitor_icon : (locations[i][4] === 6 ?  existingclient_icon2 : (locations[i][4] === 7 ?  potentialclient_icon2 : pending_icon))))))))),
				
                html: "<div>\n" +
                "<table class=\"map1\">\n" +
                "<tr>\n" +
                "<td></td>\n" +
                "<td><textarea disabled id='manual_description' placeholder='Description'>"+locations[i][3]+"</textarea></td></tr>\n" +
                "</table>\n" +
                "</div>"
            });

            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
					
                    infowindow = new google.maps.InfoWindow();
                    confirmed =  locations[i][4] === '1' ?  'checked'  :  0;
                    $("#confirmed").prop(confirmed,locations[i][4]);
                    $("#id").val(locations[i][0]);
                    $("#description").val(locations[i][3]);
                    $("#form").show();
                    infowindow.setContent(marker.html);
                    infowindow.open(map, marker);
                }
            })(marker, i));
        }

        /**
         * SAVE save marker from map.
         * @param lat  A latitude of marker.
         * @param lng A longitude of marker.
         */
        function saveData(lat,lng) {
            var description = document.getElementById('manual_description').value;
			<?php if(isset($_GET['eb'])) { ?>
            var url = 'mapview.php?add_location&description=' + description + '&lat=' + lat + '&lng=' + lng + '&eb=1';
			<?php } else if(isset($_GET['tec'])) { ?>
			var url = 'mapview.php?add_location&description=' + description + '&lat=' + lat + '&lng=' + lng + '&tec=1';
			<?php } else { ?>
			var url = 'mapview.php?add_location&description=' + description + '&lat=' + lat + '&lng=' + lng;
			<?php } ?>
            downloadUrl(url, function(data, responseCode) {
				// alert(data);
                if (responseCode === 200  && data.length > 1) {
                    var markerId = getMarkerUniqueId(lat,lng); // get marker id by using clicked point's coordinate
                    var manual_marker = markers[markerId]; // find marker
					<?php if(isset($_GET['eb'])){ ?>
                    manual_marker.setIcon(existingbranch_icon);
					<?php } else if(isset($_GET['tec'])){ ?>
                    manual_marker.setIcon(existingclient_icon);
					<?php } else { ?>
					manual_marker.setIcon(pending_icon);
					<?php } ?>
                    infowindow.close();
					<?php if(isset($_GET['eb'])){ ?>
                    infowindow.setContent("<div style=' color: blue; font-size: 20px;'> Tagged Existing Branch! </div>");
					<?php } else if(isset($_GET['tec'])){ ?>
                    infowindow.setContent("<div style=' color: green; font-size: 20px;'> Tagged Existing Client! </div>");
					<?php } else { ?>
					 infowindow.setContent("<div style=' color: purple; font-size: 20px;'> Added as pending!! </div>");
					<?php } ?>
                    infowindow.open(map, manual_marker);

                }else{
                    console.log(responseCode);
                    console.log(data);
                    infowindow.setContent("<div style='color: red; font-size: 25px;'>Inserting Errors</div>");
                }
            });
        }

        function downloadUrl(url, callback) {
            var request = window.ActiveXObject ?
                new ActiveXObject('Microsoft.XMLHTTP') :
                new XMLHttpRequest;

            request.onreadystatechange = function() {
                if (request.readyState == 4) {
                    callback(request.responseText, request.status);
                }
            };

            request.open('GET', url, true);
            request.send(null);
        }


    </script>




</body>
</html>


