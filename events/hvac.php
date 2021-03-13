<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(4001,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila'); $diraddress='../';
include_once($path.'/acrossyrs/js/includesscripts.php');
?>
<br><div id="section" style="display: block;">
<?php
include_once('../backendphp/layout/linkstyle.php');
echo '<div>';
		echo '<a id=\'link\' href="hvac.php">Event Entries (Today)</a> ';
		echo '<a id=\'link\' href="hvac.php?w=AllEntries">Event Entries (All)</a> ';
                if (allowedToOpen(4002,'1rtc')) { 
		echo '<a id=\'link\' href="hvac.php?w=SummaryReport">Summary Reports</a> ';
		echo '<a id=\'link\' href="hvac.php?w=HVACLogs">HVAC Event Logs</a>';
                }
echo '</div><br/><br/>';

$which=(!isset($_GET['w'])?'List':$_GET['w']);

if (in_array($which,array('List','EditSpecifics'))){
	include_once('../backendphp/layout/showencodedbybutton.php');
	echo comboBox($link,'SELECT e.IDNo, CONCAT(Nickname, " ", Surname) AS Name FROM `1employees` e JOIN attend_30currentpositions cp ON cp.IDNo = e.IDNo WHERE PositionID = 36 ORDER BY Name','IDNo','Name','stllist');
	
	echo comboBox($link,'SELECT DISTINCT Company FROM events_1hvacleads ORDER BY Company','Company','Company','companylist');
	echo comboBox($link,'SELECT DISTINCT Name FROM events_1hvacleads ORDER BY Name','Name','Name','namelist');
	echo comboBox($link,'SELECT DISTINCT Position FROM events_1hvacleads ORDER BY Position','Position','Position','positionlist');
	echo comboBox($link,'SELECT DISTINCT Industry FROM events_1hvacleads ORDER BY Industry','Industry','Industry','industrylist');
        
   $sql='SELECT h.*, HVACID AS TxnID, CONCAT(e1.Nickname, " ", e1.Surname) AS EncodedBy, CONCAT(e.Nickname, " ", e.Surname) AS STL FROM events_1hvacleads AS h LEFT JOIN 1_gamit.0idinfo e1 ON e1.IDNo=h.EncodedByNo JOIN `1employees` e ON e.IDNo=h.STL WHERE Date=CURDATE() '.(allowedToOpen(4003,'1rtc')?' AND h.STL='.$_SESSION['(ak0)']:'').' ORDER BY h.TimeStamp DESC';
   
   
   $columnnameslist=array('HVACID', 'Company', 'Name', 'Position', 'ContactNo', 'Email', 'Industry', 'STL', 'Date', 'InquiredAbout');
   $columnstoadd=array('Company', 'Name', 'Position', 'ContactNo', 'Email', 'Industry', 'STL', 'Date', 'InquiredAbout');
   if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
  
}

if (in_array($which,array('Add','Edit'))){
	$idno=comboBoxValue($link,'`1employees`','CONCAT(Nickname, " ", Surname)',addslashes($_POST['STL']),'IDNo');
   $columnstoadd=array('Company', 'Name', 'Position', 'ContactNo', 'Email', 'Industry', 'Date', 'InquiredAbout');
}


switch ($which)
{
	
	//Start of Case List
	case 'List':
	
		$title='List of Visitors'; 
                if (allowedToOpen(4002,'1rtc')) {
                $formdesc='Add New Visitors Info.';
		$method='post';
				$columnnames=array(
				array('field'=>'Company','type'=>'text','size'=>25,'required'=>true, 'list'=>'companylist'),
				array('field'=>'Name','type'=>'text','size'=>25,'required'=>true, 'list'=>'namelist'),
				array('field'=>'Position','type'=>'text','size'=>25,'required'=>true, 'list'=>'positionlist'),
				array('field'=>'ContactNo','type'=>'text','size'=>25,'required'=>true),
				array('field'=>'Email','type'=>'text','size'=>25,'required'=>true),
				array('field'=>'Industry','type'=>'text','size'=>25,'required'=>true, 'list'=>'industrylist'),
				array('field'=>'STL','type'=>'text','size'=>25,'required'=>true, 'list'=>'stllist'),
				array('field'=>'Date','type'=>'date','size'=>25,'required'=>true, 'value'=>date('Y-m-d')),
				array('field'=>'InquiredAbout','type'=>'text','size'=>25,'required'=>false));
							
		$action='hvac.php?w=Add'; $fieldsinrow=3; $liststoshow=array();
		echo '<div>';
		echo '<div style=\'float:left\'>';
		include('../backendphp/layout/inputmainform.php');
                $delprocess='hvac.php?w=Delete&HVACID=';
                $editprocess='hvac.php?w=EditSpecifics&HVACID='; $editprocesslabel='Edit';
                echo '</div>'; }
		echo '<div style=\'margin-left:70%\'>';
		
		echo '<h3>No. of Visitors Today</h3>';
		
		$sql2 = 'SELECT cp.IDNo, CONCAT(e.Nickname, " ", e.Surname) AS STL FROM `1employees` AS e LEFT JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE PositionID=36 ORDER BY STL';
		
		
		$stmt2 = $link->query($sql2);
		echo '<table border=\'1px solid;\' style=\'margin-left:5%;border-collapse:collapse;\'>';
		$NewTot = 0;
		while($row2 = $stmt2->fetch()) {
			
			 $sql3='SELECT COUNT(*) FROM events_1hvacleads WHERE Date=CURDATE() AND STL='.$row2['IDNo'].'';
			 
			$AllTotal = $link->query($sql3)->fetchColumn();
			echo '<tr>';
			echo '<td>';
			echo $row2['STL'];
			echo '</td>';
			echo '<td style=\'text-align:center;width:20px\'>';
			echo $AllTotal;
			echo '</td>';
			if ($AllTotal != 0)
			{
				echo '<td style=\'text-align:center;width:70px\'>';
				echo '<a href=\'hvac.php?w=Lookup&STL='.(allowedToOpen(4003,'1rtc')?$_SESSION['(ak0)']:$row2['IDNo']).'\'>Look up</a>';
				echo '</td>';
			} else { echo '<td></td>'; }
			echo '</tr>';
			
			$NewTot = $NewTot + $AllTotal;
		}
		echo '<tr style=\'font-weight:bold;\'><td>Total No. of Visitors</td><td style=\'text-align:center;width:20px\'>'.$NewTot.'</td><td></td></tr>';
		echo '</table>';
		echo '</div>';
		echo '</div>';
		//Processes
		
		
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;       
		
		
		include('../backendphp/layout/displayastable.php'); 
	break; //End of Case List
	
	//Start of Case AllEntries
	case 'AllEntries':
	include_once('../backendphp/layout/showencodedbybutton.php');
	 $sql='SELECT h.*, HVACID AS TxnID, CONCAT(e1.Nickname, " ", e1.Surname) AS EncodedBy, CONCAT(e.Nickname, " ", e.Surname) AS STL FROM events_1hvacleads AS h LEFT JOIN 1_gamit.0idinfo e1 ON e1.IDNo=h.EncodedByNo JOIN `1employees` e ON e.IDNo=h.STL '.(allowedToOpen(4003,'1rtc')?' WHERE h.STL='.$_SESSION['(ak0)']:'').' ORDER BY h.TimeStamp DESC';
   $columnnameslist=array('HVACID', 'Company', 'Name', 'Position', 'ContactNo', 'Email', 'Industry', 'STL', 'Date', 'InquiredAbout');
   $columnstoadd=array('Company', 'Name', 'Position', 'ContactNo', 'Email', 'Industry', 'STL', 'Date', 'InquiredAbout');
   if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
  
		//Processes
		
		echo '<br/>';
		$title='All Entries'; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;       
                if (allowedToOpen(4002,'1rtc')) { $editprocess='hvac.php?w=EditSpecifics&HVACID='; $editprocesslabel='Edit'; $delprocess='hvac.php?w=Delete&HVACID=';}
                
                include('../backendphp/layout/displayastable.php'); 
	break; //End of Case AllEntries
	
	
	//Start of Case Logs
	case 'HVACLogs':
	if (!allowedToOpen(4002,'1rtc')) { echo 'No permission'; exit; }
	 $sql='SELECT h.*, LogsID AS TxnID, CONCAT(e1.Nickname, " ", e1.Surname) AS EncodedBy, CONCAT(e2.Nickname, " ", e2.Surname) AS ActionBy, CONCAT(e.Nickname, " ", e.Surname) AS STL FROM events_1hvacleads_logs AS h LEFT JOIN 1_gamit.0idinfo e1 ON e1.IDNo=h.EncodedByNo JOIN `1employees` e ON e.IDNo=h.STL LEFT JOIN `1employees` e2 ON h.UserActionNo=e2.IDNo ORDER BY h.TimeStamp DESC';
	 
   $columnnameslist=array('Company', 'Name', 'Position', 'ContactNo', 'Email', 'Industry', 'STL', 'Date', 'InquiredAbout', 'EncodedBy', 'TimeStamp', 'UserAction', 'ActionBy', 'ActionTimeStamp');

		$title='HVAC Logs'; $formdesc='UserAction: 0 = Deleted, 1 = Edited'; $txnid='TxnID';
		$columnnames=$columnnameslist;      
		
		include('../backendphp/layout/displayastable.php'); 
	break; //End of Case Logs
	
	case 'Add':
		if (allowedToOpen(4002,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			
			$sql='';
			$fieldarr = array();
			foreach ($columnstoadd as $field) {$sql.=' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; $fieldarr[] = $_POST[$field]; }
			//Validation - check if theres a duplicate entry (Company AND Name checking)
			 $sql2='SELECT COUNT(*) FROM events_1hvacleads WHERE '.$columnstoadd[0].'=\''.$fieldarr[0].'\' AND '.$columnstoadd[1].'=\''.$fieldarr[1].'\'';
			$AllTotal = $link->query($sql2)->fetchColumn();
			
			if ($AllTotal>0) { echo '<h3>Data not inserted.<br/>Duplicate Entry</h3>'; }
			else
			{
				$sql='INSERT INTO `events_1hvacleads` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' STL='.$idno.', TimeStamp=Now()';
				// echo $sql; break;
				$stmt=$link->prepare($sql); $stmt->execute();
				header('Location:'.$_SERVER['HTTP_REFERER']);
			}
		}
	break; 
	
    case 'EditSpecifics':
        if (!allowedToOpen(4002,'1rtc')) { header('Location:hvac.php?w=AllEntries&denied=true'); }
		$title='Edit Specifics';
		$txnid=intval($_GET['HVACID']);

		//Condition For Edit Specifics
		$sql = 'SELECT h.*, HVACID AS TxnID, CONCAT(e1.Nickname, " ", e1.Surname) AS EncodedBy, CONCAT(e.Nickname, " ", e.Surname) AS STL FROM events_1hvacleads AS h LEFT JOIN 1_gamit.0idinfo e1 ON e1.IDNo=h.EncodedByNo JOIN `1employees` e ON e.IDNo=h.STL';
		$sql=$sql.' WHERE HVACID='.$txnid.(allowedToOpen(4003,'1rtc')?' AND STL='.$_SESSION['(ak0)']:'');
		$columnstoedit=$columnstoadd;
		// echo $sql;
		$columnnames=$columnnameslist;
		
		$columnswithlists=array('STL','Company','Name', 'Position', 'Industry');
		$listsname=array('STL'=>'stllist','Company'=>'companylist','Name'=>'namelist','Position'=>'positionlist','Industry'=>'industrylist');
		$editprocess='hvac.php?w=Edit&HVACID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	break; //End of Case EditSpecifics
	
	
    case 'Edit':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		if (allowedToOpen(4002,'1rtc')){
		$sql='';
		$fieldarr = array();
		$sql='SELECT * FROM `events_1hvacleads` WHERE HVACID='.intval($_GET['HVACID']).'';
		$stmt = $link->query($sql);
		$row = $stmt->fetch();
		
		$sql2 ='';
		foreach ($columnstoadd as $field) {$sql2.=' `' . $field. '`=\''.addslashes($row[$field]).'\', '; $fieldarr[] = $row[$field]; }
		
		$sql2='INSERT INTO `events_1hvacleads_logs` SET '.$sql2.' STL='.$idno.', EncodedByNo='.$row['EncodedByNo'].', TimeStamp=\''.$row['TimeStamp'].'\', UserAction=1, UserActionNo='.$_SESSION['(ak0)'].'';
		
		$stmt2=$link->prepare($sql2);
		$stmt2->execute();
		
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		
		$sql='UPDATE `events_1hvacleads` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' STL='.$idno.', TimeStamp=Now() WHERE HVACID='.intval($_GET['HVACID']).(allowedToOpen(4003,'1rtc')?' AND STL='.$_SESSION['(ak0)']:'');
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		
		}
		header("Location:hvac.php");
		
    break;
	
    case 'Delete':
	//access
        if (allowedToOpen(4002,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			
			$columnstoadd=array('Company', 'Name', 'Position', 'ContactNo', 'Email', 'Industry', 'Date', 'InquiredAbout');
   
			$sql='';
			$fieldarr = array();
			$sql='SELECT * FROM `events_1hvacleads` WHERE HVACID='.intval($_GET['HVACID']).'';
			$stmt = $link->query($sql);
			$row = $stmt->fetch();
			$idno = $row['STL'];
			
			$sql2 ='';
			foreach ($columnstoadd as $field) {$sql2.=' `' . $field. '`=\''.addslashes($row[$field]).'\', ';}
			
			$sql2='INSERT INTO `events_1hvacleads_logs` SET '.$sql2.' STL='.$idno.', EncodedByNo='.$row['EncodedByNo'].', TimeStamp=\''.$row['TimeStamp'].'\', UserAction=0, UserActionNo='.$_SESSION['(ak0)'].'';
			// echo $sql2; break;
			$stmt2=$link->prepare($sql2);
			$stmt2->execute();
			
			$sql='';
		
			$sql='DELETE FROM `events_1hvacleads` WHERE HVACID='.intval($_GET['HVACID']);
			$stmt=$link->prepare($sql); $stmt->execute();
		}
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;
	
	case 'SummaryReport':
                if (!allowedToOpen(4002,'1rtc')) { header('Location:hvac.php?w=AllEntries&denied=true'); }
		$title='HVAC Summary Reports';
		 echo '<title>'.$title.'</title>';
		
		 $sql = 'SELECT Date, Date AS TxnID, (@row_number:=@row_number + 1) AS Day FROM (
		SELECT DISTINCT Date FROM events_1hvacleads, (SELECT @row_number:=0) AS t
		) sub1 ORDER BY DATE';
		
		echo '<h3>View Total No. of Visitors Per Day.</h3>';	  
		$columnnameslist=array('Day', 'Date');
		$columnstoadd=array('Day', 'Date');
		
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;
		
		$editprocess='hvac.php?w=LookupDate&Date='; $editprocesslabel='Look up';
		
		// include('../backendphp/layout/displayastablenoeditnodelete.php');
		include('../backendphp/layout/displayastable.php');
		
		 $sql = 'SELECT COUNT(STL) AS Total, HVACID, STL AS TxnID, CONCAT(e.Nickname, " ", e.Surname) AS STL FROM events_1hvacleads AS h LEFT JOIN `1employees` e ON e.IDNo=h.STL LEFT JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo GROUP BY e.IDNo ORDER BY Total DESC';

		 $sql2='SELECT COUNT(*) FROM events_1hvacleads';
		 $AllTotal = $link->query($sql2)->fetchColumn();
		
		echo '<br/><h3>Total No. of Visitors (All Time): ' . $AllTotal . '</h3>';	  
		$columnnameslist=array('STL', 'Total');
		$columnstoadd=array('STL', 'Date');
		
		
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;       
		$editprocess='hvac.php?w=LookupAllTime&STL='; $editprocesslabel='Look up';
		
		include('../backendphp/layout/displayastable.php'); 
		// include('../backendphp/layout/displayastablenoeditnodelete.php'); 
		
		
	break; 
	
	case 'Lookup':
	
	$title='Lookup Summary';
	 echo '<title>'.$title.'</title>';
		
	if (isset($_GET['Date']))
	{
		 $sql='SELECT h.*, HVACID AS TxnID, CONCAT(e1.Nickname, " ", e1.Surname) AS EncodedBy, CONCAT(e.Nickname, " ", e.Surname) AS STL FROM events_1hvacleads AS h LEFT JOIN 1_gamit.0idinfo e1 ON e1.IDNo=h.EncodedByNo JOIN `1employees` e ON e.IDNo=h.STL WHERE STL='.intval($_GET['STL']).' AND DATE=\''.$_GET['Date'].'\' '.(allowedToOpen(4003,'1rtc')?' AND h.STL='.$_SESSION['(ak0)']:'');
		 $sql2='SELECT COUNT(*) FROM events_1hvacleads WHERE DATE=\''.$_GET['Date'].'\' AND STL='.intval($_GET['STL']).'';
	}
	else
	{
	 $sql='SELECT h.*, HVACID AS TxnID, CONCAT(e1.Nickname, " ", e1.Surname) AS EncodedBy, CONCAT(e.Nickname, " ", e.Surname) AS STL FROM events_1hvacleads AS h LEFT JOIN 1_gamit.0idinfo e1 ON e1.IDNo=h.EncodedByNo JOIN `1employees` e ON e.IDNo=h.STL WHERE STL='.intval($_GET['STL']).' AND DATE=CURDATE() '.(allowedToOpen(4003,'1rtc')?' AND h.STL='.$_SESSION['(ak0)']:'');
	$sql2='SELECT COUNT(*) FROM events_1hvacleads WHERE DATE=CURDATE() AND STL='.intval($_GET['STL']).'';
	}
	
		 $AllTotal = $link->query($sql2)->fetchColumn();
		
		$stmt=$link->query($sql);
			$row = $stmt->fetch();
			
		if (isset($_GET['Date']))
		{
			echo '<br/><h3>Date: '.$_GET['Date'].'<br/>Sales Team Leader: ' .$row['STL']. '<br/>Total No. of Visitors: ' . $AllTotal . '</h3>';
		}
		else {
			echo '<br/><h3>Sales Team Leader: ' .$row['STL']. '<br/>Total No. of Visitors Today: ' . $AllTotal . '</h3>';	
		}
	   $columnnameslist=array( 'Company', 'Name', 'Position', 'ContactNo', 'Email', 'Industry', 'STL', 'Date', 'InquiredAbout');
	 
	   
		
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;       
		
		include('../backendphp/layout/displayastable.php'); 
	break;
	
	case 'LookupDate':
	if (!allowedToOpen(4002,'1rtc')) { header('Location:hvac.php?w=AllEntries&denied=true'); }
	$title='Lookup Summary';
	
	$sql = 'SELECT COUNT(STL) AS Total, HVACID, STL AS TxnID, CONCAT(e.Nickname, " ", e.Surname) AS STL FROM events_1hvacleads AS h LEFT JOIN `1employees` e ON e.IDNo=h.STL LEFT JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE Date=\''.$_GET['Date'].'\' GROUP BY e.IDNo ORDER BY Total DESC';
		
			
		 $sql2='SELECT COUNT(*) FROM events_1hvacleads WHERE Date=\''.$_GET['Date'].'\'';
		 $AllTotal = $link->query($sql2)->fetchColumn();
		 
		
		echo '<h3>Date: '.$_GET['Date'].'<br/>Total No. of Visitors: ' . $AllTotal . '</h3>';	  
		$columnnameslist=array('STL', 'Total');
		$columnstoadd=array('STL', 'Date');
		
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;
		
		$editprocess='hvac.php?w=Lookup&Date='.$_GET['Date'].'&STL='; $editprocesslabel='Look up';
		
		include('../backendphp/layout/displayastable.php'); 
		
	break; 
	
	case 'LookupAllTime':
	if (!allowedToOpen(4002,'1rtc')) { header('Location:hvac.php?w=AllEntries&denied=true'); }
	$title='Lookup Summary';
	 echo '<title>'.$title.'</title>';
	 
	 $sql='SELECT h.*, HVACID AS TxnID, CONCAT(e1.Nickname, " ", e1.Surname) AS EncodedBy, CONCAT(e.Nickname, " ", e.Surname) AS STL FROM events_1hvacleads AS h LEFT JOIN 1_gamit.0idinfo e1 ON e1.IDNo=h.EncodedByNo JOIN `1employees` e ON e.IDNo=h.STL WHERE STL='.intval($_GET['STL']).' ORDER BY DATE DESC';
   
	$sql2='SELECT COUNT(*) FROM events_1hvacleads WHERE STL='.intval($_GET['STL']).'';
		 $AllTotal = $link->query($sql2)->fetchColumn();
		 
		 $stmt=$link->query($sql);
			$row = $stmt->fetch();
			
		 echo '<br/><h3>Sales Team Leader: ' .$row['STL']. '<br/>Total No. of All Time Visitors: ' . $AllTotal . '</h3>';
		 
	   $columnnameslist=array('Company', 'Name', 'Position', 'ContactNo', 'Email', 'Industry', 'STL', 'Date', 'InquiredAbout');
	   
		$title='List of Visitors'; $formdesc='Add New Visitors Info.';
		
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;       
		
		include('../backendphp/layout/displayastable.php'); 
	break; 
	
	

}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
