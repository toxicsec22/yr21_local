<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(7142,'1rtc')) {   echo 'No permission'; exit;}
$showbranches=false; 
include_once('../switchboard/contents.php');
     
    $which=isset($_REQUEST['w'])?$_REQUEST['w']:'Charge Sales';
    $fieldname='Date';
    $title='';
?>
</br><h3>Charge Sales and Collections per Day</h3></br>
<form method="post" action="#" enctype="multipart/form-data">
Choose Date:  <input type="date" name="<?php echo $fieldname; ?>" value="<?php echo date('Y-m-d'); ?>"></input>&nbsp &nbsp           
<input type="submit" name="w" value="Charge Sales">&nbsp &nbsp
<input type="submit" name="w" value="Collection Receipts">&nbsp &nbsp
</form>
<?php

if (isset($_REQUEST[$fieldname])){
$title=$which;
$formdesc=$_REQUEST[$fieldname].'<br>';
} 
if (!isset($_REQUEST['w']) or !isset($_REQUEST[$fieldname])){
    goto noform;
}

switch ($which){
    case 'Charge Sales':
	$sql1='Select Branch,b.BranchNo as BranchNo from 1branches b join invty_2sale s on s.BranchNo=b.BranchNo wHERE Date=\''.$_REQUEST[$fieldname].'\' and txntype=2 Group By b.BranchNo';
    $sql2='SELECT m.TxnID,SaleNo as Form,ClientName,m.Remarks,CheckDetails, DateofCheck,PONo,format(sum(Qty*UnitPrice),2) as Amount,sum(Qty*UnitPrice) as AmountValue FROM invty_2sale m join invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid join `1clients` as c on c.ClientNo=m.ClientNo left join invty_2salesub ss on ss.TxnID=m.TxnID';
// echo $sql1; exit();
	$groupby='BranchNo';
	$orderby=' and Date=\''.$_REQUEST[$fieldname].'\' and txntype=2 group by SaleNo ORDER BY `Form`,`SaleNo`';
    $columnnames1=array('Branch');
	$columnnames2=array('Form','ClientName','Remarks','CheckDetails','DateofCheck','PONo','Amount');
	$showgrandtotal=true;
	$coltototal='AmountValue';
    break;
   
    case 'Collection Receipts':
   	$sql1='Select Branch,BranchSeriesNo from 1branches b join acctg_2collectmain cm on cm.BranchSeriesNo=b.BranchNo WHERE Date=\''.$_REQUEST[$fieldname].'\'  Group By cm.BranchSeriesNo';
	$sql2='SELECT BranchSeriesNo,CollectNo,Date,ClientName,CollectTypeDesc as PaymentType,CheckNo,DateofCheck,format(sum(Amount),2) as Amount,sum(Amount) as AmountValue FROM acctg_2collectmain cm join acctg_1collecttype cp on cp.CollectTypeID=cm.Type join `1clients` as c on c.ClientNo=cm.ClientNo left join acctg_2collectsub s on s.TxnID=cm.TxnID';
    $groupby='BranchSeriesNo';
    $orderby=' and Date=\''.$_REQUEST[$fieldname].'\' group by CollectNo';
	$columnnames1=array('Branch');
    $columnnames2=array('CollectNo','Date','ClientName','PaymentType','CheckNo','DateofCheck','Amount');  
	$showgrandtotal=true;
	$coltototal='AmountValue';
    break;
    
}
    include('../backendphp/layout/displayastablewithsub.php');

noform:
      $link=null; $stmt=null;
?>
 