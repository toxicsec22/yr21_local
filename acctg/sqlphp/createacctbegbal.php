<?php
include_once('../acctg/sqlphp/sqlalltxnsbasedonstatic.php');

$sql1='Create temporary table acctbegbal (
ControlNo varchar(100) null,
AccountID smallint(6) not null,
BranchNo smallint(6) not null,
FromBudgetOf smallint(6) not null, 
SumofAmount double null,
Entry varchar(2) not null
)'.$sqllastmonth;
//if($_SESSION['(ak0)']==1002) { echo $acctid.'<br>closedmonth='.$closedmonth.'<br>monthfrom='.$monthfrom.'<br>monthto='.$monthto.'<br>'.$sql1.'<br><hr><br>'.$sql0; }
$stmt=$link->prepare($sql1);
$stmt->execute();
?>