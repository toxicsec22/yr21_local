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
      Approval limit for HR : <br>&nbsp; not more than P 50,000<br>&nbsp; not more than max rate
</div>
<?php

    $sqlrates='CREATE TEMPORARY TABLE `payroll_ratesforapproval` AS
Select r.*, FullName, Position, Branch, if(DailyORMonthly=1,"Monthly","Daily") AS DorM, if(DailyORMonthly=1,(BasicRate),(BasicRate*26.08)) AS Basic, if(DailyORMonthly=1,(TaxShield),(TaxShield*26.08)) AS Allowance, 
if(DailyORMonthly=1,(DeMinimisRate),(DeMinimisRate*26.08)) AS DeMinimis,
(SELECT (SSEE+ECEE+MPFEE) AS Employee FROM `payroll_0ssstable` WHERE if(DailyORMonthly=1,((BasicRate+DeMinimisRate)),((BasicRate+DeMinimisRate)*26.08)) BETWEEN RangeMin AND RangeMax) AS CalcSSS, TRUNCATE(getContriEE(if(DailyORMonthly=1,(BasicRate),(BasicRate*26.08)),"phic"),2) AS CalcPHIC, e.Nickname AS EncodedBy
FROM `payroll_22rates` r JOIN `attend_30currentpositions` p ON r.IDNo=p.IDNo 
JOIN `1employees` e ON r.`EncodedByNo`=e.IDNo
    WHERE ApprovedByNo IS NULL OR ApprovedByNo=0'; 
  
    $stmtrates=$link->prepare($sqlrates); $stmtrates->execute(); //echo $sqlrates;
    

    $sql0='CREATE TEMPORARY TABLE effectivedate AS
            SELECT MAX(DateEffective) AS DateEffective, MinWageAreaID FROM `1_gamit`.`payroll_4wageorders` WHERE YEAR(DateEffective)<='.$currentyr.' GROUP BY MinWageAreaID;'; 
	
            $stmt0=$link->prepare($sql0); $stmt0->execute();

            $sql0='CREATE TEMPORARY TABLE NCRRate AS SELECT TotalMinWage AS NCRrate FROM `1_gamit`.`payroll_4wageorders` wo JOIN `effectivedate` ed ON ed.DateEffective=wo.DateEffective AND ed.MinWageAreaID=wo.MinWageAreaID WHERE wo.MinWageAreaID=1';
            $stmt0=$link->prepare($sql0); $stmt0->execute();
            $multiplier=1.1; //10% above provincial rate


			$sql1='CREATE TEMPORARY TABLE storesrate AS SELECT IDNo,TotalMinWage AS EffectiveMinWage,JobClassNo FROM `1_gamit`.`payroll_4wageorders` wo JOIN `effectivedate` ed ON ed.DateEffective=wo.DateEffective AND ed.MinWageAreaID=wo.MinWageAreaID LEFT JOIN `1_gamit`.`payroll_0regionsminwageareas` r ON r.MinWageAreaID=wo.MinWageAreaID LEFT JOIN 1branches b ON b.EffectiveMinWageAreaID=r.MinWageAreaID AND Pseudobranch IN (0,2) JOIN attend_30currentpositions cp ON cp.BranchNo=b.BranchNo WHERE Active="1" AND IDNo IN (SELECT IDNo FROM payroll_ratesforapproval)';
      // echo $sql1.'<br><br>';
      $stmt0=$link->prepare($sql1); $stmt0->execute();
      $increaserate=1.1; $steprate=1.1;

      
    $sql='SELECT TxnId,r.IDNo,DateofChange,Remarks,FullName,Position,Branch,DorM,IF(DailyORMonthly=0,

    (SELECT TRUNCATE(SalaryStructureDaily(IF((EffectiveMinWage*'.$multiplier.')>=(SELECT NCRRate FROM NCRRate),(SELECT NCRRate FROM NCRRate),(EffectiveMinWage*'.$multiplier.')),JobClassNo,'.$increaserate.','.$steprate.',5),2) FROM storesrate WHERE IDNo=r.IDNo)
    
    ,(SELECT TRUNCATE(MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100),2) FROM attend_0joblevels jl JOIN attend_0jobclass jc ON jc.JobClassNo=jl.JobClassNo JOIN attend_1positions p ON jl.JobLevelID=p.JobLevelID AND p.PositionID=(SELECT NewPositionID FROM attend_2changeofpositions WHERE IDNo=r.IDNo ORDER BY DateofChange LIMIT 1))) AS MaxRate,
        CONCAT(
          IF(((SELECT MaxRate)<Basic) AND DailyORMonthly=1,"<font color=\"red\">",""),
          FORMAT(Basic,2), IF(((SELECT MaxRate)<Basic) AND DailyORMonthly=1,"</font>","")
          ,IF(DailyORMonthly=1,IF((SELECT MaxRate)<Basic,CONCAT(" > ",(SELECT MaxRate)),""),"")) AS Basic, FORMAT(DeMinimis,2) AS DeMinimis, FORMAT(CalcSSS,2) AS CalcSSS, FORMAT(CalcPHIC,2) AS CalcPHIC, FORMAT(`PagIbig-EE`,2) AS `PagIbig-EE`,FORMAT(WTax,2) AS WTax,IF(DailyORMonthly=1,"",CONCAT(
            IF(((SELECT MaxRate)<BasicRate) AND DailyORMonthly=0,"<font color=\"red\">",""), 
            BasicRate,IF(((SELECT MaxRate)<BasicRate) AND DailyORMonthly=0,"</font>","")
            ,IF((SELECT MaxRate)<BasicRate,CONCAT(" > ",(SELECT MaxRate)),""))) AS DailyRate,
FORMAT(Basic+DeMinimis+Allowance,2) AS TotalMonthly, FORMAT(TaxDue(((Basic-CalcSSS-CalcPHIC-100)*12))/12,2) AS CalcTax, 
TxnId AS TxnID, EncodedBy,'.((allowedToOpen(7913,'1rtc'))?1:"
IF(DailyORMonthly=1,IF((SELECT MaxRate)<Basic,0,1),IF((SELECT MaxRate)<BasicRate,0,1))
").' AS showeditprocess 
FROM payroll_ratesforapproval r JOIN `1employees` e ON e.IDNo=r.IDNo ';

    // echo $sql; 

    $columnnames=array('IDNo','FullName','Position','Branch','DorM','DailyRate','TotalMonthly','Basic','DeMinimis','CalcSSS','CalcPHIC','PagIbig-EE','WTax','CalcTax','EncodedBy');
    $title='Rates for Approval';
    if (allowedToOpen(7912,'1rtc')) {
    $delprocess='praddentry.php?w=DeleteRate&TxnID=';
    $editprocess='praddentry.php?w=ApproveRate&TxnID='; $editprocesslabel='Approve';
    }
    include '../backendphp/layout/displayastablenosort.php';


    ?>