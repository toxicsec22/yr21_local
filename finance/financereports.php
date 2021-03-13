<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
$showbranches=false;
if (!allowedToOpen(5904,'1rtc')) { echo 'No permission'; exit();}
include_once('../switchboard/contents.php');
$dbtouse=$link; 
if(!isset($_GET['w'])){
	echo'<title>Finance Reports</title>';	
}
$which=(!isset($_GET['w'])?'':$_GET['w']);
include_once('../backendphp/layout/linkstyle.php');
	  ?>		
<!--buttons --></br>
    <div>
    <font size=4 face='sans-serif'>
        <a id="link" href='financereports.php?w=quota'>Target</a><?php echo str_repeat('&nbsp',5)?> 
		<a id="link" href='financereports.php?w=netsales'>Net Sales</a><?php echo str_repeat('&nbsp',5)?>
		<a id='link' href='financereports.php?w=cogs'>Cost Of Good Sold</a><?php echo str_repeat('&nbsp',5)?>
		<a id='link' href='financereports.php?w=grossprofit'>Gross Profit</a><?php echo str_repeat('&nbsp',5)?>
        <a id="link" href='financereports.php?w=expenses'>Expenses With Depn</a><?php echo str_repeat('&nbsp',5)?> 
		<a id="link" href='financereports.php?w=expenseswo'>Depreciation</a><?php echo str_repeat('&nbsp',5)?>  
		<a id='link' href='financereports.php?w=soh'>Share in OverHead</a><?php echo str_repeat('&nbsp',5)?>
		<a id='link' href='financereports.php?w=netincome'>Net Income</a><?php echo str_repeat('&nbsp',5)?>
		<a id='link' href='financereports.php?w=netsalesvsnetincome'>Net Sales vs Net Income</a><?php echo str_repeat('&nbsp',5)?>
    
    </font></div><br>
    <?php
		$monthdate=date('m',strtotime($_SESSION['nb4A']));
		// echo $monthdate; exit();	
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT BranchNo, Branch FROM 1branches WHERE Active<>0 ORDER BY Branch','BranchNo','Branch','branchlist');
echo comboBox($link,'SELECT CompanyNo, CompanyName FROM 1companies  ORDER BY CompanyNo','CompanyNo','CompanyName','companylist');		
	
switch ($which){
	case'netincome':
	case'netsales':
	case'cogs':
	case 'expenses':
	case 'soh':
	case 'expenseswo':
	if(isset($_GET['w'])){
	if($_GET['w']=='expenses'){
		$accountype='AccountType in (210,200,201,220,230,150,240)';
		$sumamount='sum(ut.Amount)';
		$title='Expenses With Depn';
	}elseif($_GET['w']=='expenseswo'){
		$accountype='AccountType=200 and OrderNo=3';
		$sumamount='sum(ut.Amount)';
		$title='Depreciation';
	}elseif($_GET['w']=='netsales'){
		$accountype='(AccountType=100 or ut.AccountID=810)';
		$sumamount='sum(ut.Amount*-1)';
		$title='NetSales';
	}elseif($_GET['w']=='cogs'){
		$accountype='AccountType=101';
		$sumamount='sum(ut.Amount)';
		$title='Cost of Good Sold';
	}elseif($_GET['w']=='soh'){
		$accountype='AccountType=240';
		$sumamount='sum(ut.Amount)';
		$title='Share in OverHead';
		
	}elseif($_GET['w']=='netincome'){
		$accountype='AccountType in(100,101,150,201,200,210,220,230,240,250) and month(Date)<=\''.$monthdate.'\'';
		$sumamount='sum(ut.Amount*-1)';
		$title='Net Income';
		
	}
	}else{
		$accountype='AccountType in (210,200,201,220,230,150,240,250)';
		$sumamount='sum(ut.Amount)';
		$title='Expenses';
	}
	echo '<title>'.$title.'</title><h3>'.$title.'</h3>';
//start enablebasedonradio	
			$radionamefield='radiolist';	
			echo '</br><form id="form-id">
			Per Branch <input type="radio" id="watch-me1" name="'.$radionamefield.'">
			Per Company <input type="radio" id="watch-me2" name="'.$radionamefield.'">
			All <input type="radio" id="watch-me3" name="'.$radionamefield.'">
			</form>
			</br>';
			$formaction='<form method="post" action="financereports.php?w='.$_GET['w'].'">';
			$all='<input type="hidden" name="All">';
			$branchinput='Branch <input type="text" name="BranchNo" list="branchlist">';
			$companyinput='Company <input type="text" name="CompanyNo" list="companylist">';
			
			//perbranch
			echo '<div id="show-me1" style="display:none">
					'.$formaction.'
					'.$branchinput.'
					<input type="submit" name="submit"> 
				</form>
				</div>';
			
			//per company
			echo '<div id="show-me2" style="display:none">
					'.$formaction.'
					'.$companyinput.'
					<input type="submit" name="submit"> 
				</form>
				</div>';
			
			//all
			echo '<div id="show-me3" style="display:none">
					'.$formaction.'
					'.$all.'
					<input type="submit" name="submit"> 
				</form>
				</div>';				
			
			include $path.'/acrossyrs/commonfunctions/enablebasedonradio.php';	
//end
if(isset($_POST['submit'])){
	
	if(isset($_POST['All'])){
					$condition='';
					echo '<b>All</b>';
					$columnnames=array('Branch','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','YrTotal');
				}elseif(isset($_POST['BranchNo'])){
					$branchno=companyandbranchValue($link, '1branches', 'Branch', $_POST['BranchNo'], 'BranchNo');
					$condition='AND b.BranchNo='.$branchno.'';
					echo '<b>'.$_POST['BranchNo'].'</b>';
					$columnnames=array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','YrTotal');
					$hidecount=true;
				}elseif(isset($_POST['CompanyNo'])){
					$companyno=companyandbranchValue($link, '1companies', 'CompanyName', $_POST['CompanyNo'], 'CompanyNo');	
					$condition='AND CompanyNo='.$companyno.'';
					echo '<b>'.$_POST['CompanyNo'].'</b>';
					$columnnames=array('Branch','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','YrTotal');
				}
	
	$title='';
		$sql='CREATE TEMPORARY TABLE gen_info_1financereportsUNION AS ';
			$fmn=1; $tmn=12;
			while($fmn<=$tmn){
				$sql.='SELECT Branch AS ColName, b.BranchNo,'.$fmn.' AS MonthName, '.$sumamount.' AS ColSum FROM 1branches b  left join acctg_0unialltxns ut on ut.BranchNo=b.BranchNo left join acctg_1chartofaccounts ca  on ca.AccountID=ut.AccountID  where '.$accountype.' AND month(ut.Date)='.$fmn.' '.$condition.'  GROUP BY BranchNo,MonthName UNION ';
				$fmn++;
			}
			$sql=substr($sql, 0, -6);
			// echo $sql; exit();
			$stmt1=$link->prepare($sql); $stmt1->execute();
				
				$sql='SELECT ColName AS Branch,BranchNo,FORMAT(SUM(ColSum),0) AS YrTotal,sum(ColSum) as YrTotalValue,
				FORMAT(AVG(CASE WHEN fru.MonthName=1 THEN IFNULL(fru.`ColSum`,0) END),0) AS Jan,
				FORMAT(AVG(CASE WHEN fru.MonthName=2 THEN IFNULL(fru.`ColSum`,0) END),0) AS Feb,
				FORMAT(AVG(CASE WHEN fru.MonthName=3 THEN IFNULL(fru.`ColSum`,0) END),0) AS Mar,
				FORMAT(AVG(CASE WHEN fru.MonthName=4 THEN IFNULL(fru.`ColSum`,0) END),0) AS Apr,
				FORMAT(AVG(CASE WHEN fru.MonthName=5 THEN IFNULL(fru.`ColSum`,0) END),0) AS May,
				FORMAT(AVG(CASE WHEN fru.MonthName=6 THEN IFNULL(fru.`ColSum`,0) END),0) AS Jun,
				FORMAT(AVG(CASE WHEN fru.MonthName=7 THEN IFNULL(fru.`ColSum`,0) END),0) AS Jul,
				FORMAT(AVG(CASE WHEN fru.MonthName=8 THEN IFNULL(fru.`ColSum`,0) END),0) AS Aug,
				FORMAT(AVG(CASE WHEN fru.MonthName=9 THEN IFNULL(fru.`ColSum`,0) END),0) AS Sep,
				FORMAT(AVG(CASE WHEN fru.MonthName=10 THEN IFNULL(fru.`ColSum`,0) END),0) AS Oct,
				FORMAT(AVG(CASE WHEN fru.MonthName=11 THEN IFNULL(fru.`ColSum`,0) END),0) AS Nov,
				FORMAT(AVG(CASE WHEN fru.MonthName=12 THEN IFNULL(fru.`ColSum`,0) END),0) AS \'Dec\'
				FROM  gen_info_1financereportsUNION fru GROUP BY BranchNo ORDER BY ColName;';
				
				// echo $sql; exit();
				// $coltototal='YrTotalValue';
				// $showgrandtotal='true';
				include('../backendphp/layout/displayastablenosort.php');
				unset($title);
				$title='';
					$sql2='CREATE TEMPORARY TABLE TOTAL AS ';
			$c=1; $c1=12;
			while($c<=$c1){
				$sql2.='SELECT Branch AS ColName, b.BranchNo,'.$c.' AS MonthName, '.$sumamount.' AS ColSum FROM 1branches b  left join acctg_0unialltxns ut on ut.BranchNo=b.BranchNo left join acctg_1chartofaccounts ca  on ca.AccountID=ut.AccountID  where '.$accountype.' AND month(ut.Date)='.$c.' '.$condition.' UNION ';
				$c++;
			}
			$sql2=substr($sql2, 0, -6);
			// echo $sql2; exit();
			$stmt2=$link->prepare($sql2); $stmt2->execute();
			
			if($_GET['w']=='netsales'){
			$sqltns='SELECT sum(ColSum) as GrandYrTotalValue,
				AVG(CASE WHEN t.MonthName=1 THEN IFNULL(t.`ColSum`,0) END) AS JanTotal,
				AVG(CASE WHEN t.MonthName=2 THEN IFNULL(t.`ColSum`,0) END) AS FebTotal,
				AVG(CASE WHEN t.MonthName=3 THEN IFNULL(t.`ColSum`,0) END) AS MarTotal,
				AVG(CASE WHEN t.MonthName=4 THEN IFNULL(t.`ColSum`,0) END) AS AprTotal,
				AVG(CASE WHEN t.MonthName=5 THEN IFNULL(t.`ColSum`,0) END) AS MayTotal,
				AVG(CASE WHEN t.MonthName=6 THEN IFNULL(t.`ColSum`,0) END) AS JunTotal,
				AVG(CASE WHEN t.MonthName=7 THEN IFNULL(t.`ColSum`,0) END) AS JulTotal,
				AVG(CASE WHEN t.MonthName=8 THEN IFNULL(t.`ColSum`,0) END) AS AugTotal,
				AVG(CASE WHEN t.MonthName=9 THEN IFNULL(t.`ColSum`,0) END) AS SepTotal,
				AVG(CASE WHEN t.MonthName=10 THEN IFNULL(t.`ColSum`,0) END) AS OctTotal,
				AVG(CASE WHEN t.MonthName=11 THEN IFNULL(t.`ColSum`,0) END) AS NovTotal,
				AVG(CASE WHEN t.MonthName=12 THEN IFNULL(t.`ColSum`,0) END) AS \'DecTotal\'
				FROM  TOTAL t ';
			$stmttns=$link->query($sqltns); $resulttns=$stmttns->fetch();
			
			//Create temp table
			$sqltable='Create temporary table TOTALS (TxnID int(11) AUTO_INCREMENT,
			PRIMARY KEY (`TxnID`),ColTotal Varchar(255),Jan Double,Feb Double,Mar Double,Apr Double,May Double,Jun Double,Jul Double,Aug Double,Sep Double,Oct Double,Nov Double,`Dec` Double,GrandTotal Double)';
			// echo $sqltable; exit();
			$stmttable=$link->prepare($sqltable); $stmttable->execute();
			//insert total ns
			$sqlitable='INSERT INTO TOTALS set ColTotal=\'NetSales\',Jan=\''.$resulttns['JanTotal'].'\',Feb=\''.$resulttns['FebTotal'].'\',Mar=\''.$resulttns['MarTotal'].'\',Apr=\''.$resulttns['AprTotal'].'\',May=\''.$resulttns['MayTotal'].'\',Jun=\''.$resulttns['JunTotal'].'\',Jul=\''.$resulttns['JulTotal'].'\',Aug=\''.$resulttns['AugTotal'].'\',Sep=\''.$resulttns['SepTotal'].'\',Oct=\''.$resulttns['OctTotal'].'\',Nov=\''.$resulttns['NovTotal'].'\',`Dec`=\''.$resulttns['DecTotal'].'\',GrandTotal=\''.$resulttns['GrandYrTotalValue'].'\' ';
			$stmtitable=$link->prepare($sqlitable); $stmtitable->execute();
			//select quota
			$sqlqt='select sum(`01`) as JanTotal,sum(`02`) as FebTotal,sum(`03`) as MarTotal,sum(`04`) as AprTotal,sum(`05`) as MayTotal,sum(`06`) JunTotal,sum(`07`) as JulTotal,sum(`08`) as AugTotal,sum(`09`) as SepTotal,sum(`10`) as OctTotal,sum(`11`) as NovTotal,sum(`12`) as \'DecTotal\',(sum(`01`)+sum(`02`)+sum(`03`)+sum(`04`)+sum(`05`)+sum(`06`)+sum(`07`)+sum(`08`)+sum(`09`)+sum(`10`)+sum(`11`)+sum(`12`)) as GrandYrTotalValue from acctg_1yearsalestargets yst ';
			$stmtqt=$link->query($sqlqt); $resultqt=$stmtqt->fetch();
			//insert totaltarget
			$sqlitable='INSERT INTO TOTALS set ColTotal=\'Target\',Jan=\''.$resultqt['JanTotal'].'\',Feb=\''.$resultqt['FebTotal'].'\',Mar=\''.$resultqt['MarTotal'].'\',Apr=\''.$resultqt['AprTotal'].'\',May=\''.$resultqt['MayTotal'].'\',Jun=\''.$resultqt['JunTotal'].'\',Jul=\''.$resultqt['JulTotal'].'\',Aug=\''.$resultqt['AugTotal'].'\',Sep=\''.$resultqt['SepTotal'].'\',Oct=\''.$resultqt['OctTotal'].'\',Nov=\''.$resultqt['NovTotal'].'\',`Dec`=\''.$resultqt['DecTotal'].'\',GrandTotal=\''.$resultqt['GrandYrTotalValue'].'\' ';
			$stmtitable=$link->prepare($sqlitable); $stmtitable->execute();
			// % to target
			$tjan=($resulttns['JanTotal']/$resultqt['JanTotal'])*100; $tfeb=($resulttns['FebTotal']/$resultqt['FebTotal'])*100;
			$tmar=($resulttns['MarTotal']/$resultqt['MarTotal'])*100; $tapr=($resulttns['AprTotal']/$resultqt['AprTotal'])*100;
			$tmay=($resulttns['MayTotal']/$resultqt['MayTotal'])*100; $tjun=($resulttns['JunTotal']/$resultqt['JunTotal'])*100;
			$tjul=($resulttns['JulTotal']/$resultqt['JulTotal'])*100; $taug=($resulttns['AugTotal']/$resultqt['AugTotal'])*100;
			$tsep=($resulttns['SepTotal']/$resultqt['SepTotal'])*100; $toct=($resulttns['OctTotal']/$resultqt['OctTotal'])*100;
			$tnov=($resulttns['NovTotal']/$resultqt['NovTotal'])*100; $tdec=($resulttns['DecTotal']/$resultqt['DecTotal'])*100;
			$tgrand=($resulttns['GrandYrTotalValue']/$resultqt['GrandYrTotalValue'])*100; 
			//insert % to target
			$sqlitable='INSERT INTO TOTALS set ColTotal=\'% to target\',Jan=\''.$tjan.'\',Feb=\''.$tfeb.'\',Mar=\''.$tmar.'\',Apr=\''.$tapr.'\',May=\''.$tmay.'\',Jun=\''.$tjun.'\',Jul=\''.$tjul.'\',Aug=\''.$taug.'\',Sep=\''.$tsep.'\',Oct=\''.$toct.'\',Nov=\''.$tnov.'\',`Dec`=\''.$tdec.'\',GrandTotal=\''.$tgrand.'\' ';
			$stmtitable=$link->prepare($sqlitable); $stmtitable->execute();

			$sql='select ColTotal as Totals,if(ColTotal=\'% to target\',(concat(format(jan,2),"%")),(format(Jan,2))) as Jan,if(ColTotal=\'% to target\',(concat(format(Feb,2),"%")),(format(Feb,2))) as Feb,if(ColTotal=\'% to target\',(concat(format(Mar,2),"%")),(format(Mar,2))) as Mar,if(ColTotal=\'% to target\',(concat(format(Apr,2),"%")),(format(Apr,2))) as Apr,if(ColTotal=\'% to target\',(concat(format(May,2),"%")),(format(May,2))) as May,if(ColTotal=\'% to target\',(concat(format(Jun,2),"%")),(format(Jun,2))) as Jun,if(ColTotal=\'% to target\',(concat(format(Jul,2),"%")),(format(Jul,2))) as Jul,if(ColTotal=\'% to target\',(concat(format(Aug,2),"%")),(format(Aug,2))) as Aug,if(ColTotal=\'% to target\',(concat(format(Sep,2),"%")),(format(Sep,2))) as Sep,if(ColTotal=\'% to target\',(concat(format(Oct,2),"%")),(format(Oct,2))) as Oct,if(ColTotal=\'% to target\',(concat(format(Nov,2),"%")),(format(Nov,2))) as Nov,if(ColTotal=\'% to target\',(concat(format(`Dec`,2),"%")),(format(`Dec`,2))) as `Dec`,if(ColTotal=\'% to target\',(concat(format(GrandTotal,2),"%")),(format(GrandTotal,2))) as GrandTotal from TOTALS';
			
			$title='';
			$columnnames=array('Totals','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','GrandTotal');
			// unset($showgrandtotal);
			// unset($coltototal);
			$hidecount=true;
				include('../backendphp/layout/displayastablenosort.php');
			}else{
			if(!isset($_POST['BranchNo'])){	
				$sql='SELECT FORMAT(SUM(ColSum),0) AS GrandYrTotal,sum(ColSum) as GrandYrTotalValue,
				FORMAT(AVG(CASE WHEN t.MonthName=1 THEN IFNULL(t.`ColSum`,0) END),0) AS JanTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=2 THEN IFNULL(t.`ColSum`,0) END),0) AS FebTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=3 THEN IFNULL(t.`ColSum`,0) END),0) AS MarTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=4 THEN IFNULL(t.`ColSum`,0) END),0) AS AprTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=5 THEN IFNULL(t.`ColSum`,0) END),0) AS MayTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=6 THEN IFNULL(t.`ColSum`,0) END),0) AS JunTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=7 THEN IFNULL(t.`ColSum`,0) END),0) AS JulTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=8 THEN IFNULL(t.`ColSum`,0) END),0) AS AugTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=9 THEN IFNULL(t.`ColSum`,0) END),0) AS SepTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=10 THEN IFNULL(t.`ColSum`,0) END),0) AS OctTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=11 THEN IFNULL(t.`ColSum`,0) END),0) AS NovTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=12 THEN IFNULL(t.`ColSum`,0) END),0) AS \'DecTotal\'
				FROM  TOTAL t ';
			$title='';
			$columnnames=array('JanTotal','FebTotal','MarTotal','AprTotal','MayTotal','JunTotal','JulTotal','AugTotal','SepTotal','OctTotal','NovTotal','DecTotal','GrandYrTotal');
			// unset($showgrandtotal);
			// unset($coltototal);
				include('../backendphp/layout/displayastablenosort.php');
			}
			}
}			
	break; 
	
	case'grossprofit':
		$accountype='(AccountType=100 or AccountType=101 or ut.AccountID=810)';
		$sumamount='sum(ut.Amount*-1)';
		$title='Gross Profit';
		echo '<title>'.$title.'</title><h3>'.$title.'</h3>';
		//start enablebasedonradio	
			$radionamefield='radiolist';	
			echo '</br><form id="form-id">
			Per Branch <input type="radio" id="watch-me1" name="'.$radionamefield.'">
			Per Company <input type="radio" id="watch-me2" name="'.$radionamefield.'">
			All <input type="radio" id="watch-me3" name="'.$radionamefield.'">
			</form>
			</br>';
			$formaction='<form method="post" action="financereports.php?w='.$_GET['w'].'">';
			$all='<input type="hidden" name="All">';
			$branchinput='Branch <input type="text" name="BranchNo" list="branchlist">';
			$companyinput='Company <input type="text" name="CompanyNo" list="companylist">';
			
			//perbranch
			echo '<div id="show-me1" style="display:none">
					'.$formaction.'
					'.$branchinput.'
					<input type="submit" name="submit"> 
				</form>
				</div>';
			
			//per company
			echo '<div id="show-me2" style="display:none">
					'.$formaction.'
					'.$companyinput.'
					<input type="submit" name="submit"> 
				</form>
				</div>';
			
			//all
			echo '<div id="show-me3" style="display:none">
					'.$formaction.'
					'.$all.'
					<input type="submit" name="submit"> 
				</form>
				</div>';				
			
			include $path.'/acrossyrs/commonfunctions/enablebasedonradio.php';	
//end
if(isset($_POST['submit'])){
	
	if(isset($_POST['All'])){
					$condition='';
					echo '<b>All</b>';
					$columnnames=array('Branch','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','YrTotal');
				}elseif(isset($_POST['BranchNo'])){
					$branchno=companyandbranchValue($link, '1branches', 'Branch', $_POST['BranchNo'], 'BranchNo');
					$condition='AND b.BranchNo='.$branchno.'';
					echo '<b>'.$_POST['BranchNo'].'</b>';
					$hidecount=true;
					$columnnames=array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','YrTotal');
				}elseif(isset($_POST['CompanyNo'])){
					$companyno=companyandbranchValue($link, '1companies', 'CompanyName', $_POST['CompanyNo'], 'CompanyNo');	
					$condition='AND CompanyNo='.$companyno.'';
					echo '<b>'.$_POST['CompanyNo'].'</b>';
					$columnnames=array('Branch','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','YrTotal');
				}
	
	$title='';
		$sql='CREATE TEMPORARY TABLE GrossProfit AS ';
			$fmn=1; $tmn=12;
			while($fmn<=$tmn){
				$sql.='SELECT Branch AS ColName, b.BranchNo,'.$fmn.' AS MonthName, '.$sumamount.' AS ColSum FROM 1branches b  left join acctg_0unialltxns ut on ut.BranchNo=b.BranchNo left join acctg_1chartofaccounts ca  on ca.AccountID=ut.AccountID  where '.$accountype.' AND month(ut.Date)='.$fmn.' '.$condition.'  GROUP BY BranchNo,MonthName UNION ';
				$fmn++;
			}
			$sql=substr($sql, 0, -6);
			// echo $sql; exit();
			$stmt1=$link->prepare($sql); $stmt1->execute();
			
			$sql='SELECT ColName AS Branch,BranchNo,FORMAT(SUM(ColSum),2) AS YrTotal,sum(ColSum) as YrTotalValue,
				FORMAT(AVG(CASE WHEN fru.MonthName=1 THEN IFNULL(fru.`ColSum`,0) END),2) AS Jan,
				FORMAT(AVG(CASE WHEN fru.MonthName=2 THEN IFNULL(fru.`ColSum`,0) END),2) AS Feb,
				FORMAT(AVG(CASE WHEN fru.MonthName=3 THEN IFNULL(fru.`ColSum`,0) END),2) AS Mar,
				FORMAT(AVG(CASE WHEN fru.MonthName=4 THEN IFNULL(fru.`ColSum`,0) END),2) AS Apr,
				FORMAT(AVG(CASE WHEN fru.MonthName=5 THEN IFNULL(fru.`ColSum`,0) END),2) AS May,
				FORMAT(AVG(CASE WHEN fru.MonthName=6 THEN IFNULL(fru.`ColSum`,0) END),2) AS Jun,
				FORMAT(AVG(CASE WHEN fru.MonthName=7 THEN IFNULL(fru.`ColSum`,0) END),2) AS Jul,
				FORMAT(AVG(CASE WHEN fru.MonthName=8 THEN IFNULL(fru.`ColSum`,0) END),2) AS Aug,
				FORMAT(AVG(CASE WHEN fru.MonthName=9 THEN IFNULL(fru.`ColSum`,0) END),2) AS Sep,
				FORMAT(AVG(CASE WHEN fru.MonthName=10 THEN IFNULL(fru.`ColSum`,0) END),2) AS Oct,
				FORMAT(AVG(CASE WHEN fru.MonthName=11 THEN IFNULL(fru.`ColSum`,0) END),2) AS Nov,
				FORMAT(AVG(CASE WHEN fru.MonthName=12 THEN IFNULL(fru.`ColSum`,0) END),2) AS \'Dec\'
				FROM  GrossProfit fru GROUP BY BranchNo ORDER BY ColName;';
				
				// echo $sql; exit();
				// $coltototal='YrTotalValue';
				// $showgrandtotal='true';
				include('../backendphp/layout/displayastablenosort.php');
				unset($title);
				
			$title='';
					$sql2='CREATE TEMPORARY TABLE TOTAL AS ';
			$c=1; $c1=12;
			while($c<=$c1){
				$sql2.='SELECT Branch AS ColName, b.BranchNo,'.$c.' AS MonthName, '.$sumamount.' AS ColSum FROM 1branches b  left join acctg_0unialltxns ut on ut.BranchNo=b.BranchNo left join acctg_1chartofaccounts ca  on ca.AccountID=ut.AccountID  where '.$accountype.' AND month(ut.Date)='.$c.' '.$condition.' UNION ';
				$c++;
			}
			$sql2=substr($sql2, 0, -6);
			// echo $sql2; exit();
			$stmt2=$link->prepare($sql2); $stmt2->execute();
//totals
		$sql1='CREATE TEMPORARY TABLE NetSales AS ';
			$fmn=1; $tmn=12;
			while($fmn<=$tmn){
				$sql1.='SELECT Branch AS ColName, b.BranchNo,'.$fmn.' AS MonthName, sum(ut.Amount*-1) AS ColSum FROM 1branches b  left join acctg_0unialltxns ut on ut.BranchNo=b.BranchNo left join acctg_1chartofaccounts ca  on ca.AccountID=ut.AccountID  where (AccountType=100 or ut.AccountID=810) AND month(ut.Date)='.$fmn.' '.$condition.'  GROUP BY BranchNo,MonthName UNION ';
				$fmn++;
			}
			$sql1=substr($sql1, 0, -6);
			// echo $sql1; exit();
			$stmt1=$link->prepare($sql1); $stmt1->execute();	

$sql3='select \'GrossProfit\' as Totals,
				sum(CASE WHEN gp.MonthName=1 THEN IFNULL(gp.`ColSum`,0) END) AS Jan,
				sum(CASE WHEN gp.MonthName=2 THEN IFNULL(gp.`ColSum`,0) END) AS Feb,
				sum(CASE WHEN gp.MonthName=3 THEN IFNULL(gp.`ColSum`,0) END) AS Mar,
				sum(CASE WHEN gp.MonthName=4 THEN IFNULL(gp.`ColSum`,0) END) AS Apr,
				sum(CASE WHEN gp.MonthName=5 THEN IFNULL(gp.`ColSum`,0) END) AS May,
				sum(CASE WHEN gp.MonthName=6 THEN IFNULL(gp.`ColSum`,0) END) AS Jun,
				sum(CASE WHEN gp.MonthName=7 THEN IFNULL(gp.`ColSum`,0) END) AS Jul,
				sum(CASE WHEN gp.MonthName=8 THEN IFNULL(gp.`ColSum`,0) END) AS Aug,
				sum(CASE WHEN gp.MonthName=9 THEN IFNULL(gp.`ColSum`,0) END) AS Sep,
				sum(CASE WHEN gp.MonthName=10 THEN IFNULL(gp.`ColSum`,0) END) AS Oct,
				sum(CASE WHEN gp.MonthName=11 THEN IFNULL(gp.`ColSum`,0) END) AS Nov,
				sum(CASE WHEN gp.MonthName=12 THEN IFNULL(gp.`ColSum`,0) END) AS `Dec`,
				sum(gp.`ColSum`) as GrandTotal
				from GrossProfit gp';
$stmt3=$link->query($sql3); $result3=$stmt3->fetch();

$sql4='select \'NetSales\' as Totals,
				sum(CASE WHEN ns.MonthName=1 THEN IFNULL(ns.`ColSum`,0) END) AS Jan,
				sum(CASE WHEN ns.MonthName=2 THEN IFNULL(ns.`ColSum`,0) END) AS Feb,
				sum(CASE WHEN ns.MonthName=3 THEN IFNULL(ns.`ColSum`,0) END) AS Mar,
				sum(CASE WHEN ns.MonthName=4 THEN IFNULL(ns.`ColSum`,0) END) AS Apr,
				sum(CASE WHEN ns.MonthName=5 THEN IFNULL(ns.`ColSum`,0) END) AS May,
				sum(CASE WHEN ns.MonthName=6 THEN IFNULL(ns.`ColSum`,0) END) AS Jun,
				sum(CASE WHEN ns.MonthName=7 THEN IFNULL(ns.`ColSum`,0) END) AS Jul,
				sum(CASE WHEN ns.MonthName=8 THEN IFNULL(ns.`ColSum`,0) END) AS Aug,
				sum(CASE WHEN ns.MonthName=9 THEN IFNULL(ns.`ColSum`,0) END) AS Sep,
				sum(CASE WHEN ns.MonthName=10 THEN IFNULL(ns.`ColSum`,0) END) AS Oct,
				sum(CASE WHEN ns.MonthName=11 THEN IFNULL(ns.`ColSum`,0) END) AS Nov,
				sum(CASE WHEN ns.MonthName=12 THEN IFNULL(ns.`ColSum`,0) END) AS `Dec`,
				sum(ns.`ColSum`) as GrandTotal
				from NetSales ns';
$stmt4=$link->query($sql4); $result4=$stmt4->fetch();

$tjan=number_format(($result3['Jan']/$result4['Jan'])*100,2);
$tfeb=number_format(($result3['Feb']/$result4['Feb'])*100,2); 
$tmar=number_format(($result3['Mar']/$result4['Mar'])*100,2); 
$tapr=number_format(($result3['Apr']/$result4['Apr'])*100,2); 
$tmay=number_format(($result3['May']/$result4['May'])*100,2); 
$tjun=number_format(($result3['Jun']/$result4['Jun'])*100,2); 
$tjul=number_format(($result3['Jul']/$result4['Jul'])*100,2); 
$taug=number_format(($result3['Aug']/$result4['Aug'])*100,2); 
$tsep=number_format(($result3['Sep']/$result4['Sep'])*100,2); 
$toct=number_format(($result3['Oct']/$result4['Oct'])*100,2); 
$tnov=number_format(($result3['Nov']/$result4['Nov'])*100,2); 
$tdec=number_format(($result3['Dec']/$result4['Dec'])*100,2);
$tgrantotal=number_format(($result3['GrandTotal']/$result4['GrandTotal'])*100,2);   
//end netsales			
			
			
			
			$sql='SELECT \'GrossProfit\' as Totals,
				FORMAT(AVG(CASE WHEN t.MonthName=1 THEN IFNULL(t.`ColSum`,0) END),2) AS JanTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=2 THEN IFNULL(t.`ColSum`,0) END),2) AS FebTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=3 THEN IFNULL(t.`ColSum`,0) END),2) AS MarTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=4 THEN IFNULL(t.`ColSum`,0) END),2) AS AprTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=5 THEN IFNULL(t.`ColSum`,0) END),2) AS MayTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=6 THEN IFNULL(t.`ColSum`,0) END),2) AS JunTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=7 THEN IFNULL(t.`ColSum`,0) END),2) AS JulTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=8 THEN IFNULL(t.`ColSum`,0) END),2) AS AugTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=9 THEN IFNULL(t.`ColSum`,0) END),2) AS SepTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=10 THEN IFNULL(t.`ColSum`,0) END),2) AS OctTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=11 THEN IFNULL(t.`ColSum`,0) END),2) AS NovTotal,
				FORMAT(AVG(CASE WHEN t.MonthName=12 THEN IFNULL(t.`ColSum`,0) END),2) AS `DecTotal`,
				FORMAT(SUM(ColSum),2) AS GrandTotal
				FROM  TOTAL t

				UNION ALL
				select \'NetSales\' as Totals,
				FORMAT(sum(CASE WHEN ns.MonthName=1 THEN IFNULL(ns.`ColSum`,0) END),2) AS JanTotal,
				FORMAT(sum(CASE WHEN ns.MonthName=2 THEN IFNULL(ns.`ColSum`,0) END),2) AS FebTotal,
				FORMAT(sum(CASE WHEN ns.MonthName=3 THEN IFNULL(ns.`ColSum`,0) END),2) AS MarTotal,
				FORMAT(sum(CASE WHEN ns.MonthName=4 THEN IFNULL(ns.`ColSum`,0) END),2) AS AprTotal,
				FORMAT(sum(CASE WHEN ns.MonthName=5 THEN IFNULL(ns.`ColSum`,0) END),2) AS MayTotal,
				FORMAT(sum(CASE WHEN ns.MonthName=6 THEN IFNULL(ns.`ColSum`,0) END),2) AS JunTotal,
				FORMAT(sum(CASE WHEN ns.MonthName=7 THEN IFNULL(ns.`ColSum`,0) END),2) AS JulTotal,
				FORMAT(sum(CASE WHEN ns.MonthName=8 THEN IFNULL(ns.`ColSum`,0) END),2) AS AugTotal,
				FORMAT(sum(CASE WHEN ns.MonthName=9 THEN IFNULL(ns.`ColSum`,0) END),2) AS SepTotal,
				FORMAT(sum(CASE WHEN ns.MonthName=10 THEN IFNULL(ns.`ColSum`,0) END),2) AS OctTotal,
				FORMAT(sum(CASE WHEN ns.MonthName=11 THEN IFNULL(ns.`ColSum`,0) END),2) AS NovTotal,
				FORMAT(sum(CASE WHEN ns.MonthName=12 THEN IFNULL(ns.`ColSum`,0) END),2) AS `DecTotal`,
				format(sum(ns.`ColSum`),2) as GrandTotal
				from NetSales ns

				UNION ALL
				select \'%\' as Totals,
				concat(\''.$tjan.'\',"%") as Jan, concat(\''.$tfeb.'\',"%") as Feb,
				concat(\''.$tmar.'\',"%") as Mar, concat(\''.$tapr.'\',"%") as Apr,
				concat(\''.$tmay.'\',"%") as May, concat(\''.$tjun.'\',"%") as Jun,
				concat(\''.$tjul.'\',"%") as Jul, concat(\''.$taug.'\',"%") as Aug,
				concat(\''.$tsep.'\',"%") as Sep, concat(\''.$toct.'\',"%") as Oct,
				concat(\''.$tnov.'\',"%") as Nov, concat(\''.$tdec.'\',"%") as `Dec`,
				concat(\''.$tgrantotal.'\',"%") as GrandTotal				';
			$title='';
			$columnnames=array('Totals','JanTotal','FebTotal','MarTotal','AprTotal','MayTotal','JunTotal','JulTotal','AugTotal','SepTotal','OctTotal','NovTotal','DecTotal','GrandTotal');
			// unset($showgrandtotal);
			// unset($coltototal);
				include('../backendphp/layout/displayastablenosort.php');
}
	
	break;
	
	case'netsalesvsnetincome':
	$title='Net Sales vs Net Income';
	echo '<title>'.$title.'</title><h3>'.$title.'</h3>';
	//start enablebasedonradio	
			$radionamefield='radiolist';	
			echo '</br><form id="form-id">
			Per Branch <input type="radio" id="watch-me1" name="'.$radionamefield.'">
			Per Company <input type="radio" id="watch-me2" name="'.$radionamefield.'">
			All <input type="radio" id="watch-me3" name="'.$radionamefield.'">
			</form>
			</br>';
			$formaction='<form method="post" action="financereports.php?w='.$_GET['w'].'">';
			$all='<input type="hidden" name="All">';
			$branchinput='Branch <input type="text" name="BranchNo" list="branchlist">';
			$companyinput='Company <input type="text" name="CompanyNo" list="companylist">';
			
			//perbranch
			echo '<div id="show-me1" style="display:none">
					'.$formaction.'
					'.$branchinput.'
					<input type="submit" name="submit"> 
				</form>
				</div>';
			
			//per company
			echo '<div id="show-me2" style="display:none">
					'.$formaction.'
					'.$companyinput.'
					<input type="submit" name="submit"> 
				</form>
				</div>';
			
			//all
			echo '<div id="show-me3" style="display:none">
					'.$formaction.'
					'.$all.'
					<input type="submit" name="submit"> 
				</form>
				</div>';				
			
			include $path.'/acrossyrs/commonfunctions/enablebasedonradio.php';	
//end
if(isset($_POST['submit'])){
	
	if(isset($_POST['All'])){
					$condition='';
					echo '<b>All</b>';
					$columnnames=array('Branch','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','YrTotal');
				}elseif(isset($_POST['BranchNo'])){
					$branchno=companyandbranchValue($link, '1branches', 'Branch', $_POST['BranchNo'], 'BranchNo');
					$condition='AND b.BranchNo='.$branchno.'';
					echo '<b>'.$_POST['BranchNo'].'</b>';
					$columnnames=array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','YrTotal');
					$hidecount=true;
				}elseif(isset($_POST['CompanyNo'])){
					$companyno=companyandbranchValue($link, '1companies', 'CompanyName', $_POST['CompanyNo'], 'CompanyNo');	
					$condition='AND CompanyNo='.$companyno.'';
					echo '<b>'.$_POST['CompanyNo'].'</b>';				
					$columnnames=array('Branch','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','YrTotal');
				}
	
	$title='';
	
	//net sales
		$sql1='CREATE TEMPORARY TABLE NetSales AS ';
			$fmn=1; $tmn=12;
			while($fmn<=$tmn){
				$sql1.='SELECT Branch AS ColName, b.BranchNo,'.$fmn.' AS MonthName, sum(ut.Amount*-1) AS ColSum FROM 1branches b  left join acctg_0unialltxns ut on ut.BranchNo=b.BranchNo left join acctg_1chartofaccounts ca  on ca.AccountID=ut.AccountID  where (AccountType=100 or ut.AccountID=810) AND month(ut.Date)='.$fmn.' '.$condition.'  GROUP BY BranchNo,MonthName UNION ';
				$fmn++;
			}
			$sql1=substr($sql1, 0, -6);
			// echo $sql1; exit();
			$stmt1=$link->prepare($sql1); $stmt1->execute();
	//net income		
		$sql2='CREATE TEMPORARY TABLE NetIncome AS ';
			$fmn=1; $tmn=12;
			while($fmn<=$tmn){
				$sql2.='SELECT Branch AS ColName, b.BranchNo,'.$fmn.' AS MonthName, sum(ut.Amount*-1) AS ColSum FROM 1branches b  left join acctg_0unialltxns ut on ut.BranchNo=b.BranchNo left join acctg_1chartofaccounts ca  on ca.AccountID=ut.AccountID  where AccountType in(100,101,150,201,200,210,220,230,240,250) AND month(ut.Date)='.$fmn.' and month(Date)<=\''.$monthdate.'\' '.$condition.' GROUP BY BranchNo,MonthName UNION ';
				$fmn++;
			}
			$sql2=substr($sql2, 0, -6);
			// echo $sql2; exit();
			$stmt2=$link->prepare($sql2); $stmt2->execute();	
			
			$sql='SELECT ni.ColName AS Branch,ni.BranchNo,CONCAT(FORMAT((SUM(ni.ColSum)/SUM(ns.ColSum))*100,0),"%") AS YrTotal,
			CONCAT(FORMAT(AVG(CASE WHEN ni.MonthName=1 THEN IFNULL((ni.`ColSum`/ns.`ColSum`)*100,0) END),0),"%") AS Jan,
			CONCAT(FORMAT(AVG(CASE WHEN ni.MonthName=2 THEN IFNULL((ni.`ColSum`/ns.`ColSum`)*100,0) END),0),"%")  AS Feb,
			CONCAT(FORMAT(AVG(CASE WHEN ni.MonthName=3 THEN IFNULL((ni.`ColSum`/ns.`ColSum`)*100,0) END),0),"%")  AS Mar,
			CONCAT(FORMAT(AVG(CASE WHEN ni.MonthName=4 THEN IFNULL((ni.`ColSum`/ns.`ColSum`)*100,0) END),0),"%")  AS Apr,
			CONCAT(FORMAT(AVG(CASE WHEN ni.MonthName=5 THEN IFNULL((ni.`ColSum`/ns.`ColSum`)*100,0) END),0),"%")  AS May,
			CONCAT(FORMAT(AVG(CASE WHEN ni.MonthName=6 THEN IFNULL((ni.`ColSum`/ns.`ColSum`)*100,0) END),0),"%")  AS Jun,
			CONCAT(FORMAT(AVG(CASE WHEN ni.MonthName=7 THEN IFNULL((ni.`ColSum`/ns.`ColSum`)*100,0) END),0),"%")  AS Jul,
			CONCAT(FORMAT(AVG(CASE WHEN ni.MonthName=8 THEN IFNULL((ni.`ColSum`/ns.`ColSum`)*100,0) END),0),"%")  AS Aug,
			CONCAT(FORMAT(AVG(CASE WHEN ni.MonthName=9 THEN IFNULL((ni.`ColSum`/ns.`ColSum`)*100,0) END),0),"%")  AS Sep,
			CONCAT(FORMAT(AVG(CASE WHEN ni.MonthName=10 THEN IFNULL((ni.`ColSum`/ns.`ColSum`)*100,0) END),0),"%")  AS Oct,
			CONCAT(FORMAT(AVG(CASE WHEN ni.MonthName=11 THEN IFNULL((ni.`ColSum`/ns.`ColSum`)*100,0) END),0),"%")  AS Nov,
			CONCAT(FORMAT(AVG(CASE WHEN ni.MonthName=12 THEN IFNULL((ni.`ColSum`/ns.`ColSum`)*100,0) END),0),"%")  AS \'Dec\'
				FROM  NetIncome ni left join NetSales ns on ns.BranchNo=ni.BranchNo and ns.MonthName=ni.MonthName GROUP BY ni.BranchNo ORDER BY ni.ColName;';

				// echo $sql; exit();
				include('../backendphp/layout/displayastablenosort.php');
/////////////////////////////////////////////////////////////////totals////////////////////////////

$sql3='select \'NetIncome\' as Totals,
				sum(CASE WHEN ni.MonthName=1 THEN IFNULL(ni.`ColSum`,0) END) AS Jan,
				sum(CASE WHEN ni.MonthName=2 THEN IFNULL(ni.`ColSum`,0) END) AS Feb,
				sum(CASE WHEN ni.MonthName=3 THEN IFNULL(ni.`ColSum`,0) END) AS Mar,
				sum(CASE WHEN ni.MonthName=4 THEN IFNULL(ni.`ColSum`,0) END) AS Apr,
				sum(CASE WHEN ni.MonthName=5 THEN IFNULL(ni.`ColSum`,0) END) AS May,
				sum(CASE WHEN ni.MonthName=6 THEN IFNULL(ni.`ColSum`,0) END) AS Jun,
				sum(CASE WHEN ni.MonthName=7 THEN IFNULL(ni.`ColSum`,0) END) AS Jul,
				sum(CASE WHEN ni.MonthName=8 THEN IFNULL(ni.`ColSum`,0) END) AS Aug,
				sum(CASE WHEN ni.MonthName=9 THEN IFNULL(ni.`ColSum`,0) END) AS Sep,
				sum(CASE WHEN ni.MonthName=10 THEN IFNULL(ni.`ColSum`,0) END) AS Oct,
				sum(CASE WHEN ni.MonthName=11 THEN IFNULL(ni.`ColSum`,0) END) AS Nov,
				sum(CASE WHEN ni.MonthName=12 THEN IFNULL(ni.`ColSum`,0) END) AS `Dec`,
				sum(ni.`ColSum`) as GrandTotal
				from NetIncome ni';
$stmt3=$link->query($sql3); $result3=$stmt3->fetch();

$sql4='select \'NetSales\' as Totals,
				sum(CASE WHEN ns.MonthName=1 THEN IFNULL(ns.`ColSum`,0) END) AS Jan,
				sum(CASE WHEN ns.MonthName=2 THEN IFNULL(ns.`ColSum`,0) END) AS Feb,
				sum(CASE WHEN ns.MonthName=3 THEN IFNULL(ns.`ColSum`,0) END) AS Mar,
				sum(CASE WHEN ns.MonthName=4 THEN IFNULL(ns.`ColSum`,0) END) AS Apr,
				sum(CASE WHEN ns.MonthName=5 THEN IFNULL(ns.`ColSum`,0) END) AS May,
				sum(CASE WHEN ns.MonthName=6 THEN IFNULL(ns.`ColSum`,0) END) AS Jun,
				sum(CASE WHEN ns.MonthName=7 THEN IFNULL(ns.`ColSum`,0) END) AS Jul,
				sum(CASE WHEN ns.MonthName=8 THEN IFNULL(ns.`ColSum`,0) END) AS Aug,
				sum(CASE WHEN ns.MonthName=9 THEN IFNULL(ns.`ColSum`,0) END) AS Sep,
				sum(CASE WHEN ns.MonthName=10 THEN IFNULL(ns.`ColSum`,0) END) AS Oct,
				sum(CASE WHEN ns.MonthName=11 THEN IFNULL(ns.`ColSum`,0) END) AS Nov,
				sum(CASE WHEN ns.MonthName=12 THEN IFNULL(ns.`ColSum`,0) END) AS `Dec`,
				sum(ns.`ColSum`) as GrandTotal
				from NetSales ns';
$stmt4=$link->query($sql4); $result4=$stmt4->fetch();

$tjan=number_format(($result3['Jan']/$result4['Jan'])*100,0);
$tfeb=number_format(($result3['Feb']/$result4['Feb'])*100,0); 
$tmar=number_format(($result3['Mar']/$result4['Mar'])*100,0); 
$tapr=number_format(($result3['Apr']/$result4['Apr'])*100,0); 
$tmay=number_format(($result3['May']/$result4['May'])*100,0); 
$tjun=number_format(($result3['Jun']/$result4['Jun'])*100,0); 
$tjul=number_format(($result3['Jul']/$result4['Jul'])*100,0); 
$taug=number_format(($result3['Aug']/$result4['Aug'])*100,0); 
$tsep=number_format(($result3['Sep']/$result4['Sep'])*100,0); 
$toct=number_format(($result3['Oct']/$result4['Oct'])*100,0); 
$tnov=number_format(($result3['Nov']/$result4['Nov'])*100,0); 
$tdec=number_format(($result3['Dec']/$result4['Dec'])*100,0);
$tgrantotal=number_format(($result3['GrandTotal']/$result4['GrandTotal'])*100,0);   
				
				unset($title);
				$title='';
				$sql='select \'NetIncome\' as Totals,
				FORMAT(sum(CASE WHEN ni.MonthName=1 THEN IFNULL(ni.`ColSum`,0) END),0) AS Jan,
				FORMAT(sum(CASE WHEN ni.MonthName=2 THEN IFNULL(ni.`ColSum`,0) END),0) AS Feb,
				FORMAT(sum(CASE WHEN ni.MonthName=3 THEN IFNULL(ni.`ColSum`,0) END),0) AS Mar,
				FORMAT(sum(CASE WHEN ni.MonthName=4 THEN IFNULL(ni.`ColSum`,0) END),0) AS Apr,
				FORMAT(sum(CASE WHEN ni.MonthName=5 THEN IFNULL(ni.`ColSum`,0) END),0) AS May,
				FORMAT(sum(CASE WHEN ni.MonthName=6 THEN IFNULL(ni.`ColSum`,0) END),0) AS Jun,
				FORMAT(sum(CASE WHEN ni.MonthName=7 THEN IFNULL(ni.`ColSum`,0) END),0) AS Jul,
				FORMAT(sum(CASE WHEN ni.MonthName=8 THEN IFNULL(ni.`ColSum`,0) END),0) AS Aug,
				FORMAT(sum(CASE WHEN ni.MonthName=9 THEN IFNULL(ni.`ColSum`,0) END),0) AS Sep,
				FORMAT(sum(CASE WHEN ni.MonthName=10 THEN IFNULL(ni.`ColSum`,0) END),0) AS Oct,
				FORMAT(sum(CASE WHEN ni.MonthName=11 THEN IFNULL(ni.`ColSum`,0) END),0) AS Nov,
				FORMAT(sum(CASE WHEN ni.MonthName=12 THEN IFNULL(ni.`ColSum`,0) END),0) AS `Dec`,
				format(sum(ni.`ColSum`),0) as GrandTotal
				from NetIncome ni
				UNION ALL
				select \'NetSales\' as Totals,
				FORMAT(sum(CASE WHEN ns.MonthName=1 THEN IFNULL(ns.`ColSum`,0) END),0) AS Jan,
				FORMAT(sum(CASE WHEN ns.MonthName=2 THEN IFNULL(ns.`ColSum`,0) END),0) AS Feb,
				FORMAT(sum(CASE WHEN ns.MonthName=3 THEN IFNULL(ns.`ColSum`,0) END),0) AS Mar,
				FORMAT(sum(CASE WHEN ns.MonthName=4 THEN IFNULL(ns.`ColSum`,0) END),0) AS Apr,
				FORMAT(sum(CASE WHEN ns.MonthName=5 THEN IFNULL(ns.`ColSum`,0) END),0) AS May,
				FORMAT(sum(CASE WHEN ns.MonthName=6 THEN IFNULL(ns.`ColSum`,0) END),0) AS Jun,
				FORMAT(sum(CASE WHEN ns.MonthName=7 THEN IFNULL(ns.`ColSum`,0) END),0) AS Jul,
				FORMAT(sum(CASE WHEN ns.MonthName=8 THEN IFNULL(ns.`ColSum`,0) END),0) AS Aug,
				FORMAT(sum(CASE WHEN ns.MonthName=9 THEN IFNULL(ns.`ColSum`,0) END),0) AS Sep,
				FORMAT(sum(CASE WHEN ns.MonthName=10 THEN IFNULL(ns.`ColSum`,0) END),0) AS Oct,
				FORMAT(sum(CASE WHEN ns.MonthName=11 THEN IFNULL(ns.`ColSum`,0) END),0) AS Nov,
				FORMAT(sum(CASE WHEN ns.MonthName=12 THEN IFNULL(ns.`ColSum`,0) END),0) AS `Dec`,
				format(sum(ns.`ColSum`),0) as GrandTotal
				from NetSales ns
				UNION ALL
				select \'%\' as totals,
				concat(\''.$tjan.'\',"%") as Jan, concat(\''.$tfeb.'\',"%") as Feb,
				concat(\''.$tmar.'\',"%") as Mar, concat(\''.$tapr.'\',"%") as Apr,
				concat(\''.$tmay.'\',"%") as May, concat(\''.$tjun.'\',"%") as Jun,
				concat(\''.$tjul.'\',"%") as Jul, concat(\''.$taug.'\',"%") as Aug,
				concat(\''.$tsep.'\',"%") as Sep, concat(\''.$toct.'\',"%") as Oct,
				concat(\''.$tnov.'\',"%") as Nov, concat(\''.$tdec.'\',"%") as `Dec`,
				concat(\''.$tgrantotal.'\',"%") as GrandTotal
				';
				$columnnames=array('Totals','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','GrandTotal');
				// echo $sql; exit();
				include('../backendphp/layout/displayastablenosort.php');
}
			
	break;
	
	case 'quota':
	$title='Target';
	echo '<title>'.$title.'</title><h3>'.$title.'</h3>';
	//start enablebasedonradio	
			$radionamefield='radiolist';	
			echo '</br><form id="form-id">
			Per Branch <input type="radio" id="watch-me1" name="'.$radionamefield.'">
			Per Company <input type="radio" id="watch-me2" name="'.$radionamefield.'">
			All <input type="radio" id="watch-me3" name="'.$radionamefield.'">
			</form>
			</br>';
			$formaction='<form method="post" action="financereports.php?w='.$_GET['w'].'">';
			$all='<input type="hidden" name="All">';
			$branchinput='Branch <input type="text" name="BranchNo" list="branchlist">';
			$companyinput='Company <input type="text" name="CompanyNo" list="companylist">';
			
			//perbranch
			echo '<div id="show-me1" style="display:none">
					'.$formaction.'
					'.$branchinput.'
					<input type="submit" name="submit"> 
				</form>
				</div>';
			
			//per company
			echo '<div id="show-me2" style="display:none">
					'.$formaction.'
					'.$companyinput.'
					<input type="submit" name="submit"> 
				</form>
				</div>';
			
			//all
			echo '<div id="show-me3" style="display:none">
					'.$formaction.'
					'.$all.'
					<input type="submit" name="submit"> 
				</form>
				</div>';				
			
			include $path.'/acrossyrs/commonfunctions/enablebasedonradio.php';	
//end
if(isset($_POST['submit'])){
	
	if(isset($_POST['All'])){
					$condition='';
					echo '<b>All</b>';
					$columnnames=array('Branch','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','Total');
				}elseif(isset($_POST['BranchNo'])){
					$branchno=companyandbranchValue($link, '1branches', 'Branch', $_POST['BranchNo'], 'BranchNo');
					$condition='where b.BranchNo='.$branchno.'';
					echo '<b>'.$_POST['BranchNo'].'</b>';
					$columnnames=array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','Total');
					$hidecount=true;
				}elseif(isset($_POST['CompanyNo'])){
					$companyno=companyandbranchValue($link, '1companies', 'CompanyName', $_POST['CompanyNo'], 'CompanyNo');	
					$condition='where CompanyNo='.$companyno.'';
					echo '<b>'.$_POST['CompanyNo'].'</b>';
					$columnnames=array('Branch','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','Total');
	
				}
	
	$title='';
	$sql='select Branch,`01` as Jan,`02` as Feb,`03` as Mar,`04` as Apr,`05` as May,`06` Jun,`07` as Jul,`08` as Aug,`09` as Sep,`10` as Oct,`11` as Nov,`12` as \'Dec\',(`01`+`02`+`03`+`04`+`05`+`06`+`07`+`08`+`09`+`10`+`11`+`12`) as Total from acctg_1yearsalestargets yst join 1branches b on b.BranchNo=yst.BranchNo '.$condition.' Order By Branch';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	// $coltototal='Total';
	// $showgrandtotal='true';

	include('../backendphp/layout/displayastablenosort.php');
	
	if(!isset($_POST['BranchNo'])){
	unset($title);
	$title='';
	$sql='select format(sum(`01`),2) as JanTotal,format(sum(`02`),2) as FebTotal,format(sum(`03`),2) as MarTotal,format(sum(`04`),2) as AprTotal,format(sum(`05`),2) as MayTotal,format(sum(`06`),2) JunTotal,format(sum(`07`),2) as JulTotal,format(sum(`08`),2) as AugTotal,format(sum(`09`),2) as SepTotal,format(sum(`10`),2) as OctTotal,format(sum(`11`),2) as NovTotal,format(sum(`12`),2) as \'DecTotal\',format((sum(`01`)+sum(`02`)+sum(`03`)+sum(`04`)+sum(`05`)+sum(`06`)+sum(`07`)+sum(`08`)+sum(`09`)+sum(`10`)+sum(`11`)+sum(`12`)),2) as GrandTotal from acctg_1yearsalestargets yst join 1branches b on b.BranchNo=yst.BranchNo '.$condition.'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	// unset($coltototal);
	// unset($showgrandtotal);
	$columnnames=array('JanTotal','FebTotal','MarTotal','AprTotal','MayTotal','JunTotal','JulTotal','AugTotal','SepTotal','OctTotal','NovTotal','DecTotal','GrandTotal');
	
	include('../backendphp/layout/displayastablenosort.php');
	}
	}

	break;
	
	
	
}
  $link=null; $stmt=null;
?>

