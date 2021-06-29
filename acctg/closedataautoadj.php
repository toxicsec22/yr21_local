<html>
<head>
<title>Data Closing</title>
<?php
	$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	
        // check if allowed
        $allowed=array(6455,6456); $allow=0;
        foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
        if ($allow==0) { echo 'No permission'; exit;}
        allowed:
        // end of check
        $showbranches=false;    
        include_once('../switchboard/contents.php');
        include('../backendphp/functions/getnumber.php');
        include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 


        function checkExists($stringtomatch,$field,$table,$txnidname,$link){
            $sql='SELECT `'.$txnidname.'` FROM `'.$table.'` WHERE '.$field.' LIKE \'%'.$stringtomatch.'%\'';	
            $stmt=$link->query($sql);
            $result=$stmt->fetch();
            if ($stmt->rowCount()>0){ return $result[$txnidname]; } else {   return 0;	}
        }


        $sql0='SELECT CONCAT(IF(ForDB=0,"Inventory","Accounting"),":  ",`DataClosedBy`) AS ToShow FROM `00dataclosedby` WHERE ForDB IN (0,1)';
        $stmt=$link->query($sql0); $resasof=$stmt->fetchAll(); 
        echo '<br><br><h2 style="color: maroon; border: solid 1.5px; padding: 20px; text-align:center;">Data protected as of --'.str_repeat('&nbsp;', 10);
        foreach($resasof as $date) { echo $date['ToShow'].str_repeat('&nbsp;', 10);}
        echo 'Protected for Acctg: '.$_SESSION['nb4A'];
        echo '</h2><br><br>';
        ?>
</head>
    <br><br><div style="color: maroon; border: solid 1.5px; padding: 20px;" >
    <h2>Update Static Data</h2><br><br>
    <form action='#' method='POST'>
    Choose month (1-12)<input type='text' name='month' autocomplete='off' size='2' style="text-align: center" value="<?php echo (date('m')==1?12:(date('m')-1)); ?>">
    <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>">
    <input type='submit' name='submit' value='Update Static Data - Acctg Only'>&nbsp; &nbsp; &nbsp;
    <input type='submit' name='submit' value='Update Static Data - including data for special accounts'></form>
    </div>
<br><br><div style="color: maroon; border: solid 1.5px; padding: 20px;" >
    <h2>Protect Data</h2><br><br>
<form action='#' method='POST'>
    For the month (1-12)<input type='text' name='month' autocomplete='off' size='2' style="text-align: center" value="<?php echo substr($_SESSION['nb4A'],5,2); ?>"><br>
    <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>"><br>
    <?php
    if (isset($_GET['done'])){
	switch ($_GET['done']){
		case 0: echo '<font color=red><b>'.$_GET['w'].':  Rejected.  Data is protected.</b></font>';break;
		case 1: echo '<font color=darkblue><b>'.$_GET['w'].':  Done successfully.</b></font>'; break;
		case 2: echo '<font color=red><b>'.$_GET['w'].':  Rejected.  Data already exists.</b></font>';break;
		case 3: echo '<font color=red><b>'.$_GET['w'].':  Rejected.  No recorded weighted average costs for closing month.</b></font>';break;
		default: break;
	}
}
    ?><br><br>
    Beginning of the month setting of weighted average costs are done automatically at 10:30 am on the first day of every month, except January.<br>
    This may be done as needed for updating weighted average costs at any time.  Every update, including the auto-update monthly, recalculates the past months.
    <input type='submit' name='submit' value='Update Weighted Average Costs'><br><br>
    
    <input type='submit' name='submit' value='Update Closed-By Date for Invty'><br>
    &nbsp &nbsp &nbsp &nbsp &nbsp Actions done:<br>
    &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp 1. Updates date of protected data for invty. All users will be updated only when they login again.<br>
    &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp 2. Creates fixed data of interbranch transferred items as of protected date.  This may take a few minutes. Pls make sure it is done completely.<br><br><hr><br>
    <input type='submit' name='submit' value='Step 0. Accept Store Used'><br><br>
    <input type='submit' name='submit' value='Step 0. Set FreightINCL as confirmed if there are no errors shown.'><br><br>
    <input type='submit' name='submit' value='Step 1. Record 13th month accrual'><br><br>
    &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp 13th month pay is approximated based on salaries given for the period.  Rationale for this is to distribute the 13th month expense throughout the year as accrued expense, because (1) this has been earned by employees already, so technically an expense already, and (2) this will "unburden" December, since the expense is not solely a December expense.<br><br>
    <a href='closingdetailsoverhead.php?w=OHShare' target='_blank'><b>Step 2. Distribute Overhead</b></a><br><br>
    &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp 1. Overhead of warehouses are distributed to client-branches according to values of transfers for the month.<br>
    &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp 2. Overhead of pseudobranches are distributed to all branches according to sales for the month.
    <br><br>
    <input type='submit' name='submit' value='Step 3.  Return Profit of Transfers and Adjust InTransit'><br><br>
    <style>
    .drcr td,th { font-size: 12px;}
    .drcr td {background-color: white; padding: 3px;}
    </style>
    <table class="drcr">
    <thead><th></th><th>Debit</th><th>Credit</th></thead>
    <tr><td>FROM Warehouse</td><td>Inventory</td><td>Reconciliation</td></tr>
    <tr><td>TO Branch</td><td>Reconciliation</td><td>Inventory</td></tr>
    <tr><td colspan="3">If month of DateOUT <> month of DateIN</td></tr>
    <tr><td>TO Branch on DateOUT</td><td>Reconciliation</td><td>Inventory In Transit</td></tr>
    <tr><td>TO Branch on DateIN</td><td>Inventory In Transit</td><td>Inventory</td></tr>
</table><br/><br/>
    &nbsp &nbsp &nbsp &nbsp &nbsp Adjustments include:<br>
    &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp 1. Return profits to FROM Branch/Warehouse (whether received or not).<br>
    &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp 2. Add back values of inventory in transit, since the items have not arrived.<br><br>
    &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp Profits are calculated based on the weighted average cost ending last month.<br><br>
    <a href='closingdetailscogs.php?w=COGSList' target='_blank'><b>Step 4. Record COGS</b></a><br><br>           
    &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp Make adjustments to update Acctg's ending inventory values (good and defective)<br><br>
    
    <input type='submit' name='submit' value='Step 5.  Pay Interbranch Transfers AFTER ALL ACCTG ADJUSTMENTS'><br><br>
    
    <input type='submit' name='submit' value='Step 6. Update Closed-By Date for Acctg'><br><br>
    &nbsp &nbsp &nbsp &nbsp &nbsp Actions done:<br>
    &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp 1. Updates date of protected data for acctg. All users will be updated only when they login again.<br>
    &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp 2. Creates fixed data for all transactions as of protected date, for faster queries.<br>
    &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp 3. Updates closing data of special accounts and used series for monitoring.<br><br><br><br>
    
    <input type='submit' name='submit' value='Step 7.  Consolidate CIB for Branches per Company AFTER static update'><br><br>
    <input type='submit' name='submit' value='Update Static Data - Acctg Only'><br><br>
    <input type='submit' name='submit' value='Step 8.  Consolidate CIB for PseudoBranches per Company AFTER static update'><br><br>
    <input type='submit' name='submit' value='Update Static Data - Acctg Only'><br><br>
    <input type='submit' name='submit' value='Step 9.  Zero Recon of Non-Pseudo AFTER static update'><br><br>
    <input type='submit' name='submit' value='Update Static Data - Acctg Only'><br><br>
    <b>DONE!</b>
</form></div><br>

<?php
if (!isset($_REQUEST['month'])){    goto noform;} else {
require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
$month=$_REQUEST['month']; $reportmonth=$month;
$lastdayofmonth=$reportmonth==0?date("Y-m-t",strtotime(($currentyr-1).'-12-31')):date("Y-m-t",strtotime(''.$currentyr.'-'.$reportmonth.'-10'));
}


switch ($_POST['submit']){

case 'Update Weighted Average Costs':
        if(allowedToOpen(6455,'1rtc')){	include('maketables/makeweightedavecosts.php');}
	header("Location:/yr".substr($currentyr,2,2)."/acctg/closedataautoadj.php?w=WtdAveCosts&done=1");
    break;
    
case "Step 0. Accept Store Used":
    if(allowedToOpen(6455,'1rtc')){
    $sql='Select Date, Month(Date) from `invty_2mrr` where txntype=9 and Month(Date)='.$month.' and Posted=1 group by Month(Date)';

	$stmtmain=$link->query($sql); $resultmain=$stmtmain->fetch();
	if ($stmtmain->rowCount()>0){
			// check adj number if exists, create if dne
            $stringtomatch=(date('y',strtotime($resultmain['Date']))).'SU-'.$month;
            $jvno=checkExists($stringtomatch,'Remarks','acctg_2jvmain','JVNo',$link);
            
	            if ($jvno==0){     
                    
                $jvno=lastNum('JVNo','acctg_2jvmain',((date('Y',strtotime($currentyr.'-01-01')))-2000)*10000+1000000)+1;
				$sqlmain='INSERT INTO `acctg_2jvmain` SET  JVNo=\''.$jvno.'\', Posted=0, JVDate=Last_Day(\''.$resultmain['Date'].'\'), Remarks=\''.$stringtomatch.'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
		        $stmt=$link->prepare($sqlmain); $stmt->execute();  
			} 
			
			// end of jvno
			// add sub per main
                        
			$sqlsub='Select MRRNo, BranchNo,Sum(Qty*UnitCost)*-1 as Amount from `invty_2mrr` m join `invty_2mrrsub` ms on m.TxnID=ms.TxnID where (MRRNo not in (Select Particulars from `acctg_2jvsub` where Particulars is not null)) and txntype=9 and  Month(Date)='.$month.'  group by m.TxnID'; // echo $sqlsub;break;
			$stmtsub=$link->query($sqlsub); $resultsub=$stmtsub->fetchAll();
			foreach ($resultsub as $sub){
				$sqlinsert='Insert into `acctg_2jvsub` SET JVNo='.$jvno.', Date=Last_Day(\''.$resultmain['Date'].'\'), Particulars=\''.$sub['MRRNo'].'\', BranchNo='.$sub['BranchNo'].', FromBudgetOf='.$sub['BranchNo'].', DebitAccountID=919, CreditAccountID=300, Amount='.($sub['Amount']).',  EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();';
	    $stmt=$link->prepare($sqlinsert);
	    $stmt->execute();
            $done=1;
            }
			// end of sub
			
		}
		
    header("Location:formjv.php?w=JV&JVNo=".$jvno."&done=".$done); }
    break;

case 'Step 0. Set FreightINCL as confirmed if there are no errors shown.':
    if(allowedToOpen(6456,'1rtc')){
    $sql0='UPDATE `approvals_2freightclients` SET `Confirmed`=1 WHERE `PriceFreightInclusive`=1;';
    $link->query($sql0); 
    header("Location:/yr".substr($currentyr,2,2)."/acctg/closedataautoadj.php?w=FreightConfirmation&done=1");}
    break;

case "Step 1. Record 13th month accrual":
    if(allowedToOpen(6455,'1rtc')){

        
    if ($month==0) { header("Location:/yr".substr($currentyr,2,2)."/acctg/closedataautoadj.php?w=NoRecord&done=1"); }
    else {
        // check adj number if exists, create if dne
    $stringtomatch=(date('y',strtotime($lastdayofmonth))).'-13th-'.str_pad($month,2,'0',STR_PAD_LEFT);

    $jvno=checkExists($stringtomatch,'Remarks','acctg_2jvmain','JVNo',$link);
           
	            if ($jvno==0){     
                  
                $jvno=lastNum('JVNo','acctg_2jvmain',((date('Y',strtotime($currentyr.'-01-01')))-2000)*10000+1000000)+1;
                $done=1;
                $sqlmain='INSERT INTO `acctg_2jvmain` SET  JVNo='.$jvno.',Posted=0, JVDate=\''.$lastdayofmonth.'\', Remarks=\''.$stringtomatch.'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
		        $stmt=$link->prepare($sqlmain); $stmt->execute();  
         
        include('maketables/adjust13th.php');
        $sqlsub='INSERT INTO `acctg_2jvsub` (`Date`,`DebitAccountID`,`CreditAccountID`,`Amount`,`TimeStamp`,`FromBudgetOf`,`BranchNo`,`EncodedByNo`,`JVNo`)
SELECT \''.$lastdayofmonth.'\', tc.AccountID, 500, TRUNCATE((`13thBasicAsOf`-`TotalRecorded`),2) AS `Amount`, Now(), `BranchNo`, `BranchNo`, \''.$_SESSION['(ak0)'].'\', '.$jvno.' FROM paidorrecorded pr JOIN totalcalculated tc ON pr.BranchNo=tc.RecordInBranchNo AND pr.AccountID=tc.AccountID HAVING `Amount`>0;'; 
        $stmtsub=$link->prepare($sqlsub); $stmtsub->execute();
        } else {
            $done=2;
        }
    }
	existing13th:			
    header("Location:formjv.php?w=JV&JVNo=".$jvno."&done=".$done);}
    break;
    
case 'Step 3.  Return Profit of Transfers and Adjust InTransit':
    
    if(!allowedToOpen(6456,'1rtc')){ echo 'No permission'; exit();}
        $wacmonth=str_pad(($reportmonth==00?$reportmonth:($reportmonth-1)),2,'0',STR_PAD_LEFT);
        $sql0='CREATE TEMPORARY TABLE txfrcosts AS ';
        
        $sqlfrom=' AS SameMonth, MONTH(tm.DateOUT) AS `Month`,tm.BranchNo AS FromBranchNo,tm.ToBranchNo, round(Sum((if(isnull(wac.ItemCode),UnitPrice,`'.$wacmonth.'`))*QtySent),2) AS Cost, round((Sum(UnitPrice*QtySent)-Sum((if(isnull(wac.ItemCode),UnitPrice,`'.$wacmonth.'`))*QtySent)),2) AS Profit, bfrom.CompanyNo AS FromCompanyNo, bto.CompanyNo AS ToCompanyNo, bfrom.Pseudobranch AS FromPseudo 
FROM  `invty_2transfer` tm JOIN `invty_2transfersub` ts ON tm.TxnID = ts.TxnID
JOIN `1branches` bfrom ON bfrom.BranchNo=tm.BranchNo JOIN `1branches` bto ON bto.BranchNo=tm.ToBranchNo
left join `'.$currentyr.'_static`.`invty_weightedavecost` wac on ts.ItemCode=wac.ItemCode ';
        
        // first : where Month(DateIN)=Month(DateOUT)
        $sql0.='SELECT 1 '.$sqlfrom.' WHERE tm.BranchNo<>tm.ToBranchNo  AND YEAR(tm.DateOUT)='.$currentyr.'  AND YEAR(tm.DateIN)='.$currentyr.' AND MONTH(tm.DateIN)=MONTH(tm.DateOUT) AND (tm.DateIN IS NOT NULL) AND MONTH(tm.DateOUT)='.$reportmonth.'  AND (tm.DateOUT)>(IF('.$reportmonth.'=1,\''.$lastyr.'-12-31\',(LAST_DAY(\''.$currentyr.'-'.($reportmonth-1).'-1\')))) GROUP BY MONTH(tm.DateOUT),tm.BranchNo,tm.ToBranchNo HAVING Profit<>0 ';
        
        // Add where Month(DateIN) IS NULL OR Month(DateIN)<>Month(DateOUT)
        $sql0.=' UNION SELECT 2 '.$sqlfrom.'
WHERE tm.BranchNo<>tm.ToBranchNo  AND YEAR(tm.DateOUT)='.$currentyr.'  AND ((tm.DateIN IS NULL) OR (Month(tm.DateIN)<>MONTH(tm.DateOUT))) AND MONTH(tm.DateOUT)='.$reportmonth.'  AND (tm.DateOUT)>(IF('.$reportmonth.'=1,\''.$lastyr.'-12-31\',(LAST_DAY(\''.$currentyr.'-'.($reportmonth-1).'-1\')))) GROUP BY MONTH(tm.DateOUT),tm.BranchNo,tm.ToBranchNo HAVING Profit<>0 ';
        
        $m=1; $sqlcase=' CASE WHEN YEAR(tm.DateOUT)<>'.$currentyr.' THEN wac.`00` ';
        while ($m<=$reportmonth){
            $sqlcase.=' WHEN MONTH(tm.DateOUT)='.$m.' THEN wac.`'.str_pad(($m-1), 2, '0', STR_PAD_LEFT).'` ';
            $m++;
        }
        
        // Add where Month(DateIN) IS NOT NULL AND Month(DateIN)<>Month(DateOUT)
        $sql0.=' UNION SELECT 3 AS SameMonth, MONTH(tm.DateIN) AS `Month`,tm.BranchNo AS FromBranchNo,tm.ToBranchNo, round(Sum((if(isnull(wac.ItemCode),UnitPrice,('.$sqlcase.' END)))*QtySent),2) AS Cost, round((Sum(UnitPrice*QtySent)-Sum((if(isnull(wac.ItemCode),UnitPrice,('.$sqlcase.' END)))*QtySent)),2) AS Profit, bfrom.CompanyNo AS FromCompanyNo, bto.CompanyNo AS ToCompanyNo, bfrom.Pseudobranch AS FromPseudo 
FROM  `invty_2transfer` tm JOIN `invty_2transfersub` ts ON tm.TxnID = ts.TxnID
JOIN `1branches` bfrom ON bfrom.BranchNo=tm.BranchNo JOIN `1branches` bto ON bto.BranchNo=tm.ToBranchNo
left join `'.$currentyr.'_static`.`invty_weightedavecost` wac on ts.ItemCode=wac.ItemCode
WHERE tm.BranchNo<>tm.ToBranchNo  AND YEAR(tm.DateOUT)='.$currentyr.' AND (tm.DateIN IS NOT NULL) AND (MONTH(tm.DateIN)<>MONTH(tm.DateOUT)) AND MONTH(tm.DateIN)='.$reportmonth.'  AND (tm.DateIN)>(IF('.$reportmonth.'=1,\''.$lastyr.'-12-31\',(LAST_DAY(\''.$currentyr.'-'.($reportmonth-1).'-1\')))) GROUP BY MONTH(tm.DateIN),tm.BranchNo,tm.ToBranchNo HAVING Profit<>0 ';
   
  // if($_SESSION['(ak0)']==1002){ echo $sql0; break;}
        
        $stmt0=$link->prepare($sql0); $stmt0->execute();
        
        // Return profit in transfers via adjustment
        // check adj number if exists, create if dne
    $stringtomatch=(date('y',strtotime($lastdayofmonth))).'-TxfrProfit-'.str_pad($month,2,'0',STR_PAD_LEFT);
    $jvno=checkExists($stringtomatch,'Remarks','acctg_2jvmain','JVNo',$link);
	if ($jvno==0){
                $jvno=lastNum('JVNo','acctg_2jvmain',((date('Y',strtotime($currentyr.'-01-01')))-2000)*10000+1000000)+1;
                $done=1;
                $sqlmain='INSERT INTO `acctg_2jvmain` SET JVNo='.$jvno.', Posted=0, JVDate=\''.$lastdayofmonth.'\', Remarks=\''.$stringtomatch.'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
		        $stmt=$link->prepare($sqlmain); $stmt->execute();  
        }  else {
            $done=2;
        }
	        
        if ($done==2){  echo 'Record already exists'; exit(); } else {  
            
            $sql1='SELECT IFNULL(GROUP_CONCAT(DISTINCT FromBranchNo),-1) AS FromBranches FROM txfrcosts'; 
        $stmt1=$link->query($sql1); $res1=$stmt1->fetch();
        $arrayfrom=explode(',',$res1['FromBranches']);
        
        foreach($arrayfrom as $frombranch){
            if($frombranch<0){ goto skipinsert;}
       
            $sql0='INSERT INTO `acctg_2jvsub` (`JVNo`,`Date`,`BranchNo`,`FromBudgetOf`,`DebitAccountID`,`CreditAccountID`,`Amount`,`TimeStamp`,`EncodedByNo`) ';
            
            
        // Recored DueTo to return profits --> in FROM BRANCH whether received or not
   $sql1='SELECT '.$jvno.', \''.$lastdayofmonth.'\' AS `Date`, tc.FromBranchNo, tc.FromBranchNo, 300 AS `DebitAccountID`, 105 AS `CreditAccountID`, round(sum(Profit),2) AS Amount, NOW(),'.$_SESSION['(ak0)'].' FROM txfrcosts tc WHERE SameMonth IN (1,2) AND `Month`='.$reportmonth.' AND tc.FromBranchNo='.$frombranch.' GROUP BY tc.FromBranchNo,ToCompanyNo;';  echo '2nd step';
   $stmt=$link->prepare($sql0.$sql1); $stmt->execute();
   
// Record corresponding DueFrom in ToBranch, whether received or not
   $sql1='SELECT  '.$jvno.', \''.$lastdayofmonth.'\' AS `Date`, tc.ToBranchNo, tc.ToBranchNo, 105 AS `DebitAccountID`, IF(SameMonth=1,300,330) AS `CreditAccountID`, round(SUM(Profit),2) as Amount, NOW(),'.$_SESSION['(ak0)'].' FROM txfrcosts tc  WHERE SameMonth IN (1,2) AND `Month`='.$reportmonth.' AND  tc.FromBranchNo='.$frombranch.' GROUP BY tc.ToBranchNo, FromCompanyNo, SameMonth;'; echo '3rd step';
   $stmt=$link->prepare($sql0.$sql1); $stmt->execute();   
   
// Correct the value of inventory by reducing the profit deducted from invty in transit
   $sql1='SELECT  '.$jvno.', \''.$lastdayofmonth.'\' AS `Date`, tc.ToBranchNo, tc.ToBranchNo, 330 AS `DebitAccountID`, 300 AS `CreditAccountID`, round(SUM(Profit),2) as Amount, NOW(),'.$_SESSION['(ak0)'].' FROM txfrcosts tc  WHERE SameMonth=3 AND `Month`='.$reportmonth.' AND  tc.FromBranchNo='.$frombranch.' GROUP BY tc.ToBranchNo, FromCompanyNo;'; echo '4th step';
   $stmt=$link->prepare($sql0.$sql1); $stmt->execute();
   
   skipinsert:
        }
        
        header("Location:formjv.php?w=JV&JVNo=".$jvno."&done=".$done);
    }
    break;
    
case 'Step 5.  Pay Interbranch Transfers AFTER ALL ACCTG ADJUSTMENTS':
	if(allowedToOpen(6455,'1rtc')){
// PAY ALL NON-WAREHOUSE INTERBRANCH TRANSFERS USING MB ACCOUNTS
	
	$sql='UPDATE `acctg_2txfrsub` ts JOIN acctg_2txfrmain tm ON tm.TxnID=ts.TxnID Set `DatePaid`=Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\'), 
            `PaidViaAcctID`=(SELECT AccountID FROM `banktxns_1maintaining` m JOIN `1branches` b ON b.CompanyNo=m.RCompanyUse WHERE b.BranchNo=ts.`ClientBranchNo`) 
WHERE ((Month(tm.Date)<='.$reportmonth.') AND (Month(ts.DateIN)<='.$reportmonth.') AND ((ts.DatePaid) IS NULL)) '
                . ' AND ClientBranchNo NOT IN (SELECT BranchNo FROM `1branches` WHERE PseudoBranch=2);';
        $stmt=$link->prepare($sql); $stmt->execute();
// END OF NON-WAREHOUSE

$fromtable=' `'.$currentyr.'_static`.`acctg_unialltxns` ';        
$condition='WHERE AccountDescription Like \'CIB%\' and Month(`Date`)<='.$reportmonth.' AND AccountDescription NOT LIKE \'%$%\'';
include('sqlphp/createcibbalances.php');

$sql0='CREATE TEMPORARY TABLE unpaidtxfr AS '
        . 'SELECT TOBranchNo AS ClientBranchNo, Balance AS Amount, UnpdTxfrId AS `TxnSubId`, DateIN, Particulars, 1 AS LastYr FROM `acctg_3unpdinterbranchlastperiod` iblp 
            WHERE ((Month(iblp.DateIN) is not null) AND ((iblp.DatePaid) Is Null))
            UNION ALL 
            SELECT ts.ClientBranchNo, ts.Amount, ts.`TxnSubId`, DateIN, Particulars, 0 AS LastYr 
            FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnId=ts.TxnId
            WHERE ((YEAR(tm.Date)<='.$currentyr.' OR Month(tm.Date)<='.$reportmonth.') AND (Month(ts.DateIN)<='.$reportmonth.') AND ((ts.DatePaid) Is Null)) '
        . ' Order by DateIN, Particulars ';
$link->query($sql0); 

$sqlbranches='SELECT ClientBranchNo FROM `unpaidtxfr` group by ClientBranchNo';
$stmt=$link->query($sqlbranches);  $resultbranches=$stmt->fetchAll();

foreach ($resultbranches as $branch){
        $sql0='SELECT AccountID FROM `banktxns_1maintaining` m JOIN `1branches` b ON b.CompanyNo=m.RCompanyUse WHERE b.BranchNo='.$branch['ClientBranchNo'];
        $stmt=$link->query($sql0); $resultacct=$stmt->fetch(); $account=$resultacct['AccountID'];
        $sql='Select SUM(TotalCIB) AS TotalCIB from cibbalances where BranchNo='.$branch['ClientBranchNo'].' group by BranchNo';
        $stmt=$link->query($sql); $resultbank=$stmt->fetch(); $bankbal=$resultbank['TotalCIB'];
        
	// PAY LAST YEAR
	$sqllastyr='SELECT iblp.* FROM `acctg_3unpdinterbranchlastperiod` iblp JOIN `unpaidtxfr` unpd ON iblp.UnpdTxfrId=unpd.TxnSubId
WHERE unpd.LastYr=1 AND TOBranchNo='.$branch['ClientBranchNo'].' Order by DateIN,Particulars';
        $stmt=$link->query($sqllastyr); $resultlastyr=$stmt->fetchAll();
	
    foreach ($resultlastyr as $lastyr){
	if ($bankbal>$lastyr['Balance']){
	$sql='Update `acctg_3unpdinterbranchlastperiod` SET `DatePaid`=Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\'), `PaidViaAcctID`='.$account
                .' WHERE `UnpdTxfrId`='.$lastyr['UnpdTxfrId'];
	$stmt=$link->prepare($sql); $stmt->execute();
	$bankbal=$bankbal-$lastyr['Balance'];
		} 
	}

        // END OF PAY LAST YEAR

        // PAY THIS YEAR

	$sqlthisyr='SELECT * FROM unpaidtxfr unpd WHERE unpd.LastYr=0 AND ClientBranchNo='.$branch['ClientBranchNo'].' Order by DateIN, Particulars';
        $stmt=$link->query($sqlthisyr); $resultthisyr=$stmt->fetchAll();

foreach ($resultthisyr as $thisyr){
	if ($bankbal>$thisyr['Amount']){
	$sql='Update `acctg_2txfrsub` Set `DatePaid`=Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\'), `PaidViaAcctID`='.$account.' where `TxnSubId`='.$thisyr['TxnSubId'];
	$stmt=$link->prepare($sql); $stmt->execute();
	$bankbal=$bankbal-$thisyr['Amount'];
		} 
	}
}

// END OF PAY THIS YEAR
header('Location:/yr'.substr($currentyr,2,2).'/acctg/closedataautoadj.php?w=PayInterbranch&done=1');
        }
break;


case 'Step 7.  Consolidate CIB for Branches per Company AFTER static update':
	if(!allowedToOpen(6455,'1rtc')){ echo 'No permission.'; exit();}
      if (($lastdayofmonth)<($_SESSION['nb4A'])){ header("Location:/yr".substr($currentyr,2,2)."/acctg/closedataautoadj.php?done=0"); break;      }  else {
	$condition='WHERE AccountDescription Like \'CIB%\' and Month(`Date`)<='.$reportmonth.' AND AccountDescription NOT LIKE \'%$%\' ';
        $fromtable=' `'.$currentyr.'_static`.`acctg_unialltxns` ';
	include('sqlphp/createcibbalances.php');
      }
      
       $sqlco='SELECT RCompanyUse AS CompanyNo, AccountID FROM `banktxns_1maintaining` m WHERE (RCompanyUse IS NOT NULL) AND (RCompanyUse<>0) ORDER BY RCompanyUse;';
      $stmt=$link->query($sqlco); $resultco=$stmt->fetchAll(); 
 foreach ($resultco as $co){
     $company=$co['CompanyNo']; $coaccount=$co['AccountID'];
     $stringtomatch='ConsolCIB'.str_pad($reportmonth,2,'0',STR_PAD_LEFT).'-'.$company;
     $jvno=checkExists($stringtomatch,'Remarks','acctg_2jvmain','JVNo',$link);
	if ($jvno==0){
                $jvno=lastNum('JVNo','acctg_2jvmain',((date('Y',strtotime($currentyr.'-01-01')))-2000)*10000+1000000)+1;
                $done=1; 
	$sql='INSERT INTO `acctg_2jvmain` SET  JVNo='.$jvno.', Posted=0, JVDate=Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\'), Remarks=\''.$stringtomatch.'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; //if ($_SESSION['(ak0)']==1002) { echo $sql;}
	
        $stmt=$link->prepare($sql); 	$stmt->execute();
     
      
	    // branch side
        $sqlinsert='INSERT INTO `acctg_2jvsub` (`Date`, JVNo, BranchNo, FromBudgetOf, DebitAccountID, CreditAccountID, Amount, EncodedByNo, `TimeStamp`) 
 SELECT Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\') AS `Date`, '.$jvno.', cib.BranchNo, cib.BranchNo, '.$coaccount.' AS DebitAccountID, cib.AccountID AS CreditAccountID, TotalCIB AS Amount, \''.$_SESSION['(ak0)'].'\' AS EncodedByNo, Now() AS `TimeStamp`
     FROM cibbalances cib JOIN `1branches` b ON b.BranchNo=cib.BranchNo 
 WHERE cib.BranchNo<95 AND cib.BranchNo<>0 AND TotalCIB<>0 
 AND (cib.AccountID <>'.$coaccount.') AND b.CompanyNo='.$company.' group by cib.AccountID, cib.BranchNo;';
       $stmt=$link->prepare($sqlinsert); $stmt->execute(); //AND cib.AccountID <>105 
       
	    // pseudo side
            $sqlpsb='SELECT BranchNo FROM `1branches` b WHERE PseudoBranch=1 AND BranchNo<>95 AND CompanyNo='.$company;
            $stmt=$link->query($sqlpsb); $resultpsb=$stmt->fetch();
	    $sqlinsert='Insert into `acctg_2jvsub` (`Date`,`DebitAccountID`, `CreditAccountID`, `Amount`, `TimeStamp`, `BranchNo`, FromBudgetOf, `EncodedByNo`, `JVNo`) '
                    . 'SELECT Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\') AS `Date`, '.$coaccount.', CreditAccountID, (SUM(Amount)*-1) AS Amount, Now(),'.$resultpsb['BranchNo'].','.$resultpsb['BranchNo'].',\''.$_SESSION['(ak0)'].'\','.$jvno.' FROM acctg_2jvsub where JVNo='.$jvno.' AND BranchNo<>'.$resultpsb['BranchNo'].' GROUP BY CreditAccountID HAVING Amount<>0';
	    $stmt=$link->prepare($sqlinsert); $stmt->execute();
	
 } else {
            $done=2;
        }
    }


	header("Location:formjv.php?w=JV&JVNo=".$jvno."&done=".$done);
        
	break;
        
case 'Step 8.  Consolidate CIB for PseudoBranches per Company AFTER static update':
    if(allowedToOpen(6455,'1rtc')){
    if (($lastdayofmonth)<($_SESSION['nb4A'])){ header("Location:/yr".substr($currentyr,2,2)."/acctg/closedataautoadj.php?done=0"); break;      } else {
	$condition=' WHERE AccountDescription Like \'CIB%\' and Month(`Date`)<='.$reportmonth.' AND AccountDescription NOT LIKE \'%$%\' ';
    include('sqlphp/createcibbalances.php');}
    
   // check if existing
   $stringtomatch='ConsolCIB'.str_pad($reportmonth,2,'0',STR_PAD_LEFT).'-Pseudo';
   $jvno=checkExists($stringtomatch,'Remarks','acctg_2jvmain','JVNo',$link);
	if ($jvno==0){
                $jvno=lastNum('JVNo','acctg_2jvmain',((date('Y',strtotime($currentyr.'-01-01')))-2000)*10000+1000000)+1;
                $done=1; 
     
    //zero out pseudobranches
 $sql='INSERT INTO `acctg_2jvmain` SET  JVNo='.$jvno.', Posted=0, JVDate=Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\'), Remarks=concat("ConsolCIB","'.str_pad($reportmonth,2,"0",STR_PAD_LEFT).'-Pseudo"), EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
        $stmt=$link->prepare($sql); 	$stmt->execute();

         // step 1 - zero out accounts not owned
$sqlinsert='INSERT INTO `acctg_2jvsub` (JVNo, `Date`, BranchNo, FromBudgetOf, DebitAccountID, CreditAccountID, Amount, EncodedByNo, `TimeStamp`)
SELECT '.$jvno.', Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\') AS `Date`, BranchNo, BranchNo, 105 AS DebitAccountID, AccountID AS CreditAccountID, TotalCIB AS Amount, \''.$_SESSION['(ak0)'].'\' AS EncodedByNo, NOW() AS `TimeStamp` FROM cibbalances cib WHERE (cib.BranchNo=0 OR cib.BranchNo>=95) AND (AccountID IN (SELECT AccountID FROM `banktxns_1maintaining` m WHERE OwnedByCompany<>cib.CompanyNo)) AND AccountID<>105 GROUP BY BranchNo,AccountID;'; if ($_SESSION['(ak0)']==1002) { echo $sqlinsert;}
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
        // step 2 - credit to pseudobranch who owns it
$sqlinsert='INSERT INTO `acctg_2jvsub` (JVNo, `Date`, BranchNo, FromBudgetOf, DebitAccountID, CreditAccountID, Amount, EncodedByNo, `TimeStamp`)
SELECT '.$jvno.', Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\') AS `Date`, b.BranchNo, b.BranchNo, 105 AS DebitAccountID, CreditAccountID, SUM(Amount)*-1 AS Amount, \''.$_SESSION['(ak0)'].'\' AS EncodedByNo, NOW() AS `TimeStamp` 
FROM `acctg_2jvsub` s JOIN `banktxns_1maintaining` m ON  m.AccountID=s.CreditAccountID JOIN  `1branches` b ON m.OwnedByCompany=b.CompanyNo AND PseudoBranch=1 AND b.BranchNo<>95 WHERE JVNo='.$jvno.' GROUP BY CreditAccountID;'; //if ($_SESSION['(ak0)']==1002) { echo $sqlinsert;}
	$stmt=$link->prepare($sqlinsert); $stmt->execute();  
        }
      // end of pseudobranches
       
    } else {  $done=2;} 
	existingcibpseudo:
	header("Location:formjv.php?w=JV&JVNo=".$jvno."&done=".$done);
    
	break;


case 'Step 9.  Zero Recon of Non-Pseudo AFTER static update':
    if(allowedToOpen(6455,'1rtc')){
    if (($lastdayofmonth)<($_SESSION['nb4A'])){ header("Location:/yr".substr($currentyr,2,2)."/acctg/closedataautoadj.php?done=0");  } else {
	$condition=' WHERE u.AccountID=105 and Month(`Date`)<='.$reportmonth;
    include('sqlphp/createcibbalances.php');}

   // check if existing

   $stringtomatch='ConsolCIB'.str_pad($reportmonth,2,'0',STR_PAD_LEFT).'-Recon';
   $jvno=checkExists($stringtomatch,'Remarks','acctg_2jvmain','JVNo',$link);
	if ($jvno==0){
                $jvno=lastNum('JVNo','acctg_2jvmain',((date('Y',strtotime($currentyr.'-01-01')))-2000)*10000+1000000)+1;
                $done=1; 

	
 $sql='INSERT INTO `acctg_2jvmain` SET  JVNo='.$jvno.', Posted=0, JVDate=Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\'), Remarks=concat("ConsolCIB","'.str_pad($reportmonth,2,"0",STR_PAD_LEFT).'-Recon"), EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
        $stmt=$link->prepare($sql); 	$stmt->execute();

         // zero out non-pseudobranches
$sqlinsert='INSERT INTO `acctg_2jvsub` (JVNo, `Date`, BranchNo, FromBudgetOf, DebitAccountID, CreditAccountID, Amount, EncodedByNo, `TimeStamp`)
SELECT '.$jvno.' , Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\') AS `Date`, BranchNo, BranchNo, (SELECT AccountID FROM `banktxns_1maintaining` m WHERE RCompanyUse=cib.CompanyNo) AS DebitAccountID, 105 AS CreditAccountID, TotalCIB AS Amount, \''.$_SESSION['(ak0)'].'\' AS EncodedByNo, NOW() AS `TimeStamp` FROM cibbalances cib WHERE (cib.BranchNo<>0 OR cib.BranchNo<95) GROUP BY BranchNo;'; 
if ($_SESSION['(ak0)']==1002) { echo $sqlinsert;}
        $stmt=$link->prepare($sqlinsert); $stmt->execute();
        // step 2 - credit to pseudobranch who owns it
$sqlinsert='INSERT INTO `acctg_2jvsub` (JVNo, `Date`, BranchNo, FromBudgetOf, DebitAccountID, CreditAccountID, Amount, EncodedByNo, `TimeStamp`)
SELECT '.$jvno.',Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\') AS `Date`, b.BranchNo, b.BranchNo, DebitAccountID, 105 AS CreditAccountID, SUM(Amount)*-1 AS Amount, \''.$_SESSION['(ak0)'].'\' AS EncodedByNo, NOW() AS `TimeStamp` 
FROM `acctg_2jvsub` s JOIN `banktxns_1maintaining` m ON  m.AccountID=s.DebitAccountID JOIN  `1branches` b ON m.RCompanyUse=b.CompanyNo AND PseudoBranch=1 AND b.BranchNo<>95 WHERE JVNo='.$jvno.' GROUP BY DebitAccountID;'; //if ($_SESSION['(ak0)']==1002) { echo $sqlinsert;}
	$stmt=$link->prepare($sqlinsert); $stmt->execute();  
        }

    } else {  
        $done=2; 
}
      // end of pseudobranches
       
	header("Location:formjv.php?w=JV&JVNo=".$jvno."&done=".$done);
    
    break;


case 'Update Closed-By Date for Invty':
	if(allowedToOpen(6455,'1rtc')){
      $sql='UPDATE `00dataclosedby` SET `DataClosedBy`=\''.$lastdayofmonth.'\',UpdatedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE `ForDB`=0';
	$stmt=$link->prepare($sql); $stmt->execute();
        if($currentyr==date('Y')){
            $sql='UPDATE `00dataclosedby` SET `BranchesUnprotected` = NULL, `UnprotectedAfterDate` = NULL,UpdatedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE `ForDB`=0';
            $stmt=$link->prepare($sql); $stmt->execute();
        }
	$link=connect_db("".$currentyr."_1rtc",1);
	include('../invty/maketables/makefixedinvdata.php'); if($_SESSION['(ak0)']==1002){echo 'step 1 done.<br><br>'; echo $sql1;}
	include ('../invty/maketables/makealltxnstable.php');if($_SESSION['(ak0)']==1002){echo 'step 2 done.<br><br>'; echo $sql0;}
	
	header('Location:/yr'.substr($currentyr,2,2).'/acctg/closedataautoadj.php?w=InvtyMonthEndDate&done=1');
        }
break;

case 'Step 6. Update Closed-By Date for Acctg':
	if(allowedToOpen(6455,'1rtc')){
      $sql='UPDATE `00dataclosedby` SET `DataClosedBy`=\''.$lastdayofmonth.'\',UpdatedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE `ForDB`=1';
	$stmt=$link->prepare($sql); $stmt->execute();
        if($_SESSION['(ak0)']==1002){echo 'step 0 done.<br><br>'; echo $sql;}
        if($currentyr==date('Y')){
            $sql='UPDATE `00dataclosedby` SET `BranchesUnprotected` = NULL, `UnprotectedAfterDate` = NULL,UpdatedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE `ForDB`=0';
            $stmt=$link->prepare($sql); $stmt->execute();
        }
        }
case 'Update Static Data - Acctg Only':  
case 'Update Static Data - including data for special accounts':  	 
	// fixed data
	$whichdata='static'; //this creates static data of all detailed transactions
        require 'maketables/makefixedacctgdata.php'; if($_SESSION['(ak0)']==1002){echo 'step 1 done.<br><br>'; echo $sql1;}
	$whichdata='staticfs'; //this creates static data of balances per month
	require 'maketables/makefixedacctgdata.php'; if($_SESSION['(ak0)']==1002){echo '<br><br>step 2 done.<br><br>'; echo $sql0;}
	$closedmonth=$_POST['month'];
	include '../backendphp/functions/monthsarray.php'; //the ff creates balances with months as columns
	$link1=connect_db("".$currentyr."_1rtc",1);
	$sql0='drop table if exists `'.$currentyr.'_static`.`acctg_fsvaluesmonthcol`'; $link1->query($sql0); 
	$sql=' AS SELECT AccountType, fs.AccountID, BranchNo, SUM(case when FSMonth=0 then ifnull(`Bal`,0) end) as `00` ';
	foreach ($months as $fsmonth){
		$monthcol=str_pad($fsmonth,2,'0',STR_PAD_LEFT);
		$sql=$sql.', SUM(case when FSMonth='.$fsmonth.' then `Bal` end) as `'.$monthcol.'`, SUM(case when FSMonth<='.$fsmonth.' then ifnull(`Bal`,0) end) as `'.$monthcol.'asof`';
	}
	
	$stmt=$link1->prepare('CREATE TABLE `'.$currentyr.'_static`.`acctg_fsvaluesmonthcol` '.$sql.' FROM '.$currentyr.'_static.acctg_fsvalues fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID GROUP BY AccountID, BranchNo'); $stmt->execute();
        
	$sqlbranches='SELECT u.BranchNo, Branch FROM `1branches` b join '.$currentyr.'_static.acctg_unialltxns u on b.BranchNo=u.BranchNo group by u.BranchNo order by u.BranchNo;';
	$stmtbranch=$link->query($sqlbranches); $branchlist=$stmtbranch->fetchAll();
	//the ff creates balances with branches as columns
	$sql0='drop table if exists `'.$currentyr.'_static`.`acctg_fsvaluesbranchcol`'; $link1->query($sql0); 
	$sql=' AS SELECT AccountType, fs.AccountID, FSMonth ';
	foreach ($branchlist as $branch){
		$sql=$sql.', SUM(case when BranchNo='.$branch['BranchNo'].' then ifnull(`Bal`,0) end) as `'.$branch['BranchNo'].'`';
	}
	// $sql=$sql.''; 
	$stmt=$link1->prepare('CREATE TABLE `'.$currentyr.'_static`.`acctg_fsvaluesbranchcol` '.$sql.' FROM '.$currentyr.'_static.acctg_fsvalues fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID GROUP BY AccountID, FSMonth'); $stmt->execute();
        
        
        // grouped by company
        $sqlco='SELECT b.CompanyNo FROM `1branches` b join '.$currentyr.'_static.acctg_unialltxns u on b.BranchNo=u.BranchNo group by b.CompanyNo order by b.CompanyNo;';
	$stmtco=$link->query($sqlco); $colist=$stmtco->fetchAll();
	//the ff creates balances with companies as columns
	$sql0='drop table if exists `'.$currentyr.'_static`.`acctg_fsvaluescompanycol`'; $link1->query($sql0); 
	$sql=' AS SELECT AccountType, fs.AccountID, FSMonth ';
	foreach ($colist as $co){
		$sql.=', SUM(case when BranchNo in (Select BranchNo FROM `1branches` WHERE CompanyNo='.$co['CompanyNo'].') then ifnull(`Bal`,0) end) as `'.$co['CompanyNo'].'`';
	}
	$sql='CREATE TABLE `'.$currentyr.'_static`.`acctg_fsvaluescompanycol` '.$sql.' FROM '.$currentyr.'_static.acctg_fsvalues fs JOIN acctg_1chartofaccounts ca on ca.AccountID=fs.AccountID GROUP BY AccountID, FSMonth'; 
	$stmt=$link1->prepare($sql); 
        if($_SESSION['(ak0)']==1002) {echo '<br><br>before company cols.<br><br>'; echo $sql;}
        $stmt->execute();
    if($_POST['submit']<>'Update Static Data - Acctg Only'){
        // send balances to closing db
        // first update existing data
        $stmt=$link1->prepare('UPDATE `closing_2closemain` cm 
SET cm.`EndBal` = (SELECT SUM(`Bal`)*`NormBal` FROM `'.$currentyr.'_static`.`acctg_fsvalues` fs JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=fs.AccountID WHERE BranchNo=cm.BranchNo AND FSMonth<=cm.Month AND fs.AccountID=cm.AccountID),`EncodedByNo` = \''.$_SESSION['(ak0)'].'\',`TimeStamp`=Now()'); $stmt->execute();
        // insert new data
        $stmt=$link1->prepare('INSERT INTO `closing_2closemain` (`Month`,`AccountID`,`BranchNo`,`EndBal`,`EncodedByNo`,`TimeStamp`)
SELECT `FSMonth`,fs.`AccountID`,`BranchNo`,(SELECT SUM(`Bal`)*`NormBal` FROM `'.$currentyr.'_static`.`acctg_fsvalues` fs2 JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=fs2.AccountID WHERE fs2.BranchNo=fs.BranchNo AND fs2.FSMonth<=fs.FSMonth AND fs2.AccountID=fs.AccountID) AS `EndBal`, \''.$_SESSION['(ak0)'].'\', Now() FROM `'.$currentyr.'_static`.`acctg_fsvalues` fs JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=fs.AccountID WHERE fs.`AccountID` IN (100,135,205,206,330,403,405,501,502,503,504,505,507,508,512) AND `FSMonth`<>0 AND (CONCAT(fs.AccountID,":",fs.BranchNo,":",fs.FSMonth) NOT IN (SELECT CONCAT(AccountID,":",BranchNo,":",Month) FROM `closing_2closemain`))'); $stmt->execute();
	header('Location:/yr'.substr($currentyr,2,2).'/acctg/closedataautoadj.php?w=UpdateClosedByDate_And_StaticData_Acctg&done=1');
        
        // used series info
        $stmt=$link1->prepare('CREATE TEMPORARY TABLE usedthismonth 
            SELECT MONTH(`Date`) AS Month, BranchNo, MIN(SaleNo) AS MinSaleNo, MAX(SaleNo) AS MaxSaleNo, 
MAX(`NumericOnly(SaleNo))-MIN(`NumericOnly(SaleNo))+1 AS CountShouldBe,COUNT(SaleNo) AS ActualCount, txntype 
    FROM `invty_2sale` WHERE MONTH(`Date`)<='.$month.' GROUP BY MONTH(`Date`),BranchNo, txntype
    
    UNION ALL
    
    SELECT MONTH(`Date`) AS Month, BranchSeriesNo, MIN(`CollectNo`) AS MinSaleNo, MAX(`CollectNo`) AS MaxSaleNo, 
    MAX(`NumericOnly(`CollectNo`))-MIN(`NumericOnly(`CollectNo`))+1 AS CountShouldBe,COUNT(`CollectNo`) AS ActualCount, "30" AS txntype 
    FROM `acctg_2collectmain` WHERE MONTH(`Date`)<='.$month.' GROUP BY MONTH(`Date`),BranchSeriesNo
    
    UNION ALL
    
    SELECT MONTH(`DateOUT`) AS Month, BranchNo, MIN(TransferNo) AS MinSaleNo, MAX(TransferNo) AS MaxSaleNo, 
MAX(`NumericOnly(TransferNo))-MIN(`NumericOnly(TransferNo))+1 AS CountShouldBe,COUNT(TransferNo) AS ActualCount, txntype 
    FROM `invty_2transfer` WHERE MONTH(`DateOUT`)<='.$month.' GROUP BY MONTH(`DateOUT`),BranchNo, txntype
    
    UNION ALL
    
    SELECT MONTH(`Date`) AS Month, BranchNo, MIN(MRRNo) AS MinSaleNo, MAX(MRRNo) AS MaxSaleNo, 
MAX(`NumericOnly(MRRNo))-MIN(`NumericOnly(MRRNo))+1 AS CountShouldBe,COUNT(MRRNo) AS ActualCount, txntype 
    FROM `invty_2mrr` WHERE MONTH(`Date`)<='.$month.' GROUP BY MONTH(`Date`),BranchNo, txntype'); $stmt->execute();
        
        // update existing
         $stmt=$link1->prepare('UPDATE `closing_2seriesused` su JOIN `usedthismonth` u ON su.Month=u.Month AND su.BranchNo=u.BranchNo AND su.txntypeid=u.txntype
SET su.`MinSaleNo` = u.`MinSaleNo`, su.`MaxSaleNo`= u.`MaxSaleNo`, su.`CountShouldBe` = u.`CountShouldBe`, su.`ActualCount` = u.`ActualCount`, su.DataUpdatedByNo=\''.$_SESSION['(ak0)'].'\', su.DataUpdatedTS=Now();'); $stmt->execute();
        // insert new date
        $stmt=$link1->prepare('INSERT INTO `closing_2seriesused` 
            (`Month`,`BranchNo`,`MinSaleNo`,`MaxSaleNo`,`CountShouldBe`,`ActualCount`,`txntypeid`,`EncodedByNo`,`TimeStamp`,DataUpdatedByNo,DataUpdatedTS)
            SELECT Month, BranchNo, MinSaleNo, MaxSaleNo, CountShouldBe, ActualCount, txntype,  \''.$_SESSION['(ak0)'].'\', Now(),  \''.$_SESSION['(ak0)'].'\', Now() 
    FROM usedthismonth WHERE (CONCAT(txntype,":",BranchNo,":",`Month`) NOT IN (SELECT CONCAT(txntypeid,":",BranchNo,":",Month) FROM `closing_2seriesused`)) '); $stmt->execute();
    $link1=null;
    header("Location:/yr".substr($currentyr,2,2)."/acctg/closedataautoadj.php?w=UpdateStaticAll&done=1");
    } else { 
        $link1=null;
        header("Location:/yr".substr($currentyr,2,2)."/acctg/closedataautoadj.php?w=UpdateStaticAcctgOnly&done=1");}
break;

default:
		 header('Location:../index.php');
	

        }
                
noform:
/*
$title='Closing Accounts NOT Balanced'; 
 $filter=' HAVING DiffValue>1';
include('../closing/listsqls.php');
$columnnames=array('Month','AccountID','Account','BranchNo','Branch','DataEndBalance','Accounted','Difference');
include('../backendphp/layout/displayastable.php');
    */
      $stmt=null; $link=null;
?>
</body></html>