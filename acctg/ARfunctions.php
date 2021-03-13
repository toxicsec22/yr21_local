<?php
function makepdcs($clientno,$link){
   
   $sql0='Create temporary table undeppdcs (
DateofPDC date not null,
PDCNo varchar(30) not null,
PDC double not null,
CompanyNo smallint(6) NOT NULL
)
SELECT `DateofPDC`,`PDCNo`, `PDC`, b.`CompanyNo` FROM acctg_undepositedclientpdcs up join `1branches` b on b.BranchNo=up.BranchNo where PDC<>0 and up.ClientNo='.$clientno.' Order by `DateofPDC`,`PDCNo`';
//echo $sql0; break;acctg_38undepositedclientpdcs
$stmt=$link->prepare($sql0);
$stmt->execute();
}
?>