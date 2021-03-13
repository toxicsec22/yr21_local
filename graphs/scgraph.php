<?php
	$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	if (!allowedToOpen(71311,'1rtc')) { echo 'No permission'; exit; }
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$showbranches=false;
	include_once('../switchboard/contents.php');
	$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
	
	
	
?>

<?php include($path.'/acrossyrs/js/reportcharts/includejscharts.php'); 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');

$label="'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'";
?>


<br>
<div id="section" style="display: block;">


<?php	

				
echo '<br> &nbsp; ';

	include_once('scgraphlinks.php');
	
echo '<br><br><br>';

$which=isset($_GET['w'])?$_GET['w']:'SoldItems';

if (in_array($which,array('Seasonality','MonthToMonth','HistoryPerYear','PerItemSold'))){
	$upto=2014; $yrtoday=$currentyr;
	$yrtodayf='';
	while($yrtoday>=$upto){
		$yrtodayf.='<option value="'.$yrtoday.'">'.$yrtoday.'</option>';
		$yrtoday--;
		
	}
	$yrtodayt=''; $yrtoday=$currentyr;
	
	while($yrtoday>=$upto){
		$yrtodayt.='<option value="'.$upto.'">'.$upto.'</option>';
		$upto++;
	}
	$formsearch='<br><form action="scgraph.php?w='.$which.'" method="POST">ItemCode: <input type="text" name="itemcode" size="10" placeholder="1,2,3"> From: <select name="YearFrom">'.$yrtodayt.'</select> To: <select name="YearTo">'.$yrtodayf.'</select>'.(($_GET['w']!='PerItemSold')?'Graph Width (%): <input type="text" name="gwidth" value="100" size="5">':"").' <input type="submit" name="btnLookUp" value="Lookup"> Search: <input type="text" size="10" list="itemlist"></form><br>';
	
}

if (in_array($which,array('SoldItems','Seasonality','MonthToMonth','HistoryPerYear','PerItemSold'))){
	echo comboBox($link,'SELECT ItemCode, CONCAT(Category," - ",ItemDesc) AS CatItemDesc FROM invty_1items i JOIN invty_1category c ON i.CatNo=c.CatNo ORDER BY Category','CatItemDesc','ItemCode','itemlist');
	
	if(isset($_POST['btnLookUp'])){
			$items=explode(",",$_POST['itemcode']);
			$search='';$unit='';
			$search.='<br>Items:<br>';
			foreach($items as $item){
				
				$sql='SELECT ItemCode, Unit, CONCAT("Category: [<b>",Category,"</b>], ItemDesc: <b>",ItemDesc,"</b>, Unit: <b>",Unit,"</b>") AS CatItemDesc FROM invty_1items i JOIN invty_1category c ON i.CatNo=c.CatNo WHERE ItemCode='.$item;
				$stmt=$link->query($sql); $row=$stmt->fetch();
			
				$search.='<b>'.$item.'</b> - '.$row['CatItemDesc'].'<br>';
				$unit.=$row['Unit'].',';
			}
			$unit='Unit: '.substr($unit, 0, -1);
		}
		
}

switch ($which){
	case 'SoldItems':
		$echo='';
		$graphtitle='ReportTitle';
		$title='Sold Items';
		echo '<title>'.$title.'</title>';
		echo '<h3>'.$title.'</h3>';
		
		
		echo '<form action="scgraph.php" method="POST">ItemCode: <input type="text" name="itemcode" size="10" placeholder="1,2,3"> <input type="submit" name="btnLookUp" value="Lookup"> Search: <input type="text" size="10" list="itemlist"></form>';
		
		if(isset($_POST['itemcode']) AND $_POST['itemcode']==''){
			$_POST['itemcode']=-1;			
		}
		
		if(isset($_POST['btnLookUp'])){
			
		echo $search;
		
		
		$upto=2014; $yrtoday=$currentyr-1;
		$unionsqly='SELECT ';
		while($yrtoday>=$upto){
			$unionsqly.='(SELECT IFNULL(CONCAT(SUM(`01`),",",SUM(`02`),",",SUM(`03`),",",SUM(`04`),",",SUM(`05`),",",SUM(`06`),",",SUM(`07`),",",SUM(`08`),",",SUM(`09`),",",SUM(`10`),",",SUM(`11`),",",SUM(`12`)),"0,0,0,0,0,0,0,0,0,0,0,0") FROM hist_incus.'.$upto.'_soldperitemcode WHERE ItemCode IN ('.$_POST['itemcode'].')) AS Sold'.$upto.',';
			$upto++;
		}
		$unionsqly=substr($unionsqly, 0, -1);
		$sql=$unionsqly;
		
		$stmt=$link->query($sql); $row=$stmt->fetch();
		
		
		$mn=12; $ms=1; $sqlunion=''; $year=$currentyr;
		while($ms<=$mn){
			
			$sqlunion.='SELECT 
			IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
			FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
			where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$_POST['itemcode'].')
			AND Month(sm.`Date`)='.$ms.' UNION ';
			
			$ms++;
		}
		$sqlunion=substr($sqlunion, 0, -6);
		
		$sql0='CREATE TEMPORARY TABLE tempdata'.$currentyr.' AS '.$sqlunion.'';
		$stmt0=$link->prepare($sql0); $stmt0->execute(); 
		
		$upto=2014; $yrtoday=$currentyr-1;
		$addsqlloop=''; $cntr=3;
		while($yrtoday>=$upto){
			$addsqlloop.='"'.$upto.'" AS legend'.$cntr.',"'.$row['Sold'.$upto.''].'" AS DataSet'.$cntr.',';
			$cntr++; $upto++;
		}
		
		$sql='SELECT "Sold Item" AS ReportTitle, "Month" AS xaxis, "'.$unit.'" AS yaxis, '.$addsqlloop.'"" AS IDNo,"" AS FullName,2 AS GraphID,"'.$label.'" AS Label,"'.$currentyr.'" AS legend'.$cntr.',(SELECT GROUP_CONCAT(Sold) FROM tempdata'.$currentyr.') AS DataSet'.$cntr.'';
		
				
	
		$lwidth="60%";
		$stmt=$link->query($sql); $field=$stmt->fetch();

		}
	break;
	
	case 'Seasonality':
	
	$echo='';
	$graphtitle='ReportTitle';
	$title='Seasonality';
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';
	
	echo $formsearch;
	
	
	if(isset($_POST['itemcode']) AND $_POST['itemcode']==''){
		$_POST['itemcode']=-1;			
	}
	

	if(isset($_POST['btnLookUp'])){
		echo $search;
		$yrfrom=$_POST['YearFrom'];
			$yrto=$_POST['YearTo'];
			if($yrto==$currentyr){
				$yrto=$yrto-1;
			}
			$datasetval=''; $labelval=''; $condival='';
			while ($yrfrom<=$yrto){
				
				
					$sql2="SELECT CONCAT('\'Jan".$yrfrom."\',','\'Feb".$yrfrom."\',','\'Mar".$yrfrom."\',','\'Apr".$yrfrom."\',','\'May".$yrfrom."\',','\'Jun".$yrfrom."\',','\'Jul".$yrfrom."\',','\'Aug".$yrfrom."\',','\'Sep".$yrfrom."\',','\'Oct".$yrfrom."\',','\'Nov".$yrfrom."\',','\'Dec".$yrfrom."\',') AS MonthLabel";
				// echo $sql2; 
					$stmt=$link->query($sql2); $row2=$stmt->fetch();
					$labelval.=$row2['MonthLabel'].',';
					
					
					$sql2="SELECT IFNULL(CONCAT(SUM(`01`),',',SUM(`02`),',',SUM(`03`),',',SUM(`04`),',',SUM(`05`),',',SUM(`06`),',',SUM(`07`),',',SUM(`08`),',',SUM(`09`),',',SUM(`10`),',',SUM(`11`),',',SUM(`12`)),'0,0,0,0,0,0,0,0,0,0,0,0') AS Sold FROM hist_incus.".$yrfrom."_soldperitemcode WHERE ItemCode IN (".$_POST['itemcode'].")";
				
				$stmt=$link->query($sql2); $row2=$stmt->fetch();
				$datasetval.=$row2['Sold'].',';
				
				$yrfrom=$yrfrom+1;
				
			}
			// echo $datasetval;
			$label = str_replace(",,",",",$labelval);
			$label=substr($label, 0, -1);
			
			$dataset=substr($datasetval, 0, -1);
			$dataset=substr($dataset, 0, -1);
			
			
		if (isset($_POST['YearTo']) AND $_POST['YearTo']==$currentyr){
			$mn=12; $ms=1; $sqlunion=''; $year=$currentyr;
			while($ms<=$mn){
				
				$sqlunion.='SELECT '.$ms.' AS MonthNo,
				IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
				FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
				where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$_POST['itemcode'].')
				AND Month(sm.`Date`)='.$ms.' UNION ';
				
				$ms++;
			}
			$sqlunion=substr($sqlunion, 0, -6);
			
			$sql0='CREATE TEMPORARY TABLE tempdata'.$currentyr.' AS '.$sqlunion.'';
			$stmt0=$link->prepare($sql0); $stmt0->execute();
		
			$sql1='SELECT GROUP_CONCAT(Sold) AS Sold'.$currentyr.' FROM tempdata'.$currentyr.'';
			$stmt=$link->query($sql1); $row=$stmt->fetch();
			
			
				$label=$label.",'Jan".$year."','Feb".$year."','Mar".$year."','Apr".$year."','May".$year."','Jun".$year."','Jul".$year."','Aug".$year."','Sep".$year."','Oct".$year."','Nov".$year."','Dec".$year."'";
				$dataset=$dataset.",".$row['Sold'.$currentyr.'']; 
			
		}
		
		$sql='SELECT "Sold Items" AS ReportTitle, "Month" AS xaxis, "'.$unit.'" AS yaxis, "Sold" AS legend1,"" AS IDNo,"" AS FullName,2 AS GraphID,"" AS legend2,"" AS legend3,"" AS legend4, "'.$label.'" AS Label,"'.$dataset.'" AS DataSet1';
		
		$stmt=$link->query($sql); $field=$stmt->fetch();
	}
	$lwidth=((isset($_POST['gwidth']))?$_POST['gwidth']:'100')."%";
	
	break;
	
	
	
	case 'MonthToMonth':
	
	$echo='';
	$graphtitle='ReportTitle';
	$title='Month To Month Comparison';
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';
	
	echo $formsearch;
	
	if(isset($_POST['itemcode']) AND $_POST['itemcode']==''){
		$_POST['itemcode']=-1;			
	}
	

	if(isset($_POST['btnLookUp'])){
		echo $search;

		$yrfrom=$_POST['YearFrom'];
			$yrto=$_POST['YearTo'];
			if($yrto==$currentyr){
				$yrto=$yrto-1;
			}
			$datasetval=''; $labelval=''; $condival=''; $sql2=''; 
			while ($yrfrom<=$yrto){
				
				$sql2.='SELECT '.$yrfrom.' as year,IFNULL(SUM(`01`),0) AS `01`,IFNULL(SUM(`02`),0) AS `02`,IFNULL(SUM(`03`),0) AS `03`,IFNULL(SUM(`04`),0) AS `04`,IFNULL(SUM(`05`),0) AS `05`,IFNULL(SUM(`06`),0) AS `06`,IFNULL(SUM(`07`),0) AS `07`,IFNULL(SUM(`08`),0) AS `08`,IFNULL(SUM(`09`),0) AS `09`,IFNULL(SUM(`10`),0) AS `10`,IFNULL(SUM(`11`),0) AS `11`,IFNULL(SUM(`12`),0) AS `12` FROM `hist_incus`.`'.$yrfrom.'_soldperitemcode` WHERE ItemCode IN ('.$_POST['itemcode'].') UNION ';
					
				
				$yrfrom=$yrfrom+1;
				$yeararr=$yrfrom;
			}
			
			$sql2=substr($sql2, 0, -6);
			$stmt=$link->query($sql2); $row2=$stmt->fetchAll();
			
			$legend='';
			$cnt=3; $datasets='';
			
			foreach($row2 as $rowval){
				
				$datasets.='"'.$rowval['01'].','.$rowval['02'].','.$rowval['03'].','.$rowval['04'].','.$rowval['05'].','.$rowval['06'].','.$rowval['07'].','.$rowval['08'].','.$rowval['09'].','.$rowval['10'].','.$rowval['11'].','.$rowval['12'].'" AS DataSet'.$cnt.',';
				$legend.=$rowval['year'].' AS legend'.$cnt.',';
			
				$cnt++;
			}
			
			$legend=substr($legend, 0, -1);
			$datasets=substr($datasets, 0, -1);
			 
		if (isset($_POST['YearTo']) AND $_POST['YearTo']==$currentyr){
			$mn=12; $ms=1; $sqlunion=''; $year=$currentyr;
			while($ms<=$mn){
				
				$sqlunion.='SELECT '.$ms.' AS MonthNo,
				IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
				FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
				where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$_POST['itemcode'].')
				AND Month(sm.`Date`)='.$ms.' UNION ';
				
				$ms++;
			}
			$sqlunion=substr($sqlunion, 0, -6);
			
			$sql0='CREATE TEMPORARY TABLE tempdata'.$currentyr.' AS '.$sqlunion.'';
			$stmt0=$link->prepare($sql0); $stmt0->execute();
		
			$sql1='SELECT GROUP_CONCAT(Sold) AS Sold'.$currentyr.' FROM tempdata'.$currentyr.' ';
			$stmt=$link->query($sql1); $row=$stmt->fetch();
			
				$datasets='"'.$row['Sold'.$currentyr.'']."\" AS DataSet".$cnt.','.$datasets;
				$legend=''.$currentyr.' AS legend'.$cnt.','.$legend;
		}
			$sql='SELECT "Sold Items" AS ReportTitle, "Months" AS xaxis, "'.$unit.'" AS yaxis, '.$legend.',"" AS IDNo,"" AS FullName,1 AS GraphID, "'.$label.'"  AS Label,'.$datasets.'';
		$stmt=$link->query($sql); $field=$stmt->fetch();
		
	$borderdash=1;
	$bwidth=((isset($_POST['gwidth']))?$_POST['gwidth']:'100')."%";
	}
	
	
	break;
	
	
	case 'HistoryPerYear':
	
	$echo='';
	$graphtitle='ReportTitle';
	$title='Historical';
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';
	
	echo $formsearch;
	
	if(isset($_POST['itemcode']) AND $_POST['itemcode']==''){
		$_POST['itemcode']=-1;			
	}
	

	if(isset($_POST['btnLookUp'])){
		echo $search;

		$yrfrom=$_POST['YearFrom'];
			$yrto=$_POST['YearTo'];
			if($yrto==$currentyr){
				$yrto=$yrto-1;
			}
			$datasetval=''; $labelval=''; $condival=''; $sql2=''; 
			while ($yrfrom<=$yrto){
				
				$sql2.='SELECT '.$yrfrom.' as year,
	IFNULL(SUM(`01`)+SUM(`02`)+SUM(`03`)+SUM(`04`)+SUM(`05`)+SUM(`06`)+SUM(`07`)+SUM(`08`)+SUM(`09`)+SUM(`10`)+SUM(`11`)+SUM(`12`),0) AS `YearSold`
	 FROM `hist_incus`.`'.$yrfrom.'_soldperitemcode` WHERE ItemCode IN ('.$_POST['itemcode'].') UNION ';
					
				
				$yrfrom=$yrfrom+1;
				$yeararr=$yrfrom;
			}
			
			$sql2=substr($sql2, 0, -6);
			$stmt=$link->query($sql2); $row2=$stmt->fetchAll();
			
			$legend='';
			$cnt=3; $datasets='';
			
			foreach($row2 as $rowval){
				$datasets.='"'.$rowval['YearSold'].'" AS DataSet'.$cnt.',';
				$legend.=$rowval['year'].' AS legend'.$cnt.',';
				
				$cnt++;
			}
			
			$legend=substr($legend, 0, -1);
			$datasets=substr($datasets, 0, -1);
		if (isset($_POST['YearTo']) AND $_POST['YearTo']==$currentyr){
			$year=$currentyr;
			$sqltot='SELECT 
				IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
				FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
				where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$_POST['itemcode'].')';
				$stmt=$link->query($sqltot); $row=$stmt->fetch();
			
				$datasets='"'.$row['Sold']."\" AS DataSet".$cnt.','.$datasets;
				$legend=''.$currentyr.' AS legend'.$cnt.','.$legend;
		}
			$sql='SELECT "Sold Items" AS ReportTitle, "Months" AS xaxis, "'.$unit.'" AS yaxis, '.$legend.',"" AS IDNo,"" AS FullName,1 AS GraphID, "\'Total Sold Per Year\'"  AS Label,'.$datasets.'';
		$stmt=$link->query($sql); $field=$stmt->fetch();
		// echo $sql;
		$borderdash=1;
		$bwidth=((isset($_POST['gwidth']))?$_POST['gwidth']:'100')."%";
	}
	
	break;
	
	case 'PerItemSold':
		$echo='';
	$graphtitle='ReportTitle';
	$title='PerItemCode';
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';
	echo $formsearch;
	
	if(isset($_POST['itemcode']) AND $_POST['itemcode']==''){
		$_POST['itemcode']=-1;			
	}
	

	if(isset($_POST['btnLookUp'])){
		echo $search;
		$yrfrom=$_POST['YearFrom'];
			$yrto=$_POST['YearTo'];
			if($yrto==$currentyr){
				$yrto=$yrto-1;
			}
			
			$datasetval=''; $labelval=''; $condival=''; $datasetval3=''; $datasetval4=''; $datasetval5=''; $datasetval6=''; $datasetval7=''; $datasetval8=''; $datasetval9='';$datasetval10='';$datasetval11='';
			$itemsexplode=explode(",",$_POST['itemcode']);
			// echo $_POST['itemcode']; echo '</br>';
			// print_r($itemsexplode); echo '</br>';
				
				$reset=reset($itemsexplode); $next=next($itemsexplode); $next1=next($itemsexplode); $next2=next($itemsexplode); $next3=next($itemsexplode); $next4=next($itemsexplode); $next5=next($itemsexplode); $next6=next($itemsexplode); $next7=next($itemsexplode); $next8=next($itemsexplode);	
					
				$sqll1='select CONCAT(Category,\' - \',ItemDesc) as ItemDesc from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode="'.$reset.'"'; $stmtl1=$link->query($sqll1); $rowl1=$stmtl1->fetch();
				$sqll2='select CONCAT(Category,\' - \',ItemDesc) as ItemDesc from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode="'.$next.'"'; $stmtl2=$link->query($sqll2); $rowl2=$stmtl2->fetch();
				$sqll3='select CONCAT(Category,\' - \',ItemDesc) as ItemDesc from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode="'.$next1.'"'; $stmtl3=$link->query($sqll3); $rowl3=$stmtl3->fetch();
				$sqll4='select CONCAT(Category,\' - \',ItemDesc) as ItemDesc from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode="'.$next2.'"'; $stmtl4=$link->query($sqll4); $rowl4=$stmtl4->fetch();
				$sqll5='select CONCAT(Category,\' - \',ItemDesc) as ItemDesc from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode="'.$next3.'"'; $stmtl5=$link->query($sqll5); $rowl5=$stmtl5->fetch();
				$sqll6='select CONCAT(Category,\' - \',ItemDesc) as ItemDesc from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode="'.$next4.'"'; $stmtl6=$link->query($sqll6); $rowl6=$stmtl6->fetch();
				$sqll7='select CONCAT(Category,\' - \',ItemDesc) as ItemDesc from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode="'.$next5.'"'; $stmtl7=$link->query($sqll7); $rowl7=$stmtl7->fetch();
				$sqll8='select CONCAT(Category,\' - \',ItemDesc) as ItemDesc from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode="'.$next6.'"'; $stmtl8=$link->query($sqll8); $rowl8=$stmtl8->fetch();
				$sqll9='select CONCAT(Category,\' - \',ItemDesc) as ItemDesc from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode="'.$next7.'"'; $stmtl9=$link->query($sqll9); $rowl9=$stmtl9->fetch();
				$sqll10='select CONCAT(Category,\' - \',ItemDesc) as ItemDesc from invty_1items i join invty_1category c on c.CatNo=i.CatNo where ItemCode="'.$next8.'"'; $stmtl10=$link->query($sqll10); $rowl10=$stmtl10->fetch();
				
				// if($next==null){$next=0;} if($next1==null){$next1=0;}
				// echo '</br>';	print_r($peritem); echo '</br>'; 
			while ($yrfrom<=$yrto){
					$sql2="SELECT ".$yrfrom." AS Year";
					$stmt=$link->query($sql2); $row2=$stmt->fetch();
					$labelval.=$row2['Year'].',';
					$sql2="SELECT ifnull((sum(`01`)+sum(`02`)+sum(`03`)+sum(`04`)+sum(`05`)+sum(`06`)+sum(`07`)+sum(`08`)+sum(`09`)+sum(`10`)+sum(`11`)+sum(`12`)),0) AS Sold FROM hist_incus.".$yrfrom."_soldperitemcode WHERE ItemCode IN (".$reset.")";	
					
					// echo $sqll1; exit();
				  // echo $sql2;
				$stmt=$link->query($sql2); $row2=$stmt->fetch();
				$datasetval.=$row2['Sold'].',';
				if($next!=null){
				$sql3="SELECT ifnull((sum(`01`)+sum(`02`)+sum(`03`)+sum(`04`)+sum(`05`)+sum(`06`)+sum(`07`)+sum(`08`)+sum(`09`)+sum(`10`)+sum(`11`)+sum(`12`)),0) AS Sold FROM hist_incus.".$yrfrom."_soldperitemcode WHERE ItemCode IN (".$next.")";		
				  // echo $sql3; 
				$stmt3=$link->query($sql3); $row3=$stmt3->fetch();
				$datasetval3.=$row3['Sold'].',';
				}
				
				if($next1!=null){
				$sql4="SELECT ifnull((sum(`01`)+sum(`02`)+sum(`03`)+sum(`04`)+sum(`05`)+sum(`06`)+sum(`07`)+sum(`08`)+sum(`09`)+sum(`10`)+sum(`11`)+sum(`12`)),0) AS Sold FROM hist_incus.".$yrfrom."_soldperitemcode WHERE ItemCode IN (".$next1.")";		
				  // echo $sql4;
				$stmt4=$link->query($sql4); $row4=$stmt4->fetch();
				$datasetval4.=$row4['Sold'].',';
				}
				
				if($next2!=null){
				$sql5="SELECT ifnull((sum(`01`)+sum(`02`)+sum(`03`)+sum(`04`)+sum(`05`)+sum(`06`)+sum(`07`)+sum(`08`)+sum(`09`)+sum(`10`)+sum(`11`)+sum(`12`)),0) AS Sold FROM hist_incus.".$yrfrom."_soldperitemcode WHERE ItemCode IN (".$next2.")";		
				  // echo $sql4;
				$stmt5=$link->query($sql5); $row5=$stmt5->fetch();
				$datasetval5.=$row5['Sold'].',';
				}
				
				if($next3!=null){
				$sql6="SELECT ifnull((sum(`01`)+sum(`02`)+sum(`03`)+sum(`04`)+sum(`05`)+sum(`06`)+sum(`07`)+sum(`08`)+sum(`09`)+sum(`10`)+sum(`11`)+sum(`12`)),0) AS Sold FROM hist_incus.".$yrfrom."_soldperitemcode WHERE ItemCode IN (".$next3.")";		
				  // echo $sql4;
				$stmt6=$link->query($sql6); $row6=$stmt6->fetch();
				$datasetval6.=$row6['Sold'].',';
				}
				
				if($next4!=null){
				$sql7="SELECT ifnull((sum(`01`)+sum(`02`)+sum(`03`)+sum(`04`)+sum(`05`)+sum(`06`)+sum(`07`)+sum(`08`)+sum(`09`)+sum(`10`)+sum(`11`)+sum(`12`)),0) AS Sold FROM hist_incus.".$yrfrom."_soldperitemcode WHERE ItemCode IN (".$next4.")";		
				  // echo $sql4;
				$stmt7=$link->query($sql7); $row7=$stmt7->fetch();
				$datasetval7.=$row7['Sold'].',';
				}
				if($next5!=null){
				$sql8="SELECT ifnull((sum(`01`)+sum(`02`)+sum(`03`)+sum(`04`)+sum(`05`)+sum(`06`)+sum(`07`)+sum(`08`)+sum(`09`)+sum(`10`)+sum(`11`)+sum(`12`)),0) AS Sold FROM hist_incus.".$yrfrom."_soldperitemcode WHERE ItemCode IN (".$next5.")";		
				  // echo $sql4;
				$stmt8=$link->query($sql8); $row8=$stmt8->fetch();
				$datasetval8.=$row8['Sold'].',';
				}
				if($next6!=null){
				$sql9="SELECT ifnull((sum(`01`)+sum(`02`)+sum(`03`)+sum(`04`)+sum(`05`)+sum(`06`)+sum(`07`)+sum(`08`)+sum(`09`)+sum(`10`)+sum(`11`)+sum(`12`)),0) AS Sold FROM hist_incus.".$yrfrom."_soldperitemcode WHERE ItemCode IN (".$next6.")";		
				  // echo $sql4;
				$stmt9=$link->query($sql9); $row9=$stmt9->fetch();
				$datasetval9.=$row9['Sold'].',';
				}
				if($next7!=null){
				$sql10="SELECT ifnull((sum(`01`)+sum(`02`)+sum(`03`)+sum(`04`)+sum(`05`)+sum(`06`)+sum(`07`)+sum(`08`)+sum(`09`)+sum(`10`)+sum(`11`)+sum(`12`)),0) AS Sold FROM hist_incus.".$yrfrom."_soldperitemcode WHERE ItemCode IN (".$next7.")";		
				  // echo $sql4;
				$stmt10=$link->query($sql10); $row10=$stmt10->fetch();
				$datasetval10.=$row10['Sold'].',';
				}
				if($next8!=null){
				$sql11="SELECT ifnull((sum(`01`)+sum(`02`)+sum(`03`)+sum(`04`)+sum(`05`)+sum(`06`)+sum(`07`)+sum(`08`)+sum(`09`)+sum(`10`)+sum(`11`)+sum(`12`)),0) AS Sold FROM hist_incus.".$yrfrom."_soldperitemcode WHERE ItemCode IN (".$next8.")";		
				  // echo $sql4;
				$stmt11=$link->query($sql11); $row11=$stmt11->fetch();
				$datasetval11.=$row11['Sold'].',';
				}
				
				
				$yrfrom=$yrfrom+1;
				}
					
			
			$label = str_replace(",,",",",$labelval);
			$label=substr($label, 0, -1);

			$dataset=substr($datasetval, 0, -1);
			if($next!=null){
			$dataset3=substr($datasetval3, 0, -1);
			}
			if($next1!=null){
			$dataset4=substr($datasetval4, 0, -1);
			}
			if($next2!=null){
			$dataset5=substr($datasetval5, 0, -1);
			}
			if($next3!=null){
			$dataset6=substr($datasetval6, 0, -1);
			}
			if($next4!=null){
			$dataset7=substr($datasetval7, 0, -1);
			}
			if($next5!=null){
			$dataset8=substr($datasetval8, 0, -1);
			}
			if($next6!=null){
			$dataset9=substr($datasetval9, 0, -1);
			}
			if($next7!=null){
			$dataset10=substr($datasetval10, 0, -1);
			}
			if($next8!=null){
			$dataset11=substr($datasetval11, 0, -1);
			}
			// echo '<br>'; echo $datasetval;
			// echo '<br>'; echo $dataset;
			// echo '<br>'; echo $dataset3;
			
		if (isset($_POST['YearTo']) AND $_POST['YearTo']==$currentyr){
			$mn=12; $ms=1;  $year=$currentyr;
				
				$sqlthisyr='SELECT IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
				FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
				where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$reset.')';
				$stmt=$link->query($sqlthisyr); $row=$stmt->fetch();
				if($next!=null){
				$sqlthisyrs='SELECT IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
				FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
				where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$next.')';
				$stmts=$link->query($sqlthisyrs); $rows=$stmts->fetch();
				}
				if($next1!=null){
				$sqlthisyrt='SELECT IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
				FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
				where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$next1.')';
				$stmtt=$link->query($sqlthisyrt); $rowt=$stmtt->fetch();
				}
				if($next2!=null){
				$sqlthisyrf='SELECT IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
				FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
				where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$next2.')';
				$stmtf=$link->query($sqlthisyrf); $rowf=$stmtf->fetch();
				}
				if($next3!=null){
				$sqlthisyrfv='SELECT IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
				FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
				where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$next3.')';
				$stmtfv=$link->query($sqlthisyrfv); $rowfv=$stmtfv->fetch();
				}
				if($next4!=null){
				$sqlthisyrsix='SELECT IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
				FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
				where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$next4.')';
				$stmtsix=$link->query($sqlthisyrsix); $rowsix=$stmtsix->fetch();
				}
				if($next5!=null){
				$sqlthisyrseven='SELECT IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
				FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
				where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$next5.')';
				$stmtseven=$link->query($sqlthisyrseven); $rowseven=$stmtseven->fetch();
				}
				if($next6!=null){
				$sqlthisyreth='SELECT IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
				FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
				where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$next6.')';
				$stmteth=$link->query($sqlthisyreth); $roweth=$stmteth->fetch();
				}
				if($next7!=null){
				$sqlthisyrnth='SELECT IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
				FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
				where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$next7.')';
				$stmtnth=$link->query($sqlthisyrnth); $rownth=$stmtnth->fetch();
				}
				if($next8!=null){
				$sqlthisyrtth='SELECT IFNULL(TRUNCATE((Sum(ss.Qty)),2),0) AS Sold
				FROM `'.$year.'_1rtc`.invty_2sale as sm INNER JOIN `'.$year.'_1rtc`.invty_2salesub as ss ON sm.TxnID=ss.TxnID join `'.$year.'_1rtc`.invty_1items i on i.ItemCode=ss.ItemCode JOIN `'.$year.'_1rtc`.invty_1category c ON c.CatNo=i.CatNo
				where txntype in (1,2,5,10) AND ss.ItemCode IN ('.$next8.')';
				$stmttth=$link->query($sqlthisyrtth); $rowtth=$stmttth->fetch();
				}
				
				
				$label=$label.','.$currentyr;
				$dataset=$dataset.",".$row['Sold']; 
				if($next!=null){
				$dataset3=$dataset3.",".$rows['Sold']; 
				}
				if($next1!=null){
				$dataset4=$dataset4.",".$rowt['Sold']; 
				}
				if($next2!=null){
				$dataset5=$dataset5.",".$rowf['Sold']; 
				}
				if($next3!=null){
				$dataset6=$dataset6.",".$rowfv['Sold']; 
				}
				if($next4!=null){
				$dataset7=$dataset7.",".$rowsix['Sold']; 
				}
				if($next5!=null){
				$dataset8=$dataset8.",".$rowseven['Sold']; 
				}
				if($next6!=null){
				$dataset9=$dataset9.",".$roweth['Sold']; 
				}
				if($next7!=null){
				$dataset10=$dataset10.",".$rownth['Sold']; 
				}
				if($next8!=null){
				$dataset11=$dataset11.",".$rowtth['Sold']; 
				}
				
			
		}
		
		
		$sql='SELECT "Per Item Sold" AS ReportTitle, "Year" AS xaxis, "'.$unit.'" AS yaxis, "'.$rowl1['ItemDesc'].'" AS legend1, "'.$rowl2['ItemDesc'].'" AS legend2,"'.$rowl3['ItemDesc'].'" AS legend3,"'.$rowl4['ItemDesc'].'" AS legend4,"'.$rowl5['ItemDesc'].'" AS legend5,"'.$rowl6['ItemDesc'].'" AS legend6,"'.$rowl7['ItemDesc'].'" AS legend7,"'.$rowl8['ItemDesc'].'" AS legend8,"'.$rowl9['ItemDesc'].'" AS legend9,"'.$rowl10['ItemDesc'].'" AS legend10,2 AS GraphID, "'.$label.'" AS Label,"'.$dataset.'" AS DataSet1,'.(($next!=null)?'"'.$dataset3.'" AS DataSet2':'""').','.(($next1!=null)?'"'.$dataset4.'" AS DataSet3':'""').','.(($next2!=null)?'"'.$dataset5.'" AS DataSet4':'""').','.(($next3!=null)?'"'.$dataset6.'" AS DataSet5':'""').','.(($next4!=null)?'"'.$dataset7.'" AS DataSet6':'""').','.(($next5!=null)?'"'.$dataset8.'" AS DataSet7':'""').','.(($next6!=null)?'"'.$dataset9.'" AS DataSet8':'""').','.(($next7!=null)?'"'.$dataset10.'" AS DataSet9':'""').','.(($next8!=null)?'"'.$dataset11.'" AS DataSet10':'""').'';
		// echo $sql; exit();
		
		$stmt=$link->query($sql); $field=$stmt->fetch();
	}
	$lwidth='65%';
	
	break;
	
}

		if(isset($_POST['btnLookUp'])){
			$displaydiv=''; $newdiv=''; $newentry=''; $last=''; $c=1;
			
			if ($field['GraphID']==1){
				include($path.'/acrossyrs/js/reportcharts/vbar.php');
			} else if ($field['GraphID']==2){
				include($path.'/acrossyrs/js/reportcharts/line.php');
			} else if ($field['GraphID']==3){
				include($path.'/acrossyrs/js/reportcharts/hbar.php');
			} else {
				include($path.'/acrossyrs/js/reportcharts/pie.php');
			} 

			echo $displaydiv;
			echo '<script>';
			echo 'window.onload = function() {';
			echo $echo;
			echo '}';	
			echo '</script>';
		}

?>
</div>
