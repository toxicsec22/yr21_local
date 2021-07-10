<?php
$sql0='CREATE TABLE `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'` AS
    SELECT 
        `uni`.`BranchNo` AS `BranchNo`,
        IF(((`uni`.`AccountID` >= 200)
                AND (`uni`.`AccountID` <= 202)),
            SUM(`uni`.`Amount`),
            (SUM(`uni`.`Amount`) * -(1))) AS `ARAPEnd`,
        `uni`.`AccountID` AS `AccountID`,
        "EndBal" AS `DataFrom`
    FROM
        `'.$currentyr.'_static`.`acctg_0unialltxns` `uni`
    WHERE
        (((`uni`.`AccountID` >= 200)
            AND (`uni`.`AccountID` <= 202))
            OR ((`uni`.`AccountID` >= 400)
            AND (`uni`.`AccountID` <= 402)))
    GROUP BY `uni`.`BranchNo` , `uni`.`AccountID` 
    UNION ALL SELECT 
        `acctg_23balperinv`.`BranchNo` AS `BranchNo`,
        SUM(`acctg_23balperinv`.`PayBalance`) AS `NetPayables`,
        `acctg_23balperinv`.`CreditAccountID` AS `CreditAccountID`,
        "InvBalances" AS `DataFrom`
    FROM
        `acctg_23balperinv`
    GROUP BY `acctg_23balperinv`.`BranchNo` , `acctg_23balperinv`.`CreditAccountID` 
    UNION ALL SELECT 
        `acctg_33qrybalperrecpt`.`BranchNo` AS `BranchNo`,
        SUM(`acctg_33qrybalperrecpt`.`InvBalance`) AS `NetReceivables`,
        `acctg_33qrybalperrecpt`.`DebitAccountID` AS `DebitAccountID`,
        "InvBalances" AS `DataFrom`
    FROM
        `acctg_33qrybalperrecpt`
    GROUP BY `acctg_33qrybalperrecpt`.`BranchNo` , `acctg_33qrybalperrecpt`.`DebitAccountID` 
    UNION ALL SELECT 
        `acctg_38undepositedclientpdcs`.`BranchNo` AS `BranchNo`,
        SUM(`acctg_38undepositedclientpdcs`.`PDC`) AS `NetPDC`,
        201 AS `201`,
        "InvBalances" AS `DataFrom`
    FROM
        `acctg_38undepositedclientpdcs`
    GROUP BY `acctg_38undepositedclientpdcs`.`BranchNo`';

$sql1='CREATE TABLE `acctg_dailyclose_endapar'.$_SESSION['(ak0)'].'` AS
    SELECT 
        `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`BranchNo` AS `BranchNo`,
        `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`AccountID` AS `AccountID`,
        FORMAT(IFNULL(SUM((CASE
                        WHEN (`acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`DataFrom` = "EndBal") THEN `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`ARAPEnd`
                    END)),
                    0),
            2) AS `BSAmt`,
        FORMAT(IFNULL(SUM((CASE
                        WHEN (`acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`DataFrom` = "InvBalances") THEN `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`ARAPEnd`
                    END)),
                    0),
            2) AS `InvBalances`,
        FORMAT((IFNULL(SUM((CASE
                        WHEN (`acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`DataFrom` = "EndBal") THEN `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`ARAPEnd`
                    END)),
                    0) - IFNULL(SUM((CASE
                        WHEN (`acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`DataFrom` = "InvBalances") THEN `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`ARAPEnd`
                    END)),
                    0)),
            2) AS `Diff`
    FROM
        `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`
    GROUP BY `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`BranchNo` , `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`.`AccountID`';
?>