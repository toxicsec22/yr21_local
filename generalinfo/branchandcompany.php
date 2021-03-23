<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false; 
include_once('../switchboard/contents.php');


$which=(!isset($_GET['w'])?'Companies':$_GET['w']);
switch ($which){
   case 'Companies':
        $title='Companies and Branches'; 
        
        $sql1='SELECT CompanyNo, concat("No: ",CompanyNo) AS Num, concat("IncorpDate: ",IncorporationDate) AS IncorpDate, CompanyName,  concat("SECRegNo: ",SECRegNo) AS SECRegNo, concat("TIN: ",TIN) AS TIN, CONCAT("RegisteredAddress",RegisteredAddress) AS RegisteredAddress, CONCAT("RDO ",RDO) AS RDO FROM `1companies` c where Active<>0';
        $sql2='SELECT `BranchNo`, `Branch`,`Anniversary`, FORMAT(DATEDIFF(CURDATE(),`Anniversary`)/365,2) AS `AgeinYears`,`RegisteredAddress`,`CompanyNo`, RDO, PriceLevel
        , RegionMinWageArea AS EffectiveMinWageArea
        FROM `1branches` b LEFT JOIN `1_gamit`.`payroll_0regionsminwageareas` rmwa on rmwa.MinWageAreaID=EffectiveMinWageAreaID  '; //WHERE `Active`<>0 AND 
            $groupby='CompanyNo'; $orderby=' AND PseudoBranch<>1 AND `Active`<>0 AND `BranchNo`<>95 ORDER BY Branch';
            $columnnames1=array('Num','CompanyName','IncorpDate','SECRegNo','TIN','RegisteredAddress', 'RDO');
            $columnnames2=array('BranchNo','Branch','Anniversary','AgeinYears','RegisteredAddress', 'RDO');
             if (allowedToOpen(1614,'1rtc')) {  $columnnames2[]='EffectiveMinWageArea'; $columnnames2[]='PriceLevel';}
             if (allowedToOpen(64471,'1rtc')) {  $columnnames2[]='EffectiveMinWageArea';}
        include('../backendphp/layout/displayastablewithsub.php');
  break;
   case 'Locals':
       
       $title='Local Numbers at Infinity Office'; 
       $formdesc='<div style="text-align: center;"><BR><BR></i>OFFICE: 808-1569 / 808-1574 / 519-8232 / 478-8394<i><br><br>';
       if (allowedToOpen(64313,'1rtc')) { $formdesc.='<a href="../attendance/directoryedit.php"> Edit Local Numbers</a>';}
       
       $sql='SELECT p.LocalNo, FullName, department AS Dept FROM `1_gamit`.`1rtcusers` p JOIN `attend_30currentpositions` cp ON p.IDNo=cp.IDNo WHERE LocalNo>0
UNION ALL SELECT 120, "","HR/Admin" UNION ALL SELECT 200, "","Fax" ORDER BY LocalNo;';
       $columnnames=array('LocalNo','FullName','Dept'); $hidecount=true;
       include('../backendphp/layout/displayastable.php');
       echo '<br><br>dial(9) - <b>outgoing call</b><br>(82) - <b>long distance</b><br>(40) - <b>pick up call</b><br>(flash + local) - <b>transfer call</b><br></div>';
   break;
}
?>