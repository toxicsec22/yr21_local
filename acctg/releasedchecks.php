<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(5402); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=false; include_once('../switchboard/contents.php');
 
  

$alternatecolor="ecd9c6";
$columnnames=array('Date','CVNo','DateofCheck','CheckNo','Bank','Payee','Remarks','Amount','CheckReceivedBy','Released','ReleaseDate','Unreleased','ClearedAmt','ClearedDate','Uncleared');
$columnsub=array('Date','CVNo','DateofCheck','CheckNo','Bank','Payee','Remarks','AmountValue','ReleasedValue','ReleaseDate','UnreleasedValue','ClearedValue','ClearedDate','UnclearedValue');

$which=!isset($_GET['w'])?'List':$_GET['w'];
$rdate=(!isset($_REQUEST['ReleaseDate']) or empty($_REQUEST['ReleaseDate']))?date('Y-m-d'):$_REQUEST['ReleaseDate'];
$filter=!isset($_REQUEST['f'])?'2':$_REQUEST['f'];
$title='Released Checks Report'; 

switch ($filter){
    case 1: $filter=''; $futurefilter=$filter; $title.=':  ALL'; break; //show all
    case 2: $filter=' HAVING (ReleaseDate IS NULL) '; $futurefilter=$filter; $title='Unreleased Checks'; break; 
    case 3: $filter=' HAVING (ReleaseDate IS NOT NULL) '; $futurefilter=$filter; $title='Released Checks (Cleared and Uncleared)'; break;
    case 4: $filter=' HAVING (ReleaseDate IS NOT NULL) AND (Cleared IS NULL) '; 
        $futurefilter=' HAVING (ReleaseDate IS NOT NULL) '; 
        $title='Released Checks (Uncleared)'; 
        break;
default:
    $filter=' HAVING (Cleared IS NULL) AND DateofCheck<=Date_Add(Now(), Interval 30 day)'; 
    $title='Released and Uncleared Checks Dated Until '.date('Y-m-d',strtotime('+30 days')); $futurefilter=' HAVING DateofCheck<=Date_Add(Now(), Interval 30 day)';
}

if (isset($_REQUEST['filter1']) or isset($_REQUEST['filter2'])) {
    if (isset($_REQUEST['filter1'])) { $filter=''; $title='Filtered by  '; } else { $title='<br>Filtered by  ';}
    if(!empty($_REQUEST['CheckDate'])) { $filter.=(empty($filter)?' HAVING ':' AND ').' DateofCheck<=\''.$_REQUEST['CheckDate'].'\' '; 
    $title.=' Date of Check As Of '.$_REQUEST['CheckDate'].str_repeat('&nbsp;', 3).'<br>';} 
    if(!empty($_REQUEST['ReleaseDate'])) { $filter.=(empty($filter)?' HAVING ':' AND ').' ReleaseDate=\''.$_REQUEST['ReleaseDate'].'\' '; 
    $title.=' Release Date : '.$_REQUEST['ReleaseDate'].str_repeat('&nbsp;', 3).'<br>';} 
    if(!empty($_REQUEST['Bank'])) { $filter.=(empty($filter)?' HAVING ':' AND ').' Bank LIKE \''.$_REQUEST['Bank'].'\' '; 
    $title.=' Bank: '.$_REQUEST['Bank'].str_repeat('&nbsp;', 3).'<br>'; } 
    if(!empty($_REQUEST['Payee'])) { $filter.=(empty($filter)?' HAVING ':' AND ').' Payee LIKE \''.$_REQUEST['Payee'].'\' '; $title.=' Payee: '.$_REQUEST['Payee']; } 
    //if($_SESSION['(ak0)']==1002) { echo $filter;}
    $futurefilter=$filter;
}

if (in_array($which,array('List'))){
  
$sqllastyr='SELECT CONCAT(0,"-",CVNo) AS TxnID,"From Last Yr" AS `Date`,`CVNo`,`DateofCheck`,`CheckNo`,ShortAcctID AS `Bank`,`Payee`, "" AS Remarks,truncate(AmountofCheck,2) AS `AmountValue`,CheckReceivedBy, IF(ISNULL(ReleaseDate),"",truncate(AmountofCheck,2)) AS `ReleasedValue`, IF(ISNULL(ReleaseDate),truncate(AmountofCheck,2),"") AS `UnreleasedValue`,ReleaseDate, IF(ISNULL(Cleared),"",truncate(AmountofCheck,2)) AS `ClearedValue`, IF(ISNULL(Cleared),truncate(AmountofCheck,2),"") AS `UnclearedValue`,`Cleared` AS ClearedDate FROM `acctg_3unclearedchecksfromlastperiod` lp JOIN acctg_1chartofaccounts ca ON ca.AccountID=lp.FromAccount';
$sqlcurr='SELECT CONCAT(1,"-",m.CVNo) AS TxnID, m.Date, m.CVNo, m.DateofCheck,m.CheckNo, ca.ShortAcctID as Bank, m.Payee, m.Remarks, truncate(sum(s.Amount),2) as AmountValue,CheckReceivedBy,IF(ISNULL(ReleaseDate),"",truncate(sum(s.Amount),2)) AS `ReleasedValue`, IF(ISNULL(ReleaseDate),truncate(sum(s.Amount),2),"") AS `UnreleasedValue`, ReleaseDate, IF(ISNULL(Cleared),"",truncate(sum(s.Amount),2)) AS `ClearedValue`, IF(ISNULL(Cleared),truncate(sum(s.Amount),2),"") AS `UnclearedValue`,m.`Cleared` AS ClearedDate from acctg_2cvmain as m join acctg_1chartofaccounts ca on ca.AccountID=m.CreditAccountID join acctg_2cvsub s on m.CVNo=s.CVNo group by m.CVNo '; 
$sqlfuture='SELECT CONCAT(2,"-",m.CVNo) AS TxnID, m.Date, m.CVNo, m.DateofCheck,m.CheckNo, ca.ShortAcctID as Bank, m.Payee, m.Remarks, truncate(sum(s.Amount),2) as AmountValue,CheckReceivedBy,IF(ISNULL(ReleaseDate),"",truncate(sum(s.Amount),2)) AS `ReleasedValue`, IF(ISNULL(ReleaseDate),truncate(sum(s.Amount),2),"") AS `UnreleasedValue`, ReleaseDate, 0 AS `ClearedValue`, TRUNCATE(sum(s.Amount),2) AS `UnclearedValue`, "" AS ClearedDate from acctg_4futurecvmain as m join acctg_1chartofaccounts ca on ca.AccountID=m.CreditAccountID join acctg_4futurecvsub s on m.CVNo=s.CVNo group by m.CVNo '; 
}

if (in_array($which,array('Lookup','AddReleaseDate','Unset'))){    $txnid=substr($_GET['TxnID'],2);}

if (in_array($which,array('AddReleaseDate','Unset','SetNonBank','SetBasedOnPayee'))){
    if (!allowedToOpen(5402,'1rtc')) { echo 'No permission'; exit; }
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    if(substr($_GET['TxnID'],0,1)==0) { $table='acctg_3unclearedchecksfromlastperiod'; $field='CVNo';} 
    elseif (substr($_GET['TxnID'],0,1)==2) { $table='acctg_4futurecvmain'; $field='CVNo'; } else { $table='acctg_2cvmain'; $field='CVNo'; }
    switch ($which){
        case 'SetNonBank':
            $sql=' CreditAccountID NOT IN (SELECT AccountID FROM `banktxns_1maintaining`) ';
            break;
        case 'SetBasedOnPayee':
            $sql=' (Payee LIKE \''.$_POST['Payee'].'\') ';
            break;
        default:
            $sql=' `'.$field.'`='.$txnid;
            break;
    }
    
    $sql='UPDATE `'.$table.'` SET ReleaseDate='.($which=='Unset'?'NULL':'\''.$rdate.'\'').', ReleaseDateByNo='.$_SESSION['(ak0)'].', ReleaseDateTS=Now()'.(in_array($which,array('AddReleaseDate','SetBasedOnPayee'))?',CheckReceivedBy="'.$_POST['CheckReceivedBy'].'"':'').' WHERE '.$sql.($which=='Unset'?' AND ((ReleaseDate >\''.$_SESSION['nb4A'].'\') OR ReleaseDate LIKE \'0000-00-00\')':' AND (ReleaseDate IS NULL)');
 //   if($_SESSION['(ak0)']==1002) { echo $sql; break;}
    $stmt=$link->prepare($sql); $stmt->execute(); $connector=strpos($_SERVER['HTTP_REFERER'],'?')?'&':'?';
    header("Location:".$_SERVER['HTTP_REFERER'].(strpos($_SERVER['HTTP_REFERER'],'ReleaseDate')?'':$connector."ReleaseDate=".$rdate));
}

switch ($which){
case 'List':
if (!allowedToOpen(5402,'1rtc')) { echo 'No permission'; exit; }
$txnidname='CVNo';
$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' `DateofCheck`,`CVNo` ');
$sql0='CREATE TEMPORARY TABLE released AS '.$sqllastyr.$filter.' UNION ALL '.$sqlcurr.$filter.' UNION ALL '.$sqlfuture.$futurefilter.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC'); // if($_SESSION['(ak0)']==1002) { echo $sql0;}
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sqltotal='SELECT FORMAT(SUM(AmountValue),2) AS Amount, FORMAT(SUM(ReleasedValue),2) AS Released, FORMAT(SUM(UnreleasedValue),2) AS Unreleased, FORMAT(SUM(ClearedValue),2) AS ClearedAmt, FORMAT(SUM(UnclearedValue),2) AS Uncleared FROM released;';
$stmttotal=$link->query($sqltotal); $restotal=$stmttotal->fetch();
$totals='<br><br><table style="background-color:FFFFFF;"><tr><td>TOTALS</td>'; $colsamounts=array('Amount','Released','Unreleased','ClearedAmt','Uncleared');
foreach($colsamounts as $col){ $totals.='<td>'.$col.'<br>'.$restotal[$col].'</td>';}
$totals.='</tr></table>';

$formdesc='</i><br><br><form action="releasedchecks.php" method=post style="display:inline-block;">Set default release date: <input type=date name="ReleaseDate" value="'.$rdate.'"><input type=submit></form>'
        .  str_repeat('&nbsp;', 20).'<form action="releasedchecks.php?w=SetNonBank&TxnID=9&ReleaseDate='.$rdate.'&action_token='.$_SESSION['action_token'].'" method=post style="display:inline-block;">'
        . '<input type=hidden name="ReleaseDate" value="'.$rdate.'"><input type=submit value="Set all NON-BANK as released"></form>'
        .  str_repeat('&nbsp;', 20).'<form action="releasedchecks.php?w=SetBasedOnPayee&TxnID=9&ReleaseDate='.$rdate.'&action_token='.$_SESSION['action_token'].'" method=post style="display:inline-block;">Release based on payee (first verify with filter) &nbsp; <input type=text name=Payee placeholder="Complete payee name" >'
        . '  Received By<input type="text" name="CheckReceivedBy" size=10 ></input>'
        . '<input type=hidden name="ReleaseDate" value="'.$rdate.'"> <input type=submit value="Set ALL as released (current year)"></form><br><br>';
$formdesc.='<form action="releasedchecks.php" method=get style="display:in-line; border: solid 1px; padding: 10px;" >Filter by: '
        . '<input type=radio name=f value=0>Uncleared (Released and Unreleased)</input>'.str_repeat('&nbsp;', 10)
        . '<input type=radio name=f value=2>Unreleased </input>'.str_repeat('&nbsp;', 10)
        . '<input type=radio name=f value=3>Released (Cleared and Uncleared) </input>'.str_repeat('&nbsp;', 10)
        . '<input type=radio name=f value=4>Released and Uncleared </input>'.str_repeat('&nbsp;', 10)
        . '<input type=radio name=f value=1>Show All</input>'.str_repeat('&nbsp;', 10)
        . '<input type=submit value="Set filter"></form>'
        . '<form action="releasedchecks.php" method=post style="display:in-line; border: solid 1px; padding: 10px;" >Filter by: '.str_repeat('&nbsp;', 10)
        . 'Date of Check as of &nbsp; <input type=date name=CheckDate ></input>'.str_repeat('&nbsp;', 10)
        . 'Release Date &nbsp; <input type=date name=ReleaseDate ></input>'.str_repeat('&nbsp;', 10)
        . 'Bank &nbsp; <input type=text name=Bank placeholder="wildcard search: use %" ></input>'.str_repeat('&nbsp;', 10)
        . 'Payee &nbsp; <input type=text name=Payee placeholder="wildcard search: use %" ></input>'.str_repeat('&nbsp;', 10)
        . '<input type=submit name="filter1" value="Set as filter">'.str_repeat('&nbsp;', 10)
        .'<input type=hidden name="f" value="'.(!isset($_GET['f'])?'1':$_GET['f']).'">'
        .'<input type=submit name="filter2" value="Set as second filter"></form>'
        . $totals.'<i>';

$sql='SELECT *, FORMAT(AmountValue,2) AS Amount, FORMAT(ReleasedValue,2) AS Released, FORMAT(UnreleasedValue,2) AS Unreleased, FORMAT(ClearedValue,2) AS ClearedAmt, FORMAT(UnclearedValue,2) AS Uncleared  FROM released;'; 

//$addlprocess2='releasedchecks.php?w=AddReleaseDate&ReleaseDate='.$rdate.'&TxnID='; $addlprocesslabel2='Set'; 
    $editprocesslabel='Lookup'; $editprocess='releasedchecks.php?w=Lookup&CVNo=';$addlprocesslabel='Unset'; $addlprocess='releasedchecks.php?w=Unset&ReleaseDate='.$rdate.'&CVNo=';
if (allowedToOpen(5402,'1rtc')){
$inputprocess='releasedchecks.php?w=AddReleaseDate&ReleaseDate='.$rdate.'&'; $inputprocesslabel='Received By'; $inputname='CheckReceivedBy'; 
}
include('../backendphp/layout/displayastable.php');
   break;
case 'Lookup':
    if (!allowedToOpen(5402,'1rtc')) { echo 'No permission'; exit; }
    
    if(substr($_GET['CVNo'],0,1)==0) { //last yr
        $yr='yr'.substr((intval($currentyr))-1,2);
        $sql0='SELECT TxnID FROM `acctg_2vouchermain` WHERE TxnID='.$txnid; 
        $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $txnid=$res0['TxnID'];
        $txn='TxnID';
        header('Location:/'.$yr.'/acctg/addeditsupplyside.php?w='.$txn.'&TxnID='.$txnid); exit();
    } elseif (substr($_GET['CVNo'],0,1)==2) { $yr='yr'.substr($currentyr,2); $txn='FutureCV';}
            else { $yr='yr'.substr($currentyr,2); $txn='CV'; 
    
            header('Location:/'.$yr.'/acctg/addeditsupplyside.php?w='.$txn.'&CVNo='.$txnid);
            }
    break;
  
    
}
noform:
      $link=null; $stmt=null;
?>