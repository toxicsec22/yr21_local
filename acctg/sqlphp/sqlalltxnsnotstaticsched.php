<?php
$thisyr=$currentyr;
include_once $path.'/acrossyrs/commonfunctions/fxncountrows.php';
// Journal Vouchers
    $sqlalltxns=$sqlalltxns.' SELECT jvs.Date, concat("JVNo ",jvm.JVNo) AS ControlNo, "B" AS `BECS`, 0 as `SuppNo/ClientNo`, "-" as `Supplier/Customer/Branch`, jvs.Particulars, jvs.DebitAccountID as AccountID, BranchNo,FromBudgetOf, jvs.Amount, "DR" as Entry, "JV" as w, jvm.JVNo AS TxnID FROM acctg_2jvmain jvm INNER JOIN acctg_2jvsub jvs ON jvm.JVNo=jvs.JVNo WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$montharray.') and jvs.DebitAccountID in '.$acctid.'
UNION ALL
SELECT jvs.Date, concat("JVNo ",jvm.JVNo) AS ControlNo, "B" AS `BECS`, 0, "" as `Supplier/Customer/Branch`, jvs.Particulars, jvs.CreditAccountID, BranchNo,FromBudgetOf, jvs.Amount*-1, "CR" as Entry, "JV" as w, jvm.JVNo FROM acctg_2jvmain jvm INNER JOIN acctg_2jvsub jvs ON jvm.JVNo=jvs.JVNo WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$montharray.') and CreditAccountID in '.$acctid;

// ASSETS This should actually have been recorded somewhere else, such as vouchers during payments or adjustments for loans
if(countRows('acctg_1assetsdepr')>0){
// DEPRECIATION
$sqlalltxns=$sqlalltxns.' UNION ALL SELECT d.DeprDate, concat("DeprID ",d.DeprID) AS ControlNo, "B" AS `BECS`, 0 as `SuppNo/ClientNo`,"-" as `Supplier/Customer/Branch`, AssetDesc, IFNULL(d.DeprAccountID,100) as AccountID, BranchNo,BranchNo as FromBudgetOf, d.Amount, "DR" as Entry, "AssetandDepr" as w, a.AssetID FROM acctg_1assets a JOIN acctg_1assetsdepr d ON a.AssetID=d.AssetID WHERE YEAR(DeprDate)='.$thisyr.' AND MONTH(DeprDate) in ('.$montharray.') and DeprAccountID in '.$acctid.' 
UNION ALL
SELECT d.DeprDate, concat("DeprID ",d.DeprID) AS ControlNo, "B" AS `BECS`, 0 as `SuppNo/ClientNo`,"-" as `Supplier/Customer/Branch`, AssetDesc, IFNULL(ca.AccountID,100), BranchNo,BranchNo as FromBudgetOf, d.Amount*-1, "CR" as Entry, "AssetandDepr" as w, a.AssetID FROM acctg_1assets a JOIN acctg_1assetsdepr d ON a.AssetID=d.AssetID 
JOIN `acctg_1chartofaccounts` ca ON ca.AccumDepAcctOf=d.DeprAccountID WHERE YEAR(DeprDate)='.$thisyr.' AND MONTH(DeprDate) in ('.$montharray.') and ca.AccountID in '.$acctid;
}
if(countRows('acctg_2prepaidamort')>0){
// PREPAID EXPENSE APPLICATION
$sqlalltxns=$sqlalltxns.' UNION ALL SELECT d.AmortDate, concat("PrExpID ",d.AmortID) AS ControlNo, "B" AS `BECS`, 0 as `SuppNo/ClientNo`,"-" as `Supplier/Customer/Branch`, PrepaidDesc, IFNULL(d.ExpenseAccountID,100) as AccountID, BranchNo,BranchNo as FromBudgetOf, d.Amount, "DR" as Entry, "PrepaidExpense" as w, a.PrepaidID FROM acctg_2prepaid a JOIN acctg_2prepaidamort d ON a.PrepaidID=d.PrepaidID WHERE YEAR(AmortDate)='.$thisyr.' AND MONTH(AmortDate) in ('.$montharray.') and ExpenseAccountID in '.$acctid.' 
UNION ALL
SELECT d.AmortDate, concat("PrExpID ",d.AmortID) AS ControlNo, "B" AS `BECS`, 0 as `SuppNo/ClientNo`,"-" as `Supplier/Customer/Branch`, PrepaidDesc, IFNULL(PrepaidAccountID,100) as AccountID, BranchNo,BranchNo as FromBudgetOf, d.Amount*-1, "CR" as Entry, "PrepaidExpense" as w, a.PrepaidID FROM acctg_2prepaid a JOIN acctg_2prepaidamort d ON a.PrepaidID=d.PrepaidID WHERE YEAR(AmortDate)='.$thisyr.' AND MONTH(AmortDate) in ('.$montharray.') and PrepaidAccountID in '.$acctid;
}

// PURCHASES
$sqlalltxns=$sqlalltxns.' UNION ALL SELECT Date, concat("Supp.Inv# ",SupplierInv), "S" AS `BECS`, p.SupplierNo, CONCAT(SupplierName, " TIN#",s.TIN, " ", s.Address), concat("Inv.Date ",DateofInv), DebitAccountID,BranchNo,FromBudgetOf,  Amount, "DR", "Purchase", p.TxnID FROM `acctg_2purchasemain` p join `acctg_2purchasesub` ps on `p`.TxnID=`ps`.TxnID JOIN `1suppliers` s on s.SupplierNo=p.SupplierNo  WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$montharray.') and DebitAccountID in '.$acctid.'
UNION ALL 
SELECT Date, concat("Supp.Inv# ",SupplierInv), "S" AS `BECS`, p.SupplierNo, SupplierName, concat("Inv.Date ",DateofInv), CreditAccountID, BranchNo,FromBudgetOf,  Amount*-1, "CR", "Purchase", p.TxnID  FROM `acctg_2purchasemain` p join `acctg_2purchasesub` ps on `p`.TxnID=`ps`.TxnID JOIN `1suppliers` s on s.SupplierNo=p.SupplierNo  WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$montharray.')  and CreditAccountID in '.$acctid;

// Check VOUCHERS
$sqlalltxns=$sqlalltxns.' UNION ALL
SELECT vchm.Date, concat("CVNo ",vchm.CVNo),  IF(PayeeNo>1000,"E","S") AS `BECS`, PayeeNo as `SuppNo/ClientNo`, vchm.Payee, concat(ifnull(vchs.Particulars,"")," ",ifnull(vchs.ForInvoiceNo,"")," Chk# ",vchm.CheckNo, 
if(isnull(vchs.TIN),"", concat(" TIN#",vchs.TIN, " ", t.CompanyName," ", t.Address))) as Particulars, vchs.DebitAccountID, BranchNo,FromBudgetOf, vchs.Amount, "DR", "CV", vchm.CVNo  FROM acctg_2cvmain vchm INNER JOIN acctg_2cvsub vchs ON vchm.CVNo=vchs.CVNo LEFT JOIN `gen_info_1tinforexpenses` t on t.TIN=vchs.TIN WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$montharray.') and DebitAccountID in '.$acctid.'
UNION ALL
SELECT vchm.Date, concat("CVNo ",vchm.CVNo),  IF(PayeeNo>1000,"E","S") AS `BECS`, PayeeNo, vchm.Payee, concat(ifnull(vchs.Particulars,"")," ",ifnull(vchs.ForInvoiceNo,"")," Chk# ",vchm.CheckNo, 
if(isnull(vchs.TIN),"", concat(" TIN#",vchs.TIN, " ", t.CompanyName, " ", t.Address))), vchm.CreditAccountID, BranchNo,FromBudgetOf, vchs.Amount*-1, "CR", "CV", vchm.CVNo FROM acctg_2cvmain vchm INNER JOIN acctg_2cvsub vchs ON vchm.CVNo=vchs.CVNo LEFT JOIN `gen_info_1tinforexpenses` t on t.TIN=vchs.TIN WHERE YEAR(Date)='.$thisyr.' AND MONTH(Date) in ('.$montharray.')  and CreditAccountID in '.$acctid;

// SALES 
$sqlalltxns=$sqlalltxns.' UNION ALL SELECT sm.Date, "Sale" AS Expr1, IF(ss.ClientNo<9999,"E","C") AS `BECS`, ss.ClientNo, ifnull(`ClientName`,"") as ClientName, ss.Particulars, ss.DebitAccountID, sm.BranchNo, sm.BranchNo as FromBudgetOf, ss.Amount, "DR" AS Expr2, "Sale", sm.TxnID
FROM acctg_2salemain sm INNER JOIN (acctg_2salesub ss left JOIN `acctg_01uniclientsalespersonfordep` c ON ss.ClientNo = c.ClientNo) ON (sm.TxnID = ss.TxnID)
WHERE Month(sm.Date) in ('.$montharray.') and DebitAccountID in '.$acctid.'
UNION ALL
SELECT sm.Date, "Sale" AS Expr1, IF(ss.ClientNo<9999,"E","C") AS `BECS`, ss.ClientNo, ifnull(`ClientName`,"") as ClientName, ss.Particulars, ss.CreditAccountID, sm.BranchNo,sm.BranchNo as FromBudgetOf, Amount*-1 AS Expr2, "CR" AS Expr3, "Sale", sm.TxnID
FROM acctg_2salemain sm INNER JOIN (acctg_2salesub ss left JOIN `acctg_01uniclientsalespersonfordep` c ON ss.ClientNo = c.ClientNo) ON (sm.TxnID = ss.TxnID)
WHERE Month(sm.Date) in ('.$montharray.')  and CreditAccountID in '.$acctid;


// COLLECTIONS
$sqlalltxns=$sqlalltxns.' UNION ALL SELECT orm.Date, concat("Collect",orm.CollectNo) AS Expr1, IF(orm.ClientNo<9999,"E","C") AS `BECS`, orm.ClientNo, ifnull(`ClientName`,"") as ClientName, concat(orm.CollectNo," Inv",ForChargeInvNo,"/",`Type`,"/",ifnull(`CheckNo`,"")) AS Expr2, orm.DebitAccountID, ors.BranchNo,ors.BranchNo as FromBudgetOf, ors.Amount, "DR" AS Expr3, "Collect", orm.TxnID
FROM (acctg_2collectmain orm left JOIN `acctg_01uniclientsalespersonfordep` c ON orm.ClientNo = c.ClientNo) INNER JOIN acctg_2collectsub ors ON (orm.TxnID = ors.TxnID)
WHERE Month(orm.Date) in ('.$montharray.') and DebitAccountID in '.$acctid.'
UNION ALL
SELECT orm.Date, concat("Collect",orm.CollectNo) AS Expr1, IF(orm.ClientNo<9999,"E","C") AS `BECS`, orm.ClientNo, ifnull(`ClientName`,"") as ClientName, DeductDetails, orm.DebitAccountID as CreditAccountID, ors.BranchNo,ors.BranchNo as FromBudgetOf, ors.Amount*-1, "CR" AS Expr3, "Collect", orm.TxnID
FROM (acctg_2collectmain orm left JOIN `acctg_01uniclientsalespersonfordep` c ON orm.ClientNo = c.ClientNo) INNER JOIN acctg_2collectsubdeduct ors ON (orm.TxnID = ors.TxnID)
WHERE Month(orm.Date) in ('.$montharray.') and orm.DebitAccountID in '.$acctid.'
UNION ALL SELECT orm.Date, concat("Collect",orm.CollectNo) AS Expr1, IF(orm.ClientNo<9999,"E","C") AS `BECS`, orm.ClientNo, ifnull(`ClientName`,"") as ClientName, concat(orm.CollectNo," Inv",ForChargeInvNo,"/",`Type`,"/",ifnull(`CheckNo`,"")) AS Expr2, ors.CreditAccountID, ors.BranchNo,ors.BranchNo as FromBudgetOf, ors.Amount*-1 AS Expr3, "CR" AS Expr4, "Collect", orm.TxnID
FROM (acctg_2collectmain orm left JOIN `acctg_01uniclientsalespersonfordep` c ON orm.ClientNo = c.ClientNo) INNER JOIN acctg_2collectsub ors ON  (orm.TxnID = ors.TxnID)
WHERE Month(orm.Date) in ('.$montharray.')  and CreditAccountID in '.$acctid.'
UNION ALL SELECT orm.Date, concat("Collect",orm.CollectNo) AS Expr1, IF(orm.ClientNo<9999,"E","C") AS `BECS`, orm.ClientNo, ifnull(`ClientName`,"") as ClientName, DeductDetails, ors.DebitAccountID, ors.BranchNo,ors.BranchNo as FromBudgetOf, ors.Amount AS Expr3, "DR" AS Expr4, "Collect", orm.TxnID
FROM (acctg_2collectmain orm left JOIN `acctg_01uniclientsalespersonfordep` c ON orm.ClientNo = c.ClientNo) INNER JOIN acctg_2collectsubdeduct ors ON  (orm.TxnID = ors.TxnID)
WHERE Month(orm.Date) in ('.$montharray.')  and ors.DebitAccountID in '.$acctid;

// DEPOSITS and ENCASHMENTS
$sqlalltxns=$sqlalltxns.' UNION ALL
SELECT depm.Date, concat("Dep# ",depm.DepositNo) AS Expr1, IF(deps.ClientNo<9999,"E","C") AS `BECS`, deps.ClientNo as `SuppNo/ClientNo`, concat(ifnull(`ClientName`,"")," ",ifnull(`DepDetails`," ")) AS Expr2, concat(deps.`ForChargeInvNo`, "/",deps.`Type`,"/",ifnull(`CheckNo`,"")) AS Expr3, depm.DebitAccountID, deps.BranchNo,deps.BranchNo as FromBudgetOf, deps.Amount, "DR","Deposit", depm.TxnID 
FROM acctg_2depositmain depm   JOIN acctg_2depositsub deps ON depm.TxnID = deps.TxnID left JOIN `acctg_01uniclientsalespersonfordep` c ON  (deps.ClientNo = c.ClientNo) 
WHERE Month(depm.Date) in ('.$montharray.') and DebitAccountID in '.$acctid.'
UNION ALL
SELECT depm.Date, concat("Dep# ",depm.DepositNo) AS Expr1, IF(deps.ClientNo<9999,"E","C") AS `BECS`, deps.ClientNo, concat(ifnull(`ClientName`,"")," ",ifnull(`DepDetails`," ")) AS Expr2, concat(deps.ForChargeInvNo,"/",deps.`Type`,"/",ifnull(`CheckNo`,"")) AS Expr3, deps.CreditAccountID, deps.BranchNo,deps.BranchNo as FromBudgetOf, deps.Amount*-1 AS Expr4, "CR" AS Expr5, "Deposit", depm.TxnID 
FROM acctg_2depositmain depm INNER JOIN (acctg_2depositsub deps left JOIN `acctg_01uniclientsalespersonfordep` c ON (deps.ClientNo = c.ClientNo)) ON depm.TxnID = deps.TxnID
WHERE Month(depm.Date) in ('.$montharray.')  and CreditAccountID in '.$acctid.'
UNION ALL
SELECT depm.Date, concat("Encash# ",depm.DepositNo) AS Expr1, "B" AS `BECS`, 0, CONCAT(depe.EncashDetails,if(isnull(depe.TIN),"", concat(" ", t.CompanyName))), CONCAT(if(isnull(ApprovalNo),"",concat("approval ",`ApprovalNo`)),if(isnull(depe.TIN),"", concat(" TIN#",depe.TIN, " ", t.Address))) AS Expr2, depe.DebitAccountID, depe.BranchNo,depe.FromBudgetOf, depe.Amount, "DR" AS Expr4,"Deposit", depm.TxnID 
FROM acctg_2depositmain depm  INNER JOIN acctg_2depencashsub depe ON depm.TxnID = depe.TxnID LEFT JOIN `gen_info_1tinforexpenses` t on t.TIN=depe.TIN 
WHERE  Month(depm.Date) in ('.$montharray.') and depe.DebitAccountID in '.$acctid.'
UNION ALL
SELECT depm.Date, concat("Encash# ",depm.DepositNo) AS Expr1, "B" AS `BECS`, 0, CONCAT(depe.EncashDetails,if(isnull(depe.TIN),"", concat(" ", t.CompanyName))), CONCAT(if(isnull(ApprovalNo),"",concat("approval ",`ApprovalNo`)),if(isnull(depe.TIN),"", concat(" TIN#",depe.TIN, " ", t.Address)))  AS Expr2, depm.DebitAccountID AS CreditAcctID, depe.BranchNo,depe.FromBudgetOf, `Amount`*-1 AS Expr3, "CR" AS Expr4, "Deposit", depm.TxnID 
FROM acctg_2depositmain depm INNER JOIN acctg_2depencashsub depe ON depm.TxnID = depe.TxnID LEFT JOIN `gen_info_1tinforexpenses` t on t.TIN=depe.TIN 
WHERE Month(depm.Date) in ('.$montharray.') and depm.DebitAccountID in '.$acctid;

if(countRows('acctg_3undepositedpdcfromlastperiodbounced')>0){
// BOUNCED CHECKS FROM LAST PERIOD
$sqlalltxns=$sqlalltxns.' UNION ALL SELECT bs.DateBounced, CONCAT("Bounced#CR",bm.CRNo,"_",bm.PDCNo), IF(bm.ClientNo<9999,"E","C") AS `BECS`, bm.ClientNo, c.ClientName, PDCNo, 200 AS DebitAccountID, BranchNo,BranchNo as FromBudgetOf, AmountofPDC, "DR" AS Expr4, "Bounced", bm.UndepPDCId
FROM (`acctg_01uniclientsalespersonfordep` c right JOIN `acctg_3undepositedpdcfromlastperiod` bm ON c.ClientNo = bm.ClientNo) INNER JOIN `acctg_3undepositedpdcfromlastperiodbounced` bs ON  (bm.UndepPDCId = bs.UndepPDCId)
WHERE Month(DateBounced) in ('.$montharray.') and 200 in '.$acctid.'
UNION ALL SELECT bs.DateBounced, CONCAT("Bounced#CR",bm.CRNo,"_",bm.PDCNo), IF(bm.ClientNo<9999,"E","C") AS `BECS`, bm.ClientNo, c.ClientName, PDCNo, IFNULL(CreditAccountID,100), BranchNo,BranchNo as FromBudgetOf, `AmountofPDC`*-1 AS Expr3, "CR" AS Expr4, "Bounced", bm.UndepPDCId
FROM (`acctg_01uniclientsalespersonfordep` c right JOIN `acctg_3undepositedpdcfromlastperiod` bm ON c.ClientNo = bm.ClientNo) INNER JOIN `acctg_3undepositedpdcfromlastperiodbounced` bs ON  (bm.UndepPDCId = bs.UndepPDCId)
WHERE Month(DateBounced) in ('.$montharray.') and CreditAccountID in '.$acctid;
}

if(countRows('acctg_2collectsubbounced')>0){
// BOUNCED CHECKS DIRECT FROM COLLECTION RECEIPT 
$sqlalltxns=$sqlalltxns.' UNION ALL SELECT DateBounced, CONCAT("Bounced#CR",cm.CollectNo,"_",cm.CheckNo) AS Expr1, IF(cm.ClientNo<9999,"E","C") AS `BECS`, cm.ClientNo, c.ClientName, concat(cm.CheckNo," Inv",ForChargeInvNo) AS Expr2, 200 AS DebitAccountID, cs.BranchNo,cs.BranchNo as FromBudgetOf, cs.Amount, "DR" AS Expr4, "Bounced", cm.TxnID
FROM (`acctg_01uniclientsalespersonfordep` c right JOIN `acctg_2collectmain` cm ON c.ClientNo = cm.ClientNo) JOIN `acctg_2collectsubbounced` cbs ON  (cm.TxnID = cbs.TxnID)
JOIN `acctg_2collectsub` cs ON  (cm.TxnID = cs.TxnID)
WHERE Month(DateBounced) in ('.$montharray.') and 200 in '.$acctid.'

UNION ALL SELECT DateBounced, CONCAT("Bounced#CR",cm.CollectNo,"_",cm.CheckNo) AS Expr1, IF(cm.ClientNo<9999,"E","C") AS `BECS`, cm.ClientNo, c.ClientName, concat(cm.CheckNo," Inv",ForChargeInvNo) AS Expr2, IFNULL(cbs.CreditAccountID,100) AS CreditAccountID, cs.BranchNo,cs.BranchNo as FromBudgetOf, `Amount`*-1 AS Expr3, "CR" AS Expr4, "Bounced", cm.TxnID
FROM (`acctg_01uniclientsalespersonfordep` c right JOIN `acctg_2collectmain` cm ON c.ClientNo = cm.ClientNo) JOIN `acctg_2collectsubbounced` cbs ON  (cm.TxnID = cbs.TxnID)
JOIN `acctg_2collectsub` cs ON  (cm.TxnID = cs.TxnID)
WHERE Month(DateBounced) in ('.$montharray.') and cbs.CreditAccountID in '.$acctid.'
        
UNION ALL
SELECT DateBounced, CONCAT("Bounced#CR",cm.CollectNo,"_",cm.CheckNo) AS Expr1, IF(cm.ClientNo<9999,"E","C") AS `BECS`, cm.ClientNo, c.ClientName, concat(cm.CheckNo," deduct ",DeductDetails), cbb.CreditAccountID AS DebitAccountID, cbs.BranchNo,cbs.BranchNo as FromBudgetOf, cbs.Amount, "DR" AS Expr3, "Bounced", cm.TxnID
FROM (acctg_2collectmain cm LEFT JOIN `acctg_01uniclientsalespersonfordep` c ON cm.ClientNo = c.ClientNo) JOIN `acctg_2collectsubdeduct` cbs ON  (cm.TxnID = cbs.TxnID)   JOIN `acctg_2collectsubbounced` cbb ON  (cm.TxnID = cbb.TxnID) 
WHERE Month(DateBounced) in ('.$montharray.') and cbb.CreditAccountID in '.$acctid.' 
UNION ALL
SELECT DateBounced, CONCAT("Bounced#CR",cm.CollectNo,"_",cm.CheckNo) AS Expr1, IF(cm.ClientNo<9999,"E","C") AS `BECS`, cm.ClientNo, c.ClientName, concat(cm.CheckNo," deduct ",DeductDetails), cbs.DebitAccountID as CreditAccountID, cbs.BranchNo,cbs.BranchNo as FromBudgetOf, cbs.Amount*-1, "CR" AS Expr3, "Bounced", cm.TxnID
FROM (acctg_2collectmain cm LEFT JOIN `acctg_01uniclientsalespersonfordep` c ON cm.ClientNo = c.ClientNo) JOIN `acctg_2collectsubdeduct` cbs ON  (cm.TxnID = cbs.TxnID)   JOIN `acctg_2collectsubbounced` cbb ON  (cm.TxnID = cbb.TxnID) 
WHERE Month(DateBounced) in ('.$montharray.') and cbs.DebitAccountID in '.$acctid.' ';
}
// INTERBRANCH TRANSFERS
// Transfer OUT - Inventory (actual DR & CR entries, usually DR ARTradeTxfr CR IncomeTxfr/Inventory)
$sqlalltxns=$sqlalltxns.' UNION ALL
SELECT tm.Date, "TxfrOUT" AS Expr1, "B" AS `BECS`, ts.ClientBranchNo,ts.ClientBranchNo, ts.Particulars, ts.DebitAccountID, tm.FromBranchNo,tm.FromBranchNo as FromBudgetOf, ts.Amount, "DR" AS Expr2, "Interbranch", tm.TxnID
FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(tm.Date) in ('.$montharray.') and DebitAccountID in '.$acctid.'
UNION ALL
SELECT tm.Date, "TxfrOUT" AS Expr1, "B" AS `BECS`, ts.ClientBranchNo,ts.ClientBranchNo, ts.Particulars, tm.CreditAccountID, tm.FromBranchNo,tm.FromBranchNo as FromBudgetOf, ts.Amount*-1, "CR" AS Expr2, "Interbranch", tm.TxnID
FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(tm.Date) in ('.$montharray.')  and CreditAccountID in '.$acctid;

// Transfer IN - Inventory 
if (in_array(300,$acctidarray)){ //Inventory account
        // Month of IN = Month of OUT (DR Inventory CR APTradeTxfr)
        $sqlalltxns=$sqlalltxns.' UNION ALL SELECT ts.DateIN, "TxfrIN" AS Expr1, "B" AS `BECS`, tm.FromBranchNo, tm.FromBranchNo, ts.Particulars, 300 AS DRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, ts.Amount, "DR" AS Expr3, "Interbranch", tm.TxnID FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(ts.DateIN) in ('.$montharray.')  And ((ts.DateIN Is Not Null) AND (MONTH(ts.DateIN)=MONTH(tm.Date)) AND (YEAR(ts.DateIN)=YEAR(tm.Date))) and DebitAccountID=204';        
        // DateIN is NOT NULL and Month of IN <> Month of OUT (DR Inventory CR InTransit)
        $sqlalltxns=$sqlalltxns.' UNION ALL SELECT tm.Date, "TxfrIN" AS Expr1, "B" AS `BECS`, tm.FromBranchNo, tm.FromBranchNo, ts.Particulars, 300 AS CRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, ts.Amount*-1, "CR" AS Expr3, "Interbranch", tm.TxnID FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(tm.Date) in ('.$montharray.')  And (ts.DateIN Is Not Null) AND ((MONTH(ts.DateIN)<>MONTH(tm.Date)) OR (YEAR(ts.DateIN)<>YEAR(tm.Date))) and DebitAccountID=204 UNION ALL SELECT ts.DateIN, "TxfrIN" AS Expr1, tm.FromBranchNo, tm.FromBranchNo, ts.Particulars, 300 AS CRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, ts.Amount, "DR" AS Expr3, "Interbranch", tm.TxnID FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(ts.DateIN) in ('.$montharray.')  And (ts.DateIN Is Not Null) AND ((MONTH(ts.DateIN)<>MONTH(tm.Date)) OR (YEAR(ts.DateIN)<>YEAR(tm.Date))) and DebitAccountID=204';
}
if (in_array(330,$acctidarray)){ //In Transit - Incoming account
        // DateIN is NULL (DR InvtInTransitIN CR APTradeTxfr)
        $sqlalltxns=$sqlalltxns.' UNION ALL SELECT tm.Date, "TxfrIN" AS Expr1, "B" AS `BECS`, tm.FromBranchNo, tm.FromBranchNo, ts.Particulars, 330 AS DRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, ts.Amount, "DR" AS Expr3, "Interbranch", tm.TxnID FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(tm.Date) in ('.$montharray.')  And (ts.DateIN Is Null) AND Year(tm.Date)='.$thisyr.' and DebitAccountID=204';
        // DateIN is NOT NULL and Month of IN <> Month of OUT (DR Inventory CR InvtInTransitIN)
        $sqlalltxns=$sqlalltxns.' UNION ALL SELECT tm.Date, "TxfrIN" AS Expr1, "B" AS `BECS`, tm.FromBranchNo, tm.FromBranchNo, ts.Particulars, 330 AS DRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, ts.Amount, "CR" AS Expr3, "Interbranch", tm.TxnID FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(tm.Date) in ('.$montharray.')  And (ts.DateIN Is Not Null) AND ((MONTH(ts.DateIN)<>MONTH(tm.Date)) OR (YEAR(ts.DateIN)<>YEAR(tm.Date))) and DebitAccountID=204 UNION ALL SELECT ts.DateIN, "TxfrIN" AS Expr1, "B" AS `BECS`, tm.FromBranchNo, tm.FromBranchNo, ts.Particulars, 330 AS CRID, ts.ClientBranchNo, ts.ClientBranchNo as FromBudgetOf,ts.Amount*-1, "CR" AS Expr3, "Interbranch", tm.TxnID FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(ts.DateIN) in ('.$montharray.')  And (ts.DateIN Is Not Null) AND ((MONTH(ts.DateIN)<>MONTH(tm.Date)) OR (YEAR(ts.DateIN)<>YEAR(tm.Date))) and DebitAccountID=204';
}
if (in_array(337,$acctidarray)){ //In Transit - Outgoing account
        // DateIN is NULL (DR ARTradeTxfr CR InvtInTransitOUT)
        $sqlalltxns=$sqlalltxns.' UNION ALL SELECT tm.Date, "TxfrOUT" AS Expr1, "B" AS `BECS`, ts.ClientBranchNo, ts.ClientBranchNo, ts.Particulars, 330 AS DRID, tm.FromBranchNo,tm.FromBranchNo as FromBudgetOf, ts.Amount, "DR" AS Expr3, "Interbranch", tm.TxnID FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(tm.Date) in ('.$montharray.')  And (ts.DateIN Is Null) AND Year(tm.Date)='.$thisyr.' and DebitAccountID=204';
        // DateIN is NOT NULL and Month of IN <> Month of OUT (DR InvtInTransitOUT CR SalesInterbranch)
        $sqlalltxns=$sqlalltxns.' UNION ALL SELECT tm.Date, "TxfrIN" AS Expr1, "B" AS `BECS`, tm.FromBranchNo, tm.FromBranchNo, ts.Particulars, 330 AS DRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, ts.Amount, "CR" AS Expr3, "Interbranch", tm.TxnID FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(tm.Date) in ('.$montharray.')  And (ts.DateIN Is Not Null) AND ((MONTH(ts.DateIN)<>MONTH(tm.Date)) OR (YEAR(ts.DateIN)<>YEAR(tm.Date))) and DebitAccountID=204 UNION ALL SELECT ts.DateIN, "TxfrIN" AS Expr1, tm.FromBranchNo, tm.FromBranchNo, ts.Particulars, 330 AS CRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, ts.Amount*-1, "CR" AS Expr3, "Interbranch", tm.TxnID FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(ts.DateIN) in ('.$montharray.')  And (ts.DateIN Is Not Null) AND ((MONTH(ts.DateIN)<>MONTH(tm.Date)) OR (YEAR(ts.DateIN)<>YEAR(tm.Date))) and DebitAccountID=204';
}
if (in_array(404,$acctidarray)){ //APTradeTxfr account
        // Month of IN = Month of OUT (DR Inventory CR APTradeTxfr)
        $sqlalltxns=$sqlalltxns.' UNION ALL SELECT ts.DateIN, "TxfrIN" AS Expr1, "B" AS `BECS`, tm.FromBranchNo, tm.FromBranchNo, ts.Particulars, 404 AS CRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, ts.Amount*-1, "CR" AS Expr3, "Interbranch", tm.TxnID FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(ts.DateIN) in ('.$montharray.')  And ((ts.DateIN Is Not Null) AND (MONTH(ts.DateIN)=MONTH(tm.Date)) AND (YEAR(ts.DateIN)=YEAR(tm.Date))) and DebitAccountID=204';
        // IF DATEIN IS NULL or different months/years as DateOut (DR InTransit CR APTradeTxfr)
        $sqlalltxns=$sqlalltxns.' UNION ALL SELECT tm.Date, "TxfrIN" AS Expr1, "B" AS `BECS`, tm.FromBranchNo, tm.FromBranchNo, ts.Particulars, 404 AS CRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, ts.Amount*-1, "CR" AS Expr3, "Interbranch", tm.TxnID FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(tm.Date) in ('.$montharray.')  And ((ts.DateIN Is Null) OR (MONTH(ts.DateIN)<>MONTH(tm.Date)) OR (YEAR(ts.DateIN)<>YEAR(tm.Date))) and DebitAccountID=204';
}

// Tranfer OUT - Receive Payment (DR PaidViaAcctID, usually BDO  CR to reverse the Debit account for transfer, usually ARTradeTxfr)
$sqlalltxns=$sqlalltxns.' UNION ALL
SELECT ts.DatePaid, "TxfrOUTRecvPayment" AS Expr1, "B" AS `BECS`, ts.ClientBranchNo,ts.ClientBranchNo, ts.Particulars, ts.PaidViaAcctID, tm.FromBranchNo,tm.FromBranchNo as FromBudgetOf, ts.Amount, "DR" AS Expr2, "Interbranch", tm.TxnID
FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(ts.DatePaid) in ('.$montharray.')  And (ts.DatePaid Is Not Null) and DebitAccountID=204 and PaidViaAcctID in '.$acctid.'
UNION ALL
SELECT ts.DatePaid, "TxfrOUTRecvPayment" AS Expr1, "B" AS `BECS`, ts.ClientBranchNo,ts.ClientBranchNo, ts.Particulars, ts.DebitAccountID, tm.FromBranchNo,tm.FromBranchNo as FromBudgetOf, ts.Amount*-1, "CR" AS Expr2, "Interbranch", tm.TxnID
FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(ts.DatePaid) in ('.$montharray.')  And (ts.DatePaid Is Not Null) and DebitAccountID=204 and DebitAccountID in '.$acctid;

// Transfer IN - Give Payment (DR APTradeTxfr  CR PaidViaAcctID)
$sqlalltxns=$sqlalltxns.' UNION ALL SELECT ts.DatePaid, "TxfrINPayment" AS Expr1, "B" AS `BECS`, tm.FromBranchNo, tm.FromBranchNo, ts.Particulars, ts.PaidViaAcctID AS CRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, ts.Amount*-1, "CR" AS Expr3, "Interbranch", tm.TxnID
FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(ts.DatePaid) in ('.$montharray.')  And (ts.DatePaid Is Not Null) and DebitAccountID=204 and PaidViaAcctID in '.$acctid;

if (in_array(404,$acctidarray)){
        $sqlalltxns=$sqlalltxns.' UNION ALL SELECT ts.DatePaid, "TxfrINPayment" AS Expr1, "B" AS `BECS`, tm.FromBranchNo, tm.FromBranchNo, ts.Particulars, 404 AS DRID, ts.ClientBranchNo,ts.ClientBranchNo as FromBudgetOf, ts.Amount, "DR" AS Expr3, "Interbranch", tm.TxnID FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnID = ts.TxnID
WHERE Month(ts.DatePaid) in ('.$montharray.')  And (ts.DatePaid Is Not Null) and DebitAccountID=204 ';
}
?>