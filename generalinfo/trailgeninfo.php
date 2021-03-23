<?php
function recordtrail($txnid,$table,$link,$editordel){
global $currentyr;
//$currentyr='2020';
//0 edit, 1 delete, 2 record sub for deletions of main
//if((strpos($table,'sub') !== false) OR ($editordel==2)){ goto sub;}
switch($table){
    case '1employees':
        $target='employeeedits'; $txnidfield='IDNo';
        break;    
    case '1_gamit.0idinfo':
        $target='idinfoedits'; $txnidfield='IDNo';
        break;
	case '1suppliers':
        $target='supplieredits'; $txnidfield='SupplierNo';
        break;
    case '1clients':
        $target='clientedits'; $txnidfield='ClientNo';
        break;
    case 'acctg_4blotterassign':
        $target='blotteredits'; $txnidfield='TxnID';
        break;
	case 'budget_1budgets':
        $target='budgetedits'; $txnidfield='TxnID';
        break;
}


$sql = 'SHOW COLUMNS FROM '.$table.''; $result = $link->query($sql); $res=$result->fetchAll();
$sql0='';
foreach( $res as $col  ) { $sql0.='`'.$col['Field']. '`,';}

$sqltrail='INSERT INTO `'.$currentyr.'_trail`.`'.$target.'` ('.$sql0.'EditOrDel,`EditOrDelByNo`,`EditOrDelTS`) SELECT '.$sql0
        .$editordel.' AS EditOrDel, '.$_SESSION['(ak0)'].' AS `EditOrDelByNo`, Now() AS `EditOrDelTS` FROM '.$table.' WHERE `'.$txnidfield.'`='.$txnid;
goto skipsub;

skipsub:
    if($_SESSION['(ak0)']==1002){ echo $sqltrail;}
$stmttrail=$link->prepare($sqltrail); $stmttrail->execute();
}

?>