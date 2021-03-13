<?php
$filter=(!isset($filter)?'':$filter);
$orderby=(!isset($orderby)?' ORDER BY Month, AccountID, Branch':$orderby);
$sql0='CREATE TEMPORARY TABLE `compare` AS
SELECT cm.*, (SELECT IFNULL(SUM(Amount),0) FROM closing_2closesub WHERE CloseID=cm.CloseID) AS Accounted, ABS(EndBal-(SELECT IFNULL(SUM(Amount),0) FROM closing_2closesub WHERE CloseID=cm.CloseID)) AS DiffValue FROM closing_2closemain cm '.$filter;
//if($_SESSION['(ak0)']==1002){echo $sql0;}
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql='SELECT cm.*, ShortAcctID AS Account, Branch, CONCAT(e.Nickname," ",e.Surname) AS EncodedBy, FORMAT(EndBal,2) AS DataEndBalance, FORMAT(Accounted,2) AS Accounted, FORMAT((EndBal-Accounted),2) AS Difference FROM `compare` cm LEFT JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=cm.AccountID LEFT JOIN `1employees` e ON e.IDNo=cm.EncodedByNo  JOIN `1branches` b ON b.BranchNo=cm.BranchNo  HAVING (EndBal-Accounted)<>0 '.$orderby;
?>