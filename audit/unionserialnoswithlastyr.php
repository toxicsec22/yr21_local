<?php
$sql0='create temporary table serialnos(
BranchNo smallint(6) not null,
ItemCode smallint(6) not null,
SerialNo varchar(100) null
)
Select BranchNo, ss.ItemCode,left(ss.SerialNo,100) as SerialNo from `invty_2sale` sm join `invty_2salesub` ss on sm.TxnID=ss.TxnID where ItemCode in (Select ItemCode from `invty_1items` where CatNo=90)
union Select BranchNo, ss.ItemCode,left(ss.SerialNo,100) as SerialNo from `'.(date('Y')).'_1rtc`.`invty_2sale` sm join `'.(date('Y')).'_1rtc`.`invty_2salesub` ss on sm.TxnID=ss.TxnID where ItemCode in (Select ItemCode from `invty_1items` where CatNo=90) ;
';
?>