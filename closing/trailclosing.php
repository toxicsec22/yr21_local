<?php
function recordtrail($txnid,$table,$link,$editordel){
    global $currentyr;
//$thisyr='2020';
//0 edit, 1 delete, 2 record sub for deletions of main
if((strpos($table,'sub') !== false) OR ($editordel==2)){ goto sub;}

switch($table){ case 'closing_2closemain': $target='closemain'; $txnidname='CloseID'; break;  }


$sql = 'SHOW COLUMNS FROM `'.$table.'`'; $result = $link->query($sql); $res=$result->fetchAll();
$sql0='';
foreach( $res as $col  ) { $sql0.='`'.$col['Field']. '`,';}

$sqltrail='INSERT INTO `'.$currentyr.'_trail`.`'.$target.'` ('.$sql0.'EditOrDel,`EditOrDelByNo`,`EditOrDelTS`) SELECT '.$sql0
        .$editordel.' AS EditOrDel, '.$_SESSION['(ak0)'].' AS `EditOrDelByNo`, Now() AS `EditOrDelTS` FROM `'.$table.'` WHERE `'.$txnidname.'`='.$txnid;

sub:

switch($table){ case 'closing_2closesub': $target='closesub'; $txnidname='CloseSubID'; break;  }

    $sql = 'SHOW COLUMNS FROM `'.$table.'`'; $result = $link->query($sql); $res=$result->fetchAll();
$sql0='';
foreach( $res as $col  ) { $sql0.='`'.$col['Field']. '`,';}
if ($editordel==2){ $sqltrail='` WHERE `'.$txnidname.'`='.$txnid; } //record sub for deletions of main
 else { $sqltrail=' WHERE `CloseSubID`='.$txnid; } 

$sqltrail='INSERT INTO `'.$currentyr.'_trail`.`'.$target.'` ('.$sql0.'EditOrDel,`EditOrDelByNo`,`EditOrDelTS`) SELECT '.$sql0
        .$editordel.' AS EditOrDel, '.$_SESSION['(ak0)'].' AS `EditOrDelByNo`, Now() AS `EditOrDelTS` FROM '.$table.$sqltrail;
goto skipsub;

skipsub:
    if($_SESSION['(ak0)']==1002){ echo $sqltrail;}
$stmttrail=$link->prepare($sqltrail); $stmttrail->execute();
}

?>