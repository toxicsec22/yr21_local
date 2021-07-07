<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6150,'1rtc') and !allowedToOpen(6151,'1rtc')) { echo 'No permission'; exit;}
 
 
//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="F5EBCC";
        $rcolor[1]="FFFFFF";
$showbranches=false;
include_once('../switchboard/contents.php');
include_once "../generalinfo/lists.inc"; include_once('../backendphp/functions/getnumber.php');

$whichqry=!isset($_GET['w'])?'AveMonthly':$_GET['w'];

switch ($whichqry){
    case 'AveMonthly':
        $title='Average Sales Per Person Per Branch'; 
        $columnnames=array('Branch', 'EmployeeCount', 'Average<br/>Monthly Sales', 'Average Sales<br/>Per Person<br/>Per Branch','Age of Branch','CalculatedClass', 'ClassLastYr');
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' (AverageMonthlySales/EmployeeCount) '); $columnsub=$columnnames;
        goto classreport;
    case 'ShowClass':
        if (allowedToOpen(730,'1rtc')) {   
            include_once('../backendphp/layout/linkstyle.php');
            echo '<br><a id="link" target="_blank" href="../invty/lookupgeninv.php?w=ClassPerQuarter">Class Per Quarter</a><br>';
        }
        $title='Branch Classification'; 
        $columnnames=array('Branch','AverageDailyNoSundays','Average<br/>Monthly Sales', 'Age of Branch','CalculatedClass', 'ClassLastYr');
		$columnnames1=array('Branch', 'AverageDailyNoSundaysValue','AverageMonthlySales', 'Age of Branch','CalculatedClass', 'ClassLastYr');
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' (AverageMonthlySales/EmployeeCount) '); $columnsub=$columnnames1;
        goto classreport;
    classreport:
    $sql0='DROP TEMPORARY TABLE IF EXISTS monthlysales;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
    $sql0='CREATE TEMPORARY TABLE monthlysales AS
SELECT 
        MONTH(`Date`) AS `Month`, s.BranchNo,
        SUM(`Amount`) AS `MonthlySales`
    FROM
        `acctg_61unisalereturn` s JOIN `1branches` b ON b.BranchNo=s.BranchNo
    WHERE
        b.Pseudobranch=0
    GROUP BY MONTH(`Date`), s.BranchNo;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
			
	 
    
    $sql0='DROP TEMPORARY TABLE IF EXISTS monthlysalesper;'; $stmt0=$link->prepare($sql0); $stmt0->execute();

    $sql0='CREATE TEMPORARY TABLE monthlysalesper AS
    SELECT ms.BranchNo, (SELECT COUNT(IDNo) FROM `attend_30currentpositions` WHERE BranchNo=ms.BranchNo) AS EmployeeCount, 
    (SELECT ClassID FROM 0branchclass WHERE AVG(`MonthlySales`)>=CutOffMin ORDER BY CutOffMin DESC LIMIT 1) AS `CalculatedClassID`,
    TRUNCATE(AVG(`MonthlySales`),0) AS AverageMonthlySales, 1 AS `DailyAverage` FROM monthlysales ms
    GROUP BY ms.BranchNo;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
	
		//temporary 1
	 $sqlc='CREATE TEMPORARY TABLE totaldaily 
(
`BranchNo` smallint(6) unsigned NOT NULL,
  `Date` date NOT NULL, 
  DailySales double DEFAULT 0
)
 SELECT BranchNo, `Date`,(SUM(Amount)) AS DailySales FROM acctg_61unisalereturn s1 WHERE WEEKDAY(`Date`)<>1 GROUP BY BranchNo, `Date`;';
	 $stmtc=$link->prepare($sqlc); $stmtc->execute();
		//temporary 2
		$sqlc2='Create temporary table dailytotal as SELECT BranchNo, TRUNCATE(AVG(DailySales),0) AS AverageDailySales FROM totaldaily GROUP BY BranchNo;';
		$stmtc2=$link->prepare($sqlc2); $stmtc2->execute();
	
		//update
			$sqlu='UPDATE monthlysalesper m join dailytotal dt on dt.BranchNo=m.BranchNo set DailyAverage=AverageDailySales';
			$stmtu=$link->prepare($sqlu); $stmtu->execute();
    
    $sql1='SELECT ClassID, Class, CutOffMin, IFNULL(COUNT(BranchNo),0) AS CountofBranchesThisYr, 
            (SELECT COUNT(BranchNo) FROM 1branches WHERE Pseudobranch=0 AND Active=1 AND ClassLastYr=ClassID GROUP BY ClassLastYr)  AS CountofBranchesLastYr 
            FROM `0branchclass` bc LEFT JOIN monthlysalesper m ON bc.ClassID=m.CalculatedClassID  GROUP BY ClassID ORDER BY ClassID DESC';
    $stmt1=$link->query($sql1); $res1=$stmt1->fetchAll();
    
    
    
    $formdesc='</i><br/><br/><br/><table style="border: black 1px collapsed;"><tr><th>Class</th><th>Minimum Monthly Sales</th>'
            . '<th>CountofBranchesLastYr</th><th>CountofBranchesThisYr</th></tr>';
    
    foreach ($res1 as $class){
        $formdesc.='<tr><td>'.$class['Class'].'</td><td>'.number_format($class['CutOffMin'],0).'</td><td>'.$class['CountofBranchesLastYr'].'</td><td>'.$class['CountofBranchesThisYr'].'</td></tr>';}
                
    $formdesc.='</table><br><br>';
    $sql='SELECT ms.*, Branch, DailyAverage as AverageDailyNoSundaysValue,FORMAT(DailyAverage,0) AS `AverageDailyNoSundays`,FORMAT(AverageMonthlySales,0) AS `Average<br/>Monthly Sales`, AverageMonthlySales,TRUNCATE(((TO_DAYS(NOW()) - TO_DAYS(`b`.`Anniversary`)) / 365.25),1) AS `Age of Branch` , FORMAT((AverageMonthlySales/EmployeeCount),0) AS `Average Sales<br/>Per Person<br/>Per Branch`, '
            . '(CASE WHEN CalculatedClassID=4 THEN "Mature" WHEN CalculatedClassID=3 THEN "Prime" WHEN CalculatedClassID=2 THEN "Growth" ELSE "Seed" END)  AS `CalculatedClass`, '
            . '(CASE WHEN ClassLastYr=4 THEN "Mature" WHEN ClassLastYr=3 THEN "Prime" WHEN ClassLastYr=2 THEN "Growth" ELSE "Seed" END) AS `ClassLastYr` '
            . 'FROM monthlysalesper ms JOIN `1branches` b ON b.BranchNo=ms.BranchNo ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' DESC');
			// echo $sql; exit();
    
    
    include_once('../backendphp/layout/displayastablenosort.php');
	
	unset($columnnamesm,$formdesc,$sortfield,$title);
	$title='';
	$formdesc='Totals';
	$hidecount=true;
	 $sql='SELECT format(sum(DailyAverage),0) as `AverageDailyNoSundaysValue`,format(sum(AverageMonthlySales),0) as `AverageMonthlySales`  FROM monthlysalesper';
	$columnnames=array('AverageDailyNoSundaysValue','AverageMonthlySales');
	include('../backendphp/layout/displayastablenosort.php');
	
	
        break;
}
 