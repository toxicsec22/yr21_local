<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(570,571,572,573,574,575,576,5701,5711,5712,5713,5714,5721,5731,5741,5751,5752,5761);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=true; 
if($_GET['w']!='Print'){
include_once('../switchboard/contents.php');
}else{
	$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
}
include_once('../invty/invlayout/pricelevelcase.php');

 
//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="F5EBCC";
        $rcolor[1]="FFFFFF";
include_once "../generalinfo/lists.inc"; include_once('../backendphp/functions/getnumber.php');

$whichqry=$_GET['w'];
if (in_array($whichqry,array('Print','IncusGiftBudget'))){
	$sql0='CREATE TEMPORARY TABLE IncusBudget AS SELECT m.ClientNo,ClientName,ROUND(SUM(Qty*UnitPrice)/1000,0) as BudgetValue,FORMAT(ROUND(SUM(Qty*UnitPrice)/1000,0),0) as Budget, Branch, 0 AS GiftValue FROM `invty_2sale` m join `invty_2salesub` s on m.TxnID=s.TxnID
	join `1clients` c on c.ClientNo=m.ClientNo JOIN `1branches` b ON b.BranchNo=m.BranchNo '
			. 'WHERE m.ClientNo not in (10000,10001,10004,15001,15002,15003,15004,15005) AND m.Date<=\''.$currentyr.'-11-07\' GROUP BY ClientNo HAVING BudgetValue>=200 ORDER BY BudgetValue DESC';
	$stmt0=$link->prepare($sql0); $stmt0->execute();

	$sql0='UPDATE `IncusBudget` SET GiftValue=(SELECT GiftValue FROM invty_0incusgrouping WHERE BudgetValue BETWEEN RangeMin AND RangeMax);';
	$stmt0=$link->prepare($sql0); $stmt0->execute();
	
	$coltototal='BudgetValue'; $showgrandtotal=true;
	
	if(isset($_POST['OrderBy'])){
		$orderby=''.$_POST['OrderBy'].' '.$_POST['sort'].'';
	}else{
		$orderby='BudgetValue Desc';
	}
	$sql='SELECT * FROM IncusBudget ORDER BY '.$orderby.'';
	// echo $sql;
	
}
switch ($whichqry){

case 'SalesAnalysis':
    if (!allowedToOpen(575,'1rtc')) { echo 'No permission'; exit; }
if (allowedToOpen(5751,'1rtc')) {
   $show=!isset($_POST['show'])?0:$_POST['show'];
?><form style="display:inline" method="post" action="#">
   <input type=hidden name="show" value="<?php echo ($show==0?1:0); ?>">
    <input type="submit" name="submit" value="<?php echo ($show==0?'Show All Branches':'Per Branch'); ?>">
</form>&nbsp &nbsp
<?php
}  else { // grouphead per branch always
   $show=0;
}

//added condition
if(!isset($_POST['submit'])){
	$_SESSION['taon']=1;
}else{
	if(isset($_POST['taon'])){
		if($_POST['taon']==0){	
			$_SESSION['taon']=0;
		}else{
			$_SESSION['taon']=1;
			}
	}
}
	if($_SESSION['taon']==1){
		$taon=$currentyr;
		$dbyr=''.$currentyr.'_1rtc.';
	}else{
		$taon=$lastyr;
		$dbyr=''.$lastyr.'_1rtc.';
	}
	
	echo'<form style="display:inline" method="post" action="#">
			<input type="hidden" name="taon" value="'.($_SESSION['taon']==1?'0':'1').'">
			<input type="submit" name="submit" value="'.($taon==$currentyr?$lastyr:$currentyr).'">
		</form></br>';
//

$condition=($show==1)?' WHERE (ClientNo NOT BETWEEN 15001 AND 15005) AND (ClientNo NOT BETWEEN 1000 AND 9999) ':' WHERE (ClientNo NOT BETWEEN 15001 AND 15005) AND (ClientNo NOT BETWEEN 1000 AND 9999) AND m.BranchNo='.$_SESSION['bnum'];

$txnidname='TxnID';
$title=''.$taon.' Sales Analysis';

$sql='Select date_format(Date,\'%b\') as `Month`, ';
$days=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
foreach ($days as $day){
   $sql=$sql.'Sum(case when Day(Date)='.$day.' then Amount end)/1000 as `'.$day.($day==31?'` from '.$dbyr.'acctg_61unisalereturn m '.$condition.' group by Month(Date) order by Month(Date)':'`, ');
}

$rowtotal='RowTotal';
$rowheading='Month';
$columnnames=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
$columnnamessales=$columnnames;

$sqlrowtotal='drop temporary table if exists rowtotal';
$stmt=$link->prepare($sqlrowtotal);
$stmt->execute();

$sqlrowtotal='create temporary table rowtotal(
RowTotal varchar(100) not null,
RowTotalValue double not null
)
Select date_format(Date, \'%b\') as RowTotal, ROUND(sum(Amount)/1000 ,0) as RowTotalValue
from '.$dbyr.'acctg_61unisalereturn m '.$condition.' group by Month(Date) order by month(Date);';
//echo $sqlrowtotal; break;

$stmt=$link->prepare($sqlrowtotal);
$stmt->execute();


if ($show==1){
   $formdesc='in 000\'s - ALL';
   $showbranches=false; 
   $dailysales='acctg_65dailysales m ';
   $separatesales='SELECT `Date`, SUM(`Amount`) AS `Amount`, `AccountID` FROM '.$dbyr.'`acctg_61unisalereturn` m WHERE ClientNo NOT IN (15001,15002,15003,15004,15005) GROUP BY `Date`, `AccountID`;';
   
} else {
   $formdesc='in 000\'s - '.$_SESSION['@brn'];
   $showbranches=true;
   $sqldailybranch='create temporary table dailysales 
       (`Date` date NOT NULL,
`DailySales` double DEFAULT 0,
`CashLessReturns` double DEFAULT 0,
`ChargeSales` double DEFAULT 0
)
select Date,sum(Amount) as DailySales, SUM(CASE WHEN AccountID IN (100,705) THEN Amount END) AS CashLessReturns, 
SUM(CASE WHEN AccountID IN (200) THEN Amount END) AS ChargeSales from '.$dbyr.'acctg_61unisalereturn m '.$condition.' group by Date';
$stmt=$link->prepare($sqldailybranch); $stmt->execute();
$dailysales='dailysales m ';
$separatesales='SELECT `BranchNo`, `Date`, SUM(`Amount`) AS `Amount`, `AccountID` FROM '.$dbyr.'`acctg_61unisalereturn` m '.$condition.' GROUP BY `Date`, `AccountID`, `BranchNo`;';
}

$sqlcoltotal='Select ROUND(DailySales/1000,0) as ColTotal from ' . $dailysales;
$sqlrowaverage='Select Day(Date),Avg((DailySales)) as DailyAverageMonth from ' . $dailysales.' group by Day(Date)';
$sqlcolaverage='Select avg(DailySales) from ' . $dailysales.$condition;

$stmt=$link->prepare('Select ROUND(Avg(DailySales)/1000,0) as DailyAvgYr from ' . $dailysales);
	$stmt->execute();
	$resultavgyr=$stmt->fetch(PDO::FETCH_ASSOC);
        
$stmt=$link->prepare('Select sum(RowTotalValue)/count(RowTotal) as MonthlyAvg from rowtotal m');
	$stmt->execute();
	$resultavgmonth=$stmt->fetch(PDO::FETCH_ASSOC);

$stmt=$link->prepare('Select ROUND(sum(Amount)/1000 ,0) as GrandTotalValue from '.$dbyr.'acctg_61unisalereturn m '.$condition);
	$stmt->execute();
	$resultgrandtotal=$stmt->fetch(PDO::FETCH_ASSOC);
        
$summary='Daily Average (Year):  '.number_format($resultavgyr['DailyAvgYr'],0).str_repeat('&nbsp; ',8).
         'Monthly Average:  '.number_format($resultavgmonth['MonthlyAvg'],0).str_repeat('&nbsp; ',8).
         'Year Total:  '.number_format($resultgrandtotal['GrandTotalValue'],0).'<br>
         ';
include_once('../backendphp/layout/displayastablevalues.php');

 
$sql=' SELECT "'.($taon==$lastyr?$lastyr-1:$lastyr).'" AS `Year`, FORMAT(SUM(s.Qty*s.UnitPrice)/1000,0) AS `Actual(Qty*Price)`, FORMAT(SUM(s.Qty*(


'.$plcase.'
	

))/1000,0) AS `Qty*LatestMinPrice`, CONCAT((FORMAT((SUM(s.Qty*(


'.$plcase.'
	

))-SUM(s.Qty*s.UnitPrice))/SUM(s.Qty*s.UnitPrice)*100,2)),"%") AS `%PriceIncrease` FROM `'.($taon==$lastyr?$lastyr-1:$lastyr).'_1rtc`.`invty_2sale` m JOIN `'.($taon==$lastyr?$lastyr-1:$lastyr).'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID`'
           . ' JOIN '.$dbyr.'`invty_5latestminprice` lmp ON lmp.ItemCode=s.ItemCode JOIN '.$dbyr.'`1branches` b ON b.BranchNo=m.BranchNo '.$condition. ' AND txntype IN (1,2,5,10) '.
' UNION SELECT "'.($taon==$lastyr?$last2yrs-1:$last2yrs).'" AS `Year`, FORMAT(SUM(s.Qty*s.UnitPrice)/1000,0) AS `Actual(Qty*Price)`, FORMAT(SUM(s.Qty*(

'.$plcase.'
	
))/1000,0) AS `Qty*LatestMinPrice`, CONCAT((FORMAT((SUM(s.Qty*(

'.$plcase.'
	
))-SUM(s.Qty*s.UnitPrice))/SUM(s.Qty*s.UnitPrice)*100,2)),"%") AS `%PriceIncrease` FROM `'.($taon==$lastyr?$last2yrs-1:$last2yrs).'_1rtc`.`invty_2sale` m JOIN `'.($taon==$lastyr?$last2yrs-1:$last2yrs).'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID`'
           . ' JOIN '.$dbyr.'`invty_5latestminprice` lmp ON lmp.ItemCode=s.ItemCode JOIN '.$dbyr.'`1branches` b ON b.BranchNo=m.BranchNo '.$condition. ' AND txntype IN (1,2,5,10)'; 
//$stmtly=$link->query($last2yrssales); $resly=$stmtly->fetch();
$subtitle='<BR><BR>Price Increase vs. Last 2 Years</h4><i>Negative means lower prices in current year.</i><h4>';
$columnnames=array('Year','Actual(Qty*Price)','Qty*LatestMinPrice','%PriceIncrease');
$color1='aec3e5';
include('../backendphp/layout/displayastableonlynoheaders.php');

$columnnames=$columnnamessales; unset($color1);
array_unshift($columnnames,'Month', 'Subtotal');
$hidecount=TRUE;
$subtitle='<BR><BR>Cash Sales Less Returns';
$sql0='CREATE TEMPORARY TABLE `separatesales`
(
`BranchNo` smallint(6) NOT NULL,
`Date`  Date NOT NULL,
`Amount` Double DEFAULT NULL,
`AccountID` smallint (6) NULL
)
 AS '.$separatesales; $stmt0=$link->prepare($sql0); $stmt0->execute();
$sql1='Select '.(($show==1)?'':'BranchNo,').' MONTH(Date) AS Month, FORMAT(SUM(Amount)/1000,0) AS Subtotal, ';
$days=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
foreach ($days as $day){
   $sql1=$sql1.'ROUND(Sum(case when Day(uni.Date)='.$day.' then Amount end)/1000,0) as `'.$day.($day==31?'` from separatesales uni ':'`, ');
}
$sql=$sql1.'WHERE AccountID <>200 GROUP BY Month(uni.Date) ORDER BY Month(uni.Date)';
$sqltotal='SELECT FORMAT(SUM(Amount)/1000,0) AS Subtotal FROM separatesales WHERE AccountID<>200'; 
$stmt0=$link->query($sqltotal); $res0=$stmt0->fetch();
$totalstext='Subtotal '.$res0['Subtotal'];
//echo $sql0.'<br><br>'.$sql;
include('../backendphp/layout/displayastableonlynoheaders.php');
$subtitle='<BR><BR>Charge Sales';
$sql=$sql1.'WHERE AccountID IN (200) GROUP BY Month(uni.Date) ORDER BY Month(uni.Date)';
$sqltotal='SELECT FORMAT(SUM(Amount)/1000,0) AS Subtotal FROM separatesales WHERE AccountID IN (200)'; 
$stmt0=$link->query($sqltotal); $res0=$stmt0->fetch();
$totalstext='Subtotal '.$res0['Subtotal'];
//echo $sql;
include('../backendphp/layout/displayastableonlynoheaders.php');
   break;

case 'Incus':
    if (!allowedToOpen(570,'1rtc')) {   echo 'No permission'; exit;}
if (allowedToOpen(5701,'1rtc')) {
   $show=!isset($_POST['show'])?0:$_POST['show'];   
?><form style="display:inline" method="post" action="#">
   <input type=hidden name="show" value="<?php echo ($show==0?1:0); ?>">
    <input type="submit" name="submit" value="<?php echo ($show==0?'Show All Branches':'Per Branch'); ?>">
</form>&nbsp &nbsp
<?php
}  else { $show=0;}
if ($show==1){
   $formdesc='All Clients';
   $showbranches=false;
} else {
   $formdesc=$_SESSION['@brn'];
   $showbranches=true;
}

$monthly=!isset($_POST['monthly'])?0:$_POST['monthly'];
?>
<form style="display:inline" method="post" action="#">
   <input type=hidden name="monthly" value="<?php echo ($monthly==0?1:0); ?>">
    <input type="submit" name="submit" value="<?php echo ($monthly==0?'Show Monthly':'Show Whole Year'); ?>">
</form>&nbsp &nbsp
<?php
//added condition
if(!isset($_POST['submit'])){
	$_SESSION['taon']=1;
}else{
	if(isset($_POST['taon'])){
		if($_POST['taon']==0){	
			$_SESSION['taon']=0;
		}else{
			$_SESSION['taon']=1;
			}
	}
}
	if($_SESSION['taon']==1){
		$taon=$currentyr;
		$dbyr=''.$currentyr.'_1rtc.';
	}else{
		$taon=$lastyr;
		$dbyr=''.$lastyr.'_1rtc.';
	}
	
	echo'<form style="display:inline" method="post" action="#">
			<input type="hidden" name="taon" value="'.($_SESSION['taon']==1?'0':'1').'">
			<input type="submit" name="submit" value="'.($taon==$currentyr?$lastyr:$currentyr).'">
		</form></br>';
//
$condition=($show==1)?'':' AND (m.BranchNo='.$_SESSION['bnum'].' OR m.BranchNo=(SELECT MovedBranch FROM '.$dbyr.'`1branches` WHERE BranchNo='.$_SESSION['bnum'].'))';
$title=''.$taon.' Incus';
if ($monthly==0){
$sql='SELECT m.ClientNo,ClientName,sum(Qty*UnitPrice) as TotalYrSalesValue,format(sum(Qty*UnitPrice),0) as TotalYrSales FROM '.$dbyr.'`invty_2sale` m join '.$dbyr.'`invty_2salesub` s on m.TxnID=s.TxnID
join '.$dbyr.'`1clients` c on c.ClientNo=m.ClientNo JOIN '.$dbyr.'`1branches` b ON b.BranchNo=m.BranchNo '
        . 'where m.ClientNo not in (15001,15002,15003,15004,15005) '.$condition.'
group by ClientNo order by Sum(Qty*UnitPrice) desc';
// echo $sql; exit();
$columnnames=array('ClientNo','ClientName','TotalYrSales');
$coltototal='TotalYrSalesValue'; $showgrandtotal=true;
} else {


$sql='SELECT m.ClientNo, ClientName, ';
$months=array(1,2,3,4,5,6,7,8,9,10,11,12);
foreach ($months as $month){
   $sql=$sql.'format(Sum(case when Month(Date)='.$month.' then (Qty*UnitPrice) end),0) as `'.$month.($month==12?'`, format(Sum(Qty*UnitPrice),0) as YrTotal,Sum(Qty*UnitPrice) as YrTotalValue from '.$dbyr.'`invty_2sale` m join '.$dbyr.'`invty_2salesub` s on m.TxnID=s.TxnID
join '.$dbyr.'`1clients` c on c.ClientNo=m.ClientNo JOIN '.$dbyr.'`1branches` b ON b.BranchNo=m.BranchNo '
           . 'where m.ClientNo not in (15001,15002,15003,15004,15005) '.$condition.' group by m.ClientNo order by Sum(Qty*UnitPrice) desc':'`, ');
}

$columnnames=array('ClientNo','ClientName','YrTotal',1,2,3,4,5,6,7,8,9,10,11,12); 
$coltototal='YrTotalValue'; $showgrandtotal=true;
} // echo $sql; break;
include_once('../backendphp/layout/displayastable.php');
   break;
   
   case'Print':
   $title='Incus Gift Budget';
   $formdesc='</i></br>Date: '.date('Y-m-d').'</br>';
   $hidecontents=1;
   $columnnames=array('ClientNo','ClientName','Budget','GiftValue','Branch'); $columnsub=array_diff($columnnames,array('Budget')); $columnsub[]='BudgetValue'; 
   include('../backendphp/layout/displayastablenosort.php');
   
   break;
   
case 'IncusGiftBudget':
    if (!allowedToOpen(571,'1rtc')) {   echo 'No permission'; exit;} 
   $title='Incus Gift Budget';
   $formdesc='Clients above P200 budget. All paid and unpaid sales counted up to '.$currentyr.' Nov 07 only.';
   $showbranches=false;

if (allowedToOpen(5711,'1rtc')) {
    $columnnames=array('ClientNo','ClientName','Budget','GiftValue','Branch'); $columnsub=array_diff($columnnames,array('Budget')); $columnsub[]='BudgetValue'; 
} else { $columnnames=array('ClientNo','ClientName','GiftValue','Branch'); $columnsub=array_diff($columnnames,array('Budget')); $columnsub[]='GiftValue'; }

echo '<div style="width:130%;"><div style="float:left; display: inline;">';
if (allowedToOpen(5712,'1rtc')) { $formdesc=$formdesc.'</i><br><br>
        <form method="post" action="lookuptools.php?w=AutoEncodeXmasCards" enctype="multipart/form-data"><input type="submit" name="autoencode" value="Auto Encode Xmas Cards">
        <input type="hidden" name="encode" value=""></input></form><i>'; }
if (allowedToOpen(5713,'1rtc')){
$formdesc=$formdesc.'</i></br></br>
        <form style="display:inline;" method="post" action="lookuptools.php?w=Print&print=1">
		Order By: <select name="OrderBy">
			<option value="ClientNo">ClientNo</option>
			<option value="ClientName">ClientName</option>
			<option value="Budget">Budget</option>
			<option value="GiftValue">GiftValue</option>
			<option value="Branch">Branch</option>
		</select>
		
		<select name="sort">
			<option value="Asc">Ascending</option>
			<option value="Desc">Descending</option>
		</select>
			<input type="submit" name="submit" value="Print">
		</form></br>';

	include('../backendphp/layout/displayastable.php');}
echo '</div>'; //left
$subtitle='<br><br><br><h4>Summary of Gifts</h4>';
$title='';
unset($columnsub,$sortfield,$showgrandtotal,$coltototal,$formdesc);
$columnnames=array('GroupID','RangeMin','RangeMax','Qty','GiftValue','Amount');  
$sql='SELECT `GroupID`, COUNT(ClientNo) AS Qty, ib.GiftValue, ROUND(COUNT(ClientNo)*ib.GiftValue,0) AS `AmountValue`, FORMAT(COUNT(ClientNo)*ib.GiftValue,0) AS `Amount`,RangeMin,RangeMax FROM IncusBudget ib JOIN invty_0incusgrouping ig ON ib.GiftValue=ig.GiftValue GROUP BY `GroupID`;';
$sql0='SELECT COUNT(ClientNo) AS TotalQty, SUM(BudgetValue) AS TotalBudget, SUM(GiftValue) AS TotalGifts FROM IncusBudget;';
$stmt0=$link->query($sql0); $res0=$stmt0->fetch();
$totalstext='Total Qty: '.number_format($res0['TotalQty'],0).str_repeat('&nbsp;',5).'Total Gift Value: '.number_format($res0['TotalGifts'],0); 
if (allowedToOpen(5714,'1rtc')) { $totalstext=$totalstext.str_repeat('&nbsp;',5).'Total Budget : '.number_format($res0['TotalBudget'],0); }
$hidecount=true; $width='30%';
    echo '<div style="display:inline; margin-left: 50px;"';
    include('../backendphp/layout/displayastablenosort.php');

$subtitle='<br><br>Summary Per Branch'; unset($totalstext);
$columnnames=array('Branch','QtyPerBranch','GiftValue','Amount'); 
$sql='SELECT `Branch`, `GroupID`, COUNT(ClientNo) AS QtyPerBranch, ib.GiftValue, FORMAT(COUNT(ClientNo)*ib.GiftValue,0) AS `Amount` FROM IncusBudget ib JOIN invty_0incusgrouping ig ON ib.GiftValue=ig.GiftValue GROUP BY `GroupID`, `Branch` ORDER BY `Branch`;';
    include('../backendphp/layout/displayastable.php');
$subtitle='<br><br>Values Per Branch';
if (allowedToOpen(5714, '1rtc')) { $columnnames=array('Branch','TotalQty','TotalBudget','TotalGifts'); }
else {$columnnames=array('Branch','TotalQty','TotalGifts'); }
$sql='SELECT `Branch`, COUNT(ClientNo) AS TotalQty, FORMAT(SUM(BudgetValue),0) AS TotalBudget, FORMAT(SUM(GiftValue),0) AS TotalGifts FROM IncusBudget GROUP BY `Branch`;';
include('../backendphp/layout/displayastable.php');
    echo '</div>'; //right

   break;
   
case 'AutoEncodeXmasCards':
    if (!allowedToOpen(5712,'1rtc')) {   echo 'No permission'; exit;} 
   
$sql0='CREATE TEMPORARY TABLE encodexmascards AS SELECT m.ClientNo,ROUND(SUM(Qty*UnitPrice)/1000,0) AS BudgetValue, (SELECT GroupID FROM invty_0incusgrouping WHERE ROUND(SUM(Qty*UnitPrice)/1000,0) BETWEEN RangeMin AND RangeMax GROUP BY m.ClientNo)  AS GroupID  FROM `invty_2sale` m join `invty_2salesub` s on m.TxnID=s.TxnID JOIN `1branches` b ON b.BranchNo=m.BranchNo '
        . 'WHERE m.ClientNo not in (10000,10001,10004,15001,15002,15003,15004,15005) AND m.Date<=\''.$currentyr.'-11-07\' GROUP BY ClientNo HAVING BudgetValue>=200 ORDER BY Branch';
$stmt0=$link->prepare($sql0); $stmt0->execute();
$sql0='INSERT INTO `acctg_xmascards` (`ClientNo`,`GroupID`) SELECT `ClientNo`,`GroupID` FROM `encodexmascards`';
$stmt0=$link->prepare($sql0); $stmt0->execute();
header("Location:".$_SERVER['HTTP_REFERER']);
break;
   
case 'NewBranchSales':
    if (!allowedToOpen(572,'1rtc')) {   echo 'No permission'; exit;} 
    $title='New Branch Sales in the Past 3 Years';
    $formdesc='Moved branches are counted with the original branch.<br><br>';
    
    $sql0='CREATE TEMPORARY TABLE `branchnumbers` AS SELECT b.BranchNo, IF(b.BranchNo IN (SELECT `MovedBranch` FROM `1branches`),(SELECT BranchNo FROM `1branches` WHERE MovedBranch=b.BranchNo AND BranchNo=b.BranchNo),b.BranchNo) AS BranchNum,
YEAR(IF(`MovedBranch`=-1,`Anniversary`,(SELECT `Anniversary` FROM `1branches` WHERE BranchNo=b.MovedBranch))) AS BranchAnnivYr FROM `1branches` b '.(allowedToOpen(5721,'1rtc')? ' JOIN `attend_1branchgroups` bg ON b.BranchNo=bg.BranchNo WHERE bg.SAM='.$_SESSION['(ak0)']:' WHERE b.BranchNo<>999 ');
// echo $sql0.'<br><br>';
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql0='CREATE TEMPORARY TABLE `newbranchsales` AS SELECT b.BranchNo, BranchNum,`BranchAnnivYr`,"'.$currentyr.'" AS `ForYear`, SUM(Amount) AS `YrTotal`, SUM(CASE WHEN `BranchAnnivYr`<='.$last3yrs.' THEN Amount END) AS ExistingBranches, SUM(CASE WHEN `BranchAnnivYr`='.$last2yrs.' THEN Amount END) AS `'.$last2yrs.'NewBranches`, SUM(CASE WHEN `BranchAnnivYr`='.$lastyr.' THEN Amount END) AS `'.$lastyr.'NewBranches`, SUM(CASE WHEN `BranchAnnivYr`='.$currentyr.' THEN Amount END) AS `'.$currentyr.'NewBranches` FROM `acctg_61unisalereturn` s JOIN `branchnumbers` b ON b.`BranchNo`=s.`BranchNo` GROUP BY b.BranchNo ;';
    $stmt0=$link->prepare($sql0); $stmt0->execute();

    $sql0='INSERT INTO `newbranchsales` SELECT b.BranchNo,BranchNum,`BranchAnnivYr`,"'.$lastyr.'" AS `ForYear`, SUM(Amount) AS `YrTotal`, SUM(CASE WHEN `BranchAnnivYr`<='.$last3yrs.' THEN Amount END) AS ExistingBranches, SUM(CASE WHEN `BranchAnnivYr`='.$last2yrs.' THEN Amount END) AS `'.$last2yrs.'NewBranches`, SUM(CASE WHEN `BranchAnnivYr`='.$lastyr.' THEN Amount END) AS `'.$lastyr.'NewBranches`, SUM(CASE WHEN `BranchAnnivYr`='.$currentyr.' THEN Amount END) AS `'.$currentyr.'NewBranches` FROM `'.$lastyr.'_1rtc`.`acctg_61unisalereturn` s JOIN `branchnumbers` b ON b.`BranchNo`=s.`BranchNo` GROUP BY b.BranchNo ;';
    $stmt0=$link->prepare($sql0); $stmt0->execute();

    $sql0='INSERT INTO `newbranchsales` SELECT b.BranchNo,BranchNum,`BranchAnnivYr`,"'.$last2yrs.'" AS `ForYear`, SUM(Amount) AS `YrTotal`, SUM(CASE WHEN `BranchAnnivYr`<='.$last3yrs.' THEN Amount END) AS ExistingBranches, SUM(CASE WHEN `BranchAnnivYr`='.$last2yrs.' THEN Amount END) AS `'.$last2yrs.'NewBranches`, SUM(CASE WHEN `BranchAnnivYr`='.$lastyr.' THEN Amount END) AS `'.$lastyr.'NewBranches`, SUM(CASE WHEN `BranchAnnivYr`='.$currentyr.' THEN Amount END) AS `'.$currentyr.'NewBranches` FROM `'.$last2yrs.'_1rtc`.`acctg_61unisalereturn` s JOIN `branchnumbers` b ON b.`BranchNo`=s.`BranchNo` GROUP BY b.BranchNo;';
    $stmt0=$link->prepare($sql0); $stmt0->execute();

    $sql='SELECT ForYear, FORMAT(SUM(YrTotal),0) AS YrTotal, FORMAT(SUM(ExistingBranches),0) AS ExistingBranches, FORMAT(SUM('.$last2yrs.'NewBranches),0) AS '.$last2yrs.'NewBranches, FORMAT(SUM('.$lastyr.'NewBranches),0) AS '.$lastyr.'NewBranches, FORMAT(SUM('.$currentyr.'NewBranches),0) AS '.$currentyr.'NewBranches FROM `newbranchsales` GROUP BY ForYear ORDER BY ForYear DESC;';
    $columnnames=array('ForYear','YrTotal',$currentyr.'NewBranches',$lastyr.'NewBranches',$last2yrs.'NewBranches','ExistingBranches');
    $hidecount=true;
    $width="60%";
    include_once('../backendphp/layout/displayastable.php');
    
//    $sql1='SELECT SUM(YrTotal) AS YrTotal FROM `newbranchsales` WHERE ForYear='.$currentyr; $stmt1=$link->query($sql1); $res1=$stmt1->fetch();
//    $totalvalue=$res1['YrTotal'];
//    
    $sql='SELECT Branch, FORMAT(SUM(CASE WHEN ForYear='.$currentyr.' THEN YrTotal END),0) AS `'.$currentyr.'`, '
            . 'FORMAT(SUM(CASE WHEN ForYear='.$lastyr.' THEN YrTotal END),0) AS `'.$lastyr.'`, '
            . 'FORMAT(SUM(CASE WHEN ForYear='.$last2yrs.' THEN YrTotal END),0) AS `'.$last2yrs.'`'
            . ' FROM `newbranchsales` n '
            . ' JOIN `1branches` b ON b.BranchNo=n.BranchNum WHERE BranchAnnivYr>='.$last2yrs.' GROUP BY BranchNum ORDER BY `BranchAnnivYr` DESC, `Branch`;'; 
    $columnnames=array('Branch',$currentyr,$lastyr,$last2yrs);
    unset($hidecount); $subtitle='<br><br><h4>3-yr Sales of New Branches</h4>';
    echo '<div style="width:130%;"><div style="float:left; display: inline;"';
    include('../backendphp/layout/displayastableonlynoheaders.php');
    echo '</div>'; //left
    echo '<div style="display:inline; margin-left: 50px;"';
    $sql='SELECT Branch, CONCAT(FORMAT((SUM(CASE WHEN ForYear='.$currentyr.' THEN YrTotal END)-SUM(CASE WHEN ForYear='.$lastyr.' THEN YrTotal END))/SUM(CASE WHEN ForYear='.$lastyr.' THEN YrTotal END)*100,2),"%") AS `'.$currentyr.'`, CONCAT(FORMAT((SUM(CASE WHEN ForYear='.$lastyr.' THEN YrTotal END)-SUM(CASE WHEN ForYear='.$last2yrs.' THEN YrTotal END))/SUM(CASE WHEN ForYear='.$last2yrs.' THEN YrTotal END)*100,2),"%") AS `'.$lastyr.'`'
            . ' FROM `newbranchsales` n '
            . ' JOIN `1branches` b ON b.BranchNo=n.BranchNum WHERE BranchAnnivYr>='.$last2yrs.' GROUP BY BranchNum ORDER BY `BranchAnnivYr` DESC, `Branch`;'; 
    $columnnames=array('Branch',$currentyr,$lastyr); $color1='cce6ff'; $subtitle='<br><br><br>';
    $width="30%";
    include('../backendphp/layout/displayastableonlynoheaders.php');
    echo '</div>'; //right
    echo '</div>'; //wrap
    break;
   
case 'SalesHistory':
    if (!allowedToOpen(576,'1rtc')) {   echo 'No permission'; exit;} 
    $title='Sales History';
    
    $sql0='CREATE TEMPORARY TABLE `branchnumbers` AS SELECT b.BranchNo, IF(b.BranchNo IN (SELECT `MovedBranch` FROM `1branches` WHERE b.BranchNo>0),(SELECT BranchNo FROM `1branches` WHERE MovedBranch=b.BranchNo),b.BranchNo) AS BranchNum,
YEAR(IF(`MovedBranch`=-1,`Anniversary`,(SELECT `Anniversary` FROM `1branches` WHERE BranchNo=b.MovedBranch))) AS BranchAnnivYr, ProvincialBranch FROM `1branches` b '.(allowedToOpen(5761,'1rtc')? ' JOIN `attend_1branchgroups` bg ON b.BranchNo=bg.BranchNo WHERE bg.SAM='.$_SESSION['(ak0)']:' WHERE b.BranchNo<>999 AND b.BranchNo>=0');

// echo $sql0;
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql0='CREATE TEMPORARY TABLE `PriceIncrease` AS SELECT lcurr.ItemCode, IFNULL((lcurr.PriceLevel3-llast.PriceLevel3),0) AS MPDiff, IFNULL((lcurr.PriceLevel4-llast.PriceLevel4),0) AS PMPDiff FROM `invty_5latestminprice` `lcurr` JOIN `'.$lastyr.'_1rtc`.`invty_5latestminprice` `llast` ON lcurr.ItemCode=llast.ItemCode;';
    $stmt0=$link->prepare($sql0); $stmt0->execute(); // echo $sql0;
    
    $condition=' ';
    $sql0='CREATE TEMPORARY TABLE `SalesHistory` AS
SELECT  b.BranchNo,b.BranchNum, (SELECT TRUNCATE(SUM(s.Qty*s.UnitPrice),0) FROM `'.$last2yrs.'_1rtc`.`invty_2sale` m JOIN `'.$last2yrs.'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID` WHERE txntype IN (1,2,5,10) AND (ClientNo NOT BETWEEN 1000 AND 9999) AND (ClientNo NOT BETWEEN 15001 AND 15005) AND BranchNo=b.BranchNo) AS `'.$last2yrs.'`, 
(SELECT TRUNCATE(SUM(s.Qty*s.UnitPrice),0) FROM `'.$lastyr.'_1rtc`.`invty_2sale` m JOIN `'.$lastyr.'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID` WHERE txntype IN (1,2,5,10) AND (ClientNo NOT BETWEEN 1000 AND 9999) AND (ClientNo NOT BETWEEN 15001 AND 15005) AND BranchNo=b.BranchNo) AS `'.$lastyr.'`,
(SELECT TRUNCATE(SUM(s.Qty*s.UnitPrice),0) FROM `invty_2sale` m JOIN `invty_2salesub` s ON m.`TxnID`=s.`TxnID` WHERE txntype IN (1,2,5,10) AND (ClientNo NOT BETWEEN 1000 AND 9999) AND (ClientNo NOT BETWEEN 15001 AND 15005) AND BranchNo=b.BranchNo) AS `'.$currentyr.'`, 0 AS `PriceDriver`, 0 AS `VolumeDriver`  
FROM `branchnumbers` b ;'; // echo $sql0;
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql0='UPDATE `SalesHistory` p SET `PriceDriver`=(SELECT TRUNCATE(SUM(Qty*(PMPDiff)),0) FROM `PriceIncrease` pi JOIN `invty_2salesub` s ON pi.ItemCode=s.ItemCode JOIN `invty_2sale` m ON m.`TxnID`=s.`TxnID`  
JOIN `branchnumbers` b ON b.BranchNo=m.BranchNo AND b.ProvincialBranch=1
WHERE txntype IN (1,2,5,10) AND (ClientNo NOT BETWEEN 1000 AND 9999) AND (ClientNo NOT BETWEEN 15001 AND 15005) AND BranchAnnivYr<>'.$currentyr.' AND p.BranchNo=b.BranchNo);';
    // echo $sql0;
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql0='UPDATE `SalesHistory` p SET `PriceDriver`=(SELECT TRUNCATE(SUM(Qty*(MPDiff)),0) FROM `PriceIncrease` pi JOIN `invty_2salesub` s ON pi.ItemCode=s.ItemCode JOIN `invty_2sale` m ON m.`TxnID`=s.`TxnID`  
JOIN `branchnumbers` b ON b.BranchNo=m.BranchNo AND b.ProvincialBranch=0
WHERE txntype IN (1,2,5,10) AND (ClientNo NOT BETWEEN 1000 AND 9999) AND (ClientNo NOT BETWEEN 15001 AND 15005) AND BranchAnnivYr<>'.$currentyr.' AND p.BranchNo=b.BranchNo);';
    // echo $sql0;
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql0='UPDATE `SalesHistory` SET `VolumeDriver`=`'.$currentyr.'`-`PriceDriver`;'; // echo $sql0;
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql='SELECT `Branch`, FORMAT(SUM(`'.$last2yrs.'`),0) AS `'.$last2yrs.'`, FORMAT(SUM(`'.$lastyr.'`),0) AS `'.$lastyr.'`, FORMAT(SUM(`'.$currentyr.'`),0) AS `'.$currentyr.'`,FORMAT(SUM(`PriceDriver`),0) AS `PriceDriver`,FORMAT(SUM(`VolumeDriver`),0) AS `VolumeDriver` FROM `SalesHistory` s JOIN `1branches` b ON b.BranchNo=s.BranchNum WHERE b.Pseudobranch<>1 GROUP BY `BranchNum` ORDER BY Branch'; //echo $sql;
    $columnnames=array('Branch',$last2yrs,$lastyr,$currentyr,'PriceDriver','VolumeDriver'); $hidecount=true;
    include('../backendphp/layout/displayastable.php');
    $sql='SELECT "Totals" AS Branch, FORMAT(SUM(`'.$last2yrs.'`),0) AS `'.$last2yrs.'`, FORMAT(SUM(`'.$lastyr.'`),0) AS `'.$lastyr.'`, FORMAT(SUM(`'.$currentyr.'`),0) AS `'.$currentyr.'`,FORMAT(SUM(`PriceDriver`),0) AS `PriceDriver`,FORMAT(SUM(`VolumeDriver`),0) AS `VolumeDriver` FROM `SalesHistory` s ';
    include('../backendphp/layout/displayastableonlynoheaders.php');
    
case 'RaiseClients':
    if (!allowedToOpen(573,'1rtc')) {   echo 'No permission'; exit;} 
    $title='"Raise" Candidates'; 
    if (allowedToOpen(5731,'1rtc')) { $condition='';} 
    else { $condition=' AND m.BranchNo IN (SELECT BranchNo FROM `attend_1branchgroups` WHERE `TeamLeader`='.$_SESSION['(ak0)'].' OR `SAM`='.$_SESSION['(ak0)'].')';}
    if(!isset($_POST['fromdate'])){ $fromdate=date('Y-m-d',strtotime('01-01-'.$currentyr)); $todate=date('Y-m-t'); }
    else { $fromdate=$_REQUEST['fromdate']; $todate=$_REQUEST['todate']; }
    $formdesc='Sales on same period last 2 years:  From '.$fromdate.' To '.$todate.'<br><br>';
    ?>
<form method="post" action="lookuptools.php?w=RaiseClients" enctype="multipart/form-data">
    From &nbsp<input type='date' name='fromdate' value="<?php echo $fromdate; ?>"></input>&nbsp &nbsp &nbsp 
    To &nbsp<input type='date' name='todate' value="<?php echo $todate; ?>"></input>&nbsp &nbsp &nbsp &nbsp
    <input type="submit" name="lookup" value="Lookup"></form><br><br>
<?php
   // if(!isset($_POST['fromdate'])){ goto noform;}
    $lastyrfromdate=date('Y-m-d',strtotime($fromdate.' -1 year')); $lastyrtodate=date('Y-m-d',strtotime($todate.' -1 year'));
    $last2yrsfromdate=date('Y-m-d',strtotime($fromdate.' -2 year')); $last2yrstodate=date('Y-m-d',strtotime($todate.' -2 year'));

    $sql0='DROP TEMPORARY TABLE IF EXISTS `SalesPerClient`;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql0='CREATE TEMPORARY TABLE `SalesPerClient` AS
        SELECT ClientNo, 0 AS `Yr'.$last2yrs.'`, ROUND(SUM(Qty*UnitPrice),0) AS `Yr'.$lastyr.'`, 0 AS `Yr'.$currentyr.'`
        FROM `'.$lastyr.'_1rtc`.`invty_2sale` m JOIN `'.$lastyr.'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID` 
        WHERE m.Date BETWEEN \''.$lastyrfromdate.'\' AND \''.$lastyrtodate.'\' AND `txntype` IN (1,2,5) AND ClientNo NOT IN (10000,10001,10004,15001,15002,15003,15004,15005) '.$condition.' GROUP BY `ClientNo` HAVING `Yr'.$lastyr.'`>10000';  
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql0='UPDATE `SalesPerClient` sc SET `Yr'.$currentyr.'`=(SELECT ROUND(IFNULL(SUM(Qty*UnitPrice),0),0)
        FROM `invty_2sale` m JOIN `invty_2salesub` s ON m.`TxnID`=s.`TxnID` 
        WHERE m.Date BETWEEN \''.$fromdate.'\' AND \''.$todate.'\' AND `txntype` IN (1,2,5) AND m.ClientNo=sc.ClientNo)';  
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql0='UPDATE `SalesPerClient` sc SET `Yr'.$last2yrs.'`=(SELECT ROUND(IFNULL(SUM(Qty*UnitPrice),0),0)
        FROM `'.$last2yrs.'_1rtc`.`invty_2sale` m JOIN `'.$last2yrs.'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID` 
        WHERE m.Date BETWEEN \''.$last2yrsfromdate.'\' AND \''.$last2yrstodate.'\' AND `txntype` IN (1,2,5) AND m.ClientNo=sc.ClientNo)';  
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    
$sql='SELECT s.ClientNo, ClientName, FORMAT(`Yr'.$last2yrs.'`,0) AS `'.$last2yrs.'`, FORMAT(`Yr'.$lastyr.'`,0) AS `'.$lastyr.'`, FORMAT(`Yr'.$currentyr.'`,0) AS `'.$currentyr.'`, CONCAT(FORMAT((`Yr'.$currentyr.'`/`Yr'.$lastyr.'`)*100,2),"%") AS `%ToLastYr` FROM `SalesPerClient` s JOIN `1clients` c on c.ClientNo=s.ClientNo '
        . ' WHERE `Yr'.$lastyr.'`>=`Yr'.$currentyr.'`'
        . ' ORDER BY `%ToLastYr` ASC,`Yr'.$lastyr.'` DESC';

$columnnames=array('ClientNo','ClientName',$last2yrs,$lastyr,$currentyr,'%ToLastYr');

include_once('../backendphp/layout/displayastable.php');
   break;
   
case 'RecoverClients':
    if (!allowedToOpen(574,'1rtc')) {   echo 'No permission'; exit;} 
    $title='Clients To "Recover"'; 
    if (allowedToOpen(5741,'1rtc')) { $condition='';} 
    else { $condition=' AND m.BranchNo IN (SELECT BranchNo FROM `attend_1branchgroups` WHERE `TeamLeader`='.$_SESSION['(ak0)'].' OR `SAM`='.$_SESSION['(ak0)'].')';}
    $formdesc='List of clients with ff conditions:<br><div style="margin: 15px;"><ol><li> No transaction in the past 4 months, including current month</li>'
            . '<li> Purchased at least 3 times in the past 12 months</li>'
            . '<li> Total purchase >10,000 in the past 12 months</li></ol><br>';
    
    $show=!isset($_POST['show'])?0:$_POST['show'];
?><form style="display:inline" method="post" action="#">
   <input type=hidden name="show" value="<?php echo ($show==0?1:0); ?>">
    <input type="submit" name="submit" value="<?php echo ($show==0?'Show All Branches':'Per Branch'); ?>">
</form><br><br>
<?php
    
    $condition.=($show==1)?'':' AND m.BranchNo='.$_SESSION['bnum'];
    $currmonth=date('m'); $checkmonth=$currmonth-4;
    
    $sql0='DROP TEMPORARY TABLE IF EXISTS `12month`;'; $stmt0=$link->prepare($sql0); $stmt0->execute();

    $sql0='CREATE TEMPORARY TABLE `12month` AS 
SELECT MONTH(m.Date) AS SaleMonth, ClientNo, COUNT(m.TxnID) AS Frequency, SUM(Qty*UnitPrice) AS SaleAmt, CONCAT(MONTHNAME(m.Date)," ",'.$currentyr.') AS SaleDate  FROM `invty_2sale` m JOIN `invty_2salesub` s ON m.TxnID=s.TxnID WHERE MONTH(m.Date)<'.$checkmonth.' AND m.txntype IN (1,2) AND m.ClientNo NOT IN (10000,10001,10004,15001,15002,15003,15004,15005) '.$condition.' GROUP BY ClientNo HAVING SaleAmt>10000 
UNION
SELECT MONTH(m.Date) AS SaleMonth, ClientNo, COUNT(m.TxnID) AS Frequency, SUM(Qty*UnitPrice) AS SaleAmt, CONCAT(MONTHNAME(m.Date)," ",'.$lastyr.') AS SaleDate FROM `'.$lastyr.'_1rtc`.`invty_2sale` m JOIN `'.$lastyr.'_1rtc`.`invty_2salesub` s ON m.TxnID=s.TxnID WHERE MONTH(m.Date)>('.$currmonth.') AND m.txntype IN (1,2) AND m.ClientNo NOT IN (10000,10001,10004,15001,15002,15003,15004,15005) '.$condition.' GROUP BY ClientNo HAVING SaleAmt>10000 '; //if($_SESSION['(ak0)']==1002){echo $sql0;}
    $stmt0=$link->prepare($sql0); $stmt0->execute();

    $sql0='DROP TEMPORARY TABLE IF EXISTS `activebefore`;'; $stmt0=$link->prepare($sql0); $stmt0->execute();

    $sql0='CREATE TEMPORARY TABLE `activebefore` AS 
SELECT @t:=@t+1 AS TxnNo, ClientNo, SUM(Frequency) AS FrequencyInPast12Months, SaleDate AS LastSaleDate FROM `12month` JOIN (SELECT @t:=0) t GROUP BY ClientNo HAVING SUM(SaleAmt)>10000 AND SUM(Frequency)>2 AND MAX(TxnNo);'; $stmt0=$link->prepare($sql0); $stmt0->execute(); // if($_SESSION['(ak0)']==1002){echo $sql0;}

    $sql='SELECT c.*, FrequencyInPast12Months, LastSaleDate FROM `1clients` c JOIN `activebefore` ab ON c.ClientNo=ab.ClientNo WHERE ab.ClientNo NOT IN (SELECT ClientNo FROM `invty_2sale` m WHERE m.ClientNo NOT IN (10000,10001,10004,15001,15002,15003,15004,15005) AND m.txntype IN (1,2) AND MONTH(m.Date)>='.$checkmonth.') AND c.Inactive=0 ORDER BY ClientName';
    

$columnnames=array('ClientNo','ClientName','FrequencyInPast12Months','LastSaleDate');

include_once('../backendphp/layout/displayastable.php');
   break;   

case 'WklyDep':
    if (!allowedToOpen(5752,'1rtc')) { echo 'No permission'; exit; }
    $title='Weekly Deposits'; $formdesc='WARNING: This includes internal <b>fund transfers</b> <br>';
    $sql0='CREATE TEMPORARY TABLE GrossDeposit AS
 SELECT 
         (`Cleared` + INTERVAL (((6 - DAYOFWEEK(`Cleared`)) + 7) % 7) DAY) AS Friday, COUNT(dm.TxnID) AS DepCount,
        `dm`.`DebitAccountID` AS `AccountID`, SUM(Amount) AS `WklyDeposit`, m.Order
    FROM
        `acctg_2depositmain` `dm` JOIN acctg_2depositsub ds ON dm.TxnID=ds.TxnID
        JOIN `banktxns_1maintaining` m ON m.AccountID=`dm`.`DebitAccountID` WHERE `Cleared` IS NOT NULL
    GROUP BY (`Cleared` + INTERVAL (((6 - DAYOFWEEK(`Cleared`)) + 7) % 7) DAY),`dm`.`DebitAccountID`
    ORDER BY (`Cleared` + INTERVAL (((6 - DAYOFWEEK(`Cleared`)) + 7) % 7) DAY),`dm`.`DebitAccountID`';
    $stmt0=$link->prepare($sql0); $stmt0->execute();
 $sql0='CREATE TEMPORARY TABLE Encash AS
 SELECT 
         (`Cleared` + INTERVAL (((6 - DAYOFWEEK(`Cleared`)) + 7) % 7) DAY) AS Friday, COUNT(dm.TxnID) AS DepCount,
        `dm`.`DebitAccountID` AS `AccountID`, SUM(Amount) AS `WklyDeposit`
    FROM
        `acctg_2depositmain` `dm` JOIN acctg_2depencashsub ds ON dm.TxnID=ds.TxnID
        JOIN `banktxns_1maintaining` m ON m.AccountID=`dm`.`DebitAccountID` WHERE `Cleared` IS NOT NULL
    GROUP BY (`Cleared` + INTERVAL (((6 - DAYOFWEEK(`Cleared`)) + 7) % 7) DAY),`dm`.`DebitAccountID` 
    ORDER BY (`Cleared` + INTERVAL (((6 - DAYOFWEEK(`Cleared`)) + 7) % 7) DAY),`dm`.`DebitAccountID`';
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    $sql1='SELECT gd.AccountID, ShortAcctID AS Account FROM GrossDeposit gd JOIN `acctg_1chartofaccounts` `ca` ON `gd`.`AccountID` = `ca`.`AccountID` GROUP BY gd.AccountID ORDER BY gd.Order';
    $stmt1=$link->query($sql1); $res1=$stmt1->fetchAll();
    $sql=''; $columnnames=array('Friday');
 foreach ($res1 as $fri){
     $sql.=', FORMAT(SUM(CASE WHEN gd.AccountID='.$fri['AccountID'].' THEN (gd.WklyDeposit-IFNULL(en.WklyDeposit,0)) END),0) AS `'.$fri['Account'].'` ';
     $columnnames[]=$fri['Account'];
 }
    $sql='SELECT gd.Friday '.$sql.', FORMAT(SUM(gd.WklyDeposit-IFNULL(en.WklyDeposit,0)),0) AS `Total`  FROM GrossDeposit gd LEFT JOIN Encash en ON gd.Friday=en.Friday AND gd.AccountID=en.AccountID GROUP BY gd.Friday ORDER BY gd.Friday DESC ';
    $columnnames[]='Total';

include_once('../backendphp/layout/displayastable.php');
    break;
   
}

noform:
      $link=null; $stmt=null;
?>