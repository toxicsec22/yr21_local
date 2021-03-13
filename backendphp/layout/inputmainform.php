<html>
<head>
<title><?php echo $title; ?></title>
<style>
.myButton {
	box-shadow: 0px 1px 0px 0px #f0f7fa;
	background:linear-gradient(to bottom, #80c0ff 5%, #019ad2 100%);
	background-color:#80c0ff;
	border-radius:6px;
	border:1px solid #057fd0;
	display:inline-block;
	cursor:pointer;
	color:#1b3d6d;
	font-family:Arial;
	font-size:12px;
	font-weight:bold;
	padding:3px 33px;
	text-decoration:none;
	text-shadow:0px -1px 0px #5b6178;
}
.myButton:hover {
	background:linear-gradient(to bottom, #019ad2 5%, #80c0ff 100%);
	background-color:#019ad2;
}
.myButton:active {
	position:relative;
	top:1px;
}

</style>


<?php
if ((isset($hidecontents) AND $hidecontents==1) or (isset($outside) and $outside)){ goto skipcontents;} else {include_once($path.'/'.$url_folder.'/switchboard/contents.php');}
skipcontents:
if (isset($_GET['done'])){
	if ($_GET['done']==1){
	echo '<font color="red">Data encoded.</font>';
	} else {
	echo '<font color="red">No permission.</font>';
	}
}
if (isset($title)) { echo '<br><br><h3>'.$title.'</h3>';}
?>
<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br>
<br>
</head>
<body>
<?php echo (isset($_GET['denied'])?'<font color="red">No permission</font>':''); ?>
<?php echo (isset($_GET['duplicate'])?'<font color="red">This record exists.</font>':''); ?>
<?php echo (isset($_GET['norequest'])?'<font color="red">No pending request of this number.</font>':''); ?>
<fieldset style="padding:6px;background-color:#1b3d6d;width:<?php echo (isset($fieldsetwidth)?$fieldsetwidth:'97%')?>;">
<!--<legend style="background-color:yellow;">
	<span style="color:#1b3d6d;padding:5px;font-size:11pt;"><b>&nbsp;<?php //if (isset($title)) { echo $title; } ?>&nbsp;</b></span>
</legend>-->
<form method="<?php echo (!isset($method)?'post':$method); ?>" action="<?php echo $action ?>" enctype="multipart/form-data">
<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" /> 
<font color="white">
<?php
$nooffields=count($columnnames);$fieldsinrow=(isset($fieldsinrow)?$fieldsinrow:4); 
for ($row = 0; $row <  $nooffields; $row++) {
    $whichlist=isset($columnnames[$row]['list'])?$columnnames[$row]['list']:null;
    $value=isset($columnnames[$row]['value'])?' value='.$columnnames[$row]['value']:'';
	$readonly=isset($columnnames[$row]['readonly'])?'readonly':'';
    $checked=isset($columnnames[$row]['checked'])?' checked ':'';
    $autocomplete=isset($columnnames[$row]['autocomplete'])?' autocomplete="on" ':' autocomplete="off" ';
    $required=((!isset($columnnames[$row]['required'])) or ($columnnames[$row]['required']==false))?'  >':' required='. $columnnames[$row]['required'] . ' onclick="IsEmpty('. $columnnames[$row]['field'].');"><font color="red">*</font>';
    $caption=isset($columnnames[$row]['caption'])?$columnnames[$row]['caption']:$columnnames[$row]['field'];
    $style=isset($columnnames[$row]['style'])?(' style="'.$columnnames[$row]['style'].'"'):'';
    if($columnnames[$row]['type']=='textarea'){
        echo $columnnames[$row]['field'].'<textarea rows='.$columnnames[$row]['rows'].' cols='.$columnnames[$row]['cols'].' id='.$columnnames[$row]['formid'].' name='.$columnnames[$row]['field'].' required='. $columnnames[$row]['required'].'></textarea>';
    } else {
		
    echo  ($columnnames[$row]['type']=='hidden'?'':$caption).' <input '.(isset($columnnames[$row]['placeholder'])?'placeholder="'.$columnnames[$row]['placeholder'].'"':'').' '.(isset($columnnames[$row]['maxlength'])?'maxlength="'.$columnnames[$row]['maxlength'].'"':'').' '.(isset($columnnames[$row]['id'])?'id="'.$columnnames[$row]['id'].'"':'').' '.(isset($columnnames[$row]['input-mask'])?'input-mask="'.$columnnames[$row]['input-mask'].'"':'').' type='. $columnnames[$row]['type']. ' size='. $columnnames[$row]['size'].$style. ' name='. $columnnames[$row]['field']. $value. $checked . (is_null($whichlist)?'':' list='. $columnnames[$row]['list']). ' '.$readonly.' ' .$autocomplete.$required.(($row+1)%$fieldsinrow==0?'<br><div style="margin-bottom:3px;"></div>':' &nbsp &nbsp');
    }
}
if (isset($outside) AND $outside){}// for zzjye and aquasys use
else { 
include_once $path.'/'.$url_folder.'/generalinfo/lists.inc';
foreach ($liststoshow as $list){
renderlist($list);
}
include_once('renderotherlists.php');
}
?>
</font>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<input type="submit" class="myButton" name="submit" value="Add New" <?php echo (isset($confmsg)?'OnClick="return confirm(\''.$confmsg.'\');"':''); ?>><br/><br/>
</form>
</fieldset>
</body>
<?php 
if(isset($inputmask)){
	include_once($path.'/acrossyrs/js/inputmask.php');
}
?>
</html>
