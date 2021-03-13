<html>
<head>
<title><?php echo $title; ?></title>
<?php
if (!allowedToOpen(599,'1rtc')) { echo 'No permission'; exit; }
include_once('../switchboard/contents.php');
include_once "../generalinfo/lists.inc";
if (isset($liststoshow)){ foreach ($liststoshow as $list){renderlist($list); }}
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
<div id="right"><?php  (isset($nopost)?'':include('../backendphp/layout/postunpostform.php')); ?><br>
<?php  echo $main . ($editsub?'<br><br><font size="2">'.$addsub.'</font>':''); ?><br></div>
<?php 
if ($editsub){
?>
</form>
<div id="left">
<?php 
include('insertedinfo/cashsalesvsdep.php');
echo $lookupdata.'<br>';
include('branchbudgetbal.php');

echo $cashcalc; 
include('../forms/calcbills.php');
echo 'Diff from Net Deposit: '.number_format($sum-$netdep,2);
$action='praddsub.php?w=SaveCashCount&TxnID='; include('addeditdepcashcount.php');
 ?>

</div>
<?php
echo isset($adddata)?$adddata:'';
} // end if($editsub)
?>
<div id="right">
<?php
echo $sub.'<br>';
echo isset($totalamt)?'Deposit Subtotal:  '.number_format($totalamt,2).'<br><br>':'';
echo ($editsub?$addencash:'') .(isset($_GET['msg'])?'<font color="maroon"><b>'.$_GET['msg'].'</b></font><br>':''). ((!isset($subencash) or $subencash=='')?'':'Encashment:<br>'.$subencash.'<br>'.$totalencash);
echo $grandtotal;
?>
</div>
</body>
</html>