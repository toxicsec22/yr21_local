<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php'; 
if (!allowedToOpen(71313,'1rtc')) { echo 'No Permission'; exit(); };
$showbranches=false;

	include_once('../switchboard/contents.php');
	include_once('../backendphp/layout/linkstyle.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
echo '<br>';
echo '<a id=\'link\' href="itemusage.php?w=ItemUsageTypePerYear">Item Usage Type - Per Year</a> ';
echo '<a id=\'link\' href="itemusage.php?w=ItemUsageTypePerBranch">Item Usage Type - Per Branch</a> ';
?>

	<?php include($path.'/acrossyrs/js/reportcharts/includejscharts.php'); ?>
	<?php include_once($path.'/acrossyrs/js/reportcharts/mgraphlabel.php'); ?>


<br><div id="section" style="display: block;">


<?php



echo '<br>';
					
$echo='';

$graphtitle='FullName';
$which=isset($_GET['w'])?$_GET['w']:'ItemUsageTypePerYear';

$sql0='CREATE TEMPORARY TABLE `graphreporthistincus` (
	`ReportID` tinyint(4) NOT NULL AUTO_INCREMENT,
	`ReportTitle` varchar(100) DEFAULT NULL,
	`OtherDesc` varchar(20) NOT NULL,
	`OnSB` tinyint(1) NOT NULL,
	`Label` varchar(3000) NOT NULL,
	`xaxis` varchar(25) NOT NULL,
	`yaxis` varchar(25) NOT NULL,
	`legend1` varchar(25) NOT NULL,
	`legend2` varchar(25) NOT NULL,
	`legend3` varchar(25) NOT NULL,
	`legend4` varchar(25) NOT NULL,
	`legend5` varchar(25) NOT NULL,
	`legend6` varchar(25) NOT NULL,
	`legend7` varchar(25) NOT NULL,
	`legend8` varchar(25) NOT NULL,
	`legend9` varchar(25) NOT NULL,
	`legend10` varchar(25) NOT NULL,
	`EncodedByNo` smallint(6) DEFAULT NULL,
	`Timestamp` timestamp NULL DEFAULT NULL,
	PRIMARY KEY (`ReportID`)
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;';
  $stmt=$link->prepare($sql0);$stmt->execute();
  
  
  $sql0='CREATE TEMPORARY TABLE `graphboardhistincus` (
	`TxnID` smallint(6) NOT NULL AUTO_INCREMENT,
	`IDNo` smallint(6) DEFAULT NULL,
	`AllowedToView` varchar(100) DEFAULT NULL,
	`GraphID` tinyint(2) NOT NULL,
	  `DataSet2` varchar(2100) DEFAULT NULL,
	  `DataSet1` varchar(2100) DEFAULT NULL,
	  `DataSet3` varchar(2100) DEFAULT NULL,
	  `DataSet4` varchar(2100) DEFAULT NULL,
	  `DataSet5` varchar(2100) DEFAULT NULL,
	  `DataSet6` varchar(2100) DEFAULT NULL,
	  `DataSet7` varchar(2100) DEFAULT NULL,
	  `DataSet8` varchar(2100) DEFAULT NULL,
	  `DataSet9` varchar(2100) DEFAULT NULL,
	  `DataSet10` varchar(2100) DEFAULT NULL,
	`ReportID` tinyint(4) NOT NULL,
	`EncodedByNo` smallint(6) DEFAULT NULL,
	`Timestamp` timestamp NULL DEFAULT NULL,
	PRIMARY KEY (`TxnID`),
	UNIQUE KEY `IDNo` (`IDNo`,`GraphID`,`ReportID`)
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;';
  $stmt=$link->prepare($sql0);$stmt->execute();

if($which=='ItemUsageTypePerYear' OR $which=='ItemUsageTypePerBranch'){

	$areaselect='';
	if($which=='ItemUsageTypePerBranch'){
	$sqlarea = 'SELECT * FROM 0area';
	$stmtarea=$link->query($sqlarea); $resareas=$stmtarea->fetchall();
	$areaselect=' Area: <select name="AreaNo"><option value=-1>All Areas</option>';
		foreach($resareas AS $resarea){
			$areaselect.='<option value="'.$resarea['AreaNo'].'" '.((isset($_POST['AreaNo']) AND $_POST['AreaNo']==$resarea['AreaNo'])?'selected':'').'>'.$resarea['Area'].'</option>';
		}

	$areaselect.='</select>';
	

	$lookupyr=(isset($_POST['Year'])?$_POST['Year']:date('Y'));
	echo '<br><form action="#" method="POST">Year: <input type="text" name="Year" size=5 value="'.$lookupyr.'"> '.$areaselect.' <input type="submit" name="btnLookup" value="Lookup"></form><br>';
	} else {

		$sqlbranches = 'SELECT BranchNo,Branch FROM 1branches WHERE Active=1 AND Pseudobranch=0 ORDER BY Branch';
	$stmtbranches=$link->query($sqlbranches); $resbranches=$stmtbranches->fetchall();
	$branchselect=' Branch: <select name="BranchNo"><option value=-1>All Branches</option>';
		foreach($resbranches AS $resbranch){
			$branchselect.='<option value="'.$resbranch['BranchNo'].'" '.((isset($_POST['BranchNo']) AND $_POST['BranchNo']==$resbranch['BranchNo'])?'selected':'').'>'.$resbranch['Branch'].'</option>';
		}

	$branchselect.='</select>';
	
	$yrfrom=(isset($_POST['YearFrom'])?$_POST['YearFrom']:date('Y'));
	$yrto=(isset($_POST['YearTo'])?$_POST['YearTo']:date('Y'));
	echo '<br><form action="#" method="POST">YearFrom: <input type="text" name="YearFrom" size=5 value="'.	$yrfrom.'"> YearTo: <input type="text" name="YearTo" size=5 value="'.	$yrto.'"> '.$branchselect.' <input type="submit" name="btnLookup" value="Lookup"></form><br>';
	}
}
switch ($which){


case 'ItemUsageTypePerYear':
	$title='Item Usage Type - Per Year (in %)';
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';

	while($yrfrom<=$yrto){
		$branchcon='';
		if(isset($_POST['BranchNo']) AND $_POST['BranchNo']<>-1){
			$branchcon=' AND BranchNo='.$_POST['BranchNo'];
			$graphmtitle=comboBoxValue($link,'1branches','BranchNo',$_POST['BranchNo'],'Branch');
		} else {
			$graphmtitle='Year';
		}
		if($yrfrom>2020){ //static dapat kasi ibang table sa <yr21
			$sql1='CREATE TEMPORARY TABLE itemtype'.$yrfrom.' AS SELECT MonthNo, TRUNCATE(SUM(Auto)/(SUM(Auto+Aircon+Ref+Multi))*100,2) AS `Auto`, 
			TRUNCATE(SUM(Aircon)/(SUM(Auto+Aircon+Ref+Multi))*100,2) AS `Aircon`, TRUNCATE(SUM(Ref)/(SUM(Auto+Aircon+Ref+Multi))*100,2) AS `Ref`, TRUNCATE(SUM(Multi)/(SUM(Auto+Aircon+Ref+Multi))*100,2) AS `Multi`
			FROM '.$yrfrom.'_1rtc.acctg_6targetscores WHERE MonthNo>0 '.$branchcon.' GROUP BY MonthNo';
		  $stmt=$link->prepare($sql1);$stmt->execute();
		} else {
			$sql1='CREATE TEMPORARY TABLE itemtype'.$yrfrom.' AS SELECT MonthNo, BranchNo, Multi, Auto, 
			Aircon, Ref 
			FROM '.$yrfrom.'_1rtc.invty_1itemusageinpercent WHERE MonthNo>0 '.$branchcon.' GROUP BY MonthNo';
			  $stmt=$link->prepare($sql1);$stmt->execute();
		}
		//loop here
	
		$label="'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'";
		$sql1='INSERT INTO `graphreporthistincus` SET
		  `ReportID`='.($yrfrom-2000).',
		  `ReportTitle`="'.$graphmtitle.' '.$yrfrom.'",
		  `OtherDesc`="",
		  `legend4`="Multi",
		  `legend3`="Auto",
		  `legend2`="Ref",
		  `legend1`="Aircon",
		  `Label`="'.$label.'",
		  `xaxis`="Month",
		  `yaxis`="In Percent",
		  `OnSB`=0;';
		  $stmt=$link->prepare($sql1);$stmt->execute();
		 
		
		$bwidth='45%';$lwidth='31%';
		
		$mn=12; $ms=1; $aircon=''; $multi=''; $ref=''; $auto='';
		while($ms<=$mn){
			$sql='SELECT Aircon,Multi,Ref,`Auto` FROM itemtype'.$yrfrom.' WHERE MonthNo='.$ms.';';
			$stmt=$link->query($sql); $res=$stmt->fetch();
			if(!isset($res['Aircon']) OR $res['Aircon']==''){
				$res['Aircon']=0;
			}
			if(!isset($res['Multi']) OR $res['Multi']==''){
				$res['Multi']=0;
			}
			if(!isset($res['Ref']) OR $res['Ref']==''){
				$res['Ref']=0;
			}
			if(!isset($res['Auto']) OR $res['Auto']==''){
				$res['Auto']=0;
			}
		
			$aircon.=$res['Aircon'].",";
			$multi.=$res['Multi'].",";
			$ref.=$res['Ref'].",";
			$auto.=$res['Auto'].",";
			$ms++;
		}
		
		$aircon=substr($aircon, 0, -1);
		$multi=substr($multi, 0, -1);
		$ref=substr($ref, 0, -1);
		$auto=substr($auto, 0, -1);
		
		
		$sql1='INSERT INTO `graphboardhistincus` SET `AllowedToView`="100,99,31",
		  `GraphID`=2, 
		  `DataSet4`="'.$multi.'",
			`DataSet3`="'.$auto.'",
			`DataSet2`="'.$ref.'",
			`DataSet1`="'.$aircon.'",
		  `ReportID`='.($yrfrom-2000).';
		';
		$stmt=$link->prepare($sql1);$stmt->execute();


		$yrfrom++;

	}

	
	

	$c=1;
	$sql = 'SELECT g.*,"" AS addlabel,"" AS FullName, gr.* FROM graphboardhistincus g JOIN graphreporthistincus gr ON g.ReportID=gr.ReportID';
	
	$stmt=$link->query($sql); $res=$stmt->fetchall();

	break;


	case 'ItemUsageTypePerBranch':

		$title='Item Usage Type Per - Branch (in %)';
		echo '<title>'.$title.'</title>';
		echo '<h3>'.$title.'</h3>';
		$in='';
		if(isset($_POST['AreaNo']) AND $_POST['AreaNo']<>-1){
			$sqlin='SELECT GROUP_CONCAT(BranchNo) AS Branches FROM 1branches WHERE AreaNo='.$_POST['AreaNo'];
			$stmtin=$link->query($sqlin); $resbranch=$stmtin->fetch();
			$in='WHERE BranchNo IN ('.$resbranch['Branches'].') AND MonthNo>0 ';
		}

		if($lookupyr>=2021){ //Static dapat kasi ibang table kapag <2021
			$sql1='CREATE TEMPORARY TABLE itemtype AS SELECT BranchNo,MonthNo, TRUNCATE(SUM(Auto)/(SUM(Auto+Aircon+Ref+Multi))*100,2) AS `Auto`, 
			TRUNCATE(SUM(Aircon)/(SUM(Auto+Aircon+Ref+Multi))*100,2) AS `Aircon`, TRUNCATE(SUM(Ref)/(SUM(Auto+Aircon+Ref+Multi))*100,2) AS `Ref`, TRUNCATE(SUM(Multi)/(SUM(Auto+Aircon+Ref+Multi))*100,2) AS `Multi`
			FROM '.$lookupyr.'_1rtc.acctg_6targetscores '.$in.' GROUP BY BranchNo,MonthNo';
		  $stmt=$link->prepare($sql1);$stmt->execute();
		} else {
			$sql1='CREATE TEMPORARY TABLE itemtype AS SELECT MonthNo, BranchNo, Multi, Auto, Aircon,  Ref FROM '.$lookupyr.'_1rtc.invty_1itemusageinpercent '.$in.' GROUP BY BranchNo,MonthNo';
			  $stmt=$link->prepare($sql1);$stmt->execute();
		}
	
		$sqlallbranches='SELECT it.BranchNo,Branch FROM itemtype it JOIN 1branches b ON it.BranchNo=b.BranchNo GROUP BY it.BranchNo ORDER BY Branch';
		$stmtallbranches=$link->query($sqlallbranches); $resall=$stmtallbranches->fetchAll();

	foreach($resall AS $resa){
		$label="'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'";
		$sql1='INSERT INTO `graphreporthistincus` SET
		  `ReportID`='.$resa['BranchNo'].',
		  `ReportTitle`="'.$resa['Branch'].' '.$lookupyr.'",
		  `OtherDesc`="",
		  `legend4`="Multi",
		  `legend3`="Auto",
		  `legend2`="Ref",
		  `legend1`="Aircon",
		  `Label`="'.$label.'",
		  `xaxis`="Month",
		  `yaxis`="In Percent",
		  `OnSB`=0;';
		  $stmt=$link->prepare($sql1);$stmt->execute();
		 
		
		$bwidth='45%';$lwidth='30%';
		
		$mn=12; $ms=1; $aircon=''; $multi=''; $ref=''; $auto='';
		while($ms<=$mn){
			$sql='SELECT Aircon,Multi,Ref,`Auto` FROM itemtype WHERE MonthNo='.$ms.' AND BranchNo='.$resa['BranchNo'].';';
			$stmt=$link->query($sql); $res=$stmt->fetch();
			if(!isset($res['Aircon']) OR $res['Aircon']==''){
				$res['Aircon']=0;
			}
			if(!isset($res['Multi']) OR $res['Multi']==''){
				$res['Multi']=0;
			}
			if(!isset($res['Ref']) OR $res['Ref']==''){
				$res['Ref']=0;
			}
			if(!isset($res['Auto']) OR $res['Auto']==''){
				$res['Auto']=0;
			}
		
			$aircon.=$res['Aircon'].",";
			$multi.=$res['Multi'].",";
			$ref.=$res['Ref'].",";
			$auto.=$res['Auto'].",";
			$ms++;
		}
		
		$aircon=substr($aircon, 0, -1);
		$multi=substr($multi, 0, -1);
		$ref=substr($ref, 0, -1);
		$auto=substr($auto, 0, -1);
		
		
		$sql1='INSERT INTO `graphboardhistincus` SET `AllowedToView`="100,99,31",
		  `GraphID`=2, 
		  `DataSet4`="'.$multi.'",
			`DataSet3`="'.$auto.'",
			`DataSet2`="'.$ref.'",
			`DataSet1`="'.$aircon.'",
		  `ReportID`='.$resa['BranchNo'].';
		';
		$stmt=$link->prepare($sql1);$stmt->execute();
	
	}
		$c=1;
		$sql = 'SELECT g.*,"" AS addlabel,"" AS FullName, gr.* FROM graphboardhistincus g JOIN graphreporthistincus gr ON g.ReportID=gr.ReportID';

		$stmt=$link->query($sql); $res=$stmt->fetchall();
	
	break;
	

}


$c=1;

$displaydiv=''; $newdiv=''; $newentry=''; $last='';
foreach ($res as $field) {
	
	
	if ($newentry==$last){
		$newdiv='';
	} else {
		$newdiv='<div style="clear: both; display: block; position: relative;height:30px;"></div>';
	}
	
	
	if ($field['GraphID']==1){
		include($path.'/acrossyrs/js/reportcharts/vbar.php');
	} else if ($field['GraphID']==2){
		include($path.'/acrossyrs/js/reportcharts/line.php');
	} else if ($field['GraphID']==3){
		include($path.'/acrossyrs/js/reportcharts/hbar.php');
	} else {
		include($path.'/acrossyrs/js/reportcharts/pie.php');
	}
	
	$last=$newentry;
	
$c++;	 
} 
	echo $displaydiv;
	
	echo '<script>';
	echo 'window.onload = function() {';
	echo $echo;
	echo '}';	
	echo '</script>';

?>

