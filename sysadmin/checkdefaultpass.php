<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(2201,'1rtc')) { echo 'No permission'; exit; }

include_once('../backendphp/functions/hashandcrypt.php');
echo '<title>Number of users who uses default password.</title>';
echo '<form action="#" method="POST"><input type="submit" name="btnSubmit" value="Count number of users who uses default password."/></form><br/>';

if (isset($_POST['btnSubmit']))
{
	
	$sqlidno = 'SELECT IDNo, CONCAT(Firstname," ",Surname) AS Name FROM `1employees` WHERE Resigned=0';
	$stmtidno = $link->query($sqlidno);
        //echo $sqlidno;
	$sqlcnt = 'SELECT COUNT(IDNo) AS userdefault FROM `1employees` WHERE Resigned=0';
	$stmtcnt = $link->query($sqlcnt);
	$rowcnt = $stmtcnt->fetch();
        //echo $sqlcnt;
	$cnt = 0;
	
	while($rowidno = $stmtidno->fetch())
	{
		$login = $rowidno['IDNo'];
		$pw = $rowidno['IDNo'];

		$sql = 'SELECT u.IDNo, u.uphashmayasin, e.FullName, e.Branch, e.department FROM `1_gamit`.`1rtcusers` as u JOIN attend_30currentpositions as e ON u.IDNo=e.IDNo WHERE (e.IDNo='.$login.')'; //echo $sql;
		$stmt=$link->prepare($sql);
		//$stmt->bindValue(':UserID', $login, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch();

		if (verify($pw,$row['uphashmayasin'])) {
			//no intention to the display names. maybe unethical? XD
                        
			$cnt++;
                       //  echo $cnt.'. '.$row['IDNo'].': '.$row['FullName'].'. '.$row['Branch'].'<br/>';
                        echo $cnt.'<br/>';
		}
	}

	echo '<br/><b>' .$cnt  . '</b> out of ';// <b>.$rowcnt['userdefault'] . '</b> uses default password.';

}
echo 'end of file';
?>