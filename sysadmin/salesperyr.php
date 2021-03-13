<?php
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(3001,'1rtc')) { echo 'No permission'; exit; }

include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=connect_db('hist_incus',1);

echo '<title>Update Hist Incus</title>';
echo '<form action="salesperyr.php" method="POST"><input type="number" name="Yr" autocomplete="off"><input type="submit" name="btnUpdate" value="Update Hist Incus"></form>';

if (isset($_POST['btnUpdate'])){
	if ($_POST['Yr']>=date('Y')){
		echo 'Invalid Date. Should be less than '.date('Y').'.'; exit();
	}
	$montharr=array('1','2','3','4','5','6','7','8','9','10','11','12');

	foreach ($montharr as $month){
		$sqlupdate='UPDATE totalsalesperyr SET `'.$month.'`=TRUNCATE((SELECT TRUNCATE(SUM(s.Qty*s.UnitPrice),0) FROM `'.$_POST['Yr'].'_1rtc`.`invty_2sale` m JOIN `'.$_POST['Yr'].'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID` WHERE txntype IN (1,2,5,10) AND (ClientNo NOT BETWEEN 1000 AND 9999) AND (ClientNo NOT BETWEEN 15001 AND 15005) AND Month(`Date`)='.$month.'),2) WHERE Year='.$_POST['Yr'].';'; // echo $sqlupdate;
		$stmt=$link->prepare($sqlupdate);$stmt->execute();
	}
	
	
	$sql='SELECT * FROM totalsalesperyr WHERE Year='.$_POST['Yr'].'';
	$stmt=$link->query($sql); $res=$stmt->fetch();
	echo '<h3>'.$_POST['Yr'].' Sales</h3>';
	echo '<table border="1px solid black" style="border-collapse:collapse;">';
		echo '<tr><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td><td>11</td><td>12</td></tr>';
		echo '<td>'.number_format($res['1'],2).'</td>';
		echo '<td>'.number_format($res['2'],2).'</td>';
		echo '<td>'.number_format($res['3'],2).'</td>';
		echo '<td>'.number_format($res['4'],2).'</td>';
		echo '<td>'.number_format($res['5'],2).'</td>';
		echo '<td>'.number_format($res['6'],2).'</td>';
		echo '<td>'.number_format($res['7'],2).'</td>';
		echo '<td>'.number_format($res['8'],2).'</td>';
		echo '<td>'.number_format($res['9'],2).'</td>';
		echo '<td>'.number_format($res['10'],2).'</td>';
		echo '<td>'.number_format($res['11'],2).'</td>';
		echo '<td>'.number_format($res['12'],2).'</td>';
		echo '</tr>';
	echo '</table>';
}
 $stmt=null; $link=null;
?>