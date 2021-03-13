<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(736,'1rtc') AND !allowedToOpen(737,'1rtc')) { echo 'No permission'; exit;}
 
$showbranches=true; 
include_once('../switchboard/contents.php');

 $whichqry=$_GET['w'];

switch ($whichqry){
case 'BuyPattern':
    if (allowedToOpen(7361,'1rtc')) { include('../backendphp/layout/showallbranchesbutton.php'); echo '<br><br>';} else { $show=0;}
	
    //added condition
if(!isset($_POST['lookup'])){
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
			<input type="submit" name="lookup" value="'.($taon==$currentyr?$lastyr:$currentyr).'">
		</form></br>';
//

   $title=''.$taon.' Buying Pattern Per Client Per Branch - '.($show==0?$_SESSION['@brn']:'ALL');   
   $formdesc='<style> .priority { background: #e60000; color: white; font-weight: bold; }</style></i><br>'
           . 'Priority: Clients who have bought at a less frequency in the past 2 weeks than their average buying pattern.'
           . '<br><br><i>';
    $sql0='CREATE TEMPORARY TABLE Frequency AS
        SELECT  MONTHNAME(Date) AS `Month`, MONTH(Date) AS MonthNum, sm.ClientNo, sm.BranchNo, ClientName, COUNT(DISTINCT `Date`) AS Frequency, 
        '.(($taon==$lastyr)?'IF(KeyAccount=1,"Key Account","") AS ClientClass':'CASE WHEN ClientClass=1 THEN "KeyAccount" WHEN ClientClass=2 THEN "Strategic Account" ELSE 0 end as ClientClass').'
        FROM '.$dbyr.'invty_2sale sm 
        JOIN '.$dbyr.'`1clients` c ON c.ClientNo=sm.ClientNo
        JOIN '.$dbyr.'`1branches` b ON b.BranchNo=sm.BranchNo
        WHERE sm.ClientNo>10001 AND txntype<>5 '.($show==1?'':' AND sm.BranchNo='.$_SESSION['bnum']).' GROUP BY sm.ClientNo, MONTH(Date) ORDER BY MONTH(Date)'; 
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql1='SELECT `Month`, MonthNum FROM `Frequency` GROUP BY `MonthNum`';
    $stmt1=$link->query($sql1); $res1=$stmt1->fetchAll();
    
    $sql=''; $columnnames=array('ClientNo','ClientName');
    foreach ($res1 as $month){
        $columnnames[]=$month['Month'];
        $sql=$sql.', SUM(CASE WHEN `MonthNum`='.$month['MonthNum'].' THEN Frequency end) as `'.$month['Month'].'` ';
    }
    $sql='SELECT f.ClientNo, ClientName'.$sql.', CEIL(AVG(Frequency)/2) AS `2WkAve`, '
            . 'IF(IFNULL(SUM(CASE WHEN `MonthNum`='.date('m').' THEN Frequency end),0)<(AVG(Frequency)/2),"<div class=priority >&nbsp P &nbsp</div>","") AS `Priority?`,
(SELECT COUNT(*) AS Calls FROM '.$dbyr.'`calllogs_2telsub` s JOIN '.$dbyr.'`calllogs_2telmain` m ON m.`TxnID` = s.`TxnID` AND MONTH(`Date`)='.date('m').' WHERE s.`ClientNo`=f.ClientNo) AS CallsThisMonth,
(SELECT COUNT(*) AS Visits FROM '.$dbyr.'`calllogs_2visitsub` s JOIN '.$dbyr.'`calllogs_2visitmain` m ON m.`TxnID` = s.`TxnID` AND MONTH(`VisitDate`)='.date('m').' WHERE s.`ClientNo`=f.ClientNo) AS VisitsThisMonth, ClientClass FROM `Frequency` f GROUP BY ClientNo'; 
   
    array_push($columnnames,'2WkAve','Priority?','CallsThisMonth','VisitsThisMonth');
    if (allowedToOpen(7361,'1rtc')) { $columnnames[]='ClientClass';}
   // if($_SESSION['(ak0)']==1002){ echo $sql;}
   include('../backendphp/layout/displayastable.php');
   break;

case 'TopCat':
    if (!allowedToOpen(737,'1rtc')) { echo 'No permission'; exit;}   
    $title='Top Sold - ';  
    if(!isset($_POST['lookup']) OR ((strpos($_POST['lookup'],'Branch '))!==FALSE)) { $title.=$_SESSION['@brn']; $allcond=' AND m.BranchNo='.$_SESSION['bnum'];} else { $title.='ALL'; $allcond='';} 
   
    if(!isset($_POST['fromdate'])){ $fromdate=date('Y-m-d',strtotime('first day of this month')); $todate=date('Y-m-t'); $top=30; $topvalue=0; }
    else { $fromdate=$_REQUEST['fromdate']; $todate=$_REQUEST['todate']; $top=$_REQUEST['top']; }
    $formdesc='From '.$fromdate.' To '.$todate.' : Top '.$top.'%<br><br>';// &nbsp; Actual Value: '.  number_format($topvalue).'<br><br>';
    $formdesc.='</i><b>Note:</b> If there is no result, try increasing the percentage.<i><br><br>';
    //echo '<h4>'.$title.'</h4>'.$formdesc;
    ?>
<form method="post" action="lookupinvsalestools.php?w=TopCat" enctype="multipart/form-data">
    From &nbsp<input type='date' name='fromdate' value="<?php echo $fromdate; ?>"></input>&nbsp &nbsp &nbsp 
    To &nbsp<input type='date' name='todate' value="<?php echo $todate; ?>"></input>&nbsp &nbsp &nbsp &nbsp
    Top <input type='text' name='top' value="<?php echo $top; ?>" size="2"></input>% (Value) &nbsp &nbsp &nbsp
<input type="submit" name="lookup" value="Per Branch in Categories">&nbsp &nbsp &nbsp 
<?php if (allowedToOpen(7371,'1rtc')) { ?><input type="submit" name="lookup" value="ALL Branches in Categories">&nbsp &nbsp &nbsp <?php } ?>
<input type="submit" name="lookup" value="Per Branch in Items">&nbsp &nbsp &nbsp 
<?php if (allowedToOpen(7371,'1rtc')) { ?><input type="submit" name="lookup" value="ALL Branches in Items"><?php } ?></form><br><br>
<?php
   // if(!isset($_POST['fromdate'])){ goto noform;}
    $lastyrfromdate=date('Y-m-d',strtotime($fromdate.' -1 year')); $lastyrtodate=date('Y-m-d',strtotime($todate.' -1 year'));
    $last2yrsfromdate=date('Y-m-d',strtotime($fromdate.' -2 year')); $last2yrstodate=date('Y-m-d',strtotime($todate.' -2 year'));

    if(((strpos($_POST['lookup'],'Categories'))!==FALSE)){
    $sql0='DROP TEMPORARY TABLE IF EXISTS `TopCatNos`;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql0='CREATE TEMPORARY TABLE `TopCatNos` AS
        SELECT CatNo, ROUND(SUM(Qty*UnitPrice),0) AS SalesPerCat, @ts:=(@ts+SUM(Qty*UnitPrice)) AS TotalSales, "0.00" AS `%ToTotal`,
        0 AS `Yr'.$lastyr.'`, 0 AS `Yr'.$last2yrs.'`
        FROM `invty_2sale` m JOIN `invty_2salesub` s ON m.`TxnID`=s.`TxnID` JOIN `invty_1items` i ON i.`ItemCode`=s.`ItemCode` JOIN (SELECT @ts:=0) AS ts
        WHERE m.Date BETWEEN \''.$fromdate.'\' AND \''.$todate.'\' AND `txntype` IN (1,2,5) AND ClientNo NOT IN (15001,15002,15003,15004,15005) '.$allcond.' GROUP BY `CatNo` ORDER BY SalesPerCat DESC;';  
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql1='SELECT ('.($top/100).')*SUM(SalesPerCat) AS TopTotalSales, SUM(SalesPerCat) AS TotalSales FROM TopCatNos'; 
    $stmt1=$link->query($sql1); $res1=$stmt1->fetch(); $topvalue=$res1['TopTotalSales']; $totalsales=$res1['TotalSales'];
   // if($_SESSION['(ak0)']==1002){ echo $sql1;}
    $sql2='UPDATE `TopCatNos` tc SET TotalSales=(SELECT @ts:=(@ts+SalesPerCat));'; $stmt2=$link->prepare($sql2); $stmt2->execute();
    $sql2='UPDATE `TopCatNos` tc SET `%ToTotal`=(SalesPerCat/'.$totalsales.')*100;'; 
        $stmt2=$link->prepare($sql2); $stmt2->execute();
    $sql2='UPDATE `TopCatNos` tc SET `Yr'.$lastyr.'`=(SELECT ROUND(SUM(Qty*UnitPrice),0) FROM
  `'.$lastyr.'_1rtc`.`invty_2sale` m JOIN `'.$lastyr.'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID` JOIN `'.$lastyr.'_1rtc`.`invty_1items` i ON i.`ItemCode`=s.`ItemCode` 
  WHERE m.Date BETWEEN \''.$lastyrfromdate.'\' AND \''.$lastyrtodate.'\' AND `txntype` IN (1,2,5) AND ClientNo NOT IN (15001,15002,15003,15004,15005) '.$allcond.' AND i.CatNo=tc.CatNo)'; 
      $stmt2=$link->prepare($sql2); $stmt2->execute();
    $sql2='UPDATE `TopCatNos` tc SET `Yr'.$last2yrs.'`=(SELECT ROUND(SUM(Qty*UnitPrice),0) FROM
  `'.$last2yrs.'_1rtc`.`invty_2sale` m JOIN `'.$last2yrs.'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID` JOIN `'.$last2yrs.'_1rtc`.`invty_1items` i ON i.`ItemCode`=s.`ItemCode` 
  WHERE m.Date BETWEEN \''.$last2yrsfromdate.'\' AND \''.$last2yrstodate.'\' AND `txntype` IN (1,2,5) AND ClientNo NOT IN (15001,15002,15003,15004,15005) '.$allcond.' AND i.CatNo=tc.CatNo)'; 
    $stmt2=$link->prepare($sql2); $stmt2->execute();    
   // if($_SESSION['(ak0)']==1002){ echo $sql2;}
    $subtitle='In Categories';
    $sql='SELECT tc.*,Category, FORMAT(SalesPerCat,0) AS SalesPerCategory, CONCAT(ROUND(`%ToTotal`,2),"%") AS `%_ToTotal` , FORMAT(`Yr'.$lastyr.'`,0) AS `'.$lastyr.'`, FORMAT(`Yr'.$last2yrs.'`,0) AS `'.$last2yrs.'` FROM TopCatNos tc JOIN `invty_1category` c ON c.CatNo=tc.CatNo WHERE TotalSales<='.$topvalue.' ORDER BY SalesPerCat DESC';
    $columnnames=array('Category','%_ToTotal','SalesPerCategory',$lastyr,$last2yrs); // if($_SESSION['(ak0)']==1002){ echo $sql;}
    include('../backendphp/layout/displayastable.php');
    
    $subtitle='Specifics'; 
    $columnnames=array('ItemCode','Description',$currentyr.'_Qty','Unit','%ToCategoryTotal',$currentyr.'_SalesValue',$lastyr.'_Qty',$lastyr.'_SalesValue',$last2yrs.'_Qty',$last2yrs.'_SalesValue');
    $stmt1=$link->query($sql); $res1=$stmt1->fetchAll();
    $incat=''; foreach($res1 as $cat){ $incat.=$cat['CatNo'].',';}
    
    $sql1='CREATE TEMPORARY TABLE `lastyritems` AS SELECT s.ItemCode, ROUND(SUM(Qty),0) AS `'.$lastyr.'_Qty`,ROUND(SUM(Qty*UnitPrice),0) AS `'.$lastyr.'_SalesValue` FROM `'.$lastyr.'_1rtc`.`invty_2sale` m JOIN `'.$lastyr.'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID` JOIN `invty_1items` i ON i.ItemCode=s.ItemCode
  WHERE m.Date BETWEEN \''.$lastyrfromdate.'\' AND \''.$lastyrtodate.'\' AND `txntype` IN (1,2,5) AND ClientNo NOT IN (15001,15002,15003,15004,15005) AND (CatNo IN ('.$incat.'0)) 
      '.$allcond.' GROUP BY s.ItemCode ;'; $stmt1=$link->prepare($sql1); $stmt1->execute();
    
    $sql2='CREATE TEMPORARY TABLE `last2yrsitems` AS SELECT s.ItemCode, ROUND(SUM(Qty),0) AS `'.$last2yrs.'_Qty`,ROUND(SUM(Qty*UnitPrice),0) AS `'.$last2yrs.'_SalesValue` FROM `'.$last2yrs.'_1rtc`.`invty_2sale` m JOIN `'.$last2yrs.'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID` JOIN `invty_1items` i ON i.ItemCode=s.ItemCode
  WHERE m.Date BETWEEN \''.$last2yrsfromdate.'\' AND \''.$last2yrstodate.'\' AND `txntype` IN (1,2,5) AND ClientNo NOT IN (15001,15002,15003,15004,15005) AND (CatNo IN ('.$incat.'0)) 
      '.$allcond.' GROUP BY s.ItemCode ;';
    $stmt2=$link->prepare($sql2); $stmt2->execute();
    
    $sql1='CREATE TEMPORARY TABLE `currentyritems` AS SELECT s.ItemCode, ROUND(SUM(Qty),0) AS `'.$currentyr.'_Qty`, ROUND(SUM(Qty*UnitPrice),0) AS `'.$currentyr.'SalesValue`
            FROM `invty_2sale` m JOIN `invty_2salesub` s ON m.`TxnID`=s.`TxnID` JOIN `invty_1items` i ON i.ItemCode=s.ItemCode
  WHERE m.Date BETWEEN \''.$fromdate.'\' AND \''.$todate.'\' AND `txntype` IN (1,2,5) AND ClientNo NOT IN (15001,15002,15003,15004,15005) AND (CatNo IN ('.$incat.'0))
      '.$allcond.' GROUP BY s.ItemCode ;'; $stmt1=$link->prepare($sql1); $stmt1->execute();
    
    
    foreach($res1 as $cat){
        $subtitle='<BR>'.$cat['Category'];
        $sql='SELECT i.ItemCode, ItemDesc AS Description, Unit, FORMAT(`'.$currentyr.'_Qty`,0) AS `'.$currentyr.'_Qty`,
                CONCAT(ROUND(`'.$currentyr.'SalesValue`/'.$cat['SalesPerCat'].'*100,2),"%") AS `%ToCategoryTotal`, '
                . 'FORMAT(`'.$currentyr.'SalesValue`,0) AS `'.$currentyr.'_SalesValue`, 
            FORMAT(`'.$lastyr.'_Qty`,0) AS `'.$lastyr.'_Qty`, FORMAT( `'.$lastyr.'_SalesValue`,0) AS `'.$lastyr.'_SalesValue`, '
                . 'FORMAT(`'.$last2yrs.'_Qty`,0) AS `'.$last2yrs.'_Qty`, FORMAT( `'.$last2yrs.'_SalesValue`,0) AS `'.$last2yrs.'_SalesValue` '
                . 'FROM `invty_1items` i LEFT JOIN `currentyritems` curr ON i.`ItemCode`=curr.`ItemCode` LEFT JOIN `lastyritems` ly ON i.`ItemCode`=ly.`ItemCode`
            LEFT JOIN `last2yrsitems` l2y ON i.`ItemCode`=l2y.`ItemCode`  WHERE (CatNo='.$cat['CatNo'].') ORDER BY `'.$currentyr.'SalesValue` DESC;'; 
        include('../backendphp/layout/displayastableonlynoheaders.php');
    }
    } else { //items
        $formdesc.=' Items';
        
        $sql0='DROP TEMPORARY TABLE IF EXISTS `TopItems`;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
    
        $sql0='CREATE TEMPORARY TABLE `TopItems` AS
        SELECT ItemCode, ROUND(SUM(Qty*UnitPrice),0) AS SalesPerItem, ROUND(SUM(Qty),0) AS `'.$currentyr.'_Qty`, 
            @ts:=(@ts+SUM(Qty*UnitPrice)) AS TotalSales, "0.00" AS `%ToTotal`,
        0 AS `Yr'.$lastyr.'`, 0 AS `'.$lastyr.'_Qty`, 0 AS `Yr'.$last2yrs.'`, 0 AS `'.$last2yrs.'_Qty`
        FROM `invty_2sale` m JOIN `invty_2salesub` s ON m.`TxnID`=s.`TxnID` JOIN (SELECT @ts:=0) AS ts
        WHERE m.Date BETWEEN \''.$fromdate.'\' AND \''.$todate.'\' AND `txntype` IN (1,2,5) AND ClientNo NOT IN (15001,15002,15003,15004,15005) '.$allcond
                .' GROUP BY s.`ItemCode` ORDER BY SalesPerItem DESC;';  
        $stmt0=$link->prepare($sql0); $stmt0->execute();
    
        $sql1='SELECT ('.($top/100).')*SUM(SalesPerItem) AS TopTotalSales, SUM(SalesPerItem) AS TotalSales FROM TopItems'; 
    $stmt1=$link->query($sql1); $res1=$stmt1->fetch(); $topvalue=$res1['TopTotalSales']; $totalsales=$res1['TotalSales'];
    
    $sql2='UPDATE `TopItems` tc SET TotalSales=(SELECT @ts:=(@ts+SalesPerItem));'; $stmt2=$link->prepare($sql2); $stmt2->execute();
    $sql2='UPDATE `TopItems` tc SET `%ToTotal`=(SalesPerItem/'.$totalsales.')*100;'; 
        $stmt2=$link->prepare($sql2); $stmt2->execute(); 
    $sql2='UPDATE `TopItems` tc SET `Yr'.$lastyr.'`=(SELECT ROUND(SUM(Qty*UnitPrice),0) FROM
  `'.$lastyr.'_1rtc`.`invty_2sale` m JOIN `'.$lastyr.'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID` 
  WHERE m.Date BETWEEN \''.$lastyrfromdate.'\' AND \''.$lastyrtodate.'\' AND `txntype` IN (1,2,5) AND ClientNo NOT IN (15001,15002,15003,15004,15005) '.$allcond.' AND s.ItemCode=tc.ItemCode), `'.$lastyr.'_Qty`=(SELECT ROUND(SUM(Qty),0) FROM
  `'.$lastyr.'_1rtc`.`invty_2sale` m JOIN `'.$lastyr.'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID` 
  WHERE m.Date BETWEEN \''.$lastyrfromdate.'\' AND \''.$lastyrtodate.'\' AND `txntype` IN (1,2,5) AND ClientNo NOT IN (15001,15002,15003,15004,15005) '.$allcond.' AND s.ItemCode=tc.ItemCode)'; 
      $stmt2=$link->prepare($sql2); $stmt2->execute(); 
    $sql2='UPDATE `TopItems` tc SET `Yr'.$last2yrs.'`=(SELECT ROUND(SUM(Qty*UnitPrice),0) FROM
  `'.$last2yrs.'_1rtc`.`invty_2sale` m JOIN `'.$last2yrs.'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID` 
  WHERE m.Date BETWEEN \''.$last2yrsfromdate.'\' AND \''.$last2yrstodate.'\' AND `txntype` IN (1,2,5) AND ClientNo NOT IN (15001,15002,15003,15004,15005) '.$allcond.' AND s.ItemCode=tc.ItemCode), `'.$last2yrs.'_Qty`=(SELECT ROUND(SUM(Qty),0) FROM
  `'.$last2yrs.'_1rtc`.`invty_2sale` m JOIN `'.$last2yrs.'_1rtc`.`invty_2salesub` s ON m.`TxnID`=s.`TxnID` 
  WHERE m.Date BETWEEN \''.$last2yrsfromdate.'\' AND \''.$last2yrstodate.'\' AND `txntype` IN (1,2,5) AND ClientNo NOT IN (15001,15002,15003,15004,15005) '.$allcond.' AND s.ItemCode=tc.ItemCode)'; 
    $stmt2=$link->prepare($sql2); $stmt2->execute();
    
    $sql='SELECT tc.*,Category, ItemDesc AS Description, Unit, FORMAT(`'.$currentyr.'_Qty`,0) AS `'.$currentyr.'_Qty`, '
            . 'FORMAT(SalesPerItem,0) AS `'.$currentyr.'_SalesValue`, CONCAT(ROUND(`%ToTotal`,2),"%") AS `%_ToTotal` , '
            . 'FORMAT(`Yr'.$lastyr.'`,0) AS `'.$lastyr.'_SalesValue'.'`, FORMAT(`'.$lastyr.'_Qty`,0) AS `'.$lastyr.'_Qty`, '
            . 'FORMAT(`'.$last2yrs.'_Qty`,0) AS `'.$last2yrs.'_Qty`, FORMAT(`Yr'.$last2yrs.'`,0) AS `'.$last2yrs.'_SalesValue'.'` '
            . 'FROM TopItems tc JOIN `invty_1items` i ON i.ItemCode=tc.ItemCode JOIN `invty_1category` c ON c.CatNo=i.CatNo '
            . ' WHERE TotalSales<='.$topvalue.' ORDER BY SalesPerItem DESC'; 
    $columnnames=array('ItemCode','Category','Description','%_ToTotal',$currentyr.'_Qty','Unit',$currentyr.'_SalesValue',$lastyr.'_Qty',$lastyr.'_SalesValue',$last2yrs.'_Qty',$last2yrs.'_SalesValue');
    include('../backendphp/layout/displayastable.php');
    
    }
    
    break;


default:
    header('Location:'.$_SERVER['HTTP_REFERER']);
}
noform:
    
     $link=null; $stmt=null;
?>