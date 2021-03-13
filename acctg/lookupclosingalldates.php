<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(557,'1rtc')) { echo 'No permission'; exit; } 
$showbranches=false; include_once('../switchboard/contents.php');
 

?>
<html>
<head>
<title>Data Errors</title>
</head>
<body>
<h3>Data Errors</h3><br>
<?php

if (allowedToOpen(5573,'1rtc')) { echo '<a href="balsheetvspdcs.php" target="_blank">Bal Sheet Vs Unpaid Invoices</a><br><br>'; }

echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';
$showsubtitlealways=true; 
//$whichdata='withcurrent'; $reportmonth=(date('Y')<>substr($_SESSION['nb4A'],0,4)?12:date('m'));
//require("maketables/makefixedacctgdata.php");
if(isset($_GET['active']) and $_GET['active']==1) { $closedbydate=$_SESSION['nb4A']; $datecondition=' AND `Date`>\''.$closedbydate.'\'';  $dateconditionmain=' AND m.`Date`>\''.$closedbydate.'\'';} else {$datecondition='';$dateconditionmain='';}

    if (allowedToOpen(5571,'1rtc')) {	$conditionerr=' AND b.BranchNo='.$_SESSION['bnum'];} else { $conditionerr='';}

$subtitle='Cash Sales vs. Cash Deposits'; //direct from sales & deposits tables

    include_once('sqlphp/cashsalesvsdeposits.php');
    $sql=$sqlcheck;
    $columnnames=array('BranchNo','Branch','SaleDate','CashSales','CashDep','Diff');
    // $showtotals=false; $showgrandtotal=true; $runtotal=true;
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Uncleared Cash & Check Deposits (Today Not Counted)'; //e-wallets and direct dep not counted
    include_once '../banktxns/sqlphp/dataforcleardep.php';
    $sql='SELECT 
    `dm`.`Date`, Branch,
    `ca`.`ShortAcctID` AS `Bank`,
    `dm`.`DepositNo`,        
    FORMAT(sum(`dt`.`SumOfAmount`),2) AS `Amount`, `DepType`
FROM
    `acctg_2depositmain` `dm`
    JOIN `banktxns_32uniunclearedfordeptotals` `dt` ON `dm`.`TxnID` = `dt`.`TxnID`
    JOIN `acctg_1chartofaccounts` `ca` ON `dm`.`DebitAccountID` = `ca`.`AccountID`
    JOIN `banktxns_1maintaining` m ON m.AccountID=`dm`.`DebitAccountID`
    JOIN 1branches b ON b.BranchNo=dt.BranchNo
    JOIN acctg_1deptype dy ON dy.DepTypeID=dt.FirstofType
WHERE
    ISNULL(`dm`.`Cleared`) AND `Date`<CURDATE() AND dt.FirstofType IN (0,1,2) '.$conditionerr.'
GROUP BY `dm`.`TxnID` 
ORDER BY `dm`.`Date`, Branch, `ca`.`ShortAcctID`;';
    $columnnames=array('Date','Branch','Bank','DepositNo','DepType','Amount');

    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='With Invoice but not AP/AR OR AP/AR With No Invoice OR ARPDC With Invoice'; //direct from tables

   $sql='SELECT concat("CV ",vm.CVNo) AS Form, vs.Particulars, b.Branch, vs.BranchNo, vs.ForInvoiceNo, vs.DebitAccountID, vs.Amount
FROM acctg_2cvmain vm JOIN acctg_2cvsub vs ON vm.CVNo = vs.CVNo JOIN `1branches` b ON vs.BranchNo = b.BranchNo
WHERE (((vs.ForInvoiceNo Is Not Null) AND (vs.ForInvoiceNo) <>"") AND (vs.DebitAccountID NOT IN (400,404)) 
OR (((vs.ForInvoiceNo IS NULL) OR (vs.ForInvoiceNo) LIKE "")) AND (vs.DebitAccountID IN (400,404))) '.$conditionerr.$datecondition.'

UNION ALL
SELECT concat("Deposit ",dm.`DepositNo`) AS Form, ds.ClientNo, b.Branch, ds.BranchNo, ds.ForChargeInvNo, ds.CreditAccountID, ds.Amount
FROM (acctg_2depositsub ds INNER JOIN acctg_2depositmain dm ON ds.TxnID = dm.TxnID) INNER JOIN `1branches` b ON ds.BranchNo = b.BranchNo
WHERE (((ds.ForChargeInvNo Is Not Null) AND (ds.CreditAccountID NOT IN (200,202,203,204))) OR (((ds.ForChargeInvNo Is Not Null) AND (ds.CreditAccountID IN (201)))
OR ((ds.ForChargeInvNo IS NULL) AND (ds.CreditAccountID IN (200,202,203,204))))) '.$conditionerr.$datecondition.'

UNION ALL SELECT concat("Collect ",cm.`CollectNo`) AS Form, b.Branch, cm.BranchSeriesNo, cm.ClientNo, cs.`ForChargeInvNo`, cs.CreditAccountID, cs.Amount
FROM (acctg_2collectmain cm INNER JOIN acctg_2collectsub cs ON (cm.TxnID = cs.TxnID)) INNER JOIN `1branches` b ON cm.BranchSeriesNo = b.BranchNo
WHERE (((cs.`ForChargeInvNo` Is Not Null And cs.`ForChargeInvNo` Not Like "0" And cs.`ForChargeInvNo` Not Like "00" ) AND (cs.CreditAccountID NOT IN (200,201,202,203,204,705)) AND cs.Amount<>0)
OR ((cs.`ForChargeInvNo` IS NULL) AND (cs.CreditAccountID IN (200,201,202,203,204,705)))) '.$conditionerr.$datecondition;
           
          
//if($_SESSION['(ak0)']==1002) {echo $sql; }
$columnnames=array('Form','Particulars','Branch','ForInvoiceNo','DebitAccountID','Amount');
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Selling Price does NOT cover freight expense';
include_once('../invty/invlayout/pricelevelcase.php');
$sql0='CREATE TEMPORARY TABLE approvedfreight AS
SELECT m.TxnID, fc.*, fc.Amount AS FreightAmt, e.`Nickname` AS `ApprovedBy` FROM `approvals_2freightclients` fc JOIN `invty_2sale` m ON m.SaleNo=fc.ForInvoiceNo AND m.BranchNo=fc.BranchNo AND m.txntype=fc.txntype JOIN `1employees` e on fc.ApprovedByNo=e.IDNo JOIN `1branches` b ON m.BranchNo = b.BranchNo WHERE PriceFreightInclusive=1 AND Confirmed=0 '.$conditionerr.$dateconditionmain;
$stmt=$link->prepare($sql0);$stmt->execute();
$sql0='CREATE TEMPORARY TABLE soldamtnofreight AS
Select m.*,
SUM((SELECT 
						'.$plcase.'
					FROM `1branches` b1 where b1.BranchNo=m.BranchNo
				)*Qty)

	as MinPriceTotal, SUM(Qty*UnitPrice) AS SaleAmt FROM `invty_5latestminprice` lmp JOIN `invty_2salesub` s ON s.ItemCode=lmp.ItemCode JOIN `invty_2sale` m ON m.TxnID=s.TxnID
JOIN `approvedfreight` af ON af.TxnID=m.TxnID JOIN `1branches` b ON m.BranchNo = b.BranchNo WHERE m.TxnID IS NOT NULL '.$conditionerr.$dateconditionmain.' GROUP BY m.TxnID';
$stmt=$link->prepare($sql0);$stmt->execute(); 
$sql='SELECT m.*, ClientName AS Client, Branch, FreightAmt, IF(SaleAmt-(MinPriceTotal+FreightAmt)>=0,"Ok",CONCAT("LACKS ",(MinPriceTotal+FreightAmt)-SaleAmt)) AS `Lacking` FROM soldamtnofreight m JOIN `approvedfreight` af ON af.TxnID=m.TxnID JOIN `1clients` c ON c.ClientNo=m.ClientNo '
        . ' JOIN `1branches` b on b.BranchNo=m.BranchNo '
        . ' WHERE m.TxnID IS NOT NULL '.$conditionerr.' HAVING `Lacking` NOT LIKE "Ok"';
$columnnames=array('Date','SaleNo','Client','Branch','Lacking');    
include('../backendphp/layout/displayastableonlynoheaders.php'); 
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Unit Price is Lower than Special Price';
$sql='SELECT `Date`, Branch, SaleNo, IF(txntype=1,"Cash","Charge") AS SaleType, ss.ItemCode, Category, ItemDesc, SpecPriceApproved, UnitPrice AS ActualSellPrice, ss.Qty, ss.Qty*(SpecPriceApproved-UnitPrice) AS AmtDiff FROM `invty_2sale` sm JOIN `invty_2salesub` ss ON sm.TxnID=ss.TxnID JOIN `invty_7specdisctapproval` sd ON ss.TxnID=sd.TxnID AND ss.ItemCode=sd.ItemCode 
JOIN `1branches` b ON sm.BranchNo=b.BranchNo LEFT JOIN `invty_1items` i ON i.ItemCode=ss.ItemCode LEFT JOIN `invty_1category` c ON c.CatNo=i.CatNo
WHERE UnitPrice<SpecPriceApproved '.$conditionerr.$datecondition.' ORDER BY Branch, `Date`, SaleType, SaleNo;';
$columnnames=array('Date','Branch','SaleNo','SaleType','ItemCode','Category','ItemDesc','ActualSellPrice','SpecPriceApproved','Qty','AmtDiff');    
include('../backendphp/layout/displayastableonlynoheaders.php'); 
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Incorrect Invoice in Collection Receipts';

$sql='SELECT b.Branch, m.CollectNo AS PRorCollectNo, m.Date, m.ClientNo, c.ClientName, cs.ForChargeInvNo, cs.CreditAccountID, b2.Branch AS BranchSeries, m.Posted, cs.Amount
FROM ((acctg_2collectmain m INNER JOIN `1branches` b2 ON m.BranchSeriesNo=b2.BranchNo) INNER JOIN `1clients` c ON m.ClientNo=c.ClientNo) INNER JOIN (`1branches` b INNER JOIN (acctg_33qrybalperrecpt bal RIGHT JOIN acctg_2collectsub cs ON (bal.Particulars=cs.ForChargeInvNo) AND (bal.BranchNo=cs.BranchNo)) ON b.BranchNo=cs.BranchNo) ON m.TxnID=cs.TxnID
WHERE (((bal.Particulars) Is Null)) AND m.Date<>CURDATE() '.$conditionerr.$dateconditionmain.'
GROUP BY m.CollectNo, m.Date, m.ClientNo, cs.ForChargeInvNo, cs.CreditAccountID, m.Posted, m.BranchSeriesNo, cs.BranchNo
HAVING (((cs.ForChargeInvNo) Not Like "0") AND ((cs.CreditAccountID)>=200 And (cs.CreditAccountID)<300 And (cs.CreditAccountID)<>201))
;';

    $columnnames=array('Branch','PRorCollectNo','Date','ClientName','ForChargeInvNo','CreditAccountID','Amount','BranchSeries','Posted');
    // $showtotals=false; $showgrandtotal=true; $runtotal=true;
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Dated Check Clients with Outstanding Invoices';
$sql='SELECT bal.ClientNo, c.ClientName, Branch, FORMAT(SUM(`InvBalance`),2) AS TotalDue FROM acctg_33qrybalperrecpt bal JOIN 1clients c ON c.ClientNo=bal.ClientNo 
JOIN 1branches b ON b.BranchNo=bal.BranchNo WHERE Terms=1 GROUP BY c.ClientNo,bal.BranchNo HAVING TotalDue<>0;';
$columnnames=array('ClientName', 'Branch', 'TotalDue');
include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Dated Check Clients with Undeposited Payments';
$sql='SELECT bal.ClientNo, DateofPDC, c.ClientName, Branch, FORMAT(SUM(`PDC`),2) AS TotalPDC FROM acctg_undepositedclientpdcs bal JOIN `1clients` c ON c.ClientNo=bal.ClientNo 
JOIN `1branches` b ON b.BranchNo=bal.BranchNo WHERE Terms<=1 GROUP BY c.ClientNo,DateofPDC, bal.BranchNo HAVING TotalPDC<>0;';
$columnnames=array('DateofPDC', 'ClientName', 'Branch', 'TotalPDC');
include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Diff Date of Deposit vs Cleared Date';

$sql='SELECT dm.*, ca.ShortAcctID as Bank FROM acctg_2depositmain dm
join acctg_1chartofaccounts ca on ca.AccountID=dm.DebitAccountID
WHERE (((Month(`Date`))<>Month(`Cleared`))) ';

    $columnnames=array('DepositNo','Date','Bank','Remarks','Cleared','Posted');
    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Inv Number Used Twice';

$sql='SELECT b.Branch, clp.Particulars, Count(clp.Particulars) AS CountOfParticulars, clp.Date
FROM `1branches` b INNER JOIN acctg_30uniar clp ON b.BranchNo=clp.BranchNo
WHERE (((clp.FROM) Not Like "bounced")) '.$conditionerr.'
GROUP BY b.Branch, clp.Particulars, clp.Date, clp.BranchNo
HAVING (((Count(clp.Particulars))>1));';

    $columnnames=array('Branch','Particulars','CountOfParticulars','Date');
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Cash Client with ARTrade';

$sql='SELECT b.Branch, sm.Date AS `SaleDate`, ss.ClientNo, c.ClientName, ca.ShortAcctID, ss.Amount, sm.Posted
FROM (`1branches` b INNER JOIN acctg_2salemain sm ON b.BranchNo=sm.BranchNo) INNER JOIN (acctg_1chartofaccounts ca INNER JOIN acctg_2salesub ss ON ca.AccountID=ss.DebitAccountID) ON sm.TxnID=ss.TxnID
join `1clients` c on c.ClientNo=ss.ClientNo
WHERE (((ss.ClientNo)=10000 Or (ss.ClientNo)=10001 Or (ss.ClientNo) Is Null) AND ((ss.DebitAccountID)>=200 And (ss.DebitAccountID)<=300)) and Amount<>0 '.$conditionerr.$datecondition;

    $columnnames=array('Branch','SaleDate','ClientName','ShortAcctID','Amount','Posted');
    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Cancelled Client with Items or Overprice';

$sql='SELECT b.Branch, CONCAT("\'",sm.Date,"\'") AS `Sale_Date`, sm.SaleNo, sm.ClientNo, c.ClientName, SUM(ss.Qty*ss.UnitPrice) AS Amount, txndesc AS TxnType, sm.Posted
FROM (`1branches` b INNER JOIN `invty_2sale` sm ON b.BranchNo=sm.BranchNo) '
        . 'JOIN `invty_2salesub` ss ON sm.TxnID=ss.TxnID
JOIN `1clients` c ON c.ClientNo=sm.ClientNo JOIN `invty_0txntype` tt ON tt.txntypeid=sm.txntype
WHERE ((sm.ClientNo)=10001) '.$conditionerr.$datecondition.' GROUP BY sm.TxnID HAVING SUM(ss.Qty)<>0
    UNION ALL
SELECT b.Branch, CONCAT("\'",sm.Date,"\'") AS `Sale_Date`, sm.SaleNo, sm.ClientNo, c.ClientName, op.Amount, "Overprice" AS TxnType, sm.Posted
FROM (`1branches` b INNER JOIN `invty_2sale` sm ON b.BranchNo=sm.BranchNo) 
JOIN `invty_7opapproval` op ON sm.TxnID=op.TxnID JOIN `invty_2salesub` ss ON sm.TxnID=ss.TxnID
JOIN `1clients` c ON c.ClientNo=sm.ClientNo JOIN `invty_0txntype` tt ON tt.txntypeid=sm.txntype
WHERE ((sm.ClientNo)=10001) '.$datecondition.' GROUP BY sm.TxnID HAVING SUM(op.Amount)<>0 OR SUM(ss.Qty*ss.UnitPrice)<>0

';

    $columnnames=array('Branch','Sale_Date','SaleNo','ClientName','TxnType','Amount','Posted');
    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Beyond Approved Encashment Budget';
$sql0='CREATE TEMPORARY TABLE withapproval AS SELECT `TimeStamp`,`Approval`,`BranchNo`, `Particulars`, `Amount`, `AccountID` FROM `approvals_2encashedexpenses` 
		UNION SELECT `TimeStamp`,`Approval`,`BranchNo`, concat(`Particulars`, \' InvNo \', ForInvoiceNo) as `Particulars`, `Amount`, 925 as `AccountID` FROM `approvals_2freightclients`  
		UNION SELECT `Date`,`PONo`,`BranchNo`, sm.SaleNo as Particulars, sum(Qty*UnitPrice)*-1 as Amount, 705 as `AccountID` FROM `invty_2sale` sm join `invty_2salesub` ss on sm.TxnID=ss.TxnID  where txntype=5'; 
		$stmt=$link->prepare($sql0); $result0=$stmt->execute();
		
$sql0='create temporary table Spent as SELECT  de.`BranchNo`, de.`ApprovalNo`, Sum(de.Amount) as `Spent` FROM `acctg_2depencashsub` de group by de.`ApprovalNo`;';
			$stmt=$link->prepare($sql0); $stmt->execute();
			$sql='SELECT `TimeStamp`,Approval, Branch, ee.Amount AS ApprovedAmount, IFNULL(s.Spent,0) AS Spent,ROUND(ee.Amount-IFNULL(s.Spent,0),2) AS Net FROM `withapproval` ee LEFT JOIN `Spent` s ON ee.Approval=s.ApprovalNo JOIN `1branches` b ON b.BranchNo=ee.BranchNo HAVING Spent>ApprovedAmount+1';
			
    $columnnames=array('TimeStamp','Branch','Approval','ApprovedAmount','Spent');    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Diff AR Clients (Acctg vs. Invty)';

$sql='SELECT b.Branch, sm.Date, ss.Particulars AS Invoice, ss.ClientNo AS AcctClientNo, c.ClientName AS AcctgClient, invsale.ClientNo AS InvtyClientNo, c1.ClientName AS InvtyClient
FROM `1branches` b INNER JOIN (acctg_2salemain sm INNER JOIN (`1clients` c INNER JOIN ((acctg_2salesub ss INNER JOIN `invty_2sale` invsale ON ss.Particulars=invsale.SaleNo) INNER JOIN `1clients` c1 ON invsale.ClientNo=c1.ClientNo) ON c.ClientNo=ss.ClientNo) ON (sm.BranchNo=invsale.BranchNo) AND (sm.TxnID=ss.TxnID)) ON b.BranchNo=sm.BranchNo
WHERE (((ss.ClientNo)<>invsale.ClientNo) And ((ss.DebitAccountID)>=200) And ((invsale.PaymentType)=2))'.$conditionerr.'
ORDER BY b.Branch, sm.Date, ss.Particulars;
';
    $columnnames=array('Branch','Date','Invoice','AcctClientNo','AcctgClient','InvtyClientNo','InvtyClient');
    
    include('../backendphp/layout/displayastableonlynoheaders.php');

$subtitle='Diff Clients/Amounts (Collection Receipts vs. Deposits)';
$sql='SELECT DepositNo, ds.CRNo AS CRNo_in_Deposit, dm.Cleared AS ClearedDate, Branch, FORMAT(ds.Amount,2) AS Amount, c.ClientName AS Client_in_Deposit,  c1.ClientName AS Client_in_CollectReceipt
FROM `acctg_2depositsub` ds JOIN `acctg_2depositmain` dm ON dm.TxnID=ds.TxnID 
JOIN `acctg_2collectsub` cs ON cs.BranchNo=ds.BranchNo AND cs.Amount=ds.Amount AND cs.OtherORDetails=dm.DepositNo AND cs.ForChargeInvNo=ds.CRNo
JOIN `acctg_2collectmain` cm  ON cm.TxnID=cs.TxnID 
JOIN `1branches` b ON b.BranchNo=ds.BranchNo JOIN `1clients` c on c.ClientNo=ds.ClientNo
JOIN `1clients` c1 on c1.ClientNo=cm.ClientNo WHERE ds.Amount<>cs.Amount OR ds.ClientNo<>cm.ClientNo';
$columnnames=array('DepositNo','CRNo_in_Deposit','ClearedDate','Branch','Amount','Client_in_Deposit','Client_in_CollectReceipt');
include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Diff Totals (Deposits vs Collection Receipts)';
$sql='SELECT DepositNo, ds.CRNo AS CRNo_in_Deposit, dm.Cleared AS ClearedDate, Branch, FORMAT(SUM(ds.Amount),2) AS DepositAmount, FORMAT(SUM(cs.Amount),2) AS CollectReceipts, c.ClientName
FROM `acctg_2depositsub` ds JOIN `acctg_2depositmain` dm ON dm.TxnID=ds.TxnID 
JOIN `acctg_2collectsub` cs ON cs.BranchNo=ds.BranchNo AND cs.Amount=ds.Amount AND cs.BranchNo=ds.BranchNo AND cs.OtherORDetails=dm.DepositNo
JOIN `acctg_2collectmain` cm  ON cm.TxnID=cs.TxnID 
JOIN `1branches` b ON b.BranchNo=ds.BranchNo JOIN `1clients` c on c.ClientNo=ds.ClientNo
GROUP BY ds.ClientNo, ds.BranchNo,dm.DepositNo
HAVING (DepositAmount-CollectReceipts)<>0';
$columnnames=array('DepositNo','CRNo_in_Deposit','ClearedDate','Branch','ClientName','DepositAmount','CollectReceipts');
include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Collection Receipt Date > Date Today';

$sql='SELECT cm.Date, cm.CollectNo, cm.ClientNo, c.ClientName, Sum(cs.Amount) AS SumOfAmount, cs.BranchNo, b.Branch
FROM (`1clients` c INNER JOIN acctg_2collectmain cm ON c.ClientNo=cm.ClientNo) INNER JOIN (`1branches` b INNER JOIN acctg_2collectsub cs ON b.BranchNo=cs.BranchNo) ON cm.TxnID=cs.TxnID
WHERE (((cm.Date)>Now()))'.$conditionerr.'
GROUP BY cm.Date, cm.CollectNo, cm.ClientNo, cs.BranchNo;';

    $columnnames=array('Date','CollectNo','ClientName','SumOfAmount','Branch');
    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}


if (allowedToOpen(5571,'1rtc')) { goto skipincorrectdatesacctg;}
$sql='SELECT 1 AS DB,  "acctg_2jvmain" AS `Table`, "Date" AS DateField, adjs.Date, concat("JVNo ",adjm.JVNo) AS ControlNo, "JV" as w, "addeditsupplyside" AS filetoopen, adjm.JVNo FROM acctg_2jvmain adjm JOIN acctg_2jvsub adjs ON adjm.JVNo=adjs.JVNo WHERE YEAR(`Date`)<>'.$currentyr.' 
UNION ALL SELECT 1 AS DB,  "acctg_1assets" AS `Table`, "DateAcquired" AS DateField, a.DateAcquired, concat("AssetID ",a.AssetID) AS ControlNo, "AssetandDepr" as w, "assetanddepr" AS filetoopen, a.AssetID FROM acctg_1assets a WHERE YEAR(DateAcquired)>'.$currentyr.'
UNION ALL SELECT 1 AS DB,  "acctg_2purchasemain" AS `Table`, "Date" AS DateField, Date, concat("Supp.Inv# ",SupplierInv), "Purchases", "addeditsupplyside" AS filetoopen, p.TxnID FROM `acctg_2purchasemain` p LEFT JOIN `acctg_2purchasesub` ps ON p.TxnID=ps.TxnID WHERE YEAR(Date)<>'.$currentyr.'
UNION ALL SELECT 1 AS DB, "acctg_2cvmain" AS `Table`, "Date" AS DateField, vchm.Date, concat("Vch# ",vchm.CVNo), "CVs", "addeditsupplyside" AS filetoopen, vchm.CVNo  FROM acctg_2cvmain vchm WHERE YEAR(Date)<>'.$currentyr.'
UNION ALL SELECT 1 AS DB,  "acctg_2salemain" AS `Table`, "Date" AS DateField, sm.Date, "Sale" AS Expr1, "Sales", "addeditclientside" AS filetoopen, sm.TxnID FROM acctg_2salemain sm WHERE YEAR(sm.Date)<>'.$currentyr.'
UNION ALL SELECT 1 AS DB,  "acctg_2collectmain" AS `Table`, "Date" AS DateField, orm.Date, concat("Collect#",orm.CollectNo) AS Expr1, "Sales", "addeditclientside" AS filetoopen, orm.TxnID FROM acctg_2collectmain orm WHERE YEAR(orm.Date)<>'.$currentyr.'
UNION ALL SELECT 1 AS DB,  "acctg_2depositmain" AS `Table`, "Date" AS DateField, depm.Date, concat("Dep# ",depm.DepositNo) AS Expr1, "Deposits", "addeditdep" AS filetoopen, depm.TxnID FROM acctg_2depositmain depm WHERE YEAR(depm.Date)<>'.$currentyr.'
UNION ALL SELECT 1 AS DB,  "acctg_2collectsubbounced" AS `Table`, "DateBounced" AS DateField, DateBounced, concat("Bounced#",bm.CheckNo) AS Expr1, "BouncedfromCR", "addeditclientside" AS filetoopen, bm.TxnID FROM acctg_2collectmain bm JOIN acctg_2collectsubbounced bs ON bm.TxnID=bs.TxnID WHERE YEAR(DateBounced)<>'.$currentyr.'
UNION ALL SELECT 1 AS DB,  "acctg_3undepositedpdcfromlastperiodbounced" AS `Table`, "DateBounced" AS DateField, DateBounced, concat("Bounced#",bm.PDCNo) AS Expr1, "BouncedfromCR", "addeditclientside" AS filetoopen, bm.UndepPDCId FROM acctg_3undepositedpdcfromlastperiod bm JOIN acctg_3undepositedpdcfromlastperiodbounced bs ON bm.UndepPDCId=bs.UndepPDCId WHERE YEAR(DateBounced)<>'.$currentyr.'
UNION ALL SELECT 1 AS DB,  "acctg_2txfrmain" AS `Table`, "Date" AS DateField, tm.Date, "TxfrOUT" AS Expr1, "Interbranch", "addeditclientside" AS filetoopen, tm.TxnID FROM acctg_2txfrmain tm WHERE YEAR(tm.Date)>'.$currentyr.'
UNION ALL SELECT 1 AS DB,  "acctg_2txfrsub" AS `Table`, "DateIN" AS DateField, ts.DateIN, "TxfrIN" AS Expr1, "Interbranch", "addeditclientside" AS filetoopen, ts.TxnID FROM acctg_2txfrsub ts WHERE YEAR(ts.DateIN)<'.$currentyr.' ';
$stmt=$link->query($sql); $result=$stmt->fetchAll();
if ($stmt->rowCount()==0){ goto skipincorrectdatesacctg;}
$subtitle='Incorrect Dates';
$sub='<br>Accounting<br><table>';
$columnnames=array('Date','ControlNo'); $colorcount=0; $rcolor[0]="FFE9E7"; $rcolor[1]="FFFFFF";
foreach ($result as $row){
   $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnnames as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        if (allowedToOpen(5572,'1rtc')) {
            $toopen=$row['filetoopen'].'.php?w='.$row['w'].'&TxnID='.$row['TxnID'];
            $sub=$sub.'<td><a href="setastoday.php?tbl='.$row['Table'].'&DB='.$row['DB'].'&Date='.$row['DateField'].'&TxnID='.$row['TxnID'].'&ToOpen='.$toopen.'" target=_blank>Set_as_Today</a>';}
        $sub=$sub.'<td><a href="'.$toopen.'"  target=_blank>Lookup</a></tr>';
   $colorcount++;
}
echo $sub.'</table>';
skipincorrectdatesacctg: //UNION ALL SELECT  1 AS DB, "acctg_2depositmain" AS `Table`, "Date" AS DateField, "Deposit", `TxnID`, `Date`, 0 AS `BranchNo`, `EncodedByNo`, `TimeStamp` FROM `acctg_2depositmain` WHERE (`Date`=\'0000-00-00\' OR YEAR(`Date`)<>'.$currentyr.') '.$conditionbranch.'

    
if (allowedToOpen(5571,'1rtc')) { $conditionbranch=' AND BranchNo='.$_SESSION['bnum']; $conditionbranchseries=' AND BranchSeriesNo='.$_SESSION['bnum'];} 
else { $conditionbranch=''; $conditionbranchseries='';}
$sql='SELECT  0 AS DB, "invty_2mrr" AS `Table`, "Date" AS DateField, "MRR/PR" AS `FromTxn`, `TxnID`, `Date`, `BranchNo`, `EncodedByNo`, `TimeStamp` FROM `invty_2mrr` WHERE (`Date`=\'0000-00-00\' OR YEAR(`Date`)<>'.$currentyr.') '.$conditionbranch.'
UNION ALL SELECT  0 AS DB, "invty_2sale" AS `Table`, "Date" AS DateField, "Sale", `TxnID`, `Date`, `BranchNo`, `EncodedByNo`, `TimeStamp` FROM `invty_2sale` WHERE (`Date`=\'0000-00-00\' OR YEAR(`Date`)<>'.$currentyr.') '.$conditionbranch.'
UNION ALL SELECT  0 AS DB, "invty_2transfer" AS `Table`, "DateOUT" AS DateField, "Transfer OUT", `TxnID`, `DateOUT`, `BranchNo`, `FromEncodedByNo`, `FromTimeStamp` FROM `invty_2transfer` WHERE (`DateOUT`=\'0000-00-00\' OR YEAR(`DateOUT`)>'.$currentyr.') '.$conditionbranch.'
UNION ALL SELECT  0 AS DB, "invty_2transfer" AS `Table`, "DateIN" AS DateField, "Transfer IN", `TxnID`, `DateIN`, `ToBranchNo`, `ToEncodedByNo`, `ToTimeStamp` FROM `invty_2transfer` WHERE (`DateIN`=\'0000-00-00\' OR YEAR(`DateIN`)>'.$currentyr.') '.$conditionbranch.'
UNION ALL SELECT  0 AS DB, "invty_4adjust" AS `Table`, "Date" AS DateField, "JVment", `TxnID`, `Date`, `BranchNo`, `EncodedByNo`, `TimeStamp` FROM `invty_4adjust` WHERE (`Date`=\'0000-00-00\' OR YEAR(`Date`)<>'.$currentyr.') '.$conditionbranch.'
UNION ALL SELECT  1 AS DB, "acctg_2collectmain" AS `Table`, "Date" AS DateField, "Collection", `TxnID`, `Date`, `BranchSeriesNo`, `EncodedByNo`, `TimeStamp` FROM `acctg_2collectmain` WHERE (`Date`=\'0000-00-00\' OR YEAR(`Date`)<>'.$currentyr.') '.$conditionbranchseries.'
UNION ALL SELECT  1 AS DB, "acctg_2purchasemain" AS `Table`, "Date" AS DateField, "Purchase", `TxnID`, `Date`, `BranchNo`, `EncodedByNo`, `TimeStamp` FROM `acctg_2purchasemain` WHERE (`Date`=\'0000-00-00\' OR YEAR(`Date`)<>'.$currentyr.') '.$conditionbranch.';';
$stmt=$link->query($sql); $result=$stmt->fetchAll();
if ($stmt->rowCount()==0){ goto skipincorrectdatesinvty;}
$sub='';$subheadings='';
$columnnames=array('FromTxn','TxnID','Date','BranchNo','EncodedByNo','TimeStamp'); $colorcount=0; $rcolor[0]="FFE9E7"; $rcolor[1]="FFFFFF";
foreach ($columnnames AS $col){ $subheadings=$subheadings.'<td>'.$col.'</td>';}
foreach ($result as $row){
   $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnnames as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        if (allowedToOpen(5572,'1rtc')) {
        $sub=$sub.'<td><a href="setastoday.php?tbl='.$row['Table'].'&DB='.$row['DB'].'&Date='.$row['DateField'].'&TxnID='.$row['TxnID'].'" target=_blank>Set_as_Today</a>';}
        $sub=$sub.'</tr>';
   $colorcount++;
}
echo '<br><br>Incorrect Dates - Inventory<br><table><thead>'.$subheadings.'</thead>'.$sub.'</table>';

skipincorrectdatesinvty:

$subtitle='Clients Not Assigned to Branch';
if (allowedToOpen(5571,'1rtc')) { $conditionbranch=' AND sm.BranchNo='.$_SESSION['bnum'];} else { $conditionbranch='';}
$sql='SELECT ss.ClientNo, sm.BranchNo, ClientName, Branch
    FROM `acctg_2salemain` sm JOIN `acctg_2salesub` ss ON sm.`TxnID` = ss.`TxnID` LEFT JOIN gen_info_1branchesclientsjxn bc 
        ON sm.BranchNo=bc.BranchNo AND ss.ClientNo=bc.ClientNo
    LEFT JOIN `1clients` c ON c.ClientNo=ss.ClientNo LEFT JOIN `1branches` b ON b.BranchNo=sm.BranchNo
    WHERE bc.BranchClientID IS NULL AND ss.ClientNo>9999 '.$conditionbranch.' GROUP BY ss.ClientNo,sm.BranchNo;';
$columnnames=array('ClientName','Branch');  
include('../backendphp/layout/displayastableonlynoheaders.php'); 
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}    
    
$subtitle='Deposit Applied to No Such Invoice';

$sql='SELECT dm.Date, "Dep" AS `Where`, dm.DepositNo, ds.BranchNo, b.Branch, ds.ClientNo, c.ClientName, ds.ForChargeInvNo, ds.DepDetails, ds.CreditAccountID, ca.ShortAcctID, ds.Amount, clp.Particulars, e.Nickname AS EncodedBy, dm.Posted
FROM acctg_1chartofaccounts ca INNER JOIN ((`1branches`  b INNER JOIN (`1clients` c RIGHT JOIN (acctg_2depositmain dm INNER JOIN (acctg_2depositsub ds LEFT JOIN `acctg_30uniar` clp ON (ds.ForChargeInvNo = clp.Particulars) AND (ds.BranchNo = clp.BranchNo)) ON (dm.TxnID = ds.TxnID) AND (dm.TxnID = ds.TxnID)) ON (c.ClientNo = ds.ClientNo) ) ON b.BranchNo = ds.BranchNo) left join `1employees` e on e.IDNo=ds.EncodedByNo) ON ca.AccountID = ds.CreditAccountID
WHERE (((ds.ForChargeInvNo) Is Not Null) AND ((ds.CreditAccountID)<>705) AND ((clp.Particulars) Is Null)) AND dm.Date<>CURDATE()'.$conditionerr.'

UNION ALL SELECT cm.`Date`, "Collection" AS Expr1, cm.CollectNo, cs.BranchNo, b.Branch, cm.ClientNo, c.ClientName, cs.ForChargeInvNo, cs.OtherORDetails, cs.CreditAccountID, ca.ShortAcctID, cs.Amount, clp.Particulars, e.Nickname AS EncodedBy, cm.Posted
FROM acctg_1chartofaccounts ca INNER JOIN ((`1clients` c INNER JOIN acctg_2collectmain cm ON c.ClientNo = cm.ClientNo) INNER JOIN ((`1branches`  b INNER JOIN (acctg_2collectsub cs LEFT JOIN `acctg_30uniar` clp ON (cs.ForChargeInvNo = clp.Particulars) AND (cs.BranchNo = clp.BranchNo)) ON b.BranchNo = cs.BranchNo) left join `1employees` e on e.IDNo=cs.EncodedByNo) ON (cm.TxnID = cs.TxnID)) ON ca.AccountID = cs.CreditAccountID
WHERE (((cs.ForChargeInvNo) Is Not Null And (cs.ForChargeInvNo)<>"0" And (cs.ForChargeInvNo)<>"00") AND ((cs.CreditAccountID) IN (200,202) AND ((clp.Particulars) Is Null)) AND cm.Date<>CURDATE()) '.$conditionerr.'
ORDER BY `Date`;';

    $columnnames=array('Date','Where','DepositNo','Branch','ClientName','ForChargeInvNo','DepDetails','ShortAcctID','Amount');
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Negative Client Invoice Balance';

$sql='SELECT bal.*, c.ClientName, b.Branch,ca.ShortAcctID as DebitAccount FROM acctg_33qrybalperrecpt bal
join `1clients` c ON c.ClientNo=bal.ClientNo
join `1branches` b ON b.BranchNo=bal.BranchNo
join acctg_1chartofaccounts ca ON ca.AccountID=bal.DebitAccountID
WHERE (((bal.InvBalance)<-0.01))'.$conditionerr.';';

    $columnnames=array('ClientName','Particulars','Date','SaleAmount','RcdAmount','InvBalance','Branch','DebitAccount');
    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Same Invoice, Diff Client';

$sql='SELECT clp.Date, clp.ClientNo, c.ClientName AS WithLastPeriod, dep.ForChargeInvNo, b.Branch, dep.ClientNo, c2.ClientName AS `Dep&PDC`, dep.RcdAmount
FROM `acctg_30uniar` clp JOIN `acctg_32qryrcdamtperinv` dep ON (clp.Particulars=dep.ForChargeInvNo) AND (clp.BranchNo=dep.BranchNo) 
JOIN `1branches`  b ON clp.BranchNo=b.BranchNo 
JOIN `1clients` c ON clp.ClientNo=c.ClientNo 
JOIN `1clients` c2 ON dep.ClientNo=c2.ClientNo WHERE (b.BranchNo IS NOT NULL) '.$conditionerr.'
GROUP BY clp.Date, clp.ClientNo, dep.ForChargeInvNo, dep.ClientNo, dep.BranchNo
HAVING (((dep.ForChargeInvNo) Is Not Null) And ((dep.ClientNo) Not Like clp.ClientNo));';
        
     $columnnames=array('Date','WithLastPeriod','ForChargeInvNo','Branch','Dep&PDC','RcdAmount');
    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Incorrect Dates in Bank Transactions';

$sql='SELECT bt.*, c.ShortAcctID AS Account FROM `banktxns_banktxns` bt JOIN `acctg_1chartofaccounts` c on c.AccountID=bt.AccountID WHERE TxnDate=\'0000-00-00\' OR YEAR(TxnDate)<>'.$currentyr.' ORDER BY TxnNo DESC;';
$columnnames=array('AccountID','ShortAcctID','TxnDate','Particulars','BankBranch','CheckNo','BankTransCode','WithdrawAmt','DepositAmt','Balance','Remarks','Cleared');    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}


$subtitle='Unpaid AR1';

	$sqlt='CREATE TEMPORARY TABLE Receivables ( Date DATE NOT NULL, ClientNo SMALLINT NOT NULL, ClientName VARCHAR(100) NULL, INVNO VARCHAR(150) NOT NULL, SaleAmount DOUBLE NOT NULL, RcdAmount DOUBLE NULL, InvBalance DOUBLE NOT NULL, Age smallint(5) NOT NULL, Terms smallint(3) NOT NULL, CreditLimit double NOT NULL, BranchNo smallint(6) NOT NULL, Branch varchar(25) NOT NULL, Hold varchar(10) null )

 SELECT r.Date, r.ClientNo, c.ClientName as WHO, r.Particulars AS INVNO, round(r.SaleAmount,2) as SaleAmount, round(r.RcdAmount,2) as RcdAmount, round(r.InvBalance,2) as InvBalance, DateDiff(curdate(),r.Date) as Age, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit, r.BranchNo, b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold FROM `acctg_33qrybalperrecpt` as r join `1clients` c on c.ClientNo=r.ClientNo join `1branches` b on b.BranchNo=r.BranchNo left join `acctg_34holdstatus` ch on ch.ClientNo=r.ClientNo WHERE (InvBalance<>0 AND (InvBalance>0.05 OR InvBalance<-0.05)) AND CreditLimit=10000 and Terms=1

 union all SELECT ifnull(up.SaleDate,up.DateofPDC) as Date, up.ClientNo, c.`ClientName`,concat("CRNo",CRNo,\' \',`PDCBank`,\' \',`PDCNo`,\' \',`DateofPDC`),0,0,`PDC`,DateDiff(curdate(),ifnull(up.SaleDate,up.DateofPDC)) as Age, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit , up.`BranchNo`,b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold FROM acctg_undepositedclientpdcs up join `1branches` b on b.BranchNo=up.BranchNo join `1clients` c on c.ClientNo=up.ClientNo left join `acctg_34holdstatus` ch on ch.ClientNo=up.ClientNo WHERE CreditLimit=10000 and Terms=1 ;';
	// echo $sqlt; exit();
	$stmtt=$link->prepare($sqlt); $stmtt->execute();

$sql='select *,WHO AS ClientName from Receivables';
$columnnames=array('Date','ClientName','INVNO','SaleAmount','RcdAmount','InvBalance','Branch');    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}


if (allowedToOpen(5571,'1rtc')) { echo 'End of Branch Report';goto endofreport;}

$subtitle='Unrecorded GCash Sales';
    $columnnames=array('BranchNo','Branch','SaleDate','GCash_Acctg','GCash_Invty');
    $sql='SELECT gc.`BranchNo`,`Branch`,`SaleDate`, FORMAT(SUM(`GCashSalesAcctg`),2) AS `GCash_Acctg`,FORMAT(SUM(`GCashSalesInvty`),2) AS `GCash_Invty` FROM `acctg_closing_dailygcashsales` gc JOIN `1branches` b ON b.BranchNo=gc.BranchNo GROUP BY gc.`BranchNo`,`SaleDate` HAVING (`GCash_Acctg`<>`GCash_Invty`)';
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Unrecorded Paymaya Sales';
    $columnnames=array('BranchNo','Branch','SaleDate','Paymaya_Acctg','Paymaya_Invty');
    $sql='SELECT gc.`BranchNo`,`Branch`,`SaleDate`, FORMAT(SUM(`PaymayaSalesAcctg`),2) AS `Paymaya_Acctg`,FORMAT(SUM(`PaymayaSalesInvty`),2) AS `Paymaya_Invty`  FROM `acctg_closing_dailygcashsales` gc JOIN `1branches` b ON b.BranchNo=gc.BranchNo GROUP BY gc.`BranchNo`,`SaleDate` HAVING (`Paymaya_Acctg`<>`Paymaya_Invty`)';
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}


$subtitle='Same Supplier Same Invoice - Accounting Data';

$sql='SELECT s.SupplierName as SupplierinPurchase, pm.SupplierInv as InvinPurchase, s2.SupplierName as SupplierLastPeriod, up.SupplierInv as InvLastPeriod
FROM ((`acctg_2purchasemain` pm  INNER JOIN `acctg_3unpdsuppinvlastperiod` up ON (pm.SupplierInv=up.SupplierInv) AND (pm.SupplierNo=up.SupplierNo)) INNER JOIN `1suppliers` s ON pm.SupplierNo=s.SupplierNo) INNER JOIN `1suppliers` AS s2 ON up.SupplierNo=s2.SupplierNo;';

    $columnnames=array('SupplierinPurchase','InvinPurchase','SupplierLastPeriod','InvLastPeriod');
    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='MRR Numbers Used Twice - Accounting Data';

$sql='SELECT GROUP_CONCAT(Date) AS Dates,MRRNo,COUNT(MRRNo) AS CountofMRR, SupplierName, GROUP_CONCAT(SupplierInv) AS SupplierInvoices FROM `acctg_2purchasemain` pm JOIN `1suppliers` s ON s.SupplierNo=pm.SupplierNo WHERE SupplierInv NOT LIKE \'%EWT%\'  GROUP BY MRRNo HAVING CountofMRR>1;';

    $columnnames=array('Dates','MRRNo','CountofMRR','SupplierName','SupplierInvoices');
    
    include('../backendphp/layout/displayastableonlynoheaders.php');    
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Same Supplier Same Invoice - Inventory Data';

$sql='SELECT GROUP_CONCAT(Date) AS Dates,GROUP_CONCAT(MRRNo) AS MRRNumbers,COUNT(SuppInvNo) AS CountOfInv, SupplierName, SuppInvNo FROM invty_2mrr m '
        . 'JOIN `1suppliers` s ON s.SupplierNo=m.SupplierNo WHERE SuppInvNo<>0 GROUP BY m.SupplierNo, SuppInvNo HAVING CountOfInv>1;';

    $columnnames=array('Dates','MRRNumbers','CountOfInv','SupplierName','SuppInvNo');
    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='No Such Supplier Invoice';    
$sql='SELECT *, vm.`Date` AS `CVDate`, vs.Amount AS Amt FROM `acctg_2cvmain` vm JOIN `acctg_2cvsub` vs ON `vm`.`CVNo` = `vs`.`CVNo`
    LEFT JOIN `acctg_21unipurchasewithlastperiod` pm ON pm.`SupplierNo`=vm.PayeeNo AND pm.SupplierInv=vs.`ForInvoiceNo` WHERE pm.SupplierInv IS NULL AND vs.`ForInvoiceNo` IS NOT NULL AND vs.`ForInvoiceNo`<>"";';

$columnnames=array('CVNo','CheckNo','CVDate','DateofCheck', 'PayeeNo', 'Payee','Amt');
    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

if (allowedToOpen(5573,'1rtc')) {


//SEPARATED
/* 
$subtitle='Bal Sheet Vs Unpaid Invoices';
$link=connect_db("".$currentyr."_1rtc",1); 
//$whichdata='withcurrent'; $reportmonth=((date('Y')<>substr($_SESSION['nb4A'],0,4))?12:date('m')); require ('maketables/makefixedacctgdata.php');
$sql0='drop table if exists `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();
$sql0='drop table if exists `acctg_dailyclose_endapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();
require('sqlphp/sqldailyclose.php');
$stmt=$link->prepare($sql0);$stmt->execute();
$stmt=$link->prepare($sql1);$stmt->execute();

$columnnames=array('Account','Branch','BSAmt', 'InvBalances','Diff');  
$sql='select dc.*, b.Branch, ca.ShortAcctID as Account from acctg_dailyclose_endapar'.$_SESSION['(ak0)'].' dc
join `1branches` b ON dc.BranchNo = b.BranchNo
join acctg_1chartofaccounts ca on ca.AccountID=dc.AccountID   where (Diff<-0.1 or Diff>0.1)   order by Account,Branch'; //or (BSAmt<>0 and InvBalances<>0)
// include('../backendphp/layout/displayastableonlynoheaders.php');

 // 
$sql0='drop table if exists `acctg_dailyclose_1uniforendapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();
$sql0='drop table if exists `acctg_dailyclose_endapar'.$_SESSION['(ak0)'].'`'; $stmt=$link->prepare($sql0); $stmt->execute();
    
 */

$subtitle='With MRR, No Recorded Purchase in Acctg after 3 Days';

$sql='SELECT m.`Date`, m.MRRNo AS `MRRorPR`, m.SuppInvNo, m.TxnID, s.SupplierName FROM `invty_2mrr` m
        LEFT JOIN `acctg_2purchasemain` pm ON m.MRRNo=pm.MRRNo
        JOIN `1suppliers` AS s ON m.SupplierNo=s.SupplierNo 
        WHERE (txntype=6) AND (pm.MRRNo IS NULL)
        UNION ALL
SELECT m.`Date`, m.PRNo, m.PRNo, m.TxnID, s.SupplierName FROM `invty_2pr` m
        LEFT JOIN `acctg_2purchasemain` pm ON m.PRNo=pm.MRRNo
        JOIN `1suppliers` AS s ON m.SupplierNo=s.SupplierNo 
        WHERE (txntype=8) AND (pm.MRRNo IS NULL);';
$columnnames=array('Date','MRRorPR','SupplierName','SuppInvNo','TxnID');    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}    
    
$subtitle='No Recorded Purchase Return in Acctg (beyond current month)';

$sql='SELECT m.*, s.SupplierName from `invty_2mrr` m
        JOIN `1suppliers` AS s ON m.SupplierNo=s.SupplierNo 
        LEFT JOIN `acctg_2purchasemain` pm ON m.MRRNo=pm.MRRNo
        WHERE (txntype=8) AND (MONTH(m.`Date`)<>MONTH(CURDATE())) AND (pm.MRRNo IS NULL);';
$columnnames=array('Date','MRRNo','SupplierName','TxnID');    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

// purchase return
$sql='select *,\'0\' as DB, \'invty_2pr\' as `Table`,\'Date\' as DateField,\'purchasereturn\' as filetoopen,\'addeditpr\' as w from invty_2pr where Date=\'0000-00-00\'';
// echo $sql; exit();
$stmt=$link->query($sql); $result=$stmt->fetchAll();
if ($stmt->rowCount()!=0){ 
echo '<b>Purchase Return Incorrect Dates</b>';
$sub='<table>';
$columnnames=array('Date','CRNo'); $colorcount=0; $rcolor[0]="FFE9E7"; $rcolor[1]="FFFFFF";
foreach ($result as $row){
   $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnnames as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        if (allowedToOpen(5572,'1rtc')) {
            $toopen=$row['filetoopen'].'.php?w='.$row['w'].'&TxnID='.$row['TxnID'];
            $sub=$sub.'<td><a href="setastoday.php?tbl='.$row['Table'].'&DB='.$row['DB'].'&Date='.$row['DateField'].'&TxnID='.$row['TxnID'].'&ToOpen='.$toopen.'" target=_blank>Set_as_Today</a>';}
        $sub=$sub.'<td><a href="../invty/'.$toopen.'"  target=_blank>Lookup</a></tr>';
   $colorcount++;
}
echo $sub.'</table><br>';
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}
}
//


$subtitle='With Purchase, no MRR for Inventory Suppliers';
//$sql='SELECT pm.*, SupplierName, DATE_ADD(pm.`Date`, INTERVAL pm.`Terms` DAY) AS DueDate FROM  `acctg_2purchasemain` pm JOIN 1suppliers s ON s.SupplierNo=pm.SupplierNo LEFT JOIN `invty_2mrr` m ON m.MRRNo=pm.MRRNo WHERE (m.MRRNo IS NULL) AND (InvtySupplier<>0) AND (SupplierInv NOT LIKE "%EWT%" AND pm.Remarks NOT LIKE "EWT%");';
$sql='SELECT pm.*, SupplierName, DATE_ADD(pm.`Date`, INTERVAL pm.`Terms` DAY) AS DueDate FROM  `acctg_2purchasemain` pm JOIN 1suppliers s ON s.SupplierNo=pm.SupplierNo 
LEFT JOIN `invty_2mrr` m ON m.MRRNo=pm.MRRNo LEFT JOIN `invty_2pr` pr ON pr.PRNo=pm.MRRNo WHERE ((m.MRRNo IS NULL) AND (InvtySupplier<>0) AND (SupplierInv NOT LIKE "%EWT%" AND pm.Remarks NOT LIKE "EWT%")) AND (pr.PRNo IS NULL);';
$columnnames=array('Date','MRRNo','SupplierName','SupplierInv','DateofInv','DueDate','TxnID');    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='Supplier Delivered More Than Ordered';
$sql='SELECT s.SupplierName, ud.*, i.*, ItemDesc AS Description, Category from `invty_41supplierundelivered` ud join invty_1items i on i.ItemCode=ud.ItemCode JOIN invty_1category c ON c.CatNo=i.CatNo join `1suppliers` s on s.SupplierNo=ud.SupplierNo WHERE SupplierUndelivered<0   GROUP BY PONo ORDER BY SupplierName, PONo';
$columnnames=array('SupplierName','PONo', 'ItemCode','Category','Description','Ordered','Received','SupplierUndelivered','Unit');    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}    
    

$subtitle='Reconciliation Account Not Equal to Zero -- may need static data update';

$sql='SELECT FORMAT(SUM(Amount),0) AS NetValue FROM '.$currentyr.'_static.acctg_unialltxns u WHERE AccountID=105 HAVING NetValue>0.10 OR NetValue<-0.10;';
$columnnames=array('NetValue');    
    include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='BS Assets vs Asset List -- may need static data update';

$stmt=$link->prepare('DROP TEMPORARY TABLE IF EXISTS BSAssets;'); $stmt->execute();
$stmt=$link->prepare('DROP TEMPORARY TABLE IF EXISTS BSAccumDep;'); $stmt->execute();
$stmt=$link->prepare('DROP TEMPORARY TABLE IF EXISTS BSAssetsBalance;'); $stmt->execute();
$stmt=$link->prepare('DROP TEMPORARY TABLE IF EXISTS AssetList;'); $stmt->execute();

$stmt=$link->prepare('CREATE TEMPORARY TABLE BSAssets as 
SELECT BranchNo,  u.AccountID, Sum(Amount) as AssetVal  FROM '.$currentyr.'_static.acctg_unialltxns u 
WHERE u.AccountID IN (SELECT AccountID FROM acctg_1chartofaccounts WHERE AccountType IN (6,7) AND NormBal=1 AND AccountID<>150)
GROUP BY u.BranchNo, u.AccountID;'); $stmt->execute();

$stmt=$link->prepare('CREATE TEMPORARY TABLE BSAccumDep AS
SELECT BranchNo,  u.AccountID, ca.ContraAccountOf, Sum(Amount)*-1 as AccumDep  FROM '.$currentyr.'_static.acctg_unialltxns u JOIN acctg_1chartofaccounts ca ON ca.AccountID=u.AccountID
WHERE AccountType IN (6,7) AND NormBal=-1
GROUP BY u.BranchNo, u.AccountID;'); $stmt->execute();

$stmt=$link->prepare('CREATE TEMPORARY TABLE BSAssetsBalance AS
SELECT a.*, d.AccountID AS DeprAcctID, AccumDep, AssetVal-AccumDep AS NetBSVal FROM BSAssets a LEFT JOIN BSAccumDep d ON a.BranchNo=d.BranchNo AND a.AccountID=d.ContraAccountOf WHERE AssetVal<>0 OR AccumDep<>0;
'); $stmt->execute();


$stmt=$link->prepare('CREATE TEMPORARY TABLE AccumDep AS
SELECT `BranchNo`, a.`AssetAccountID`,SUM(CASE WHEN ((Year(`DeprDate`)<'.$currentyr.') OR (Year(`DeprDate`)='.$currentyr.' AND MONTH(`DeprDate`)<=(IF(YEAR(CURDATE())>'.$currentyr.',12,MONTH(CURDATE()))))) THEN  IFNULL(d.`Amount`,0) END) AS `AccumDep` FROM `acctg_1assets` a LEFT JOIN `acctg_1assetsdepr` d ON a.AssetID=d.AssetID GROUP BY `BranchNo`,a.AssetAccountID;'); $stmt->execute();

$stmt=$link->prepare('CREATE TEMPORARY TABLE AssetList AS
SELECT a.BranchNo, a.AssetAccountID, TRUNCATE(SUM(`AcqCost`),2)  AS `GrossAssetValue`
FROM `acctg_1assets` a GROUP BY a.BranchNo, a.AssetAccountID ;'); $stmt->execute();

$stmt=$link->prepare('CREATE TEMPORARY TABLE AssetListNetValue AS
SELECT a.BranchNo, a.AssetAccountID, TRUNCATE(IFNULL(`GrossAssetValue`,0)-IFNULL(ad.`AccumDep`,0),2) AS `NetValueThisYr`
FROM `AssetList` a LEFT JOIN AccumDep ad ON a.AssetAccountID=ad.AssetAccountID  AND a.BranchNo=ad.BranchNo GROUP BY a.BranchNo, a.AssetAccountID ;'); $stmt->execute();

$sql='SELECT Company, bs.BranchNo, Branch, AccountDescription, bs.*, FORMAT(NetBSVal,2) AS NetBSValue, FORMAT(IFNULL(`NetValueThisYr`,0),2) AS `NetValueThisYr`, FORMAT(bs.NetBSVal-IFNULL(`NetValueThisYr`,0),2) AS Diff FROM BSAssetsBalance bs LEFT JOIN AssetListNetValue a on bs.AccountID=a.AssetAccountID AND bs.BranchNo=a.BranchNo 
JOIN `1branches` b ON b.BranchNo=bs.BranchNo JOIN `1companies` c ON c.CompanyNo=b.CompanyNo
JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=bs.AccountID
HAVING (Diff>0.5 OR Diff<-0.5) ORDER BY Branch;';

$columnnames=array('Company','BranchNo','Branch','AccountDescription','NetBSValue','NetValueThisYr','Diff');
include('../backendphp/layout/displayastableonlynoheaders.php');
if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}

$subtitle='No Account ID in Chart of Accounts -- may need static data update';

$sql='SELECT `uni`.Date, uni.BranchNo, uni.AccountID, uni.ControlNo, Particulars,  Amount, Branch, IF(b.Active=1,"","CLOSED BRANCH") AS Status
FROM '.$currentyr.'_static.acctg_unialltxns uni  LEFT JOIN acctg_1begbal bb ON (uni.AccountID = bb.AccountID) AND (uni.BranchNo = bb.BranchNo)
JOIN `1branches` b ON b.BranchNo=uni.BranchNo
WHERE bb.AccountID Is Null '.$conditionerr; 

    $columnnames=array('Date','ControlNo','Particulars','AccountID','Amount','BranchNo','Branch','Status');
    include('../backendphp/layout/displayastableonlynoheaders.php');
    if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}    
    
$subtitle='Bal Sheet Prepaid vs Prepaid List -- may need static data update';

$stmt=$link->prepare('DROP TEMPORARY TABLE IF EXISTS BSPrepaid;'); $stmt->execute();
$stmt=$link->prepare('DROP TEMPORARY TABLE IF EXISTS PrepaidList;'); $stmt->execute();

$stmt=$link->prepare('CREATE TEMPORARY TABLE BSPrepaid as 
SELECT BranchNo,  u.AccountID, Sum(u.Amount) as NetBSVal  FROM '.$currentyr.'_static.acctg_unialltxns u 
WHERE u.AccountID IN (Select PrepaidAccountID FROM `acctg_2prepaid`) OR (u.AccountID IN (150,151))
GROUP BY u.BranchNo, u.AccountID;'); $stmt->execute();

$stmt=$link->prepare('CREATE TEMPORARY TABLE amortized AS
SELECT `BranchNo`,`PrepaidAccountID`,SUM(CASE WHEN ((Year(`AmortDate`)<'.$currentyr.') OR (Year(`AmortDate`)='.$currentyr.' AND IF(Year(`AmortDate`)=YEAR(CURDATE()),MONTH(`AmortDate`)<=MONTH(CURDATE()),MONTH(`AmortDate`)<=12))) THEN  IFNULL(s.`Amount`,0) END) AS `TotalAmort` FROM `acctg_2prepaid` m JOIN `acctg_2prepaidamort` s ON m.PrepaidID=s.PrepaidID GROUP BY `BranchNo`,`PrepaidAccountID`;');  $stmt->execute();
$stmt=$link->prepare('CREATE TEMPORARY TABLE PrepaidList as 
SELECT m.BranchNo, m.PrepaidAccountID, TRUNCATE(SUM(m.`Amount`)-IFNULL(a.`TotalAmort`,0),2) AS `NetValueThisYr` FROM `acctg_2prepaid` m LEFT JOIN `amortized` a ON m.`PrepaidAccountID`=a.`PrepaidAccountID` AND m.`BranchNo`=a.`BranchNo` GROUP BY m.BranchNo, m.PrepaidAccountID;'); $stmt->execute();

$sql='SELECT Company, bs.BranchNo,Branch, AccountDescription, bs.*, FORMAT(bs.NetBSVal,2) AS NetBSPrepaid, FORMAT(SUM(IFNULL(a.NetValueThisYr,0)),2) AS NetValueThisYr, FORMAT(bs.NetBSVal-IFNULL(a.NetValueThisYr,0),2) AS Diff FROM BSPrepaid bs LEFT JOIN PrepaidList a on bs.AccountID=a.PrepaidAccountID AND bs.BranchNo=a.BranchNo 
JOIN `1branches` b ON b.BranchNo=bs.BranchNo JOIN `1companies` c ON c.CompanyNo=b.CompanyNo
JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=bs.AccountID GROUP BY bs.AccountID, bs.BranchNo
HAVING (Diff>0.5 OR Diff<-0.5) ORDER BY Branch';

$columnnames=array('Company','BranchNo','Branch','AccountDescription','NetBSPrepaid','NetValueThisYr','Diff');
    
    include('../backendphp/layout/displayastableonlynoheaders.php');
    if($_SESSION['(ak0)']==1002) { echo 'As of '.date('Y-m-d h:i:s l').'<br><br>';}
}
endofreport:
      $stmt=null; $link=null;
?><BR>END OF REPORT
</body>
</html>