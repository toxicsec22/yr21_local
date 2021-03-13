<?php

date_default_timezone_set('Asia/Manila');
$defaultyr=date('Y'); $attendyr=(date('Y-m-d')>date('Y-m-d',strtotime($defaultyr.'-12-20')))?($defaultyr+1):$defaultyr;

//AWOL
$sqlawol='SELECT FullName FROM '.$attendyr.'_1rtc.attend_30currentpositions cp JOIN 1_gamit.1rtcusers ru ON cp.IDNo=ru.IDNo JOIN '.$attendyr.'_1rtc.1branches b ON cp.BranchNo=b.BranchNo JOIN '.$attendyr.'_1rtc.attend_2attendance a ON cp.IDNo=a.IDNo WHERE DateToday=CURDATE() AND DATE_FORMAT(NOW(), "%H:%i")>="08:00" AND DATE_FORMAT(NOW(), "%H:%i")<="10:00" AND LeaveNo=18 AND IF(cp.deptid=10,ru.ProgCookie="'.$_COOKIE['_comkey'].'",cp.deptid IN (SELECT DISTINCT(d.deptid) FROM '.$attendyr.'_1rtc.attend_30currentpositions cp2 JOIN 1_gamit.1rtcusers ru2 ON cp2.IDNo=ru2.IDNo JOIN 1departments d ON cp2.deptheadpositionid=d.deptheadpositionid WHERE ru2.ProgCookie="'.$_COOKIE['_comkey'].'"));';
$stmtawol=$link->query($sqlawol);
$dataawol=$stmtawol->fetchAll(); $cntawol=$stmtawol->rowCount();

?>