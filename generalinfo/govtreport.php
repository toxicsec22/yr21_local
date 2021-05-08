<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

if (!allowedToOpen(6717,'1rtc')) { echo 'No permission'; exit;}

$showbranches=false; include_once('../switchboard/contents.php');

$txnidname='IDNo';
$w=isset($_GET['w'])?$_GET['w']:'List';
$title=!isset($_REQUEST['f'])?'Pending Govt Report':$_REQUEST['f'];
echo '<br><h3>Government Report</h3><br>';
echo '<form method="get" action="govtreport.php">';
echo '<input type="submit" name="f" value="Pending"/> ';
echo '<input type="submit" name="f" value="Reported Hired"/> ';
echo '<input type="submit" name="f" value="Reported Resigned"/>'.'<br/>';
echo '</form><br/>';

switch ($w){
	
	case 'List':
	    
		$columnnames=array('IDNo','Nickname-FullName','Position','Company','Branch','DateHired','RDateHired');
        if (!allowedToOpen(6717,'1rtc')){  echo 'No permission'; exit;}
        if($title=='Reported Resigned'){
                        $label="Back to Reported Resigned";
			$sql='SELECT e.*,ResignReason,DateResigned,CONCAT(e.Nickname,"-",e.FirstName," ",e.Surname) AS `Nickname-FullName`, c.Company, p.Position, IF(p.deptid NOT IN (2,10),d.Department,Branch) AS Branch FROM `1employees` e LEFT JOIN `1companies` c on e.RCompanyNo=c.CompanyNo JOIN `1_gamit`.`0idinfo` i ON i.IDNo=e.IDNo JOIN `attend_30latestpositionsinclresigned` cp ON cp.IDNo=e.IDNo JOIN `attend_0positions` `p` ON ((`p`.`PositionID` = `cp`.`PositionID`)) 
JOIN 1departments d ON d.deptid=p.deptid JOIN attend_1defaultbranchassign dba ON e.IDNo=dba.IDNo JOIN 1branches b ON b.BranchNo=dba.DefaultBranchAssignNo WHERE e.Resigned=1 AND GovtReport=2 ORDER BY RDateHired DESC';
			 array_push($columnnames,'DateResigned','ResignReason');
			 $case='Reset';
                         goto skipsql;
		} elseif ($title=='Reported Hired'){
			  $label="Set as Reported Resigned";
			$condi = ' WHERE GovtReport=1 AND Resigned=0';
			$case='SetAsReportedResigned';
		} else {
			$label="Set as Reported Hired";
			$condi = ' WHERE Resigned=0 AND GovtReport=0';
			$case='SetAsReportedHired';
		}
		
        
        $sql='SELECT e.*,CONCAT(e.Nickname,"-",e.FirstName," ",e.Surname) AS `Nickname-FullName`,DATEDIFF(CURDATE(),e.DateHired) AS Days, i.DateResigned, c.Company, cp.Position, IF(cp.deptid NOT IN (2,10),cp.Department,Branch) AS Branch FROM `1employees` e LEFT JOIN `1companies` c on e.RCompanyNo=c.CompanyNo JOIN `1_gamit`.`0idinfo` i ON i.IDNo=e.IDNo JOIN attend_30currentpositions cp ON cp.IDNo=e.IDNo '.$condi.' ORDER BY RDateHired DESC';
 
        skipsql:
	$editprocess='govtreport.php?w='.$case.'&f='.$title.'&IDNo=';$editprocesslabel=$label;         
        $width='100%';
		 include('../backendphp/layout/displayastable.php');
		 
	break;
	
	
	case 'SetAsReportedHired':
		if (allowedToOpen(6717,'1rtc')){
		$sql='UPDATE `1employees` SET EncodedByNo='.$_SESSION['(ak0)'].', RDateHired=DateHired, Timestamp=Now() WHERE IDNo='.intval($_GET['IDNo']);
		$stmt=$link->prepare($sql); $stmt->execute();
		
		$sql='UPDATE `1_gamit`.`0idinfo` SET EncodedByNo='.$_SESSION['(ak0)'].', GovtReport=1, Timestamp=Now() WHERE IDNo='.intval($_GET['IDNo']);
		echo $sql;
		$stmt=$link->prepare($sql); $stmt->execute();
		
		header("Location:govtreport.php?f=".$title);
		} else {
		echo 'No permission'; exit;
		}
    break;
	
	case 'SetAsReportedResigned':
		if (allowedToOpen(6717,'1rtc')){
		
		$sql='UPDATE `1_gamit`.`0idinfo` SET EncodedByNo='.$_SESSION['(ak0)'].', GovtReport=2, Timestamp=Now() WHERE IDNo='.intval($_GET['IDNo']) .' AND `Resigned?`=1';
		$stmt=$link->prepare($sql); $stmt->execute();
		
		header("Location:govtreport.php?f=".$title);
		} else {
		echo 'No permission'; exit;
		}
    break;
	
	case 'Reset':
		if (allowedToOpen(6717,'1rtc')){
		$sql='UPDATE `1_gamit`.`0idinfo` SET EncodedByNo='.$_SESSION['(ak0)'].', GovtReport=1, Timestamp=Now() WHERE IDNo='.intval($_GET['IDNo']);
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:govtreport.php?f=".$title);
		} else {
		echo 'No permission'; exit;
		}
    break;
   
}

$link=null; $stmt=null;

?>
