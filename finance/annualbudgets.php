<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(517,'1rtc')) { echo 'No permission'; exit; }
// if ($_GET['w']=='AddMain'){$showbranches=false;}else{
// $showbranches=true; }
$showbranches=false;

if($_GET['w']!='specific'){
include_once('../switchboard/contents.php');
} else {
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
}
include_once ('../generalinfo/trailgeninfo.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=!isset($_GET['w'])?'lists':$_GET['w'];

if (in_array($which,array('report','specific'))){

?>
		<title>Budget Monitoring</title>
		<style>
#budget {
  border-collapse: collapse;
  font-size:10pt;
  width: auto;
  background-color:#cccccc;
}

#budget td, #budget th {
  border: 1px solid black;
  padding: 3px;
}
#budget tr:nth-child(even){background-color:white;}


</style>
		<?php
}
if (in_array($which,array('lists','add','report','specific'))){
	if(isset($_REQUEST['entity'])){
	$entity=companyandbranchValue($link, 'acctg_1budgetentities', 'Entity', $_REQUEST['entity'], 'EntityID');	
	if(empty($entity)){
		$sqlchecker='select BranchNo from 1branches where Branch=\''.$_REQUEST['entity'].'\'';
		$stmtchecker=$link->query($sqlchecker); $resultchecker=$stmtchecker->fetch();
		$entity=$resultchecker['BranchNo'];
	}
	// echo $entity; exit();
	$account=comboBoxValue($link, 'acctg_1chartofaccounts', 'ShortAcctID', $_REQUEST['account'], 'AccountID');
	}
}

if (in_array($which,array('lists','edit','report','ReachedBudgets'))){
	echo comboBox($link,'SELECT EntityID, Entity FROM `acctg_1budgetentities`
	union select BranchNo as EntityID, Branch as Entity from 1branches ORDER BY Entity','EntityID','Entity','entities'); 

	echo comboBox($link,'SELECT AccountID, ShortAcctID AS Account, AccountDescription FROM `acctg_1chartofaccounts` ORDER BY ShortAcctID;','AccountID','Account','accounts');
}


switch ($which){
	case'lists':
	if(isset($_POST['switch'])){
		if($_POST['switchvalue']==0){
			$switchlabel='Hide Encoded By and Timestamp';
			$switchvalue=1;
		}else{
			$switchlabel='Show Encoded By and Timestamp';
			$switchvalue=0;
		}
	}else{
		$switchlabel='Show Encoded By and Timestamp';
		$switchvalue=0;
	}
	if(!isset($_REQUEST['entity'])){
		$entityvalue='';
		$accountvalue='';
	}else{
		$entityvalue=$_REQUEST['entity'];
		$accountvalue=$_REQUEST['account'];
	}
	echo'</br><form method="post" action="annualbudgets.php?w=lists&entity='.$entityvalue.'&account='.$accountvalue.'">
			<input type="submit" name="switch" value="'.$switchlabel.'">
			<input type="hidden" name="switchvalue" value="'.$switchvalue.'">
	
		</form>';
	echo '<title>Budget</title>';
	
	if(isset($_GET['Message'])){
    echo $_GET['Message'];
	}
	echo'</br></br><table style="border:1px solid black;  padding: 3px;  font-size:9pt;"><tr><td><h3>Filtering</h3><form method="post" action="annualbudgets.php?w=lists">
	<input type="text" name="entity" list="entities" placeholder="Entity" required>
	<input type="text" name="account" list="accounts" placeholder="Account" >
	<input type="submit" name="submit">	
	</form></td></tr></table>';
	
	if (allowedToOpen(5170,'1rtc')) {
	echo '</br><table style="border:1px solid black;  padding: 3px;  font-size:9pt;"><tr><td><h3>Set Budget</h3><form method="post" action="annualbudgets.php?w=add">
	Budget for Month Beginning (1-12) <input type="text" name="from" size="3">
	to Month Ending (1-12) <input type="text" name="to" size="3">
	Entity <input type="text" name="entity" list="entities">
	Account <input type="text" name="account" list="accounts">
	Details <input type="text" name="details"></br></br>
	Specifics <input type="text" name="Specifics">
	Remarks <input type="text" name="Remarks">
	Budget <input type="text" name="budget" size=5 >
	<input type="submit" name="submit">	
	</form></td></tr></table>';
	
	echo'</br><table style="border:1px solid black;  padding: 3px; font-size:9pt;"><tr><td><h3>Upload Budget</h3></br>
	<form method="post" action="annualbudgets.php?w=Upload" enctype="multipart/form-data">		
			<input type="file" name="userfile" accept="csv/text" required><input type="submit" name="upload" value="Upload" OnClick="return confirm("Are you sure you want to Upload?");"></form></td></tr></table>';	
	}
	//first table starts here
	if(isset($_REQUEST['entity'])){
		if (allowedToOpen(5170,'1rtc')) {

		$title='';

	// $formdesc='</i><b>'.$_REQUEST['entity'].'</b>';
	$sql='select if(Entity is null,Branch,Entity) as Entity,TxnID,Month,ShortAcctID as Account,Details,Specifics,Budget,Concat(Nickname,\' \',SurName) as EncodedBy,b.TimeStamp,b.Remarks from budget_1budgets b left join acctg_1budgetentities be on be.EntityID=b.EntityID left join acctg_1chartofaccounts ca on ca.AccountID=b.AccountID left join 1employees e on e.IDNo=b.EncodedByNo left join 1branches br on br.BranchNo=b.EntityID where b.EntityID=\''.$entity.'\' '.((isset($_REQUEST['account']) AND !empty($_REQUEST['account']))? ' and b.AccountID=\''.$account.'\' ':'').' Order by Month,Account,Details,Specifics ';
	// echo $sql; exit();
	if($switchvalue==1){
	$columnnames=array('Entity','Month','Account','Details','Specifics','Budget','Remarks','EncodedBy','TimeStamp');
	}else{
	$columnnames=array('Entity','Month','Account','Details','Specifics','Budget','Remarks');	
	}
	$showgrandtotal=true;
	$coltototal='Budget';
	$txnidname='TxnID';
	$rep=str_replace(' ','_',$_REQUEST['entity']);
	$repa=str_replace(' ','_',$_REQUEST['account']);
	$delprocess='annualbudgets.php?w=delete&entity='.$rep.'&account='.$repa.'&TxnID=';
	$editprocess='annualbudgets.php?w=editprocess&TxnID=';
	$editprocesslabel='edit';
	$columnstoedit=array('Entity','Month','Account','Details','Specifics','Budget','Remarks');
	 $columnswithlists=array('Entity','Account');
	 $listsname=array('Entity'=>'entities','Account'=>'accounts');
	include('../backendphp/layout/displayastableeditcells.php');
	unset($formdesc); unset($delprocess); unset($editprocess); unset($editprocesslabel);
			?></div><?php	
		}	
	//second table starts here
		$title='';
			
			$sql='SELECT if(Entity is null,Branch,Entity) as Entity,ShortAcctID as Account,FORMAT(SUM(Budget),0) AS YrTotal,sum(Budget) as YrTotalValue,
				FORMAT(sum(CASE WHEN b.Month=1 THEN IFNULL(b.`Budget`,0) END),2) AS Jan,
				FORMAT(sum(CASE WHEN b.Month=2 THEN IFNULL(b.`Budget`,0) END),2) AS Feb,
				FORMAT(sum(CASE WHEN b.Month=3 THEN IFNULL(b.`Budget`,0) END),2) AS Mar,
				FORMAT(sum(CASE WHEN b.Month=4 THEN IFNULL(b.`Budget`,0) END),2) AS Apr,
				FORMAT(sum(CASE WHEN b.Month=5 THEN IFNULL(b.`Budget`,0) END),2) AS May,
				FORMAT(sum(CASE WHEN b.Month=6 THEN IFNULL(b.`Budget`,0) END),2) AS Jun,
				FORMAT(sum(CASE WHEN b.Month=7 THEN IFNULL(b.`Budget`,0) END),2) AS Jul,
				FORMAT(sum(CASE WHEN b.Month=8 THEN IFNULL(b.`Budget`,0) END),2) AS Aug,
				FORMAT(sum(CASE WHEN b.Month=9 THEN IFNULL(b.`Budget`,0) END),2) AS Sep,
				FORMAT(sum(CASE WHEN b.Month=10 THEN IFNULL(b.`Budget`,0) END),2) AS Oct,
				FORMAT(sum(CASE WHEN b.Month=11 THEN IFNULL(b.`Budget`,0) END),2) AS Nov,
				FORMAT(sum(CASE WHEN b.Month=12 THEN IFNULL(b.`Budget`,0) END),2) AS \'Dec\'
				FROM  budget_1budgets b left join acctg_1budgetentities be on be.EntityID=b.EntityID left join acctg_1chartofaccounts ca on ca.AccountID=b.AccountID left join 1branches br on br.BranchNo=b.EntityID where b.EntityID=\''.$entity.'\' '.((isset($_REQUEST['account']) AND !empty($_REQUEST['account']))? ' and b.AccountID=\''.$account.'\' ':'').' Group By Account ORDER BY Account';
				
				// echo $sql; exit();
				unset($coltototal);
				unset($showgrandtotal);
				$columnnames=array('Entity','Account','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','YrTotal');
				include('../backendphp/layout/displayastablenosort.php');
				
				
				if(isset($_REQUEST['account']) AND empty($_REQUEST['account'])){
	
	//Third Table starts here
			$title='';
			
			$sqlt='Create temporary table totals as SELECT Entity,ShortAcctID as Account,
				sum(Budget) AS YrTotal,
				sum(CASE WHEN b.Month=1 THEN IFNULL(b.`Budget`,0) END) AS Jan,
				sum(CASE WHEN b.Month=2 THEN IFNULL(b.`Budget`,0) END) AS Feb,
				sum(CASE WHEN b.Month=3 THEN IFNULL(b.`Budget`,0) END) AS Mar,
				sum(CASE WHEN b.Month=4 THEN IFNULL(b.`Budget`,0) END) AS Apr,
				sum(CASE WHEN b.Month=5 THEN IFNULL(b.`Budget`,0) END) AS May,
				sum(CASE WHEN b.Month=6 THEN IFNULL(b.`Budget`,0) END) AS Jun,
				sum(CASE WHEN b.Month=7 THEN IFNULL(b.`Budget`,0) END) AS Jul,
				sum(CASE WHEN b.Month=8 THEN IFNULL(b.`Budget`,0) END) AS Aug,
				sum(CASE WHEN b.Month=9 THEN IFNULL(b.`Budget`,0) END) AS Sep,
				sum(CASE WHEN b.Month=10 THEN IFNULL(b.`Budget`,0) END) AS Oct,
				sum(CASE WHEN b.Month=11 THEN IFNULL(b.`Budget`,0) END) AS Nov,
				sum(CASE WHEN b.Month=12 THEN IFNULL(b.`Budget`,0) END) AS `Dec`
				FROM  budget_1budgets b left join acctg_1budgetentities be on be.EntityID=b.EntityID left join acctg_1chartofaccounts ca on ca.AccountID=b.AccountID where b.EntityID=\''.$entity.'\' '.((isset($_REQUEST['account']) AND !empty($_REQUEST['account']))? ' and b.AccountID=\''.$account.'\' ':'').' Group By Account ORDER BY Account';
			$stmtt=$link->prepare($sqlt); $stmtt->execute();
				// echo $sql; exit();
				$sql='select format(sum(Jan),2) as Jan,format(sum(Feb),2) as Feb,format(sum(Mar),2) as Mar,format(sum(Apr),2) as Apr,format(sum(May),2) as May,format(sum(Jun),2) as Jun,format(sum(Jul),2) as Jul,format(sum(Aug),2) as Aug,format(sum(Sep),2) as Sep,format(sum(Oct),2) as Oct,format(sum(Nov),2) as Nov,format(sum(`Dec`),2) as `Dec`,format(sum(YrTotal),2) as GrandTotal from totals';
				// $coltototal='YrTotalValue';
				// $showgrandtotal='true';
				$formdesc='</i></br><b>Totals</b>';
				$columnnames=array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','GrandTotal');
				include('../backendphp/layout/displayastablenosort.php');
				
				}
	}
	
	break;
	

	
	case'editprocess':
	$txnid=intval($_GET['TxnID']);
	$table='budget_1budgets';
		recordtrail($txnid,$table,$link,0);
	
	$entity=companyandbranchValue($link, 'acctg_1budgetentities', 'Entity', $_REQUEST['Entity'], 'EntityID');
if(empty($entity)){
		$sqlchecker='select BranchNo from 1branches where Branch=\''.$_REQUEST['Entity'].'\'';
		$stmtchecker=$link->query($sqlchecker); $resultchecker=$stmtchecker->fetch();
		$entity=$resultchecker['BranchNo'];
}	
	$account=comboBoxValue($link, 'acctg_1chartofaccounts', 'ShortAcctID', $_POST['Account'], 'AccountID');
	
	$sql='update budget_1budgets set Month=\''.$_POST['Month'].'\',EntityID=\''.$entity.'\',AccountID=\''.$account.'\',Details=\''.$_POST['Details'].'\',Specifics=\''.$_POST['Specifics'].'\',Budget=\''.$_POST['Budget'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now(),Remarks=\''.$_POST['Remarks'].'\' where TxnID=\''.$txnid.'\'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:annualbudgets.php?w=lists&entity=".$_REQUEST['Entity']."&account=".$_REQUEST['Account']."");
	break;
	
	
	
	case 'add':	

	$from=$_POST['from'];
	$to=$_POST['to'];
	while($from<=$to){
	$sql='insert into budget_1budgets set Month=\''.$from.'\',EntityID=\''.$entity.'\',AccountID=\''.$account.'\',Details=\''.$_POST['details'].'\',Specifics=\''.$_POST['Specifics'].'\',Budget=\''.$_POST['budget'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now(),Remarks=\''.$_POST['Remarks'].'\'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	$from++;
	}
	
	header("Location:annualbudgets.php?w=lists&entity=".$_REQUEST['entity']."&account=".$_REQUEST['account']."");
	break;
	
	
	case'delete':
	$txnid=intval($_GET['TxnID']);
		$table='budget_1budgets';
		recordtrail($txnid,$table,$link,1);
	$sql='delete from budget_1budgets where TxnID='.$txnid.'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:annualbudgets.php?w=lists&entity=".$_REQUEST['entity']."&account=".$_REQUEST['account']."");
	break;
	
	case'Upload':
	$requireencodedby=true;
	$requiredts=true;
	if(isset($_POST['upload'])){
$tblname='budget_1budgets'; $firstcolumnname='Month';
$DOWNLOAD_DIR="../../uploads/"; 
    if (!isset($_FILES['userfile'])) { goto nodata; }
$maxsize = 10004800; //MAX Size 10MB


if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
       
                $csv_file=$_FILES['userfile']['name'];
				
				$ext = pathinfo($csv_file, PATHINFO_EXTENSION);
				if( $ext !== 'csv' ) { echo 'Error! Invalid File Type.'; exit(); }
				if(($_FILES['userfile']['size'] > $maxsize)){ echo 'Error! Invalid File Size (MAX 10MB).'; exit(); }
				
                $file_to_use=$DOWNLOAD_DIR . $csv_file;
                if (file_exists($file_to_use)) {
                    unlink($file_to_use);
                }
                 if (copy($_FILES['userfile']['tmp_name'],$file_to_use)) {
                 $good="Successfully_added_$csv_file";
                 }
                 else {
                 $good="Error: " . $_FILES["userfile"]["error"];
                 }
           } else {
             $good="Did not work  " . "Error: " . $_FILES["userfile"]["error"];            
            echo $csv_file . " is the file name";
            }


$csv = array_map("str_getcsv", file($file_to_use,FILE_SKIP_EMPTY_LINES));
$keys = array_shift($csv);

$numcols = count($keys)-1;
$num=0;
$fieldlist="";
while ($num<$numcols) {
    $fieldlist=$fieldlist . $keys[$num].", ";
    $num=$num+1;
}
$fieldlist=$fieldlist . $keys[$numcols];


$query="";
$row = 1;
if (($handle = fopen($file_to_use,"r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num=0;
    $values=""; //echo $data[0];
if($data[0]!=$firstcolumnname){

        while ($num<=$numcols) {
          $values=$values."'". addslashes($data[$num]) . (($num<$numcols)?"', ":"'");
          $num=$num+1;
        } //end while 

        if(isset($requireencodedby) and $requireencodedby==true) { $fieldlist2=$fieldlist.",EncodedByNo"; $values.=",".$_SESSION['(ak0)']; } else { $fieldlist2=$fieldlist;}
        if(isset($requiredts) and $requiredts==true) { $fieldlist2.=",TimeStamp"; $values.=",Now()"; }
        if(isset($requireencodedby) OR isset($requiredts)) { $fields=$fieldlist2; } else { $fields=$fieldlist; }
$query="Insert into $tblname (" . $fields . ") values (" . $values . ");";
// echo $query;
if($_SESSION['(ak0)']==1002 OR $_SESSION['(ak0)']==1003){ echo $query . "<br>" ; print_r($data). "<br>" ;}
  $row++;
$link->query($query);
} //end if        
  
    }
    fclose($handle);
}

echo ($row-1) . " rows successfully imported to database!!";
$Message = urlencode("successfully added please filter");
header("Location:annualbudgets.php?w=lists&Message=".$Message."");
}
	  nodata:
	  echo 'walang laman';
	break;
	
	
	
	case'report':
	
	echo'</br><div style="background-color:#cccccc; width:30%; border: 1px solid black; padding:5px;" >
		<b>INSTRUCTION:</b></br>
		If row is color red, the Expenses reached the 80% of the Budget<br>
			 </div></br>';
	
	if(!isset($_POST['submit'])){
		$_REQUEST['entity']='';
		$_REQUEST['account']='';
	}
		echo'<h3>Budget Monitoring</h3></br><table  style="float:left; border:1px solid black;  padding: 3px;  font-size:9pt;"><tr><td><h3>Filtering</h3><form method="post" action="annualbudgets.php?w=report">
	<input type="text" name="entity" list="entities" placeholder="Entity" value="'.$_REQUEST['entity'].'">
	<input type="text" name="account" list="accounts" placeholder="Account" value="'.$_REQUEST['account'].'">
	<input type="submit" name="submit" value="Lookup">	
	</form></td></tr></table>';
	
	//ReachedBudgets
	echo '<form style="margin-top:15px;" method="post" action="annualbudgets.php?w=ReachedBudgets">
		  '.str_repeat('&nbsp;',30).'<input type="submit" name="ReachedBudgets" value="Reached80%OfTheBudget?">
		  </form>';
	
	if(isset($_POST['submit'])){
		// $title='Budget Monitoring';		
		$sqlb='select format(sum(Budget),2) as Budget,ShortAcctID as Account from budget_1budgets b join acctg_1chartofaccounts ca on ca.AccountID=b.AccountID where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' ';
		// echo $sqlb; exit();
		$stmt=$link->query($sqlb); $resultb=$stmt->fetch();

		$sqlbm='CREATE TEMPORARY TABLE BM AS select (@running_total:=@running_total - Actual) AS RemainingBudget,Actual,Month,TxnID,MonthValue,`1`,`2`,`3`,`4`,`5`,`6`,`7`,`8`,`9`,`10`,`11`,`12` from 
		(select ut.TxnID,sum(Amount) as Actual,
		case when month(ut.Date)=1 then "January"
		when month(ut.Date)=2 then "February"
		when month(ut.Date)=3 then "March"
		when month(ut.Date)=4 then "April"
		when month(ut.Date)=5 then "May"
		when month(ut.Date)=6 then "June"
		when month(ut.Date)=7 then "July"
		when month(ut.Date)=8 then "August"
		when month(ut.Date)=9 then "September"
		when month(ut.Date)=10 then "October"
		when month(ut.Date)=11 then "November"
		when month(ut.Date)=12 then "December"

		end as Month,month(ut.Date) as MonthValue from '.$currentyr.'_static.acctg_0unialltxns ut left join acctg_1budgetentities be on be.EntityID=ut.FromBudgetOf left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID where ut.FromBudgetOf=\''.$entity.'\' AND ut.AccountID=\''.$account.'\' and ControlNo not like \'%BegBal\' Group By month(Date) ) Actual JOIN 
		(SELECT @running_total:=sum(Budget) from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' ) Budget 
		JOIN (select sum(Budget) as `1` from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' AND Month=1) as `1` 
		JOIN (select sum(Budget) as `2` from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' AND Month=2) as `2`
		JOIN (select sum(Budget) as `3` from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' AND Month=3) as `3`
		JOIN (select sum(Budget) as `4` from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' AND Month=4) as `4`
		JOIN (select sum(Budget) as `5` from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' AND Month=5) as `5`
		JOIN (select sum(Budget) as `6` from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' AND Month=6) as `6`
		JOIN (select sum(Budget) as `7` from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' AND Month=7) as `7`
		JOIN (select sum(Budget) as `8` from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' AND Month=8) as `8`
		JOIN (select sum(Budget) as `9` from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' AND Month=9) as `9`
		JOIN (select sum(Budget) as `10` from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' AND Month=10) as `10`
		JOIN (select sum(Budget) as `11` from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' AND Month=11) as `11`
		JOIN (select sum(Budget) as `12` from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' AND Month=12) as `12`';
		// echo $sqlbm; exit();
		$stmtbm=$link->prepare($sqlbm); 
		$stmtbm->execute();
		
		$sql='Select TxnID,MonthValue,Month,format(RemainingBudget,2) as RemainingBudget,format(Actual,2) as Actual,
		case 
		when MonthValue=1 then format((Actual/`1`)*100,2) when MonthValue=2 then format((Actual/`2`)*100,2)
		when MonthValue=3 then format((Actual/`3`)*100,2) when MonthValue=4 then format((Actual/`4`)*100,2)
		when MonthValue=5 then format((Actual/`5`)*100,2) when MonthValue=6 then format((Actual/`6`)*100,2)
		when MonthValue=7 then format((Actual/`7`)*100,2) when MonthValue=8 then format((Actual/`8`)*100,2)
		when MonthValue=9 then format((Actual/`9`)*100,2) when MonthValue=10 then format((Actual/`10`)*100,2)
		when MonthValue=11 then format((Actual/`11`)*100,2) when MonthValue=12 then format((Actual/`12`)*100,2)

		end as BudgetUsage,Case

		when MonthValue=1 then format(`1`,2) when MonthValue=2 then format(`2`,2)
		when MonthValue=3 then format(`3`,2) when MonthValue=4 then format(`4`,2)
		when MonthValue=5 then format(`5`,2) when MonthValue=6 then format(`6`,2)
		when MonthValue=7 then format(`7`,2) when MonthValue=8 then format(`8`,2)
		when MonthValue=9 then format(`9`,2) when MonthValue=10 then format(`10`,2)
		when MonthValue=11 then format(`11`,2) when MonthValue=12 then format(`12`,2)

		end as MonthlyBudget from BM';
		
		$stmt=$link->query($sql); 
		$result=$stmt->fetchAll();
		
		echo'</br><table id="budget"">Entity: <b>'.$_POST['entity'].'</b> Account: <b>'.$resultb['Account'].'</b>
		<tr><th>Month</th><th>MonthlyBudget</th><th>BudgetUsage</th><th>Actual</th><th>RemainingBudget</th><th></th></tr>
		<tr><td>Budget for the year:</td><td></td><td></td><td></td><td>'.($resultb['Budget']).'</td><td></td></tr>';
		foreach($result as $res){
			if($res['BudgetUsage']>=80){
			echo'<tr><td bgcolor="red">'.$res['Month'].'</td><td bgcolor="red">'.$res['MonthlyBudget'].'</td><td bgcolor="red">'.$res['BudgetUsage'].' %</td><td bgcolor="red">'.$res['Actual'].'</td><td bgcolor="red">'.$res['RemainingBudget'].'</td><td bgcolor="red">
			<a style="text-decoration:none;" href=""  onclick="window.open(\'annualbudgets.php?w=specific&TxnID='.$res['TxnID'].'&entity='.$_POST['entity'].'&account='.$_POST['account'].'&MonthValue='.$res['MonthValue'].'\', \'newwindow\',\'width=1000,height=500\');return false;">Lookup</a></td></tr>';		
			}else{
			echo'<tr><td>'.$res['Month'].'</td><td>'.$res['MonthlyBudget'].'</td><td>'.$res['BudgetUsage'].' %</td><td>'.$res['Actual'].'</td><td>'.$res['RemainingBudget'].'</td><td>
			<a style="text-decoration:none;" href=""  onclick="window.open(\'annualbudgets.php?w=specific&TxnID='.$res['TxnID'].'&entity='.$_POST['entity'].'&account='.$_POST['account'].'&MonthValue='.$res['MonthValue'].'\', \'newwindow\',\'width=1000,height=500\');return false;">Lookup</a></td></tr>';		
			}
		}
		
		
		// echo $sql; exit();
		
	}
	
	
	break;
	
	
	case'specific':
	$m1=1;
	$sqlb='select format(sum(Budget),2) as Budget,ShortAcctID as Account from budget_1budgets b join acctg_1chartofaccounts ca on ca.AccountID=b.AccountID where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\'  ';
		// echo $sqlb; exit();
		$stmt=$link->query($sqlb); $resultb=$stmt->fetch();
	
	$txnid=intval($_GET['TxnID']);
	 $sql='select format(@running_total:=@running_total - Actual,2) AS RemainingBudget,format(Actual,2) as Actual,Date,TxnID,ControlNo,`Supplier/Customer/Branch`,Particulars,Branch from 
		(select ControlNo,`Supplier/Customer/Branch`,Particulars,ut.TxnID,Amount as Actual,Date,Branch from '.$currentyr.'_static.acctg_0unialltxns ut left join acctg_1budgetentities be on be.EntityID=ut.FromBudgetOf left join 1branches b on b.BranchNo=ut.BranchNo left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID where ut.FromBudgetOf=\''.$entity.'\' AND ut.AccountID=\''.$account.'\' AND month(ut.Date) between \''.$m1.'\' AND \''.$_GET['MonthValue'].'\' and ControlNo not like \'%BegBal\'  ) Actual JOIN 
		(SELECT @running_total:=sum(Budget) from budget_1budgets b where b.EntityID=\''.$entity.'\' AND b.AccountID=\''.$account.'\' ) Budget Order By Date';
		// echo $sql; exit();
		$stmt=$link->query($sql); 
		$result=$stmt->fetchAll();
		
		
		echo'</br><table id="budget"">
		<tr><th>Date</th><th>ControlNo</th><th>Supplier/Customer/Branch</th><th>Particulars</th><th>Branch</th><th>Actual</th><th>RemainingBudget</th></tr>
		<tr><td>Budget for the year:</td><td></td><td></td><td></td><td></td><td></td><td>'.($resultb['Budget']).'</td></tr>';

		foreach($result as $res){
			echo'<tr><td>'.$res['Date'].'</td><td>'.$res['ControlNo'].'</td><td>'.$res['Supplier/Customer/Branch'].'</td><td>'.$res['Particulars'].'</td>
			<td>'.$res['Branch'].'</td><td>'.$res['Actual'].'</td><td>'.$res['RemainingBudget'].'</td></tr>';		
		}
	
	break;
	
	case'ReachedBudgets';
	if(!isset($_POST['submit'])){
		$_REQUEST['entity']='';
		$_REQUEST['month']='';
	}
			echo '<title>Reached 80% of the Budget</title>';
			echo'</br><h3>Reached 80% of the Budget</h3></br><table  style="float:left; border:1px solid black;  padding: 3px;  font-size:9pt;"><tr><td><h3>Filtering</h3><form method="post" action="annualbudgets.php?w=ReachedBudgets">
	<input type="text" name="entity" list="entities" placeholder="Entity" value="'.$_REQUEST['entity'].'">
	Choose Month (1 - 12):<input type="text" name="month" size="1" value="'.$_REQUEST['month'].'">
	<input type="submit" name="submit" value="Lookup">	
	</form></td></tr></table></br>';
	
	if(isset($_POST['submit'])){
		$entity=companyandbranchValue($link, 'acctg_1budgetentities', 'Entity', $_REQUEST['entity'], 'EntityID');	
		$sql1='Create Temporary table Budget as select sum(Budget) as Budget,AccountID from budget_1budgets where EntityID=\''.$entity.'\' and Month=\''.$_POST['month'].'\' Group By AccountID';
		// echo $sql1; exit();
		$stmt1=$link->prepare($sql1); $stmt1->execute();
		
		$sql2='Create Temporary table Actual as select sum(Amount) as Actual,AccountID from '.$currentyr.'_static.acctg_0unialltxns where FromBudgetOf=\''.$entity.'\' and month(Date)=\''.$_POST['month'].'\' Group By AccountID';
		$stmt2=$link->prepare($sql2); $stmt2->execute();
		
		
		$title='';
		$formdesc='</br></i>Branch: <b>'.$_POST['entity'].'</b> Month: <b>'.$_POST['month'].'</b>';
		$sql='select ShortAcctID as Account,Budget,Actual,CONCAT(format((Actual/Budget)*100,2)," %") as BudgetUsage,(Actual/Budget)*100 as BudgetUsageValue,format((Budget-Actual),2) as RemainingBudget from Budget b left join Actual a on a.AccountID=b.AccountID left join acctg_1chartofaccounts ca on ca.AccountID=b.AccountID having BudgetUsageValue>=\'80\'';
		$columnnames=array('Account','Budget','Actual','BudgetUsage','RemainingBudget');
		 include('../backendphp/layout/displayastablenosort.php');
		
	}
	
	break;
	
            
   
}
  $link=null; $stmt=null;
?>
</body>
</html>