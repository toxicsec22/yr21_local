<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(516,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');
 
 

$fieldname='Date';
$title='Add Inventory Transactions to Acctg';
$method='GET';
include_once('../backendphp/layout/clickontabletoedithead.php');
include_once('../invty/invlayout/pricelevelcase.php');
?>
<div style="margin-left: 50px;">
<form method="post" action="addtoacctg.php" enctype="multipart/form-data">
    Choose Date:  <input type="date" name="<?php echo $fieldname; ?>" value="<?php echo date('Y-m-d',strtotime("-1 days")); ?>"></input> &nbsp; &nbsp; This may take some time to finish all entries. &nbsp; &nbsp; 
<input type="submit" name="submit" value="ENTER ALL"></form> <br><br>
<a href="addtoacctg_specific.php">Encode specific transactions</a> <br><br>

CASH SALES
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
CHARGE SALES<br><br>
<div style="margin-left: 100px;"><table><thead><th>Entries for</th><th>Particulars</th><th>Client</th><th>Debit</th><th>Credit</th></thead>
<tr><td>Charge Sales</td><td>Invoice Number - VAT Type</td><td>Client Name</td><td>ARTrade</td><td>Sales (VAT Type)</td></tr>
<tr><td>VAT from Charge Sales</td><td>Invoice Number - VAT</td><td>Client Name</td><td>Sales (VAT Type)</td><td>Output VAT</td></tr>
<!--<tr><td>Cost of Sales from Charge Sales</td><td></td><td></td><td>Cost of Sales</td><td>Inventory-Internal</td></tr>-->
<tr><td>Overprice from Charge Sales</td><td>Invoice Number - overprice</td><td>Client Name</td><td>Overprice from Sale</td><td>AP Others</td></tr>
<tr><td colspan="5">Vat Collection for OP is done when OP is paid to close AP Others.</td></tr>
<tr><td>Freight - not included in price</td><td>Invoice Number - Freight</td><td>Client Name</td><td>ARTrade</td><td>Freight (Clients) Expense</td></tr>
<tr><td>Freight - included in price</td><td>Invoice Number - FreightAdjIncl</td><td>Client Name</td><td>Discounts</td><td>Freight (Clients) Expense</td></tr>
    </table></div><br><br>
SALES RETURNS with EXCHANGES ONLY
<div style="margin-left: 100px;"><table><thead><th>Entries for</th><th>Particulars</th><th>Client</th><th>Debit</th><th>Credit</th></thead>
<tr><td>Returned items</td><td>Per sales return slip</td><td>Client Name</td><td>Sales Returns</td><td>Cash on Hand</td></tr>
<tr><td>Exchanged items</td><td>Per sales return slip</td><td>Client Name</td><td>Cash on Hand</td><td>Sales (Vatable)</td></tr>
    </table></div><br><br>
Interbranch Transfers<br><br>
<div style="margin-left: 100px;">Complete information on the Interbranch Transfers page.</div><br><br>
Audit Charges<br><br>
<div style="margin-left: 100px;"><table><thead><th>Entries for</th><th>Particulars</th><th>Client</th><th>Debit</th><th>Credit</th></thead>
<tr><td>Audit Charges</td><td>Invoice Number - Nickname</td><td>Employee Name</td><td>ARTrade</td><td>Sales -Others/Discrepancies</td></tr>
<!--<tr><td>Cost of Sales from Audit Charges</td><td></td><td></td><td>Cost of Sales</td><td>Inventory-Internal</td></tr>-->
</table></div><br><br>
</div>
<br><br>
Types of Sales as subject to VAT:<br>
1. Vatable (12%)<br>
2. Vat-Exempt (no VAT)<br>
3. Zero-Rated (0%)<br>
4. Government (12%)<br>
<?php
if (!isset($_REQUEST[$fieldname])){goto noform;} 


$txndate=$_REQUEST[$fieldname];
$whichqry=$_REQUEST['submit'];
$totalwithop=0;


//POST ALL INVTY TXNS FIRST
include_once ('../backendphp/functions/postperdate.php');

    
   $posttables=array('invty_2sale','invty_2mrr','invty_2pr','invty_3order','invty_4adjust');
   foreach ($posttables as $table){
    postperdate($link,$table,$txndate,false);
   }
   postperdate($link,'invty_2transfer',$txndate,'Out');
   postperdate($link,'invty_2transfer',$txndate,'In');
// END POST ALL


skippost:

$sqlbranches='SELECT BranchNo from `1branches` where Active=1 and PseudoBranch<>1';
$stmtbranch=$link->query($sqlbranches); $resultbranch=$stmtbranch->fetchAll(); 
$branch='';
foreach ($resultbranch as $res){
    $branch=$res['BranchNo']; //echo $branch; break;goto nextrow;
// CASH Sales (and COGS of Returns]
    //check if main form exists:
    $sql='Select TxnID from acctg_2salemain where Date=\''.$txndate.'\' And BranchNo='.$branch;
    $stmt=$link->query($sql);
    $result=$stmt->fetch();
    
    if ($stmt->rowCount()==0){
        $sqlsales='SELECT TxnID FROM `invty_2sale` ism WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch;
        $stmtsales=$link->query($sqlsales); $resultsales=$stmtsales->fetch();
        if ($stmtsales->rowCount()==0){ goto skipsales;}
        
            // add main form
    $sqlmain='Insert into acctg_2salemain (`Date`,`BranchNo`,`EncodedByNo`,`PostedByNo`,`TimeStamp`,`TeamLeader`) SELECT ism.`Date`, ism.BranchNo, '.$_SESSION['(ak0)'].', '.$_SESSION['(ak0)'].', Now(), bg.TeamLeader FROM `invty_2sale` ism LEFT JOIN `attend_1branchgroups` bg ON bg.BranchNo=ism.BranchNo WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch.' GROUP BY ism.Date, ism.BranchNo ';
    if($_SESSION['(ak0)']==1002){ echo $sqlmain; }
    $stmt=$link->prepare($sqlmain);
    $stmt->execute();
        
    $sql='Select `TxnID` from acctg_2salemain where Date=\''.$txndate.'\' And BranchNo='.$branch;
    $stmt=$link->query($sql);
    $result=$stmt->fetch();
    $txnid=$result['TxnID'];
        goto addsubform;
    } else {
        $txnid=$result['TxnID'];
           goto addsubform;
    }
    
//add sub form
    addsubform:
        
    //check overprice first for cash sales
      
          $sqlop='Select ism.Date, ism.txntype,a.BranchNo from invty_7opapproval a '
                  . 'join `invty_2sale` ism on ism.TxnID=a.TxnID '
                  . 'WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch
                  .' AND ism.txntype=1'; 
      $stmtop=$link->query($sqlop); //$resultop=$stmtop->fetchAll();
      if ($stmtop->rowCount()>0){ 
         $sqlopinsert='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) 
                 SELECT '.$txnid.' as TxnID, concat(Min(ism.`SaleNo`),\' - \',Max(ism.`SaleNo`), " ", REPLACE(VatType,"(12%)",""), " overprice") as Particulars, '
                 . '10000 AS ClientNo, 704 AS DebitAccountID, 405 as `CreditAccountID`, '
                 . 'round((SUM(Amount)),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism '
                 . 'join `invty_7opapproval` isb on ism.TxnID=isb.TxnID '
                 . ' join `1clients` c on c.ClientNo=ism.ClientNo '
                 . ' JOIN `gen_info_1vattype` vt ON c.VatTypeNo=vt.VatTypeNo '
                 . 'WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch.'  and ism.txntype=1 
                      GROUP BY vt.`VatTypeNo`
    UNION ALL
    SELECT '.$txnid.' as TxnID, concat(Min(ism.`SaleNo`),\' - \',Max(ism.`SaleNo`), " ", REPLACE(VatType,"(12%)",""), " vat from op") as Particulars, '
                 . '10000 AS ClientNo, 405 as DebitAccountID, '
                 . '709 as `CreditAccountID`, round((SUM(Amount)*(0.12)),0) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism '
                 . ' JOIN `invty_7opapproval` isb ON ism.TxnID=isb.TxnID '
                 . ' JOIN `1clients` c ON c.ClientNo=ism.ClientNo '
                 . ' JOIN `gen_info_1vattype` vt ON c.VatTypeNo=vt.VatTypeNo '
                 . 'WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch.'  and ism.txntype=1 GROUP BY vt.`VatTypeNo`';
     
     $stmtop2=$link->prepare($sqlopinsert); $stmtop2->execute();
                            
      }
      
    //get figures of op
    $sqlop1='CREATE TEMPORARY TABLE opamt'.$branch.' AS
        SELECT ism.PaymentType,c.VatTypeNo, SUM(Amount) AS OPAmt FROM `invty_7opapproval` a JOIN `invty_2sale` ism ON ism.TxnID=a.TxnID '
            . ' JOIN `1clients` c on c.ClientNo=ism.ClientNo WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' '
            . ' AND (ism.BranchNo)='.$branch.' AND ism.txntype=1 group by c.VatTypeNo,ism.PaymentType;'; 
    $stmtop3=$link->prepare($sqlop1); $stmtop3->execute(); //echo 'this shows 2'; break;
      // end of op
    
    $sqlwhere=' FROM `invty_2sale` ism join `invty_2salesub` isb on ism.TxnID=isb.TxnID join `1clients` c on c.ClientNo=ism.ClientNo JOIN `gen_info_1vattype` vt ON c.VatTypeNo=vt.VatTypeNo
LEFT JOIN opamt'.$branch.' op ON op.VatTypeNo=c.VatTypeNo AND op.PaymentType=ism.PaymentType WHERE ism.Date=\''.$txndate.'\' AND (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch.' and ism.txntype=1'; 
    $sqlgroupby=' GROUP BY c.VatTypeNo,ism.PaymentType;';
    $sql0='SELECT '.$txnid.' as TxnID, CONCAT(Min(ism.`SaleNo`),\' - \',Max(ism.`SaleNo`)," ", REPLACE(VatType,"(12%)","")) AS Particulars,
        10000 as ClientNo, IF(ism.PaymentType=32,107,IF(ism.PaymentType=33,108,100)) as DebitAccountID, (700+vt.VatTypeNo) AS `CreditAccountID`, ROUND((SUM(`UnitPrice`*`Qty`)+IFNULL(op.OPAmt,0)),2) as Amount, '.$_SESSION['(ak0)'].', Now() '.$sqlwhere.$sqlgroupby; 
        
    // add Sales
    $sqlinsert='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) '; 
    $sql=$sqlinsert.$sql0; $stmt=$link->prepare($sql); $stmt->execute();
 
    // add cost of goods of cash sales and returns
    $month=substr($txndate,5,2); 
    
    
// CHARGE Sales AND Audit Charges
  //  $txntype=($whichqry=='CHARGE Sales'?2:3);
   
    //add sub form 
    addsubcharge:
     // charge sales
      //check overprice first
      $sqlop='Select a.* from invty_7opapproval a join `invty_2sale` ism on ism.TxnID=a.TxnID where ism.BranchNo='.$branch.' and ism.txntype=2 group by ism.txntype, ism.SaleNo'; 
      $stmtop=$link->query($sqlop); $resultop=$stmtop->fetchAll();
      if ($stmtop->rowCount()>0){ 
         $sqlopinsert='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) 
    SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`, " overprice") as Particulars, ClientNo, 704 AS DebitAccountID, 405 as `CreditAccountID`, '
                 . 'round((Amount*(1-0.12)),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism '
                 . 'join `invty_7opapproval` isb on ism.TxnID=isb.TxnID WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch.'  and ism.txntype=2 group by ism.txntype, ism.SaleNo
    union all
    SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`, " vat from op") as Particulars, ClientNo, 704 as DebitAccountID, 709 as `CreditAccountID`, round((Amount*(0.12)),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `invty_7opapproval` isb on ism.TxnID=isb.TxnID WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch.'  and ism.txntype=2 group by ism.txntype, ism.SaleNo';
      
      $stmtop=$link->prepare($sqlopinsert); $stmtop->execute();
}
      //check freight-clients SEPARATE FREIGHT
      $sqlfc='Select fc.* from `approvals_2freightclients` fc join `invty_2sale` ism ON ism.SaleNo=fc.ForInvoiceNo AND ism.BranchNo=fc.BranchNo AND ism.txntype=fc.txntype WHERE PriceFreightInclusive=0 AND ism.BranchNo='.$branch.' and ism.txntype=2 group by ism.txntype, ism.SaleNo';
      $stmtfc=$link->query($sqlfc);
      $resultfc=$stmtfc->fetchAll();
      if ($stmtfc->rowCount()>0){
         $sqlfcinsert='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) 
    SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`, "Freight") as Particulars, ClientNo, 200 as DebitAccountID, 925 AS `CreditAccountID`, round((Amount),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `approvals_2freightclients` fc ON ism.SaleNo=fc.ForInvoiceNo AND ism.BranchNo=fc.BranchNo AND ism.txntype=fc.txntype WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch.'  and ism.txntype=2 AND PriceFreightInclusive=0 group by ism.txntype, ism.SaleNo
    ';
      
      $stmtfc=$link->prepare($sqlfcinsert);     $stmtfc->execute();
      }
      
      //check freight-clients FREIGHT INCLUDED IN PRICE
      $sqlfc='Select fc.* from `approvals_2freightclients` fc join `invty_2sale` ism ON ism.SaleNo=fc.ForInvoiceNo AND ism.BranchNo=fc.BranchNo AND ism.txntype=fc.txntype WHERE PriceFreightInclusive=1 AND Confirmed=0 AND ism.BranchNo='.$branch.' and ism.txntype=2 group by ism.txntype, ism.SaleNo';
      $stmtfc=$link->query($sqlfc);
      $resultfc=$stmtfc->fetchAll();
      if ($stmtfc->rowCount()>0){
         $sqlwhere=' WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch.'  and ism.txntype=2 AND PriceFreightInclusive=1 group by ism.TxnID ';
         // Freight is removed as discounts since it will be paid out as expense
         $sqlfcinsert='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`) 
    SELECT '.$txnid.' as TxnID, concat(ism.`SaleNo`, "FreightAdjIncl") as Particulars, ClientNo, 706 AS DebitAccountID, 925 as `CreditAccountID`, round((Amount),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `approvals_2freightclients` fc ON ism.SaleNo=fc.ForInvoiceNo AND ism.BranchNo=fc.BranchNo AND ism.txntype=fc.txntype '.$sqlwhere;
    
      // check if freight is covered by selling price
         $sqlmin='DROP TEMPORARY TABLE IF EXISTS FreightInc';$stmtmin=$link->prepare($sqlmin); $stmtmin->execute();
                 
         $sqlmin='CREATE TEMPORARY TABLE FreightInc AS
               SELECT ism.TxnID,ism.SaleNo, ism.ClientNo, (fc.Amount+
				SUM((SELECT 
						'.$plcase.'
					FROM `1branches` b1 where b1.BranchNo='.$branch.'
				)*Qty)	
			   ) as MinPriceTotal, SUM(Qty*UnitPrice) as SellPriceTotal
               FROM `invty_5latestminprice` lmp JOIN `invty_2salesub` s ON s.ItemCode=lmp.ItemCode
               JOIN `invty_2sale` ism ON s.TxnID=ism.TxnID 
               JOIN  `approvals_2freightclients` fc ON (fc.ForInvoiceNo=ism.SaleNo AND fc.BranchNo=ism.BranchNo AND fc.txntype=ism.txntype) '.$sqlwhere;
         $stmtmin=$link->prepare($sqlmin); $stmtmin->execute();
         if ($stmtmin->rowCount()>0){
         $sqlfcinsert=$sqlfcinsert.' UNION ALL SELECT '.$txnid.' as TxnID, concat(SaleNo," ShortInFreight") AS Particulars, ClientNo, 100 as DebitAccountID, 925 as `CreditAccountID`,
               IF(MinPriceTotal>SellPriceTotal,MinPriceTotal-SellPriceTotal,0),  '.$_SESSION['(ak0)'].', Now() FROM FreightInc WHERE (MinPriceTotal-SellPriceTotal)<>0';
         $stmtfc=$link->prepare($sqlfcinsert);     $stmtfc->execute();
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
    WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch.'  and ism.txntype=2 and c.VatTypeNo=';
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
    
    
    // invty charges
    
    $sql='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`)
    SELECT '.$txnid.' as TxnID, concat(ism.SaleNo,"-",e.Nickname) as Particulars, isb.ChargeToIDNo as ClientNo, 200 as DebitAccountID, 707 AS `CreditAccountID`, round(isb.ChargeAmount,2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `invty_2salesubauditdistri` isb on ism.TxnID=isb.TxnID join `1employees` e on e.IDNo=isb.ChargeToIDNo WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch.'  and ism.txntype=3';
    $stmt=$link->prepare($sql);
    $stmt->execute();
    
    
    //'SALES RETURNS with EXCHANGES ONLY':
    $sql='Insert into acctg_2salesub (TxnID, Particulars, ClientNo, DebitAccountID, `CreditAccountID`, Amount, EncodedByNo, `TimeStamp`)  '
            . ' SELECT '.$txnid.' as TxnID, CONCAT("returns with exchange ",(ism.`SaleNo`)) AS Particulars, ism.ClientNo, 705 as DebitAccountID, 100 AS `CreditAccountID`, round(SUM(`UnitPrice`*`Qty`)*-1,2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `invty_2salesub` isb on ism.TxnID=isb.TxnID WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch.' AND ism.TxnID IN (SELECT sm.TxnID FROM invty_2salesub ss JOIN invty_2sale sm ON sm.TxnID=ss.TxnID WHERE ss.Qty>0 AND sm.PaymentType=5) AND isb.Qty<0 AND ism.txntype IN (5) GROUP BY ism.TxnID '
        . ' UNION  SELECT '.$txnid.' as TxnID, CONCAT("exchanged/replaced item ",(ism.`SaleNo`)) AS Particulars, ism.ClientNo, 100 as DebitAccountID, 700 AS `CreditAccountID`, round(SUM(`UnitPrice`*`Qty`),2) as Amount, '.$_SESSION['(ak0)'].', Now() FROM `invty_2sale` ism join `invty_2salesub` isb on ism.TxnID=isb.TxnID WHERE ism.Date=\''.$txndate.'\' And (ism.Date)>\''.$_SESSION['nb4A'].'\' AND (ism.BranchNo)='.$branch.' AND isb.Qty>0 AND ism.txntype IN (5) GROUP BY ism.TxnID'; 
    //echo $sql;
    $stmt=$link->prepare($sql); $stmt->execute();
    
   
    skipsales:
   // Interbranch Transfers':
   // 
   // Check if there is transfer data:
   $sql='SELECT TxnID FROM `invty_2transfer` tm WHERE tm.DateOUT=\''.$txndate.'\' And (tm.DateOUT)>\''.$_SESSION['nb4A'].'\' AND tm.BranchNo='.$branch.' and tm.ToBranchNo <> '.$branch.' GROUP BY tm.DateOUT, tm.BranchNo';
   $stmttxfr=$link->query($sql); $result=$stmttxfr->fetch();
    if ($stmttxfr->rowCount()==0){ goto skiptxfrout;}
    //check if main form exists:
    $sql='Select TxnID from acctg_2txfrmain where Date=\''.$txndate.'\' And FromBranchNo='.$branch;
    $stmttxfr=$link->query($sql); $result=$stmttxfr->fetch();
    
    if ($stmttxfr->rowCount()==0){ 
            // add main form 
    $sqlmain='INSERT INTO acctg_2txfrmain ( `Date`, FromBranchNo, EncodedByNo, PostedByNo, `TimeStamp`, CreditAccountID )
SELECT tm.DateOUT as `Date`, tm.BranchNo, '.$_SESSION['(ak0)'].', '.$_SESSION['(ak0)'].', Now(), 300 as CreditAccountID
FROM `invty_2transfer` tm JOIN `1branches` b ON b.BranchNo=tm.BranchNo WHERE tm.DateOUT=\''.$txndate.'\' And (tm.DateOUT)>\''.$_SESSION['nb4A'].'\' AND tm.BranchNo='.$branch.' and tm.ToBranchNo <> '.$branch.' GROUP BY tm.DateOUT, tm.BranchNo';
   // echo $sqlmain;
    $stmt=$link->prepare($sqlmain);  $stmt->execute();
    $sql='Select `TxnID` from acctg_2txfrmain where Date=\''.$txndate.'\' And FromBranchNo='.$branch;
    $stmt=$link->query($sql);  $result=$stmt->fetch();
    $txnid=$result['TxnID'];
        goto addsubtxfr;
    } else { $txnid=$result['TxnID']; goto addsubtxfr;   }
    //add sub form
    addsubtxfr:
    $sql='INSERT INTO acctg_2txfrsub ( TxnID, Particulars, ClientBranchNo, DebitAccountID, Amount, OUTEncodedByNo, OUTTimeStamp, INEncodedByNo, INTimeStamp, DateIN)
SELECT '.$txnid.' as TxnID, tm.TransferNo, tm.ToBranchNo, 204 AS DebitAccountID, round(Sum(UnitPrice*QtySent),2) AS Amount, '.$_SESSION['(ak0)'].', Now(), ts.TOEncodedByNo, ts.TOTimeStamp, tm.DateIN
FROM  `invty_2transfer` tm JOIN `invty_2transfersub` ts ON tm.TxnID = ts.TxnID
WHERE tm.BranchNo='.$branch.' AND tm.DateOUT=\''.$txndate.'\' And (tm.DateOUT)>\''.$_SESSION['nb4A'].'\'  and tm.ToBranchNo<>'.$branch.' GROUP BY tm.TransferNo, tm.ToBranchNo';
// echo $sql;
    $stmt=$link->prepare($sql); $stmt->execute();
    if (in_array($branch,array(40,27,65))){
    // add adjusting entries for transfers
    $month=substr($txndate,5,2);
    
    }
    
    skiptxfrout:
      
    // RECORD DATE IN
    $sql='UPDATE acctg_2txfrsub ats JOIN  `invty_2transfer` tm ON tm.ToBranchNo=ats.ClientBranchNo AND tm.TransferNo=ats.Particulars
    SET ats.DateIN=tm.DateIN, ats.INTimeStamp=Now(), ats.INEncodedByNo='.$_SESSION['(ak0)'].' WHERE tm.ToBranchNo='.$branch.' AND tm.DateIN=\''.$txndate.'\'';
    $stmt=$link->prepare($sql); $stmt->execute();
    nextrow:
}      
  
    header("Location:addedtoacctgsummary.php");
    
noform:
      $link=null; $stmt=null;
?>