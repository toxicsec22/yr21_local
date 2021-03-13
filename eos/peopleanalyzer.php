<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(1602,1604); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=FALSE;
include_once('../switchboard/contents.php');

$which=!isset($_GET['w'])?'List':$_GET['w'];
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$sqlchoices='SELECT "1" AS ScoreNo, "+" AS Score UNION SELECT "0" AS ScoreNo, "+/-" AS Score UNION SELECT "-1" AS ScoreNo, "-" AS Score ';
$listssql=array(
    array('sql'=>'SELECT e.IDNo, CONCAT(Nickname,": ",FirstName," ",Surname) AS FullName FROM `1employees` e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo where deptheadpositionid='.$_SESSION['&pos'].' OR supervisorpositionid='.$_SESSION['&pos'].' ', 'listvalue'=>'FullName', 'label'=>'IDNo','listname'=>'employees'),
    array('sql'=>$sqlchoices, 'listvalue'=>'Score', 'label'=>'ScoreNo','listname'=>'scores'),
    array('sql'=>'SELECT "1" AS ScoreNo, "Yes" AS Score UNION SELECT "-1" AS ScoreNo, "No" AS Score', 'listvalue'=>'Score', 'label'=>'ScoreNo','listname'=>'yesno')
    );
foreach($listssql as $listlookup){ echo comboBox($link,$listlookup['sql'],$listlookup['listvalue'],$listlookup['label'],$listlookup['listname']);}

	$sql=''; $sqlmypa='';
foreach(array('Driven','Agility','RespectforOthers','Transparent') as $score){
    $sql.=' (CASE WHEN `'.$score.'`=1 THEN "+" WHEN `'.$score.'`=-1 THEN "-" ELSE "+/-" END) AS `'.$score.'`,';
    $sqlmypa.=' (CASE WHEN `'.$score.'`=1 THEN "<td class=positive>+</td>" WHEN `'.$score.'`=-1 THEN "<td class=negative>-</td>" ELSE "<td class=neutral>+/-</td>" END) AS `'.$score.'`,';
}

foreach(array('GetsIt','WantsIt','Capacity') as $score){
    $sql.=' (CASE WHEN `'.$score.'`=1 THEN "Yes" ELSE "No" END) AS `'.$score.'`,';
    $sqlmypa.=' (CASE WHEN `'.$score.'`=1 THEN "<td class=positive>Yes</td>" ELSE "<td class=negative>No</td>" END) AS `'.$score.'`,';
}
switch($which) {
	case'List':

$title='EOS: People Analyzer'; 
$formdesc='<div style="background-color: #e6e6e6; width: 1100px; border: 2px solid grey; padding: 25px; margin: 25px;">
        <b>Right People in the Right Seat</b>
        <br/><br/>
        The <i>Right People</i> exhibit the company\'s core VALUES.  They are in the <i>Right Seat</i> if they pass the GWC test: (answerable by Yes [1] or No [-1] only)<br/>
        <br/><b>Get it?</b> - Does he or she get all of the ins and outs of the position? (If not, can be transferred to the Right Seat.)
        <br/><b>Want it?</b> - Does he or she get up every morning wanting to do it?
        <br/><b>Capacity?</b> - Does he or she have the mental, physical, spiritual, time, knowledge and emotional
capacity to do the job? (Or can be trained, and we can wait for him/her to be trained)
        <br/><br/>
        Score each person based on our VALUES as follows:<br/><br/>
        &nbsp; &nbsp; &nbsp; &nbsp; + [1]  &nbsp; &nbsp; &nbsp;He or she exhibits that core value most of the time.<br/>
        &nbsp; &nbsp; &nbsp; &nbsp; +/− [0] &nbsp; &nbsp; Sometimes he or she exhibits the core value and sometimes he or she doesn’t.<br/>
        &nbsp; &nbsp; &nbsp; &nbsp; − [-1] &nbsp; &nbsp; &nbsp;He or she doesn’t exhibit the core value most of the time.<br/><br/>
        <u>The Bar</u><br />
        <ol style="margin-left: 5%;">
        <li>Values: 2 (+)\'s, 1 (+/-), and 1 (-)</li>
        <li>Respect for Others cannot be a (-).</li>
        <li>GWC: Only Gets It can be a No.</li>
        </ol>
        <br/><br/>
    </div>';
$showenc=!isset($_POST['showenc'])?0:$_POST['showenc'];   
$formdesc.='<form style="display:inline" method="post" action="#">
	<input type=hidden name="showenc" value="'.($showenc==0?1:0).'">
    <input type="submit" name="submit" value="'.($showenc==0?'Show Encoded By and Timestamp':'Hide Encoded By and Timestamp').'">
	</form></br>';
	
if (allowedToOpen(1604,'1rtc')) {
	$formdesc.='</i></br><form method="post" action="peopleanalyzer.php?w=add">
				IDNo <input type="text" size="10" name="IDNo" required list="employees">
				Driven <input type="text" size="2" name="Driven" required list="scores">
				Agility <input type="text" size="2" name="Agility" required list="scores">
				RespectforOthers <input type="text" size="2" name="RespectforOthers" required list="scores">
				Transparent <input type="text" size="2" name="Transparent" required list="scores">
				GetsIt <input type="text" size="2" name="GetsIt" required list="yesno">
				WantsIt <input type="text" size="2" name="WantsIt" required list="yesno">
				Capacity <input type="text" size="2" name="Capacity" required list="yesno">		
				<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
					<input type="submit"  name="submit">	
			</form>';
			$condition='where deptheadpositionid='.$_SESSION['&pos'].' OR supervisorpositionid='.$_SESSION['&pos'].'';
}else{
	$condition='where pa.IDNo='.$_SESSION['(ak0)'].'';
}
		$sql='SELECT pa.*, FullName,  '. $sql . ' CONCAT(e.Nickname," ",e.Surname) AS EncodedBy FROM `eos_2peopleanalyzer` pa LEFT JOIN `attend_30currentpositions` cp ON pa.IDNo=cp.IDNo LEFT JOIN `1employees` e ON e.IDNo=pa.EncodedByNo '.$condition.'';
		
		$txnidname='PAID'; 
		
		$columnnames=array('IDNo','FullName','Driven','Agility','RespectforOthers','Transparent','GetsIt','WantsIt','Capacity');
		if (isset($showenc) and $showenc==1) { array_push($columnnames,'EncodedBy','TimeStamp');}
			
if (allowedToOpen(1604,'1rtc')) {
		$delprocess='peopleanalyzer.php?w=delete&PAID=';	
}		
			include('../backendphp/layout/displayastable.php');


break;

case'add':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='insert into eos_2peopleanalyzer set IDNo=\''.$_POST['IDNo'].'\',Driven=\''.$_POST['Driven'].'\',Agility=\''.$_POST['Agility'].'\',RespectforOthers=\''.$_POST['RespectforOthers'].'\',Transparent=\''.$_POST['Transparent'].'\',GetsIt=\''.$_POST['GetsIt'].'\',WantsIt=\''.$_POST['WantsIt'].'\',Capacity=\''.$_POST['Capacity'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("location:peopleanalyzer.php?w=List");

break;

case'delete':
	$sql='delete from eos_2peopleanalyzer where PAID=\''.$_GET['PAID'].'\' and EncodedByNo=\''.$_SESSION['(ak0)'].'\'';
	$stmt=$link->prepare($sql); $stmt->execute();
	header("location:peopleanalyzer.php?w=List");
break;
}

    
?>