<html>
<head>
<title><?php echo $title; ?></title>
<?php
include_once('../switchboard/contents.php');
if (isset($title)) { echo '<br><br><h3>'.$title.'</h3>';}
echo (isset($formdesc)?$formdesc:'');

if (isset($_GET['done'])){
	if ($_GET['done']==1){
	echo '<font color="red">Data encoded.</font>';
	} else {
	echo '<font color="red">No permission.</font>';
	}
}
?>
<br><br>
</head>
<body>
<form method="<?php echo $method ?>" action="<?php echo $action ?>" enctype="multipart/form-data">
<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" /> 
<?php
$nooffields=count($columnnames);
for ($row = 0; $row <  $nooffields; $row++) {
    $whichlist=isset($columnnames[$row]['list'])?$columnnames[$row]['list']:null;//is_null($columnnames[$row]['list'])?null:$columnnames[$row]['list'];
    $caption=isset($columnnames[$row]['caption'])?$columnnames[$row]['caption']:$columnnames[$row]['field'];
    $value=isset($columnnames[$row]['value'])?' value='.$columnnames[$row]['value']:' ';
    $required=($columnnames[$row]['required']==false)?'  autocomplete="off">':' required='. $columnnames[$row]['required'] . '  autocomplete="off"><font color="red">*</font>';
    echo  ($columnnames[$row]['type']=='hidden'?'':$caption).'<input type='. $columnnames[$row]['type']. ' size='. $columnnames[$row]['size']. ' name='. $columnnames[$row]['field']. $value . (is_null($whichlist)?'':' list='. $columnnames[$row]['list']). ' ' .
    $required.'<br>';
}
include_once "../generalinfo/lists.inc";
foreach ($liststoshow as $list){
renderlist($list);    
}
?>
<input type="submit" name="submit" value="Submit">
</form>
</body>
</html>