<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false; include_once('../switchboard/contents.php');
if (!allowedToOpen(6893,'1rtc')) {   echo 'No permission'; exit;} 

 
 

$which=(!isset($_GET['w'])?'PerPerson':$_GET['w']);

switch ($which){
   case 'PerPerson':
       if (!allowedToOpen(6893,'1rtc')){ echo 'No permission'; exit;}
       $title='Employee Service Report - Yr '.$currentyr.'';
         echo '<title>'.$title.'</title>';     
       include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
       $sql0='SELECT IDNo, CONCAT(FullName," - ", Branch) AS Name FROM `attend_30currentpositions` ';
       echo comboBox($link, $sql0, 'Name', 'IDNo', 'emplist');
	   
       $sql0='Select e.IDNo,Concat(e1.NickName," ",e1.SurName," - ",(SELECT IF(PseudoBranch=1,dept,Branch) FROM attend_1defaultbranchassign dba JOIN attend_30latestpositionsinclresigned lpir ON dba.IDNo=lpir.IDNo JOIN 1branches b ON DefaultBranchAssignNo=b.BranchNo JOIN attend_1positions p ON lpir.PositionID=p.PositionID JOIN 1departments d ON p.deptid=d.deptid WHERE lpir.IDNo=e1.IDNo)) as Name from 1employees e  join 1employees e1 on e1.IDNo=e.IDNo WHERE e.Resigned=1;';
       echo comboBox($link, $sql0, 'Name', 'IDNo', 'empresigned');
       echo '<h3>'.$title.'</h3><br>'; $title='';
       ?><div>
	   <div style="float:left;">
	   <form method="post" action="lookupreports.php?w=PerPerson" enctype="multipart/form-data">
                Employee:  <input type="text" name="IDNo" list="emplist"></input>
<input type="submit" name="lookup" value="Lookup"> </form>
</div>
<div style="margin-left:500px;">
<form method="post" action="lookupreports.php?w=PerPerson" enctype="multipart/form-data">
                Resigned Employee:  <input type="text" name="IDNo" list="empresigned"> <input type="hidden" name="Res" value="1">
<input type="submit" name="lookup" value="Lookup"> </form>
</div>
</div>
       
    <?php
    
    if(!isset($_POST['IDNo'])) { goto nodata;}
    $idno=$_POST['IDNo'];
	if(isset($_POST['Res'])){
		$fname=comboBoxValue($link, '`1_gamit`.`0idinfo`', 'IDNo', $idno, 'Nickname');
		$lname=comboBoxValue($link, '`1_gamit`.`0idinfo`', 'IDNo', $idno, 'SurName');
		$name=$fname." ".$lname;
	} else {
		 $name=comboBoxValue($link, '`attend_30currentpositions`', 'IDNo', $idno, 'FullName');
	}
    $formdesc='</i><br><br><h3>IDNo '.$idno.' :  '.$name.'</h3><i>';
    $subtitle='<br><br>Personnel Action';
    $sql='SELECT pa.*, e.Nickname, CONCAT(e.FirstName," ",e.SurName) AS FullName, Position, CONCAT(Department, " - ", Branch) AS `Department/Branch`, ActionDesc AS PersonnelAction, department AS Department, e2.Nickname AS EncodedBy FROM `hr_2personnelaction` pa JOIN `hr_0personnelaction` po ON po.ActionID=pa.ActionID 
JOIN `1departments` d ON d.deptid=pa.deptID JOIN `1branches` b ON b.BranchNo=pa.BranchNo
JOIN attend_1positions p ON p.PositionID=pa.PositionID
JOIN `1employees` e ON e.IDNo=pa.IDNo JOIN `1employees` e2 ON e2.IDNo=pa.EncodedByNo 
        WHERE  pa.IDNo='.$idno.' ORDER BY DateServed DESC';

$columnnames=array('IDNo','Nickname','FullName','Position','Department/Branch','DateServed','PersonnelAction','Details','BasicSalary','Allowances','EncodedBy','TimeStamp');
     include('../backendphp/layout/displayastable.php');

include('meritdemeritsummary.php');
     
     
     $subtitle='<br><br>Performance Evaluation';
    $sql='SELECT pf.*, CONCAT(e1.FirstName, " ", e1.Surname) AS FullName, e1.DateHired, TRUNCATE(((TO_DAYS(NOW()) - TO_DAYS(`e1`.`DateHired`)) / 365),2) AS `HowLongWithUsinYrs`, e.Nickname as HREncodedBy, Position AS CurrentPosition, b.Branch AS CurrentBranch, c.Company, CONCAT(e2.Nickname, " ", e2.Surname) AS Supervisor,
    SelfEval, SupervisorEval, IF(HRStatus=1,"Filed","Pending") AS HR_Status, IF(EmpResponse=0,"",IF(EmpResponse=1,"Agree","Disagree")) AS Emp_Response
	       FROM hr_2perfevalmain pf   
	       LEFT JOIN `1employees` e ON e.IDNo=pf.HREncodedByNo
	       JOIN `1employees` e1 ON e1.IDNo=pf.IDNo
	       LEFT JOIN `1employees` e2 ON e2.IDNo=pf.SupervisorIDNo
	       JOIN `1branches` b ON b.BranchNo=pf.CurrentBranchNo
	       JOIN `1companies` c ON c.CompanyNo=e1.RCompanyNo
	       LEFT JOIN `attend_1positions` p ON p.PositionID=pf.CurrentPositionID
        WHERE  pf.IDNo='.$idno.' ORDER BY EvalDueDate DESC';

$columnnames=array('CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','SelfEval','SelfRemarks','Supervisor','SupervisorEval','SuperRemarks','Emp_Response','EmpRemarks','HRRemarks','HREncodedBy','HR_Status');
     $showsubtitlealways=true; include('../backendphp/layout/displayastableonlynoheaders.php');
     
     $subtitle='<br><br>Changes in Personal Information - All ID Info'; $color1='ffe6e6';
     
    $sql='SELECT "Current" AS SupersededOn, ie.`IDNo`, CONCAT(ie.`Nickname`," - ",ie.`SurName`,", ",ie.`FirstName`," ",ie.`MiddleName`) AS `Name`,`StreetAddress`,`BarangayOrTown`,`CityOrProvince`,`ZipCode`,CONCAT(StreetAddress_Provincial,", ",CityOrProvince_Provincial,", ",BarangayOrTown_Provincial) AS ProvincialAddress,`OldAddress`,`ResTel`,`MobileNo`,`Email`,ie.`DateHired`,ie.`Birthdate`,`ReferredBy`,`SSSNo`,`PHICNo`,`PagIbigNo`,`TIN`,`NoofDependents`,`SpouseName`, IF((`SpouseBirthdate` LIKE "0000-00-00") OR (`SpouseBirthdate` LIKE ""), "",`SpouseBirthdate`) AS `SpouseBirthdate`, CONCAT(IFNULL(`ChildName1`,""),IFNULL(CONCAT("<BR>",`ChildName2`),""),IFNULL(CONCAT("<BR>",`ChildName3`),""),IFNULL(CONCAT("<BR>",`ChildName4`),"")) AS `Child/ren`,

CONCAT(IF(`ChildBirthdate1`=0,"",`ChildBirthdate1`),IF(`ChildBirthdate2`=0,"",CONCAT("<BR>",`ChildBirthdate2`)),IF(`ChildBirthdate3`=0,"",CONCAT("<BR>",`ChildBirthdate3`)),IF(`ChildBirthdate4`=0,"",CONCAT("<BR>",`ChildBirthdate4`))) AS `Bdays_Child/ren`, CONCAT(`ICEPerson`, "(",IFNULL(RelationshiptoEmployee,""),")","<BR>",`ICEContactInfo`,"<BR>",`ICEAddress`) AS `InCaseOfEmergency`,`CivilStatus`,`Resigned?`, `DateResigned`,`ResignedWithClearance`,`ResignReason`,ie.`TimeStamp`, e.Nickname AS EncodedBy, "" AS EditOrDelBy, "Current" AS `Edit?` FROM `1_gamit`.`0idinfo` ie LEFT JOIN `1employees` e ON e.IDNo=ie.EncodedByNo WHERE ie.IDNo='.$idno.'
UNION ALL
SELECT EditOrDelTS AS SupersededOn,ie.`IDNo`, CONCAT(ie.`Nickname`," - ",ie.`SurName`,", ",ie.`FirstName`," ",ie.`MiddleName`) AS `Name`,`StreetAddress`,`BarangayOrTown`,`CityOrProvince`,`ZipCode`,CONCAT(StreetAddress_Provincial,", ",CityOrProvince_Provincial,", ",BarangayOrTown_Provincial) AS ProvincialAddress,`OldAddress`,`ResTel`,`MobileNo`,`Email`,ie.`DateHired`,ie.`Birthdate`,`ReferredBy`,`SSSNo`,`PHICNo`,`PagIbigNo`,`TIN`,`NoofDependents`,`SpouseName`, IF((`SpouseBirthdate` LIKE "0000-00-00") OR (`SpouseBirthdate` LIKE ""), "",`SpouseBirthdate`) AS `SpouseBirthdate`,CONCAT(IFNULL(`ChildName1`,""),IFNULL(CONCAT("<BR>",`ChildName2`),""),IFNULL(CONCAT("<BR>",`ChildName3`),""),IFNULL(CONCAT("<BR>",`ChildName4`),"")) AS `Child/ren`,

CONCAT(IF(`ChildBirthdate1`=0,"",`ChildBirthdate1`),IF(`ChildBirthdate2`=0,"",CONCAT("<BR>",`ChildBirthdate2`)),IF(`ChildBirthdate3`=0,"",CONCAT("<BR>",`ChildBirthdate3`)),IF(`ChildBirthdate4`=0,"",CONCAT("<BR>",`ChildBirthdate4`))) AS `Bdays_Child/ren`, CONCAT(`ICEPerson`, "(",IFNULL(RelationshiptoEmployee,""),")","<BR>",`ICEContactInfo`,"<BR>",`ICEAddress`) AS `InCaseOfEmergency`,`CivilStatus`,`Resigned?`,`DateResigned`,`ResignedWithClearance`,`ResignReason`,ie.
`TimeStamp`, e.Nickname AS EncodedBy, e2.Nickname AS EditOrDelBy, IF(EditOrDel=0,"Edit","Delete") AS `Edit?` FROM `'.$currentyr.'_trail`.`idinfoedits` ie LEFT JOIN `1employees` e ON e.IDNo=ie.EncodedByNo
LEFT JOIN `1employees` e2 ON e2.IDNo=ie.EditOrDelByNo WHERE ie.IDNo='.$idno.' ORDER BY SupersededOn DESC';
// echo $sql; exit();
    $columnnames=array('SupersededOn','IDNo','Name','StreetAddress','BarangayOrTown','CityOrProvince','ZipCode','ProvincialAddress','OldAddress','ResTel','MobileNo','Email','DateHired','Birthdate','ReferredBy','SSSNo','PHICNo','PagIbigNo','TIN','NoofDependents','SpouseName','SpouseBirthdate','Child/ren','Bdays_Child/ren','InCaseOfEmergency','CivilStatus','Resigned?','DateResigned','ResignedWithClearance','ResignReason','TimeStamp','EncodedBy','Edit?','EditOrDelBy');
    //if($_SESSION['(ak0)']==1002){ echo $sql;}
    $showsubtitlealways=true; include('../backendphp/layout/displayastablenosort.php');
    
    $subtitle='<br><br>Changes in Personal Information - Current Yr Data'; $color1='ffe6ff';
        
    $sql='SELECT "Current" AS SupersededOn, ie.`IDNo`,ie.`Nickname`,ie.`SurName`,ie.`FirstName`,ie.`MiddleName`,IF(ie.`Gender`<>0,"Male","Female") AS `Gender`,ie.`UBPATM`, IF(ie.`WithLeaves`<>0,"Yes","No") AS `WithLeaves`, IF(ie.`WithHMO`<>0,"Yes","No") AS `WithHMO`,IF(ie.Resigned=0,"","Resigned") AS `Resigned`, ie.`DateHired`, `Company`,ie.`RDateHired`, IF(ie.`DirectOrAgency`<>0,"Agency","Direct") AS `DirectOrAgency`, ie.`Birthdate`,ELT(ie.`WithSat`+1,"Sat Restdays", "Sat Halfday Work", "With Sat Work") AS `WithSat`, ELT(ie.RestDay+1, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday") AS  `RestDay`, ie.`SLBalDecCutoff`,ie.`PaidSLBenefit`,ie.`SLThisYr`,ie.`VLfromPosition`,ie.`VLfromTenure`,e.Nickname AS EncodedBy, "" AS EditOrDelBy, "Current" AS `Edit?` FROM `1employees` ie LEFT JOIN `1employees` e ON e.IDNo=ie.EncodedByNo
 JOIN `1companies` c ON c.CompanyNo=ie.RCompanyNo  WHERE  ie.IDNo='.$idno.' 
UNION ALL
SELECT EditOrDelTS AS SupersededOn,ie.`IDNo`,ie.`Nickname`,ie.`SurName`,ie.`FirstName`,ie.`MiddleName`,IF(ie.`Gender`<>0,"Male","Female") AS `Gender`,ie.`UBPATM`, IF(ie.`WithLeaves`<>0,"Yes","No") AS `WithLeaves`, IF(ie.`WithHMO`<>0,"Yes","No") AS `WithHMO`,IF(ie.Resigned=0,"","Resigned") AS `Resigned`,ie.`DateHired`, `Company`,ie.`RDateHired`, IF(ie.`DirectOrAgency`<>0,"Agency","Direct") AS `DirectOrAgency`,ie.`Birthdate`,
ELT(ie.`WithSat`+1,"Sat Restdays", "Sat Halfday Work", "With Sat Work") AS `WithSat`, ELT(ie.RestDay+1, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday") AS `RestDay`,ie.`SLBalDecCutoff`,ie.`PaidSLBenefit`,ie.`SLThisYr`,ie.`VLfromPosition`,ie.`VLfromTenure`, e.Nickname AS EncodedBy, e2.Nickname AS EditOrDelBy, IF(EditOrDel=0,"Edit","Delete") AS `Edit?` FROM `'.$currentyr.'_trail`.`employeeedits` ie LEFT JOIN `1employees` e ON e.IDNo=ie.EncodedByNo
LEFT JOIN `1employees` e2 ON e2.IDNo=ie.EditOrDelByNo JOIN `1companies` c ON c.CompanyNo=ie.RCompanyNo  WHERE  ie.IDNo='.$idno.' ORDER BY SupersededOn DESC;';
                
    $columnnames=array('SupersededOn','IDNo','Nickname','SurName','FirstName','MiddleName','Gender','UBPATM','WithLeaves','WithHMO','DateHired','Company','RDateHired','DirectOrAgency','Birthdate','WithSat','RestDay','SLBalDecCutoff','PaidSLBenefit','SLThisYr','VLfromPosition','VLfromTenure','EncodedBy','Edit?','EditOrDelBy'); 
     $showsubtitlealways=true; include('../backendphp/layout/displayastablenosort.php');
     
     $subtitle='<br><br>Trainings/Seminars'; $color1='e6f7ff';
     $sql='SELECT StartDate,EndDate,TrainingTitle,Trainor,TrainorTitle,Venue,IF(Completed=1,"Yes","No") AS `Completed?`,Comments, Position
	FROM `hr_2traintrack` ts JOIN `hr_2trainsched` tm ON tm.TxnID=ts.TxnID JOIN `hr_1trainings` t ON t.TrainingID=tm.TrainingID
	 JOIN attend_1positions p ON p.PositionID=ts.PositionID WHERE IDNo='.$idno.' ORDER BY StartDate DESC';
        $columnnames=array('StartDate','EndDate','TrainingTitle','Trainor','TrainorTitle','Venue','Completed?','Comments','Position');
     $showsubtitlealways=true; include('../backendphp/layout/displayastablenosort.php');
              
      $subtitle='<br><br>Tardiness'; $color1='ffb3b3';
     $sql='SELECT Branch AS AssignedBranch, Position,`Month`,LatesPerMonth,TotalMinutesLate FROM attend_62latescount WHERE IDNo='.$idno;
        $columnnames=array('Position','AssignedBranch','Month','LatesPerMonth','TotalMinutesLate');
     $showsubtitlealways=true; include('../backendphp/layout/displayastablenosort.php');
     
     $subtitle='<br><br>Absence Without Leave (AWOL)'; $color1='ffb3b3';
     $sql='SELECT `IDNo`, COUNT(`a`.`IDNo`) AS `UnapprovedAbsencesPerMonth`, (MONTHNAME(`a`.`DateToday`)) AS `Month`
    FROM `attend_2attendance` `a` WHERE
        (`a`.`DateToday` <= NOW()) AND (`a`.`LeaveNo`=18) AND a.IDNo='.$idno.
    ' GROUP BY `a`.`IDNo`, MONTHNAME(`a`.`DateToday`) ORDER BY MONTH(`a`.`DateToday`)'; 
    $columnnames=array('Month','UnapprovedAbsencesPerMonth'); $width='20%';
    $showsubtitlealways=true; include('../backendphp/layout/displayastablenosort.php');
     unset($width);
     
     $subtitle='<br><br>Offenses'; 
     $sql0='SELECT TxnID FROM `hr_4offensemain` WHERE IDNo='.$idno;
     $stmt0=$link->query($sql0); $res0=$stmt0->fetchAll();
     $hidecount=true;
 foreach ($res0 as $offense){
     
     $txnid=$offense['TxnID']; $color1='ddccff';
     $sql='SELECT TxnID, DateofIncident, ShortSummary, FirstInfo, DateResult, CONCAT("<b>",IFNULL(ResultDesc,"UNRESOLVED"),"</b>") AS ResultDesc, ResultSanction, ResultCOCArticle
, NthOffense  FROM `hr_4offensemain` om LEFT JOIN `hr_0offenseresult` res ON res.ResultID=om.ResultID WHERE om.TxnID='.$txnid;
     $columnnames=array('DateofIncident', 'ShortSummary', 'FirstInfo', 'DateResult', 'ResultDesc', 'ResultSanction', 'ResultCOCArticle', 'NthOffense');
     $showsubtitlealways=true; include('../backendphp/layout/displayastablenosort.php');
     
     unset($subtitle); $color1='f2f2f2'; 
     $sql='SELECT OtherInfo FROM `hr_4offensesub` WHERE TxnID='.$txnid.' ORDER BY OrderOfInfo;';
     $columnnames=array('OtherInfo');
     include('../backendphp/layout/displayastablenosort.php');
     
     $sql='SELECT OtherInfo AS Findings FROM `hr_4offensefindings` WHERE TxnID='.$txnid.' ORDER BY OrderOfInfo;';
     $columnnames=array('Findings');
     include('../backendphp/layout/displayastablenosort.php');
     
     $sql='SELECT OtherInfo AS Result FROM `hr_4offenseresult` WHERE TxnID='.$txnid.' ORDER BY OrderOfInfo;';
     $columnnames=array('Result');
     include('../backendphp/layout/displayastablenosort.php');
 }
 
		$subtitle='<br><br>Property Assigned'; 
        $sql1='SELECT pa.*,PropertyDesc AS Property, CONCAT(e.FirstName, " ", e.Surname) AS AssignedTo, Branch AS AssignedBranch, department AS Department, CONCAT(e1.Nickname, " ", e1.Surname) AS EncodedBy, CONCAT(e2.Nickname, " ", e2.Surname) AS ReturnEncodedBy FROM `admin_1property` p JOIN `admin_2propertyassign` AS pa ON p.PropID = pa.PropID LEFT JOIN `1_gamit`.`0idinfo` e ON e.IDNo=pa.AssignToIDNo LEFT JOIN `1branches` b ON b.BranchNo=pa.AssignBranchNo LEFT JOIN `1departments` d ON d.deptid=pa.DeptID LEFT JOIN `1_gamit`.`0idinfo` e1 ON e1.IDNo=pa.EncodedByNo LEFT JOIN `1_gamit`.`0idinfo` e2 ON e2.IDNo=pa.ReturnEncodedByNo WHERE pa.`AssignToIDNo`='.$idno;

        $sql=$sql1.' AND DateReturned IS NULL ORDER BY AssignDate DESC;';
		// echo $sql;
        $columnnames=array('Property','AssignDate','AssignedTo','Department','AssignedBranch','AssignRemarks','BookValueAsOfIssueDate');
        include('../backendphp/layout/displayastablenosort.php');
        
		
		$subtitle='<br><br>Property Assigned and Returned';
        $sql=$sql1.' AND DateReturned IS NOT NULL ORDER BY DateReturned DESC;';
        $columnnames=array('Property','AssignDate','AssignedTo','Department','AssignedBranch','AssignRemarks','BookValueAsOfIssueDate','DateReturned','ReturnRemarks','BookValueAsOfReturnDate');
        include('../backendphp/layout/displayastablenosort.php');
		
		
		$subtitle='<br><br>Key Assigned and Returned';
		$sql='SELECT ks.*,Branch,CONCAT(e.FirstName, " ", e.Surname) AS AssignedTo FROM `admin_2klformmain` km LEFT JOIN `admin_2klformsub` ks ON km.TxnID = ks.TxnID LEFT JOIN `1_gamit`.`0idinfo` e ON e.IDNo=km.AssigneeIDNo LEFT JOIN `1branches` b ON b.BranchNo=km.BranchNo WHERE AssigneeIDNo='.$idno.' ORDER BY DateReceived';
		$columnnames=array('PadlockBrand','NumberOfKeys','AssignedTo','Branch','DateReceived','DateReturned');
        include('../backendphp/layout/displayastablenosort.php');
		
		
		
     
     echo '<br>END OF REPORT';
       break; 
   
}
nodata:
    $link=null; $stmt=null; 
noform:
?>