<?php
	$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	if (!allowedToOpen(21,'1rtc')) { echo 'No permission'; exit; }
	
	include_once('../switchboard/contents.php');
?>
<html>
<?php
	$title = 'Copy User Data';
	echo "<title>".$title."</title>
	<body>
	<form action='#' method='POST'>
	<h2>".$title."</h2><br>
	<input type='submit' name='btnCopy' value='Copy Data'/>
        <input type='submit' name='btnCopyNewUser' value='Copy New User Data'/>
        <!--<input type='submit' name='btnCopyUserAsin' value='Copy User Asin'/>-->
	</form>";

	if(isset($_POST['btnCopy'])){
		$sql='UPDATE `1_gamit`.`1rtcusers` pu JOIN 1_gamit.1rtcusers g ON pu.IDNo=g.IDNo SET pu.`FingerprintTemplate_L1`=g.`FingerprintTemplate_L1`, pu.`FingerprintTemplate_L2`=g.`FingerprintTemplate_L2`, pu.`FingerprintTemplate_R1`=g.`FingerprintTemplate_R1`, pu.`FingerprintTemplate_R2`=g.`FingerprintTemplate_R2`, pu.`FingerprintAuditor`=g.`FingerprintAuditor` WHERE (g.`FingerprintTemplate_L1` IS NOT NULL) OR (g.`FingerprintTemplate_L2` IS NOT NULL) OR (g.`FingerprintTemplate_R1` IS NOT NULL) OR (g.`FingerprintTemplate_R2` IS NOT NULL) OR (g.`FingerprintAuditor` IS NOT NULL);';
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
		echo '<br>Successful!';
	}
        
        if(isset($_POST['btnCopyNewUser'])){
		$sql='INSERT INTO `1_gamit`.`1rtcusers`
(`IDNo`,`Active`,`LocalNo`,`uphashmayasin`,`Email`,`mobilenumbers`,`saltforid`,`EncodedByNo`,`TimeStamp`,`WorkAssign`)
SELECT `IDNo`,`Active`,`LocalNo`,`uphashmayasin`,`Email`,`mobilenumbers`,`saltforid`,`EncodedByNo`,`TimeStamp`,`WorkAssign`
FROM `1_gamit`.`1rtcusers` WHERE IDNo NOT IN (SELECT IDNo FROM `1_gamit`.`1rtcusers`);';
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
		echo '<br>Successfully added new users!';
	}
	
	// if(isset($_POST['btnCopyUserAsin'])){
		// $sql='UPDATE 1_gamit.1rtcusers g JOIN attend_1programusers pu ON pu.IDNo=g.IDNo SET g.`uphashmayasin`=pu.`uphashmayasin` WHERE g.uphashmayasin<>pu.uphashmayasin AND g.Active=1 AND g.`uphashmayasin`<>"prelimresign";';
		// $stmt=$link->prepare($sql); $stmt->execute();
		
		// echo '<br>Successful!';
	// }
	
	//Prog Cookie
	
	// echo '<>';
	$sqlemployee = "select e.`IDNo` AS `IDNo`,concat(e.`Nickname`,' ',e.`FirstName`,' ',e.`SurName`,' - ',b.`Branch`,' (',b.`BranchNo`,')') AS `NameandBranch` from ((`1employees` as e join `attend_1defaultbranchassign` as d on((e.`IDNo` = d.`IDNo`))) join `1branches` as b on((d.`DefaultBranchAssignNo` = b.`BranchNo`))) JOIN 1_gamit.1rtcusers pu ON e.IDNo=pu.IDNo where (pu.Active=1) order by `NameandBranch`;";
		$stmt = $link->query($sqlemployee);
	
		echo '<datalist id="employeeid">';
			while($row = $stmt->fetch()) {
				echo "<option value='". $row['IDNo']. "'>" . $row['NameandBranch'] ."</option>";
			}
		echo '</datalist>';
		$openform="<form action='#' method='POST'>";
		
	//Branch
		$sqlbranch = "select BranchNo,Branch FROM 1branches WHERE Active=1;";
		$stmt = $link->query($sqlbranch);
		echo '<datalist id="branchid">';
			while($row = $stmt->fetch()) {
				echo "<option value='". $row['BranchNo']. "'>" . $row['Branch'] ."</option>";
			}
		echo '</datalist>';
		
	$openform="<form action='#' method='POST'>";
	
	echo "
	<br><h2>Copy ProgCookie</h2><br>
	".$openform."
	<b>Rider, etc. copies WH super:</b> 
	<input type='submit' name='btnCopyRiderSuper' value='Copy ProgCookie'>
	</form><br>
	".$openform."
	<!--<b>Rider, etc. copies OnBiometrics (WH):</b> 
		<input type='submit' name='btnCopyRiderBio' value='Copy ProgCookie'>
	</form><br>-->
	".$openform."
	<b>IDNo copies from IDNo:</b> 
	FROM <input type=text size=4 name=ProgCookieFrom list=employeeid autocomplete='off'  required=1>
	TO <input type=text size=4 name=ProgCookieTo list=employeeid autocomplete='off'  required=1>
	<input type='submit' name='btnCopyProgCookieFromTo' value='Copy ProgCookie'/>
	</form><br>".$openform."<b>IDNo copies from BranchNo:</b> 
	BranchNo <input type=text size=4 name=ProgCookieBranchNo list=branchid autocomplete='off'  required=1>
	IDNo <input type=text size=4 name=ProgCookieIDNoTo list=employeeid autocomplete='off'  required=1>
	<input type='submit' name='btnCopyProgBranchIDNo' value='Copy ProgCookie'/></form>";
	
	$msg='Successful!';
	if(isset($_POST['btnCopyProgCookieFromTo'])){
		$sqlemployee = "SELECT ProgCookie FROM 1_gamit.1rtcusers WHERE IDNo=".$_POST['ProgCookieFrom']."";
		$stmt = $link->query($sqlemployee); $row = $stmt->fetch();
		$sql='UPDATE 1_gamit.1rtcusers ru SET ru.ProgCookie="'.$row['ProgCookie'].'" WHERE ru.IDNo='.$_POST['ProgCookieTo'].'';
		$stmt=$link->prepare($sql); $stmt->execute();
		echo '<br>'.$msg;
	}
		
	if(isset($_POST['btnCopyProgBranchIDNo'])){
		$sqlbranch = "SELECT ProgCookie FROM 1branches WHERE BranchNo=".$_POST['ProgCookieBranchNo']."";
		$stmt = $link->query($sqlbranch); $row = $stmt->fetch();
		
		$sql='UPDATE 1_gamit.1rtcusers ru SET ru.ProgCookie="'.$row['ProgCookie'].'" WHERE ru.IDNo='.$_POST['ProgCookieIDNoTo'].'';
		$stmt=$link->prepare($sql); $stmt->execute();
		echo '<br>'.$msg;
	}
        
	$sql='CREATE TEMPORARY TABLE currentpos AS SELECT * FROM attend_30currentpositions';
	$stmt=$link->prepare($sql); $stmt->execute();
		
	if(isset($_POST['btnCopyRiderSuper'])){
		
		$sql='CREATE TEMPORARY TABLE supervisor AS SELECT BranchNo, ProgCookie FROM 1_gamit.1rtcusers ru1 JOIN attend_30currentpositions cp1 ON ru1.IDNo=cp1.IDNo WHERE cp1.PositionID=50;';
		$stmt=$link->prepare($sql); $stmt->execute();
		
		$sql0='UPDATE 1_gamit.1rtcusers ru JOIN currentpos cp ON ru.IDNo=cp.IDNo JOIN 1branches b ON cp.BranchNo=b.BranchNo SET ru.ProgCookie=(SELECT ProgCookie FROM supervisor s WHERE s.BranchNo=cp.BranchNo) WHERE b.Pseudobranch=2 AND PositionID IN (1,2,3,4,55)'; //Messenger/Rider, car/truck driver, carpenter, wh personnel
		
		$stmt0=$link->prepare($sql0); $stmt0->execute();
		echo '<br>'.$msg;
	}
	
	/* if(isset($_POST['btnCopyRiderBio'])){
		echo 'Not Yet Working.';
		$sql='CREATE TEMPORARY TABLE branchcookie AS SELECT BranchNo, ProgCookie, OnBiometrics FROM 1branches WHERE Active=1 AND Pseudobranch=2';
		$stmt=$link->prepare($sql); $stmt->execute();
		
		$sql='UPDATE 1_gamit.1rtcusers ru JOIN currentpos cp ON ru.IDNo=cp.IDNo JOIN 1branches b ON cp.BranchNo=b.BranchNo SET ru.ProgCookie=(SELECT ProgCookie FROM branchcookie s WHERE s.BranchNo=cp.BranchNo) WHERE b.Pseudobranch=2 AND PositionID IN (1,2,3,4,55) AND OnBiometrics=2'; //Messenger/Rider, car/truck driver, carpenter, wh personnel
		$stmt=$link->prepare($sql); $stmt->execute();
		echo '<br>'.$msg;
	} */
        
?>
</body>
</html>