<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(524,'1rtc')) { echo 'No permission'; exit; }
$showbranches=true; include_once('../switchboard/contents.php');

	include_once('../backendphp/functions/editok.php');
	include_once "../generalinfo/lists.inc"; //include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	include_once('../backendphp/functions/getnumber.php');
 $which=$_GET['w'];  $method='POST'; 

switch($which){
    case 'NewAsset':
        if (!allowedToOpen(525,'1rtc')) { echo 'No permission'; exit; }
    $title='Record New Asset'; $fieldsinrow=4;
    $columnnames=array(
                    array('field'=>'Branch', 'type'=>'text','size'=>15, 'required'=>true, 'list'=>'branchnames', 'value'=>$_SESSION['@brn']),
		    array('field'=>'DateAcquired', 'type'=>'date','size'=>15,'required'=>true,'value'=>date('Y-m-d')),
		    array('field'=>'AssetDesc', 'caption'=>'Asset Description', 'type'=>'text','size'=>20, 'required'=>true),
		    array('field'=>'AssetAccount', 'type'=>'text','size'=>15, 'required'=>true, 'list'=>'accounts'),
		    array('field'=>'DeprAccount', 'caption'=>'Depreciation Account', 'type'=>'text','size'=>15, 'required'=>true, 'list'=>'accounts'),
		    array('field'=>'AcqCost', 'caption'=>'Acquisition Cost', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'EstLifeInMonths', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'SalvageValue', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0)                    
                    );
    $action='assetanddepr.php?w=RecordAsset';
    $liststoshow=array('branchnames');
    $whichotherlist='acctg'; $listcondition='WHERE AccountType=6 OR (AccountID>=940 AND AccountID<=947) '; $otherlist=array('accounts');
     include('../backendphp/layout/inputmainform.php');

        break;

case 'RecordAsset':
    if (!allowedToOpen(525,'1rtc')) { echo 'No permission'; exit; }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php'; 
        //to check if editable TEMPORARILY REMOVED THIS RESTRICTION
	
        $branchno=getValue($link,'1branches','Branch',addslashes($_POST['Branch']),'BranchNo');
	$acctid=getNumber('Account',addslashes($_POST['AssetAccount']));
	$depracctid=getNumber('Account',addslashes($_POST['DeprAccount']));
        $sql='';
        $columnstoadd=array('DateAcquired','AssetDesc', 'EstLifeInMonths', 'SalvageValue');
	foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; }
	$sql='INSERT INTO `acctg_1assets` SET BranchNo='.$branchno.', AssetAccountID='.$acctid.', AcqCost='.(!is_numeric($_POST['AcqCost'])?str_replace(',', '',$_POST['AcqCost']):$_POST['AcqCost']).','.$sql.'EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	$stmt=$link->prepare($sql); $stmt->execute();	
        //get txnid
        $sql='SELECT AssetID,DateAcquired FROM `acctg_1assets` WHERE AssetDesc LIKE \''.$_POST['AssetDesc'].'\' AND BranchNo='.$branchno.' AND DateAcquired=\''.$_POST['DateAcquired'].'\'';
        $stmt=$link->query($sql); $result=$stmt->fetch(); $txnid=$result['AssetID']; //echo $sql;       break;
	//$month=substr($result['DateAcquired'],5,2)+((substr($result['DateAcquired'],7,2)<16)?0:1);
        //if acquired before the 16th of the month, depreciation starts at the same month of acquisition
	$deprdate=substr($result['DateAcquired'],0,4).'-'.(substr($result['DateAcquired'],5,2)+((substr($result['DateAcquired'],8,2)<16)?0:(substr($result['DateAcquired'],5,2)==12?-11:1))).'-'.substr($result['DateAcquired'],8,2);
	$deprvalue=round(($_POST['AcqCost']-$_POST['SalvageValue'])/$_POST['EstLifeInMonths'],2);
	$counter=0;
	while ($counter<$_POST['EstLifeInMonths']) {
	    $sql1='INSERT INTO `acctg_1assetsdepr` (`AssetID`,`DeprAccountID`,`DeprDate`,`Amount`, `EncodedByNo`) SELECT '.$txnid.', '.$depracctid.', LAST_DAY(date_add(\''.$deprdate.'\', INTERVAL '.$counter.' MONTH)), '.$deprvalue.','.$_SESSION['(ak0)']; 
	    $stmt=$link->prepare($sql1); $stmt->execute();
	    $counter=$counter+1;
	}
        header('Location:assetanddepr.php?w=AssetandDepr&TxnID='.$txnid);
        
    break;

case 'AssetandDepr':
    if (!allowedToOpen(525,'1rtc')) { echo 'No permission'; exit; }
    $txnid=intval($_REQUEST['TxnID']); $title='Asset and Depreciation';  $formdesc='<a href="assetanddepr.php?w=NewAsset">Add New Asset</a>'.  str_repeat('&nbsp;', 10).'<a href="assetanddepr.php?w=Lapse">Lapsing Sched</a><i>';
    $sqlmain='SELECT Branch, a.*, ca.ShortAcctID as AssetAccount, concat(e.`Nickname`," ",e.`SurName`) as EncodedBy, FORMAT(`AcqCost`,2) AS AcqCost, FORMAT(`SalvageValue`,2) AS SalvageValue FROM acctg_1assets a
    JOIN `1branches` b ON b.BranchNo=a.BranchNo
LEFT JOIN `1employees` e ON e.IDNo=a.EncodedByNo
JOIN acctg_1chartofaccounts ca on ca.AccountID=a.AssetAccountID 
WHERE a.AssetID='.$txnid;
    $stmt=$link->query($sqlmain); $result=$stmt->fetch();

    $sqlsub='SELECT d.DeprID,`DeprDate`, ShortAcctID as DeprAccount, `Amount`, concat(e.`Nickname`," ",e.`SurName`) as EncodedBy, d.TimeStamp FROM acctg_1assets a
    JOIN acctg_1assetsdepr d on a.AssetID=d.AssetID JOIN acctg_1chartofaccounts ca on ca.AccountID=d.DeprAccountID
    LEFT JOIN `1employees` e ON e.IDNo=d.EncodedByNo WHERE d.AssetID='.$txnid;
    
    $columnnamesmain=array('Branch', 'DateAcquired', 'AssetDesc', 'AssetAccount', 'AcqCost', 'EstLifeInMonths', 'SalvageValue','EncodedBy', 'TimeStamp');
    $columnsub=array('DeprDate', 'DeprAccount', 'Amount', 'EncodedBy', 'TimeStamp');
    
    $main=''; $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%5==0?'</tr><tr>':'');
    }
    
    if((($result['DateAcquired'])>$_SESSION['nb4A']  or intval($result['DateAcquired'])==$currentyr) AND ($result['Posted']==0)){
        $editok=true; $editsub=true;
	$editprocessmain='assetanddepr.php?w=EditMain&TxnID='; $editprocesslabelmain='Enter'; 	
	$delprocessmain='assetanddepr.php?w=DeleteMain&TxnID='; 
	$columnstoeditmain=array('Branch','DateAcquired','AssetDesc', 'AssetAccount', 'AcqCost', 'EstLifeInMonths', 'SalvageValue');
	$colwithlistmain=array('Branch','AssetAccount', 'CreditAccount');
	$listsmain=array('Branch'=>'branchnames','AssetAccount'=>'accounts');
	$liststoshow=array('branchnames');		      
        $columnstoedit=array('DeprDate','DeprAccount','Amount');
	$editsub=true; $editprocess='assetanddepr.php?AssetID='.$txnid.'&w=EditSub&DeprID='; $editprocesslabel='Enter';
        if (allowedToOpen(5251,'1rtc')) { $delprocess='assetanddepr.php?AssetID='.$txnid.'&w=DeleteSub&DeprID=';}
	$colwithlistsub=array('DeprAccount');
        } else {
            $editok=false; $editsub=false; $columnstoedit=array(); $editprocessmain=''; $editprocess =''; $columnstoeditmain=array(); $liststoshow=array();
            }
    
    $main='<table><tr>'.$main.'<tr></table>';
    $main=$main.'<br>'.(isset($_GET['msg'])?'<br><b><font color="maroon">'.strtoupper($_GET['msg']).'</font></b><br>':'');
     $txnsubid='DeprID'; 
    $withsub=true; $coltototal='Amount'; $runtotal=true;
    // info for posting:
    $postvalue='1'; $table='acctg_1assets';  $txnidname='AssetID'; $datefield='DateAcquired';
    //to add records in sub
    $columnnames=array(
                    array('field'=>'DeprDate', 'type'=>'date','size'=>15,'required'=>true,'autofocus'=>true),
                    array('field'=>'DeprAccount', 'type'=>'text','size'=>10, 'required'=>false, 'list'=>'accounts'),
                    array('field'=>'Amount', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'AssetID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
        
    $action='assetanddepr.php?w=AddSub&AssetID='.$txnid;
    $whichotherlist='acctg'; $listcondition='WHERE (AccountID>=940 AND AccountID<=945) '; $otherlist=array('accounts');
    // end add records in sub
    $txnidcol='AssetID'; 
    $addlsubmit='<form action="assetanddepr.php?w=RedoDep&TxnID='.$txnid.'" method=post>'
            . 'OR Depr Account<input type=text name="DeprAccount" size=10 list="accounts">&nbsp &nbsp'
            . '<input type="submit" name="redo" value="Redo Depreciation"></input></form><br><br>';
    include('../backendphp/layout/inputsubeditmain.php');
    break;

case 'RedoDep':
    if (!allowedToOpen(525,'1rtc')) { echo 'No permission'; exit; }
    $txnid=intval($_REQUEST['TxnID']);
    $sql0='DELETE FROM `acctg_1assetsdepr` WHERE AssetID='.$txnid; $stmt0=$link->prepare($sql0); $stmt0->execute();
    $sql='SELECT * FROM `acctg_1assets` WHERE AssetID='.$txnid;
        $stmt=$link->query($sql); $result=$stmt->fetch(); $txnid=$result['AssetID']; 
        $depracctid=getNumber('Account',addslashes($_POST['DeprAccount']));
        //if acquired before the 16th of the month, depreciation starts at the same month of acquisition
	$deprdate=substr($result['DateAcquired'],0,4).'-'.(substr($result['DateAcquired'],5,2)+((substr($result['DateAcquired'],8,2)<16)?0:(substr($result['DateAcquired'],5,2)==12?-11:1))).'-'.substr($result['DateAcquired'],8,2);
	$deprvalue=round(($result['AcqCost']-$result['SalvageValue'])/$result['EstLifeInMonths'],2);
	$counter=0;
	while ($counter<$result['EstLifeInMonths']) {
	    $sql1='INSERT INTO `acctg_1assetsdepr` (`AssetID`,`DeprAccountID`,`DeprDate`,`Amount`, `EncodedByNo`) SELECT '.$txnid.', '.$depracctid.', LAST_DAY(date_add(\''.$deprdate.'\', INTERVAL '.$counter.' MONTH)), '.$deprvalue.','.$_SESSION['(ak0)']; 
	    $stmt=$link->prepare($sql1); $stmt->execute();
	    $counter=$counter+1;
	}
        header('Location:assetanddepr.php?w=AssetandDepr&TxnID='.$txnid);
    break;
    
case 'AddSub':
    if (!allowedToOpen(525,'1rtc')) { echo 'No permission'; exit; }
    $txnid=$_REQUEST['AssetID'];
    $depracctid=getNumber('Account',addslashes($_POST['DeprAccount']));
        $sql1='INSERT INTO `acctg_1assetsdepr` (`AssetID`,`DeprAccountID`,`DeprDate`,`Amount`, `EncodedByNo`) SELECT '.$txnid.', '.$depracctid.', \''.$_POST['DeprDate'].'\', '.$_POST['Amount'].','.$_SESSION['(ak0)'];  
    $stmt=$link->prepare($sql1); $stmt->execute();
    header('Location:assetanddepr.php?w=AssetandDepr&TxnID='.$txnid);
    break;

case 'Lapse':
    if (!allowedToOpen(525,'1rtc')) { echo 'No permission'; exit; }
    include_once('acctglists.inc');
    $title='Lapsing Schedule'; $thisyr=$currentyr; $formdesc='<a href="assetanddepr.php?w=NewAsset">Add New Asset</a>';
    
    renderotherlist('accounts',' WHERE AccountID IN (SELECT AssetAccountID FROM `acctg_1assets`) ');
    ?><form action="assetanddepr.php?w=Lapse" method="post" style="display:inline">
        <input type='submit' name='filter' value='All Assets'></input>&nbsp;&nbsp;
        <input type='submit' name='filter' value='Assets Per Branch'></input>&nbsp;&nbsp;
        <input type='submit' name='filter' value='Assets Per Company'></input>&nbsp;&nbsp;&nbsp;&nbsp;
        Filter by Account<input type='text' name='account' list='accounts'></input>
        <input type='submit' name='filter' value='By Branch'></input>&nbsp;&nbsp;<input type='submit' name='filter' value='By Company'></input>
        &nbsp;&nbsp;<input type='submit' name='filter' value='All Branches'></input>
        </form><br><br>
    <?php
    $filter=!isset($_POST['filter'])?'Assets Per Branch':$_POST['filter'];
   switch ($filter){
            case 'All Assets': $branchcondition='';  break;
            case 'Assets Per Company': 
                $branchcondition='  WHERE a.BranchNo IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo='.$_SESSION['*cnum'].')'; 
                $formdesc=$formdesc.'<br><br>'.$_POST['*cn']; 
                break;
            case 'By Branch': $branchcondition=' WHERE a.BranchNo='.$_SESSION['bnum'].' AND a.AssetAccountID='.getNumber('Account',$_POST['account']);
                $formdesc=$formdesc.'<br><br>'.$_POST['account'].' of '.$_SESSION['@brn']; 
                break;
            case 'By Company': $branchcondition=' WHERE a.BranchNo IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo='.$_SESSION['*cnum'].') AND a.AssetAccountID='.getNumber('Account',$_POST['account']);
                $formdesc=$formdesc.'<br><br>'.$_POST['account'].' of '.$_SESSION['*cname'];
                break;
            case 'All Branches': $branchcondition=' WHERE a.AssetAccountID='.getNumber('Account',$_POST['account']);
                $formdesc=$formdesc.'<br><br>'.$_POST['account'].' - All ';
                break;
            default: //'Assets Per Branch'
            $branchcondition=' WHERE BranchNo='.$_SESSION['bnum']; $formdesc=$formdesc.'<br><br>'.$_SESSION['@brn'];
        }
        
    $monthsarray=array(1,2,3,4,5,6,7,8,9,10,11,12); $sql='';
    $columnnames=array('AssetAccount','AssetDesc', 'AcqCost', 'DateAcquired', 'SalvageValue', 'EstLifeInMonths','PreviousYrs');  
    foreach ($monthsarray as $month){
	$monthname=date('M',strtotime($thisyr.'-'.$month.'-01'));
	$columnnames[]=$monthname;
	$sql=$sql.' FORMAT(SUM(IFNULL(CASE WHEN Year(`DeprDate`)='.$thisyr.' AND MONTH(`DeprDate`)='.$month.' THEN  `Amount` END,0)),2) AS `'.$monthname.'`, ';
    }
    $columnnames[]='TotalDepreciationAsOfThisYr';  $columnnames[]='NetValueThisYr'; 
$sql='SELECT a.`AssetID` as TxnID, ca.ShortAcctID as AssetAccount,`AssetDesc`, FORMAT(`AcqCost`,2) AS `AcqCost`, `DateAcquired`, `SalvageValue`, `EstLifeInMonths`, FORMAT(SUM(IFNULL(CASE WHEN Year(`DeprDate`)<'.$thisyr.' THEN  `Amount` END,0)),2) AS `PreviousYrs`, '.$sql.' FORMAT(SUM(IFNULL(CASE WHEN Year(`DeprDate`)<='.$thisyr.' THEN  `Amount` END,0)),2) AS `TotalDepreciationAsOfThisYr`, FORMAT((`AcqCost`-SUM(IFNULL(CASE WHEN Year(`DeprDate`)<='.$thisyr.' THEN  `Amount` END,0))),2)  AS `NetValueThisYr`
FROM `acctg_1assets` a JOIN `acctg_1chartofaccounts` ca ON a.AssetAccountID=ca.AccountID LEFT JOIN `acctg_1assetsdepr` d ON a.AssetID=d.AssetID '.$branchcondition.' GROUP BY a.AssetID;'; //echo $sql;
$txnidname='TxnID'; 
$editprocess='assetanddepr.php?w=AssetandDepr&TxnID='; $editprocesslabel='Lookup';

$sqlsum='SELECT FORMAT(SUM(`AcqCost`),2) AS `AcqCost` FROM `acctg_1assets` a  '.$branchcondition; //echo $sqlsum;
$stmtsum=$link->query($sqlsum); $resultsum=$stmtsum->fetch();
$coltotals=array('AcqCost');
$totalstext='';
foreach($coltotals as $coltotal){ $totalstext=$totalstext.$coltotal.' '.$resultsum[$coltotal].'<br>';}
$totalstext='Totals<br>'.$totalstext;
include_once('../backendphp/layout/displayastable.php');
    break;
    
case 'DeleteMain':
    if (!allowedToOpen(5251,'1rtc')) { echo 'No permission'; exit; }
    if (allowedToOpen(5251,'1rtc')) { 
    $txnid=intval($_REQUEST['TxnID']);
    $sql='DELETE FROM `acctg_1assetsdepr` WHERE `AssetID`='.$txnid; $stmt=$link->prepare($sql); $stmt->execute();
    $sql='DELETE FROM `acctg_1assets` WHERE `AssetID`='.$txnid; $stmt=$link->prepare($sql); $stmt->execute();
    }
    header("Location:assetanddepr.php?w=Lapse");
    break;


case 'EditMain':
    if (!allowedToOpen(525,'1rtc')) { echo 'No permission'; exit; }
    $txnid=intval($_REQUEST['TxnID']);
    $branchno=getValue($link,'1branches','Branch',addslashes($_POST['Branch']),'BranchNo');
    $acctid=getNumber('Account',addslashes($_POST['AssetAccount']));
    $columnstoedit=array('DateAcquired','AssetDesc', 'EstLifeInMonths');
    $sql=''; 		
	foreach ($columnstoedit as $field) {$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; }
	$sql='UPDATE `acctg_1assets` a SET BranchNo='.$branchno.', AssetAccountID='.$acctid.', '
                . 'AcqCost='.(!is_numeric($_POST['AcqCost'])?str_replace(',', '',$_POST['AcqCost']):$_POST['AcqCost']).','
                . 'SalvageValue='.(!is_numeric($_POST['SalvageValue'])?str_replace(',', '',$_POST['SalvageValue']):$_POST['SalvageValue']).','
                .$sql.' a.EncodedByNo=\''.$_SESSION['(ak0)'].'\', a.TimeStamp=Now() where AssetID='.$txnid . '  and a.`DateAcquired`>\''.$_SESSION['nb4'].'\''; 	
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:assetanddepr.php?w=AssetandDepr&TxnID='.$txnid);
    break;
    
case 'DeleteSub':
    if (!allowedToOpen(525,'1rtc')) { echo 'No permission'; exit; }
    $txnid=$_REQUEST['AssetID']; $txnsubid=$_REQUEST['DeprID'];
	$sql='Delete from `acctg_1assetsdepr` where DeprID='.$txnsubid;$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:assetanddepr.php?w=AssetandDepr&TxnID='.$txnid);
    break;

case 'EditSub':
    if (!allowedToOpen(525,'1rtc')) { echo 'No permission'; exit; }
    $txnid=$_REQUEST['AssetID']; $txnsubid=$_REQUEST['DeprID'];
    $columnstoedit=array('DeprDate','Amount');
    $depracctid=getNumber('Account',addslashes($_POST['DeprAccount']));
    $sql=''; 		
	foreach ($columnstoedit as $field) {$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; }
	$sql='UPDATE `acctg_1assetsdepr` d JOIN `acctg_1assets` a on a.AssetID=d.AssetID SET DeprAccountID='.$depracctid.', '.$sql.' d.EncodedByNo=\''.$_SESSION['(ak0)'].'\', d.TimeStamp=Now() where DeprID='.$txnsubid . '  and a.`DateAcquired`>\''.$_SESSION['nb4'].'\''; 	
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:assetanddepr.php?w=AssetandDepr&TxnID='.$txnid);
    break;
}
  $link=null; $stmt=null;