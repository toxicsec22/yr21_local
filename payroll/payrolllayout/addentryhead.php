<html>
<head>
<title><?php echo $title; ?></title>
<?php
include_once('../switchboard/contents.php');
include_once("../backendphp/layout/regulartablestyle.php");
echo 
"<h3>$title</h3>";
if (isset($_GET['done']) and ($_GET['done']==1)){
	echo '<font color="red">Data encoded.</font>';
} elseif(isset($_GET['done']) and ($_GET['done']==0)){
	echo '<font color="red">Data protected. No data was encoded.</font>';
} 
?>
</head>
<body>
    