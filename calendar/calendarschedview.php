<html>
<head>
<title>Branch Visit</title>
<style>
div.day-number	 { 
	background:#999; 
	position:absolute; 
	z-index:2; 
	top:-5px; 
	right:-25px; 
	padding:5px; 
	color:#fff; 
	font-weight:bold; 
	width:20px; 
	text-align:center; 
}
td.calendar-day, td.calendar-day-np { 
	width:11.5%; 
        background:#fff;
	padding:5px 25px 5px 5px; 
	border-bottom:1px solid #999; 
	border-right:1px solid #999; 
}
div.event{
    font-size: 10pt;
}
</style>
</head>
<body>
<?php // based on : https://davidwalsh.name/php-event-calendar
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$allowed=array(5633,5634,5636); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$link=connect_db("".$currentyr."_1rtc",0); 
$year=$currentyr; $month=str_pad((!isset($_GET['month'])?date('m'):$_GET['month']),2,'0', STR_PAD_LEFT);
include_once('../backendphp/layout/linkstyle.php');

include_once('calendarlinks.php');

if ((isset($_REQUEST['DeptID']) AND ($_REQUEST['DeptID']<>'All') OR (isset($_REQUEST['BranchNo']) AND ($_REQUEST['BranchNo']<>'All')))){
	if ((!isset($_REQUEST['DeptID']) OR ($_REQUEST['DeptID']=='All'))){
		echo '<br><h2>All Departments';
		$filteredres="alldepts";
	} else {
		echo '<br><h2>'.comboBoxValue($link,'1departments','deptid',$_REQUEST['DeptID'],'department').' Dept';
		$filteredres="bydept";
	}		
	if ($_REQUEST['BranchNo']<>'All'){
		if ($_REQUEST['BranchNo']<>0){
			echo ', '.companyandbranchValue($link,'1branches','BranchNo',$_REQUEST['BranchNo'],'Branch').'<br><br>';
		} else {
			echo ', Office';
		}
		$filteredres.="bybranch";
	} else {
		echo ', All Branches</h2><br>';
		$filteredres.="allbranches";
	}
} 
else if (!isset($_REQUEST['DeptID'])){
	echo '<br><h2>My Department, All Branches</h2><br>';
	$filteredres="bydeptallbranches";
}
else {
	echo '<br><h2>All Departments, All Branches</h2><br>';
	$filteredres="default";
}

/* draws a calendar */
function draw_calendar($month,$year,$events = array(),$filteredres){
//if ($_SESSION['(ak0)']==1002){ print_r($events);}
	/* draw table */
	$calendar = '<table cellpadding="0" cellspacing="0" class="calendar">';

	/* table headings */
	$headings = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	$calendar.= '<tr class="calendar-row"><td class="calendar-day-head">'.implode('</td><td class="calendar-day-head">',$headings).'</td></tr>';

	/* days and weeks vars now ... */
	$running_day = date('w',mktime(0,0,0,$month,1,$year));
	$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
	$days_in_this_week = 1;
	$day_counter = 0;
	$dates_array = array();

	/* row for week one */
	$calendar.= '<tr class="calendar-row">';

	/* print "blank" days until the first of the current week */
	for($x = 0; $x < $running_day; $x++):
		$calendar.= '<td class="calendar-day-np">&nbsp;</td>';
		$days_in_this_week++;
	endfor;

	/* keep going with days.... */
	for($list_day = 1; $list_day <= $days_in_month; $list_day++):
		$calendar.= '<td class="calendar-day"><div style="position:relative;">';//height:100px;
			/* add in the day number */
			$calendar.= '<div class="day-number">'.$list_day.'</div>';
			$calendar1='';
			$event_day = $year.'-'.$month.'-'.str_pad($list_day,2,'0', STR_PAD_LEFT);; 
			if(isset($events[$event_day])) {
                                $calendar1.='<br>';
				foreach($events[$event_day] as $event) {
										// $calendar1.= '<div style="background-color:'.$event['ColorHex'].'">'.(($filteredres<>'default')?'<a style="color:red;text-decoration:none;" href="calendarsettings.php?w=Details&TxnID='.$event['TxnID'].'">*</a>':'').$event['title'].'</div>';
										$calendar1.= '<div style="background-color:'.$event['ColorHex'].'">'.(($filteredres<>'default' OR $filteredres<>'alldeptsallbranches')?'<a style="color:red;text-decoration:none;" href="calendarsettings.php?w=Details&TxnID='.$event['TxnID'].'">*</a>':'').$event['title'].'</div>';
										// $calendar1.= '<div style="background-color:'.$event['ColorHex'].'"><a style="color:red;text-decoration:none;" href="calendarsettings.php?w=Details&TxnID='.$event['TxnID'].'">*</a>'.$event['title'].'</div>';
				}
			}
			else {
				$calendar.= str_repeat('<p>&nbsp;</p>',2);
			}
		$calendar.=$calendar1.'</div></td>';
		if($running_day == 6):
			$calendar.= '</tr>';
			if(($day_counter+1) != $days_in_month):
				$calendar.= '<tr class="calendar-row">';
			endif;
			$running_day = -1;
			$days_in_this_week = 0;
		endif;
		$days_in_this_week++; $running_day++; $day_counter++;
	endfor;

	/* finish the rest of the days in the week */
	if($days_in_this_week < 8):
		for($x = 1; $x <= (8 - $days_in_this_week); $x++):
			$calendar.= '<td class="calendar-day-np">&nbsp;</td>';
		endfor;
	endif;

	/* final row */
	$calendar.= '</tr>';
	

	/* end the table */
	$calendar.= '</table>';

	/** DEBUG **/
	$calendar = str_replace('</td>','</td>'."\n",$calendar);
	$calendar = str_replace('</tr>','</tr>'."\n",$calendar);
	
	/* all done, return result */
	return $calendar;
}

/* select month control */
$select_month_control = '<select name="month" id="month">';
for($x = 1; $x <= 12; $x++) {
	$select_month_control.= '<option value="'.$x.'"'.($x != $month ? '' : ' selected="selected"').'>'.date('F',mktime(0,0,0,$x,1,$year)).'</option>';
}
$select_month_control.= '</select>';


/* "next month" control */
$next_month_link = '<a href="?month='.($month != 12 ? $month + 1 : 1).'&year='.($month != 12 ? $year : $year + 1).'" class="control">Next Month &gt;&gt;</a>'.str_repeat('<br>',4);

/* "previous month" control */
$previous_month_link = '<a href="?month='.($month != 1 ? $month - 1 : 12).'&year='.($month != 1 ? $year : $year - 1).'" class="control">&lt;&lt; 	Previous Month</a>';



$sql='SELECT * FROM 1departments ORDER BY department;';
$stmt = $link->query($sql);
	
$choosedept='<select name="DeptID"><option value="All">All Departments</option>';
while($row= $stmt->fetch()) {
	$choosedept.='<option value="'.$row['deptid'].'">'.$row['department'].'</option>';
}
$choosedept.='</select>';

$sql='SELECT BranchNo,Branch FROM 1branches WHERE PseudoBranch<>1 UNION SELECT 0,"Office" ORDER BY Branch;';
$stmt = $link->query($sql);
	
$choosebranch='<b>Branch:</b> <select name="BranchNo"><option value="All">All Branches</option>';
while($row= $stmt->fetch()) {
	$choosebranch.='<option value="'.$row['BranchNo'].'">'.$row['Branch'].'</option>';
}
$choosebranch.='</select>';
	
/* bringing the controls together */ // REMOVED THIS $select_year_control.
$controls = '<form method="get">'.$select_month_control.'&nbsp; '.((allowedToOpen(5633,'1rtc') OR allowedToOpen(5634,'1rtc'))?'<b>Department:</b> '.$choosedept.' '.$choosebranch.'':'').' <input type="submit" name="submit" value="Go" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$previous_month_link.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$next_month_link.' </form>';

/* get all events for the given month */
$events = array();


//Dept Heads or Assistant else self sched
if ((allowedToOpen(5633,'1rtc')) OR (allowedToOpen(5634,'1rtc'))) {
// $addlcon = ((isset($_REQUEST['DeptID']) AND ((allowedToOpen(5633,'1rtc')) OR (allowedToOpen(5634,'1rtc'))))?(($_REQUEST['DeptID']<>'All')?' AND cp.deptid='.$_REQUEST['DeptID']:''):' AND cp.deptid=(SELECT deptid FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].')');
$addlcon = ((isset($_REQUEST['DeptID']) AND ((allowedToOpen(5633,'1rtc')) OR (allowedToOpen(5634,'1rtc'))))?(($_REQUEST['DeptID']<>'All')?' AND cp.deptid='.$_REQUEST['DeptID']:''):' AND cp.supervisorpositionid=(SELECT positionid FROM attend_30currentpositions WHERE PositionID='.$_SESSION['&pos'].')');
$addlcon2 = (isset($_GET['BranchNo'])?($_GET['BranchNo']=='All'?'':' AND c.BranchNo='.$_GET['BranchNo'].''):'');
} else {
	$addlcon =' AND (EmpIDNo='.$_SESSION['(ak0)'].' OR LatestSupervisorIDNo='.$_SESSION['(ak0)'].')';
	$addlcon2='';
}

$colhex = (((isset($_REQUEST['DeptID']) AND $_REQUEST['DeptID']=='All') OR (!isset($_REQUEST['DeptID'])))?!isset($_REQUEST['submit'])?'ColorHex':'deptcolor':'IFNULL(ColorHex,\'FFFFFF\')');

if ($filteredres=='bydeptallbranches') {
	$infotoshow = 'CONCAT(NickName," > ",IF(c.BranchNo=0,"Office",b.Branch)) AS title, ';
	$topinfo='';
	$groupby='';
	$showoffice=1;
} else if ($filteredres=='bydeptbybranch') {
	$infotoshow = 'NickName AS title, ';
	$topinfo='';
	$groupby='';
	$showoffice=1;
} else {
	if ((allowedToOpen(5633,'1rtc')) OR (allowedToOpen(5634,'1rtc'))) {
		if ($filteredres=='alldeptsbybranch'){
			$infotoshow = 'd.dept AS title, ';
			$showoffice=1;
		}
		else {
			$infotoshow = 'CONCAT(d.dept," > ",IF(c.BranchNo=0,"Office",b.Branch)) AS title, ';
			$showoffice=0;
		}
	} else {
		$infotoshow = 'b.Branch AS title, '; //self
		$showoffice=1;
	}
	$topinfo='';
	$groupby='';
}
$sql0 = 'SELECT '.$infotoshow.' TxnID, DATE_FORMAT(`DateSchedule`,\'%Y-%m-%d\') AS `event_date`, '.$colhex.' AS ColorHex FROM `calendar_2sched` `c` LEFT JOIN `calllogs_0bgcolorperid` bg ON bg.TLIDNo=c.EmpIDNo JOIN `1employees` e ON e.IDNo=c.EmpIDNo JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo JOIN 1departments d ON cp.deptid=d.deptid JOIN 1branches b ON c.BranchNo=b.BranchNo WHERE MONTH(`DateSchedule`)='.$month.' '.$addlcon.' '.$addlcon2.' '.($showoffice==0?' AND c.BranchNo<>0':'').' '.$groupby.' ORDER BY Nickname';

$stmt0=$link->query($sql0);
$res0 = $stmt0->fetchAll();
foreach($res0 as $event) { $events[$event['event_date']][] = $event; }

$sql1 = "SELECT TLIDNo AS ARIDNo, CONCAT(Nickname, ' ',Surname".$topinfo.") AS Name, ".$colhex." AS ColorHex FROM `calendar_2sched` `c` LEFT JOIN `calllogs_0bgcolorperid` bg ON bg.TLIDNo=c.EmpIDNo JOIN `1employees` e ON e.IDNo=c.EmpIDNo JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo JOIN 1departments d ON cp.deptid=d.deptid JOIN 1branches b ON c.BranchNo=b.BranchNo WHERE MONTH(`DateSchedule`)=".$month.' '.$addlcon.' '.$addlcon2.' '.($showoffice==0?' AND c.BranchNo<>0':'').' GROUP BY c.EmpIDNo ORDER BY Nickname';
// echo $sql1;
$stmt1=$link->query($sql1); $res1 = $stmt1->fetchAll(); $arlist='';
foreach($res1 as $arid) {$arlist.='<font style="background-color:'.$arid['ColorHex'].'">'.$arid['Name'].'</font><br>';}

echo '<h2 style="float:left; padding-right:30px;">'.date('F',mktime(0,0,0,$month,1,$year)).' '.$year.'</h2>';
echo '<div style="float:left;">'.$controls.'</div>';
echo '<div style="clear:both;"></div>';
?>
<?php 
echo $arlist.'<br /><br />';
echo draw_calendar($month,$year,$events,$filteredres);
echo '<br /><br />';
$link=null; $stmt=null;
?>