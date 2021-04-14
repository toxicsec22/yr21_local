<?php
$lastmonth=$activemonthfrom; $thisyr=$currentyr;

include_once $path.'/acrossyrs/commonfunctions/fxncountrows.php';
// Journal Vouchers
$sqllastmonth=$sqllastmonth.' UNION ALL SELECT "JV" AS ControlNo, "B" AS `BECS`, 0  as `SuppNo/ClientNo`, jvs.DebitAccountID as AccountID, BranchNo,FromBudgetOf, Sum(jvs.Amount) as SumofAmount, 1 AS Forex, SUM(Amount*Forex) AS SumofPHPAmount, "DR" as Entry FROM acctg_2jvmain jvm INNER JOIN acctg_2jvsub jvs ON jvm.JVNo=jvs.JVNo WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and jvs.DebitAccountID in '.$acctid.' group by DebitAccountID, BranchNo
UNION ALL
SELECT "JV", "B" AS `BECS`, 0 as `SuppNo/ClientNo`, jvs.CreditAccountID, BranchNo,FromBudgetOf, Sum(jvs.Amount)*-1, 1 AS Forex, SUM(Amount*-1*Forex) AS SumofPHPAmount, "CR" as Entry FROM acctg_2jvmain jvm INNER JOIN acctg_2jvsub jvs ON jvm.JVNo=jvs.JVNo WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and CreditAccountID in '.$acctid.'  group by CreditAccountID, BranchNo
';

if(countRows('acctg_1assetsdepr WHERE YEAR(DeprDate)='.$thisyr.' AND MONTH(DeprDate) in ('.$lastmonth.')')>0){
// DEPRECIATION
$sqllastmonth=$sqllastmonth.' UNION ALL SELECT "Depr" AS ControlNo, "B" AS `BECS`, 0 as `SuppNo/ClientNo`, IFNULL(d.DeprAccountID,100) as AccountID, IFNULL(BranchNo,0) AS BranchNo, IFNULL(BranchNo,0) as FromBudgetOf, Sum(d.Amount), 1 AS Forex, SUM(Amount) AS SumofPHPAmount, "DR" as Entry FROM acctg_1assets a JOIN acctg_1assetsdepr d ON a.AssetID=d.AssetID WHERE YEAR(DeprDate)='.$thisyr.' AND MONTH(DeprDate) in ('.$lastmonth.') and DeprAccountID in '.$acctid.' 
UNION ALL
SELECT "Depr" AS ControlNo, "B" AS `BECS`, 0 as `SuppNo/ClientNo`, IFNULL(ca.AccountID,100), IFNULL(BranchNo,0) AS BranchNo, IFNULL(BranchNo,0) as FromBudgetOf, Sum(d.Amount)*-1, 1 AS Forex, SUM(Amount*-1) AS SumofPHPAmount, "CR" as Entry FROM acctg_1assets a JOIN acctg_1assetsdepr d ON a.AssetID=d.AssetID JOIN `acctg_1chartofaccounts` ca ON ca.AccumDepAcctOf=d.DeprAccountID WHERE YEAR(DeprDate)='.$thisyr.' AND MONTH(DeprDate) in ('.$lastmonth.') and ca.AccountID in '.$acctid;
}

if(countRows('acctg_2prepaidamort')>0){ 
// APPLICATION OF PREPAID EXPENSES
$sqllastmonth=$sqllastmonth.' UNION ALL SELECT "PrExp" AS ControlNo, "B" AS `BECS`, 0 as `SuppNo/ClientNo`, IFNULL(d.ExpenseAccountID,100) as AccountID, BranchNo,BranchNo as FromBudgetOf, Sum(d.Amount), 1 AS Forex, SUM(Amount) AS SumofPHPAmount,  "DR" as Entry FROM acctg_2prepaid a JOIN acctg_2prepaidamort d ON a.PrepaidID=d.PrepaidID WHERE YEAR(AmortDate)='.$thisyr.' AND MONTH(AmortDate) in ('.$lastmonth.') and ExpenseAccountID in '.$acctid.' 
UNION ALL
SELECT "PrExp" AS ControlNo, "B" AS `BECS`, 0 as `SuppNo/ClientNo`, IFNULL(PrepaidAccountID,100) as AccountID, BranchNo,BranchNo as FromBudgetOf, Sum(d.Amount)*-1, 1 AS Forex, SUM(Amount*-1) AS SumofPHPAmount, "CR" as Entry FROM acctg_2prepaid a JOIN acctg_2prepaidamort d ON a.PrepaidID=d.PrepaidID WHERE YEAR(AmortDate)='.$thisyr.' AND MONTH(AmortDate) in ('.$lastmonth.') and PrepaidAccountID in '.$acctid;
}

// PURCHASES
$sqllastmonth=$sqllastmonth.' UNION ALL SELECT "Supp.Inv", "S" AS `BECS`, SupplierNo as `SuppNo/ClientNo`, DebitAccountID,BranchNo,FromBudgetOf, Sum(Amount), 1 AS Forex, SUM(Amount*Forex) AS SumofPHPAmount, "DR" FROM `acctg_2purchasemain` pm join `acctg_2purchasesub` ps on `pm`.TxnID=`ps`.TxnID WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and DebitAccountID in '.$acctid.'  group by DebitAccountID, BranchNo, SupplierNo
UNION ALL 
SELECT "Supp.Inv", "S" AS `BECS`, SupplierNo as `SuppNo/ClientNo`, CreditAccountID, BranchNo,FromBudgetOf, Sum(Amount)*-1, 1 AS Forex, SUM(Amount*Forex*-1) AS SumofPHPAmount, "CR" FROM `acctg_2purchasemain` pm join `acctg_2purchasesub` ps on `pm`.TxnID=`ps`.TxnID WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.')  and CreditAccountID in '.$acctid.' group by CreditAccountID, BranchNo, SupplierNo';

// Check VOUCHERS
$sqllastmonth=$sqllastmonth.' UNION ALL SELECT "CV", IF(PayeeNo>1000,"E","S") AS `BECS`, PayeeNo  as `SuppNo/ClientNo`, vchs.DebitAccountID, BranchNo,FromBudgetOf, Sum(vchs.Amount), 1 AS Forex, SUM(Amount*Forex) AS SumofPHPAmount, "DR"  FROM acctg_2cvmain vchm INNER JOIN acctg_2cvsub vchs ON vchm.CVNo=vchs.CVNo WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and DebitAccountID in '.$acctid.' group by DebitAccountID, BranchNo, PayeeNo
UNION ALL
SELECT "CV", IF(PayeeNo>1000,"E","S") AS `BECS`, PayeeNo as `SuppNo/ClientNo`, vchm.CreditAccountID, BranchNo,FromBudgetOf, Sum(vchs.Amount)*-1, 1 AS Forex, SUM(Amount*Forex*-1) AS SumofPHPAmount, "CR" FROM acctg_2cvmain vchm INNER JOIN acctg_2cvsub vchs ON vchm.CVNo=vchs.CVNo WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and CreditAccountID in '.$acctid.' group by CreditAccountID, BranchNo, PayeeNo
';

// SALES
$sqllastmonth=$sqllastmonth.' UNION ALL SELECT "Sale" AS Expr1, IF(ss.ClientNo<9999,"E","C") AS `BECS`, ss.ClientNo as `SuppNo/ClientNo`,ss.DebitAccountID, sm.BranchNo,sm.BranchNo as FromBudgetOf, Sum(ss.Amount), 1 AS Forex, SUM(Amount) AS SumofPHPAmount,  "DR" AS Expr2
FROM acctg_2salemain sm INNER JOIN acctg_2salesub ss  ON (sm.TxnID = ss.TxnID)
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and DebitAccountID in '.$acctid.' group by DebitAccountID, BranchNo, ss.ClientNo
UNION ALL
SELECT "Sale", IF(ss.ClientNo<9999,"E","C") AS `BECS`, ss.ClientNo as `SuppNo/ClientNo`, ss.CreditAccountID, sm.BranchNo,sm.BranchNo as FromBudgetOf, Sum(Amount)*-1 AS Expr2, 1 AS Forex, SUM(Amount*-1) AS SumofPHPAmount,  "CR" AS Expr3
FROM acctg_2salemain sm INNER JOIN acctg_2salesub ss ON (sm.TxnID = ss.TxnID)
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and CreditAccountID in '.$acctid.' group by CreditAccountID, BranchNo, ss.ClientNo';

// COLLECTIONS
$sqllastmonth=$sqllastmonth.' UNION ALL SELECT "Collect", IF(orm.ClientNo<9999,"E","C") AS `BECS`, orm.ClientNo as `SuppNo/ClientNo`, orm.DebitAccountID, ors.BranchNo,ors.BranchNo as FromBudgetOf, Sum(ors.Amount), 1 AS Forex, SUM(Amount) AS SumofPHPAmount,  "DR" AS Expr3
FROM acctg_2collectmain orm  INNER JOIN acctg_2collectsub ors ON (orm.TxnID = ors.TxnID)
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and DebitAccountID in '.$acctid.' group by DebitAccountID, BranchNo, orm.ClientNo
UNION ALL
SELECT "Collect", IF(orm.ClientNo<9999,"E","C") AS `BECS`, orm.ClientNo as `SuppNo/ClientNo`, orm.DebitAccountID as CreditAccountID, ors.BranchNo,ors.BranchNo as FromBudgetOf, Sum(ors.Amount)*-1, 1 AS Forex, SUM(Amount*-1) AS SumofPHPAmount,  "CR" AS Expr3
FROM acctg_2collectmain orm  INNER JOIN acctg_2collectsubdeduct ors ON (orm.TxnID = ors.TxnID)
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and orm.DebitAccountID in '.$acctid.' group by orm.DebitAccountID, BranchNo, orm.ClientNo
UNION ALL SELECT "Collect", IF(orm.ClientNo<9999,"E","C") AS `BECS`, orm.ClientNo, ors.CreditAccountID, ors.BranchNo,ors.BranchNo as FromBudgetOf, Sum(ors.Amount)*-1 AS Expr3, 1 AS Forex, SUM(Amount*-1) AS SumofPHPAmount,  "CR" AS Expr4
FROM acctg_2collectmain orm  INNER JOIN acctg_2collectsub ors ON  (orm.TxnID = ors.TxnID)
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and CreditAccountID in '.$acctid.' group by CreditAccountID, BranchNo, orm.ClientNo

UNION ALL SELECT "Collect", IF(orm.ClientNo<9999,"E","C") AS `BECS`, orm.ClientNo, ors.DebitAccountID, ors.BranchNo,ors.BranchNo as FromBudgetOf, Sum(ors.Amount) AS Expr3, 1 AS Forex, SUM(Amount) AS SumofPHPAmount,  "DR" AS Expr4
FROM acctg_2collectmain orm  INNER JOIN acctg_2collectsubdeduct ors ON  (orm.TxnID = ors.TxnID)
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and ors.DebitAccountID in '.$acctid.' group by ors.DebitAccountID, BranchNo, orm.ClientNo
';

// DEPOSITS and ENCASHMENTS
$sqllastmonth=$sqllastmonth.' UNION ALL SELECT "Dep#", IF(deps.ClientNo<9999,"E","C") AS `BECS`, deps.ClientNo  as `SuppNo/ClientNo`, depm.DebitAccountID, deps.BranchNo,deps.BranchNo as FromBudgetOf, Sum(deps.Amount), 1 AS Forex, SUM(Amount*Forex) AS SumofPHPAmount, "DR" AS Expr4
FROM acctg_2depositmain depm  INNER JOIN acctg_2depositsub deps  ON depm.TxnID = deps.TxnID
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and DebitAccountID in '.$acctid.'  group by DebitAccountID, BranchNo,deps.ClientNo
UNION ALL
SELECT "Encash", "B" AS `BECS`, depe.BranchNo, depe.DebitAccountID, depe.BranchNo,FromBudgetOf, Sum(depe.Amount), 1 AS Forex, SUM(Amount) AS SumofPHPAmount,  "DR" AS Expr4
FROM acctg_2depositmain depm  INNER JOIN acctg_2depencashsub depe ON depm.TxnID = depe.TxnID
WHERE  YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and depe.DebitAccountID in '.$acctid.' group by depe.DebitAccountID, BranchNo
UNION ALL
SELECT "Dep", IF(deps.ClientNo<9999,"E","C") AS `BECS`, deps.ClientNo as `SuppNo/ClientNo`, deps.CreditAccountID, deps.BranchNo,deps.BranchNo as FromBudgetOf, Sum(deps.Amount)*-1 AS Expr4, 1 AS Forex, SUM(Amount*Forex*-1) AS SumofPHPAmount, "CR" AS Expr5
FROM acctg_2depositmain depm INNER JOIN acctg_2depositsub deps ON depm.TxnID = deps.TxnID
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and CreditAccountID in '.$acctid.' group by CreditAccountID, BranchNo, deps.ClientNo
UNION ALL
SELECT "Encash", "B" AS `BECS`, depe.BranchNo as `SuppNo/ClientNo`, depm.DebitAccountID AS CreditAcctID, depe.BranchNo,FromBudgetOf, Sum(`Amount`)*-1 AS Expr3, 1 AS Forex, SUM(Amount*-1) AS SumofPHPAmount,  "CR" AS Expr4
FROM acctg_2depositmain depm INNER JOIN acctg_2depencashsub depe ON depm.TxnID = depe.TxnID
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and depm.DebitAccountID in '.$acctid.' group by depm.DebitAccountID, depe.BranchNo
';
if(countRows('acctg_3undepositedpdcfromlastperiodbounced')>0){
// BOUNCED CHECKS FROM LAST PERIOD 
$sqlalltxns=$sqlalltxns.' UNION ALL SELECT "BouncedCollectLastYr", IF(bm.ClientNo<9999,"E","C") AS `BECS`, bm.ClientNo, 200 AS DebitAccountID, BranchNo,BranchNo as FromBudgetOf, Sum(AmountofPDC), 1 AS Forex, SUM(AmountofPDC) AS SumofPHPAmount,  "DR"
FROM `acctg_3undepositedpdcfromlastperiod` bm JOIN `acctg_3undepositedpdcfromlastperiodbounced` bs ON  (bm.UndepPDCId = bs.UndepPDCId)
WHERE Year(DateBounced)='.$thisyr.' AND MONTH(DateBounced) in ('.$lastmonth.') and 200 in '.$acctid.'
    GROUP BY BranchNo,bm.ClientNo
     UNION ALL SELECT "BouncedCollectLastYr",IF(bm.ClientNo<9999,"E","C") AS `BECS`, bm.ClientNo, IFNULL(CreditAccountID,100) AS CreditAccountID, BranchNo,BranchNo as FromBudgetOf, Sum(AmountofPDC)*-1, 1 AS Forex, SUM(AmountofPDC*-1) AS SumofPHPAmount,  "CR"
FROM `acctg_3undepositedpdcfromlastperiod` bm JOIN `acctg_3undepositedpdcfromlastperiodbounced` bs ON  (bm.UndepPDCId = bs.UndepPDCId)
WHERE Year(DateBounced)='.$thisyr.' AND MONTH(DateBounced) in ('.$lastmonth.') and CreditAccountID in '.$acctid.'
    GROUP BY CreditAccountID, BranchNo,bm.ClientNo ';
}

if(countRows('acctg_2collectsubbounced')>0){
// BOUNCED CHECKS FROM CR
$sqllastmonth=$sqllastmonth.' UNION ALL SELECT "Bounced", IF(cm.ClientNo<9999,"E","C") AS `BECS`, cm.ClientNo, 200 AS DebitAccountID, cs.BranchNo,cs.BranchNo as FromBudgetOf, Sum(cs.Amount), 1 AS Forex,  Sum(cs.Amount) AS SumofPHPAmount,  "DR" AS Expr4
FROM `acctg_2collectmain` `cm` JOIN  `acctg_2collectsub` `cs` ON `cm`.`TxnID` = `cs`.`TxnID` JOIN `acctg_2collectsubbounced` `cbs` ON `cm`.`TxnID` = `cbs`.`TxnID`
WHERE YEAR(DateBounced)='.$thisyr.' AND MONTH(DateBounced) in ('.$lastmonth.') and (200 in '.$acctid.') group by BranchNo, cm.ClientNo
UNION ALL
SELECT "Bounced", IF(cm.ClientNo<9999,"E","C") AS `BECS`, cm.ClientNo, IFNULL(cbs.CreditAccountID,100) AS CreditAccountID, cs.BranchNo,cs.BranchNo as FromBudgetOf, Sum(`Amount`)*-1, 1 AS Forex,  Sum(Amount)*-1 AS SumofPHPAmount, "CR" AS Expr4
FROM `acctg_2collectmain` `cm` JOIN  `acctg_2collectsub` `cs` ON `cm`.`TxnID` = `cs`.`TxnID` JOIN `acctg_2collectsubbounced` `cbs` ON `cm`.`TxnID` = `cbs`.`TxnID`
WHERE YEAR(DateBounced)='.$thisyr.' AND MONTH(DateBounced) in ('.$lastmonth.') and cbs.CreditAccountID in '.$acctid.' group by CreditAccountID, BranchNo, cm.ClientNo 
UNION ALL
SELECT "Bounced", IF(cm.ClientNo<9999,"E","C") AS `BECS`, cm.ClientNo, IFNULL(cbb.CreditAccountID,100) AS DebitAccountID, cbs.BranchNo,cbs.BranchNo as FromBudgetOf, SUM(cbs.Amount), 1 AS Forex,  Sum(cbs.Amount) AS SumofPHPAmount, "DR" AS Expr3
FROM acctg_2collectmain cm JOIN `acctg_2collectsubdeduct` cbs ON  (cm.TxnID = cbs.TxnID)   JOIN `acctg_2collectsubbounced` cbb ON  (cm.TxnID = cbb.TxnID) 
WHERE YEAR(DateBounced)='.$thisyr.' AND MONTH(DateBounced) in ('.$lastmonth.') and cbb.CreditAccountID in '.$acctid.' 
UNION ALL
SELECT "Bounced", IF(cm.ClientNo<9999,"E","C") AS `BECS`, cm.ClientNo, IFNULL(cbs.DebitAccountID,100) as CreditAccountID, cbs.BranchNo,cbs.BranchNo as FromBudgetOf, SUM(cbs.Amount)*-1, 1 AS Forex,  Sum(cbs.Amount)*-1 AS SumofPHPAmount, "CR" AS Expr3
FROM acctg_2collectmain cm JOIN `acctg_2collectsubdeduct` cbs ON  (cm.TxnID = cbs.TxnID)   JOIN `acctg_2collectsubbounced` cbb ON  (cm.TxnID = cbb.TxnID) 
WHERE YEAR(DateBounced)='.$thisyr.' AND MONTH(DateBounced) in ('.$lastmonth.') and cbs.DebitAccountID in '.$acctid.' ';
}

// INTERBRANCH TRANSFERS
// Transfer OUT - Inventory (actual DR & CR entries, usually DR ARTradeTxfr CR IncomeTxfr/Inventory)
$sqllastmonth=$sqllastmonth.' UNION ALL SELECT "TxfrOUT", "B" AS `BECS`, tm.FromBranchNo,ts.DebitAccountID, tm.FromBranchNo,tm.FromBranchNo as FromBudgetOf, Sum(ts.Amount), 1 AS Forex,  Sum(ts.Amount) AS SumofPHPAmount, "DR" AS Expr2
FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and DebitAccountID in '.$acctid.' group by DebitAccountID, FromBranchNo
UNION ALL
SELECT "TxfrOUT", "B" AS `BECS`, tm.FromBranchNo,tm.CreditAccountID, tm.FromBranchNo,tm.FromBranchNo as FromBudgetOf, Sum(ts.Amount)*-1, 1 AS Forex,  Sum(ts.Amount)*-1 AS SumofPHPAmount, "CR" AS Expr2
FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') and CreditAccountID in '.$acctid.' group by CreditAccountID, FromBranchNo';
// Transfer IN - Inventory (DR Inventory CR APTradeTxfr)
if (in_array(300,$acctidarray)){
        // DateIN Is Not Null (DR Inventory)
        $sqllastmonth=$sqllastmonth.' UNION ALL SELECT "TxfrIN" AS Expr1, "B" AS `BECS`, ts.ClientBranchNo, 300 AS DRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, Sum(ts.Amount), 1 AS Forex,  Sum(ts.Amount) AS SumofPHPAmount, "DR" AS Expr3 FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(DateIN) in ('.$lastmonth.')  And (ts.DateIN Is Not Null) and DebitAccountID=204 group by ClientBranchNo ';
}

if (in_array(330,$acctidarray)){ //In Transit account 
        // DateIN is NULL (DR InTransit)
        $sqllastmonth=$sqllastmonth.' UNION ALL SELECT "TxfrIN" AS Expr1, "B" AS `BECS`, ts.ClientBranchNo, 330 AS DRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, Sum(ts.Amount), 1 AS Forex,  Sum(ts.Amount) AS SumofPHPAmount, "DR" AS Expr3 FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.')  And (ts.DateIN Is Not Null)  AND Year(tm.Date)='.$thisyr.' and DebitAccountID=204 group by ClientBranchNo ';
}

if (in_array(404,$acctidarray)){ //APTradeTxfr account
        // DateIN Is Not Null
        $sqllastmonth=$sqllastmonth.' UNION ALL SELECT "TxfrIN" AS Expr1, "B" AS `BECS`, ts.ClientBranchNo,404 AS CRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, Sum(ts.Amount)*-1, 1 AS Forex,  Sum(ts.Amount)*-1 AS SumofPHPAmount, "CR" AS Expr3 FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(DateIN) in ('.$lastmonth.')  And DebitAccountID=204 group by ClientBranchNo';
        // DateIN Is Null
        $sqllastmonth=$sqllastmonth.' UNION ALL SELECT "TxfrIN" AS Expr1, "B" AS `BECS`, ts.ClientBranchNo,404 AS CRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, Sum(ts.Amount)*-1,  1 AS Forex,  Sum(ts.Amount)*-1 AS SumofPHPAmount, "CR" AS Expr3 FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.')  And DebitAccountID=204 group by ClientBranchNo';
}

// Tranfer OUT - Receive Payment (DR PaidViaAcctID, usually BDO  CR to reverse the Debit account for transfer, usually ARTradeTxfr)
$sqllastmonth=$sqllastmonth.' UNION ALL SELECT "TxfrOUTRecvPayment", "B" AS `BECS`, tm.FromBranchNo,ts.PaidViaAcctID, tm.FromBranchNo,tm.FromBranchNo as FromBudgetOf, Sum(ts.Amount),  1 AS Forex,  Sum(ts.Amount) AS SumofPHPAmount, "DR" AS Expr2
FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$lastmonth.') And (ts.DatePaid Is Not Null)  and PaidViaAcctID in '.$acctid.' group by PaidViaAcctID, FromBranchNo
UNION ALL
SELECT "TxfrOUTRecvPayment", "B" AS `BECS`, tm.FromBranchNo,ts.DebitAccountID, tm.FromBranchNo,tm.FromBranchNo as FromBudgetOf, Sum(ts.Amount)*-1,  1 AS Forex,  Sum(ts.Amount)*-1 AS SumofPHPAmount, "CR" AS Expr2
FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(DatePaid) in ('.$lastmonth.')  And (ts.DatePaid Is Not Null) and DebitAccountID in '.$acctid.' group by DebitAccountID, FromBranchNo';

// Transfer IN - Give Payment (DR APTradeTxfr  CR PaidViaAcctID)
$sqllastmonth=$sqllastmonth.' UNION ALL SELECT "TxfrINPayment", "B" AS `BECS`, ts.ClientBranchNo,ts.PaidViaAcctID AS CRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, Sum(ts.Amount)*-1,  1 AS Forex,  Sum(ts.Amount)*-1 AS SumofPHPAmount,"CR" AS Expr3
FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(DatePaid) in ('.$lastmonth.')  And (ts.DatePaid Is Not Null)  and PaidViaAcctID in '.$acctid.' group by PaidViaAcctID, ClientBranchNo';
if (in_array(404,$acctidarray)){
$sqllastmonth=$sqllastmonth.' UNION ALL SELECT "TxfrINPayment" AS Expr1,"B" AS `BECS`, ts.ClientBranchNo,404 AS DRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, Sum(ts.Amount),  1 AS Forex,  Sum(ts.Amount) AS SumofPHPAmount,"DR" AS Expr3 FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(DatePaid) in ('.$lastmonth.')  And (ts.DatePaid Is Not Null) and DebitAccountID=204 group by ClientBranchNo
';
}

//echo $sqllastmonth; break;
?>