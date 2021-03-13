<?php
$path=$_SERVER['DOCUMENT_ROOT']; 
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(date('Y').'_1rtc',0):$link;


$sql0='UPDATE `1employees` e JOIN `attend_30currentpositions` cp ON e.IDNo=cp.IDNo JOIN `attend_0positions` p ON p.PositionID=cp.PositionID JOIN `attend_howlongwithus` h ON e.IDNo=h.IDNo SET e.SLThisYr=IF(InYears>=0.5,5,0), e.VLfromPosition=IF(InYears>=0.5,p.VLfromPosition,0), e.VLfromTenure=IF(InYears>1,IF((TRUNCATE(InYears,0)-1)>MaxVLfromTenure,MaxVLfromTenure,TRUNCATE(InYears,0)-1),0),WithLeaves=IF(InYears>.5,1,0);';
		
$stmt=$link->prepare($sql0); $stmt->execute();

header('Location:/yr21/index.php?done=1');
?>