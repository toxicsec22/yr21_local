<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(58220,'1rtc')) { echo 'No permission'; exit();}
include_once $path.'/acrossyrs/dbinit/userinit.php';
$showbranches=false;
include_once('../switchboard/contents.php');
// $dbtouse=$link;
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';


$which=(!isset($_GET['w'])?'Lists':$_GET['w']);

switch ($which){
	
	case 'Lists':


			echo comboBox($link,'SELECT BranchNo, Branch FROM 1branches WHERE BranchNo>=0 AND Active<>0 ORDER BY Branch','BranchNo','Branch','branchlist');
			echo comboBox($link,'SELECT CompanyNo, CompanyName FROM 1companies  ORDER BY CompanyNo','CompanyNo','CompanyName','companylist');
			echo '<title>Financial Ratios</title><h3>Financial Ratios</h3></br>';
			
//start enablebasedonradio	
			$radionamefield='radiolist';	
			echo '<form id="form-id">
			Per Branch <input type="radio" id="watch-me1" name="'.$radionamefield.'">
			Per Company <input type="radio" id="watch-me2" name="'.$radionamefield.'">
			All <input type="radio" id="watch-me3" name="'.$radionamefield.'">
			</form>
			</br>';
			$formaction='<form method="post" action="financialratios.php?w=Lists">';
			$all='<input type="hidden" name="All">';
			$branchinput='Branch <input type="text" name="BranchNo" list="branchlist">';
			$companyinput='Company <input type="text" name="CompanyNo" list="companylist">';
			
			//perbranch
			echo '<div id="show-me1" style="display:none">
					'.$formaction.'
					'.$branchinput.'
					Choose Month(1 - 12): <input type="text" name="month" required size="3">
					<input type="submit" name="submit"> 
				</form>
				</div>';
			
			//per company
			echo '<div id="show-me2" style="display:none">
					'.$formaction.'
					'.$companyinput.'
					Choose Month(1 - 12): <input type="text" name="month" required size="3">
					<input type="submit" name="submit"> 
				</form>
				</div>';
			
			//all
			echo '<div id="show-me3" style="display:none">
					'.$formaction.'
					'.$all.'
					Choose Month(1 - 12): <input type="text" name="month" required size="3">
					<input type="submit" name="submit"> 
				</form>
				</div>';				
			
			include $path.'/acrossyrs/commonfunctions/enablebasedonradio.php';	
//end
			$sqldmonth='select month(DataClosedBy) as DefaultMonth from 00dataclosedby where ForDB=1';
			$stmtmonth=$link->query($sqldmonth);
			$resultdmonth=$stmtmonth->fetch();
			
			if(isset($_POST['submit'])){
				if(isset($_POST['All'])){
					$condition='';
					echo'All </br>';
				}elseif(isset($_POST['BranchNo'])){
					$branchno=companyandbranchValue($link, '1branches', 'Branch', $_POST['BranchNo'], 'BranchNo');
					$condition='AND b.BranchNo='.$branchno.'';
					echo $_POST['BranchNo']; echo '</br>';
				}elseif(isset($_POST['CompanyNo'])){
					$companyno=companyandbranchValue($link, '1companies', 'CompanyName', $_POST['CompanyNo'], 'CompanyNo');	
					$condition='AND CompanyNo='.$companyno.'';
					echo $_POST['CompanyNo']; echo '</br>';
				}
				
				if($_POST['month']!=null){
				$month=$_POST['month'];
				$month=(strlen($month)<>2?'0'.$month:$month);

					if($_POST['month']!=null){
						echo $month; 
					}
				
				}
				
			//total equity
			$sqlte='select ifnull(format(sum(Amount),2),0) as TotalEquity,ifnull(TRUNCATE(sum(Amount),2),0) as TotalEquityValue from acctg_0unialltxns ut join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType=11 '.$condition.' AND month(ut.Date)='.$month.' ';
			// echo $sqlte; exit();
			$stmtte=$link->query($sqlte);
			$resultte=$stmtte->fetch();
			
			// echo $sqlte;exit();
			
			//Total Asset
			$sqlta='select ifnull(format(sum(Amount),2),0) as TotalAsset,ifnull(TRUNCATE(sum(Amount),2),0) as TotalAssetValue from acctg_0unialltxns ut join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType in (1,2,3,4,5,6,7) '.$condition.' AND month(ut.Date)='.$month.'';
			$stmtta=$link->query($sqlta);
			$resultta=$stmtta->fetch();
				
			//Receivable Turnover	
			$sqlrt='select ifnull(format((sum(Qty*UnitPrice)/( ((select sum(Amount) from acctg_0unialltxns ut join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID where AccountType in (200,201,202) '.$condition.' AND month(ut.Date)=('.$month.'-1))+(select sum(Amount) from acctg_0unialltxns ut join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType in (200,201,202) '.$condition.' AND month(ut.Date)='.$month.'))/2 )),2),0) as Rturnover from invty_2sale s join invty_2salesub ss on ss.TxnID=s.TxnID join 1branches b on b.BranchNo=s.BranchNo where PaymentType=2 '.$condition.' AND month(s.Date)='.$month.'';
			$stmtrt=$link->query($sqlrt);
			$resultrt=$stmtrt->fetch();
			
			//Days Sales Outstanding
			$dso=365/$resultrt['Rturnover'];
			// $dso=intval($dso,0).' days';
			
			//Average Total Asset
			$sqlata='select ifnull(format(( ((select sum(Amount) from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType in (1,2,3,4,5,6,7) '.$condition.' AND month(ut.Date)=('.$month.'-1))+(select (sum(Amount)) from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType in (1,2,3,4,5,6,7) '.$condition.' AND month(ut.Date)='.$month.'))/2 ),2),0) as AveTA,ifnull(truncate(( ((select sum(Amount) from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType in (1,2,3,4,5,6,7) '.$condition.' AND month(ut.Date)=('.$month.'-1))+(select (sum(Amount)) from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType in (1,2,3,4,5,6,7) '.$condition.' AND month(ut.Date)='.$month.'))/2 ),2),0) as AveTAValue';
			$stmtata=$link->query($sqlata);
			$resultata=$stmtata->fetch();
			
			//Current Ratios
			$sqlcr='select truncate(( (select ifnull(sum(Amount),0) from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType in (1,2,3,4,5) '.$condition.' AND month(ut.Date)='.$month.')/(select ifnull(sum(Amount),0) from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType in (8,9) '.$condition.' AND month(ut.Date)='.$month.') ),2) as CurrentRatios';
			$stmtcr=$link->query($sqlcr);
			$resultcr=$stmtcr->fetch();
			// echo $resultcr['CurrentRatios']; exit();
			
			//Cash Ratio
			$sqlchr='select truncate(( (select ifnull(sum(Amount),0) from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType=1 '.$condition.' AND month(ut.Date)='.$month.')/(select ifnull(sum(Amount),0) from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType in (8,9) '.$condition.' AND month(ut.Date)='.$month.') ),2) as CashRatios';
			$stmtchr=$link->query($sqlchr);
			$resultchr=$stmtchr->fetch();
			
			//Debt Ratio
			$sqldr='select truncate(( (select ifnull(sum(Amount),0) from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType in (8,9,10) '.$condition.' AND month(ut.Date)='.$month.')/(select ifnull(sum(Amount),0) from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType in (1,2,3,4,5,6,7) '.$condition.' AND month(ut.Date)='.$month.') ),2) as DebtRatio ';
			$stmtdr=$link->query($sqldr);
			$resultdr=$stmtdr->fetch();
			
			//Accounts Payable Turnover
			$sqlapt='select ifnull(truncate(sum(Amount)/( ((select ifnull(sum(Amount),0) from acctg_0unialltxns ut join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID where ut.AccountID=400 '.$condition.' AND month(ut.Date)=('.$month.'-1))+(select ifnull(sum(Amount),0) from acctg_0unialltxns ut join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID where ut.AccountID=400 '.$condition.' AND month(ut.Date)='.$month.'))/2 ),2),0) as AccountPayableTurnover from acctg_2purchasemain pm join 1suppliers s on s.SupplierNo=pm.SupplierNo join acctg_2purchasesub ps on ps.TxnID=pm.TxnID join 1branches b on b.BranchNo=pm.BranchNo where InvtySupplier=1 '.$condition.' AND month(pm.Date)='.$month.' ';
			$stmtapt=$link->query($sqlapt);
			$resultapt=$stmtapt->fetch();
			
			//Acid Test Ratio
			$sqlatr='select truncate(( (select ifnull(sum(Amount),0) from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where (AccountType=4) '.$condition.' AND month(ut.Date)='.$month.')/(select ifnull(sum(Amount),0) from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where ut.AccountID=400 '.$condition.' AND month(ut.Date)='.$month.') ),2) as AcidTestRatio';
			$stmtatr=$link->query($sqlatr);
			$resultatr=$stmtatr->fetch();
			// echo $resultatr['AcidTestRatio']; echo '<br>'; exit();
				
			//Inventory Turnover
			$sql='SELECT ifnull(truncate(sum(Amount)/(((select ifnull(sum(amount),0) from acctg_0unialltxns ut join acctg_1chartofaccounts  ca on ca.AccountID=ut.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType=4 '.$condition.' AND month(ut.Date)='.$month.')+(select ifnull(sum(amount),0) from acctg_0unialltxns ut join acctg_1chartofaccounts  ca on ca.AccountID=ut.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType=4 '.$condition.' AND month(ut.Date)=('.$month.'-1)))/2),2),0) as Turnover FROM acctg_0unialltxns ut join acctg_1chartofaccounts  ca on ca.AccountID=ut.AccountID join 1branches b on b.BranchNo=ut.BranchNo where AccountType in (101) '.$condition.' AND month(ut.Date)='.$month.'';
			$stmt=$link->query($sql);
			$result=$stmt->fetch();
			
			//Days Inventory Outstanding
			$dio=365/$result['Turnover'];
			// echo $dio; exit();
			
			//Net Sales
			$sqlns='select ifnull(format(sum(Amount*-1),2),0) as NetSales,ifnull(TRUNCATE(sum(Amount*-1),2),0) as NetSalesValue from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where (AccountType=100 or ut.AccountID=810) '.$condition.' AND month(ut.Date)='.$month.''; 
			$stmtns=$link->query($sqlns); $resns=$stmtns->fetch();
			
			//Gross Profit
			$sqlgp='select ifnull(format(sum(Amount*-1),2),0) as GrossProfit,ifnull(TRUNCATE(sum(Amount*-1),2),0) as GrossProfitValue from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where (AccountType=100 or AccountType=101 or ut.AccountID=810) '.$condition.' AND month(ut.Date)='.$month.''; 
			$stmtgp=$link->query($sqlgp); $resgp=$stmtgp->fetch();
			
			//Gross Profit Rate
			$gpr=($resgp['GrossProfitValue']/$resns['NetSalesValue']);
			$gpr=number_format($gpr,2).'%';
			
			//Net Income
			$sqlni='select ifnull(format(sum(Amount*-1),2),0) as NetIncome,ifnull(TRUNCATE(sum(Amount*-1),2),0) as NetIncomeValue from acctg_1chartofaccounts ca join acctg_0unialltxns ut on ut.AccountID=ca.AccountID join 1branches b on b.BranchNo=ut.BranchNo where (AccountType=210 or AccountType=200 or AccountType=220 or AccountType=201 or AccountType=230 or AccountType=150 or AccountType=100 or AccountType=101 or AccountType=240 or AccountType=250) '.$condition.' AND month(ut.Date)='.$month.' '; 
			$stmtni=$link->query($sqlni); $resni=$stmtni->fetch();
			$ros=($resni['NetIncomeValue']/$resns['NetSalesValue']);
			$ros=number_format($ros,2).'%';
			// echo $pni; exit();
			
			//Return on Asset
			$roa=($resni['NetIncomeValue']/$resultata['AveTAValue']);		
			$roa=number_format($roa,2).'%';
			// echo $roa; exit();
			
			//Equity Ratio
			$er=($resultte['TotalEquityValue']/$resultta['TotalAssetValue']);
			$er=number_format($er,2).'%';
			
			//Asset Turnover Ratio
			$atr=($resns['NetSalesValue']/$resultata['AveTAValue']);
			$atr=number_format($atr,0);
			
			//Days Payable Outstanding
			$dpo=(365/$resultapt['AccountPayableTurnover']);
			// $dpo=intval($dpo,0).' days';
			
			//Operating Cycle
			$oc=($dio+$dso);
			// echo $dpo; exit();
			
			//Cash Conversion Cycle
			$ccc=($oc-$dpo);
			
				

			?>
			<title>Financial Ratios</title>
			<style>
table {
  border-collapse: collapse;
  width: 100%;
  font-size:10pt;
  border:1px solid black;
  background-color:white;
}

th, td {
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {background-color: #f2f2f2;}
			</style>
			
			<?php

			echo '<table>
			<tr><th>Ratio</th><th>Formula</th><th>Results</th><th>Standard/Ideal</th><th>Purpose</th></tr>
			<tr><td><b>Liquidity Ratios</b></td><td></td><td></td><td></td><td></td></tr>
			<tr><td>Current Ratios</td><td style="white-space: pre;">Current assets ÷ Current liabilities</td><td><b>'.number_format($resultcr['CurrentRatios'],0).'</b></td><td>2 : 1</td><td>Evaluates the ability of a company to pay short-term obligations using current assets</td></tr>
			<tr><td>Acid-test ratio</td><td>Inventories ÷ APTrade</td><td><b>'.number_format($resultatr['AcidTestRatio'],0).'</b></td><td>1:1</td><td>Also known as "quick ratio", it measures the ability of a company to pay short-term obligations</td></tr>
			<tr><td>Cash ratio</td><td style="white-space: pre;">Cash and Cash equivalents ÷ Current Liabilities</td><td><b>'.number_format($resultchr['CashRatios'],0).'</b></td><td>0.5 : 1</td><td>Measures the ability of a company to pay its current liabilities using cash</td></tr>
			<tr><td style="white-space: pre;"><b>Management Efficiency Ratios</b></td><td></td><td></td><td></td><td></td></tr>
			<tr ><td>Asset turnover ratio</td><td>Net sales ÷ Ave. Total assets</td><td><b>'.$atr.'</b></td><td></td><td>Measures overall efficiency of a company in generating sales using its assets. </td></tr>
			<tr><td>Receivable Turnover</td><td style="white-space: pre;">Net Credit Sales ÷ Average Accounts Receivable</td><td><b>'.number_format($resultrt['Rturnover'],0).'</b></td><td>11</td><td>Measures the efficiency of extending credit and collecting the same. It indicates the average number of times in a year a company collects its open accounts. A high ratio implies efficient credit and collection process.</td></tr>
			<tr><td>Days Sales Outstanding</td><td>365 Days ÷ Receivable Turnover</td><td><b>'.number_format($dso,0).' days'.'</b></td><td>31 days below</td><td>Also known as "receivable turnover in days", "collection period". It measures the average number of days it takes a company to collect a receivable. The shorter the DSO, the better.</td></tr>
			<tr><td>Inventory Turnover</td><td> Cost of Sales ÷ Average Inventory</td><td><b>'.$result['Turnover'].'</b></td><td>4 to 6</td><td>Represents the number of times inventory is sold and replaced.  A high ratio indicates that the company is efficient in managing its inventories.</td></tr>
			<tr><td>Days Inventory Outstanding</td><td>365 Days ÷ Inventory Turnover</td><td><b>'.number_format($dio,0).' days'.'</b></td><td>60 to 90 days</td><td>Also known as "inventory turnover in days". It represents the number of days inventory sits in the warehouse. In other words, it measures the number of days from purchase of inventory to the sale of the same. Like DSO, the shorter the DIO the better.</td></tr>
			<tr><td>Accounts Payable Turnover</td><td> Net Credit Purchases ÷ Ave. Accounts Payable</td><td><b>'.$resultapt['AccountPayableTurnover'].'</b></td><td>3</td><td>Represents the number of times a company pays its accounts payable during a period. A low ratio is favored because it is better to delay payments as much as possible so that the money can be used for more productive purposes.</td></tr>
			<tr><td>Days Payable Outstanding</td><td>365 Days ÷ Accounts Payable Turnover</td><td><b>'.number_format($dpo,0).' days'.'</b><td>120 days</td></td><td>Also known as "accounts payable turnover in days", "payment period". It measures the average number of days spent before paying obligations to suppliers. Unlike DSO and DIO, the longer the DPO the better</td></tr>
			<tr><td>Operating Cycle</td><td>Days Inventory Outstanding + Days Sales Outstanding</td><td><b>'.number_format($oc,0).' days'.'</b></td><td>90 to 120 days</td><td>Measures the number of days a company makes 1 complete operating cycle, i.e. purchase merchandise, sell them, and collect the amount due. A shorter operating cycle means that the company generates sales and collects cash faster.</td></tr>
			<tr ><td>Cash Conversion Cycle</td><td>Operating Cycle - Days Payable Outstanding</td><td><b>'.number_format($ccc,0).'</b></td><td>90</td><td>CCC measures how fast a company converts cash into more cash. It represents the number of days a company pays for purchases, sells them, and collects the amount due. Generally, like operating cycle, the shorter the CCC the better.</td></tr>
			<tr><td><b>Leverage Ratios</b></td><td></td><td></td><td></td><td></td></tr>
			<tr><td>Debt Ratio</td><td>Total Liabilities ÷ Total Assets</td><td><b>'.number_format($resultdr['DebtRatio'],2).' %'.'</b></td><td>40% below</td><td>Total Liabilities ÷ Total Assets</td></tr>
			<tr><td>Equity Ratio</td><td>Total Equity ÷ Total Assets</td><td><b>'.$er.'</b></td><td>1 : 1</td><td>Determines the portion of total assets provided by equity</td></tr>
			<tr><td><b>Profitability Ratios</b></td><td></td><td></td><td></td><td></td></tr>
			<tr><td>Gross Profit Rate</td><td>Gross Profit ÷ Net Sales</td><td><b>'.$gpr.'</b></td><td>70%</td><td>Evaluates how much gross profit is generated from sales.</td></tr>
			<tr><td>Return on Sales</td><td>Net Income ÷ Net Sales</td><td><b>'.$ros.'</b></td><td>10% to 15%</td><td>Also known as "net profit margin" or "net profit rate", it measures the percentage of income derived from dollar sales. Generally, the higher the ROS the better.</td></tr>
			<tr><td>Return on Assets</td><td>Net Income ÷ Average Total Assets</td><td><b>'.$roa.'</b></td><td>5%</td><td>it is the measure of the return on investment. ROA is used in evaluating management\'s efficiency in using assets to generate income.</td></tr>
			</table>';
			
	}
	break;
	
	
	
}
?>