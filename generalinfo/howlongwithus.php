<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false; 
include_once('../switchboard/contents.php');
if (!allowedToOpen(676,'1rtc')) { header ("Location:/index.php");}


    
    $title='How Long With Us';
    $showbranches=false;
    
if (allowedToOpen(6761,'1rtc')) {
    $condition='';
}  else if (allowedToOpen(6762,'1rtc')) {
	$condition=' WHERE p.deptid=10';
} else {
    $condition=' WHERE deptheadpositionid='.$_SESSION['&pos'];
}
    $sql1='Select IF(p.deptid=10,p.Branch, p.department) AS Department from `attend_30currentpositions` p '.$condition. ' GROUP BY IF(p.deptid=10,p.Branch, p.department) ORDER BY p.deptid, p.BranchNo';
	
    $sql2='SELECT h.*, IF(p.deptid=10,p.Branch, p.department) AS Department, Position AS CurrentPosition from `attend_howlongwithus` h  JOIN `attend_30currentpositions` p ON p.IDNo=h.IDNo '.$condition;
    $groupby='Department'; $orderby=' ORDER BY InYears DESC';
    $columnnames1=array('Department'); $columnnames2=array('IDNo','Nickname','FullName','CurrentPosition','DateHired','InYears','WithHMO');
     include('../backendphp/layout/displayastablewithsubHAVING.php');
       $link=null; $stmt=null;
    ?>
