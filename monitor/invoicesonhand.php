<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once('../switchboard/contents.php');

include_once "../generalinfo/lists.inc"; include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
 $showbranches=true; $which=$_GET['which'];  $method='POST'; $branchno=$_SESSION['bnum'];

include_once('monitorinvlinks.php');
switch($which){
    case 'LastSeries':
        if (!allowedToOpen(785,'1rtc')) { echo 'No permission'; exit;}    
    $title='Latest Series At Branch'; $formdesc='Note: Invoices that have been issued but not marked as accepted by branch is shown.';
    $sql='SELECT b2.Branch AS OwnedBy,b.`Branch` AS BranchorComp, fm.InvType, Max(`SeriesFrom`) as LastSeries, DateAccepted, `txndesc` as `InvoiceType`, CEIL(Max(`SeriesFrom`)/50)*50 AS SeriesTo,
concat(`Nickname`," ",`SurName`) as `AcceptedBy`, IF(ISNULL(DateAccepted), "NotYetAccepted","") as Accepted FROM `monitor_2fromsuppliersub` fs
    JOIN `monitor_2fromsuppliermain` fm ON fm.TxnID=fs.TxnID
    JOIN `1branches` b ON b.BranchNo=fs.IssuedTo
    JOIN `1branches` b2 ON b2.BranchNo=fm.BranchNo
LEFT JOIN `1employees` e ON e.IDNo=fs.AcceptedByNo
JOIN `invty_0txntype` tt ON tt.txntypeid=fm.InvType 
WHERE DateIssued IS NOT NULL AND fs.`IssuedTo`='.$branchno.' GROUP BY fs.`IssuedTo`, `InvType`,  IF(ISNULL(DateAccepted), "NotYetAccepted","") ORDER BY InvoiceType;'; //echo $sql;
    $columnnames=array('OwnedBy','BranchorComp', 'InvoiceType', 'LastSeries', 'SeriesTo', 'DateAccepted', 'AcceptedBy');
     include('../backendphp/layout/displayastable.php');    
        break;

case 'OnStock':
        if (!allowedToOpen(786,'1rtc')) { echo 'No permission'; exit;}
    $title='Invoices On Stock'; $formdesc='Note: Invoices that have not been accepted by branch is shown.';
    $sql0='CREATE TEMPORARY TABLE onstock as SELECT fm.InvType, BookletNo, SeriesFrom, CEIL(SeriesFrom/50)*50 AS SeriesTo, IF(ISNULL(DateIssued),"",DateIssued) AS DateIssued FROM `monitor_2fromsuppliersub` fs JOIN `monitor_2fromsuppliermain` fm ON fm.TxnID=fs.TxnID WHERE (DateAccepted IS NULL OR DateAccepted="0000-00-00") AND BranchNo='.$branchno.' ORDER BY SeriesFrom';
    $stmt=$link->prepare($sql0); $stmt->execute();
    $sql1='SELECT txntypeid as InvType, `txndesc` as InvoiceType FROM `invty_0txntype` WHERE `txntypeid` IN (1,2, 4,5,10,11,30,41);';
    $columnnames1=array('InvoiceType');
    $sql2='SELECT SeriesFrom, SeriesTo, BookletNo, DateIssued FROM onstock'; //echo $sql0; echo $sql1; echo $sql2;
    $columnnames2=array('SeriesFrom', 'SeriesTo', 'BookletNo', 'DateIssued');
    $orderby='ORDER BY InvType,BookletNo,SeriesFrom'; $groupby='InvType';
    include('../backendphp/layout/displayastablewithsub.php');    
	
    break;

}
  $link=null; $stmt=null;

?>