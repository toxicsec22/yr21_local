<?php
error_reporting(E_ALL);
	ini_set('display_errors', 1);
if(session_id()==''){ session_start(); }
$home=true;
$path=$_SERVER['DOCUMENT_ROOT'];
include_once ($path.'/acrossyrs/dbinit/userinit.php');
include_once($path.'/acrossyrs/logincodes/checkifloggedon.php');
?>
<HTML>
<head>
<title>Home</title>
</head>
<?php
 if (date('Y')==$currentyr) { include_once('../indexbg.php');}
?>
<body style="
    background-image:url(<?php echo 'generalinfo/bgpics/'.$bgpicsuffix; ?>.jpg);
                        background-repeat: no-repeat;
                        background-attachment: fixed;
                        background-size: <?php echo $picsize; ?>%;
                        background-position: center;
                        background-color:#<?php echo $bodybgcolor; ?>;
                        padding: 10px;">
<div style=" position: absolute;
    right:  5px;
    bottom: 50px;">
<?php
include_once('generalinfo/bdaytoday.php');
?>
</div>
<!--<div style="float:right;"><img src='generalinfo/bgpics/announceonswitch.jpg'></div>-->
<?php

include_once('switchboard/messagesonswitch.php');
//include_once('approvals/forapprovalswitchboard.php');
include_once('switchboard/contents.php');

?>
</div> <!-- end div menu -->
</div> <!-- end div wrapper -->
<br class="clearFloat" />
</body>
</html>