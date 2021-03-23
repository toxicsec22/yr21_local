<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 
if (!allowedToOpen(7308,'1rtc')) { echo 'No permission'; exit;}
$showbranches=false;
include_once('../switchboard/contents.php');



include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=!isset($_REQUEST['w'])?'List':($_REQUEST['w']);


$monthlist='<option value="All">All</option>';  $monthform=''; $monthcnt=1;
while ($monthcnt<=date('m')){
	$monthlist.='<option value='.$monthcnt.'>'.$monthcnt.'</option>';
	$monthcnt++;
}
$monthform = '<select name="monthno">'.$monthlist.'</select>';

$sqlbranch='SELECT Branch, BranchNo FROM 1branches WHERE Active<>0 AND BranchNo<>95 UNION SELECT Fullname,IDNo FROM attend_30currentpositions WHERE PositionID IN (36,61) ORDER BY Branch;'; $stmtbranch=$link->query($sqlbranch);
$branchlist='<option value="All">All</option>'; $branchform ='';
while ($rowbranch = $stmtbranch->fetch()){
	$branchlist.='<option value='.$rowbranch['BranchNo'].'>'.$rowbranch['Branch'].'</option>';
}
$branchform = '<select name="branchno">'.$branchlist.'</select>';

switch ($which){
case 'List':
$title='Targets Reached';
$formdesc='</i><br><br><form action="loginmsgsettings.php" method="POST" enctype="multipart/form-data" style="display: inline">
        MonthNo '.$monthform.'
        Branch/STL/SAM '.$branchform.'
        <input type="submit" name="btnFilter" value="Look up">
   </form><br>';    
    if (isset($_POST['btnFilter'])){
		$condit=(($_POST['monthno']=='All')?'':'MonthNo='.$_POST['monthno'].' AND ') . (($_POST['branchno']=='All')?'':'lm.BranchNo='.$_POST['branchno'].' AND');
	} else {
		$condit='';
	}
$sql='SELECT lm.*, IF(`DisplayType`=2,"Announcement",Branch) AS Branch, CONCAT(e.Nickname," ",e.Surname) AS EncodedBy FROM acctg_6targetscores lm 
JOIN 1branches b ON lm.BranchNo=b.BranchNo
JOIN 1employees e ON e.IDNo=lm.EncodedByNo WHERE '.$condit.' `DisplayType`<>3 
UNION 
SELECT lm.*, CONCAT(e1.Nickname," ",e1.Surname) , CONCAT(e.Nickname," ",e.Surname) AS EncodedBy FROM acctg_6targetscores lm 
JOIN 1employees e1 ON e1.IDNo=lm.BranchNo
JOIN 1employees e ON e.IDNo=lm.EncodedByNo WHERE '.$condit.' `DisplayType`=3 ORDER BY `DisplayType`, MonthNo DESC, Score DESC; ';

$columnnames=array('MonthNo','Branch','Score','EncodedBy','TimeStamp'); 

$width="75%";
     include('../backendphp/layout/displayastable.php');
	 
     break;
     
}
  $link=null;  $stmt=null;
?>