<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

// check if allowed
$allowed=array(645,646,647,648,649,650,651,652,6451,6452,6466);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=true; include_once('../switchboard/contents.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link; 



$whichqry=$_GET['w'];

switch ($whichqry){
case 'InvAuditPerMonth':
    if (!allowedToOpen(648,'1rtc')) { echo 'No permission'; exit;}
$title='Inventory Audit Per Month';
$txnid='CountID';
$fieldname='AuditMonth';
$showbranches=false;

?>
<form method='post' action='lookupaudit.php?w=InvAuditPerMonth&AuditMonth=<?php echo (!isset($_REQUEST[$fieldname])?date('m'):$_REQUEST[$fieldname]); ?>'>
   Choose month (1-12): <input type='text' name='AuditMonth' size=5 value='<?php echo (!isset($_REQUEST[$fieldname])?date('m'):$_REQUEST[$fieldname]); ?>' autocomplete=off>
   <input type='submit' name='lookup' value='Lookup'>
</form>
<?php

if (!isset($_REQUEST[$fieldname])){
goto noform;
} else {
$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Date, Branch');
$sql='SELECT Month(c.Date) as AuditMonth, b.Branch, c.Date, e.Nickname as AuditedBy, c.CountID,  c.Remarks, Posted, Count(ItemCode) as LineItems FROM audit_2countmain c join `1branches` b on b.BranchNo=c.BranchNo
right join `1employees` e on e.IDNo=c.AuditedByNo
join audit_2countsub s on c.CountID=s.CountID where Month(c.Date)='.$_REQUEST[$fieldname].' group by CountID
union Select Month(c.Date) as AuditMonth, b.Branch, c.Date, e.Nickname as AuditedBy, c.CountID,  c.Remarks, Posted, 0 as LineItems FROM audit_2countmain c join `1branches` b on b.BranchNo=c.BranchNo
right join `1employees` e on e.IDNo=c.AuditedByNo
left join audit_2countsub s on c.CountID=s.CountID
where Month(c.Date)='.$_REQUEST[$fieldname].' and s.ItemCode is null group by CountID order by '.$sortfield;
$columnnames=array('Date','Branch','AuditedBy','Remarks','LineItems','Posted');
$columnsub=$columnnames;
$process1='editaudit.php?w=InvCount&';
$processlabel1='Lookup';
include_once('../backendphp/layout/clickontabletoeditbody.php');
}
break;

case 'CashAuditPerMonth':
if (!allowedToOpen(array(646,6466),'1rtc')) { echo 'No permission'; exit;}
$title='Cash Audit Per Month';
$fieldname='CashAuditMonth';
$showbranches=false;
include_once('../backendphp/layout/clickontabletoedithead.php');
if((allowedToOpen(646,'1rtc')) AND (!allowedToOpen(6466,'1rtc'))){
?>
<form method='post' action='lookupaudit.php?w=CashAuditPerMonth'>
   Choose month (1-12): <input type='text' name='CashAuditMonth' size=5 value='<?php echo date('m'); ?>' autocomplete=off>
   <input type='submit' name='lookup' value='Lookup'>
</form>
<?php
}
if ((!isset($_REQUEST[$fieldname])) AND (!allowedToOpen(6466,'1rtc'))){
goto noform;
} else {
    if((allowedToOpen(646,'1rtc')) AND (!allowedToOpen(6466,'1rtc'))){
        $addlcon='Month(c.DateCounted)='.$_REQUEST[$fieldname].' ';
    } else {
        $addlcon='c.DateCounted > CURDATE() - INTERVAL 7 DAY ';
    }
$sql='SELECT Month(c.DateCounted) as AuditMonth, b.Branch, c.DateCounted, e.Nickname as AuditedBy, c.CashCountID,  c.Remarks, Posted FROM audit_2countcash c join `1branches` b on b.BranchNo=c.BranchNo
right join `1employees` e on e.IDNo=c.EncodedByNo
join audit_2countcashsub s on c.CashCountID=s.CashCountID where '.$addlcon.' group by CashCountID
union Select Month(c.DateCounted) as AuditMonth, b.Branch, c.DateCounted, e.Nickname as AuditedBy, c.CashCountID,  c.Remarks, Posted FROM audit_2countcash c join `1branches` b on b.BranchNo=c.BranchNo
right join `1employees` e on e.IDNo=c.EncodedByNo
left join audit_2countcashsub s on c.CashCountID=s.CashCountID
where '.$addlcon.' AND s.InvandPRCollectNo is null group by CashCountID order by `DateCounted`,`Branch`';
// echo $sql;
$columnnames=array('DateCounted','Branch','AuditedBy','Remarks','Posted');  
$editprocess='editcash.php?w=CashCount&CashCountID=';
$editprocesslabel='Lookup';
$txnidname='CashCountID';

$title='';
}

include_once('../backendphp/layout/displayastable.php');
break;

case 'Tools':
if (!allowedToOpen(651,'1rtc')) { echo 'No permission'; exit;}
$title='Tools Audit Per Month';
$fieldname='ToolsAuditMonth';
$showbranches=false;
include_once('../backendphp/layout/clickontabletoedithead.php');
?>
<form method='post' action='lookupaudit.php?w=Tools'>
   Choose month (1-12): <input type='text' name='ToolsAuditMonth' size=5 value='<?php echo date('m'); ?>' autocomplete=off>
   <input type='submit' name='lookup' value='Lookup'>
</form>
<?php
if (!isset($_REQUEST[$fieldname])){
goto noform;
} else {
$title='';
$sql='SELECT Month(c.Date) as AuditMonth, b.Branch, c.Date, c.DateofLastCount, e.Nickname as AuditedBy, Count(s.ToolID) as NumberofTools, c.CountID,  c.Remarks, Posted FROM audit_2toolscountmain c join `1branches` b on b.BranchNo=c.BranchNo
right join `1employees` e on e.IDNo=c.AuditedByNo
join audit_2toolscountsub s on c.CountID=s.CountID where Month(c.Date)='.$_REQUEST[$fieldname].' group by CountID
union Select Month(c.Date) as AuditMonth, b.Branch, c.Date, c.DateofLastCount, e.Nickname as AuditedBy, 0 as NoofTools, c.CountID,  c.Remarks, Posted FROM audit_2toolscountmain c join `1branches` b on b.BranchNo=c.BranchNo
right join `1employees` e on e.IDNo=c.AuditedByNo
left join audit_2toolscountsub s on c.CountID=s.CountID
where Month(c.Date)='.$_REQUEST[$fieldname].' and s.ToolID is null group by CountID order by `Date`,`Branch`';
$columnnames=array('Date','Branch','AuditedBy','NumberofTools','Remarks','Posted');  $width='100%';
$editprocess='editcash.php?w=Tools&CountID=';
$editprocesslabel='Lookup';
$txnid='CountID'; $txnidname='CountID';
}

include_once('../backendphp/layout/displayastable.php');
break;


case 'TransfertoCentral':
if (!allowedToOpen(652,'1rtc')) { echo 'No permission'; exit;}
$title='Transfer Vacuum to Central';
$fieldname='Date';
$showbranches=false;
include_once('../backendphp/layout/clickontabletoedithead.php');
?>
<form method='post' action='prvacuum.php?w=TransfertoCentral'>
  Date of Vacuum: <input type='date' name='Date' size=5 value='<?php echo date('Y-m-d'); ?>' autocomplete=off>
   <input type='submit' name='send' value='Transfer'>
   <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>"><br>
   <i>Only posted vacuum entries will be processed.</i>
</form>
<?php
if (!isset($_REQUEST[$fieldname])){
goto noform;
} 
include_once('../backendphp/layout/clickontabletoeditbody.php');
break;

case 'Adjust':
    if (!allowedToOpen(645,'1rtc') and !allowedToOpen(6452,'1rtc')) { echo 'No permission'; exit;}

$title='Adjustments Per Month';
$fieldname='Month';
$showbranches=false;
include_once('../backendphp/layout/clickontabletoedithead.php');
?>
<form method='post' action='lookupaudit.php?w=Adjust'>
   Choose month (1-12): <input type='text' name='Month' size=5 value='<?php echo date('m'); ?>' autocomplete=off>
   <input type='submit' name='lookup' value='Lookup'>
</form>
<?php
if (!isset($_REQUEST[$fieldname])){
goto noform;
} else {
if (allowedToOpen(6451,'1rtc')) { $adjcondition='';}
elseif (allowedToOpen(6452,'1rtc')) { $adjcondition=' AND m.AdjType<>0';}
else {$adjcondition=' AND m.AdjType=0';}
$sql='SELECT Month(m.Date) as AdjMonth, m.AdjNo, b.Branch, m.Date, e.Nickname as EncodedBy, Count(s.ItemCode) as LineItems, Posted, m.TxnID FROM invty_4adjust m right join `1employees` e on e.IDNo=m.EncodedByNo
join `1branches` b on b.BranchNo=m.BranchNo
join invty_4adjustsub s on m.TxnID=s.TxnID where Month(m.Date)='.$_REQUEST[$fieldname].$adjcondition.' group by m.TxnID
union Select Month(m.Date) as AdjMonth, m.AdjNo, b.Branch, m.Date, e.Nickname as EncodedBy, 0 as LineItems, Posted, m.TxnID FROM invty_4adjust m join `1branches` b on b.BranchNo=m.BranchNo
right join `1employees` e on e.IDNo=m.EncodedByNo
left join invty_4adjustsub s on m.TxnID=s.TxnID
where Month(m.Date)='.$_REQUEST[$fieldname].' AND s.ItemCode is null '.$adjcondition.' group by m.TxnID order by `Date`,`AdjNo`';
$columnnames=array('Date','AdjNo', 'Branch', 'EncodedBy','LineItems','Posted');  
$process1='addeditadj.php?w=Adjust&';
$processlabel1='Lookup';
$txnid='TxnID';
}

include_once('../backendphp/layout/clickontabletoeditbody.php');
break;

case 'NoCount':
if (!allowedToOpen(650,'1rtc')) { echo 'No permission'; exit;}
$fieldname='Branch';
$showbranches=true;
?>
<form method='post' action='lookupaudit.php?w=NoCount'>
  Enter Branch Number: <input type='text' name='Branch' size=5 value='<?php echo $_SESSION['bnum']; ?>' autocomplete=off>
  Count FROM:  <input type="date" name="fromdate" value="<?php echo date('Y-m-d'); ?>"></input> TO <input type="date" name="todate" value="<?php echo date('Y-m-d'); ?>"></input>
   <input type='submit' name='lookup' value='Lookup'>
</form>
<?php
if (!isset($_REQUEST[$fieldname])){
goto noform;
} else {
    $title='With End Inv, No Count from '.$_POST['fromdate'].' to '.$_POST['todate'];
    $formdesc='For Branch No:'. $_REQUEST[$fieldname].'<br><a href="javascript:window.print()">Print</a>';
    
     $sql0='CREATE TEMPORARY TABLE endinvperbranch (
BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
CatNo	smallint(6)	NOT NULL,
Category	varchar(100)	NOT NULL,
Description	varchar(100)	NOT NULL,
Unit		varchar(10)	NOT NULL,
EndInvToday	double	NOT NULL,
PRIMARY KEY (`ItemCode`)
)
SELECT BranchNo,a.ItemCode,i.CatNo,c.Category,i.ItemDesc as Description,i.Unit,Sum(Qty) as EndInvToday FROM invty_20uniallposted as a join invty_1items i on i.ItemCode=a.ItemCode join `invty_1category` c on c.CatNo=i.CatNo where i.CatNo<>1 and Date is not null and Date<=Now() and BranchNo='.$_REQUEST[$fieldname].' group by a.ItemCode' ;    
   
    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
    
$sql0='CREATE TEMPORARY TABLE latestcount (
CountID	int(11)	NOT NULL,
PRIMARY KEY (`CountID`))
Select CountID from `audit_2countmain` where BranchNo='.$_REQUEST[$fieldname].' and Date>=\''.$_POST['fromdate'].'\' and Date<=\''.$_POST['todate'].'\'';  
// replaced this: Select CountID from `audit_2countmain` where BranchNo='.$_REQUEST[$fieldname].' order by Date desc limit 3
    $stmt0=$link->prepare($sql0);
    $stmt0->execute(); 

     
     $sql0='CREATE TEMPORARY TABLE counted AS
SELECT BranchNo, s.ItemCode,Count FROM `audit_2countmain` m join `audit_2countsub` s on m.CountID=s.CountID where m.CountID in (Select CountID from latestcount) group by s.ItemCode' ;    

    $stmt0=$link->prepare($sql0);
    $stmt0->execute(); 
    
    $sql0='CREATE TEMPORARY TABLE nocount AS
SELECT e.* from endinvperbranch e left join counted c on e.ItemCode=c.ItemCode where e.EndInvToday<>0 AND c.ItemCode IS NULL';

   $stmt0=$link->prepare($sql0);
    $stmt0->execute();
    
    $sql1='Select CatNo, Category from nocount group by CatNo';
    
    $sql2='Select * from nocount ';
    $showbranches=false;
    $groupby='CatNo';
    $orderby=' ORDER By CatNo';
    $columnnames1=array('Category');
    $columnnames2=array('ItemCode','Description','EndInvToday','Unit');
    
    include('../backendphp/layout/displayastablewithsub.php');
    $sql0='DROP TEMPORARY TABLE IF EXISTS `nocount`,`counted`,`latestcount`,`endinvperbranch`;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
    break;
}

case 'CountSeries':
    if (!allowedToOpen(647,'1rtc')) { echo 'No permission'; exit;}
$title='Count of Series';
include_once('../backendphp/layout/clickontabletoedithead.php');
$perday=$_REQUEST['perday']; 
$fieldname=($perday==0?'Month':'Date');
$showbranches=false;
?>
<form method='post' style="display:inline" action='lookupaudit.php?w=<?php echo $whichqry;?>'> 
   Choose Date <input type='text' name='Date' size=10 value='<?php echo date('Y-m-d',strtotime("-1 days")); ?>' autocomplete=off>
   <input type="hidden" name="perday" value="1">
   <input type='submit' name='lookup' value='Lookup Per Day'>
</form> &nbsp; &nbsp; &nbsp;
<form method='post'  style="display:inline" action='lookupaudit.php?w=<?php echo $whichqry;?>'>
   Choose Month (1-12) <input type='text' name='Month' size=5 value='<?php echo date('m'); ?>' autocomplete=off>
   <input type="hidden" name="perday" value="0">
   <input type='submit' name='lookup' value='Lookup Per Month'>
</form><br><br>

<?php
if (!isset($_REQUEST[$fieldname])){
goto noform;
} else {
    
    if ($perday==0){
$formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$_POST[$fieldname].'-1')).'<br>';   
$txndate='Month(s.Date)='.$_REQUEST[$fieldname];
} else {
$formdesc=$_REQUEST[$fieldname].'<br>';
$txndate='s.Date=\''.$_REQUEST[$fieldname].'\'';
}
    
     $sql0='create temporary table series(
Branch varchar(20) not null,
Txn varchar(20) not null,
First varchar(11)  not null,
Last varchar(11)  not null,
Encoded int(11) not null
)
SELECT b.Branch, t.txndesc AS `Txn`, Min(s.SaleNo) AS `First`, Max(s.SaleNo) AS `Last`, Count(s.SaleNo) AS Encoded 
FROM invty_2sale s JOIN `1branches` b ON s.BranchNo = b.BranchNo
join invty_0txntype t on t.txntypeid=s.txntype
where '.$txndate.' and s.txntype<>5 and s.txntype<>3
GROUP BY b.Branch, t.txndesc
union
SELECT b.Branch, t.txndesc AS `Txn`, Min(uExtractNumberFromString(s.SaleNo)) AS `First`, Max(uExtractNumberFromString(s.SaleNo)) AS `Last`, Count(s.SaleNo) AS Encoded 
FROM invty_2sale s JOIN `1branches` b ON s.BranchNo = b.BranchNo
join invty_0txntype t on t.txntypeid=s.txntype
where '.$txndate.' and (s.txntype=5 or s.txntype=3)
GROUP BY b.Branch, t.txndesc';

   
    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
    
  
    $sql='select *,(`Last`-`First`+1) AS ShouldBe, (`Last`-`First`+1)-`Encoded` AS `Diff`  from series;';
    $showbranches=false;
    $orderby='Branch,Txn';
    $columnnames=array('Branch','Txn','First','Last','ShouldBe','Encoded','Diff');
    
    include('../backendphp/layout/displayastable.php');
    $sql0='DROP TEMPORARY TABLE IF EXISTS `series`;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
}
    break;
   
case 'LookupSeries':
if (!allowedToOpen(649,'1rtc')) { echo 'No permission'; exit;}
   $title='Check Series';
   $showbranches=true;
   include_once('../backendphp/layout/clickontabletoedithead.php');
$fieldname='Month';
?>
<form method='post' action='lookupaudit.php?w=<?php echo $whichqry;?>'>
   Choose month (1-12): <input type='text' name='Month' size=5 value='<?php echo date('m'); ?>' autocomplete=off>
   <input type='submit' name='lookup' value='Lookup'>
</form>
<?php
if (!isset($_REQUEST[$fieldname])){
goto noform;
} else {
    $formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$_REQUEST[$fieldname].'-1')).'<br>';//<a href="javascript:window.print()">Print</a>';
    
     $sql0='create temporary table series(
Date date not null,
TxnType varchar(20) not null,
ControlNo varchar(11)  not null,
Posted tinyint(1)  not null,
Total double  not null,
Remarks varchar(20)  null
)
SELECT mm.Date, tt.txndesc AS `TxnType`, mm.MRRNo AS ControlNo, mm.Posted, truncate(Sum(UnitCost*Qty), 2) AS Total, Remarks
FROM invty_2mrr mm INNER JOIN invty_2mrrsub ms ON (mm.TxnID = ms.TxnID)  join invty_0txntype tt on tt.txntypeid=mm.txntype
where mm.BranchNo='.$_SESSION['bnum'].' AND Month(mm.Date)='.$_REQUEST[$fieldname].' Group by mm.TxnID

union all
SELECT tm.DateIN, tt.txndesc, tm.TransferNo, tm.`Posted`, truncate(Sum(`UnitCost`*`QtyReceived`),2) AS Total, tm.Remarks
FROM invty_2transfer tm INNER JOIN invty_2transfersub ts ON (tm.TxnID =ts.TxnID)  join invty_0txntype tt on tt.txntypeid=tm.txntype
where tm.BranchNo='.$_SESSION['bnum'].' AND Month(tm.DateIN)='.$_REQUEST[$fieldname].' Group by tm.TxnID

UNION ALL SELECT tm.DateOUT,tt.txndesc, tm.TransferNo, tm.`Posted`, truncate(Sum(`UnitPrice`*`QtySent`),2) AS Total, tm.Remarks
FROM invty_2transfer tm INNER JOIN invty_2transfersub ts ON (tm.TxnID =ts.TxnID)  join invty_0txntype tt on tt.txntypeid=tm.txntype
where tm.BranchNo='.$_SESSION['bnum'].' AND Month(tm.DateOUT)='.$_REQUEST[$fieldname].' Group by tm.TxnID

UNION ALL 
SELECT sm.Date, tt.txndesc, sm.SaleNo, sm.`Posted`, truncate(Sum(`UnitPrice`*`Qty`), 2) AS Total, Remarks
FROM invty_2sale sm INNER JOIN invty_2salesub ss ON (sm.TxnID =ss.TxnID)  join invty_0txntype tt on tt.txntypeid=sm.txntype
where sm.BranchNo='.$_SESSION['bnum'].' AND Month(sm.Date)='.$_REQUEST[$fieldname].' Group by sm.TxnID order by TxnType,Date,ControlNo;';

   
    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
    
  
    $sql='select *  from series;';
    $orderby='TxnType,Date,ControlNo';
    $showbranches=true;
    $columnnames=array('TxnType','Date','ControlNo','Posted','Total','Remarks');
    
    include('../backendphp/layout/displayastable.php');
    $sql0='DROP TEMPORARY TABLE IF EXISTS `series`;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
}
   break;
}
noform:
      $link=null; $stmt=null;
?>