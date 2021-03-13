<?php
function recordtrail($txnid,$table,$link,$editordel){
//0 edit, 1 delete, 2 delete main and sub
if((strpos($table,'sub') !== false) OR ($editordel==2)){ goto sub;}
$sqltrail='INSERT INTO `quotations_2quotemaintrail`
    (`QuoteDate`,`ClientName`,`ContactPerson`,`Position`,`SirMaam`,`Warranty`,`Payment`,
    `Note1`,`Note2`,`Note3`,`Approved`,`EncodedByNo`,`TimeStamp`,`QuoteID`,
    `FaxNo`,`PostedByNo`,`Posted`,`EditOrDel`, `EditOrDelByNo`, `EditOrDelTS`)
    SELECT `QuoteDate`,`ClientName`,`ContactPerson`,`Position`,`SirMaam`,`Warranty`,`Payment`,
    `Note1`,`Note2`,`Note3`,`Approved`,`EncodedByNo`,`TimeStamp`,`QuoteID`,
    `FaxNo`,`PostedByNo`,`Posted`,'.$editordel.' AS EditOrDel, '.$_SESSION['(ak0)'].' AS `EditOrDelByNo`, Now() FROM `'.$table.'` WHERE `QuoteID`='.$txnid;
goto skipsub;

sub: 
$main=!isset($main)?str_replace('sub','main',$table):$main;
        
if ($editordel==2){ //record sub for deletions of main
    $sqltrail='INSERT INTO `quotations_2quotesubtrail` SELECT `QuoteSubID`,s.`QuoteID`,`Category`,`ItemCode`,`Description`,`Qty`,`Unit`,`UnitPrice`,
    `SupplierDetails1`,`SupplierDetails2`,s.`EncodedByNo`,s.`TimeStamp`, '
        .$editordel.' AS EditOrDel, '.$_SESSION['(ak0)'].' AS `EditOrDelByNo`, Now() '
        . ' FROM `'.$main.'` m JOIN `'.$table.'` s ON m.`QuoteID`=s.`QuoteID` WHERE s.`QuoteID`='.$txnid;
} else {
    $sqltrail='INSERT INTO `quotations_2quotesubtrail` SELECT `QuoteSubID`,s.`QuoteID`,`Category`,`ItemCode`,`Description`,`Qty`,`Unit`,`UnitPrice`,
    `SupplierDetails1`,`SupplierDetails2`,s.`EncodedByNo`,s.`TimeStamp`, '
        .$editordel.' AS EditOrDel, '.$_SESSION['(ak0)'].' AS `EditOrDelByNo`, Now() '
        . ' FROM `'.$main.'` m JOIN `'.$table.'` s ON m.`QuoteID`=s.`QuoteID` WHERE `QuoteSubID`='.$txnid;    
} 

    
skipsub:
    if($_SESSION['(ak0)']==1002){ echo $sqltrail;}
$stmttrail=$link->prepare($sqltrail); $stmttrail->execute();
}

function recordtrailallsub($txnid,$table,$link){
    $subtable=str_replace('main','sub',$table);
    recordtrail($txnid,$subtable,$link,2);
}