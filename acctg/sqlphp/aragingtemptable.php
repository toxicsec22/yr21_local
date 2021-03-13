<?php
$thisyr=$currentyr; $lastyr=($thisyr-1); $twoyrsago=$thisyr-2;

$sql0='CREATE TEMPORARY TABLE araging AS select bal.ClientNo, c.ClientName, bal.BranchNo, Company, b.Branch,
sum(case when YEAR(Due)<'.$twoyrsago.' then ARAmount end) as `Before_'.$twoyrsago.'`,
sum(case when YEAR(Due)='.$twoyrsago.' then ARAmount end) as `Yr_'.$twoyrsago.'`,
sum(case when YEAR(Due)='.$lastyr.' then ARAmount end) as `Yr_'.$lastyr.'`,
sum(case when DateDiff('.$fromdate.',Due)>90 AND YEAR(Due)='.$thisyr.' then ARAmount end) as Beyond3MosOverdue,
sum(case when DateDiff('.$fromdate.',Due)<=90 and DateDiff('.$fromdate.',Due)>=60 AND YEAR(Due)='.$thisyr.' then ARAmount end) as `2to3MosOverdue`,
sum(case when DateDiff('.$fromdate.',Due)<60 and DateDiff('.$fromdate.',Due)>=30 AND YEAR(Due)='.$thisyr.' then ARAmount end) as `1MonthOverdue`,
sum(case when DateDiff('.$fromdate.',Due)<30 and DateDiff('.$fromdate.',Due)>=15 AND YEAR(Due)='.$thisyr.' then ARAmount end) as `2wksOverdue`,
sum(case when DateDiff('.$fromdate.',Due)<15 and DateDiff('.$fromdate.',Due)>=0 AND YEAR(Due)='.$thisyr.' then ARAmount end) as `DueNow`,
sum(case when DateDiff('.$fromdate.',Due)<0 and DateDiff('.$fromdate.',Due)>=(-7) then ARAmount end) as `DueNextWk`,
sum(case when DateDiff('.$fromdate.',Due)<(-7) and DateDiff('.$fromdate.',Due)>=(-15) then ARAmount end) as `DueNext2Wks`,
sum(case when DateDiff('.$fromdate.',Due)<(-15) and DateDiff('.$fromdate.',Due)>=(-21) then ARAmount end) as `DueNext3Wks`,
sum(case when DateDiff('.$fromdate.',Due)<(-21) then ARAmount end) as `DueBeyond3Wks`,
sum(ARAmount) as Total
from `'.$table.'` bal 
JOIN `1clients` c ON (bal.ClientNo =c.ClientNo)
join `1branches` b on b.BranchNo=bal.BranchNo join `1companies` co on co.CompanyNo=b.CompanyNo
'.$condition.' group by bal.ClientNo, bal.BranchNo order by sum(ARAmount) desc;';
//if($_SESSION['(ak0)']==1002){ echo $sql0; break;}
$stmt=$link->prepare($sql0); $stmt->execute();
?>