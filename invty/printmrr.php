<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(763,'1rtc')) {   echo 'No permission'; exit;}  

include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
?>
<html>
<head>
<title>Print MRR</title>
<?php
include ('../backendphp/layout/standardprintsettings.php');
?>
</head>
<body>
<?php

$whichqry=$_GET['w'];
switch ($whichqry){
    CASE 'MRR': 
$sqlmain='select m.*,CompanyName, s.SupplierName, DATE_ADD(`Date`,INTERVAL mod(6-DAYOFWEEK(`Date`)+7,7)+m.`Terms` DAY) as DueDate from invty_2mrr m
        join `1suppliers` as s on m.SupplierNo=s.SupplierNo left join `1companies` co on co.CompanyNo=m.RCompany where  m.MRRNo>=\''.addslashes($_POST['MRRFrom']).'\' and m.MRRNo<=\''.addslashes($_POST['MRRTo']).'\'';
echo '<center><a href="javascript:window.print()">MRR from '.addslashes($_POST['MRRFrom']).' to '.addslashes($_POST['MRRTo']).'</a></center><br>';
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetchAll();
foreach ($result as $mainrow){
$main='<div class="keeptog"><font face="arial" size="3"><table width="100%" class="maintable">
<tr>
<td>MRR No. '.$mainrow['MRRNo'].'</td>
<td>Supplier:  '.$mainrow['SupplierName'].'</td>
<td>For PO No: '.$mainrow['ForPONo'].'</td>
<td>DeliveryReceiptNo: '.$mainrow['SuppDRNo'].' </td><td>Date of DR: '.$mainrow['SuppDRDate'].' </td> <td>Invoice No: '.$mainrow['SuppInvNo'].'</td> <td>Date of Invoice: '.$mainrow['SuppInvDate'].'</td>
<td>CompanyName: '.$mainrow['CompanyName'].'</td>
</tr>
<tr><td >Date Received:  '.$mainrow['Date'].'</td>
<td> '.$mainrow['Remarks'].'</td><td>Terms: '.$mainrow['Terms'].'D</td>
<td colspan="5">Due: '.$mainrow['DueDate'].'</td></tr></table></font>';

$sqlsub='Select s.ItemCode, s.UnitCost,s.Qty,concat(c.Category,\' \', i.ItemDesc) as Description, i.Unit, s.UnitCost*s.Qty as Amount from invty_2mrrsub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo join invty_2mrr m on m.TxnID=s.TxnID where m.MRRNo=\''.($mainrow['MRRNo']).'\' Order by Category'; //txntype=6 and 
    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
    $sub='<table width="100%" class="subtable"><tr>
<td width=5%>ItemCode</td>
<td width=65%>Description</td>
<td width=5%>Qty</td>
<td width=5%>Unit</td>
<td width=5%>UnitCost</td>
<td width=5%>Amount</td></tr>';
foreach ($resultsub as $row){    
$sub=$sub.'<tr>
<td width=5%>'.$row['ItemCode'].'</td>
<td width=65%>'.$row['Description'].'</td>
<td width=5%>'.$row['Qty'].'</td>
<td width=5%>'.$row['Unit'].'</td>
<td width=5%>'.$row['UnitCost'].'</td>
<td width=5%>'.$row['Amount'].'</td></tr>';
$sqlsum='Select count(ItemCode) as LineItems, sum(s.UnitCost*s.Qty) as Total from invty_2mrrsub s join invty_2mrr m on m.TxnID=s.TxnID where m.MRRNo=\''.($mainrow['MRRNo']).'\''; //removed txntype=6 and 

    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='<div style="float:right">Line Items: '.$result['LineItems'].str_repeat('&nbsp',20).'Total:  '.number_format($result['Total'],2).'</div>';
}
echo $main.$sub.'</table><br>'.$total.'</div><br><hr>';
}

    
      break;
    
}
 $link=null; $stmt=null;
?>
</body>
</html>