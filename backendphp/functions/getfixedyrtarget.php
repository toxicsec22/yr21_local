<?php

$sqlget='SELECT `FixedYrTarget` FROM `00dataclosedby` WHERE `ForDB`=1;';
$stmtget=$link->query($sqlget);
$resget=$stmtget->fetch();
$targetthisyear=$resget['FixedYrTarget'];
?>