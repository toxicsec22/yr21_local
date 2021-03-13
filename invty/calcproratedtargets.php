<?php
// GET PRORATED TARGETS -- NOT USED SINCE THERE ARE NOW PERSONAL TARGETS
$thisyr=$currentyr; $firstofmonth=$thisyr.'-'.$txndate.'-01';

$sql0='CREATE TEMPORARY TABLE stltotal AS 
SELECT t.*, IF(DateofChange<\''.$firstofmonth.'\',\''.$firstofmonth.'\',IF(DateofChange>LAST_DAY(\''.$firstofmonth.'\'),0,DateofChange)) AS CalcFrom, 
			IF(ToDate<\''.$firstofmonth.'\',0,IF(ToDate>LAST_DAY(\''.$firstofmonth.'\'),LAST_DAY(\''.$firstofmonth.'\'),ToDate)) AS CalcTo,             
            DATEDIFF( IF(ToDate<\''.$firstofmonth.'\',0,IF(ToDate>LAST_DAY(\''.$firstofmonth.'\'),LAST_DAY(\''.$firstofmonth.'\'),ToDate)),
            IF(DateofChange<\''.$firstofmonth.'\',\''.$firstofmonth.'\', IF(DateofChange>LAST_DAY(\''.$firstofmonth.'\'),0,DateofChange))) as Days_Between
from (select t.*,              
             (select MAX(DateofChange) from `attend_1branchgroups` t3 where t.BranchNo = t3.BranchNo and t.DateofChange > t3.DateofChange) as FromDate2,
             IFNULL((select MIN(DateofChange) from `attend_1branchgroups` t2 where t.BranchNo = t2.BranchNo and t.DateofChange < t2.DateofChange), 
             (SELECT LAST_DAY(\''.$firstofmonth.'\'))) as ToDate from `attend_1branchgroups` t ) t ORDER BY BranchNo, DateofChange;';
// if($_SESSION['(ak0)']==1002){ echo $sql0.'<br><br>';}
$stmt=$link->prepare($sql0);$stmt->execute();

$sql1='CREATE TEMPORARY TABLE sumstltotal AS SELECT BranchNo,  TeamLeader, SUM(Days_Between) AS DaysCount, COUNT(BranchNo) AS CountofChange FROM stltotal GROUP BY BranchNo, TeamLeader HAVING SUM(Days_Between)>0;';// if($_SESSION['(ak0)']==1002){ echo $sql1.'<br><br>';}
$stmt=$link->prepare($sql1);$stmt->execute();


$sql2='CREATE TEMPORARY TABLE stldetail AS SELECT stl.*,TRUNCATE(IF(DaysCount<>(SELECT DAY(LAST_DAY(\''.$firstofmonth.'\')))-1,(`DaysCount`/(SELECT DAY(LAST_DAY(\''.$firstofmonth.'\'))))*(IF((datediff(\''.$firstofmonth.'\',b.Anniversary)<120 and MovedBranch=-1),0, yt.`'.$txndate.'`)),yt.`'.$txndate.'`),0) AS ProratedBranchTarget, TRUNCATE(yt.`'.$txndate.'`,0) AS TotalBranchTarget FROM sumstltotal stl 
JOIN `acctg_1yearsalestargets` yt ON stl.BranchNo=yt.BranchNo JOIN `1branches` b on b.BranchNo=stl.BranchNo; ';
// if($_SESSION['(ak0)']==1002){ echo $sql2.'<br><br>';}
$stmt=$link->prepare($sql2);$stmt->execute();


$sql3='CREATE TEMPORARY TABLE targettl AS
SELECT `TeamLeader`, SUM(ProratedBranchTarget) AS ProratedTarget FROM stldetail tl GROUP BY TeamLeader;';
// if($_SESSION['(ak0)']==1002){ echo $sql3.'<br><br>';}
$stmt=$link->prepare($sql3);$stmt->execute();

/*
$sql0='CREATE TEMPORARY TABLE tlratiostep1 AS
SELECT MONTH(`Date`) AS `Month`,`TeamLeader`,`BranchNo`,COUNT(`TeamLeader`) AS TLCount, (SELECT COUNT(`BranchNo`) FROM `acctg_2salemain` bsm WHERE bsm.BranchNo=asm.BranchNo AND MONTH(`Date`)='.$txndate.' GROUP BY `BranchNo`,MONTH(`Date`)) AS BranchCount FROM `acctg_2salemain` asm WHERE MONTH(`Date`)='.$txndate.' AND asm.BranchNo<95 GROUP BY `BranchNo`,MONTH(`Date`),`TeamLeader`;';
$stmt=$link->prepare($sql0);$stmt->execute();
$sql1='CREATE TEMPORARY TABLE targettl AS
SELECT `TeamLeader`,TRUNCATE(SUM((`TLCount`/`BranchCount`)*yt.`'.$txndate.'`),0) AS ProratedTarget FROM tlratiostep1 tl
JOIN `acctg_1yearsalestargets` yt ON tl.BranchNo=yt.BranchNo GROUP BY TeamLeader;';
$stmt=$link->prepare($sql1);$stmt->execute();
*/
?>