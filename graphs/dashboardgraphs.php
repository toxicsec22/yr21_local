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

//ORDERS
/*
1. GraphTitle
2. Label
3. GraphID
4. xaxis
5. yaxis
6. legend1
7. legend2
8. legend3
9. DataSet1
10. DataSet2
11. DataSet3
12. fllegend1
13. FilledLine4
*/
$dateno=(strlen(date('m'))==1?'0'.date('m'):date('m')); //$dateno='04';
$datename = date("F", mktime(0, 0, 0, $dateno, 10));
$idno=$_SESSION['(ak0)'];

$sql='';
if (isset($_POST['SBIDNo'])){
	$idno=$_POST['SBIDNo'];
	if ($_POST['PositionToView']==36){ //STL
		goto passtostl;
	} else if ($_POST['PositionToView']==61) { //SAM
		goto passtosam;
	} else if ($_POST['PositionToView']==31) { //Sales Dept Head
		goto passtorcejyesaleshead;
	} else if ($_POST['PositionToView']==99 OR $_POST['PositionToView']==100){ //RCE//JYE
		goto passtorcejyesaleshead;
	} else {
		goto nodashboard;
	}
}
	if (allowedToOpen(7135,'1rtc')) {	//STL
		passtostl:
						
		$sql.='SELECT "Branch Target Scores" AS GraphTitle,GROUP_CONCAT("\'",Branch,"\'") AS Label, 1 AS GraphID,"'.$datename.'" AS xaxis,"In Percent (%)" AS yaxis, "Score" AS legend1, "" AS legend2,GROUP_CONCAT(TRUNCATE( IF((((100*Net)/`'.$dateno.'`)>0),((100*Net)/`'.$dateno.'`),0) ,2)) AS DataSet1,"" AS DataSet2,"" AS DataSet3,"100%" AS fllegend3,GROUP_CONCAT("100") AS FilledLine3 FROM acctg_6targetscores ts JOIN 1branches b ON ts.BranchNo=b.BranchNo JOIN attend_1branchgroups bg ON ts.BranchNo=bg.BranchNo LEFT JOIN 1_gamit.0idinfo id ON bg.TeamLeader=id.IDNo JOIN acctg_1yearsalestargets yst ON ts.BranchNo=yst.BranchNo WHERE MonthNo='.$dateno.' AND TeamLeader='.$idno.'';
		// echo $sql; exit();
		goto passed;
	}
// echo $sql;
if (allowedToOpen(7134,'1rtc')) { //SAM
	passtosam:
	$bwidth="45%"; $grid=1;
	
	//Create Temp Table
	$sql00='CREATE TEMPORARY TABLE tempTargets AS SELECT SUM(`'.$dateno.'`) AS TotalTarget,SUM(Net) AS TotalNet, TeamLeader FROM acctg_1yearsalestargets yst JOIN attend_1branchgroups bg ON yst.BranchNo=bg.BranchNo JOIN acctg_6targetscores ts ON yst.BranchNo=ts.BranchNo WHERE MonthNo='.$dateno.' AND TeamLeader IS NOT NULL GROUP BY TeamLeader;';
	$stmt00=$link->prepare($sql00); $stmt00->execute();
	
	$sql.='SELECT "Individual Scores" AS GraphTitle,GROUP_CONCAT("\'",Nickname," (",CONCAT(TRUNCATE((TotalNet/1000000),2)),"M/",CONCAT(TRUNCATE((`TotalTarget`/1000000),2)),"M)","\'") AS Label, 3 AS GraphID,"" AS xaxis,"In Percent (%)" AS yaxis, "" AS legend1, "'.$datename.'" AS legend2, "" AS DataSet1, GROUP_CONCAT(TRUNCATE( IF(((100*TotalNet)/`TotalTarget`)>0,((100*TotalNet)/`TotalTarget`),0) ,2)) AS DataSet2,"" AS DataSet3,"" AS fllegend3,"" AS FilledLine3 FROM tempTargets tts JOIN 1_gamit.0idinfo id ON tts.TeamLeader=id.IDNo WHERE tts.TeamLeader IN (SELECT DISTINCT(TeamLeader) FROM attend_1branchgroups WHERE SAM IN ('.$idno.'))';
	

	$sql.=' UNION '; 
	
	$sql.='SELECT "Branch Target Scores" AS GraphTitle,GROUP_CONCAT("\'",Branch,"\'") AS Label, 1 AS GraphID,"'.$datename.'" AS xaxis,"In Percent (%)" AS yaxis, "Score" AS legend1, "" AS legend2, GROUP_CONCAT(TRUNCATE(IF(((100*Net)/`'.$dateno.'`)>0,((100*Net)/`'.$dateno.'`),0),2)) AS DataSet1, "" AS DataSet2,"" AS DataSet3,"100%" AS fllegend3,GROUP_CONCAT("100") AS FilledLine3 FROM acctg_6targetscores ts JOIN 1branches b ON ts.BranchNo=b.BranchNo JOIN attend_1branchgroups bg ON ts.BranchNo=bg.BranchNo LEFT JOIN 1_gamit.0idinfo id ON bg.TeamLeader=id.IDNo JOIN acctg_1yearsalestargets yst ON ts.BranchNo=yst.BranchNo WHERE MonthNo='.$dateno.' AND '.$idno.' IN (SAM) ';
	
	goto passed;
}

if ((allowedToOpen(7137,'1rtc')) OR (allowedToOpen(7136,'1rtc'))) { // jye/rce/sales head

	passtorcejyesaleshead:
	
	//Sales Last Year
	$sql1='SELECT `1`,`2`,`3`,`4`,`5`,`6`,`7`,`8`,`9`,`10`,`11`,`12` FROM hist_incus.totalsalesperyr WHERE Year='.($currentyr-1).'';
	$stmt=$link->query($sql1); $res=$stmt->fetch();
	$mn=12; $ms=1; $lastyrsale='';
	while($ms<=$mn){
		$lastyrsale.=number_format(($res[$ms]/1000000),2).",";
		$ms++;
	}
	$lastyrsale=substr($lastyrsale, 0, -1); 
	//End
	
	//This Year Target
		$sql1='SELECT SUM(`01`) AS `1`,SUM(`02`) AS `2`,SUM(`03`) AS `3`,SUM(`04`) AS `4`,SUM(`05`) AS `5`,SUM(`06`) AS `6`,SUM(`07`) AS `7`,SUM(`08`) AS `8`,SUM(`09`) AS `9`,SUM(`10`) AS `10`,SUM(`11`) AS `11`,SUM(`12`) AS `12` FROM acctg_1yearsalestargets;'; //echo $sql;
		$stmt=$link->query($sql1); $res=$stmt->fetch();

		$mn=12; $ms=1; $thisyrtarget='';
		while($ms<=$mn){
			$thisyrtarget.=number_format(($res[$ms]/1000000),2).",";
			$ms++;
		}
		$thisyrtarget=substr($thisyrtarget, 0, -1); //echo $thisyrtarget; exit();
	//end
	
	//Sales Current
	$mn=12; $ms=1; $salescurrent='';
	while($ms<=$mn){
		$sql1='SELECT Net FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo='.$ms.';'; //echo $sql;
		$stmt=$link->query($sql1); $res=$stmt->fetch();
		
		
		// if($res['Net']==''){
		if($stmt->rowCount()==0 OR (isset($res['Net']) AND $res['Net']=='')){
			$res['Net']=0;
		} 
		
		$salescurrent.=number_format(($res['Net']/1000000),2).",";
		$ms++;
	}
	$salescurrent=substr($salescurrent, 0, -1); //echo $salescurrent; exit();
	//End
	if ((date('Y')-$currentyr)<>0){
		$dateno=12;
	}
	
	$label="'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'";
	$sql.='SELECT "Last Year, This Year, Current Sales (Up to Current Month Only)" AS GraphTitle,"'.$label.'" AS Label,2 AS GraphID,"Month" AS xaxis, "In Millions" AS yaxis, "Current Sales" AS legend1,"Current Yr Target" AS legend2,"Sales Last Yr" AS legend3,"'.$salescurrent.'" AS DataSet1,"'.$thisyrtarget.'" AS DataSet2,"'.$lastyrsale.'" AS DataSet3,"" AS fllegend1,"" AS FilledLine1 ';
	
	$sql.=' UNION ';
	
	//Target for the year
	$sql.='SELECT "Sales and Target" AS GraphTitle,"\'For the year\'" AS Label,3 AS GraphID,"" AS xaxis, "In Millions" AS yaxis, "YrToDate Target" AS legend1,"Sales" AS legend2,"Annual Target" AS legend3,TRUNCATE(((FixedYrTarget*(('.$dateno.'/12)))/1000000),2) AS DataSet1,TRUNCATE(((SELECT SUM(Net) FROM acctg_6targetscores WHERE BranchNo=9999 AND MonthNo<>0)/1000000),2) AS DataSet2,TRUNCATE((FixedYrTarget/1000000),2) AS DataSet3,"" AS fllegend1,"" AS FilledLine1 FROM 00dataclosedby dc WHERE ForDB=1';
	goto passed;
}
passed:
// echo $sql;
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
<?php nodashboard: ?>
