<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 
if (!allowedToOpen(613,'1rtc')){ echo 'No permission'; exit;}
$showbranches=false; include_once('../switchboard/contents.php');

?>
<html>
<head>
<title>Delete Attendance</title>
<form action=deleteattendofresigned.php method="POST">
    Please make sure that you have tagged employee as Resigned in Employees and ID Tables (including date of resignation).<br>
    Only future dates are deleted.<br><br>
    Delete attendance for ID No.<input type="text" autocomplete=false required=true name="IDNo">
    <input type="submit" value="Delete!" name="submit">
</form>
<?php
if (!isset($_POST['submit'])){
goto noform;
}
$idno=$_POST['IDNo'];
$sql='delete `attend_2attendance`.* FROM `attend_2attendance` join 1employees  where `Resigned`=1 and `attend_2attendance`.`DateToday`>=\''. date('Y-m-d').'\' and `attend_2attendance`.`IDNo`=\''.$idno.'\'';
$stmt=$link->prepare($sql);
$stmt->execute();
header('Location:/yr'.date('y').'/index.php');
noform:
     $link=null; $stmt=null;
?>