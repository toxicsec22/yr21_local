<?php
if(isset($_POST['get_option']))
{
	$path=$_SERVER['DOCUMENT_ROOT'];
include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=!isset($link)?connect_db('2021_1rtc',0):$link;

 $spid = $_POST['get_option'];
 

$sqlbn = "SELECT Branch,SPID FROM 1_gamit.invty_1shipperprice sp JOIN 1branches b ON sp.BranchNo=b.BranchNo WHERE ShipperID='".$spid."' ORDER BY Branch";
$stmtbn=$link->query($sqlbn); $resbn=$stmtbn->fetchAll();

foreach($resbn AS $row)
 {
  echo "<option value='".$row['SPID']."'>".$row['Branch']."</option>";
 }
 exit;
}
?>