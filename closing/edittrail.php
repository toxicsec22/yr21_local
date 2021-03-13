<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(5691); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=FALSE;
include_once('../switchboard/contents.php');

$link=connect_db("".$currentyr."_trail",0); 

$title='Edits to Closing Data';
$action='edittrail.php'; include_once('filters.php');
$orderby=' ORDER BY Month, AccountID, Branch, EditOrDelTS ';



/*
         $subtitle='<br><br>Edits to Main From';
         $columnnames=array('Month','AccountID','Branch','EndBal','EncodedBy','TimeStamp','EditOrDel','EditOrDelBy','EditOrDelTS');
         $sql='SELECT cm.*, CONCAT(e.Nickname," ",e.Surname) AS EncodedBy, CONCAT(e2.Nickname," ",e2.Surname) AS EditOrDelBy FROM closemain cm LEFT JOIN `1employees` e ON e.IDNo=cm.EncodedByNo LEFT JOIN `1employees` e2 ON e2.IDNo=cm.EditOrDelByNo JOIN `1branches` b ON b.BranchNo=cm.BranchNo '.$filter.$orderby;
         include('../backendphp/layout/displayastable.php'); unset($formdesc); */
         //$subtitle='<br><br>Edits to Subform';
         $columnnames=array('Month','AccountID','Branch','WhereTxnID','ControlNo','Link','Details','Amount','HowToSettle','EncodedBy','TimeStamp','EditOrDel','EditOrDelBy','EditOrDelTS','CloseID','CloseSubID');
         $sql='SELECT Month, AccountID, Branch, cs.*, CONCAT(e.Nickname," ",e.Surname) AS EncodedBy, CONCAT(e2.Nickname," ",e2.Surname) AS EditOrDelBy FROM closesub cs LEFT JOIN `closing_2closemain` cm ON cm.CloseID=cs.CloseID LEFT JOIN `1employees` e ON e.IDNo=cs.EncodedByNo LEFT JOIN `1employees` e2 ON e2.IDNo=cs.EditOrDelByNo JOIN `1branches` b ON b.BranchNo=cm.BranchNo '.$filter.$orderby;
         include('../backendphp/layout/displayastable.php');

 $link=null; $stmt=null;
?>