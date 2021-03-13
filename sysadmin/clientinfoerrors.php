<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(3000,'1rtc')) { echo 'No permission'; exit; }
$which=(!isset($_GET['w'])?'ClientsWithSpecChars':$_GET['w']);


$showbranches=false;
include_once('../switchboard/contents.php');

if(in_array($which,array('ClientsWithSpecChars','ClientsWith9TIN','ClientsWithNOTIN','ClientsNot12'))){
	$colorcount=0;
	$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
	$rcolor[1]="FFFFFF";
}

$notin=' AND ClientNo NOT IN (10000,10001,10004) ';

$sqlstep1='SELECT ClientNo,ClientName,TIN FROM 1clients WHERE TIN<>"none" '.$notin.' AND TIN NOT LIKE "%TIN%" AND TIN NOT REGEXP "^[0-9]+$"';
$stmtstep1=$link->query($sqlstep1); $step1count=$stmtstep1->rowCount();

$sqlstep2='SELECT ClientNo,ClientName,TIN FROM 1clients WHERE LENGTH(TIN)=9 '.$notin.'';
$stmtstep2=$link->query($sqlstep2); $step2count=$stmtstep2->rowCount();

$sqlstep3='SELECT ClientNo,ClientName,TIN FROM 1clients WHERE TIN="none" '.$notin.'';
$stmtstep3=$link->query($sqlstep3); $step3count=$stmtstep3->rowCount();

$sqlstep4='SELECT ClientNo,ClientName,TIN FROM 1clients WHERE LENGTH(TIN)<>9 AND LENGTH(TIN)<>12 '.$notin.'';
$stmtstep4=$link->query($sqlstep4); $step4count=$stmtstep4->rowCount();

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');


?>
<body>
<br><div id="section" style="display: block;">

 <div>
		<?php 
			if ($step1count>0){
		?>
		<a id='link' href="clientinfoerrors.php?w=ClientsWithSpecChars" style="color:blue;">Clients w/ SPECIAL or ALPHANUMERIC chars in TIN</a>
		<?php } else {
			echo '<span id="link" style="color:gray;">Clients w/ SPECIAL or ALPHANUMERIC chars in TIN</span> ';
		}
			if ($step1count==0){
		?>
			<a id='link' href="clientinfoerrors.php?w=ClientsWith9TIN" style="color:blue;">Clients w/ 9 chars TIN</a>
		<?php 
			} else {
				echo '<span id="link" style="color:gray;">Clients w/ 9 chars TIN</span> ';
			}
			if ($step1count==0){
		?>
			<a id='link' href="clientinfoerrors.php?w=ClientsWithNOTIN" style="color:blue;">Clients w/ NO TIN</a>
		<?php 
			} else {
				echo '<span id="link" style="color:gray;">Clients w/ NO TIN</span> ';
			}
			if ($step1count==0 AND $step2count==0){
		?>
			<a id='link' href="clientinfoerrors.php?w=ClientsNot12" style="color:blue;">Clients w/ TIN chars is not 12</a>
		<?php 
			} else {
				echo '<span id="link" style="color:gray;">Clients w/ TIN chars is not 12</span> ';
			}
		?>
    </div><br/><br/>
	
<?php




switch ($which)
{
	case 'ClientsWithSpecChars':
	$title='Clients w/ SPECIAL or ALPHANUMERIC chars in TIN';
	$sql=$sqlstep1;
	$stmt=$link->query($sql); $res=$stmt->fetchAll();
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';
	
	echo '<br><form action="clientinfoerrors.php?w=PrClientsWithSpecChars" method="post"><table style="padding:4px;font-size:10.5pt;background-color:#ffffff; display: inline-block; border: 1px solid">';
	echo '<thead style="font-weight:bold;"><tr><th colspan=4 align="right"><input type="submit" value="Update ALL TIN"></th></tr><tr><th>ClientNo</th><th>ClientName</th><th>TIN</th><th>New TIN</th></tr></thead><tbody style=\"overflow:auto;\">';
	foreach($res AS $row){
		echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td>'.$row['ClientNo'].'</td><td>'.$row['ClientName'].'</td><td>'.$row['TIN'].'</td><td><b>'.preg_replace("/[^0-9]/", "",$row['TIN']).'</b></td></tr>';
		$colorcount++;
	}
	echo '</tbody></table></form>';
	
	
	break;
	
	case 'ClientsWith9TIN':
	
	
		$title='Clients w/ 9 chars TIN';
		
		$sql=$sqlstep2;
		$stmt=$link->query($sql); $res=$stmt->fetchAll();
		echo '<title>'.$title.'</title>';
		
		echo '<h3>'.$title.'</h3>';
		
		echo '<br><form action="clientinfoerrors.php?w=PrClientsWith9TIN" method="post"><table style="padding:4px;font-size:10.5pt;background-color:#ffffff; display: inline-block; border: 1px solid">';
		echo '<thead style="font-weight:bold;"><tr><th colspan=4 align="right"><input type="submit" value="Update ALL TIN"></th></tr><tr><th>ClientNo</th><th>ClientName</th><th>TIN</th><th>New TIN</th></tr></thead><tbody style=\"overflow:auto;\">';
		foreach($res AS $row){
			echo '<form action="nopa.php?w=PrClientsWithSpecChars&ClientNo='.$row['ClientNo'].'" method="post"><tr bgcolor='. $rcolor[$colorcount%2].'><td>'.$row['ClientNo'].'</td><td>'.$row['ClientName'].'</td><td>'.$row['TIN'].'</td><td><b>'.$row['TIN'].'000</b></td></tr></form>';
			$colorcount++;
		}
		echo '</tbody></table></form>';
	break;
	
	
	
	case 'ClientsWithNOTIN':
	$title='Clients w/ NO TIN';
	$sql=$sqlstep3;
	$stmt=$link->query($sql); $res=$stmt->fetchAll();
	
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';
	
	echo '<br><form action="clientinfoerrors.php?w=PrClientsWithNOTIN" method="post"><table style="padding:4px;font-size:10.5pt;background-color:#ffffff; display: inline-block; border: 1px solid">';
	echo '<thead style="font-weight:bold;"><tr><th colspan=4 align="right"><input type="submit" value="Update ALL TIN"></th></tr><tr><th>ClientNo</th><th>ClientName</th><th>TIN</th><th>New TIN</th></tr></thead><tbody style=\"overflow:auto;\">';
	foreach($res AS $row){
		echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td>'.$row['ClientNo'].'</td><td>'.$row['ClientName'].'</td><td>'.$row['TIN'].'</td><td><b>No_TIN_'.$row['ClientNo'].'</b></td></tr>';
		$colorcount++;
	}
	echo '</tbody></table></form>';
	
	
	break;
	
	
	case 'ClientsNot12':
	$title='Client\'s TIN = Less than 12 AND 0 chars';
	$sql=$sqlstep4;
	$stmt=$link->query($sql); $res=$stmt->fetchAll();
	
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';
	
	
	echo '<br><form action="clientinfoerrors.php?w=PrClientsNot12" method="post"><table style="padding:4px;font-size:10.5pt;background-color:#ffffff; display: inline-block; border: 1px solid">';
	echo '<thead style="font-weight:bold;"><tr><th colspan=4 align="right"><input type="submit" value="Update ALL TIN"></th></tr><tr><th>ClientNo</th><th>ClientName</th><th>TIN</th><th>Possible TIN</th></tr></thead><tbody style=\"overflow:auto;\">';
	foreach($res AS $row){
		echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td>'.$row['ClientNo'].'</td><td>'.$row['ClientName'].'</td><td>'.$row['TIN'].'</td><td><b>Get_TIN'.$row['ClientNo'].'</b></td></tr></form>';
		$colorcount++;
	}
	echo '</tbody></table></form>';
	
	
	
	break;
	
	
	case 'PrClientsWithSpecChars':
	$sql=$sqlstep1;
	$stmt=$link->query($sql); $res=$stmt->fetchAll();
	foreach($res AS $row){
		$sql1='UPDATE 1clients SET TIN="'.preg_replace("/[^0-9]/", "",$row['TIN']).'" WHERE ClientNo='.$row['ClientNo'].';'; 
		$stmt=$link->prepare($sql1); $stmt->execute();
	}
	echo 'TIN Updated Successfully.';
	break;
	
	case 'PrClientsWith9TIN':
	$sql=$sqlstep2;
	$stmt=$link->query($sql); $res=$stmt->fetchAll();
	foreach($res AS $row){
		$sql1='UPDATE 1clients SET TIN="'.$row['TIN'].'000" WHERE ClientNo='.$row['ClientNo'].';';
		$stmt=$link->prepare($sql1); $stmt->execute();
	}
	echo 'TIN Updated Successfully.';
	break;
	
	case 'PrClientsWithNOTIN':
	$sql=$sqlstep3;
	$stmt=$link->query($sql); $res=$stmt->fetchAll();
	foreach($res AS $row){
		$sql1='UPDATE 1clients SET TIN="No_TIN_'.$row['ClientNo'].'" WHERE ClientNo='.$row['ClientNo'].';';
		$stmt=$link->prepare($sql1); $stmt->execute();
	}
	echo 'TIN Updated Successfully.';
	break;
	
	
	case 'PrClientsNot12':
	$sql=$sqlstep4;
	$stmt=$link->query($sql); $res=$stmt->fetchAll();
	foreach($res AS $row){
		$sql1='UPDATE 1clients SET TIN="Get_TIN'.$row['ClientNo'].'" WHERE ClientNo='.$row['ClientNo'].';';
		$stmt=$link->prepare($sql1); $stmt->execute();
	}
	echo 'TIN Updated Successfully.';
	break;
	
	
}
  $link=null; $stmt=null;
?>
</div> 

