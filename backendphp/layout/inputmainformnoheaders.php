<?php echo (isset($_GET['denied'])?'<font color="red">No permission</font>':''); ?>
<?php echo (isset($_GET['duplicate'])?'<font color="red">This record exists.</font>':''); ?>
<?php echo (isset($_GET['norequest'])?'<font color="red">No pending request of this number.</font>':''); ?>
<form method="<?php echo $method ?>" action="<?php echo $action ?>" enctype="multipart/form-data">
<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" /> 
<?php
if (isset($subtitle)) { echo '<br><br><h4>'.$subtitle.'</h4><BR>';}
$nooffields=count($columnnames);
for ($row = 0; $row <  $nooffields; $row++) {
    $whichlist=isset($columnnames[$row]['list'])?$columnnames[$row]['list']:null;
    $value=isset($columnnames[$row]['value'])?' value='.$columnnames[$row]['value']:'';
    $autocomplete=isset($columnnames[$row]['autocomplete'])?' autocomplete="on" ':' autocomplete="off" ';
    $required=((!isset($columnnames[$row]['required'])) or ($columnnames[$row]['required']==false))?'  >':' required='. $columnnames[$row]['required'] . ' onclick="IsEmpty('. $columnnames[$row]['field'].');"><font color="red">*</font>';
    $caption=isset($columnnames[$row]['caption'])?$columnnames[$row]['caption']:$columnnames[$row]['field'];
    
    echo  ($columnnames[$row]['type']=='hidden'?'':$caption).'<input type='. $columnnames[$row]['type']. ' size='. $columnnames[$row]['size']. ' name='. $columnnames[$row]['field']. $value . (is_null($whichlist)?'':' list='. $columnnames[$row]['list']). ' ' .$autocomplete.$required.(($row+1)%4==0?'<br><br>':' &nbsp &nbsp');
}
include_once "../generalinfo/lists.inc";
foreach ($liststoshow as $list){
renderlist($list);
}
if (isset($whichotherlist)){
	switch ($whichotherlist){
		case 'invty':
			include_once "../invty/undeliveredlist.inc";
			break;
		case 'acctg':
			include_once "../acctg/acctglists.inc";
			break;
	}
	
	foreach ($otherlist as $list){
	renderotherlist($list,$listcondition);    
}	
}
?>
&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<input type="submit" name="submit" value="Submit">
</form>
