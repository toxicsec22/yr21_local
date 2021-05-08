<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once('../switchboard/contents.php');
 


    
    $title='Audit Charges and Refunds';
    $perday=$_GET['perday'];
    $pagetouse='txnspermonthaudit.php?perday='.$perday;
    $fieldname=($perday==0?'Month':'Date');
    
    
$txnidname='TxnID';
$method='GET';
$showbranches=true;
include_once('../backendphp/layout/clickontabletoedithead.php'); //this is ok
if (!allowedToOpen(768,'1rtc')) {   echo 'No permission'; exit;} 
if (!isset($_REQUEST['print'])){
?>
<form method="post" action="<?php echo $pagetouse; ?>" enctype="multipart/form-data">
<?php if ($perday==1){ ?>
                Choose Date:  <input type="date" name="<?php echo $fieldname; ?>" value="<?php echo date('Y-m-d'); ?>"></input>         
        <?php } else { ?>
                Choose Month (1 - 12):  <input type="text" name="<?php echo $fieldname; ?>" value="<?php echo date('m'); ?>"></input>
        <?php } ?>   
<input type="submit" name="lookup" value="Lookup"> </form>
    <table style="display: inline-block; border: 1px solid; float: left; ">
<?php
}
if (isset($_REQUEST[$fieldname])){
if ($perday==0){
$formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$_POST[$fieldname].'-1')).'<br>';   
$txndate='Month(m.Date)='.$_REQUEST[$fieldname];
$columnnames=array('Date','SaleNo','FullName','Remarks','Form','Amount');
} else {
$formdesc=$_POST[$fieldname].'<br>';
$txndate='m.Date=\''.$_REQUEST[$fieldname].'\'';
$columnnames=array('SaleNo','FullName','Remarks','Form', 'Amount');
}
if (allowedToOpen(7681,'1rtc')){?>
<form>
<input name="print" TYPE="button" onClick="window.print()" value="Print!">
</form><?php
}
$sql0='CREATE TEMPORARY TABLE 2salemaintoday (
TxnID	int(11)	NOT NULL,
Date	date	NOT NULL,
SaleNo	varchar(50)	NOT NULL,
ClientNo	smallint(6)	NOT NULL,
Remarks	varchar(50)	NULL,
txntype	smallint(6)	NOT NULL,
Amount	double	NOT NULL,
TimeStamp	timestamp	NOT NULL,
BranchNo	smallint(6)	NOT NULL,
EncodedByNo	smallint(6)	NOT NULL,
PostedByNo	smallint(6)	NOT NULL,
Posted	tinyint(1)	NULL,
Form	varchar(20) NOT NULL,
FullName varchar(100) NOT NULL,
EncodedBy varchar(100)  NULL)
SELECT m.*, `invty_0txntype`.txndesc as `Form`, concat(ec.Nickname,\' \',ec.Surname) as FullName, e.Nickname as EncodedBy, round(sum(s.UnitPrice*s.Qty),2) as Amount FROM invty_2sale m join invty_2salesub as s on m.TxnID=s.TxnID 
INNER JOIN invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid left join `1employees` ec on ec.IDNo=m.ClientNo
left join `1employees` as e on e.IDNo=m.EncodedByNo WHERE (('.$txndate.') AND ((m.BranchNo)='.$_SESSION['bnum'].' and txntype=3)) Group by m.TxnID 
union 
SELECT m.*, `invty_0txntype`.txndesc as `Form`,  concat(ec.Nickname,\' \',ec.Surname) as FullName, e.Nickname as EncodedBy, 0 as Amount FROM invty_2sale m left join invty_2salesub as s on m.TxnID=s.TxnID 
INNER JOIN invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid join `1employees` ec on ec.IDNo=m.ClientNo
left join `1employees` as e on e.IDNo=m.EncodedByNo WHERE (('.$txndate.') AND ((m.BranchNo)='.$_SESSION['bnum'].' and txntype=3) and s.TxnSubId is null) Group by m.TxnID
union
SELECT m.*, `invty_0txntype`.txndesc as `Form`, concat(ec.Nickname,\' \', ec.Surname) as  FullName, e.Nickname as EncodedBy, round(sum(s.UnitPrice*s.Qty),2) as Amount FROM invty_2sale m join invty_2salesub as s on m.TxnID=s.TxnID 
INNER JOIN invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid join `1employees` ec on ec.IDNo=m.ClientNo
left join `1employees` as e on e.IDNo=m.EncodedByNo WHERE (('.$txndate.') AND ((m.BranchNo)='.$_SESSION['bnum'].' and txntype=3)) Group by m.TxnID 
';
$stmt=$link->prepare($sql0);
	$stmt->execute();
        
$sql='Select * from 2salemaintoday Order By Date, Form, SaleNo';

// echo $sql;
$process1='addeditsale.php?';
$addlfield='txntype';
$processlabel1='Lookup';
$total='';
$sqlsum='Select Form, sum(Amount) as Total from 2salemaintoday group by Form';
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetchAll();
    foreach ($result as $row){
    $total=$total.'<br>Total '.$row['Form'].' :  '.number_format($row['Total'],2);
    }
}else {
$txndate=$perday==0?date('m'):date('Y-m-d',time()); //default
}
include_once('../backendphp/layout/clickontabletoeditbody.php');
  $link=null; $stmt=null;
?>