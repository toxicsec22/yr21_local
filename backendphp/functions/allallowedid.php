<?php
$currentyr=2021; $lastyr=$currentyr-1; $nextyr=$currentyr+1; $last2yrs=$currentyr-2; $last3yrs=$currentyr-3;

$path=$_SERVER['DOCUMENT_ROOT'];
include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

function allAllowedID($processid){
    global $link;
    $sqlinfunction='SELECT IFNULL(GROUP_CONCAT(cp.IDNo),"x") AS allAlowed
    FROM permissions_2allprocesses ap JOIN attend_30currentpositions cp ON FIND_IN_SET(cp.PositionID,`AllowedPos`) OR
    FIND_IN_SET(cp.IDNo,`AllowedPerID`)
    WHERE ProcessID='.$processid; 
    $stmt=$link->query($sqlinfunction); $res=$stmt->fetch();
    $strallowed=$res['allAlowed']; 
    return $strallowed;
}