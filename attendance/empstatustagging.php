<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

if (!allowedToOpen(array(6711,6714,6715),'1rtc')) { echo 'No permission'; exit;}
$showbranches=false; include_once('../switchboard/contents.php');

$txnidname='IDNo';
$w=isset($_GET['w'])?$_GET['w']:'List';
$title=!isset($_REQUEST['f'])?'Probationary':$_REQUEST['f'];
include('../generalinfo/employeefilterlinks.php');
/* if (allowedToOpen(6711,'1rtc')){
            $show=!isset($_POST['show'])?'Show Current':$_POST['show'];
	    $formdesc='<br><form action="#" method="post"><input type=submit name="show" value="Show Current">&nbsp; &nbsp;<input type=submit name="show" value="Show Resigned This Year">&nbsp; &nbsp;<input type=submit name="show" value="Show Resigned But With System Access">&nbsp; &nbsp;<input type=submit name="show" value="Show Not Resigned And No System Access">&nbsp; &nbsp;<input type=submit name="show" value="Show Prelim Resign">
            </form>';
		}
	if (allowedToOpen(6714,'1rtc')){
echo '<br><form method="get" action="empstatustagging.php">';
echo '<input type="submit" name="f" value="Probationary"/> ';
echo '<input type="submit" name="f" value="Regular"/> ';
echo '<input type="submit" name="f" value="Resigned with Clearance"/> ';
echo '<input type="submit" name="f" value="Resigned No Clearance"/>'.'<br/>';
echo '</form><br/>';
	} */
$formdesc='';

switch ($w){
	
	case 'List':
		if (!allowedToOpen(array(6711,6714),'1rtc')){  echo 'No permission'; exit;}
		$columnnames=array('IDNo','Nickname-FullName','Position','Branch/Dept','DateHired');
		$switchaction='';
        if($title=='Resigned with Clearance'){
			 $condi = ' AND EmpStatus=2 ';
		} elseif ($title=='Resigned No Clearance'){
			$condi = ' AND EmpStatus=3 ';
		} elseif ($title=='Regular'){
			$tagemp="Probationary";
			$condi = ' WHERE e.IDNo NOT IN (1001,1002) AND EmpStatus=1 AND Resigned<>1 ORDER BY DateHired';
		} else {
			$tagemp="Regular";
			$condi = ' WHERE EmpStatus=0 AND e.Resigned<>1 ORDER BY DateHired';
			$switchaction=',IF((SELECT COUNT(TxnID) FROM hr_2personnelaction WHERE IDNo=e.IDNo AND ActionID=0)>0,1,0) AS showeditprocess ';
		}
		if (in_array($title,array('Resigned with Clearance','Resigned No Clearance'))){
		$sql='SELECT e.*,ResignReason,CONCAT(e.Nickname,"-",e.FirstName," ",e.Surname) AS `Nickname-FullName`, IF(Gender=0,"F","M") AS Gender,DATEDIFF(CURDATE(),e.DateHired) AS Yrs, i.DateResigned, c.Company, p.Position, IF(p.deptid IN (1,2,3,10),Branch,Department) AS `Branch/Dept` FROM `1employees` e LEFT JOIN `1companies` c on e.RCompanyNo=c.CompanyNo JOIN `1_gamit`.`0idinfo` i ON i.IDNo=e.IDNo JOIN `attend_30latestpositionsinclresigned` cp ON cp.IDNo=e.IDNo JOIN `attend_0positions` `p` ON ((`p`.`PositionID` = `cp`.`PositionID`)) 
		JOIN 1departments d ON d.deptid=p.deptid JOIN attend_1defaultbranchassign dba ON cp.IDNo=dba.IDNo JOIN 1branches b ON dba.DefaultBranchAssignNo=b.BranchNo WHERE e.IDNo NOT IN (1001,1002) AND e.Resigned=1 '.$condi.' ORDER BY DateResigned DESC';
	
			 array_push($columnnames,'DateResigned','ResignReason');
			 $width='100%';
                         goto skipsql;
		}
		if (in_array($title,array('Probationary','Regular'))){
			if (allowedToOpen(6715,'1rtc')){
			$editprocess='empstatustagging.php?w=Edit'.($title=='Regular'?'&Confirmation=1':'').'&f='.$title.'&IDNo=';
			$editprocesslabel='Tag as '.$tagemp;
			}
		}
		$width='70%';
		
        
        $sql='SELECT e.*,CONCAT(e.Nickname,"-",e.FirstName," ",e.Surname) AS `Nickname-FullName`, IF(Gender=0,"F","M") AS Gender,DATEDIFF(CURDATE(),e.DateHired) AS Yrs, i.DateResigned, c.Company, WithHMO, cp.Position, IF(cp.deptid NOT IN (1,2,3,10),cp.Department,Branch) AS `Branch/Dept`'.$switchaction.' FROM `1employees` e LEFT JOIN `1companies` c on e.RCompanyNo=c.CompanyNo JOIN `1_gamit`.`0idinfo` i ON i.IDNo=e.IDNo JOIN attend_30currentpositions cp ON cp.IDNo=e.IDNo '.$condi;
     
	 skipsql:
		
	  
        
		 include('../backendphp/layout/displayastable.php');
		 
	break;
	
	
	case 'Edit':
		if (allowedToOpen(6714,'1rtc')){
			$idno=intval($_GET['IDNo']);
			if(isset($_GET['Confirmation'])){
				$sql='SELECT CONCAT(Nickname," ",SurName) AS Name FROM `1employees` WHERE IDNo='.$idno;
				$stmt=$link->query($sql); $res=$stmt->fetch();
				echo '<a href="empstatustagging.php?w=Edit&f='.$title.'&IDNo='.$idno.'"><h3>Really Tag '.$res['Name'].' as Probationary?</h3></a>';
				exit();
			}
			$sql='UPDATE `1_gamit`.`0idinfo` SET EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now(), EmpStatus=IF(EmpStatus=0,1,0) WHERE IDNo='.$idno.'';
			$stmt=$link->prepare($sql);
			$stmt->execute();
			header("Location:empstatustagging.php?f=".$title);
		} else {
		echo 'No permission'; exit;
		}
    break;
   
}

$link=null; $stmt=null;

?>
