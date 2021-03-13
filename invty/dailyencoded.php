<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(714,'1rtc')) {   echo 'No permission'; exit;}
$showbranches=true; 
include_once('../switchboard/contents.php');
 


    
    $title='Daily Encoded';
    $whichqry=isset($_REQUEST['w'])?$_REQUEST['w']:'Cash Sales & Returns';
    //$pagetouse='dailyencoded.php?w='.$whichqry;
    $fieldname='Date';
    
if (!isset($_REQUEST['print'])) { include_once('../backendphp/layout/clickontabletoedithead.php');
?>
<form method="post" action="#" enctype="multipart/form-data">
                Choose Date:  <input type="date" name="<?php echo $fieldname; ?>" value="<?php echo date('Y-m-d'); ?>"></input>&nbsp &nbsp           
<input type="submit" name="w" value="Cash Sales & Returns">&nbsp &nbsp
<input type="submit" name="w" value="Charge Sales">&nbsp &nbsp
<input type="submit" name="w" value="Transfer OUT">&nbsp &nbsp
<input type="submit" name="w" value="Transfer IN">&nbsp &nbsp
<input type="submit" name="w" value="Collection Receipts">&nbsp &nbsp
<input type="submit" name="w" value="Deposits">&nbsp &nbsp
<input type="submit" name="w" value="Store Used">&nbsp &nbsp
   </form>

<?php
} else { //if print==1
    if (allowedToOpen(7141,'1rtc')){
        $title='<center>'.$_SESSION['@brn'].' - '.$whichqry.'</center>';
    }
}
if (isset($_REQUEST[$fieldname])){
  //  echo '<table style="display: inline-block; border: 1px solid; float: left; "><br><br>';
    $title.=' - '.$_SESSION['@brn'].': '.$whichqry;
$formdesc=$_REQUEST[$fieldname].'<br>';
$txndate='m.Date=\''.$_REQUEST[$fieldname].'\'';
$columnnames=array('SaleNo','ClientName','Remarks','Form','PayType','Posted','CheckDetails','DateofCheck','PONo', 'Amount');
if (allowedToOpen(7141,'1rtc') and !isset($_REQUEST['print'])){
?>
        <br><a href="<?php echo 'printchargesales.php?'.$fieldname.'='.$_REQUEST[$fieldname].'&w='.$_REQUEST['w'].'&print=1';?>">Print Preview</a>&nbsp &nbsp &nbsp
<?php }
} 
if (!isset($_REQUEST['w']) or !isset($_REQUEST[$fieldname])){
    goto noform;
}


switch ($whichqry){
    case 'Cash Sales & Returns':
    
     
    $sql1='SELECT m.TxnID,concat(`invty_0txntype`.txndesc,": ",`SaleNo`) as `Form`, concat("Client: ",ClientName) as ClientName, concat("PaymentType: ",(Case when PaymentType=1 then \'Cash\' when PaymentType=2 then \'Charge\' when PaymentType=33 then \'Paymaya\' when PaymentType=32 then \'GCash\' else \'Return\'  end)) as PayType, concat("Remarks: ", m.Remarks) as Remarks, concat("CheckDetails: ", CheckDetails) as CheckDetails, concat("DateofCheck: ", DateofCheck) as DateofCheck, concat("PONo: ", PONo) as PONo, concat("EncodedBy: ", Nickname) as EncodedBy, concat("Posted: ", Posted) as Posted FROM invty_2sale m 
join invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid join `1clients` as c on c.ClientNo=m.ClientNo
left join `1employees` as e on e.IDNo=m.EncodedByNo WHERE ((Date=\''.$_REQUEST[$fieldname].'\') AND (m.BranchNo='.$_SESSION['bnum'].') and txntype in (1,3,5,10))  ORDER BY txntype, SaleNo';
    $sql2uniop='create temporary table subwithop as SELECT s.TxnID, s.TxnSubId, s.ItemCode, s.Qty, s.UnitPrice, Qty*UnitPrice as Amount, Category, ItemDesc, Unit, s.SerialNo, Nickname as EncodedBy FROM invty_2salesub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e on e.IDNo=s.EncodedByNo
    union all SELECT a.TxnID,  a.TxnSubId, 0 as ItemCode, 0 as Qty, 0 as UnitPrice, if(m.PaymentType=1,round(a.Amount*0.12,0),a.Amount) as Amount, "" as Category, "Overprice" as ItemDesc, "" as Unit, "" as SerialNo, Nickname as EncodedBy FROM `invty_7opapproval` a join invty_2sale m on a.TxnID=m.TxnID left join `1employees` as e on e.IDNo=a.EncodedByNo
    '; // echo $sql2uniop; break;
    $stmt=$link->prepare($sql2uniop);
    $stmt->execute();
    
    $sql2='Select * from subwithop';

    $coltototal='Amount'; $showgrandtotal=true;
    $groupby='TxnID';
    $orderby=' Order by TxnSubId';
    $columnnames1=array('Form','ClientName','Remarks','CheckDetails','DateofCheck','PONo','PayType','EncodedBy','Posted');
    $columnnames2=array('ItemCode','Category','ItemDesc','Qty','Unit','UnitPrice','Amount','SerialNo','EncodedBy');
        break;
    case 'Charge Sales':
     

    $sql1='SELECT m.TxnID,concat(`invty_0txntype`.txndesc,": ",`SaleNo`) as `Form`, concat("Client: ",ClientName) as ClientName, concat("PaymentType: ",(Case when PaymentType=1 then \'Cash\' when PaymentType=2 then \'Charge\' when PaymentType=33 then \'Paymaya\' when PaymentType=32 then \'GCash\' else \'Return\'  end)) as PayType, concat("Remarks: ", m.Remarks) as Remarks, concat("CheckDetails: ", CheckDetails) as CheckDetails, concat("DateofCheck: ", DateofCheck) as DateofCheck, concat("PONo: ", PONo) as PONo, concat("EncodedBy: ", Nickname) as EncodedBy, concat("Posted: ", Posted) as Posted FROM invty_2sale m 
join invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid join `1clients` as c on c.ClientNo=m.ClientNo
left join `1employees` as e on e.IDNo=m.EncodedByNo WHERE ((Date=\''.$_REQUEST[$fieldname].'\') AND (m.BranchNo='.$_SESSION['bnum'].') and txntype in (2))  ORDER BY txntype, SaleNo';
    //$sql2='SELECT s.*, Qty*UnitPrice as Amount, Category, ItemDesc, Unit, Nickname as EncodedBy FROM invty_2salesub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e on e.IDNo=s.EncodedByNo ';
    $sql2uniop='create temporary table subwithop as SELECT s.TxnID, s.TxnSubId, s.ItemCode, s.Qty, s.UnitPrice, Qty*UnitPrice as Amount, Category, ItemDesc, Unit, s.SerialNo, Nickname as EncodedBy FROM invty_2salesub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e on e.IDNo=s.EncodedByNo
    union all SELECT a.TxnID,  a.TxnSubId, 0 as ItemCode, 0 as Qty, 0 as UnitPrice, if(m.PaymentType=1,round(a.Amount,0),a.Amount) as Amount, "" as Category, "Overprice" as ItemDesc, "" as Unit, "" as SerialNo, Nickname as EncodedBy FROM `invty_7opapproval` a join invty_2sale m on a.TxnID=m.TxnID left join `1employees` as e on e.IDNo=a.EncodedByNo
    '; // echo $sql2uniop; break;
    $stmt=$link->prepare($sql2uniop);
    $stmt->execute();
    
    $sql2='Select * from subwithop';

    $coltototal='Amount'; $showgrandtotal=true;
    $groupby='TxnID';
    $orderby=' Order by TxnSubId';
    if (!isset($_REQUEST['print'])){
    $columnnames1=array('Form','ClientName','Remarks','CheckDetails','DateofCheck','PONo','PayType','EncodedBy','Posted');
    $columnnames2=array('ItemCode','Category','ItemDesc','Qty','Unit','UnitPrice','Amount','SerialNo','EncodedBy');
    } else {
        $columnnames1=array('Form','ClientName','Remarks','CheckDetails','DateofCheck','PONo');
    $columnnames2=array('ItemCode','Category','ItemDesc','Qty','Unit','UnitPrice','Amount','SerialNo');
    }
        break;
    
    case 'Transfer OUT':
    
    $sql1='SELECT t.*, b1.Branch as FROMBranch,  b2.Branch as TOBranch,  e1.Nickname as FromEncodedBy FROM invty_2transfer as t 
join `1branches` as b1 on b1.BranchNo=t.BranchNo
join `1branches` as b2 on b2.BranchNo=t.ToBranchNo
left join `1employees` as e1 on t.FromEncodedByNo=e1.IDNo WHERE ((DateOUT=\''.$_REQUEST[$fieldname].'\') AND (t.BranchNo='.$_SESSION['bnum'].')) ORDER BY txntype, TransferNo';
    $sql2='SELECT s.*, QtySent*UnitPrice as Amount, Category, ItemDesc, Unit, Nickname as EncodedBy FROM invty_2transfersub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e on e.IDNo=s.FromEncodedByNo ';

    $coltototal='Amount';
    $groupby='TxnID';
    $orderby=' Order by TxnSubId';
    $columnnames1=array('TransferNo','FROMBranch','TOBranch','Remarks','ForRequestNo','FromEncodedBy','Posted');
    $columnnames2=array('ItemCode','Category','ItemDesc','QtySent','Unit','UnitPrice','Amount','SerialNo','EncodedBy');
        break;
    
    case 'Transfer IN':
   
    
    $sql1='SELECT t.*, b1.Branch as FROMBranch,  b2.Branch as TOBranch,  e1.Nickname as FromEncodedBy FROM invty_2transfer as t 
join `1branches` as b1 on b1.BranchNo=t.BranchNo
join `1branches` as b2 on b2.BranchNo=t.ToBranchNo
left join `1employees` as e1 on t.FromEncodedByNo=e1.IDNo WHERE ((DateOUT=\''.$_REQUEST[$fieldname].'\') AND (t.ToBranchNo='.$_SESSION['bnum'].'))  ORDER BY txntype, TransferNo';
    $sql2='SELECT s.*, QtySent*UnitPrice as Amount, Category, ItemDesc, Unit, Nickname as EncodedBy FROM invty_2transfersub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e on e.IDNo=s.FromEncodedByNo ';

    $coltototal='Amount';
    $groupby='TxnID';
    $orderby=' Order by TxnSubId';
    $columnnames1=array('TransferNo','FROMBranch','TOBranch','Remarks','ForRequestNo','FromEncodedBy','Posted');
    $columnnames2=array('ItemCode','Category','ItemDesc','QtySent','Unit','UnitPrice','Amount','SerialNo','EncodedBy');
        break;
    
    case 'Collection Receipts':
          
        
    $sql1='select m.TxnID,m.CollectNo, c.ClientName, m.Date, concat("CheckNo: ",m.CheckNo) as CheckNo, concat("DateofCheck: ",m.DateofCheck) as DateofCheck, e.Nickname as ReceivedBy, concat("Posted: ",m.Posted) as Posted, if(m.DebitAccountID=100,\'Cash\',\'Check\') as PaymentType from acctg_2collectmain as m join `1employees` e on e.IDNo=m.ReceivedBy
left join `1clients` c on c.ClientNo=m.ClientNo
where m.BranchSeriesNo='.$_SESSION['bnum'].' and m.Date=\''.$_REQUEST[$fieldname].'\' and m.Date>\''.$_SESSION['nb4'].'\'';
    
$sql0='CREATE TEMPORARY TABLE `sub` AS SELECT s.TxnID, s.ForChargeInvNo, OtherORDetails, Amount, b.Branch FROM acctg_2collectsub s JOIN `1branches` b on b.BranchNo=s.BranchNo JOIN `acctg_2collectmain` m ON m.TxnID=s.TxnID WHERE m.BranchSeriesNo='.$_SESSION['bnum'].' and m.Date=\''.$_REQUEST[$fieldname].'\' and m.Date>\''.$_SESSION['nb4'].'\' 
UNION ALL
SELECT s.TxnID, "",`DeductDetails`,`Amount`*-1, b.Branch FROM `acctg_2collectsubdeduct` s JOIN `1branches` b on b.BranchNo=s.BranchNo JOIN `acctg_2collectmain` m ON m.TxnID=s.TxnID WHERE m.BranchSeriesNo='.$_SESSION['bnum'].' and m.Date=\''.$_REQUEST[$fieldname].'\' and m.Date>\''.$_SESSION['nb4'].'\'';

$stmt=$link->prepare($sql0); $stmt->execute();

$sql2='SELECT * FROM `sub`';

    $coltototal='Amount'; 
    $groupby='TxnID';
    $orderby=' ';
    $columnnames1=array('CollectNo','Date','ClientName','PaymentType','CheckNo','DateofCheck','ReceivedBy','Posted');
    $columnnames2=array('Branch','ForChargeInvNo','OtherORDetails','Amount');
        
        break;
    
    case 'Deposits':
     
        
    $sql1='select m.Date, m.TxnID, m.DepositNo, ca.ShortAcctID as Bank, concat("Posted: ",m.Posted) as Posted from acctg_2depositmain m
    join acctg_2depositsub s on m.TxnID=s.TxnID join acctg_1chartofaccounts ca on ca.AccountID=m.DebitAccountID where m.Date=\''.$_REQUEST[$fieldname].'\' and s.BranchNo='.$_SESSION['bnum'].' and m.Date>\''.$_SESSION['nb4'].'\' GROUP BY DepositNo ORDER BY DepositNo';
    $sql0='create temporary table depsub(
        TxnID int(11) not null,
        BranchNo smallint(6) not null,
        ClientorEncash varchar(100) null,
        DepDetails  varchar(100) null,
        ForChargeInvNo varchar(100) null,
        DepositType varchar(100) null,
        CheckNo varchar(100) null,
        Amount double not null
    )
    select s.TxnID,BranchNo, c.ClientName as ClientorEncash, DepDetails, ForChargeInvNo, (case when Type=0 then "Cash" else (case when Type=1 then "ORCash" else "Check" end) end) as DepositType, CheckNo, Amount  from acctg_2depositsub s
    join acctg_2depositmain m on m.TxnID=s.TxnID
    join `1clients` c on c.ClientNo=s.ClientNo
    where m.Date=\''.$_REQUEST[$fieldname].'\' and m.Date>\''.$_SESSION['nb4'].'\'
    union all
select de.TxnID,BranchNo, EncashDetails as ClientorEncash, if(ApprovalNo<>0,concat("Approval: ",ApprovalNo),"") as DepDetails, "" as ForChargeInvNo, "Encashment" as DepositType, "" as CheckNo, Amount*-1 as Amount from acctg_2depencashsub de
join acctg_2depositmain m on m.TxnID=de.TxnID
where m.Date=\''.$_REQUEST[$fieldname].'\' and m.Date>\''.$_SESSION['nb4'].'\'
';
// echo $sql0; break;
$stmt=$link->prepare($sql0);
$stmt->execute();
$sql2='Select * from depsub ';

    $coltototal='Amount'; 
    $groupby='TxnID';
    $orderby=' ';
    $columnnames1=array('DepositNo','Bank','Posted');
    $columnnames2=array('BranchNo','ClientorEncash','DepDetails','ForChargeInvNo','DepositType','CheckNo','Amount');
        break;
    
    case 'Store Used':
    

    $sql1='SELECT m.TxnID,concat(`invty_0txntype`.txndesc,": ",`MRRNo`) as `Form`, concat("Approval No: ",SuppInvNo) as Approval, m.Remarks, concat("EncodedBy: ", Nickname) as EncodedBy, concat("Posted: ", Posted) as Posted FROM invty_2mrr m 
join invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid join `1branches` b on b.BranchNo=m.BranchNo
left join `1employees` as e on e.IDNo=m.EncodedByNo WHERE ((Date=\''.$_REQUEST[$fieldname].'\') AND (m.BranchNo='.$_SESSION['bnum'].') and txntype in (9))  ORDER BY txntype, MRRNo';
    $sql2='SELECT s.*, Qty*UnitCost as Amount, Category, ItemDesc, Unit, Nickname as EncodedBy FROM invty_2mrrsub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e on e.IDNo=s.EncodedByNo ';

    $coltototal='Amount'; 
    $groupby='TxnID';
    $orderby=' Order by TxnSubId';
    $columnnames1=array('Form','Approval','Remarks','EncodedBy','Posted');
    $columnnames2=array('ItemCode','Category','ItemDesc','Qty','Unit','UnitCost','Amount','SerialNo','EncodedBy');
    
        break;
}

if (!isset($_REQUEST['print'])) {include('../backendphp/layout/displayastablewithsub.php');} else 
{   unset($sortfield);$hidecontents=1; $formdesc='<a href="javascript:window.print()">'.$formdesc.'</a>'; 
    include('../backendphp/layout/displayastablewithsub.php');}

noform:
      $link=null; $stmt=null;
?>
 