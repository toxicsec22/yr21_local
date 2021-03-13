<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(6501,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');
?>
<br><div id="section" style="display: block;">
<h3>Download PDF Forms</h3><br>
    <div><a id='link' href="../info/handbook/Remote_Working_Saturdays_Application_Form.pdf">Remote Working Saturdays Application</a>
        <a id='link' href="../info/handbook/recommendationforpromotion.pdf">Recommendation Form for Internal Promotion / Lateral Transfer</a>
		<a id='link' href="../info/handbook/on-boarding_checklist.pdf">On-Boarding Checklist and Schedule</a>
    </div><br/><br/>
