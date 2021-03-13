<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(5515,'1rtc')) { echo 'No permission'; exit(); }
$showbranches=false;
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
$sqldataasof='SELECT `DataAsOf` FROM `00staticdataasof` WHERE `ForDB`=1';
$stmtasof=$link->query($sqldataasof); $dataasof=$stmtasof->fetch();
?><title>Export Data</title>
<h3>Export Data for 1RTC Portal</h3><br/>
<form method="POST" action="exportforportal.php?done=1" enctype="multipart/form-data">
    Static data of receivables as of <?php echo $dataasof['DataAsOf']; ?><br/><br/>
	<?php if (allowedToOpen(5516,'1rtc')) {  ?>
<input type='submit' name='submit' value='Export for Client Portal (may take up to 5 minutes)'>

<?php } if (allowedToOpen(5517,'1rtc')) {  ?>
	<input type='submit' name='submit1' value='Export for Supplier Portal (may take up to 5 minutes)'>
	<input type='submit' name='submit2' value='Export for Mrr (may take up to 5 minutes)'><?php }?>
</form>
<?php

if (isset($_GET['done']) and $_GET['done']==1) { echo '<br/><br/><h3 style="color: darkgreen;">Data has been exported.</h3>';}
if ((!isset($_POST['submit']) AND (!isset($_POST['submit1'])) AND (!isset($_POST['submit2'])))) { goto noform;}
include_once $path.'/acrossyrs/commonfunctions/exportassql.php'; 
if (isset($_POST['submit'])){
        $sqlclient='SELECT ClientNo as ASCNo, `ClientName` as ASCCompanyName, ContactPerson, StreetAddress, Barangay, TownOrCity,  Province, TelNo1, Mobile, EmailAddress, Terms, CreditLimit, PORequired, ARClientType AS ARClient,  TIN, 11 AS AccessLevel, Inactive, EncodedByNo, TimeStamp from 1clients where ClientNo in 
(Select ClientNo from acctg_33qrybalperrecpt where invbalance>1) OR ARClientType<>0;';

        $sqlpdc='SELECT ClientNo,`DateofPDC`,`PDCNo`, SUM(`PDC`) AS PDCAmt, b.`CompanyNo` FROM acctg_38undepositedclientpdcs up join `1branches` b on b.BranchNo=up.BranchNo where PDC<>0 GROUP BY ClientNo,`PDCNo`, b.`CompanyNo` Order by `DateofPDC`,`PDCNo`;';
        
        $sqlrec='SELECT r.Date AS InvDate, r.ClientNo, r.Particulars,ss.PONo, ROUND(r.SaleAmount,2) AS SaleAmount, ROUND(r.RcdAmount,2) AS RcdAmount, round(r.InvBalance,2) AS InvBalance,
DateDiff(Now(),r.Date)-IFNULL(c.Terms,0) AS DaysOverdue, IFNULL(c.Terms,0) AS Terms, IFNULL(c.CreditLimit,0) AS CreditLimit, r.BranchNo,
 b.CompanyNo FROM `acctg_33qrybalperrecpt` AS r JOIN `1clients` c ON c.ClientNo=r.ClientNo
JOIN `1branches` b on b.BranchNo=r.BranchNo
LEFT JOIN `invty_2sale` ss ON r.ClientNo=ss.ClientNo AND r.BranchNo=ss.BranchNo AND r.Particulars=ss.SaleNo AND ss.PaymentType=2
WHERE InvBalance>1 ORDER BY r.Date;';
        
        $sqlhkr='SELECT a.BranchNo,a.ItemCode,i.ItemDesc AS Description,i.Unit,SUM(CASE WHEN Defective=1 THEN Qty END) AS GoodItem, SUM(CASE WHEN Defective<>0 THEN Qty END) AS Defective FROM invty_20uniallposted AS a JOIN invty_1items i on i.ItemCode=a.ItemCode
where Date is not null and Date<=Now() and i.ItemCode IN (4337,588,592,3769) group by BranchNo,a.ItemCode';



$tablearray=array('0adminsuppandclients','cpundeppdcs','cpreceivables','cpendinvperbranchhkr');
$sqlarray=array('0adminsuppandclients'=>$sqlclient,'cpundeppdcs'=>$sqlpdc,'cpreceivables'=>$sqlrec,'cpendinvperbranchhkr'=>$sqlhkr);
exportassql($link,$tablearray,$sqlarray);
} elseif (isset($_POST['submit1'])) {

        $sqlsupplier='SELECT SupplierNo as ASCNo, SupplierName as ASCCompanyName,12 AS AccessLevel from 1suppliers where inactive=0 and InvtySupplier=1';
		
        $sqlbp='SELECT bp.SupplierNo,SupplierInv,Date,PurchaseAmt,PaidAmt,PayTerms,DateDue,PayBalance,BranchNo from acctg_23balperinv bp join 1suppliers s on s.SupplierNo=bp.SupplierNo where inactive=0 and InvtySupplier=1';
		
		// $sqlvm='SELECT TxnID,CVNo,CheckNo,Date,PayeeNo,CreditAccountID,Cleared,Remarks,CheckReceivedBy,ReleaseDate,DateofCheck FROM acctg_2cvmain vm join 1suppliers s on s.SupplierNo=vm.PayeeNo where inactive=0 and InvtySupplier=1
		// UNION SELECT TxnID,CVNo,CheckNo,Date,PayeeNo,CreditAccountID,Cleared,Remarks,CheckReceivedBy,ReleaseDate,DateofCheck FROM '.$lastyr.'_1rtc.acctg_2cvmain vm join '.$lastyr.'_1rtc.1suppliers s on s.SupplierNo=vm.PayeeNo where inactive=0 and InvtySupplier=1 AND (ReleaseDate IS NULL)';
		
		$sqlvm='SELECT CVNo,CheckNo,Date,PayeeNo,CreditAccountID,Cleared,Remarks,CheckReceivedBy,ReleaseDate,DateofCheck FROM acctg_2cvmain vm join 1suppliers s on s.SupplierNo=vm.PayeeNo where inactive=0 and InvtySupplier=1
		UNION SELECT uclp.CVNo,uclp.CheckNo,Date,uclp.PayeeNo,CreditAccountID,uclp.Cleared,Remarks,uclp.CheckReceivedBy,uclp.ReleaseDate,uclp.DateofCheck FROM '.$lastyr.'_1rtc.acctg_2vouchermain vm JOIN acctg_3unclearedchecksfromlastperiod uclp ON vm.TxnID=uclp.CVNo AND vm.CheckNo=uclp.CheckNo join '.$lastyr.'_1rtc.1suppliers s on s.SupplierNo=vm.PayeeNo where inactive=0 and InvtySupplier=1 AND (uclp.ReleaseDate IS NULL);';
		
		$sqlca='SELECT AccountID,ShortAcctID,AccountType,OrderNo,ca.Remarks,DeptID FROM acctg_1chartofaccounts ca join acctg_2cvmain vm on vm.CreditAccountID=ca.AccountID join 1suppliers s on s.SupplierNo=vm.PayeeNo where inactive=0 and InvtySupplier=1';
		
		$sqlvs='SELECT TxnSubId,vs.CVNo,Particulars,ForInvoiceNo,DebitAccountID,Amount,BranchNo from acctg_2cvsub vs join acctg_2cvmain vm on vm.CVNo=vs.CVNo join 1suppliers s on s.SupplierNo=vm.PayeeNo where inactive=0 and InvtySupplier=1
		UNION SELECT uclp.CVNo AS TxnSubId,uclp.CVNo,Particulars,ForInvoiceNo,DebitAccountID,AmountOfCheck,uclp.BranchNo from '.$lastyr.'_1rtc.acctg_2vouchersub vs join '.$lastyr.'_1rtc.acctg_2vouchermain vm on vm.TxnID=vs.TxnID JOIN acctg_3unclearedchecksfromlastperiod uclp ON vm.TxnID=uclp.CVNo AND vm.CheckNo=uclp.CheckNo join '.$lastyr.'_1rtc.1suppliers s on s.SupplierNo=vm.PayeeNo where inactive=0 and InvtySupplier=1 AND (uclp.ReleaseDate IS NULL)';
		

		// SELECT uclp.CVNo,uclp.CVNo,Particulars,ForInvoiceNo,DebitAccountID,AmountOfCheck,uclp.BranchNo from '.$lastyr.'_1rtc.acctg_2cvsub vs join '.$lastyr.'_1rtc.acctg_2cvmain vm on vm.TxnID=vs.TxnID JOIN acctg_3unclearedchecksfromlastperiod uclp ON vm.CVNo=uclp.CVNo AND vm.CheckNo=uclp.CheckNo join '.$lastyr.'_1rtc.1suppliers s on s.SupplierNo=vm.PayeeNo where inactive=0 and InvtySupplier=1 AND (uclp.ReleaseDate IS NULL);


		$sqli='SELECT ItemCode,CatNo,ItemDesc,Unit,Remarks,MoveType,ReorderQty,WithBarcode from invty_1items';
		$sqlc='SELECT CatNo,Category,StdDesc from invty_1category';
		$sqlud='SELECT TxnID,ud.SupplierNo,PONo,BranchNo,ItemCode,Ordered,UnitCost,Received,SupplierUndelivered from invty_41supplierundelivered ud join 1suppliers s on s.SupplierNo=ud.SupplierNo where inactive=0 and InvtySupplier=1';
		
		
	


$tablearray=array('0adminsuppandclients','acctg_23balperinv','acctg_2cvmain','acctg_1chartofaccounts','acctg_2cvsub','invty_1items','invty_1category','invty_41supplierundelivered');
$sqlarray=array('0adminsuppandclients'=>$sqlsupplier,'acctg_23balperinv'=>$sqlbp,'acctg_2cvmain'=>$sqlvm,'acctg_1chartofaccounts'=>$sqlca,'acctg_2cvsub'=>$sqlvs,'invty_1items'=>$sqli,'invty_1category'=>$sqlc,'invty_41supplierundelivered'=>$sqlud);
exportassql($link,$tablearray,$sqlarray);
}elseif (isset($_POST['submit2'])) {
		
		$sqlm='SELECT TxnID,Date,MRRNO,m.SupplierNo,ForPONo,Remarks,SuppInvNo,SuppInvDate,m.Terms,RCompany,BranchNo,Posted,txntype from invty_2mrr m join 1suppliers s on s.SupplierNo=m.SupplierNo where inactive=0 and InvtySupplier=1';
      
		$sqlms='SELECT TxnSubId,ms.TxnID,ItemCode,Qty,UnitCost,SerialNo,Defective from invty_2mrrsub ms join invty_2mrr m on m.TxnID=ms.TxnID join 1suppliers s on s.SupplierNo=m.SupplierNo where inactive=0 and InvtySupplier=1';

		
		

$tablearray=array('invty_2mrr','invty_2mrrsub');
$sqlarray=array('invty_2mrr'=>$sqlm,'invty_2mrrsub'=>$sqlms);
exportassql($link,$tablearray,$sqlarray);
}

noform:
    $link=null;