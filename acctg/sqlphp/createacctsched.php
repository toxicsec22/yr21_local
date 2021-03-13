<?php
include_once('../acctg/sqlphp/sqlalltxnsbasedonstatic.php');
$sql0='Create temporary table acctsched (
Date date not null,
ControlNo varchar(100) null, BECS varchar(1) null,
`Supplier/Customer/Branch` varchar(100) null,
Particulars varchar(300) null,
AccountID smallint(6) not null,
BranchNo smallint(6) not null, 
FromBudgetOf smallint(6) not null, 
Amount double null,
Entry varchar(2) not null,
w varchar(20) not null,
TxnID int(11) not null
)'.$sqlalltxns;
// echo $acctid.'<br>'.$sql0; break;
$stmt=$link->prepare($sql0);
$stmt->execute();

?>