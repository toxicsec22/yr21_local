<?php
function postall($link,$table,$transfer){ 
    switch ($transfer){
        case 'In':
        $sql='Update '.$table.' Set `PostedIn`=1, `PostedInByNo`=0 where (`DateIN` is not null) and `PostedIn`=0';//   and DateOUT<Date_format(Now(),\'%Y-%m-%d\')';//temporarily set for yesterday since not automatic.
        break;
        default:
        $sql='Update '.$table.' Set `Posted`=1, `PostedByNo`=0 where `Posted`=0 ';// and Date<Date_format(Now(),\'%Y-%m-%d\')';            
            break;
    }
    $stmt=$link->prepare($sql);
    $stmt->execute();
    header('Location:/'.$url_folder.'/index.php?done=1');
}

function postpermonth($link,$table,$month,$transfer){
    switch ($transfer){
        case 'Out':
        $sql='Update '.$table.' Set `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' where Month(`DateOUT`)='.$month.' and Posted=0';   
            break;
        case 'In':
        $sql='Update '.$table.' Set `PostedIn`=1, `PostedInByNo`='.$_SESSION['(ak0)'].' where Month(`DateIN`)='.$month.' and PostedIn=0';
        break;
        default:
        $sql='Update '.$table.' Set `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' where Month(`Date`)='.$month.' and Posted=0';            
            break;
    }
    $stmt=$link->prepare($sql);
    $stmt->execute();
    header('Location:/'.$url_folder.'/index.php?done=1');
}

function postperdate($link,$table,$date,$transfer){ // REMOVED THIS CONDITION FROM ALL: and `BranchNo`='.$_SESSION['bnum'].'; and `ToBranchNo`='.$_SESSION['bnum'].' ;
    switch ($transfer){
        case 'Out':
        $sql='Update '.$table.' Set `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' where `DateOUT`<=\''.$date.'\'  and Posted=0';   
            break;
        case 'In':
        $sql='Update '.$table.' Set `PostedIn`=1, `PostedInByNo`='.$_SESSION['(ak0)'].' where `DateIN`<=\''.$date.'\' and PostedIn=0';
        break;
        case 'Assets':
        $sql='Update '.$table.' Set `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' where `DateAcquired`<=\''.$date.'\' and Posted=0';
        break;
        case 'Prepaid':
        $sql='Update '.$table.' Set `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' where `DatePaid`<=\''.$date.'\' and Posted=0';
        break;
        case 'JV':
            $sql='Update '.$table.' Set `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' where `JVDate`<=\''.$date.'\' and Posted=0';
            break;
        default:
        $sql='Update '.$table.' Set `Posted`=1, `PostedByNo`='.$_SESSION['(ak0)'].' where `Date`<=\''.$date.'\' and Posted=0';            
            break;
    }
    $stmt=$link->prepare($sql);
    $stmt->execute();
  //  header("Location:/'.$url_folder.'/index.php?done=1");
}
?>