<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(585,'1rtc')) { echo 'No permission'; exit; }   
if (!isset($_REQUEST['print'])) { $showbranches=true; include_once('../switchboard/contents.php');}
 
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link; 
 $title='Print Blotters'; //$formdesc='Only <b>POSTED</b> transactions are shown.';
// $fieldname='Date';

if (!isset($_REQUEST['print'])) { include_once('../backendphp/layout/clickontabletoedithead.php');
?>
<form method="post" action="printblotters.php?print=1" enctype="multipart/form-data">
      Show Transactions From: <br> Date From: <input type="date" width=8 name="datefrom" value="<?php echo date('Y-m-d'); ?>"></input>
 Date To:  <input type="date" width=8 name="dateto" value="<?php echo date('Y-m-d'); ?>"></input> &nbsp; &nbsp;
        <input type="submit" name="w" value="Cash Sales & Returns">&nbsp &nbsp
        <input type="submit" name="w" value="Charge Sales">&nbsp &nbsp
        <input type="submit" name="w" value="Inventory Charges">&nbsp &nbsp
        <input type="submit" name="w" value="Collection Receipts per Company"></form>
<br><br>
Types of Sales as subject to VAT:<br>
1. Vatable (12%)<br>
2. Vat-Exempt (no VAT)<br>
3. Zero-Rated (0%)<br>
4. Government (12%)<br><br>
Vatable Sales = Total Invoice Amount / 1.12<br><br>
VAT = (Total Invoice Amount * 0.12) / 1.12
    <?php 
goto noform;
}
$whichqry=$_REQUEST['w'];
$title=$_SESSION['@brn'].' - '.$whichqry;

$formdesc='Date From: '.$_REQUEST['datefrom'].', Date To:'.$_REQUEST['dateto'] .'<br>'; 
$txndate='m.Date BETWEEN \''.$_REQUEST['datefrom'].'\' AND \''.$_REQUEST['dateto'].'\' ';


switch ($whichqry){
    case 'Cash Sales & Returns':
        $sqlfrom=' FROM invty_2sale m join invty_2salesub as s on m.TxnID=s.TxnID 
INNER JOIN invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid join `1clients` as c on c.ClientNo=m.ClientNo
join invty_0paytype pt on pt.paytypeid=m.PaymentType JOIN `gen_info_1vattype` v ON v.VatTypeNo=c.VatTypeNo
left join `invty_7opapproval` a on a.TxnID=m.TxnID LEFT JOIN `1employees` e ON e.IDNo=m.SoldByNo
WHERE (('.$txndate.') AND ((m.BranchNo)='.$_SESSION['bnum'].') AND txntype IN (1,5)) ';
        $sql1='Select `Date`, date_format(Date,\'%Y %b %d\') as DateForm, e.Nickname AS SoldBy,IF(TIN<>0,TIN,"") AS TIN '.$sqlfrom.'  GROUP BY `Date`';// echo $sql1;
        $sql0='CREATE TEMPORARY TABLE cashsales AS SELECT m.*, `invty_0txntype`.txndesc as `Form`, IF(TIN<>0,TIN,"") AS TIN,ClientName, pt.paytypedesc as PayType, e.Nickname AS SoldBy,  '
                . 'IF(CheckDetails="","",DateofCheck) AS CheckDate, round(sum(s.UnitPrice*s.Qty)+if(m.PaymentType=1,round(ifnull(a.Amount,0)*0.12,0),round(ifnull(a.Amount,0))),2) as AmountValue, IF(c.VatTypeNo<>0,VatType,"") AS VatType,FORMAT((sum(s.UnitPrice*s.Qty)+IFNULL(a.Amount,0)),2) as Total, '
                . 'if(c.VatTypeNo IN (1,2),0,FORMAT((sum(s.UnitPrice*s.Qty)+IFNULL(a.Amount,0))/1.12,2)) as Vatable, if(c.VatTypeNo IN (1,2),0,ROUND((sum(s.UnitPrice*s.Qty)+IFNULL(a.Amount,0))/1.12,2)) as VatableValue,'
                . 'if(c.VatTypeNo IN (1,2),0,FORMAT((sum(s.UnitPrice*s.Qty)+IFNULL(a.Amount,0))*(0.12/1.12),2)) as Vat, if(c.VatTypeNo IN (1,2),0,ROUND((sum(s.UnitPrice*s.Qty)+IFNULL(a.Amount,0))*(0.12/1.12),2)) as VatValue, IFNULL(a.Amount,"") as Overprice, sum(s.Qty) AS TotalQty, COUNT(s.ItemCode) AS LineItems  '.$sqlfrom.' Group by m.TxnID';
        $stmt0=$link->prepare($sql0);  $stmt0->execute();
        $sql2='SELECT *, format(AmountValue,2) as Amount FROM cashsales s '; //echo '<br>'.$sql0.'<br>'.$sql2;
        $groupby='Date'; $orderby=' ORDER BY `Form`,`SaleNo`';
        $grandtotal=true; //$coltototal='AmountValue';
        $columnnames1=array('DateForm');//'CheckDetails','CheckDate','PONo', 
        $columnnames2=array('SaleNo','ClientName','Remarks','SoldBy','LineItems','TotalQty','Total','VatType','Vatable','Vat','Overprice','TIN');
        $sqlsubtotal='SELECT FORMAT(SUM(VatableValue),2) AS Vatable, FORMAT(SUM(VatValue),2) AS Vat, FORMAT(SUM(AmountValue),2) AS TotalNetofReturns, FORMAT(SUM(Overprice),2) AS Overprice FROM cashsales';   $colsubtotals=array('Vatable','Vat','TotalNetofReturns','Overprice');
        break;
    case 'Charge Sales':
        $sqlcond=' WHERE  (('.$txndate.') AND (m.BranchNo='.$_SESSION['bnum'].') and m.txntype in (2)) ';
     $sqlfrom=' FROM invty_2sale m JOIN  invty_2salesub s ON m.TxnID=s.TxnID LEFT JOIN `1clients` as c on c.ClientNo=m.ClientNo  '
            . ' JOIN `gen_info_1vattype` v ON v.VatTypeNo=c.VatTypeNo LEFT JOIN `1employees` e ON e.IDNo=m.SoldByNo'
            . $sqlcond.'  GROUP BY m.TxnID ';
    $sql1='SELECT m.TxnID,m.Date,concat(`SaleNo`,": ") as `Form`, concat(ClientName,IF(PORequired=1,"(PO Required)",""),IF(ARClientType=2,"(PDC Required)",""),IF(ARClientType=2,CONCAT("(Terms: ",Terms," days)"),"")) as ClientName, concat("PaymentType: ",(Case when PaymentType=1 then \'Cash\' when PaymentType=2 then \'Charge\' else \'Return\'  end)) as PayType, IF(m.Remarks="","",concat("Remarks: ", m.Remarks)) as Remarks, CONCAT(" - ",e.Nickname) AS SoldBy, IF(CheckDetails="","",concat("CheckDetails: ", CheckDetails)) as CheckDetails, IF(CheckDetails="","",concat("DateofCheck: ", DateofCheck)) as DateofCheck, IF(PONo="","",concat("PONo: ", PONo)) as PONo,CONCAT("TIN ",IF(TIN<>0,TIN,"")) AS TIN '.$sqlfrom.' ORDER BY txntype, SaleNo';

        
    $sql2uniop='create temporary table subwithop as SELECT CONCAT("TIN ",IF(TIN<>0,TIN,"")) AS TIN,s.TxnID, s.ItemCode, s.Qty, s.UnitPrice, FORMAT(Qty*UnitPrice,2) as Amount, Category, ItemDesc, Unit, s.SerialNo FROM invty_2salesub s join invty_2sale m ON m.TxnID=s.TxnID LEFT JOIN `1clients` as cs on cs.ClientNo=m.ClientNo join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo '. $sqlcond.'
    union all SELECT CONCAT("TIN ",IF(TIN<>0,TIN,"")) AS TIN,a.TxnID, 0 as ItemCode, 0 as Qty, 0 as UnitPrice, FORMAT(a.Amount,0) as Amount, "" as Category, "Overprice" as ItemDesc, "" as Unit, "" as SerialNo FROM `invty_7opapproval` a join invty_2sale m on a.TxnID=m.TxnID  LEFT JOIN `1clients` as cs on cs.ClientNo=m.ClientNo '. $sqlcond.'
    UNION ALL SELECT CONCAT("TIN ",IF(TIN<>0,TIN,"")) AS TIN,m.TxnID,  0 as ItemCode, 0 as Qty, 0 as UnitPrice, FORMAT(Amount,0) as Amount, "" as Category, "Freight Expense" as ItemDesc, "" as Unit, "" as SerialNo FROM `approvals_2freightclients` fc JOIN `invty_2sale` m ON m.SaleNo=fc.ForInvoiceNo AND m.BranchNo=fc.BranchNo AND m.txntype=fc.txntype LEFT JOIN `1clients` as cs on cs.ClientNo=m.ClientNo '. $sqlcond; 
	// echo $sql2uniop; break;
    $stmt=$link->prepare($sql2uniop);
    $stmt->execute();
    
    $sql2='Select * from subwithop';
 
    $sqlsubtotal0='CREATE TEMPORARY TABLE totals AS SELECT m.TxnID, VatType,FORMAT(sum(s.UnitPrice*s.Qty),2) as Total, if(c.VatTypeNo IN (1,2),0,FORMAT(sum(s.UnitPrice*s.Qty)/1.12,2)) as Vatable, if(c.VatTypeNo IN (1,2),0,FORMAT(sum(s.UnitPrice*s.Qty)*(0.12/1.12),2)) as Vat '.$sqlfrom; //echo $sqlsubtotal0;
    $sqlsubtotal='SELECT * FROM totals';   $colsubtotals=array('VatType','Vatable','Vat','Total');
    $stmt=$link->prepare($sqlsubtotal0);  $stmt->execute();

    //$coltototal='Amount'; 
    $groupby='TxnID'; $orderby=' ';
    $columnnames1=array('Date','Form','ClientName','Remarks','SoldBy','CheckDetails','DateofCheck','PONo','TIN');
    $columnnames2=array('ItemCode','Category','ItemDesc','Qty','Unit','UnitPrice','Amount','SerialNo');
    
        break;
    
case 'Inventory Charges':
    
    $sqlcond=' WHERE (('.$txndate.') AND (m.BranchNo='.$_SESSION['bnum'].') and m.txntype in (3)) ';
     
    $sql10='create temporary table invcharge as SELECT m.TxnID, m.Date, `SaleNo` as `Invty Charge`, CONCAT("Care of: ",c.FirstName," ",c.Surname) AS Employee, '
            . 'IF(m.Remarks="","",concat("Remarks: ", m.Remarks)) as Remarks, CONCAT(" - Audit By ",e.Nickname) AS AuditedBy, SUM(Qty*UnitPrice) as AmountValue, FORMAT(SUM(Qty*UnitPrice),2) as Amount, '
            . 'CONCAT("(",COUNT(ItemCode)," Line Item/s)") AS LineItems FROM invty_2salesub s join invty_2sale m ON m.TxnID=s.TxnID  JOIN `1employees` as c on c.IDNo=m.ClientNo LEFT JOIN `1employees` e ON e.IDNo=m.EncodedByNo '. $sqlcond.'  GROUP BY m.TxnID ';
    $stmt=$link->prepare($sql10);    $stmt->execute();
    
    $sql1='Select * from invcharge ';
    
    $sql20='CREATE TEMPORARY TABLE invchargedetails AS Select m.TxnID,m.Date,FORMAT(ChargeAmount,2) AS ChargeAmount, concat(e1.Nickname, " ", e1.Surname) as ChargeTo from invty_2salesubauditdistri s join invty_2sale m ON m.TxnID=s.TxnID  left join `1employees` as e1 on s.ChargeToIDNo=e1.IDNo';
    $stmt=$link->prepare($sql20);    $stmt->execute();
    
    $sql2='Select * from invchargedetails ';
    $sqlsubtotal0='CREATE TEMPORARY TABLE totals AS SELECT TxnID,FORMAT(SUM(AmountValue),2) as Total FROM invcharge '; 
    $sqlsubtotal='SELECT TxnID, FORMAT(SUM(AmountValue),2) AS Total FROM invcharge  ';   $colsubtotals=array('Total'); 
    $stmt=$link->prepare($sqlsubtotal0);  $stmt->execute();

    $groupby='TxnID'; $orderby=' '; 
    $columnnames1=array('Invty Charge','Employee','Remarks','AuditedBy','LineItems');
    $columnnames2=array('ChargeTo','ChargeAmount');
    //echo $sql1.'<br>'.$sql2uniop.'<br>'.$sql2.'<br>'.$sqlsubtotal0.'<br>'.$sqlsubtotal.'<br>';
        break;  
    
    
case 'Collection Receipts per Company':
    $title=$_SESSION['*cname'].' - Collection Receipts';
        $sql0='CREATE TEMPORARY TABLE collect AS SELECT m.*, (IFNULL(SUM(s.Amount),0)-(IFNULL(SUM(ps.Amount),0))) AS TotalValue, CollectTypeDesc AS `TxnType`, e.Nickname as Received_By, Branch AS BranchSeries, ClientName, Approval FROM `acctg_2collectmain` m 
LEFT JOIN `acctg_2collectsub` s ON m.TxnID=s.TxnID  LEFT JOIN `acctg_2collectsubdeduct` ps ON m.TxnID=ps.TxnID 
LEFT JOIN acctg_1collecttype ct ON m.Type=ct.CollectTypeID JOIN  `1branches` b ON b.BranchNo=m.BranchSeriesNo
LEFT JOIN `1clients` as c on c.ClientNo=m.ClientNo
LEFT JOIN `1employees` e ON e.IDNo=m.ReceivedBy  WHERE (('.$txndate.') AND (BranchSeriesNo IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo='.$_SESSION['*cnum'].'))) GROUP BY m.TxnID;';
        $stmt0=$link->prepare($sql0);  $stmt0->execute();
        
        $sql0='CREATE TEMPORARY TABLE `sub` AS SELECT s.TxnID, s.ForChargeInvNo, OtherORDetails, Amount AS AmountValue, FORMAT(Amount,2) AS Amount,b.Branch FROM `acctg_2collectsub` s JOIN `1branches` b on b.BranchNo=s.BranchNo JOIN `acctg_2collectmain` m ON m.TxnID=s.TxnID WHERE  (('.$txndate.') AND (BranchSeriesNo IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo='.$_SESSION['*cnum'].'))) 
UNION ALL
SELECT s.TxnID, "",`DeductDetails`,`Amount`*-1 AS AmountValue,FORMAT(Amount*-1,2) AS Amount, b.Branch FROM `acctg_2collectsubdeduct` s JOIN `1branches` b on b.BranchNo=s.BranchNo JOIN `acctg_2collectmain` m ON m.TxnID=s.TxnID  WHERE (('.$txndate.') AND (BranchSeriesNo IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo='.$_SESSION['*cnum'].')))';
$stmt=$link->prepare($sql0); $stmt->execute();
        
        $sql1='SELECT * FROM collect ';
        $sql2='SELECT *, Amount FROM sub s ';
        $groupby='TxnID'; $orderby=' ';
        $grandtotal=true; 
        $columnnames1=array('BranchSeries','CollectNo','ClientName','TxnType','CheckBank','CheckNo','CheckBRSTN','DateofCheck','Remarks', 'Approval','Received_By','Total');
        $columnnames2=array('Branch','ForChargeInvNo','OtherORDetails','Amount');
        $sqlsubtotal='SELECT FORMAT(SUM(AmountValue),2) AS Total FROM sub';   $colsubtotals=array('Total');
        break;
    
}

$hidecontents=1; $formdesc='<a href="javascript:window.print()">'.$formdesc.'</a>'; 
$stmt=$link->query('SELECT CONCAT(Nickname, " ",SurName) AS FullName FROM `1employees` WHERE IDNo='.$_SESSION['(ak0)']);
$who=$stmt->fetch();
$totaltext='Printed on '.date('Y-m-d h:i:s l').' by '.$who['FullName'];
    include ('../backendphp/layout/printmainwithsub.php');

noform:
     $link=null; $stmt=null; 
?>
 