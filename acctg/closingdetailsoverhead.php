<?php 
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if(!allowedToOpen(6457,'1rtc')) { echo 'No permission'; exit;}
$showbranches=false; include_once('../switchboard/contents.php');
$link=connect_db(''.$currentyr.'_1rtc',1); 

//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="eeffe6";
        $rcolor[1]="FFFFFF";
       
$which=$_GET['w'];
$reportmonth=!isset($_REQUEST['month'])?date('m', strtotime($_SESSION['nb4A']))+1:$_REQUEST['month'];// $reportmonth=$month;

$formdesc='</i>Sharing is based on branch purchases per warehouse<br/><br/><form action="#" method="POST" style="display: inline;">
    Choose month (1-12)<input type="text" name="month" autocomplete="off" size="2" style="text-align: center" value="'. $reportmonth.'">
    <input type="submit" name="submit" value="Lookup Details"></form><i>';

if(in_array($which, array('OHShare','OHShareDistri','ListofExpenseAccts'))) { 
    $sqlaccts='Select AccountID, ShortAcctID AS Account from acctg_1chartofaccounts where (AccountType BETWEEN 150 AND 240) and AccountID not in (900,932,509) ORDER BY AccountType, ShortAcctID';
}


if(in_array($which, array('OHShare','OHShareDistri'))) { 
    
    
    $stmt=$link->query($sqlaccts); $resultacct=$stmt->fetchAll();
$acctid='('; $counter=0;$countof=$stmt->rowCount();
$acctidarray=array();
foreach ($resultacct as $acct){
   $counter++;
   $acctid=$acctid.$acct['AccountID'].($counter==$countof?'':', ');
   $acctidarray[]=$acct['AccountID'];
}
$acctid=$acctid.')'; $sqlalltxns=''; $montharray=$reportmonth;
include('sqlphp/sqlalltxnsnotstaticsched.php');


$sql1='Create temporary table alloh '.$sqlalltxns;
// echo $sql1; 
$stmt=$link->prepare($sql1); $stmt->execute();

$sqlohwh='Create temporary table `ohperwh` (
WH smallint(6) not null, TotalOH double null)
Select oh.BranchNo as WH, round(Sum(Amount),2) as TotalOH FROM alloh oh JOIN `1branches` b ON b.BranchNo=oh.BranchNo where (PseudoBranch<>0)  group by oh.BranchNo;';
$stmt=$link->prepare($sqlohwh); $stmt->execute();
// echo $sqlohwh;  
// Distribute overhead of warehouses to recipient branches, using total transfer amounts from each wh to get ratio
$sqlohwh='Create temporary table totaltxfr (
WH smallint(6) not null, TotalTxfr double null)
SELECT m.BranchNo as WH,round(Sum(`UnitPrice`*`qtySent`),2) as TotalTxfr FROM `invty_2transfer` m INNER JOIN `invty_2transfersub` s ON m.TxnId = s.TxnId 
JOIN `1branches` b ON b.BranchNo=m.BranchNo
WHERE (Month(`DateOUT`))='.$reportmonth.' AND (b.PseudoBranch=2) AND (m.ToBranchNo<>m.BranchNo) GROUP BY Month(`DateOUT`),m.BranchNo;';
$stmt=$link->prepare($sqlohwh); $stmt->execute();
// echo $sqlohwh;  

$sqldel='DROP TABLE IF EXISTS `'.$_SESSION['(ak0)'].'OHShare`';
    $stmt=$link->prepare($sqldel); $stmt->execute(); 

$tablename=($which=='OHShare'?' TEMPORARY TABLE `':' TABLE `'.$_SESSION['(ak0)']);
$sqlohwh='Create '.$tablename.'OHShare` AS SELECT Month(`DateOUT`) AS `Month`, ToBranchNo, CONCAT("From ",m.BranchNo) as Particulars, t.WH, fromb.CompanyNo AS OHCompany, tob.CompanyNo AS OHShareCompany,
round((Sum(`UnitPrice`*`qtySent`)/TotalTxfr)*TotalOH,2) as OHShareAmt, FORMAT((Sum(`UnitPrice`*`qtySent`)/TotalTxfr)*TotalOH,2) as OHShare, 
IF(fromb.CompanyNo=tob.CompanyNo,(SELECT AccountID FROM `banktxns_1maintaining` WHERE RCompanyUse=tob.CompanyNo),(210+tob.CompanyNo)) AS `WarehouseDR`, 900 AS `WarehouseCR`, 900 AS `BranchDR`, IF(fromb.CompanyNo=tob.CompanyNo,(SELECT AccountID FROM `banktxns_1maintaining` WHERE RCompanyUse=tob.CompanyNo),(410+fromb.CompanyNo)) AS `BranchCR`
 FROM `invty_2transfer` m INNER JOIN `invty_2transfersub` s ON m.TxnId = s.TxnId  
join ohperwh oh on oh.WH=m.BranchNo join totaltxfr t on t.WH=m.BranchNo JOIN `1branches` fromb ON fromb.BranchNo=m.BranchNo
JOIN `1branches` tob ON tob.BranchNo=m.ToBranchNo
WHERE (Month(`DateOUT`))='.$reportmonth.' AND (fromb.PseudoBranch=2) AND (m.ToBranchNo<>m.BranchNo) GROUP BY Month(`DateOUT`),m.BranchNo, m.ToBranchNo';
/*
 * REPLACED: IF(fromb.CompanyNo=tob.CompanyNo,(SELECT AccountID FROM `banktxns_1maintaining` WHERE RCompanyUse=tob.CompanyNo),(410+fromb.CompanyNo)) AS `BranchCR` WITH 105
 * REPLACED:  (SELECT AccountID FROM `banktxns_1maintaining` WHERE RCompanyUse=(10+tob.CompanyNo)),(210+tob.CompanyNo)) AS `WarehouseDR` WITH 105
 *  -- REVERTED 2021-02-08
 */
$stmt=$link->prepare($sqlohwh); $stmt->execute();
// echo $sqlohwh;  

  
// Distribute overhead of pseudobranches, using total company-wide sales to get ratio .  Only branches are counted, not warehouses      
$sqlpsb='SELECT oh.WH AS PseudoNo ,pseudob.Branch AS Pseudo, CompanyNo AS PseudoCompanyNo, SUM(TotalOH) AS TotalOH FROM ohperwh oh JOIN `1branches` pseudob ON pseudob.BranchNo=oh.WH WHERE (PseudoBranch=1) GROUP BY oh.WH ';
$stmtpsb=$link->query($sqlpsb); $resultpsb=$stmtpsb->fetchAll(); 
//echo $sqlpsb;

$sql0='CREATE TEMPORARY TABLE totalsales 
SELECT m.BranchNo, CompanyNo AS BranchCompanyNo, ROUND(SUM(`UnitPrice`*`Qty`),2) as BranchSales FROM `invty_2sale` m INNER JOIN `invty_2salesub` s ON m.TxnId = s.TxnId  
 JOIN `1branches` b ON b.BranchNo=m.BranchNo
WHERE (MONTH(`Date`))='.$reportmonth.' AND (PseudoBranch=0) GROUP BY m.BranchNo ORDER BY m.BranchNo;';
//echo $sql0;
$link->query($sql0); 
$sqlsales='SELECT SUM(BranchSales) AS TotalSales, (SELECT ShortAcctID FROM acctg_1chartofaccounts WHERE AccountID=900) AS DebitAccount FROM totalsales ts '; 
$stmtsales=$link->query($sqlsales); $resultsales=$stmtsales->fetch(); $totalsales=$resultsales['TotalSales'];
 

$sql0='SELECT BranchNo AS WH, Branch AS Warehouse FROM 1branches WHERE Pseudobranch=2';
$stmt=$link->query($sql0); $reswh=$stmt->fetchAll();
    
}

switch ($which){
    
case 'OHShare':
    $title='Overhead Sharing - '.date('F',strtotime(''.$currentyr.'-'.$reportmonth.'-1'));    
    $formdesc.=str_repeat('&nbsp;',5).'</i><form action="closingdetailsoverhead.php?w=OHShareDistri&month='. $reportmonth.'"  method="POST" style="display: inline;">'
            . '<input type="submit" name="submit" value="Auto-Encode OH Distribution"></form>'
            . str_repeat('&nbsp;',5).'<form action="closingdetailsoverhead.php?w=ListofExpenseAccts"  method="POST" style="display: inline;">'
            . '<input type="submit" name="submit" value="List of Expense Accounts"></form><br/><br/>';  
    echo '<title>'. $title . '</title><h3>'.$title.'</h3>';
    echo $formdesc;

$width='20%';
$columnnames=array('Branch','OHShare');
echo '<style>
    .drcr td,th { font-size: 14px;}
    .drcr td {background-color: white; padding: 3px;}
    </style>
    <table class="drcr">
    <thead><th>Entity</th><th>Debit</th><th>Credit</th></thead>
    <tr><td>Warehouse/Pseudobranch</td><td>Reconciliation</td><td>OverheadShare</td></tr>
    <tr><td>Branch</td><td>OverheadShare</td><td>Reconciliation</td></tr>
</table><br/><br/><h3>Sharing of Overhead of Warehouses</h3>';
foreach ($reswh as $wh){
    $subtitle=strtoupper($wh['Warehouse']).'<br/><br/>Entries for branches: ';
    
    $sql='SELECT oh.*, Branch, wdr.ShortAcctID AS `Warehouse_DR`, wcr.ShortAcctID AS `Warehouse_CR`, bdr.ShortAcctID AS `Branch_DR`, bcr.ShortAcctID AS `Branch_CR` '
            . ' FROM OHShare oh JOIN `1branches` fromb ON fromb.BranchNo=oh.ToBranchNo JOIN acctg_1chartofaccounts wdr ON wdr.AccountID=oh.WarehouseDR '
            . '  JOIN acctg_1chartofaccounts wcr ON wcr.AccountID=oh.WarehouseCR  JOIN acctg_1chartofaccounts bdr ON bdr.AccountID=oh.BranchDR'
            . '  JOIN acctg_1chartofaccounts bcr ON bcr.AccountID=oh.BranchCR '
            . ' WHERE WH='.$wh['WH'];

    $sqltotal='SELECT WH, FORMAT(SUM(OHShareAmt),2) AS Total FROM OHShare oh WHERE WH='.$wh['WH'];
    $stmt=$link->query($sqltotal); $restotal=$stmt->fetch();
    $totalstext='<br/>Total '.$restotal['Total'];
    echo '<br/><hr>';
    include('../backendphp/layout/displayastableonlynoheaders.php');
    echo '<br/>';
}


$columnnamespsb=array('Branch');
$sqlpsb=''; $sqlval='';
foreach($resultpsb AS $psb){
    $sqlpsb.=', ROUND(((BranchSales/'.($totalsales).')*'.($psb['TotalOH']).'),2) AS `'.$psb['Pseudo'].'Val`, FORMAT(((BranchSales/'.($totalsales).')*'.($psb['TotalOH']).'),2) AS `'.$psb['Pseudo'].'`';
    $sqlpsb.=',IF(b.CompanyNo='.$psb['PseudoCompanyNo'].',(SELECT AccountID FROM `banktxns_1maintaining` WHERE RCompanyUse=b.CompanyNo),(210+b.CompanyNo)) AS `'.$psb['Pseudo'].'DR`';
    $sqlpsb.=',IF(b.CompanyNo='.$psb['PseudoCompanyNo'].',(SELECT ShortAcctID FROM `banktxns_1maintaining` WHERE RCompanyUse=b.CompanyNo),(SELECT ShortAcctID FROM acctg_1chartofaccounts WHERE AccountID=(210+b.CompanyNo))) AS `'.$psb['Pseudo'].'_DR`';
    $sqlpsb.=',IF(b.CompanyNo='.$psb['PseudoCompanyNo'].',(SELECT AccountID FROM `banktxns_1maintaining` WHERE RCompanyUse=b.CompanyNo),(410+'.$psb['PseudoCompanyNo'].')) AS `'.$psb['PseudoNo'].'Branch_CR`';
    $sqlpsb.=',IF(b.CompanyNo='.$psb['PseudoCompanyNo'].',(SELECT ShortAcctID FROM `banktxns_1maintaining` WHERE RCompanyUse=b.CompanyNo),(SELECT ShortAcctID FROM acctg_1chartofaccounts WHERE AccountID=(410+'.$psb['PseudoCompanyNo'].'))) AS `Branch_CR_'.$psb['PseudoNo'].'`';
    $columnnamespsb[]=$psb['Pseudo']; $columnnamespsb[]=$psb['Pseudo'].'_DR'; $columnnamespsb[]='Branch_CR_'.$psb['PseudoNo'];
    $sqlval.=' `'.$psb['Pseudo'].'Val` +';
}

$sqlpsb='CREATE TEMPORARY TABLE pseudoohshare AS SELECT b.Branch '.$sqlpsb.', 900 AS `PseudoCR`, 900 AS `BranchDR`, IF(b.CompanyNo=pseudob.CompanyNo,(SELECT AccountID FROM `banktxns_1maintaining` WHERE RCompanyUse=b.CompanyNo),(410+pseudob.CompanyNo)) AS `BranchCR` FROM totalsales ts JOIN 1branches b ON b.BranchNo=ts.BranchNo JOIN 1branches pseudob ON pseudob.BranchNo=ts.BranchNo   ;'; // echo $sqlpsb;
$link->query($sqlpsb);

$subtitle='Pseudobranches (Pseudobranch CR '.$resultsales['DebitAccount'].' &nbsp; &nbsp; and  &nbsp; &nbsp; Branch DR '.$resultsales['DebitAccount'].')</h4><p>Basis for sharing is sales of branches over company-wide sales.</p><h4>';

//$subtitle='Sharing of Overhead of Pseudobranches </h4><p>Basis for sharing is sales of branches over company-wide sales.</p><h4>';

$sql='SELECT *, ROUND('.$sqlval.'0,2) AS TotalVal , FORMAT('.$sqlval.'0,2) AS Total '
        . ' FROM pseudoohshare ps  '
            . '  JOIN acctg_1chartofaccounts pcr ON pcr.AccountID=ps.PseudoCR  JOIN acctg_1chartofaccounts bdr ON bdr.AccountID=ps.BranchDR'
            . '  JOIN acctg_1chartofaccounts bcr ON bcr.AccountID=ps.BranchCR  ORDER BY Branch;';
//echo $sql; break;
$columnnames=$columnnamespsb;
$columnnames[]='Total'; 
$sqltotal='SELECT FORMAT(SUM(TotalOH),2) AS TotalOH FROM ohperwh oh JOIN `1branches` b ON b.BranchNo=oh.WH WHERE (PseudoBranch=1)';
    $stmt=$link->query($sqltotal); $restotal=$stmt->fetch();
    $totalstext='<br/>Total '.$restotal['TotalOH'];
$width='50%';
include('../backendphp/layout/displayastableonlynoheaders.php');



$subtitle='Pseudobranch CR: '.$resultsales['DebitAccount'];
//$sql='SELECT  FROM pseudoohshare';
$sql='SELECT Branch AS Pseudobranch, ShortAcctID AS DebitAccount, FORMAT(SUM(TotalOH),2) AS TotalOH FROM ohperwh ts JOIN 1branches b ON b.BranchNo=ts.WH JOIN acctg_1chartofaccounts ca ON ca.AccountID=(210+CompanyNo) WHERE (PseudoBranch=1) GROUP BY Branch;';
$columnnames=array('Pseudobranch','TotalOH'); 

echo '<br/><br/>';
$width='20%';
include('../backendphp/layout/displayastableonlynoheaders.php');

 break;   
 
case 'ListofExpenseAccts':
    $title='List of Expense Accounts for OH Sharing';
    $sql=$sqlaccts;
    $columnnames=array('AccountID','Account');
    unset($formdesc);
    include('../backendphp/layout/displayastablenosort.php');
    break;

case 'OHShareDistri':
    $jvnoarr=array();
    // distribute overhead of warehouses
    foreach ($reswh as $wh){
        $jvremarks='DistriOH-'.strtoupper($wh['Warehouse']).str_pad($reportmonth,2,"0",STR_PAD_LEFT);
        
        $sql='Select JVNo from `acctg_2jvmain` where Remarks=\'%'.$jvremarks.'%\'';
	    $stmt=$link->query($sql); $resultadj=$stmt->fetch();
	if ($stmt->rowCount()>0){     
            $jvno=$resultadj['JVNo'];
            goto existingoh;
	} else {   
        include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
        $jvno=lastNum('JVNo','acctg_2jvmain',((date('Y',strtotime($currentyr.'-01-01')))-2000)*10000+1000000)+1;
	$sql='INSERT INTO `acctg_2jvmain` SET  Posted=1, JVDate=Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\'), JVNo=\''.$jvno.'\', Remarks=\''.$jvremarks.'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sql; break;
        $stmt=$link->prepare($sql); $stmt->execute();
	
        
        $sqlohwh='INSERT INTO `acctg_2jvsub` (`Date`,`DebitAccountID`,`CreditAccountID`,`Amount`,`TimeStamp`,`BranchNo`,`FromBudgetOf`,`EncodedByNo`,`JVNo`) ';
        $sqlohwh.=' SELECT Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\') AS `Date`,BranchDR, BranchCR, OHShareAmt, Now(), ToBranchNo, ToBranchNo, '.$_SESSION['(ak0)'].', '.$jvno.' FROM `'.$_SESSION['(ak0)'].'OHShare` WHERE WH='.$wh['WH'];
        $sqlohwh.=' UNION SELECT Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\') AS `Date`,WarehouseCR, WarehouseDR, SUM(OHShareAmt)*-1, Now(), WH,WH, '.$_SESSION['(ak0)'].', '.$jvno.' FROM `'.$_SESSION['(ak0)'].'OHShare` WHERE WH='.$wh['WH'].' GROUP BY WarehouseDR';
        $stmt=$link->prepare($sqlohwh); $stmt->execute();
        
    }
    existingoh:
        $jvnoarr[]=$jvno;
    }
    // end of warehouses
    
    // start of pseudobranches sharing
    foreach($resultpsb AS $psb){
    $jvremarks='DistriOH-'.$psb['Pseudo'].str_pad($reportmonth,2,"0",STR_PAD_LEFT);
    $sql='SELECT JVNo from `acctg_2jvmain` WHERE Remarks LIKE \'%'.$jvremarks.'%\'';
	$stmt=$link->query($sql); $resultadj=$stmt->fetch();
	if ($stmt->rowCount()>0){     
        $jvno=$resultadj['JVNo'];
            goto existingpsboh;
	} else {  
            
            $sqldel='DROP TABLE IF EXISTS `'.$_SESSION['(ak0)'].'pseudoohshare`';
            $stmt=$link->prepare($sqldel); $stmt->execute(); 
            // get values for pseudo
    $sqlpsb='CREATE TABLE `'.$_SESSION['(ak0)'].'pseudoohshare` AS SELECT pseudob.BranchNo AS PseudoNo, ts.BranchNo, b.Branch, ROUND(((BranchSales/'.($totalsales).')*'.($psb['TotalOH']).'),2) AS OHShareAmt, IF(b.CompanyNo='.$psb['PseudoCompanyNo'].',(SELECT AccountID FROM `banktxns_1maintaining` WHERE RCompanyUse=b.CompanyNo),(210+b.CompanyNo)) AS PseudoDR, 900 AS `PseudoCR`, 900 AS `BranchDR`, IF(b.CompanyNo=pseudob.CompanyNo,(SELECT AccountID FROM `banktxns_1maintaining` WHERE RCompanyUse=b.CompanyNo),(410+pseudob.CompanyNo)) AS `BranchCR` FROM totalsales ts JOIN 1branches b ON b.BranchNo=ts.BranchNo JOIN 1branches pseudob ON pseudob.BranchNo='.$psb['PseudoNo'].'   ;'; // echo $sqlpsb;
$link->query($sqlpsb);
            
            $sql='INSERT INTO `acctg_2jvmain` SET  Posted=1, JVDate=Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\'), JVNo=\''.$jvno.'\', Remarks=\''.$jvremarks.'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	// echo $sql; break;
        
        $sqlohwh='INSERT INTO `acctg_2jvsub` (`Date`,`DebitAccountID`,`CreditAccountID`,`Amount`,`TimeStamp`,`BranchNo`,`FromBudgetOf`,`EncodedByNo`,`JVNo`) ';
        $sqlohwh.=' SELECT Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\') AS `Date`,BranchDR, BranchCR, OHShareAmt, Now(), BranchNo, `BranchNo`,'.$_SESSION['(ak0)'].', '.$jvno.' FROM `'.$_SESSION['(ak0)'].'pseudoohshare` WHERE PseudoNo='.$psb['PseudoNo'];
        $sqlohwh.=' UNION SELECT Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\') AS `Date`,PseudoCR, PseudoDR, SUM(OHShareAmt)*-1, Now(), PseudoNo, PseudoNo, '.$_SESSION['(ak0)'].', '.$jvno.' FROM `'.$_SESSION['(ak0)'].'pseudoohshare` WHERE PseudoNo='.$psb['PseudoNo'].' GROUP BY PseudoDR ';  //ECHO $sqlohwh;
        $stmt=$link->prepare($sqlohwh); $stmt->execute();
        
        
        }
        existingpsboh:
            $jvnoarr[]=$jvno;
    }
    // end of pseudobranches
    // 
    // delete all temp tables
    $sqldel='DROP TABLE IF EXISTS `'.$_SESSION['(ak0)'].'OHShare`';
    $stmt=$link->prepare($sqldel); $stmt->execute();   
    $sqldel='DROP TABLE IF EXISTS `'.$_SESSION['(ak0)'].'pseudoohshare`';
    $stmt=$link->prepare($sqldel); $stmt->execute(); 
    // 
    // show summary
       $jvnos=implode(',', $jvnoarr);
       $title='Summary of journal entries';
           $sql1='SELECT adjm.*, CONCAT(Nickname," ",Surname) AS EncodedBy FROM `acctg_2jvmain` adjm JOIN 1employees e ON e.IDNo=adjm.EncodedByNo WHERE JVNo IN ('. $jvnos .')';
           $sql2='SELECT `Date`,Particulars, Branch, cad.ShortAcctID AS DR, cac.ShortAcctID AS CR, Amount AS AmountVal, FORMAT(Amount,2) AS JVAmount FROM `acctg_2jvsub` adjs '
                   . ' JOIN acctg_1chartofaccounts cad ON cad.AccountID=adjs.DebitAccountID '
                   . ' JOIN acctg_1chartofaccounts cac ON cac.AccountID=adjs.CreditAccountID '
                   . ' JOIN 1branches b ON b.BranchNo=adjs.BranchNo ';
           $columnnames1=array('JVNo','Remarks','TimeStamp','EncodedBy');
           $columnnames2=array('Date','Particulars','Branch','DR','CR','JVAmount');
           $coltototal='AmountVal';
           $groupby='JVNo'; $orderby=''; // echo $sql1.'<br/><br/>'.$sql2;
           include('../backendphp/layout/displayastablewithsub.php');
    break;


}
$link=null;