<html>
<head>
<title><?php echo $title; ?></title>
<style type="text/css">
#wrap {
   width:<?php echo !isset($width)?'120%':$width; ?>;
   margin:0 auto;
}
#left {
   float:left;
   width:<?php echo !isset($left)?'50%':$left; ?>;overflow: auto;
}
#right {
   margin-left: <?php echo !isset($leftmargin)?'51%':$leftmargin; ?>;overflow: auto;
   width:<?php echo !isset($right)?'50%':$right; ?>;
}

</style>
<?php
include_once('../switchboard/contents.php');
if (isset($_GET['done'])){
	switch ($_GET['done']){
	case 1:
	echo '<font color="red"><br>Data encoded.</font>';
	break;
	case 2:
	echo '<font color="red"><br>Data already existing.</font>';
	break;
	default:
	echo '<font color="red"><br>No permission.</font>';
	break;	
	}
}
if (isset($title)) { echo '<br>'.$title;} ?>
<br><i><?php echo (isset($formdesc)?$formdesc:'');?></i><br><br>
<?php
include_once ('regulartablestyle.php');
?>
</head>
<body>

<?php echo (isset($_GET['denied'])?'<font color="red">No permission</font>':''); ?>
<?php echo (isset($_GET['closeddata'])?'<font color="red">Data protected.</font>':''); ?>
<?php echo (isset($_GET['norequest'])?'<font color="red">No pending request of this number.</font>':''); ?>
<?php  (isset($nopost)?'':include('../backendphp/layout/postunpostform.php')); ?><?php echo (isset($filter)?'$filter':''); ?><br>
<div id="wrap"><div id="left">
<?php echo isset($lookupdata)?$lookupdata.'<br></div><div id="right">':'</div><div id="left">';
include('../backendphp/layout/displayastableeditcellsmain.php'); ?><br>
<form method="<?php echo $method ?>" action="<?php echo $action ?>" enctype="multipart/form-data" style="font-family:sans-serif;">
<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />
<?php echo (isset($_GET['noitem'])?'<font color="red">This item has been served or not in this request.</font><br>':''); ?>
<?php
if (isset($lookup)){echo $lookup;}
$nooffields=count($columnnames);
if ($editsub){
for ($row = 0; $row <  $nooffields; $row++) {
    $whichlist=isset($columnnames[$row]['list'])?$columnnames[$row]['list']:null;
    $value=isset($columnnames[$row]['value'])?' value='.$columnnames[$row]['value']:' ';
    $required=((!isset($columnnames[$row]['required'])) or ($columnnames[$row]['required']==false))?'  autocomplete="off">':' required='. $columnnames[$row]['required'] . '  autocomplete="off"><font color="red">*</font>';
    $caption=isset($columnnames[$row]['caption'])?$columnnames[$row]['caption']:$columnnames[$row]['field'];
    echo  ($columnnames[$row]['type']=='hidden'?'':$caption).'<input type='. $columnnames[$row]['type']. ' size='. $columnnames[$row]['size']. ' name='. $columnnames[$row]['field']. $value . (is_null($whichlist)?'':' list='. $columnnames[$row]['list']). ' ' .(isset($columnnames[$row]['autofocus'])?' autofocus ':'').$required;
}
?><input type="submit" name="submit" value="Add"><br><br><?php
include_once "../generalinfo/lists.inc"; foreach ($liststoshow as $list){ renderlist($list); }
include_once('renderotherlists.php');
?>
</form>
<?php
if (isset($addlsubmit)) { echo $addlsubmit;}
} // end if editsub=true
if (!empty($sub)) { echo $sub.'<br>';}
echo isset($rowcount)?$rowcount." record/s shown &nbsp &nbsp":"";
echo isset($total)?$total:'';

 if (isset($sqlsub2)){
	$sql=$sqlsub2; $columnnames=$columnnames2;
	if (isset($coltototal2)){ $coltototal=$coltototal2; }
	echo '</div><div id="right">'; 
	include('displayastableonlynoheaders.php');
	echo '';
 }
if (isset($withsub) and $withsub==true){include('../backendphp/layout/displayastableeditcellssub.php');}
//if (isset($with2sub) and $with2sub=true){include('../backendphp/layout/display2tableseditcellssub.php');}
?>

</div>
</div>
</body>
</html>