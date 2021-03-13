<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 

if (!allowedToOpen(619,'1rtc')){ echo 'No permission'; exit;}
    
    $title='Lates Per Month';

/* Rank 6 - Managers - (plus SNP and JVN) are exempt from tardiness.
 * Rank 5 - Dept Heads - employees are given until 8:30.
 */
$sql='SELECT * from `attend_62latescount`';

$orderby='Branch, IDNo';    

$columnnames=array('IDNo', 'Nickname', 'FirstName','SurName', 'Position','Branch', 'LatesPerMonth', 'TotalMinutesLate', 'Month');
    $showbranches=false;
     include('../backendphp/layout/displayastable.php');
?>