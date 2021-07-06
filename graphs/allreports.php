<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php'; 
if (!allowedToOpen(7131,'1rtc')) { echo 'No Permission'; exit(); };
$showbranches=false;

	include_once('../switchboard/contents.php');
	include_once('../backendphp/layout/linkstyle.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;



?>

	<?php include($path.'/acrossyrs/js/reportcharts/includejscharts.php'); ?>
	<?php include_once($path.'/acrossyrs/js/reportcharts/mgraphlabel.php'); ?>


<br><div id="section" style="display: block;">


<?php

include_once('allreportslinks.php');	

echo '<br><br><br>';
					
$echo='';

$which=isset($_GET['w'])?$_GET['w']:(((allowedToOpen(7136,'1rtc')) OR (allowedToOpen(7137,'1rtc')))?'AggregateReports':(allowedToOpen(7138,'1rtc')?'NumberOfTransactions':'BranchComparisons'));


$graphtitle='FullName';
$bwidth='30%'; $lwidth='30%'; $pwidth='30%';

if (allowedToOpen(7134,'1rtc')) { //SAM
	$join='JOIN attend_1salesgroups ag ON g.IDNo=ag.TeamLeader';
	$condiothers='AND (FIND_IN_SET('.$_SESSION['&pos'].',`AllowedToView`))';
	$condi='AND '.$_SESSION['(ak0)'].' IN (SAM)';
} else if (allowedToOpen(7135,'1rtc')) { //STL
	$join='JOIN attend_1salesgroups ag ON g.IDNo=ag.TeamLeader';
	$condiothers='AND (FIND_IN_SET('.$_SESSION['&pos'].',`AllowedToView`))';
	$condi='AND '.$_SESSION['(ak0)'].' IN (TeamLeader)';
} else {
	$join='';
	$condiothers='';
	$condi='';
}

if (in_array($which,array('AggregateReports'))){

$sql0='CREATE TEMPORARY TABLE `graphreport11` (
  `ReportID` tinyint(4) NOT NULL AUTO_INCREMENT,
  `ReportTitle` varchar(100) DEFAULT NULL,
  `OtherDesc` varchar(20) NOT NULL,
  `Label` varchar(100) NOT NULL,
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


// $txndate=(strlen(date('m'))==1?'0'.date('m'):date('m'));
if (isset($_POST['btnSubmit'])){
	$txndate=strlen($_POST['monthno'])==1?'0'.$_POST['monthno']:$_POST['monthno'];
} else {
	$txndate=date('m'); 
}

}

if (in_array($which,array('TransactionsPerBranch','NoOfSales','LeadsAndWins'))){
	
	if (allowedToOpen(7134,'1rtc')){ //sam
		$acondi='WHERE bg.SAM='.$_SESSION['(ak0)'].''; 
	} else if (allowedToOpen(7135,'1rtc')){
		$acondi='WHERE bg.TeamLeader='.$_SESSION['(ak0)'].'';
	} else {
		$acondi='';
	}

	$sql0='CREATE TEMPORARY TABLE `tempdata` (
	  `TxnID` smallint(6) NOT NULL AUTO_INCREMENT,
	  `BranchNo` smallint(6) DEFAULT NULL,
	  `TeamLeader` smallint(6) DEFAULT NULL,
	  `Data` varchar(2000) NOT NULL,
	  `Data2` varchar(2000) NOT NULL,
	  PRIMARY KEY (`TxnID`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;';
	$stmt=$link->prepare($sql0);$stmt->execute();
	
}

switch ($which){


case 'AggregateReports':

$title='Aggregate Reports';
echo '<title>'.$title.'</title>';
echo '<form action="allreports.php?w=AggregateReports" method="POST">Month: <input type="text" size="5" value="'.$txndate.'" name="monthno"> <input type="submit" name="btnSubmit" value="Lookup"></form><br>';
echo '<h3>'.$title.' ('.date("F", mktime(0, 0, 0, $txndate, 10)).')</h3>';
echo '<br>';
if (allowedToOpen(7137,'1rtc')) {
$reportid='1,4,9,10';
} else if (allowedToOpen(7136,'1rtc')){
	$reportid='4,9';
} else {
	$reportid='0';
}



//start sales by company
   $sqlcsales='SELECT SUM(TRUNCATE((Net/1000000),2)) AS CompanySales,Company FROM acctg_6targetscores ts JOIN 1branches b ON ts.BranchNo=b.BranchNo JOIN 1companies c ON b.CompanyNo=c.CompanyNo WHERE DisplayType='.($txndate==date('m')?'5':'1').' AND MonthNo='.$txndate.' GROUP BY c.CompanyNo ORDER BY Company;';
   
   $stmt=$link->query($sqlcsales); $res=$stmt->fetchall();
	include_once('../../acrossyrs/js/reportcharts/colors.php');
	$label=''; $dataset1=''; $dataset2=''; $rc=0;
	foreach ($res as $field) {
		
		$label.="'".$field['Company']."'".',';
		$dataset1.=$field['CompanySales'].',';
		$dataset2.='window.chartColors.'.$color[$rc].',';
		
		$rc=$rc+1;
	}
   
	$label=substr($label, 0, -1);
	$label = substr($label, 1); //removed first char
	$dataset1=substr($dataset1, 0, -1);
	$dataset2=substr($dataset2, 0, -1);
	   
	$sqlcmain='INSERT INTO graphreport11 SET Label="'.$label.'", ReportID=1,ReportTitle="Sales By Company"';
	$stmt=$link->prepare($sqlcmain);$stmt->execute();

	$sqlcsub='INSERT INTO graphboard11 SET DataSet1="'.$dataset1.'",DataSet2="'.$dataset2.'", ReportID=1';
	$stmt=$link->prepare($sqlcsub);$stmt->execute();
	//end



$sql = 'SELECT g.*,"" AS addlabel, gr.*,CONCAT(NickName," ",Surname) AS FullName FROM graphboard11 g LEFT JOIN 1_gamit.0idinfo id ON g.IDNo=id.IDNo JOIN graphreport11 gr ON g.ReportID=gr.ReportID WHERE g.ReportID IN ('.$reportid.') '.$condiothers.' ORDER BY g.ReportID DESC; '; //echo $sql;
$stmt=$link->query($sql); $res=$stmt->fetchall();
break;


case 'SalesHistory':

if ((!allowedToOpen(7137,'1rtc')) AND (!allowedToOpen(7136,'1rtc'))){ echo 'No Permission'; exit(); }
$title='Based on Actual Sales';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3>';

$yrfrom=2014; $yrto=$lastyr;
$sql=''; $sql0='SELECT `Year`,SUM(`1`)+SUM(`2`)+SUM(`3`)+SUM(`4`)+SUM(`5`)+SUM(`6`)+SUM(`7`)+SUM(`8`)+SUM(`9`)+SUM(`10`)+SUM(`11`)+SUM(`12`) AS `TotalSales` FROM hist_incus.totalsalesperyr WHERE Year=';
while ($yrfrom<=$yrto){
    $sql.=$sql0.$yrfrom.' UNION ';
    $yrfrom=$yrfrom+1;
}
$sql.=' SELECT '.$currentyr.' AS Year,(SELECT SUM(Net) FROM acctg_6targetscores WHERE BranchNo=9999);'; //echo $sql;
$stmt=$link->query($sql); $res=$stmt->fetchall();

$label=''; $dataset2='';
foreach($res as $field){	
	$label.=$field['Year'].',';
	$dataset2.=number_format(($field['TotalSales']/1000000),2).',';
}
$label=substr($label, 0, -1);
$dataset2=substr($dataset2, 0, -1);


$sql0='CREATE TEMPORARY TABLE `graphreporthistincus` (
  `ReportID` tinyint(4) NOT NULL AUTO_INCREMENT,
  `ReportTitle` varchar(100) DEFAULT NULL,
  `OtherDesc` varchar(20) NOT NULL,
  `OnSB` tinyint(1) NOT NULL,
  `Label` varchar(3000) NOT NULL,
  `xaxis` varchar(25) NOT NULL,
  `yaxis` varchar(25) NOT NULL,
  `legend1` varchar(25) NOT NULL,
  `legend2` varchar(25) NOT NULL,
  `legend3` varchar(25) NOT NULL,
  `legend4` varchar(25) NOT NULL,
  `legend5` varchar(25) NOT NULL,
  `legend6` varchar(25) NOT NULL,
  `legend7` varchar(25) NOT NULL,
  `legend8` varchar(25) NOT NULL,
  `legend9` varchar(25) NOT NULL,
  `legend10` varchar(25) NOT NULL,
  `EncodedByNo` smallint(6) DEFAULT NULL,
  `Timestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;';
$stmt=$link->prepare($sql0);$stmt->execute();

//Sales per year
$sql1='INSERT INTO `graphreporthistincus` SET
  `ReportID`=1,
  `ReportTitle`="Sales History (Up To Current Month)",
  `OtherDesc`="",
  `legend1`="Sales",
  `Label`="'.$label.'",
  `xaxis`="Years",
  `yaxis`="In Millions",
  `OnSB`=0;';
  $stmt=$link->prepare($sql1);$stmt->execute();


$sql0='CREATE TEMPORARY TABLE `graphboardhistincus` (
  `TxnID` smallint(6) NOT NULL AUTO_INCREMENT,
  `IDNo` smallint(6) DEFAULT NULL,
  `AllowedToView` varchar(100) DEFAULT NULL,
  `GraphID` tinyint(2) NOT NULL,
	`DataSet2` varchar(2100) DEFAULT NULL,
	`DataSet1` varchar(2100) DEFAULT NULL,
	`DataSet3` varchar(2100) DEFAULT NULL,
	`DataSet4` varchar(2100) DEFAULT NULL,
	`DataSet5` varchar(2100) DEFAULT NULL,
	`DataSet6` varchar(2100) DEFAULT NULL,
	`DataSet7` varchar(2100) DEFAULT NULL,
	`DataSet8` varchar(2100) DEFAULT NULL,
	`DataSet9` varchar(2100) DEFAULT NULL,
	`DataSet10` varchar(2100) DEFAULT NULL,
  `ReportID` tinyint(4) NOT NULL,
  `EncodedByNo` smallint(6) DEFAULT NULL,
  `Timestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`TxnID`),
  UNIQUE KEY `IDNo` (`IDNo`,`GraphID`,`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;';
$stmt=$link->prepare($sql0);$stmt->execute();

$sql1='INSERT INTO `graphboardhistincus` SET `AllowedToView`="100,99",
  `GraphID`=2,
	`DataSet1`="'.$dataset2.'",
  `ReportID`=1;
';
$stmt=$link->prepare($sql1);$stmt->execute();
//End


//Last Year target,this year, sales current
$label="'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'";
$sql1='INSERT INTO `graphreporthistincus` SET
  `ReportID`=2,
  `ReportTitle`="Current Sales, Current Yr Target, Sales Last Yr",
  `OtherDesc`="",
  `legend3`="Sales Last Yr",
  `legend2`="Current Yr Target",
  `legend1`="Current Sales",
  `Label`="'.$label.'",
  `xaxis`="Month",
  `yaxis`="In Millions",
  `OnSB`=0;';
  $stmt=$link->prepare($sql1);$stmt->execute();
 


$bwidth='45%';$lwidth='45%';
$mn=12; $ms=1; $sqllastsales='';  $sqlsalestarget=''; 
while($ms<=$mn){
	$sqllastsales.="SUM(`".$ms."`) AS `".$ms."`,";
	$sqlsalestarget.="SUM(`".(strlen($ms)==1?'0'.$ms:$ms)."`) AS `".$ms."`,";
	$ms++;
}
$sqllastsales=substr($sqllastsales, 0, -1);
$sqlsalestarget=substr($sqlsalestarget, 0, -1);


//Sales Last Year
$sql='SELECT '.$sqllastsales.' FROM hist_incus.totalsalesperyr WHERE Year='.($currentyr-1).''; //echo $sql;
$stmt=$link->query($sql); $res=$stmt->fetch();

$mn=12; $ms=1; $lastyrsale='';
while($ms<=$mn){
	$lastyrsale.=number_format(($res[$ms]/1000000),2).",";
	$ms++;
}
$lastyrsale=substr($lastyrsale, 0, -1);
//End

//This Year Target
$sql='SELECT '.$sqlsalestarget.' FROM acctg_1yearsalestargets;'; //echo $sql;
$stmt=$link->query($sql); $res=$stmt->fetch();

$mn=12; $ms=1; $thisyrtarget='';
while($ms<=$mn){
	$thisyrtarget.=number_format(($res[$ms]/1000000),2).",";
	$ms++;
}
$thisyrtarget=substr($thisyrtarget, 0, -1);

//ENd


//Sales Current
$mn=12; $ms=1; $salescurrent='';
while($ms<=$mn){
	$sql='SELECT TRUNCATE((Net/1000000),0) AS MonthNet FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo='.$ms.';';
	$stmt=$link->query($sql); $res=$stmt->fetch();

	if($res['MonthNet']==''){
		$res['MonthNet']=0;
	}
	
	$salescurrent.=$res['MonthNet'].",";
	$ms++;
}
$salescurrent=substr($salescurrent, 0, -1);
//End

$sql1='INSERT INTO `graphboardhistincus` SET `AllowedToView`="100,99,31",
  `GraphID`=2,
	`DataSet3`="'.$lastyrsale.'",
	`DataSet2`="'.$thisyrtarget.'",
	`DataSet1`="'.$salescurrent.'",
  `ReportID`=2;
';
$stmt=$link->prepare($sql1);$stmt->execute();

//end


$yrfrom=2014; $yrto=$lastyr;
$datasetval=''; $labelval=''; $condival=''; $sql2=''; 
while ($yrfrom<=$yrto){
	
	$sql2.='SELECT '.$yrfrom.' as year,CONCAT(TRUNCATE(`1`/1000000,2),",",TRUNCATE(`2`/1000000,2),",",TRUNCATE(`3`/1000000,2),",",TRUNCATE(`4`/1000000,2),",",TRUNCATE(`5`/1000000,2),",",TRUNCATE(`6`/1000000,2),",",TRUNCATE(`7`/1000000,2),",",TRUNCATE(`8`/1000000,2),",",TRUNCATE(`9`/1000000,2),",",TRUNCATE(`10`/1000000,2),",",TRUNCATE(`11`/1000000,2),",",TRUNCATE(`12`/1000000,2)) AS Total FROM `hist_incus`.`totalsalesperyr` WHERE Year='.$yrfrom.' UNION ';
		
	$yrfrom=$yrfrom+1;
}

$sql2=substr($sql2, 0, -6);

$sql2.=' UNION SELECT '.$currentyr.' AS Year,CONCAT(IFNULL((SELECT TRUNCATE((SUM(Net)/1000000),2) FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo=1),0.00),",",IFNULL((SELECT TRUNCATE((SUM(Net)/1000000),2) FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo=2),0.00),",",IFNULL((SELECT TRUNCATE((SUM(Net)/1000000),2) FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo=3),0.00),",",IFNULL((SELECT TRUNCATE((SUM(Net)/1000000),2) FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo=4),0.00),",",IFNULL((SELECT TRUNCATE((SUM(Net)/1000000),2) FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo=5),0.00),",",IFNULL((SELECT TRUNCATE((SUM(Net)/1000000),2) FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo=6),0.00),",",IFNULL((SELECT TRUNCATE((SUM(Net)/1000000),2) FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo=7),0.00),",",IFNULL((SELECT TRUNCATE((SUM(Net)/1000000),2) FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo=8),0.00),",",IFNULL((SELECT TRUNCATE((SUM(Net)/1000000),2) FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo=9),0.00),",",IFNULL((SELECT TRUNCATE((SUM(Net)/1000000),2) FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo=10),0.00),",",IFNULL((SELECT TRUNCATE((SUM(Net)/1000000),2) FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo=11),0.00),",",IFNULL((SELECT TRUNCATE((SUM(Net)/1000000),2) FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo=12),0.00)) AS Total';
$stmt=$link->query($sql2); $row2=$stmt->fetchAll();

$legend='';
$cnt=3; $datasets='';

foreach($row2 as $rowval){
	
	$datasets.='DataSet'.$cnt.'="'.$rowval['Total'].'",';
	$legend.='legend'.$cnt.'='.$rowval['year'].',';

	$cnt++;
}
// echo $datasets; exit();

$sql1='INSERT INTO `graphreporthistincus` SET
  `ReportID`=3,
  `ReportTitle`="Total Sales",
  `OtherDesc`="",
  '.$legend.'
  `Label`="'.$label.'",
  `xaxis`="",
  `yaxis`="In Millions",
  `OnSB`=0;';
 
  $stmt=$link->prepare($sql1);$stmt->execute();
  
  $sql1='INSERT INTO `graphboardhistincus` SET `AllowedToView`="100,99,31",
  `GraphID`=2,
	'.$datasets.'
  `ReportID`=3;
';
$stmt=$link->prepare($sql1);$stmt->execute();


$c=1;
// $sql = 'SELECT g.*,"" AS addlabel,"" AS FullName, gr.* FROM graphboardhistincus g JOIN graphreporthistincus gr ON g.ReportID=gr.ReportID';
$sql = 'SELECT g.*,"" AS addlabel,"" AS FullName, gr.* FROM graphboardhistincus g JOIN graphreporthistincus gr ON g.ReportID=gr.ReportID';
// echo $sql;
$stmt=$link->query($sql); $res=$stmt->fetchall();

break;

case 'BranchComparisons':
echo '<title>Comparison of Branch Sales</title>';
$bwidth='100%';

if (isset($_POST['btnSubmit'])){
	$date=$_POST['MonthNo'];
} else {
	$date=date('m');
}


$sql='SELECT TeamLeader AS IDNo,CONCAT(Fullname," (",Position,")") AS NameAndPos,JobLevelID FROM attend_1branchgroups bg JOIN attend_30currentpositions cp ON bg.TeamLeader=cp.IDNo GROUP BY TeamLeader UNION SELECT SAM,CONCAT(Fullname," (",Position,")") AS NameAndPos,JobLevelID FROM attend_1branchgroups bg JOIN attend_30currentpositions cp ON bg.SAM=cp.IDNo GROUP BY TeamLeader ORDER BY JobLevelID DESC';
$stmt=$link->query($sql); $res=$stmt->fetchall();

$nameandpos='';
foreach ($res as $field){
	$nameandpos.='<option value="'.$field['IDNo'].'">'.$field['NameAndPos'].'</option>';
}

echo '<form method="POST" action="allreports.php?w=BranchComparisons">MonthNo: <input type="number" name="MonthNo" style="width:50px;" max=12 value="'.$date.'"> '.(((allowedToOpen(7136,'1rtc')) OR (allowedToOpen(7137,'1rtc')))?'SAM/STL: <select name="SAMSTL"><option value="All">All</option>'.$nameandpos.'</select> ':'').'<input type="submit" name="btnSubmit" value="Lookup"></form><br>';

if (allowedToOpen(7135,'1rtc')){
	$condi='AND TeamLeader='.$_SESSION['(ak0)'].'';
	$bwidth='50%';
	
}
if (allowedToOpen(7134,'1rtc')){
	$bwidth='100%';
	
}
if(isset($_POST['SAMSTL'])){
	if($_POST['SAMSTL']<>'All'){
		$condi.=' AND (TeamLeader='.$_POST['SAMSTL'].' OR SAM='.$_POST['SAMSTL'].')';
		$bwidth='50%';
	}
}


$datename = date("F", mktime(0, 0, 0, $date, 10));

echo '<h3>'.$datename.' sales of '.(((isset($_POST['SAMSTL'])) AND ($_POST['SAMSTL']<>'All'))?'branches assigned to '.comboBoxValue($link,'attend_30currentpositions','IDNo',$_POST['SAMSTL'],'FullName').'':'All Branches').'</h3>';
$sql='SELECT Branch, TRUNCATE((Net/1000000),2) AS InMillions,TRUNCATE((`'.(strlen($date)==1?'0'.$date:$date).'`/1000000),2) AS Target,Anniversary FROM acctg_6targetscores ts JOIN 1branches b ON ts.BranchNo=b.BranchNo JOIN attend_1branchgroups bg ON ts.BranchNo=bg.BranchNo LEFT JOIN 1_gamit.0idinfo id ON bg.TeamLeader=id.IDNo JOIN acctg_1yearsalestargets yst ON ts.BranchNo=yst.BranchNo WHERE MonthNo='.$date.' '.$condi.' ORDER BY Branch;';

$stmt=$link->query($sql); $res=$stmt->fetchall();

$dataset=''; 
$cnt=1;

$label=''; $dataset1=''; $dataset2=''; $flset1='';$flset2='';$flset3='';$flset4='';
foreach ($res as $field){
	$date1 = strtotime($field['Anniversary']);  
	$date2 = strtotime(date('Y-m-d')); 
	
	
	$diff = abs($date2 - $date1); 
	$years = floor($diff / (365*60*60*24));  


	$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24)); 

	
	$label.="'".$field['Branch']." (".$years."y,".$months."m)'".',';
	
	$dataset1.=$field['InMillions'].',';
	$dataset2.=$field['Target'].',';
	$flset1.='.8,';
	$flset2.='1.3,';
	$flset4.='5,';
	$flset3.='4,';
	
	$cnt++;
}

$label=substr($label, 0, -1);
$flset1=substr($flset1, 0, -1);
$flset2=substr($flset2, 0, -1);
$flset4=substr($flset4, 0, -1);
$flset3=substr($flset3, 0, -1);
$dataset1=substr($dataset1, 0, -1);
$dataset2=substr($dataset2, 0, -1);


$sql0='CREATE TEMPORARY TABLE `graphreport2` (
  `ReportID` tinyint(4) NOT NULL AUTO_INCREMENT,
  `ReportTitle` varchar(100) DEFAULT NULL,
  `OtherDesc` varchar(20) NOT NULL,
  `OnSB` tinyint(1) NOT NULL,
  `Label` varchar(3000) NOT NULL,
  `xaxis` varchar(25) NOT NULL,
  `yaxis` varchar(25) NOT NULL,
  `legend1` varchar(25) NOT NULL,
  `legend2` varchar(25) NOT NULL,
  `fllegend1` varchar(25) NOT NULL,
  `fllegend2` varchar(25) NOT NULL,
  `fllegend4` varchar(25) NOT NULL,
  `fllegend3` varchar(25) NOT NULL,
  `EncodedByNo` smallint(6) DEFAULT NULL,
  `Timestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;';
$stmt=$link->prepare($sql0);$stmt->execute();


$sql1='INSERT INTO `graphreport2` SET
  `ReportID`=1,
  `ReportTitle`="Branch All",
  `OtherDesc`="",
  `Label`="'.$label.'",
  `legend1`="Sale",
  `legend2`="Target",
  `fllegend1`="Seed (<.8m)",
  `fllegend2`="Growth (>=.8m and <1.3m)",
  `fllegend4`="Prime (>=1.3m and <4m)",
  `fllegend3`="Mature (>4m)",
  `xaxis`="Branches",
  `yaxis`="InMillions",
  `OnSB`=0;';
  $stmt=$link->prepare($sql1);$stmt->execute();

$sql0='CREATE TEMPORARY TABLE `graphboard2` (
  `TxnID` smallint(6) NOT NULL AUTO_INCREMENT,
  `IDNo` smallint(6) DEFAULT NULL,
  `AllowedToView` varchar(100) DEFAULT NULL,
  `GraphID` tinyint(2) NOT NULL,
	`DataSet2` varchar(2100) DEFAULT NULL,
	`DataSet1` varchar(2100) DEFAULT NULL,
	`FilledLine1` varchar(2100) DEFAULT NULL,
	`FilledLine2` varchar(2100) DEFAULT NULL,
	`FilledLine4` varchar(2100) DEFAULT NULL,
	`FilledLine3` varchar(2100) DEFAULT NULL,
  `ReportID` tinyint(4) NOT NULL,
  `EncodedByNo` smallint(6) DEFAULT NULL,
  `Timestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`TxnID`),
  UNIQUE KEY `IDNo` (`IDNo`,`GraphID`,`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;'; //echo $sql0;
$stmt=$link->prepare($sql0);$stmt->execute();

$sql1='INSERT INTO `graphboard2` SET `AllowedToView`="100,99",
  `GraphID`=1,
	`DataSet1`="'.$dataset1.'",
	`DataSet2`="'.$dataset2.'",
	`FilledLine1`="'.$flset1.'",
	`FilledLine2`="'.$flset2.'",
	`FilledLine4`="'.$flset3.'",
	`FilledLine3`="'.$flset4.'",
  `ReportID`=1;
'; 
$stmt=$link->prepare($sql1);$stmt->execute();

$c=1;


$sql = 'SELECT g.*,"" AS addlabel, gr.*,CONCAT(NickName," ",Surname) AS FullName FROM graphboard2 g LEFT JOIN 1_gamit.0idinfo id ON g.IDNo=id.IDNo JOIN graphreport2 gr ON g.ReportID=gr.ReportID WHERE g.ReportID IN (1)';
$stmt=$link->query($sql); $res=$stmt->fetchall();

$displaydiv=''; $newdiv=''; $newentry=''; $last='';

break;



case 'NumberOfTransactions':
if ((!allowedToOpen(7137,'1rtc')) AND (!allowedToOpen(7136,'1rtc')) AND (!allowedToOpen(7138,'1rtc'))){ echo 'No Permission'; exit(); }
$title='Number of Transactions';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3><br>';
$bwidth='100%';

echo '<form action="allreports.php?w=NumberOfTransactions" method="POST">MonthNo: <input type="text" name="MonthNo" size="10" value="'.(isset($_POST['MonthNo'])?$_POST['MonthNo']:date('m')).'"> <input type="submit" name="btnSubmit" value="Lookup"></form><br>';
if (isset($_POST['btnSubmit'])){
	$txndate=$_POST['MonthNo'];
} else {
	$txndate=date('m');
}
$sql0='CREATE TEMPORARY TABLE tempTxn AS SELECT b.Branch,COUNT(s.TxnID) AS TotalTxn,IFNULL(TRUNCATE(((SELECT SUM(Qty*UnitPrice) FROM invty_2salesub ss2 JOIN invty_2sale s2 ON ss2.TxnID=s2.TxnID WHERE s2.txntype IN (1,2) AND s2.BranchNo=s.BranchNo AND MONTH(s2.`Date`)='.$txndate.')/350000),2),0) AS TotalSales FROM invty_2sale s JOIN 1branches b ON s.BranchNo=b.BranchNo WHERE txntype IN (1,2) AND MONTH(`Date`)='.$txndate.' GROUP BY s.BranchNo ORDER BY Branch;';
$stmt=$link->prepare($sql0);$stmt->execute();

$sql='SELECT 1 AS GraphID,"" AS IDNo,"No of Transactions" AS ReportTitle,"Total Sales" AS lineonly,"No of Transactions" AS legend1,"" AS FullName,"" AS xaxis,"No. of Transactions" AS yaxis,GROUP_CONCAT(TotalSales) AS LineOnly,GROUP_CONCAT("\'",Branch,"\'") AS Label,GROUP_CONCAT(TotalTxn) AS DataSet1 FROM tempTxn;';
$stmt=$link->query($sql); $res=$stmt->fetchall();
$yaxis1st='Transactions'; $yaxis2nd='In Units';
echo '<h3>Month of '.date("F", mktime(0, 0, 0, $txndate, 10)).'</h3>';
break;


case 'TransactionsPerBranch':

$title='Number Of Transactions Per Branch';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3><br>';


	$sql='SELECT bg.BranchNo,TeamLeader FROM attend_1branchgroups bg '.$acondi.'';
	
	$stmt=$link->query($sql); $res=$stmt->fetchall();
	$lwidth='45%';
	
	
	foreach($res as $field){	
		
		$dataset1='';
		for ($i = 1; $i <= date('m'); $i++) {
			
			$sql0='SELECT COUNT(TxnID) as NoOfTxns FROM invty_2sale WHERE txntype IN (1,2) AND BranchNo='.$field['BranchNo'].' AND MONTH(`Date`)='.$i.';';
			$stmt=$link->query($sql0); $res=$stmt->fetch();
			
			if($stmt->rowCount()==0){
					$dataset1.='0,';
			} else {
					$dataset1.=$res['NoOfTxns'].',';
			}
		}
		
			$dataset1=substr($dataset1, 0, -1);
			
		 $sqlcsub='INSERT INTO tempdata SET Data="'.$dataset1.'",TeamLeader='.$field['TeamLeader'].',BranchNo='.$field['BranchNo'].';'; 
		
		$stmt=$link->prepare($sqlcsub); $stmt->execute();
	}
	
	
		$sql='SELECT DISTINCT(bg.TeamLeader) FROM attend_1branchgroups bg '.$acondi.'';
		$stmt=$link->query($sql); $resmain=$stmt->fetchall();
		$sql='';
		foreach($resmain as $fieldmain){
				$sql2='SELECT `Data`,Branch FROM tempdata td JOIN 1branches b ON td.BranchNo=b.BranchNo WHERE TeamLeader='.$fieldmain['TeamLeader'].' ORDER BY Branch;';
				$stmt=$link->query($sql2); $res=$stmt->fetchall();
				$a=1;
			
				foreach($res as $field){
					
					if (isset($field['Data'])){
						if ($a==1){
							$dataset1=$field['Data'];
							$legend1=$field['Branch'];
						} else if ($a==2) {
							$dataset2=$field['Data'];
							$legend2=$field['Branch'];
						} else if ($a==3) {
							$dataset3=$field['Data'];
							$legend3=$field['Branch'];
						} else if ($a==4) {
							$dataset4=$field['Data'];
							$legend4=$field['Branch'];
						} else if ($a==5) {
							$dataset5=$field['Data'];
							$legend5=$field['Branch'];
						} else if ($a==6) {
							$dataset6=$field['Data'];
							$legend6=$field['Branch'];
						} else if ($a==7) {
							$dataset7=$field['Data'];
							$legend7=$field['Branch'];
						} else if ($a==8) {
							$dataset8=$field['Data'];
							$legend8=$field['Branch'];
						} else if ($a==9) {
							$dataset9=$field['Data'];
							$legend9=$field['Branch'];
						} else if ($a==10) {
							$dataset10=$field['Data'];
							$legend10=$field['Branch'];
						} else if ($a==11) {
							$dataset11=$field['Data'];
							$legend11=$field['Branch'];
						} else if ($a==12) {
							$dataset12=$field['Data'];
							$legend12=$field['Branch'];
						} else if ($a==13) {
							$dataset13=$field['Data'];
							$legend13=$field['Branch'];
						} else if ($a==14) {
							$dataset14=$field['Data'];
							$legend14=$field['Branch'];
						} else if ($a==15) {
							$dataset15=$field['Data'];
							$legend15=$field['Branch'];
						} else if ($a==16) {
							$dataset16=$field['Data'];
							$legend16=$field['Branch'];
						} else if ($a==17) {
							$dataset17=$field['Data'];
							$legend17=$field['Branch'];
						} else if ($a==18) {
							$dataset18=$field['Data'];
							$legend18=$field['Branch'];
						} else if ($a==19) {
							$dataset19=$field['Data'];
							$legend19=$field['Branch'];
						} else if ($a==20) {
							$dataset20=$field['Data'];
							$legend20=$field['Branch'];
						} else if ($a==21) {
							$dataset21=$field['Data'];
							$legend21=$field['Branch'];
						} else if ($a==22) {
							$dataset22=$field['Data'];
							$legend22=$field['Branch'];
						} else if ($a==23) {
							$dataset23=$field['Data'];
							$legend23=$field['Branch'];
						} else if ($a==24) {
							$dataset24=$field['Data'];
							$legend24=$field['Branch'];
						}
					}
					$a++;
				}
				
				$addsqldata=''; $addsqllegend='';
				if (isset($dataset1)){
					$addsqldata.='"'.$dataset1.'" AS DataSet1,';
					$addsqllegend.='"'.$legend1.'" AS legend1,';
				} else {
					$addsqldata.='"" AS DataSet1,';
					$addsqllegend.='"" AS legend1,';
				}
				if (isset($dataset2)){
					$addsqldata.='"'.$dataset2.'" AS DataSet2,';
					$addsqllegend.='"'.$legend2.'" AS legend2,';
				} else {
					$addsqldata.='"" AS DataSet2,';
					$addsqllegend.='"" AS legend2,';
				}
				if (isset($dataset3)){
					$addsqldata.='"'.$dataset3.'" AS DataSet3,';
					$addsqllegend.='"'.$legend3.'" AS legend3,';
				} else {
					$addsqldata.='"" AS DataSet3,';
					$addsqllegend.='"" AS legend3,';
				}
				if (isset($dataset4)){
					$addsqldata.='"'.$dataset4.'" AS DataSet4,';
					$addsqllegend.='"'.$legend4.'" AS legend4,';
				} else {
					$addsqldata.='"" AS DataSet4,';
					$addsqllegend.='"" AS legend4,';
				}
				if (isset($dataset5)){
					$addsqldata.='"'.$dataset5.'" AS DataSet5,';
					$addsqllegend.='"'.$legend5.'" AS legend5,';
				} else {
					$addsqldata.='"" AS DataSet5,';
					$addsqllegend.='"" AS legend5,';
				}
				if (isset($dataset6)){
					$addsqldata.='"'.$dataset6.'" AS DataSet6,';
					$addsqllegend.='"'.$legend6.'" AS legend6,';
				} else {
					$addsqldata.='"" AS DataSet6,';
					$addsqllegend.='"" AS legend6,';
				}
				if (isset($dataset7)){
					$addsqldata.='"'.$dataset7.'" AS DataSet7,';
					$addsqllegend.='"'.$legend7.'" AS legend7,';
				} else {
					$addsqldata.='"" AS DataSet7,';
					$addsqllegend.='"" AS legend7,';
				}
				if (isset($dataset8)){
					$addsqldata.='"'.$dataset8.'" AS DataSet8,';
					$addsqllegend.='"'.$legend8.'" AS legend8,';
				} else {
					$addsqldata.='"" AS DataSet8,';
					$addsqllegend.='"" AS legend8,';
				}
				if (isset($dataset9)){
					$addsqldata.='"'.$dataset9.'" AS DataSet9,';
					$addsqllegend.='"'.$legend9.'" AS legend9,';
				} else {
					$addsqldata.='"" AS DataSet9,';
					$addsqllegend.='"" AS legend9,';
				}
				if (isset($dataset10)){
					$addsqldata.='"'.$dataset10.'" AS DataSet10,';
					$addsqllegend.='"'.$legend10.'" AS legend10,';
				} else {
					$addsqldata.='"" AS DataSet10,';
					$addsqllegend.='"" AS legend10,';
				}
				if (isset($dataset11)){
					$addsqldata.='"'.$dataset11.'" AS DataSet11,';
					$addsqllegend.='"'.$legend11.'" AS legend11,';
				} else {
					$addsqldata.='"" AS DataSet11,';
					$addsqllegend.='"" AS legend11,';
				}
				if (isset($dataset12)){
					$addsqldata.='"'.$dataset12.'" AS DataSet12,';
					$addsqllegend.='"'.$legend12.'" AS legend12,';
				} else {
					$addsqldata.='"" AS DataSet12,';
					$addsqllegend.='"" AS legend12,';
				}
				if (isset($dataset13)){
					$addsqldata.='"'.$dataset13.'" AS DataSet13,';
					$addsqllegend.='"'.$legend13.'" AS legend13,';
				} else {
					$addsqldata.='"" AS DataSet13,';
					$addsqllegend.='"" AS legend13,';
				}
				if (isset($dataset14)){
					$addsqldata.='"'.$dataset14.'" AS DataSet14,';
					$addsqllegend.='"'.$legend14.'" AS legend14,';
				} else {
					$addsqldata.='"" AS DataSet14,';
					$addsqllegend.='"" AS legend14,';
				}
				if (isset($dataset15)){
					$addsqldata.='"'.$dataset15.'" AS DataSet15,';
					$addsqllegend.='"'.$legend15.'" AS legend15,';
				} else {
					$addsqldata.='"" AS DataSet15,';
					$addsqllegend.='"" AS legend15,';
				}
				if (isset($dataset16)){
					$addsqldata.='"'.$dataset16.'" AS DataSet16,';
					$addsqllegend.='"'.$legend16.'" AS legend16,';
				} else {
					$addsqldata.='"" AS DataSet16,';
					$addsqllegend.='"" AS legend16,';
				}
				if (isset($dataset17)){
					$addsqldata.='"'.$dataset17.'" AS DataSet17,';
					$addsqllegend.='"'.$legend17.'" AS legend17,';
				} else {
					$addsqldata.='"" AS DataSet17,';
					$addsqllegend.='"" AS legend17,';
				}
				if (isset($dataset18)){
					$addsqldata.='"'.$dataset18.'" AS DataSet18,';
					$addsqllegend.='"'.$legend18.'" AS legend18,';
				} else {
					$addsqldata.='"" AS DataSet18,';
					$addsqllegend.='"" AS legend18,';
				}
				if (isset($dataset19)){
					$addsqldata.='"'.$dataset19.'" AS DataSet19,';
					$addsqllegend.='"'.$legend19.'" AS legend19,';
				} else {
					$addsqldata.='"" AS DataSet19,';
					$addsqllegend.='"" AS legend19,';
				}
				if (isset($dataset20)){
					$addsqldata.='"'.$dataset20.'" AS DataSet20,';
					$addsqllegend.='"'.$legend20.'" AS legend20,';
				} else {
					$addsqldata.='"" AS DataSet20,';
					$addsqllegend.='"" AS legend20,';
				}
				if (isset($dataset21)){
					$addsqldata.='"'.$dataset21.'" AS DataSet21,';
					$addsqllegend.='"'.$legend21.'" AS legend21,';
				} else {
					$addsqldata.='"" AS DataSet21,';
					$addsqllegend.='"" AS legend21,';
				}
				if (isset($dataset22)){
					$addsqldata.='"'.$dataset22.'" AS DataSet22,';
					$addsqllegend.='"'.$legend22.'" AS legend22,';
				} else {
					$addsqldata.='"" AS DataSet22,';
					$addsqllegend.='"" AS legend22,';
				}
				if (isset($dataset23)){
					$addsqldata.='"'.$dataset23.'" AS DataSet23,';
					$addsqllegend.='"'.$legend23.'" AS legend23,';
				} else {
					$addsqldata.='"" AS DataSet23,';
					$addsqllegend.='"" AS legend23,';
				}
				if (isset($dataset24)){
					$addsqldata.='"'.$dataset24.'" AS DataSet24,';
					$addsqllegend.='"'.$legend24.'" AS legend24,';
				} else {
					$addsqldata.='"" AS DataSet24,';
					$addsqllegend.='"" AS legend24,';
				}
				$addsqldata=substr($addsqldata, 0, -1);
				$addsqllegend=substr($addsqllegend, 0, -1);
				
				unset($dataset1,$dataset2,$dataset3,$dataset4,$dataset5,$dataset6,$dataset7,$dataset8,$dataset9,$dataset10,$dataset11,$dataset12,$dataset13,$dataset14,$dataset15,$dataset16,$dataset17,$dataset18,$dataset19,$dataset20,$dataset21,$dataset22,$dataset23,$dataset24);
				
				$stl=comboBoxValue($link,'1employees','IDNo',$fieldmain['TeamLeader'],'Nickname');
				$sql.='SELECT '.$addsqldata.' , '.$addsqllegend.',"Branches of '.$stl.'" AS ReportTitle,"" AS IDNo,2 AS GraphID, "" AS xaxis, "" AS yaxis,"" AS FullName,"'.$inclabel.'" AS Label';
				
				$sql.=' UNION ';
				
		}
	$sql=substr($sql, 0, -6);
	
	$stmt=$link->query($sql); $res=$stmt->fetchall();

break;

case 'NoOfSales':

$title='Number Of Clients with Sales';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3><br>';
// if ((!allowedToOpen(7137,'1rtc')) AND (!allowedToOpen(7136,'1rtc'))){ echo 'No Permission'; exit(); }


	$sql='SELECT bg.BranchNo,TeamLeader FROM attend_1branchgroups bg '.$acondi.'';
	
	$stmt=$link->query($sql); $res=$stmt->fetchall();
	$lwidth='31%';
	
	
	
	foreach($res as $field){
		$dataset1='';
		$dataset2='';
		for ($i = 1; $i <= date('m'); $i++) {
			$sql0='SELECT COUNT(DISTINCT(s.ClientNo)) AS NoOfSales,(SELECT COUNT(bcj.ClientNo) AS NoOfClients FROM gen_info_1branchesclientsjxn bcj JOIN 1clients c ON bcj.ClientNo=c.ClientNo WHERE BranchNo='.$field['BranchNo'].' AND bcj.ClientNo NOT IN (10000,10001,10004) AND IF((Year(`ClientSince`)<YEAR(CURDATE())) OR Year(`ClientSince`)=YEAR(CURDATE()) AND MONTH(`ClientSince`)<='.$i.',1,0)=1) AS NoOfClients FROM invty_2sale s WHERE MONTH(s.`Date`)='.$i.' AND s.BranchNo='.$field['BranchNo'].' AND s.ClientNo NOT IN (10001,10000,10004) AND s.txntype IN (1,2);';
			$stmt=$link->query($sql0); $res=$stmt->fetch();
			
			if($stmt->rowCount()==0){
					$dataset1.='0,';
					$dataset2.='0,';
			} else {
					$dataset1.=$res['NoOfSales'].',';
					$dataset2.=$res['NoOfClients'].',';
			}
		}
		
			$dataset1=substr($dataset1, 0, -1);
			$dataset2=substr($dataset2, 0, -1);
			
		 $sqlcsub='INSERT INTO tempdata SET Data="'.$dataset1.'",Data2="'.$dataset2.'",TeamLeader='.$field['TeamLeader'].',BranchNo='.$field['BranchNo'].';'; 
		// echo $sqlcsub;
		$stmt=$link->prepare($sqlcsub); $stmt->execute();
	}
	
		
		// $sql='';
		// $sql='SELECT Data AS FilledLine1, "WithSales" AS fllegend1, "NoOfClients" AS fllegend2,Data2 AS FilledLine2,CONCAT(Branch," (",Nickname,")") AS ReportTitle,"" AS IDNo,2 AS GraphID, "" AS xaxis, "" AS yaxis,"" AS FullName,"'.$inclabel.'" AS Label FROM tempdata td JOIN 1branches b ON td.BranchNo=b.BranchNo JOIN 1employees e ON td.TeamLeader=e.IDNo '.$acondi.' ORDER BY Branch';
		// $sql='SELECT Data AS FilledLine1, "WithSales" AS fllegend1, "NoOfClients" AS fllegend2,Data2 AS FilledLine2,CONCAT(Branch," (",Nickname,")") AS ReportTitle,"" AS IDNo,2 AS GraphID, "" AS xaxis, "" AS yaxis,"" AS FullName,"'.$inclabel.'" AS Label FROM tempdata td JOIN 1branches b ON td.BranchNo=b.BranchNo JOIN 1employees e ON td.TeamLeader=e.IDNo ORDER BY Branch';
		// $sql='SELECT Data AS FilledLine1, "WithSales" AS fllegend1, "NoOfClients" AS fllegend2,Data2 AS FilledLine2,CONCAT(Branch," (",Nickname,")") AS ReportTitle,"" AS IDNo,2 AS GraphID, "" AS xaxis, "" AS yaxis,"" AS FullName,"'.$inclabel.'" AS Label,IF((SELECT COUNT(bcj.ClientNo) AS NoOfClients FROM gen_info_1branchesclientsjxn bcj JOIN 1clients c ON bcj.ClientNo=c.ClientNo WHERE BranchNo=td.BranchNo AND bcj.ClientNo NOT IN (10000,10001,10004))>250,(SELECT COUNT(bcj.ClientNo) AS NoOfClients FROM gen_info_1branchesclientsjxn bcj JOIN 1clients c ON bcj.ClientNo=c.ClientNo WHERE BranchNo=td.BranchNo AND bcj.ClientNo NOT IN (10000,10001,10004)),250) AS max FROM tempdata td JOIN 1branches b ON td.BranchNo=b.BranchNo JOIN 1employees e ON td.TeamLeader=e.IDNo ORDER BY Branch';
		$sql='SELECT Data AS FilledLine1, "WithSales" AS fllegend1, "NoOfClients" AS fllegend2,Data2 AS FilledLine2,CONCAT(Branch," (",Nickname,")") AS ReportTitle,"" AS IDNo,2 AS GraphID, "" AS xaxis, "" AS yaxis,"" AS FullName,"'.$inclabel.'" AS Label,(SELECT IF(COUNT(bcj.ClientNo)>250,COUNT(bcj.ClientNo),250) AS NoOfClients FROM gen_info_1branchesclientsjxn bcj JOIN 1clients c ON bcj.ClientNo=c.ClientNo WHERE BranchNo=td.BranchNo AND bcj.ClientNo NOT IN (10000,10001,10004)) AS max FROM tempdata td JOIN 1branches b ON td.BranchNo=b.BranchNo JOIN 1employees e ON td.TeamLeader=e.IDNo ORDER BY Branch';
		// echo $sql;
	
	$stmt=$link->query($sql); $res=$stmt->fetchall();
	// $max=300;
break; 

case 'LeadsAndWins':
$title='Leads And Wins';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3><br>';
echo '<i><b>Leads</b> = Total number of quotations from visit and tel logs.</i> ';
echo '<i><b>Wins</b> = Total number of invoice from visit and tel logs.</i><br><br>';
if ((!allowedToOpen(7137,'1rtc')) AND (!allowedToOpen(7136,'1rtc'))){ echo 'No Permission'; exit(); }

$sql='SELECT DISTINCT(TeamLeader) AS STL FROM attend_1branchgroups bg '.$acondi.'';
$stmt=$link->query($sql); $res=$stmt->fetchall();

foreach($res as $field){
		$dataset1='';
		$dataset2='';
		for ($i = 1; $i <= date('m'); $i++) {
		
			$sql0='SELECT ((SELECT COUNT(InvoiceNo) FROM calllogs_2telmain tm JOIN calllogs_2telsub ts ON tm.TxnID=ts.TxnID WHERE MONTH(`Date`)='.$i.' AND tm.EncodedByNo='.$field['STL'].' AND (InvoiceNo<>"" AND InvoiceNo IS NOT NULL)) + (SELECT COUNT(InvoiceNo) FROM calllogs_2visitmain vm JOIN calllogs_2visitsub vs ON vm.TxnID=vs.TxnID WHERE MONTH(`VisitDate`)='.$i.' AND vm.EncodedByNo='.$field['STL'].' AND (InvoiceNo<>"" AND InvoiceNo IS NOT NULL))) AS NoOfInv,((SELECT COUNT(QuoteNo) FROM calllogs_2telmain tm JOIN calllogs_2telsub ts ON tm.TxnID=ts.TxnID WHERE MONTH(`Date`)='.$i.' AND tm.EncodedByNo='.$field['STL'].' AND (QuoteNo<>"" AND QuoteNo IS NOT NULL)) + (SELECT COUNT(QuoteNo) FROM calllogs_2visitmain vm JOIN calllogs_2visitsub vs ON vm.TxnID=vs.TxnID WHERE MONTH(`VisitDate`)='.$i.' AND vm.EncodedByNo='.$field['STL'].' AND (QuoteNo<>"" AND QuoteNo IS NOT NULL))) AS NoOfQuote;'; //echo $sql0.'<br>';  //exit();
			$stmt=$link->query($sql0); $res=$stmt->fetch();
			
			if($stmt->rowCount()==0){
					$dataset1.='0,';
					$dataset2.='0,';
			} else {
					$dataset1.=$res['NoOfQuote'].',';
					$dataset2.=$res['NoOfInv'].',';
			}
		}
		
			$dataset1=substr($dataset1, 0, -1);
			$dataset2=substr($dataset2, 0, -1);
			
		 $sqlcsub='INSERT INTO tempdata SET Data="'.$dataset1.'",Data2="'.$dataset2.'",TeamLeader='.$field['STL'].';'; 
		// echo $sqlcsub.'<br>';
		$stmt=$link->prepare($sqlcsub); $stmt->execute();
	}
	
	// $sql='SELECT Data AS FilledLine1, "NoOfSales" AS fllegend1, "NoOfClients" AS fllegend2,Data2 AS FilledLine2,CONCAT(Branch," (",Nickname,")") AS ReportTitle,"" AS IDNo,2 AS GraphID, "" AS xaxis, "" AS yaxis,"" AS FullName,"'.$inclabel.'" AS Label FROM tempdata td JOIN 1branches b ON td.BranchNo=b.BranchNo JOIN 1employees e ON td.TeamLeader=e.IDNo '.$acondi.' ORDER BY Branch';
	$sql='SELECT Data AS FilledLine3, "Leads" AS fllegend3, "Wins" AS fllegend4,Data2 AS FilledLine4,NickName AS ReportTitle,"" AS IDNo,2 AS GraphID, "" AS xaxis, "" AS yaxis,"" AS FullName,"'.$inclabel.'" AS Label FROM tempdata td JOIN 1employees e ON td.TeamLeader=e.IDNo '.$acondi.' ;';
			// echo $sql;
		
		$stmt=$link->query($sql); $res=$stmt->fetchall();

// exit();

break;

}


$c=1;

$displaydiv=''; $newdiv=''; $newentry=''; $last='';
foreach ($res as $field) {
	
	
	/* if (isset($_POST['OrderBy']) AND $_POST['OrderBy']=='ReportID'){
		$newentry=$field['ReportID'];
	} else {
		$newentry=$field['IDNo'];
	} */
	if ($newentry==$last){
		$newdiv='';
	} else {
		$newdiv='<div style="clear: both; display: block; position: relative;height:30px;"></div>';
	}
	
	
	if ($field['GraphID']==1){
		include($path.'/acrossyrs/js/reportcharts/vbar.php');
	} else if ($field['GraphID']==2){
		include($path.'/acrossyrs/js/reportcharts/line.php');
	} else if ($field['GraphID']==3){
		include($path.'/acrossyrs/js/reportcharts/hbar.php');
	} else {
		include($path.'/acrossyrs/js/reportcharts/pie.php');
	}
	
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

