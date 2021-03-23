<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(array(6711,67111),'1rtc')) {   echo 'No permission'; exit;} 
$showbranches=FALSE;
include_once('../switchboard/contents.php');

$link=connect_db("".$currentyr."_trail",0); 
//$which=(!isset($_GET['w'])?'Current':$_GET['w']);

//switch ($which){
//   case 'Current':
   $title='Edits to Employee Lists';
         $subtitle='Edits to Employee List This Year';
		 //'RDateHired',
         $columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','ATM','WithLeaves','WithHMO','Resigned','DateHired','RCompanyNo','Birthdate','SLBalDecCutoff','DirectOrAgency','PaidSLBenefit','WithSat','EncodedBy','TimeStamp','SLDays','VLfromPosition','VLfromTenure','Gender','RestDay','EditOrDel','EditOrDelBy','EditOrDelTS');
         $sql='SELECT ee.*, CONCAT(e.Nickname," ",e.Surname) AS EncodedBy, CONCAT(e2.Nickname," ",e2.Surname) AS EditOrDelBy, Company AS RCompany FROM `employeeedits` ee LEFT JOIN `'.$currentyr.'_1rtc`.`1employees` e ON e.IDNo=ee.EncodedByNo LEFT JOIN `'.$currentyr.'_1rtc`.`1employees` e2 ON e2.IDNo=ee.EditOrDelByNo LEFT JOIN `'.$currentyr.'_1rtc`.`1companies` c ON c.CompanyNo=e.RCompanyNo ORDER BY ee.IDNo, EditOrDelTS';
         include('../backendphp/layout/displayastable.php');
         $subtitle='<br><br>Edits to ID Information';
         $columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','StreetAddress','StreetAddress_Provincial','OldAddress','ResTel','MobileNo','Email','DateHired','Birthdate','ReferredBy','SSSNo','PHICNo','PagIbigNo','TIN','NoofDependents','SpouseName','ChildName1','ChildName2','ChildName3','ChildName4','SpouseBirthdate','ChildBirthdate1','ChildBirthdate2','ChildBirthdate3','ChildBirthdate4','ICEPerson','ICEContactInfo','ICEAddress','CivilStatus','Resigned?','DateResigned','ResignedWithClearance','ResignReason','EncodedBy','TimeStamp','SLDays','VLfromPosition','VLfromTenure','Gender','RestDay','EditOrDel','EditOrDelBy','EditOrDelTS');
         $sql='SELECT ee.*, CONCAT(e.Nickname," ",e.Surname) AS EncodedBy, CONCAT(e2.Nickname," ",e2.Surname) AS EditOrDelBy FROM `idinfoedits` ee LEFT JOIN `1_gamit`.`0idinfo` e ON e.IDNo=ee.EncodedByNo LEFT JOIN `1_gamit`.`0idinfo` e2 ON e2.IDNo=ee.EditOrDelByNo ORDER BY ee.IDNo, EditOrDelTS';
         include('../backendphp/layout/displayastableonlynoheaders.php');
//       break;
//   case 'AllID':
//         $title='Edits to ID Information';
//       break;
//}
  $link=null; $stmt=null;
?>