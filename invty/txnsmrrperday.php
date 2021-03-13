<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=true;
include_once('../switchboard/contents.php');
 



$txnid='TxnID';
$whichqry=$_GET['w'];
$pagetouse='txnsmrrperday.php?w='.$whichqry;
$show=!isset($show)?0:$show;

// check if allowed
$allowed=array(762,763,764);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check

if (allowedToOpen(7621,'1rtc')) {
   //include('../backendphp/layout/showallbranchesbutton.php'); 	DIDN'T WORK AS PLANNED YET
}  else { // branches
   $show=0;
}
$condition=($show==1)?'':' and m.BranchNo='.$_SESSION['bnum'];
//$orderby=($show==1)?' order by b.Branch, MRRNo ':' order by MRRNo, Date ';
switch ($whichqry){
case 'MRR':
if (!allowedToOpen(762,'1rtc')){   echo 'No permission'; exit;}
$title='Materials Receiving Report';
$fieldname='Date';
$skipsql=true;
$perday=$_GET['perday'];
$lookupprocess='txnsmrrperday.php?w=MRR&perday='.$perday;
$fieldname='TxnDate';
$method='GET';

//include_once('../backendphp/layout/clickontabletoedithead.php');
?><br><br>
<form method="post" style="display:inline" action="<?php echo 'txnsmrrperday.php?w=MRR&perday=1'; ?>" enctype="multipart/form-data">
                Choose Date:  <input type="date" name="Date" value="<?php echo date('Y-m-d'); ?>"></input> 
                <input type="submit" name="lookup" value="Lookup"> </form>&nbsp; &nbsp; &nbsp;
<form method="post" style="display:inline" action="<?php echo 'txnsmrrperday.php?w=MRR&perday=0'; ?>" enctype="multipart/form-data">
                Choose Month (1 - 12):  <input type="text" name="Month" value="<?php echo date('m'); ?>"></input>
    <input type="submit" name="lookup" value="Lookup"> </form><br><br>
   <?php
if ($perday==1){
   $txndate=(isset($_POST[$fieldname])?$_POST[$fieldname]:date("Y-m-d"));
   $datecondition=' m.Date=\''.$txndate.'\' ';
   $columnnames=array('MRRNo','SupplierName','LineItems','Amount','ForPONo','Posted');
   $orderby='MRRNo';
} else {
   $txndate=(isset($_POST['Month'])?$_POST['Month']:date("m"));
   $datecondition=' Month(m.Date)='.$txndate.' ';
   $columnnames=array('Date','MRRNo','SupplierName','LineItems','Amount','ForPONo','Posted');
   $orderby='Date,MRRNo';
}
if ($show==1){$columnnames[]='BranchNo';}
$sql='SELECT m.*, s.SupplierName, COUNT(ms.ItemCode) AS LineItems, SUM(UnitCost*Qty) AS Amount FROM invty_2mrr m JOIN invty_2mrrsub ms ON m.TxnID=ms.TxnID
         JOIN `1suppliers` AS s ON s.SupplierNo=m.SupplierNo WHERE (txntype=6 OR  txntype=8) AND '.$datecondition.$condition.' GROUP BY MRRNo 
	UNION  SELECT m.*,s.SupplierName, COUNT(ms.ItemCode) as LineItems, SUM(UnitCost*Qty) AS Amount FROM invty_2mrr m LEFT JOIN invty_2mrrsub ms on m.TxnID=ms.TxnID
	LEFT JOIN `1suppliers` AS s ON s.SupplierNo=m.SupplierNo WHERE (txntype=6 OR  txntype=8) '.$condition.' AND '.$datecondition.' AND ms.TxnID IS NULL	
	';
	//echo $sql;
$editprocess='addeditmrr.php?w='.$whichqry.'&TxnID=';$editprocesslabel='Edit';
$processlabel1='Lookup';
include_once('../backendphp/layout/displayastablewithedit.php');
    break;

case 'PrintMRR':
if (!allowedToOpen(763,'1rtc')){   echo 'No permission'; exit;}
$title='Print MRR';
?>
<form action="printmrr.php?w=MRR" method='POST'>
From MRR Number:  <input type=text size=20 name='MRRFrom' autocomplete='off'><br>
To MRR Number:  <input type=text size=20 name='MRRTo' autocomplete='off'><br>
<input type=submit name=submit value='Print preview'>
</form>
<?php
break;

case 'StoreUsed':
if (!allowedToOpen(764,'1rtc')){
   echo 'No permission'; exit;
}
$title='Store Used';
$fieldname='Date';
$skipsql=true;
$perday=$_GET['perday'];
$lookupprocess='txnsmrrperday.php?txntype=9&w=StoreUsed&perday='.$perday;
$fieldname='TxnDate';
$method='GET';
//$showbranches=true;
//include_once('../backendphp/layout/clickontabletoedithead.php');
?>
<form method="post" action="<?php echo $lookupprocess; ?>" enctype="multipart/form-data">
        <?php if ($perday==1){ ?>
                Choose Date:  <input type="date" name="<?php echo $fieldname; ?>" value="<?php echo date('Y-m-d'); ?>"></input>         
        <?php } else { ?>
                Choose Month (1 - 12):  <input type="text" name="<?php echo $fieldname; ?>" value="<?php echo date('m'); ?>"></input>
        <?php } ?>    
    <input type="submit" name="lookup" value="Lookup"> </form>
   <?php
if ($perday==1){
   $txndate=(isset($_POST[$fieldname])?$_POST[$fieldname]:date("Y-m-d"));
   $datecondition=' m.Date=\''.$txndate.'\' ';
   $columnnames=array('MRRNo','LineItems','Amount','ApprovalNo','ForPONo','Posted');
   if ($show==1){$columnnames[]='BranchNo';}
   $orderby='MRRNo';
} else {
   $txndate=(isset($_POST[$fieldname])?$_POST[$fieldname]:date("m"));
   $datecondition=' Month(m.Date)='.$txndate.' ';
   $columnnames=array('Date','MRRNo','LineItems','Amount','ApprovalNo','ForPONo','Posted');
   if ($show==1){$columnnames[]='BranchNo';}
   $orderby='Date,MRRNo';
}
$sql='select m.*, SuppInvNo as ApprovalNo,count(ms.ItemCode) as LineItems, sum(UnitCost*Qty) as Amount from invty_2mrr m join invty_2mrrsub ms on m.TxnID=ms.TxnID
        where (txntype=9) and '.$datecondition.$condition.' group by MRRNo 
	union select m.*, SuppInvNo as ApprovalNo,count(ms.ItemCode) as LineItems, sum(UnitCost*Qty) as Amount from invty_2mrr m join invty_2mrrsub ms on m.TxnID=ms.TxnID
        join `1branches` b on b.BranchNo=m.BranchNo where txntype=9 '.$condition.' and '.$datecondition.' group by MRRNo
	union  select m.*,SuppInvNo as ApprovalNo,count(ms.ItemCode) as LineItems, sum(UnitCost*Qty) as Amount from invty_2mrr m left join invty_2mrrsub ms on m.TxnID=ms.TxnID
	where txntype=9 '.$condition.' and '.$datecondition.' and ms.TxnID is null	
	';
	//echo $sql;
$editprocess='addeditmrr.php?w='.$whichqry.'&TxnID=';$editprocesslabel='Edit';
$processlabel1='Lookup';
include_once('../backendphp/layout/displayastablewithedit.php');
    break;
}
  $link=null; $stmt=null;
?>