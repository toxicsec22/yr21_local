<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php'; 
$allowed=array(532,533,534,535,5333);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
$showbranches=false;
include_once('../backendphp/layout/linkstyle.php');

include_once($path.'/acrossyrs/js/reportcharts/mgraphlabel.php'); 
include($path.'/acrossyrs/js/reportcharts/includejscharts.php');
include_once('../switchboard/contents.php');
$dbtouse=$link;
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($dbtouse,'SELECT BranchNo, Branch FROM 1branches WHERE Active<>0 ORDER BY Branch','BranchNo','Branch','branchlist');
?>

<?php

echo ' </br><a id=\'link\' href="accountinggraphs.php?w=NetSales">NetSales</a>';
echo ' <a id=\'link\' href="accountinggraphs.php?w=TurnOverRate">Turn Over Rate</a><br><br><br>';
					
$echo='';
$which=(!isset($_GET['w'])?'NetSales':$_GET['w']);
$lwidth='45%'; 

$sql0='CREATE TEMPORARY TABLE `graphreport11` (
  `ReportID` tinyint(4) NOT NULL AUTO_INCREMENT,
  `ReportTitle` varchar(100) DEFAULT NULL,

  `OtherDesc` varchar(20) NOT NULL,
  `Label` varchar(100) NOT NULL,
  `xaxis` varchar(25) NOT NULL,
  `yaxis` varchar(25) NOT NULL,
  `min` tinyint(1)DEFAULT NULL,
  `legend1` varchar(15) NOT NULL,
  `legend2` varchar(25) NOT NULL,
  `legend3` varchar(15) NOT NULL,
  `legend4` varchar(25) NOT NULL,
  `legend5` varchar(25) NOT NULL,
  `legend6` varchar(25) NOT NULL,
  `legend7` varchar(25) NOT NULL,
  `legend8` varchar(25) NOT NULL,
   `fllegend1` varchar(25) NOT NULL,
   `lineonly` varchar(25) NOT NULL,
  
  PRIMARY KEY (`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;'; //graph report
$stmt=$link->prepare($sql0);$stmt->execute();

$sql0='CREATE TEMPORARY TABLE `graphboard11` (
  `TxnID` smallint(6) NOT NULL AUTO_INCREMENT,
  `IDNo` smallint(6) DEFAULT NULL,
  `GraphID` tinyint(2) NOT NULL,
  `DataSet1` varchar(2000) NOT NULL,
  `DataSet2` varchar(2000) NOT NULL,
  `DataSet3` varchar(2000) DEFAULT NULL,
  `DataSet4` varchar(2000) DEFAULT NULL,
  `DataSet5` varchar(2000) DEFAULT NULL,
  `DataSet6` varchar(2000) DEFAULT NULL,
  `DataSet7` varchar(2000) DEFAULT NULL,
  `DataSet8` varchar(2000) DEFAULT NULL,
  `FilledLine1` varchar(2100) DEFAULT NULL,
  `LineOnly` varchar(2100) DEFAULT NULL,
  `figure` varchar(50) DEFAULT NULL,
  `figure1` varchar(50) DEFAULT NULL,
  `figure2` varchar(50) DEFAULT NULL,
  `figure3` varchar(50) DEFAULT NULL,
  `figure4` varchar(50) DEFAULT NULL,
  `figure5` varchar(5) DEFAULT NULL,
  `figure6` varchar(5) DEFAULT NULL,
  `figure7` varchar(5) DEFAULT NULL,
  `figure8` varchar(5) DEFAULT NULL,
  `ReportID` tinyint(4) NOT NULL,
  PRIMARY KEY (`TxnID`),
  UNIQUE KEY `IDNo` (`IDNo`,`GraphID`,`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1'; //graphboard
$stmt=$link->prepare($sql0);$stmt->execute();


if (isset($_POST['btnSubmit'])){
	$txndate=strlen($_POST['monthno'])==1?'0'.$_POST['monthno']:$_POST['monthno'];
} else {
	$txndate=date('m'); 
}

switch ($which){
case 'NetSales':
$figurename='NET SALES = ';
$figurename1='COST OF GOOD SOLD = ';
$figurename2='GROSS PROFIT = ';
$figurename3='TOTAL EXPENSES = ';
$figurename4='NET INCOME = ';

$figurename5='COST OF GOOD SOLD = ';
// $figurename6='GROSS PROFIT = ';
$figurename6='TOTAL EXPENSES = ';
$figurename7='NET INCOME = ';
$position='position:relative;';
$paddingright='padding-right:290px;';
$title='NetSales Report';
echo '<title>'.$title.'</title>';

	echo '<h3>NetSales Report</h3><br>';
	echo '<form method="post" action="accountinggraphs.php?w=NetSales">
			<input list="conditions" name="condition" placeholder="Filtering Group By" >
			<datalist id="conditions"><option value="Per Area"><option value="Per Company"></datalist></input>
			Per Branch<input type="text" name="BranchNo" list="branchlist" placeholder="Filtering Per Branch"></input>
			Choose Month(1 - 12): <input type="text" name="month" size="3">
			<input type="submit" name="submit"> 
			</br></br></form>';
			if(!isset($_POST['month'])){
			$_POST['month']=12;}
			switch ($_POST['month']){
				case '12':
				$montharray="'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'";
				$monthin='AND month(Date) in (01,02,03,04,05,06,07,08,09,10,11,12)';
				break;
				case '11':
				$montharray="'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov'";
				$monthin='AND month(Date) in (01,02,03,04,05,06,07,08,09,10,11)';
				break;
				case '10':
				$montharray="'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct'";
				$monthin='AND month(Date) in (01,02,03,04,05,06,07,08,09,10)';
				break;
				case '09':
				$montharray="'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep'";
				$monthin='AND month(Date) in (01,02,03,04,05,06,07,08,09)';
				break;
				case '08':
				$montharray="'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'";
				$monthin='AND month(Date) in (01,02,03,04,05,06,07,08)';
				break;
				case '07':
				$montharray="'Jan','Feb','Mar','Apr','May','Jun','Jul'";
				$monthin='AND month(Date) in (01,02,03,04,05,06,07)';
				break;
				case '06':
				$montharray="'Jan','Feb','Mar','Apr','May','Jun'";
				$monthin='AND month(Date) in (01,02,03,04,05,06)';
				break;
				case '05':
				$montharray="'Jan','Feb','Mar','Apr','May'";
				$monthin='AND month(Date) in (01,02,03,04,05)';
				break;
				case '04':
				$montharray="'Jan','Feb','Mar','Apr'";
				$monthin='AND month(Date) in (01,02,03,04)';
				break;
				case '03':
				$montharray="'Jan','Feb','Mar'";
				$monthin='AND month(Date) in (01,02,03)';
				break;
				case '02':
				$montharray="'Jan','Feb'";
				$monthin='AND month(Date) in (01,02)';
				break;
				case '01':
				$montharray="'Jan'";
				$monthin='AND month(Date) in (01)';
				break;
			}
			
			
if(isset($_POST['submit'])){
	
	if($_POST['BranchNo']!=null){
		$branchno=companyandbranchValue($link, '1branches', 'Branch', $_POST['BranchNo'], 'BranchNo');
		$sql0='select BranchNo from 1branches where BranchNo='.$branchno.' ';
		// echo $sql0; exit();
		$stmt=$dbtouse->query($sql0); $res=$stmt->fetchAll();
		$ReportTitle='ReportTitle="Total Sales Per Branch"';
		$graphtitle='Branch';
		$select='Branch';
		$condi='GROUP BY g.IDNo';
		$leftjoin='LEFT JOIN 1branches b on b.BranchNo=g.IDNo';
	}
	
	
	switch ($_POST['condition']){
		// case 'Per Branch':
		// $sql0='select BranchNo from 1branches where Active<>0';
		// $stmt=$dbtouse->query($sql0); $res=$stmt->fetchAll();
		// $ReportTitle='ReportTitle="Total Sales Per Branch"';
		// $graphtitle='Branch';
		// $select='Branch';
		// $condi='GROUP BY g.IDNo';
		// $leftjoin='LEFT JOIN 1branches b on b.BranchNo=g.IDNo';
		// break;
		case 'Per Area': 
		$sql0='select if(TargetShareWith=0,BranchNo,TargetShareWith) as TargetShareWith from 1branches where Active<>0 and TargetShareWith<>0 Group By if(TargetShareWith=0,BranchNo,TargetShareWith)';
		$stmt=$dbtouse->query($sql0); $res=$stmt->fetchAll();
		$ReportTitle='ReportTitle="Total Sales Per Area"';
		$graphtitle='Branch';
		$select='Group_Concat(Branch) as Branch';
		$condi='AND Active<>0 and TargetShareWith<>0 GROUP BY g.IDNo';
		$leftjoin='LEFT JOIN 1branches b on b.TargetShareWith=g.IDNo';
		break;
		case 'Per Company': 
		$sql0='select b.CompanyNo from 1branches b join 1companies c on c.CompanyNo=b.CompanyNo Group By b.CompanyNo';
		$stmt=$dbtouse->query($sql0); $res=$stmt->fetchAll();
		$ReportTitle='ReportTitle="Total Sales Per Company"';
		$graphtitle='CompanyName';
		$select='CompanyName';
		$condi='GROUP BY g.IDNo';
		$leftjoin='LEFT JOIN 1companies c on c.CompanyNo=g.IDNo';
		break;

		}
	
	
}
else{
//
$sql0='select if(TargetShareWith=0,BranchNo,TargetShareWith) as TargetShareWith from 1branches where Active<>0 and TargetShareWith<>0 Group By if(TargetShareWith=0,BranchNo,TargetShareWith)';
$stmt=$dbtouse->query($sql0); $res=$stmt->fetchAll();
$ReportTitle='ReportTitle="Total Sales Per Area"';

$graphtitle='Branch';
$select='Group_Concat(Branch) as Branch';
$condi='AND Active<>0 and TargetShareWith<>0 GROUP BY g.IDNo';
$leftjoin='LEFT JOIN 1branches b on b.TargetShareWith=g.IDNo';
}
foreach($res as $field){	
	$dataset1=''; 
	$dataset2='';
	$dataset3='';
	$dataset4='';
	$dataset5='';
	$dataset6='';
	$dataset7='';
	$dataset8='';
	
	if(isset($_POST['submit'])){
		
		if($_POST['BranchNo']!=null){
			$conditions='And b.BranchNo='.$branchno.'';
			$name=$field['BranchNo'];
		}
			// exit();
		switch ($_POST['condition']){
		// case 'Per Branch':
		// $conditions=' AND ut.BranchNo='.$field['BranchNo'].'';
		// $name=$field['BranchNo'];
		// break;
		case 'Per Area':
		$conditions=' AND if(TargetShareWith=0,ut.BranchNo='.$field['TargetShareWith'].',TargetShareWith='.$field['TargetShareWith'].')';
		$name=$field['TargetShareWith'];
		break;
		case 'Per Company':
		$conditions=' AND CompanyNo='.$field['CompanyNo'].'';
		$name=$field['CompanyNo'];
		break;
		}
		}else{
			$conditions=' AND if(TargetShareWith=0,ut.BranchNo='.$field['TargetShareWith'].',TargetShareWith='.$field['TargetShareWith'].')';
		$name=$field['TargetShareWith'];
		}
	for ($i = 1; $i <= $_POST['month']; $i++) {
		$i=(strlen($i)<>2?'0'.$i:$i);
		
		// Graph This Year Net Sales
		$sql0='select ifnull(truncate(sum(Amount*-1),2),0) as NetSales from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where (ca.AccountType=\'100\' or ut.AccountID=\'810\') '.$conditions.' AND month(Date)='.$i.' '; //echo $sql0.'<br>';
		// echo $sql0; exit();
		$stmt=$dbtouse->query($sql0); $res=$stmt->fetch();
		
		
		if($stmt->rowCount()==0){
			$dataset1.='0,';
		
		} else {
			$dataset1.=$res['NetSales'].',';
			// echo $dataset1; exit();
			
		}
		
		// Graph Last Year Net Sales
		$sqlold='select ifnull(truncate(sum(Amount*-1),2),0) as NetSales from '.$lastyr.'_1rtc.acctg_1chartofaccounts ca join '.$lastyr.'_1rtc.acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where (ca.AccountType=\'100\' or ut.AccountID=\'810\') '.$conditions.' AND month(Date)='.$i.' '; //echo $sql0.'<br>';
		// echo $sqlold; exit();
		$stmtold=$dbtouse->query($sqlold); $resold=$stmtold->fetch();
		
		
		if($stmtold->rowCount()==0){
			$dataset2.='0,';
		
		} else {
			$dataset2.=$resold['NetSales'].',';
			// echo $dataset1; exit();
			
		}
		

		
	}
	//Net Sales
	$sqlns='select ifnull(format(sum(Amount*-1),2),0) as NetSales,ifnull(TRUNCATE(sum(Amount*-1),2),0) as NetSalesValue from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where (AccountType=100 or ut.AccountID=810) '.$conditions.' '.$monthin.' '; 
	$stmtns=$dbtouse->query($sqlns); $resns=$stmtns->fetch();
	
	//Gross Profit
	$sqlgp='select ifnull(format(sum(Amount*-1),2),0) as GrossProfit,ifnull(TRUNCATE(sum(Amount*-1),2),0) as GrossProfitValue from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where (AccountType=100 or AccountType=101 or ut.AccountID=810) '.$conditions.' '.$monthin.' '; 
	$stmtgp=$dbtouse->query($sqlgp); $resgp=$stmtgp->fetch();
	// $pgp=($resgp['GrossProfitValue']/$resns['NetSalesValue'])*100;
	// $pgp=round($pgp,0).'%';
	// echo $pgp; exit();
	
	//Net Income
	$sqlni='select ifnull(format(sum(Amount*-1),2),0) as NetIncome,ifnull(TRUNCATE(sum(Amount*-1),2),0) as NetIncomeValue from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where (AccountType=210 or AccountType=200 or AccountType=220 or AccountType=201 or AccountType=230 or AccountType=150 or AccountType=100 or AccountType=101 or AccountType=240 or AccountType=250) '.$conditions.' '.$monthin.' '; //echo $sql0.'<br>';
	$stmtni=$dbtouse->query($sqlni); $resni=$stmtni->fetch();
	// echo $resni['NetIncome']; 
	$pni=($resni['NetIncomeValue']/$resns['NetSalesValue'])*100;
	$pni=number_format($pni,0).'%';
	
	//Cost of Good Sold
	$sqlcogs='select ifnull(format(sum(Amount),2),0) as COGS,ifnull(TRUNCATE(sum(Amount),2),0) as COGSValue from acctg_0unialltxns ut join 1branches b on b.BranchNo=ut.BranchNo join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID where AccountType=101 '.$conditions.' '.$monthin.' '; 
	$stmtcogs=$dbtouse->query($sqlcogs); $rescogs=$stmtcogs->fetch();
	// echo $rescogs['COGS']; 
	$pcogs=($rescogs['COGSValue']/$resns['NetSalesValue'])*100;
	$pcogs=number_format($pcogs,0).'%';
	
	//Total Expenses
	$sqlte='select ifnull(format(sum(Amount),2),0) as TotalExpense,ifnull(TRUNCATE(sum(Amount),2),0) as TotalExpenseValue from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where (AccountType=210 or AccountType=200 or AccountType=201 or AccountType=220 or AccountType=230 or AccountType=150 or AccountType=240) '.$conditions.' '.$monthin.' ';
	$stmtte=$dbtouse->query($sqlte); $reste=$stmtte->fetch();
	$pte=($reste['TotalExpenseValue']/$resns['NetSalesValue'])*100;
	$pte=number_format($pte,0).'%';
	$dataset1=substr($dataset1, 0, -1);
	$dataset2=substr($dataset2, 0, -1);

		// echo $dataset1;
		// echo $dataset2; exit();
	
	
	$sqlcsub='INSERT INTO graphboard11 SET GraphID=2,DataSet1="'.$dataset1.'",DataSet2="'.$dataset2.'",IDNo='.$name.',figure=\''.$resns['NetSales'].'\',figure1=\''.$rescogs['COGS'].'\',figure2=\''.$resgp['GrossProfit'].'\',figure3=\''.$reste['TotalExpense'].'\',figure4=\''.$resni['NetIncome'].'\',figure5=\''.$pcogs.'\',figure6=\''.$pte.'\',figure7=\''.$pni.'\',ReportID=5;';
	$stmt=$link->prepare($sqlcsub);$stmt->execute(); 
	// echo $sqlcsub.'<br>';  
	//END
	
	
}

//report 5  STL Personal targets
$sqlcmain='INSERT INTO graphreport11 SET xaxis="Branch",legend1="Net Sales",legend2="Last Year",yaxis="In Millions",min="",'.$ReportTitle.',ReportID=5,Label="'.$montharray.'"';

// echo $sqlcmain; exit();
$stmt=$link->prepare($sqlcmain);$stmt->execute();
//End

$sql = 'SELECT g.*,gr.*,'.$select.' FROM graphboard11 g '.$leftjoin.' JOIN graphreport11 gr ON g.ReportID=gr.ReportID WHERE g.ReportID IN (5) '.$condi.' ';
// echo $sql; exit();
$stmt=$dbtouse->query($sql); $res=$stmt->fetchall();

$sqldrop='DROP TEMPORARY TABLE graphboard11';
$stmt=$link->prepare($sqldrop);$stmt->execute();

$sqldrop='DROP TEMPORARY TABLE graphreport11';
$stmt=$link->prepare($sqldrop);$stmt->execute();

break;


case 'TurnOverRate':

$title='Turnover Report';
echo '<title>'.$title.'</title>';
	echo '<h3>Turnover Report</h3><br>';
	echo '<form method="post" action="accountinggraphs.php?w=TurnOverRate">
			<input list="conditions" name="condition" placeholder="Filtering Group By" >
			<datalist id="conditions"><option value="Per Area"><option value="Per Company"></datalist>
			Per Branch<input type="text" name="BranchNo" list="branchlist" placeholder="Filtering Per Branch"></input>
			<input type="submit" name="submit"> 
			</br></br></form>';
			
if(isset($_POST['submit'])){
	
	if($_POST['BranchNo']!=null){
		$branchno=companyandbranchValue($link, '1branches', 'Branch', $_POST['BranchNo'], 'BranchNo');
		$sql0='select BranchNo from 1branches where BranchNo='.$branchno.' ';
		// echo $sql0; exit();
		$stmt=$dbtouse->query($sql0); $res=$stmt->fetchAll();
		$ReportTitle='ReportTitle="TurnOver Rate Per Branch"';
		$graphtitle='Branch';
		$select='Branch';
		$condi='GROUP BY g.IDNo';
		$leftjoin='LEFT JOIN 1branches b on b.BranchNo=g.IDNo';
	}
	
	switch ($_POST['condition']){
		// case 'Per Branch':
		// $sql0='select BranchNo from 1branches where Active<>0';
		// $stmt=$dbtouse->query($sql0); $res=$stmt->fetchAll();
		// $ReportTitle='ReportTitle="TurnOver Rate Per Branch"';
		// $graphtitle='Branch';
		// $select='Branch';
		// $condi='GROUP BY g.IDNo';
		// $leftjoin='LEFT JOIN 1branches b on b.BranchNo=g.IDNo';
		// break;
		case 'Per Area': 
		$sql0='select if(TargetShareWith=0,BranchNo,TargetShareWith) as TargetShareWith from 1branches where Active<>0 and TargetShareWith<>0 Group By if(TargetShareWith=0,BranchNo,TargetShareWith)';
		$stmt=$dbtouse->query($sql0); $res=$stmt->fetchAll();
		$ReportTitle='ReportTitle="TurnOver Rate Per Area"';
		$graphtitle='Branch';
		$select='Group_Concat(Branch) as Branch';
		$condi='AND Active<>0 and TargetShareWith<>0 GROUP BY g.IDNo';
		$leftjoin='LEFT JOIN 1branches b on b.TargetShareWith=g.IDNo';
		break;
		case 'Per Company': 
		$sql0='select b.CompanyNo from 1branches b join 1companies c on c.CompanyNo=b.CompanyNo Group By b.CompanyNo';
		$stmt=$dbtouse->query($sql0); $res=$stmt->fetchAll();
		$ReportTitle='ReportTitle="TurnOver Rate Per Company"';
		$graphtitle='CompanyName';
		$select='CompanyName';
		$condi='GROUP BY g.IDNo';
		$leftjoin='LEFT JOIN 1companies c on c.CompanyNo=g.IDNo';
		break;

		}
}
else{
//
$sql0='select if(TargetShareWith=0,BranchNo,TargetShareWith) as TargetShareWith from 1branches where Active<>0 and TargetShareWith<>0 Group By if(TargetShareWith=0,BranchNo,TargetShareWith)';
$stmt=$dbtouse->query($sql0); $res=$stmt->fetchAll();
$ReportTitle='ReportTitle="TurnOver Rate Per Area"';

$graphtitle='Branch';
$select='Group_Concat(Branch) as Branch';
$condi='AND Active<>0 and TargetShareWith<>0 GROUP BY g.IDNo';
$leftjoin='LEFT JOIN 1branches b on b.TargetShareWith=g.IDNo';
}
foreach($res as $field){	
	$dataset1=''; 
	$dataset2='';
	$dataset3='';
	$dataset4='';
	$dataset5='';
	$dataset6='';
	$dataset7='';
	$dataset8='';
	$flset1='';
	if(isset($_POST['submit'])){
		
		if($_POST['BranchNo']!=null){
			$conditions='And b.BranchNo='.$branchno.'';
			$name=$field['BranchNo'];
			
		}
			// exit();
		switch ($_POST['condition']){
		// case 'Per Branch':
		// $conditions=' AND ut.BranchNo='.$field['BranchNo'].'';
		// $name=$field['BranchNo'];
		// break;
		case 'Per Area':
		$conditions=' AND if(TargetShareWith=0,ut.BranchNo='.$field['TargetShareWith'].',TargetShareWith='.$field['TargetShareWith'].')';
		$name=$field['TargetShareWith'];
		break;
		case 'Per Company':
		$conditions=' AND CompanyNo='.$field['CompanyNo'].'';
		$name=$field['CompanyNo'];
		break;
		}
		}else{
			$conditions=' AND if(TargetShareWith=0,ut.BranchNo='.$field['TargetShareWith'].',TargetShareWith='.$field['TargetShareWith'].')';
		$name=$field['TargetShareWith'];
		}
	for ($i = 1; $i <= date('m'); $i++) {
		$i=(strlen($i)<>2?'0'.$i:$i);
		
		
		// $sql0='SELECT ifnull(sum(ut.Amount)/((sum(ut.Amount)-sum(BegBalance))/2),0) as Turnover FROM acctg_0unialltxns ut join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID join 1branches b on b.BranchNo=ut.BranchNo join acctg_1begbal bb on bb.BranchNo=ut.BranchNo where AccountType=101 '.$conditions.' AND month(ut.Date)='.$i.' '; //echo $sql0.'<br>';
		$sql0='SELECT ifnull(sum(Amount)/(((select ifnull(sum(amount),0) from acctg_0unialltxns ut join acctg_1chartofaccounts  ca on ca.AccountID=ut.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType=4 '.$conditions.' and month(Date)='.$i.')+(select ifnull(sum(amount),0) from acctg_0unialltxns ut join acctg_1chartofaccounts  ca on ca.AccountID=ut.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType=4 '.$conditions.' and month(Date)='.$i.'-1))/2),0) as Turnover FROM acctg_0unialltxns ut join acctg_1chartofaccounts  ca on ca.AccountID=ut.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType in (101) '.$conditions.' AND month(ut.Date)='.$i.'';
		// echo $sql0; exit();
		$stmt=$dbtouse->query($sql0); $res=$stmt->fetch();
		
		
		
		
		if($stmt->rowCount()==0){
			$dataset1.='0,';
		
		} else {
			$dataset1.=$res['Turnover'].',';
			// echo $dataset1; exit();
			
		}
		
		$flset1.='3.7,';
		
	}
	
	$dataset1=substr($dataset1, 0, -1);

	$flset1=substr($flset1, 0, -1);

		// echo $dataset1;
		// echo $flset1; exit();
	
	
	$sqlcsub='INSERT INTO graphboard11 SET GraphID=1,DataSet1="'.$dataset1.'",LineOnly="'.$flset1.'",IDNo='.$name.',ReportID=5;';
	$stmt=$link->prepare($sqlcsub);$stmt->execute(); 
	// echo $sqlcsub.'<br>';  
	//END
}

//report 5  STL Personal targets
$sqlcmain='INSERT INTO graphreport11 SET xaxis="Branch",legend1="TurnOver Rate",lineonly="Target",yaxis="In Ones",min="",'.$ReportTitle.',ReportID=5,Label="'.$inclabel.'"';

// echo $sqlcmain; exit();
$stmt=$link->prepare($sqlcmain);$stmt->execute();
//End

$sql = 'SELECT g.*,gr.*,'.$select.' FROM graphboard11 g '.$leftjoin.' JOIN graphreport11 gr ON g.ReportID=gr.ReportID WHERE g.ReportID IN (5) '.$condi.' ';
// echo $sql; exit();
$stmt=$dbtouse->query($sql); $res=$stmt->fetchall();

$sqldrop='DROP TEMPORARY TABLE graphboard11';
$stmt=$link->prepare($sqldrop);$stmt->execute();

$sqldrop='DROP TEMPORARY TABLE graphreport11';
$stmt=$link->prepare($sqldrop);$stmt->execute();
$bwidth='39%';

break;
}

$c=1;
$displaydiv=''; $newdiv=''; 
foreach ($res as $field) {
	 if ($field['GraphID']==2){
		include($path.'/acrossyrs/js/reportcharts/line.php');
	 }else if ($field['GraphID']==1){
		include($path.'/acrossyrs/js/reportcharts/vbar.php');
	}
	
$c++;	 
 
} 
	echo $displaydiv;
	echo '<script>';
	echo 'window.onload = function() {';
	echo $echo;
	echo '}';	
	echo '</script>';
	
	$link=null; $stmt=null; $dbtouse=null;
?>