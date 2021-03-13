<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(8288,'1rtc')) { echo 'No permission'; exit; }
if (allowedToOpen(8286,'1rtc')){ header("Location:motorvehicles.php?w=ListOfRepairs"); }
if (allowedToOpen(8285,'1rtc')){ header("Location:motorvehicles.php?w=VehicleAssign"); }
if (allowedToOpen(8287,'1rtc')){ header("Location:motorvehicles.php?w=ListOfRepairs"); }
if (allowedToOpen(8289,'1rtc')){ header("Location:motorvehicles.php?w=ListOfRepairs"); }
if (allowedToOpen(8288,'1rtc')){ header("Location:motorvehicles.php?w=ListOfRepairs"); }
?>