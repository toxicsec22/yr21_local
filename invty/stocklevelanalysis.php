<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(7153,7154,7155);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check

	if(isset($_GET['w']) and ($_GET['w']=='Turnover' or $_GET['w']=='All' or $_GET['w']=='AllItemsAllBranches')){
		$showbranches=false;
	}

include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT ItemCode, ItemDesc FROM `invty_1items` ORDER BY ItemCode','ItemDesc','ItemCode','items'); 
include_once('../backendphp/layout/linkstyle.php');
$which=!isset($_GET['w'])?'OpportunityLoss':$_GET['w'];



if (in_array($which,array('All','OpportunityLoss','AllItemsPerBranch'))){
?>

<style>
.flex-container {
  display: flex;
}

.flex-container > div {
  margin: 5px;
  padding: 5px;
}
</style>
<?php
}


//links
if($which!='Turnover'){
	echo'</br><a id="link" href="stocklevelanalysis.php?w=OpportunityLoss">Per Item Per Branch</a>';
	echo' <a id="link" href="stocklevelanalysis.php?w=All">Per Item All Branches</a>';
	echo' <a id="link" href="stocklevelanalysis.php?w=AllItemsPerBranch">All Items Per Branch</a>';
	echo' <a id="link" href="stocklevelanalysis.php?w=AllItemsAllBranches">All Items All Branches</a>';
}

?>
<style>
#table {
  border-collapse: collapse;
  font-size:10pt;
  padding: 5px;
  background-color:#FFFFCC;
}

#table td, #table th, #table tr {
  border: 1px solid black;
  padding: 5px;
}

#table tr:nth-child(even){background-color:#FFFFFF;}
</style>
<?php
switch($which){
	case'AllItemsAllBranches':
if (!allowedToOpen(7153,'1rtc')){   echo 'No permission'; exit;}
echo'</br>';
	$sqli='select ItemCode,Concat(Category,\'-\',ItemDesc) as Description from invty_1items i left join invty_1category c on c.CatNo=i.CatNo where MoveType=8';
	$stmti=$link->query($sqli); $resulti=$stmti->fetchAll();
	
$sqlu='create temporary table AllItemsAllBranches as ';	
foreach($resulti as $resi){
		$postitemcode=$resi['ItemCode'];
		$sqlb='SELECT BranchNo,Branch FROM 1branches where PseudoBranch=0 and Active=1';
		$stmtb=$link->query($sqlb); $resultb=$stmtb->fetchAll();
	
	foreach($resultb as $resb){

		$sql0='CREATE TEMPORARY TABLE `'.$postitemcode.''.$resb['Branch'].'AllTransactions` AS '
                            . 'SELECT Date,`s`.`ItemCode`,`s`.`Qty`,`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2mrr` `m` JOIN `invty_2mrrsub` `s` ON `m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date,`s`.`ItemCode`,`s`.`Qty`,`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2pr` `m` JOIN `invty_2prsub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date, `s`.`ItemCode`,(`s`.`Qty` * -(1)) AS `Qty`,`m`.`BranchNo`,IF((`s`.`DecisionNo` = 3),0,1),txntype FROM `invty_2pr` `m` JOIN `invty_2prsub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE (`s`.`DecisionNo` in (2,3)) AND BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT DateIN, `s`.`ItemCode`,`s`.`QtyReceived`,`m`.`ToBranchNo`,`s`.`Defective`,txntype FROM `invty_2transfer` `m` JOIN `invty_2transfersub` `s` ON `m`.`TxnID` = `s`.`TxnID` WHERE (`m`.`DateIN` is not null) and (year(`m`.`DateIN`) = '.$currentyr.') AND ToBranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date, `s`.`ItemCode`,(`s`.`Qty` * -(1)),`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2sale` `m` JOIN `invty_2salesub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT DateOUT, `s`.`ItemCode`,(`s`.`QtySent` * -(1)) AS `Expr1`,`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2transfer` `m` JOIN `invty_2transfersub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE (year(`m`.`DateOUT`) = '.$currentyr.') AND BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date, `s`.`ItemCode`,`s`.`Qty`,`m`.`BranchNo`,`s`.`Defective`, 20 AS txntype FROM `invty_4adjust` `m` JOIN `invty_4adjustsub` `s` ON `m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT \''.$currentyr.'-01-01\', `b`.`ItemCode`,`b`.`BegInv`,`b`.`BranchNo`,0,0 AS txntype FROM `invty_1beginv` `b` WHERE BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')';
                    
// echo $sql0; exit();
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql1='Create Temporary table `'.$postitemcode.''.$resb['Branch'].'RunningTotalTable` select Date,ItemCode,(@running_total:=@running_total + Qty) as RunningTotal
	
		from (SELECT ItemCode,Date, sum(Qty) as Qty from `'.$postitemcode.''.$resb['Branch'].'AllTransactions` Group By Date order by Date) Qty
		
		join (SELECT @running_total:=0) RunningTotal order by Date,Qty';
		// echo $sql1; exit();
$stmt1=$link->prepare($sql1); $stmt1->execute();

$datetoday=date('Y-m-d');


	$sql='select * from `'.$postitemcode.''.$resb['Branch'].'RunningTotalTable`';
	$stmt=$link->query($sql); $resultw=$stmt->fetchAll();
        
        $counter1=0; $stockout=0; $newdate=''; $olddate='';

	foreach($resultw AS $result){
		
		if($result['RunningTotal']==0){
                        if($counter1==0){ 
							$olddate=$result['Date'];
						}
			$counter1++;
                        
		} elseif($result['RunningTotal']>0){
                    $newdate=$result['Date'];
                        if($counter1<>0){ 
                            $stmt=$link->query('SELECT DATEDIFF(\''.$newdate.'\',\''.$olddate.'\') AS StockOutDays');
                            $res=$stmt->fetch();
                            $days=$res['StockOutDays'];  
                        
                        $stockout=$stockout+$days;}
                        $counter1=0;
                        $newdate=$olddate;			
		}	
	}
	// echo $olddate; exit();
	$sql='select RunningTotal from `'.$postitemcode.''.$resb['Branch'].'RunningTotalTable` ORDER By Date Desc Limit 1';
			$stmt=$link->query($sql); $resultc=$stmt->fetch();
			if($resultc['RunningTotal']<=0){
				$newdate=$datetoday;
							$stmt=$link->query('SELECT DATEDIFF(\''.$newdate.'\',\''.$olddate.'\') AS StockOutDays');
                            $res=$stmt->fetch();
                            $days=$res['StockOutDays'];  
                        
                        $stockout=$stockout+$days;
			}
//execute table

$sql1='SELECT PriceLevel FROM 1branches WHERE BranchNo='.$resb['BranchNo'].'';
$stmt=$link->query($sql1); $res=$stmt->fetch(); 
$pricelevel=$res['PriceLevel'];

				$sqlu.='SELECT \''.$resb['BranchNo'].'\' as BranchNo,i.ItemCode as ItemCode,Concat(Category,\'-\',ItemDesc) as Description, TRUNCATE(AVG(Sold),0) as AveQtySoldPerDay,'.$stockout.' AS DaysStockOut,UnitPrice, truncate(AVG(Sold),0)*'.$stockout.'*UnitPrice as OpportunityLoss FROM invty_1items i left join invty_1category c on c.CatNo=i.CatNo
				
				left JOIN (SELECT ItemCode, SUM(Qty) AS Sold FROM invty_2salesub ss JOIN invty_2sale s ON s.TxnID=ss.TxnID where s.BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.') GROUP BY `Date`) s1 ON s1.ItemCode=i.ItemCode
				
				
                left JOIN (SELECT ItemCode,AVG(`PriceLevel'.$pricelevel.'`) AS UnitPrice FROM invty_5latestminprice lmp where ItemCode in ('.$postitemcode.')) p on p.ItemCode=i.ItemCode where i.ItemCode in ('.$postitemcode.') UNION ALL ';

				
				

	}
}
	$sqlu=substr($sqlu, 0, -10);
	// echo $sqlu; exit();
	$stmtu=$link->prepare($sqlu); $stmtu->execute();
	$sql='select ItemCode,Description,format(sum(UnitPrice),2) as UnitPrice, sum(AveQtySoldPerDay) as AveQtySoldPerDay ,sum(DaysStockOut) as DaysStockOut, format(sum(OpportunityLoss),0) as OpportunityLoss from AllItemsAllBranches Group By ItemCode';
	$title='Opportunity Loss All Items Per Branch';
	$columnnames=array('ItemCode','Description','UnitPrice','AveQtySoldPerDay','DaysStockOut','OpportunityLoss');		
	$hidecount=true;
	include('../backendphp/layout/displayastablenosort.php');
break;

case'AllItemsPerBranch':
if (!allowedToOpen(7153,'1rtc')){   echo 'No permission'; exit;}
echo'</br><title>Opportunity Loss All Items Per Branch</title></br><h3>Opportunity Loss All Items Per Branch</h3>';
	$sqli='select ItemCode,Concat(Category,\'-\',ItemDesc) as Description from invty_1items i left join invty_1category c on c.CatNo=i.CatNo where MoveType=8';
	$stmti=$link->query($sqli); $resulti=$stmti->fetchAll();
$c=1;	
echo'<div class="flex-container">';
while($c<=3){
if($c==1){
	$tablename='first';
	$backgroundcolor='background-color:#d6e0f5;';
	$formdesc='</i><center><b>ITEMCODE</b></center></br>';

}elseif($c==2){
	$tablename='second';
	$backgroundcolor='background-color:#f2f2f2;';	
	$formdesc='</i><center><b>PRODUCTCODES</b></center></br>';
}else{
	$tablename='third';
	$backgroundcolor='background-color:#e6ffff;';	
	$formdesc='</i><center><b>SUBSTITUTES</b></center></br>';
}

echo'<div style="'.$backgroundcolor.'">';	
$sqlu='create temporary table '.$c.'AllItemsPerBranch as ';	

	foreach($resulti as $resi){
$postitemcode=$resi['ItemCode'];
			
if($c==1){
	$postitemcode=$resi['ItemCode'];
}elseif($c==2){
		$itemcodes=explode(',',$postitemcode);	
			$postitemcode='';
			foreach($itemcodes as $icodes){
			$itemcode=$icodes;
			$sqlc='select ItemCodes from invty_1productcode where FIND_IN_SET(\''.$itemcode.'\',ItemCodes)';
			$stmtc=$link->query($sqlc); $resultc=$stmtc->fetch();
				if($stmtc->rowCount()!=0){
				$postitemcode.=''.$resultc['ItemCodes'].',';
				}else{
					$postitemcode.=$resi['ItemCode'].',';
				}
			}
			$postitemcode=substr($postitemcode, 0, -1);
}else{
	$itemcodes=explode(',',$postitemcode);	
			$postitemcode='';
			foreach($itemcodes as $icodes){
			$itemcode=$icodes;
			$sqlc='select ItemCodes from invty_1substitution where FIND_IN_SET(\''.$itemcode.'\',ItemCodes)';
			$stmtc=$link->query($sqlc); $resultc=$stmtc->fetch();
				if($stmtc->rowCount()!=0){
				$postitemcode.=''.$resultc['ItemCodes'].',';
				}else{
					$postitemcode.=$resi['ItemCode'].',';
				}
			}
			$postitemcode=substr($postitemcode, 0, -1);
		// echo $postitemcode; exit();
}

		$sql0='CREATE TEMPORARY TABLE `'.$resi['ItemCode'].''.$tablename.''.$c.'AllTransactions` AS '
                            . 'SELECT Date,`s`.`ItemCode`,`s`.`Qty`,`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2mrr` `m` JOIN `invty_2mrrsub` `s` ON `m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date,`s`.`ItemCode`,`s`.`Qty`,`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2pr` `m` JOIN `invty_2prsub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date, `s`.`ItemCode`,(`s`.`Qty` * -(1)) AS `Qty`,`m`.`BranchNo`,IF((`s`.`DecisionNo` = 3),0,1),txntype FROM `invty_2pr` `m` JOIN `invty_2prsub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE (`s`.`DecisionNo` in (2,3)) AND BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT DateIN, `s`.`ItemCode`,`s`.`QtyReceived`,`m`.`ToBranchNo`,`s`.`Defective`,txntype FROM `invty_2transfer` `m` JOIN `invty_2transfersub` `s` ON `m`.`TxnID` = `s`.`TxnID` WHERE (`m`.`DateIN` is not null) and (year(`m`.`DateIN`) = '.$currentyr.') AND ToBranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date, `s`.`ItemCode`,(`s`.`Qty` * -(1)),`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2sale` `m` JOIN `invty_2salesub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT DateOUT, `s`.`ItemCode`,(`s`.`QtySent` * -(1)) AS `Expr1`,`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2transfer` `m` JOIN `invty_2transfersub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE (year(`m`.`DateOUT`) = '.$currentyr.') AND BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date, `s`.`ItemCode`,`s`.`Qty`,`m`.`BranchNo`,`s`.`Defective`, 20 AS txntype FROM `invty_4adjust` `m` JOIN `invty_4adjustsub` `s` ON `m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT \''.$currentyr.'-01-01\', `b`.`ItemCode`,`b`.`BegInv`,`b`.`BranchNo`,0,0 AS txntype FROM `invty_1beginv` `b` WHERE BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')';
                    
// echo $sql0; exit();
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql1='Create Temporary table `'.$resi['ItemCode'].''.$tablename.''.$c.'RunningTotalTable` select Date,ItemCode,(@running_total:=@running_total + Qty) as RunningTotal
	
		from (SELECT ItemCode,Date, sum(Qty) as Qty from `'.$resi['ItemCode'].''.$tablename.''.$c.'AllTransactions` Group By Date order by Date) Qty
		
		join (SELECT @running_total:=0) RunningTotal order by Date,Qty';
		// echo $sql1; exit();
$stmt1=$link->prepare($sql1); $stmt1->execute();

$datetoday=date('Y-m-d');


	$sql='select * from `'.$resi['ItemCode'].''.$tablename.''.$c.'RunningTotalTable`';
	$stmt=$link->query($sql); $resultw=$stmt->fetchAll();
        
        $counter1=0; $stockout=0; $newdate=''; $olddate='';

	foreach($resultw AS $result){
		
		if($result['RunningTotal']==0){
                        if($counter1==0){ 
							$olddate=$result['Date'];
						}
			$counter1++;
                        
		} elseif($result['RunningTotal']>0){
                    $newdate=$result['Date'];
                        if($counter1<>0){ 
                            $stmt=$link->query('SELECT DATEDIFF(\''.$newdate.'\',\''.$olddate.'\') AS StockOutDays');
                            $res=$stmt->fetch();
                            $days=$res['StockOutDays'];  
                        
                        $stockout=$stockout+$days;}
                        $counter1=0;
                        $newdate=$olddate;			
		}	
	}
	// echo $olddate; exit();
	$sql='select RunningTotal from `'.$resi['ItemCode'].''.$tablename.''.$c.'RunningTotalTable` ORDER By Date Desc Limit 1';
			$stmt=$link->query($sql); $resultc=$stmt->fetch();
			if($resultc['RunningTotal']<=0){
				$newdate=$datetoday;
							$stmt=$link->query('SELECT DATEDIFF(\''.$newdate.'\',\''.$olddate.'\') AS StockOutDays');
                            $res=$stmt->fetch();
                            $days=$res['StockOutDays'];  
                        
                        $stockout=$stockout+$days;
			}
//execute table

$sql1='SELECT PriceLevel FROM 1branches WHERE BranchNo='.$_SESSION['bnum'].'';
$stmt=$link->query($sql1); $res=$stmt->fetch(); 
$pricelevel=$res['PriceLevel'];

				$sqlu.='SELECT group_concat(distinct(i.ItemCode)) as ItemCode,group_concat(distinct(Concat(Category,\'-\',ItemDesc))  SEPARATOR \' * \') as Description, TRUNCATE(AVG(Sold),0) as AveQtySoldPerDay,'.$stockout.' AS DaysStockOut,UnitPrice, truncate(AVG(Sold),0)*'.$stockout.'*UnitPrice as OpportunityLoss FROM invty_1items i left join invty_1category c on c.CatNo=i.CatNo
				
				left JOIN (SELECT ItemCode, SUM(Qty) AS Sold FROM invty_2salesub ss JOIN invty_2sale s ON s.TxnID=ss.TxnID where s.BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.') GROUP BY `Date`) s1 ON s1.ItemCode=i.ItemCode
				
				
                left JOIN (SELECT ItemCode,AVG(`PriceLevel'.$pricelevel.'`) AS UnitPrice FROM invty_5latestminprice lmp where ItemCode in ('.$postitemcode.')) p on p.ItemCode=i.ItemCode where i.ItemCode in ('.$postitemcode.') UNION ALL ';

				
				

	}
	$sqlu=substr($sqlu, 0, -10);
	// echo $sqlu; exit();
	$stmtu=$link->prepare($sqlu); $stmtu->execute();
	$sql='select ItemCode,Description,format(UnitPrice,2) as UnitPrice, AveQtySoldPerDay ,DaysStockOut, format(OpportunityLoss,0) as OpportunityLoss from '.$c.'AllItemsPerBranch';
	$title='';
	$columnnames=array('ItemCode','Description','UnitPrice','AveQtySoldPerDay','DaysStockOut','OpportunityLoss');		
	$hidecount=true;
$sql1='SELECT SUM(OpportunityLoss) AS Total FROM '.$c.'AllItemsPerBranch';
$stmt1=$link->query($sql1); $res1=$stmt1->fetch();
$formdesc.='</i><br>Total for '.$_SESSION['@brn'].': '.number_format($res1['Total'],0).'';
	include('../backendphp/layout/displayastablenosort.php');
echo'</div>';
$c++;	
}
echo'</div>';	        
break;

case'OpportunityLoss':
if (!allowedToOpen(7153,'1rtc')){   echo 'No permission'; exit;}
	$title='Opportunity Loss Per Item Per Branch';
	echo'<title>'.$title.'</title></br></br><h3>'.$title.'</h3><i>Based on actual data, like Item Activity</i><br /><br />';
	
			echo'<form method="post" action="stocklevelanalysis.php?w=OpportunityLoss">
						ItemCode: <input type="text" name="ItemCode" placeholder="1,2,3" size="10">
								  <input type="submit" name="submit" value="LookUp">
						Search:   <input type="text" name="list" list="items" size="10">
								 </form></br>';
		
//1 itemcode 2 productcode 3 substitute
$c=1;
		if(isset($_POST['submit'])){
echo'<div class="flex-container">';				
while($c<=3){			

if($c==1){
$backgroundcolor='background-color:#d6e0f5;';
	$postitemcode=$_POST['ItemCode'];
		
	$sqlf='select ItemCode,Concat(Category,\'-\',ItemDesc,\'-\',Unit) as Description from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode in ('.$postitemcode.')';
	$stmtf=$link->query($sqlf); $resultf=$stmtf->fetchAll(); 
	$formdesc='</i><center><b>ITEMCODES</b></center></br>';
	foreach($resultf as $resf){
	$formdesc.=''.$resf['ItemCode'].' '.$resf['Description'].'</br>';
	}
}elseif($c==2){
$backgroundcolor='background-color:#f2f2f2;';	
		$itemcodes=explode(',',$_POST['ItemCode']);	
			$postitemcode='';
			foreach($itemcodes as $icodes){
			$itemcode=$icodes;
			$sqlc='select ItemCodes from invty_1productcode where FIND_IN_SET(\''.$itemcode.'\',ItemCodes)';
			$stmtc=$link->query($sqlc); $resultc=$stmtc->fetch();
				if($stmtc->rowCount()!=0){
				$postitemcode.=''.$resultc['ItemCodes'].',';
				}else{
					$postitemcode.=$_POST['ItemCode'].',';
				}
			}
			$postitemcode=substr($postitemcode, 0, -1);
		// echo $postitemcode; exit();
		
	$sqlf='select ItemCode,Concat(Category,\'-\',ItemDesc,\'-\',Unit) as Description from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode in ('.$postitemcode.')';
	$stmtf=$link->query($sqlf); $resultf=$stmtf->fetchAll(); 
	$formdesc='</i><center><b>PRODUCTCODES</b></center></br>';
	foreach($resultf as $resf){
	$formdesc.=''.$resf['ItemCode'].' '.$resf['Description'].'</br>';	
	}
}else{
$backgroundcolor='background-color:#e6ffff;';	
	$itemcodes=explode(',',$_POST['ItemCode']);	
			$postitemcode='';
			foreach($itemcodes as $icodes){
			$itemcode=$icodes;
			$sqlc='select ItemCodes from invty_1substitution where FIND_IN_SET(\''.$itemcode.'\',ItemCodes)';
			$stmtc=$link->query($sqlc); $resultc=$stmtc->fetch();
				if($stmtc->rowCount()!=0){
				$postitemcode.=''.$resultc['ItemCodes'].',';
				}else{
					$postitemcode.=$_POST['ItemCode'].',';
				}
			}
			$postitemcode=substr($postitemcode, 0, -1);
		// echo $postitemcode; exit();
		
	$sqlf='select ItemCode,Concat(Category,\'-\',ItemDesc,\'-\',Unit) as Description from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode in ('.$postitemcode.')';
	$stmtf=$link->query($sqlf); $resultf=$stmtf->fetchAll(); 
	$formdesc='</i><center><b>SUBSTITUTES</b></center></br>';
	foreach($resultf as $resf){
	$formdesc.=''.$resf['ItemCode'].' '.$resf['Description'].'</br>';
	}
}

echo'<div style="'.$backgroundcolor.'">';

                    $sql0='CREATE TEMPORARY TABLE '.$c.'AllTransactions AS '
                            . 'SELECT Date,`s`.`ItemCode`,`s`.`Qty`,`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2mrr` `m` JOIN `invty_2mrrsub` `s` ON `m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date,`s`.`ItemCode`,`s`.`Qty`,`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2pr` `m` JOIN `invty_2prsub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date, `s`.`ItemCode`,(`s`.`Qty` * -(1)) AS `Qty`,`m`.`BranchNo`,IF((`s`.`DecisionNo` = 3),0,1),txntype FROM `invty_2pr` `m` JOIN `invty_2prsub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE (`s`.`DecisionNo` in (2,3)) AND BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT DateIN, `s`.`ItemCode`,`s`.`QtyReceived`,`m`.`ToBranchNo`,`s`.`Defective`,txntype FROM `invty_2transfer` `m` JOIN `invty_2transfersub` `s` ON `m`.`TxnID` = `s`.`TxnID` WHERE (`m`.`DateIN` is not null) and (year(`m`.`DateIN`) = '.$currentyr.') AND ToBranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date, `s`.`ItemCode`,(`s`.`Qty` * -(1)),`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2sale` `m` JOIN `invty_2salesub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT DateOUT, `s`.`ItemCode`,(`s`.`QtySent` * -(1)) AS `Expr1`,`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2transfer` `m` JOIN `invty_2transfersub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE (year(`m`.`DateOUT`) = '.$currentyr.') AND BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date, `s`.`ItemCode`,`s`.`Qty`,`m`.`BranchNo`,`s`.`Defective`, 20 AS txntype FROM `invty_4adjust` `m` JOIN `invty_4adjustsub` `s` ON `m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT \''.$currentyr.'-01-01\', `b`.`ItemCode`,`b`.`BegInv`,`b`.`BranchNo`,0,0 AS txntype FROM `invty_1beginv` `b` WHERE BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.')';
                    
// echo $sql0; exit();
$stmt0=$link->prepare($sql0); $stmt0->execute();

	$sql1='Create Temporary table '.$c.'RunningTotalTable select Date,ItemCode,(@running_total:=@running_total + Qty) as RunningTotal
	
		from (SELECT ItemCode,Date, sum(Qty) as Qty from '.$c.'AllTransactions Group By Date order by Date) Qty
		
		join (SELECT @running_total:=0) RunningTotal order by Date,Qty';
		// echo $sql1; exit();
	$stmt1=$link->prepare($sql1); $stmt1->execute();


$datetoday=date('Y-m-d');


	$sql='select * from '.$c.'RunningTotalTable';
	$stmt=$link->query($sql); $resultw=$stmt->fetchAll();
        
        $counter1=0; $stockout=0; $newdate=''; $olddate='';

	foreach($resultw AS $result){
		
		if($result['RunningTotal']==0){
                        if($counter1==0){ 
							$olddate=$result['Date'];
						}
			$counter1++;
                        
		} elseif($result['RunningTotal']>0){
                    $newdate=$result['Date'];
                        if($counter1<>0){ 
                            $stmt=$link->query('SELECT DATEDIFF(\''.$newdate.'\',\''.$olddate.'\') AS StockOutDays');
                            $res=$stmt->fetch();
                            $days=$res['StockOutDays'];  
                        
                        $stockout=$stockout+$days;}
                        $counter1=0;
                        $newdate=$olddate;			
		}	
	}
	// echo $olddate; exit();
	$sql='select RunningTotal from '.$c.'RunningTotalTable ORDER By Date Desc Limit 1';
			$stmt=$link->query($sql); $resultc=$stmt->fetch();
			if($resultc['RunningTotal']<=0){
				$newdate=$datetoday;
							$stmt=$link->query('SELECT DATEDIFF(\''.$newdate.'\',\''.$olddate.'\') AS StockOutDays');
                            $res=$stmt->fetch();
                            $days=$res['StockOutDays'];  
                        
                        $stockout=$stockout+$days;
			}
//execute table
$title='';
$sql1='SELECT PriceLevel FROM 1branches WHERE BranchNo='.$_SESSION['bnum'];
$stmt=$link->query($sql1); $res=$stmt->fetch(); 
$pricelevel=$res['PriceLevel'];

				$sql='SELECT i.ItemCode,Concat(Category,\'-\',ItemDesc) as Description, TRUNCATE(AVG(Sold),0) as AveQtySoldPerDay,'.$stockout.' AS DaysStockOut,FORMAT(UnitPrice,2) as UnitPrice, FORMAT(truncate(AVG(Sold),0)*'.$stockout.'*UnitPrice,0) as OpportunityLoss FROM invty_1items i left join invty_1category c on c.CatNo=i.CatNo
				
				JOIN (SELECT ItemCode, SUM(Qty) AS Sold FROM invty_2salesub ss JOIN invty_2sale s ON s.TxnID=ss.TxnID where s.BranchNo='.$_SESSION['bnum'].' AND ItemCode in('.$postitemcode.') GROUP BY `Date`) s1 ON s1.ItemCode=i.ItemCode
				
				
                JOIN (SELECT ItemCode,AVG(`PriceLevel'.$pricelevel.'`) AS UnitPrice FROM invty_5latestminprice lmp where ItemCode in ('.$postitemcode.')) p on p.ItemCode=i.ItemCode';

				$columnnames=array('UnitPrice','AveQtySoldPerDay','DaysStockOut','OpportunityLoss');
				
				$hidecount=true;
				include('../backendphp/layout/displayastablenosort.php');
////////////////////////////////////////second table///////////////////////////////
        $table='</br><table id="table"><tr><th>Date</th><th>RunningTotal</th><th>DaysStockOut</th></tr>';
	$sql='select * from '.$c.'RunningTotalTable';
	$stmt=$link->query($sql); $resultw1=$stmt->fetchAll();
	 $counter2=0; $stockout1=0; $newdate1=''; $olddate1='';
		foreach($resultw1 AS $result1){
		$table.='<tr><td>'.$result1['Date'].'</td><td>'.$result1['RunningTotal'].'</td>';		
		if($result1['RunningTotal']==0){
                        if($counter2==0){ 
							$olddate1=$result1['Date'];
						}
			$counter2++;
                        
		} elseif($result1['RunningTotal']>0){
                    $newdate1=$result1['Date'];
                        if($counter2<>0){ 
                            $stmt=$link->query('SELECT DATEDIFF(\''.$newdate1.'\',\''.$olddate1.'\') AS StockOutDays');
                            $res=$stmt->fetch();
                            $days1=$res['StockOutDays'];  
                        
                        $stockout1=$stockout1+$days1;
								$table.='<td style="text-align:center;"><b> '.$stockout1.' </b></td></tr>';}
                        $counter2=0;
                        $newdate1=$olddate1;						
		}
	}
	// echo $olddate1; exit();

	$sql='select RunningTotal from '.$c.'RunningTotalTable ORDER By Date Desc Limit 1';
	$stmt=$link->query($sql); $resultc1=$stmt->fetch();
	$stockout2=0;
			if($resultc1['RunningTotal']<=0){
				$newdate1=$datetoday;
							$stmt=$link->query('SELECT DATEDIFF(\''.$newdate1.'\',\''.$olddate1.'\') AS StockOutDays');
                            $res=$stmt->fetch();
                            $days1=$res['StockOutDays'];  
                        
                        $stockout2=$stockout2+$days1;
						$table.='<td style="text-align:center;"><b> '.$stockout2.' </b></td></tr>';
			}
$table.='</table>';
	echo $table;
echo'</div>';	
$c++;
}
echo '</div>';		
		}
break;
/////////////////////////////////////////////ALL///////////////////
case'All':

if (!allowedToOpen(7153,'1rtc')){   echo 'No permission'; exit;}
$title='Opportunity Loss Per Item All Branches';
echo'<title>'.$title.'</title></br></br><h3>'.$title.'</h3></br><form method="post" action="stocklevelanalysis.php?w=All">
						ItemCode: <input type="text" name="ItemCode" list="items" size="10">
								  <input type="submit" name="submit" value="LookUp">
								 </form></br>';

//1 itemcode 2 productcode 3 substitute
$c=1;
								 								 
if(isset($_POST['submit'])){
echo'<div class="flex-container">';	
	while($c<=3){			

if($c==1){
$tablename='first';	
$backgroundcolor='background-color:#d6e0f5;';
	$postitemcode=$_POST['ItemCode'];
		
	$sqlf='select ItemCode,Concat(Category,\'-\',ItemDesc,\'-\',Unit) as Description from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode in ('.$postitemcode.')';
	$stmtf=$link->query($sqlf); $resultf=$stmtf->fetchAll(); 
	$formdesc='</i><center><b>ITEMCODE</b></center></br>';
	foreach($resultf as $resf){
	$formdesc.=''.$resf['ItemCode'].' '.$resf['Description'].'</br>';
	}
}elseif($c==2){
$tablename='second';
$backgroundcolor='background-color:#f2f2f2;';	
		$itemcodes=explode(',',$_POST['ItemCode']);	
			$postitemcode='';
			foreach($itemcodes as $icodes){
			$itemcode=$icodes;
			$sqlc='select ItemCodes from invty_1productcode where FIND_IN_SET(\''.$itemcode.'\',ItemCodes)';
			$stmtc=$link->query($sqlc); $resultc=$stmtc->fetch();
				if($stmtc->rowCount()!=0){
				$postitemcode.=''.$resultc['ItemCodes'].',';
				}else{
					$postitemcode.=$_POST['ItemCode'].',';
				}
			}
			$postitemcode=substr($postitemcode, 0, -1);
		// echo $postitemcode; exit();
		
	$sqlf='select ItemCode,Concat(Category,\'-\',ItemDesc,\'-\',Unit) as Description from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode in ('.$postitemcode.')';
	$stmtf=$link->query($sqlf); $resultf=$stmtf->fetchAll(); 
	$formdesc='</i><center><b>PRODUCTCODES</b></center></br>';
	foreach($resultf as $resf){
	$formdesc.=''.$resf['ItemCode'].' '.$resf['Description'].'</br>';	
	}
}else{
$tablename='third';
$backgroundcolor='background-color:#e6ffff;';	
	$itemcodes=explode(',',$_POST['ItemCode']);	
			$postitemcode='';
			foreach($itemcodes as $icodes){
			$itemcode=$icodes;
			$sqlc='select ItemCodes from invty_1substitution where FIND_IN_SET(\''.$itemcode.'\',ItemCodes)';
			$stmtc=$link->query($sqlc); $resultc=$stmtc->fetch();
				if($stmtc->rowCount()!=0){
				$postitemcode.=''.$resultc['ItemCodes'].',';
				}else{
					$postitemcode.=$_POST['ItemCode'].',';
				}
			}
			$postitemcode=substr($postitemcode, 0, -1);
		// echo $postitemcode; exit();
		
	$sqlf='select ItemCode,Concat(Category,\'-\',ItemDesc,\'-\',Unit) as Description from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode in ('.$postitemcode.')';
	$stmtf=$link->query($sqlf); $resultf=$stmtf->fetchAll(); 
	$formdesc='</i><center><b>SUBSTITUTES</b></center></br>';
	foreach($resultf as $resf){
	$formdesc.=''.$resf['ItemCode'].' '.$resf['Description'].'</br>';
	}
}
echo'<div style="'.$backgroundcolor.'">';

$sqlu='create temporary table '.$tablename.'PerItemAllBranches as ';
	$sqlb='SELECT BranchNo,Branch FROM 1branches where PseudoBranch=0 and Active=1';
	$stmtb=$link->query($sqlb); $resultb=$stmtb->fetchAll();
	
	foreach($resultb as $resb){
		$sql0='CREATE TEMPORARY TABLE '.$tablename.''.$resb['BranchNo'].'AllTransactions AS '
                            . 'SELECT Date,`s`.`ItemCode`,`s`.`Qty`,`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2mrr` `m` JOIN `invty_2mrrsub` `s` ON `m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date,`s`.`ItemCode`,`s`.`Qty`,`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2pr` `m` JOIN `invty_2prsub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date, `s`.`ItemCode`,(`s`.`Qty` * -(1)) AS `Qty`,`m`.`BranchNo`,IF((`s`.`DecisionNo` = 3),0,1),txntype FROM `invty_2pr` `m` JOIN `invty_2prsub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE (`s`.`DecisionNo` in (2,3)) AND BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT DateIN, `s`.`ItemCode`,`s`.`QtyReceived`,`m`.`ToBranchNo`,`s`.`Defective`,txntype FROM `invty_2transfer` `m` JOIN `invty_2transfersub` `s` ON `m`.`TxnID` = `s`.`TxnID` WHERE (`m`.`DateIN` is not null) and (year(`m`.`DateIN`) = '.$currentyr.') AND ToBranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date, `s`.`ItemCode`,(`s`.`Qty` * -(1)),`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2sale` `m` JOIN `invty_2salesub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT DateOUT, `s`.`ItemCode`,(`s`.`QtySent` * -(1)) AS `Expr1`,`m`.`BranchNo`,`s`.`Defective`,txntype FROM `invty_2transfer` `m` JOIN `invty_2transfersub` `s` ON`m`.`TxnID` = `s`.`TxnID` WHERE (year(`m`.`DateOUT`) = '.$currentyr.') AND BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT Date, `s`.`ItemCode`,`s`.`Qty`,`m`.`BranchNo`,`s`.`Defective`, 20 AS txntype FROM `invty_4adjust` `m` JOIN `invty_4adjustsub` `s` ON `m`.`TxnID` = `s`.`TxnID` WHERE BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')

UNION ALL SELECT \''.$currentyr.'-01-01\', `b`.`ItemCode`,`b`.`BegInv`,`b`.`BranchNo`,0,0 AS txntype FROM `invty_1beginv` `b` WHERE BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.')';
                    
// echo $sql0; exit();
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sql1='Create Temporary table '.$tablename.''.$resb['BranchNo'].'RunningTotalTable select Date,ItemCode,(@running_total:=@running_total + Qty) as RunningTotal
	
		from (SELECT ItemCode,Date, sum(Qty) as Qty from '.$tablename.''.$resb['BranchNo'].'AllTransactions Group By Date order by Date) Qty
		
		join (SELECT @running_total:=0) RunningTotal order by Date,Qty';
		// echo $sql1; exit();
$stmt1=$link->prepare($sql1); $stmt1->execute();

$datetoday=date('Y-m-d');


	$sql='select * from '.$tablename.''.$resb['BranchNo'].'RunningTotalTable';
	$stmt=$link->query($sql); $resultw=$stmt->fetchAll();
        
        $counter1=0; $stockout=0; $newdate=''; $olddate='';

	foreach($resultw AS $result){
		
		if($result['RunningTotal']==0){
                        if($counter1==0){ 
							$olddate=$result['Date'];
						}
			$counter1++;
                        
		} elseif($result['RunningTotal']>0){
                    $newdate=$result['Date'];
                        if($counter1<>0){ 
                            $stmt=$link->query('SELECT DATEDIFF(\''.$newdate.'\',\''.$olddate.'\') AS StockOutDays');
                            $res=$stmt->fetch();
                            $days=$res['StockOutDays'];  
                        
                        $stockout=$stockout+$days;}
                        $counter1=0;
                        $newdate=$olddate;			
		}	
	}
	// echo $olddate; exit();
	$sql='select RunningTotal from '.$tablename.''.$resb['BranchNo'].'RunningTotalTable ORDER By Date Desc Limit 1';
			$stmt=$link->query($sql); $resultc=$stmt->fetch();
			if($resultc['RunningTotal']<=0){
				$newdate=$datetoday;
							$stmt=$link->query('SELECT DATEDIFF(\''.$newdate.'\',\''.$olddate.'\') AS StockOutDays');
                            $res=$stmt->fetch();
                            $days=$res['StockOutDays'];  
                        
                        $stockout=$stockout+$days;
			}
//execute table

$sql1='SELECT PriceLevel FROM 1branches WHERE BranchNo='.$resb['BranchNo'].'';
$stmt=$link->query($sql1); $res=$stmt->fetch(); 
$pricelevel=$res['PriceLevel'];

				$sqlu.='SELECT \''.$resb['Branch'].'\' as Branch,i.ItemCode,Concat(Category,\'-\',ItemDesc) as Description, TRUNCATE(AVG(Sold),0) as AveQtySoldPerDay,'.$stockout.' AS DaysStockOut,UnitPrice, truncate(AVG(Sold),0)*'.$stockout.'*UnitPrice as OpportunityLoss FROM invty_1items i left join invty_1category c on c.CatNo=i.CatNo
				
				left JOIN (SELECT ItemCode, SUM(Qty) AS Sold FROM invty_2salesub ss JOIN invty_2sale s ON s.TxnID=ss.TxnID where s.BranchNo='.$resb['BranchNo'].' AND ItemCode in('.$postitemcode.') GROUP BY `Date`) s1 ON s1.ItemCode=i.ItemCode
				
				
                left JOIN (SELECT ItemCode,AVG(`PriceLevel'.$pricelevel.'`) AS UnitPrice FROM invty_5latestminprice lmp where ItemCode in ('.$postitemcode.')) p on p.ItemCode=i.ItemCode where i.ItemCode in ('.$postitemcode.') UNION ALL ';

				
				

	}
	$sqlu=substr($sqlu, 0, -10);
	// echo $sqlu; exit();
	$stmtu=$link->prepare($sqlu); $stmtu->execute();
	$sql='select Branch,format(UnitPrice,2) as UnitPrice, AveQtySoldPerDay ,DaysStockOut, format(OpportunityLoss,0) as OpportunityLoss from '.$tablename.'PerItemAllBranches';
	$title='';
	$columnnames=array('Branch','UnitPrice','AveQtySoldPerDay','DaysStockOut','OpportunityLoss');		
	$hidecount=true;
$sql1='SELECT SUM(OpportunityLoss) AS Total FROM '.$tablename.'PerItemAllBranches';
$stmt1=$link->query($sql1); $res1=$stmt1->fetch();
echo '<BR>Total for ALL Branches: '.number_format($res1['Total'],0).'</br>';	
	
	include('../backendphp/layout/displayastablenosort.php');
echo'</div>';	
$c++;
	}
echo'</div>';	
}

break;

        case 'Turnover':
if (!allowedToOpen(7154,'1rtc')){   echo 'No permission'; exit;}

$last2years=$currentyr-2;

	$sqlmonth='SELECT substring(COLUMN_NAME,1,2) as Month FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = \''.$currentyr.'_static\' AND TABLE_NAME =\'acctg_fsvaluesmonthcol\' ORDER BY ORDINAL_POSITION DESC LIMIT 1;';
	// echo $sqlmonth; exit();
	$stmtmonth=$link->query($sqlmonth); $resultmonth=$stmtmonth->fetch();
        $month=date('F',strtotime(''.$currentyr.'-'.$resultmonth['Month'].'-01'));

$title='Inventory Turnover as of '.$month.'';	
echo'<title>'.$title.'</title></br><h3>'.$title.'</h3></br><div style="background-color:white; padding:5px; width:540px;"></i><b>Notes:</b></br></br>
	Basis for information is only from protected data.</br></br>
	Inventory Turnover (Branch) = Net Sales /  Average Inventory</br></br>
	Inventory Turnover (Warehouse) = (Total Transfer Out + Net Sales) / Average Inventory</div></br>';
$presentyr=$currentyr;
$lastyear=$currentyr-1;
$lastlastyear=$currentyr-2;
//looping
while($currentyr>=$last2years){
//backgroundcolor	
	if($currentyr==$presentyr){
		$backgroundcolor='background-color:#d6e0f5;';
		$condition='where `DateOUT`<="'.$_SESSION['nb4A'].'"';
		$condition2='and pseudobranch=\'2\'';
		$condition3='and pseudobranch=0';
	}elseif($currentyr==$lastyear){
		$backgroundcolor='background-color:#f2f2f2;';
		$condition='';
		$condition2='and pseudobranch=\'2\'';
		$condition3='and pseudobranch=0';
	}else{
		$backgroundcolor='background-color:#e6ffff;';
		$condition='';
		$condition2='and b.BranchNo in (27,40,65)';
		$condition3='and b.BranchNo not in (0,27,40,65,95,96,97,98,99)';
	}
//
	
	$sqlmonth='SELECT substring(COLUMN_NAME,1,2) as Month FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = \''.$currentyr.'_static\' AND TABLE_NAME =\'acctg_fsvaluesmonthcol\' ORDER BY ORDINAL_POSITION DESC LIMIT 1;';
	// echo $sqlmonth; exit();
	$stmtmonth=$link->query($sqlmonth); $resultmonth=$stmtmonth->fetch();
switch($resultmonth['Month']){
	case'01':$month='January';break;   case'02':$month='February';break;
	case'03':$month='March';break;     case'04':$month='April';break;
	case'05':$month='May';break;       case'06':$month='June';break;
	case'07':$month='July';break;      case'08':$month='August';break;
	case'09':$month='September';break; case'10':$month='October';break;
	case'11':$month='November';break;  case'12':$month='December';break;
	
}
	
	$title='';
	$formdesc='';
echo'<div style="float:left; width:31%; padding:1%; '.$backgroundcolor.'">';	
	$table=''.$currentyr.'_1rtc.';
	
// Average Inventory
	$sqlc='Create Temporary table '.$currentyr.'AverageInventoryWarehouse as select f.BranchNo,(';
	
	$wc=1;
	while($wc<=$resultmonth['Month']){
		if(strlen($wc)==1){
			$columnname='0'.$wc.'asof';
		}else{
			$columnname=$wc.'asof';
		}
		$sqlc.='sum(`'.$columnname.'`)+';
	$wc++;
	}
	$sqlc=substr($sqlc, 0, -1);
	
	$wcdivisor=$wc-1;

	$sqlc.=')/'.$wcdivisor.' as AveInvty from '.$currentyr.'_static.acctg_fsvaluesmonthcol f left join '.$table.'1branches b on b.BranchNo=f.BranchNo where AccountID=\'300\' and Active=\'1\' '.$condition2.' group by f. BranchNo';
	// echo $sqlc; exit();
	$stmtc=$link->prepare($sqlc); $stmtc->execute();
//end	

//Sales
	$sqlc1='Create Temporary table '.$currentyr.'SalesWarehouse as SELECT f.BranchNo, (sum(`'.$columnname.'`))*-1 as NetSales FROM '.$currentyr.'_static.acctg_fsvaluesmonthcol f join '.$table.'1branches b on b.Branchno=f.Branchno where active=1 '.$condition2.' and accounttype=100 group by f.Branchno';
	// echo $sqlc1; exit();
	$stmtc1=$link->prepare($sqlc1); $stmtc1->execute();
//end

//TransferOut
	$sqlc2='Create Temporary table '.$currentyr.'TransferOut as SELECT t.BranchNo, (sum(`QtySent`*UnitPrice)) as `Out` FROM '.$table.'invty_2transfer t left join '.$table.'invty_2transfersub ts on ts.TxnID=t.TxnID join 1branches b on b.Branchno=t.Branchno '.$condition.' group by t.Branchno';
	// echo $sqlc2; exit();
	$stmtc2=$link->prepare($sqlc2); $stmtc2->execute();
//end
	
	
//total	
	$sqlt='select format(sum(`Out`),2) as TransferOut,format(sum(NetSales),2) as NetSales,format(sum(AveInvty),2) as AveInvty,truncate((sum(`Out`)+sum(NetSales))/sum(AveInvty),2) as Turnover from '.$currentyr.'AverageInventoryWarehouse a left join '.$currentyr.'SalesWarehouse s on s.BranchNo=a.BranchNo left join '.$currentyr.'TransferOut t on t.BranchNo=a.BranchNo left join '.$table.'1branches b on b.BranchNo=a.BranchNo ';
	$stmtt=$link->query($sqlt); $resultt=$stmtt->fetch();
	echo'<center><h3>'.$currentyr.'</center></h3></br><b>WAREHOUSES</b></br></br><table style="background-color:white;">
	<tr><th>Turnover</th><th>TransferOut</th><th>NetSales</th><th>AveInvty</th><tr>
	<tr><td>'.$resultt['Turnover'].'</td><td>'.$resultt['TransferOut'].'</td><td>'.$resultt['NetSales'].'</td><td>'.$resultt['AveInvty'].'</td><tr>
	</table>';
//end	
	
	$sql='select format(`Out`,2) as TransferOut,Branch as Warehouse,format(NetSales,2) as NetSales,format(AveInvty,2) as AveInvty,truncate((`Out`+NetSales)/AveInvty,2) as Turnover from '.$currentyr.'AverageInventoryWarehouse a left join '.$currentyr.'SalesWarehouse s on s.BranchNo=a.BranchNo left join '.$currentyr.'TransferOut t on t.BranchNo=a.BranchNo left join '.$table.'1branches b on b.BranchNo=a.BranchNo ORDER BY Turnover Desc';
	// $subtitle=''.$currentyr.' Warehouses';
	// echo $sql; exit();
	$columnnames=array('Warehouse','Turnover','TransferOut','NetSales','AveInvty');
	$hidecount=true;
	include('../backendphp/layout/displayastablenosort.php'); 


///////////////////////////////////////////////////////////////////////////////////////Branches///////////////////////////////////////////////////////////////////
	
// Average Inventory
	$sqlc='Create Temporary table '.$currentyr.'AverageInventory as select f.BranchNo,(';
	
	$c=1;
	while($c<=$resultmonth['Month']){
		if(strlen($c)==1){
			$columnname='0'.$c.'asof';
		}else{
			$columnname=$c.'asof';
		}
		$sqlc.='sum(`'.$columnname.'`)+';
	$c++;
	}
	$sqlc=substr($sqlc, 0, -1);
	
	$divisor=$c-1;

	$sqlc.=')/'.$divisor.' as AveInvty from '.$currentyr.'_static.acctg_fsvaluesmonthcol f left join '.$table.'1branches b on b.BranchNo=f.BranchNo where AccountID=\'300\' and Active=\'1\' '.$condition3.' group by f. BranchNo';
	// echo $sqlc; exit();
	$stmtc=$link->prepare($sqlc); $stmtc->execute();
//end	

//Sales
	$sqlc1='Create Temporary table '.$currentyr.'Sales as SELECT f.BranchNo, (sum(`'.$columnname.'`))*-1 as NetSales FROM '.$currentyr.'_static.acctg_fsvaluesmonthcol f join '.$table.'1branches b on b.branchno=f.Branchno where active=1 '.$condition3.' and accounttype=100 group by f.Branchno';
	// echo $sqlc1; exit();
	$stmtc1=$link->prepare($sqlc1); $stmtc1->execute();
//end
	
//Total	
	$sqlt='select format(sum(NetSales),2) as NetSales,format(sum(AveInvty),2) as AveInvty,truncate(sum(NetSales)/sum(AveInvty),2) as Turnover from '.$currentyr.'AverageInventory a left join '.$currentyr.'Sales s on s.BranchNo=a.BranchNo left join '.$table.'1branches b on b.BranchNo=a.BranchNo';
	$stmtt=$link->query($sqlt); $resultt=$stmtt->fetch();
	echo'</br><hr style="border-color:black;"></hr></br><center><h3>'.$currentyr.'</h3></center></br><b>BRANCHES</b></br></br><table style="background-color:white;">
	<tr><th>Turnover</th><th>NetSales</th><th>AveInvty</th><tr>
	<tr><td>'.$resultt['Turnover'].'</td><td>'.$resultt['NetSales'].'</td><td>'.$resultt['AveInvty'].'</td><tr>
	</table>';
//end	

	unset($formdesc);
	$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' Turnover');
	$columnsub=array('Branch','Turnover','NetSales','AveInvty');	
	$title='';
	$sql='select Branch,format(NetSales,2) as NetSales,format(AveInvty,2) as AveInvty,truncate(NetSales/AveInvty,2) as Turnover from '.$currentyr.'AverageInventory a left join '.$currentyr.'Sales s on s.BranchNo=a.BranchNo left join '.$table.'1branches b on b.BranchNo=a.BranchNo ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' Desc').'
	';
	// echo $sql; exit();
	// $subtitle=''.$currentyr.' Branches';
	$columnnames=array('Branch','Turnover','NetSales','AveInvty');
	$hidecount=true;
	include('../backendphp/layout/displayastablenosort.php'); 

	unset($sortfield);
		$currentyr--;
echo'</div>';
	
}
echo'<div style="clear:both"></div>';
break;

}