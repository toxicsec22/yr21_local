<?php
if(isset($_POST['get_option']))
{
	$path=$_SERVER['DOCUMENT_ROOT'];
include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

 $cpid = $_POST['get_option'];
 

$sqlbarangayortown = "SELECT BarangayOrTown,BTID FROM 1_gamit.0barangayortown WHERE CPID='".$cpid."' ORDER BY BarangayOrTown";
$stmtbarangayortown=$link->query($sqlbarangayortown); $resbarangayortown=$stmtbarangayortown->fetchAll();

foreach($resbarangayortown AS $row)
 {
  echo "<option value='".$row['BTID']."'>".$row['BarangayOrTown']."</option>";
 }
 exit;
}
?>