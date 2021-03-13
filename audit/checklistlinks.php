<?php
if(isset($_GET['withcontent'])){
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(6234,'1rtc')){ echo 'No Permission'; exit(); }
$showbranches=false;


include_once('../switchboard/contents.php');
echo '<h3>Please select list.</h3>';
}

include_once('../backendphp/layout/linkstyle.php');
echo '
<br><div id="section" style="display: block;">

    <div>';
	
	if (allowedToOpen(6230,'1rtc')){
		echo '<a id="link" href="branchauditchecklist.php?w=List">Branch Audit</a> &nbsp; &nbsp; &nbsp;';
	}
	
	if (allowedToOpen(6232,'1rtc')){	
		echo '<a id="link" href="keyliabilitychecklist.php?w=List">Key Liability</a> &nbsp; &nbsp; &nbsp;';
	}	
   
echo '</div><br><br>';


?>