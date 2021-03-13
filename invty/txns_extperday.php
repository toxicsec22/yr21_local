<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
include_once('../switchboard/contents.php');
 


$txnid='TxnID';
$whichqry=$_GET['w'];
$pagetouse='txns_extperday.php?w='.$whichqry;
if (!allowedToOpen(769,'1rtc')){   echo 'No permission'; exit;}
switch ($whichqry){
case 'Request':
    if (!allowedToOpen(770,'1rtc')){   echo 'No permission'; exit;}
$title='Pending External Requests';
$fieldname='w';
$orderby='RequestNo';
$columnnames=array('RequestNo','RequestingBranch','Date','DateRequired','LineItems','Posted');  
$method='GET';
$showbranches=true;
include_once('../backendphp/layout/clickontabletoedithead.php');
$sql='select p.*,b.Branch as RequestingBranch,count(ItemCode) as LineItems from invty_40pendingextrequests as p
        join `1branches` as b on p.BranchNo=b.BranchNo 
        where Pending<>0 group by RequestNo, p.BranchNo
	union select r.TxnID,r.RequestNo, r.Date, r.DateReq as DateRequired, r.BranchNo, r.EncodedByNo, r.Posted, r.PostedByNo, \'\' as ItemCode, 0 as Pending, b.Branch as RequestingBranch, 0 as LineItems from invty_3extrequest r left join invty_3extrequestsub rs on r.TxnID=rs.TxnID join `1branches` as b on r.BranchNo=b.BranchNo where rs.TxnID is null';
	//echo $sql;
$process1='addeditext.php?w='.$whichqry.'&';
$processlabel1='Lookup';
include_once('../backendphp/layout/clickontabletoeditbody.php');
    break;
case 'Order':
    if (!allowedToOpen(769,'1rtc')){   echo 'No permission'; exit;}
$title='Purchase Orders Per Day';
$showbranches=true;
$fieldname='Date';
$skipsql=true;
$lookupprocess='txns_extperday.php?w=Order';
$orderby='PONo';
$columnnames=array('PONo','SupplierName','RequestingBranch','DateReq','LineItems','Company','CompanyNo','Posted');  
$method='GET';
if (isset($_REQUEST['Date'])){$txndate=$_REQUEST['Date'];}else {$txndate=date("Y-m-d");}
include_once('../backendphp/layout/clickontabletoedithead.php');
$title='';
$sql='select o.*,b.Branch as RequestingBranch,count(os.ItemCode) as LineItems,s.SupplierName, Company from invty_3order o join invty_3ordersub os on o.TxnID=os.TxnID
        join `1branches` as b on o.BranchNo=b.BranchNo
	join `1suppliers` as s on o.SupplierNo=s.SupplierNo
	LEFT JOIN `1companies` AS c ON c.CompanyNo=o.CompanyNo
	where o.Date=\''.$txndate.'\' group by PONo
	union select o.*, b.Branch as RequestingBranch, 0 as LineItems,s.SupplierName, Company from invty_3order o
	left join invty_3ordersub os on o.TxnID=os.TxnID join `1branches` as b on o.BranchNo=b.BranchNo
	join `1suppliers` as s on o.SupplierNo=s.SupplierNo
	LEFT JOIN `1companies` AS c ON c.CompanyNo=o.CompanyNo
	where os.TxnID is null and Posted=0';
	//echo $sql;
$editprocess='addeditext.php?w='.$whichqry.'&TxnID=';
$processlabel1='Lookup';
include_once('../backendphp/layout/displayastablewithconditionandedit.php');
    break;
}
         $link=null; $stmt=null;
?>