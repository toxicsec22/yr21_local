<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(59050,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');

$which=(!isset($_GET['w'])?'PositionList':$_GET['w']);

include_once('../backendphp/layout/linkstyle.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

echo '<br>';
echo '<a id="link" href="jobratingplan.php?w=List">Percentage Weights</a> ';
echo '<a id="link" href="jobratingplan.php?w=PositionList">Job Rating Summary</a> ';
echo '<br><br>';

if (in_array($which,array('PositionList','AddEditPosition'))){
	if((allowedToOpen(59053,'1rtc'))){
		$deptcondi='';
	}
	else {
		$deptcondi=' '.(isset($_POST['btnFilter'])?'AND':'WHERE').' PositionID IN (select PositionID FROM attend_1positions p JOIN 1departments d ON p.deptid=d.deptid WHERE deptheadpositionid='.$_SESSION['&pos'].')';
	}
}
if (in_array($which,array('LookupPoints','LookupJobGrade','PositionList'))){
	$formulaname='';
	if($which=='LookupJobGrade'){
		$sqlp='SELECT jrp.WJRID,FormulaName,Posted,PostedTS,PostedByNo FROM hr_1jobratingplan jrp JOIN hr_1jobratingweights jrw ON jrp.WJRID=jrw.WJRID WHERE PositionID='.intval($_GET['PositionID']);
		$stmtp=$link->query($sqlp); $rowp=$stmtp->fetch();
		$wjrid=$rowp['WJRID'];
		$formulaname=$rowp['FormulaName'];
		$posted=$rowp['Posted'];
		$postedbyno=$rowp['PostedByNo'];
		$postedts=$rowp['PostedTS'];
	} else if($which=='LookupPoints') { 
		$wjrid=$_GET['WJRID'];
	}
	
	$othercolumns='ROUND((SELECT MaxPoints)*.1,0) AS `1`,
	
	IF((2<(SELECT NoOfDegrees)),(ROUND(((SELECT MaxPoints)*0.9)/((SELECT NoOfDegrees)-1)+(SELECT `1`),0)),IF((2>(SELECT NoOfDegrees)),0,(SELECT MaxPoints))) AS `2`,
	
	IF((3<(SELECT NoOfDegrees)),(ROUND(((SELECT MaxPoints)*0.9)/((SELECT NoOfDegrees)-1)+(SELECT `2`),0)),IF((3>(SELECT NoOfDegrees)),0,(SELECT MaxPoints))) AS `3`,
	
	IF((4<(SELECT NoOfDegrees)),(ROUND(((SELECT MaxPoints)*0.9)/((SELECT NoOfDegrees)-1)+(SELECT `3`),0)),IF((4>(SELECT NoOfDegrees)),0,(SELECT MaxPoints))) AS `4`,
	
	IF((5<(SELECT NoOfDegrees)),(ROUND(((SELECT MaxPoints)*0.9)/((SELECT NoOfDegrees)-1)+(SELECT `4`),0)),IF((5>(SELECT NoOfDegrees)),0,(SELECT MaxPoints))) AS `5`,
	
	IF((6<(SELECT NoOfDegrees)),(ROUND(((SELECT MaxPoints)*0.9)/((SELECT NoOfDegrees)-1)+(SELECT `5`),0)),IF((6>(SELECT NoOfDegrees)),0,(SELECT MaxPoints))) AS `6`

	FROM hr_1jobratingweights';
	
	if($which=='PositionList'){
		$wherecond=' GROUP BY WJRID';
	} else {
		$wherecond=' WHERE WJRID='.$wjrid.'';
	}
	
	$sql0='
	CREATE TEMPORARY TABLE formulatable AS
	SELECT 1 as cnt, WJRID,"Education" AS C,WA1 AS PercentWeight,(WA1*10) AS MaxPoints,5 AS NoOfDegrees,'.$othercolumns.$wherecond.'
	UNION ALL SELECT 2 as cnt, WJRID,"Experience",WA2,(WA2*10) AS MaxPoints,6 AS NoOfDegrees,'.$othercolumns.$wherecond.' 
	UNION ALL SELECT 3 as cnt, WJRID,"Technical Skills/Functional Skills",WA3,(WA3*10) AS MaxPoints,5 AS NoOfDegrees,'.$othercolumns.$wherecond.'
	UNION ALL SELECT 4 as cnt, WJRID,"Analysis & Problem Solving",WB4,(WB4*10) AS MaxPoints,5 AS NoOfDegrees,'.$othercolumns.$wherecond.'
	UNION ALL SELECT 5 as cnt, WJRID,"Nature of External Relations",WB5,(WB5*10) AS MaxPoints,5 AS NoOfDegrees,'.$othercolumns.$wherecond.'
	UNION ALL SELECT 6 as cnt, WJRID,"Nature of Internal Relations",WB6,(WB6*10) AS MaxPoints,3 AS NoOfDegrees,'.$othercolumns.$wherecond.'
	UNION ALL SELECT 7 as cnt, WJRID,"Complexity of Work",WB7,(WB7*10) AS MaxPoints,4 AS NoOfDegrees,'.$othercolumns.$wherecond.'
	UNION ALL SELECT 8 as cnt, WJRID,"Physical Effort /Working Conditions",WB8,(WB8*10) AS MaxPoints,3 AS NoOfDegrees,'.$othercolumns.$wherecond.'
	UNION ALL SELECT 9 as cnt, WJRID,"Planning and Controlling",WB9,(WB9*10) AS MaxPoints,5 AS NoOfDegrees,'.$othercolumns.$wherecond.'
	UNION ALL SELECT 10 as cnt, WJRID,"Company Assets",WB10,(WB10*10) AS MaxPoints,5 AS NoOfDegrees,'.$othercolumns.$wherecond.'
	UNION ALL SELECT 11 as cnt, WJRID,"Confidentiality",WB11,(WB11*10) AS MaxPoints,3 AS NoOfDegrees,'.$othercolumns.$wherecond.'
	UNION ALL SELECT 12 as cnt, WJRID,"Leading and Responsibility",WB12,(WB12*10) AS MaxPoints,5 AS NoOfDegrees,'.$othercolumns.$wherecond.'
	UNION ALL SELECT 13 as cnt, WJRID,"Contribution to Organization",WB13,(WB13*10) AS MaxPoints,4 AS NoOfDegrees,'.$othercolumns.$wherecond.'';
	
   $stmt0=$link->prepare($sql0); $stmt0->execute();
   
   
   
}

switch ($which)
{

	case 'List':
	if (!allowedToOpen(array(59051,59052),'1rtc')) { echo 'No permission'; exit; }
		$title='Percentage Weights';
               
				 $sql='SELECT *,`WA1` AS Education,`WA2` AS Experience,`WA3` AS `Technical Skills / Functional Skills`,`WB4` AS `Analysis & Problem Solving`,`WB5` AS `Nature of External Relations`,`WB6` AS `Nature of Internal Relations`,`WB7` AS `Complexity of Work`,`WB8` AS `Physical Effort /Working Conditions`,`WB9` AS `Planning and Controlling`,`WB10` AS `Company Assets`,`WB11` AS `Confidentiality`,`WB12` AS `Leading and Responsibility`,`WB13` AS `Contribution to Organization`, WJRID AS TxnID,(WA1+WA2+WA3+WB4+WB5+WB6+WB7+WB8+WB9+WB10+WB11+WB12+WB13) AS TotalWeight FROM hr_1jobratingweights';
				 
				 
				 
				 
				 $stmt=$link->query($sql); $rows=$stmt->fetchAll();
	
	echo '<title>'.$title.'</title>';
	
	if (allowedToOpen(59051,'1rtc')){
		echo '<b><a href="jobratingplan.php?w=AddEditFormula">Add New Formula</a></b><br><br>';
	}
	echo '<h3>'.$title.'</h3>';
	echo '<table style="border-collapse:collapse;font-size:9pt;background-color:#fff;" border="1px solid black">';
	echo '<tr><th></th><th style="background-color:#c9ee82;padding:3px;" colspan=3>A. Skills & Knowledge</th><th style="background-color:#fed8b1;padding:3px;" colspan="12">B. Effort</th></tr>';
	echo '<tr><td style="padding:3px;"><b>Formula Name</b></td><td style="background-color:#c9ee82;padding:3px;">Education</td><td style="background-color:#c9ee82;padding:3px;">Experience</td><td style="background-color:#c9ee82;padding:3px;">Technical Skills / Functional Skills</td><td style="background-color:#fed8b1;padding:3px;" >Analysis & Problem Solving</td><td style="background-color:#fed8b1;padding:3px;">Nature of External Relations</td><td style="background-color:#fed8b1;padding:3px;">Nature of Internal Relations</td><td style="background-color:#fed8b1;padding:3px;">Complexity of Work</td><td style="background-color:#fed8b1;padding:3px;">Physical Effort /Working Conditions</td><td style="background-color:#fed8b1;padding:3px;">Planning and Controlling</td><td style="background-color:#fed8b1;padding:3px;">Company Assets</td><td style="background-color:#fed8b1;padding:3px;">Confidentiality</td><td style="background-color:#fed8b1;padding:3px;">Leading and Responsibility</td><td style="background-color:#fed8b1;padding:3px;">Contribution to Organization</td><td style="background-color:#dd82ee;padding:3px;"><b>Total</b></td><td></td></tr>';
	$colorcount=0;
	$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
	$rcolor[1]="FFFFFF";
		
	foreach($rows AS $row){
		echo '<tr bgcolor="'. $rcolor[$colorcount%2].'" style="text-align:center;"><td style="text-align:left;padding:3px;width:85px;"><b>'.$row['FormulaName'].'</b></td><td>'.$row['Education'].'</td><td>'.$row['Experience'].'</td><td>'.$row['Technical Skills / Functional Skills'].'</td><td>'.$row['Analysis & Problem Solving'].'</td><td>'.$row['Nature of External Relations'].'</td><td>'.$row['Nature of Internal Relations'].'</td><td>'.$row['Complexity of Work'].'</td><td>'.$row['Physical Effort /Working Conditions'].'</td><td>'.$row['Planning and Controlling'].'</td><td>'.$row['Company Assets'].'</td><td>'.$row['Confidentiality'].'</td><td>'.$row['Leading and Responsibility'].'</td><td>'.$row['Contribution to Organization'].'</td><td style="background-color:#dd82ee;'.($row['TotalWeight']<>100?'color:red;':'').'"><b>'.$row['TotalWeight'].'</b></td><td style="width:90px;"><a href="jobratingplan.php?w=LookupPoints&WJRID='.$row['TxnID'].'">Lookup</a> '.((allowedToOpen(59051,'1rtc'))?' <a href="jobratingplan.php?w=AddEditFormula&edit=1&WJRID='.$row['TxnID'].'">Edit</a> <a href="jobratingplan.php?w=DelFormula&action_token='.$_SESSION['action_token'].'&WJRID='.$row['TxnID'].'" OnClick="return confirm(\'Really delete this?\');">Del</a> ':'').'</td></tr>';
		$colorcount++;

	}
	
	echo '</table>';
	
	
	break;
	
	case 'AddEditFormula':
	
	$editl='';
	$wa1=0;
	$wa2=0;
	$wa3=0;
	$wb4=0;
	$wb5=0;
	$wb6=0;
	$wb7=0;
	$wb8=0;
	$wb9=0;
	$wb10=0;
	$wb11=0;
	$wb12=0;
	$wb13=0;
	$formulaname='';
	
	
	if(isset($_GET['edit'])){
		$act='Edit';
		$editl='&edit=1&WJRID='.$_GET['WJRID'].'';
		
		$sqlf='SELECT * FROM hr_1jobratingweights WHERE WJRID='.intval($_GET['WJRID']);
		$stmtf=$link->query($sqlf); $rowf=$stmtf->fetch();
		
		$wa1=$rowf['WA1'];
		$wa2=$rowf['WA2'];
		$wa3=$rowf['WA3'];
		$wb4=$rowf['WB4'];
		$wb5=$rowf['WB5'];
		$wb6=$rowf['WB6'];
		$wb7=$rowf['WB7'];
		$wb8=$rowf['WB8'];
		$wb9=$rowf['WB9'];
		$wb10=$rowf['WB10'];
		$wb11=$rowf['WB11'];
		$wb12=$rowf['WB12'];
		$wb13=$rowf['WB13'];
		$formulaname=$rowf['FormulaName'];
		
	} else {
		$act='Add';
	}
	
	
	
	
	

		
		

	
	
	$title=''.$act.' Formula';
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';
	if (allowedToOpen(59051,'1rtc')){
		echo '<br><form action="jobratingplan.php?w=PrAddEditFormula'.$editl.'&action_token='.$_SESSION['action_token'].'" method="POST" autocomplete="off">';
			echo 'Formula Name: <input type="text" size="30" name="FormulaName" value="'.$formulaname.'"><br>';
			echo '<br><div style="background-color:#c9ee82;padding:3px;width:30%;">';
			echo '<b>A. Skills & Knowledge</b><br>';
			echo '1. Education: <input type="text" size="4" name="WA1" value="'.$wa1.'"><br>';
			echo '2. Experience: <input type="text" size="4" name="WA2" value="'.$wa2.'"><br>';
			echo '3. Technical Skills / Functional Skills: <input type="text" size="4" name="WA3" value="'.$wa3.'"></div>';
			echo '<div style="background-color:#fed8b1;padding:3px;width:30%;">';
			echo '<b>B. Effort</b><br>';
			echo '4. Analysis & Problem Solving: <input type="text" size="4" name="WB4" value="'.$wb4.'"><br>';
			echo '5. Nature of External Relations: <input type="text" size="4" name="WB5" value="'.$wb5.'"><br>';
			echo '6. Nature of Internal Relations: <input type="text" size="4" name="WB6" value="'.$wb6.'"><br>';
			echo '7. Complexity of Work: <input type="text" size="4" name="WB7" value="'.$wb7.'"><br>';
			echo '8. Physical Effort /Working Conditions: <input type="text" size="4" name="WB8" value="'.$wb8.'"><br>';
			echo '9. Planning and Controlling: <input type="text" size="4" name="WB9" value="'.$wb9.'"><br>';
			echo '10. Company Assets: <input type="text" size="4" name="WB10" value="'.$wb10.'"><br>';
			echo '11. Confidentiality: <input type="text" size="4" name="WB11" value="'.$wb11.'"><br>';
			echo '12. Leading and Responsibility: <input type="text" size="4" name="WB12" value="'.$wb12.'"><br>';
			echo '13. Contribution to Organization: <input type="text" size="4" name="WB13" value="'.$wb13.'"></div>';
			echo '<input type="submit" value="'.$act.' Formula" name="btnAddFormula" OnClick="return confirm(\'Is this Final?\');">';
		echo '</form><br>';
	}
	
	
	
	break;
	
	
	
	case 'LookupPoints':
	if (!allowedToOpen(array(59051,59052),'1rtc')) { echo 'No permission'; exit; }
	
	$title='Formula Table';
	$sql='SELECT * FROM formulatable';
	
	$stmt=$link->query($sql); $rows=$stmt->fetchAll();
	echo '<title>'.$title.'</title>';
	echo '<h3><font color="green"><u>'.comboBoxValue($link,'hr_1jobratingweights','WJRID',$_GET['WJRID'],'FormulaName').'</u></font> '.$title.'</h3>';
	echo '<table border="1px solid black" style="font-size:9.5pt;border-collapse:collapse;background-color:#fff;">';
	echo '<tr><th style="padding:5px;"></th><th style="padding:5px;">PercentWeight</th><th style="padding:5px;">MaxPoints</th><th style="padding:5px;">NoOfDegrees</th><th style="padding:5px;">1</th><th style="padding:5px;">2</th><th style="padding:5px;">3</th><th style="padding:5px;">4</th><th style="padding:5px;">5</th><th style="padding:5px;">6</th></tr>';
	
	$totalweight=0;
	foreach($rows AS $row){
		if($row['cnt']==1){
			echo '<tr><td colspan="10" style="background-color:#c9ee82;padding:5px;"><b>A. Skills & Knowledge</b>
		</td></tr>';
		}
		if($row['cnt']==4){
			echo '<tr><td colspan="10" style="background-color:#fed8b1;padding:5px;"><b>B. Effort</b>
		</td></tr>';
		}
		echo '<tr style="text-align:center;"><td style="padding:5px;text-align:left;">'.$row['C'].'</td><td style="padding:5px;">'.$row['PercentWeight'].'</td><td style="padding:5px;">'.$row['MaxPoints'].'</td><td style="padding:5px;">'.$row['NoOfDegrees'].'</td><td style="padding:5px;">'.$row['1'].'</td><td style="padding:5px;">'.$row['2'].'</td><td style="padding:5px;">'.$row['3'].'</td><td style="padding:5px;">'.$row['4'].'</td><td style="padding:5px;">'.$row['5'].'</td><td style="padding:5px;">'.$row['6'].'</td></tr>';
		$totalweight=$totalweight+$row['PercentWeight'];
	}
	echo '<tr style="background-color:#dd82ee;"><td style="padding:5px;">Total</td><td style="padding:5px;text-align:center;"><b>'.$totalweight.'</b></td><td colspan=8></td></tr>';
	echo '</table>';
	break;
	
	
	case 'LookupJobGrade':
	if (!allowedToOpen(59052,'1rtc')) { echo 'No permission'; exit; }
	$positionid=$_GET['PositionID'];
	
	$cntr=1; $sqlunion='CREATE TEMPORARY TABLE jobpoints AS ';
	while($cntr<=13){
		$sqlunion.='SELECT cnt,PercentWeight,C,
	(
	CASE
		WHEN '.($cntr<4?'A':'B').''.$cntr.'=1 THEN `1`
		WHEN '.($cntr<4?'A':'B').''.$cntr.'=2 THEN `2`
		WHEN '.($cntr<4?'A':'B').''.$cntr.'=3 THEN `3`
		WHEN '.($cntr<4?'A':'B').''.$cntr.'=4 THEN `4`
		WHEN '.($cntr<4?'A':'B').''.$cntr.'=5 THEN `5`
		ELSE `6`
	END)

		as Points,'.($cntr<4?'A':'B').''.$cntr.'
		as Degree FROM formulatable ft JOIN hr_1jobratingplan jrp ON ft.WJRID=jrp.WJRID WHERE ft.cnt='.$cntr.' AND PositionID='.$positionid.' UNION ALL ';
		$cntr++;
	}
	$sqlunion=substr($sqlunion, 0, -10);
	
	
	$stmtunion=$link->prepare($sqlunion); $stmtunion->execute();
	
	
	$sql='SELECT * FROM jobpoints';
	
	$stmt=$link->query($sql); $rows=$stmt->fetchAll();
	echo '<a href="jobratingplan.php?w=LookupPoints&WJRID='.$wjrid.'" target="_blank"><b>Open Formula Table</b></a><br><br>';
	$pos=comboBoxValue($link,'attend_1positions','PositionID',$_GET['PositionID'],'Position');
	$title=$pos;
	echo '<title>'.$title.'</title>';
	echo '<h3>Position: '.$title.', Formula: <font color="green"><u>'.$formulaname.'</u></font></h3>';
	$btnstyle=' style="background-color:maroon;color:white;padding:2px;width:100px;" OnClick="return confirm(\'Are you sure?\');"';
	echo '<br><form action="jobratingplan.php?w=PostUnpost&PositionID='.$_GET['PositionID'].'&action_token='.$_SESSION['action_token'].'" method="POST">';
	// echo $postedbyno;
	$postedby=($posted==1?'<font style="color:orange;"><u><b>Posted by: '.comboBoxValue($link,'1employees','IDNo',$postedbyno,'Nickname').', PostedTS: '.$postedts.'</b></u></font>':'');
	if($posted==0){
		echo '<input type="submit" value="POST" name="btnPost" '.$btnstyle.'>';
	} else if($posted==1 AND (allowedToOpen(59053,'1rtc'))) {
		echo '<input type="submit" value="UNPOST" name="btnUnPost"  '.$btnstyle.'>';
		echo '<br>'.$postedby;
	} else {
		echo $postedby;
	}
	
	
	echo '</form>';
	
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:#fff;">';
	$totalpoints=0;
	foreach($rows AS $row){
		if($row['cnt']==1){
			echo '<tr><td colspan="4" style="background-color:#c9ee82;padding:5px;"><b>A. Skills & Knowledge</b>
		</td></tr>';
			echo '<tr><td></td><td style="padding:5px;">PercentWeight</td><td style="padding:5px;">Degree</td><td style="padding:5px;">Converted Points</td></tr>';
		}
		if($row['cnt']==4){
			echo '<tr><td colspan="4" style="background-color:#fed8b1;padding:5px;"><b>B. Effort</b>
		</td></tr>';
		}
		
		echo '<tr><td style="padding:5px;">'.$row['C'].'</td><td style="padding:5px;">'.$row['PercentWeight'].'</td><td style="padding:5px;">'.$row['Degree'].'</td><td style="padding:5px;">'.$row['Points'].'</td></tr>';
		$totalpoints=$totalpoints+$row['Points'];
	}
	
	echo '<tr style="background-color:#dd82ee;"><td colspan="3"></td><td style="padding:5px;"><b>'.$totalpoints.'</b></td></tr>';
	echo '</table>';
	
	break;
	
	
	
	case 'PositionList':
	if (!allowedToOpen(59052,'1rtc')) { echo 'No permission'; exit; }
	
	$title='Job Rating Summary';
	
	
	$totalp=''; $cnts=1;
	while($cnts<=13){
		$totalp.='(SELECT (CASE 
WHEN '.($cnts<4?'A':'B').''.$cnts.'=1 THEN `1`
WHEN '.($cnts<4?'A':'B').''.$cnts.'=2 THEN `2`
WHEN '.($cnts<4?'A':'B').''.$cnts.'=3 THEN `3`
WHEN '.($cnts<4?'A':'B').''.$cnts.'=4 THEN `4`
WHEN '.($cnts<4?'A':'B').''.$cnts.'=5 THEN `5`
ELSE `6`
END) FROM formulatable WHERE WJRID=jrp.WJRID AND cnt='.$cnts.')+';
$cnts++;
	}
	$totalp=substr($totalp, 0, -1);
	
	
	$sql0='CREATE TEMPORARY TABLE tempPoints AS SELECT '.$totalp.' AS `Points`,FormulaName,`A1` AS Education,`A2` AS Experience,`A3` AS `Technical Skills / Functional Skills`,`B4` AS `Analysis & Problem Solving`,`B5` AS `Nature of External Relations`,`B6` AS `Nature of Internal Relations`,`B7` AS `Complexity of Work`,`B8` AS `Physical Effort /Working Conditions`,`B9` AS `Planning and Controlling`,`B10` AS `Company Assets`,`B11` AS `Confidentiality`,`B12` AS `Leading and Responsibility`,`B13` AS `Contribution to Organization`, jrp.PositionID AS TxnID,jrp.PositionID,Position,jrp.EncodedByNo,jrp.Posted FROM hr_1jobratingplan jrp JOIN attend_1positions p ON jrp.PositionID=p.PositionID JOIN hr_1jobratingweights jrw ON jrp.WJRID=jrw.WJRID';
	$stmt0=$link->prepare($sql0); $stmt0->execute();
	
	$filterc='';
	if(isset($_POST['btnFilter'])){
		$filterc=' WHERE `Points` BETWEEN ('.$_POST['MinPoint'].' AND '.$_POST['MaxPoint'].') ';
	}
	
	
	
	
	$sql='SELECT * FROM tempPoints '.$filterc.$deptcondi.' ORDER BY `Position`';
	// echo $sql; exit();
	$stmt=$link->query($sql); $rows=$stmt->fetchAll();
	
	
	
	echo '<title>'.$title.'</title>';
	echo '<b><a href="jobratingplan.php?w=AddEditPosition">Add New Position</a></b><br><br>';
	echo '<h3>'.$title.'</h3>';
	
	echo '<br><form action="#" method="POST" autocomplete="off">MinPoint: <input type="text" size="5" name="MinPoint" placeholder="0" value="'.(isset($_POST['MinPoint'])?$_POST['MinPoint']:'').'"> MaxPoint: <input type="text" size="5" name="MaxPoint" placeholder="300" value="'.(isset($_POST['MaxPoint'])?$_POST['MaxPoint']:'').'"> <input type="submit" name="btnFilter" value="Filter"> <input type="submit" name="btnAll" value="Show All"></form><br>';
	echo '<table style="border-collapse:collapse;font-size:9pt;background-color:#fff;" border="1px solid black">';
	echo '<tr><th colspan=3></th><th style="background-color:#c9ee82;padding:3px;" colspan=3>A. Skills & Knowledge</th><th style="background-color:#fed8b1;padding:3px;" colspan="12">B. Effort</th></tr>';
	echo '<tr><td style="padding:3px;"><b>Position</b></td><td style="padding:3px;"><b>Points</b></td><td style="padding:3px;"><b>Formula</b></td><td style="background-color:#c9ee82;padding:3px;">Education<br><b>[1-5]</b></td><td style="background-color:#c9ee82;padding:3px;">Experience<br><b>[1-6]</b></td><td style="background-color:#c9ee82;padding:3px;">Technical Skills / Functional Skills<br><b>[1-5]</b></td><td style="background-color:#fed8b1;padding:3px;" >Analysis & Problem Solving<br><b>[1-5]</b></td><td style="background-color:#fed8b1;padding:3px;">Nature of External Relations<br><b>[1-5]</b></td><td style="background-color:#fed8b1;padding:3px;">Nature of Internal Relations<br><b>[1-3]</b></td><td style="background-color:#fed8b1;padding:3px;">Complexity of Work<br><b>[1-4]</b></td><td style="background-color:#fed8b1;padding:3px;">Physical Effort /Working Conditions<br><b>[1-2]</b></td><td style="background-color:#fed8b1;padding:3px;">Planning and Controlling<br><b>[1-5]</b></td><td style="background-color:#fed8b1;padding:3px;">Company Assets<br><b>[1-5]</b></td><td style="background-color:#fed8b1;padding:3px;">Confidentiality<br><b>[1-3]</b></td><td style="background-color:#fed8b1;padding:3px;">Leading and Responsibility<br><b>[1-5]</b></td><td style="background-color:#fed8b1;padding:3px;">Contribution to Organization<br><b>[1-4]</b></td><td style="background-color:#fed8b1;padding:3px;">Posted</td><td style="background-color:#fed8b1;padding:3px;"></td></tr>';
	$colorcount=0;
	$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
	$rcolor[1]="FFFFFF";
		
	foreach($rows AS $row){
		echo '<tr bgcolor="'. $rcolor[$colorcount%2].'" style="text-align:center;"><td style="text-align:left;padding:3px;width:85px;"><b>'.$row['Position'].'</b></td><td style="text-align:left;padding:3px;width:85px;"><b>'.$row['Points'].'</b></td><td style="text-align:left;padding:3px;width:85px;"><b>'.$row['FormulaName'].'</b></td><td>'.$row['Education'].'</td><td>'.$row['Experience'].'</td><td>'.$row['Technical Skills / Functional Skills'].'</td><td>'.$row['Analysis & Problem Solving'].'</td><td>'.$row['Nature of External Relations'].'</td><td>'.$row['Nature of Internal Relations'].'</td><td>'.$row['Complexity of Work'].'</td><td>'.$row['Physical Effort /Working Conditions'].'</td><td>'.$row['Planning and Controlling'].'</td><td>'.$row['Company Assets'].'</td><td>'.$row['Confidentiality'].'</td><td>'.$row['Leading and Responsibility'].'</td><td>'.$row['Contribution to Organization'].'</td><td>'.$row['Posted'].'</td><td style="width:90px;"><a href="jobratingplan.php?w=LookupJobGrade&PositionID='.$row['TxnID'].'">Lookup</a>'.(($row['EncodedByNo']==$_SESSION['(ak0)'] OR (allowedToOpen(59053,'1rtc')))?' <a href="jobratingplan.php?w=AddEditPosition&PositionID='.$row['TxnID'].'">Edit</a> <a href="jobratingplan.php?w=DelPosition&action_token='.$_SESSION['action_token'].'&PositionID='.$row['TxnID'].'" OnClick="return confirm(\'Really delete this?\');">Del</a>':'').'</td></tr>';
		$colorcount++;
	}
	echo '</table>';
	
	break;
	
	case 'AddEditPosition':
	if (!allowedToOpen(59052,'1rtc')) { echo 'No permission'; exit; }
	$title='Add New Position';
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';
	
	$editl='';
	$a1='';
	$a2='';
	$a3='';
	$b4='';
	$b5='';
	$b6='';
	$b7='';
	$b8='';
	$b9='';
	$b10='';
	$b11='';
	$b12='';
	$b13='';
	$positionid='';
	$wjrid='';
	$edit=0;
	if(isset($_GET['PositionID'])){
		$editl='&edit='.$_GET['PositionID'].'';
		$sqlf='SELECT * FROM hr_1jobratingplan WHERE PositionID='.intval($_GET['PositionID']);
		$stmtf=$link->query($sqlf); $rowf=$stmtf->fetch();
		
		$a1=$rowf['A1'];
		$a2=$rowf['A2'];
		$a3=$rowf['A3'];
		$b4=$rowf['B4'];
		$b5=$rowf['B5'];
		$b6=$rowf['B6'];
		$b7=$rowf['B7'];
		$b8=$rowf['B8'];
		$b9=$rowf['B9'];
		$b10=$rowf['B10'];
		$b11=$rowf['B11'];
		$b12=$rowf['B12'];
		$b13=$rowf['B13'];
		$positionid=$rowf['PositionID'];
		$wjrid=$rowf['WJRID'];
		$edit=1;
	}
	
	echo '<form action="jobratingplan.php?w=PrAddEditPosition'.$editl.'&action_token='.$_SESSION['action_token'].'" method="POST">';
	echo '<div style="border:2px solid blue;padding:5px;background-color:#ffffff;">';
	
	
	
	$sqlpos='SELECT PositionID,Position FROM attend_1positions '.$deptcondi.' ORDER BY Position';
	$stmtpos=$link->query($sqlpos); $rowpos=$stmtpos->fetchAll();
	
	$poslist='';
	foreach($rowpos AS $rowpo){
		$poslist.='<option value="'.$rowpo['PositionID'].'" '.($edit==1?($rowpo['PositionID']==$positionid?'selected':''):'').'>'.$rowpo['Position'].'</option>';
	}
	
	$sqlformula='SELECT WJRID,FormulaName FROM hr_1jobratingweights ORDER BY FormulaName';
	$stmtformula=$link->query($sqlformula); $rowformulas=$stmtformula->fetchAll();
	
	$formulalist='';
	foreach($rowformulas AS $rowformula){
		$formulalist.='<option value="'.$rowformula['WJRID'].'" '.($edit==1?($rowformula['WJRID']==$wjrid?'selected':''):'').'>'.$rowformula['FormulaName'].'</option>';
	}
	
	echo '<b>Position: </b><select name="PositionID" required><option value="">-- Select Position --</option>'.$poslist.'</select><br><br>';
	echo '<b>Formula: </b><select name="WJRID" required><option value="">-- Select Formula --</option>'.$formulalist.'</select><br><br>';
	echo '<div style="background-color:#c9ee82;padding:5px;">';
	echo '<h3>A. Skills & Knowledge</h3>';
	echo '<br><b>1. Education</b><br><i><font style="font-size:10pt"><b>This refers to formal education or equivalent knowledge acquired through a specialized course and licensure needed to practice a profession.</b></font></i><br>';
	echo '[1] <input type="radio" value="1" name="A1" required '.($a1==1?'checked':'').'> HS Graduate<br>';
	echo '[2] <input type="radio" value="2" name="A1" '.($a1==2?'checked':'').'> 2-yr vocational or 2 yrs college education<br>';
	echo '[3] <input type="radio" value="3" name="A1" '.($a1==3?'checked':'').'> Graduate of 4-yr or 5-yr college degree<br>';
	echo '[4] <input type="radio" value="4" name="A1" '.($a1==4?'checked':'').'> Graduate of 4-yr or 5-yr college degree plus a license to practice the profession<br>';
	echo '[5] <input type="radio" value="5" name="A1" '.($a1==5?'checked':'').'> LLB with professional license or MBA or Master\'s Degree in field of specialization<br>';
	
	echo '<br><b>2. Experience</b><br><i><font style="font-size:10pt"><b>This refers to the length of time required to gain familiarity and learn the fundamentals of the job to be able to perform the duties and responsibilities effectively under normal supervision.</b></font></i><br>';
	echo '[1] <input type="radio" value="1" name="A2" required '.($a2==1?'checked':'').'> No experience<br>';
	echo '[2] <input type="radio" value="2" name="A2" '.($a2==2?'checked':'').'> Less than a year<br>';
	echo '[3] <input type="radio" value="3" name="A2" '.($a2==3?'checked':'').'> Over a year to less than 2 years<br>';
	echo '[4] <input type="radio" value="4" name="A2" '.($a2==4?'checked':'').'> Over 2 years to 3 years experience in a similar or subordinate jobs<br>';
	echo '[5] <input type="radio" value="5" name="A2" '.($a2==5?'checked':'').'> Over 4 years to 5 years experience [2 years of which is in a supervisory/specialist/officer/professional capacity] to assure organizational maturity and integrated view of the company.<br>';
	echo '[6] <input type="radio" value="6" name="A2" '.($a2==6?'checked':'').'> Over 5 years to 7 years experience, 3 years of which is in a managerial capacity to assure exposure to a wide range of industry or field of specialization practices and professional association is required by the job.<br>';
	
	echo '<br><b>3. Technical Skills/Functional Skills</b><br><i><font style="font-size:10pt"><b>This refers to the conceptual, technical or functional skills required in the performance of the job.</b></font></i><br>';
	echo '[1] <input type="radio" value="1" name="A3" required '.($a3==1?'checked':'').'> Has basic understanding of the procedures, terminologies and regulations on own area/s of specialization by correctly and consistently performing most routine aspects of work.<br>';
	echo '[2] <input type="radio" value="2" name="A3" '.($a3==2?'checked':'').'> Has solid understanding of own job by applying technical/functional knowledge and skills to solve simple problems or assignments.<br>';
	echo '[3] <input type="radio" value="3" name="A3" '.($a3==3?'checked':'').'> Has broad knowledge of principles, practices, and procedures in his/her field of specialization to complete difficult assignments which affects a department or a client.<br>';
	echo '[4] <input type="radio" value="4" name="A3" '.($a3==4?'checked':'').'> Has understanding of his/her field of expertise; communicates this to different levels in the company and recommends solutions to very complex problems to improve corporate-wide performance. <br>';
	echo '[5] <input type="radio" value="5" name="A3" '.($a3==5?'checked':'').'> On top of degree 4, technical skills and knowledge extends to integrate peculiarities of different organizations within a group of companies or within an industry in seeing its implication to government regulations.<br>';
	echo '</div>';
	echo '<div style="background-color:#fed8b1;padding:5px;">';
	echo '<h3>B. Effort</h3>';
	
	echo '<br><b>4. Analysis & Problem Solving</b><br><i><font style="font-size:10pt"><b>This refers to the degree of analysis or relational problem-solving thinking required for the successful performance of the job.</b></font></i><br>';
	echo '[1] <input type="radio" value="1" name="B4" required '.($b4==1?'checked':'').'> Follows specific activity instructions, requiring minimal independent thinking.<br>';
	echo '[2] <input type="radio" value="2" name="B4" '.($b4==2?'checked':'').'> Processes data or information in accordance with established operating procedures or steps.<br>';
	echo '[3] <input type="radio" value="3" name="B4" '.($b4==3?'checked':'').'> Collects and analyzes facts that involves choices between two or more varying methods or procedures.<br>';
	echo '[4] <input type="radio" value="4" name="B4" '.($b4==4?'checked':'').'> Comes up with conclusions or solutions to problems based on established standard procedures and policies based on facts that are readily available.<br>';
	echo '[5] <input type="radio" value="5" name="B4" '.($b4==5?'checked':'').'> Comes up with courses of actions or responses based only on desired goals and objectives; and collects and analyzes data for said purpose.<br>';
	
	echo '<br><b>5. Nature of External Relations</b><br><i><font style="font-size:10pt"><b>Involves the degree to which job holder is expected to coordinate outside of the organization to enble him/her to do his/her job properly.</b></font></i><br>';
	echo '[1] <input type="radio" value="1" name="B5" required '.($b5==1?'checked':'').'> Gives/receives information, which calls for routine and casual courtesy. <br>';
	echo '[2] <input type="radio" value="2" name="B5" '.($b5==2?'checked':'').'> Gives/receives information, which require routine discussions or explanations. <br>';
	echo '[3] <input type="radio" value="3" name="B5" '.($b5==3?'checked':'').'> Has non-routine exchange/explanation of information, which require courtesy to avoid friction and to obtain assistance and/or cooperation.<br>';
	echo '[4] <input type="radio" value="4" name="B5" '.($b5==4?'checked':'').'> Directs and coordinates the work of third party service providers.<br>';
	echo '[5] <input type="radio" value="5" name="B5" '.($b5==5?'checked':'').'> Creates plans and tactful approach to convince and/or influence others to carry out certain courses of action, such as making final negotiation to close a deal or to get full support of partners in the delivery of products and services.<br>';
	
	echo '<br><b>6. Nature of Internal Relations</b><br><i><font style="font-size:10pt"><b>Involves the degree to which job holder is expected to coordinate and even motivate others in the organization to enable him/her to do his/her job properly.</b></font></i><br>';
	echo '[1] <input type="radio" value="1" name="B6" required '.($b6==1?'checked':'').'> Routinely receive inputs for normal work situations and are within his/her control and knowledge. <br>';
	echo '[2] <input type="radio" value="2" name="B6" '.($b6==2?'checked':'').'> Processes inputs which require verification of accuracy and close coordination with other units to produce final results.<br>';
	echo '[3] <input type="radio" value="3" name="B6" '.($b6==3?'checked':'').'> Instructs / influences other units/employees to supply inputs and results in a timely manner for him/her to complete his/her tasks properly.<br>';
	
	echo '<br><b>7. Complexity of Work</b><br><i><font style="font-size:10pt"><b>It refers to the, intricacy, and variety of the functions and responsibilities assigned to the job holder.</b></font></i><br>';
	echo '[1] <input type="radio" value="1" name="B7" required '.($b7==1?'checked':'').'> Performs simple and repetitive jobs.<br>';
	echo '[2] <input type="radio" value="2" name="B7" '.($b7==2?'checked':'').'> Performs activities that are specific in objectives and content, with general understanding of related activities to finish the tasks.<br>';
	echo '[3] <input type="radio" value="3" name="B7" '.($b7==3?'checked':'').'> Does operational or conceptual integration or coordination of activities that are relatively uniform in nature and objective for the completion of a minor project or program.<br>';
	echo '[4] <input type="radio" value="4" name="B7" '.($b7==4?'checked':'').'> Does operational or conceptual integration or coordination of activities that are diverse in nature and objective in an important managed area.  This involves major projects. <br>';
	
	echo '<br><b>8. Physical Effort /Working Conditions</b><br><i><font style="font-size:10pt"><b>This refers to the kind of physical effort, working conditions and possible hazard the job holder is exposed to in the performance of his/her job.</b></font></i><br>';
	echo '[1] <input type="radio" value="1" name="B8" required '.($b8==1?'checked':'').'> Stays generally in an office environment with minimal physical effort.<br>';
	echo '[2] <input type="radio" value="2" name="B8" '.($b8==2?'checked':'').'> Stays generally in an office/store environment with 30% of physical effort.<br>';
	echo '[3] <input type="radio" value="3" name="B8" '.($b8==3?'checked':'').'> Does field work or light physical exertion (at least 40%of the time).<br>';
	
	echo '<br><b>9. Planning and Controlling</b><br><i><font style="font-size:10pt"><b>This refers to the extent which job holder determines the appropriate goals and the corresponding programs and implementing activities in his functional area. Related to it , this factor also describe the type of results monitoring and evaluation that the he is expected to do.</b></font></i><br>';
	echo '[1] <input type="radio" value="1" name="B9" required '.($b9==1?'checked':'').'> Follows given detailed instructions/standard procedures to carry out the day to day work activities.<br>';
	echo '[2] <input type="radio" value="2" name="B9" '.($b9==2?'checked':'').'> On top of degree 1, has some degree of judgment and amount of flexibility in order to prioritize schedule, depending on work demands.<br>';
	echo '[3] <input type="radio" value="3" name="B9" '.($b9==3?'checked':'').'> On top of degree 2, can carry out specific programs or projects, requiring him/her to choose from among alternative methods and approaches.  In such instances, controlling involves interim monitoring and checking to ensure successful completion of 1-2 projects/programs at a given time.<br>';
	echo '[4] <input type="radio" value="4" name="B9" '.($b9==4?'checked':'').'> On top of degree 3, ensures the successful completion of 3 or more projects at a given time or a major project at a given time.<br>';
	echo '[5] <input type="radio" value="5" name="B9" '.($b9==5?'checked':'').'> Involved in planning on an annual basis for the effectiveness of a given functional area.  Planning is normally based on agreed objectives and controlling entails adjustment in work activities and programs to conform to the demands of the organization from time to time.<br>';
	
	echo '<br><b>10. Company Assets</b><br><i><font style="font-size:10pt"><b>This refers to the magnitude of company funds or assets which, due to the nature of the jobholder. These include tools of the trade, money and corporate securities, properties, stocks and equipment being operated or handled.</b></font></i><br>';
	echo '[1] <input type="radio" value="1" name="B10" required '.($b10==1?'checked':'').'> Nil and up to P 20,000.00.<br>';
	echo '[2] <input type="radio" value="2" name="B10" '.($b10==2?'checked':'').'> More than P 20,000.00 up to P 100,000.00.<br>';
	echo '[3] <input type="radio" value="3" name="B10" '.($b10==3?'checked':'').'> More than P 100,000.00 up to P 500,000.00.<br>';
	echo '[4] <input type="radio" value="4" name="B10" '.($b10==4?'checked':'').'> More than  P 500,000.00 to P1 million.<br>';
	echo '[5] <input type="radio" value="5" name="B10" '.($b10==5?'checked':'').'> In excess of P1 million.<br>';
	
	echo '<br><b>11. Confidentiality</b><br><i><font style="font-size:10pt"><b>The integrity and discretion required by the job or safeguarding confidential and restricted information.</b></font></i><br>';
	echo '[1] <input type="radio" value="1" name="B11" required '.($b11==1?'checked':'').'> Works occasionally [at least 25% of the time] on materials of a confidential nature, discretion and integrity are recognized requirements of the job though disclosure would not have an apparent effect on the company [some embarrassment].<br>';
	echo '[2] <input type="radio" value="2" name="B11" '.($b11==2?'checked':'').'> Works periodically [at least 50%-75% of the time] with confidential data and disclosure might have an appreciable effect on the company [some embarrassment and some losses].<br>';
	echo '[3] <input type="radio" value="3" name="B11" '.($b11==3?'checked':'').'> Works regularly [100% of the time] with confidential data of considerable importance, which if disclosed, may be detrimental to the interest of the company as a whole. [Loss of business/clients or leads to a major legal case].<br>';
	
	echo '<br><b>12. Leading and Responsibility</b><br><i><font style="font-size:10pt"><b>Normally expressed in terms of and measured by the number of direct and one-half the number of indirect supervised subordinates.</b></font></i><br><div style="margin-left:10px;"><u>Direct Supervision</u> - Has direct managerial responsibility over staff of the team. He/She is vested the authority to oversea a team and to effectively recommend and/or decide over directions, plans, programs and employee actions.<br><u>Indirect Supervision</u> - Has supervisory responsibility over non-managerial employees of the team.</div>';
	echo '[1] <input type="radio" value="1" name="B12" required '.($b12==1?'checked':'').'> None<br>';
	echo '[2] <input type="radio" value="2" name="B12" '.($b12==2?'checked':'').'> One (1) to two (2)<br>';
	echo '[3] <input type="radio" value="3" name="B12" '.($b12==3?'checked':'').'> Three (3) to Four (4)<br>';
	echo '[4] <input type="radio" value="4" name="B12" '.($b12==4?'checked':'').'> Five (5) to Six (6)<br>';
	echo '[5] <input type="radio" value="5" name="B12" '.($b12==5?'checked':'').'> Seven (7) or more<br>';
	
	echo '<br><b>13. Contribution to Organization</b><br><i><font style="font-size:10pt"><b>This refers to the level of contribution of the job holder for the attainment of corporate goals and objectives.</b></font></i><br>';
	echo '[1] <input type="radio" value="1" name="B13" required '.($b13==1?'checked':'').'> Contributes by assisting others through limited or incidental support services with indirect effects on goal attainment.<br>';
	echo '[2] <input type="radio" value="2" name="B13" '.($b13==2?'checked':'').'> Contributes by working independently which leads to significant improvements and contributions towards achievement of goals and objectives.<br>';
	echo '[3] <input type="radio" value="3" name="B13" '.($b13==3?'checked':'').'> Contributes by providing expertise and guiding individuals in a unit/section/team to achieve section/unit/team goals and objectives.<br>';
	echo '[4] <input type="radio" value="4" name="B13" '.($b13==4?'checked':'').'> Executes annual objectives through others in the team.<br>';
	
	echo '<br><input type="submit" value="Add New" style="border-radius:10px;background-color:blue;color:white;width:100%;font-size:16pt;padding:4px;" OnClick="return confirm(\'Is this Final?\');">';
	echo '</div>';
	echo '</div>';
	echo '</form>';
	
	
	break;
	
	case 'PrAddEditPosition':
	if (!allowedToOpen(59052,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
	$insupd='PositionID='.$_POST['PositionID'].',A1='.$_POST['A1'].',A2='.$_POST['A2'].',A3='.$_POST['A3'].',B4='.$_POST['B4'].',B5='.$_POST['B5'].',B6='.$_POST['B6'].',B7='.$_POST['B7'].',B8='.$_POST['B8'].',B9='.$_POST['B9'].',B10='.$_POST['B10'].',B11='.$_POST['B11'].',B12='.$_POST['B12'].',B13='.$_POST['B13'].',WJRID='.$_POST['WJRID'].',EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=Now()';
	
	if(isset($_GET['edit'])){
		
		$sql = 'UPDATE hr_1jobratingplan SET '.$insupd.' WHERE '.((allowedToOpen(59053,'1rtc'))?'':'EncodedByNo='.$_SESSION['(ak0)'].' AND ').' Posted=0 AND PositionID='.$_GET['edit'];
	} else {
		$sql = 'INSERT INTO hr_1jobratingplan SET '.$insupd.'';
	}
	
	$stmt=$link->prepare($sql); $stmt->execute();
	// echo $sql; exit();
	header('Location:jobratingplan.php?w=PositionList');
	
	break;
	
	case 'PrAddEditFormula':
	if (!allowedToOpen(59051,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
	$insupd='FormulaName="'.$_POST['FormulaName'].'",WA1='.$_POST['WA1'].',WA2='.$_POST['WA2'].',WA3='.$_POST['WA3'].',WB4='.$_POST['WB4'].',WB5='.$_POST['WB5'].',WB6='.$_POST['WB6'].',WB7='.$_POST['WB7'].',WB8='.$_POST['WB8'].',WB9='.$_POST['WB9'].',WB10='.$_POST['WB10'].',WB11='.$_POST['WB11'].',WB12='.$_POST['WB12'].',WB13='.$_POST['WB13'].',EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=Now()';
	
	if(isset($_GET['edit'])){
		
		$sql = 'UPDATE hr_1jobratingweights SET '.$insupd.' WHERE '.((allowedToOpen(59053,'1rtc'))?'':'EncodedByNo='.$_SESSION['(ak0)'].' AND ').' WJRID='.$_GET['WJRID'];
	} else {
		$sql = 'INSERT INTO hr_1jobratingweights SET '.$insupd.'';
	}
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	
	header('Location:jobratingplan.php?w=List');
	
	break;
	
	case 'DelFormula':
	if (!allowedToOpen(59051,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql = 'DELETE FROM hr_1jobratingweights WHERE '.((allowedToOpen(59053,'1rtc'))?'':'EncodedByNo='.$_SESSION['(ak0)'].' AND ').' WJRID='.$_GET['WJRID'];
	$stmt=$link->prepare($sql); $stmt->execute();
	
	header('Location:jobratingplan.php?w=List');
	
	break;
	
	case 'DelPosition':
	if (!allowedToOpen(59052,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql = 'DELETE FROM hr_1jobratingplan WHERE '.((allowedToOpen(59053,'1rtc'))?'':'EncodedByNo='.$_SESSION['(ak0)'].' AND ').' Posted=0 AND PositionID='.$_GET['PositionID'];
	$stmt=$link->prepare($sql); $stmt->execute();
	
	header('Location:jobratingplan.php?w=PositionList');
	
	break;
	
	
	case 'PostUnpost':
	if (!allowedToOpen(59052,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql = 'UPDATE hr_1jobratingplan SET Posted=IF(Posted=1,0,1),PostedByNo='.$_SESSION['(ak0)'].',PostedTS=NOW() WHERE '.((allowedToOpen(59053,'1rtc'))?'':'EncodedByNo='.$_SESSION['(ak0)'].' AND ').' PositionID='.$_GET['PositionID'];
	$stmt=$link->prepare($sql); $stmt->execute();
	
	header('Location:jobratingplan.php?w=LookupJobGrade&PositionID='.$_GET['PositionID']);
	
	break;
	
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
