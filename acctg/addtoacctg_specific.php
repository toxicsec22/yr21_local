<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(516,'1rtc')) { echo 'No permission'; exit; }
$showbranches=true; include_once('../switchboard/contents.php');
include_once('../invty/invlayout/pricelevelcase.php'); 


$fieldname='Date';
$whichqry=$_REQUEST['submit'];
if($whichqry=='Preview All Entries'){ goto skipchoices;}

$title='Add Inventory Transactions to Acctg';
$method='GET';
include_once('../backendphp/layout/clickontabletoedithead.php');

?>
<div style="margin-left: 50px;">
<form method="post" action="addtoacctg_specific.php" enctype="multipart/form-data">
    Choose Date:  <input type="date" name="<?php echo $fieldname; ?>" value="<?php echo date('Y-m-d',strtotime("-1 days")); ?>"></input>&nbsp; &nbsp; 
<input type="submit" name="submit" value="Preview All Entries"><br><br>
<input type="submit" name="submit" value="CASH Sales (and COGS of Returns)"><br><br>
<div style="margin-left: 100px;"><table><thead><th>Entries for</th><th>Particulars</th><th>Client</th><th>Debit</th><th>Credit</th></thead>
<tr><td>Cash Sales (for each VAT Type)</td><td>Range of Invoices - VAT Type</td><td>CASH</td><td>Cash on Hand</td><td>Sales (VAT Type)</td></tr>
<tr><td>VAT from Cash Sales (for each VAT Type)</td><td>Range of Invoices - VAT</td><td>CASH</td><td>Sales (VAT Type)</td><td>Output VAT</td></tr>
<tr><td>GCash Sales (for each VAT Type)</td><td>Range of Invoices - VAT Type</td><td>CASH</td><td>GCash Wallet</td><td>Sales (VAT Type)</td></tr>
<tr><td>Paymaya Sales (for each VAT Type)</td><td>Range of Invoices - VAT Type</td><td>CASH</td><td>Paymaya Wallet</td><td>Sales (VAT Type)</td></tr>
<!--<tr><td>Returns: Good item</td><td></td><td></td><td>Inventory-Internal</td><td>Cost of Sales</td></tr>
<tr><td>Returns: Defective item</td><td></td><td></td><td>Defective Inventory</td><td>Cost of Sales</td></tr>-->
<tr><td>Overprice from Cash Sales</td><td>Range of Invoices - overprice</td><td>CASH</td><td>Overprice from Sale</td><td>Cash on Hand</td></tr>
<tr><td>Vat Collection for OP in Cash Sales</td><td>Range of Invoices - vat from op</td><td>CASH</td><td>Cash on Hand</td><td>OP-OutputVatCollected</td></tr>
    </table></div><br><br>
<input type="submit" name="submit" value="CHARGE Sales"><br><br>
<div style="margin-left: 100px;"><table><thead><th>Entries for</th><th>Particulars</th><th>Client</th><th>Debit</th><th>Credit</th></thead>
<tr><td>Charge Sales</td><td>Invoice Number - VAT Type</td><td>Client Name</td><td>ARTrade</td><td>Sales (VAT Type)</td></tr>
<tr><td>VAT from Charge Sales</td><td>Invoice Number - VAT</td><td>Client Name</td><td>Sales (VAT Type)</td><td>Output VAT</td></tr>
<!--<tr><td>Cost of Sales from Charge Sales</td><td></td><td></td><td>Cost of Sales</td><td>Inventory-Internal</td></tr>-->
<tr><td>Overprice from Charge Sales</td><td>Invoice Number - overprice</td><td>Client Name</td><td>Overprice from Sale</td><td>AP Others</td></tr>
<tr><td colspan="5">Vat Collection for OP is done when OP is paid to close AP Others.</td></tr>
<tr><td>Freight - not included in price</td><td>Invoice Number - Freight</td><td>Client Name</td><td>ARTrade</td><td>Freight (Clients) Expense</td></tr>
<tr><td>Freight - included in price</td><td>Invoice Number - FreightAdjIncl</td><td>Client Name</td><td>Discounts</td><td>Freight (Clients) Expense</td></tr>
    </table></div><br><br>
	
<input type="submit" name="submit" value="AR1 CHARGE Sales"><br><br>
<div style="margin-left: 100px;"><table><thead><th>Entries for</th><th>Particulars</th><th>Client</th><th>Debit</th><th>Credit</th></thead>
<tr><td>AR1 Charge Sales</td><td>Invoice Number - VAT Type</td><td>Client Name</td><td>ARTrade</td><td>Sales (VAT Type)</td></tr>
<tr><td>VAT from AR1 Charge Sales</td><td>Invoice Number - VAT</td><td>Client Name</td><td>Sales (VAT Type)</td><td>Output VAT</td></tr>
<!--<tr><td>Cost of Sales from Charge Sales</td><td></td><td></td><td>Cost of Sales</td><td>Inventory-Internal</td></tr>-->
<tr><td>Overprice from AR1 Charge Sales</td><td>Invoice Number - overprice</td><td>Client Name</td><td>Overprice from Sale</td><td>AP Others</td></tr>
<tr><td colspan="5">Vat Collection for OP is done when OP is paid to close AP Others.</td></tr>
<tr><td>Freight - not included in price</td><td>Invoice Number - Freight</td><td>Client Name</td><td>ARTrade</td><td>Freight (Clients) Expense</td></tr>
<tr><td>Freight - included in price</td><td>Invoice Number - FreightAdjIncl</td><td>Client Name</td><td>Discounts</td><td>Freight (Clients) Expense</td></tr>
    </table></div><br><br>
	
	
<input type="submit" name="submit" value="SALES RETURNS with EXCHANGES ONLY"><br><br>
<div style="margin-left: 100px;"><table><thead><th>Entries for</th><th>Particulars</th><th>Client</th><th>Debit</th><th>Credit</th></thead>
<tr><td>Returned items</td><td>Per sales return slip</td><td>Client Name</td><td>Sales Returns</td><td>Cash on Hand</td></tr>
<tr><td>Exchanged items</td><td>Per sales return slip</td><td>Client Name</td><td>Cash on Hand</td><td>Sales (Vatable)</td></tr>
    </table></div><br><br>
<input type="submit" name="submit" value="Interbranch Transfers"><br><br>
<div style="margin-left: 100px;">Complete information on the Interbranch Transfers page.</div><br><br>
<input type="submit" name="submit" value="Audit Charges"><br><br>
<div style="margin-left: 100px;"><table><thead><th>Entries for</th><th>Particulars</th><th>Client</th><th>Debit</th><th>Credit</th></thead>
<tr><td>Audit Charges</td><td>Invoice Number - Nickname</td><td>Employee Name</td><td>ARTrade</td><td>Sales -Others/Discrepancies</td></tr>
<!--<tr><td>Cost of Sales from Audit Charges</td><td></td><td></td><td>Cost of Sales</td><td>Inventory-Internal</td></tr>-->
</table></div><br><br>
</form>
</div>
<br><br>
Types of Sales as subject to VAT:<br>
1. Vatable (12%)<br>
2. Vat-Exempt (no VAT)<br>
3. Zero-Rated (0%)<br>
4. Government (12%)<br>
<?php
if (!isset($_REQUEST[$fieldname])){goto noform;} 
skipchoices:
    
$txndate=$_REQUEST[$fieldname];
$totalwithop=0;
$condition=' ism.Date=\''.$txndate.'\' AND (ism.BranchNo)='.$_SESSION['bnum'].' ';


if(in_array($whichqry, array('CASH Sales (and COGS of Returns)','CHARGE Sales','Audit Charges','SALES RETURNS with EXCHANGES ONLY','AR1 CHARGE Sales'))){
    //check if main form exists:
    $sql='Select TxnID from acctg_2salemain where Date=\''.$txndate.'\' And BranchNo='.$_SESSION['bnum'];
    $stmtmain=$link->query($sql);
    $result=$stmtmain->fetch();
    
    if ($stmtmain->rowCount()==0){
            // add main form
    $sqlmain='Insert into acctg_2salemain (`Date`,`BranchNo`,`EncodedByNo`,`PostedByNo`,`TimeStamp`,`TeamLeader`) 
    SELECT ism.`Date`, ism.BranchNo, '.$_SESSION['(ak0)'].', '.$_SESSION['(ak0)'].', Now(), bg.TeamLeader FROM `invty_2sale` ism LEFT JOIN `attend_1branchgroups` bg ON bg.BranchNo=ism.BranchNo WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].' GROUP BY ism.Date, ism.BranchNo';
    // echo $sqlmain; break;
    $stmt=$link->prepare($sqlmain);
    $stmt->execute();
    $sql='Select `TxnID` from acctg_2salemain where Date=\''.$txndate.'\' And BranchNo='.$_SESSION['bnum'];
    $stmt=$link->query($sql);
    $result=$stmt->fetch();
    $txnid=$result['TxnID'];
    } else {
        $txnid=$result['TxnID'];
    }
    
}




switch ($whichqry){
    
case 'Preview All Entries':
    $title='Data for '.$_SESSION['@brn'].' on '.$txndate;
    
    // cash sales
    $sql='SELECT CONCAT(Min(ism.`SaleNo`)," - ",Max(ism.`SaleNo`)," ", REPLACE(VatType,"(12%)","")) AS Particulars,
        IF(ism.PaymentType=32,"GCashWallet",IF(ism.PaymentType=33,"PaymayaWallet","CASH")) as ClientName, IF(ism.PaymentType=32,"GCashWallet",IF(ism.PaymentType=33,"PaymayaWallet","CASH")) as DebitAccount, ShortAcctID AS `CreditAccount`, FORMAT(SUM(`UnitPrice`*`Qty`)+SUM(IFNULL(op.Amount,0)),2) as Amount
        FROM `invty_2sale` ism join `invty_2salesub` isb on ism.TxnID=isb.TxnID JOIN `1clients` c on c.ClientNo=ism.ClientNo JOIN `gen_info_1vattype` vt ON c.VatTypeNo=vt.VatTypeNo
LEFT JOIN invty_7opapproval op ON op.TxnID=ism.TxnID JOIN acctg_1chartofaccounts ca ON ca.AccountID=(700 + vt.VatTypeNo) WHERE '.$condition.' AND ism.txntype IN (1) GROUP BY ism.txntype';
    
    // charge sales
    $sql.=' UNION ALL SELECT CONCAT(ism.`SaleNo`,"") AS Particulars, ClientName, "ARTrade" AS DebitAccount, "Sales -VATable" AS `CreditAccount`, FORMAT((SUM(`UnitPrice`*`Qty`)+IFNULL(a.Amount,0)),2) AS Amount FROM `invty_2sale` ism JOIN `invty_2salesub` isb ON ism.TxnID=isb.TxnID LEFT JOIN invty_7opapproval a ON a.TxnID=ism.TxnID JOIN `1clients` c ON c.ClientNo=ism.ClientNo WHERE '.$condition.' AND ism.txntype=2 AND c.VatTypeNo=0 GROUP BY ism.txntype, ism.SaleNo 
UNION ALL SELECT CONCAT(ism.`SaleNo`," VAT Exempt") AS Particulars, ClientName, "ARTrade" AS DebitAccount, "Sales -Exempt" AS `CreditAccount`, round((SUM(`UnitPrice`*`Qty`)+IFNULL(a.Amount,0)),2) AS Amount FROM `invty_2sale` ism JOIN `invty_2salesub` isb ON ism.TxnID=isb.TxnID LEFT JOIN invty_7opapproval a ON a.TxnID=ism.TxnID JOIN `1clients` c ON c.ClientNo=ism.ClientNo WHERE '.$condition.' AND ism.txntype=2 AND c.VatTypeNo=1 GROUP BY ism.txntype, ism.SaleNo 
UNION ALL SELECT CONCAT(ism.`SaleNo`," Zero-Rated Sales") AS Particulars, ism.ClientNo, "ARTrade" AS DebitAccount, "Sales - Zero-Rated" AS `CreditAccount`, round((SUM(`UnitPrice`*`Qty`)+IFNULL(a.Amount,0)),2) AS Amount FROM `invty_2sale` ism JOIN `invty_2salesub` isb ON ism.TxnID=isb.TxnID LEFT JOIN invty_7opapproval a ON a.TxnID=ism.TxnID JOIN `1clients` c ON c.ClientNo=ism.ClientNo WHERE '.$condition.' AND ism.txntype=2 AND c.VatTypeNo=2 GROUP BY ism.txntype, ism.SaleNo 
UNION ALL SELECT CONCAT(ism.`SaleNo`," Sales to Govt") AS Particulars, ism.ClientNo, "ARTrade" AS DebitAccount, "Sales -Govt" AS `CreditAccount`, round((SUM(`UnitPrice`*`Qty`)+IFNULL(a.Amount,0)),2) AS Amount FROM `invty_2sale` ism JOIN `invty_2salesub` isb ON ism.TxnID=isb.TxnID LEFT JOIN invty_7opapproval a ON a.TxnID=ism.TxnID JOIN `1clients` c ON c.ClientNo=ism.ClientNo WHERE '.$condition.' AND ism.txntype=2 AND c.VatTypeNo=3 GROUP BY ism.txntype, ism.SaleNo ';
    
    //overprice
    $sql.=' UNION ALL SELECT CONCAT(Min(ism.`SaleNo`)," - ",Max(ism.`SaleNo`), " ", REPLACE(VatType,"(12%)",""), " overprice") AS Particulars, IF(PaymentType=32,"GCash",IF(PaymentType=33,"Paymaya","CASH")) AS ClientName, "Overprice from Sale" as DebitAccount, "APOthers" AS `CreditAccount`,  FORMAT((SUM(Amount)*(1-0.12)),2) AS Amount FROM `invty_2sale` ism JOIN  `1clients` c ON c.ClientNo=ism.ClientNo  JOIN `gen_info_1vattype` vt ON c.VatTypeNo=vt.VatTypeNo
            JOIN invty_7opapproval a ON ism.TxnID=a.TxnID WHERE '.$condition.' AND ism.txntype IN (1)
              GROUP BY ism.txntype, vt.`VatTypeNo`
    UNION ALL
    SELECT CONCAT(Min(ism.`SaleNo`)," - ",Max(ism.`SaleNo`), " ", REPLACE(VatType,"(12%)",""), " vat from op") AS Particulars, IF(PaymentType=32,"GCash",IF(PaymentType=33,"Paymaya","CASH")) AS ClientName, "APOthers" as DebitAccount, "	OP-OutputVatCollected" AS `CreditAccount`, FORMAT((SUM(Amount)*(0.12)),0) as Amount FROM `invty_2sale` ism 
 JOIN `invty_7opapproval` isb ON ism.TxnID=isb.TxnID JOIN `1clients` c ON c.ClientNo=ism.ClientNo JOIN `gen_info_1vattype` vt ON c.VatTypeNo=vt.VatTypeNo 
WHERE '.$condition.'  AND ism.txntype IN (1)  GROUP BY ism.txntype, vt.`VatTypeNo`
     
UNION ALL
SELECT CONCAT(ism.`SaleNo`, " overprice") AS Particulars, ClientName, "Overprice from Sale" as DebitAccount, "APOthers" AS `CreditAccount`, 
            FORMAT((Amount*(1-0.12)),2) AS Amount FROM `invty_2sale` ism JOIN  1clients c ON c.ClientNo=ism.ClientNo 
            JOIN invty_7opapproval a ON ism.TxnID=a.TxnID WHERE '.$condition.'  AND ism.txntype NOT IN (1)
              GROUP BY ism.txntype, ism.SaleNo
    UNION ALL
    SELECT CONCAT(ism.`SaleNo`, " vat from op") AS Particulars, ClientName, "Overprice from Sale" as DebitAccount, "OP-OutputVatCollected" AS `CreditAccount`, 
    FORMAT((Amount*(0.12)),2) as Amount FROM `invty_2sale` ism JOIN  1clients c ON c.ClientNo=ism.ClientNo JOIN invty_7opapproval a ON ism.TxnID=a.TxnID 
    WHERE '.$condition.'  AND ism.txntype NOT IN (1) group by ism.txntype, ism.SaleNo';
      
    //returns with exchanges
    $sql.=' UNION ALL SELECT CONCAT("returns with exchange ",(ism.`SaleNo`)) AS Particulars, ClientName, "Sales Returns" as DebitAccount, "Cash" AS `CreditAccount`, FORMAT(SUM(`UnitPrice`*`Qty`)*-1,2) as Amount
        FROM `invty_2sale` ism join `invty_2salesub` isb on ism.TxnID=isb.TxnID JOIN `1clients` c on c.ClientNo=ism.ClientNo  WHERE '.$condition.' AND ism.TxnID IN (SELECT sm.TxnID FROM invty_2salesub ss JOIN invty_2sale sm ON sm.TxnID=ss.TxnID WHERE ss.Qty>0 AND sm.PaymentType=5) AND isb.Qty<0 AND ism.txntype IN (5) GROUP BY ism.TxnID  UNION ALL SELECT CONCAT("exchanged/replaced item ",(ism.`SaleNo`)) AS Particulars, ClientName, "Cash" as DebitAccount, "Sales-VATable" AS `CreditAccount`, FORMAT(SUM(`UnitPrice`*`Qty`),2) as Amount
        FROM `invty_2sale` ism join `invty_2salesub` isb on ism.TxnID=isb.TxnID JOIN `1clients` c on c.ClientNo=ism.ClientNo  WHERE '.$condition.' AND isb.Qty>0 AND ism.txntype IN (5) GROUP BY ism.TxnID';
    
    
    $columnnames=array('Particulars', 'ClientName', 'DebitAccount', 'CreditAccount', 'Amount') ;
    $width='60%';
    include('../backendphp/layout/displayastable.php');
    break;
    
case 'CASH Sales (and COGS of Returns)':
    
    
//add sub form
    addsubform:
        
    //check overprice first for cash sales
      
          $sqlop='Select ism.Date, ism.txntype,a.BranchNo from invty_7opapproval a '
                  . 'join `invty_2sale` ism on ism.TxnID=a.TxnID '
                  . 'WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum']
                  .' AND ism.txntype in (1)'; 
      $stmtop=$link->query($sqlop); //$resultop=$stmtop->fetchAll();
      if ($stmtop->rowCount()>0){ 
         $sqlopinsert='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) 
                 SELECT '.$txnid.' as TxnID, concat(Min(ism.`SaleNo`),\' - \',Max(ism.`SaleNo`), " ", REPLACE(VatType,"(12%)",""), " overprice") as Particulars, '
                 . '10000 AS ClientNo, 704 AS DebitAccountID, 405 as `CreditAccountID`, '
                 . 'round((SUM(Amount)),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism '
                 . 'join `invty_7opapproval` isb on ism.TxnID=isb.TxnID '
                 . ' join `1clients` c on c.ClientNo=ism.ClientNo '
                 . ' JOIN `gen_info_1vattype` vt ON c.VatTypeNo=vt.VatTypeNo '
                 . 'WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].'  and ism.txntype in (1) 
                      GROUP BY vt.`VatTypeNo`
    UNION ALL
    SELECT '.$txnid.' as TxnID, concat(Min(ism.`SaleNo`),\' - \',Max(ism.`SaleNo`), " ", REPLACE(VatType,"(12%)",""), " vat from op") as Particulars, '
                 . '10000 AS ClientNo, 405 as DebitAccountID, '
                 . '709 as `CreditAccountID`, round((SUM(Amount)*(0.12)),0) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism '
                 . ' JOIN `invty_7opapproval` isb ON ism.TxnID=isb.TxnID '
                 . ' JOIN `1clients` c ON c.ClientNo=ism.ClientNo '
                 . ' JOIN `gen_info_1vattype` vt ON c.VatTypeNo=vt.VatTypeNo '
                 . 'WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].'  and ism.txntype in (1) GROUP BY vt.`VatTypeNo`';
     
     $stmtop2=$link->prepare($sqlopinsert); $stmtop2->execute();
                            
      }
      // echo 'this shows 1';
    //get figures of op
    $sqlop1='CREATE TEMPORARY TABLE opamt AS
        SELECT a.TxnID,c.VatTypeNo, SUM(Amount) AS OPAmt FROM `invty_7opapproval` a JOIN `invty_2sale` ism ON ism.TxnID=a.TxnID '
            . ' JOIN `1clients` c on c.ClientNo=ism.ClientNo WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' '
            . ' AND (ism.BranchNo)='.$_SESSION['bnum'].' AND ism.txntype in (1) group by c.VatTypeNo;'; 
    $stmtop3=$link->prepare($sqlop1); $stmtop3->execute(); //echo 'this shows 2'; break;
      
    $sqlwhere=' FROM `invty_2sale` ism join `invty_2salesub` isb on ism.TxnID=isb.TxnID join `1clients` c on c.ClientNo=ism.ClientNo JOIN `gen_info_1vattype` vt ON c.VatTypeNo=vt.VatTypeNo
LEFT JOIN opamt op ON op.VatTypeNo=c.VatTypeNo AND op.TxnID=ism.TxnID WHERE ism.Date=\''.$txndate.'\' AND (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].' and ism.txntype in (1)'; 
    $sqlgroupby=' GROUP BY c.VatTypeNo,ism.PaymentType;';
    $sql0='SELECT '.$txnid.' as TxnID, CONCAT(Min(ism.`SaleNo`),\' - \',Max(ism.`SaleNo`)," ", REPLACE(VatType,"(12%)","")) AS Particulars,
        10000 as ClientNo, IF(ism.PaymentType=32,107,IF(ism.PaymentType=33,108,100)) as DebitAccountID, (700+vt.VatTypeNo) AS `CreditAccountID`, ROUND((SUM(`UnitPrice`*`Qty`)+IFNULL(op.OPAmt,0)),2) as Amount, '.$_SESSION['(ak0)'].', Now() '.$sqlwhere.$sqlgroupby; 
        
    // add Sales
    $sqlinsert='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) ';
    $sql=$sqlinsert.$sql0; $stmt=$link->prepare($sql); $stmt->execute();
 
    
    //POST INVENTORY DATA
    $sql='UPDATE `invty_2sale` ism SET Posted=1, PostedByNo='.$_SESSION['(ak0)'].' WHERE ism.Date=\''.$txndate.'\' AND (ism.BranchNo)='.$_SESSION['bnum'].'  and ism.txntype in (1,5) AND ism.Posted=0';
    $stmt=$link->prepare($sql); $stmt->execute();
    header('Location:addeditclientside.php?w=Sale&TxnID='.$txnid);
    break;
case 'CHARGE Sales':
case 'Audit Charges':
    $txntype=($whichqry=='CHARGE Sales'?2:3);
    
    //add sub form 
    addsubcharge:
    if ($txntype==2){ // charge sales
      //check overprice first
      $sqlop='Select a.* from invty_7opapproval a join `invty_2sale` ism on ism.TxnID=a.TxnID where ism.BranchNo='.$_SESSION['bnum'].' and ism.txntype='.$txntype.' group by ism.txntype, ism.SaleNo';
      $stmtop=$link->query($sqlop); $resultop=$stmtop->fetchAll();
      if ($stmtop->rowCount()>0){ 
         $sqlopinsert='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) 
    SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`, " overprice") as Particulars, ism.ClientNo, 704 AS DebitAccountID, 405 as `CreditAccountID`, '
                 . 'round((Amount*(1-0.12)),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism '
                 . 'join `invty_7opapproval` isb on ism.TxnID=isb.TxnID left join `1clients` c on c.ClientNo=ism.ClientNo WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].' AND ARClientType<>4  and ism.txntype='.$txntype.' group by ism.txntype, ism.SaleNo
    union all
    SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`, " vat from op") as Particulars, ism.ClientNo, 704 as DebitAccountID, 709 as `CreditAccountID`, round((Amount*(0.12)),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `invty_7opapproval` isb on ism.TxnID=isb.TxnID left join `1clients` c on c.ClientNo=ism.ClientNo WHERE ism.Date=\''.$txndate.'\' AND ARClientType<>4 And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].'  and ism.txntype='.$txntype.' group by ism.txntype, ism.SaleNo';
      
      $stmtop=$link->prepare($sqlopinsert); $stmtop->execute();
}
      //check freight-clients SEPARATE FREIGHT
      $sqlfc='Select fc.* from `approvals_2freightclients` fc join `invty_2sale` ism ON ism.SaleNo=fc.ForInvoiceNo AND ism.BranchNo=fc.BranchNo AND ism.txntype=fc.txntype WHERE PriceFreightInclusive=0 AND ism.BranchNo='.$_SESSION['bnum'].' and ism.txntype='.$txntype.' group by ism.txntype, ism.SaleNo';
      $stmtfc=$link->query($sqlfc);
      $resultfc=$stmtfc->fetchAll();
      if ($stmtfc->rowCount()>0){
         $sqlfcinsert='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) 
    SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`, "Freight") as Particulars, ism.ClientNo, 200 as DebitAccountID, 925 AS `CreditAccountID`, round((Amount),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `approvals_2freightclients` fc ON ism.SaleNo=fc.ForInvoiceNo AND ism.BranchNo=fc.BranchNo AND ism.txntype=fc.txntype left join `1clients` c on c.ClientNo=ism.ClientNo WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].'  and ism.txntype='.$txntype.' AND PriceFreightInclusive=0 AND ARClientType<>4 group by ism.txntype, ism.SaleNo
    ';
      
      $stmtfc=$link->prepare($sqlfcinsert);
    $stmtfc->execute();
      }
      
      //check freight-clients FREIGHT INCLUDED IN PRICE
      $sqlfc='Select fc.* from `approvals_2freightclients` fc join `invty_2sale` ism ON ism.SaleNo=fc.ForInvoiceNo AND ism.BranchNo=fc.BranchNo AND ism.txntype=fc.txntype WHERE PriceFreightInclusive=1 AND Confirmed=0 AND ism.BranchNo='.$_SESSION['bnum'].' and ism.txntype='.$txntype.' group by ism.txntype, ism.SaleNo';
      $stmtfc=$link->query($sqlfc);
      $resultfc=$stmtfc->fetchAll();
      if ($stmtfc->rowCount()>0){
         $sqlwhere=' WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].'  and ism.txntype='.$txntype.' AND PriceFreightInclusive=1 AND ARClientType<>4 group by ism.TxnID ';
         $sqlfcinsert='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) 
    SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`, "FreightAdjIncl") as Particulars, ClientNo, 706 AS DebitAccountID, 925 as `CreditAccountID`, round((Amount),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `approvals_2freightclients` fc ON ism.SaleNo=fc.ForInvoiceNo AND ism.BranchNo=fc.BranchNo AND ism.txntype=fc.txntype left join `1clients` c on c.ClientNo=ism.ClientNo '.$sqlwhere;
    
      // check if freight is covered by selling price
         $sqlmin='CREATE TEMPORARY TABLE FreightInc AS
               SELECT ism.TxnID,ism.SaleNo, ism.ClientNo, (fc.Amount+
			   
			   SUM((SELECT 
						'.$plcase.'
					FROM `1branches` b1 where b1.BranchNo='.$_SESSION['bnum'].'
				)*Qty)
			   ) as MinPriceTotal, SUM(Qty*UnitPrice) as SellPriceTotal
               FROM `invty_5latestminprice` lmp JOIN `invty_2salesub` s ON s.ItemCode=lmp.ItemCode
               JOIN `invty_2sale` ism ON s.TxnID=ism.TxnID 
               JOIN  `approvals_2freightclients` fc ON (fc.ForInvoiceNo=ism.SaleNo AND fc.BranchNo=ism.BranchNo AND fc.txntype=ism.txntype) left join `1clients` c on c.ClientNo=ism.ClientNo '.$sqlwhere;
         $stmtmin=$link->prepare($sqlmin); $stmtmin->execute();
         if ($stmtmin->rowCount()>0){
         $sqlfcinsert=$sqlfcinsert.' UNION ALL SELECT '.$txnid.' as TxnID, concat(SaleNo," ShortInFreight") AS Particulars, ClientNo, 100 as DebitAccountID, 925 as `CreditAccountID`,
               IF(MinPriceTotal>SellPriceTotal,MinPriceTotal-SellPriceTotal,0),  '.$_SESSION['(ak0)'].', Now() FROM FreightInc WHERE (MinPriceTotal-SellPriceTotal)<>0';
         $stmtfc=$link->prepare($sqlfcinsert);
    $stmtfc->execute();
         }
      }
      
      
      // query to insert charge sales
    $sql='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) ';
    $sql0=' SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`,';
    $sql1=') as Particulars, ism.ClientNo, ';
    $sql2=' as DebitAccountID, ';
    $sql3=' AS `CreditAccountID`, ';
    $sql4='round((Sum(`UnitPrice`*`Qty`)+ifnull(a.Amount,0)),2) as Amount, ';
    $sql5=$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `invty_2salesub` isb on ism.TxnID=isb.TxnID left join invty_7opapproval a on a.TxnID=ism.TxnID join `1clients` c on c.ClientNo=ism.ClientNo
    WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].' AND ARClientType<>4  and ism.txntype='.$txntype.' and c.VatTypeNo=';
    $sql6=' group by ism.txntype, ism.SaleNo ';
    $sql7=' round((Sum(`UnitPrice`*`Qty`)+ifnull(a.Amount,0))*(0.12/1.12),2) as Amount, ';
    // add Vatable Sales and VAT
    $sql=$sql.$sql0.'\'\''.$sql1.'200'.$sql2.'700'.$sql3.$sql4.$sql5.'0'.$sql6;
    // add zero-rated
    $sql=$sql.' UNION ALL '.$sql0.'\' VAT Exempt\''.$sql1.'200'.$sql2.'701'.$sql3.$sql4.$sql5.'1'.$sql6;
    // add vat-exempt
    $sql=$sql.' UNION ALL '.$sql0.'\' Zero-Rated Sales\''.$sql1.'200'.$sql2.'702'.$sql3.$sql4.$sql5.'2'.$sql6;
    // add sales to govt and VAT
    $sql=$sql.' UNION ALL '.$sql0.'\' Sales to Govt\''.$sql1.'200'.$sql2.'703'.$sql3.$sql4.$sql5.'3'.$sql6;
    // echo $sql; break;
    $stmt=$link->prepare($sql); $stmt->execute();
    } else { // invty charges
    
    $sql='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`)
    SELECT '.$txnid.' as TxnID, concat(ism.SaleNo,"-",e.Nickname) as Particulars, isb.ChargeToIDNo as ClientNo, 200 as DebitAccountID, 707 AS `CreditAccountID`, round(isb.ChargeAmount,2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `invty_2salesubauditdistri` isb on ism.TxnID=isb.TxnID join `1employees` e on e.IDNo=isb.ChargeToIDNo WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].' AND ism.txntype='.$txntype;
    $stmt=$link->prepare($sql);
    $stmt->execute();
    
    }
    
      
    //POST INVENTORY DATA
    $sql='UPDATE `invty_2sale` ism SET Posted=1, PostedByNo='.$_SESSION['(ak0)'].' WHERE ism.Date=\''.$txndate.'\' AND (ism.BranchNo)='.$_SESSION['bnum'].'  and ism.txntype in (2,3) AND ism.Posted=0';
    $stmt=$link->prepare($sql); $stmt->execute();
    skipexec:
    header('Location:addeditclientside.php?w=Sale&TxnID='.$txnid);
    
    break;
	
CASE 'AR1 CHARGE Sales':

	 $txntype=2;
    if ($txntype==2){ // charge sales
      //check overprice first
      $sqlop='Select a.* from invty_7opapproval a join `invty_2sale` ism on ism.TxnID=a.TxnID where ism.BranchNo='.$_SESSION['bnum'].' and ism.txntype='.$txntype.' group by ism.txntype, ism.SaleNo';
      $stmtop=$link->query($sqlop); $resultop=$stmtop->fetchAll();
      if ($stmtop->rowCount()>0){ 
         $sqlopinsert='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) 
    SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`, " overprice") as Particulars, ism.ClientNo, 704 AS DebitAccountID, 405 as `CreditAccountID`, '
                 . 'round((Amount*(1-0.12)),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism '
                 . 'join `invty_7opapproval` isb on ism.TxnID=isb.TxnID left join `1clients` c on c.ClientNo=ism.ClientNo WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].' AND ARClientType=4  and ism.txntype='.$txntype.' group by ism.txntype, ism.SaleNo
    union all
    SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`, " vat from op") as Particulars, ism.ClientNo, 704 as DebitAccountID, 709 as `CreditAccountID`, round((Amount*(0.12)),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `invty_7opapproval` isb on ism.TxnID=isb.TxnID left join `1clients` c on c.ClientNo=ism.ClientNo WHERE ism.Date=\''.$txndate.'\' AND ARClientType=4 And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].'  and ism.txntype='.$txntype.' group by ism.txntype, ism.SaleNo';
      
      $stmtop=$link->prepare($sqlopinsert); $stmtop->execute();
}
      //check freight-clients SEPARATE FREIGHT
      $sqlfc='Select fc.* from `approvals_2freightclients` fc join `invty_2sale` ism ON ism.SaleNo=fc.ForInvoiceNo AND ism.BranchNo=fc.BranchNo AND ism.txntype=fc.txntype WHERE PriceFreightInclusive=0 AND ism.BranchNo='.$_SESSION['bnum'].' and ism.txntype='.$txntype.' group by ism.txntype, ism.SaleNo';
      $stmtfc=$link->query($sqlfc);
      $resultfc=$stmtfc->fetchAll();
      if ($stmtfc->rowCount()>0){
         $sqlfcinsert='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) 
    SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`, "Freight") as Particulars, ism.ClientNo, 200 as DebitAccountID, 925 AS `CreditAccountID`, round((Amount),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `approvals_2freightclients` fc ON ism.SaleNo=fc.ForInvoiceNo AND ism.BranchNo=fc.BranchNo AND ism.txntype=fc.txntype left join `1clients` c on c.ClientNo=ism.ClientNo WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].'  and ism.txntype='.$txntype.' AND PriceFreightInclusive=0 AND ARClientType=4 group by ism.txntype, ism.SaleNo
    ';
      
      $stmtfc=$link->prepare($sqlfcinsert);
    $stmtfc->execute();
      }
      
      //check freight-clients FREIGHT INCLUDED IN PRICE
      $sqlfc='Select fc.* from `approvals_2freightclients` fc join `invty_2sale` ism ON ism.SaleNo=fc.ForInvoiceNo AND ism.BranchNo=fc.BranchNo AND ism.txntype=fc.txntype WHERE PriceFreightInclusive=1 AND Confirmed=0 AND ism.BranchNo='.$_SESSION['bnum'].' and ism.txntype='.$txntype.' group by ism.txntype, ism.SaleNo';
      $stmtfc=$link->query($sqlfc);
      $resultfc=$stmtfc->fetchAll();
      if ($stmtfc->rowCount()>0){
         $sqlwhere=' WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].'  and ism.txntype='.$txntype.' AND PriceFreightInclusive=1 AND ARClientType=4 group by ism.TxnID ';
         $sqlfcinsert='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) 
    SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`, "FreightAdjIncl") as Particulars, ClientNo, 706 AS DebitAccountID, 925 as `CreditAccountID`, round((Amount),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `approvals_2freightclients` fc ON ism.SaleNo=fc.ForInvoiceNo AND ism.BranchNo=fc.BranchNo AND ism.txntype=fc.txntype left join `1clients` c on c.ClientNo=ism.ClientNo '.$sqlwhere;
   
      // check if freight is covered by selling price
         $sqlmin='CREATE TEMPORARY TABLE FreightInc AS
               SELECT ism.TxnID,ism.SaleNo, ism.ClientNo, (fc.Amount+
			   
			   SUM((SELECT 
						'.$plcase.'
					FROM `1branches` b1 where b1.BranchNo='.$_SESSION['bnum'].'
				)*Qty)
			   ) as MinPriceTotal, SUM(Qty*UnitPrice) as SellPriceTotal
               FROM `invty_5latestminprice` lmp JOIN `invty_2salesub` s ON s.ItemCode=lmp.ItemCode
               JOIN `invty_2sale` ism ON s.TxnID=ism.TxnID 
               JOIN  `approvals_2freightclients` fc ON (fc.ForInvoiceNo=ism.SaleNo AND fc.BranchNo=ism.BranchNo AND fc.txntype=ism.txntype) left join `1clients` c on c.ClientNo=ism.ClientNo '.$sqlwhere;
         $stmtmin=$link->prepare($sqlmin); $stmtmin->execute();
         if ($stmtmin->rowCount()>0){
         $sqlfcinsert=$sqlfcinsert.' UNION ALL SELECT '.$txnid.' as TxnID, concat(SaleNo," ShortInFreight") AS Particulars, ClientNo, 100 as DebitAccountID, 925 as `CreditAccountID`,
               IF(MinPriceTotal>SellPriceTotal,MinPriceTotal-SellPriceTotal,0),  '.$_SESSION['(ak0)'].', Now() FROM FreightInc WHERE (MinPriceTotal-SellPriceTotal)<>0';
         $stmtfc=$link->prepare($sqlfcinsert);
    $stmtfc->execute();
         }
      }
      
      
      // query to insert charge sales
    $sql='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) ';
    $sql0=' SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`,';
    $sql1=') as Particulars, ism.ClientNo, ';
    $sql2=' as DebitAccountID, ';
    $sql3=' AS `CreditAccountID`, ';
    $sql4='round((Sum(`UnitPrice`*`Qty`)+ifnull(a.Amount,0)),2) as Amount, ';
    $sql5=$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `invty_2salesub` isb on ism.TxnID=isb.TxnID left join invty_7opapproval a on a.TxnID=ism.TxnID join `1clients` c on c.ClientNo=ism.ClientNo
    WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].' AND ARClientType=4  and ism.txntype='.$txntype.' and c.VatTypeNo=';
    $sql6=' group by ism.txntype, ism.SaleNo ';
    $sql7=' round((Sum(`UnitPrice`*`Qty`)+ifnull(a.Amount,0))*(0.12/1.12),2) as Amount, ';
    // add Vatable Sales and VAT
    $sql=$sql.$sql0.'\'\''.$sql1.'200'.$sql2.'700'.$sql3.$sql4.$sql5.'0'.$sql6;
    // add zero-rated
    $sql=$sql.' UNION ALL '.$sql0.'\' VAT Exempt\''.$sql1.'200'.$sql2.'701'.$sql3.$sql4.$sql5.'1'.$sql6;
    // add vat-exempt
    $sql=$sql.' UNION ALL '.$sql0.'\' Zero-Rated Sales\''.$sql1.'200'.$sql2.'702'.$sql3.$sql4.$sql5.'2'.$sql6;
    // add sales to govt and VAT
    $sql=$sql.' UNION ALL '.$sql0.'\' Sales to Govt\''.$sql1.'200'.$sql2.'703'.$sql3.$sql4.$sql5.'3'.$sql6;
    // echo $sql; break;
    $stmt=$link->prepare($sql); $stmt->execute();
    } 
      
    //POST INVENTORY DATA
    $sql='UPDATE `invty_2sale` ism SET Posted=1, PostedByNo='.$_SESSION['(ak0)'].' WHERE ism.Date=\''.$txndate.'\' AND (ism.BranchNo)='.$_SESSION['bnum'].'  and ism.txntype in (2,3) AND ism.Posted=0';
    $stmt=$link->prepare($sql); $stmt->execute();

    header('Location:addeditclientside.php?w=Sale&TxnID='.$txnid);
    

break;

case 'SALES RETURNS with EXCHANGES ONLY':
    $sql='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`)  '
            . ' SELECT '.$txnid.' as TxnID, CONCAT("returns with exchange ",(ism.`SaleNo`)) AS Particulars, ism.ClientNo, 705 as DebitAccountID, 100 AS `CreditAccountID`, round(SUM(`UnitPrice`*`Qty`)*-1,2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `invty_2salesub` isb on ism.TxnID=isb.TxnID WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].' AND ism.TxnID IN (SELECT sm.TxnID FROM invty_2salesub ss JOIN invty_2sale sm ON sm.TxnID=ss.TxnID WHERE ss.Qty>0 AND sm.PaymentType=5) AND isb.Qty<0 AND ism.txntype IN (5) GROUP BY ism.TxnID '
        . ' UNION  SELECT '.$txnid.' as TxnID, CONCAT("exchanged/replaced item ",(ism.`SaleNo`)) AS Particulars, ism.ClientNo, 100 as DebitAccountID, 700 AS `CreditAccountID`, round(SUM(`UnitPrice`*`Qty`),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `invty_2salesub` isb on ism.TxnID=isb.TxnID WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$_SESSION['bnum'].' AND isb.Qty>0 AND ism.txntype IN (5) GROUP BY ism.TxnID'; 
    //echo $sql;
    $stmt=$link->prepare($sql); $stmt->execute();
    
    header('Location:addeditclientside.php?w=Sale&TxnID='.$txnid);
    break;

    
case 'Interbranch Transfers':
    //check if main form exists:
    $sql='Select TxnID from acctg_2txfrmain where Date=\''.$txndate.'\' And FromBranchNo='.$_SESSION['bnum'];
    $stmt=$link->query($sql);
    $result=$stmt->fetch();
    
    if ($stmt->rowCount()==0){
            // add main form 
    $sqlmain='INSERT INTO acctg_2txfrmain ( `Date`, FromBranchNo, EncodedByNo, PostedByNo, `TimeStamp`, CreditAccountID )
SELECT tm.DateOUT as `Date`, tm.BranchNo, '.$_SESSION['(ak0)'].', '.$_SESSION['(ak0)'].', Now(), 300 as CreditAccountID
FROM `invty_2transfer` tm JOIN `1branches` b ON b.BranchNo=tm.BranchNo WHERE tm.DateOUT=\''.$txndate.'\' And (tm.DateOUT)>\''.$_SESSION['nb4A'].'\' AND tm.BranchNo='.$_SESSION['bnum'].' and tm.ToBranchNo <> '.$_SESSION['bnum'].' GROUP BY tm.DateOUT, tm.BranchNo';
    
    $stmt=$link->prepare($sqlmain);
    $stmt->execute();
    $sql='Select `TxnID` from acctg_2txfrmain where Date=\''.$txndate.'\' And FromBranchNo='.$_SESSION['bnum'];
    $stmt=$link->query($sql);
    $result=$stmt->fetch();
    $txnid=$result['TxnID'];
        goto addsubtxfr;
    } else {
        $txnid=$result['TxnID'];
           goto addsubtxfr;
    }
    //add sub form
    addsubtxfr:
    $sql='INSERT INTO acctg_2txfrsub ( TxnID, Particulars, ClientBranchNo, DebitAccountID, Amount, OUTEncodedByNo, OUTTimeStamp, INEncodedByNo, INTimeStamp, DateIN)
SELECT '.$txnid.' as TxnID, tm.TransferNo, tm.ToBranchNo, 204 AS DebitAccountID, round(Sum(UnitPrice*QtySent),2) AS Amount, '.$_SESSION['(ak0)'].', Now(), ts.TOEncodedByNo, ts.TOTimeStamp, tm.DateIN
FROM  `invty_2transfer` tm JOIN `invty_2transfersub` ts ON tm.TxnID = ts.TxnID
WHERE tm.BranchNo='.$_SESSION['bnum'].' AND tm.DateOUT=\''.$txndate.'\' And (tm.DateOUT)>\''.$_SESSION['nb4A'].'\'  and tm.ToBranchNo<>'.$_SESSION['bnum'].' GROUP BY tm.TransferNo, tm.ToBranchNo';

    $stmt=$link->prepare($sql); $stmt->execute();
        
    //POST INVENTORY DATA - TRANSFER OUT
    $sql='UPDATE `invty_2transfer` tm SET Posted=1, PostedByNo='.$_SESSION['(ak0)'].' WHERE tm.DateOUT=\''.$txndate.'\' AND tm.BranchNo='.$_SESSION['bnum'].'  AND tm.Posted=0';
    $stmt=$link->prepare($sql); $stmt->execute();
    
    // RECORD DATE IN
    $sql='UPDATE acctg_2txfrsub ats JOIN  `invty_2transfer` tm ON tm.ToBranchNo=ats.ClientBranchNo AND tm.TransferNo=ats.Particulars
    SET ats.DateIN=tm.DateIN, ats.INTimeStamp=Now(), ats.INEncodedByNo='.$_SESSION['(ak0)'].' WHERE tm.ToBranchNo='.$_SESSION['bnum'].' AND tm.DateIN=\''.$txndate.'\'';
    $stmt=$link->prepare($sql); $stmt->execute();
    
    //POST INVENTORY DATA - TRANSFER IN
    $sql='UPDATE `invty_2transfer` tm SET PostedIn=1, PostedInByNo='.$_SESSION['(ak0)'].' WHERE tm.DateIN=\''.$txndate.'\' AND tm.ToBranchNo='.$_SESSION['bnum'].'  AND tm.PostedIn=0';
    $stmt=$link->prepare($sql); $stmt->execute();
    
    header('Location:addeditclientside.php?w=Interbranch&TxnID='.$txnid);
    
    
    break;

}
noform:
      $link=null; $stmt=null;
?>