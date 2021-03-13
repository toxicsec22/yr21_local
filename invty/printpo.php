<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6920,'1rtc')) {   echo 'No permission'; exit;}  
 
include_once('../backendphp/functions/editok.php');
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
 

$txnid=intval($_REQUEST['TxnID']);

$user=$resultuser['FirstName'].' '.substr($resultuser['MiddleName'],0,1).'. '.$resultuser['SurName'];


$whichqry=$_GET['w'];
switch ($whichqry){
    CASE 'Order':
$title='Print PO';
    $sqlmain='select o.*, s.SupplierName, b.Branch as RequestingBranch, e.Nickname as EncodedBy, c.Company,CompanyName from invty_3order as o
    join `1branches` as b on o.BranchNo=b.BranchNo
    join `1suppliers` as s on o.SupplierNo=s.SupplierNo
    left join `1companies` as c on o.CompanyNo=c.CompanyNo
left join `1employees` as e on o.EncodedByNo=e.IDNo where Posted=1 AND Approved=1 AND TxnID='.$txnid;

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    if ($stmt->rowCount()<1){goto no_po;}
$logo=$result['Company'];
$main='<font face="arial" size="3"><table width="100%" class="maintable">
<tr>
<td width="40%">Issued To:  '.$result['SupplierName'].'</td>
<td width="30%"><a href="javascript:window.print()">PO No. '.$result['PONo'].'</a></td>
<td width="30%">PO Date: '.$result['Date'].'</td></tr>
<tr><td>For Request: '.$result['RequestNo'].'</td><td> '.$result['Remarks'].'</td><td>Date Required: '.$result['DateReq'].'</td></tr></table></font><br>';
    
$sqlsub='Select s.ItemCode, s.UnitCost,s.Qty,concat(c.Category,\' \', i.ItemDesc) as Description, i.Unit, s.UnitCost*s.Qty as Amount from invty_3ordersub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo where TxnID='.$txnid. ' Order by Category';
    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
    $sub='<table width="100%" class="subtable"><tr>
<td width=5%>ItemCode</td>
<td width=65%>Description</td>
<td width=5%>Qty</td>
<td width=5%>Unit</td>
<td width=5%>UnitCost</td>
<td width=20%>Amount</td></tr>';
foreach ($resultsub as $row){    
$sub=$sub.'<tr>
<td width=5%>'.$row['ItemCode'].'</td>
<td width=65%>'.$row['Description'].'</td>
<td width=5%>'.$row['Qty'].'</td>
<td width=5%>'.$row['Unit'].'</td>
<td width=5%>'.$row['UnitCost'].'</td>
<td width=20%>'.number_format($row['Amount'],2).'</td></tr>';
}
$sub=$sub.'</table><center>------   NOTHING FOLLOWS  ------<center><br>';

    $sqlsum='Select count(ItemCode) as LineItems, sum(s.UnitCost*s.Qty) as Total from invty_3ordersub s where TxnID='.$txnid;
    $stmt=$link->query($sqlsum);
    $result1=$stmt->fetch();
    $total='Line Items: '.$result1['LineItems'].str_repeat('&nbsp',40).'Total:  '.number_format($result1['Total'],2).str_repeat('&nbsp',7)  ;
      break;
    
}
 $link=null; $stmt=null;
?>
<html>
<head>
<title><?php echo $title; ?></title>
<style>

body  
{ 
    /* this affects the margin on the content before sending to printer */ 
    margin: 0px;
    font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
}
table,td {
        border:1px solid black;
border-collapse:collapse;
padding: 3px;
    font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
    }
media print  
{
    
      table {
        border:1px solid black;
border-collapse:collapse;
padding: 3px;
font-size: 9pt;
    }
}

</style>
</head>
<body>
<center><font size="2"><img src='../generalinfo/logo/<?php echo $logo; ?>.png'></font><br>
United Glorietta Compound, Pasig Boulevard Extension, Caniogan, Pasig City<br>Tel: 635 2479; 508 7822    Ofc: 808-1574<br>Purchase Order<br><br></center>
</div>
<?php  echo $main; ?>
<?php  echo $sub.'<br>';
echo isset($total)?$total:'';
?>
<br><br>
<br>
Prepared by:&nbsp &nbsp  <?php echo $user; ?><div style="float:right">Conforme:  _______________________<br><font size="1">Signature above printed name</font></div>
<br><br><hr>
<font size="1">
TERMS & CONDITIONS
1.  By application of this order, the vendor represents that the fulfillment and invoicing of this order will conform to all applicable government regulations.
2.  No partial delivery will be allowed unless with prior approval from us.
3.  If vendor should fail to deliver the items on the scheduled date(s) for reason(s) due to its own fault, the vendor shall be liable to pay a penalty of one-tenth (1/10) of one percent (1%) of the total amount due as indicated in every Purchase Order for each calendar day of delay as damages.
4.  If the delay in the delivery is no longer acceptable, the Company reserves the right to cancel this Purchase Order without necessarily informing the vendor.  All Purchase Orders older than 60 days will automatically be canceled. If there are partial deliveries, only the delivered goods will be recognized.
5.  If sales tax is applicable to the purchase, such tax must appear on the invoice which covers the charges for the goods.
6.  Prices, terms and conditions are firm and cannot be changed after acceptance of this order.</font>
<?php no_po: ?>
</body>
</html>