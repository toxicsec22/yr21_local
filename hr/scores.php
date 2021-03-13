<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(6507,'1rtc')) { echo 'No permission'; exit; }

$showbranches=false;
include_once('../switchboard/contents.php');

 $link=$link;
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila'); $diraddress='../';

?>

<br><div id="section" style="display: block;">

<?php
include_once('scorelinks.php');

$which=(!isset($_GET['w'])?'StatementList':$_GET['w']);

$sqldept = 'SELECT LatestSupervisorIDNo,deptid,PositionID,deptheadpositionid FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].'';
$stmtdept = $link->query($sqldept);
$rowdept = $stmtdept->fetch();


if (allowedToOpen(6507,'1rtc')) { //Employees
	$condition = ' AND ss.deptid='.$rowdept['deptid'];
} 
if (allowedToOpen(65071,'1rtc')){
	$condition = ' AND (ss.EncodedByNo='.$_SESSION['(ak0)'].' OR (ss.EncodedByNo IN (SELECT IDNo FROM attend_30currentpositions WHERE deptheadpositionid='.$_SESSION['&pos'].')))';
}

if (in_array($which,array('StatementList','EditSpecificsStatement','MeritsList'))){
	include_once('../backendphp/layout/showencodedbybutton.php');

   $sql='SELECT *,SSID as TxnID,PointDesc,CONCAT(PointDesc, " (",WeightinPoints,")") AS PointsInfo FROM hr_71scorestmt ss JOIN hr_70points p ON ss.PointID=p.PointID';
   
	$columnnameslist=array('ShortDesc','Statement','PointsInfo');
   $columnstoadd=array('ShortDesc','Statement','PointDesc');
   
   if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
   
   echo comboBox($link,'SELECT CONCAT(PointDesc," (",WeightinPoints,")") AS Point, PointDesc FROM `hr_70points`','Point','PointDesc','pointlist');
   
   
}

if (in_array($which,array('StatementList','MeritsList'))){
	if (allowedToOpen(array(65071,65076),'1rtc')) {
		
		$title='Statement List'; 
               
				$method='post';
				$columnnames=array(
				array('field'=>'ShortDesc','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'Statement','type'=>'text','size'=>70,'required'=>true),
				array('field'=>'PointDesc','caption'=>'WeightinPoints','type'=>'text','size'=>5,'list'=>'pointlist','required'=>true),
				array('field'=>'StmtCat','type'=>'hidden','size'=>5,'value'=>((isset($_GET['w']) AND $_GET['w']=='MeritsList')?'1':'0'))
				);
							
		$action='scores.php?w=AddStatement'; $fieldsinrow=5; $liststoshow=array();
		
		include('../backendphp/layout/inputmainform.php');
		echo '<br>';
		if (isset($_GET['w']) AND isset($_GET['w'])=='MeritsList'){
			echo '<h2 style="color:green;">Merits</h2>';
		} else { echo '<h2 style="color:blue;">Demerits</h2>'; }
		
		
		$delprocess='scores.php?w=DeleteStatement&SSID=';
		$editprocess='scores.php?w=EditSpecificsStatement&SSID='; $editprocesslabel='Edit';
     
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;
		
		$width='80%';
		
		$sql .= $condition .' AND stmtcat='.((isset($_GET['w']) AND $_GET['w']=='MeritsList')?'1':'0').''; 
	
		include('../backendphp/layout/displayastable.php'); 
	} else {
		echo 'No permission'; exit;
		}
}

if (in_array($which,array('ScoreDemerits','ScoreMerits','EditSpecificsScore','ScoreDemeritsMonth','ScoreMeritsMonth'))){
	if (allowedToOpen(6507,'1rtc')) {
		
		
		// $whereand = ($condition==''?' WHERE': ' AND');
		
		if (($_GET['w']=='ScoreDemerits')) { 
			// $condicat =  $whereand. ' stmtcat=0';
			$condicat =  ' AND stmtcat=0';
			
			$title1 = '<h2 style="color:blue;">Demerits</h2>';
		} else if (($_GET['w']=='ScoreDemeritsMonth') OR ($_GET['w']=='ScoreMeritsMonth')){
			$title1 = '';
			$condicat ='';
		} else if (($_GET['w']=='ScoreMerits')) {
			$condicat = ' AND stmtcat=1';
			$title1 = '<h2 style="color:green;">Merits</h2>';
		} else {
			$txnid=intval($_GET['TxnID']);
			
			$sqlstmtcat = 'SELECT stmtcat FROM hr_71scorestmt ss JOIN hr_72scores s ON ss.SSID=s.SSID WHERE TxnID='.$txnid; 
			$stmtcat = $link->query($sqlstmtcat);
			$row = $stmtcat->fetch();
			
			$condicat = ' AND stmtcat='.$row['stmtcat'];
		}
	} else {
		echo 'No permission'; exit;
		}
}

if (in_array($which,array('AddStatement','EditStatement'))){
   $columnstoadd=array('ShortDesc','Statement');
   $pointid=comboBoxValue($link,'hr_70points','PointDesc',addslashes($_POST['PointDesc']),'PointID');
}

if (in_array($which,array('ScoreDemerits','ScoreMerits','ScoreDemeritsMonth','ScoreMeritsMonth','EditSpecificsScore','LookupScore','ScoreSummaryPending'))){
	if ($_GET['w']=='LookupScore' OR $_GET['w']=='ScoreSummaryPending'){$condicat=''; }
   
   if (($_GET['w']=='ScoreMeritsMonth') OR ($_GET['w']=='ScoreDemeritsMonth') OR ($_GET['w']=='ScoreSummaryPending')){
	   $sql0 = 'SELECT s.*, (CASE
			WHEN DecisionStatus = 1 THEN "Counted"
			WHEN DecisionStatus = 2 THEN "Not Counted"
			ELSE "No Decision Yet"
		END) AS Decision, Statement, IF(cp.deptid<>10,dept,Branch) AS Branch, WeightinPoints, CONCAT(Nickname, " ", Surname) AS EncodedBy, ss.PointID, cp.FullName AS Employee';
   } else {
	   $sql0='SELECT s.*, (CASE
			WHEN DecisionStatus = 1 THEN "Counted"
			WHEN DecisionStatus = 2 THEN "Not Counted"
			ELSE "No Decision Yet"
		END) AS Decision, Statement, IF(cp.deptid<>10,dept,Branch) AS Branch, WeightinPoints, CONCAT(Nickname, " ", Surname) AS EncodedBy, ss.PointID, cp.FullName AS Employee,CONCAT("(",FullName,") ",Department) AS DeptandName FROM hr_72scores s JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo LEFT JOIN 1_gamit.0idinfo id ON s.EncodedByNo=id.IDNo JOIN hr_70points p ON ss.PointID=p.PointID';
   }
   echo comboBox($link,'SELECT Statement, SSID FROM `hr_71scorestmt` ss JOIN 1departments d ON ss.deptid=d.deptid WHERE (1=1 '.$condition.' OR ss.deptid=(SELECT deptid FROM attend_30currentpositions WHERE PositionID=(SELECT deptheadpositionid FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].')) OR ss.EncodedByNo in (SELECT IDNo FROM attend_30currentpositions WHERE PositionID=(SELECT supervisorpositionid FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].'))) '.$condicat.'','SSID','Statement','statementlist');
   // echo comboBox($link,'SELECT IDNo, FullName FROM attend_30currentpositions','IDNo','FullName','employeelist');
   echo comboBox($link,'SELECT IDNo, CONCAT("(",FullName,") ",Department) AS FullName FROM attend_30currentpositions','IDNo','FullName','employeelist');
   
  if (($_GET['w']<>'ScoreMeritsMonth') AND ($_GET['w']<>'ScoreDemeritsMonth')){
	$columnnameslist=array('Branch', 'Employee', 'Statement', 'WeightinPoints', 'DateofIncident', 'Details');
  } else { $columnnameslist=''; }
   $columnstoadd=array('DeptandName','Statement','DateofIncident','Details');
   
}

if (in_array($which,array('ScoreDemerits','ScoreMerits','ScoreDemeritsMonth','ScoreMeritsMonth'))){
	
	if (allowedToOpen(6507,'1rtc')) {
		$title='Scores'; 
        
		if (($_GET['w']=='ScoreDemerits') OR ($_GET['w']=='ScoreMerits')) {
			$method='post';
			$columnnames=array(
			array('field'=>'Statement','type'=>'text','size'=>50,'required'=>true,'list'=>'statementlist'),
			array('field'=>'Employee','type'=>'text','size'=>20,'required'=>true,'list'=>'employeelist'),
			array('field'=>'DateofIncident','type'=>'date','size'=>5,'required'=>true, 'value'=>date('Y-m-d')),
			array('field'=>'Details','type'=>'text','size'=>25,'required'=>false)
			);
								
			$action='scores.php?w=AddScore'; $fieldsinrow=4; $liststoshow=array();
		
		include('../backendphp/layout/inputmainform.php');
		}
		$delprocess='scores.php?w=DeleteScore&TxnID=';
		$addlprocess='scores.php?w=Lookup&TxnID='; $addlprocesslabel='Lookup';
		$editprocess='scores.php?w=EditSpecificsScore&TxnID='; $editprocesslabel='Edit';
		
		if (($_GET['w']=='ScoreDemerits') OR ($_GET['w']=='ScoreDemeritsMonth')){
			echo '<title>Demerits</title>';
			
		} else if (($_GET['w']=='ScoreMerits') OR ($_GET['w']=='ScoreMeritsMonth')){
			echo '<title>Merits</title>';
		}
		echo $title1;
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;
		echo '<br>';
		
		// if ((($_GET['w']=='ScoreDemeritsMonth') OR ($_GET['w']=='ScoreMeritsMonth')) AND (allowedToOpen(65071,'1rtc'))) {
			echo '<form method="POST" action="scores.php?w='.$_GET['w'].'">Month: <input type="text" name="MonthNo" value="'.date('m').'" size=5><input type="submit" name="submit" value="Filter"></form>';
		// }
		if (isset($_POST['submit'])){
			$datecondi = ' AND DateofIncident>="'.date('Y-'.$_POST['MonthNo'].'-01').'" AND DateofIncident<=LAST_DAY("'.date('Y-'.$_POST['MonthNo'].'-01').'")';
			$date= 'MonthNo: '.$_POST['MonthNo'];
		} else {
			if (($_GET['w']=='ScoreDemerits') OR ($_GET['w']=='ScoreMerits')) {
				$datecondi = ' AND (DateofIncident="'.date('Y-m-d').'" OR s.TimeStamp LIKE \'%'.date('Y-m-d').'%\')';
				$date=date('Y-m-d');
			} else {
				$datecondi = ' AND DateofIncident>="'.date('Y-m-01').'" AND DateofIncident<=LAST_DAY("'.date('Y-m-01').'")';
				$date= 'MonthNo: '.date('m');
			}
		}
		$formdesc='Filtered by: '.$date .'<br>';
		  
		$width='100%';
		
		
		if (($_GET['w']<>'ScoreDemeritsMonth') AND ($_GET['w']<>'ScoreMeritsMonth')){
			$sql0 .= ' WHERE ('.$_SESSION['(ak0)'].' in (s.EncodedByNo,ReporteeNo,ReporterHeadNo,ReporteeHeadNo)) AND IF(ReporteeNo='.$_SESSION['(ak0)'].', IF(stmtcat=1,ReporteeHeadStatus=3,ReporterHeadStatus=1), 1=1)' . $datecondi . $condicat; 
		} else {
			$sql01 = ' WHERE ('.$_SESSION['(ak0)'].' in (s.EncodedByNo,ReporteeNo,ReporterHeadNo,ReporteeHeadNo,DecisionByNo)) AND IF(ReporteeNo='.$_SESSION['(ak0)'].', IF(stmtcat=1,ReporteeHeadStatus=3,ReporterHeadStatus=1), 1=1)' . $datecondi . $condicat;
		}
		
		if (($_GET['w']=='ScoreDemerits') OR ($_GET['w']=='ScoreMerits')){
			$sql = $sql0 . ' AND s.EncodedByNo='.$_SESSION['(ak0)'].''; //echo $sql;
			$subtitle='Today\'s Report';
			include('../backendphp/layout/displayastable.php');
		} 
		$formdesc='';
		
		
		if (($_GET['w']=='ScoreDemeritsMonth') OR ($_GET['w']=='ScoreMeritsMonth')) { //Process
			unset($editprocesslabel,$editprocess,$delprocess);
			
			
				$casecondi = ', (CASE
			WHEN ReporterHeadStatus = 3 THEN "GO"
			WHEN ReporterHeadStatus = 4 THEN "NO GO"
			ELSE "Pending"
		END) AS ReporterHeadStatus, (CASE
			WHEN ReporteeStatus = 5 THEN "Acknowledged"
			WHEN ReporteeStatus = 4 THEN "No Go"
			ELSE "Pending"
		END) AS ReporteeStatus, (CASE
			WHEN ReporteeHeadStatus = 3 THEN "GO"
			WHEN ReporteeHeadStatus = 4 THEN "NO GO"
			ELSE "Pending"
		END) AS ReporteeHeadStatus ';
	
				$sql = $sql0 . $casecondi . ' FROM hr_72scores s JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo LEFT JOIN 1_gamit.0idinfo id ON s.EncodedByNo=id.IDNo JOIN hr_70points p ON ss.PointID=p.PointID '.$sql01.' AND stmtcat=1';
				// echo $sql;
				$subtitle='List of <font color="green">Merit</font> Reports';
				
				$columnnameslist=array('Branch', 'Employee', 'Statement', 'WeightinPoints', 'DateofIncident', 'Details','ReporterHeadStatus','ReporteeHeadStatus','ReporteeStatus');
				$columnnames=$columnnameslist;
				// print_r($columnnameslist);
				
				include('../backendphp/layout/displayastablenosort.php');
				
				$casecondi = ', (CASE
				WHEN ReporterHeadStatus = 1 THEN "GO"
				WHEN ReporterHeadStatus = 2 THEN "NO GO"
				ELSE "Pending"
			END) AS ReporterHeadStatus, (CASE
				WHEN ReporteeStatus = 1 THEN "Responded"
				ELSE "Pending"
			END) AS ReporteeStatus, (CASE
				WHEN ReporteeHeadStatus = 1 THEN "GO"
				WHEN ReporteeHeadStatus = 2 THEN "NO GO"
				ELSE "Pending"
			END) AS ReporteeHeadStatus, (CASE
				WHEN DecisionStatus = 1 THEN "Counted"
				WHEN DecisionStatus = 2 THEN "Not Counted"
				ELSE "Pending"
			END) AS Decision ';
			
				$sql = $sql0 . $casecondi . ' FROM hr_72scores s JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo LEFT JOIN 1_gamit.0idinfo id ON s.EncodedByNo=id.IDNo JOIN hr_70points p ON ss.PointID=p.PointID '.$sql01.' AND stmtcat=0';
				// echo $sql;
				$subtitle='List Of <font color="blue">Demerit</font> Reports';
				$columnnameslist=array('Branch', 'Employee', 'Statement', 'WeightinPoints', 'DateofIncident', 'Details','ReporterHeadStatus','ReporteeStatus','ReporteeHeadStatus','Decision');
				$columnnames=$columnnameslist;
				// echo $sql;
				include('../backendphp/layout/displayastablenosort.php');
				
				
		}
		
		
		
	} else {
		echo 'No permission'; exit;
		}
	
}


if (in_array($which,array('AddScore','EditScore'))){
	$ssid=comboBoxValue($link,'hr_71scorestmt','Statement',addslashes($_POST['Statement']),'SSID');
	if ($_GET['w']=='AddScore'){
		$str=addslashes($_POST['Employee']);
	} else {
		$str=addslashes($_POST['DeptandName']);
	}
	
	$start  = strpos($str, '(');
	$end    = strpos($str, ')', $start + 1);
	$length = $end - $start;
	$extractedemp = substr($str, $start + 1, $length - 1);
	
	$employeeid=comboBoxValue($link,'attend_30currentpositions','FullName',$extractedemp,'IDNo');
	$columnstoadd=array('DateofIncident','Details');
}

switch ($which)
{
	case 'StatementList';
	
	break;
	
	
	case 'MeritsList';
	
	break;
	
	
	case 'AddStatement':
	// if (allowedToOpen(65071,'1rtc')){
	if (allowedToOpen(array(65071,65076),'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='INSERT INTO `hr_71scorestmt` SET '.$sql.' PointID='.$pointid.', stmtcat='.$_POST['StmtCat'].',deptid="'.$rowdept['deptid'].'", EncodedByNo='.$_SESSION['(ak0)']; //echo 
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	
	case 'EditSpecificsStatement': //echo 'test';
        if (allowedToOpen(array(65071,65076),'1rtc')) {
		$title='Edit Specifics';
		$txnid=intval($_GET['SSID']);

		$sql=$sql.' WHERE SSID='.$txnid; 
		
		$columnnames=$columnnameslist;
		$columnstoedit=$columnstoadd;
			
		$columnswithlists=array('PointDesc');
		$listsname=array('PointDesc'=>'pointlist');
		
		
		$editprocess='scores.php?w=EditStatement&SSID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'EditStatement':
		
		if (allowedToOpen(array(65071,65076),'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$txnid = intval($_GET['SSID']);
		$sql='';
		
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		
		$sql='UPDATE `hr_71scorestmt` SET '.$sql.' PointID='.$pointid.', EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now() WHERE SSID='.$txnid;
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		
		header("Location:".$_SERVER['HTTP_REFERER']);
		} else {
		echo 'No permission'; exit;
		}
		
    break;
	
	case 'DeleteStatement':
	if (allowedToOpen(array(65071,65076),'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `hr_71scorestmt` WHERE SSID='.intval($_GET['SSID']);
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
	header("Location:".$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}
    break;
	
	
	case 'ScoreDemerits':
	
	break;
	
	case 'ScoreMerits':
	
	break;
	
	
	case 'AddScore':
	if ((allowedToOpen(6507,'1rtc')) AND (!allowedToOpen(65073,'1rtc'))){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }

//NewReporterDeptHead
	$sqlc='select LatestSupervisorIDNo,IDNo,PositionID,deptheadpositionid from attend_30currentpositions where IDNo='.$rowdept['LatestSupervisorIDNo'].'';	
	$stmtc=$link->query($sqlc); $resultc=$stmtc->fetch();
	
	if($rowdept['deptheadpositionid']==$resultc['PositionID']){ // if depthead
		$resreporterdepthead['IDNo']=$resultc['IDNo'];
		$DecisionByNo=$resreporterdepthead['IDNo'];
	}else{
		$sqlcc='select IDNo from attend_30currentpositions where IDNo='.$resultc['LatestSupervisorIDNo'].'';
		$stmtcc=$link->query($sqlcc); $resultcc=$stmtcc->fetch();
		$resreporterdepthead['IDNo']=$resultc['IDNo'];
		$DecisionByNo=$resultcc['IDNo'];
	}
		if($resreporterdepthead['IDNo']==1001){
				$resreporterdepthead['IDNo']=1002;
			}
			
		if($DecisionByNo==1001){
				$DecisionByNo=1002;
			}


//	


//NewReporteeDeptHead
	$sqlce='select LatestSupervisorIDNo,IDNo,PositionID,deptheadpositionid,deptid from attend_30currentpositions where IDNo='.$employeeid.'';	
	// echo $sqlce; exit();
	$stmtce=$link->query($sqlce); $resultce=$stmtce->fetch();
	
		$sqlcee='select IDNo,PositionID from attend_30currentpositions where IDNo='.$resultce['LatestSupervisorIDNo'].'';	
		$stmtcee=$link->query($sqlcee); $resultcee=$stmtcee->fetch();
		
		if($resultce['deptheadpositionid']==$resultcee['PositionID']){
			$resreporteedepthead['IDNo']=$resultcee['IDNo'];

		}else{
			$sqlreporteedepthead = 'SELECT LatestSupervisorIDNo as IDNo from attend_30currentpositions where IDNo='.$resultce['LatestSupervisorIDNo'].''; 
			$stmtreporteedepthead=$link->query($sqlreporteedepthead); $resreporteedepthead=$stmtreporteedepthead->fetch();
		}
		
			if($resreporteedepthead['IDNo']==1001){
				$resreporteedepthead['IDNo']=1002;
			}
			// echo $resreporteedepthead['IDNo']; exit();
//
	
//OldReporterDeptHead

		// check reporter head idno
		// $sqlreporterdepthead = 'SELECT IDNo, deptid FROM attend_30currentpositions WHERE PositionID=(SELECT IF(deptheadpositionid='.$rowdept['PositionID'].',supervisorpositionid,deptheadpositionid) FROM attend_30currentpositions cp WHERE cp.IDNo = '.$_SESSION['(ak0)'].');'; 
		//(SELECT IF(deptheadpositionid=17,supervisorpositionid,deptheadpositionid) FROM attend_30currentpositions cp WHERE cp.IDNo = 1640)
		// $stmtreporterdepthead=$link->query($sqlreporterdepthead); $resreporterdepthead=$stmtreporterdepthead->fetch();
	
//	

//OldReporteeDeptHead
		// check reportee head idno
		// $sqlreporteedepthead = 'SELECT IDNo, deptid FROM attend_30currentpositions WHERE PositionID=(SELECT deptheadpositionid FROM attend_30currentpositions cp WHERE cp.IDNo = '.$employeeid.');';
		// $stmtreporteedepthead=$link->query($sqlreporteedepthead); $resreporteedepthead=$stmtreporteedepthead->fetch();
//		
		
		
		
		
		$sql='INSERT INTO `hr_72scores` SET '.$sql.' SSID='.$ssid.', ReporterHeadNo='.$resreporterdepthead['IDNo'].', ReporteeHeadNo='.$resreporteedepthead['IDNo'].',DecisionByNo='.$DecisionByNo.', ReporteeNo='.$employeeid.', TimeStamp=NOW(), EncodedByNo='.$_SESSION['(ak0)'];
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	
	case 'EditSpecificsScore':
        if ((allowedToOpen(6507,'1rtc')) AND (!allowedToOpen(65073,'1rtc'))){
		$title='Edit Specifics';
		
		
		$sql=$sql0.' WHERE TxnID='.$txnid; 
		$columnnames=$columnnameslist;
		$columnstoedit=$columnstoadd;
			
		$columnswithlists=array('Statement','DeptandName');
		$listsname=array('Statement'=>'statementlist','DeptandName'=>'employeelist');
		
		$editprocess='scores.php?w=EditScore&TxnID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'EditScore':
		
		if ((allowedToOpen(6507,'1rtc')) AND (!allowedToOpen(65073,'1rtc'))){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$txnid = intval($_GET['TxnID']);
		$sql='';
		
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		
		$sql='UPDATE `hr_72scores` SET SSID='.$ssid.', ReporteeNo='.$employeeid.', '.$sql.' EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now() WHERE TxnID='.$txnid;
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:scores.php?w=ScoreDemerits");
		} else {
		echo 'No permission'; exit;
		}
		
		
    break;
	
	case 'DeleteScore':
	if ((allowedToOpen(6507,'1rtc')) AND (!allowedToOpen(65073,'1rtc'))){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `hr_72scores` WHERE TxnID='.intval($_GET['TxnID']).' AND ReporterHeadStatus=0 AND EncodedByNo='.$_SESSION['(ak0)'].'';
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
	header("Location:scores.php?w=ScoreMeritsMonth");
	} else {
		echo 'No permission'; exit;
		}
    break;
	
	case 'Lookup':
		if (!allowedToOpen(6507,'1rtc')){ echo 'No Permission'; exit(); }
		$txnid=intval($_GET['TxnID']);
		
		$sql = 'SELECT s.*,Statement, WeightinPoints, ss.stmtcat, Decision, CONCAT(e1.Nickname, " ", e1.Surname, " - ", IF(cp.deptid<>10,dept,Branch)) AS Reportee, CONCAT(e2.Nickname, " ", e2.Surname) AS ReportedBy, CONCAT(e3.Nickname, " ", e3.Surname) AS ReporterHead, CONCAT(e4.Nickname, " ", e4.Surname) AS ReporteeHead FROM hr_72scores s JOIN `1employees` e1 ON s.ReporteeNo=e1.IDNo JOIN `1employees` e2 ON s.EncodedByNo=e2.IDNo JOIN `1employees` e3 ON s.ReporterHeadNo=e3.IDNo JOIN `1employees` e4 ON s.ReporteeHeadNo=e4.IDNo JOIN attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN hr_70points p ON ss.PointID=p.PointID WHERE s.TxnID = '.$txnid; 
		$stmt=$link->query($sql); $res=$stmt->fetch();
		
		$title = 'Lookup Report';
	    ?><title><?php  echo $title; ?></title>
		
		<div>
		<div style="float:left;">
                <h2><?php  echo $title; ?></h2><br>
                <i>Reports are editable if next step is not yet done.</i><br><br>
                <style>.hoverTable tr:hover {
                        background-color: transparent;
						tr.border_bottom td {
						  border-bottom:1pt solid black;
						}
				</style><div>
	
					<table frame="box" class="hoverTable" width="650px" bgcolor="white">
					<tr><td colspan="4" align="center"><h3><?php if ($res['stmtcat']==0) { echo ($res['DecisionStatus']<>1?'Possible':'') . ' <font color="blue">Demerit</font>'; } else { echo ' <font color="green">Merit</font>'; } ?></h3><br></td></tr>
					
					<tr><td colspan="4"><b>DateofIncident:</b> <?php echo $res['DateofIncident'];?></td><td></td><td></td></tr>
					<tr><td colspan="2"><b>Reportee:</b> <?php echo $res['Reportee'];?></td><td colspan="2"><b>Reporter:</b> <?php echo $res['ReportedBy'];?></td></tr>
					
					<tr><td colspan="2"><b>Re:</b> <?php echo $res['Statement'];?></td><td colspan="2"><b>WeightinPoints:</b> <font color="red"><?php echo $res['WeightinPoints'];?></font></td></tr>
					<tr><td colspan="4"><b>Details:</b> <?php echo $res['Details'];?></td></tr><tr><?php if(($res['EncodedByNo']==$_SESSION['(ak0)']) AND ($res['ReporterHeadStatus']==0)){ echo '<td><a href="scores.php?w=EditSpecificsScore&TxnID='.intval($_GET["TxnID"]).'&action_token='.$_GET['action_token'].'">Edit?</a></td><td><a href="scores.php?w=DeleteScore&TxnID='.intval($_GET["TxnID"]).'&action_token='.$_GET['action_token'].'">Delete Report?</a></td>'; } ?></tr>
					
					<?php if ($res['stmtcat']==0){?>
					
						<?php if ($res['ReporterHeadStatus']==0){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4" style="color:red;"><i>Waiting for Reporter's Head Remarks.</i></td></tr><tr>
						<?php }?>
						
						<?php if ($res['ReporterHeadStatus']<>0){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4"><b>Reporter's Head Remarks:<?php if ($res['ReporterHeadStatus']==2) { echo ' (NO GO)'; }?></b></td></tr><tr><td colspan="4">(<?php echo $res['ReporterHead'];?>): <?php echo $res['RemarksOfReporterHead'];?></td></tr><tr><td><?php if($res['ReporterHeadStatus']<>0 AND $res['ReporteeStatus']==0 AND $res['ReporterHeadNo']==$_SESSION['(ak0)']){ echo '<a href="scores.php?w=UnsetReportersHead&TxnID='.intval($_GET["TxnID"]).'&action_token='.$_SESSION['action_token'].'">Edit?</a>'; } ?></td></tr>
						<?php }?>
						
						<?php if ($res['ReporterHeadStatus']<>0 AND $res['ReporteeStatus']==0){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4" style="color:red;"><i>Waiting for Employee's Reply.</i></td></tr><tr>
						<?php }?>
						
						<?php if ($res['ReporteeStatus']<>0){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4"><b>Reportee's Reply:<?php if ($res['ReporteeStatus']==2) { echo ' (NO GO)'; }?></b></td></tr><tr><td colspan="4">(<?php echo $res['Reportee'];?>): <?php echo $res['RemarksOfReportee'];?></td></tr><tr><td><?php if($res['ReporteeStatus']<>0 AND $res['ReporteeHeadStatus']==0 AND $res['ReporteeNo']==$_SESSION['(ak0)']){ echo '<a href="scores.php?w=UnsetReportee&TxnID='.intval($_GET["TxnID"]).'&action_token='.$_GET['action_token'].'">Edit?</a>'; } ?></td></tr>
						<?php }?>
						
						<?php if ($res['ReporteeStatus']<>0 AND $res['ReporteeHeadStatus']==0){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4" style="color:red;"><i>Waiting for Reportee's Head Comments/Recommendation.</i></td></tr><tr>
						<?php }?>
						
						<?php if (($res['ReporteeHeadStatus']<>0)){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4"><b>Reportee's Head Remarks: <?php if ($res['ReporteeHeadStatus']==1) { echo '(GO)'; } else { echo '(NO GO)'; }?></b></td></tr>
						<tr><td colspan="4">(<?php echo $res['ReporteeHead'];?>): <?php echo $res['RemarksOfReporteeHead'];?></td></tr><tr><td><?php if($res['ReporteeHeadStatus']<>0 AND $res['DecisionStatus']==0 AND $res['ReporteeHeadNo']==$_SESSION['(ak0)']) { echo '<a href="scores.php?w=SetIncReporteeHead&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Edit?</a>'; } ?></td></tr>
						<?php } ?>
						
						<?php if ($res['ReporteeHeadStatus']<>0 AND $res['DecisionStatus']==0){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4" style="color:red;"><i>Waiting for Final Decision.</i></td></tr><tr>
						<?php }?>
						
						<?php if (($res['DecisionStatus']<>0)){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4"><b>Rationale<br><?php if ($res['DecisionStatus']==2) { echo ' (NO GO)'; }?></b></td></tr><tr><td colspan="4">(<?php echo $res['ReporterHead'];?>): <?php echo $res['Decision'];?></td></tr>
						<tr><td style="height:20px;"></td></tr>
						<tr><td><h4>Final Decision: <?php if ($res['DecisionStatus']==1){ echo '<font color="blue">COUNTED</font>'; } else if ($res['DecisionStatus']==2) { echo '<font color="brown">NOT COUNTED</font>'; } ?></h4></td></tr>
						<?php 
						// if ($res['DecisionStatus']==1){ echo (allowedToOpen(65071,'1rtc'))?((allowedToOpen(65075,'1rtc'))?'<tr><td style="height:30px;"></td></tr><tr><td></td></tr>':(($_SESSION['(ak0)']==$res['ReporterHeadNo'])?'<tr><td></td></tr>':'')):''; }
						$unsetaddress = '<a href="scores.php?w=UnsetFinal&action_token='.$_SESSION['action_token'].'&TxnID='.$_GET['TxnID'].'">Unset?</a>';
						if ($res['DecisionStatus']==1 OR $res['DecisionStatus']==2){ echo (allowedToOpen(65071,'1rtc'))?((allowedToOpen(65075,'1rtc'))?'<tr><td style="height:30px;"></td></tr><tr><td>'.$unsetaddress.'</td></tr>':(($_SESSION['(ak0)']==$res['ReporterHeadNo'] AND substr($res['DecisionTS'], 0, 10)==date('Y-m-d'))?'<tr><td style="height:30px;"></td></tr><tr><td>'.$unsetaddress.'</td></tr>':'')):''; }
						} 
						?>
					<?php } else { ?>
					
						<?php if ($res['ReporterHeadStatus']==0){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4" style="color:red;"><i>Waiting for Acknowledgement of Reporter's Head.</i></td></tr><tr>
						<?php }?>
						
						<?php if ($res['ReporterHeadStatus']<>0){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4"><b>Acknowledged by Reporter's Head: </b><?php echo $res['ReporterHead'];?> <?php echo $res['RemarksOfReporterHead'];?></td></tr><tr><td><?php if($res['ReporterHeadStatus']<>0 AND $res['ReporteeHeadStatus']==0 AND $res['ReporterHeadNo']==$_SESSION['(ak0)']){ echo '<a href="scores.php?w=UnsetReporterHeadMerit&TxnID='.intval($_GET["TxnID"]).'&action_token='.$_SESSION['action_token'].'">Unset?</a>'; } ?></td></tr>
						<?php }?>
						
						<?php if ($res['ReporterHeadStatus']<>0 AND $res['ReporteeHeadStatus']==0){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4" style="color:red;"><i>Waiting for Reportee's Head Reply.</i></td></tr><tr>
						<?php }?>
						
						<?php if ($res['ReporterHeadStatus']<>0 AND $res['ReporteeHeadStatus']<>0){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4"><b>Reportee's Head Reply: <?php if ($res['ReporteeHeadStatus']==3) { echo '(GO)'; } else { echo '(NO GO)'; }?> </b><br><?php echo $res['ReporteeHead'];?>: <?php echo $res['RemarksOfReporteeHead'];?></td></tr><tr><td><?php if($res['ReporteeStatus']==3 AND $res['ReporteeHeadStatus']<>4 AND $res['ReporteeHeadNo']==$_SESSION['(ak0)']){ echo '<a href="scores.php?w=SetIncReporteeHeadMerits&TxnID='.intval($_GET["TxnID"]).'&action_token='.$_SESSION['action_token'].'">Unset?</a>'; } ?></td></tr>
						<?php }?>
						
						<?php //if (($res['ReporteeStatus']==3) AND ($res['ReporteeHeadStatus']<>4) AND ($res['ReporteeNo']==$_SESSION['(ak0)'])){?>
						<?php if (($res['ReporteeStatus']==3) AND ($res['ReporteeHeadStatus']<>4)){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4" style="color:red;"><i>Waiting for Employee's Acknowledgement.</i></td></tr><tr>
						<?php }?>
						
						<?php if ($res['ReporteeStatus']==5){?>
						<tr><td style="height:30px;"></td></tr>
						<tr><td colspan="4" style="color:green;"><i>Acknowledged By Employee.</i></td></tr><?php
						$unsetaddress = '<a href="scores.php?w=UnsetFinalMerit&action_token='.$_SESSION['action_token'].'&TxnID='.$_GET['TxnID'].'">Unset?</a>';
						if ($res['ReporteeStatus']==5){ echo (allowedToOpen(65071,'1rtc'))?((allowedToOpen(65075,'1rtc'))?'<tr><td style="height:30px;"></td></tr><tr><td>'.$unsetaddress.'</td></tr>':(($_SESSION['(ak0)']==$res['ReporterHeadNo'] AND substr($res['ReporteeTS'], 0, 10)==date('Y-m-d'))?'<tr><td style="height:30px;"></td></tr><tr><td>'.$unsetaddress.'</td></tr>':'')):''; }
						// }
						?><tr>
						<?php }?>
					<?php } ?>
					
					
			<?php
			if ($res['stmtcat']==0){
				//Reporter Head
				if (($res['ReporterHeadNo']==$_SESSION['(ak0)']) AND $res['ReporterHeadStatus']==0){
					echo '<tr><td colspan="4" align="left">';
					echo '<form action="scores.php?w=PostReporterHead" method="POST"><br/>Remarks:<br/><textarea name="RemarksOfReporterHead" rows="2" cols="50" maxlength="50" placeholder="Leave empty if no remarks <br>(MAXIMUM 50 CHARACTERS ONLY).">'.$res['RemarksOfReporterHead'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="submit" name="btnReporterHeadGo" value=" GO "></div><div style="margin-left:30%;"><input type="submit" name="btnReporterHeadNoGo" value=" NO GO "></div></div></form>';
					
					echo '</td></tr>';
				}
				//Reportee
				if (($res['ReporterHeadStatus']==1) AND ($res['ReporteeNo']==$_SESSION['(ak0)']) AND $res['ReporteeStatus']==0){
					echo '<tr><td colspan="4" align="left">';
					echo '<form action="scores.php?w=PostReportee" method="POST"><br/>Employee\'s Reply:<br/><textarea name="RemarksOfReportee" rows="2" cols="50" maxlength="50" placeholder="Leave empty if no remarks <br>(MAXIMUM 50 CHARACTERS ONLY).">'.$res['RemarksOfReportee'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="submit" name="btnReporteeGo" value=" GO "></div></div></form>';
					
					echo '</td></tr>';
				}
				//Reportee Head
				if (($res['ReporteeStatus']==1) AND ($res['ReporteeHeadNo']==$_SESSION['(ak0)']) AND $res['ReporteeHeadStatus']==0){
					echo '<tr><td colspan="4" align="left">';
					echo '<form action="scores.php?w=PostReporteeHead" method="POST"><br/>Comments/Recommendation:<br/><textarea name="RemarksOfReporteeHead" rows="2" cols="50" maxlength="100" placeholder="Leave empty if no remarks<br>(MAXIMUM 100 CHARACTERS ONLY).">'.$res['RemarksOfReporteeHead'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="submit" name="btnReporteeHeadGo" value=" GO "></div><div style="margin-left:30%;"><input type="submit" name="btnReporteeHeadNoGo" value=" NO GO "></div></div></form>';
					
					echo '</td></tr>';
				}
				
				//Decision
				if (($res['ReporteeHeadStatus']<>0) AND ($res['DecisionByNo']==$_SESSION['(ak0)']) AND $res['DecisionStatus']==0){
					echo '<tr><td colspan="4" align="left">';
					echo '<form action="scores.php?w=PostDecision" method="POST"><br/>Decision:<br/><textarea name="Decision" rows="2" maxlength="50" cols="50" placeholder="Leave empty if no remarks <br>(MAXIMUM 50 CHARACTERS ONLY).">'.$res['Decision'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="submit" name="btnDecisionGo" value=" GO "></div><div style="margin-left:30%;"><input type="submit" name="btnDecisionNoGo" value=" NO GO "></div></div></form>';
					
					echo '</td></tr>';
				}
			} else {
				
				//Reporters Head Acknowledgement
				if (($res['ReporterHeadNo']==$_SESSION['(ak0)']) AND $res['ReporterHeadStatus']==0){
					echo '<tr><td colspan="4" align="left">';
					echo '<form action="scores.php?w=ReporterHeadAcknowledge" method="POST"> <input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><input type="submit" name="btnAcknowledge" value=" Acknowledge "></form>';
					
					echo '</td></tr>';
				}
				//Reportee Head
				// if (($res['ReporteeHeadStatus']==0) AND ($res['ReporteeHeadNo']==$_SESSION['(ak0)']) AND $res['ReporterHeadStatus']<>0 AND $res['ReporteeStatus']<>3){
				if ($res['ReporteeHeadNo']==$_SESSION['(ak0)'] and ($res['ReporteeHeadStatus']==0) AND ($res['ReporterHeadStatus']<>0) AND ($res['ReporteeStatus']<>3)){
					echo '<tr><td colspan="4" align="left">';
					// echo '<form action="scores.php?w=PostReporteeHeadMerit" method="POST"><br/>Comments:<br/><textarea name="RemarksOfReporteeHead" rows="2" cols="50" placeholder="Leave empty if no remarks (MAXIMUM 100 CHARACTERS ONLY).">'.$res['RemarksOfReporteeHead'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="submit" name="btnReporteeHeadGoMerit" value=" GO "></div><div style="margin-left:30%;"><input type="submit" name="btnReporteeHeadNoGoMerit" value=" NO GO "></div></div></form>';
					echo '<form action="scores.php?w=PostReporteeHeadMerit" method="POST"><br/>Comments:<br/><textarea name="RemarksOfReporteeHead" rows="2" cols="50"  maxlength="100" placeholder="Leave empty if no remarks <br>(MAXIMUM 100 CHARACTERS ONLY).">'.$res['RemarksOfReporteeHead'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="radio" name="rdoFinal" value="3"> <b>GO?</b><br><input type="radio" name="rdoFinal" value="4"> <b>NO GO?</b><br><br><input type="submit" value="Final Submit"></div></div></form>';
					
					echo '</td></tr>';
				}
				
				//Employee's Acknowledgement
				if (($res['ReporteeNo']==$_SESSION['(ak0)']) AND $res['ReporteeStatus']==3){
					echo '<tr><td colspan="4" align="left">';
					echo '<form action="scores.php?w=EmployeeAcknowledge" method="POST"><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><input type="submit" name="btnAcknowledge" value=" Acknowledge "><br><br><i>Keep up the good work!</i> </form>';
					
					echo '</td></tr>';
				}
			}
			echo '</table></div>';
			
	break;
	
	
	case 'PostReporterHead':
    // if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = $_POST['TxnID'];
	if(isset($_POST['btnReporterHeadGo'])){
		$sql='UPDATE `hr_72scores` SET ReporterHeadStatus=1, RemarksOfReporterHead="'.$_POST['RemarksOfReporterHead'].'", ReporterHeadTS=Now() WHERE TxnID='.$txnid.'';
	} else {
		$sql='UPDATE `hr_72scores` SET ReporterHeadStatus=2, RemarksOfReporterHead="'.$_POST['RemarksOfReporterHead'].'", ReporterHeadTS=Now(),ReporteeStatus=2,ReporteeHeadStatus=2, DecisionStatus=2 WHERE TxnID='.$txnid.''; 
	}
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:scores.php?w=Lookup&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid);
	
	break;
	
	case 'UnsetReportersHead':
	case 'UnsetReporterHeadMerit':
    // if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = intval($_GET['TxnID']);
	$sql='UPDATE `hr_72scores` SET ReporterHeadStatus=0 WHERE TxnID='.$txnid.'';
	
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:scores.php?w=Lookup&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid);
	
	break;
	
	// case 'UnsetReporterHeadMerit':
    // if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	// require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	// $sql='';
	// $txnid = intval($_GET['TxnID']);
	// $sql='UPDATE `hr_72scores` SET ReporterHeadStatus=0 WHERE TxnID='.$txnid.'';
	
    // $stmt=$link->prepare($sql); $stmt->execute(); 
	// header('Location:scores.php?w=Lookup&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid);
	
	// break;
	
	case 'UnsetReportee':
    if (!allowedToOpen(6507,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = intval($_GET['TxnID']);
	$sql='UPDATE `hr_72scores` SET ReporteeStatus=0 WHERE TxnID='.$txnid.'';
	
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:scores.php?w=Lookup&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid);
	break;
	
	case 'UnsetFinal':
    if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = intval($_GET['TxnID']);
	$sql='UPDATE `hr_72scores` SET DecisionStatus=0 WHERE TxnID='.$txnid.'';
	
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:scores.php?w=Lookup&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid);
	break;
	
	case 'UnsetFinalMerit':
    if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = intval($_GET['TxnID']);
	$sql='UPDATE `hr_72scores` SET ReporteeStatus=3 WHERE TxnID='.$txnid.'';
	
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:scores.php?w=Lookup&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid);
	break;
	
	
	case 'PostReportee':
    if (!allowedToOpen(6507,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = $_POST['TxnID'];
	
	$sql='UPDATE `hr_72scores` SET ReporteeStatus=1, RemarksOfReportee="'.$_POST['RemarksOfReportee'].'", ReporteeTS=Now() WHERE TxnID='.$txnid.'';
	
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:scores.php?w=Lookup&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid);
	
	break;
	
	case 'EmployeeAcknowledge':
    if (!allowedToOpen(6507,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = $_POST['TxnID'];
	
	$sql='UPDATE `hr_72scores` SET ReporteeStatus=5, DecisionStatus=3, ReporteeTS=Now() WHERE TxnID='.$txnid.'';
	
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:scores.php?w=Lookup&TxnID='.$txnid);
	
	break;
	
	case 'ReporterHeadAcknowledge':
    // if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = $_POST['TxnID'];
	
	$sql='UPDATE `hr_72scores` SET ReporterHeadStatus=3, ReporterHeadTS=Now() WHERE TxnID='.$txnid.'';
	// echo $sql; exit();
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:scores.php?w=Lookup&TxnID='.$txnid);
	
	break;
	
	case 'PostReporteeHead':
    // if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = $_POST['TxnID'];
	
	
	$sql='UPDATE `hr_72scores` SET ReporteeHeadStatus='.(isset($_POST['btnReporteeHeadGo'])?1:2).', RemarksOfReporteeHead="'.$_POST['RemarksOfReporteeHead'].'", ReporteeHeadTS=Now() WHERE TxnID='.$txnid.'';
	
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:scores.php?w=Lookup&TxnID='.$txnid);
	
	break;

	case 'PostReporteeHeadMerit':
    // if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = $_POST['TxnID'];
	
	
	$sql='UPDATE `hr_72scores` SET ReporteeHeadStatus='.$_POST['rdoFinal'].', RemarksOfReporteeHead="'.$_POST['RemarksOfReporteeHead'].'", ReporteeHeadTS=Now(),ReporteeStatus=3 WHERE TxnID='.$txnid.'';
	// echo $sql; exit();
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:scores.php?w=Lookup&TxnID='.$txnid);
	
	break;
	
	
	case 'PostDecision':
    // if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = $_POST['TxnID'];
	
	$sql='UPDATE `hr_72scores` SET DecisionStatus='.(isset($_POST['btnDecisionGo'])?1:2).', Decision="'.$_POST['Decision'].'", DecisionTS=Now() WHERE TxnID='.$txnid.'';
	
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:scores.php?w=Lookup&TxnID='.$txnid);
	
	break;
	
	case 'SetIncReporteeHead':
    if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = $_GET['TxnID'];
	
	$sql='UPDATE `hr_72scores` SET ReporteeHeadStatus=0 WHERE TxnID='.$txnid.'';
	
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:scores.php?w=Lookup&TxnID='.$txnid);
	
	break;
	
	case 'SetIncReporteeHeadMerits':
    if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$txnid = $_GET['TxnID'];
	
	$sql='UPDATE `hr_72scores` SET ReporteeHeadStatus=0,ReporteeStatus=0 WHERE TxnID='.$txnid.'';
	
    $stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:scores.php?w=Lookup&TxnID='.$txnid);
	
	break;
	
	
	case 'ScoreSummary':
	case 'ScoreSummaryOthers':
	if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	$title = 'Finished Scores';
	
	if ($_GET['w']=='ScoreSummary') { $title.=' Of My Department'; } else { $title .= ' By My Department'; }
	echo '<title>'.$title.'</title>';
	echo '<br><h3>'.$title.'</h3><br>';
	
	echo '<form method="POST" action="#">Statement Category<select name="StmtCat"><option value="0">Demerits</option><option value="1">Merits</option></select><input type="submit" name="btnShowScore"></form>';
	
	
	if (isset($_POST['btnShowScore'])){
		
		if($_GET['w']=='ScoreSummary'){
			$incondi='ReporteeNo,ReporteeHeadNo';
		} else {
			$incondi='s.EncodedByNo,ReporterHeadNo';
		}
		
		$sql='SELECT cp.Branch, ReporteeNo AS TxnID, FullName AS Employee, department AS Department, Position, 
			IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  1 THEN WeightinPoints END), 2),0) AS January,
          IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  2 THEN WeightinPoints END), 2),0) AS February,
          IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  3 THEN WeightinPoints END), 2),0) AS March,
          IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  4 THEN WeightinPoints END), 2),0) AS April,
          IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  5 THEN WeightinPoints END), 2),0) AS May,
          IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  6 THEN WeightinPoints END), 2),0) AS June,
          IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  7 THEN WeightinPoints END), 2),0) AS July,
          IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  8 THEN WeightinPoints END), 2),0) AS August,
          IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  9 THEN WeightinPoints END), 2),0) AS September,
          IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN 10 THEN WeightinPoints END), 2),0) AS October,
          IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN 11 THEN WeightinPoints END), 2),0) AS November,
          IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN 12 THEN WeightinPoints END), 2),0) AS December FROM hr_72scores s JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo JOIN hr_70points p ON ss.PointID=p.PointID WHERE ('.$_SESSION['(ak0)'].' in ('.$incondi.')) AND stmtcat='.$_POST['StmtCat'].' AND DecisionStatus='.($_POST['StmtCat']==0?1:3).' GROUP BY ReporteeNo ORDER BY Department,JLID DESC;';
		// echo $sql;
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnameslist=array('Branch', 'Employee', 'Department', 'Position', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
		$columnnames=$columnnameslist;
		$editprocess='scores.php?w=LookupScore&StmtCat='.$_POST['StmtCat'].'&IDNo='; $editprocesslabel='Lookup';
		$width='80%';
		
		$title='';
		$subtitle = ' '.($_POST['StmtCat']==0?'Demerits':'Merits') .'';
		include('../backendphp/layout/displayastablenosort.php');	
	}
	break;
	
	
	case 'LookupScorePerPersonPerMonth':
	if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	echo comboBox($link,'SELECT IDNo, FullName FROM attend_30currentpositions '.(allowedToOpen(65072,'1rtc')?'':' WHERE deptid='.$rowdept['deptid'].' '.((allowedToOpen(65074,'1rtc'))?' OR deptid=10':'').'').'','FullName','IDNo','employeelist');
	$title = 'Lookup Score Per Person Per Month';
	echo '<br><br><h3>'.$title.'</h3>';
	echo '<title>'.$title.'</title>';
	echo '<form method="POST" action="#">Month: <input type="number" min="1" max="12" name="MonthNo" style="width: 50px" value='.date('m').'> Employee <input type="text"  list="employeelist" name="IDNo"><input type="submit" name="btnShowScore" value="Lookup"></form>';
	
	
	
	if (isset($_POST['btnShowScore'])){
		
		
		$sql0='SELECT s.*, ReporteeNo AS TxnID, FullName AS Employee, department AS Department, Branch, Position, TRUNCATE(SUM(WeightinPoints),2) AS Points FROM hr_72scores s JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo JOIN hr_70points p ON ss.PointID=p.PointID WHERE Month(DateofIncident)='.$_POST['MonthNo'].' AND ReporteeNo='.$_POST['IDNo'].' ';
		$lookupprocess = 'scores.php?w=LookupScore&MonthNo='.$_POST['MonthNo'].'';
		$formdesc=''; $txnid='TxnID';
		$columnnameslist=array('Branch', 'Employee', 'Department', 'Position', 'Points');
		$columnnames=$columnnameslist;
		
		$width='80%';
		$title='';
		echo '<br><h3><font color="green">Merits</font></h3>';
		$title='';
		$editprocess=$lookupprocess.'&StmtCat=1&IDNo='; $editprocesslabel='Lookup';
		$sql = $sql0 . ' AND DecisionStatus=3 AND stmtcat=1 GROUP BY ReporteeNo ORDER BY Department,JLID DESC;';
		include('../backendphp/layout/displayastablenosort.php');
		
		echo '<br><br><h3><font color="blue">Demerits</font></h3>';
		$editprocess=$lookupprocess.'&StmtCat=0&IDNo='; $editprocesslabel='Lookup';
		$sql = $sql0 . ' AND DecisionStatus=1 AND stmtcat=0 GROUP BY ReporteeNo ORDER BY Department,JLID DESC;'; //echo $sql;
		include('../backendphp/layout/displayastablenosort.php');
			
	}
	break;
	
	
	
	case 'ScoreSummaryAll':
	if (!allowedToOpen(65072,'1rtc')) { echo 'No permission'; exit; }
	$title1 = 'All Score Summary (Finished Decision)';
	echo '<br><br><h3>'.$title1.'</h3>';
	
	$sql='SELECT d.deptid, department FROM 1departments d JOIN hr_71scorestmt ss ON d.deptid=ss.deptid JOIN hr_72scores s ON ss.SSID=s.SSID GROUP BY ss.deptid';
	
	$stmt = $link->query($sql);
	
	echo '<form method="POST" action="#">MonthNo: <input type="number" min="1" max="12" name="MonthNo" style="width: 50px" value='.date('m').'> ReportedBy? <select name="deptid"><option value="All">All Departments</option>';
	while($row= $stmt->fetch()) {
		echo '<option value="'.$row['deptid'].'">'.$row['department']. ' Dept</option>';
	}
	echo '</select><input type="submit" name="btnShowScore"></form>';
	
	
	if (isset($_POST['btnShowScore'])){
		$sql0='SELECT s.*, ReporteeNo AS TxnID, FullName AS Employee, cp.department AS Department,d.department AS ReportingDepartment, Branch, Position, TRUNCATE(SUM(WeightinPoints),2) AS Points FROM hr_72scores s JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo JOIN hr_70points p ON ss.PointID=p.PointID JOIN 1departments d ON ss.deptid=d.deptid WHERE Month(DateofIncident)='.$_POST['MonthNo'].' '.($_POST['deptid']=='All'?'':'AND ss.deptid='.$_POST['deptid'].'').'';
		
		
		$formdesc=''; $txnid='TxnID';
		// $columnnameslist=array('Branch', 'Employee', 'Department', 'Position', 'ReportingDepartment', 'Merits', 'Demerits', 'NotCounted');
		$columnnameslist=array('Branch', 'Employee', 'Department', 'Position', 'ReportingDepartment', 'Points');
		$columnnames=$columnnameslist;
		
		
		$title='Scores';
		$subtitle = ' <font color="blue">Demerit</font>';
		$sql = $sql0.' AND stmtcat=0 AND DecisionStatus=1 GROUP BY ReporteeNo'.(($_POST['deptid']=='All')?',ss.deptid':'').' ORDER BY Department,JLID DESC;';
		
		$lookupaddr = 'scores.php?w=LookupScore&MonthNo='.$_POST['MonthNo'].'&StmtCat=';
		// echo $sql;
		echo '<div><div style="float:left;">';
		$editprocess=$lookupaddr.'0&IDNo='; $editprocesslabel='Lookup';
		$width='80%';
		include('../backendphp/layout/displayastablenosort.php');
		
		echo '</div><div style="margin-left:50%">';
		$editprocess=$lookupaddr.'1&IDNo='; $editprocesslabel='Lookup';
		$subtitle = '<font color="green">Merit</font>';
		$sql = $sql0.' AND stmtcat=1 AND DecisionStatus=3 GROUP BY ReporteeNo'.(($_POST['deptid']=='All')?',ss.deptid':'').' ORDER BY Department,JLID DESC;';
		
		include('../backendphp/layout/displayastablenosort.php');
		echo '</div>';
		
		
	}
	break;
	
	case 'Offense':
	if (!allowedToOpen(array(65071,217),'1rtc')) { echo 'No permission'; exit; }
        $title='Offenses';
	
        // check maxpoint
		$sqlmax = 'SELECT MaxPoint FROM hr_70maxpoint LIMIT 1;'; 
		$stmtmax=$link->query($sqlmax); $resmax=$stmtmax->fetch();
		$formdesc='<br><font color="red">'.$resmax['MaxPoint'] . ' points in one calendar month will result in one offense.</font><br><br>';
                
	
                
                $sql0='CREATE TEMPORARY TABLE offenses AS SELECT MONTHNAME(DateofIncident) AS `Month`, MONTH(DateofIncident) AS MonthNo, ReporteeNo, FullName AS Employee, cp.department AS Department,Branch, Position, SUM(WeightinPoints) AS Points FROM hr_72scores s JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo JOIN hr_70points p ON ss.PointID=p.PointID JOIN 1departments d ON ss.deptid=d.deptid WHERE stmtcat=0 AND DecisionStatus=1 '.(!allowedToOpen(217 ,'1rtc')?'AND cp.deptheadpositionid='.$_SESSION['&pos']:'').' GROUP BY MONTH(DateofIncident), ReporteeNo HAVING Points>=10 ORDER BY MONTH(DateofIncident), Department,Branch,PositionID';
		$stmt0=$link->prepare($sql0); $stmt0->execute();
		$sql1='SELECT `Month`, MonthNo FROM offenses GROUP BY MonthNo ORDER BY MonthNo DESC';                
                $sql2='SELECT * FROM offenses';                
                $columnnames1=array('Month');
                $columnnames2=array('Employee','Department','Branch','Position','Points');
                $groupby='MonthNo'; $orderby=''; $nocount1=true;
		include('../backendphp/layout/displayastablewithsub.php');
		
	
	break;
	
	
	case 'NotCountedReports':
	
	if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	$title = 'Not Counted Reports';
	
		$sql='SELECT s.*, FullName AS Employee, cp.department AS Department,d.department AS ReportingDepartment, Branch, Position, TRUNCATE(SUM(WeightinPoints),2) AS Points FROM hr_72scores s JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo JOIN hr_70points p ON ss.PointID=p.PointID JOIN 1departments d ON ss.deptid=d.deptid WHERE (DecisionStatus=2 OR ReporteeHeadStatus=4) '.(allowedToOpen(65072,'1rtc')?'':'AND ss.deptid='.$rowdept['deptid']).' ORDER BY Branch,Department,JLID DESC';
		// echo $sql;
		$formdesc=''; $txnid='TxnID';
		
		$columnnameslist=array('Branch', 'Employee', 'Department', 'Position', 'ReportingDepartment', 'Points');
		$columnnames=$columnnameslist;
		
		$width='80%';
		include('../backendphp/layout/displayastable.php');
		
		
	break;
	
	
	case 'LookupScore':
	if (!allowedToOpen(65071,'1rtc')) { echo 'No permission'; exit; }
	$title='Score Summary per Employee'; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;
		
		$width='80%';
		// $sql = $sql0.' WHERE ReporteeNo='.intval($_GET['IDNo']).' AND Month(DateofIncident)='.$_GET['MonthNo'].' AND stmtcat='.$_GET['StmtCat'].'';
		$sql = $sql0.' WHERE ReporteeNo='.intval($_GET['IDNo']).' AND stmtcat='.$_GET['StmtCat'].' ORDER BY DateofIncident DESC';
		$addlprocess='scores.php?w=Lookup&TxnID='; $addlprocesslabel='Lookup';
		
		include('../backendphp/layout/displayastable.php'); 
	
	break;
	
	case 'ScoreSummaryPending':
	if (!allowedToOpen(65072,'1rtc')) { echo 'No permission'; exit; }
	$title='All Pending Reports'; $formdesc=''; $txnid='TxnID';
	$addlprocess='scores.php?w=Lookup&TxnID='; $addlprocesslabel='Lookup';
	// $sql01='';
		$casecondi = ', (CASE
			WHEN ReporterHeadStatus = 3 THEN "GO"
			WHEN ReporterHeadStatus = 4 THEN "NO GO"
			ELSE "Pending"
		END) AS ReporterHeadStatus, (CASE
			WHEN ReporteeStatus = 5 THEN "Acknowledged"
			WHEN ReporteeStatus = 4 THEN "No Go"
			ELSE "Pending"
		END) AS ReporteeStatus, (CASE
			WHEN ReporteeHeadStatus = 3 THEN "GO"
			WHEN ReporteeHeadStatus = 4 THEN "NO GO"
			ELSE "Pending"
		END) AS ReporteeHeadStatus ';
	
				$sql = $sql0 . $casecondi . ' FROM hr_72scores s JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo LEFT JOIN 1_gamit.0idinfo id ON s.EncodedByNo=id.IDNo JOIN hr_70points p ON ss.PointID=p.PointID WHERE stmtcat=1 AND (DecisionStatus=0) ORDER BY DateOfIncident DESC';
				// echo $sql;
				$subtitle='<font color="green">Merits</font>';
				
				$columnnameslist=array('Branch', 'Employee', 'Statement', 'WeightinPoints', 'DateofIncident', 'Details','ReporterHeadStatus','ReporteeHeadStatus','ReporteeStatus');
				$columnnames=$columnnameslist;
				include('../backendphp/layout/displayastablenosort.php');
				
				
				$casecondi = ', (CASE
				WHEN ReporterHeadStatus = 1 THEN "GO"
				WHEN ReporterHeadStatus = 2 THEN "NO GO"
				ELSE "Pending"
			END) AS ReporterHeadStatus, (CASE
				WHEN ReporteeStatus = 1 THEN "Responded"
				ELSE "Pending"
			END) AS ReporteeStatus, (CASE
				WHEN ReporteeHeadStatus = 1 THEN "GO"
				WHEN ReporteeHeadStatus = 2 THEN "NO GO"
				ELSE "Pending"
			END) AS ReporteeHeadStatus, (CASE
				WHEN DecisionStatus = 1 THEN "Counted"
				WHEN DecisionStatus = 2 THEN "Not Counted"
				ELSE "Pending"
			END) AS Decision ';
			
				$sql = $sql0 . $casecondi . ' FROM hr_72scores s JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo LEFT JOIN 1_gamit.0idinfo id ON s.EncodedByNo=id.IDNo JOIN hr_70points p ON ss.PointID=p.PointID WHERE stmtcat=0 AND DecisionStatus=0 ORDER BY DateOfIncident DESC';
				$title='';
				$subtitle='<font color="blue">Demerit</font>';
				$columnnameslist=array('Branch', 'Employee', 'Statement', 'WeightinPoints', 'DateofIncident', 'Details','ReporterHeadStatus','ReporteeStatus','ReporteeHeadStatus','Decision');
				$columnnames=$columnnameslist;
				
				include('../backendphp/layout/displayastablenosort.php');
	
	break;
	
	case'ScoresOfMyTeam':
	$title='Scores Of My Team';				
			if (allowedToOpen(65077,'1rtc')) {  
				$condition='where 1=1';
			}else{				
				$condition='where LatestSupervisorIDNo='.$_SESSION['(ak0)'].'';
			}
	$c=1;
	while($c<=2){
		if($c==1){
			$subtitle='Merits';
			$condition2=' AND stmtcat=1 and DecisionStatus=1';
		}else{
			$title='';
			$subtitle='Demerits';
			$condition2='AND stmtcat=0 and DecisionStatus=1';
		}
			$columnnames=array('Branch','Employee','Statement','WeightinPoints','DateOfIncident','Details');
			$sql='select Branch,FullName as Employee,DateOfIncident,Details,Statement,WeightinPoints from hr_72scores s JOIN hr_71scorestmt ss ON ss.SSID=s.SSID JOIN hr_70points p ON p.PointID=ss.PointID JOIN attend_30currentpositions cp ON cp.IDNo=s.ReporteeNo '.$condition.' '.$condition2.' ORDER BY DateOfIncident DESC';
			// echo $sql;
			include('../backendphp/layout/displayastablenosort.php');
	$c++;
	}
	break;
	
	
	
}

 $link=null; $stmt=null;
?>
</div> <!-- end section -->
