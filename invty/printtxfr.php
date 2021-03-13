<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

// check if allowed
        $allowed=array(6927, 6932, 6933, 6934,7000);
        $allow=0;
        foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=$allow+1; } else { $allow=$allow; }}
        if ($allow==0) { echo 'No permission'; exit;}
        // end of check

 
include_once('../backendphp/functions/editok.php'); 
 

$txnid=intval($_REQUEST['TxnID']);

$whichqry=$_GET['w'];
switch ($whichqry){
    CASE 'Transfers':
$title='Print Transfer Receipt';
    $sqlmain='SELECT t.*, b1.Branch as FROMBranch,  b2.Branch as TOBranch,  e1.Nickname as FromEncodedBy,  e2.Nickname as ToEncodedBy, format(sum(s.UnitPrice*s.QtySent),2) as AmountSent, format(sum(s.UnitCost*s.QtyReceived),2) as AmountReceived, t.FROMTimeStamp, t.TOTimeStamp FROM invty_2transfer as t 
join `1branches` as b1 on b1.BranchNo=t.BranchNo
join `1branches` as b2 on b2.BranchNo=t.ToBranchNo
join `1employees` as e1 on e1.IDNo=t.FromEncodedByNo
join `1employees` as e2 on e2.IDNo=t.ToEncodedByNo
left join invty_2transfersub as s on t.TxnID=s.TxnID WHERE t.TxnID='.$txnid;

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
//$printcaption='Download '.$result['TransferNo'].'.pdf';
//$filename=$result['TransferNo'].'.pdf';

$main='<table width="100%"><tr><td width="80%"></td><td width="20%">From '.$result['FROMBranch'].'</td></tr><td></td><td><a href="javascript:window.print()">'.$result['TransferNo'].'</a></td></tr>';

$main=$main.'<tr><td><div style="margin-left:100px">'.strtoupper($result['TOBranch']).'</div></td><td></td></tr><tr><td><div style="margin-left:100px">'.$result['ForRequestNo'].'</div></td><td>'.$result['DateOUT'].'</td></tr></table><br><br><br>';

$sqlsub='Select  s.UnitPrice,s.QtySent,s.ItemCode,concat(c.Category,\' \', i.ItemDesc,\' \',s.SerialNo) as Description, i.Unit, s.UnitPrice*s.QtySent as AmountSent from invty_2transfersub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo where TxnID='.$txnid;
    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
    $sub='<table width="100%">';
foreach ($resultsub as $row){    
$sub=$sub.'<tr>
<td width=8%>'.$row['QtySent'].' '.$row['Unit'].'</td>
<td width=61%>'.$row['ItemCode'].str_repeat('&nbsp',3).$row['Description'].'</td>
<td width=8%></td>
<td width=8% align="center">'.$row['UnitPrice'].'</td>
<td width=15% align="right">'.number_format($row['AmountSent'],2).'</td></tr>';
}
$sub=$sub.'</table><center>------   NOTHING FOLLOWS  ------</center><br>';

    $sqlsum='Select count(ItemCode) as LineItems, sum(s.UnitPrice*s.QtySent) as TotalSent from invty_2transfersub s where TxnID='.$txnid;
    $stmt=$link->query($sqlsum);
    $resultsum=$stmt->fetch();
    $total='<footer><div style="margin-left:100px;display:inline;">Line Items: '.$resultsum['LineItems'].'</div><div style="float:right">'.number_format($resultsum['TotalSent'],2).'</div><br><br><div style="margin-left:100px">'.$result['Remarks'].'</div></footer>';
   
      break;
   
   
CASE 'Request':
$title='Print Request';
$sqlmain='select rm.*, b1.Branch as SupplierBranch, b2.Branch as RequestingBranch from invty_3branchrequest as rm
join `1branches` as b1 on rm.SupplierBranchNo=b1.BranchNo
join `1branches` as b2 on rm.BranchNo=b2.BranchNo where TxnID='.$txnid;
 
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
//$printcaption='<center><h4>RequestNo ' .$result['RequestNo'].'</h4></center>';
$printcaption='Download '.$result['RequestNo'].'.pdf';
$filename=$result['RequestNo'].'.pdf';
$main='<table width="100%">
<td width="50%">Requesting Branch: <a href="javascript:window.print()">'.strtoupper($result['RequestingBranch']).'</a></td>
<td width="25%">Date Required: '.$result['DateReq'].'</td></tr>
<tr><td>'.$result['Remarks'].'</td></tr></table>';
 
 $sqlsub='Select concat(s.ItemCode,\' \',c.Category,\' \', i.ItemDesc) as Description, i.Unit,s.RequestQty, SendBal as BalanceToSend, e.EndInvToday-ud.SendBal as EndInvAfter from invty_3branchrequestsub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo  join invty_3branchrequest m on m.TxnID=s.TxnID
join `invty_44undeliveredreq` ud on s.ItemCode=ud.ItemCode and m.RequestNo=ud.RequestNo
join `invty_21endinv` e on e.BranchNo=m.SupplierBranchNo and e.ItemCode=s.ItemCode
where m.TxnID='.$txnid.' and ud.SendBal<>0 Order By Category, ItemDesc';

    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
    $sub='<table width="100%"><tr>
<td width=5%>RequestQty</td>
<td width=5%>BalanceToSend</td>
<td width=5%>Unit</td>
<td width=85%>Description</td>
<td width=5%>EndInv After Send Bal</td></tr>';
 
$sqlendinv='SELECT e.EndInvToday FROM invty_21endinv e join `invty_3branchrequest` r on e.BranchNo=r.BranchNo
join`invty_3branchrequestsub` s on r.TxnID=s.TxnID and e.ItemCode=s.ItemCode 
where e.EndInvToday<=0 and r.TxnID='.$txnid;

    $rowcount=0;
foreach ($resultsub as $row){
   $rowcount=$rowcount+1;
$sub=$sub.'<tr>
<td>'.$row['RequestQty'].'</td>
<td>'.$row['BalanceToSend'].'</td>
<td>'.$row['Unit'].'</td>
<td>'.$row['Description'].'</td>'.
($row['EndInvAfter']<=0?'<td>'.$row['EndInvAfter'].'</td>':'<td></td>').
'</tr>'.($rowcount%10==0?'<tr><td colspan=4><hr></td></tr>':'');
}
$sub=$sub.'</table><center>------   NOTHING FOLLOWS  ------</center><br>';

    $sqlsum='Select Count(s.ItemCode) as LineItems from invty_3branchrequestsub s join invty_3branchrequest m on m.TxnID=s.TxnID
join `invty_44undeliveredreq` ud on s.ItemCode=ud.ItemCode and m.RequestNo=ud.RequestNo
where m.TxnID='.$txnid.' and ud.SendBal<>0 Group By m.TxnID';
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='  Line Items: '.$result['LineItems'];
 
      break;
    
CASE 'Charge': 
$title='Print Charge Receipt';
$sqlmain='Select m.*, c.ClientName, concat(c.StreetAddress,\', \',c.Barangay,\', \',TownorCity) as Address, TIN, Terms from `invty_2sale` m join `1clients` as c on m.ClientNo=c.ClientNo  where txntype=2 and TxnID='.$txnid;
    
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();

$main='<div style="margin: 16mm 5mm 12mm 10mm; font-size: 15pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;"><table width="100%"><tr  height="18" ><td colspan=3 align=right><a href="javascript:window.print()">'.$result['SaleNo'].'</a></td></tr><tr  height="18" ><td width="80%" colspan=2>'.$result['ClientName'].'</td><td width="20%" align=right>'.$result['Date'].'</td></tr><tr height="18" ><td colspan=2>'.$result['Address'].'</td><td align=right>'.$result['PONo'].'</td></tr><td></td><td>'.$result['TIN'].'</td><td align=right>'.$result['Terms'].' days</td></tr><tr height="22"><td></td><td></td><td align=right>'.$_SESSION['(ak0)'].'</td></tr></table></div><BR><BR><BR><BR>';

$sqlsub='Select  s.UnitPrice,s.Qty,s.ItemCode,concat(c.Category,\' \', i.ItemDesc,\' \',s.SerialNo) as Description, i.Unit, s.UnitPrice*s.Qty as Amount from invty_2salesub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo where TxnID='.$txnid;
    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
    $sub='<div style="margin: 0mm 5mm 45mm 4mm; font-size: 12pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;"><table width="100%">';
foreach ($resultsub as $row){    
$sub=$sub.'<tr height="20">
<td width=12% align="left">'.$row['Qty'].'</td>
<td width=6% align="left">'.$row['Unit'].'</td>
<td width=52% style="white-space: nowrap; word-wrap: break-word;">'.$row['ItemCode'].str_repeat('&nbsp',3).$row['Description'].'</td>
<td width=15% align="center">'.number_format($row['UnitPrice'],2).'</td>
<td width=15% align="right">'.number_format($row['Amount'],2).'</td></tr>';
}
$sub=$sub.'</table><center><font size=1pt>------   NOTHING FOLLOWS  ------</font></center><br></div>';

    $sqlsum='Select count(ItemCode) as LineItems, sum(s.UnitPrice*s.Qty) as Total, truncate(sum(s.UnitPrice*s.Qty)/1.12,2) as Vatable, truncate(sum(s.UnitPrice*s.Qty)*(0.12/1.12),2) as Vat from invty_2salesub s where TxnID='.$txnid;
    $stmt=$link->query($sqlsum);
    $resultsum=$stmt->fetch();
    $total='<footer style="position:absolute;
   bottom:0;
   width:90%;
   height: 35mm;
   margin: 0mm 8mm 70mm 8mm;"><table width="100%"><tr height="20"><td width="80%">Line Items: '.$resultsum['LineItems'].'</td><td width="10%" align=right>'.number_format($resultsum['Vatable'],2).'</td><tr height="20"><td colspan=2>'.$result['Remarks'].'</td></tr><tr height="20"></tr><tr height="20"></tr><tr><td colspan=2 align=right>'.number_format($resultsum['Vat'],2).'</td></tr><tr height="20"><td colspan=2 align=right>'.number_format($resultsum['Total'],2).'</td></tr></footer>';
   
      break;
      
CASE 'InvCharge': 
    if (!allowedToOpen(7000,'1rtc')) {   echo 'No permission'; exit;} 
$title='Print Inventory Charges'; 
$sqlmain='Select m.*, CONCAT("<a href=\"javascript:window.print()\">",SaleNo,"</a>") AS `Invty Charge Reference No`, CONCAT(e.FirstName," ",e.SurName) AS Employee, CONCAT(e1.FirstName," ",e1.SurName) AS AuditedBy  from `invty_2sale` m JOIN `1employees` e on m.ClientNo=e.IDNo JOIN `1employees` e1 on m.EncodedByNo=e1.IDNo WHERE TxnID='.$txnid;

$stmt=$link->query($sqlmain); $result=$stmt->fetch(); $txntype=$result['txntype'];
    $columnnamesmain=array('Date','Invty Charge Reference No','Employee','Remarks','AuditedBy');
    $main=''; $colno=0; $fieldsinrow=(isset($fieldsinrow)?$fieldsinrow:5);
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font><br><br><font face="arial" size="3">'.$result[$rowmain].str_repeat('&nbsp',5).'</font></td>'.($colno%$fieldsinrow==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.'<tr></table>';
    $main=$main.'<br>';
    
    
$sqlsub='Select  FORMAT(s.UnitPrice,2) AS UnitPrice,s.Qty,s.ItemCode,concat(c.Category,\' \', i.ItemDesc,\' \',s.SerialNo) as Description, i.Unit, FORMAT(s.UnitPrice*s.Qty,2) as Amount, s.UnitPrice*s.Qty as AmountValue from invty_2salesub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo WHERE s.TxnID='.$txnid;

$stmtsub=$link->query($sqlsub); $resultsub=$stmtsub->fetchAll();
$columnnamessub=array('ItemCode','Description','Qty','Unit','UnitPrice', 'Amount');   
$subitems=''; $subfields='';

foreach ($columnnamessub as $rowsub){ $subfields.='<td><font face="arial" size="2">'.$rowsub.'</font></td>';  }

foreach ($resultsub as $item){
foreach ($columnnamessub as $rowsub){ $subitems.='<td>'.$item[$rowsub].str_repeat('&nbsp',5).'</td>';    }
    $subitems='</tr>'.$subitems.'</tr>';
}

    $sqlsum='Select sum(s.UnitPrice*s.Qty) as Total, count(ItemCode) as LineItems from invty_2salesub s where TxnID='.$txnid;
    $stmt=$link->query($sqlsum);    $resultsum=$stmt->fetch();

$total='Total :  '.number_format($resultsum['Total'],2).'&nbsp &nbsp &nbsp <br>';
echo '<title>Inv Charge '.$result['SaleNo'].'</title>';
echo '<style>table,td,tr {border:1px solid black; border-collapse:collapse; padding: 3px; font-size: 9pt;     font-family: Arial, Helvetica, sans-serif; font-weight: 300;}</style>';
echo $main.'<table><tr>'.$subfields.'</tr>'.$subitems.'</table><br>'.$total.'<br>';
include 'auditdistri.php';
goto noform;
      break;
    
}
?>
<html>
<head>
<title><?php echo $title; ?></title>
<!--<a href="javascript:window.print()">Print</a>-->
<style type="text/css">
 
body { 
    margin: 0mm 8mm 85mm 8mm;
    font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
}
table,td {
        border:0px solid black;
border-collapse:collapse;
padding: 3px;
font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
}
footer {
   position:absolute;
   bottom:0;
   width:90%;
   height: 100mm;
   margin: 0mm 15mm 5mm 8mm;
}
</style>
</head>
<body>
<?php  echo $main; ?><br>
<?php  echo $sub.'<br>';
echo isset($total)?$total:'';
//
//if (!isset($_POST['print'])){
//   goto noform;
//} else {
//$file = fopen($filename, "w+b");
//fwrite($file, $sub);
//fclose($file);
//header("Content-disposition: attachment; filename=".$filename);
//header("Content-type: application/pdf");
//readfile($file);
//}

noform:
     $link=null; $stmt=null; 
?>
</body>
</html>