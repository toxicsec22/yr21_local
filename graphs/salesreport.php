<?php
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/dbinit/userinit.php'; 
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

?>
<br><div id="section" style="display: block;">

<head>
	<?php include($path.'/acrossyrs/js/reportcharts/includejscharts.php'); ?>
</head>

<body>

<?php

$echo='';
$graphtitle='GraphTitle';
$bwidth='30%'; $lwidth='30%'; $pwidth='30%';

$orderbycondi='ORDER BY g.ReportID';
if (allowedToOpen(7135,'1rtc')){
	$join='';
	$condi='WHERE g.IDNo='.$_SESSION['(ak0)'].'';
	$gt='OtherDesc';
} else if (allowedToOpen(7134,'1rtc')) { //SAM
	$join='JOIN attend_1salesgroups ag ON g.IDNo=ag.TeamLeader';
	$condi='WHERE '.$_SESSION['(ak0)'].' IN (SAM)';
	$gt='Nickname';
} else {
	$join='';
	$condi='WHERE (FIND_IN_SET('.$_SESSION['&pos'].',`AllowedToView`))';
	$gt='OtherDesc';
}

$sql = 'SELECT g.*,gr.*,"" AS addlabel,'.$gt.' AS GraphTitle FROM graphboard g LEFT JOIN 1_gamit.0idinfo id ON g.IDNo=id.IDNo JOIN graphreport gr ON g.ReportID=gr.ReportID '.$join.' '.$condi.' AND gr.OnSB=1 AND g.ReportID NOT IN (3) '.$orderbycondi.'; ';
$stmt=$link->query($sql); $res=$stmt->fetchall();

$c=1;
$displaydiv=''; $newdiv='';
foreach ($res as $field) {
	if ($field['GraphID']==1){
		include($path.'/acrossyrs/js/reportcharts/vbar.php');
	} else if ($field['GraphID']==2){
		include($path.'/acrossyrs/js/reportcharts/line.php');
	} else if ($field['GraphID']==3){
		include($path.'/acrossyrs/js/reportcharts/hbar.php');
	} else {
		include($path.'/acrossyrs/js/reportcharts/pie.php');
	}

$c++;	 
} 
	echo $displaydiv;
	
	echo '<script>';
	echo 'window.onload = function() {';
	echo $echo;
	echo '}';	
	echo '</script>';

?>
</div>
<div style="clear: both; display: block; position: relative;"></div> <!--force to new line-->
