<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 

$allowed=array(5631,5632); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:

$showbranches=false;
include_once('../switchboard/contents.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila');

?>

<br><div id="section" style="display: block;">

<?php
include_once('calendarlinks.php');
$which=(!isset($_GET['w'])?'ColorSettings':$_GET['w']);

if (in_array($which,array('ColorSettings','EditSpecificsColorSettings'))){
   $sql='SELECT *, TLIDNo AS TxnID, FullName FROM calllogs_0bgcolorperid bc LEFT JOIN attend_30currentpositions cp ON bc.TLIDNo=cp.IDNo WHERE deptheadpositionid='.$_SESSION['&pos'].'';
   $columnnameslist=array('TLIDNo','FullName','ColorHex');
   $columnstoadd=array('TLIDNo','ColorHex');
}
if (in_array($which,array('DeptColorSettings','EditSpecificsDeptColorSettings'))){
   $sql='SELECT *, deptid AS TxnID FROM 1departments d'; 
   $columnnameslist=array('deptid','department','deptcolor');
   $columnstoadd=array('deptcolor');
}
// $headorasst = ((allowedToOpen(5633,'1rtc'))?'deptheadpositionid='.$_SESSION['&pos'].'':'deptid='.comboBoxValue($link,'attend_30currentpositions','idno',$_SESSION['(ak0)'],'deptid').'');
$headorasst = ((allowedToOpen(5633,'1rtc'))?'deptheadpositionid='.$_SESSION['&pos'].'':((allowedToOpen(5634,'1rtc'))?'deptid='.comboBoxValue($link,'attend_30currentpositions','idno',$_SESSION['(ak0)'],'deptid').'':' (IDNo='.$_SESSION['(ak0)'].' OR LatestSupervisorIDNo='.$_SESSION['(ak0)'].')'));

if (in_array($which,array('ColorSettings','EditSpecificsColorSettings','Schedule','EditSpecificsSchedule'))){
	echo comboBox($link,'SELECT IDNo, FullName FROM attend_30currentpositions WHERE '.$headorasst.'','IDNo','FullName','idnolist');
}
if (in_array($which,array('AddColorSettings','EditColorSettings'))){
   $columnstoadd=array('TLIDNo','ColorHex');
}
if (in_array($which,array('Schedule','EditSpecificsSchedule'))){
	include_once('../backendphp/layout/showencodedbybutton.php');
	if (isset($_POST["btnLookup"])){
		$lcondi='c.DateSchedule LIKE "%'.$currentyr.'-'.(strlen($_POST['monthno'])==1?'0':'').$_POST['monthno'].'%"';
	} else {
		$lcondi='(c.Timestamp LIKE "%'.$currentyr.'-'.date('m').'%" OR c.DateSchedule=CURDATE())';
	}
	$sql='SELECT *,IF(c.BranchNo=0,"Office",b.Branch) AS Branch FROM calendar_2sched c LEFT JOIN attend_30currentpositions cp ON c.EmpIDNo=cp.IDNo LEFT JOIN 1branches b ON c.BranchNo=b.BranchNo WHERE '.$lcondi.' AND '.$headorasst.'';
   $columnnameslist=array('DateSchedule','FullName','Branch','Details');
   if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
}

if (in_array($which,array('AddSchedule','EditSchedule','EditSpecificsSchedule','Schedule'))){
	$columnstoadd=array('DateSchedule','FullName','Branch','Details');
	echo comboBox($link,'SELECT BranchNo, Branch FROM 1branches WHERE PseudoBranch<>1 AND Active=1 UNION SELECT 0, "Office" ORDER BY Branch;','BranchNo','Branch','branchlist');
	
	if (in_array($which,array('AddSchedule','EditSchedule'))){
		$columnstoadd=array('DateSchedule','Details');
		if ($_POST['Branch']=='Office'){
			$branchno=0;
		} else {
			$branchno=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['Branch']),'BranchNo');
		}
	}
}
if (in_array($which,array('AddSchedule','EditSchedule','AddColorSettings','EditColorSettings'))){
	$empidno=comboBoxValue($link,'`attend_30currentpositions`','FullName',addslashes($_POST[($_GET['w']=='AddSchedule' OR $_GET['w']=='EditSchedule')?'FullName':'TLIDNo']),'IDNo');
}


switch ($which)
{
	
	
	case 'DeptColorSettings':
	if (allowedToOpen(5630,'1rtc')) {
		$title='Department Color'; 
        
		$editprocess='calendarsettings.php?w=EditSpecificsDeptColorSettings&deptid='; $editprocesslabel='Edit';
     
		$formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='70%';
		
		include('../backendphp/layout/displayastable.php');
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	case 'EditSpecificsDeptColorSettings':
        if (allowedToOpen(5630,'1rtc')) { //header('Location:calendarsettings.php?denied=true'); }
			$title='Edit Specifics';
			$txnid=intval($_GET['deptid']);

			$sql=$sql.' WHERE deptid='.$txnid;
			
			$columnstoedit=$columnstoadd;
			
			$columnnames=$columnnameslist;
			
			$editprocess='calendarsettings.php?w=EditDeptColorSettings&deptid='.$txnid;
			
			include('../backendphp/layout/editspecificsforlists.php');
		} else {
			echo 'No permission'; exit;
		}
	break;
	
	case 'EditDeptColorSettings':
		if (allowedToOpen(5630,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['deptid']);
		
		$sql='';
		$sql='UPDATE `1departments` SET deptcolor="'.$_REQUEST['deptcolor'].'" WHERE deptid='.$txnid;
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:calendarsettings.php?w=DeptColorSettings");
		} else {
		echo 'No permission'; exit;
		}
		
		
    break;

	case 'ColorSettings':
	if (allowedToOpen(5631,'1rtc')) {
		$title='Color Settings'; 
                
				$method='post';
				$columnnames=array(
				array('field'=>'TLIDNo','caption'=>'IDNo','type'=>'text','size'=>25,'required'=>true,'list'=>'idnolist'),
				array('field'=>'ColorHex','type'=>'text','size'=>25,'required'=>true)
				);
							
		$action='calendarsettings.php?w=AddColorSettings'; $fieldsinrow=4; $liststoshow=array();
		
		include('../backendphp/layout/inputmainform.php');
		
		$delprocess='calendarsettings.php?w=DeleteColorSettings&TLIDNo=';
		$editprocess='calendarsettings.php?w=EditSpecificsColorSettings&TLIDNo='; $editprocesslabel='Edit';
     
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='70%';
		
		include('../backendphp/layout/displayastable.php');
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	case 'AddColorSettings':
	if (allowedToOpen(5631,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='';
		$sql='INSERT INTO `calllogs_0bgcolorperid` SET ColorHex="'.$_POST['ColorHex'].'", TLIDNo='.$empidno;
		
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	case 'EditSpecificsColorSettings':
        if (allowedToOpen(5631,'1rtc')) {
			$title='Edit Specifics';
			$txnid=intval($_GET['TLIDNo']);

			$sql=$sql.' AND TLIDNo='.$txnid;
			
			$columnstoedit=$columnstoadd;
			
			$columnnames=$columnnameslist;
			
			$editprocess='calendarsettings.php?w=EditColorSettings&TLIDNo='.$txnid;
			
			include('../backendphp/layout/editspecificsforlists.php');
		} else {
			echo 'No permission'; exit;
		}
	break;
	
	case 'EditColorSettings':
		if (allowedToOpen(5631,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TLIDNo']);
		$sql='';
		$sql='UPDATE `calllogs_0bgcolorperid` SET TLIDNo="'.$_REQUEST['TLIDNo'].'", ColorHex="'.$_REQUEST['ColorHex'].'" WHERE TLIDNo='.$txnid;
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:calendarsettings.php?w=ColorSettings");
		} else {
		echo 'No permission'; exit;
		}
		
		
    break;
	
	case 'DeleteColorSettings':
	if (allowedToOpen(5631,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `calllogs_0bgcolorperid` WHERE TLIDNo='.intval($_GET['TLIDNo']);
		
		$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:".$_SERVER['HTTP_REFERER']);
	} else {
		echo 'No permission'; exit;
		}

    break;
	
	case 'Schedule':
	if (allowedToOpen(5632,'1rtc')) {
		$title='Encode Schedule';
				$method='post';
				 $columnnames=array(
					array('field'=>'DateSchedule', 'type'=>'date','size'=>20,'value'=>date('Y-m-d'),'required'=>true),
                       array('field'=>'FullName','caption'=>'Employee','type'=>'text','size'=>20,'list'=>'idnolist','required'=>true),
                        array('field'=>'Branch','caption'=>'BranchToVisit','type'=>'text','size'=>20, 'list'=>'branchlist', 'required'=>true),
                    array('field'=>'Details', 'type'=>'text','size'=>25)
		      );
							
		$action='calendarsettings.php?w=AddSchedule'; $fieldsinrow=5; $liststoshow=array();
		
		include('../backendphp/layout/inputmainform.php');
		
		echo '<form method="POST" action="calendarsettings.php?w=Schedule">Filter month: <input type="number" name="monthno" min="1" max="12" size="10"><input type="submit" name="btnLookup" value="Lookup"></form>';
		$delprocess='calendarsettings.php?w=DeleteSchedule&TxnID=';
		$editprocess='calendarsettings.php?w=EditSpecificsSchedule&TxnID='; $editprocesslabel='Edit';
     
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='100%';
		
		include('../backendphp/layout/displayastable.php'); 
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'AddSchedule':
	if (allowedToOpen(5632,'1rtc')){
		require_once($path.'/acrossyrs/logincodes/confirmtoken.php');
		$sql='';
		
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		
		$sql='INSERT INTO `calendar_2sched` SET '.$sql.' TimeStamp=Now(), EncodedByNo='.$_SESSION['(ak0)'].', BranchNo='.$branchno.',EmpIDNo='.$empidno; 
		
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:calendarsettings.php?w=Schedule");
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	
	case 'EditSpecificsSchedule':
        if (allowedToOpen(5632,'1rtc')) {
		$title='Edit Specifics';
		$txnid=intval($_GET['TxnID']);
		
		$sql=$sql.' AND TxnID='.$txnid;
		$columnstoedit=$columnstoadd;
		
		$columnswithlists=array('DateSchedule','FullName','Branch','Details');
		$listsname=array('Branch'=>'branchlist','FullName'=>'idnolist');
		
		$columnnames=$columnswithlists;
		
		$editprocess='calendarsettings.php?w=EditSchedule&TxnID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'EditSchedule':
		if (allowedToOpen(5632,'1rtc')){
		require_once($path.'/acrossyrs/logincodes/confirmtoken.php');
		
		$txnid = intval($_GET['TxnID']);
	
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='UPDATE `calendar_2sched` SET '.$sql.' EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now(),EmpIDNo='.$empidno.',BranchNo='.$branchno.' WHERE TxnID='.$txnid;
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:calendarsettings.php?w=Schedule");
		} else {
		echo 'No permission'; exit;
		}
		
    break;
	
	case 'DeleteSchedule':
	if (allowedToOpen(5632,'1rtc')){
		require_once($path.'/acrossyrs/logincodes/confirmtoken.php');
		$sql='DELETE FROM `calendar_2sched` WHERE TxnID='.intval($_GET['TxnID'].' AND EncodedByNo='.$_SESSION['(ak0)'].';');
		
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:calendarsettings.php?w=Schedule");
	} else {
		echo 'No permission'; exit;
		}
	
    break;
	
	
	case 'Details':
	if (allowedToOpen(5632,'1rtc')){
		echo '<title>Details</title>';
		echo '<br><h3>Details</h3><br>';
		$sql0 = 'SELECT c.*,b.Branch,cp.FullName FROM calendar_2sched c JOIN 1branches b ON c.BranchNo=b.BranchNo JOIN attend_30currentpositions cp ON c.EmpIDNo=cp.IDNo WHERE TxnID='.intval($_GET['TxnID']).'';
		$stmt0=$link->query($sql0);
		$res0 = $stmt0->fetch();
		
		echo 'DateSchedule: '.$res0['DateSchedule'].'<br>';
		echo 'Employee: '.$res0['FullName'].'<br>';
		echo 'BranchToVisit: '.$res0['Branch'].'<br>';
		echo 'Details: '.$res0['Details'].'<br>';
	} else {
		echo 'No permission'; exit;
		}
	
    break;
	
	
	
	case 'SchedSummary':
	if (allowedToOpen(5632,'1rtc')){
		$title = 'Schedule Summary';
		echo '<title>'.$title.'</title>';
		echo '<h2>'.$title.'</h2><br>';
		
		// $sql='SELECT * FROM 1departments ORDER BY department;';
		$sql='SELECT deptid,department FROM attend_30currentpositions WHERE supervisorpositionid = '.$_SESSION['&pos'].' GROUP BY deptid ORDER BY department;';
		$stmt = $link->query($sql);
			
		$choosedept='<select name="DeptID"><option value="All">All My Departments</option>';
		while($row= $stmt->fetch()) {
			$choosedept.='<option value="'.$row['deptid'].'">'.$row['department'].'</option>';
		}
		$choosedept.='</select>';

		echo '<form method="post" action="calendarsettings.php?w=SchedSummary" enctype="multipart/form-data">
		Choose Month (1 - 12):  <input type="text" name="month" value="'.(isset($_POST['month'])?$_POST['month']:date("m")).'"/> '.$choosedept.'
		<input type="submit" name="lookup" value="Lookup"> </form>';
	
		if (isset($_POST['lookup'])){
			if ($_POST['DeptID']<>'All'){
				echo '<br><h2>'.comboBoxValue($link,'1departments','deptid',$_POST['DeptID'],'department').'</h2><br>';
			} else {
				echo '<br><h2>All Depts</h2><br>';
			}
			if ($_POST['DeptID']<>'All'){
				$cond = 'deptid IN (SELECT deptid FROM attend_30currentpositions WHERE deptid IN ('.$_POST['DeptID'].') GROUP BY deptid)';
			} else {
				$cond = 'supervisorpositionid IN (SELECT PositionID FROM attend_30currentpositions WHERE IDNO = '.$_SESSION['(ak0)'].') OR deptheadpositionid = '.$_SESSION['&pos'].'';
			}
			$month=$_POST['month'];	
			$sql0='SELECT e.IDNo, e.Nickname FROM attend_30currentpositions cp join `1employees` e on cp.IDNo=e.IDNo where '.$cond.' AND cp.IDNo<>'.$_SESSION['(ak0)'].'';
			$stmt0=$link->query($sql0);
			$resultauditor=$stmt0->fetchAll();
			$sql='SELECT DateSchedule, ';
			$columnnames=array('DateSchedule');
			foreach ($resultauditor as $auditor){
			   $sql=$sql.'max(case when s.EmpIDNo='.$auditor['IDNo'].' then CONCAT("(",IF(s.BranchNo=0,"Office",Branch),") ",s.Details) end) as `'.$auditor['Nickname'].'`, ';
			   $columnnames[]=$auditor['Nickname'];
			}
			$sql=$sql.' TxnID FROM calendar_2sched s join `1employees` e on s.EmpIDNo=e.IDNo JOIN 1branches b ON s.BranchNo=b.BranchNo where month(DateSchedule)='.$month.' AND DateSchedule LIKE "%'.$currentyr.'%" group by DateSchedule ORDER BY DateSchedule;'; //echo $sql;break;
			$txnid='TxnID';

			$columnstoedit=array_diff($columnnames,array('DateSchedule'));
			
			include('../backendphp/layout/displayastableonlynoheaders.php');
		}

	} else {
		echo 'No permission'; exit;
		}
	
    break;
	
}

 $link=null; $stmt=null;
?>
</div> <!-- end section -->
