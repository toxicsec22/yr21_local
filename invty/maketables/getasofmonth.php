<?php
$sql0='SELECT IF((YEAR(`00dataclosedby`.`DataClosedBy`) < '.$currentyr.'),0,MONTH(`00dataclosedby`.`DataClosedBy`)) AS `AsofMonth`
            FROM `00dataclosedby` WHERE (`00dataclosedby`.`ForDB` = 0)';
$stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $asofmonth=$res0['AsofMonth'];
?>