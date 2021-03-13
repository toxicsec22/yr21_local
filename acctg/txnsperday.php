<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(592,593,594,595,596,597,598,599,601,6001,6002,5403);$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=true; 

include_once('../switchboard/contents.php');
 


$txnid='TxnID';
$whichqry=$_GET['w'];
$perday=$_REQUEST['perday']; 
$fieldname=($perday==0?'Month':'Date');

//Converted the long tertiary line to this if else statements, just for readability purposes.
if($whichqry != "CVBudget")
  $defaultdate = date('Y-m-d');
else if(isset($_REQUEST['Date']))
  $defaultdate = $_REQUEST['Date'];
else
  $defaultdate = date('Y-m-d', strtotime("next Friday"));

if(!isset($_GET['Date']))
  $txndate = "m.Date = '{$defaultdate}'";
else
  $txndate = "m.Date = '{$_GET['Date']}'";

$pagetouse='txnsperday.php?perday='.$perday.'&w='.$whichqry.'&Date='.(!isset($_REQUEST[$fieldname])?$defaultdate:$_REQUEST[$fieldname]);

$title=$whichqry=='Bounced'?$whichqry.' Checks':($whichqry.' Per '.$fieldname);
$method='GET';
include_once('../backendphp/layout/clickontabletoedithead.php');
if (in_array($whichqry,array('Bounced','BouncedfromCR'))){ goto skipdates;}
?>
<form method="post" style="display:inline"
      action="<?php echo 'txnsperday.php?perday=1&w='.$whichqry.'&Date='.(!isset($_REQUEST['Date'])?$defaultdate:$_REQUEST['Date']); ?>" enctype="multipart/form-data">
                Choose Date:  <input type="date" name="Date" value="<?php echo $defaultdate; ?>"></input> 
                <input type="hidden" name="perday" value="1">
                <input type="submit" name="lookup" value="Lookup Per Day">        
</form> &nbsp; &nbsp; &nbsp;
<?php if ($whichqry=='CVBudget'){ goto skipmonthandadd;} ?>

<form method="post" style="display:inline"
      action="<?php echo 'txnsperday.php?perday=0&w='.$whichqry.'&Date='.(!isset($_REQUEST['Month'])?$defaultdate:$_REQUEST['Month']); ?>" enctype="multipart/form-data">
                Choose Month (1 - 12):  <input type="text" name="Month" value="<?php echo date('m'); ?>"></input>
                <input type="hidden" name="perday" value="0">
                <input type="submit" name="lookup" value="Lookup Per Month">
</form>
<?php skipdates: echo str_repeat('<br>',2); ?><a href='addmain.php?w=<?php echo ($whichqry); ?>'  target=_blank>Add <?php echo $whichqry; ?></a>
<?php
echo str_repeat('<br>',2); 
skipmonthandadd:
if (!isset($_REQUEST[$fieldname])){
$perday=1;
$formdesc=$defaultdate.'<br>';
//goto noform;
} else {
   
if ($perday==0){
$formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$_POST[$fieldname].'-1')).'<br>';   
$txndate='Month(`m`.`Date`)='.$_REQUEST[$fieldname];
} else {
$formdesc=$_REQUEST[$fieldname].'<br>';
$txndate='m.Date=\''.$_REQUEST[$fieldname].'\'';
}
if (allowedToOpen(6001,'1rtc')){
$txndate=$txndate;
} else {
    $txndate=$txndate.' and `m`.`Date`>Date_Add(Now(),interval -7 day)';
}
}

switch ($whichqry){
case 'Sale':
if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=597;}
        if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;}
$columnnames=array('Date','PaymentType','Total','TeamLeader','Posted');  
$sql='select m.TxnID,m.Date, m.Posted, if(ss.DebitAccountID=100,\'Cash\',if(ss.DebitAccountID=509,\'OutputVAT\',\'Charge\')) as PaymentType, format(sum(ss.Amount),2) as Total, e.Nickname AS TeamLeader from acctg_2salemain as m join acctg_2salesub ss on m.TxnID=ss.TxnID
left join `1employees` e on e.IDNo=m.TeamLeader
where m.BranchNo='.$_SESSION['bnum'].' and '.$txndate .' group by Date, DebitAccountID order by Date, PaymentType';

$process1='addeditclientside.php?w='.$whichqry.'&';
$processlabel1='Lookup';
include_once('../backendphp/layout/clickontabletoeditbody.php');
    break;

case 'Collect':
    if (!allowedToOpen(6002,'1rtc')){ echo 'No permission'; exit;}
    $title='Collection Receipts';
    $columnnames=array('Date','CollectNo','ClientName','Total','CollectType','ReceivedBy','Posted');  

$sql='select m.TxnID,m.CollectNo AS CollectNo, c.ClientName, m.Date, e.Nickname as ReceivedBy, m.Posted, CONCAT(ct.`CollectTypeID`," - ",ct.`CollectTypeDesc`) AS CollectType, format(sum(s.Amount)-(select ifnull(sum(sd.Amount),0) from acctg_2collectsubdeduct sd where sd.TxnID=m.TxnID),2) as Total from acctg_2collectmain as m join acctg_2collectsub s on m.TxnID=s.TxnID left join `1employees` e on e.IDNo=m.ReceivedBy JOIN `acctg_1collecttype` ct ON ct.`CollectTypeID`=m.Type 
left join `1clients` c on c.ClientNo=m.ClientNo 
WHERE m.BranchSeriesNo='.$_SESSION['bnum'].' and '.$txndate .' group by CollectNo
UNION select m.TxnID,m.CollectNo, c.ClientName, m.Date,e.Nickname as ReceivedBy, m.Posted, CONCAT(ct.`CollectTypeID`," - ",ct.`CollectTypeDesc`) AS CollectType,0 as Total from acctg_2collectmain as m left join acctg_2collectsub s on m.TxnID=s.TxnID left join `1employees` e on e.IDNo=m.ReceivedBy
left join `1clients` c on c.ClientNo=m.ClientNo JOIN `acctg_1collecttype` ct ON ct.`CollectTypeID`=m.Type
WHERE s.TxnID is null and m.BranchSeriesNo='.$_SESSION['bnum'].' and '.$txndate .' group by CollectNo
order by CollectNo';

$process1='addeditclientside.php?w=Collect&';
$processlabel1='Lookup';

include_once('../backendphp/layout/clickontabletoeditbody.php');
    break;

case 'Deposit':
    if (!allowedToOpen(599,'1rtc')) { echo 'No permission'; exit;}
   if (allowedToOpen(6001,'1rtc')){
$sql='select m.Date, m.TxnID, m.DepositNo, ca.ShortAcctID as Bank, m.Cleared, m.Posted, format((Select sum(Amount) from acctg_2depositsub where TxnID=m.TxnID group by TxnID)-ifnull((Select sum(Amount) from acctg_2depencashsub where TxnID=m.TxnID group by TxnID),0),2) as Total, if(isnull(de.TxnID),\'no\',\'yes\') as WithEncashment from acctg_2depositmain as m join acctg_1chartofaccounts ca on ca.AccountID=m.DebitAccountID join acctg_2depositsub s on m.TxnID=s.TxnID left join acctg_2depencashsub de on m.TxnID=de.TxnID where '.$txndate .' group by m.TxnID
union select m.Date, m.TxnID, m.DepositNo, ca.ShortAcctID, m.Cleared, m.Posted, 0 as Total,null as WithEncashment from acctg_2depositmain as m join acctg_1chartofaccounts ca on ca.AccountID=m.DebitAccountID left join acctg_2depositsub s on m.TxnID=s.TxnID where s.TxnID is null and '.$txndate .' order by Date,DepositNo';
$print='<form action="printvoucher.php?w=Encashments" method="POST">
        Print Encashments FROM <input type="text" name="FromDep">  TO <input type="text" name="ToDep"> <input type="Submit" name="Print" value="Print">
    </form>';
   } else {
$sql='select m.Date, m.TxnID, m.DepositNo, ca.ShortAcctID as Bank, m.Cleared, m.Posted, format((Select sum(Amount) from acctg_2depositsub where TxnID=m.TxnID group by TxnID)-ifnull((Select sum(Amount) from acctg_2depencashsub where TxnID=m.TxnID group by TxnID),0),2) as Total, if(isnull(de.TxnID),\'no\',\'yes\') as WithEncashment from acctg_2depositmain as m join acctg_1chartofaccounts ca on ca.AccountID=m.DebitAccountID join acctg_2depositsub s on m.TxnID=s.TxnID left join acctg_2depencashsub de on m.TxnID=de.TxnID  where '.$txndate .' and s.BranchNo='.$_SESSION['bnum'].' and m.Date>\''.$_SESSION['nb4'].'\' group by m.TxnID  
union select m.Date, m.TxnID, m.DepositNo, ca.ShortAcctID, m.Cleared, m.Posted, 0 as Total,null as WithEncashment from acctg_2depositmain as m join acctg_1chartofaccounts ca on ca.AccountID=m.DebitAccountID left join acctg_2depositsub s on m.TxnID=s.TxnID where s.TxnID is null and '.$txndate .' and m.Date>\''.$_SESSION['nb4'].'\' order by Date,DepositNo';      
   }
$columnnames=$perday==1?array('DepositNo','Bank','Cleared','Total','WithEncashment','Posted'):array('Date','DepositNo','Bank','Cleared','Total','WithEncashment','Posted');
$process1='addeditdep.php?';
$processlabel1='Lookup';
include_once('../backendphp/layout/clickontabletoeditbody.php');
    break;   

    
case 'Bounced':
    if (!allowedToOpen(593,'1rtc')) { echo 'No permission'; exit; }
$showall=!isset($_POST['showall'])?0:$_POST['showall'];
// $print='<form  method="post" action="txnsperday.php?perday=0&w=BouncedfromCR">
$print='<form  method="post" action="txnsperday.php?perday=0&w=Bounced">
   <input type=hidden name="showall" value="'.($showall==0?1:0).'"><input type="submit" name="submit" value="'.($showall==0?'Show All':'Show Unpaid').'"></form>&nbsp &nbsp';
$columnnames=array('DateBounced','ClientName','CheckNo','CheckBank','Remarks','Bank','Amount','EncodedBy');  
if ($showall==0){
$title='Unpaid Bounced Checks from Collection Receipts'; 
$sql='SELECT sb.*, m.CheckNo, m.CheckBank, c.ClientName, FORMAT(SUM(s.Amount),2)  AS Amount, e.Nickname AS EncodedBy, ca.ShortAcctID AS Bank FROM acctg_2collectmain m JOIN acctg_2collectsub s ON m.TxnID=s.TxnID JOIN acctg_2collectsubbounced sb ON m.TxnID=sb.TxnID
JOIN `1clients` c ON c.ClientNo=m.ClientNo
JOIN `1employees` e ON e.IDNo=sb.EncodedByNo
JOIN `acctg_unpaidinv` un ON un.ClientNo=m.ClientNo and un.Particulars=concat("BouncedfromCR",`m`.`CollectNo`,"_",`m`.`CheckNo`," Inv",`s`.`ForChargeInvNo`)
JOIN acctg_1chartofaccounts ca ON ca.AccountID=sb.CreditAccountID
GROUP BY m.TxnID	

ORDER BY DateBounced DESC;';

} else {
$title='All Bounced Checks (from Collection Receipts) this Year'; 
$sql='SELECT sb.*, m.CheckNo, m.CheckBank, c.ClientName, FORMAT(SUM(s.Amount),2)  AS Amount, e.Nickname AS EncodedBy, ca.ShortAcctID AS Bank FROM acctg_2collectmain m JOIN acctg_2collectsub s ON m.TxnID=s.TxnID JOIN acctg_2collectsubbounced sb ON m.TxnID=sb.TxnID
JOIN `1clients` c ON c.ClientNo=m.ClientNo
JOIN `1employees` e ON e.IDNo=sb.EncodedByNo
JOIN acctg_1chartofaccounts ca ON ca.AccountID=sb.CreditAccountID
GROUP BY m.TxnID';
}
// $editprocess='addeditclientside.php?w=BouncedfromCR&TxnID=';
// $editprocesslabel='Lookup';
$process1='addeditclientside.php?w=BouncedfromCR&';
$processlabel1='Lookup';
// echo '<h4>Bounced From CR Current Year</h4>';
include_once('../backendphp/layout/clickontabletoeditbody.php');
// include('../backendphp/layout/displayastableonlynoheaders.php');
echo '<br>';

$sql='SELECT uplb.*,uplb.UndepPDCId AS TxnID, CRNo, PDCBank AS CheckBank,PDCNo AS CheckNo, PDCBank, c.ClientName, FORMAT(SUM(AmountofPDC),2)  AS Amount, e.Nickname AS EncodedBy, ca.ShortAcctID AS Bank FROM acctg_3undepositedpdcfromlastperiod upl JOIN acctg_3undepositedpdcfromlastperiodbounced uplb ON upl.UndepPDCId=uplb.UndepPDCId
JOIN `1clients` c ON c.ClientNo=upl.ClientNo
JOIN `1employees` e ON e.IDNo=uplb.EncodedByNo
JOIN acctg_1chartofaccounts ca ON ca.AccountID=uplb.CreditAccountID
GROUP BY UndepPDCId ORDER BY DateBounced DESC';


$editprocess='addeditclientside.php?w=BouncedfromCR&fromlast=1&TxnID=';
$editprocesslabel='Lookup';
echo '<h4>Bounced From CR Last Year</h4>';
include('../backendphp/layout/displayastableonlynoheaders.php');
    break;    
    
case 'Purchase':
if (!allowedToOpen(596,'1rtc')) { echo 'No permission'; exit;} else {
$showpronly=!isset($_POST['showpronly'])?0:$_POST['showpronly'];
$print='<form  method="post" action="txnsperday.php?perday=0&Month='.$_REQUEST[$fieldname].'&w=Purchase">
   <input type=hidden name="showpronly" value="'.($showpronly==0?1:0).'">
    <input type="submit" name="submit" value="'.($showpronly==0?'Show Purchase Returns':'Show All Purchases').'">
</form>&nbsp &nbsp';

if ($showpronly==0){
$columnnames=array('Date','SupplierName','InvNo','InvDate','Amount','MRRNo','ForPONo','Company','RegisteredSupplier','Remarks','Posted');  //,'Debit','Credit';ca1.ShortAcctID as Debit, ca2.ShortAcctID as Credit,; join acctg_1chartofaccounts ca1 on ca1.AccountID=ps.DebitAccountID join acctg_1chartofaccounts ca2 on ca2.AccountID=m.CreditAccountID
$sql='select m.TxnID, m.Date AS `Date`, s.SupplierName, m.SupplierInv as InvNo, m.DateofInv as InvDate, sum(ps.Amount) as Amount, sum(ps.Amount) as AmountValue, m.MRRNo, mrr.ForPONo, m.Remarks, m.Posted, co.Company, s1.SupplierName AS RegisteredSupplier from `acctg_2purchasemain` m join `acctg_2purchasesub` ps on `m`.TxnID=`ps`.TxnID
left join `1companies` co on co.CompanyNo=m.RCompany
left join `invty_2mrr` mrr ON mrr.MRRNo=m.MRRNo AND m.BranchNo=mrr.BranchNo
join `1suppliers` s on s.SupplierNo=m.SupplierNo
LEFT join `1suppliers` s1 on s1.SupplierNo=m.RegisteredSupplierNo
where '.$txndate .' and m.BranchNo='.$_SESSION['bnum'].' group by m.TxnID 
union all
select m.TxnID, m.Date, s.SupplierName, m.SupplierInv as InvNo, m.DateofInv as InvDate, sum(ps.Amount) as Amount, sum(ps.Amount) as AmountValue, m.MRRNo, mrr.ForPONo, m.Remarks, m.Posted, co.Company, s1.SupplierName AS RegisteredSupplier from `acctg_2purchasemain` m left join `acctg_2purchasesub` ps on `m`.TxnID=`ps`.TxnID
left join `1companies` co on co.CompanyNo=m.RCompany
left join `invty_2mrr` mrr ON mrr.MRRNo=m.MRRNo AND m.BranchNo=mrr.BranchNo
join `1suppliers` s on s.SupplierNo=m.SupplierNo
LEFT join `1suppliers` s1 on s1.SupplierNo=m.RegisteredSupplierNo
where '.$txndate .' and m.BranchNo='.$_SESSION['bnum'].' and (ps.TxnID is null) group by m.TxnID order by `Date`, SupplierName, InvDate';
} else {
$columnnames=array('Date','SupplierName','InvNo','InvDate','Amount','CollectNo','RegisteredSupplier','Remarks','Posted');  //,'Debit','Credit';, ca1.ShortAcctID as Debit, ca2.ShortAcctID as Credit; join acctg_1chartofaccounts ca1 on ca1.AccountID=ps.DebitAccountID join acctg_1chartofaccounts ca2 on ca2.AccountID=m.CreditAccountID
$sql='select m.TxnID, m.Date AS `Date`, s.SupplierName, m.SupplierInv as InvNo, m.DateofInv as InvDate, ps.Amount as Amount, ps.Amount as AmountValue, MRRNo as CollectNo,m.Remarks, m.Posted from `acctg_2purchasemain` m join `acctg_2purchasesub` ps on `m`.TxnID=`ps`.TxnID 
join `1suppliers` s on s.SupplierNo=m.SupplierNo
where '.$txndate .' and BranchNo='.$_SESSION['bnum'].' and ps.Amount<0 group by m.TxnID 
union all
select m.TxnID, m.Date, s.SupplierName, m.SupplierInv as InvNo, m.DateofInv as InvDate, ps.Amount as Amount, ps.Amount as AmountValue, MRRNo as CollectNo,m.Remarks, m.Posted from `acctg_2purchasemain` m left join `acctg_2purchasesub` ps on `m`.TxnID=`ps`.TxnID 
join `1suppliers` s on s.SupplierNo=m.SupplierNo
where '.$txndate .' and BranchNo='.$_SESSION['bnum'].' and (ps.TxnID is null) group by m.TxnID order by `Date`, SupplierName, InvDate
';
}
if (allowedToOpen(5961,'1rtc')){ $coltototal='AmountValue';$showgrandtotal=true;}  
$process1='addeditsupplyside.php?w='.$whichqry.'&'; $processlabel1='Lookup';
include_once('../backendphp/layout/clickontabletoeditbody.php');

}

    break; 

    
case 'CV':
if (!allowedToOpen(598,'1rtc')) { echo 'No permission'; exit;}

$print=<<<HTML
<p>Note: Choosing 0 for the month will show uncleared checks from previous year.</p>
<br><br>
  <form action="printvoucher.php?w={$whichqry}" method="POST">
      Print FROM <input type="text" name="FromVch">  TO <input type="text" name="ToVch"> 
      <input type="Submit" name="Print" value="Print">
  </form>
  <form action="printvoucher.php?w=Check" method="POST">
      Print Check Number 
      <input type="text" name="CheckNo">  
      <input type="Submit" name="PrintCheck" value="Print Check (mm-dd-yyyy)">
      <input type="Submit" name="PrintCheck" value="Print Check (mm/dd/yy)">      
  </form>
HTML;



$columnnames=array('Date','CVNo','DateofCheck','PaymentMode','CheckNo','Bank','PayeeNo','Payee','Total','Remarks','Cleared','Posted');

if (isset($_REQUEST['Month']) and $_REQUEST['Month']==0){
  $sql=<<<SQL
SELECT 
  '{((intval($currentyr))-1)}-12-31' AS `Date`, `CVNo`, `DateofCheck`,
  `CheckNo`, FromAccount AS `Bank`, `PayeeNo`, `Payee`,
  AmountofCheck AS `Total`, "From Last Yr" AS `Remarks`, `Cleared`,1 AS `Posted` 
FROM 
  `acctg_3unclearedchecksfromlastperiod`;
SQL;

// echo $sql;
  include_once('../backendphp/layout/displayastable.php');
} 
else {
  
$sql=<<<SQL
SELECT 
  m.CVNo, PaymentMode,  m.Date, m.DateofCheck, m.CheckNo, ca.ShortAcctID as Bank, 
  m.PayeeNo, m.Payee, m.Cleared, m.Posted, FORMAT(SUM(s.Amount), 2) as Total, m.Remarks 
FROM 
  acctg_2cvmain as m 
  JOIN acctg_1chartofaccounts ca ON ca.AccountID=m.CreditAccountID 
  JOIN acctg_0paymentmodes pm ON m.PaymentModeID=pm.PaymentModeID
  JOIN acctg_2cvsub s on m.CVNo=s.CVNo where $txndate group by m.CVNo  
UNION SELECT 
  m.CVNo, PaymentMode, m.Date, m.DateofCheck,m.CheckNo, ca.ShortAcctID as Bank, 
  m.PayeeNo, m.Payee, m.Cleared, m.Posted, 0 as Total, m.Remarks from acctg_2cvmain as m 
  JOIN acctg_1chartofaccounts ca ON ca.AccountID = m.CreditAccountID 
  JOIN acctg_0paymentmodes pm ON m.PaymentModeID=pm.PaymentModeID
  LEFT JOIN acctg_2cvsub s on m.CVNo=s.CVNo 
  where s.CVNo is null and $txndate 
ORDER BY Date, CVNo;
SQL;

$process1='addeditsupplyside.php?w='.$whichqry.'&';
$processlabel1='Lookup'; $txnid='CVNo';
include_once('../backendphp/layout/clickontabletoeditbody.php');
}
break;
   
case 'CVBudget':
if (!allowedToOpen(601,'1rtc')) { echo 'No permission'; exit;}
$title='';
$skipmainswitch=true;
$columnnames=array('Date','CVNo','DateofCheck','CheckNo','CreditAccountID','Bank','PayeeNo','Payee','Total','Remarks');
$columnsub=$columnnames; $columnsub[]='TotalValue';
$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'CVNo');
$columnstoedit=array('DateofCheck','CheckNo','CreditAccountID','Remarks');
$txndate=!isset($_GET['Date'])?'m.Date=\''.$defaultdate.'\'':'m.Date=\''.$_GET['Date'].'\'';
$sql='select m.CVNo, m.Date, m.DateofCheck,m.CheckNo, CreditAccountID,ca.ShortAcctID as Bank, m.PayeeNo, m.Payee, m.Cleared, m.Posted, format(sum(s.Amount),2) as Total, ROUND(sum(s.Amount),2) as TotalValue, m.Remarks from acctg_2cvmain as m join acctg_1chartofaccounts ca on ca.AccountID=m.CreditAccountID join acctg_2cvsub s on m.CVNo=s.CVNo where '.$txndate .' and CheckNo<100 group by m.CVNo  Order By '.$sortfield;
$txnid='CVNo';
$editprocess='preditsupplyside.php?w='.$whichqry.'&Date='.$defaultdate.'&TxnID=';
$editprocesslabel='Change!'; $coltototal='TotalValue'; $showgrandtotal=true;
$addlprocess='addeditsupplyside.php?w=CV&TxnID=';$addlprocesslabel='Lookup';

include_once('../backendphp/layout/displayastableeditcells.php');
    break;   

case 'JV':
if (!allowedToOpen(592,'1rtc')) { echo 'No permission'; exit;}
$columnnames=array('JVDate','JVNo','Remarks','Total','Posted');  
$sql='select m.JVNo, m.JVDate, m.Remarks, m.Posted, format(sum(s.Amount),2) as Total from acctg_2jvmain as m join acctg_2jvsub s on m.JVNo=s.JVNo where '.str_replace('Date','JVDate',$txndate) .' group by m.JVNo  
union select m.JVNo, m.JVDate, m.Remarks, m.Posted, 0 as Total from acctg_2jvmain as m left join acctg_2jvsub s on m.JVNo=s.JVNo where s.JVNo is null and '. str_replace('Date','JVDate',$txndate) .' order by JVDate, JVNo';
$txnid='JVNo';
$process1='addeditsupplyside.php?w=JV&';
$processlabel1='Lookup';
include_once('../backendphp/layout/clickontabletoeditbody.php');
    break;
    
case 'Interbranch':
case 'Txfr':
if (!allowedToOpen(595,'1rtc')) { echo 'No permission'; exit;} 
$columnnamesleft=array('Date','FromBranch','Remarks', 'CountofTransfers','Total','Posted');  
$sqlleft='select m.TxnID, m.Date, b.Branch as FromBranch, m.Remarks, m.Posted,  count(s.Particulars) as CountofTransfers, format(sum(s.Amount),2) as Total from acctg_2txfrmain m join acctg_2txfrsub s on m.TxnID=s.TxnID join `1branches` b on b.BranchNo=m.FromBranchNo where '.$txndate .' and FromBranchNo='.$_SESSION['bnum'].' group by m.Date, b.Branch order by m.Date, b.Branch ';
$lefttabletitle='Transfer OUT';
$lefteditprocess='addeditclientside.php?w='.$whichqry.'&TxnID=';
$lefteditprocesslabel='Lookup';

$columnnamesright=array('Date','ClientBranch','Remarks', 'CountofTransfers','Total','Posted');  
$sqlright='select m.TxnID, m.Date, b.Branch as ClientBranch, m.Remarks, m.Posted,  count(s.Particulars) as CountofTransfers, format(sum(s.Amount),2) as Total from acctg_2txfrmain m join acctg_2txfrsub s on m.TxnID=s.TxnID join `1branches` b on b.BranchNo=s.ClientBranchNo where '.$txndate .' and ClientBranchNo='.$_SESSION['bnum'].' group by m.Date, b.Branch order by m.Date, b.Branch ';
$righttabletitle='Transfer IN';
$righteditprocess='addeditclientside.php?w='.$whichqry.'&TxnID=';
$righteditprocesslabel='Lookup';
include_once('../backendphp/layout/twotablessidebyside.php');
    break;

}
noform:
     $link=null; $stmt=null; 
?>