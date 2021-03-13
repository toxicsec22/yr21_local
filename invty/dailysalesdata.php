<?php 

$sql0='CREATE TEMPORARY TABLE dailysales(
    BranchNo smallint(6) NOT NULL, Branch varchar(30) NOT NULL, AreaNo smallint(6) NOT NULL, Area varchar(30) NOT NULL,
    Cash double NOT NULL, Cash_NoOfReceipts smallint(3) NOT NULL DEFAULT 0,
    Collections double NOT NULL,
    Charge double NOT NULL, Charge_NoOfReceipts smallint(3) NOT NULL DEFAULT 0,
    Returns double NOT NULL, AuditCharges double NOT NULL, StoreUsed double NOT NULL
)
SELECT b.BranchNo, Branch, b.AreaNo, Area, TRUNCATE(ifnull(sum(case when (m.txntype in (1,10)) then (Qty*UnitPrice) end),0),0) as Cash,
(SELECT IFNULL(COUNT(TxnID),0) FROM `invty_2sale` WHERE BranchNo=b.BranchNo AND txntype in (1,10) AND `Date`=\''.$date.'\' GROUP BY Date,BranchNo) as Cash_NoOfReceipts,
0 as Collections,
truncate(ifnull(sum(case when m.txntype=2 then (Qty*UnitPrice) end),0),0) as Charge,  
(SELECT IFNULL(COUNT(TxnID),0) FROM `invty_2sale` WHERE BranchNo=b.BranchNo AND txntype=2 AND `Date`=\''.$date.'\' GROUP BY Date,BranchNo) as Charge_NoOfReceipts,
truncate(ifnull(sum(case when m.txntype=5 then (Qty*UnitPrice) end),0),0) as Returns,  
truncate(ifnull(sum(case when m.txntype=3 then (Qty*UnitPrice) end),0),0) as AuditCharges,  
truncate(ifnull(sum(case when m.txntype=9 then (Qty*UnitPrice) end),0),0) as StoreUsed 
from `1branches` b left join `invty_2sale` m  on b.BranchNo=m.BranchNo and (m.Date=\''.$date.'\')
 join `invty_2salesub` s on m.TxnID=s.TxnID  JOIN `0area` a ON b.AreaNo=a.AreaNo group by m.Date, m.BranchNo 
union all
SELECT s.BranchNo, Branch, b.AreaNo, Area, 0, 0 AS Cash_NoOfReceipts, Sum(ifnull(Amount,0)) as Collections, 0, 0 as Charge_NoOfReceipts, 0, 0, 0 FROM `acctg_2collectmain` m join `acctg_2collectsub` s on m.TxnID=s.TxnID
join `1branches` b on b.BranchNo=s.BranchNo  JOIN `0area` a ON b.AreaNo=a.AreaNo
where m.Date=\''.$date.'\' and ((DebitAccountID=100) or (DebitAccountID=201 and DateofCheck=\''.$date.'\'))  group by s.BranchNo,m.Date';
//echo $sql0; break;
$stmt=$link->prepare($sql0); $stmt->execute();

$sql1='SELECT AreaNo, Area FROM dailysales  '.$condition.' GROUP BY Area ORDER BY AreaNo;'; $stmt1=$link->query($sql1); $res1=$stmt1->fetchAll();
$msg='';  $total=0; $totalper=0;
foreach($res1 as $area){
    $msgarea='<h4>'.$area['Area'].'</h4>'.'<table border=1><tr><td>Branch</td><td>Cash</td><td>Cash_NoOfReceipts</td><td>Collections</td>
<td>Charge</td><td>Charge_NoOfReceipts</td><td>Returns</td><td>AuditCharges</td><td>StoreUsed</td></tr>';
    $sql2='Select s.Branch, format(sum(Cash),0) as Cash, SUM(Cash_NoOfReceipts) AS Cash_NoOfReceipts, format(sum(Collections),0) as Collections,format(sum(Charge),0) as Charge, SUM(Charge_NoOfReceipts) AS Charge_NoOfReceipts, format(sum(Returns),0) as Returns,format(sum(AuditCharges),0) as AuditCharges,format(sum(StoreUsed),0) as StoreUsed from dailysales s WHERE s.AreaNo='.$area['AreaNo'].' group by s.BranchNo order by s.Branch asc'; 
    $stmt2=$link->query($sql2);$res2=$stmt2->fetchAll();
    
    foreach ($res2 as $row) {
    $msgarea=$msgarea.'<tr><td>'.str_replace(' ','_',$row['Branch']).'</td><td>'.$row['Cash'].'</td><td>'.$row['Cash_NoOfReceipts'].'</td><td>'.$row['Collections'].'</td><td>'.$row['Charge'].'</td><td>'.$row['Charge_NoOfReceipts'].'</td><td>'.$row['Returns'].'</td><td>'.$row['AuditCharges'].'</td><td>'.$row['StoreUsed'].'</td></tr>';
}
    $msg=$msg.$msgarea.'</table>';
    $sqltotalarea='Select format((sum(Cash)+sum(Returns)),0) as TotalCash, format((sum(Collections)),0) as TotalCollections, format((sum(Charge)),0) as TotalCharge from dailysales WHERE AreaNo='.$area['AreaNo'];
    $stmttotalarea=$link->query($sqltotalarea); $restotalarea=$stmttotalarea->fetch();
    $msg=$msg.'<br>'.'Number of branches: '.$stmt2->rowCount().str_repeat(' ',10).'Cash less Returns: '.$restotalarea['TotalCash'].str_repeat(' ',10).'Dated Collections: '.$restotalarea['TotalCollections'].str_repeat(' ',10).'Charge Sales:'.$restotalarea['TotalCharge'].'<br><br><hr>';
}


$sqltotal='Select COUNT(DISTINCT BranchNo) AS NumBranches, format((sum(Cash)+sum(Returns)),0) as TotalCash, format((sum(Collections)),0) as TotalCollections, format((sum(Charge)),0) as TotalCharge from dailysales';
$stmttotal=$link->query($sqltotal); $restotal=$stmttotal->fetch();
$sqlnodata='Select b.BranchNo, Branch from `1branches` b  where b.BranchNo not in (Select BranchNo from dailysales) and b.Active=1 and b.Pseudobranch=0 order by Branch asc;';

// <table border=1><tr><td>Branch</td><td>Cash</td><td>Cash_NoOfReceipts</td><td>Collections</td>
//<td>Charge</td><td>Charge_NoOfReceipts</td><td>Returns</td><td>AuditCharges</td><td>StoreUsed</td></tr>'.;

$msg=$msg.'<br>'.'Count of branches: '.$restotal['NumBranches'].'<br>'.'Total Cash (Less Returns): '.$restotal['TotalCash'].'<br>Total Dated Collections: '.$restotal['TotalCollections'];

if (allowedToOpen(7331,'1rtc')){ $msg.='<br>Total Charge Sales: '.$restotal['TotalCharge'];}

$stmtnodata=$link->query($sqlnodata);$resnodata=$stmtnodata->fetchAll();
if ($stmtnodata->rowCount()==0){ $msgnodata='';}
    else { $msgnodata='No data:<br>';
            foreach($resnodata as $nodata){ $msgnodata=$msgnodata.$nodata['Branch'].'<br>';}
    }
$msg=$msgnodata.'<br><br>'.$msg;
?>