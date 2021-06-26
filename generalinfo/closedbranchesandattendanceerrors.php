<?php
// To check if stores are open

   if (allowedToOpen(900,'1rtc')){
   $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link; 
   date_default_timezone_set('Asia/Manila');
   $defaultyr=date('Y'); $attendyr=(date('Y-m-d')>date('Y-m-d',strtotime($defaultyr.'-12-20')))?$defaultyr+1:$defaultyr;   
   
   if (date('H:i:s')>='08:05:00'){
   $sqlclosed='Select b.Branch from `'.$attendyr.'_1rtc`.`1branches` b where b.Active=1 and b.Pseudobranch<>1 AND b.BranchNo<>100 and b.BranchNo not in (SELECT a.BranchNo FROM `'.$attendyr.'_1rtc`.`attend_2attendance` a where DateToday=curdate() and (TimeIn is not null and TimeIn<>0) group by a.BranchNo) 
       AND (YEAR(b.Anniversary)<'.$defaultyr.' OR b.Anniversary<=CURDATE()) AND IF(WEEKDAY(CURDATE())=6,`WithSunday`=1,1=1) AND IF((SELECT TypeOfDayNo FROM '.$attendyr.'_1rtc.attend_2attendancedates WHERE DateToday=CURDATE())<>0,b.Pseudobranch=0,1=1) order by Branch;'; 

    $stmt=$link->prepare($sqlclosed);
    $stmt->execute();
    $datatoshowclosed=$stmt->fetchAll(PDO::FETCH_ASSOC);    
    $count=0;
   if ($stmt->rowCount()>0){
       include_once($path.'/'.$url_folder.'/backendphp/layout/blink.php');
    $msg='<div id="blink" style="float:left; ">Branches that are not yet open today:  ';
    foreach($datatoshowclosed as $rows){
        $count++;
        //$msg=$msg.'<td>'.$rows['Branch'].'</td>'.($count%15==0?'</tr><tr>':'');
        $msg=$msg.$rows['Branch'].'&nbsp &nbsp';
   }
   echo $msg.'</div>';
   }
   }
   }
    
// to show attendance errors per person
include_once $path.'/'.$url_folder.'/attendance/attendsql/missingtimeinout.php';
$sqlnotimeout='SELECT `IDNo`, DATE_FORMAT(`DateToday`,"%Y-%m-%d") AS `MISSING_ATTENDANCE` FROM attend_41missingtimeinout 
    WHERE `IDNo`='.$_SESSION['(ak0)'].' AND `DateToday`<=CURDATE() ';

$stmt2=$link->query($sqlnotimeout); $missingtimeout=$stmt2->fetchAll(PDO::FETCH_ASSOC);
if ($stmt2->rowCount()>0){
    include_once($path.'/'.$url_folder.'/backendphp/layout/blink.php');
    $msg2='<div id="blink" style="float:left; font-weight:bold; font-size:large;"><br><b>ATTENDANCE ERRORS: (Please inform your department\'s person-in-charge asap.)</b><br><table><th>IDNo</th><th>On Dates</th><tr>';
    foreach($missingtimeout as $rows){
        $msg2=$msg2.'<td>'.$rows['IDNo'].'</td><td>'.$rows['MISSING_ATTENDANCE'].'</td></tr><tr>';
        
   }
   echo $msg2.'</tr></table></div>';
   }
$stmt=null; 
?>