<?php
function getNumber($type,$name){
	global $currentyr;
    include_once('../generalinfo/lists.inc');
    $linkinfunction=!isset($linkinfunction)?connect_db(''.$currentyr.'_1rtc',0):$linkinfunction;
    switch ($type){
        case 'Client':
            $num=getValue($linkinfunction,'1clients','Left(`ClientName`,20)',$name,'ClientNo');
            break;
        case 'Supplier':
            //
            $num=getValue($linkinfunction,'1suppliers','`SupplierName`',$name,'SupplierNo');
            break;
        case 'Company':
           $num=getValue($linkinfunction,'1companies','Company',$name,'CompanyNo');
            break;
        case 'Branch':
            
	    if (!in_array($_SESSION['&pos'],array(13,14,15,16,17))) { $num=getValue($linkinfunction,'1branches','Branch',$name,'BranchNo');}
	    else {
		$listsql="SELECT `BranchNo` from `1branches` WHERE Branch Like '" . $name ."' AND CompanyNo=".$_SESSION['*cnum']." Limit 1";
		$stmt=$linkinfunction->query($listsql);  $result=$stmt->fetch(PDO::FETCH_ASSOC);
		$num=($stmt->rowCount()>0)?$result['BranchNo']:'';}
            break;
	case 'BranchName':
           // 
            $num=getValue($linkinfunction,'1branches','BranchNo',$name,'Branch');
	    break;
        case 'Employee':
            $num=getValue($linkinfunction,'1employees` `e','concat(e.`Nickname`,\' \',e.`SurName`)',$name,'IDNo');
            break;
        case 'ClientEmployee':
            $num=getValue($linkinfunction,'acctg_01uniclientsalespersonfordep','Left(`ClientName`,20)',$name,'ClientNo');
            break;
        case 'Account':
            //
            $num=getValue($linkinfunction,'acctg_1chartofaccounts','ShortAcctID',$name,'AccountID');
            break;
//	case 'BudgetType':  //not currently used
//	    $num=getValue($linkinfunction,'acctg_1branchpreapprovedbudgetlist','BudgetDesc',$name,'TypeID');
//            break;
    }
    return $num;
}

function getAutoTxnNo($txnnoprefix,$charnum,$field,$table,$linkinfunction){
    $sql='SELECT ' .$field.' FROM '.$table.' where Left('.$field.','.$charnum.')=\''.$txnnoprefix.'\' order by  ' .$field .'  desc Limit 1;';
	    $stmt=$linkinfunction->query($sql);
	    $result=$stmt->fetch();
	    if (is_null($result[$field])){
		$txnno=$txnnoprefix.str_pad('1',3,'0',STR_PAD_LEFT);
	    } else {
		$txnno=$txnnoprefix.str_pad((substr($result[$field],-3)+1),3,'0',STR_PAD_LEFT);
	    }
    return $txnno;
}

?>