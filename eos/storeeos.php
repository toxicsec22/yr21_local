<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// error_reporting(E_ALL);
	// ini_set('display_errors', 1);
if (!allowedToOpen(1617,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once '../backendphp/layout/linkstyle.php';

$which=(!isset($_GET['w'])?'Batch':$_GET['w']);

?>
<style>
#table {
  border-collapse: collapse;
  font-size:10pt;
  padding: 5px;
  background-color:#FFFFCC;
}

#table td, #table th, #table tr {
  border: 1px solid black;
  padding: 5px;
}

#table tr:nth-child(even){background-color:#FFFFFF;}
</style>
<?php

if (in_array($which,array('Batch','EditBatch'))){
echo comboBox($link,'select Branch, BranchNo from 1branches where Active=1 and PseudoBranch=0 ORDER BY Branch','BranchNo','Branch','branches'); 
echo comboBox($link,'select e.IDNo,CONCAT(Nickname,\' \',SurName) as Fullname from 1employees e left join attend_30currentpositions cp on cp.IDNo=e.IDNo where cp.deptid=\'70\' and PositionID<>\'68\'','IDNo','Fullname','supervisors'); 
echo comboBox($link,'select e.IDNo,CONCAT(Nickname,\' \',SurName) as Fullname from 1employees e left join attend_30currentpositions cp on cp.IDNo=e.IDNo where cp.deptid=\'10\' and PositionID in (32,37,81)','IDNo','Fullname','attendees'); 
}


if($which!='Batch' and $which!='EditBatch'){
	if (allowedToOpen(1619,'1rtc')) {
		$cond='';
	}elseif(allowedToOpen(1618,'1rtc')){
		$cond='where Supervisor =\''.$_SESSION['(ak0)'].'\'';
	}
	else{
		$cond='where Attendee=\''.$_SESSION['(ak0)'].'\'';
	}
$sqlb='select Batch from eos_2batches '.$cond.' Group By Batch';
$stmtb=$link->query($sqlb); $resultb=$stmtb->fetchAll();

$batches='</br><h3>Batches</h3>';
	foreach($resultb AS $resb){
		$batches.='<form style="float:left;margin-right:3px;" action="storeeos.php?w=SwitchBatch&which='.$which.'" method="POST"><input type="hidden" value="'.$resb['Batch'].'" name="batch"><input style="width:90px;" type="submit" value="'.$resb['Batch'].'" name="switchbatch"></form> ';
	}
echo $batches.'<div style="clear: both; display: block; position: relative;height:10px;"></div>';


if(!isset($_SESSION['batch'])){
$sqls='select Batch from eos_2batches '.$cond.' limit 1';	
$stmts=$link->query($sqls); $results=$stmts->fetch();

	$_SESSION['batch']=$results['Batch'];
}

echo '<br><h1 align="center" style="background-color:white;padding:8px;border:1px solid green;"><font color="blue">Batch:&nbsp; '.$_SESSION['batch'].'</font></h1><br>';
}

//links
echo'</br>';
if(in_array($which, array('Traction','EncodeList','Rocks','Issues','ToDo','ScorecardList','CanceledRocksToDo','MonthlyMeetingIssues','MonthlyMeeting','MonthlyUpdates','ToDoSummary','EditQtr','EditMeasurables'))){	
echo'<a id="link" href="storeeos.php?w=Traction"><font style="font-size:8.5pt;font-weight:bold;">Measurables Summary</font></a> &nbsp; &nbsp; &nbsp; &nbsp;
<a id="link" href="storeeos.php?w=Rocks">Rocks</a> 
<a id="link" href="storeeos.php?w=Issues">Issues</a>
<a id="link" href="storeeos.php?w=ToDo">To-Do</a>
<a id="link" href="storeeos.php?w=ScorecardList">Scorecard</a>
'.str_repeat('&nbsp;',9).'
<a id="link" href="storeeos.php?w=MonthlyUpdates">Meeting Updates</a> 
<a id="link" href="storeeos.php?w=MonthlyMeeting">Meeting - Measurables</a> 
<a id="link" href="storeeos.php?w=MonthlyMeetingIssues">Meeting - ISSUES</a>
<a id="link" href="storeeos.php?w=ToDoSummary">To Do Summary</a> ';
if (allowedToOpen(array(1618,1619),'1rtc')) {
echo'<a id="link" target="_blank" href="storeeos.php">Branch Batch</a> '; 
}
}
echo'</br></br>';

if (in_array($which,array('AddBatch','EditBatchProcess'))){
$branchno=companyandbranchValue($link,'1branches','Branch',addslashes($_POST['Branch']),'BranchNo');
$supervisor=comboBoxValue($link,'1employees','CONCAT(Nickname,\' \',SurName)',addslashes($_POST['Supervisor']),'IDNo');
$attendee=comboBoxValue($link,'1employees','CONCAT(Nickname,\' \',SurName)',addslashes($_POST['Attendee']),'IDNo');
		}
		
if (allowedToOpen(array(1619,1618),'1rtc')) {
		$branchorsupervisor=$_SESSION['(ak0)'];
		$inputvalue='<input type="hidden" name="BranchOrSupervisor" value="'.$_SESSION['(ak0)'].'">';
	}
	else{
		$branchorsupervisor=$_SESSION['bnum'];
		$inputvalue='<input type="hidden" name="BranchOrSupervisor" value="'.$_SESSION['bnum'].'">';
	}
echo comboBox($link,'select * from eos_2vtoqtrmain ORDER BY VTOQtrId','VTOQtrId','VTOQtrId','vtoqs'); 
echo comboBox($link,'SELECT "Counted" AS CountedNotCounted, 1 AS CountedNotCountedValue UNION SELECT "NotCounted" AS CountedNotCounted, 0 AS CountedNotCountedValue','CountedNotCountedValue','CountedNotCounted','countednotcountedlist');

echo comboBox($link,'select br.Branch, br.BranchNo from eos_2batches b join 1branches br on br.BranchNo=b.BranchNo where Batch=\''.$_SESSION['batch'].'\' 
UNION 
select CONCAT(Nickname,\' \',SurName) as Fullname,Supervisor from eos_2batches b join 1employees e on e.IDNo=b.Supervisor where Batch=\''.$_SESSION['batch'].'\'','Branch','BranchNo','branchesorsupervisors');
if(in_array($which, array('Rocks','Issues','ToDo'))){
		$imgedit='<img src="../generalinfo/icons/edit.png" alt="Edit" height="20px;">';
		$imgdel='<img src="../generalinfo/icons/delete.png" alt="Edit" height="20px;">';
		$imgcancel='<img src="../generalinfo/icons/cancel.png" alt="Edit" height="20px;">';
}

if(in_array($which, array('Traction','Rocks','Issues','ToDo','ScorecardList','ToDoSummary'))){
	echo '<style>

		.tabs {
			width:100%;
			display:inline-block;
		}

			.tab-links:after {
			display:block;
			clear:both;
			content:"";
		}

		.tab-links li {
			margin:0px 5px;
			float:left;
			list-style:none;
		}

		.tab-links a {
			padding:9px 15px;
			display:inline-block;
			border-radius:3px 3px 0px 0px;
			background:#ffffe2;
			font-size:16px;
			font-weight:600;
			color:#4c4c4c;
			transition:all linear 0.15s;
			text-decoration: none;
		}

		.tab-links a:hover {
			background:#a7cce5;
			text-decoration:none;
		}

		li.active a, li.active a:hover {
			background:yellow;
			color:#4c4c4c;
		}

		.tab-content {
			padding:15px;
			border-radius:3px;
			box-shadow:-1px 1px 1px rgba(0,0,0,0.15);
			background:#fff;
		}

		.tab {
			display:none;
		}

		.tab.active {
			display:block;
		}
</style>';
}

$date=date('n');
	 if($date<=3){
		 $qtr=1;	 
	 }elseif($date<=6){
		 $qtr=2;
	 }elseif($date<=9){
		 $qtr=3;	 
	 }elseif($date<=12){
		 $qtr=4;	 
	 } 

$monthno=date('m');
	
	switch ($which){
	
	case 'ToDoSummary':
	$isrock=2; //todo
	$subtitle='ToDo';
	$title='To-Do Summary';
	echo '<title>'.$title.'</title>';
	
	echo '<div class="tabs">';

	echo '<br>';
	echo '<div class="tab-content">';
	echo '<h2>'.$title.'</h2><br>';
	echo '<div id="tab1" class="tab active">';
	
	$sql='select BranchOrSupervisor as IDNo,CONCAT(Nickname," ",SurName) AS FullName from eos_2storerocksissuestodo vqs join 1employees e on e.IDNo=vqs.BranchOrSupervisor WHERE e.Resigned=0 AND Batch='.$_SESSION['batch'].' AND IsRock='.$isrock.' AND `Stat`=0 AND (RIGHT(RIStatPerMonth,1)=0 OR RIGHT(RIStatPerMonth,1) IS NULL) GROUP BY IDNo
	UNION
	select BranchOrSupervisor as IDNo,Branch AS FullName from eos_2storerocksissuestodo vqs join 1branches b on b.BranchNo=vqs.BranchOrSupervisor WHERE Batch='.$_SESSION['batch'].' AND IsRock='.$isrock.' AND `Stat`=0 AND (RIGHT(RIStatPerMonth,1)=0 OR RIGHT(RIStatPerMonth,1) IS NULL) GROUP BY IDNo ORDER BY IDNo
	';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	//echo $sql; //exit();
	
	echo '<table style="width:100%;">';
	$cnttr=1;
	
	foreach($result as $ress){
		
		$sqlw='SELECT RockOrIssues FROM eos_2storerocksissuestodo WHERE Batch='.$_SESSION['batch'].' AND IsRock='.$isrock.' AND (RIGHT(RIStatPerMonth,1)=0 OR RIGHT(RIStatPerMonth,1) IS NULL) AND BranchOrSupervisor="'.$ress['IDNo'].'" AND Stat=0 ORDER BY TimeStamp DESC';
		// echo $sqlw;
		
		if ($cnttr % 2 == 1){
					echo '</tr><tr align="left"><td valign="top" align="left">';
				} else {
						
					echo '<td valign="top" align="left">';
				}
			echo '
				<table border="1px solid black;" style="background-color:#FFFACD;width:570px;margin-left:5%;border-collapse:collapse;"><tr><th align="left" style="padding:3px;">'.$ress['FullName'].'</th></tr><tr>';
		$stmtw=$link->query($sqlw); $resultw=$stmtw->fetchAll();
		foreach($resultw as $res){
			echo '<td style="width:100%;padding:3px;">'.$res['RockOrIssues'].'</td></tr>';
		}
		echo '</table><br><br>
			</td>';
			
			$cnttr++;
			
	}
	
	echo '</table>';
	
	echo '</div>';
	echo '</div>';
	echo '</div>';

	break;
	
	case 'MonthlyMeetingIssues':
	echo '<title>Meeting - ISSUES</title>';	

	$filtering='<option value="'.$monthno.'">Month: '.$monthno.'</option>';
		for ($i = $monthno-1; $i >=1; $i--) {
				if(strlen($i)==1){
					$i='0'.$i.'';
				}else{
					$i=$i;
				}
					$filtering.='<option value="'.$i.'">Month: '.$i.'</option>'; 
			}
		
	echo '<form action="#" method="POST">';
	echo '<select name="MonthNo">';
	echo $filtering;
	
	echo '</select>';
	echo ' <input type="submit" value="View Meeting">';
	echo '</form><br>';
	$rcondi='';
	if(isset($_POST['MonthNo']) AND $monthno<>$_POST['MonthNo']){
		$monthno=$_POST['MonthNo'];
		$adq=' AND RIStatPerMonth LIKE "%'.$monthno.'%"';
		$adq1=$adq;
		$adq2='';
		$openr=0;
		$sr=0;
	} else {
		$adq=' HAVING (LEFT(MonthNo,2)>'.($monthno-1).' OR MonthNo IS NULL OR `Status`=0)';
		$adq1=' HAVING (MonthNo IS NULL OR `Status`=0)';
		$adq2=' HAVING (MonthNo IS NOT NULL AND `Status`=1 AND MonthNo='.$monthno.')';
		$openr=1;
		$sr=1;
		$rcondi=' AND Resigned=0 ';
	}
	
	echo '<h3>Meeting - ISSUES (Month: '.$monthno.')</h3>';

	$sqlmain='select vqs.*,CONCAT(Nickname," ",SurName) AS `BranchOrSupervisor`,RIGHT(RIStatPerMonth,4) AS `WhatMonthNo`,RIGHT((SELECT WhatMonthNo),1) AS `Status`, LEFT((SELECT WhatMonthNo),2) AS MonthNo from eos_2storerocksissuestodo vqs join 1employees e on e.IDNo=vqs.BranchOrSupervisor WHERE Batch='.$_SESSION['batch'].' AND Stat=0 '.$rcondi;
	$sqlmainunion=' UNION select vqs.*,Branch AS `BranchOrSupervisor`,RIGHT(RIStatPerMonth,4) AS `WhatMonthNo`,RIGHT((SELECT WhatMonthNo),1) AS `Status`, LEFT((SELECT WhatMonthNo),2) AS MonthNo from eos_2storerocksissuestodo vqs join 1branches b on b.BranchNo=vqs.BranchOrSupervisor WHERE Batch='.$_SESSION['batch'].' AND Stat=0 ';
	
	
	$sql=$sqlmain.' AND VTOQtrId<='.$qtr.' AND IsRock=0 '.$adq1.'';
	$sql.=$sqlmainunion.' AND VTOQtrId<='.$qtr.' AND IsRock=0 '.$adq1.' ORDER BY IFNULL(`Status`,0),Priority';
	// echo $sql; exit();
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<br><b>Issues</b>';
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>Issues</th><th>Priority</th><th>Status</th><th>Resolution</th><th>BranchOrSupervisor</th></tr>';
		foreach($result as $ress){
			$otlink='vto.php?FromMonthlyMeeting=2&w=ResolveNotResolve&VTOQtrSubId='.$ress['VTOQtrSubId'].'&action_token='.$_SESSION['action_token'];
			
			echo'<tr style="'.(($monthno==$ress['MonthNo'] AND $ress['Status']==1)?'':(($monthno==$ress['MonthNo'] AND $ress['Status']==0)?'font-weight:bold;':'font-weight:bold;')).'"><td style="padding:5px;">'.$ress['RockOrIssues'].'</td><td align="center" style="padding:5px;">'.$ress['Priority'].'</td><td style="padding:5px;'.(($monthno==$ress['MonthNo'] AND $ress['Status']==0)?'color:red;':'').'">'.(($monthno==$ress['MonthNo'] AND $ress['Status']==1)?'Resolved':(($monthno==$ress['MonthNo'] AND $ress['Status']==0)?'Not Resolved':'<font color="red">Not Resolved</font>')).'</td><td style="padding:3px;width:500px;">'.$ress['RemarksOrResolution'].'</td><td style="padding:5px;">'.$ress['BranchOrSupervisor'].'</td></tr>';
		}
	echo'</table>';
	
	
	$sql=$sqlmain.' AND VTOQtrId<='.$qtr.' AND IsRock=0 '.$adq2.'';
	$sql.=$sqlmainunion.' AND VTOQtrId<='.$qtr.' AND IsRock=0 '.$adq2.' ORDER BY IFNULL(`Status`,1),Priority';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	if($sr==1){
	
	echo '<br><br><b>Resolved Issues</b>';
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>Issues</th><th>Priority</th><th>Resolution</th><th>BranchOrSupervisor</th></tr>';
		foreach($result as $ress){
		
			echo'<tr style=""><td style="padding:5px;">'.$ress['RockOrIssues'].'</td><td align="center" style="padding:5px;">'.$ress['Priority'].'</td><td style="padding:3px;width:330px;">'.$ress['RemarksOrResolution'].'</td><td style="padding:5px;">'.$ress['BranchOrSupervisor'].'</td></tr>';
		}
	echo'</table>';
	
	}
	
	break;
	
	case 'MonthlyMeeting':
	echo '<title>Meeting - Measurables</title>';

	$filtering='<option value="'.$monthno.'">Month: '.$monthno.'</option>';
	for ($i = $monthno-1; $i >=1; $i--) {
			if(strlen($i)==1){
				$i='0'.$i.'';
			}else{
				$i=$i;
			}
				$filtering.='<option value="'.$i.'">Month: '.$i.'</option>'; 
		}
	
	echo '<form action="#" method="POST">';
	echo '<select name="MonthNo">';
	echo $filtering;
	
	echo '</select>';
	echo ' <input type="submit" value="View Meeting">';
	echo '</form><br>';
	
	$rcondi='';
	if(isset($_POST['MonthNo']) AND $monthno<>$_POST['MonthNo']){
		$monthno=$_POST['MonthNo'];
		$adq=' AND RIStatPerMonth LIKE "%'.$monthno.'%"';
		$adq1=$adq;
		$openr=0;
	} else {
		$adq=' HAVING (LEFT(MonthNo,2)>'.($monthno-1).' OR MonthNo IS NULL OR `Status`=0)  AND (ISNULL(`Status`) OR `Status`=0 OR `Status`=1 )  ';
		$adq1=' HAVING (MonthNo IS NULL OR `Status`=0) ';
		$openr=1;
		$rcondi=' AND Resigned=0 ';
	}
	
	echo '<h3>Meeting - Measurables (Month: '.$monthno.')</h3>';
	
	
	
	$sqlmain='select vqs.*,CONCAT(Nickname," ",SurName) AS `BranchOrSupervisor`,CONCAT(Nickname," ",SurName) AS `BranchOrSupervisorOB`,RIGHT(RIStatPerMonth,4) AS `WhatMonthNo`,RIGHT((SELECT WhatMonthNo),1) AS `Status`, LEFT((SELECT WhatMonthNo),2) AS MonthNo from eos_2storerocksissuestodo vqs join 1employees e on e.IDNo=vqs.BranchOrSupervisor WHERE Batch='.$_SESSION['batch'].' AND Stat=0 '.$rcondi;
	$sqlmainunion=' UNION select vqs.*,Branch AS `BranchOrSupervisor`,Branch AS `BranchOrSupervisorOB`,RIGHT(RIStatPerMonth,4) AS `WhatMonthNo`,RIGHT((SELECT WhatMonthNo),1) AS `Status`, LEFT((SELECT WhatMonthNo),2) AS MonthNo from eos_2storerocksissuestodo vqs join 1branches b on b.BranchNo=vqs.BranchOrSupervisor WHERE Batch='.$_SESSION['batch'].' AND Stat=0 ';
	
	

	echo '<br><b>Scorecard</b>';
$sql0='CREATE TEMPORARY TABLE monthsandscore (
  `SCID` int(11) NOT NULL ,
  `Measurables` varchar(50) NOT NULL ,
  `Goal` double NOT NULL ,
  `CountedforEval` tinyint(1) DEFAULT NULL,
  `BranchOrSupervisor` smallint(6) NOT NULL ,
  `Month` varchar(255) DEFAULT NULL,
  `HigherOrLower` tinyint(1) DEFAULT NULL,
  `Scores` double NOT NULL
);'; 
// echo $sql0.'<br>';
$stmt0=$link->prepare($sql0);$stmt0->execute();
		$sql='select * from eos_2storescorecard WHERE Batch='.$_SESSION['batch'].' AND MonthNoAndScores is not null and Status=0';
		
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		foreach($result as $res){
			$monthandscores=explode(",",$res['MonthNoAndScores']);	
			foreach($monthandscores as $monthandscores){	
				$month=substr($monthandscores,0,2);				
				$scores=substr($monthandscores,3,100);	
				$sqli='insert into monthsandscore set Measurables=\''.$res['Measurables'].'\',Goal=\''.$res['Goal'].'\',CountedforEval=\''.$res['CountedforEval'].'\',HigherOrLower=\''.$res['HigherOrLower'].'\',BranchOrSupervisor=\''.$res['BranchOrSupervisor'].'\',Month=\''.$month.'\',Scores=\''.$scores.'\',SCID=\''.$res['SCID'].'\';';
				$stmti=$link->prepare($sqli);$stmti->execute();
				// echo $sqli.'<br>';

			}
			
		}
		
//thead		
	$table='<table id="table"><tr>';
		$columndata=array('BranchOrSupervisor','Measurables','Goal','HigherOrLower');
		foreach($columndata as $data){
			$table.='<th>'.$data.'</th>';			
		}		
	$sqls='select * from  monthsandscore where month between \'01\' and \'12\' Group By Month Order By Month Desc';
	// echo $sqls; exit();
	$stmts=$link->query($sqls); $results=$stmts->fetchAll();
		$sqlss='select Measurables,Goal,if(CountedforEval=1,"Counted","NotCounted") as CountedforEval,CONCAT(Nickname,\' \',SurName) as BranchOrSupervisor,HigherOrLower as HigherOrLowerValue,if(HigherOrLower=0,"Higher is Better","Lower is Better") as HigherOrLower,';
		$sqlunion=' UNION select Measurables,Goal,if(CountedforEval=1,"Counted","NotCounted") as CountedforEval,Branch as BranchOrSupervisor,HigherOrLower as HigherOrLowerValue,if(HigherOrLower=0,"Higher is Better","Lower is Better") as HigherOrLower,';
	foreach($results as $res){
			$sqlss.='max(CASE WHEN Month=\''.$res['Month'].'\' then Scores END) as `'.$res['Month'].'`,';
			$sqlunion.='max(CASE WHEN Month=\''.$res['Month'].'\' then Scores END) as `'.$res['Month'].'`,';
			$columndata[]=''.$res['Month'].'';
			
			$table.='<th>'.$res['Month'].'</th>';
		}
			$table.='</tr>';
//end thead		
//tbody				
		$sqlss.='\'Who\' as `nothing` from monthsandscore m join 1employees e on e.IDNo=m.BranchOrSupervisor Group By SCID '.$sqlunion.' \'Who\' as `nothing` from monthsandscore m join 1branches b on b.BranchNo=m.BranchOrSupervisor Group By SCID';
		// echo $sqlss; exit();
		$stmtss=$link->query($sqlss);$resultss=$stmtss->fetchAll();
	 $table.='<tr>';
		foreach($resultss as $ress){
			foreach($columndata as $data){
				if(is_numeric($ress[$data]) and $ress['HigherOrLowerValue']==0 and $ress['Goal']>$ress[$data]){
						$style='style="background-color:red; color:#fff; font-weight:bold;"';
				}elseif(is_numeric($ress[$data]) and $ress['HigherOrLowerValue']==-1 and $ress['Goal']<$ress[$data]){
						$style='style="background-color:red; color:#fff; font-weight:bold;"';
				}else{
						$style='';
				}
			$table.='<td '.$style.'>'.$ress[$data].'</td>';
			}
		 $table.='</tr>';
		}	
		echo '<table>'.$table.'</table>';
		
		
	$sql=$sqlmain.' AND VTOQtrId='.$qtr.' AND IsRock=1 '.$adq.'';
	$sql.=$sqlmainunion.'AND VTOQtrId='.$qtr.' AND IsRock=1 '.$adq.' ORDER BY Status,BranchOrSupervisorOB';
	// echo $sql; exit();
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<br><b>Rocks</b>';
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>Rock</th><th>Status</th><th>BranchOrSupervisor</th></tr>';
	
		foreach($result as $ress){
			echo'<tr style="'.(($monthno==$ress['MonthNo'] AND ($ress['Status']==1 OR $ress['Status']==2))?'':(($monthno==$ress['MonthNo'] AND $ress['Status']==0)?'font-weight:bold;':'font-weight:bold;')).'"><td style="padding:5px;">'.$ress['RockOrIssues'].'</td><td style="padding:5px;'.(($monthno==$ress['MonthNo'] AND $ress['Status']==0)?'color:red;':'').'">'.(($monthno==$ress['MonthNo'] AND $ress['Status']==1)?'On-Track':(($monthno==$ress['MonthNo'] AND $ress['Status']==0)?'Off-Track':(($monthno==$ress['MonthNo'] AND $ress['Status']==2)?'Done':''))).'</td><td style="padding:5px;">'.$ress['BranchOrSupervisor'].'</td></tr>';
		}
	echo'</table>';
	
	
	echo '<br><b>To-Do</b>';
	
	$sql=$sqlmain.' AND IsRock=2 '.$adq.'';
	$sql.=$sqlmainunion.'AND IsRock=2 '.$adq.' ORDER BY Status,BranchOrSupervisorOB';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();

	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>To-Do</th><th>Status</th><th>BranchOrSupervisor</th></tr>';
		foreach($result as $ress){
			echo'<tr style="'.(($monthno==$ress['MonthNo'] AND $ress['Status']==1)?'':(($monthno==$ress['MonthNo'] AND $ress['Status']==0)?'font-weight:bold;':'font-weight:bold;')).'"><td style="padding:5px;">'.$ress['RockOrIssues'].'</td><td style="padding:5px;'.(($monthno==$ress['MonthNo'] AND $ress['Status']==0)?'color:red;':'').'">'.(($monthno==$ress['MonthNo'] AND $ress['Status']==1)?'Done':(($monthno==$ress['MonthNo'] AND $ress['Status']==0)?'Not Done':'')).'</td><td style="padding:5px;">'.$ress['BranchOrSupervisor'].'</td></tr>';
		}
	echo'</table>';

	$sql=$sqlmain.' AND VTOQtrId<='.$qtr.' AND IsRock=0 '.$adq1.' ORDER BY IFNULL(`Status`,0),Priority';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();

	break;
	
	case 'ResolveNotResolve':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$addlsql='';
	if(isset($_POST['Resolution'])){
		$addlsql='RemarksOrResolution="'.addslashes($_POST['Resolution']).'",';
	}else{
		$addlsql='RemarksOrResolution=NULL,';
	}
	
	if(isset($_POST['btnResolved'])){
		$rnr=1;
	} else {
		$rnr=0;
	}
	
	$sql='UPDATE eos_2storerocksissuestodo set '.$addlsql.'RIStatPerMonth=(CASE 
		WHEN RIGHT(RIStatPerMonth,4)="'.$monthno.'-1" THEN REPLACE(RIStatPerMonth,"'.$monthno.'-1","'.$monthno.'-'.$rnr.'") 
		WHEN RIGHT(RIStatPerMonth,4)="'.$monthno.'-0" THEN REPLACE(RIStatPerMonth,"'.$monthno.'-0","'.$monthno.'-'.$rnr.'")
		WHEN RIGHT(RIStatPerMonth,4)=0 THEN "'.$monthno.'-'.$rnr.'"
		ELSE IFNULL(CONCAT(RIStatPerMonth,",'.$monthno.'-'.$rnr.'"),"'.$monthno.'-'.$rnr.'")
	END),EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE VTOQtrSubId='.$_GET['VTOQtrSubId'];
	
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	if(isset($_GET['FromMonthlyMeeting'])){
		if($_GET['FromMonthlyMeeting']==1){
			header("Location:storeeos.php?w=MonthlyMeeting");
		} else {
			header("Location:storeeos.php?w=MonthlyMeetingIssues");
		}
	} else {
		header("Location:storeeos.php?w=MonthlyUpdates");
	}
	
	
	break;
	
	case 'DoneNotDone':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
	
	$sql='UPDATE eos_2storerocksissuestodo set RIStatPerMonth=(CASE 
	WHEN RIGHT(RIStatPerMonth,4)="'.$monthno.'-1" THEN REPLACE(RIStatPerMonth,"'.$monthno.'-1","'.$monthno.'-'.$_GET['Done'].'") 
	WHEN RIGHT(RIStatPerMonth,4)="'.$monthno.'-0" THEN REPLACE(RIStatPerMonth,"'.$monthno.'-0","'.$monthno.'-'.$_GET['Done'].'")
	WHEN RIGHT(RIStatPerMonth,4)=0 THEN "'.$monthno.'-'.$_GET['Done'].'"
    ELSE IFNULL(CONCAT(RIStatPerMonth,",'.$monthno.'-'.$_GET['Done'].'"),"'.$monthno.'-'.$_GET['Done'].'")
END),EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE VTOQtrSubId='.$_GET['VTOQtrSubId'];
	
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:storeeos.php?w=MonthlyUpdates");
	break;
	
	case 'OnOffTrackProcess':
	
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='UPDATE eos_2storerocksissuestodo set RIStatPerMonth=(CASE 
	WHEN RIGHT(RIStatPerMonth,4)="'.$monthno.'-1" THEN REPLACE(RIStatPerMonth,"'.$monthno.'-1","'.$monthno.'-'.$_GET['Track'].'") 
	WHEN RIGHT(RIStatPerMonth,4)="'.$monthno.'-0" THEN REPLACE(RIStatPerMonth,"'.$monthno.'-0","'.$monthno.'-'.$_GET['Track'].'")
	WHEN RIGHT(RIStatPerMonth,4)="'.$monthno.'-2" THEN REPLACE(RIStatPerMonth,"'.$monthno.'-2","'.$monthno.'-'.$_GET['Track'].'")
	WHEN RIGHT(RIStatPerMonth,4)=0 THEN "'.$monthno.'-'.$_GET['Track'].'"
    ELSE IFNULL(CONCAT(RIStatPerMonth,",'.$monthno.'-'.$_GET['Track'].'"),"'.$monthno.'-'.$_GET['Track'].'")
END),EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE VTOQtrSubId='.$_GET['VTOQtrSubId'];
// echo $sql; exit();

	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:storeeos.php?w=MonthlyUpdates");
	
	break;
	
	case'UpdateScore':
if($_POST['submit']=='Submit'){
	$set='(case
	when MonthNoAndScores is not null then CONCAT("'.$monthno.'-'.$_POST['Scores'].',",MonthNoAndScores)
	else CONCAT("'.$monthno.'-'.$_POST['Scores'].'")
	end)';
	
}else{
	$set='(case
	when MonthNoAndScores is not null then REPLACE(MonthNoAndScores,substring_index(MonthNoAndScores,\',\',1),"'.$monthno.'-'.$_POST['Scores'].'")
	else REPLACE(MonthNoAndScores,MonthNoAndScores,"'.$monthno.'-'.$_POST['Scores'].'")
	end)';
}
		$sql='UPDATE eos_2storescorecard set MonthNoAndScores='.$set.' WHERE SCID='.$_REQUEST['SCID'];
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:storeeos.php?w=MonthlyUpdates");
	break;
	
	case 'MonthlyUpdates':
	echo '<title>Meeting Updates</title>';

	echo '<h3>Meeting Updates</h3>';
	
	$addcon=' HAVING (MonthNo>'.($monthno-1).' OR MonthNo IS NULL OR `Status`=0)';
	
	$sqlmain='select vqs.*,RIGHT(RIStatPerMonth,4) AS WhatMonthNo,RIGHT((SELECT WhatMonthNo),1) AS `Status`, LEFT((SELECT WhatMonthNo),2) AS MonthNo from eos_2storerocksissuestodo vqs WHERE Batch='.$_SESSION['batch'].' AND Stat=0 AND BranchOrSupervisor='.$_SESSION['(ak0)'].'';
	$sqlunion=' UNION
	select vqs.*,RIGHT(RIStatPerMonth,4) AS WhatMonthNo,RIGHT((SELECT WhatMonthNo),1) AS `Status`, LEFT((SELECT WhatMonthNo),2) AS MonthNo from eos_2storerocksissuestodo vqs WHERE Batch='.$_SESSION['batch'].' AND Stat=0 AND BranchOrSupervisor='.$_SESSION['bnum'].'';
	
	
	//scorecard

$sql='select *,substring(substring_index(MonthNoAndScores,\',\',1),4,100) AS Scores,substring(MonthNoAndScores,1,2) as MonthNo,if(CountedforEval=1,"Counted","NotCounted") as CountedforEval from eos_2storescorecard where Batch='.$_SESSION['batch'].' AND BranchOrSupervisor='.$_SESSION['(ak0)'].' and Status=0
UNION
select *,substring(substring_index(MonthNoAndScores,\',\',1),4,100) AS Scores,substring(MonthNoAndScores,1,2) as MonthNo,if(CountedforEval=1,"Counted","NotCounted") as CountedforEval from eos_2storescorecard where Batch='.$_SESSION['batch'].' AND BranchOrSupervisor='.$_SESSION['bnum'].' and Status=0';
// echo $sql; exit();
$stmt=$link->query($sql); $result=$stmt->fetchAll();
	echo '<br><b>Scorecard</b>';			
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>Measurables</th><th>Goal</th><th>CountedforEval</th><th>Scores</th><th></th></tr>';
		foreach($result as $res){
			if($res['MonthNo']==$monthno){
				$input='<input OnClick="return confirm(\'Are you sure you want to Overwrite?\');" type="submit" name="submit" value="Overwrite">';
				$value=$res['Scores'];
				
			}else{
				$input='<input OnClick="return confirm(\'Are you sure you want to Submit?\');" type="submit" name="submit" value="Submit">';
				$value='';
			}
			echo'<tr>
					<td style="padding:3px;">'.$res['Measurables'].'</td><td style="padding:3px;">'.$res['Goal'].'</td><td style="padding:3px;">'.$res['CountedforEval'].'</td>
					<td><form method="post" action="storeeos.php?w=UpdateScore"><input type="text" size="10" name="Scores" placeholder="Scores" value="'.$value.'" required><input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'"><input type="hidden" name="SCID" value="'.$res['SCID'].'">'.$input.'</form></td><td style="padding:3px;">
					<a  OnClick="return confirm(\'Are you sure?\');" href="storeeos.php?w=ScorecardStatus&SCID='.$res['SCID'].'&action_token='.$_SESSION['action_token'].'">Done</a></td>
				</tr>';
		}
	echo'</table>';
	
	
	$sql=$sqlmain.' AND VTOQtrId='.$qtr.' AND IsRock=1';
	$sql.=$sqlunion.' AND VTOQtrId='.$qtr.' AND IsRock=1 HAVING ISNULL(`Status`) OR `Status`=0 OR `Status`=1';
	// echo $sql; exit();
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<br><b>Rocks</b>';
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>Rock</th><th>Status</th><th colspan=3></th></tr>';
		foreach($result as $ress){
			$otlink='storeeos.php?w=OnOffTrackProcess&VTOQtrSubId='.$ress['VTOQtrSubId'].'&action_token='.$_SESSION['action_token'];
			echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.(($monthno==$ress['MonthNo'] AND $ress['Status']==1)?'On-Track':(($monthno==$ress['MonthNo'] AND $ress['Status']==0)?'Off-Track':(($monthno==$ress['MonthNo'] AND $ress['Status']==2)?'Done':''))).'</td><td style="padding:3px;"><a href="'.$otlink.'&Track=1">On-Track</a></td><td style="padding:3px;"><a href="'.$otlink.'&Track=0">Off-Track</a></td></td><td style="padding:3px;"><a href="'.$otlink.'&Track=2">Done</a></td></tr>';
		}
	echo'</table>';
	
	
	
	echo '<br><b>To-Do</b>';
	
	$sql=$sqlmain.' AND IsRock=2 '.$addcon.'';
	$sql.=$sqlunion.' AND IsRock=2 '.$addcon.' ORDER BY Priority';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>To-Do</th><th>Status</th><th colspan=2></th></tr>';
		foreach($result as $ress){
			$otlink='storeeos.php?w=DoneNotDone&VTOQtrSubId='.$ress['VTOQtrSubId'].'&action_token='.$_SESSION['action_token'];
			echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.(($monthno==$ress['MonthNo'] AND $ress['Status']==1)?'Done':(($monthno==$ress['MonthNo'] AND $ress['Status']==0)?'Not Done':'')).'</td><td style="padding:3px;"><a href="'.$otlink.'&Done=1">Done</a></td><td style="padding:3px;"><a href="'.$otlink.'&Done=0">Not Done</a></td></tr>';
		}
	echo'</table>';
	
	
	$sql=$sqlmain.' AND VTOQtrId<='.$qtr.' '.$addcon.' AND IsRock=0';
	$sql.=$sqlunion.' AND VTOQtrId<='.$qtr.' '.$addcon.' AND IsRock=0 ORDER BY Priority';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<br><b>Issues</b>';
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>Issues</th><th>Priority</th><th>Status</th><th>Resolution</th></tr>';
		foreach($result as $ress){
			$otlink='storeeos.php?w=ResolveNotResolve&VTOQtrSubId='.$ress['VTOQtrSubId'].'&action_token='.$_SESSION['action_token'];
			echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td align="center" style="padding:3px;">'.$ress['Priority'].'</td><td style="padding:3px;">'.(($monthno==$ress['MonthNo'] AND $ress['Status']==1)?'Resolved':(($monthno==$ress['MonthNo'] AND $ress['Status']==0)?'Not Resolved':'')).'</td><td style="padding:3px;"><form action="'.$otlink.'" method="POST" autocomplete="off"><input type="text" size="40" name="Resolution" value="'.$ress['RemarksOrResolution'].'"><input type="submit" name="btnResolved" value="Resolved"> <input type="submit" name="btnNotResolved" value="Not Resolved"></form></td></tr>';
			//<td style="padding:3px;"><a href="'.$otlink.'&Resolve=0">Not Resolved</a></td>
		}
	echo'</table>';
	
	break;
	
	case 'CanceledRocksToDo':
	echo '<title>Canceled Rocks and To-Do</title>';
	
	

	break;
	
	case'EditMeasurables':
	echo '<title>Edit Measurable</title>';
	$sql='select *,if(HigherOrLower=0,"Higher is Better","Lower is Better") as HigherOrLower,if(CountedforEval=1,"Counted","NotCounted") as CountedforEval from eos_2storescorecard sm where SCID=\''.$_GET['SCID'].'\'';
	// echo $sql; exit();
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo'<h3>Edit?</h3></br><form method="post" action="storeeos.php?w=EditMeasurablesProcess&SCID='.$_GET['SCID'].'">
		 Measurable: <input type="text" name="Measurables" value="'.$result['Measurables'].'">
		 Goal: <input type="text" name="Goal" value="'.$result['Goal'].'">
		 '.$inputvalue.'
		 Higher Or Lower: <input type="text" name="HigherOrLower" value="'.$result['HigherOrLower'].'"  list="higherorlowerlist">
<datalist id="higherorlowerlist">
    <option value="Higher is Better" label="0"></option>
    <option value="Lower is Better" label="-1"></option>
</datalist>
CountedforEval: <input type="text" name="CountedforEval" list="countednotcountedlist" size="7" value="'.$result['CountedforEval'].'">
		 <input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		 <input type="submit" name="submit">
	';
	break;
	
	case'EditMeasurablesProcess':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$scid = intval($_GET['SCID']);
		
		if($_POST['HigherOrLower']=='Higher is Better'){
			$hol=0;
		} else {
			$hol=-1;
		}
		
		if($_POST['CountedforEval']=='Counted'){
		$countedforeval=1;
		}else{
			$countedforeval=0;
		}
		
		$sql='update eos_2storescorecard set Measurables=\''.$_POST['Measurables'].'\',Goal=\''.$_POST['Goal'].'\',BranchOrSupervisor=\''.$_POST['BranchOrSupervisor'].'\',Batch='.$_SESSION['batch'].',HigherOrLower=\''.$hol.'\',CountedforEval=\''.$countedforeval.'\' where SCID=\''.$scid.'\'';
		
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:storeeos.php?w=ScorecardList");
	break;
	
	case'DeleteMeasurables':
	$scid = intval($_GET['SCID']);
	$sql='delete from eos_2storescorecard where SCID=\''.$scid.'\'';
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:storeeos.php?w=ScorecardList");
	break;
	
	case'ScorecardList':
		
	$subtitle='Scorecard Measurables';
	echo '<title>'.$subtitle.'</title>';
	echo '<h2>'.$subtitle.'</h2>';
	echo'</br><div style="background-color:white;"><b>ENCODING: </b></br></br>
		<form method="post" action="storeeos.php?w=EncodeProcessMeasurables" autocomplete="off">
			<b>Measurable:</b> <input type="text" name="Measurables" size="25">
			<b>Goal Per Month:</b> <input type="text" name="Goal" size="5">
			'.$inputvalue.'
			<b>Higher Or Lower:</b> <input type="text" name="HigherOrLower" list="higherorlowerlist">
<datalist id="higherorlowerlist">
    <option value="Higher is Better" label="0"></option>
    <option value="Lower is Better" label="-1"></option>
</datalist>
<b>CountedforEval:</b> <input type="text" name="CountedforEval" list="countednotcountedlist" size="7">
			<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
			 <input type="submit" name="submit">
			</form></br></div></br>';
			
	echo '<form method="post" action="storeeos.php?w=ScorecardList">
		<b>BranchOrSupervisor:</b> <input type="text" name="BranchOrSupervisor" list="branchesorsupervisors" size="10">
		<input type="submit" name="Lookup" value="Lookup">
	</form><br>';
	
	$addlcond='';
	
	if(isset($_POST['Lookup'])){
		$branchorsupervisor=$_POST['BranchOrSupervisor'];
		$addlcond=' AND BranchOrSupervisor='.$branchorsupervisor.'';
		$showedit=0;
	} else {
		$addlcond=' AND BranchOrSupervisor='.$branchorsupervisor.'';
		$showedit=1;
	}
	
	$sqls='select br.Branch, br.BranchNo from eos_2batches b join 1branches br on br.BranchNo=b.BranchNo where b.BranchNo=\''.$branchorsupervisor.'\' 
	UNION 
	select CONCAT(Nickname,\' \',SurName) as Fullname,Supervisor from eos_2batches b join 1employees e on e.IDNo=b.Supervisor where Supervisor=\''.$branchorsupervisor.'\' Group by Supervisor';
	// echo $sqls; exit();
	$stmts=$link->query($sqls); $results=$stmts->fetch();
	
	echo '<div class="tabs">';
	if (allowedToOpen(1619,'1rtc') and !isset($_POST['Lookup'])) {
		$sqlse='select CONCAT(Nickname,\' \',SurName) as Fullname from 1employees where IDNo=\''.$_SESSION['(ak0)'].'\'';
		$stmtse=$link->query($sqlse); $resultse=$stmtse->fetch();
		$results['Branch']=$resultse['Fullname'];
	}
	echo '<br><h3>BranchOrSupervisor: '.$results['Branch'].'</h3>';
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	$sql='select *,if(HigherOrLower=0,"Higher is Better","Lower is Better") as HigherOrLower,if(CountedforEval=1,"Counted","NotCounted") as CountedforEval from eos_2storescorecard m WHERE Batch=\''.$_SESSION['batch'].'\' '.$addlcond.' and Status=0';
	// echo $sql; exit();
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	echo '<table border="1px solid black" style="border-collapse:collapse;"><b>ACTIVE</b>';
	echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th>Goal Per Month</th><th>Higher Or Lower</th><th>CountedforEval</th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['Measurables'].'</td><td style="padding:3px;">'.$ress['Goal'].'</td><td style="padding:3px;">'.$ress['HigherOrLower'].'</td><td style="padding:3px;">'.$ress['CountedforEval'].'</td>'.(($branchorsupervisor==$_SESSION['(ak0)'] or $branchorsupervisor==$_SESSION['bnum'])?'<td style="padding:3px;"><a href="storeeos.php?w=EditMeasurables&SCID='.$ress['SCID'].'">Edit</a> <a href="storeeos.php?w=DeleteMeasurables&SCID='.$ress['SCID'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">Delete</a></td>':'').'</tr>';
		}
		
	echo'</table>';
	
//DONE	
$sql='select *,if(HigherOrLower=0,"Higher is Better","Lower is Better") as HigherOrLower,if(CountedforEval=1,"Counted","NotCounted") as CountedforEval from eos_2storescorecard m WHERE Batch=\''.$_SESSION['batch'].'\' '.$addlcond.' and Status=1';
	// echo $sql; exit();
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	echo '</br><table border="1px solid black" style="border-collapse:collapse;"><b>DONE</b>';
	echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th>Goal Per Month</th><th>Higher Or Lower</th><th></th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['Measurables'].'</td><td style="padding:3px;">'.$ress['Goal'].'</td><td style="padding:3px;">'.$ress['HigherOrLower'].'</td><td><a  OnClick="return confirm(\'Are you sure?\');" href="storeeos.php?w=ScorecardStatus&SCID='.$ress['SCID'].'&action_token='.$_SESSION['action_token'].'">Not Done</a></td></tr>';
		}
		
	echo'</table>';
	
	echo'</div></div></div';	
	
	break;
	
	case 'ToDo':
	$isrock=2; //todo
	$subtitle='ToDo';
	echo '<title>'.$subtitle.'</title>';
	echo '<h2>'.$subtitle.'</h2>';
	echo '</br><div style="background-color:white;"><b>ENCODING: <b><br></br>';
	echo '<form method="post" action="storeeos.php?w=EncodeProcessQtr&IsRock=2" autocomplete="off">
		<input type="hidden" name="Quarter" value="0">
		<b>To-Do:</b> <input type="text" name="Rocks" size="50">
		'.$inputvalue.'
		<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		<input type="submit" name="submit">
	</form><br>';
	echo '</div></br>';
	
	echo '<form method="post" action="storeeos.php?w='.$subtitle.'">
		<b>BranchOrSupervisor:</b> <input type="text" name="BranchOrSupervisor" list="branchesorsupervisors" size="10" required>
		<input type="submit" name="Lookup" value="Lookup">
	</form><br>';
	
	$addlcond='';
	if(isset($_POST['Lookup'])){
		$branchorsupervisor=$_POST['BranchOrSupervisor'];
		$addlcond=' AND BranchOrSupervisor='.$branchorsupervisor.'';
		$showedit=0;
	} else {
		$addlcond=' AND BranchOrSupervisor='.$branchorsupervisor.'';
		$showedit=1;
	}
	
	$sqls='select br.Branch, br.BranchNo from eos_2batches b join 1branches br on br.BranchNo=b.BranchNo where b.BranchNo=\''.$branchorsupervisor.'\' 
	UNION 
	select CONCAT(Nickname,\' \',SurName) as Fullname,Supervisor from eos_2batches b join 1employees e on e.IDNo=b.Supervisor where Supervisor=\''.$branchorsupervisor.'\' Group by Supervisor';
	// echo $sqls; exit();
	$stmts=$link->query($sqls); $results=$stmts->fetch();
	
	echo '<div class="tabs">';
	
	$sqlmain='select * from eos_2storerocksissuestodo vqs WHERE Batch=\''.$_SESSION['batch'].'\' AND IsRock='.$isrock.' AND `Stat`=0';
	if (allowedToOpen(1619,'1rtc') and !isset($_POST['Lookup'])) {
		$sqlse='select CONCAT(Nickname,\' \',SurName) as Fullname from 1employees where IDNo=\''.$_SESSION['(ak0)'].'\'';
		$stmtse=$link->query($sqlse); $resultse=$stmtse->fetch();
		$results['Branch']=$resultse['Fullname'];
	}
	echo '<br><h3>BranchOrSupervisor: '.$results['Branch'].'</h3>';
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	$sql=$sqlmain.' '.$addlcond.' AND (RIGHT(RIStatPerMonth,1)=0 OR RIGHT(RIStatPerMonth,1) IS NULL)';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th>'.$subtitle.' - Pending/UnDone</th><th></th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.(($branchorsupervisor==$_SESSION['(ak0)'] or $branchorsupervisor==$_SESSION['bnum'])?'<a href="storeeos.php?subtitle='.$subtitle.'&w=EditQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'">'.$imgedit.'</a> <a href="storeeos.php?go='.$isrock.'&w=DeleteQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">'.$imgdel.'</a> <a href="storeeos.php?go='.$isrock.'&w=CancelQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Cancel?\');">'.$imgcancel.'</a>':'').'</td></tr>';
		}
		
	echo'</table>';
	
	echo '<br>';
	$sql=$sqlmain.' '.$addlcond.' AND RIGHT(RIStatPerMonth,1)=1';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th>'.$subtitle.' - DONE</th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td></tr>';
		}
		
	echo'</table>';
	
	echo '</div>';
	
	
	
	
	
	echo '</div>';
	
	echo '</div>';
	
	$isrock=2; //todo
	$subtitle='Canceled To-Do';
	
	echo '</br></br><h2>'.$subtitle.'</h2><br>';
	$addlcond='';
	
	
	echo '<div class="tabs">
		';
	
	$sqlmain='select vqs.TimeStamp as TimeStamp,RockOrIssues,CONCAT(Nickname,\' \',SurName) as BranchOrSupervisor from eos_2storerocksissuestodo vqs join 1employees e on e.IDNo=vqs.BranchOrSupervisor  WHERE Batch='.$_SESSION['batch'].' AND IsRock='.$isrock.' AND `Stat`=1 
	UNION
	select vqs.TimeStamp as TimeStamp,RockOrIssues,Branch as BranchOrSupervisor from eos_2storerocksissuestodo vqs join 1branches b on b.BranchNo=vqs.BranchOrSupervisor  WHERE Batch='.$_SESSION['batch'].' AND IsRock='.$isrock.' AND `Stat`=1 
	';
	// echo $sqlmain; exit();
	
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	
	$sql=$sqlmain.' ORDER BY TimeStamp DESC';
	// echo $sql; exit();
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th>BranchOrSupervisor</th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.$ress['BranchOrSupervisor'].'</td></tr>';
		}
		
	echo'</table>';
	
	echo '</div>';
	echo '</div>';
	echo '</div>';
	
	break;
	
	case 'Issues':
	
	$isrock=0; //issues
	$subtitle='Issues';
	echo '<title>'.$subtitle.'</title>';
	echo '<h2>'.$subtitle.'</h2>';
	echo '<br><i>Priority: 1 = 1st Priority, 2 = 2nd Priority, 3 = Last Priority </i><br>';
	echo '<div style="background-color:white;"><b>ENCODING<b></br><br>';
	echo '<form method="post" action="storeeos.php?w=EncodeProcessQtr&IsRock=0" autocomplete="off">
		<input type="hidden" name="Quarter" value="0">
		<b>Issue:</b> <input type="text" name="Rocks" size="50">
		'.$inputvalue.'
		<b>Priority:</b> <input type="number" min="1" max="3" name="Priority" style="width:40;">
		<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		<input type="submit" name="submit">
	</form><br>';
	echo '</div></br>';
	
	
	echo '<form method="post" action="storeeos.php?w='.$subtitle.'">
		<b>BranchOrSupervisor:</b> <input type="text" name="BranchOrSupervisor" list="branchesorsupervisors" size="10" required>
		<input type="submit" name="Lookup" value="Lookup">
	</form><br>';
	
	$addlcond='';
	if(isset($_POST['Lookup'])){
		$branchorsupervisor=$_POST['BranchOrSupervisor'];
		$addlcond=' AND BranchOrSupervisor='.$branchorsupervisor.'';
		$showedit=0;
	} else {
		$addlcond=' AND BranchOrSupervisor='.$branchorsupervisor.'';
		$showedit=1;
	}
	
	$sqls='select br.Branch, br.BranchNo from eos_2batches b join 1branches br on br.BranchNo=b.BranchNo where b.BranchNo=\''.$branchorsupervisor.'\' 
	UNION 
	select CONCAT(Nickname,\' \',SurName) as Fullname,Supervisor from eos_2batches b join 1employees e on e.IDNo=b.Supervisor where Supervisor=\''.$branchorsupervisor.'\' Group by Supervisor';
	// echo $sqls; exit();
	$stmts=$link->query($sqls); $results=$stmts->fetch();	
	echo '<div class="tabs">';
	
	$sqlmain='select vqs.* from eos_2storerocksissuestodo vqs WHERE Batch=\''.$_SESSION['batch'].'\' AND IsRock='.$isrock.' AND `Stat`=0';
	// echo $sqlmain; exit();
	if (allowedToOpen(1619,'1rtc') and !isset($_POST['Lookup'])) {
		$sqlse='select CONCAT(Nickname,\' \',SurName) as Fullname from 1employees where IDNo=\''.$_SESSION['(ak0)'].'\'';
		$stmtse=$link->query($sqlse); $resultse=$stmtse->fetch();
		$results['Branch']=$resultse['Fullname'];
	}
	echo '<br><h3>BranchOrSupervisor: '.$results['Branch'].'</h3>';
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	$sql=$sqlmain.' '.$addlcond.' AND (RIGHT(RIStatPerMonth,1)=0 OR RIGHT(RIStatPerMonth,1) IS NULL) ORDER BY VTOQtrId,Priority';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th align="center">'.$subtitle.' - Pending/Not Resolved</th><th>Priority</th><th></th></tr>';
		foreach($result as $ress){
			echo'<tr style="'.($ress['VTOQtrId']==$qtr?'background-color:#FFFACD;':'').'"><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.$ress['Priority'].'</td><td style="padding:3px;">'.(($branchorsupervisor==$_SESSION['(ak0)'] or $branchorsupervisor==$_SESSION['bnum'])?'<a href="storeeos.php?subtitle='.$subtitle.'&w=EditQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'">'.$imgedit.'</a> <a href="storeeos.php?go='.$isrock.'&w=DeleteQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">'.$imgdel.'</a>':'').'</td></tr>';
		}
		
	echo'</table>';
	
	echo '<br>';
	$sql=$sqlmain.' '.$addlcond.' AND RIGHT(RIStatPerMonth,1)=1 ORDER BY VTOQtrId,Priority';
	// echo $sql; exit();
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th align="center">'.$subtitle.' - RESOLVED</th><th>Resolution</th><th>Priority</th></tr>';
		foreach($result as $ress){
			echo'<tr style="'.($ress['VTOQtrId']==$qtr?'background-color:#FFFACD;':'').'"><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.$ress['RemarksOrResolution'].'</td><td style="padding:3px;">'.$ress['Priority'].'</td></tr>';
		}
		
	echo'</table>';
	
	echo '</div>';
	
	
	
	
	
	echo '</div>';
	
	echo '</div>';
	
	break;
	
	case'EditQtr':
	echo '<title>Edit '.$_GET['subtitle'].'</title>';
		
	$sql='select vqs.* from eos_2storerocksissuestodo vqs where VTOQtrSubId=\''.$_GET['VTOQtrSubId'].'\'';
	
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo'<h3>Edit '.$_GET['subtitle'].'?</h3></br><form method="post" action="storeeos.php?subtitle='.$_GET['subtitle'].'&w=EditQtrProcess&VTOQtrSubId='.$_GET['VTOQtrSubId'].'">';
		if($_GET['subtitle']=='Rocks'){
			echo '<b>Quarter:</b> <input type="text" name="Quarter" value="'.$result['VTOQtrId'].'" size="5" min="1" max="4">';
		}
		 echo '<b>'.$_GET['subtitle'].':</b> <input type="text" name="Rocks" value="'.$result['RockOrIssues'].'" size="50">
		 '.$inputvalue.'';
		 
		 if($_GET['subtitle']=='Issues'){
			 echo ' <b>Priority:</b> <input type="number" min="1" max="3" name="Priority" value="'.$result['Priority'].'">';
		 }
		 echo '
		 <input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		 <input type="submit" name="submit">
	';
	break;
	
	case'EditQtrProcess':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$vtoqtrsubid = intval($_GET['VTOQtrSubId']);
		$priosql=''; $qtrsql='';
		if($_GET['subtitle']=='Rocks'){
			$qtrsql='VTOQtrId='.$_POST['Quarter'].',';
		}
	
		if(isset($_POST['Priority'])){
			$priosql=',Priority='.$_POST['Priority'].'';
		}
		// print_r($_POST);
		$sql='update eos_2storerocksissuestodo set '.$qtrsql.'RockOrIssues=\''.$_POST['Rocks'].'\''.$priosql.',EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=NOW(),BranchOrSupervisor=\''.$_POST['BranchOrSupervisor'].'\',Batch='.$_SESSION['batch'].' where VTOQtrSubId=\''.$vtoqtrsubid.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		if($_GET['subtitle']=='Rocks'){
			$go='Rocks';
		} elseif($_GET['subtitle']=='ToDo') {
			$go='ToDo';
		} else {
			$go='Issues';
		}
		header("Location:storeeos.php?w=".$go);
	break;
	
	case'DeleteQtr':
		$vtoqtrsubid = intval($_GET['VTOQtrSubId']);
		
		$sqlc='select RIStatPerMonth from eos_2storerocksissuestodo where VTOQtrSubId=\''.$vtoqtrsubid.'\' AND (RIStatPerMonth IS NOT NULL)';
		$stmtc=$link->query($sqlc); 
		
		if($stmtc->rowCount()==0){
			$sql='delete from eos_2storerocksissuestodo where VTOQtrSubId=\''.$vtoqtrsubid.'\' AND RIStatPerMonth IS NULL';
		}
		else {
			$sql='UPDATE eos_2storerocksissuestodo SET Stat=1,EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=NOW() where VTOQtrSubId=\''.$vtoqtrsubid.'\'';
		}
			// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		if($_GET['go']==1){
			$go='Rocks';
		} else if($_GET['go']==0){
			$go='Issues';
		} else {
			$go='ToDo';
		}
		header("Location:storeeos.php?w=".$go);
	break;
	
	case'CancelQtr':
		$vtoqtrsubid = intval($_GET['VTOQtrSubId']);
		$sql='UPDATE eos_2storerocksissuestodo SET Stat=1,EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=NOW() where VTOQtrSubId=\''.$vtoqtrsubid.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		if($_GET['go']==1){
			$go='Rocks';
		} else if($_GET['go']==0){
			$go='Issues';
		} else {
			$go='ToDo';
		} 
		header("Location:storeeos.php?w=".$go);
	break;
	
	case 'Rocks':
	
		$subtitle='Rocks';
		echo '<title>'.$subtitle.'</title>';
		$isrock=1;
	
	echo '<h2>'.$subtitle.'</h2>';
	echo '</br><div style="background-color:white;"><b>ENCODING:</b> </br></br>';
	echo '<form method="post" action="storeeos.php?w=EncodeProcessQtr&IsRock=1" autocomplete="off">
		<b>Quarter:</b> <input type="text" name="Quarter" list="vtoqs" size="1" required>
		<b>Rocks:</b> <input type="text" name="Rocks" size="40">
		'.$inputvalue.'
		<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		<input type="submit" name="submit">
	</form><br>';
	echo '</div></br>';
	
	
	echo '<form method="post" action="storeeos.php?w='.$subtitle.'">
		<b>BranchOrSupervisor:</b> <input type="text" name="BranchOrSupervisor" list="branchesorsupervisors" size="10" required>';
	
		echo '<input type="submit" name="Lookup" value="Lookup">
	</form><br>';
	
	$addlcond='';
	if(isset($_POST['Lookup'])){
		$branchorsupervisor=$_POST['BranchOrSupervisor'];
		$addlcond=' AND BranchOrSupervisor='.$branchorsupervisor.' ';
		$showedit=0;
	} else {
		$addlcond=' AND BranchOrSupervisor='.$branchorsupervisor;
		$showedit=1;
	}
	$addlcond.=' ORDER BY Priority';
	
	$sqls='select br.Branch, br.BranchNo from eos_2batches b join 1branches br on br.BranchNo=b.BranchNo where b.BranchNo=\''.$branchorsupervisor.'\' 
	UNION 
	select CONCAT(Nickname,\' \',SurName) as Fullname,Supervisor from eos_2batches b join 1employees e on e.IDNo=b.Supervisor where Supervisor=\''.$branchorsupervisor.'\' Group by Supervisor';
	$stmts=$link->query($sqls); $results=$stmts->fetch();
// echo $sqls; exit();

	
	
	echo '<div class="tabs">';
	
	$sqlmain='select vqs.*,IF(RIGHT(RIStatPerMonth,1)=1,"green","red") as bullcolor  from eos_2storerocksissuestodo vqs WHERE Batch=\''.$_SESSION['batch'].'\' and Stat=0 AND IsRock='.$isrock.' ';
	// echo $sqlmain; exit();
	if (allowedToOpen(1619,'1rtc') and !isset($_POST['Lookup'])) {
		$sqlse='select CONCAT(Nickname,\' \',SurName) as Fullname from 1employees where IDNo=\''.$_SESSION['(ak0)'].'\'';
		$stmtse=$link->query($sqlse); $resultse=$stmtse->fetch();
		$results['Branch']=$resultse['Fullname'];
	}
	echo '<br><h3>BranchOrSupervisor: '.$results['Branch'].'</h3>';
	echo '<div class="tab-content">';
	
	
	
	
	$hl='style="background-color:yellow;"';
	$hlt='background-color:#FFFACD;';
	echo '<div id="tab5" class="tab active">';
	
	echo '<table style="width:100%;">';
	echo '<tr>';
		echo '<th '.($qtr==1?$hl:'').'>1st Quarter</th>';
		echo '<th '.($qtr==2?$hl:'').'>2nd Quarter</th>';
		echo '<th '.($qtr==3?$hl:'').'>3rd Quarter</th>';
		echo '<th '.($qtr==4?$hl:'').'>4th Quarter</th>';
	echo '</tr>';
	echo '<tr>';
		echo '<td valign="top" style="width:25%;'.($qtr==1?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=1 '.$addlcond.'';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th></th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td><td style="padding:3px;width:70px;">'.((($branchorsupervisor==$_SESSION['(ak0)'] AND $qtr<=1) or ($branchorsupervisor==$_SESSION['bnum'] AND $qtr<=1))?'<a href="storeeos.php?subtitle='.$subtitle.'&w=EditQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'">'.$imgedit.'</a> <a href="storeeos.php?go='.$isrock.'&w=DeleteQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">'.$imgdel.'</a> <a href="storeeos.php?go='.$isrock.'&w=CancelQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Cancel?\');">'.$imgcancel.'</a>':'').'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	echo '<td valign="top" valign="top" style="width:25%;'.($qtr==2?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=2 '.$addlcond.'';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th></th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td><td style="padding:3px;width:70px;">'.((($idno==$_SESSION['(ak0)'] AND $qtr<=2) or allowedToOpen(1614,'1rtc'))?'<a href="storeeos.php?subtitle='.$subtitle.'&w=EditQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'">'.$imgedit.'</a> <a href="storeeos.php?go='.$isrock.'&w=DeleteQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">'.$imgdel.'</a> <a href="storeeos.php?go='.$isrock.'&w=CancelQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Cancel?\');">'.$imgcancel.'</a>':'').'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	echo '<td valign="top" valign="top" style="width:25%;'.($qtr==3?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=3 '.$addlcond.'';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th></th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td><td style="padding:3px;width:70px;">'.((($idno==$_SESSION['(ak0)']  AND $qtr<=3) or allowedToOpen(1614,'1rtc'))?'<a href="storeeos.php?subtitle='.$subtitle.'&w=EditQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'">'.$imgedit.'</a> <a href="storeeos.php?go='.$isrock.'&w=DeleteQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">'.$imgdel.'</a> <a href="storeeos.php?go='.$isrock.'&w=CancelQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Cancel?\');">'.$imgcancel.'</a>':'').'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	echo '<td valign="top" valign="top" style="width:25%;'.($qtr==4?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=4 '.$addlcond.'';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th></th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td><td style="padding:3px;width:70px;">'.((($idno==$_SESSION['(ak0)'] AND $qtr<=4) or allowedToOpen(1614,'1rtc'))?'<a href="storeeos.php?subtitle='.$subtitle.'&w=EditQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'">'.$imgedit.'</a> <a href="storeeos.php?go='.$isrock.'&w=DeleteQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">'.$imgdel.'</a> <a href="storeeos.php?go='.$isrock.'&w=CancelQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Cancel?\');">'.$imgcancel.'</a>':'').'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	
	echo '</tr>';
	
	echo '</table>';
	echo '</div>';
	
	echo '</div>';
	
	echo '</div>';
	
	$isrock=1; //rocks
	$subtitle='Canceled Rocks';
	
	echo '</br></br><h2>'.$subtitle.'</h2><br>';
	$addlcond='';
	
	echo '<div class="tabs">';
	
	$sqlmain='select vqs.TimeStamp as TimeStamp,RockOrIssues,CONCAT(Nickname,\' \',SurName) as BranchOrSupervisor from eos_2storerocksissuestodo vqs join 1employees e on e.IDNo=vqs.BranchOrSupervisor  WHERE Batch='.$_SESSION['batch'].' AND IsRock='.$isrock.' AND `Stat`=1
	UNION 
	select vqs.TimeStamp as TimeStamp,RockOrIssues,Branch as BranchOrSupervisor from eos_2storerocksissuestodo vqs join 1branches b on b.BranchNo=vqs.BranchOrSupervisor WHERE Batch='.$_SESSION['batch'].' AND IsRock='.$isrock.' AND `Stat`=1';
	// echo $sqlmain; exit();
	
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	
	$sql=$sqlmain.' ORDER BY TimeStamp DESC';
	// echo $sql; exit();
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th>BranchOrSupervisor</th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.$ress['BranchOrSupervisor'].'</td></tr>';
		}
		
	echo'</table>';
	
	echo '</div>';
	echo '</div>';
	
	echo '</div>';
	
	break;
	
	case'EncodeProcessMeasurables':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
	if($_POST['CountedforEval']=='Counted'){
		$countedforeval=1;
	}else{
		$countedforeval=0;
	}
	
	if($_POST['HigherOrLower']=='Higher is Better'){
			$hol=0;
		} else {
			$hol=-1;
		}
		$sql='Insert into eos_2storescorecard set Measurables=\''.$_POST['Measurables'].'\',Batch='.$_SESSION['batch'].',Goal=\''.$_POST['Goal'].'\',BranchOrSupervisor=\''.$_POST['BranchOrSupervisor'].'\',HigherOrLower=\''.$hol.'\',CountedforEval=\''.$countedforeval.'\' ';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:storeeos.php?w=ScorecardList");
	break;

	case'EncodeProcessQtr':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$prioritysql='';
	if($_GET['IsRock']==0){
		$prioritysql='Priority='.$_POST['Priority'].',';
		
	}
		$sql='Insert into eos_2storerocksissuestodo set '.$prioritysql.'IsRock='.$_GET['IsRock'].',RockOrIssues=\''.$_POST['Rocks'].'\',VTOQtrId=\''.$_POST['Quarter'].'\',BranchOrSupervisor=\''.$_POST['BranchOrSupervisor'].'\',Batch='.$_SESSION['batch'].',EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW();';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		if($_GET['IsRock']==1){
			$go='Rocks';
		} else if($_GET['IsRock']==0){
			$go='Issues';
		} else {
			$go='ToDo';
		}
		header("Location:storeeos.php?w=".$go."");
	break;	
	
	case 'SwitchBatch':
		$_SESSION['batch']=$_POST['batch'];
	header('Location:storeeos.php?w='.$_GET['which'].'');
	
	break;	
		
	case'Batch':
	$title='Branch Batch';
	$formdesc='</i></br><form method="post" action="storeeos.php?w=AddBatch">
		Batch:&nbsp; <input type="text" name="Batch" size="1">
		Branch:&nbsp; <input type="text" name="Branch" list="branches" size="10">
		Attendee:&nbsp; <input type="text" name="Attendee" list="attendees">
		Supervisor:&nbsp; <input type="text" name="Supervisor" list="supervisors">
		<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		<input type="submit" name="submit">
	</form>';
	$sql1='select Batch,concat("Batch: ",Batch) as BatchValue from eos_2batches group by Batch';
	$sql2='select b.TxnID,Batch,Branch,
	CONCAT(e1.Nickname,\' \',e1.SurName) as Supervisor,
	CONCAT(e2.Nickname,\' \',e2.SurName) as Attendee
	from eos_2batches b left join 1branches br on br.BranchNo=b.BranchNo
	left join 1employees e1 on e1.IDNo=b.Supervisor
	left join 1employees e2 on e2.IDNo=b.Attendee';
	$columnnames1=array('BatchValue');
	$columnnames2=array('Branch','Attendee','Supervisor');
	$groupby='Batch';
	$orderby='';
	$txnidname='TxnID';
	$delprocess='storeeos.php?w=DeleteBatch&TxnID=';
	$delprocesslabel='Delete';
	$editprocess='storeeos.php?w=EditBatch&TxnID=';
	$editprocesslabel='Edit';
	
	include('../backendphp/layout/displayastablewithsub.php');
			
		
	break;
	
	case'EditBatch':
	$txnid = intval($_GET['TxnID']);
		$sql='select Batch,Branch,
	CONCAT(e1.Nickname,\' \',e1.SurName) as Supervisor,
	CONCAT(e2.Nickname,\' \',e2.SurName) as Attendee from eos_2batches b left join 1branches br on br.BranchNo=b.BranchNo
	left join 1employees e1 on e1.IDNo=b.Supervisor
	left join 1employees e2 on e2.IDNo=b.Attendee where b.TxnID=\''.$txnid.'\'';
		$stmt=$link->query($sql); $result=$stmt->fetch();
		
	echo'</br><title>Edit?</title><h3>Edit?</h3><form method="post" action="storeeos.php?w=EditBatchProcess&TxnID='.$txnid.'">
		Batch:&nbsp; <input type="text" name="Batch" size="1" value="'.$result['Batch'].'">
		Branch:&nbsp; <input type="text" name="Branch" list="branches" size="10" value="'.$result['Branch'].'">
		Attendee:&nbsp; <input type="text" name="Attendee" list="attendees" value="'.$result['Attendee'].'">
		Supervisor:&nbsp; <input type="text" name="Supervisor" list="supervisors" value="'.$result['Supervisor'].'">
		<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		<input type="submit" name="submit" value="Edit">
	</form>';
	
	break;
	
	case'EditBatchProcess':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnid = intval($_GET['TxnID']);			
		$sql='update eos_2batches set Batch=\''.$_POST['Batch'].'\', BranchNo=\''.$branchno.'\', Supervisor=\''.$supervisor.'\',Attendee=\''.$attendee.'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', Timestamp=Now() where TxnID=\''.$txnid.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: storeeos.php?w=Batch');
	break;
	
	case'DeleteBatch':
	$txnid = intval($_GET['TxnID']);
		$sql='DELETE FROM `eos_2batches` WHERE TxnID=\''.$txnid.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: storeeos.php?w=Batch');		
	break;
	
	case'AddBatch':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';	
	$branchno=companyandbranchValue($link,'1branches','Branch',addslashes($_POST['Branch']),'BranchNo');		
		$sql='insert into eos_2batches set Batch=\''.$_POST['Batch'].'\', BranchNo=\''.$branchno.'\', Supervisor=\''.$supervisor.'\',Attendee=\''.$attendee.'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', Timestamp=Now()';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: storeeos.php?w=Batch');
	break;
	
	case 'Traction':
	$title='Measurables Summary';
    echo '<title>Measurables Summary</title>';
    echo '<h3>'.$title.'</h3><br>';
	
	echo '<form method="post" action="storeeos.php?w=Traction">
		<b>BranchOrSupervisor:</b> <input type="text" name="BranchOrSupervisor" list="branchesorsupervisors" size="10">
		<input type="submit" name="Lookup" value="Lookup">
	</form><br>';
	
	// $addlcond='';
	if(isset($_POST['Lookup'])){
		$branchorsupervisor=$_POST['BranchOrSupervisor'];
		$addlcond=' BranchOrSupervisor='.$branchorsupervisor.'';
		$showedit=0;
	} else {
		$addlcond=' BranchOrSupervisor='.$branchorsupervisor.'';
		$showedit=1;
	}
	
	$sqls='select br.Branch, br.BranchNo from eos_2batches b join 1branches br on br.BranchNo=b.BranchNo where b.BranchNo=\''.$branchorsupervisor.'\' 
	UNION 
	select CONCAT(Nickname,\' \',SurName) as Fullname,Supervisor from eos_2batches b join 1employees e on e.IDNo=b.Supervisor where Supervisor=\''.$branchorsupervisor.'\' Group by Supervisor';
	
	$stmts=$link->query($sqls); $results=$stmts->fetch();
	echo '<div class="tabs">';
	
	if (allowedToOpen(1619,'1rtc') and !isset($_POST['Lookup'])) {
		$sqlse='select CONCAT(Nickname,\' \',SurName) as Fullname from 1employees where IDNo=\''.$_SESSION['(ak0)'].'\'';
		$stmtse=$link->query($sqlse); $resultse=$stmtse->fetch();
		$results['Branch']=$resultse['Fullname'];
	}
	echo '<br><h3>BranchOrSupervisor: '.$results['Branch'].'</h3>';
	
	
	$subtitle='Scorecard Measurables';
	//scorecards
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	$sql='select * from eos_2storescorecard m WHERE Batch=\''.$_SESSION['batch'].'\' AND '.$addlcond.'';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th>Goal Per Month</th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['Measurables'].'</td><td style="padding:3px;">'.$ress['Goal'].'</td></tr>';
		}
		
	echo'</table></div></div></div';
	
	$subtitle='Rocks';
	//rocks
	$isrock=1;
	echo '<div class="tabs">';
	
	$sqlmain='select vqs.*,IF(RIGHT(RIStatPerMonth,1)=1,"green","red") as bullcolor from eos_2storerocksissuestodo vqs WHERE Batch=\''.$_SESSION['batch'].'\' AND Stat=0 AND IsRock='.$isrock.' ';
	echo '<br>';
	echo '<div class="tab-content">';
	
	$hl='style="background-color:yellow;"';
	$hlt='background-color:#FFFACD;';
	echo '<div id="tab5" class="tab active">';
	
	echo '<table style="width:100%;">';
	echo '<tr>';
		echo '<th '.($qtr==1?$hl:'').'>1st Quarter</th>';
		echo '<th '.($qtr==2?$hl:'').'>2nd Quarter</th>';
		echo '<th '.($qtr==3?$hl:'').'>3rd Quarter</th>';
		echo '<th '.($qtr==4?$hl:'').'>4th Quarter</th>';
	echo '</tr>';
	echo '<tr>';
		echo '<td valign="top" style="width:25%;'.($qtr==1?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=1 AND '.$addlcond.'';
		// echo $addlcond;
		// echo $sql; exit();
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	echo '<td valign="top" valign="top" style="width:25%;'.($qtr==2?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=2  AND '.$addlcond.'';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	echo '<td valign="top" valign="top" style="width:25%;'.($qtr==3?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=3  AND '.$addlcond.'';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	echo '<td valign="top" valign="top" style="width:25%;'.($qtr==4?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=4  AND '.$addlcond.'';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	
	echo '</tr>';
	
	echo '</table>';
	echo '</div>';
	
	echo '</div>';
	
	echo '</div>';
	
	//todo
	$isrock=2; //todo
	$subtitle='ToDo';
	
	echo '<div class="tabs">
		';
	
	$sqlmain='select * from eos_2storerocksissuestodo vqs WHERE Batch=\''.$_SESSION['batch'].'\' AND IsRock='.$isrock.' AND `Stat`=0';
	
	echo '<br>';
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	$sql=$sqlmain.' AND '.$addlcond.' AND (RIGHT(RIStatPerMonth,1)=0 OR RIGHT(RIStatPerMonth,1) IS NULL)';
	// echo $sql; exit();
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td></tr>';
		}
		
	echo'</table>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	
	//issues
	
	echo '<div class="tabs">
		';
	$subtitle='Issues';
	$isrock=0;
	$sqlmain='select vqs.* from eos_2storerocksissuestodo vqs WHERE Batch=\''.$_SESSION['batch'].'\' AND IsRock='.$isrock.' AND `Stat`=0';
	
	echo '<br>';
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	$sql=$sqlmain.' AND '.$addlcond.' AND (RIGHT(RIStatPerMonth,1)=0 OR RIGHT(RIStatPerMonth,1) IS NULL) ORDER BY VTOQtrId,Priority';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th align="center">'.$subtitle.'</th><th>Priority</th></tr>';
		foreach($result as $ress){
			echo'<tr style="'.($ress['VTOQtrId']==$qtr?'background-color:#FFFACD;':'').'"><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.$ress['Priority'].'</td></tr>';
		}
		
	echo'</table>';
	
	echo '</div>';
	echo '</div>';
	echo '</div>';
	
    break;
	
	}
?>
		
	