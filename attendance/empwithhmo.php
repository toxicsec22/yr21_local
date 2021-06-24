<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

if (!allowedToOpen(6713,'1rtc')) { echo 'No permission'; exit;}

$showbranches=false; include_once('../switchboard/contents.php');

$txnidname='IDNo';
$w=isset($_GET['w'])?$_GET['w']:'List';
$title=!isset($_REQUEST['f'])?'Employees with HMO':$_REQUEST['f'];
echo '<br><form method="get" action="empwithhmo.php">';
echo '<input type="submit" name="f" value="Employees with HMO"/> ';
echo '<input type="submit" name="f" value="Regular Employees without HMO"/> ';
echo '<input type="submit" name="f" value="Resigned Employees with HMO"/>'.'<br/>';
echo '</form><br/>';

$formdesc='</i><div style="background-color: #e6e6e6;
  width: 70%;
  border: 2px solid grey;
  padding: 10px;
  margin: 15px;"><br>This page is to ensure that our list coincides with the list of the HMO provider.<br><br><b>Edit AFTER informing HMO provider and they have confirmed.</b> &nbsp; Check accuracy of the list of the HMO provider <i><b>monthly</b></i>.<br><br>If tenure is between 6 months and 1 year, the ABL (Annual Benefit Limit) is P40,000. All employees beyond 1 year has ABL of P80,000.</div><i>';

switch ($w){
	
	case 'List':
	    
		$columnnames=array('IDNo','Nickname-FullName','Gender','Age','EmploymentStatus','InYrs','Position','Company','Branch','DateHired');
        if (!allowedToOpen(6713,'1rtc')){  echo 'No permission'; exit;}
        if($title=='Resigned Employees with HMO'){
                        $sethmo="Remove HMO";
                        $from='';
			$sql='SELECT e.*,(CASE
	WHEN EmpStatus=0 THEN "Probationary"
	WHEN EmpStatus=1 THEN "Regular"
	WHEN EmpStatus=2 THEN "Resigned With Clearance"
	ELSE "Resigned No Clearance"
 END) AS `EmploymentStatus`,TIMESTAMPDIFF (YEAR, e.Birthdate, CURDATE()) AS Age,CONCAT(e.Nickname,"-",e.FirstName," ",e.Surname) AS `Nickname-FullName`, IF(Gender=0,"F","M") AS Gender,DATEDIFF(CURDATE(),e.DateHired) AS Days,(TO_DAYS(CURRENT_TIMESTAMP()) - TO_DAYS(`e`.`DateHired`)) / 365 AS `InYears`,IF((SELECT InYears)<1,CONCAT("<font style=\"background-color:maroon;color:white;\">",(SELECT InYears),"</font>"),(SELECT InYears)) AS InYrs, i.DateResigned, c.Company, WithHMO, p.Position, IF(p.deptid<>10,d.Department,Branch) AS Branch FROM `1employees` e LEFT JOIN `1companies` c on e.RCompanyNo=c.CompanyNo JOIN `1_gamit`.`0idinfo` i ON i.IDNo=e.IDNo JOIN `attend_30latestpositionsinclresigned` cp ON cp.IDNo=e.IDNo JOIN `attend_1positions` `p` ON ((`p`.`PositionID` = `cp`.`PositionID`)) 
JOIN 1departments d ON d.deptid=p.deptid JOIN attend_1defaultbranchassign dba ON e.IDNo=dba.IDNo JOIN 1branches b ON b.BranchNo=dba.DefaultBranchAssignNo WHERE e.Resigned=1 AND WithHMO<>0';
			 array_push($columnnames,'DateResigned');
                         goto skipsql;
		} elseif ($title=='Regular Employees without HMO'){
			  $sethmo="Set HMO";
			$condi = ' WHERE e.IDNo NOT IN (1001,1002) AND WithHMO=0 AND Resigned=0 HAVING Days>182';
		} else {
			$sethmo="Remove HMO";
			$condi = ' WHERE Resigned=0 AND WithHMO<>0';
		}
		
        
        $sql='SELECT e.*,TIMESTAMPDIFF (YEAR, e.Birthdate, CURDATE()) AS Age,(CASE
	WHEN EmpStatus=0 THEN "Probationary"
	WHEN EmpStatus=1 THEN "Regular"
	WHEN EmpStatus=2 THEN "Resigned With Clearance"
	ELSE "Resigned No Clearance"
 END) AS `EmploymentStatus`,CONCAT(e.Nickname,"-",e.FirstName," ",e.Surname) AS `Nickname-FullName`, IF(Gender=0,"F","M") AS Gender,DATEDIFF(CURDATE(),e.DateHired) AS Days,(TO_DAYS(CURRENT_TIMESTAMP()) - TO_DAYS(`e`.`DateHired`)) / 365 AS `InYears`, IF((SELECT InYears)<1,CONCAT("<font style=\"background-color:maroon;color:white;\">",(SELECT InYears),"</font>"),(SELECT InYears)) AS InYrs, i.DateResigned, c.Company, WithHMO, cp.Position, IF(cp.deptid<>10,cp.Department,Branch) AS Branch FROM `1employees` e LEFT JOIN `1companies` c on e.RCompanyNo=c.CompanyNo JOIN `1_gamit`.`0idinfo` i ON i.IDNo=e.IDNo JOIN attend_30currentpositions cp ON cp.IDNo=e.IDNo '.$condi;
 // echo $sql;
        skipsql:
	$editprocess='empwithhmo.php?w=Edit&f='.$title.'&IDNo=';$editprocesslabel=$sethmo;         
        $width='100%';
		 include('../backendphp/layout/displayastable.php');
		 
	break;
	
	case 'EditSpecifics':
        if (allowedToOpen(6713,'1rtc')) {
		$sql='SELECT IDNo,WithHMO,WithHMO, CONCAT(Nickname," ",Surname) AS `Nickname-FullName` FROM 1employees';
		$columnnameslist=array('IDNo','Nickname-FullName','WithHMO');
		$columnstoadd=array('WithHMO');
		$title='Edit Specifics';
		$txnid=intval($_GET['IDNo']);

		$sql=$sql.' WHERE IDNo='.$txnid;
		$columnstoedit=$columnstoadd;
		
		$columnnames=$columnnameslist;
		
		$editprocess='empwithhmo.php?w=Edit&TxnID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	} else {
		echo 'No permission'; exit;
		}
	break;
	
	case 'Edit':
		
		if (allowedToOpen(6713,'1rtc')){
                    include_once '../generalinfo/trailgeninfo.php';
                recordtrail(intval($_GET['IDNo']),'1employees',$link,0);
		$sql='UPDATE `1employees` SET EncodedByNo='.$_SESSION['(ak0)'].', WithHMO=IF(WithHMO=1,0,1), Timestamp=Now() WHERE IDNo='.intval($_GET['IDNo']);
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:empwithhmo.php?f=".$title);
		} else {
		echo 'No permission'; exit;
		}
    break;
   
}

$link=null; $stmt=null;

?>
