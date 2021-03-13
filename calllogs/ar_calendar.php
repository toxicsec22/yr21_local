<html>
<head>
<title>Calendar</title>
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
if (!allowedToOpen(662,'1rtc')) {   echo 'No permission'; exit;}  
include_once('../switchboard/contents.php');
 include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
 
$year=$currentyr; $month=str_pad((!isset($_GET['month'])?date('m'):$_GET['month']),2,'0', STR_PAD_LEFT);

/* draws a calendar */
function draw_calendar($month,$year,$events = array()){
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
                                $calendar.='<div style="background-color:b3b3cc">For Collection</div>';
                                $calendar1.='<br><div style="background-color:b3b3cc">For Follow Up</div>';
				foreach($events[$event_day] as $event) {
                                        if ($event['Action']==0){ $calendar.= '<div style="background-color:'.$event['ColorHex'].'">'.$event['title'].'</div>';}
                                        else { $calendar1.= '<div style="background-color:'.$event['ColorHex'].'">'.$event['title'].'</div>';}
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


/* bringing the controls together */ // REMOVED THIS $select_year_control.
$controls = '<form method="get">'.$select_month_control.'&nbsp;<input type="submit" name="submit" value="Go" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$previous_month_link.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$next_month_link.' </form>';

/* get all events for the given month */
$events = array();
$sql0 = 'SELECT ARIDNo,ClientName AS title, Action, DATE_FORMAT(`ActionDate`,\'%Y-%m-%d\') AS `event_date`, IFNULL(ColorHex,\'FFFFFF\') AS ColorHex  FROM `calllogs_3armain` `m` JOIN `calllogs_3arsub` `s` ON `m`.`TxnID` = `s`.`TxnID` JOIN `1clients` c ON c.ClientNo=s.ClientNo LEFT JOIN `calllogs_0bgcolorperid` bg ON bg.TLIDNo=m.ARIDNo JOIN `1employees` e ON e.IDNo=m.ARIDNo WHERE MONTH(`ActionDate`)='.$month.' ORDER BY Nickname';
//if ($_SESSION['(ak0)']==1002){ echo $sql0;}
$stmt0=$link->query($sql0);
$res0 = $stmt0->fetchAll();
foreach($res0 as $event) { $events[$event['event_date']][] = $event; }


$sql1 = "SELECT TLIDNo AS ARIDNo, CONCAT(Nickname, ' ',Surname) AS Name, IFNULL(ColorHex,'FFFFFF') AS ColorHex  FROM `calllogs_3armain` `m` JOIN `calllogs_3arsub` `s` ON `m`.`TxnID` = `s`.`TxnID` LEFT JOIN `calllogs_0bgcolorperid` bg ON bg.TLIDNo=m.ARIDNo JOIN `1employees` e ON e.IDNo=m.ARIDNo WHERE MONTH(`ActionDate`)=".$month.' GROUP BY m.ARIDNo ORDER BY Action,Nickname';
// if ($_SESSION['(ak0)']==1002){ echo $sql1;}
$stmt1=$link->query($sql1); $res1 = $stmt1->fetchAll(); $arlist='';
foreach($res1 as $arid) {$arlist.='<font style="background-color:'.$arid['ColorHex'].'">'.$arid['Name'].'</font><br>';}

echo '<h2 style="float:left; padding-right:30px;">'.date('F',mktime(0,0,0,$month,1,$year)).' '.$year.'</h2>';
echo '<div style="float:left;">'.$controls.'</div>';
echo '<div style="clear:both;"></div>';
?><!-- New calendar entry: (future events only) 
    <form method="post" style="display:inline" action="prcalendar.php?w=Add" enctype="multipart/form-data"> -->
    <?php
// add calendar entries
// echo $tlentry;
?><!--  Date <input type="date" name="Date">  Details <input type="text" name="Details" size="60">  
<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />
<input type="submit" name="add" value="Enter">
<input type="submit" name="delete" value="Delete"><br><br><br> -->
<?php 
echo $arlist.'<br /><br />';
echo draw_calendar($month,$year,$events);
echo '<br /><br />';
 $link=null; $stmt=null;
?>