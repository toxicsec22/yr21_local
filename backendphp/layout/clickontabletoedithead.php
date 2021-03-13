<html>
<head>
<title><?php echo $title; ?></title>
<?php
if (isset($outside) AND $outside){ $diraddress='../../../'.$url_folder.'/';}// for zzjye and aquasys use
else { $diraddress='../';}

if (isset($hidecontents) AND $hidecontents==1){ goto skipcontents;} else {include_once('../switchboard/contents.php');}
skipcontents:

include_once($diraddress."backendphp/layout/regulartablestyle.php");
if (isset($_GET['done'])){
	if ($_GET['done']==1){
	echo '<font color="red">Data encoded.</font>';
	} else {
	echo '<font color="red">No permission.</font>';
	}
}
if (isset($title)) { echo '<br><br><h3>'.$title.'</h3>';}
?>
<i><?php echo (isset($formdesc)?$formdesc:'');?></i>
<br><br>
</head>
<body>