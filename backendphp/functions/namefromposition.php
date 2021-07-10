<?php
$sqlpos='SELECT PositionID, Nickname, FirstName, Surname, CONCAT(LEFT(FirstName,1),LEFT(MiddleName,1),LEFT(SurName,1)) AS Initials FROM `1employees` e JOIN `attend_30currentpositions` p ON e.IDNo=p.IDNo WHERE p.PositionID IN ('.$positionid.') ORDER BY JobLevelID DESC LIMIT 1';
$stmtpos=$link->query($sqlpos); $resfrompos=$stmtpos->fetch();
?>