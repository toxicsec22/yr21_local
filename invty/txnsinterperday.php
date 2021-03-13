<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=true;
include_once('../switchboard/contents.php');
 



$txnid='TxnID';
$whichqry=$_GET['w'];
$pagetouse='txnsinterperday.php?w='.$whichqry;
include_once('../backendphp/layout/linkstyle.php');
if (allowedToOpen(761,'1rtc') and $_GET['w']!='Request') { 
echo'<a id="link" href="txnsinterperday.php?w=Transfers&perday=1">Interbranch Transfers Summary</a> '.str_repeat('&nbsp;',5).'';
echo'<a id="link" href="txnsinterperday.php?w=TransferWithoutLookup">Interbranch Transfers Full Details</a> '.str_repeat('&nbsp;',5).'';

echo'</br>';
}
switch ($whichqry){
	

case'TransferWithoutLookup':
if (!allowedToOpen(761,'1rtc')){   echo 'No permission'; exit;}
echo'<title>Interbranch Transfers Full Details</title></br><h3>Interbranch Transfers Full Details</h3>';

echo'</br><form method="post" action="txnsinterperday.php?w=TransferWithoutLookup">
		From month (1 - 12): <input type="text" name="From" size="1"">
		To: <input type="text" name="To" size="1"">
		<input type="submit" name="filter" value="Lookup">
	</form>';
	
if(isset($_POST['filter'])){
$addedcondition='and (month(DateIN) between \''.$_POST['From'].'\' and \''.$_POST['To'].'\')';	
echo'</br><b> From month:</b> '.$_POST['From'].' <b>To:</b> '.$_POST['To'].'';
}else{
$addedcondition='and month(DateIN)='.date('m').'';
echo '</br><b>Month:</b> '.date('m').'';
}
$sql0='Create temporary Table Hiraman (
		TxnID int(11),
        DateOUT date not null,
        DateIN date not null,
        TransferNo varchar(50) not null,
        ForRequestNo varchar(50) not null,
        FROMBranch varchar(50) not null,
        TOBranch varchar(50) not null,
        Remarks varchar(50) not null,
		Waybill varchar(45) not null,
		Posted tinyint(1) not null,
		PostedIn tinyint(1) not null
    )
    SELECT t.TxnID,DateOUT,DateIN,TransferNo,ForRequestNo,b1.Branch as FROMBranch,b2.Branch as TOBranch,Remarks,Waybill,Posted,PostedIn FROM invty_2transfer t 
	join `1branches` b1 on b1.BranchNo=t.BranchNo
	join `1branches` b2 on b2.BranchNo=t.ToBranchNo
	where b1.PseudoBranch<>2 and t.BranchNo<>'.$_SESSION['bnum'].' AND ToBranchNo='.$_SESSION['bnum'].' '.$addedcondition.'';
	// echo $sql0; exit();
$stmt0=$link->prepare($sql0); $stmt0->execute();

	$title='';
	$sql1='select TxnID,CONCAT("DateOUT: ",DateOUT) as DateOUT, CONCAT("DateIN: ",DateIN) as DateIN,
		   CONCAT("TransferNo: ",TransferNo) as TransferNo, CONCAT("ForRequestNo: ",ForRequestNo) as ForRequestNo,
		   CONCAT("FROMBranch: ",FROMBranch) as FROMBranch, CONCAT("TOBranch: ",TOBranch) as TOBranch,
		   CONCAT("Remarks: ",Remarks) as Remarks, CONCAT("Waybill: ",Waybill) as Waybill,
		   CONCAT("Posted: ",Posted) as Posted, CONCAT("PostedIn: ",PostedIn) as PostedIn

	from Hiraman';
    $sql2='SELECT s.ItemCode,c.Category, i.ItemDesc, i.Unit, QtySent,UnitPrice,s.UnitPrice*s.QtySent as AmountSent,QtyReceived,UnitCost, s.UnitCost*s.QtyReceived as AmountReceived,SerialNo, if(s.Defective=1,"Defective",if(s.Defective=2,"ForCheckup","Good Item")) as Defective FROM invty_2transfersub s join `invty_1items` i on i.ItemCode=s.ItemCode join `invty_1category` c on c.CatNo=i.CatNo ';
    $groupby='TxnID';
    $orderby='';
    $columnnames1=array('DateOUT','DateIN','TransferNo','ForRequestNo','FROMBranch','TOBranch','Remarks','Waybill','Posted','PostedIn');
    $columnnames2=array('ItemCode','Category','ItemDesc','Unit','QtySent','UnitPrice','AmountSent','QtyReceived','UnitCost','AmountReceived','SerialNo','Defective');
	
	include('../backendphp/layout/displayastablewithsub.php');


exit();

break;
	
case 'Transfers':
    if (!allowedToOpen(761,'1rtc')){   echo 'No permission'; exit;}
$perday=$_GET['perday'];
//$pagetouse=$pagetouse.'&perday='.$perday;
$title='Interbranch Transfers Per Branch Per '.(($perday==1)?'Day':'Month');
$fieldname='TxnDate';
$orderby='TransferNo';
$columnnames=array('TransferNo','FROMBranch','TOBranch','DateOUT','DateIN','ForRequestNo','Remarks', 'Waybill', 'AmountSent','AmountReceived','LineItems','Posted','PostedIn');  
$method='GET';
$showbranches=true;
include_once('../backendphp/layout/clickontabletoedithead.php'); //this is ok

if (!isset($_REQUEST['print'])){
?>
<form method="post" style="display:inline" action="<?php echo $pagetouse.'&perday=1'; ?>" enctype="multipart/form-data">
                Choose Date:  <input type="date" name="TxnDate" value="<?php echo date('Y-m-d'); ?>"></input> 
                <input type="submit" name="lookup" value="Lookup"> </form>&nbsp; &nbsp; &nbsp;
<form method="post" style="display:inline" action="<?php echo $pagetouse.'&perday=0'; ?>" enctype="multipart/form-data">
                Choose Month (1 - 12):  <input type="text" name="TxnDate" value="<?php echo date('m'); ?>"></input>
    <input type="submit" name="lookup" value="Lookup"> </form>
    <table style="display: inline-block; border: 1px solid; float: left; ">
<?php
}

if (!isset($_REQUEST['TxnDate'])){
   $txndate=$perday==0?date("m"):date("Y-m-d"); //default
   
} else {

$txndate=$_REQUEST['TxnDate'];
}
if (allowedToOpen(7612,'1rtc')){?>
<form>
<input name="print" TYPE="button" onClick="window.print()" value="Print!">
</form><?php
}

$sql0='CREATE TEMPORARY TABLE 2txfrmaintoday (
TxnID	int(11)	NOT NULL,
DateOUT	date	NOT NULL,		
DateIN	date	NULL,		
TransferNo	varchar(50)	NOT NULL,
FROMBranch      varchar(50)	NOT NULL,
TOBranch      varchar(50)	NOT NULL,
ToBranchNo	smallint(6)	NOT NULL,		
ForRequestNo	varchar(50)	NULL,		
Remarks	varchar(50)	NULL,
AmountSent	varchar(50)	NOT NULL,
AmountReceived	varchar(50)	NOT NULL,
BranchNo	smallint(6)	NOT NULL,		
FROMTimeStamp	timestamp	NOT NULL,		
FROMEncodedByNo	smallint(6)	NOT NULL,
FromEncodedBy      varchar(50)	 NULL,
ToEncodedBy      varchar(50)	 NULL,
TOTimeStamp	datetime	NOT NULL,		
TOEncodedByNo	smallint(6)	NOT NULL,		
PostedByNo	smallint(6)	NOT NULL,		
Posted	tinyint(1)	NULL,
PostedIn	tinyint(1)	NULL,
Checked	tinyint(1)	NULL,		
txntype	tinyint(2)	NOT NULL)

SELECT t.*, b1.Branch as FROMBranch,  b2.Branch as TOBranch,  e1.Nickname as FromEncodedBy,  e2.Nickname as ToEncodedBy, Count(ItemCode) as LineItems, format(sum(s.UnitPrice*s.QtySent),2) as AmountSent, format(sum(s.UnitCost*s.QtyReceived),2) as AmountReceived FROM invty_2transfer as t 
join `1branches` as b1 on b1.BranchNo=t.BranchNo
join `1branches` as b2 on b2.BranchNo=t.ToBranchNo
left join `1employees` as e1 on t.FromEncodedByNo=e1.IDNo
left join `1employees` as e2 on t.ToEncodedByNo=e2.IDNo
join invty_2transfersub as s on t.TxnID=s.TxnID ';

if (allowedToOpen(7613,'1rtc')){
$tobranchcondition='';
$tobranchwithdateincondition='';
$tobranchnodateincondition='';
$monthtobranchwithdateincondition='';
} else { // show both sides
    $tobranchcondition=' AND t.ToBranchNo='.$_SESSION['bnum'];
    $tobranchwithdateincondition=' or (((t.`DateIN`)=\''.$txndate.'\') AND ((t.ToBranchNo)='.$_SESSION['bnum'].')) ';
    $tobranchnodateincondition=' OR (t.`DateIN` is null  AND t.ToBranchNo='.$_SESSION['bnum'].' ) ';
    $monthtobranchwithdateincondition=' or (Month(t.`DateIN`)=\''.$txndate.'\') AND ((t.ToBranchNo)='.$_SESSION['bnum'].') ';
}

if ($perday==1){

$sql0=$sql0.' WHERE (((t.`DateOUT`)=\''.$txndate.'\') AND ((t.BranchNo)='.$_SESSION['bnum'].')) '.$tobranchwithdateincondition.' OR (t.`DateOUT`=\''.$txndate.'\' AND t.BranchNo='.$_SESSION['bnum'].$tobranchcondition.') Group by t.TxnID

union select t.*, b1.Branch as FROMBranch,  b2.Branch as TOBranch,  "NoItemsEncoded" as FromEncodedBy,  "NoItemsEncoded" as ToEncodedBy, 0 as LineItems, 0 as AmountSent, 0 as AmountReceived FROM invty_2transfer as t 
join `1branches` as b1 on b1.BranchNo=t.BranchNo
join `1branches` as b2 on b2.BranchNo=t.ToBranchNo
left join invty_2transfersub as s on t.TxnID=s.TxnID
WHERE ((t.`DateOUT`=\''.$txndate.'\' and s.TxnSubId is null  AND t.BranchNo='.$_SESSION['bnum'].')) '.$tobranchnodateincondition.' OR (t.`DateOUT`=\''.$txndate.'\' and s.TxnSubId is null  AND t.BranchNo='.$_SESSION['bnum'].$tobranchcondition.')
GROUP BY t.TxnID
ORDER BY `TransferNo`';
} else { // per month
$sql0=$sql0.' WHERE ((Month(t.`DateOUT`)=\''.$txndate.'\') AND ((t.BranchNo)='.$_SESSION['bnum'].')) '.$monthtobranchwithdateincondition.' OR (Month(t.`DateOUT`)=\''.$txndate.'\' AND t.BranchNo='.$_SESSION['bnum'].$tobranchcondition.') Group by t.TxnID

union select t.*, b1.Branch as FROMBranch,  b2.Branch as TOBranch,  "NoItemsEncoded" as FromEncodedBy,  "NoItemsEncoded" as ToEncodedBy, 0 as LineItems, 0 as AmountSent, 0 as AmountReceived FROM invty_2transfer as t 
join `1branches` as b1 on b1.BranchNo=t.BranchNo
join `1branches` as b2 on b2.BranchNo=t.ToBranchNo
left join invty_2transfersub as s on t.TxnID=s.TxnID
WHERE ((Month(t.`DateOUT`)=\''.$txndate.'\' and s.TxnSubId is null  AND t.BranchNo='.$_SESSION['bnum'].')) OR (Month(t.`DateOUT`)=\''.$txndate.'\' and s.TxnSubId is null  AND t.BranchNo='.$_SESSION['bnum'].$tobranchcondition.')
GROUP BY t.TxnID
ORDER BY `TransferNo`';
    
}
// echo $sql0;break;
$stmt=$link->prepare($sql0);
	$stmt->execute();
        
$sql='Select * from 2txfrmaintoday ';

// echo $sql;
$process1='addedittxfr.php?w='.$whichqry.'&';
//$addlfield='txntype';
$processlabel1='Lookup';
//To encode waybill:
if (allowedToOpen(7611,'1rtc')){
$inputprocess='waybill.php?w=AddWaybill&'; $inputprocesslabel='Waybill No.:'; $inputname='Waybill';
}
break;
case 'Request':
$title='Undelivered Requests Per Branch';
$fieldname='w';
$orderby='RequestNo';
$columnnames=array('RequestNo','SupplierBranch','RequestingBranch','Date','DateRequired','LineItems','Posted');  
$method='GET';
$showbranches=true;
include_once('../backendphp/layout/clickontabletoedithead.php');
$sql='select ud.ReqTxnID as TxnID, ud.Date, ud.DateRequired, ud.RequestNo, b1.`Branch` as SupplierBranch, b2.`Branch` as RequestingBranch, count(`ItemCode`) as LineItems, ud.Posted from invty_44undeliveredreq as ud
        join `1branches` as b1 on ud.SupplierBranchNo=b1.BranchNo
        join `1branches` as b2 on ud.BranchNo=b2.BranchNo
        where (RcvBal<>0 and ud.BranchNo='.$_SESSION['bnum'] . ') or (SendBal<>0 and ud.SupplierBranchNo='.$_SESSION['bnum'] . ') group by ud.SupplierBranchNo, ud.RequestNo, ud.BranchNo
        union  SELECT br.TxnID, Date, DateReq AS DateRequired, RequestNo, b1.`Branch` as SupplierBranch, b2.`Branch` as RequestingBranch, 0 as LineItems, br.Posted FROM invty_3branchrequest br join `1branches` as b1 on br.SupplierBranchNo=b1.BranchNo
        join `1branches` as b2 on br.BranchNo=b2.BranchNo left join invty_3branchrequestsub brs on br.TxnID=brs.TxnID where brs.ItemCode is null ';
$process1='addedittxfr.php?w='.$whichqry.'&';
$processlabel1='Lookup';
    break;
}
include_once('../backendphp/layout/clickontabletoeditbody.php');
         $link=null; $stmt=null;
?>