<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;

if (!allowedToOpen(array(7911,7912),'1rtc')) {   echo 'No permission'; exit;}
include_once('../switchboard/contents.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

?>
<div style='background-color: #e6e6e6;
  width: 1100px;
  border: 2px solid grey;
  padding: 25px;
  margin: 25px;'>
    <b>Note:</b><br/><br/>
      Approval limit for HR : P 50,000
</div>
<?php

    $sqlrates='CREATE TEMPORARY TABLE `payroll_ratesforapproval` AS
Select r.*, FullName, Position, Branch, if(DailyORMonthly=1,"Monthly","Daily") AS DorM, if(DailyORMonthly=1,(BasicRate),(BasicRate*26.08)) AS Basic, if(DailyORMonthly=1,(TaxShield),(TaxShield*26.08)) AS Allowance, 
if(DailyORMonthly=1,(DeMinimisRate),(DeMinimisRate*26.08)) AS DeMinimis,
(SELECT (SSEE+ECEE+MPFEE) AS Employee FROM `payroll_0ssstable` WHERE if(DailyORMonthly=1,((BasicRate+DeMinimisRate)),((BasicRate+DeMinimisRate)*26.08)) BETWEEN RangeMin AND RangeMax) AS CalcSSS, TRUNCATE(getContriEE(if(DailyORMonthly=1,(BasicRate),(BasicRate*26.08)),"phic"),2) AS CalcPHIC, e.Nickname AS EncodedBy
FROM `payroll_22rates` r JOIN `attend_30currentpositions` p ON r.IDNo=p.IDNo 
JOIN `1employees` e ON r.`EncodedByNo`=e.IDNo
    WHERE ApprovedByNo IS NULL OR ApprovedByNo=0'; //echo $sqlrates;
    $stmtrates=$link->prepare($sqlrates); $stmtrates->execute(); //echo $sqlrates;
    
    $sql='SELECT TxnId,r.IDNo,DateofChange,Remarks,FullName,Position,Branch,DorM,
        FORMAT(Basic,2) AS Basic, FORMAT(DeMinimis,2) AS DeMinimis, FORMAT(CalcSSS,2) AS CalcSSS, FORMAT(CalcPHIC,2) AS CalcPHIC, FORMAT(`PagIbig-EE`,2) AS `PagIbig-EE`,FORMAT(WTax,2) AS WTax,IF(DailyORMonthly=1,"",BasicRate) AS DailyRate,
FORMAT(Basic+DeMinimis+Allowance,2) AS TotalMonthly, FORMAT(TaxDue(((Basic-CalcSSS-CalcPHIC-100)*12))/12,2) AS CalcTax, 
TxnId AS TxnID, EncodedBy 
FROM payroll_ratesforapproval r JOIN `1employees` e ON e.IDNo=r.IDNo ';
    
    $columnnames=array('IDNo','FullName','Position','Branch','DorM','DailyRate','TotalMonthly','Basic','DeMinimis','CalcSSS','CalcPHIC','PagIbig-EE','WTax','CalcTax','EncodedBy');
    $title='Rates for Approval';
    if (allowedToOpen(7912,'1rtc')) {
    $delprocess='praddentry.php?w=DeleteRate&TxnID=';
    $addlprocess='praddentry.php?w=ApproveRate&TxnID='; $addlprocesslabel='Approve';
    }
    include '../backendphp/layout/displayastablenosort.php';


    ?>