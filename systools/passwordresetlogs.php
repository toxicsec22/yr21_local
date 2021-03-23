<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(654,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila'); $diraddress='../';
?>

<br><div id="section" style="display: block;">

<?php
include_once('../backendphp/layout/linkstyle.php');
	
		echo '<div>';
echo '<a id=\'link\' href="passwordresetlogs.php?w=Report">Log Reports</a> ';
echo '<a id=\'link\' href="passwordresetlogs.php">Encode Logs</a> ';
echo '<a id=\'link\' href="../../acrossyrs/logincodes/resetpassbyadmin.php">Reset Password</a> ';
			echo '</div>';	
			
$which=(!isset($_GET['w'])?'List':$_GET['w']);

switch ($which)
{
	case 'List':
	if (allowedToOpen(654,'1rtc')) {
		$title='Password Reset Logs'; 
		$formdesc='<br>&nbsp; &nbsp; Password Reset For: <br>&nbsp; &nbsp; &nbsp; &nbsp; <b>Arwan</b> - Automatically Encoded<br>&nbsp; &nbsp; &nbsp; &nbsp; <b>Webmail</b> - Manually Encoded';
		 echo comboBox($link,'SELECT IDNo, CONCAT("(",FullName,") ",dept) AS FullName FROM attend_30currentpositions','FullName','IDNo','employeelist');
		 echo comboBox($link,'SELECT "0" AS PWID,"Arwan" AS `PasswordFor` UNION SELECT "1" AS PWID,"Webmail" AS `PasswordFor` ORDER BY PWID DESC','PWID','PasswordFor','passwordfor');
		$method='post';
				$columnnames=array(
				array('field'=>'IDNo','type'=>'text','size'=>25,'required'=>true,'list'=>'employeelist'),
				array('field'=>'Date','type'=>'date','size'=>30,'value'=>date('Y-m-d'),'required'=>true),
				array('field'=>'PasswordFor?','type'=>'text','size'=>10,'value'=>'Webmail','list'=>'passwordfor','required'=>true)
				);
							
		$action='passwordresetlogs.php?w=Add'; $fieldsinrow=5; $liststoshow=array();
		
		include('../backendphp/layout/inputmainform.php');
		
          $columnnames=array('Name','Date','PasswordFor?','EncodedBy','Timestamp');
		$title='';
		$width='60%';
		$sql='SELECT pwr.*,IF(`PasswordFor?`=0,"Arwan","Webmail") AS `PasswordFor?`,CONCAT(e.Nickname," ",e.SurName) AS Name,CONCAT(e2.Nickname," ",e2.SurName) AS EncodedBy FROM logs_2passwordreset pwr JOIN 1employees e ON pwr.IDNo=e.IDNo JOIN 1employees e2 ON pwr.EncodedByNo=e2.IDNo
		ORDER BY Timestamp DESC';
		$delprocess='passwordresetlogs.php?w=Delete&TxnID=';
		$formdesc='';
		include('../backendphp/layout/displayastable.php');
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	case 'Add':
	if (!allowedToOpen(654,'1rtc')) { echo 'No Permission'; exit(); }
	$sql='INSERT INTO `logs_2passwordreset` SET IDNo='.intval($_POST['IDNo']).',Date="'.$_POST['Date'].'",`PasswordFor?`='.($_POST['PasswordFor?']=='Webmail'?1:0).', TimeStamp=NOW(), EncodedByNo='.$_SESSION['(ak0)'];
	
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:passwordresetlogs.php');
	break;
	
	 case 'Delete':
	 if (!allowedToOpen(654,'1rtc')) { echo 'No Permission'; exit(); }
         require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `logs_2passwordreset` WHERE EncodedByNo='.$_SESSION['(ak0)'].' AND TxnID='.intval($_GET['TxnID']);
        $link->query($sql);
        header("Location:passwordresetlogs.php");
        break; 
		
	case 'Report':
	if (!allowedToOpen(654,'1rtc')) { echo 'No Permission'; exit(); }
	$columnnames=array('Name','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','YrTotal');
	
	$columnmonth=array_diff($columnnames,array('Name','YrTotal'));
	
	$jantodecarwan='';
	$jantodecwebmail=''; $cntr=1;
	foreach($columnmonth AS $monthname){
		$jantodecarwan.='(SELECT IF(COUNT(IDNo)=0,"",COUNT(IDNo)) FROM logs_2passwordreset WHERE MONTH(`Date`)='.$cntr.' AND `PasswordFor?`=0 AND IDNo=pwr.IDNo) AS `'.$monthname.'`,';
		
		$jantodecwebmail.='(SELECT IF(COUNT(IDNo)=0,"",COUNT(IDNo)) FROM logs_2passwordreset WHERE MONTH(`Date`)='.$cntr.' AND `PasswordFor?`=1 AND IDNo=pwr.IDNo) AS `'.$monthname.'`,';
		$cntr++;
	}
		
		$title='Count Reports';
		$formdesc='<br></i><h4>&nbsp; &nbsp; ARWAN</h4><i>';
		$sql='SELECT pwr.*,CONCAT(e.Nickname," ",e.SurName) AS Name,
		'.$jantodecarwan.'
		(SELECT IF(COUNT(IDNo)=0,"",COUNT(IDNo)) FROM logs_2passwordreset WHERE `PasswordFor?`=0 AND IDNo=pwr.IDNo) AS YrTotal		
		FROM logs_2passwordreset pwr JOIN 1employees e ON pwr.IDNo=e.IDNo WHERE `PasswordFor?`=0 GROUP BY IDNo
		';
		echo '<div>';
		echo '<div style="float:left;">';
		include('../backendphp/layout/displayastablenosort.php');
		echo '</div>';
		
		$sql='SELECT pwr.*,CONCAT(e.Nickname," ",e.SurName) AS Name,
		'.$jantodecwebmail.'
		(SELECT IF(COUNT(IDNo)=0,"",COUNT(IDNo)) FROM logs_2passwordreset WHERE `PasswordFor?`=1 AND IDNo=pwr.IDNo) AS YrTotal		
		FROM logs_2passwordreset pwr JOIN 1employees e ON pwr.IDNo=e.IDNo WHERE `PasswordFor?`=1 GROUP BY IDNo
		';
		
		$title='';
		$formdesc='<br><br></i><h4>&nbsp; &nbsp; Webmail</h4><i>';
		echo '<div style="margin-left:45%;">';
		include('../backendphp/layout/displayastablenosort.php');
		echo '</div>';
		echo '</div>';
	break;
}


 $link=null; $stmt=null;
?>
</div> <!-- end section -->
