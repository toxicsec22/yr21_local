<?php
$sql0='drop temporary table if exists latestdatecount;';
$stmt0=$link->prepare($sql0);
$stmt0->execute();

$sqllatest='Select max(cm.Date) as LatestDateCount from `audit_2countmain` cm join `audit_2countsub` cs on cm.CountID=cs.CountID where (cm.Date<\''.$txndate.'\' and BranchNo='.$branch.' and ItemCode='.$itemcode.')';
// echo $sqllatest;break;
$stmt=$link->query($sqllatest);
$result=$stmt->fetch(); //echo $stmt->rowCount(); break;
if (!is_null($result['LatestDateCount'])){
    } else {
    //check if branch is new this yr
    $sql='SELECT if((Select Year(Anniversary)FROM 1branches where BranchNo='.$branch.' )='.$currentyr.',true,false) as NewBranch';
    $stmt=$link->query($sql);
    $result=$stmt->fetch();
    
    if ($result['NewBranch']==true){
    // get Transfer In date for new branches
    
    $sqllatest='SELECT min(DateIN) as LatestDateCount FROM invty_2transfer m join invty_2transfersub s on m.TxnID=s.TxnID where DateIN is not null and DateIN<\''.$txndate.'\' and ToBranchNo='.$branch.' and ItemCode='.$itemcode;
    } else {
        $sqllatest='Select max(cm.Date) as LatestDateCount from `audit_2countmain` cm join `audit_2countsub` cs on cm.CountID=cs.CountID where (cm.Date<\''.$txndate.'\' and BranchNo='.$branch.')';
    $stmt=$link->query($sqllatest);
    $result=$stmt->fetch();
    if ($stmt->rowCount()==0){ echo 'Ask IT to check this'; exit();
        
    }
    }
}
//echo $sqllatest;break;
$sql0='create temporary table latestdatecount (
LatestDateCount date not null
)'.$sqllatest;
$stmt0=$link->prepare($sql0);
$stmt0->execute();

$sql0='drop temporary table if exists mandays';
$stmt0=$link->prepare($sql0);
$stmt0->execute();
$sql0='create temporary table mandays (
IDNo smallint(6) not null,
ManDays smallint(3) not null
)
SELECT a.IDNo, count(LeaveNo) as ManDays FROM attend_2attendance a join `attend_30latestpositionsinclresigned` p on a.IDNo=p.IDNo where (p.PositionID in (32,33,34,37,81,50,51,52,53,55)) and a.BranchNo='.$branch.' and (LeaveNo=11 or (LeaveNo in (12,13,15) and OTTypeNo<>0)) and (DateToday>=(Select LatestDateCount from latestdatecount) and DateToday<=\''.$txndate.'\') group by a.IDNo';
$stmt0=$link->prepare($sql0);
$stmt0->execute();

$sql='select sum(ManDays) as TotalManDays from mandays';
$stmt=$link->query($sql);
$result=$stmt->fetch();
$totalmandays=$result['TotalManDays'];

$sql0='Insert into prorated (IDNo, SaleNo, ProratedCharge) Select m.IDNo, concat(\''.$saleno.'\'," - ",e.Nickname) as SaleNo, ((ManDays/'.$totalmandays.')*'.$amount.') as ProratedCharge from mandays m left join `1employees` e on e.IDNo=m.IDNo group by m.IDNo'; // echo $sql0; break;
$stmt0=$link->prepare($sql0);
$stmt0->execute();


?>