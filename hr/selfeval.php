<?php session_start(); $title='Self-Evaluation Login';?>
<html>
<head>
<title><?php echo $title;?></title>
<style>
    body { margin: 30px 30px 30px 30px; }
</style>

<script type="text/javascript" language="javascript" src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/disablerclick.js"></script>
</head>
<body style="font-family:Arial;background-color:#80bfff;">

<?php

 $path=$_SERVER['DOCUMENT_ROOT'];
include_once($path.'/acrossyrs/commonfunctions/hashandcrypt.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=!isset($link)?connect_db(date('Y').'_1rtc',0):$link;


if (isset($_POST['submit'])){
	include('../backendphp/layout/confirmwithpasswd.php');
	echo '<b>'.$title.'</b><br><br>';
		if (($stmt->rowCount()>0) and (verify($pw,$row['uphashmayasin']))){
				require ($path.'/acrossyrs/logincodes/todayat7.php');
                    $loginid=$row['IDNo']; 
					 $_SESSION['(ak0)']=$loginid;
					$_SESSION['oss']=1;
					$_SESSION['LAST_ACTIVITY']=time();
					
					if ((time() - $_SESSION['LAST_ACTIVITY'] > 1800)){ //30 minutes 60*30=1800; 
						$nologin=5;  
						include $path.'/logout.php'; exit();
					}
					
					function generate_secure_token($length = 16) { 
						return bin2hex(openssl_random_pseudo_bytes($length));    // important! this has to be a crytographically secure random generator 
					}
					$_SESSION['action_token'] = generate_secure_token();
					
					$sql='SELECT pf.*, CONCAT(e1.FirstName, " ", e1.Surname) AS FullName, e1.DateHired, TRUNCATE(((TO_DAYS(NOW()) - TO_DAYS(`e1`.`DateHired`)) / 365),2) AS `HowLongWithUsinYrs`, e.Nickname as HREncodedBy, Position AS CurrentPosition, b.Branch AS CurrentBranch, c.Company, CONCAT(e2.Nickname, " ", e2.Surname) AS Supervisor, SelfEval, SupervisorEval, IF(HRStatus=1,"Filed","Pending") AS HR_Status, IF(EmpResponse=0,"",IF(EmpResponse=1,"Agree","Disagree")) AS Emp_Response, IF((EmpResponseEncByIDNo<>pf.IDNo),e3.Nickname,"") AS `EmpResponseEditedBy` FROM hr_2perfevalmain pf LEFT JOIN `1employees` e ON e.IDNo=pf.HREncodedByNo JOIN `1employees` e1 ON e1.IDNo=pf.IDNo LEFT JOIN `1employees` e2 ON e2.IDNo=pf.SupervisorIDNo LEFT JOIN `1employees` e3 ON e3.IDNo=pf.EmpResponseEncByIDNo JOIN `1branches` b ON b.BranchNo=pf.CurrentBranchNo JOIN `1companies` c ON c.CompanyNo=e1.RCompanyNo LEFT JOIN `attend_0positions` p ON p.PositionID=pf.CurrentPositionID WHERE ((pf.EmpResponse=0 AND pf.IDNo='.$_SESSION['(ak0)'].'))';
					
					$stmt=$link->query($sql); $result=$stmt->fetchAll();
					
					
					echo '<table style="background-color:white;width:100%;border:1px solid black;border-collapse:collapse;">';
					
					echo '<tr style="background-color:#add8e6;text-align:left;"><th style="padding:4px;">IDNo</th><th>Full Name</th><th>EvalAfterDays</th><th>EvalDueDate</th><th>SelfEval</th><th>SelfRemarks</th><th>Supervisor</th><th>SupervisorEval</th><th>SuperRemarks</th><th>Emp_Response</th><th>EmpRemarks</th><th></th></tr>';
					foreach($result AS $res){
						echo '<tr style="background-color:white;"><td style="padding:4px;">'.$res['IDNo'].'</td><td>'.$res['FullName'].'</td><td>'.$res['EvalAfterDays'].'</td><td>'.$res['EvalDueDate'].'</td><td>'.$res['SelfEval'].'</td><td>'.$res['SelfRemarks'].'</td><td>'.$res['Supervisor'].'</td><td>'.$res['SupervisorEval'].'</td><td>'.$res['SuperRemarks'].'</td><td>'.$res['Emp_Response'].'</td><td>'.$res['EmpRemarks'].'</td><td><a href="perfevalentryform.php?TxnID='.$res['TxnID'].'">Lookup</a></td></tr>';
					}
					echo '</table>';
					
		                
                } else { echo 'Invalid Login'; exit(); } // incorrect password

} else {
	
	echo '<div style="border:1px solid black;width:500px;padding:5px;"><b>'.$title.'</b><br><br>';
	
?>

<form action="selfeval.php" method="POST" >
    ID Number: <input type="text" size="6" style="text-align: center;" name="login" autocomplete="off" required="true">&nbsp &nbsp &nbsp
Password: <input type="password" size="8" style="text-align: center;" name="pw" required="true">&nbsp &nbsp &nbsp
<input type="submit" name="submit" value="Login"></form>
<?php
 }   
 $link=null; $stmt=null; 
?>
</div>
</body>
</html>