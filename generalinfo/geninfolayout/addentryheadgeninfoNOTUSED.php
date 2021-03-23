<html>
<head>
<title><?php echo $title; ?></title>
<?php
include_once("../backendphp/layout/regulartablestyle.php");
echo 
"<br><a href=\"../../index.php\">Back to Main Switchboard</a><br>
<h3>$title</h3>";
if (isset($_GET['done'])){
	echo '<font color="red">Data encoded.</font>';
}
?>
</head>
<body>
    