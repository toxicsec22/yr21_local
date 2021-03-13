<?php
date_default_timezone_set('Asia/Manila');
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if(!allowedToOpen(array(6224,6225,6227,6226,6228,6110),'1rtc')){ echo 'No Permission'; exit(); }
$showbranches=false;
include_once('../switchboard/contents.php');

 
include_once('../backendphp/layout/linkstyle.php');

	
if(allowedToOpen(array(6224,6225,6227,6226,6228,6110),'1rtc')){
	$which=(!isset($_GET['w'])?'List':$_GET['w']);
?>
<br><div id="section" style="display: block;">

    <div>
        <a id='link' href="idexpirationdate.php">Missing Expiry Dates</a>
		<a id="link" href="idexpirationdate.php?all=1">ID Expiry Dates</a>
		<a id="link" href="idexpirationdate.php?w=Expired">Expired ID's</a>
    

<?php
}

echo '</div><br/><br/>';
$forminfo='';




if (in_array($which,array('List','Expired'))){
	$maincon='AND cp.IDNo='.$_SESSION['(ak0)'].'';
		$posin='';
		if(allowedToOpen(6225,'1rtc')){
			$stmtposids=$link->query('SELECT GROUP_CONCAT(DISTINCT(PositionID)) AS PositionIDs FROM attend_30currentpositions WHERE deptheadpositionid='.$_SESSION['&pos'].''); $resposids=$stmtposids->fetch();
			
			$posin=' OR PositionID IN ('.$resposids['PositionIDs'].')';
				
			$maincon='AND cp.PositionID IN ('.$resposids['PositionIDs'].')';
		}
		
		if(allowedToOpen(array(6224,6227),'1rtc')){
			$maincon='AND (LatestSupervisorIDNo='.$_SESSION['(ak0)'].' OR cp.IDNo='.$_SESSION['(ak0)'].')';
		}
		
		if (allowedToOpen(6110,'1rtc')){ //ops liaison
           $stmt0=$link->query('SELECT deptid FROM `attend_30currentpositions` WHERE IDNo='.$_SESSION['(ak0)']);
           $res0=$stmt0->fetch();
		   
		   $maincon='AND deptid IN ('.(($res0['deptid']==70)?'70,10':$res0['deptid']).')';
			
		}
		
		if(allowedToOpen(array(6226,6228),'1rtc')){ //viewer
			$maincon='';
		}
}

switch ($which)
{
	
	
	
	case 'List':
	$all='';
	if(isset($_GET['all'])){
		$addlsql='1=1';
		$title='ID Expiry Dates';
		$all='&all=1';
	} else {
		$addlsql=' IDExpiry IS NULL';
		$title='Missing Expiry Dates';
	}
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3><br>';
	$sqlsub='SELECT cp.IDNo, FullName,LEFT(IDExpiry,7) AS IDExpiry,IF(cp.deptid IN (10),Branch,dept) AS `Branch/Dept` FROM attend_30currentpositions cp JOIN 1employees e ON cp.IDNo=e.IDNo WHERE '.$addlsql.' '.$maincon.' ORDER BY `Branch/Dept`';
	
		$columnsub=array('FullName','Branch/Dept','IDExpiry');
		
		
		$width='50%';
		$colwithmonthsub=array('IDExpiry');
			$editprocess='idexpirationdate.php?w=EditIDExpiry'.$all.'&IDNo=';
			$editprocesslabel='Enter';
			 $editok=true; $editsub=true;
			 $columnstoedit=array('IDExpiry');
			  $txnsubid='IDNo';
        include('../backendphp/layout/displayastableeditcellssub.php');
	
	break;
	
	case 'EditIDExpiry':
	
	if(substr($_POST['IDExpiry'],0,4)<($lastyr-1) OR substr($_POST['IDExpiry'],0,4)>$currentyr+4){
		echo 'Invalid Expiry Date'; exit();
	}
	$d = new DateTime( ''.$_POST['IDExpiry'].'-01' ); 
	$idvalidity=$d->format( 'Y-m-t' );
	
	$sql='UPDATE `1employees` SET IDExpiry="'.$idvalidity.'",EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE IDNo='.$_GET['IDNo'];
	
	$stmt=$link->prepare($sql); $stmt->execute();
	
	header('Location:idexpirationdate.php?'.(isset($_GET['all'])?'all=1':'').'');
	
	
	break;
	
	
	case 'Expired':

		$title='Expired ID\'s';
	
	echo '<title>'.$title.'</title>';
	
	$formdesc='</i><form action="#" method="POST">Expired as of: <input type="month" name="ExpiryDate" value="'.(isset($_POST['ExpiryDate'])?$_POST['ExpiryDate']:date('Y-m')).'"> <input type="submit" value="Lookup"></form><i>';
	if(isset($_POST['ExpiryDate'])){
		$addlsql='LEFT(IDExpiry,7)<="'.$_POST['ExpiryDate'].'"';
		// echo $_POST['ExpiryDate'];
	} else {
		$addlsql='LEFT(IDExpiry,7)<LEFT(CURDATE(),7)';
	}
	
	
	$sql='SELECT cp.IDNo, FullName,CONCAT(MONTHNAME(IDExpiry)," ",YEAR(IDExpiry)) AS IDExpiry,IF(cp.deptid IN (10),Branch,dept) AS `Branch/Dept` FROM attend_30currentpositions cp JOIN 1employees e ON cp.IDNo=e.IDNo WHERE IDExpiry IS NOT NULL AND '.$addlsql.' '.$maincon.' ORDER BY `Branch/Dept`';
	
		$columnnames=array('FullName','Branch/Dept','IDExpiry');
		
		
		$width='50%';
        include('../backendphp/layout/displayastable.php');
	
	break;
	
	
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
