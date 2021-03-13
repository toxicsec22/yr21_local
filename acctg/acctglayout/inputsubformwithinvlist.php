<html>
<head>
<title><?php echo $title; ?></title>
<?php
include_once('../switchboard/contents.php');
include_once "../generalinfo/lists.inc";

if (isset($_GET['done'])){
	switch ($_GET['done']){
	case 1:
	echo '<font color="red">Data encoded.</font>';
	break;
	default:
	echo '<font color="red">No permission.</font>';
	break;	
	}
}
if (isset($title)) { echo '<br><br>'.$title;} ?>
<br><i><?php echo (isset($formdesc)?$formdesc:'');?></i><br><br>
<?php
include_once ('../backendphp/layout/regulartablestyle.php');
?>
<style>
#right {
   float:right;
   width:60%;
}
#left {
   float:left;
   width:40%;
}
</style>
</head>
<body>
<?php echo (isset($_GET['denied'])?'<font color="red">No permission</font>':''); ?>
<?php echo (isset($_GET['closeddata'])?'<font color="red">Data protected.</font>':''); ?>
<div id="left">
<?php echo $lookupdata.'<br>';

 ?>

</div>
<div id="right"><?php  (isset($nopost)?'':include('../backendphp/layout/postunpostform.php')); ?>
<?php (isset($sortfield)?include('../backendphp/layout/sortbyform.php'):''); ?>
<br>
<?php
echo $main ;
$nooffields=count($columnnames);
if ($nooffields==0) { echo '<br><br>'; goto noaddcols; }
?><br><form method="post" action="<?php echo $action ?>" enctype="multipart/form-data" style="font-family:sans-serif;">
<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />
<?php 
if ($editsub){
for ($row = 0; $row <  $nooffields; $row++) {
    $whichlist=isset($columnnames[$row]['list'])?$columnnames[$row]['list']:null;
    $value=isset($columnnames[$row]['value'])?' value='.$columnnames[$row]['value']:' ';
    $required=((!isset($columnnames[$row]['required'])) or ($columnnames[$row]['required']==false))?'  autocomplete="off">':' required='. $columnnames[$row]['required'] . '  autocomplete="off"><font color="red">*</font>';
    $caption=isset($columnnames[$row]['caption'])?$columnnames[$row]['caption']:$columnnames[$row]['field'];
    echo  ($columnnames[$row]['type']=='hidden'?'':$caption).'<input type='. $columnnames[$row]['type']. ' size='. $columnnames[$row]['size']. ' name='. $columnnames[$row]['field']. $value . (is_null($whichlist)?'':' list='. $columnnames[$row]['list']). ' ' .(isset($columnnames[$row]['autofocus'])?' autofocus ':'').$required;
}
?><input type="submit" name="submit" value="Add"><br><br>
</form>
<?php
noaddcols:
include_once "../generalinfo/lists.inc";
foreach ($liststoshow as $list){
renderlist($list);    
}
//include_once('../renderotherlists.php');
//echo isset($adddata)?$adddata:'';
} // end if($editsub)

echo $sub.'<br>';
echo isset($total)?$total.'<br>':'';
//echo isset($subencash)?$subencash:'';
echo (isset($_GET['msg'])?'<font color="maroon"><b>'.$_GET['msg'].'</b></font><br>':''). ((!isset($subencash) or $subencash=='')?'':'<br>'.$subencash.'<br>'.$totalencash);
echo isset($grandtotal)?$grandtotal:'';
?>
</div>
</body>
</html>