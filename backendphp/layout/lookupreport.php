<html>
<head>
<title><?php echo $title; ?></title>
<?php
if (isset($outside) AND $outside){ $diraddress='../../../'.$url_folder.'/';}// for zzjye and aquasys use
else { $diraddress='../';}

if (isset($hidecontents) AND $hidecontents==1){ goto skipcontents;} else {include_once('../switchboard/contents.php');}
skipcontents:
if (isset($showprint) and $showprint){
	?>
<form style=" display: inline">
<input name="print" TYPE="button" onClick="window.print()" value="Print!">
</form>
<?php
}


//if (isset($title)) { echo '<br><br>'.$title;} ?>
<font size="2"<i><?php echo (isset($formdesc)?'<br><br>'.$formdesc:'');?></i></font>
<?php
include_once ('regulartablestyle.php');
?>
<br>
</head>
<body>
<?php  echo $main; ?><br>
<?php
IF (isset($sortfield)){include($diraddress.'backendphp/layout/sortbyform.php');echo '<br><br>';} 
echo isset($total)?$total:'';
$nooffields=count($columnnames);
echo $sub.'<br>';

?>
</body>
</html>