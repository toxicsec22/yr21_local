<?php
function postall($link,$table,$transfer){ 
    switch ($transfer){
        case 'In':
        $sql='UPDATE '.$table.' SET `PostedIn`=1, `PostedInByNo`=0 WHERE (`DateIN` is not null) AND `PostedIn`=0';//   AND DateOUT<Date_format(Now(),\'%Y-%m-%d\')';//temporarily set for yesterday since not automatic.
        break;
        default:
        $sql='UPDATE '.$table.' SET `Posted`=1, `PostedByNo`=0 WHERE `Posted`=0 ';// AND Date<Date_format(Now(),\'%Y-%m-%d\')';            
            break;
    }
    $stmt=$link->prepare($sql);
    $stmt->execute();
    header('Location:/'.$url_folder.'/index.php?done=1');
}

function postpermonth($link,$table,$month,$transfer){
    switch ($transfer){
        case 'Out':
        $sql='UPDATE '.$table.' SET `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' WHERE Month(`DateOUT`)='.$month.' AND Posted=0';   
            break;
        case 'In':
        $sql='UPDATE '.$table.' SET `PostedIn`=1, `PostedInByNo`='.$_SESSION['(ak0)'].' WHERE Month(`DateIN`)='.$month.' AND PostedIn=0';
        break;
        default:
        $sql='UPDATE '.$table.' SET `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' WHERE Month(`Date`)='.$month.' AND Posted=0';            
            break;
    }
    $stmt=$link->prepare($sql);
    $stmt->execute();
    header('Location:/'.$url_folder.'/index.php?done=1');
}

function postperdate($link,$table,$date,$transfer){ // REMOVED THIS CONDITION FROM ALL: AND `BranchNo`='.$_SESSION['bnum'].'; AND `ToBranchNo`='.$_SESSION['bnum'].' ;
    switch ($transfer){
        case 'Out':
        $sql='UPDATE '.$table.' SET `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' WHERE `DateOUT`<=\''.$date.'\'  AND Posted=0';   
            break;
        case 'In':
        $sql='UPDATE '.$table.' SET `PostedIn`=1, `PostedInByNo`='.$_SESSION['(ak0)'].' WHERE `DateIN`<=\''.$date.'\' AND PostedIn=0';
        break;
        case 'Assets':
        $sql='UPDATE '.$table.' SET `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' WHERE `DateAcquired`<=\''.$date.'\' AND Posted=0';
        break;
        case 'Prepaid':
        $sql='UPDATE '.$table.' SET `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' WHERE `DatePaid`<=\''.$date.'\' AND Posted=0';
        break;
        case 'JV':
            $sql='UPDATE '.$table.' SET `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' WHERE `JVDate`<=\''.$date.'\' AND Posted=0';
            break;
        case 'CV':
            $sql='UPDATE '.$table.' SET `APVPosted`=1, `APVPostedByNo`='.$_SESSION['(ak0)'].' WHERE `Date`<=\''.$date.'\' AND APVPosted=0'; 
            $stmt=$link->prepare($sql); $stmt->execute();
            $sql='UPDATE '.$table.' SET `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' WHERE `Date`<=\''.$date.'\' AND Posted=0'; 
            break;
        case 'FCV':
            $sql='UPDATE '.$table.' SET `APVPosted`=1, `APVPostedByNo`='.$_SESSION['(ak0)'].' WHERE APVPosted=0'; 
            $stmt=$link->prepare($sql); $stmt->execute();
            $sql='UPDATE '.$table.' SET `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' WHERE Posted=0'; 
            break;
        default:
        $sql='UPDATE '.$table.' SET `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' WHERE `Date`<=\''.$date.'\' AND Posted=0';            
            break;
    }
    $stmt=$link->prepare($sql);
    $stmt->execute();
  //  header("Location:/'.$url_folder.'/index.php?done=1");
}
?>