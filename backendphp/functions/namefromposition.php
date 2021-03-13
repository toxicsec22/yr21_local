<?php
$sqlpos='Select * from `1employees` e join `attend_30currentpositions` p on e.IDNo=p.IDNo where p.PositionID='.$positionid;
$stmt=$link->query($sqlpos); $resfrompos=$stmt->fetch();
$fullname=$resfrompos['FirstName'].' '.substr($resfrompos['MiddleName'],0,1).'. '.$resfrompos['SurName'];
?>