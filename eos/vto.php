<?php
date_default_timezone_set("Asia/Manila");
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
if (!allowedToOpen(array(1500,1603,1616),'1rtc')){ echo 'No Permission'; exit(); }
// end of check
$showbranches=FALSE;
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=!isset($_GET['w'])?'Vision':$_GET['w'];
$vto=!isset($_REQUEST['VTOID'])?1:$_REQUEST['VTOID'];

//deptid
	if(!isset($_SESSION['deptonly'])){
			$iddept=-1;
		} else if($_SESSION['deptonly']==-100){
			$iddept=-100;
		} else{
			$iddept=$_SESSION['deptonly'];
		}

		// print_r($_SESSION);
//
if(in_array($which, array('Vision','Encode'))){
	goto skipb;
}
if (allowedToOpen(1500,'1rtc')){
	$sqlsm='select deptid,dept from attend_30currentpositions GROUP BY deptid ORDER BY dept ;';
}
else if (allowedToOpen(1603,'1rtc')){
	$sqlsm='select deptid,dept from attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].' OR LatestSupervisorIDNo='.$_SESSION['(ak0)'].' OR deptheadpositionid='.$_SESSION['&pos'].' GROUP BY deptid ORDER BY dept;';
	
} elseif(isset($_SESSION['divisionid'])){
	$_SESSION['deptonly']=-100;
}
	else {
	$_SESSION['deptonly']=comboBoxValue($link, 'attend_30currentpositions', 'IDNo', $_SESSION['(ak0)'], 'deptid');
}


//departments with 3rd layer
$deptwith3rdlayer=array(11);
	
	
if(allowedToOpen(array(1500,1603),'1rtc')){
	$stmts=$link->query($sqlsm); $ress=$stmts->fetchAll();
	
	
	$md='<form style="margin-right:3px;" action="vto.php?w=SwitchDept&go2='.$which.'" method="POST"><input style="width:90px;margin-bottom:5px;" type="submit" value="ManCom" name="btnSubmitm"></form><br><hr><h3>Departments</h3>';
	foreach($ress AS $resm){
		$md.='<form style="float:left;margin-right:3px;" action="vto.php?w=SwitchDept&go2='.$which.'" method="POST"><input type="hidden" value="'.$resm['deptid'].'" name="deptid"><input style="width:90px;" type="submit" value="'.$resm['dept'].'" name="btnSubmitm"></form> ';
	}
	$md.='<a style="background-color:#e7e7e7;padding:2px;border:1px solid blue;color:#000;text-decoration: none;" href="interdeptmeeting.php">Interdepartment Meeting</a>';





	$md2='';
	
	
	if(isset($_SESSION['deptonly']) AND in_array($_SESSION['deptonly'], $deptwith3rdlayer)){
	
	
	//positions in layer 3
	$inlayer3=' WHERE PositionID IN (61)';
	
	
	
	$sqlss='select IDNo,FullName,deptid,dept from attend_30currentpositions '.$inlayer3.' AND deptid='.$_SESSION['deptonly'].' ORDER BY dept;';
	$stmtss=$link->query($sqlss); $resss=$stmtss->fetchAll();
		foreach($resss AS $resm2){
			$md2.='<form style="float:left;margin-right:3px;" action="vto.php?w=SwitchDept&go2='.$which.'&IDNo='.$resm2['IDNo'].'" method="POST"><input type="hidden" value="'.$resm2['deptid'].'" name="deptid"><input type="submit" value="'.$resm2['dept'].' ('.$resm2['FullName'].')" name="btnSubmitm"></form> ';
		}
	
	}


	
	
	
	
	
	
	
	echo '<br>'.$md.'<div style="clear: both; display: block; position: relative;height:10px;"></div>';
	
	if(isset($_SESSION['deptonly']) AND in_array($_SESSION['deptonly'], $deptwith3rdlayer)){
	echo '<hr><b>';
		echo '<h3>Per Team</h3>'.$md2.'<div style="clear: both; display: block; position: relative;height:10px;"></div>';
	
	}
	
	




}

$sqldiv='SELECT DID,DivisionDesc,LEFT(FullName,LOCATE(" -",FullName)-1) AS DivisionHead
	 FROM `1divisions` dv JOIN attend_30currentpositions cp ON dv.DivisionHeadIDNo=cp.IDNo WHERE FIND_IN_SET('.$_SESSION['(ak0)'].',PositionIDsorIDNos) OR FIND_IN_SET('.$_SESSION['&pos'].',PositionIDsorIDNos) OR DivisionHeadIDNo='.$_SESSION['(ak0)'].' OR dv.EncodedByNo='.$_SESSION['(ak0)'].'';
	//  echo $sqldiv; exit();
	$stmtdiv=$link->query($sqldiv);
	$md3='';
	if($stmtdiv->rowCount()>0){
		$md3.='<hr><h3>Divisions</h3>';
		$resdivs=$stmtdiv->fetchAll();

		foreach($resdivs AS $resdiv){
			$md3.='<form style="float:left;margin-right:3px;" action="vto.php?w=SwitchDept&division=1&go2='.$which.'" method="POST"><input type="hidden" value="'.$resdiv['DID'].'" name="DID"><input style="width:210px;" type="submit" value="'.$resdiv['DivisionDesc'].' ('.$resdiv['DivisionHead'].')" name="btnSubmitm"></form> ';
		}
		
	}

	echo '<br>'.$md3.'<div style="clear: both; display: block; position: relative;height:10px;"></div>';

	
$sqlqtr='SELECT QtrThemeMancom, QtrTheme FROM eos_2vtoqtrmain WHERE VTOQtrId=(IF(YEAR(CURDATE())='.$currentyr.',QUARTER(CURDATE()),4));';
$stmt=$link->query($sqlqtr); $resqtr=$stmt->fetch();



if (allowedToOpen(1603,'1rtc') AND (!isset($_SESSION['deptonly']))) {
	$dep='ManCom: &nbsp; '.$resqtr['QtrThemeMancom'];
	$mancomordeptcondi=' ManComOrdept=-1 AND ';	
	$color1='green';
	$l10day='monday';
	$l10dayval='1';
	
} elseif($_SESSION['deptonly']==-100) {
	$dep=$_SESSION['divisionname'].':  &nbsp; '.$resqtr['QtrTheme'];
	// print_r($_SESSION);
	$sqldividno='SELECT IF(IsPosition=0,CONCAT((SELECT GROUP_CONCAT(IDNo) FROM attend_30currentpositions WHERE FIND_IN_SET(PositionID,PositionIDsorIDNos)),",",DivisionHeadIDNo),CONCAT(PositionIDsorIDNos,",",DivisionHeadIDNo)) AS IDNos FROM 1divisions WHERE DID='.$_SESSION['divisionid'];
	$stmtdividno=$link->query($sqldividno); $resdividno=$stmtdividno->fetch();

	// echo $sqldividno;
	$mancomordeptcondi=' Who IN ('.$resdividno['IDNos'].') AND ManComOrdept<>-1 AND ';

	$sqll10='SELECT (CASE 
		WHEN L10mtgday=1 THEN "monday"
		WHEN L10mtgday=2 THEN "tuesday"
		WHEN L10mtgday=3 THEN "wednesday"
		WHEN L10mtgday=4 THEN "thursday"
		WHEN L10mtgday=5 THEN "friday"
		WHEN L10mtgday=6 THEN "saturday"
		ELSE "sunday"
	END) AS dayofmeeting,L10mtgday FROM 1departments WHERE deptid='.(comboBoxValue($link, 'attend_30currentpositions', 'IDNo', $_SESSION['(ak0)'], 'deptid')).'';

	$stmtl10=$link->query($sqll10); $resultl10=$stmtl10->fetch();


		$l10day=$resultl10['dayofmeeting'];
		$l10dayval=$resultl10['L10mtgday'];
	

} else {
	
	$dep=comboBoxValue($link, '1departments', 'deptid', $_SESSION['deptonly'], 'dept').':  &nbsp; '.$resqtr['QtrTheme'];;
	
	if(isset($_SESSION['deptonly']) AND in_array($_SESSION['deptonly'], $deptwith3rdlayer) AND (!allowedToOpen(1603,'1rtc')) AND in_array($_SESSION['&pos'], array(36,61))){
		$idnov=$_SESSION['(ak0)'];
	}
	
	if(isset($_GET['IDNo'])){
		$idnov=$_GET['IDNo'];
	}
	
	
	
	$mancomordeptcondi=' '.(isset($idnov)?'Who IN (SELECT IDNo From attend_30currentpositions WHERE (IDNo='.intval($idnov).' OR LatestSupervisorIDNo='.intval($idnov).')) AND':'').' ManComOrdept='.$_SESSION['deptonly'].' AND ';
	
	
	$color1='blue';
	
	$sqll10='SELECT (CASE 
		WHEN L10mtgday=1 THEN "monday"
		WHEN L10mtgday=2 THEN "tuesday"
		WHEN L10mtgday=3 THEN "wednesday"
		WHEN L10mtgday=4 THEN "thursday"
		WHEN L10mtgday=5 THEN "friday"
		WHEN L10mtgday=6 THEN "saturday"
		ELSE "sunday"
	END) AS dayofmeeting,L10mtgday FROM 1departments WHERE deptid='.$_SESSION['deptonly'].'';
	$stmtl10=$link->query($sqll10); $resultl10=$stmtl10->fetch();
	

		$l10day=$resultl10['dayofmeeting'];
		$l10dayval=$resultl10['L10mtgday'];
	
	
}


// if(allowedToOpen(array(1500,1603),'1rtc')){
	
	echo '<br><h1 align="center" style="background-color:white;padding:8px;border:1px solid green;"><font color="'.$color1.'">'.$dep.(isset($_GET['IDNo'])?' ('.comboBoxValue($link, 'attend_30currentpositions', 'IDNo', $_GET['IDNo'], 'FullName').')':'').'</font></h1><br>';
// }
 skipb:
 
include_once '../backendphp/layout/linkstyle.php';
if(in_array($which, array('WeeklyUpdates','WeeklyMeeting','WeeklyMeetingIssues','EditScoreProcessSub'))){
	$yr=date('Y');
}

if(in_array($which, array('Rocks','Issues','ToDo','ScorecardList','EditMeasurables','ScorecardList','Traction'))){
$defaultwho=comboBoxValue($link, '1employees', 'IDNo', $_SESSION['(ak0)'], 'Nickname')." ".comboBoxValue($link, '1employees', 'IDNo', $_SESSION['(ak0)'], 'SurName');
}

if(in_array($which, array('EditMeasurables','ScorecardList','Traction','ToDo','Issues','Rocks','RockSummary'))){
//Encode Rocks
	$sqlpid='SELECT AllowedPos,AllowedPerID FROM permissions_2allprocesses WHERE ProcessID=1614';
	$stmtpid=$link->query($sqlpid); $resultpid=$stmtpid->fetch();
	if($resultpid['AllowedPerID']<>''){
		$mancomlistidno=$resultpid['AllowedPerID'];
	}else{
		$mancomlistidno=0;
	}
	
	echo comboBox($link,'SELECT "Counted" AS CountedNotCounted, 1 AS CountedNotCountedValue UNION SELECT "NotCounted" AS CountedNotCounted, 0 AS CountedNotCountedValue','CountedNotCountedValue','CountedNotCounted','countednotcountedlist');
	echo comboBox($link,'select * from eos_2vtoqtrmain ORDER BY VTOQtrId','VTOQtrId','VTOQtrId','vtoqs'); 
	
	if(!isset($_SESSION['deptonly'])){
		echo comboBox($link,'select e.*,CONCAT(Nickname,\' \',SurName) as Fullname from 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE PositionID IN ('.$resultpid['AllowedPos'].') OR e.IDNo IN ('.$mancomlistidno.')','IDNo','Fullname','employees');
	
		echo comboBox($link,'select e.*,CONCAT(Nickname,\' \',SurName) as Fullname from 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE PositionID IN ('.$resultpid['AllowedPos'].') OR e.IDNo IN ('.$mancomlistidno.')','Fullname','IDNo','employees1');
	} else {
		$depid='(SELECT deptid FROM attend_1positions WHERE PositionID='.$_SESSION['&pos'].')';
		$supid='(SELECT supervisorpositionid FROM attend_1positions WHERE PositionID='.$_SESSION['&pos'].')';
		echo comboBox($link,'select e.*,CONCAT(Nickname,\' \',SurName) as Fullname from 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE deptheadpositionid='.$_SESSION['&pos'].' OR PositionID='.$supid.' OR deptid='.$depid.' OR e.IDNo='.$_SESSION['(ak0)'].'','IDNo','Fullname','employees');
	// echo 'select e.*,CONCAT(Nickname,\' \',SurName) as Fullname from 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE deptheadpositionid='.$_SESSION['&pos'].' OR deptid='.$depid.' OR e.IDNo='.$_SESSION['(ak0)'].'';
		echo comboBox($link,'select e.*,CONCAT(Nickname,\' \',SurName) as Fullname from 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE deptheadpositionid='.$_SESSION['&pos'].' OR PositionID='.$supid.' OR deptid='.$depid.' OR e.IDNo='.$_SESSION['(ak0)'].'','Fullname','IDNo','employees1');
	}
	
	
	
	
}
if(in_array($which, array('Traction','Rocks','RockSummary','Issues','WeeklyUpdates','WeeklyMeeting','WeeklyMeetingIssues'))){
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
}
if(in_array($which, array('Rocks','Issues','ToDo'))){
		$imgedit='<img src="../generalinfo/icons/edit.png" alt="Edit" height="20px;">';
		$imgdel='<img src="../generalinfo/icons/delete.png" alt="Edit" height="20px;">';
		$imgcancel='<img src="../generalinfo/icons/cancel.png" alt="Edit" height="20px;">';
}
if(in_array($which, array('Traction','Rocks','RockSummary','Issues','ToDo','ScorecardList','ToDoSummary'))){
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
if(in_array($which, array('Traction'))){
				
				 
            $sql='SELECT VTOQtrId,QtrFutureDate, QtrRevenue, QtrProfit, QtrMeasurables, QtrThemeMancom, QtrTheme FROM eos_2vtoqtrmain WHERE VTOQtrId='.$qtr;
            $stmt=$link->query($sql); $result=$stmt->fetch();
}
if(in_array($which, array('MtgUpdate','WeeklyUpdates','WeeklyMeeting','OnOffTrackProcess','DoneNotDone','ResolveNotResolve','WeeklyMeetingIssues','UpdateScore','MeetingStatus'))){
	$date_string = date('Y-m-d');
	$weekno=date("W", strtotime($date_string));
	
	$dayno=date('w');
	if($dayno==0){
		$dayno=7;
	}
		if($dayno<=$l10dayval){
			$weekno=$weekno;
		} else {
			$weekno=$weekno+1;
		}

//lastweekmtgstatus	
$lastweekno=$weekno-1;
if(strlen($lastweekno)==1){
	$lastweekno='0'.$lastweekno;
}
	$sqlchecker='select right(WeekNoAndStatus,1) AS Status,substring(WeekNoAndStatus,1,2) as WeekNo from eos_1mtgstatus where '.($iddept<>-100?'deptid='.$iddept.'':'2=2').' and substring(WeekNoAndStatus,1,2)='.$lastweekno.'';
	$stmtchecker=$link->query($sqlchecker); $resultchecker=$stmtchecker->fetch();
	if($stmtchecker->rowCount()!=0){
		$status=$resultchecker['Status'];
		if($status==0 AND $dayno<>7){
			$weekno=$weekno-1;
		}else{
			$weekno=$weekno;
		}
	}else{
		$status=0;
		$weekno=$weekno;
	}

//	
}
if(isset($weekno) AND strlen($weekno)==1){
	$weekno='0'.$weekno;
}

if(in_array($which, array('Encode','WeeklyUpdates','WeeklyMeeting','WeeklyMeetingIssues'))){	?>
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
}

if(in_array($which, array('WeeklyUpdates','WeeklyMeeting','WeeklyMeetingIssues','MeetingStatus'))){	

function week_date($week, $year, $l10dayval){
		$date = new DateTime();
		return $date->setISODate($year, $week, $l10dayval)->format('Y-m-d'); 
	}
}
if(in_array($which, array('WeeklyUpdates','MeetingStatus'))){
if($stmtchecker->rowCount()!=0){
			$status=$resultchecker['Status'];
			if($status==0){
				$lastweekmeeting=$weekno;
			}else{
				$lastweekmeeting=$weekno-1;
			}
		}else{
			$lastweekmeeting=$weekno-1;
		}
}

	
?>
   
<style>
.vto{
  border-collapse: collapse;
  font-size:12pt;
}
    .vto td { border: solid black 1px; background-color: white; padding: 3px;}
    .vto li { margin-left: 6%;}
	.vto .td{
		background-color:#cccccc;
		text-align:center;
	}

#wrap {
   width:100%;
   margin:0 auto;
}
#left {
   float:left;

}
#right {
   float:right;
}	
</style>

<?php
echo '<br>';
$visionlink='<a id="link" href="vto.php?w=Vision">Vision</a> <a id="link" href="vto.php?w=Encode">Encode / Edit Information</a><br><br>';

$tractionlink='<a id="link" href="vto.php?w=Traction"><font style="font-size:8.5pt;font-weight:bold;">Measurables Summary</font></a> &nbsp; &nbsp; &nbsp; &nbsp;
<a id="link" href="vto.php?w=Rocks">Rocks</a> 
<a id="link" href="vto.php?w=Issues">Issues</a>
<a id="link" href="vto.php?w=ToDo">To-Do</a>
<a id="link" href="vto.php?w=ScorecardList">Scorecard</a>
'.str_repeat('&nbsp;',9).'
<a id="link" target="_blank" href="agenda.php">Meeting Agenda</a>
<a id="link" href="vto.php?w=WeeklyUpdates">Meeting Updates</a>
<a id="link" href="vto.php?w=WeeklyMeeting'.(isset($_GET['IDNo'])?'&IDNo='.$_GET['IDNo'].'':'').'">Meeting - Measurables</a>
<a id="link" href="vto.php?w=WeeklyMeetingIssues'.(isset($_GET['IDNo'])?'&IDNo='.$_GET['IDNo'].'':'').'">Meeting - ISSUES</a>
<a id="link" href="vto.php?w=ToDoSummary">To Do Summary</a> 
<a id="link" href="vto.php?w=RockSummary">Rock Summary</a>
&nbsp; &nbsp; &nbsp; &nbsp;<a id="link" target="_blank" href="wishlists.php"><b>Wishlist</b></a>';

$tractionlink.='</br></br>';

if(in_array($which, array('Traction','Rocks','RockSummary','Issues','ToDo','ScorecardList','WeeklyMeeting','WeeklyUpdates','ToDoSummary','WeeklyMeetingIssues','EditQtr','EditMeasurables'))){	


echo $tractionlink;


}

if(in_array($which, array('Vision','Encode'))){	


echo $visionlink;

}


?>
 
<?php

switch ($which){
	case 'SwitchDept':
	
	if(isset($_GET['division'])){
		$_SESSION['divisionid']=$_POST['DID'];
		$_SESSION['divisionname']=$_POST['btnSubmitm'];
		$_SESSION['deptonly']=-100; 
	} else {
		if(isset($_SESSION['divisionid'])){
			unset($_SESSION['divisionid']);
			unset($_SESSION['divisionname']);
		}
		
		if($_POST['btnSubmitm']=='ManCom'){
			unset($_SESSION['deptonly']);
		} else {
			$_SESSION['deptonly']=$_POST['deptid'];
		}
	}
	// print_r($_SESSION);
	// exit();
	header("Location:vto.php?w=".$_GET['go2'].(isset($_GET['IDNo'])?'&IDNo='.$_GET['IDNo'].'':'')."");
	
	break;
	
    case 'Vision':
	
	$title='VISION';
	echo '<title>'.$title.'</title>';
	echo '<h1>'.$title.'</h1><br>';
?>



<div id='wrap'>
    <div id='left'>
<table class='vto'>
    <tr>
		<td class="td"><h4>CORE VALUES</h4></td>
		<td>    
			<ol>
				<?php
				$sql='SELECT * FROM eos_2vtosub WHERE VTOID='.$vto.' AND VTOListID=1 ORDER BY OrderBy';
				$stmt=$link->query($sql); $res=$stmt->fetchAll(); $list='';
				foreach ($res as $r){
					$list.='<li>'.$r['Details'].'</li>';
				}
				echo $list;
				?>
			</ol>
		</td>
	</tr>

    <tr>
		 <td class="td">
				<h4>CORE FOCUS</h4>
		</td>
		<td>
			<i>Purpose/Cause/Passion</i>
        <ol>
            <?php
            $sql='SELECT * FROM eos_2vtosub WHERE VTOID='.$vto.' AND VTOListID=2 ORDER BY OrderBy';
            $stmt=$link->query($sql); $res=$stmt->fetchAll(); $list='';
            foreach ($res as $r){
                $list.='<li>'.$r['Details'].'</li>';
            }
            echo $list;
            ?>
        </ol><br/><br/>
            
            <i>Our Niche (One thing we do better than anyone)</i><br/>
            <?php
            $sql='SELECT Niche FROM eos_2vtomain WHERE VTOID='.$vto;
            $stmt=$link->query($sql); $res=$stmt->fetch();
            
            echo $res['Niche'];
            ?>
            <br/><br/>
		</td>
    </tr>
	
    <tr>
		<td class="td">
			<h4>10 YEAR TARGET</h4>
		</td>
		<td>
			<?php
				$sql='SELECT TenYrGoal FROM eos_2vtomain WHERE VTOID='.$vto;
				$stmt=$link->query($sql); $res=$stmt->fetch();
				
				echo $res['TenYrGoal'];
				?>
		</td>
	</tr>
	
    <tr>
		<td class="td">
			<h4>MARKETING STRATEGY</h4><br/>
		</td>
		<td>
			<i>Target Market</i><br/>
        <ol>
            <?php
            $sql='SELECT * FROM eos_2vtosub WHERE VTOID='.$vto.' AND VTOListID=3 ORDER BY OrderBy';
            $stmt=$link->query($sql); $res=$stmt->fetchAll(); $list='';
            foreach ($res as $r){
                $list.='<li>'.$r['Details'].'</li>';
            }
            echo $list;
            ?>
        </ol>
        <i>3 Uniques</i><br/>
        <ol>
            <?php
            $sql='SELECT * FROM eos_2vtosub WHERE VTOID='.$vto.' AND VTOListID=4 ORDER BY OrderBy';
            $stmt=$link->query($sql); $res=$stmt->fetchAll(); $list='';
            foreach ($res as $r){
                $list.='<li>'.$r['Details'].'</li>';
            }
            echo $list;
            ?>
        </ol><br/><br/>
        <i>Proven Process</i><br/><br/>
        <?php
            $sql='SELECT Process FROM eos_2vtomain WHERE VTOID='.$vto;
            $stmt=$link->query($sql); $res=$stmt->fetch();
            
            echo $res['Process'];
            ?>
       </br></br><i>Guarantee/Our Promise</i><br/><br/>
        <?php
            $sql='SELECT Guarantee FROM eos_2vtomain WHERE VTOID='.$vto;
            $stmt=$link->query($sql); $res=$stmt->fetch();
            
            echo $res['Guarantee'];
            ?>
		</td>
	</tr>
	
	


</table>
    </div>
<div class="right">
	<table class='vto'>
	<tr>
		<td class="td">
			<h3>3 YEAR PICTURE</h3>
        </td>
	</tr>
	
	<tr>
		<td>
			<?php
				$sql='SELECT 3YrFutureDate, 3YrRevenue, 3YrProfit, 3YrMeasurables FROM eos_2vtomain WHERE VTOID='.$vto;
				$stmt=$link->query($sql); $res=$stmt->fetch();
				
				echo 'Future Date'. str_repeat('&nbsp;', 5).$res['3YrFutureDate'].'<br/><br/>';
				echo 'Revenue'. str_repeat('&nbsp;', 5).$res['3YrRevenue'].'<br/><br/>';
				echo 'Profit'. str_repeat('&nbsp;', 5).$res['3YrProfit'].'<br/><br/>';
				echo 'Measurables'. str_repeat('&nbsp;', 5).$res['3YrMeasurables'].'<br/><br/>';
				?>     
				<i>What does it look like?</i><br/><br/>
				<ul>
				<?php
				$sql='SELECT * FROM eos_2vtosub WHERE VTOID='.$vto.' AND VTOListID=5 ORDER BY OrderBy';
				$stmt=$link->query($sql); $res=$stmt->fetchAll(); $list='';
				foreach ($res as $r){
					$list.='<li style="margin-left:30px;">'.$r['Details'].'</li>';
				}
				echo $list;
				?>
			    </ul>
		</td>
	</tr>
	<tr><th><br>1-Year Plan</th></tr>
        <tr><td>
            <?php
            $sql='SELECT 1YrFutureDate, 1YrRevenue, 1YrProfit, 1YrMeasurables FROM eos_2vtomain WHERE VTOID='.$vto;
            $stmt=$link->query($sql); $res=$stmt->fetch();
            
            echo 'Future Date'. str_repeat('&nbsp;', 5).$res['1YrFutureDate'].'<br/><br/>';
            echo 'Revenue'. str_repeat('&nbsp;', 5).$res['1YrRevenue'].'<br/><br/>';
            echo 'Profit'. str_repeat('&nbsp;', 5).$res['1YrProfit'].'<br/><br/>';
            echo 'Measurables'. str_repeat('&nbsp;', 5).$res['1YrMeasurables'].'<br/><br/>';
            ?> 
                <h4>Goals for the Year</h4>
            <ol>
            <?php
            $sql='SELECT * FROM eos_2vtosub WHERE VTOID='.$vto.' AND VTOListID=6 ORDER BY OrderBy';
			// echo $sql;
            $stmt=$link->query($sql); $res=$stmt->fetchAll(); $list='';
            foreach ($res as $r){
                $list.='<li>'.$r['Details'].'</li>';
            }
            echo $list;
            ?>
            </ol>
            </td></tr>
	</table>
</div>    
   
</div>
<?php
break;

case 'Traction':
	$title='Measurables Summary';
    echo '<title>Measurables Summary</title>';
    echo '<h3>'.$title.'</h3><br>';
	
	
	
	
	echo '<form method="post" action="vto.php?w=Traction">
		<b>Who:</b> <input type="text" name="Who" list="employees1" size="10">
		<input type="submit" name="btnWho" value="Lookup">
	</form><br>';
	
	// $addlcond='';
	if(isset($_POST['btnWho'])){
		$idno=comboBoxValue($link, 'attend_30currentpositions', 'IDNo', $_POST['Who'], 'IDNo');
		$cond=' Who='.$idno;
		$showedit=0;
	} else {
		$idno=$_SESSION['(ak0)'];
		$cond=' Who='.$idno;
		$showedit=1;
	}
	
	$who=comboBoxValue($link, 'attend_30currentpositions', 'IDNo', $idno, 'FullName');
	echo '<div class="tabs">
		';
	echo '<br><h3>Who: '.$who.'</h3>';
	
	
	$subtitle='Scorecard Measurables';
	//scorecards
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	$sql='select *,CONCAT(Nickname,\' \',SurName) as Fullname from eos_2scorecard m left join 1employees e on e.IDNo=m.Who  WHERE '.$mancomordeptcondi.' '.$cond.'';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th>Goal Per Week</th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['Measurables'].'</td><td style="padding:3px;">'.$ress['Goal'].'</td></tr>';
		}
		
	echo'</table></div></div></div';
	
	$subtitle='Rocks';
	//rocks
	$isrock=1;
	echo '<div class="tabs">';
	
	$sqlmain='select vqs.*,IF(RIGHT(RIStatPerWeek,1)=1,"green","red") as bullcolor,CONCAT(Nickname,\' \',SurName) as Fullname from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who  WHERE '.$mancomordeptcondi.' Stat=0 AND IsRock='.$isrock.' ';
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
		$sql=$sqlmain.' AND VTOQtrId=1 AND '.$cond.'';
		// echo $addlcond;
		// echo $sql;
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	echo '<td valign="top" valign="top" style="width:25%;'.($qtr==2?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=2  AND '.$cond.'';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	echo '<td valign="top" valign="top" style="width:25%;'.($qtr==3?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=3  AND '.$cond.'';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	echo '<td valign="top" valign="top" style="width:25%;'.($qtr==4?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=4  AND '.$cond.'';
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
	
	
	$who=comboBoxValue($link, 'attend_30currentpositions', 'IDNo', $idno, 'FullName');
	echo '<div class="tabs">
		';
	
	$sqlmain='select *,CONCAT(Nickname,\' \',SurName) as Fullname from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who  WHERE '.$mancomordeptcondi.' IsRock='.$isrock.' AND `Stat`=0';
	
	echo '<br>';
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	$sql=$sqlmain.' AND '.$cond.' AND (RIGHT(RIStatPerWeek,1)=0 OR RIGHT(RIStatPerWeek,1) IS NULL)';
	
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
	$sqlmain='select vqs.*,CONCAT(Nickname,\' \',SurName) as Fullname from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who WHERE '.$mancomordeptcondi.' IsRock='.$isrock.' AND `Stat`=0';
	
	echo '<br>';
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	$sql=$sqlmain.' AND '.$cond.' AND (RIGHT(RIStatPerWeek,1)=0 OR RIGHT(RIStatPerWeek,1) IS NULL) ORDER BY VTOQtrId,Priority';
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
	


	case 'RockSummary':

	
		$isrock=1; //rocks
		$subtitle='Rocks';
		$title='Rocks Summary';
		echo '<title>'.$title.'</title>';
		
		echo '<div class="tabs">
			';
		
		echo '<br>';
		echo '<div class="tab-content">';
		echo '<h2>'.$title.'</h2><br>';
		echo '<div id="tab1" class="tab active">';

		if(isset($_POST['qtr'])){
			$qtr=$_POST['qtr'];
		}

		echo '<form action="vto.php?w=RockSummary" method="POST">
		Quarter: <input type="number" min=1 max=4 name="qtr" value="'.$qtr.'"> <input type="submit" name="btnSubmit" value="Lookup">
		</form><br>';

		$sql='select IDNo,CONCAT(Nickname," ",SurName) AS FullName from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who WHERE e.Resigned=0 AND '.$mancomordeptcondi.' IsRock='.$isrock.' AND VTOQtrId='.$qtr.' AND `Stat`=0 AND IsRock='.$isrock.' GROUP BY IDNo ORDER BY IDNo;';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		//echo $sql; //exit();
		
		
		echo '<table style="width:100%;">';
		$cnttr=1;
		
		foreach($result as $ress){
			
			$sqlw='SELECT RockOrIssues FROM eos_2vtoqtrsub WHERE '.$mancomordeptcondi.' IsRock='.$isrock.' AND VTOQtrId='.$qtr.' AND Who="'.$ress['IDNo'].'" AND Stat=0 ORDER BY TimeStamp DESC';
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


	
	case 'ToDoSummary':

	
	$isrock=2; //todo
	$subtitle='ToDo';
	$title='To-Do Summary';
	echo '<title>'.$title.'</title>';
	
	echo '<div class="tabs">
		';
	
	
	echo '<br>';
	echo '<div class="tab-content">';
	echo '<h2>'.$title.'</h2><br>';
	echo '<div id="tab1" class="tab active">';
	
	$sql='select IDNo,CONCAT(Nickname," ",SurName) AS FullName from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who WHERE e.Resigned=0 AND '.$mancomordeptcondi.' IsRock='.$isrock.' AND `Stat`=0 AND (RIGHT(RIStatPerWeek,1)=0 OR RIGHT(RIStatPerWeek,1) IS NULL) GROUP BY IDNo ORDER BY IDNo;';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	//echo $sql; //exit();
	
	
	echo '<table style="width:100%;">';
	$cnttr=1;
	
	foreach($result as $ress){
		
		$sqlw='SELECT RockOrIssues,RemarksOrResolution FROM eos_2vtoqtrsub WHERE '.$mancomordeptcondi.' IsRock='.$isrock.' AND (RIGHT(RIStatPerWeek,1)=0 OR RIGHT(RIStatPerWeek,1) IS NULL) AND Who="'.$ress['IDNo'].'" AND Stat=0 ORDER BY TimeStamp DESC';
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
			echo '<td style="width:100%;padding:3px;">'.$res['RockOrIssues'].''.($res['RemarksOrResolution']<>''?' (<font style="font-size:9pt;" color="'.(''.date('Y-m-d').''>''.$res['RemarksOrResolution'].''?'red':'').'">DueDate: '.$res['RemarksOrResolution'].'</font>)':'').'</td></tr>';
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
	
	case'Encode':
	echo '<title>Update Information</title>';
	echo comboBox($link,'select * from eos_1vtolisttype ORDER BY VTOList','VTOListID','VTOList','vtos'); 
	echo comboBox($link,'select * from eos_2vtoqtrmain ORDER BY VTOQtrId','VTOQtrId','VTOQtrId','vtoqs'); 
	echo comboBox($link,'select *,CONCAT(Nickname,\' \',SurName) as Fullname from 1employees ORDER BY SurName','IDNo','Fullname','employees'); 
	//Update Information
	if (allowedToOpen(1609,'1rtc')) {
	echo'<div style="border:1px solid black; width:2280px; padding:5px">';
	$sqlm='select * from eos_2vtomain';
	$stmtm=$link->query($sqlm); $resultm=$stmtm->fetchAll();
	echo '<h3>Update Information</h3></br><table id="table">';
	echo'<tr><th>TenYrGoal</th><th>Niche</th><th>Process</th><th>Guarantee</th><th>3YrFutureDate</th><th>3YrRevenue</th><th>3YrProfit</th><th>3YrMeasurables</th><th>1YrFutureDate</th><th>1YrRevenue</th><th>1YrProfit</th><th>1YrMeasurables</th><th>Update?</th></tr>';
	foreach($resultm as $resm){
		echo'<form method="post" action="vto.php?w=UpdateProcess&VTOID='.$resm['VTOID'].'">
		<tr><td><input type="text" name="TenYrGoal" value="'.$resm['TenYrGoal'].'"></td><td><input type="text" name="Niche" value="'.$resm['Niche'].'"></td><td><input type="text" name="Process" value="'.$resm['Process'].'"></td><td><input type="text" name="Guarantee" value="'.$resm['Guarantee'].'"></td><td><input type="text" name="3YrFutureDate" value="'.$resm['3YrFutureDate'].'"></td><td><input type="text" name="3YrRevenue" value="'.$resm['3YrRevenue'].'"></td><td><input type="text" name="3YrProfit" value="'.$resm['3YrProfit'].'"></td><td><input type="text" name="3YrMeasurables" value="'.$resm['3YrMeasurables'].'"></td><td><input type="text" name="1YrFutureDate" value="'.$resm['1YrFutureDate'].'"></td><td><input type="text" name="1YrRevenue" value="'.$resm['1YrRevenue'].'"></td><td><input type="text" name="1YrProfit" value="'.$resm['1YrProfit'].'"></td><td><input type="text" name="1YrMeasurables" value="'.$resm['1YrMeasurables'].'"></td><td><input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'"><input type="submit" name="submit" value="Update?"></td></tr>
		</form>';
	}

	echo'</table></div></br>';

	//Encode Details
		echo'<div style="border:1px solid black; width:520px; padding:5px"></br><h3>Encode Details</h3></br>
		<form method="post" action="vto.php?w=EncodeProcess">
				<b>VTOList:</b> <input type="text" name="VTOList" list="vtos">
				<b>Details:</b> <input type="text" name="Details">
				<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
				<input type="submit" name="submit">
			</form></br>';
			
			$title='';
			$sql='select * from eos_1vtolisttype vlt join eos_2vtosub vs on vs.VTOListID=vlt.VTOListID Group By vlt.VTOListID';
			$stmt=$link->query($sql); $result=$stmt->fetchAll();
			echo '<table id="table">';
				foreach($result as $res){
					echo'<tr><th>'.$res['VTOList'].'</th><th>Edit?</th><th>Delete?</th></tr>';
					$sqls='select * from eos_2vtosub where VTOListID=\''.$res['VTOListID'].'\'';
					$stmts=$link->query($sqls); $results=$stmts->fetchAll();
				foreach($results as $ress){
					echo'<tr><td>'.$ress['Details'].'</td><td><a href="vto.php?w=Edit&VTOSubId='.$ress['VTOSubId'].'">edit</a></td><td><a href="vto.php?w=Delete&VTOSubId='.$ress['VTOSubId'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">Delete</a></td></tr>';
				}

				}
			echo'</table></div></br>';
	}
	if (allowedToOpen(1610,'1rtc')) {
			//Update Quarter Information
			echo'<div style="border:1px solid black; width:805px; padding:5px">';
	$sqlm='select * from eos_2vtoqtrmain';
	$stmtm=$link->query($sqlm); $resultm=$stmtm->fetchAll();
	echo '<h3>Update Quarter Information</h3></br><table id="table">';
	echo'<tr><th>QtrFutureDate</th><th>QtrRevenue</th><th>QtrProfit</th><th>QtrMeasurables</th><th>QtrThemeMancom</th><th>QtrTheme</th><th>Update?</th></tr>';
	foreach($resultm as $resm){
		echo'<form method="post" action="vto.php?w=UpdateProcessQtr&VTOQtrId='.$resm['VTOQtrId'].'">
		<tr><td><input type="text" name="QtrFutureDate" value="'.$resm['QtrFutureDate'].'"></td>
		<td><input type="text" name="QtrRevenue" value="'.$resm['QtrRevenue'].'"></td>
		<td><input type="text" name="QtrProfit" value="'.$resm['QtrProfit'].'"></td>
		<td><input type="text" name="QtrMeasurables" value="'.$resm['QtrMeasurables'].'"></td>
		<td><input type="text" name="QtrThemeMancom" value="'.$resm['QtrThemeMancom'].'"></td>
		<td><input type="text" name="QtrTheme" value="'.$resm['QtrTheme'].'"></td>
		<td><input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		<input type="submit" name="submit" value="Update?"></td></tr>
		</form>';
	}

	echo'</table></div></br>';
	
			
			
	}
	break;
	
	
	
	case 'Rocks':
	
		$subtitle='Rocks';
		echo '<title>'.$subtitle.'</title>';
		$isrock=1;
	
	echo '<h2>'.$subtitle.'</h2>';
	echo '</br><div style="background-color:white;"><b>ENCODING: </b><br></br>';
	echo '<form method="post" action="vto.php?w=EncodeProcessQtr&IsRock=1" autocomplete="off">
		<b>Quarter:</b> <input type="text" name="Quarter" list="vtoqs" size="3" required>
		<b>Rocks:</b> <input type="text" name="Rocks" size="40">
		<b>Who:</b> <input type="text" name="Fullname" list="employees" value="'.$defaultwho.'">
		<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		<input type="submit" name="submit">
	</form><br>';
	echo '</div></br>';
	
	echo comboBox($link,'SELECT IDNo, CONCAT(Nickname," ",SurName) AS FullName FROM 1employees WHERE IDNo IN (SELECT DISTINCT(Who) FROM eos_2vtoqtrsub WHERE '.$mancomordeptcondi.' IsRock='.$isrock.')','FullName','IDNo','rockwholist');
	echo '<form method="post" action="vto.php?w='.$subtitle.'">
		<b>Who:</b> <input type="text" name="Who" list="rockwholist" size="10" required>';
	
		echo '<input type="submit" name="btnWho" value="Lookup">
	</form><br>';
	
	$addlcond='';
	if(isset($_POST['btnWho'])){
		$idno=$_POST['Who'];
		$addlcond=' AND Who='.$idno.' ';
		$showedit=0;
	} else {
		$idno=$_SESSION['(ak0)'];
		$addlcond=' AND Who='.$idno;
		$showedit=1;
	}
	$addlcond.=' ORDER BY Priority';
	$who=comboBoxValue($link, 'attend_30currentpositions', 'IDNo', $idno, 'FullName');
	
	
	echo '<div class="tabs">';
	
	$sqlmain='select vqs.*,IF(RIGHT(RIStatPerWeek,1)=1,"green","red") as bullcolor,CONCAT(Nickname,\' \',SurName) as Fullname from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who  WHERE '.$mancomordeptcondi.' Stat=0 AND IsRock='.$isrock.' ';
	echo '<br><h3>Who: '.$who.'</h3>';
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
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td><td style="padding:3px;width:70px;">'.((($idno==$_SESSION['(ak0)'] AND $qtr<=1) or allowedToOpen(1614,'1rtc'))?'<a href="vto.php?subtitle='.$subtitle.'&w=EditQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'">'.$imgedit.'</a> <a href="vto.php?go='.$isrock.'&w=DeleteQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">'.$imgdel.'</a> <a href="vto.php?go='.$isrock.'&w=CancelQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Cancel?\');">'.$imgcancel.'</a>':'').'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	echo '<td valign="top" valign="top" style="width:25%;'.($qtr==2?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=2 '.$addlcond.'';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th></th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td><td style="padding:3px;width:70px;">'.((($idno==$_SESSION['(ak0)'] AND $qtr<=2) or allowedToOpen(1614,'1rtc'))?'<a href="vto.php?subtitle='.$subtitle.'&w=EditQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'">'.$imgedit.'</a> <a href="vto.php?go='.$isrock.'&w=DeleteQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">'.$imgdel.'</a> <a href="vto.php?go='.$isrock.'&w=CancelQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Cancel?\');">'.$imgcancel.'</a>':'').'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	echo '<td valign="top" valign="top" style="width:25%;'.($qtr==3?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=3 '.$addlcond.'';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th></th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td><td style="padding:3px;width:70px;">'.((($idno==$_SESSION['(ak0)']  AND $qtr<=3) or allowedToOpen(1614,'1rtc'))?'<a href="vto.php?subtitle='.$subtitle.'&w=EditQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'">'.$imgedit.'</a> <a href="vto.php?go='.$isrock.'&w=DeleteQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">'.$imgdel.'</a> <a href="vto.php?go='.$isrock.'&w=CancelQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Cancel?\');">'.$imgcancel.'</a>':'').'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	echo '<td valign="top" valign="top" style="width:25%;'.($qtr==4?$hlt:'').'">';
		$sql=$sqlmain.' AND VTOQtrId=4 '.$addlcond.'';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
		echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th></th></tr>';
			foreach($result as $ress){
				echo'<tr><td style="padding:3px;"><font color="'.$ress['bullcolor'].'">&#8226; </font>'.$ress['RockOrIssues'].'</td><td style="padding:3px;width:70px;">'.((($idno==$_SESSION['(ak0)'] AND $qtr<=4) or allowedToOpen(1614,'1rtc'))?'<a href="vto.php?subtitle='.$subtitle.'&w=EditQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'">'.$imgedit.'</a> <a href="vto.php?go='.$isrock.'&w=DeleteQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">'.$imgdel.'</a> <a href="vto.php?go='.$isrock.'&w=CancelQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Cancel?\');">'.$imgcancel.'</a>':'').'</td></tr>';
			}
			
		echo'</table>';
		echo '</td>';
	
	echo '</tr>';
	
	echo '</table>';
	echo '</div>';
	
	echo '</div>';
	
	echo '</div>';
	
	//Canceled Rocks
	$isrock=1; //rocks
	$subtitle='Canceled Rocks';
	
	echo '</br></br><h2>'.$subtitle.'</h2><br>';
	$addlcond='';
	
	
	echo '<div class="tabs">
		';
	
	$sqlmain='select vqs.*,CONCAT(Nickname,\' \',SurName) as Fullname from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who  WHERE '.$mancomordeptcondi.' IsRock='.$isrock.' AND `Stat`=1';
	
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	
	$sql=$sqlmain.' ORDER BY vqs.TimeStamp DESC';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th>Who</th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.$ress['Fullname'].'</td></tr>';
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
	echo '<div style="background-color:white;"><b>ENCODING: </b><br></br>';
	echo '<form method="post" action="vto.php?w=EncodeProcessQtr&IsRock=0" autocomplete="off">
		<input type="hidden" name="Quarter" value="0">
		<b>Issue:</b> <input type="text" name="Rocks" size="50">
		<b>Who:</b> <input type="text" name="Fullname" list="employees" value="'.$defaultwho.'">
		<b>Priority:</b> <input type="number" min="1" max="3" name="Priority" style="width:40;">
		<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		<input type="submit" name="submit">
	</form><br>';
	echo '</div></br>';
	
	echo comboBox($link,'SELECT IDNo, CONCAT(Nickname," ",SurName) AS FullName FROM 1employees WHERE IDNo IN (SELECT DISTINCT(Who) FROM eos_2vtoqtrsub WHERE '.$mancomordeptcondi.' IsRock='.$isrock.')','FullName','IDNo','rockwholist');
	echo '<form method="post" action="vto.php?w='.$subtitle.'">
		<b>Who:</b> <input type="text" name="Who" list="rockwholist" size="10" required>
		<input type="submit" name="btnWho" value="Lookup">
	</form><br>';
	
	$addlcond='';
	if(isset($_POST['btnWho'])){
		$idno=$_POST['Who'];
		$addlcond=' AND Who='.$idno;
		$showedit=0;
	} else {
		$idno=$_SESSION['(ak0)'];
		$addlcond=' AND Who='.$idno;
		$showedit=1;
	}
	
	$who=comboBoxValue($link, 'attend_30currentpositions', 'IDNo', $idno, 'FullName');
	echo '<div class="tabs">
		';
	
	$sqlmain='select vqs.*,CONCAT(Nickname,\' \',SurName) as Fullname from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who WHERE '.$mancomordeptcondi.' IsRock='.$isrock.' AND `Stat`=0';
	// echo $sqlmain; exit();
	echo '<br><h3>Who: '.$who.'</h3>';
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	$sql=$sqlmain.' '.$addlcond.' AND (RIGHT(RIStatPerWeek,1)=0 OR RIGHT(RIStatPerWeek,1) IS NULL) ORDER BY VTOQtrId,Priority';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th align="center">'.$subtitle.' - Pending/Not Resolved</th><th>Priority</th><th></th></tr>';
		foreach($result as $ress){
			echo'<tr style="'.($ress['VTOQtrId']==$qtr?'background-color:#FFFACD;':'').'"><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.$ress['Priority'].'</td><td style="padding:3px;">'.(($idno==$_SESSION['(ak0)'] or allowedToOpen(1614,'1rtc'))?'<a href="vto.php?subtitle='.$subtitle.'&w=EditQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'">'.$imgedit.'</a> <a href="vto.php?go='.$isrock.'&w=DeleteQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">'.$imgdel.'</a>':'').'</td></tr>';
		}
		
	echo'</table>';
	
	echo '<br>';
	$sql=$sqlmain.' '.$addlcond.' AND RIGHT(RIStatPerWeek,1)=1 ORDER BY VTOQtrId,Priority';
	// echo $sql;
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
	
	
	
	case 'ToDo':
	$isrock=2; //todo
	$subtitle='ToDo';
	echo '<title>'.$subtitle.'</title>';
	echo '<h2>'.$subtitle.'</h2>';
	
	echo '</br><div style="background-color:white;"><b>ENCODING: <b><br></br>';
	echo '<form method="post" action="vto.php?w=EncodeProcessQtr&IsRock=2" autocomplete="off">
		<input type="hidden" name="Quarter" value="0">
		<b>To-Do:</b> <input type="text" name="Rocks" size="50">
		<b>DueDate:</b> <input type="date" name="DueDate">
		<b>Who:</b> <input type="text" name="Fullname" list="employees" value="'.$defaultwho.'">
		<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		<input type="submit" name="submit">
	</form><br>';
	echo '</div></br>';
	
	echo comboBox($link,'SELECT IDNo, CONCAT(Nickname," ",SurName) AS FullName FROM 1employees WHERE IDNo IN (SELECT DISTINCT(Who) FROM eos_2vtoqtrsub WHERE '.$mancomordeptcondi.' IsRock='.$isrock.')','FullName','IDNo','rockwholist');
	echo '<form method="post" action="vto.php?w='.$subtitle.'">
		<b>Who:</b> <input type="text" name="Who" list="rockwholist" size="10" required>
		<input type="submit" name="btnWho" value="Lookup">
	</form><br>';
	
	$addlcond='';
	if(isset($_POST['btnWho'])){
		$idno=$_POST['Who'];
		$addlcond=' AND Who='.$idno;
		$showedit=0;
	} else {
		$idno=$_SESSION['(ak0)'];
		$addlcond=' AND Who='.$idno;
		$showedit=1;
	}
	
	$who=comboBoxValue($link, 'attend_30currentpositions', 'IDNo', $idno, 'FullName');
	echo '<div class="tabs">
		';
	
	$sqlmain='select vqs.*,CONCAT(Nickname,\' \',SurName) as Fullname, IF(ManComOrdept=-1,"Mancom",Dept) AS Dept from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who LEFT JOIN 1departments d ON d.deptid=vqs.ManComOrdept WHERE  IsRock='.$isrock.' AND `Stat`=0 '; //'.$mancomordeptcondi.'

	echo '<br><h3>Who: '.$who.'</h3>';
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	$sql=$sqlmain.' '.$addlcond.' AND (RIGHT(RIStatPerWeek,1)=0 OR RIGHT(RIStatPerWeek,1) IS NULL) ORDER BY Dept';
	//if ($_SESSION['(ak0)']==1002) {echo $sql;}
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th>Department</th><th>'.$subtitle.' - Pending/UnDone</th><th>DueDate</th><th></th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['Dept'].'</td><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td>'.$ress['RemarksOrResolution'].'</td><td style="padding:3px;">'.(($idno==$_SESSION['(ak0)'] or allowedToOpen(1614,'1rtc'))?'<a href="vto.php?subtitle='.$subtitle.'&w=EditQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'">'.$imgedit.'</a> <a href="vto.php?go='.$isrock.'&w=DeleteQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">'.$imgdel.'</a> <a href="vto.php?go='.$isrock.'&w=CancelQtr&VTOQtrSubId='.$ress['VTOQtrSubId'].'" OnClick="return confirm(\'Are you sure you want to Cancel?\');">'.$imgcancel.'</a>':'').'</td></tr>';
		}
		
	echo'</table>';
	
	echo '<br>';
	$sql=$sqlmain.' '.$addlcond.' AND RIGHT(RIStatPerWeek,1)=1';
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
	
	//Canceled To-Do
	$isrock=2; //todo
	$subtitle='Canceled To-Do';
	
	echo '</br></br><h2>'.$subtitle.'</h2><br>';
	$addlcond='';
	
	
	echo '<div class="tabs">
		';
	
	$sqlmain='select *,CONCAT(Nickname,\' \',SurName) as Fullname from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who  WHERE '.$mancomordeptcondi.' IsRock='.$isrock.' AND `Stat`=1';
	
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	
	$sql=$sqlmain.' ORDER BY vqs.TimeStamp DESC';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
	echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th>Who</th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.$ress['Fullname'].'</td></tr>';
		}
		
	echo'</table>';
	
	echo '</div>';

	echo '</div>';
	
	echo '</div>';
	
	break;
	
	case'MtgUpdate':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	if($_POST['MtgStatus']=='No'){
		$mtgval=0;
	}else{
		$mtgval=1;
	}
	// echo $_POST['MtgStatus']; exit();
		$sqls='select substring(substring_index(WeekNoAndStatus,\',\',1),4,100) AS Status from eos_1mtgstatus where '.($iddept<>-100?'deptid='.$iddept.'':'2=2').'';
		$stmts=$link->query($sqls); $results=$stmts->fetch();
	if($stmts->rowCount()!=0){
		$sqlu='update eos_1mtgstatus set EncodedByNo='.$_SESSION['(ak0)'].',`TimeStamp`=NOW(),WeekNoAndStatus=REPLACE(WeekNoAndStatus,substring_index(WeekNoAndStatus,\',\',1),"'.$lastweekno.'-'.$mtgval.'") where deptid='.$iddept.'';
		// echo $sqlu; exit();
		$stmtu=$link->prepare($sqlu); $stmtu->execute();
	}else{
		$weekno=$weekno-1;
		$sqli='insert into eos_1mtgstatus set EncodedByNo='.$_SESSION['(ak0)'].',`TimeStamp`=NOW(),deptid='.$iddept.',WeekNoAndStatus=\''.$weekno.'-'.$mtgval.'\'';
		// echo $sqli; exit();
		$stmti=$link->prepare($sqli); $stmti->execute();
	}
	header('Location:vto.php?w=WeeklyUpdates');
	
	break;
	
	case'MeetingStatus':
	echo'<title>Update Meeting Status</title>';
		echo'<h3>Is (Week '.$lastweekmeeting.': '.date("M d", strtotime(week_date($lastweekmeeting,$currentyr,$l10dayval))).') meeting done?</h3></br>
		<form method="post" action="vto.php?w=MtgUpdate">
		<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		<input type="submit" name="MtgStatus" value="Yes" OnClick="return confirm(\'Are you sure you want to submit?\');">'.str_repeat('&nbsp;',5).'
		<input type="submit" name="MtgStatus" value="No" OnClick="return confirm(\'Are you sure you want to submit?\');">
		</form>';
		
	break;
	
	case'ScorecardStatus':

		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$scid = intval($_GET['SCID']);
		$sql='Update `eos_2scorecard` SET Status=IF(Status=1,0,1) WHERE SCID=\''.$scid.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:vto.php?w=WeeklyUpdates");

	break;
	
	case 'WeeklyUpdates':
	echo '<title>Meeting Updates</title>';
	
	
	echo '<h3>Meeting Updates (Week '.$weekno.': '.date("M d", strtotime(week_date($weekno,$currentyr,$l10dayval))).')</h3>';
	
// mtg status
	if(($l10dayval<date('w') and $status==0) OR $status==0){
				
	echo'</br><b><a href="vto.php?w=MeetingStatus">Is (Week '.$lastweekmeeting.': '.date("M d", strtotime(week_date($lastweekmeeting,$currentyr,$l10dayval))).') meeting done?</a></b></br>';
	}
//
	$addcon=' HAVING (WeekNo>'.($weekno-1).' OR WeekNo IS NULL OR `Status`=0)';
	
	$sqlmain='select vqs.*,RIGHT(RIStatPerWeek,4) AS WhatWkNo,RIGHT((SELECT WhatWkNo),1) AS `Status`, LEFT((SELECT WhatWkNo),2) AS WeekNo,CONCAT(Nickname,\' \',SurName) as Fullname from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who WHERE '.$mancomordeptcondi.' Stat=0 AND Who='.$_SESSION['(ak0)'];
	
	// echo $sqlmain;
	//scorecard

$sql='select *,substring(substring_index(WeekNoAndScores,\',\',1),4,100) AS Scores,substring(WeekNoAndScores,1,2) as WeekNo,if(CountedforEval=1,"Counted","NotCounted") as CountedforEval from eos_2scorecard where '.$mancomordeptcondi.' Who=\''.$_SESSION['(ak0)'].'\' and Status=0';
$stmt=$link->query($sql); $result=$stmt->fetchAll();
	echo '<br><b>Scorecard</b>';			
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>Measurables</th><th>Goal</th><th>CountedforEval</th><th>Scores</th><th></th></tr>';
		foreach($result as $res){
			if($res['WeekNo']==$weekno){
				$input='<input OnClick="return confirm(\'Are you sure you want to Overwrite?\');" type="submit" name="submit" value="Overwrite">';
				$value=$res['Scores'];
				
			}else{
				$input='<input OnClick="return confirm(\'Are you sure you want to Submit?\');" type="submit" name="submit" value="Submit">';
				$value='';
			}
			echo'<tr>
					<td style="padding:3px;">'.$res['Measurables'].'</td><td style="padding:3px;">'.$res['Goal'].'</td><td style="padding:3px;">'.$res['CountedforEval'].'</td>
					<td><form method="post" action="vto.php?w=UpdateScore"><input type="text" size="10" name="Scores" placeholder="Scores" value="'.$value.'" required><input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'"><input type="hidden" name="SCID" value="'.$res['SCID'].'">'.$input.'</form></td><td style="padding:3px;">
					<a  OnClick="return confirm(\'Are you sure?\');" href="vto.php?w=ScorecardStatus&SCID='.$res['SCID'].'&action_token='.$_SESSION['action_token'].'">Done</a></td>
				</tr>';
		}
	echo'</table>';
	
	
	$sql=$sqlmain.' AND VTOQtrId='.$qtr.' AND IsRock=1 HAVING ISNULL(`Status`) OR `Status`=0 OR `Status`=1';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<br><b>Rocks</b>';
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>Rock</th><th colspan=2>Status</th><th colspan=1></th></tr>';
		foreach($result as $ress){
			// $otlink='vto.php?w=OnOffTrackProcess&VTOQtrSubId='.$ress['VTOQtrSubId'].'&action_token='.$_SESSION['action_token'];
			// echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.(($weekno==$ress['WeekNo'] AND $ress['Status']==1)?'On-Track':(($weekno==$ress['WeekNo'] AND $ress['Status']==0)?'Off-Track':(($weekno==$ress['WeekNo'] AND $ress['Status']==2)?'Done':''))).'</td><td style="padding:3px;"><a href="'.$otlink.'&Track=1">On-Track</a></td><td style="padding:3px;"><a href="'.$otlink.'&Track=0">Off-Track</a></td></td><td style="padding:3px;"><a href="'.$otlink.'&Track=2">Done</a></td></tr>';
			$otlink='vto.php?w=OnOffTrackProcess&VTOQtrSubId='.$ress['VTOQtrSubId'].'&action_token='.$_SESSION['action_token'];
			echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.(($weekno==$ress['WeekNo'] AND $ress['Status']==1)?'On-Track':(($weekno==$ress['WeekNo'] AND $ress['Status']==0)?'Off-Track':(($weekno==$ress['WeekNo'] AND $ress['Status']==2)?'Done':''))).'</td><td style="padding:3px;">'.$ress['RemarksOrResolution'].'</td><td style="padding:3px;"><form action="'.$otlink.'" method="POST" autocomplete="off"><input type="text" size="20" name="Remarks" value="'.$ress['RemarksOrResolution'].'" maxlength="20"> <input type="submit" name="btnOnTrack" value="On-Track" style="color:green;"> <input type="submit" name="btnOffTrack" value="Off-Track" style="color:red;"> <input type="submit" name="btnRockDone" value="Done" style="color:blue;"></form></td></tr>';
		}
	echo'</table>';
	
	
	
	echo '<br><b>To-Do</b>';
	
	$sql=$sqlmain.' AND IsRock=2 '.$addcon.' ORDER BY Priority';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>To-Do</th><th>Status</th><th colspan=2></th></tr>';
		foreach($result as $ress){
			$otlink='vto.php?w=DoneNotDone&VTOQtrSubId='.$ress['VTOQtrSubId'].'&action_token='.$_SESSION['action_token'];
			echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td style="padding:3px;">'.(($weekno==$ress['WeekNo'] AND $ress['Status']==1)?'Done':(($weekno==$ress['WeekNo'] AND $ress['Status']==0)?'Not Done':'')).'</td><td style="padding:3px;"><a href="'.$otlink.'&Done=1">Done</a></td><td style="padding:3px;"><a href="'.$otlink.'&Done=0">Not Done</a></td></tr>';
		}
	echo'</table>';
	
	
	$sql=$sqlmain.' AND VTOQtrId<='.$qtr.' '.$addcon.' AND IsRock=0 ORDER BY Priority';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<br><b>Issues</b>';
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>Issues</th><th>Priority</th><th>Status</th><th>Resolution</th></tr>';
		foreach($result as $ress){
			$otlink='vto.php?w=ResolveNotResolve&VTOQtrSubId='.$ress['VTOQtrSubId'].'&action_token='.$_SESSION['action_token'];
			echo'<tr><td style="padding:3px;">'.$ress['RockOrIssues'].'</td><td align="center" style="padding:3px;">'.$ress['Priority'].'</td><td style="padding:3px;">'.(($weekno==$ress['WeekNo'] AND $ress['Status']==1)?'Resolved':(($weekno==$ress['WeekNo'] AND $ress['Status']==0)?'Not Resolved':'')).'</td><td style="padding:3px;"><form action="'.$otlink.'" method="POST" autocomplete="off"><input type="text" size="40" name="Resolution" value="'.$ress['RemarksOrResolution'].'"><input type="submit" name="btnNotResolved" value="Not Resolved"> <input type="submit" name="btnResolved" value="Resolved"></form></td></tr>';
			//<td style="padding:3px;"><a href="'.$otlink.'&Resolve=0">Not Resolved</a></td>
		}
	echo'</table>';
	

	
	break;
	
	case'UpdateScore':
if($_POST['submit']=='Submit'){
	$set='(case
	when WeekNoAndScores is not null then CONCAT("'.$weekno.'-'.$_POST['Scores'].',",WeekNoAndScores)
	else CONCAT("'.$weekno.'-'.$_POST['Scores'].'")
	end)';
	
}else{
	$set='(case
	when WeekNoAndScores is not null then REPLACE(WeekNoAndScores,substring_index(WeekNoAndScores,\',\',1),"'.$weekno.'-'.$_POST['Scores'].'")
	else REPLACE(WeekNoAndScores,WeekNoAndScores,"'.$weekno.'-'.$_POST['Scores'].'")
	end)';
}
		$sql='UPDATE eos_2scorecard set WeekNoAndScores='.$set.' WHERE SCID='.$_REQUEST['SCID'];
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:vto.php?w=WeeklyUpdates");
	break;
	
	
	case 'OnOffTrackProcess':
	// print_r($_POST);
	if(isset($_POST['btnOnTrack'])){
		$trackstat=1;
	}
	else if($_POST['btnOffTrack']){
		$trackstat=0;
	}
	else {
		$trackstat=2;
	}

	// echo '<br><br>'.$trackstat; exit();

	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='UPDATE eos_2vtoqtrsub set RemarksOrResolution="'.addslashes($_POST['Remarks']).'",RIStatPerWeek=(CASE 
	WHEN RIGHT(RIStatPerWeek,4)="'.$weekno.'-1" THEN REPLACE(RIStatPerWeek,"'.$weekno.'-1","'.$weekno.'-'.$trackstat.'") 
	WHEN RIGHT(RIStatPerWeek,4)="'.$weekno.'-0" THEN REPLACE(RIStatPerWeek,"'.$weekno.'-0","'.$weekno.'-'.$trackstat.'")
	WHEN RIGHT(RIStatPerWeek,4)="'.$weekno.'-2" THEN REPLACE(RIStatPerWeek,"'.$weekno.'-2","'.$weekno.'-'.$trackstat.'")
	WHEN RIGHT(RIStatPerWeek,4)=0 THEN "'.$weekno.'-'.$trackstat.'"
    ELSE IFNULL(CONCAT(RIStatPerWeek,",'.$weekno.'-'.$trackstat.'"),"'.$weekno.'-'.$trackstat.'")
END),EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE VTOQtrSubId='.$_GET['VTOQtrSubId'];
	
	
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:vto.php?w=WeeklyUpdates");
	
	break;
	
	
	case 'DoneNotDone':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
	
	$sql='UPDATE eos_2vtoqtrsub set RIStatPerWeek=(CASE 
	WHEN RIGHT(RIStatPerWeek,4)="'.$weekno.'-1" THEN REPLACE(RIStatPerWeek,"'.$weekno.'-1","'.$weekno.'-'.$_GET['Done'].'") 
	WHEN RIGHT(RIStatPerWeek,4)="'.$weekno.'-0" THEN REPLACE(RIStatPerWeek,"'.$weekno.'-0","'.$weekno.'-'.$_GET['Done'].'")
	WHEN RIGHT(RIStatPerWeek,4)=0 THEN "'.$weekno.'-'.$_GET['Done'].'"
    ELSE IFNULL(CONCAT(RIStatPerWeek,",'.$weekno.'-'.$_GET['Done'].'"),"'.$weekno.'-'.$_GET['Done'].'")
END),EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE VTOQtrSubId='.$_GET['VTOQtrSubId'];
	
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:vto.php?w=WeeklyUpdates");
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
	
	$sql='UPDATE eos_2vtoqtrsub set '.$addlsql.'RIStatPerWeek=(CASE 
		WHEN RIGHT(RIStatPerWeek,4)="'.$weekno.'-1" THEN REPLACE(RIStatPerWeek,"'.$weekno.'-1","'.$weekno.'-'.$rnr.'") 
		WHEN RIGHT(RIStatPerWeek,4)="'.$weekno.'-0" THEN REPLACE(RIStatPerWeek,"'.$weekno.'-0","'.$weekno.'-'.$rnr.'")
		WHEN RIGHT(RIStatPerWeek,4)=0 THEN "'.$weekno.'-'.$rnr.'"
		ELSE IFNULL(CONCAT(RIStatPerWeek,",'.$weekno.'-'.$rnr.'"),"'.$weekno.'-'.$rnr.'")
	END),EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE VTOQtrSubId='.$_GET['VTOQtrSubId'];
	
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	if(isset($_GET['FromWeeklyMeeting'])){
		if($_GET['FromWeeklyMeeting']==1){
			header("Location:vto.php?w=WeeklyMeeting");
		} else {
			header("Location:vto.php?w=WeeklyMeetingIssues");
		}
	} else {
		header("Location:vto.php?w=WeeklyUpdates");
	}
	
	
	break;
	
	
	case'EncodeProcessQtr':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$prioritysql=''; $duedatesql='';
	if($_GET['IsRock']==0){
		$prioritysql='Priority='.$_POST['Priority'].',';
		
	}
	if($_GET['IsRock']==2){
		$duedatesql='RemarksOrResolution="'.$_POST['DueDate'].'",';
		
	}
	if(isset($_SESSION['deptonly'])){
		$mod=$_SESSION['deptonly'];
	} else {
		$mod='-1';
	}
	
	$employee=comboBoxValue($link, '1employees', 'CONCAT(Nickname,\' \',SurName)', $_REQUEST['Fullname'], 'IDNo');	
		$sql='Insert into eos_2vtoqtrsub set '.$duedatesql.''.$prioritysql.'IsRock='.$_GET['IsRock'].',RockOrIssues=\''.addslashes($_POST['Rocks']).'\',VTOQtrId=\''.$_POST['Quarter'].'\',Who=\''.$employee.'\',ManComOrdept="'.$mod.'",EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW();';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		if($_GET['IsRock']==1){
			$go='Rocks';
		} else if($_GET['IsRock']==0){
			$go='Issues';
		} else {
			$go='ToDo';
		}
		header("Location:vto.php?w=".$go."");
	break;
	
	case'EncodeProcessMeasurables':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$who=comboBoxValue($link, '1employees', 'CONCAT(Nickname,\' \',SurName)', $_REQUEST['Fullname'], 'IDNo');
	if(isset($_SESSION['deptonly'])){
		$mod=$_SESSION['deptonly'];
	} else {
		$mod='-1';
	}
	
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
		$sql='Insert into eos_2scorecard set Measurables=\''.$_POST['Measurables'].'\',ManComOrdept="'.$mod.'",Goal=\''.$_POST['Goal'].'\',Who=\''.$who.'\',HigherOrLower=\''.$hol.'\',CountedforEval=\''.$countedforeval.'\' ';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:vto.php?w=ScorecardList");
	break;
	
	
	case'ScorecardList':
		
	$subtitle='Scorecard Measurables';
	echo '<title>'.$subtitle.'</title>';
	echo '<h2>'.$subtitle.'</h2>';
	
	echo'</br><div style="background-color:white;"><b>ENCODING: </b></br></br>
		<form method="post" action="vto.php?w=EncodeProcessMeasurables" autocomplete="off">
			<b>Measurable:</b> <input type="text" name="Measurables" size="25">
			<b>Goal Per Week:</b> <input type="text" name="Goal" size="5">
			<b>Who:</b> <input type="text" name="Fullname" list="employees" value="'.$defaultwho.'">
			<b>Higher Or Lower:</b> <input type="text" name="HigherOrLower" list="higherorlowerlist">
<datalist id="higherorlowerlist">
    <option value="Higher is Better" label="0"></option>
    <option value="Lower is Better" label="-1"></option>
</datalist>
<b>CountedforEval:</b> <input type="text" name="CountedforEval" list="countednotcountedlist" size="7">
			<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
			 <input type="submit" name="submit">
			</form></br></div></br>';
	
	echo '<form method="post" action="vto.php?w=ScorecardList">
		<b>Who:</b> <input type="text" name="Who" list="employees1" size="10">
		<input type="submit" name="btnWho" value="Lookup">
	</form><br>';
	
	$addlcond='';
	if(isset($_POST['btnWho'])){
		$idno=comboBoxValue($link, 'attend_30currentpositions', 'IDNo', $_POST['Who'], 'IDNo');
		$cond=' Who='.$idno;
		$showedit=0;
	} else {
		$idno=$_SESSION['(ak0)'];
		$cond=' Who='.$idno;
		$showedit=1;
	}
	
	$who=comboBoxValue($link, 'attend_30currentpositions', 'IDNo', $idno, 'FullName');
	echo '<div class="tabs">
		';
	echo '<br><h3>Who: '.$who.'</h3>';
	echo '<div class="tab-content">';
	
	echo '<div id="tab1" class="tab active">';
	$sql='select *,CONCAT(Nickname,\' \',SurName) as Fullname,if(HigherOrLower=0,"Higher is Better","Lower is Better") as HigherOrLower,if(CountedforEval=1,"Counted","NotCounted") as CountedforEval from eos_2scorecard m left join 1employees e on e.IDNo=m.Who  WHERE '.$mancomordeptcondi.' '.$cond.' and Status=0';
	// echo $sql; exit();
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	echo '<table border="1px solid black" style="border-collapse:collapse;"><b>ACTIVE</b>';
	echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th>Goal Per Week</th><th>Higher Or Lower</th><th>CountedforEval</th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['Measurables'].'</td><td style="padding:3px;">'.$ress['Goal'].'</td><td style="padding:3px;">'.$ress['HigherOrLower'].'</td><td style="padding:3px;">'.$ress['CountedforEval'].'</td>'.(($idno==$_SESSION['(ak0)'] or allowedToOpen(1614,'1rtc'))?'<td style="padding:3px;"><a href="vto.php?w=EditMeasurables&SCID='.$ress['SCID'].'">Edit</a> <a href="vto.php?w=DeleteMeasurables&SCID='.$ress['SCID'].'" OnClick="return confirm(\'Are you sure you want to Delete?\');">Delete</a></td>':'').'</tr>';
		}
		
	echo'</table>';
	
//DONE	
$sql='select *,CONCAT(Nickname,\' \',SurName) as Fullname,if(HigherOrLower=0,"Higher is Better","Lower is Better") as HigherOrLower from eos_2scorecard m left join 1employees e on e.IDNo=m.Who  WHERE '.$mancomordeptcondi.' '.$cond.' and Status=1';
	// echo $sql; exit();
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	echo '</br><table border="1px solid black" style="border-collapse:collapse;"><b>DONE</b>';
	echo '<tr style="background-color:skyblue;"><th>'.$subtitle.'</th><th>Goal Per Week</th><th>Higher Or Lower</th><th></th></tr>';
		foreach($result as $ress){
			echo'<tr><td style="padding:3px;">'.$ress['Measurables'].'</td><td style="padding:3px;">'.$ress['Goal'].'</td><td style="padding:3px;">'.$ress['HigherOrLower'].'</td><td><a  OnClick="return confirm(\'Are you sure?\');" href="vto.php?w=ScorecardStatus&SCID='.$ress['SCID'].'&action_token='.$_SESSION['action_token'].'">Not Done</a></td></tr>';
		}
		
	echo'</table>';
	
	echo'</div></div></div';	
	
	break;
	
	case'EditMeasurables':
	echo '<title>Edit Measurable</title>';
	$sql='select *,CONCAT(Nickname,\' \',SurName) as Who,if(HigherOrLower=0,"Higher is Better","Lower is Better") as HigherOrLower,if(CountedforEval=1,"Counted","NotCounted") as CountedforEval from eos_2scorecard sm left join 1employees e on e.IDNo=sm.Who where SCID=\''.$_GET['SCID'].'\'';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo'<h3>Edit?</h3></br><form method="post" action="vto.php?w=EditMeasurablesProcess&SCID='.$_GET['SCID'].'">
		 Measurable: <input type="text" name="Measurables" value="'.$result['Measurables'].'">
		 Goal: <input type="text" name="Goal" value="'.$result['Goal'].'">
		 Who: <input type="text" name="Fullname" value="'.$result['Who'].'" list="employees">
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
		$who=comboBoxValue($link, '1employees', 'CONCAT(Nickname,\' \',SurName)', $_REQUEST['Fullname'], 'IDNo');	
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
		
		$sql='update eos_2scorecard set Measurables=\''.$_POST['Measurables'].'\',Goal=\''.$_POST['Goal'].'\',Who=\''.$who.'\',HigherOrLower=\''.$hol.'\',CountedforEval=\''.$countedforeval.'\' where SCID=\''.$scid.'\'';
		
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:vto.php?w=ScorecardList");
	break;
		
	case'DeleteMeasurables':
	$scid = intval($_GET['SCID']);
	$sql='delete from eos_2scorecard where SCID=\''.$scid.'\'';
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:vto.php?w=ScorecardList");
	break;
	
	case 'WeeklyMeeting':
	echo '<title>Meeting - Measurables</title>';
	
	
	
	$weekNoNextMonday = date('W', strtotime('next '.$l10day.''));
	$weekNoNextMondayDate = date('Y-m-d', strtotime('next '.$l10day.''));


	$allmondays='<option value="'.$weekNoNextMonday.'">'.$weekNoNextMonday.' ('.$weekNoNextMondayDate.')</option>';
	for ($i = strtotime(date('Y-m-d')); $i >= strtotime($currentyr.'-01-01'); $i = strtotime('-1 day', $i)) {
			if (date('N', $i) == $l10dayval){
				$allmondays.='<option value="'.date('W', $i).'">'.date('W (Y-m-d)', $i).'</option>'; 
			}
		}
	
	echo '<form action="#" method="POST">';
	echo '<select name="WeekNo">';
	echo $allmondays;
	
	echo '</select>';
	echo ' <input type="submit" value="View Meeting">';
	echo '</form><br>';
	
	$rcondi='';
	if(isset($_POST['WeekNo']) AND $weekno<>$_POST['WeekNo']){
		$weekno=$_POST['WeekNo'];
		$adq=' AND RIStatPerWeek LIKE "%'.$weekno.'%"';
		$adq1=$adq;
		$openr=0;
	} else {
		$adq=' HAVING (LEFT(WeekNo,2)>'.($weekno-1).' OR WeekNo IS NULL OR `Status`=0)  AND (ISNULL(`Status`) OR `Status`=0 OR `Status`=1 )  ';
		$adq1=' HAVING (WeekNo IS NULL OR `Status`=0) ';
		$openr=1;
		$rcondi=' AND Resigned=0 ';
	}
	
	echo '<h3>Meeting - Measurables (Week '.$weekno.': '.date("M d", strtotime(week_date($weekno,$currentyr,$l10dayval))).')</h3>';
	
	
	
	$sqlmain='select vqs.*,CONCAT(Nickname," ",SurName) AS `Who?`,RIGHT(RIStatPerWeek,4) AS `WhatWkNo`,RIGHT((SELECT WhatWkNo),1) AS `Status`, LEFT((SELECT WhatWkNo),2) AS WeekNo,CONCAT(Nickname,\' \',SurName) as Fullname from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who WHERE '.$mancomordeptcondi.' Stat=0 '.$rcondi;
	
	// echo $sqlmain;

	echo '<br><b>Scorecard</b>';
$sql0='CREATE TEMPORARY TABLE weeksandscore (
  `SCID` int(11) NOT NULL ,
  `Measurables` varchar(50) NOT NULL ,
  `Goal` double NOT NULL ,
  `CountedforEval` tinyint(1) DEFAULT NULL,
  `Who` smallint(6) NOT NULL ,
  `Date` varchar(255) DEFAULT NULL,
  `HigherOrLower` tinyint(1) DEFAULT NULL,
  `Scores` double NOT NULL
);'; 
// echo $sql0.'<br>';
$stmt0=$link->prepare($sql0);$stmt0->execute();
		$sql='select * from eos_2scorecard where '.$mancomordeptcondi.' WeekNoAndScores is not null and Status=0';
		
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		foreach($result as $res){
			$weeknoandscores=explode(",",$res['WeekNoAndScores']);	
			foreach($weeknoandscores as $weeknoandscore){	
				$week=substr($weeknoandscore,0,2);		
				$date = date('Y-m-d',strtotime(sprintf('%4dW%02d',$yr,$week)));			
				$scores=substr($weeknoandscore,3,100);	
				$sqli='insert into weeksandscore set Measurables=\''.$res['Measurables'].'\',Goal=\''.$res['Goal'].'\',CountedforEval=\''.$res['CountedforEval'].'\',HigherOrLower=\''.$res['HigherOrLower'].'\',Who=\''.$res['Who'].'\',Date=\''.$date.'\',Scores=\''.$scores.'\',SCID=\''.$res['SCID'].'\';';
				$stmti=$link->prepare($sqli);$stmti->execute();
				// echo $sqli.'<br>';

			}
			
		}
		
//thead		
	$table='<table id="table"><tr>';
		$columndata=array('Who','Measurables','Goal','HigherOrLower');
		foreach($columndata as $data){
			$table.='<th>'.$data.'</th>';			
		}		
	$sqls='select *,week(Date) as weeknumber from  weeksandscore where week(Date) between week(curdate())-13 and week(curdate()) Group By Date Order By Date Desc';
	// echo $sqls; exit();
	$stmts=$link->query($sqls); $results=$stmts->fetchAll();
		$sqlss='select Measurables,Goal,if(CountedforEval=1,"Counted","NotCounted") as CountedforEval,CONCAT(Nickname,\' \',SurName) as Who,HigherOrLower as HigherOrLowerValue,if(HigherOrLower=0,"Higher is Better","Lower is Better") as HigherOrLower,';
	foreach($results as $res){
			$sqlss.='max(CASE WHEN Date=\''.$res['Date'].'\' then Scores END) as `'.$res['Date'].'`,';
			$columndata[]=''.$res['Date'].'';
			
			// $firstdayoftheweek = date('m-d',strtotime(sprintf('%4dW%02d',$yr,$res['weeknumber'])));
			$firstdayoftheweek = date('m-d',strtotime(week_date($res['weeknumber'],$currentyr,$l10dayval)));
			
			$table.='<th>'.$firstdayoftheweek.'</th>';
		}
			$table.='</tr>';
//end thead		
//tbody				
		$sqlss.='Who as `nothing` from weeksandscore m left join 1employees e on e.IDNo=m.Who Group By SCID';
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
		
		
	$sql=$sqlmain.' AND VTOQtrId='.$qtr.' AND IsRock=1 '.$adq.' ORDER BY `Status`,`Who?`';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	echo '<br><b>Rocks</b>';
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>Rock</th><th colspan=2>Status</th><th>Who</th></tr>';
	
		foreach($result as $ress){
			echo'<tr style="'.(($weekno==$ress['WeekNo'] AND ($ress['Status']==1 OR $ress['Status']==2))?'':(($weekno==$ress['WeekNo'] AND $ress['Status']==0)?'font-weight:bold;':'font-weight:bold;')).'"><td style="padding:5px;">'.$ress['RockOrIssues'].'</td><td style="padding:5px;'.(($weekno==$ress['WeekNo'] AND $ress['Status']==0)?'color:red;':'').'">'.(($weekno==$ress['WeekNo'] AND $ress['Status']==1)?'On-Track':(($weekno==$ress['WeekNo'] AND $ress['Status']==0)?'Off-Track':(($weekno==$ress['WeekNo'] AND $ress['Status']==2)?'Done':''))).'</td><td style="padding:5px;">'.$ress['RemarksOrResolution'].'</td><td style="padding:5px;">'.$ress['Who?'].'</td></tr>';
		}
	echo'</table>';
	
	
	
	echo '<br><b>To-Do</b>';
	
	$sql=$sqlmain.' AND IsRock=2 '.$adq.' ORDER BY `Stat`,`RemarksOrResolution`,`Who?`';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	
	
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>To-Do</th><th>DueDate</th><th>Status</th><th>Who</th></tr>';
		foreach($result as $ress){
			echo'<tr style="'.(($weekno==$ress['WeekNo'] AND $ress['Status']==1)?'':(($weekno==$ress['WeekNo'] AND $ress['Status']==0)?'font-weight:bold;':'font-weight:bold;')).'"><td style="padding:5px;">'.$ress['RockOrIssues'].'</td><td style="padding:5px;">'.$ress['RemarksOrResolution'].'</td><td style="padding:5px;'.(($weekno==$ress['WeekNo'] AND $ress['Status']==0)?'color:red;':'').'">'.(($weekno==$ress['WeekNo'] AND $ress['Status']==1)?'Done':(($weekno==$ress['WeekNo'] AND $ress['Status']==0)?'Not Done':'')).'</td><td style="padding:5px;">'.$ress['Who?'].'</td></tr>';
		}
	echo'</table>';
	
	
	// $sql=$sqlmain.' AND VTOQtrId='.$qtr.' AND IsRock=0 '.$adq1.' ORDER BY `Status`,Priority';
	$sql=$sqlmain.' AND VTOQtrId<='.$qtr.' AND IsRock=0 '.$adq1.' ORDER BY IFNULL(`Status`,0),Priority';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	
	
	break;
	
	case 'WeeklyMeetingIssues':
	echo '<title>Meeting - ISSUES</title>';
	
	
	
	$weekNoNextMonday = date('W', strtotime('next '.$l10day.''));
	$weekNoNextMondayDate = date('Y-m-d', strtotime('next '.$l10day.''));

// echo $weekNoNextMonday;
	$allmondays='<option value="'.$weekNoNextMonday.'">'.$weekNoNextMonday.' ('.$weekNoNextMondayDate.')</option>';
	// echo date('Y-m-d');
	
	for ($i = strtotime(date('Y-m-d')); $i >= strtotime($currentyr.'-01-01'); $i = strtotime('-1 day', $i)) {
			if (date('N', $i) == $l10dayval){
				$allmondays.='<option value="'.date('W', $i).'">'.date('W (Y-m-d)', $i).'</option>'; 
			}
		}
		
	
	
	


	
	echo '<form action="#" method="POST">';
	echo '<select name="WeekNo">';
	echo $allmondays;
	
	echo '</select>';
	echo ' <input type="submit" value="View Meeting">';
	echo '</form><br>';
	$rcondi='';
	if(isset($_POST['WeekNo']) AND $weekno<>$_POST['WeekNo']){
		$weekno=$_POST['WeekNo'];
		$adq=' AND RIStatPerWeek LIKE "%'.$weekno.'%"';
		$adq1=$adq;
		$adq2='';
		$openr=0;
		$sr=0;
	} else {
		$adq=' HAVING (LEFT(WeekNo,2)>'.($weekno-1).' OR WeekNo IS NULL OR `Status`=0)';
		$adq1=' HAVING (WeekNo IS NULL OR `Status`=0)';
		$adq2=' HAVING (WeekNo IS NOT NULL AND `Status`=1 AND WeekNo='.$weekno.')';
		$openr=1;
		$sr=1;
		$rcondi=' AND Resigned=0 ';
	}
	
	echo '<h3>Meeting - ISSUES (Week '.$weekno.': '.date("M d", strtotime(week_date($weekno,$currentyr,$l10dayval))).')</h3>';
	
	
	
	$sqlmain='select vqs.*,CONCAT(Nickname," ",SurName) AS `Who?`,RIGHT(RIStatPerWeek,4) AS `WhatWkNo`,RIGHT((SELECT WhatWkNo),1) AS `Status`, LEFT((SELECT WhatWkNo),2) AS WeekNo,CONCAT(Nickname,\' \',SurName) as Fullname from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who WHERE '.$mancomordeptcondi.' Stat=0 '.$rcondi;
	
	
	$sql=$sqlmain.' AND VTOQtrId<='.$qtr.' AND IsRock=0 '.$adq1.' ORDER BY IFNULL(`Status`,0),Priority';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	// echo $sql;
	echo '<br><b>Issues</b>';
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>Issues</th><th>Priority</th><th>Status</th><th>Resolution</th><th>Who</th></tr>';
		foreach($result as $ress){
			$otlink='vto.php?FromWeeklyMeeting=2&w=ResolveNotResolve&VTOQtrSubId='.$ress['VTOQtrSubId'].'&action_token='.$_SESSION['action_token'];
			
			echo'<tr style="'.(($weekno==$ress['WeekNo'] AND $ress['Status']==1)?'':(($weekno==$ress['WeekNo'] AND $ress['Status']==0)?'font-weight:bold;':'font-weight:bold;')).'"><td style="padding:5px;">'.$ress['RockOrIssues'].'</td><td align="center" style="padding:5px;">'.$ress['Priority'].'</td><td style="padding:5px;'.(($weekno==$ress['WeekNo'] AND $ress['Status']==0)?'color:red;':'').'">'.(($weekno==$ress['WeekNo'] AND $ress['Status']==1)?'Resolved':(($weekno==$ress['WeekNo'] AND $ress['Status']==0)?'Not Resolved':'<font color="red">Not Resolved</font>')).'</td><td style="padding:3px;width:500px;">'.(($ress['Who']==$_SESSION['(ak0)'] OR (allowedToOpen(array(1615,6110),'1rtc')))?(($openr==1)?'<form action="'.$otlink.'" method="POST" autocomplete="off"><input type="text" size="45" name="Resolution" value="'.$ress['RemarksOrResolution'].'"><input type="submit" name="btnNotResolved" value="Not Resolved"> <input type="submit" name="btnResolved" value="Resolved"></form>':$ress['RemarksOrResolution']):$ress['RemarksOrResolution']).'</td><td style="padding:5px;">'.$ress['Who?'].'</td></tr>';
		}
	echo'</table>';
	
	
	$sql=$sqlmain.' AND VTOQtrId<='.$qtr.' AND IsRock=0 '.$adq2.' ORDER BY IFNULL(`Status`,1),Priority';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	if($sr==1){
	
	echo '<br><br><b>Resolved Issues</b>';
	echo '<table border="1px solid black" style="border-collapse:collapse;background-color:white;">';
	echo '<tr><th>Issues</th><th>Priority</th><th>Resolution</th><th>Who</th></tr>';
		foreach($result as $ress){
		
			echo'<tr style=""><td style="padding:5px;">'.$ress['RockOrIssues'].'</td><td align="center" style="padding:5px;">'.$ress['Priority'].'</td><td style="padding:3px;width:330px;">'.$ress['RemarksOrResolution'].'</td><td style="padding:5px;">'.$ress['Who?'].'</td></tr>';
		}
	echo'</table>';
	
	}
	
	break;
	
	
	
	case'UpdateProcessQtr':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='Update eos_2vtoqtrmain set QtrFutureDate=\''.$_POST['QtrFutureDate'].'\',QtrRevenue=\''.$_POST['QtrRevenue'].'\',QtrProfit=\''.$_POST['QtrProfit'].'\',QtrMeasurables=\''.$_POST['QtrMeasurables'].'\',QtrThemeMancom=\''.$_POST['QtrThemeMancom'].'\',QtrTheme=\''.$_POST['QtrTheme'].'\' where VTOQtrId=\''.$_GET['VTOQtrId'].'\' ';
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:vto.php?w=Encode");
	break;
	
	case'EditQtr':
	echo '<title>Edit '.$_GET['subtitle'].'</title>';
	
	if(isset($sqlsm)){ //sql main
		$stmtsm=$link->query($sqlsm); $resultsm=$stmtsm->fetchAll();
		$deptlist='<option value="-1" '.($iddept==-1?'selected':'').'>ManCom</option>';
		foreach($resultsm AS $resultsm1){
			$deptlist.='<option value="'.$resultsm1['deptid'].'" '.($resultsm1['deptid']==$iddept?'selected':'').'>'.$resultsm1['dept'].'</option>';
		}
	}
	
	
		
	echo comboBox($link,'select *,CONCAT(Nickname,\' \',SurName) as Fullname from 1employees ORDER BY SurName','IDNo','Fullname','employees'); 
	$sql='select vqs.*,CONCAT(Nickname,\' \',SurName) as Fullname from eos_2vtoqtrsub vqs left join 1employees e on e.IDNo=vqs.Who where '.$mancomordeptcondi.' VTOQtrSubId=\''.$_GET['VTOQtrSubId'].'\'';
	
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo'<h3>Edit '.$_GET['subtitle'].'?</h3></br><form method="post" action="vto.php?subtitle='.$_GET['subtitle'].'&w=EditQtrProcess&VTOQtrSubId='.$_GET['VTOQtrSubId'].'">';
		if($_GET['subtitle']=='Rocks'){
			echo '<b>Quarter:</b> <input type="text" name="Quarter" value="'.$result['VTOQtrId'].'" size="5" min="1" max="4">';
		}
		 echo '<b>'.$_GET['subtitle'].':</b> <input type="text" name="Rocks" value="'.$result['RockOrIssues'].'" size="50">
		 <b>Who:</b> <input type="text" name="Fullname"  value="'.$result['Fullname'].'" list="employees">';
		 
		 if($_GET['subtitle']=='Issues'){
			 echo ' <b>Priority:</b> <input type="number" min="1" max="3" name="Priority" value="'.$result['Priority'].'">';
		 }
		 if($_GET['subtitle']=='ToDo'){
			echo ' <b>DueDate:</b> <input type="date" min="1" max="3" name="DueDate" value="'.$result['RemarksOrResolution'].'">';
		}
		 
		 if(isset($sqlsm)){
			 echo ' <b>DeptOrMancom:</b> <select name="DeptOrMancom">'.$deptlist.'</select>';
		 } else {
			 echo '<input type="hidden" name="DeptOrMancom" value="'.$iddept.'">';
		 }
		 echo '
		 <input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		 <input type="submit" name="submit"></form>
	';
	break;
	
	case'EditQtrProcess':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$vtoqtrsubid = intval($_GET['VTOQtrSubId']);
		$employee=comboBoxValue($link, '1employees', 'CONCAT(Nickname,\' \',SurName)', $_REQUEST['Fullname'], 'IDNo');
		$priosql=''; $qtrsql=''; $duesql='';
		if($_GET['subtitle']=='Rocks'){
			$qtrsql='VTOQtrId='.$_POST['Quarter'].',';
		}
	
		if(isset($_POST['Priority'])){
			$priosql=',Priority='.$_POST['Priority'].'';
		}
		if(isset($_POST['DueDate'])){
			$duesql=',RemarksOrResolution="'.$_POST['DueDate'].'"';
		}
		// print_r($_POST);
		$sql='update eos_2vtoqtrsub set '.$qtrsql.'RockOrIssues=\''.$_POST['Rocks'].'\''.$priosql.$duesql.',EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=NOW(),Who=\''.$employee.'\',ManComOrdept='.$_POST['DeptOrMancom'].' where VTOQtrSubId=\''.$vtoqtrsubid.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		if($_GET['subtitle']=='Rocks'){
			$go='Rocks';
		} elseif($_GET['subtitle']=='ToDo') {
			$go='ToDo';
		} else {
			$go='Issues';
		}
		header("Location:vto.php?w=".$go);
	break;
	case'DeleteQtr':
		$vtoqtrsubid = intval($_GET['VTOQtrSubId']);
		
		$sqlc='select RIStatPerWeek from eos_2vtoqtrsub where VTOQtrSubId=\''.$vtoqtrsubid.'\' AND (RIStatPerWeek IS NOT NULL)';
		$stmtc=$link->query($sqlc); 
		
		if($stmtc->rowCount()==0){
			$sql='delete from eos_2vtoqtrsub where VTOQtrSubId=\''.$vtoqtrsubid.'\' AND RIStatPerWeek IS NULL';
		}
		else {
			$sql='UPDATE eos_2vtoqtrsub SET Stat=1,EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=NOW() where VTOQtrSubId=\''.$vtoqtrsubid.'\'';
		}
		
		$stmt=$link->prepare($sql); $stmt->execute();
		if($_GET['go']==1){
			$go='Rocks';
		} else if($_GET['go']==0){
			$go='Issues';
		} else {
			$go='ToDo';
		}
		header("Location:vto.php?w=".$go);
	break;
	
	case'CancelQtr':
		$vtoqtrsubid = intval($_GET['VTOQtrSubId']);
		$sql='UPDATE eos_2vtoqtrsub SET Stat=1,EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=NOW() where VTOQtrSubId=\''.$vtoqtrsubid.'\'';
		$stmt=$link->prepare($sql); $stmt->execute();
		if($_GET['go']==1){
			$go='Rocks';
		} else if($_GET['go']==0){
			$go='Issues';
		} else {
			$go='ToDo';
		} 
		header("Location:vto.php?w=".$go);
	break;
	
	case'EncodeProcess':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$vtolistid=comboBoxValue($link, 'eos_1vtolisttype', 'VTOList', $_REQUEST['VTOList'], 'VTOListID');	
		$sql='Insert into eos_2vtosub set Details=\''.$_POST['Details'].'\',VTOID=\'1\',VTOListID=\''.$vtolistid.'\' ';
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:vto.php?w=Encode");
	break;
	case'UpdateProcess':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='Update eos_2vtomain set TenYrGoal=\''.$_POST['TenYrGoal'].'\',Niche=\''.$_POST['Niche'].'\',Process=\''.$_POST['Process'].'\',Guarantee=\''.$_POST['Guarantee'].'\',3YrFutureDate=\''.$_POST['3YrFutureDate'].'\',3YrRevenue=\''.$_POST['3YrRevenue'].'\',3YrProfit=\''.$_POST['3YrProfit'].'\',3YrMeasurables=\''.$_POST['3YrMeasurables'].'\',1YrFutureDate=\''.$_POST['1YrFutureDate'].'\',1YrRevenue=\''.$_POST['1YrRevenue'].'\',1YrProfit=\''.$_POST['1YrProfit'].'\',1YrMeasurables=\''.$_POST['1YrMeasurables'].'\' where VTOID=\''.$_GET['VTOID'].'\' ';
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:vto.php?w=Encode");
	break;
	
	case'Edit':
	$sql='select * from eos_2vtosub where VTOSubId=\''.$_GET['VTOSubId'].'\'';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo'<h3>Edit Details?</h3></br><form method="post" action="vto.php?w=EditProcess&VTOSubId='.$_GET['VTOSubId'].'">
		 Details: <input type="text" name="Details" value="'.$result['Details'].'">
		 <input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		 <input type="submit" name="submit">
	';
	break;
	
	case'EditProcess':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$vtosubid = intval($_GET['VTOSubId']);
		$sql='update eos_2vtosub set Details=\''.$_POST['Details'].'\' where VTOSubId=\''.$vtosubid.'\'';
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:vto.php?w=Encode");
	break;
	case'Delete':
		$vtosubid = intval($_GET['VTOSubId']);
		$sql='delete from eos_2vtosub where VTOSubId=\''.$vtosubid.'\'';
		
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:vto.php?w=Encode");
	break;
	
	
}
?>
    
