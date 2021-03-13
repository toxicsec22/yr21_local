<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php'; 
if (!allowedToOpen(7131,'1rtc')) { echo 'No Permission'; exit(); };
$showbranches=false;

	include_once('../switchboard/contents.php');
	include_once('../backendphp/layout/linkstyle.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;


include($path.'/acrossyrs/js/reportcharts/includejscharts.php'); 
include_once($path.'/acrossyrs/js/reportcharts/mgraphlabel.php'); ?>


<br><div id="section" style="display: block;">


<?php
include_once('allreportslinks.php');
echo '<br><br><br>';
					
$echo='';

$which=isset($_GET['w'])?$_GET['w']:'SalesByClientType';


$graphtitle='FullName';
$pwidth='40%';


if (in_array($which,array('SalesByClientType','SalesByClientTypeYr'))){

$sql0='CREATE TEMPORARY TABLE `graphreport11` (
  `ReportID` tinyint(4) NOT NULL AUTO_INCREMENT,
  `ReportTitle` varchar(100) DEFAULT NULL,
  `OtherDesc` varchar(20) NOT NULL,
  `Label` varchar(200) NOT NULL,
  `xaxis` varchar(25) NOT NULL,
  `yaxis` varchar(25) NOT NULL,
  `legend1` varchar(15) NOT NULL,
  `legend2` varchar(15) NOT NULL,
  `legend3` varchar(15) NOT NULL,
  `legend4` varchar(15) NOT NULL,
  `legend5` varchar(15) NOT NULL,
  `legend6` varchar(15) NOT NULL,
  `legend7` varchar(15) NOT NULL,
  `legend8` varchar(15) NOT NULL,
  `legend9` varchar(15) NOT NULL,
  `legend10` varchar(15) NOT NULL,
  `legend11` varchar(15) NOT NULL,
  `legend12` varchar(15) NOT NULL,
  `legend13` varchar(15) NOT NULL,
  `legend14` varchar(15) NOT NULL,
  `legend15` varchar(15) NOT NULL,
  PRIMARY KEY (`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;'; //graph report
$stmt=$link->prepare($sql0);$stmt->execute();

$sql0='CREATE TEMPORARY TABLE `graphboard11` (
  `TxnID` smallint(6) NOT NULL AUTO_INCREMENT,
  `IDNo` smallint(6) DEFAULT NULL,
  `GraphID` tinyint(2) NOT NULL,
  `DataSet1` varchar(100) NOT NULL,
  `DataSet2` varchar(2000) NOT NULL,
  `DataSet3` varchar(200) NOT NULL,
  `DataSet4` varchar(200) NOT NULL,
  `DataSet5` varchar(200) NOT NULL,
  `DataSet6` varchar(200) NOT NULL,
  `DataSet7` varchar(200) NOT NULL,
  `DataSet8` varchar(200) NOT NULL,
  `DataSet9` varchar(200) NOT NULL,
  `DataSet10` varchar(200) NOT NULL,
  `DataSet11` varchar(200) NOT NULL,
  `DataSet12` varchar(200) NOT NULL,
  `DataSet13` varchar(200) NOT NULL,
  `DataSet14` varchar(200) NOT NULL,
  `DataSet15` varchar(200) NOT NULL,
  `ReportID` tinyint(4) NOT NULL,
  PRIMARY KEY (`TxnID`),
  UNIQUE KEY `IDNo` (`IDNo`,`GraphID`,`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1'; //graphboard
$stmt=$link->prepare($sql0);$stmt->execute();


if (isset($_POST['btnSubmit'])){
	$txndate=strlen($_POST['monthno'])==1?'0'.$_POST['monthno']:$_POST['monthno'];
} else {
	$txndate=date('m'); 
}

$sqlm='SELECT TRUNCATE((SUM(s.Qty*s.UnitPrice)/1000000),2) AS TotSales,ClientTypeShortName FROM `invty_2sale` m JOIN `invty_2salesub` s ON m.`TxnID`=s.`TxnID` JOIN 1clients c ON m.ClientNo=c.ClientNo JOIN gen_info_0clienttype ct ON c.ClientType=ct.ClientTypeID WHERE txntype IN (1,2,5,10) AND (m.ClientNo NOT BETWEEN 1000 AND 9999) AND (m.ClientNo NOT BETWEEN 15001 AND 15005) '.(($which=='SalesByClientType')?'AND Month(`Date`)='.$txndate.'':'').'';

}

switch ($which){

case 'SalesByClientType':
case 'SalesByClientTypeYr':

$title='Sales By Client Type';
echo '<title>'.$title.'</title>';
echo '<form action="salesperclienttype.php?w=SalesByClientType" method="POST">Month: <input type="text" size="5" value="'.$txndate.'" name="monthno"> <input type="submit" name="btnSubmit" value="Lookup"> <a href="salesperclienttype.php?w=SalesByClientTypeYr">Sales By Client Type (All Months)</a></form> <br>';

if($which=='SalesByClientType'){
	echo '<h3>'.$title.' ('.date("F", mktime(0, 0, 0, $txndate, 10)).')</h3>';
} else {
	echo '<h3>'.$title.' (All Months)</h3>';
}

echo '<br>';

$reportid='1';

	
	
	$stmttot=$link->query($sqlm); $restot=$stmttot->fetch();
	
	$alltot=$restot['TotSales'];
	
   $sqlcsales=$sqlm.' GROUP BY c.ClientType';   
   $stmtcsales=$link->query($sqlcsales); $res=$stmtcsales->fetchAll();
   
	include_once('../../acrossyrs/js/reportcharts/colors.php');
	$label=''; $dataset1=''; $dataset2=''; $rc=0;
	
	foreach ($res as $field) {
		$percent=number_format(($field['TotSales']/$alltot)*100,2);
		$label.="'".$field['ClientTypeShortName']." ".$percent."%'".',';
		$dataset1.=$field['TotSales'].',';
		$dataset2.='window.chartColors.'.$color[$rc].',';
		
		$rc=$rc+1;
	}
  
   
	$label=substr($label, 0, -1);
	$label = substr($label, 1); //removed first char
	$dataset1=substr($dataset1, 0, -1);
	$dataset2=substr($dataset2, 0, -1);
	   
	$sqlcmain='INSERT INTO graphreport11 SET Label="'.$label.'", ReportID=1,ReportTitle="Sales Per ClientType"';
	$stmt=$link->prepare($sqlcmain);$stmt->execute();

	$sqlcsub='INSERT INTO graphboard11 SET DataSet1="'.$dataset1.'",DataSet2="'.$dataset2.'", ReportID=1';
	$stmt=$link->prepare($sqlcsub);$stmt->execute();
	//end


	

$sql = 'SELECT g.*,"" AS addlabel, gr.* FROM graphboard11 g JOIN graphreport11 gr ON g.ReportID=gr.ReportID WHERE g.ReportID IN ('.$reportid.') ORDER BY g.ReportID DESC; ';
$stmt=$link->query($sql); $res=$stmt->fetchall();
break;


}


$c=1;

$displaydiv=''; $newdiv=''; $newentry=''; $last='';
foreach ($res as $field) {
	

	if ($newentry==$last){
		$newdiv='';
	} else {
		$newdiv='<div style="clear: both; display: block; position: relative;height:30px;"></div>';
	}
	
	
		include($path.'/acrossyrs/js/reportcharts/pie.php');
	
	
	$last=$newentry;
	
$c++;	 
} 
	echo $displaydiv;
	
	echo '<script>';
	echo 'window.onload = function() {';
	echo $echo;
	echo '}';	
	echo '</script>';

?>

