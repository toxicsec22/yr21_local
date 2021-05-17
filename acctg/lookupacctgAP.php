<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(540,5401,541,542,543,544,545,5431); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=false; include_once('../switchboard/contents.php');
 


//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="f6ebf9";
        $rcolor[1]="FFFFFF"; 


$whichqry=$_GET['w'];

switch ($whichqry){
case 'All':
if (!allowedToOpen(541,'1rtc')) { echo 'No permission'; exit; }
$title='Unpaid Supplier Invoices';
$method='GET';
$show=!isset($_POST['show'])?0:$_POST['show'];
?><form style="display:inline" method="post" action="#">
   <input type=hidden name="show" value="<?php echo ($show==0?1:0); ?>">
    <input type="submit" name="submit" value="<?php echo ($show==0?'Show Details':'Totals Only'); ?>">
</form>&nbsp &nbsp
<?php
if ($show==1){
    $sql1='SELECT SupplierNo, SupplierName,concat("Terms: ",PayTerms," days") as Terms FROM `acctg_23balperinv` where PayBalance<>0 GROUP BY SupplierNo ORDER BY SupplierName ';
    $sql2='SELECT date_format(`Date`,\'%Y-%m-%d\') as `Date`,`SupplierInv`,`PurchaseAmt`,`PaidAmt`,`PayBalance`, b.Branch, DateDiff(Now(),ap.Date) as Age FROM acctg_23balperinv ap join `1branches` b on b.BranchNo=ap.BranchNo ';

    $coltototal='PayBalance';
    $groupby='SupplierNo';
    $orderby=' having PayBalance<>0 ORDER BY Date, SupplierInv';
    $columnnames1=array('SupplierName','Terms');
    $columnnames2=array('Date','SupplierInv','PurchaseAmt','PaidAmt','PayBalance','Age','Branch');
    $showtotals=true; $runtotal=true;
    $showgrandtotal=true;
    include('../backendphp/layout/displayastablewithsub.php');
} else {
   $sql0='CREATE TEMPORARY TABLE APDue AS SELECT SupplierNo, SupplierName,concat(PayTerms," days") as Terms,ROUND(Sum(`PayBalance`),0) as TotalAP, 
ROUND(sum(case when (`DateDue` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7)-7 day)) then `PayBalance` end),0) as `PastDue`,
ROUND(sum(case when (`DateDue` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7) day) and `DateDue` > (now() + interval (((6 - dayofweek(now())) + 7) % 7)-7 day)) then `PayBalance` end),0) as `DueThisFri`,
ROUND(sum(case when (`DateDue` > (now() + interval (((6 - dayofweek(now())) + 7) % 7) day) and  (`DateDue` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7)+7 day))) then `PayBalance` end),0) as `DueNextWk`,
ROUND(sum(case when (`DateDue` > (now() + interval (((6 - dayofweek(now())) + 7) % 7)+7 day) and  (`DateDue` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7)+15 day))) then `PayBalance` end),0) as `DueNext2Wks`,
ROUND(sum(case when (`DateDue` > (now() + interval (((6 - dayofweek(now())) + 7) % 7)+15 day)) then `PayBalance` end),0) as `DueNext3WksandBeyond`
 FROM acctg_23balperinv ap where PayBalance<>0 group by SupplierNo ';
   $stmt0=$link->prepare($sql0); $stmt0->execute();
   $columnnames=array('SupplierName','Terms','TotalAP','PastDue','DueThisFri','DueNextWk','DueNext2Wks','DueNext3WksandBeyond');
   $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' TotalAPValue '); 
   $columnsub=array('SupplierName','Terms','TotalAPValue','PastDueValue','DueThisFriValue','DueNextWkValue','DueNext2WksValue','DueNext3WksandBeyondValue');
   $sql='SELECT SupplierName,Terms, TotalAP AS TotalAPValue, PastDue AS PastDueValue, DueThisFri AS DueThisFriValue, DueNextWk AS DueNextWkValue, DueNext2Wks AS DueNext2WksValue, DueNext3WksandBeyond AS DueNext3WksandBeyondValue, FORMAT(TotalAP,0) AS TotalAP, FORMAT(PastDue,0) AS PastDue, FORMAT(DueThisFri,0) AS DueThisFri, FORMAT(DueNextWk,0) AS DueNextWk, FORMAT(DueNext2Wks,0) AS DueNext2Wks, FORMAT(DueNext3WksandBeyond,0) AS DueNext3WksandBeyond FROM APDue GROUP BY SupplierNo ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' DESC');
   $coltototal='TotalAPValue';
   $showgrandtotal=true; 
   include('../backendphp/layout/displayastablenosort.php');

   $sql='SELECT FORMAT(SUM(TotalAP),0) AS TotalAP, FORMAT(SUM(PastDue),0) AS PastDue, FORMAT(SUM(DueThisFri),0) AS DueThisFri, FORMAT(SUM(DueNextWk),0) AS DueNextWk, FORMAT(SUM(DueNext2Wks),0) AS DueNext2Wks, FORMAT(SUM(DueNext3WksandBeyond),0) AS DueNext3WksandBeyond FROM APDue'; 
   $columnnames=array_diff($columnnames, array('SupplierName','Terms'));
   unset($coltototal,$showgrandtotal,$sortfield); $hidecount=true;
   include('../backendphp/layout/displayastableonlynoheaders.php');
}
   break;

case 'InvDue':
if (!allowedToOpen(543,'1rtc')) { echo 'No permission'; exit; }
   $datecondition=!isset($_REQUEST['PayAsOf'])?date('Y-m-d',strtotime("this Friday")):$_REQUEST['PayAsOf'];
    $title='Invoices Due This Friday - '.$datecondition;
   ?><br>
   <form method="post" action="lookupacctgAP.php?w=InvDue" enctype="multipart/form-data">
      Payments due as of :  <input type="date" name="PayAsOf" value="<?php echo date('Y-m-d',strtotime("this Friday")); ?>"></input>
      <input type="submit" name="lookup" value="Lookup"> </form>
   <br><br>
   <?php
   
   $sql0='CREATE TEMPORARY TABLE duethisfri AS SELECT
        `ap`.`SupplierNo` AS `SupplierNo`,
        CONCAT(IF(ISNULL(RCompany),"",CONCAT(c.Company," - ")),`ap`.`SupplierName`) AS `SupplierName`,
        `ap`.`SupplierInv` AS `SupplierInv`,
        `ap`.`BranchNo` AS `BranchNo`, ap.RCompany,
        date_format(`ap`.`Date`, \'%Y-%m-%d\') AS `Date`,
        `ap`.`PurchaseAmt` AS `PurchaseAmt`,
        `ap`.`PaidAmt` AS `PaidAmt`,
        `ap`.`PayBalance` AS `PayBalance`,
        `ap`.`DateDue` AS `DateDue`,
        `b`.`Branch` AS `Branch`,
        (`ap`.`Date` + interval ((((6 - dayofweek((`ap`.`Date` + interval `ap`.`PayTerms` day))) + 7) % 7) + ifnull(`ap`.`PayTerms`, 0)) day) AS `DateDueFri`,
        (to_days(now()) - to_days((`ap`.`Date` + interval `ap`.`PayTerms` day))) AS `Overdue`
    from
        (`acctg_23balperinv` `ap`
        join `1branches` `b` ON ((`b`.`BranchNo` = `ap`.`BranchNo`)))
            LEFT JOIN `1companies` c ON c.CompanyNo=ap.RCompany
    where
        (`ap`.`PayBalance` <> 0)  and (`ap`.`DateDue` <= (\''.$datecondition.'\'))
    having (DateDueFri <= (\''.$datecondition.'\' + interval (((6 - dayofweek(`Date`)) + 7) % 7) day))';
    $orderby='ORDER BY SupplierName';
   $stmt0=$link->prepare($sql0); $stmt0->execute();
    $sql1='SELECT SupplierName FROM duethisfri GROUP BY SupplierName'; $groupby='SupplierName';
    $sql2='SELECT * FROM duethisfri '; 
$columnnames1=array('SupplierName');
$columnnames2=array('Branch','SupplierInv','Date','PurchaseAmt','PaidAmt','PayBalance','DateDue','Overdue');
//$columnsub=$columnnames2;
   $coltototal='PayBalance';
   $showgrandtotal=true;// echo $sql1.$sql2;
   include('../backendphp/layout/displayastablewithsub.php'); 
   break;

case 'AutoVch':
   if (!allowedToOpen(5431,'1rtc')) { echo 'No permission'; exit; }
   $datecondition=$_REQUEST['PayAsOf']; $vchdate=strtotime($_REQUEST['PayAsOf']);
   include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
    $vchno=lastNum('CVNo','acctg_2cvmain',((date('Y',$vchdate))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',$vchdate)).',2)');
   $chkno=$_REQUEST['CheckNo'];
   
   $sql0='create temporary table duethisfri (
      SupplierNo int(6) not null,
      SupplierName varchar(100) not null,
      SupplierInv varchar(100) not null,
      BranchNo int(6) not null, RCompany tinyint(1) null,
      `Date` date not null,
      PayBalance double,
      DateDueFri date not null
   )
   select 
        `ap`.`SupplierNo` AS `SupplierNo`,
        `ap`.`SupplierName` AS `SupplierName`,
        `ap`.`SupplierInv` AS `SupplierInv`,
        `ap`.`BranchNo` AS `BranchNo`, ifnull(ap.RCompany,0) as `RCompany`,
        date_format(`ap`.`Date`, \'%Y-%m-%d\') AS `Date`,
         `ap`.`PayBalance` AS `PayBalance`,
        (`ap`.`Date` + interval ((((6 - dayofweek((`ap`.`Date` + interval `ap`.`PayTerms` day))) + 7) % 7) + ifnull(`ap`.`PayTerms`, 0)) day) AS `DateDueFri`
    from
        `acctg_23balperinv` `ap`
    where
        (`ap`.`PayBalance` <> 0)
    and (`ap`.`DateDue` <= (\''.$datecondition.'\'))
    order by `ap`.`SupplierName`';
    // echo $sql0;break;
    $stmt=$link->prepare($sql0); $stmt->execute();
   $sqlvch='SELECT ap.SupplierNo, SupplierName, RCompany, ap.DateDueFri, SUM(`PayBalance`) AS TotalDue FROM `duethisfri` ap group by ap.SupplierNo, ap.RCompany, ap.DateDueFri HAVING TotalDue>0 order by SupplierName';
   $stmt=$link->query($sqlvch);
   $resultvch=$stmt->fetchAll();
  
   
   foreach ($resultvch as $vch){ //to add CV main
      $vchno=$vchno+1; $chkno=$chkno+1;
      $sqlinsert='Insert into acctg_2cvmain Set CVNo='.$vchno.', CheckNo='.$chkno.', Date=\''.((strpos($vch['DateDueFri'],''.$currentyr.'')!==FALSE)?$vch['DateDueFri']:''.$currentyr.'-12-30').'\', DateofCheck=\''.$vch['DateDueFri'].'\', PayeeNo='.$vch['SupplierNo'].', Payee=\''.$vch['SupplierName'].'\', CreditAccountID=403, TimeStamp=Now(), EncodedByNo='.$_SESSION['(ak0)'].', PostedByNo='.$_SESSION['(ak0)'];
     // echo $sqlinsert; break;
      $stmt=$link->prepare($sqlinsert);
      $stmt->execute();      
          
   $sql='SELECT * FROM duethisfri ap where ap.SupplierNo='.$vch['SupplierNo'].' and ap.RCompany='.$vch['RCompany'].' and ap.DateDueFri=\''.$vch['DateDueFri'].'\' order by DateDueFri,SupplierInv';
   $stmt=$link->query($sql);
   $result=$stmt->fetchAll();
   
   foreach ($result as $sub){ //to add CV sub
      $sqlinsert='Insert into acctg_2cvsub Set CVNo='.$vchno.', ForInvoiceNo=\''.$sub['SupplierInv'].'\', DebitAccountID=400, Amount='.$sub['PayBalance'].', BranchNo='.$sub['BranchNo'].', TimeStamp=Now(), EncodedByNo='.$_SESSION['(ak0)'];
      $stmt=$link->prepare($sqlinsert);
      $stmt->execute();
   }      
   }  
   
   header("Location:txnsperday.php?perday=0&w=CV");
   break;

case 'Interbranch':
   if (!allowedToOpen(542,'1rtc')) { echo 'No permission'; exit; }
   $title='Aging of Interbranch Txfrs';
$sql0='create temporary table unpdtxfrs(
ClientBranchNo smallint(6) not null, 
Particulars varchar(100) not null, 
DateOUT date not null, 
Amount double not null, 
ARAccount smallint(6) not null, 
FROMBranchNo smallint(6) not null, 
DateIN date null
)
SELECT TOBranchNo as ClientBranchNo, Particulars, DateOUT, IFNULL(Balance,0) as Amount, ARAccount, FROMBranchNo, DateIN FROM acctg_3unpdinterbranchlastperiod where DatePaid is null and DateIN is not null
union all
Select ClientBranchNo, Particulars, `Date` as DateOUT, Amount, DebitAccountID as ARAccount, FromBranchNo, DateIN from acctg_2txfrmain m join acctg_2txfrsub s on m.TxnID=s.TxnID where DatePaid is null and DateIN is not null;';
$stmt=$link->prepare($sql0);
$stmt->execute();

   $sql1='Select date_format(DateIN,\'%b\') as MonthReceived from unpdtxfrs group by date_format(DateIN,\'%b\') ORDER BY Month(DateIN);';
   $stmt=$link->query($sql1);
   $result=$stmt->fetchAll();
   $columnnames=array('ClientBranchNo','ClientBranch','YrsInOperation');
   $sql='Select ClientBranchNo, b.Branch as ClientBranch, ((TO_DAYS(CURDATE()) - TO_DAYS(b.`Anniversary`)) / 365.25) AS `YrsInOperation`, ';
   foreach ($result as $row){
      $columnnames[]=$row['MonthReceived'];
      $sql=$sql.'format(Sum(Case when date_format(DateIN,\'%b\')="'.$row['MonthReceived'].'" then Amount end),2) as `'.$row['MonthReceived'].'`, ';
   }
   $sql=$sql.' format(Sum(Amount),2) as `Total`, Sum(Amount) as `TotalValue` from unpdtxfrs u join `1branches` b on b.BranchNo=u.ClientBranchNo  group by ClientBranchNo';
//echo $sql; break;
   $columnnames[]='Total'; $coltototal='TotalValue'; 
   $showgrandtotal=true;
   include('../backendphp/layout/displayastable.php');
   break;

case 'YrPurchPerSupplier':
   if (!allowedToOpen(545,'1rtc')) { echo 'No permission'; exit; }
   $title='Total Purchases Per Supplier';
 $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'YrTotalValue ');
   $sql='SELECT p.SupplierNo, s.SupplierName, truncate(sum(Amount),0) as YrTotalValue, FORMAT(sum(Amount),0) as YrTotal,
   format(sum(case when Month(Date)=1 then Amount end),0) as Jan,
   format(sum(case when Month(Date)=2 then Amount end),0) as Feb,
   format(sum(case when Month(Date)=3 then Amount end),0) as Mar,
   format(sum(case when Month(Date)=4 then Amount end),0) as Apr,
   format(sum(case when Month(Date)=5 then Amount end),0) as May,
   format(sum(case when Month(Date)=6 then Amount end),0) as Jun,
   format(sum(case when Month(Date)=7 then Amount end),0) as Jul,
   format(sum(case when Month(Date)=8 then Amount end),0) as Aug,
   format(sum(case when Month(Date)=9 then Amount end),0) as Sep,
   format(sum(case when Month(Date)=10 then Amount end),0) as Oct,
   format(sum(case when Month(Date)=11 then Amount end),0) as Nov,
   format(sum(case when Month(Date)=12 then Amount end),0) as `Dec`
   FROM `acctg_2purchasemain` p 
   join `acctg_2purchasesub` ps on p.TxnID=ps.TxnID
   join `1suppliers` s on p.SupplierNo=s.SupplierNo group by p.SupplierNo order by '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' DESC');
   //echo $sql;break;
   $columnnames=array('SupplierNo','SupplierName','YrTotal','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
   $columnsub=$columnnames; $columnsub[]='YrTotalValue';
   $coltototal='YrTotalValue';
   $showgrandtotal=true;
   include('../backendphp/layout/displayastable.php');
   break;

   case 'CatBreakdownPerSupp':
      if (!allowedToOpen(5451,'1rtc')) { echo 'No permission'; exit; }
      $title='Category Share per Supplier';
      $sql0='CREATE TEMPORARY TABLE purchpercat AS
      SELECT m.SupplierNo, i.CatNo, SUM(UnitCost*Qty) AS PerCatMRR
      FROM `invty_2mrr`m JOIN `invty_2mrrsub` s ON m.TxnID=s.TxnID JOIN invty_1items i ON i.ItemCode=s.ItemCode
      GROUP BY m.SupplierNo, i.CatNo
      UNION ALL
      SELECT m.SupplierNo, i.CatNo, SUM(UnitCost*Qty) AS PerCatMRR
      FROM `invty_2pr`m JOIN `invty_2prsub` s ON m.TxnID=s.TxnID JOIN invty_1items i ON i.ItemCode=s.ItemCode
      GROUP BY m.SupplierNo, i.CatNo;';
      $stmt=$link->prepare($sql0);  $stmt->execute();

      $sql0='CREATE TEMPORARY TABLE totalpersupp AS
      SELECT p.SupplierNo, s.SupplierName, ROUND(SUM(PerCatMRR),0) AS TotalPurch FROM purchpercat p JOIN 1suppliers s ON s.SupplierNo=p.SupplierNo WHERE InvtySupplier=1 GROUP BY p.SupplierNo;';
      $stmt=$link->prepare($sql0);  $stmt->execute();

      $sql1='SELECT SupplierNo, SupplierName, TotalPurch FROM totalpersupp WHERE TotalPurch<>0;';
      $stmt=$link->query($sql1); $res=$stmt->fetchAll();

      $sql=''; $columnnames=array('Category');

      foreach ($res as $supp){
         $sql.=', TRUNCATE((SUM(CASE WHEN SupplierNo='.$supp['SupplierNo'].' THEN IFNULL(PerCatMRR,0) END)/'.$supp['TotalPurch'].')*100,2) AS `'.$supp['SupplierName'].'`'; 
         $columnnames[]=$supp['SupplierName'];
      }

      $sql='SELECT p.CatNo, c.Category'.$sql.' FROM purchpercat p JOIN invty_1category c ON c.CatNo=p.CatNo GROUP BY p.CatNo ORDER BY c.Category';
      //echo $sql;break;     
      
      include('../backendphp/layout/displayastable.php');

      echo '<br><br><hr><br><br>';

      $title='Supplier Share per Category';
      
      $sql0='CREATE TEMPORARY TABLE totalpercat AS
      SELECT p.CatNo, c.Category, ROUND(SUM(PerCatMRR),0) AS TotalCat FROM purchpercat p JOIN invty_1category c ON c.CatNo=p.CatNo GROUP BY p.CatNo;';
      $stmt=$link->prepare($sql0);  $stmt->execute();
      
      // $sql1='SELECT CatNo, Category, TotalCat FROM totalpercat WHERE TotalCat<>0;';
      // $stmt=$link->query($sql1); $res=$stmt->fetchAll();

      $sql=''; 

      foreach ($res as $supp){
         $sql.=', TRUNCATE((SUM(CASE WHEN SupplierNo='.$supp['SupplierNo'].' THEN IFNULL(PerCatMRR,0) END)/(SELECT TotalCat FROM totalpercat t WHERE t.CatNo=p.CatNo))*100,2) AS `'.$supp['SupplierName'].'`'; 
      }

      $sql='SELECT p.CatNo, c.Category'.$sql.' FROM purchpercat p JOIN invty_1category c ON c.CatNo=p.CatNo GROUP BY p.CatNo ORDER BY c.Category';
      //echo $sql;break;     
      
      include('../backendphp/layout/displayastable.php');
      break;   


case 'FutureCV';
    if (!allowedToOpen(540,'1rtc')) { echo 'No permission'; exit; }
$title='Future CVs'; $formdesc='CVs dated beyond Yr '.$currentyr.'<br><br><a href="addmain.php?w=FutureCV"   target=_blank>Add New PDC</a><br><br>';
$formdesc=$formdesc.'<form action="printvoucher.php?w='.$whichqry.'" method="POST">
        Print FROM <input type="text" name="FromVch">  TO <input type="text" name="ToVch"> <input type="Submit" name="Print" value="Print">
    </form>
    <form action="printvoucher.php?w=FutureCheck" method="POST">
        Print Check Number <input type="text" name="CheckNo">  <input type="Submit" name="PrintCheck" value="Print Check (mm-dd-yyyy)">   <input type="Submit" name="PrintCheck" value="Print Check (mm/dd/yy)">
    </form>';
$columnnames=array('Date','CVNo','DateofCheck','PaymentMode','CheckNo','Bank','Payee','Total','Remarks','ReleaseDate');
$sortfield=(isset($_POST['sortfield']))?$_POST['sortfield']:'Date, CVNo';
$sql='select PaymentMode,m.CVNo, m.Date, m.DateofCheck,m.CheckNo, ca.ShortAcctID as Bank, m.Payee, format(sum(s.Amount),2) as Total, m.Remarks,m.ReleaseDate from acctg_4futurecvmain as m JOIN acctg_0paymentmodes pm ON m.PaymentModeID=pm.PaymentModeID join acctg_1chartofaccounts ca on ca.AccountID=m.CreditAccountID join acctg_4futurecvsub s on m.CVNo=s.CVNo  group by m.CVNo  
union select PaymentMode,m.CVNo, m.Date, m.DateofCheck,m.CheckNo, ca.ShortAcctID as Bank, m.Payee, 0 as Total, m.Remarks,"" AS ReleaseDate from acctg_4futurecvmain as m JOIN acctg_0paymentmodes pm ON m.PaymentModeID=pm.PaymentModeID join acctg_1chartofaccounts ca on ca.AccountID=m.CreditAccountID left join acctg_4futurecvsub s on m.CVNo=s.CVNo where s.CVNo is null ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC'); 
$txnidname='CVNo';
if (allowedToOpen(5401,'1rtc')) { $editprocess='addeditsupplyside.php?w='.$whichqry.'&CVNo=';
$editprocesslabel='Lookup';}
$columnsub=$columnnames;
include_once('../backendphp/layout/displayastable.php');
    break; 

case 'SLPerSupplier':
if (!allowedToOpen(544,'1rtc')) { echo 'No permission'; exit; }

$title='AP-SL Per Supplier';echo '<h3>'.$title.'</h3>';
$fieldname='Supplier'; 
$list='suppliers';
//echo $list;
include_once('../generalinfo/lists.inc'); 
renderlist($list);

$monthfrom=(isset($_REQUEST['Month1'])?$_REQUEST['Month1']:date('m'));
$monthto=(isset($_REQUEST['Month2'])?$_REQUEST['Month2']:date('m'));
   ?>
<form method="post" action="lookupacctgAP.php?w=SLPerSupplier" enctype="multipart/form-data">
For Supplier:  <input type="text" name="<?php echo $fieldname; ?>" list="<?php echo $list; ?>" value="<?php echo (!isset($_REQUEST[$fieldname])?'':$_REQUEST[$fieldname]); ?>" size="30"></input>&nbsp &nbsp &nbsp
From Month (1 - 12):  <input type="text" size=5 name="Month1" value="<?php echo $monthfrom; ?>"></input>&nbsp
To Month (1 - 12):  <input type="text" size=5 name="Month2" value="<?php echo $monthto; ?>"></input>&nbsp &nbsp &nbsp 
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
if (!isset($_REQUEST[$fieldname])){
goto noform;
} else {

$showprint=true;

include('../backendphp/functions/getnumber.php');
$suppno=getNumber('Supplier',addslashes($_REQUEST[$fieldname]));

$formdesc='<br><b>'.$suppno.' - '.$_REQUEST[$fieldname].'  <i>for the months '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$monthfrom.'-1'))).'&nbsp to '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$monthto.'-1'))).str_repeat('&nbsp',3).'</i>';


$acctid='(400)';
$acctidarray=array(400);
//include('../acctg/sqlphp/sqlalltxnsperaccountpermonth.php');
include('../acctg/sqlphp/createacctsched.php');
include('../acctg/sqlphp/createacctbegbal.php');


$sql0='Create temporary table slper (
Date date  null,
ControlNo varchar(150) null,
`SuppNo/ClientNo` smallint(6) null,
`Supplier/Customer/Branch` varchar(100) null,
Particulars varchar(100) null,
AccountID smallint(6) not null,
BranchNo smallint(6) not null,
Amount double null,
Entry varchar(2) not null,
w varchar(20) not null,
TxnID int(11) not null
)'.$sqlalltxns;
// echo $sql0; exit;
$stmt=$link->prepare($sql0);
$stmt->execute();

$sqllastyr='SELECT "Beginning" AS ControlNo, "S" AS BECS, clp.`SupplierNo`  as `SuppNo/ClientNo`, 400 AS AccountID, BranchNo, BranchNo AS FromBudgetOf,IFNULL(Balance,0) as SumofAmount, "CR" as Entry FROM `acctg_3unpdsuppinvlastperiod` clp ';
$sql1='Create temporary table slperbegbal (
ControlNo varchar(150) null, BECS varchar(1) null,
`SuppNo/ClientNo` smallint(6) null,
AccountID smallint(6) not null,
BranchNo smallint(6) not null,
SumofAmount double null,
Entry varchar(2) not null
)'.($monthfrom<>1?$sqllastmonth.' UNION ALL ':'').$sqllastyr;
//if($_SESSION['(ak0)']==1002){ echo $sql0.'<br><br>'.$sql1; exit;}
$stmt=$link->prepare($sql1);
$stmt->execute();

}
//echo $sql1; break;
$lastmonth=$monthfrom==1?'\''.(substr(($currentyr-1),0,4).'-12-31\''):'Last_Day(\''.$currentyr.'-'.($monthfrom-1).'-1\')';
$sql='SELECT '.$lastmonth.' as Date, "BegBal" as ControlNo, "Beginning Balance" as `Supplier/Customer/Branch`, "" as Particulars, b.Branch, 0 as Debit, IFNULL(Sum(SumofAmount),0) as Credit, "SLPerSupplier" as w, 0 as TxnID
from `1branches` b join slperbegbal beg on b.BranchNo=beg.BranchNo where beg.`SuppNo/ClientNo`='.$suppno.' 
UNION ALL
SELECT Date, ControlNo, `Supplier/Customer/Branch`, Particulars, Branch, SUM(Case when Entry="DR" then Amount end) as Debit,SUM(Case when Entry="CR" then Amount*-1 end) as Credit, w, TxnID from `1branches` b join slper sp on sp.BranchNo=b.BranchNo where sp.`SuppNo/ClientNo`='.$suppno.' group by Date, ControlNo, `SuppNo/ClientNo`, Branch, Particulars order by Date,ControlNo';
//echo $sql; break;    
$main='';
$columnnames=array();

$columnsub=array('Date', 'ControlNo', 'Particulars','Debit','Credit'); 
$sub='';


$stmt=$link->query($sql);
   $result=$stmt->fetchAll();
 
   $subcol='';$runtotal=0;
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        $runtotal=$runtotal+((is_null($row['Debit']) or empty($row['Debit']))?0:($row['Debit']*-1))+((is_null($row['Credit']) or empty($row['Credit']))?0:($row['Credit']));
        $cvjvtxn='TxnID';
        switch ($row['w']){
         case 'Sales':
         case 'Collect':
         case 'Bounced':
         case 'Interbranch':
            $filetoopen='addeditclientside';
         break;
         case 'Deposit':
            $filetoopen='addeditdep';
         break;
         case 'CV':
            $filetoopen='formcv'; $cvjvtxn='CVNo';
         break;
         case 'JV':
            $filetoopen='formjv'; $cvjvtxn='JVNo';
         break;
         case 'Purchase':
            $filetoopen='formpurch';
            break;
         case 'Forex':
            $filetoopen='addeditsupplyside';
            break;
         default:
            $filetoopen='lookupgenacctg';
        }
        
        $sub=$sub.'<td>'.number_format($runtotal,2).'</td><td><a href="'.$filetoopen.'.php?w='.$row['w'].'&'.$cvjvtxn.'='.$row['TxnID'].'"  target=_blank>Lookup</a></tr>';
        $colorcount++;
    }
    $sub='<table><tr>'.$subcol.'<td>Running Sum</td><td>Lookup?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select ifnull(Sum(Case when s.Entry="DR" then s.Amount end),0) as TotalDebit, IFNULL(Sum(Case when s.Entry="CR" then s.Amount end),0)-IFNULL((Select Sum(SumofAmount) from slperbegbal a where a.`SuppNo/ClientNo`='.$suppno.'),0) as TotalCredit from  `slper` s where s.`SuppNo/ClientNo`='.$suppno;
    
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Totals:'.str_repeat('&nbsp',4).'<font color="maroon">Debit:  '.number_format($result['TotalDebit'],2).str_repeat('&nbsp',7).'Credit:  '.number_format($result['TotalCredit']*-1,2).str_repeat('&nbsp',7).'Net:  '.number_format(($result['TotalDebit']+$result['TotalCredit'])*-1,2).'</font><br><br>';
// echo $sql; break;

    include('../backendphp/layout/lookupreport.php');    
    
}
noform:
      $link=null; $stmt=null;
?>