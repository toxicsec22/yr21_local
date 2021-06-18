<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php'; 
if (!allowedToOpen(71312,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../backendphp/layout/linkstyle.php');

include_once($path.'/acrossyrs/js/reportcharts/mgraphlabel.php'); 
include($path.'/acrossyrs/js/reportcharts/includejscharts.php');
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT BranchNo, Branch FROM 1branches WHERE Active<>0 ORDER BY Branch','BranchNo','Branch','branchlist');
echo comboBox($link,'SELECT CompanyNo, Company FROM 1companies WHERE CompanyNo in (1,2,3,4,5) ORDER BY Company','CompanyNo','Company','companylist');

?>

<?php
					
$echo='';
$which=(!isset($_GET['w'])?'finance':$_GET['w']);
$lwidth='45%'; 

$sql0='CREATE TEMPORARY TABLE `graphreport11` (
  `ReportID` tinyint(4) NOT NULL AUTO_INCREMENT,
  `ReportTitle` varchar(100) DEFAULT NULL,
  `OtherDesc` varchar(20) NOT NULL,
  `Label` varchar(100) NOT NULL,
  `xaxis` varchar(25) NOT NULL,
  `yaxis` varchar(25) NOT NULL,
  `min` tinyint(1)DEFAULT NULL,
   `fllegend1` varchar(25) NOT NULL,
   `fllegend2` varchar(25) NOT NULL,  
   `fllegend5` varchar(25) NOT NULL, 
   `fllegend6` varchar(25) NOT NULL, 
   `fllegend7` varchar(25) NOT NULL, 
  PRIMARY KEY (`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;'; //graph report
$stmt=$link->prepare($sql0);$stmt->execute();

$sql0='CREATE TEMPORARY TABLE `graphboard11` (
  `TxnID` smallint(6) NOT NULL AUTO_INCREMENT,
  `IDNo` smallint(6) DEFAULT NULL,
  `GraphID` tinyint(2) NOT NULL,
  `FilledLine1` varchar(2100) DEFAULT NULL,
  `FilledLine2` varchar(2100) DEFAULT NULL,
  `FilledLine5` varchar(2100) DEFAULT NULL,
  `FilledLine6` varchar(2100) DEFAULT NULL,
  `FilledLine7` varchar(2100) DEFAULT NULL,
 `ReportID` tinyint(4) NOT NULL,
  PRIMARY KEY (`TxnID`),
  UNIQUE KEY `IDNo` (`IDNo`,`GraphID`,`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1'; //graphboard
$stmt=$link->prepare($sql0);$stmt->execute();

	echo '<h3>Financial Performance</h3><br>';
	echo '<form method="post" action="financegraph.php?w=finance">
			Per Branch<input type="text" name="BranchNo" list="branchlist"></input>
			OR Per Company<input type="text" name="CompanyNo" list="companylist"></input>
			<input type="submit" name="submit"> 
			OR <input type="submit" name="ALL" value="ALL BRANCHES"> 
			</br></br></form>';
if(isset($_POST['submit']) OR isset($_POST['ALL'])){
switch ($which){
case 'finance':
$paddingright='padding-bottom:250px;';
$position='position:relative;';
$title='Financial Performance';
echo '<title>'.$title.'</title>';

$montharray="'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'";		
						
if(isset($_POST['submit'])){
	if($_POST['BranchNo']!=null AND $_POST['CompanyNo']==null){
		$branchno=companyandbranchValue($link, '1branches', 'Branch', $_POST['BranchNo'], 'BranchNo');
		$sql0='select BranchNo from 1branches where BranchNo='.$branchno.' ';
		// echo $sql0; exit();
		$stmt=$link->query($sql0); $res=$stmt->fetch();
		$ReportTitle='ReportTitle=""';
		$graphtitle='Branch';
		$select=',Branch';
		
		$leftjoin='LEFT JOIN 1branches b on b.BranchNo=g.IDNo';
	}elseif($_POST['CompanyNo']!=null AND $_POST['BranchNo']==null){
		$companyno=companyandbranchValue($link, '1companies', 'Company', $_POST['CompanyNo'], 'CompanyNo');
		$sql0='select CompanyNo from 1companies where CompanyNo='.$companyno.' ';
		// echo $sql0; exit();
		$stmt=$link->query($sql0); $res=$stmt->fetch();
		$ReportTitle='ReportTitle=""';
		$graphtitle='Company';
		$select=',Company';
		
		$leftjoin='LEFT JOIN 1companies c on c.CompanyNo=g.IDNo';
		
	}
}elseif(isset($_POST['ALL'])){
	// $branchno=companyandbranchValue($link, '1branches', 'Branch', $_POST['BranchNo'], 'BranchNo');
		$sql0='select BranchNo from 1branches where BranchNo=0 ';
		// echo $sql0; exit();
		$stmt=$link->query($sql0); $res=$stmt->fetch();
		$ReportTitle='ReportTitle=""';
		$graphtitle='ALL';
		$select=',\'ALL BRANCHES\' as `ALL`';
		
		$leftjoin='LEFT JOIN 1branches b on b.BranchNo=g.IDNo';
	
}


$sqlunion='CREATE TEMPORARY TABLE gen_info_1financereportsUNION AS ';
// foreach($res as $field){	
	$filledline1=''; 
	$filledline2='';
	$filledline5='';
	$filledline6='';
	$filledline7='';
	if(isset($_POST['submit'])){
		//PerBranch
		if($_POST['BranchNo']!=null){
			$conditions='b.BranchNo='.$branchno.'';
			$name=$res['BranchNo'];
		}elseif($_POST['CompanyNo']!=null){
			$conditions='b.CompanyNo='.$companyno.'';
			$name=$res['CompanyNo'];
		}
	}elseif(isset($_POST['ALL'])){
		$conditions='b.CompanyNo in (1,2,3,4,5)';
		$name=$res['BranchNo'];
	}
		
	for ($i = 1; $i <= 12; $i++) {
		$i=(strlen($i)<>2?'0'.$i:$i);
		
		//Expenses
		$sqle='select ifnull(truncate(sum(Amount),2),0) as Expenses from acctg_1chartofaccounts ca join '.$currentyr.'_static.acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where '.$conditions.' AND AccountType in (210,200,201,220,230,150,240,250)  AND month(Date)='.$i.' '; //echo $sql0.'<br>';
		// echo $sqle; exit();
		$stmte=$link->query($sqle); $rese=$stmte->fetch();
		if($stmte->rowCount()==0){
			$filledline1.='0,';
		} else {
			$filledline1.=$rese['Expenses'].',';
		}
		
		//GrossProfit
		$sqlgp='select ifnull(truncate(sum(Amount*-1),2),0) as GrossProfit from acctg_1chartofaccounts ca join '.$currentyr.'_static.acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where '.$conditions.' AND (AccountType=100 or AccountType=101 or ut.AccountID=810) AND month(Date)='.$i.' '; //echo $sql0.'<br>';
		// echo $sqlold; exit();
		$stmtgp=$link->query($sqlgp); $resgp=$stmtgp->fetch();
		if($stmtgp->rowCount()==0){
			$filledline2.='0,';
		} else {
			$filledline2.=$resgp['GrossProfit'].',';
		}
		
		//NetSales
		$sqlns='select ifnull(truncate(sum(Amount*-1),2),0) as NetSales from acctg_1chartofaccounts ca join '.$currentyr.'_static.acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where  '.$conditions.' AND (AccountType=100 or ut.AccountID=810) AND month(Date)='.$i.' '; //echo $sql0.'<br>';
		// echo $sqlold; exit();
		$stmtns=$link->query($sqlns); $resns=$stmtns->fetch();
		if($stmtns->rowCount()==0){
			$filledline5.='0,';
		} else {
			$filledline5.=$resns['NetSales'].',';
		}
		
		//COGS
		$sqlcogs='select ifnull(truncate(sum(Amount),2),0) as COGS from acctg_1chartofaccounts ca join '.$currentyr.'_static.acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where  '.$conditions.' AND AccountType=101 AND month(Date)='.$i.' '; //echo $sql0.'<br>';
		// echo $sqlold; exit();
		$stmtcogs=$link->query($sqlcogs); $rescogs=$stmtcogs->fetch();
		if($stmtcogs->rowCount()==0){
			$filledline7.='0,';
		} else {
			$filledline7.=$rescogs['COGS'].',';
		}
		
		
		//Table Union
		$sqlunion.='SELECT '.$i.' AS MonthNo,1 as type,1 as typename, ifnull(sum(ut.Amount),0) AS total FROM 1branches b left join '.$currentyr.'_static.acctg_0unialltxns ut on ut.BranchNo=b.BranchNo left join acctg_1chartofaccounts ca  on ca.AccountID=ut.AccountID  where '.$conditions.' AND AccountType in (210,200,201,220,230,150,240,250) AND month(ut.Date)='.$i.' UNION SELECT '.$i.' AS MonthNo,2 as type,2 as typename, ifnull(sum(ut.Amount*-1),0) AS total FROM 1branches b left join '.$currentyr.'_static.acctg_0unialltxns ut on ut.BranchNo=b.BranchNo left join acctg_1chartofaccounts ca  on ca.AccountID=ut.AccountID  where '.$conditions.' AND (AccountType=100 or AccountType=101 or ut.AccountID=810) AND month(ut.Date)='.$i.' UNION SELECT '.$i.' AS MonthNo,3 as type,3 as typename, ifnull(sum(ut.Amount*-1),0) AS total FROM 1branches b left join '.$currentyr.'_static.acctg_0unialltxns ut on ut.BranchNo=b.BranchNo left join acctg_1chartofaccounts ca  on ca.AccountID=ut.AccountID  where '.$conditions.' AND (AccountType=100 or ut.AccountID=810) AND month(ut.Date)='.$i.' UNION SELECT '.$i.' AS MonthNo,4 as type,4 as typename, ifnull(sum(ut.Amount),0) AS total FROM 1branches b left join '.$currentyr.'_static.acctg_0unialltxns ut on ut.BranchNo=b.BranchNo left join acctg_1chartofaccounts ca  on ca.AccountID=ut.AccountID  where '.$conditions.' AND AccountType=101 AND month(ut.Date)='.$i.' UNION SELECT '.$i.' AS MonthNo,5 as type,5 as typename, ifnull(sum(`'.$i.'`),0) AS total FROM 1branches b left join acctg_1yearsalestargets yst on yst.branchno=b.BranchNo where '.$conditions.' AND `'.$i.'`= `'.$i.'` UNION ';
		
	}
	$sqlunion=substr($sqlunion, 0, -6);

	// echo $sqlunion; exit();
	$stmtunion=$link->prepare($sqlunion); $stmtunion->execute();

	$sqltype='SELECT *,(CASE
				WHEN typename=1 THEN "EXPENSES"
				WHEN typename=2 THEN "GROSSPROFIT"
				WHEN typename=3 THEN "NETSALES"
				WHEN typename=4 THEN "COGS"
				WHEN typename=5 THEN "QUOTA" END) AS typename
	FROM gen_info_1financereportsUNION group by type';
	$stmttype=$link->query($sqltype); $rowtype = $stmttype->fetchAll();
	$sql10='';
	foreach ($rowtype as $rows){
					$sql10.=', FORMAT(AVG(CASE WHEN fru.type='.$rows['type'].' THEN IFNULL(fru.`total`,0) END),0) AS `'.$rows['typename'].'`, FORMAT(((AVG(CASE WHEN fru.type=3 THEN IFNULL(fru.`total`,0) END) )/(AVG(CASE WHEN fru.type=5 THEN IFNULL(fru.`total`,0) END))*100),2)  AS ACHIEVEMENT,FORMAT((AVG(CASE WHEN fru.type=2 THEN IFNULL(fru.`total`,0) END) )-(AVG(CASE WHEN fru.type=1 THEN IFNULL(fru.`total`,0) END)),0) AS DIFFERENCE, TRUNCATE(AVG(CASE WHEN fru.type='.$rows['type'].' THEN IFNULL(fru.`total`,0) END),0) AS `'.$rows['type'].'` ';
				}
				
	$sqlmain='SELECT 
				 (CASE
				WHEN MonthNo=1 THEN "Jan"
				WHEN MonthNo=2 THEN "Feb"
				WHEN MonthNo=3 THEN "Mar"
				WHEN MonthNo=4 THEN "Apr"
				WHEN MonthNo=5 THEN "May"
				WHEN MonthNo=6 THEN "Jun"
				WHEN MonthNo=7 THEN "Jul"
				WHEN MonthNo=8 THEN "Aug"
				WHEN MonthNo=9 THEN "Sep"
				WHEN MonthNo=10 THEN "Oct"
				WHEN MonthNo=11 THEN "Nov"
				ELSE "Dec"
				END ) AS MonthNo
				
				'.$sql10.' FROM  gen_info_1financereportsUNION fru GROUP BY MonthNo';	
$stmtmain=$link->query($sqlmain); $rowmain = $stmtmain->fetchAll();				
// echo $sqlmain; exit();		
$totalquota=0;
$totalns=0;	
$totalcogs=0;
$totalgp=0;
$totale=0;
$totala=0;
$totald=0;
	$table='<table style="width:97%;position:absolute; bottom:0; border:1px solid black; font-size:10pt; margin-bottom:5px;  background-color:#f2f2f2;">
	<tr><th>Months</th><th>Quota</th><th>NetSales</th><th>Achievement</th><th>COGS</th><th>GrossProfit</th><th>Expenses</th><th>Difference</th></tr>';
	foreach($rowmain as $mainrow){
		
	$totalquota=$totalquota+$mainrow['5'];
	$totalns=$totalns+$mainrow['3'];
	$totalcogs=$totalcogs+$mainrow['4'];
	$totalgp=$totalgp+$mainrow['2'];
	$totale=$totale+$mainrow['1'];
	
	$totala=($totalns/$totalquota)*100;
	$totald=$totald+($mainrow['2']-$mainrow['1']);
	
	$table.='
	<tr><td>'.$mainrow['MonthNo'].'</td><td>₱ '.$mainrow['QUOTA'].'</td><td>₱ '.$mainrow['NETSALES'].'</td><td>'.$mainrow['ACHIEVEMENT'].'%</td><td>₱ '.$mainrow['COGS'].'</td><td>₱ '.$mainrow['GROSSPROFIT'].'</td><td>₱ '.$mainrow['EXPENSES'].'</td><td>₱ '.$mainrow['DIFFERENCE'].'</td></tr>';
	}
	$table.='<tr><td><b>TOTAL<b></td><td><b>₱ '.number_format($totalquota,0).'<b></td><td><b>₱ '.number_format($totalns,0).'</b></td><td><b>'.number_format($totala,0).'%</b></td><td><b>₱ '.number_format($totalcogs,0).'</b></td><td><b>₱ '.number_format($totalgp,0).'</b></td><td><b>₱ '.number_format($totale,0).'</b></td><td><b>₱ '.number_format($totald,0).'</b></td></tr></table>';
	
	$filledline1=substr($filledline1, 0, -1);
	$filledline2=substr($filledline2, 0, -1);
	$filledline5=substr($filledline5, 0, -1);


	//Quota
	$sqlq='SELECT sum(`01`) as `01`,sum(`02`) as `02`,sum(`03`) as `03`,sum(`04`) as `04`,sum(`05`) as `05`,sum(`06`) as `06`,sum(`07`) as `07`,sum(`08`) as `08`,sum(`09`) as `09`,sum(`10`) as `10`,sum(`11`) as `11`,sum(`12`) as `12` FROM acctg_1yearsalestargets yst join 1branches b on b.BranchNo=yst.branchno where '.$conditions.'';
	// echo $sqlq; exit();
	$stmtq=$link->query($sqlq);$resultq=$stmtq->fetch(); 
	
	if($stmtq->rowCount()==1 and $resultq['01']==null and $resultq['02']==null and $resultq['03']==null and $resultq['04']==null and $resultq['05']==null and $resultq['06']==null and $resultq['07']==null and $resultq['08']==null and $resultq['09']==null and $resultq['10']==null and $resultq['11']==null and $resultq['12']==null){
		$filledline6.='0,0,0,0,0,0,0,0,0,0,0,0';
	}else{
	$filledline6.=''.$resultq['01'].','.$resultq['02'].','.$resultq['03'].','.$resultq['04'].','.$resultq['05'].','.$resultq['06'].','.$resultq['07'].','.$resultq['08'].','.$resultq['09'].','.$resultq['10'].','.$resultq['11'].','.$resultq['12'].'';
	}
	// echo $filledline6; exit();
	

	$sqlcsub='INSERT INTO graphboard11 SET GraphID=2,FilledLine1="'.$filledline1.'",FilledLine2="'.$filledline2.'",FilledLine5="'.$filledline5.'",FilledLine6="'.$filledline6.'",FilledLine7="'.$filledline7.'",IDNo='.$name.',ReportID=5;';
	$stmt=$link->prepare($sqlcsub);$stmt->execute(); 

// }//hide

$sqlcmain='INSERT INTO graphreport11 SET xaxis="Branch",fllegend1="Expenses",fllegend2="Gross Profit",fllegend5="NetSales",fllegend6="Quota",fllegend7="COGS",yaxis="In Millions",min="",'.$ReportTitle.',ReportID=5,Label="'.$montharray.'"';
$stmt=$link->prepare($sqlcmain);$stmt->execute();

$sql = 'SELECT g.*,gr.*'.$select.' FROM graphboard11 g '.$leftjoin.' JOIN graphreport11 gr ON g.ReportID=gr.ReportID WHERE g.ReportID IN (5) ';
$stmt=$link->query($sql); $res=$stmt->fetchall();

$sqldrop='DROP TEMPORARY TABLE graphboard11';
$stmt=$link->prepare($sqldrop);$stmt->execute();

$sqldrop='DROP TEMPORARY TABLE graphreport11';
$stmt=$link->prepare($sqldrop);$stmt->execute();

break;

	
}

$c=1;
$displaydiv=''; $newdiv=''; 
foreach ($res as $field) {
	 if ($field['GraphID']==2){
		include($path.'/acrossyrs/js/reportcharts/line.php');
	 }
$c++;	 
 
} 
	echo $displaydiv;
	echo '<script>';
	echo 'window.onload = function() {';
	echo $echo;
	echo '}';	
	echo '</script>';
}
?>