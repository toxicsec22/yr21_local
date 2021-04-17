<?php
$thisyr=$currentyr;
// Journal Vouchers
$sql1='SELECT 
        `jvs`.`Date` AS `Date`,
        CONCAT("JVNo ", `jvm`.`JVNo`) AS `ControlNo`, "B" AS `BECS`,
        "0" AS `SuppNo/ClientNo`,
        "-" AS `Supplier/Customer/Branch`,
        `jvs`.`Particulars` AS `Particulars`,
        `jvs`.`DebitAccountID` AS `AccountID`,
        `jvs`.`BranchNo` AS `BranchNo`,
		`jvs`.`FromBudgetOf` AS `FromBudgetOf`,
        `jvs`.`Amount` AS `Amount`,IFNULL(`Forex`,1) AS `FOREX`,IFNULL(`Forex`,1)*`Amount` AS `PHPAmount`,
        "DR" AS `Entry`,
        "JV" AS `w`,
        `jvm`.`JVNo` AS `JVNo`
    FROM
        (`acctg_2jvmain` `jvm`
        JOIN `acctg_2jvsub` `jvs` ON ((`jvm`.`JVNo` = `jvs`.`JVNo`))) where '.$condition.'
UNION ALL SELECT 
        `jvs`.`Date` AS `Date`,
        CONCAT("JVNo ", `jvm`.`JVNo`) AS `ControlNo`, "B" AS `BECS`,
        "0" AS `SuppNo/ClientNo`,
        "" AS `Supplier/Customer/Branch`,
        `jvs`.`Particulars` AS `Particulars`,
        `jvs`.`CreditAccountID` AS `CreditAccountID`,
        `jvs`.`BranchNo` AS `BranchNo`,
		`jvs`.`FromBudgetOf` AS `FromBudgetOf`,
        (`jvs`.`Amount` * -(1)) AS `Amount`,IFNULL(`Forex`,1) AS `FOREX`,IFNULL(`Forex`,1)*`Amount`*-1 AS `PHPAmount`,
        "CR" AS `Entry`,
        "JV" AS `w`,
        `jvm`.`JVNo` AS `JVNo`
    FROM
        (`acctg_2jvmain` `jvm`
        JOIN `acctg_2jvsub` `jvs` ON ((`jvm`.`JVNo` = `jvs`.`JVNo`)))  where '.$condition;    


// ASSETS


// DEPRECIATION
$sql1=$sql1.' UNION ALL SELECT d.DeprDate, concat("DeprID ",d.DeprID) AS ControlNo, "B" AS `BECS`, 0 as `SuppNo/ClientNo`,"-" as `Supplier/Customer/Branch`, AssetDesc, d.DeprAccountID as AccountID, BranchNo,BranchNo AS `FromBudgetOf`, d.Amount, 1 AS Forex, d.Amount AS PHPAmount, "DR" as Entry, "Depreciation" as w, a.AssetID FROM acctg_1assets a JOIN acctg_1assetsdepr d ON a.AssetID=d.AssetID WHERE '.$conditiondepr.' 
UNION ALL
SELECT d.DeprDate, concat("DeprID ",d.DeprID) AS ControlNo, "B" AS `BECS`, 0 as `SuppNo/ClientNo`,"-" as `Supplier/Customer/Branch`, AssetDesc, ca.AccountID, BranchNo,BranchNo AS `FromBudgetOf`, d.Amount*-1, 1 AS Forex, d.Amount*-1 AS PHPAmount, "CR" as Entry, "AssetandDepr" as w, a.AssetID FROM acctg_1assets a JOIN acctg_1assetsdepr d ON a.AssetID=d.AssetID JOIN `acctg_1chartofaccounts` ca ON ca.AccumDepAcctOf=d.DeprAccountID WHERE '.$conditiondepr;

// APPLICATION OF PREPAID EXPENSES
$sql1=$sql1.' UNION ALL SELECT d.AmortDate, concat("PrExp ",d.AmortID) AS ControlNo, "B" AS `BECS`, 0 as `SuppNo/ClientNo`,"-" as `Supplier/Customer/Branch`, PrepaidDesc, d.ExpenseAccountID as AccountID, BranchNo,BranchNo AS `FromBudgetOf`, d.Amount, 1 AS Forex, d.Amount AS PHPAmount, "DR" as Entry, "PrepaidExpense" as w, a.PrepaidID FROM acctg_2prepaid a JOIN acctg_2prepaidamort d ON a.PrepaidID=d.PrepaidID WHERE '.$conditionprepd.' 
UNION ALL
SELECT d.AmortDate, concat("PrExp ",d.AmortID) AS ControlNo, "B" AS `BECS`, 0 as `SuppNo/ClientNo`,"-" as `Supplier/Customer/Branch`, PrepaidDesc, PrepaidAccountID as AccountID, BranchNo,BranchNo AS `FromBudgetOf`, d.Amount*-1, 1 AS Forex, d.Amount*-1 AS PHPAmount, "CR" as Entry, "PrepaidExpense" as w, a.PrepaidID FROM acctg_2prepaid a JOIN acctg_2prepaidamort d ON a.PrepaidID=d.PrepaidID WHERE '.$conditionprepd;
        
// PURCHASES
$sql1=$sql1.' UNION ALL SELECT 
        `pm`.`Date` AS `Date`,
        CONCAT("Supp.Inv# ", `pm`.`SupplierInv`) AS `concat("Supp.Inv# ",SupplierInv)`, "S" AS `BECS`,
        `pm`.`SupplierNo` AS `SupplierNo`,
        CONCAT(SupplierName, " TIN#",s.TIN, " ", s.Address),
        CONCAT("Inv.Date ", `pm`.`DateofInv`) AS `concat("Inv.Date ",DateofInv)`,
        `ps`.`DebitAccountID` AS `DebitAccountID`,
        `pm`.`BranchNo` AS `BranchNo`,
		`FromBudgetOf`,
        `ps`.`Amount` AS `Amount`, IFNULL(`Forex`,1) AS `FOREX`,IFNULL(`Forex`,1)*`Amount` AS `PHPAmount`,
        "DR" AS `DR`,
        "Purchase" AS `Purchases`,
        `pm`.`TxnID` AS `TxnID`
    FROM
        ((`acctg_2purchasemain` `pm`
        JOIN `acctg_2purchasesub` `ps` ON ((`pm`.`TxnID` = `ps`.`TxnID`)))
        JOIN `1suppliers` `s` ON ((`s`.`SupplierNo` = `pm`.`SupplierNo`)))  where '.$condition.'

        UNION ALL SELECT 
        `pm`.`Date` AS `Date`,
        CONCAT("Supp.Inv# ", `pm`.`SupplierInv`) AS `concat("Supp.Inv# ",SupplierInv)`, "S" AS `BECS`,
        `pm`.`SupplierNo` AS `SupplierNo`,
        `s`.`SupplierName` AS `SupplierName`,
        CONCAT("Inv.Date ", `pm`.`DateofInv`) AS `concat("Inv.Date ",DateofInv)`,
        `pm`.`CreditAccountID` AS `CreditAccountID`,
        `pm`.`BranchNo` AS `BranchNo`,
		`FromBudgetOf`,
        (`ps`.`Amount` * -(1)) AS `Amount`, IFNULL(`Forex`,1) AS `FOREX`,IFNULL(`Forex`,1)*`Amount`*-1 AS `PHPAmount`,
        "CR" AS `CR`,
        "Purchase" AS `Purchases`,
        `pm`.`TxnID` AS `TxnID`
    FROM
        ((`acctg_2purchasemain` `pm`
        JOIN `acctg_2purchasesub` `ps` ON ((`pm`.`TxnID` = `ps`.`TxnID`)))
        JOIN `1suppliers` `s` ON ((`s`.`SupplierNo` = `pm`.`SupplierNo`)))  where '.$condition;
        
// Check VOUCHERS
$sql1=$sql1.' UNION ALL SELECT 
        `vchm`.`Date` AS `Date`,
        CONCAT("CVNo ", `vchm`.`CVNo`) AS `concat("CVNo ",vchm.CVNo)`,IF(PayeeNo>1000,"E","S") AS `BECS`, 
        `vchm`.`PayeeNo` AS `SuppNo/ClientNo`,
        `vchm`.`Payee` AS `Payee`,
        concat(ifnull(vchs.Particulars,""),"",ifnull(vchs.ForInvoiceNo,"")," Chk# ",vchm.CheckNo, 
if(isnull(vchs.TIN),"", concat(" TIN#",vchs.TIN, " ", t.CompanyName," ", t.Address))) AS `Particulars`,
        `vchs`.`DebitAccountID` AS `DebitAccountID`,
        `vchs`.`BranchNo` AS `BranchNo`,
		`vchs`.`FromBudgetOf` AS `FromBudgetOf`,
        `vchs`.`Amount` AS `Amount`, IFNULL(`Forex`,1) AS `FOREX`,IFNULL(`Forex`,1)*`Amount` AS `PHPAmount`,
        "DR" AS `DR`,
        "CV" AS `CV`,
        `vchm`.`CVNo` AS `CVNo`
    FROM
        (`acctg_2cvmain` `vchm`
        JOIN `'.$thisyr.'_1rtc`.`acctg_2cvsub` `vchs` ON ((`vchm`.`CVNo` = `vchs`.`CVNo`))) LEFT JOIN `gen_info_1tinforexpenses` t on t.TIN=vchs.TIN where '.$condition.'
        UNION ALL SELECT 
        `vchm`.`Date` AS `Date`,
        CONCAT("CVNo ", `vchm`.`CVNo`) AS `concat("CVNo ",vchm.CVNo)`,IF(PayeeNo>1000,"E","S") AS `BECS`, 
        `vchm`.`PayeeNo` AS `PayeeNo`,
        `vchm`.`Payee` AS `Payee`,
        concat(ifnull(vchs.Particulars,"")," ",ifnull(vchs.ForInvoiceNo,"")," Chk# ",vchm.CheckNo, 
if(isnull(vchs.TIN),"", concat(" TIN#",vchs.TIN, " ", t.CompanyName," ", t.Address))) AS `Name_exp_115`,
        `vchm`.`CreditAccountID` AS `CreditAccountID`,
        `vchs`.`BranchNo` AS `BranchNo`,
		`vchs`.`FromBudgetOf` AS `FromBudgetOf`,
        (`vchs`.`Amount` * -(1)) AS `Amount`,IFNULL(`Forex`,1) AS `FOREX`,IFNULL(`Forex`,1)*`Amount` AS `PHPAmount`,
        "CR" AS `CR`,
        "CV" AS `CV`,
        `vchm`.`CVNo` AS `CVNo`
    FROM
        (`acctg_2cvmain` `vchm`
        JOIN `'.$thisyr.'_1rtc`.`acctg_2cvsub` `vchs` ON ((`vchm`.`CVNo` = `vchs`.`CVNo`))) LEFT JOIN `gen_info_1tinforexpenses` t on t.TIN=vchs.TIN  where '.$condition;

// SALES 
$sql1=$sql1.' UNION ALL SELECT 
        `sm`.`Date` AS `Date`,
        "Sale" AS `Expr1`, IF(ss.ClientNo<9999,"E","C") AS `BECS`,
        `ss`.`ClientNo` AS `ClientNo`,
        `c`.`ClientName` AS `ClientName`,
        `ss`.`Particulars` AS `Particulars`,
        `ss`.`DebitAccountID` AS `DebitAccountID`,
        `sm`.`BranchNo` AS `BranchNo`,
		`sm`.`BranchNo` AS `FromBudgetOf`,
        `ss`.`Amount` AS `Amount`, 1 AS Forex, ss.Amount AS PHPAmount, 
        "DR" AS `Expr2`,
        "Sale" AS `Sale`,
        `sm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2salemain` `sm`
        JOIN (`acctg_2salesub` `ss`
        LEFT JOIN `acctg_01uniclientsalespersonfordep` `c` ON ((`ss`.`ClientNo` = `c`.`ClientNo`))) ON ((`sm`.`TxnID` = `ss`.`TxnID`)))  where '.$condition
        .' UNION ALL SELECT 
        `sm`.`Date` AS `Date`,
        "Sale" AS `Expr1`, IF(ss.ClientNo<9999,"E","C") AS `BECS`,
        `ss`.`ClientNo` AS `ClientNo`,
        `c`.`ClientName` AS `ClientName`,
        `ss`.`Particulars` AS `Particulars`,
        `ss`.`CreditAccountID` AS `CreditAccountID`,
        `sm`.`BranchNo` AS `BranchNo`,
		`sm`.`BranchNo` AS `FromBudgetOf`,
        (`ss`.`Amount` * -(1)) AS `Expr2`, 1 AS Forex, ss.Amount*-1 AS PHPAmount, 
        "CR" AS `Expr3`,
        "Sale" AS `Sale`,
        `sm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2salemain` `sm`
        JOIN (`acctg_2salesub` `ss`
        LEFT JOIN `acctg_01uniclientsalespersonfordep` `c` ON ((`ss`.`ClientNo` = `c`.`ClientNo`))) ON ((`sm`.`TxnID` = `ss`.`TxnID`)))  where '.$condition.' ';

// COLLECTIONS
$sql1=$sql1.' UNION ALL SELECT `orm`.`Date`, CONCAT("Collect", `orm`.`CollectNo`) AS `Expr1`,  IF(orm.ClientNo<9999,"E","C") AS `BECS`,`orm`.`ClientNo`, `c`.`ClientName`,
        CONCAT(`orm`.`CollectNo`, " Inv", `ors`.`ForChargeInvNo`, "/", `orm`.`Type`, "/", IFNULL(`orm`.`CheckNo`,"")) AS `Expr2`, `orm`.`DebitAccountID` AS `DebitAccountID`,
        `ors`.`BranchNo`,`ors`.`BranchNo` AS `FromBudgetOf`, `ors`.`Amount` ,  1 AS Forex, ors.Amount AS PHPAmount,   "DR" AS `Expr3`,  "Collect" AS `OR`, `orm`.`TxnID`
    FROM
        ((`acctg_2collectmain` `orm`
        LEFT JOIN `acctg_01uniclientsalespersonfordep` `c` ON ((`orm`.`ClientNo` = `c`.`ClientNo`)))
        JOIN `acctg_2collectsub` `ors` ON ((`orm`.`TxnID` = `ors`.`TxnID`)))  where '.$condition.'

    UNION ALL SELECT 
        `orm`.`Date` AS `Date`,
        CONCAT("Collect", `orm`.`CollectNo`) AS `Expr1`, IF(orm.ClientNo<9999,"E","C") AS `BECS`,
        `orm`.`ClientNo` AS `ClientNo`,
        `c`.`ClientName` AS `ClientName`,
        DeductDetails,
        `orm`.`DebitAccountID` AS `CreditAccountID`,
        `ors`.`BranchNo` AS `BranchNo`,
		`ors`.`BranchNo` AS `FromBudgetOf`,
        `ors`.`Amount`*-1 AS `Amount`,  1 AS Forex, ors.Amount*-1 AS PHPAmount, 
        "CR" AS `Expr3`,
        "Collect" AS `OR`,
        `orm`.`TxnID` AS `TxnID`
    FROM
        ((`acctg_2collectmain` `orm`
        LEFT JOIN `acctg_01uniclientsalespersonfordep` `c` ON ((`orm`.`ClientNo` = `c`.`ClientNo`)))
        JOIN `acctg_2collectsubdeduct` `ors` ON ((`orm`.`TxnID` = `ors`.`TxnID`)))  where '.$condition.'

        UNION ALL SELECT 
        `orm`.`Date` AS `Date`,
        CONCAT("Collect", `orm`.`CollectNo`) AS `Expr1`, IF(orm.ClientNo<9999,"E","C") AS `BECS`,
        `orm`.`ClientNo` AS `ClientNo`,
        `c`.`ClientName` AS `ClientName`,
        CONCAT(`orm`.`CollectNo`,
                " Inv",
                `ors`.`ForChargeInvNo`,
                "/",
                `orm`.`Type`,
                "/",
                IFNULL(`orm`.`CheckNo`,"")) AS `Expr2`,
        `ors`.`CreditAccountID` AS `CreditAccountID`,
        `ors`.`BranchNo` AS `BranchNo`,
		`ors`.`BranchNo` AS `FromBudgetOf`,
        (`ors`.`Amount` * -(1)) AS `Expr3`,  1 AS Forex, ors.Amount*-1 AS PHPAmount, 
        "CR" AS `Expr4`,
        "Collect" AS `OR`,
        `orm`.`TxnID` AS `TxnID`
    FROM
        ((`acctg_2collectmain` `orm`
        LEFT JOIN `acctg_01uniclientsalespersonfordep` `c` ON ((`orm`.`ClientNo` = `c`.`ClientNo`)))
        JOIN `acctg_2collectsub` `ors` ON ((`orm`.`TxnID` = `ors`.`TxnID`)))  where '.$condition.'

        UNION ALL SELECT 
        `orm`.`Date` AS `Date`,
        CONCAT("Collect", `orm`.`CollectNo`) AS `Expr1`, IF(orm.ClientNo<9999,"E","C") AS `BECS`,
        `orm`.`ClientNo` AS `ClientNo`,
        `c`.`ClientName` AS `ClientName`,DeductDetails,
        `ors`.`DebitAccountID` AS `DebitAccountID`,
        `ors`.`BranchNo` AS `BranchNo`,
		`ors`.`BranchNo` AS `FromBudgetOf`,
        (`ors`.`Amount`) AS `Expr3`,  1 AS Forex, ors.Amount AS PHPAmount, 
        "DR" AS `Expr4`,
        "Collect" AS `OR`,
        `orm`.`TxnID` AS `TxnID`
    FROM
        ((`acctg_2collectmain` `orm`
        LEFT JOIN `acctg_01uniclientsalespersonfordep` `c` ON ((`orm`.`ClientNo` = `c`.`ClientNo`)))
        JOIN `acctg_2collectsubdeduct` `ors` ON ((`orm`.`TxnID` = `ors`.`TxnID`)))  where '.$condition;
        
// DEPOSITS and ENCASHMENTS
$sql1=$sql1.' UNION ALL SELECT 
        `depm`.`Date` AS `Date`,
        CONCAT("Dep# ", `depm`.`DepositNo`) AS `Expr1`, IF(deps.ClientNo<9999,"E","C") AS `BECS`,
        `deps`.`ClientNo` AS `SuppNo/ClientNo`,
        CONCAT(`c`.`ClientName`,
                " ",
                IFNULL(`deps`.`DepDetails`, " ")) AS `Expr2`,
        CONCAT(`deps`.`ForChargeInvNo`,
                "/",
                `deps`.`Type`,
                "/",
                IFNULL(`deps`.`CheckNo`,"")) AS `Expr3`,
        `depm`.`DebitAccountID` AS `DebitAccountID`,
        `deps`.`BranchNo` AS `BranchNo`,
		`deps`.`BranchNo` AS `FromBudgetOf`,
        `deps`.`Amount` AS `Amount`, IFNULL(`Forex`,1) AS `Forex`,IFNULL(`Forex`,1)*`Amount` AS `PHPAmount`,
        "DR" AS `DR`,
        "Deposit" AS `Deposit`,
        `depm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2depositmain` `depm`
        JOIN (`acctg_2depositsub` `deps`
        LEFT JOIN `acctg_01uniclientsalespersonfordep` `c` ON ((`deps`.`ClientNo` = `c`.`ClientNo`))) ON ((`depm`.`TxnID` = `deps`.`TxnID`)))  where '.$condition.'
    UNION ALL SELECT 
        `depm`.`Date` AS `Date`,
        CONCAT("Encash# ", `depm`.`DepositNo`) AS `Expr1`,  "B" AS `BECS`,
        "0" AS `SuppNo/ClientNo`,
        CONCAT(depe.EncashDetails,if(isnull(depe.TIN),"", concat(" ", t.CompanyName))) AS `EncashDetails`,
        CONCAT(if(isnull(ApprovalNo),"",concat("approval ",`ApprovalNo`)),if(isnull(depe.TIN),"", concat(" TIN#",depe.TIN, " ", t.Address))) AS `Expr2`,
        `depe`.`DebitAccountID` AS `DebitAccountID`,
        `depe`.`BranchNo` AS `BranchNo`,
		`depe`.`FromBudgetOf` AS `FromBudgetOf`,
        `depe`.`Amount` AS `Amount`, 1 AS `Forex`,`Amount` AS `PHPAmount`,
        "DR" AS `Expr4`,
        "Deposit" AS `Deposit`,
        `depm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2depositmain` `depm`
        JOIN `acctg_2depencashsub` `depe` ON ((`depm`.`TxnID` = `depe`.`TxnID`))) LEFT JOIN `gen_info_1tinforexpenses` t on t.TIN=depe.TIN where '.$condition.'
    UNION ALL SELECT 
        `depm`.`Date` AS `Date`,
        CONCAT("Dep# ", `depm`.`DepositNo`) AS `Expr1`,  IF(deps.ClientNo<9999,"E","C") AS `BECS`,
        `deps`.`ClientNo` AS `ClientNo`,
        CONCAT(`c`.`ClientName`,
                " ",
                IFNULL(`deps`.`DepDetails`, " ")) AS `Expr2`,
        CONCAT(`deps`.`ForChargeInvNo`,
                "/",
                `deps`.`Type`,
                "/",
                IFNULL(`deps`.`CheckNo`,"")) AS `Expr3`,
        `deps`.`CreditAccountID` AS `CreditAccountID`,
        `deps`.`BranchNo` AS `BranchNo`,
		`deps`.`BranchNo` AS `FromBudgetOf`,
        (`deps`.`Amount` * -(1)) AS `Expr4`, IFNULL(`Forex`,1) AS `Forex`,IFNULL(`Forex`,1)*`Amount`*-1 AS `PHPAmount`,
        "CR" AS `Expr5`,
        "Deposit" AS `Deposit`,
        `depm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2depositmain` `depm`
        JOIN (`acctg_2depositsub` `deps`
        LEFT JOIN `acctg_01uniclientsalespersonfordep` `c` ON ((`deps`.`ClientNo` = `c`.`ClientNo`))) ON ((`depm`.`TxnID` = `deps`.`TxnID`)))  where '.$condition.'
    UNION ALL SELECT 
        `depm`.`Date` AS `Date`,
        CONCAT("Encash# ", `depm`.`DepositNo`) AS `Expr1`,  "B" AS `BECS`,
        "0" AS `SuppNo/ClientNo`,
        CONCAT(depe.EncashDetails,if(isnull(depe.TIN),"", concat(" ", t.CompanyName))) AS `EncashDetails`,
        CONCAT(if(isnull(ApprovalNo),"",concat("approval ",`ApprovalNo`)),if(isnull(depe.TIN),"", concat(" TIN#",depe.TIN, " ", t.Address))) AS `Expr2`,
        `depm`.`DebitAccountID` AS `CreditAcctID`,
        `depe`.`BranchNo` AS `BranchNo`,
		`depe`.`FromBudgetOf` AS `FromBudgetOf`,
        (`depe`.`Amount` * -(1)) AS `Expr3`, 1 AS `Forex`,`Amount`*-1 AS `PHPAmount`,
        "CR" AS `Expr4`,
        "Deposit" AS `Deposit`,
        `depm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2depositmain` `depm`
        JOIN `acctg_2depencashsub` `depe` ON ((`depm`.`TxnID` = `depe`.`TxnID`))) LEFT JOIN `gen_info_1tinforexpenses` t on t.TIN=depe.TIN where '.$condition;
        

// BOUNCED CHECKS FROM LAST PERIOD
$sql1=$sql1.' UNION ALL SELECT bs.DateBounced, CONCAT("Bounced#CR",bm.CRNo,"_",bm.PDCNo), IF(bm.ClientNo<9999,"E","C") AS `BECS`, bm.ClientNo, c.ClientName, PDCNo, 200 AS DebitAccountID, BranchNo,BranchNo AS `FromBudgetOf`, AmountofPDC, 1 AS `Forex`,`AmountofPDC`AS `PHPAmount`, "DR" AS Expr4, "Bounced", bm.UndepPDCId
FROM (`acctg_01uniclientsalespersonfordep` c right JOIN `acctg_3undepositedpdcfromlastperiod` bm ON c.ClientNo = bm.ClientNo) INNER JOIN `acctg_3undepositedpdcfromlastperiodbounced` bs ON  (bm.UndepPDCId = bs.UndepPDCId)
WHERE '.$conditionbounced.' 
UNION ALL SELECT bs.DateBounced, CONCAT("Bounced#CR",bm.CRNo,"_",bm.PDCNo), IF(bm.ClientNo<9999,"E","C") AS `BECS`, bm.ClientNo, c.ClientName, PDCNo, CreditAccountID, BranchNo,BranchNo AS `FromBudgetOf`, `AmountofPDC`*-1 AS Expr3, 1 AS `Forex`,`AmountofPDC`*-1 AS `PHPAmount`, "CR" AS Expr4, "Bounced", bm.UndepPDCId
FROM (`acctg_01uniclientsalespersonfordep` c right JOIN `acctg_3undepositedpdcfromlastperiod` bm ON c.ClientNo = bm.ClientNo) INNER JOIN `acctg_3undepositedpdcfromlastperiodbounced` bs ON  (bm.UndepPDCId = bs.UndepPDCId)
WHERE '.$conditionbounced;




// BOUNCED CHECKS FROM CR
$sql1=$sql1.' UNION ALL SELECT 
        `DateBounced` AS `Date`,
        CONCAT("Bounced#CR",cm.CollectNo,"_",cm.CheckNo) AS `Expr1`, IF(cm.ClientNo<9999,"E","C") AS `BECS`,
        `cm`.`ClientNo` AS `ClientNo`,
        `c`.`ClientName` AS `ClientName`,
        CONCAT(`cm`.`CheckNo`,
                " Inv",
                `cs`.`ForChargeInvNo`) AS `Expr2`,
        200 AS `DebitAccountID`,
        `cs`.`BranchNo` AS `BranchNo`,
		`cs`.`BranchNo` AS `FromBudgetOf`,
        `cs`.`Amount` AS `Amount`, 1 AS `Forex`,`Amount` AS `PHPAmount`,
        "DR" AS `Expr4`,
        "Bounced" AS `Bounced`,
        `cm`.`TxnID` AS `TxnID`
    FROM
        `acctg_2collectmain` `cm` JOIN  `acctg_2collectsub` `cs` ON `cm`.`TxnID` = `cs`.`TxnID` JOIN `acctg_2collectsubbounced` `cbs` ON `cm`.`TxnID` = `cbs`.`TxnID`
        JOIN `1clients` `c` ON `c`.`ClientNo` = `cm`.`ClientNo`  WHERE '.$conditionbounced.' 
    UNION ALL SELECT 
        `DateBounced` AS `Date`,
        CONCAT("Bounced#CR",cm.CollectNo,"_",cm.CheckNo) AS `Expr1`, IF(cm.ClientNo<9999,"E","C") AS `BECS`,
        `cm`.`ClientNo` AS `ClientNo`,
        `c`.`ClientName` AS `ClientName`,
        CONCAT(`cm`.`CheckNo`,
                " Inv",
                `cs`.`ForChargeInvNo`) AS `Expr2`,
        `cbs`.`CreditAccountID` AS `CreditAccountID`,
        `cs`.`BranchNo` AS `BranchNo`,
		`cs`.`BranchNo` AS `FromBudgetOf`,
        (`cs`.`Amount` * -(1)) AS `Expr3`, 1 AS `Forex`,`Amount`*-1 AS `PHPAmount`,
        "CR" AS `Expr4`,
        "Bounced" AS `Bounced`,
        `cm`.`TxnID` AS `TxnID`
    FROM
    `acctg_2collectmain` `cm` JOIN  `acctg_2collectsub` `cs` ON `cm`.`TxnID` = `cs`.`TxnID` JOIN `acctg_2collectsubbounced` `cbs` ON `cm`.`TxnID` = `cbs`.`TxnID`
        JOIN `'.$thisyr.'_1rtc`.`1clients` `c` ON `c`.`ClientNo` = `cm`.`ClientNo`  WHERE '.$conditionbounced.'

     UNION ALL
SELECT DateBounced, CONCAT("Bounced#CR",cm.CollectNo,"_",cm.CheckNo) AS Expr1,  IF(cm.ClientNo<9999,"E","C") AS `BECS`,cm.ClientNo, c.ClientName, concat(cm.CheckNo," deduct ",DeductDetails), cbb.CreditAccountID AS DebitAccountID, cbs.BranchNo,cbs.BranchNo AS `FromBudgetOf`, cbs.Amount, 1 AS `Forex`,`Amount` AS `PHPAmount`, "DR" AS Expr3, "Bounced", cm.TxnID
FROM (acctg_2collectmain cm LEFT JOIN `acctg_01uniclientsalespersonfordep` c ON cm.ClientNo = c.ClientNo) JOIN `acctg_2collectsubdeduct` cbs ON  (cm.TxnID = cbs.TxnID)   JOIN `acctg_2collectsubbounced` cbb ON  (cm.TxnID = cbb.TxnID) 
WHERE  '.$conditionbounced. '
    UNION ALL
SELECT DateBounced, CONCAT("Bounced#CR",cm.CollectNo,"_",cm.CheckNo) AS Expr1,  IF(cm.ClientNo<9999,"E","C") AS `BECS`,cm.ClientNo, c.ClientName, concat(cm.CheckNo," deduct ",DeductDetails), cbs.DebitAccountID as CreditAccountID, cbs.BranchNo,cbs.BranchNo AS `FromBudgetOf`, cbs.Amount*-1, 1 AS `Forex`,`Amount`*-1 AS `PHPAmount`, "CR" AS Expr3, "Bounced", cm.TxnID
FROM (acctg_2collectmain cm LEFT JOIN `acctg_01uniclientsalespersonfordep` c ON cm.ClientNo = c.ClientNo) JOIN `acctg_2collectsubdeduct` cbs ON  (cm.TxnID = cbs.TxnID)   JOIN `acctg_2collectsubbounced` cbb ON  (cm.TxnID = cbb.TxnID) 
WHERE '.$conditionbounced;

// INTERBRANCH TRANSFERS
// Transfer OUT - Inventory (actual DR & CR entries, usually DR ARTradeTxfr CR IncomeTxfr/Inventory)
$sql1=$sql1.' UNION ALL SELECT 
        `tm`.`Date` AS `Date`,
        "TxfrOUT" AS `Expr1`, "B" AS `BECS`,
        `ts`.`ClientBranchNo` AS `SuppNo/ClientNo`,
        `ts`.`ClientBranchNo` AS `ClientBranchNo`,
        `ts`.`Particulars` AS `Particulars`,
        `ts`.`DebitAccountID` AS `DebitAccountID`,
        `tm`.`FromBranchNo` AS `FromBranchNo`,
		`tm`.`FromBranchNo` AS `FromBudgetOf`,
        `ts`.`Amount` AS `Amount`, 1 AS `Forex`,`Amount` AS `PHPAmount`,
        "DR" AS `Expr2`,
        "Interbranch" AS `Interbranch`,
        `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm`
        JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
        JOIN `'.$thisyr.'_1rtc`.`1branches` `b` ON b.BranchNo=tm.FromBranchNo
        WHERE '.$condition.'
    UNION ALL SELECT 
        `tm`.`Date` AS `Date`,
        "TxfrOUT" AS `Expr1`, "B" AS `BECS`,
        `ts`.`ClientBranchNo` AS `SuppNo/ClientNo`,
        `ts`.`ClientBranchNo` AS `ClientBranchNo`,
        `ts`.`Particulars` AS `Particulars`,
        `tm`.`CreditAccountID` AS `CreditAccountID`,
        `tm`.`FromBranchNo` AS `FromBranchNo`,
		`tm`.`FromBranchNo` AS `FromBudgetOf`,
        (`ts`.`Amount` * -(1)) AS `Amount`, 1 AS `Forex`,`Amount`*-1 AS `PHPAmount`,
        "CR" AS `Expr2`,
        "Interbranch" AS `Interbranch`,
        `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm`
        JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
        JOIN `1branches` `b` ON b.BranchNo=tm.FromBranchNo
        WHERE '.$condition;

// Transfer IN - Inventory 
        // Month of IN = Month of OUT (DR Inventory CR APTradeTxfr)
    $wherecondition=' WHERE (`ts`.`DebitAccountID`=204) AND
        (`ts`.`DateIN` IS NOT NULL)  AND (MONTH(ts.DateIN)=MONTH(tm.Date)) AND (YEAR(ts.DateIN)=YEAR(tm.Date))  and '.$conditionin;
    $sql1=$sql1.' UNION ALL SELECT 
        `ts`.`DateIN` AS `DateIN`, "TxfrIN" AS `Expr1`,  "B" AS `BECS`,`tm`.`FromBranchNo` AS `SuppNo/ClientNo`, `tm`.`FromBranchNo` AS `FromBranchNo`,`ts`.`Particulars` AS `Particulars`,
        300 AS `DRID`, `ts`.`ClientBranchNo` AS `ClientBranchNo`, `ts`.`ClientBranchNo` AS `FromBudgetOf`, `ts`.`Amount` AS `Amount`, 1 AS `Forex`,`Amount` AS `PHPAmount`,"DR" AS `Expr3`, "Interbranch" AS `Interbranch`, `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm` JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) '.$wherecondition.'
    UNION ALL SELECT 
        `ts`.`DateIN` AS `DateIN`, "TxfrIN" AS `Expr1`,  "B" AS `BECS`,`tm`.`FromBranchNo` AS `SuppNo/ClientNo`, `tm`.`FromBranchNo` AS `FromBranchNo`, `ts`.`Particulars` AS `Particulars`,
        404 AS `CRID`, `ts`.`ClientBranchNo` AS `ClientBranchNo`,`ts`.`ClientBranchNo` AS `FromBudgetOf`, (`ts`.`Amount` * -(1)) AS `Amount`, 1 AS `Forex`,`Amount`*-1 AS `PHPAmount`, "CR" AS `Expr3`, "Interbranch" AS `Interbranch`, `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm`
        JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
    '.$wherecondition;
        
    // DateIN is NULL (DR InTransit CR APTradeTxfr)
    $wherecondition=' WHERE (`ts`.`DebitAccountID`=204) AND
        '.$condition.' AND ((`ts`.`DateIN` IS NULL)) AND YEAR(Date)='.$thisyr.' '; 
    $sql1=$sql1.' UNION ALL SELECT 
        `tm`.`Date` AS `Date`,
        "TxfrIN" AS `Expr1`, "B" AS `BECS`,
        `tm`.`FromBranchNo` AS `SuppNo/ClientNo`,
        `tm`.`FromBranchNo` AS `FromBranchNo`,
        `ts`.`Particulars` AS `Particulars`,
        330 AS `DRID`,
        `ts`.`ClientBranchNo` AS `ClientBranchNo`,
		`ts`.`ClientBranchNo` AS `FromBudgetOf`,
        (`ts`.`Amount`) AS `Amount`, 1 AS `Forex`,`Amount` AS `PHPAmount`,
        "DR" AS `Expr3`,
        "Interbranch" AS `Interbranch`,
        `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm`
        JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
    '.$wherecondition.'
        UNION ALL SELECT 
        `tm`.`Date` AS `Date`,
        "TxfrIN" AS `Expr1`, "B" AS `BECS`,
        `tm`.`FromBranchNo` AS `SuppNo/ClientNo`,
        `tm`.`FromBranchNo` AS `FromBranchNo`,
        `ts`.`Particulars` AS `Particulars`,
        404 AS `CRID`,
        `ts`.`ClientBranchNo` AS `ClientBranchNo`,
		`ts`.`ClientBranchNo` AS `FromBudgetOf`,
        (`ts`.`Amount` * -(1)) AS `Amount`, 1 AS `Forex`,`Amount`*-1 AS `PHPAmount`,
        "CR" AS `Expr3`,
        "Interbranch" AS `Interbranch`,
        `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm`
        JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
        '.$wherecondition;
        
        // DateIN is NOT NULL and Month of IN <> Month of OUT (DR Inventory CR InTransit)
    $wherecondition=' WHERE (`ts`.`DebitAccountID`=204) AND
        (`ts`.`DateIN` IS NOT NULL)  AND ((MONTH(ts.DateIN)<>MONTH(tm.Date)) OR (YEAR(ts.DateIN)<>YEAR(tm.Date))) AND '.$conditionin;
    $sql1=$sql1.' UNION ALL SELECT 
        `ts`.`DateIN` AS `DateIN`, "TxfrIN" AS `Expr1`, "B" AS `BECS`, `tm`.`FromBranchNo` AS `SuppNo/ClientNo`, `tm`.`FromBranchNo` AS `FromBranchNo`, `ts`.`Particulars` AS `Particulars`,
        300 AS `DRID`, `ts`.`ClientBranchNo` AS `ClientBranchNo`, `ts`.`ClientBranchNo` AS `FromBudgetOf`,`ts`.`Amount` AS `Amount`, 1 AS `Forex`,`Amount` AS `PHPAmount`,  "DR" AS `Expr3`, "Interbranch" AS `Interbranch`, `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm` JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) '
    .$wherecondition.'
        UNION ALL SELECT 
        `ts`.`DateIN` AS `DateIN`, "TxfrIN" AS `Expr1`, "B" AS `BECS`, `tm`.`FromBranchNo` AS `SuppNo/ClientNo`, `tm`.`FromBranchNo` AS `FromBranchNo`,`ts`.`Particulars` AS `Particulars`,
        330 AS `CRID`, `ts`.`ClientBranchNo` AS `ClientBranchNo`,`ts`.`ClientBranchNo` AS `FromBudgetOf`,  (`ts`.`Amount`*-1) AS `Amount`, 1 AS `Forex`,`Amount`*-1 AS `PHPAmount`, "CR" AS `Expr3`, "Interbranch" AS `Interbranch`, `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm` JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
     '.$wherecondition;
                
    // DateIN is NOT NULL, has different month/year vs. DateOut (DR InTransit CR APTradeTxfr)
    $wherecondition=' WHERE (`ts`.`DebitAccountID`=204) AND '.$condition.' AND ((`ts`.`DateIN` IS NOT NULL)  AND ((MONTH(ts.DateIN)<>MONTH(tm.Date)) OR (YEAR(ts.DateIN)<>YEAR(tm.Date)))) AND YEAR(tm.Date)='.$thisyr.' ';
    $sql1=$sql1.' UNION ALL SELECT 
        `tm`.`Date` AS `Date`,
        "TxfrIN" AS `Expr1`, "B" AS `BECS`,
        `tm`.`FromBranchNo` AS `SuppNo/ClientNo`,
        `tm`.`FromBranchNo` AS `FromBranchNo`,
        `ts`.`Particulars` AS `Particulars`,
        330 AS `DRID`,
        `ts`.`ClientBranchNo` AS `ClientBranchNo`, `ts`.`ClientBranchNo` AS `FromBudgetOf`,
        (`ts`.`Amount`) AS `Amount`, 1 AS `Forex`,`Amount` AS `PHPAmount`,
        "DR" AS `Expr3`,
        "Interbranch" AS `Interbranch`,
        `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm`
        JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) '.$wherecondition.'

        UNION ALL SELECT 
        `tm`.`Date` AS `Date`,
        "TxfrIN" AS `Expr1`, "B" AS `BECS`,
        `tm`.`FromBranchNo` AS `SuppNo/ClientNo`,
        `tm`.`FromBranchNo` AS `FromBranchNo`,		
        `ts`.`Particulars` AS `Particulars`,
        404 AS `CRID`, `ts`.`ClientBranchNo` AS `ClientBranchNo`, `ts`.`ClientBranchNo` AS `FromBudgetOf`,
        (`ts`.`Amount` * -(1)) AS `Amount`, 1 AS `Forex`,`Amount`*-1 AS `PHPAmount`,
        "CR" AS `Expr3`,
        "Interbranch" AS `Interbranch`,
        `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm`
        JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) '.$wherecondition;
            
// Tranfer OUT - Receive Payment (DR PaidViaAcctID, usually BDO  CR to reverse the Debit account for transfer, usually ARTradeTxfr)
$wherecondition=' WHERE ((`ts`.`DebitAccountID` = 204) AND (`ts`.`DatePaid` IS NOT NULL))  and '.$conditionpaid;
 $sql1=$sql1.' UNION ALL SELECT 
        `ts`.`DatePaid` AS `DatePaid`,
        "TxfrOUTRecvPayment" AS `Expr1`, "B" AS `BECS`,
        `ts`.`ClientBranchNo` AS `SuppNo/ClientNo`,
        `ts`.`ClientBranchNo` AS `ClientBranchNo`,
        `ts`.`Particulars` AS `Particulars`,
        `ts`.`PaidViaAcctID` AS `PaidViaAcctID`,
        `tm`.`FromBranchNo` AS `FromBranchNo`,
		`tm`.`FromBranchNo` AS `FromBudgetOf`,
        `ts`.`Amount` AS `Amount`, 1 AS `Forex`,`Amount` AS `PHPAmount`,
        "DR" AS `Expr2`,
        "InterbranchPaymt" AS `Interbranch`,
        `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm`
        JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
        JOIN `1branches` `b` ON b.BranchNo=tm.FromBranchNo
        '.$wherecondition.'

    UNION ALL SELECT 
        `ts`.`DatePaid` AS `DatePaid`,
        "TxfrOUTRecvPayment" AS `Expr1`, "B" AS `BECS`,
        `ts`.`ClientBranchNo` AS `SuppNo/ClientNo`,
        `ts`.`ClientBranchNo` AS `ClientBranchNo`,
        `ts`.`Particulars` AS `Particulars`,
        `ts`.`DebitAccountID` AS `DebitAccountID`,
        `tm`.`FromBranchNo` AS `FromBranchNo`,
		`tm`.`FromBranchNo` AS `FromBudgetOf`,
        (`ts`.`Amount` * -(1)) AS `Amount`, 1 AS `Forex`,`Amount`*-1 AS `PHPAmount`,
        "CR" AS `Expr2`,
        "InterbranchPaymt" AS `Interbranch`,
        `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm`
        JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`)))
        JOIN `1branches` `b` ON b.BranchNo=tm.FromBranchNo
        '.$wherecondition;
           
// Transfer IN - Give Payment (DR APTradeTxfr  CR PaidViaAcctID)
$sql1=$sql1.'
    UNION ALL SELECT 
        `ts`.`DatePaid` AS `DatePaid`,
        "TxfrINPayment" AS `Expr1`, "B" AS `BECS`,
        `tm`.`FromBranchNo` AS `SuppNo/ClientNo`,
        `tm`.`FromBranchNo` AS `FromBranchNo`,
        `ts`.`Particulars` AS `Particulars`,
        404 AS `DRID`,
        `ts`.`ClientBranchNo` AS `ClientBranchNo`,
		`ts`.`ClientBranchNo` AS `FromBudgetOf`,
        `ts`.`Amount` AS `Amount`, 1 AS `Forex`,`Amount` AS `PHPAmount`,
        "DR" AS `Expr3`,
        "InterbranchPaymt" AS `Interbranch`,
        `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm`
        JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) '.$wherecondition.'

    UNION ALL SELECT 
        `ts`.`DatePaid` AS `DatePaid`,
        "TxfrINPayment" AS `Expr1`, "B" AS `BECS`,
        `tm`.`FromBranchNo` AS `SuppNo/ClientNo`,
        `tm`.`FromBranchNo` AS `FromBranchNo`,
        `ts`.`Particulars` AS `Particulars`,
        `ts`.`PaidViaAcctID` AS `CRID`,
        `ts`.`ClientBranchNo` AS `ClientBranchNo`,
		`ts`.`ClientBranchNo` AS `FromBudgetOf`,
        (`ts`.`Amount` * -(1)) AS `Amount`, 1 AS `Forex`,`Amount`*-1 AS `PHPAmount`,
        "CR" AS `Expr3`,
        "InterbranchPaymt" AS `Interbranch`,
        `tm`.`TxnID` AS `TxnID`
    FROM
        (`acctg_2txfrmain` `tm`
        JOIN `acctg_2txfrsub` `ts` ON ((`tm`.`TxnID` = `ts`.`TxnID`))) '.$wherecondition;
            

//if($_SESSION['(ak0)']==1002) { echo $sql1; }
?>