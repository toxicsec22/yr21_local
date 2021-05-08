<?php
$path=$_SERVER['DOCUMENT_ROOT']; 
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=true;


   
    $perday=$_GET['perday'];$fieldname=($perday==0?'Month':'Date');
    $title='Branch No. '.$_SESSION['bnum'].': '.$_SESSION['@brn'].' Txns Per '.($perday==0?'Month':'Day');
    $pagetouse='txnsperday.php?perday='.$perday.(!isset($_REQUEST[$fieldname])?'&Date='.date('Y-m-d'):'&'.$fieldname.'='.$_REQUEST[$fieldname]);
    
    
$txnidname='TxnID';
$method='GET';

if (!isset($_REQUEST['print'])) { include_once('../switchboard/contents.php'); include_once('../backendphp/layout/clickontabletoedithead.php'); $title='';} //this is ok
// check if allowed
$allowed=array(765,766,767,7651,7652,7653);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
if (!isset($_REQUEST['print'])){
?>
<form method="post" action="<?php echo $pagetouse; ?>" enctype="multipart/form-data">
<?php if ($perday==1){ ?>
                Choose Date:  <input type="date" width=10 name="<?php echo $fieldname; ?>" value="<?php echo (!isset($_REQUEST[$fieldname])?date('Y-m-d'):$_REQUEST[$fieldname]); ?>"></input> 
                <input type="hidden" width=0 name="todate" value="<?php echo (!isset($_REQUEST[$fieldname])?date('Y-m-d'):$_REQUEST[$fieldname]); ?>"></input> 
        <?php } elseif ($perday==0) { ?>
                Choose Month (1 - 12):  <input type="text" width=5 name="<?php echo $fieldname; ?>" value="<?php echo (!isset($_REQUEST[$fieldname])?date('m'):$_REQUEST[$fieldname]); ?>"></input>
                <input type="hidden" width=0 name="todate" value="<?php echo (!isset($_REQUEST[$fieldname])?date('m'):$_REQUEST[$fieldname]); ?>"></input>
        <?php } else { ?>
                Show Transactions From:  <input type="date" width=8 name="<?php echo $fieldname; ?>" value="<?php echo date('Y-m-d'); ?>"></input>
                To:  <input type="date" width=8 name="todate" value="<?php echo date('Y-m-d'); ?>"></input>
        <?php } ?>    
<input type="submit" name="lookup" value="Show All"> <input type="submit" name="returns" value="Show Returns Only"> </form>

<?php
} 
if (!isset($_REQUEST[$fieldname])){
   $txndate=$perday==0?'Month(m.Date)='.date("m"):'m.Date=\''.date("Y-m-d").'\''; //default
   $columnnames=array('Date','SaleNo','ClientName','Remarks','Form','PayType','Posted','CheckDetails','DateofCheck','PONo', 'Amount','Overprice');
   
   $formdesc=$txndate.'<br>';
   $lookupfieldname=$txndate;
   $columnsub=$columnnames; $returns='';
} else {
   $lookupfieldname=$_REQUEST[$fieldname];
   $returns=!isset($_POST['returns'])?'':' WHERE txntype=5';
if ($perday==0){
$formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$_REQUEST[$fieldname].'-1')).'<br>';   
$txndate='Month(m.Date)='.$_REQUEST[$fieldname];
$columnnames=array('Date','SaleNo','ClientName','Remarks','Form','PayType','Posted','CheckDetails','DateofCheck','PONo', 'Amount','Overprice');

$columnsub=$columnnames; 

} elseif ($perday==1) {
$formdesc=$_REQUEST[$fieldname].'<br>';
$txndate='m.Date=\''.$_REQUEST[$fieldname].'\'';
$columnnames=array('SaleNo','ClientName','Remarks','Form','PayType','Posted','CheckDetails','DateofCheck','PONo', 'Amount','Overprice');

$columnsub=$columnnames; 
} else {
$formdesc=$_REQUEST[$fieldname].' to '.$_REQUEST['todate'].'<br>';
$txndate='m.Date>=\''.$_REQUEST[$fieldname].'\' and m.Date<=\''.$_REQUEST['todate'].'\'';
$columnnames1=array('DateForm');
$columnnames2=array('SaleNo','ClientName','Remarks','PayType','CheckDetails','CheckDate','PONo', 'Amount','Overprice');
}
}
if ((allowedToOpen(7651,'1rtc')) and !isset($_REQUEST['print'])){
?>
        <br><a href="<?php echo 'txnsperday.php?perday='.$perday.'&'.$fieldname.'='.$_REQUEST[$fieldname].'&todate='.$_REQUEST['todate'].'&print=1';?>">Print Preview</a>
&nbsp &nbsp &nbsp
<?php }
$sqlcommon=' m.TxnID,SaleNo,m.Remarks,Date, m.BranchNo,CheckDetails,m.Posted,m.DateofCheck,m.PONo,m.txntype,`invty_0txntype`.txndesc as `Form`, pt.paytypedesc as PayType, e.Nickname as EncodedBy, ';
$sql0='CREATE TEMPORARY TABLE 2salemaintoday 
SELECT '.$sqlcommon.' ClientName, round(sum(s.UnitPrice*s.Qty),2)as Amount,
round(ifnull(a.Amount,0)*0.12,0) as VATCollected,concat(date_format(Date,\'%Y %b %d\'),\' - \',txndesc) as DateForm, ifnull(a.Amount,"") AS Overprice FROM invty_2sale m join invty_2salesub as s on m.TxnID=s.TxnID 
INNER JOIN invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid join `1clients` as c on c.ClientNo=m.ClientNo
join invty_0paytype pt on pt.paytypeid=m.PaymentType
left join `1employees` as e on e.IDNo=m.EncodedByNo
left join `invty_7opapproval` a on a.TxnID=m.TxnID
WHERE (('.$txndate.') AND ((m.BranchNo)='.$_SESSION['bnum'].')) Group by m.TxnID 
union all
SELECT '.$sqlcommon.' ClientName,0 as Amount, 0 as VATCollected, concat(date_format(Date,\'%Y %b %d\'),\' - \',txndesc) as DateForm, 0 AS Overprice FROM invty_2sale m left join invty_2salesub as s on m.TxnID=s.TxnID 
INNER JOIN invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid join `1clients` as c on c.ClientNo=m.ClientNo
join invty_0paytype pt on pt.paytypeid=m.PaymentType
left join `1employees` as e on e.IDNo=m.EncodedByNo WHERE (('.$txndate.') AND ((m.BranchNo)='.$_SESSION['bnum'].') and s.TxnSubId is null) Group by m.TxnID
UNION ALL
SELECT '.$sqlcommon.' CONCAT(c.FirstName," ",c.Surname) AS Employee, round(sum(s.UnitPrice*s.Qty),2)as Amount,
0 as VATCollected,concat(date_format(Date,\'%Y %b %d\'),\' - \',txndesc) as DateForm, 0 AS Overprice FROM invty_2sale m join invty_2salesub as s on m.TxnID=s.TxnID
LEFT JOIN invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid join `1employees` as c on c.IDNo=m.ClientNo
join invty_0paytype pt on pt.paytypeid=10
left join `1employees` as e on e.IDNo=m.EncodedByNo
WHERE (('.$txndate.') AND ((m.BranchNo)='.$_SESSION['bnum'].')) Group by m.TxnID 
;
';

//if($_SESSION['(ak0)']==1002){  echo $sql0;}

$stmt=$link->prepare($sql0); $stmt->execute();

if ($perday<>2){        
$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Date, Form, SaleNo');
$sql='Select * from 2salemaintoday '.$returns.' Order By '.$sortfield;

// echo $sql;
//$process1='addeditsale.php?';$addlfield='txntype';$processlabel1='Lookup'; 
    $editprocess='addeditsale.php?TxnID=';$addlfield='txntype';$editprocesslabel='Lookup'; $width='80%';
if (!isset($_REQUEST['print'])) {include('../backendphp/layout/displayastablenosort.php');}
else { unset($sortfield);$hidecontents=1; $formdesc='<a href="javascript:window.print()">'.$formdesc.'</a>'; include_once('../backendphp/layout/displayastablenosort.php');}
} else {
   
$sql1='Select concat(date_format(Date,\'%Y %b %d\'),\' - \',Form) as DateForm from 2salemaintoday group by Date, Form';
$sql2='Select *, (case when length(CheckDetails)>2 then DateofCheck else \'\' end) as CheckDate  from 2salemaintoday';
$orderby=' Order By Form, SaleNo';
$skipmainswitch=true; $coltototal='Amount'; 
$groupby='DateForm';   
if (!isset($_REQUEST['print'])) {include('../backendphp/layout/displayastablewithsub.php');} else 
{   unset($sortfield);$hidecontents=1; $formdesc='<a href="javascript:window.print()">'.$formdesc.'</a>'; 
    include('../backendphp/layout/displayastablewithsub.php');}
}
$sql='Select PayType, Form, FORMAT(SUM(Amount),2) AS TotalSalesNoOP, FORMAT(SUM(VATCollected),2) AS VATCollected, FORMAT(SUM(Overprice),2) AS Overprice,'
        // . 'FORMAT(SUM(Amount+Overprice),2) AS `Total with OP` from 2salemaintoday group by txntype'; 
        . 'FORMAT(SUM(Amount+Overprice),2) AS `Total with OP` from 2salemaintoday group by PayType'; 
if ($perday==1){ // per DAY
    $columnnames=array('PayType','Form','TotalSalesNoOP','VATCollected','Overprice');
} elseif(allowedToOpen(7653,'1rtc')){
$columnnames=array('PayType','Form','TotalSalesNoOP','VATCollected'); } else { $columnnames=array();}
if(allowedToOpen(7652,'1rtc')) { array_push($columnnames,'Total with OP');}
unset($sortfield,$editprocess,$editprocesslabel); $txnidname='PayType';
include('../backendphp/layout/displayastableonlynoheaders.php');
  $link=null; $stmt=null;
?>