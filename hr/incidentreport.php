<html>
<head>
<title>Incident Report</title>
<style>
    body { margin: 30px 30px 30px 30px; background: #ffffcc; font-family: sans-serif;}
</style>
<b>Incident Report</b><br>
<script type="text/javascript" language="javascript" src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/disablerclick.js"></script>
</head>
<body>
    <br><br>
As a company, our true strength lies in the integrity and values of our people. <br><br>
Our Employee Handbook and Code of Conduct ensures that we have the guidance needed to conduct ourselves and our roles in a way where we consistently observe these values. 

We are all responsible in ensuring our Code of Conduct is held at the highest standards. <hr>
<?php
$ip=$_SERVER['REMOTE_ADDR'];
 $path=$_SERVER['DOCUMENT_ROOT'];
include_once($path.'/acrossyrs/commonfunctions/hashandcrypt.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once($path.'/acrossyrs/dbinit/userinit.php');
$currentyr=date('Y');
$url_folder = 'yr'.substr($currentyr, -2);
        $link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
		


if(isset($_POST['submit']) OR isset($_POST['sql'])){
	include('../backendphp/layout/confirmwithpasswd.php');
}


if (isset($_POST['sql'])){
		if (($stmt->rowCount()>0) and (verify($pw,$row['uphashmayasin']))){
			 $sqli=$_POST['sql'].'`EncodedByNo`='.$row['IDNo'].',TimeStamp=NOW();';
			 // echo $sqli; exit();
    if($_SESSION['(ak0)']==1002){ echo $_POST['sql'];}
    $stmt=$link->prepare($sqli);$stmt->execute();
    header('Location:/'.$url_folder.'/forms/errormsg.php?err=Sent');
		}  else { header('Location:/'.$url_folder.'/forms/errormsg.php?err=Password'); } // incorrect password
}


$sqllist='SELECT CONCAT(FullName," - ",Position,", ",dept) AS FullName, Branch FROM `attend_30currentpositions`';

if (isset($_POST['submit'])){

	
		if (($stmt->rowCount()>0) and (verify($pw,$row['uphashmayasin']))){ 
                    
                    
                    
                    $loginid=$row['IDNo']; 
                    $reidno=comboBoxValueWithSql($link,'SELECT CONCAT(FullName," - ",Position,", ",dept) AS FullName, IDNo FROM `attend_30currentpositions` e WHERE CONCAT(FullName," - ",Position,", ",dept) LIKE \''.addslashes($_POST['reportname']).'\'','IDNo');
                    $columnnames=array('DateofIncident', 'TimeofIncident', 'Place', 'OtherPeople', 'Summary');
                    $sql='';
                    foreach($columnnames as $field){
                        $sql.=' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
                    }
                        $sqlinsert='INSERT INTO `hr_3incidentreports` SET `ReIDNo`='.$reidno.', '.$sql.' ';
                    
                    if($reidno>=0 and !empty($reidno)){ 
                    echo '<div style="background: #ccffff"><font color="#006666"><i><b>Is this what you want to report?</b></i></font><br><br>';
                    echo 'Name of Person with alleged handbook breach: &nbsp &nbsp'.$_POST['reportname'].'<br><br>Date of Incident: &nbsp &nbsp'.$_POST['DateofIncident']
                            .'<br><br>Time of Incident: &nbsp &nbsp'.$_POST['TimeofIncident']
                            .'<br><br>Place of Incident: &nbsp &nbsp'.$_POST['Place']
                            .'<br><br>Other people involved: &nbsp &nbsp'.$_POST['OtherPeople']
                            .'<br><br>Summary: &nbsp &nbsp'.$_POST['Summary'].'</div>';
                    echo  '<form method=post action=incidentreport.php><input type=hidden name="sql" value="'.$sqlinsert.'"><br><br>
                        <input type=hidden name="login" value="'.$loginid.'"><br>Password: <input type="password" name="pw" value="">
                    <input type=submit name=insert value="Confirm and submit report"></form>';}
                    else { echo '<font color="maroon"><i><b>This report cannot be recorded.  Please choose a name from the dropdown list only.</b></i></font>';}			
		                
                } else { header('Location:/'.$url_folder.'/forms/errormsg.php?err=Password'); } // incorrect password
} else {
?>

<br><br>If you notice a potential breach, please report as follows:<br><br><br><br>

<form action="incidentreport.php" method="POST" >
    Name of Person with alleged handbook breach:&nbsp; <input type="text" size="30" style="text-align: center;" name="reportname" autofocus="autofocus" list="employees"><br><br>
    Date of Incident: <input type="date" size="6" style="text-align: center;" name="DateofIncident" autocomplete="off" required="true">&nbsp &nbsp &nbsp
    Time of Incident: <input type="time" size="6" style="text-align: center;" name="TimeofIncident" autocomplete="off" required="true">&nbsp &nbsp &nbsp
    Place of Incident: <input type="text" size="30" style="text-align: center;" name="Place" autocomplete="off" required="true"><br><br>
    Other people involved <i>(please indicate "none" if no other person is involved)</i>: <input type="text" size="80" style="text-align: center;" name="OtherPeople" autocomplete="off"><br><br>
    Short summary of what happened:<i>(Up to 400 characters per report. Make another report if you exceed.)</i><br> <textarea rows="5" cols="90" maxlength="400" name="Summary"></textarea><br><br>
Reported by ID Number: <input type="text" size="6" style="text-align: center;" name="login" autocomplete="off" required="true">&nbsp &nbsp &nbsp
Password: <input type="password" size="8" style="text-align: center;" name="pw" required="true">&nbsp &nbsp &nbsp
<input type="submit" name="submit" value="Send Report"><br><br></form>
<?php

 echo comboBox($link,$sqllist.' ORDER BY FullName;','Branch','FullName','employees');}   

?>
</body>
</html>