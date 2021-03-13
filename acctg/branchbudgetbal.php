<?php
$sql0='create temporary table Spent as
SELECT  de.`BranchNo`, de.`TypeID`, Sum(de.Amount) as `Spent` FROM `acctg_2depencashsub` de join `acctg_2depositmain` dm on dm.TxnID=de.TxnID 
where (de.ApprovalNo=0 or isnull(de.ApprovalNo)) and de.BranchNo='.$_SESSION['bnum'].' and (not isnull(de.TypeID))  and (Month(dm.`Date`)=Month(curdate()))  group by de.`BranchNo`, de.`TypeID`,  Month(dm.`Date`);';
$stmt=$link->prepare($sql0); $stmt->execute();
$sql='Select Branch, BudgetDesc,Specifics, `'.str_pad(date('m'),2,'0',STR_PAD_LEFT).'` as Budget, ifnull(Spent,0) as Spent, `'.str_pad(date('m'),2,'0',STR_PAD_LEFT).'`-ifnull(Spent,0) as Available  from `acctg_5branchpreapprovedbudgetspermonth` bm left join Spent s on bm.BranchNo=s.BranchNo and bm.TypeID=s.TypeID
join `1branches` b on bm.BranchNo=b.BranchNo join `acctg_1branchpreapprovedbudgetlist` bl on bl.TypeID=bm.TypeID
 where bm.BranchNo='.$_SESSION['bnum'].' AND bm.`'.str_pad(date('m'),2,'0',STR_PAD_LEFT).'`<>0 order by BudgetDesc'; $stmt0=$link->query($sql); $res0=$stmt0->fetchAll();
if($stmt0->rowCount()>0){
    echo '<br>Budget for Current Month<br>';
$columnnames=array('Branch', 'BudgetDesc','Specifics', 'Budget', 'Spent', 'Available');
$hidecount=true;
include('../backendphp/layout/displayastableonlynoheaders.php');
echo '<br>';
}
?>