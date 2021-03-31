<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(577,578,579,580,581,5771,5772,5773);$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=false; include_once('../switchboard/contents.php');
 


	include_once('../backendphp/layout/linkstyle.php');
	echo '</br>';
	?>
	<title>ARTools</title>
	<div>
	<font size=4 face='sans-serif'>
	<?php if (allowedToOpen(5781,'1rtc')) {?>
	<a id="link" href='lookuptoolsar.php?w=ARSales'>AR Comparative Sales per Yr</a><?php echo str_repeat('&nbsp',5); ?>
	<?php } ?>    
	<a id="link" href='lookuptoolsar.php?w=CreditLineUsage'>Credit Line Usage</a><?php echo str_repeat('&nbsp',5); ?>
	
    <?php if (allowedToOpen(579,'1rtc')) {?>
	<a id="link" href='lookuptoolsar.php?w=CEI'>Collection Effectiveness Index</a><?php echo str_repeat('&nbsp',5); ?>
	<?php } ?>    
	
	<?php if (allowedToOpen(580,'1rtc')) {?>	
	<a id="link" href='lookuptoolsar.php?w=DSO'>Days Sales Outstanding</a><?php echo str_repeat('&nbsp',5); ?>
	<?php } ?>  
	
	<?php if (allowedToOpen(581,'1rtc')) {?>
	<a id="link" href='lookuptoolsar.php?w=Overdue'>% Overdue</a><?php echo str_repeat('&nbsp',5); ?>
	<?php } ?>  
	
	<?php if (allowedToOpen(578,'1rtc')) {?>
	<a id="link" href='lookuptoolsar.php?w=BadDebts'>% Bad Debts</a><?php echo str_repeat('&nbsp',5); ?>
	<?php } ?>  
	
	<?php if (allowedToOpen(577,'1rtc')) {?>
	<a id="link" href='lookuptoolsar.php?w=ARGrowthandSummary'>AR Growth and Score Summary</a><?php echo str_repeat('&nbsp',5); ?>
	<?php } ?>  
	
	<?php if (allowedToOpen(5773,'1rtc')) {?>
	<a id="link" href='lookuptoolsar.php?w=WeeklyCollectionsEfficiency'>Weekly Collections Efficiency</a><?php echo str_repeat('&nbsp',5);?>
	<?php } ?> 
	
	</font></div><br>
	<?php
	


$whichqry=!isset($_GET['w'])?'':$_GET['w'];
if(allowedToOpen(5771,'1rtc')){ $condition='';} 
elseif(allowedToOpen(5772,'1rtc')) {$condition=' AND b.BranchNo IN (SELECT BranchNo FROM '. $currentyr .'_1rtc.attend_1branchgroups WHERE CNC='.$_SESSION['(ak0)'].')';}
else {$condition=' AND b.BranchNo IN (SELECT BranchNo FROM '. $currentyr .'_1rtc.attend_1branchgroups WHERE TeamLeader='.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].')';}

if (in_array($whichqry,array('CEI','DSO','Overdue','ARGrowthandSummary'))){
$sql0='CREATE TEMPORARY TABLE `TotalAR` AS SELECT BranchNo, SUM(`InvBalance`) AS `TotalAR` FROM `acctg_33qrybalperrecpt` WHERE ClientNo>9000 AND ClientNo NOT IN (15001,15002,15003,15004,15005) GROUP BY BranchNo';    $stmt=$link->prepare($sql0); $stmt->execute();
}
if (in_array($whichqry,array('CEI','ARGrowthandSummary')))
{ $sql0='CREATE TEMPORARY TABLE `BegBal` AS SELECT BranchNo,SUM(BegBalance) AS BegBal FROM `acctg_1begbal` WHERE AccountID IN (200,201,202) GROUP BY BranchNo';
    $stmt=$link->prepare($sql0); $stmt->execute();
    $sql0='CREATE TEMPORARY TABLE `ARMonth` AS
SELECT BranchNo,(SUM(Amount)/MAX(MONTH(`Date`))) AS `ARMonth` FROM `acctg_2salemain` sm JOIN `acctg_2salesub` ss ON sm.TxnID=ss.TxnID WHERE DebitAccountID IN (200,202)
AND ClientNo>9000 AND ClientNo NOT IN (15001,15002,15003,15004,15005) GROUP BY BranchNo;';
    $stmt=$link->prepare($sql0); $stmt->execute();    
    $sql0='CREATE TEMPORARY TABLE `CurrentAR` AS SELECT BranchNo, SUM(`ARAmount`) AS `CurrentAR` FROM `acctg_34arduethisfri` WHERE ClientNo>9000 AND ClientNo NOT IN (15001,15002,15003,15004,15005) GROUP BY BranchNo';
    $stmt=$link->prepare($sql0); $stmt->execute();}
 
if (in_array($whichqry,array('DSO','BadDebts','ARGrowthandSummary')))
{ $sql0='SELECT AVG(Terms) AS AvgTerms FROM 1clients where ARClientType<>0 and terms<>1 and inactive=0;';
    $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
    $dayofyr=date('Y')==$currentyr?date('z'):365;
    $sql0='CREATE TEMPORARY TABLE `CreditSales` AS
SELECT BranchNo,SUM(Amount) AS `CreditSales` FROM `acctg_2salemain` sm JOIN `acctg_2salesub` ss ON sm.TxnID=ss.TxnID WHERE DebitAccountID IN (200,202)
AND ClientNo>9000 AND ClientNo NOT IN (15001,15002,15003,15004,15005) GROUP BY BranchNo;';
    $stmt=$link->prepare($sql0); $stmt->execute();}
if (in_array($whichqry,array('Overdue','ARGrowthandSummary')))
    {
        $sql0='CREATE TEMPORARY TABLE `Overdue` AS
SELECT `bal`.`BranchNo` AS `BranchNo`, TRUNCATE(SUM(`bal`.`InvBalance`), 2) AS `Overdue`
FROM `acctg_33qrybalperrecpt` `bal` LEFT JOIN `1clients` `c` ON ((`bal`.`ClientNo` = `c`.`ClientNo`))
WHERE `bal`.`InvBalance` <> 0 AND DebitAccountID IN (200) AND bal.ClientNo>9000 AND bal.ClientNo NOT IN (15001,15002,15003,15004,15005)
AND (`bal`.`Date` + INTERVAL IFNULL(IF(`c`.`Terms`>30,`c`.`Terms`+15,`c`.`Terms`+7), 0) DAY)<= NOW()
   GROUP BY `bal`.`BranchNo`
    HAVING ((`Overdue` > 1) OR (`Overdue` < -1));';
 $stmt=$link->prepare($sql0); $stmt->execute();

$sql0='CREATE TEMPORARY TABLE `UndepositedPDC` AS
SELECT cs.BranchNo, IFNULL(SUM(SumOfAmount),0) AS `UndepPDC` FROM `acctg_31unionpdcs` cs 
LEFT JOIN `acctg_30uniar` aa ON aa.`Particulars`=cs.ForChargeInvNo AND aa.ClientNo=cs.ClientNo AND aa.BranchNo=cs.BranchNo AND cs.ClientNo>9999
 AND cs.ClientNo NOT IN (15001,15002,15003,15004,15005)
WHERE PDCNo IN 
(SELECT PDCNo FROM `acctg_undepositedclientpdcs`  pdc JOIN `1clients` c ON c.ClientNo=pdc.ClientNo
WHERE (`SaleDate` + INTERVAL IFNULL(IF(`c`.`Terms`>30,`c`.`Terms`+15,`c`.`Terms`+7), 0) DAY)<= NOW() AND pdc.BranchNo=cs.BranchNo
) GROUP BY cs.BranchNo;';
$stmt=$link->prepare($sql0); $stmt->execute();

$sql0='CREATE TEMPORARY TABLE `PercentOverdue` AS
SELECT BranchNo, IFNULL(`Overdue`,0)+(SELECT IFNULL(`UndepPDC`,0) FROM `UndepositedPDC` WHERE BranchNo=o.BranchNo) AS `Overdue`, (SELECT IF(ISNULL(`TotalAR`) OR TotalAR=0,1,TotalAR) FROM `TotalAR` WHERE BranchNo=o.BranchNo) AS `TotalAR` FROM `Overdue` o';
$stmt=$link->prepare($sql0); $stmt->execute();
    }    
    
if (in_array($whichqry,array('BadDebts','ARGrowthandSummary')))
{ $sql0='CREATE TEMPORARY TABLE `BadDebts` AS
SELECT `BranchNo` AS `BranchNo`, TRUNCATE(SUM(Amount), 2) AS `BadDebts` FROM `acctg_0unialltxns` WHERE AccountID IN (932,202) GROUP BY `BranchNo`;';
 $stmt=$link->prepare($sql0); $stmt->execute();}
   
$hidecount=true;

switch ($whichqry){
	case'WeeklyCollectionsEfficiency':
if (!allowedToOpen(5773,'1rtc')) { echo 'No permission'; exit; }
if (allowedToOpen(5774,'1rtc')){
	$condition='';
}else{
$condition='and BranchNo in (select BranchNo from attend_1branchgroups where CNC=\''.$_SESSION['(ak0)'].'\')';
}
		$title='Weekly Collections Efficiency';
		$sql='select Branch,format(CollectionThisWk,2) as CollectionThisWk,format(UndepositedPDCs,2) as UndepositedPDCs,format(BouncedCheck,2) as BouncedCheck,format(OldAccounts,2) as OldAccounts,format(ARClearedCollections,2) as ARClearedCollections,
		concat(format((ifnull(ARClearedCollections,0)/(ifnull(CollectionThisWk,0)+ifnull(UndepositedPDCs,0)+ifnull(BouncedCheck,0)+ifnull(OldAccounts,0)))*100,0),"%")
		as `CollectionEfficiency%` from 
		
		(select Branch,BranchNo from 1branches where (PseudoBranch=0 and Active=1 or BranchNo=40) '.$condition.') b left join
		
		(select bal.BranchNo,sum(ARAmount) as `CollectionThisWk` from acctg_34allarforaging bal JOIN `1clients` c ON (bal.ClientNo =c.ClientNo) join `1branches` b on b.BranchNo=bal.BranchNo join `1companies` co on co.CompanyNo=b.CompanyNo where year(Due)>\''.$last2yrs.'\' and `Due` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7) day) Group By bal.BranchNo) ctw on ctw.BranchNo=b.BranchNo left join
		
		(select BranchNo,sum(PDC) as `UndepositedPDCs` from acctg_undepositedclientpdcs where `DateofPDC` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7) day) Group By BranchNo) pdc on pdc.BranchNo=b.BranchNo left join 
		
		(SELECT s.BranchNo,SUM(s.Amount)  AS `BouncedCheck` FROM acctg_2collectmain m JOIN acctg_2collectsub s ON m.TxnID=s.TxnID JOIN acctg_2collectsubbounced sb ON m.TxnID=sb.TxnID JOIN `acctg_unpaidinv` un ON un.ClientNo=m.ClientNo and un.Particulars=concat("BouncedfromCR",`m`.`CollectNo`,"_",`m`.`CheckNo`," Inv",`s`.`ForChargeInvNo`) GROUP BY s.BranchNo) bc on bc.BranchNo=b.BranchNo left join 
		
		(SELECT bal.BranchNo,SUM(ARAmount) AS `OldAccounts` FROM acctg_34allarforaging bal JOIN `1clients` c ON (bal.ClientNo =c.ClientNo) join `1branches` b on b.BranchNo=bal.BranchNo join `1companies` co on co.CompanyNo=b.CompanyNo where year(Due)<=\''.$last2yrs.'\' Group By BranchNo) oa on oa.BranchNo=b.BranchNo left join 
		
		(SELECT s.BranchNo,sum(s.Amount) as `ARClearedCollections` FROM `acctg_2collectmain` `om`
		JOIN `acctg_2collectsub` `os` ON `om`.`TxnID` = `os`.`TxnID`
		JOIN `acctg_2depositsub` `s` ON `s`.`CRNo` = CONCAT("C-",om.BranchSeriesNo,"-",`om`.`CollectNo`)
		AND IF((ISNULL(`om`.`CheckNo`) OR (`om`.`CheckNo` LIKE "") OR (om.Type<>2)),"1=1",((`s`.`CheckNo` = `om`.`CheckNo`) AND (`om`.`CheckBank` = `s`.`CheckDraweeBank`)))
		AND (`s`.`BranchNo` = `os`.`BranchNo`) AND`om`.`ClientNo` = `s`.`ClientNo`
		JOIN `acctg_2depositmain` `m` ON `m`.`TxnID` = `s`.`TxnID`
		WHERE
		(`os`.`ForChargeInvNo` IS NOT NULL)
		AND (`os`.`CreditAccountID` = 200)
		AND (`m`.`Cleared` IS NOT NULL) and week(m.Date)=week(curdate()) Group By s.BranchNo) cc on cc.BranchNo=b.BranchNo
		
		';
		$columnnames=array('Branch','CollectionThisWk','UndepositedPDCs','BouncedCheck','OldAccounts','ARClearedCollections','CollectionEfficiency%');
		include('../backendphp/layout/displayastable.php');
		
	break;
	
	case'ARSales':
	 if (!allowedToOpen(5781,'1rtc')) { echo 'No permission'; exit; }
	$title='AR Comparative Sales per Yr';
        $startyr=$currentyr-4;
        $reportyr=$lastyr;
        $sql1=''; $sql='';
        $columnnames=array('ClientNo','ClientName','Terms','CreditLimit',$currentyr);
        
        while ($reportyr>=$startyr){
            $sql1.=' UNION
	select ClientNo,sum(Qty*UnitPrice) as Amount,\''.$reportyr.'\' as Col  from '.$reportyr.'_1rtc.invty_2sale s join '.$reportyr.'_1rtc.invty_2salesub ss on ss.TxnID=s.TxnID  where txntype=2 Group By ClientNo ';
            $sql.=',MAX(CASE WHEN Col=\''.$reportyr.'\' THEN FORMAT(Amount,0) END) AS `'.$reportyr.'`';
            $columnnames[]=$reportyr;
            $reportyr=$reportyr-1;
        }
        
	$sql1='Create temporary table Purchase as select ClientNo,sum(Qty*UnitPrice) as Amount,\''.$currentyr.'\' as Col from invty_2sale s join invty_2salesub ss on ss.TxnID=s.TxnID  where txntype=2 Group By ClientNo '.$sql1;
	$stmt1=$link->prepare($sql1); $stmt1->execute();

	$sql='select p.ClientNo,ClientName,Terms,format(CreditLimit,0) as CreditLimit'.$sql.',MAX(CASE WHEN Col=\''.$currentyr.'\' THEN FORMAT(Amount,0) END) AS `'.$currentyr.'`,CASE WHEN ARClientType=1 then "ARClient" WHEN ARClientType=2 then "PDCRequired" WHEN ARClientType=3 then "DCRequired" WHEN ARClientType=4 then "AR1" end as ARClientType,CASE WHEN ClientClass=1 THEN "KeyAccount" WHEN ClientClass=2 THEN "Strategic Account" ELSE 0 end as ClientClass,Remarks from Purchase p join 1clients c on c.ClientNo=p.ClientNo where ARClientType<>0 Group By p.ClientNo';
	
	
	array_push($columnnames,'ARClientType','ClientClass','Remarks');
	include('../backendphp/layout/displayastable.php');
	
	break;

case 'CEI':

    if (!allowedToOpen(579,'1rtc')) { echo 'No permission'; exit; }
    $title='AR Tools - CEI';
    
    $formdesc='</i><BR><BR><b>Collection Effectiveness Index (CEI)</b>
        <br><br>The CEI compares how much money was owed to the company and how much of that money was actually collected in the given time period, usually one year. The resulting percentage allows the company to gauge how strong their current collections policies and procedures are and whether or not changes need to be made.
<br><br>
<b>CEI = (Beginning receivables + Monthly credit sales - Ending total receivables) / (Beginning receivables + Monthly credit sales - Ending current receivables) x 100 </b>
<br><br>
where <br>
&nbsp; &nbsp; &nbsp; Monthly credit sales = Total credit sales / number of months<br>
&nbsp; &nbsp; &nbsp; Ending total receivables = Ending AR value<br>
&nbsp; &nbsp; &nbsp; Ending current receivables = Ending AR value due today (or this month)';
    
    $formdesc=$formdesc.'<br><br>
The closer the resulting percent is to 100% the stronger your collections processes and policies are. A low or dropping percentage means it is time to re-evaluate your policies on selling on credit and the processes your collectors are following. <i>';
    
    $formdesc=$formdesc.'<br><br>';
    
    $sql='SELECT "TOTAL" AS Score, FORMAT(SUM(`BegBal`),0) AS `BegBal`, FORMAT(SUM(IFNULL(`ARMonth`,0)),0) AS `ARMonth`, 
        FORMAT(SUM(IFNULL(`TotalAR`,0)),0) AS `TotalAR`, FORMAT(SUM(IFNULL(`CurrentAR`,0)),0) AS `CurrentAR` , 
        FORMAT((SUM(IFNULL(`BegBal`,0))+SUM(IFNULL(`ARMonth`,0))-SUM(IFNULL(`TotalAR`,0)))/(SUM(IFNULL(`BegBal`,1))+SUM(IFNULL(`ARMonth`,1))-SUM(IFNULL(`CurrentAR`,1)))*100,2) AS `CEI`  
FROM `BegBal` bb LEFT JOIN `ARMonth` arm ON bb.BranchNo=arm.BranchNo 
LEFT JOIN `TotalAR` ta ON bb.BranchNo=ta.BranchNo LEFT JOIN `CurrentAR` car ON bb.BranchNo=car.BranchNo JOIN `1branches` b ON b.BranchNo=bb.BranchNo;';
    $columnnames=array('Score','BegBal','ARMonth','TotalAR','CurrentAR','CEI');
if (!allowedToOpen(5771,'1rtc')) { $columnnames=array();}
    include('../backendphp/layout/displayastable.php');
    echo '<br><br>';
    
    $sql='SELECT b.BranchNo, Branch, FORMAT(`BegBal`,0) AS `BegBal`, FORMAT(IFNULL(`ARMonth`,0),0) AS `ARMonth`, 
        FORMAT(IFNULL(`TotalAR`,0),0) AS `TotalAR`, FORMAT(IFNULL(`CurrentAR`,0),0) AS `CurrentAR`, 
FORMAT((IFNULL(`BegBal`,0)+IFNULL(`ARMonth`,0)-IFNULL(`TotalAR`,0))/(IFNULL(`BegBal`,1)+IFNULL(`ARMonth`,1)-IFNULL(`CurrentAR`,1))*100,2) AS `CEI`  
FROM `BegBal` bb LEFT JOIN `ARMonth` arm ON bb.BranchNo=arm.BranchNo 
LEFT JOIN `TotalAR` ta ON bb.BranchNo=ta.BranchNo LEFT JOIN `CurrentAR` car ON bb.BranchNo=car.BranchNo JOIN `1branches` b ON b.BranchNo=bb.BranchNo
WHERE (`BegBal`<>0 OR `ARMonth`<>0 OR `TotalAR`<>0 OR `CurrentAR`<>0) '.$condition.'
ORDER BY TRUNCATE(`CEI`,2) DESC;';
    $columnnames=array('BranchNo','Branch','BegBal','ARMonth','TotalAR','CurrentAR','CEI');
    
    // include_once('../backendphp/layout/displayastableonlynoheaders.php');
	include('../backendphp/layout/displayastable.php');
    break;

case 'DSO':

    if (!allowedToOpen(580,'1rtc')) { echo 'No permission'; exit; }
    $title='AR Tools - DSO';
    
    $formdesc='</i><BR><BR><b>Days Sales Outstanding (DSO)</b>
        <br><br>The DSO ratio shows both the average time it takes to turn the receivables into cash and the age, in terms of days, of a company\'s accounts receivable.  DSO is not a measurement of effectiveness, rather efficiency. 
<br><br>
<b>DSO = (Total Accounts Receivables / Total Credit Sales) * No. of Days in the period being analyzed<br><br></b>';
/*<br><br>Apart from the Regular DSO, the Best Possible DSO yields insight into delinquencies since it uses only the current portion of receivables.  As a measurement, the closer the Regular DSO is to the Best Possible DSO, the closer the receivables  are to the optimal level.

<b>Best Possible DSO = (Current Receivables / Total Credit Sales) * No. of Days in the period being analyzed</b>
<br><br> */
$formdesc=$formdesc.'where <br>
&nbsp; &nbsp; &nbsp; Total Accounts Receivables = Ending AR value<br>
&nbsp; &nbsp; &nbsp; No. of Days = as of current date<i><br><br>';
// &nbsp; &nbsp; &nbsp; Current Receivables = Ending AR value due today (or this month)<br>
    
    $formdesc=$formdesc.'Days in period = '.$dayofyr;
    $formdesc=$formdesc.str_repeat('&nbsp;', 10).'Average Terms: '.number_format($res0['AvgTerms'],2).' days<br><br>';
    
    $sql='SELECT "TOTAL" AS `Score`, FORMAT(SUM(IFNULL(`CreditSales`,0)),0) AS `CreditSales`, 
        FORMAT(SUM(IFNULL(`TotalAR`,0)),0) AS `TotalAR`,  
        FORMAT(SUM(IFNULL(`TotalAR`,0))/(SUM(IFNULL(`CreditSales`,1)))*'.$dayofyr.',2) AS `DSO`
FROM `1branches` b LEFT JOIN `CreditSales` arm ON b.BranchNo=arm.BranchNo 
LEFT JOIN `TotalAR` ta ON b.BranchNo=ta.BranchNo ';
    $columnnames=array('Score','CreditSales','TotalAR','DSO');
    if (!allowedToOpen(5771,'1rtc')) { $columnnames=array();}
    include('../backendphp/layout/displayastable.php');
    echo '<br><br>';
    
    $sql='SELECT b.BranchNo, Branch, FORMAT(IFNULL(`CreditSales`,0),0) AS `CreditSales`, 
        FORMAT(IFNULL(`TotalAR`,0),0) AS `TotalAR`, 
        FORMAT((IFNULL(`TotalAR`,0)/IFNULL(`CreditSales`,1))*'.$dayofyr.',2) AS `DSO`
FROM `1branches` b LEFT JOIN `CreditSales` arm ON b.BranchNo=arm.BranchNo 
LEFT JOIN `TotalAR` ta ON b.BranchNo=ta.BranchNo 
WHERE (`CreditSales`<>0 OR `TotalAR`<>0) '.$condition.' ORDER BY TRUNCATE(`DSO`,2) DESC, `TotalAR` DESC;';
    $columnnames=array('BranchNo','Branch','CreditSales','TotalAR','DSO');
    
    // include_once('../backendphp/layout/displayastableonlynoheaders.php');
	include('../backendphp/layout/displayastable.php');
    break;

case 'Overdue':

    if (!allowedToOpen(581,'1rtc')) { echo 'No permission'; exit; }
    $title='AR Tools - Overdue';    
    $formdesc='</i><BR><BR><b>Percent Overdue</b>
        <br><br>
<b>% Overdue = Overdue Accounts/Total Accounts Receivables<br></b>';
$formdesc=$formdesc.'where <br>
&nbsp; &nbsp; &nbsp; Overdue Accounts = does not include those tagged as "Old Accounts"<br>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Undeposited postdated checks for overdue accounts are included in the calculation.<br>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Calculation of terms include collection grace period of 7 days for terms of less than 30 days, and 15 days for terms of 30 days or more.
<br><br>Perfect score is 0%.<i><br><br>';

$columnnames=array('Score','Overdue','TotalAR','%Overdue');
$sql='SELECT "" AS BranchNo, "TOTAL" AS Score, FORMAT(SUM(`Overdue`),0) AS `Overdue`, FORMAT(SUM(`TotalAR`),0) AS `TotalAR`,FORMAT((SUM(`Overdue`)/SUM(`TotalAR`))*100,2) AS `%Overdue` FROM `PercentOverdue`;';
if (!allowedToOpen(5771,'1rtc')) { $columnnames=array();}
include('../backendphp/layout/displayastable.php');
    echo '<br><br>';
$columnnames=array('BranchNo','Branch','Overdue','TotalAR','%Overdue');
$sql='SELECT o.BranchNo, Branch, FORMAT(`Overdue`,0) AS `Overdue`, FORMAT(`TotalAR`,0) AS `TotalAR`, FORMAT((`Overdue`/`TotalAR`)*100,2) AS `%Overdue` FROM `PercentOverdue` o JOIN `1branches` b ON b.BranchNo=o.BranchNo WHERE (1=1) '.$condition.' ORDER BY TRUNCATE(`%Overdue`,2) DESC,`Overdue` DESC;';
    include('../backendphp/layout/displayastable.php');
    break;
   
case 'BadDebts':

    if (!allowedToOpen(578,'1rtc')) { echo 'No permission'; exit; }
    $title='AR Tools - Bad Debts';    
    $formdesc='</i><BR><BR><b>Percent Bad Debts</b>
        <br><br>
<b>% Bad Debts = Bad Debts This Year / Total Credit Sales Year to Date<br></b>';
$formdesc=$formdesc.'where <br>
&nbsp; &nbsp; &nbsp; Bad Debts = includes those tagged as "Old Accounts"
<br><br>Perfect score is 0%.<i><br><br>';

$columnnames=array('Score','BadDebts','TotalCreditSales','%BadDebts');
$sql='SELECT "" AS BranchNo, "TOTAL" AS Score, FORMAT(SUM(`BadDebts`),0) AS `BadDebts`, FORMAT(SUM(IFNULL(`CreditSales`,0)),0) AS `TotalCreditSales`,FORMAT((SUM(`BadDebts`)/SUM(`CreditSales`))*100,2) AS `%BadDebts` FROM `BadDebts` bd LEFT JOIN `CreditSales` ta ON bd.BranchNo=ta.BranchNo;';
if (!allowedToOpen(5771,'1rtc')) { $columnnames=array();}
include('../backendphp/layout/displayastable.php');
    echo '<br><br>';
$columnnames=array('BranchNo','Branch','BadDebts','TotalCreditSales','%BadDebts');
$sql='SELECT b.BranchNo, Branch, FORMAT(`BadDebts`,0) AS `BadDebts`, FORMAT(IFNULL(`CreditSales`,0),0) AS `TotalCreditSales`, FORMAT((`BadDebts`/IF(ISNULL(`CreditSales`) OR `CreditSales`=0,1,`CreditSales`))*100,2) AS `%BadDebts` FROM `BadDebts` bd JOIN `1branches` b ON b.BranchNo=bd.BranchNo LEFT JOIN `CreditSales` ta ON bd.BranchNo=ta.BranchNo WHERE (1=1) '.$condition.' ORDER BY TRUNCATE(`%BadDebts`,2) DESC,`BadDebts` DESC;';

    include('../backendphp/layout/displayastable.php');
    break;

case 'ARGrowthandSummary':

    if (!allowedToOpen(577,'1rtc')) { echo 'No permission'; exit; }
    $title='AR Tools - AR Growth and Score Summary';    
    $formdesc='</i><BR><BR><b>AR Growth</b>
        <br><br>Growth = comparison between last year and this year for<br>
        &nbsp; &nbsp; &nbsp; Number of AR Clients<br>
        &nbsp; &nbsp; &nbsp; Total Credit Limit<br>
        &nbsp; &nbsp; &nbsp; Total Credit Sales (same period)<br>
        &nbsp; &nbsp; &nbsp; Average Credit Terms (reference only)<br>
        &nbsp; &nbsp; &nbsp; AR Turnover = Total Credit Sales / Total Credit Limit<i><br><br>';
    $thisyr=$currentyr; $lastyr=$thisyr-1;
    
$sql0='CREATE TEMPORARY TABLE `Numbers` AS SELECT '.$thisyr.' AS `Year`, COUNT(ClientNo) AS NumofARClients, SUM(CreditLimit) AS TotalCreditLimit, AVG(Terms) AS AvgTerms, 0 AS `TotalARSales` FROM `'.$thisyr.'_1rtc`.`1clients` WHERE ARClientType<>0 AND terms<>1 AND Inactive=0 AND ClientNo NOT IN (15001,15002,15003,15004,15005);'; 
$stmt0=$link->prepare($sql0); $stmt0->execute();
$sql1='INSERT INTO `Numbers` SELECT '.$lastyr.' AS `Year`, COUNT(ClientNo) AS NumofARClients, SUM(CreditLimit) AS TotalCreditLimit, AVG(Terms) AS AvgTerms, 0 AS `TotalARSales` FROM `'.$lastyr.'_1rtc`.`1clients` WHERE ARClientType=1 AND terms<>1 AND Inactive=0;';
$stmt1=$link->prepare($sql1); $stmt1->execute();

$sql2='UPDATE `Numbers` SET `TotalARSales`=(SELECT SUM(`Amount`) FROM `acctg_2salemain` sm JOIN `acctg_2salesub` ss ON sm.TxnID=ss.TxnID WHERE DebitAccountID IN (200,202)
AND ClientNo>9000 AND ClientNo NOT IN (15001,15002,15003,15004,15005)) WHERE `Year`='.$thisyr;    
$stmt2=$link->prepare($sql2); $stmt2->execute();
$sql3='UPDATE `Numbers` SET `TotalARSales`=(SELECT SUM(`Amount`) FROM `'.$lastyr.'_1rtc`.`acctg_2salemain` sm JOIN `'.$lastyr.'_1rtc`.`acctg_2salesub` ss ON sm.TxnID=ss.TxnID WHERE DebitAccountID IN (200,202)
AND ClientNo>9000 AND ClientNo NOT IN (15001,15002,15003,15004,15005)) WHERE `Year`='.$lastyr;   
$stmt3=$link->prepare($sql3); $stmt3->execute();

$sql='SELECT `Year`, `NumofARClients`, FORMAT(`TotalCreditLimit`,0) AS `TotalCreditLimit`, FORMAT(`AvgTerms`,2) AS `AvgTerms`, FORMAT(`TotalARSales`,0) AS `TotalARSales`, FORMAT((`TotalARSales`/`TotalCreditLimit`),2) AS `ARTurnover` FROM `Numbers`';
$columnnames=array('Year','NumofARClients','TotalCreditLimit','AvgTerms','TotalARSales','ARTurnover');

include_once('../backendphp/layout/displayastable.php');

$sql0='CREATE TEMPORARY TABLE `Scores` AS SELECT "Collection Effectiveness Index (CEI)" AS `Ratio`, (SUM(IFNULL(`BegBal`,0))+SUM(IFNULL(`ARMonth`,0))-SUM(IFNULL(`TotalAR`,0)))/(SUM(IFNULL(`BegBal`,1))+SUM(IFNULL(`ARMonth`,1))-SUM(IFNULL(`CurrentAR`,1)))*100 AS ScoreVal, FORMAT((SUM(IFNULL(`BegBal`,0))+SUM(IFNULL(`ARMonth`,0))-SUM(IFNULL(`TotalAR`,0)))/(SUM(IFNULL(`BegBal`,1))+SUM(IFNULL(`ARMonth`,1))-SUM(IFNULL(`CurrentAR`,1)))*100,2) AS Score, "100%" AS Ideal,
     ((SUM(IFNULL(`BegBal`,0))+SUM(IFNULL(`ARMonth`,0))-SUM(IFNULL(`TotalAR`,0)))/(SUM(IFNULL(`BegBal`,1))+SUM(IFNULL(`ARMonth`,1))-SUM(IFNULL(`CurrentAR`,1)))/100)*100 AS `Rating`, "(Beg. AR + Monthly credit sales - End TOTAL AR) / (Beg. AR + Monthly credit sales - End CURRENT AR) x 100" AS Formula
     FROM `BegBal` bb LEFT JOIN `ARMonth` arm ON bb.BranchNo=arm.BranchNo 
LEFT JOIN `TotalAR` ta ON bb.BranchNo=ta.BranchNo LEFT JOIN `CurrentAR` car ON bb.BranchNo=car.BranchNo JOIN `1branches` b ON b.BranchNo=bb.BranchNo;';
$stmt0=$link->prepare($sql0); $stmt0->execute();
$sql0='INSERT INTO `Scores` SELECT "Days Sales Outstanding (DSO)" AS `Ratio`,  SUM(IFNULL(`TotalAR`,0))/(SUM(IFNULL(`CreditSales`,1)))*'.$dayofyr.' AS ScoreVal, FORMAT(SUM(IFNULL(`TotalAR`,0))/(SUM(IFNULL(`CreditSales`,1)))*'.$dayofyr.',2) AS Score, "'.number_format($res0['AvgTerms'],2).'" AS Ideal, '
        . number_format($res0['AvgTerms'],2).'/(SUM(IFNULL(`TotalAR`,0))/(SUM(IFNULL(`CreditSales`,1)))*'.$dayofyr.' ) AS `Rating`, "(Total AR / Total Credit Sales) * No. of Days in the period" AS Formula'
        . ' FROM `1branches` b LEFT JOIN `CreditSales` arm ON b.BranchNo=arm.BranchNo 
LEFT JOIN `TotalAR` ta ON b.BranchNo=ta.BranchNo;';
$stmt0=$link->prepare($sql0); $stmt0->execute();
$sql0='INSERT INTO `Scores` SELECT "% Overdue" AS `Ratio`, (SUM(`Overdue`)/SUM(`TotalAR`))*100 AS ScoreVal, FORMAT((SUM(`Overdue`)/SUM(`TotalAR`))*100,2) AS Score, "0" AS Ideal, "0" AS `Rating`, "% Overdue = Overdue Accounts/Total AR" AS Formula FROM PercentOverdue;';
$stmt0=$link->prepare($sql0); $stmt0->execute();
$sql0='INSERT INTO `Scores` SELECT "% Bad Debts" AS `Ratio`, (SUM(`BadDebts`)/SUM(`CreditSales`))*100 AS ScoreVal, FORMAT((SUM(`BadDebts`)/SUM(`CreditSales`))*100,2) AS Score, "0" AS Ideal, "0" AS `Rating`, "% Bad Debts = Bad Debts This Year / Total Credit Sales Year to Date" AS Formula FROM `BadDebts` bd LEFT JOIN `CreditSales` ta ON bd.BranchNo=ta.BranchNo;';
$stmt0=$link->prepare($sql0); $stmt0->execute();
echo '<br><br>';
$columnnames=array('Ratio', 'Score', 'Ideal', '% Rating','Formula');
$sql=' SELECT *, FORMAT(Rating*100,2) AS `% Rating` FROM `Scores`';
include_once('../backendphp/layout/displayastableonlynoheaders.php');

	unset($formdesc);
	$title='';
	$subtitle=' '.$currentyr.' Credit Sales vs Cleared Collection';
	$sql1='create temporary table SalesCredit as select month(Date) as `Month`,sum(Amount) as CreditSales from acctg_2salemain sm left join acctg_2salesub ss on ss.TxnID=sm.TxnID where DebitAccountID="200" Group By month(Date)';
	$stmt1=$link->prepare($sql1); $stmt1->execute();
	
	$sql='select
	
	case
	when MonthNo="01" then "January" when MonthNo="02" then "February"
	when MonthNo="03" then "March" when MonthNo="04" then "April"
	when MonthNo="05" then "May" when MonthNo="06" then "June"
	when MonthNo="07" then "July" when MonthNo="08" then "August"
	when MonthNo="09" then "September" when MonthNo="10" then "October"
	when MonthNo="11" then "November" when MonthNo="12" then "December"
	end as `Month`,
	
	format(sum(ClearedCollections),2) as ClearedCollections,format(CreditSales,2) as CreditSales,format((sum(ClearedCollections)/CreditSales)*100,0) as `%` from acctg_6targetscores ts join SalesCredit sc on sc.Month=ts.MonthNo Group By MonthNo';
	// echo $sql; exit();
	

	$columnnames=array('Month','CreditSales','ClearedCollections','%');
//

include('../backendphp/layout/displayastablenosort.php');


unset($formdesc);
	$title='';
	$subtitle=' '.$lastyr.' Credit Sales vs Cleared Collection';
	$sql1='create temporary table `'.$lastyr.'SalesCredit` as select month(Date) as `Month`,sum(Amount) as CreditSales from '.$lastyr.'_1rtc. acctg_2salemain sm left join '.$lastyr.'_1rtc. acctg_2salesub ss on ss.TxnID=sm.TxnID where DebitAccountID="200" Group By month(Date)';
	$stmt1=$link->prepare($sql1); $stmt1->execute();
	
	$sql='select
	
	case
	when MonthNo="01" then "January" when MonthNo="02" then "February"
	when MonthNo="03" then "March" when MonthNo="04" then "April"
	when MonthNo="05" then "May" when MonthNo="06" then "June"
	when MonthNo="07" then "July" when MonthNo="08" then "August"
	when MonthNo="09" then "September" when MonthNo="10" then "October"
	when MonthNo="11" then "November" when MonthNo="12" then "December"
	end as `Month`,
	
	format(sum(ClearedCollections),2) as ClearedCollections,format(CreditSales,2) as CreditSales,format((sum(ClearedCollections)/CreditSales)*100,0) as `%` from '.$lastyr.'_1rtc. acctg_6targetscores ts join `'.$lastyr.'SalesCredit` sc on sc.Month=ts.MonthNo Group By MonthNo';
	// echo $sql; exit();
	

	$columnnames=array('Month','CreditSales','ClearedCollections','%');
//

include('../backendphp/layout/displayastablenosort.php');


    break;
	
case 'CreditLineUsage':
if($currentyr==date('Y')){
$dayofyr=date('z');
}else{
$dayofyr=365;
}	
	$title='Credit Line Usage';
	
$sql1='CREATE TEMPORARY TABLE `ClearedPayments` AS SELECT om.ClientNo,`os`.`ForChargeInvNo`, `m`.`Cleared`, `om`.`CollectNo` AS CollectNo FROM `acctg_2collectmain` `om`
    JOIN `acctg_2collectsub` `os` ON `om`.`TxnID` = `os`.`TxnID`
    JOIN `acctg_2depositsub` `s` ON `s`.`CRNo` = CONCAT("C-",om.BranchSeriesNo,"-",`om`.`CollectNo`)
    AND IF((ISNULL(`om`.`CheckNo`) OR (`om`.`CheckNo` LIKE "") OR (om.Type<>2)),"1=1",((`s`.`CheckNo` = `om`.`CheckNo`) AND (`om`.`CheckBank` = `s`.`CheckDraweeBank`)))
    AND (`s`.`BranchNo` = `os`.`BranchNo`) AND`om`.`ClientNo` = `s`.`ClientNo`
    JOIN `acctg_2depositmain` `m` ON `m`.`TxnID` = `s`.`TxnID`
    WHERE
    (`os`.`ForChargeInvNo` IS NOT NULL)
    AND (`os`.`CreditAccountID` = 200)
    AND (`m`.`Cleared` IS NOT NULL)'; 
// echo $sql1; exit();
	$stmt1=$link->prepare($sql1); $stmt1->execute();
	
	$formdesc='</br>
	<div style="background-color:#D3D3D3; padding:5px; width:970px;"></i><h3>Formulas:</h3>
	</br><b>Days to Date ('.date('Y-m-d').'):</b> '.$dayofyr.' days
	</br></br><b>Annual Credit Limit (ACL) in Php:</b> (300 days / Terms) x Credit Limit '.str_repeat('&nbsp;',5).' **Set to 300 days due to clearing of payments and weekends, and holidays
	</br></br><b>Avg Purchases (Php):</b> (Purchases To Date / Days to Date) x Terms
	</br></br><b>Credit Usage %:</b> (Average Purchases / Credit Limit) x 100
	</br></br><b>Avg Days To Pay:</b> Number of days of invoices before its paid / Number of invoices</br></div>';
	
	$sql='Select ss.ClientNo,ClientName,Concat(Terms," days") as Terms,format(CreditLimit,0) as CreditLimit,format((300/Terms)*CreditLimit,0) as ACL,format(sum(Amount),0) as PurchasesToDate,format(((sum(Amount)/\''.$dayofyr.'\')*Terms),0) as AvgPurchases,concat(format(((((sum(Amount)/\''.$dayofyr.'\')*Terms)/CreditLimit)*100),1),"%") as `CreditUsage%`,COUNT(TxnSubId) as NoOfInv,if(datediff(curdate(),sm.Date)>Terms,sum(datediff(ifnull(Cleared,curdate()),sm.Date)),sum(datediff(Cleared,sm.Date))) as NoOfDays,format(if(datediff(curdate(),sm.Date)>Terms,sum(datediff(ifnull(Cleared,curdate()),sm.Date)),sum(datediff(Cleared,sm.Date)))/COUNT(TxnSubId),0) AS AvgDaysToPay  from acctg_2salesub ss join acctg_2salemain sm on sm.TxnID=ss.TxnID join 1clients c on c.ClientNo=ss.ClientNo
	left join ClearedPayments cp on (cp.ForChargeInvNo=ss.Particulars and cp.ClientNo=ss.ClientNo)
	WHERE Terms<>0 AND CreditLimit<>0 AND (Terms<>1 and CreditLimit<>1) Group By ss.ClientNo';
	$columnnames=array('ClientNo','ClientName','Terms','CreditLimit','ACL','PurchasesToDate','AvgPurchases','CreditUsage%','AvgDaysToPay');

	include('../backendphp/layout/displayastable.php');
	
//last year	
if($lastyr==date('Y')){
$dayofyr=date('z');
}else{
$dayofyr=365;
}	
	$title='';
	$sql1='CREATE TEMPORARY TABLE `'.$lastyr.'ClearedPayments` AS SELECT om.ClientNo,`os`.`ForChargeInvNo`, `m`.`Cleared`, `om`.`CollectNo` AS CollectNo FROM '.$lastyr.'_1rtc.`acctg_2collectmain` `om`
    JOIN '.$lastyr.'_1rtc.`acctg_2collectsub` `os` ON `om`.`TxnID` = `os`.`TxnID`
    JOIN '.$lastyr.'_1rtc.`acctg_2depositsub` `s` ON `s`.`CRNo` = CONCAT("C-",om.BranchSeriesNo,"-",`om`.`CollectNo`)
    AND IF((ISNULL(`om`.`CheckNo`) OR (`om`.`CheckNo` LIKE "") OR (om.Type<>2)),"1=1",((`s`.`CheckNo` = `om`.`CheckNo`) AND (`om`.`CheckBank` = `s`.`CheckDraweeBank`)))
    AND (`s`.`BranchNo` = `os`.`BranchNo`) AND`om`.`ClientNo` = `s`.`ClientNo`
    JOIN '.$lastyr.'_1rtc.`acctg_2depositmain` `m` ON `m`.`TxnID` = `s`.`TxnID`
    WHERE
    (`os`.`ForChargeInvNo` IS NOT NULL)
    AND (`os`.`CreditAccountID` = 200)
    AND (`m`.`Cleared` IS NOT NULL)'; 

	$stmt1=$link->prepare($sql1); $stmt1->execute();
	
	$formdesc='<b>'.$lastyr.' Credit Line Usage</b>';
		$sql='Select ss.ClientNo,ClientName,Concat(Terms," days") as Terms,format(CreditLimit,0) as CreditLimit,format((300/Terms)*CreditLimit,0) as ACL,format(sum(Amount),0) as PurchasesToDate,format(((sum(Amount)/\''.$dayofyr.'\')*Terms),0) as AvgPurchases,concat(format(((((sum(Amount)/\''.$dayofyr.'\')*Terms)/CreditLimit)*100),1),"%") as `CreditUsage%`,COUNT(TxnSubId) as NoOfInv,if(datediff(curdate(),sm.Date)>Terms,sum(datediff(ifnull(Cleared,curdate()),sm.Date)),sum(datediff(Cleared,sm.Date))) as NoOfDays,format(if(datediff(curdate(),sm.Date)>Terms,sum(datediff(ifnull(Cleared,curdate()),sm.Date)),sum(datediff(Cleared,sm.Date)))/COUNT(TxnSubId),0) AS AvgDaysToPay  from '.$lastyr.'_1rtc.acctg_2salesub ss join '.$lastyr.'_1rtc.acctg_2salemain sm on sm.TxnID=ss.TxnID join '.$lastyr.'_1rtc.1clients c on c.ClientNo=ss.ClientNo
	left join `'.$lastyr.'ClearedPayments` cp on (cp.ForChargeInvNo=ss.Particulars and cp.ClientNo=ss.ClientNo)
	WHERE Terms<>0 AND CreditLimit<>0 AND (Terms<>1 and CreditLimit<>1) Group By ss.ClientNo';
	// echo $sql; exit();
	$columnnames=array('ClientNo','ClientName','Terms','CreditLimit','ACL','PurchasesToDate','AvgPurchases','CreditUsage%','AvgDaysToPay');

	include('../backendphp/layout/displayastable.php');
//	
	
	
	unset($title,$formdesc,$sql,$columnnames);
	$title='';
	$formdesc='<b>Cash Client with Charge Transactions</b>';
		$sql='Select ss.ClientNo,ClientName,Concat(Terms," days") as Terms,format(CreditLimit,0) as CreditLimit,format((300/Terms)*CreditLimit,0) as ACL,format(sum(Amount),0) as PurchasesToDate,format(((sum(Amount)/\''.$dayofyr.'\')*Terms),0) as AveragePurchases,concat(format(((((sum(Amount)/\''.$dayofyr.'\')*Terms)/CreditLimit)*100),1),"%") as \'%\'  from acctg_2salesub ss join acctg_2salemain sm on sm.TxnID=ss.TxnID join 1clients c on c.ClientNo=ss.ClientNo WHERE (Terms=1 and CreditLimit=1) Group By ss.ClientNo';
	$columnnames=array('ClientNo','ClientName','Terms','CreditLimit','PurchasesToDate');

	include('../backendphp/layout/displayastable.php');
	

break;

}

noform:
      $link=null; $stmt=null;
?>