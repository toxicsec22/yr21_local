<?php
if(!isset($_GET['PerBranch'])){
    $branchcondition=''; 
    $salescondition='(PaymentType<>2 and sm.txntype<>3 AND Month(sm.`Date`)='.$txndate.') group by sm.BranchNo ';
    $checkdepcondition=' AND MONTH(`Cleared`)='.$txndate;
    $freightcondition=' cm.Date>=\''.$currentyr.'-'.($txndate==1?1:$txndate-1).'-25\' ';
    $cosalescondition=' AND MONTH(`Cleared`)='.$txndate. ' GROUP BY `ItemsFromBranchNo`;';
    $cleareddepstep1cond=' MONTH(`Cleared`)='.$txndate.' GROUP BY `aa`.`TeamLeader`,cs.BranchNo';
    $cleareddepstep1cond2=' AND MONTH(Date)='.$txndate.' GROUP BY `TeamLeader`, sm.BranchNo';
    $cleareddepstep1cond3='AND MONTH(orm.`Date`)='.$txndate.' GROUP BY `TeamLeader`, ord.BranchNo';
    $cleareddepstep1cond4=' AND MONTH(bm.Date)='.$txndate.'  GROUP BY `TeamLeader`, bs.BranchNo ';
} else { 
    $branchno=$_SESSION['bnum'];
    $branchcondition=' AND BranchNo='.$branchno;
    $salescondition='(PaymentType<>2 and sm.txntype<>3 AND sm.BranchNo='.$branchno.') group by Month(sm.`Date`) ';
    $checkdepcondition=' AND s.`BranchNo`='.$branchno;
    $freightcondition=' cs.BranchNo='.$branchno;
    $cosalescondition=' AND sm.`BranchNo`='.$branchno. ' GROUP BY `ItemsFromBranchNo`,MONTH(Cleared);';
    $cleareddepstep1cond='  cs.`BranchNo`='.$branchno.' GROUP BY MONTH(`Cleared`),cs.BranchNo ';
    $cleareddepstep1cond2='  AND sm.`BranchNo`='.$branchno.' GROUP BY MONTH(`Date`),sm.BranchNo ';
    $cleareddepstep1cond3='  AND ord.`BranchNo`='.$branchno.' GROUP BY MONTH(orm.`Date`),ord.BranchNo ';
    $cleareddepstep1cond4='  AND bs.`BranchNo`='.$branchno.' GROUP BY MONTH(bm.`Date`),bs.BranchNo ';
}

// if(!isset($link)) { }
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
// Cash Sales and Returns - Branch and overall
$sql0='CREATE TEMPORARY TABLE `targets0cashsalesreturns` as
        SELECT Month(sm.`Date`) as ForMonth, Round((Sum(`Qty` * `UnitPrice`)-SUM(IF(ISNULL(fc.Amount),0,fc.Amount))),0) as CashSalesLessReturns, sm.BranchNo
    from
        (`invty_2sale` sm
        join `invty_2salesub` ss ON ((`sm`.`TxnID` = `ss`.`TxnID`)))  
        LEFT JOIN `approvals_2freightclients` fc ON (fc.ForInvoiceNo=sm.SaleNo AND fc.BranchNo=sm.BranchNo AND fc.txntype=sm.txntype AND PriceFreightInclusive=1)
WHERE sm.BranchNo<95 AND '.$salescondition;
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

// Cash Sales and Returns - STL 
$sql0='CREATE TEMPORARY TABLE `targets0cashsalesreturnstl` as
    SELECT MONTH(sm.`Date`) AS ForMonth, sm.`TeamLeader`,
        Round((Sum(`Qty` * `UnitPrice`)-SUM(IF(ISNULL(fc.Amount),0,fc.Amount))),0) AS CashSalesLessReturns
    FROM
        `invty_2sale` sm JOIN `invty_2salesub` ss ON `sm`.`TxnID` = `ss`.`TxnID`
        LEFT JOIN `approvals_2freightclients` fc ON (fc.ForInvoiceNo=sm.SaleNo AND fc.BranchNo=sm.BranchNo AND fc.txntype=sm.txntype AND PriceFreightInclusive=1)
WHERE  PaymentType<>2 AND sm.txntype<>3 AND Month(sm.`Date`)='.$txndate.' AND sm.BranchNo<95 AND (sm.`TeamLeader` IS NOT NULL AND sm.`TeamLeader`<>0) 

group by TeamLeader;
';  //AND (sm.TLTS<=(SELECT IFNULL(`Timestamp`,Now()) FROM acctg_6targetscores WHERE BranchNo=sm.`TeamLeader` AND MonthNo='.$txndate.' AND DisplayType<>5))  
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

// Cleared orcash and check deposits for the month
$sql0='CREATE TEMPORARY TABLE `CheckNos` as Select `m`.`Cleared`, `s`.`ClientNo`, `s`.`CRNo` AS DepCRNo,`s`.`BranchNo`, `s`.`CheckNo`, `s`.`CheckDraweeBank`, `s`.`Type` from `acctg_2depositsub` `s` join `acctg_2depositmain` `m` ON `m`.`TxnID` = `s`.`TxnID` WHERE (`m`.`Cleared` is not null) and `s`.`CRNo` is not null and `s`.`CRNo` not like \'\' and (`s`.`Type` IN (1,2)) and (`m`.`DebitAccountID` not in (805, 932 , 200, 202)) and (`s`.`ForChargeInvNo` IS NULL)  AND s.ClientNo>9999 AND BranchNo<95 '.$checkdepcondition. ' GROUP BY `s`.`CheckNo`, s.CRNo,`s`.`BranchNo`;';
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

//Invoices paid by the cleared checks from collection receipts

$teamleader=' IF(invs.TLTS<=(SELECT IFNULL(`Timestamp`,Now()) FROM acctg_6targetscores WHERE BranchNo=invs.`TeamLeader` AND MonthNo='.$txndate.' AND DisplayType<>5),0,IFNULL(invs.TeamLeader,0)) ';

$sql0='CREATE TEMPORARY TABLE `InvoicesPaid` AS
    SELECT `Cleared`,`chk`.`DepCRNo`,`cm`.`ClientNo`, `cm`.`CheckNo`, `cm`.`CheckBank`,`cs`.* , IFNULL(invs.TeamLeader,0) AS TeamLeader
        from `acctg_2collectmain` `cm` join `acctg_2collectsub` `cs` ON `cm`.`TxnID` = `cs`.`TxnID` 
	JOIN `CheckNos` chk ON `chk`.`DepCRNo`=CONCAT("C-",cm.BranchSeriesNo,"-",`cm`.`CollectNo`) AND cm.`CheckBank`=`chk`.`CheckDraweeBank` AND `chk`.`CheckNo`=`cm`.`CheckNo` AND chk.BranchNo=`cs`.`BranchNo` AND `cm`.`ClientNo`=`chk`.`ClientNo`
        LEFT JOIN `acctg_30uniar` invs ON invs.Particulars=cs.ForChargeInvNo AND invs.BranchNo=cs.BranchNo
        WHERE chk.Type=2 AND ForChargeInvNo NOT LIKE \'%freight%\' AND cs.BranchNo<95 AND ((CreditAccountID IN (200,202) AND cm.DebitAccountID=201))';
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();


//Invoices paid by the cleared cash collections 
$sql0='INSERT INTO `InvoicesPaid`
    SELECT `Cleared`,`chk`.`DepCRNo`,`cm`.`ClientNo`, `cm`.`CheckNo`, `cm`.`CheckBank`, `cs`.* , invs.TeamLeader
    from `acctg_2collectmain` `cm` join `acctg_2collectsub` `cs` ON `cm`.`TxnID` = `cs`.`TxnID` 
    JOIN `CheckNos` chk ON `chk`.`DepCRNo`=CONCAT("C-",cm.BranchSeriesNo,"-",`cm`.`CollectNo`) AND chk.BranchNo=`cs`.`BranchNo` AND `cm`.`ClientNo`=`chk`.`ClientNo`
    LEFT JOIN `acctg_30uniar` invs ON invs.Particulars=cs.ForChargeInvNo AND invs.BranchNo=cs.BranchNo
    WHERE CreditAccountID IN (200,202)  AND chk.Type=1 AND ForChargeInvNo NOT LIKE \'%freight%\' AND cm.DebitAccountID=100 AND '.$freightcondition.';';
  
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

//Invoices paid directly into deposits
$sql0='INSERT INTO `InvoicesPaid`
    SELECT `Cleared`, `DepositNo` as `CRNo`, s.`ClientNo`, s.`CheckNo`, `CheckDraweeBank`,TxnSubId, m.TxnID, s.ForChargeInvNo, DepDetails AS OtherORDetails,s.BranchNo,CreditAccountID, 
    Amount,s.TimeStamp,s.EncodedByNo, TeamLeader
    FROM `acctg_2depositmain` `m` JOIN `acctg_2depositsub` `s` ON ((`m`.`TxnID` = `s`.`TxnID`))
    LEFT JOIN `acctg_30uniar` invs ON invs.Particulars=s.ForChargeInvNo AND invs.BranchNo=s.BranchNo
    WHERE CreditAccountID IN (200,202) AND (s.CheckNo IS NULL or s.CheckNo LIKE \'\') AND (`m`.`DebitAccountID` not in (805, 932 , 200, 202, 204)) AND s.ClientNo>9999 
    AND s.BranchNo<95
    '.$checkdepcondition. ' AND (`m`.`Cleared` IS NOT NULL) AND (`s`.`ForChargeInvNo` IS NOT NULL) AND s.ForChargeInvNo NOT LIKE \'%freight%\';';
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

//Adjust sales that crossed companies
$sql0='CREATE TEMPORARY TABLE `AcrossCompaniesPaidItems` AS
    SELECT MONTH(cs.Cleared) AS `ForMONTH`, `ItemsFromBranchNo` AS BranchNo, sm.TeamLeader, 
IFNULL(sum((CASE WHEN (`cs`.`CreditAccountID` IN (200,202)) THEN IFNULL(`cs`.`Amount`, 0) END)), 0) AS `ARCollected`
 FROM `invty_4salesacrosscompanies` ac
        LEFT JOIN `invty_2sale` sm ON sm.SaleNo=ac.SaleNo AND sm.BranchNo=ac.InvoiceFromBranchNo
        JOIN `InvoicesPaid` `cs` ON cs.ForChargeInvNo=ac.SaleNo AND cs.ClientNo=sm.ClientNo 
        WHERE sm.BranchNo<95 AND sm.txntype=2 '.$cosalescondition;
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

$sql0='CREATE TEMPORARY TABLE `AcrossCompaniesPaidInvoice` AS
    SELECT MONTH(Cleared) AS `ForMONTH`, `InvoiceFromBranchNo` AS BranchNo,  sm.TeamLeader, 
IFNULL(sum((CASE WHEN (`cs`.`CreditAccountID` IN (200,202)) THEN IFNULL(`cs`.`Amount`*-1, 0) END)), 0) AS `ARCollected`
 FROM `invty_4salesacrosscompanies` ac
        LEFT JOIN `invty_2sale` sm ON sm.SaleNo=ac.SaleNo AND sm.BranchNo=ac.InvoiceFromBranchNo
        JOIN `InvoicesPaid` cs ON cs.ForChargeInvNo=ac.SaleNo AND cs.ClientNo=sm.ClientNo 
        WHERE sm.BranchNo<95 AND sm.txntype=2 '.$cosalescondition;
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

// Distribute cleared deposits & collections per branch, team leader (all collected & cleared are counted, regardless if within terms)
//BOUNCED NOT COUNTED
$sql0='CREATE TEMPORARY TABLE `targets0cleareddepositsstep1` AS
SELECT MONTH(Cleared) AS `ForMONTH`, cs.BranchNo,
        `aa`.`TeamLeader` AS `TeamLeader`,
        IFNULL(sum((CASE WHEN (`cs`.`CreditAccountID` IN (200,202)) THEN IFNULL(`cs`.`Amount`, 0) END)), 0) AS `ARCollected`
    FROM `InvoicesPaid` `cs` 
	JOIN `acctg_30uniar` aa on (aa.`Particulars`=cs.ForChargeInvNo AND aa.`BranchNo`=cs.BranchNo AND aa.ClientNo=`cs`.`ClientNo`) 
    LEFT JOIN `acctg_1clientsperbranch` `cb` ON `cs`.`BranchNo` = `cb`.`BranchNo` AND (`aa`.`ClientNo` = `cb`.`ClientNo`)
    WHERE '.$cleareddepstep1cond.'
    UNION ALL SELECT `ForMONTH`, BranchNo, `TeamLeader`, `ARCollected` FROM `AcrossCompaniesPaidItems`
    UNION ALL SELECT `ForMONTH`, BranchNo, `TeamLeader`, `ARCollected` FROM `AcrossCompaniesPaidInvoice`
    UNION ALL SELECT MONTH(Date), sm.BranchNo, `sm`.`TeamLeader`, Sum(Amount)*-1 AS OP 
    FROM `acctg_2salesub` ss JOIN `acctg_2salemain` sm ON sm.TxnID=ss.TxnID WHERE DebitAccountID IN (405) '.$cleareddepstep1cond2.'
    UNION ALL SELECT MONTH(orm.`Date`), ord.BranchNo, sm.TeamLeader AS `TeamLeader`, Sum(Amount)*-1 AS ORDeduct 
    FROM `acctg_2collectsubdeduct` ord JOIN `acctg_2collectmain` orm ON orm.TxnID=ord.TxnID JOIN `acctg_2salemain` sm ON sm.`Date`=orm.`Date` AND sm.BranchNo=ord.BranchNo 
    WHERE ord.DebitAccountID<>160 '.$cleareddepstep1cond3;    //removed aa.ClientNo>10000 AND aa.ClientNo<>10004 
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

$sql0='CREATE TEMPORARY TABLE `targets0cleareddeposits` AS
SELECT 
        `step1`.`ForMonth` AS `ForMonth`,
        `step1`.`TeamLeader` AS `TeamLeader`, `BranchNo`, 
        ROUND(sum(`step1`.`ARCollected`),0) AS `ClearedAR`
    from
        `targets0cleareddepositsstep1` step1
    group by `step1`.`ForMonth` , `step1`.`TeamLeader`, `BranchNo`;
';
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

//Overdue AR for the Month; In 34foraragingseparateoldfortargets, 10 days were added after due date to account for collection/clearing.
// these are unpaid invoices
// AROld will still be part of collections, but not part of OverdueAR.
if(!isset($_GET['PerBranch'])){ 
$sql0='CREATE TEMPORARY TABLE `targets0overduearstep1` as
select 
        `b`.`BranchNo` AS `BranchNo`, `aa`.`TeamLeader`,
        sum((case when (`aa`.`DebitAccountID` IN (200)) then `aa`.`ARAmount` end)) AS `OverdueAR`
    from
        (`1branches` `b`
        left join `acctg_34foraragingseparateoldfortargets` `aa` ON ((`b`.`BranchNo` = `aa`.`BranchNum`)))
    where
        ((`aa`.`ClientNum` > 9999) and (`aa`.`Due` <= (select last_day("'.$currentyr.'-'.$txndate.'-1")))) AND b.BranchNo<95
    group by `b`.`BranchNo`, `aa`.`TeamLeader`
UNION    
    SELECT `ItemsFromBranchNo`, sm.TeamLeader, SUM(`bpr`.`InvBalance`) AS `ARAmount` FROM `invty_4salesacrosscompanies` ac
    LEFT JOIN `invty_2sale` sm ON sm.SaleNo=ac.SaleNo AND sm.BranchNo=ac.InvoiceFromBranchNo
    JOIN `acctg_33qrybalperrecpt` `bpr` ON `bpr`.`ClientNo`= sm.ClientNo AND bpr.Particulars=sm.SaleNo
     LEFT JOIN `acctg_1clientsperbranch` `cb` ON `bpr`.`BranchNo` = `cb`.`BranchNo`
            AND `bpr`.`ClientNo` = `cb`.`ClientNo`
    WHERE (`bpr`.`InvBalance` <> 0) AND sm.txntype=2 
    AND (`sm`.`Date` + INTERVAL IFNULL(`cb`.`Terms`, 0) DAY + INTERVAL 10 DAY)<= (select last_day("'.$currentyr.'-'.$txndate.'-1"))  AND sm.BranchNo<95
        GROUP BY `ItemsFromBranchNo`, sm.TeamLeader
    
    UNION
    SELECT `InvoiceFromBranchNo`, sm.TeamLeader, SUM(`bpr`.`InvBalance`)*-1 AS `ARAmount` FROM `invty_4salesacrosscompanies` ac
    LEFT JOIN `invty_2sale` sm ON sm.SaleNo=ac.SaleNo AND sm.BranchNo=ac.InvoiceFromBranchNo
    JOIN `acctg_33qrybalperrecpt` `bpr` ON `bpr`.`ClientNo`= sm.ClientNo AND bpr.Particulars=sm.SaleNo
    LEFT JOIN `acctg_1clientsperbranch` `cb` ON `bpr`.`BranchNo` = `cb`.`BranchNo`
            AND `bpr`.`ClientNo` = `cb`.`ClientNo`
    WHERE (`bpr`.`InvBalance` <> 0) AND sm.txntype=2 AND (`sm`.`Date` + INTERVAL IFNULL(`cb`.`Terms`, 0) DAY + INTERVAL 10 DAY)<= (select last_day("'.$currentyr.'-'.$txndate.'-1")) 
         AND sm.BranchNo<95
    GROUP BY `ItemsFromBranchNo`, sm.TeamLeader'; 

//Uncleared pdc's -- counted only if pdc is not yet deposited and sale is past due

$sql1='CREATE TEMPORARY TABLE unpdasofcutoff as 
 SELECT CRNo FROM `acctg_undepositedclientpdcs`  pdc JOIN `1clients` c ON c.ClientNo=pdc.ClientNo
WHERE (`SaleDate` + INTERVAL IFNULL(`Terms`, 0) DAY + INTERVAL 10 DAY)<= (select last_day("'.$currentyr.'-'.$txndate.'-1")) 
UNION ALL
SELECT up.CRNo FROM `acctg_31unionpdcs` `up`
        JOIN `acctg_2depositsub` `ds` ON `up`.`CRNo` = `ds`.`CRNo` JOIN `1clients` c ON c.ClientNo=up.ClientNo
        JOIN `acctg_2depositmain` `dm` ON dm.TxnID=ds.TxnID
WHERE dm.Cleared>(`SaleDate` + INTERVAL IFNULL(`Terms`, 0) DAY + INTERVAL 10 DAY)
 AND YEAR(`SaleDate` + INTERVAL IFNULL(`Terms`, 0) DAY + INTERVAL 10 DAY)='.$currentyr.'
 AND MONTH(`SaleDate` + INTERVAL IFNULL(`Terms`, 0) DAY + INTERVAL 10 DAY)='.$txndate.';';
$stmt=$link->prepare($sql1);$stmt->execute();

$sql1='CREATE TEMPORARY TABLE `UndepositedPDC` AS
    SELECT cs.BranchNo, TeamLeader, IFNULL(SUM(SumOfAmount),0) AS `UndepPDC` FROM `acctg_31unionpdcs` cs 
LEFT JOIN `acctg_30uniar` aa ON aa.`Particulars`=cs.ForChargeInvNo AND aa.ClientNo=cs.ClientNo AND aa.BranchNo=cs.BranchNo AND cs.ClientNo>9999
WHERE cs.CRNo IN 
(SELECT CRNo FROM unpdasofcutoff) AND cs.BranchNo<95 GROUP BY cs.BranchNo, TeamLeader;';

} else {
    $sql0='CREATE TEMPORARY TABLE `targets0overduearstep1` as
select MONTH(`aa`.`Due`) AS `Month`, `b`.`BranchNo` AS `BranchNo`, 
        sum((case when (`aa`.`DebitAccountID` IN (200)) then `aa`.`ARAmount` end)) AS `OverdueAR`
    from
        (`1branches` `b` left join `acctg_34foraragingseparateoldfortargets` `aa` ON ((`b`.`BranchNo` = `aa`.`BranchNum`)))
    where
        (`aa`.`ClientNum` > 9999) AND b.BranchNo='.$branchno.' group by `b`.`BranchNo`, MONTH(`aa`.`Due`)
UNION    
    SELECT MONTH(`sm`.`Date` + INTERVAL IFNULL(`cb`.`Terms`, 0) DAY + INTERVAL 10 DAY) AS `Month`,`ItemsFromBranchNo`, 
    SUM(`bpr`.`InvBalance`) AS `ARAmount` FROM `invty_4salesacrosscompanies` ac
    LEFT JOIN `invty_2sale` sm ON sm.SaleNo=ac.SaleNo AND sm.BranchNo=ac.InvoiceFromBranchNo
    JOIN `acctg_33qrybalperrecpt` `bpr` ON `bpr`.`ClientNo`= sm.ClientNo AND bpr.Particulars=sm.SaleNo
     LEFT JOIN `acctg_1clientsperbranch` `cb` ON `bpr`.`BranchNo` = `cb`.`BranchNo`
            AND `bpr`.`ClientNo` = `cb`.`ClientNo`
    WHERE (`bpr`.`InvBalance` <> 0) AND sm.txntype=2  AND   sm.BranchNo='.$branchno.'
        GROUP BY `ItemsFromBranchNo`, MONTH(`sm`.`Date` + INTERVAL IFNULL(`cb`.`Terms`, 0) DAY + INTERVAL 10 DAY)    
    UNION
    SELECT MONTH(`sm`.`Date` + INTERVAL IFNULL(`cb`.`Terms`, 0) DAY + INTERVAL 10 DAY) AS `Month`,`InvoiceFromBranchNo`, 
    SUM(`bpr`.`InvBalance`)*-1 AS `ARAmount` FROM `invty_4salesacrosscompanies` ac
    LEFT JOIN `invty_2sale` sm ON sm.SaleNo=ac.SaleNo AND sm.BranchNo=ac.InvoiceFromBranchNo
    JOIN `acctg_33qrybalperrecpt` `bpr` ON `bpr`.`ClientNo`= sm.ClientNo AND bpr.Particulars=sm.SaleNo
    LEFT JOIN `acctg_1clientsperbranch` `cb` ON `bpr`.`BranchNo` = `cb`.`BranchNo`
            AND `bpr`.`ClientNo` = `cb`.`ClientNo`
    WHERE (`bpr`.`InvBalance` <> 0) AND sm.txntype=2 AND sm.BranchNo='.$branchno.'
    GROUP BY `ItemsFromBranchNo`, MONTH(`sm`.`Date` + INTERVAL IFNULL(`cb`.`Terms`, 0) DAY + INTERVAL 10 DAY)   ';
    
    $sql1='CREATE TEMPORARY TABLE `UndepositedPDC` AS
    SELECT MONTH(cs.`SaleDate` + INTERVAL IFNULL(`Terms`, 0) DAY + INTERVAL 10 DAY) AS `Month`, cs.BranchNo, IFNULL(SUM(SumOfAmount),0) AS `UndepPDC` FROM `acctg_31unionpdcs` cs 
LEFT JOIN `acctg_30uniar` aa ON aa.`Particulars`=cs.ForChargeInvNo AND aa.ClientNo=cs.ClientNo AND aa.BranchNo=cs.BranchNo AND cs.ClientNo>9999
JOIN `acctg_undepositedclientpdcs`  pdc ON cs.CRNo=pdc.CRNo AND cs.PDCBank=pdc.PDCBank AND pdc.BranchNo=cs.BranchNo JOIN `1clients` c ON c.ClientNo=pdc.ClientNo
WHERE cs.BranchNo<95 AND cs.BranchNo='.$branchno.' GROUP BY cs.BranchNo, MONTH(cs.`SaleDate` + INTERVAL IFNULL(`Terms`, 0) DAY + INTERVAL 10 DAY);';

}
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

//if($_SESSION['(ak0)']==1002) { echo $sql1.'<br><br>'; }
$stmt=$link->prepare($sql1);$stmt->execute();

if(!isset($_GET['PerBranch'])){ 

$sql0='CREATE TEMPORARY TABLE ClearedAfter AS SELECT CONCAT(ds.CRNo,ds.BranchNo) AS PRBranchNo FROM `acctg_2depositmain` dm JOIN `acctg_2depositsub` ds ON dm.TxnID=ds.TxnID 
WHERE MONTH(`Cleared`)>'.$txndate.'  AND `Type`<>0 AND `Cleared` IS NOT NULL ';
$stmt=$link->prepare($sql0);$stmt->execute();
$sql0='INSERT INTO `UndepositedPDC` SELECT cs.BranchNo, TeamLeader, IFNULL(SUM(Amount),0) AS `UndepPDC` FROM `acctg_2collectmain` cm JOIN `acctg_2collectsub` cs ON cm.TxnID=cs.TxnID
JOIN `acctg_30uniar` aa ON aa.`Particulars`=cs.ForChargeInvNo AND aa.ClientNo=cm.ClientNo AND aa.BranchNo=cs.BranchNo AND cm.ClientNo>9999
JOIN `1clients` c ON c.ClientNo=cm.ClientNo
WHERE CONCAT("C-",cm.BranchSeriesNo,"-",`cm`.`CollectNo`,cs.BranchNo) IN 
(SELECT PRBranchNo FROM ClearedAfter) 
AND CONCAT("C-",cm.BranchSeriesNo,"-",`cm`.`CollectNo`) NOT IN (SELECT CRNo FROM `acctg_undepositedclientpdcs`) 
AND (aa.`Date` + INTERVAL IFNULL(`Terms`, 0) DAY + INTERVAL 10 DAY)<= (select last_day("'.$currentyr.'-'.$txndate.'-1"))  AND cs.BranchNo<95
GROUP BY cs.BranchNo, TeamLeader;';

//Total overdue AR
$sql1='CREATE TEMPORARY TABLE `targets0overduear` AS SELECT `BranchNo`,`TeamLeader`, Sum(IFNULL(`OverdueAR`,0)) AS `OverdueAR`  FROM `targets0overduearstep1` group by `BranchNo`,`TeamLeader`;
';
} else { 

$sql0='INSERT INTO `UndepositedPDC`
 SELECT MONTH(aa.`Date` + INTERVAL IFNULL(`Terms`, 0) DAY + INTERVAL 10 DAY) AS `Month`,cs.BranchNo,  IFNULL(SUM(cs.Amount),0) AS `UndepPDC` FROM `acctg_2collectmain` cm JOIN `acctg_2collectsub` cs ON cm.TxnID=cs.TxnID
JOIN `acctg_30uniar` aa ON aa.`Particulars`=cs.ForChargeInvNo AND aa.ClientNo=cm.ClientNo AND aa.BranchNo=cs.BranchNo AND cm.ClientNo>9999
JOIN `1clients` c ON c.ClientNo=cm.ClientNo
 JOIN `acctg_2depositsub` ds ON ds.CheckNo=cm.CheckNo AND ds.CRNo=cm.CollectNo AND ds.CheckDraweeBank=cm.CheckBank JOIN `acctg_2depositmain` dm ON dm.TxnID=ds.TxnID 
 WHERE CONCAT(cm.CollectNo,cs.BranchNo) NOT IN (SELECT CONCAT(CRNo,BranchNo) FROM `acctg_undepositedclientpdcs`) AND  ds.`Type`<>0 AND dm.`Cleared` IS NOT NULL 
 AND MONTH(dm.`Cleared`)>MONTH(aa.`Date` + INTERVAL IFNULL(`Terms`, 0) DAY + INTERVAL 10 DAY)
 AND cs.BranchNo='.$branchno.'
 GROUP BY MONTH(aa.`Date` + INTERVAL IFNULL(`Terms`, 0) DAY + INTERVAL 10 DAY), cs.BranchNo;';

//Total overdue AR
$sql1='CREATE TEMPORARY TABLE `targets0overduear` AS SELECT `Month`,`BranchNo`, Sum(IFNULL(`OverdueAR`,0)) AS `OverdueAR`  FROM `targets0overduearstep1` group by `BranchNo`,`Month`;
';
}
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

//if($_SESSION['(ak0)']==1002) { echo $sql1.'<br><br>'; }
$stmt=$link->prepare($sql1);$stmt->execute();

if(!isset($_GET['PerBranch'])){ 
    
  //  include_once 'calcproratedtargets.php'; -- NOT USED SINCE THERE ARE NOW PERSONAL TARGETS
/*
$sql0='CREATE TEMPORARY TABLE `targetsforcalctl` AS
SELECT '.$txndate.' AS `ForMonth`, `b`.`TeamLeader`, IFNULL(`CashSales`,0) AS `CashSales`, IFNULL(`ClearedCollections`,0) AS `ClearedCollections`
FROM
        (SELECT TeamLeader FROM acctg_2salemain WHERE MONTH(`Date`)='.$txndate.' GROUP BY TeamLeader) b
LEFT JOIN (SELECT `TeamLeader`, Sum(`CashSalesLessReturns`) AS `CashSales`FROM `targets0cashsalesreturnstl` WHERE TeamLeader<>0 group by `TeamLeader`) `tcr` ON `tcr`.`TeamLeader`=`b`.`TeamLeader`
LEFT JOIN (SELECT `TeamLeader`, Sum(`Amount`) AS `ClearedCollections` FROM `InvoicesPaid` WHERE TeamLeader<>0 group by `TeamLeader`) `tcd` ON `tcd`.`TeamLeader`=`b`.`TeamLeader` 
WHERE b.TeamLeader<>0
GROUP BY  `b`.`TeamLeader`'; */
$sql0='CREATE TEMPORARY TABLE `targetsforcalctl` AS   
SELECT '.$txndate.' AS `ForMonth`, `asm`.`TeamLeader`, (SELECT IFNULL(Sum(`CashSalesLessReturns`),0) FROM `targets0cashsalesreturnstl` WHERE TeamLeader=asm.`TeamLeader`) AS `CashSales`,
    (SELECT IFNULL(Sum(`Amount`),0)  FROM `InvoicesPaid` WHERE TeamLeader=`asm`.`TeamLeader`) AS `ClearedCollections`
FROM acctg_2salemain asm
WHERE MONTH(asm.`Date`)='.$txndate.' AND asm.TeamLeader<>0
GROUP BY  `asm`.`TeamLeader`';     
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

$sql0='CREATE TEMPORARY TABLE `NetValuesTL` AS
SELECT `ForMonth`, `TeamLeader`, ClearedCollections, CashSales,(`CashSales`+`ClearedCollections`) AS `NetforTL` FROM `targetsforcalctl`;';
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

$sql0='CREATE TEMPORARY TABLE `targetsforcalc` AS
SELECT '.$txndate.' AS `ForMonth`,
        `b`.`Branch` AS `Branch`, IF((ClassLastYr=3),\'Prime\',IF(ClassLastYr=2,\'Growth\',\'Seed\')) AS Class, b.`BranchNo` AS `BranchNo`, IFNULL(`CashSales`,0) AS `CashSales`, IFNULL(`ClearedDeposits`,0) AS `ClearedDeposits`, IFNULL(`OverdueAR`,0) AS `OverdueAR`, IFNULL(`UndepPDC`,0) AS `UndepPDC`
FROM
        `1branches` `b`
LEFT JOIN (SELECT `BranchNo`, Sum(`CashSalesLessReturns`) AS `CashSales`FROM `targets0cashsalesreturns` GROUP BY `BranchNo`) `tcr` ON `tcr`.`BranchNo`=`b`.`BranchNo`
LEFT JOIN (SELECT `BranchNo`, Sum(`ClearedAR`) AS `ClearedDeposits` FROM `targets0cleareddeposits`  GROUP BY `BranchNo`) `tcd` ON `tcd`.`BranchNo`=`b`.`BranchNo` 
LEFT JOIN (SELECT `BranchNo`, Sum(`OverdueAR`)  AS `OverdueAR` FROM `targets0overduear` GROUP BY `BranchNo`) `oar` ON `oar`.`BranchNo`=`b`.`BranchNo` 
LEFT JOIN (SELECT `BranchNo`, Sum(`UndepPDC`) AS `UndepPDC` FROM `UndepositedPDC` GROUP BY `BranchNo`) `pdc` ON `pdc`.`BranchNo`=`b`.`BranchNo`
WHERE `b`.`Active`<>0 AND b.PseudoBranch=0;'; 
// if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();

$sql1='CREATE TEMPORARY TABLE `NetValues` AS
SELECT `BranchNo`,`CashSales`+`ClearedDeposits`-`OverdueAR`-`UndepPDC` AS `NetforBranch` FROM targetsforcalc;';

} else {
    $sql0='CREATE TEMPORARY TABLE `targetsforcalc` AS
SELECT `tcr`.`ForMonth` AS `Month`,
        `b`.`Branch` AS `Branch`, b.`BranchNo` AS `BranchNo`, IFNULL(Sum(`CashSalesLessReturns`),0) AS `CashSales`, IFNULL(`ClearedDeposits`,0) AS `ClearedDeposits`, IFNULL(`OverdueAR`,0) AS `OverdueAR`, IFNULL(`UndepPDC`,0) AS `UndepPDC`
FROM `targets0cashsalesreturns` `tcr` JOIN `1branches` `b` ON `tcr`.`BranchNo`=`b`.`BranchNo`
LEFT JOIN (SELECT `ForMonth`, Sum(`ClearedAR`) AS `ClearedDeposits` FROM `targets0cleareddeposits`  GROUP BY `ForMonth`) `tcd` ON `tcd`.`ForMonth`=`tcr`.`ForMonth`
LEFT JOIN (SELECT `Month`, Sum(`OverdueAR`)  AS `OverdueAR` FROM `targets0overduear` GROUP BY `Month`) `oar` ON `oar`.`Month`=`tcr`.`ForMonth`
LEFT JOIN (SELECT `Month`, Sum(`UndepPDC`) AS `UndepPDC` FROM `UndepositedPDC` GROUP BY `Month`) `pdc` ON `pdc`.`Month`=`tcr`.`ForMonth`
WHERE `b`.`BranchNo`='.$branchno.' GROUP BY `tcr`.`ForMonth`;';
    
    // if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();
    
    $months=array(1,2,3,4,5,6,7,8,9,10,11,12);
    $sql0='';
    foreach ($months as $month) { $sql0.='SELECT '.$month.' AS `Month`, `'.str_pad($month,2,'0',STR_PAD_LEFT).'` AS `Target`, '.$branchno.' as `BranchNo` FROM `acctg_1yearsalestargets` WHERE BranchNo='.$branchno;
    if($month<>12) { $sql0.=' UNION ALL '; }
    }
    $sql0='CREATE TEMPORARY TABLE `branchtargets` AS '. $sql0; // if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }$stmt=$link->prepare($sql0);$stmt->execute();

$sql1='CREATE TEMPORARY TABLE `NetValues` AS
SELECT '.$txndate.' AS `Month`,`CashSales`+`ClearedDeposits`-`OverdueAR`-`UndepPDC` AS `NetforBranch` FROM targetsforcalc;';
}

//if($_SESSION['(ak0)']==1002) { echo $sql1.'<br><br>'; }
$stmt=$link->prepare($sql1);$stmt->execute();

$sql0='SELECT ValuePerTargetUnit FROM `00dataclosedby` WHERE ForDB=1';
$stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $valueperunit=$res0['ValuePerTargetUnit'];

$sql0='CREATE TEMPORARY TABLE `targetresults` AS '
        . 'SELECT tc.`BranchNo`, `Branch`, Class, Nickname as TeamLeader, FORMAT(`CashSales`,0) as `CashSales`,TRUNCATE(`CashSales`,2) as `CashSalesValue`, FORMAT(`ClearedDeposits`,0) as `ClearedCollections`,TRUNCATE(`ClearedDeposits`,2) as `ClearedCollectionsValue`, FORMAT(`OverdueAR`,0) as `OverdueAR`, TRUNCATE(`OverdueAR`,2) as `OverdueARValue`, FORMAT(`UndepPDC`,0) as `UndepPDC`, TRUNCATE(`UndepPDC`,2) as `UndepPDCValue`,NetforBranch AS NetforBranchValue, FORMAT(`NetforBranch`,0) as `NetforBranch`, FORMAT(yt.`'.$txndate.'`,0) as MonthTarget,
if(NetforBranch<0,-100,if((NetforBranch>=yt.`'.$txndate.'`),0,truncate((NetforBranch/yt.`'.$txndate.'`)*100,2)-100)) as PercentToReachTarget,
truncate((NetforBranch/yt.`'.$txndate.'`)*100,2) as TargetReached,
if ((NetforBranch/yt.`'.$txndate.'`)<1,0,truncate(NetforBranch/'.$valueperunit.',0)) as Units
FROM targetsforcalc tc JOIN `acctg_1yearsalestargets` yt on tc.BranchNo=yt.BranchNo LEFT JOIN `attend_1branchgroups` g on g.BranchNo=yt.BranchNo LEFT JOIN `1employees` e on g.TeamLeader=e.IDNo 
JOIN `NetValues` net on tc.BranchNo=net.BranchNo ORDER BY Branch;';
if($_SESSION['(ak0)']==1002) { echo $sql0.'<br><br>'; }
$stmt=$link->prepare($sql0);$stmt->execute();


?>