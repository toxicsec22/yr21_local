<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 
include_once "../generalinfo/lists.inc";


// check if allowed
$allowed=array(738,739,740,741,7195);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=false;
include_once('../switchboard/contents.php');

$whichqry=$_GET['w'];
switch ($whichqry){
  case 'ClientMonthTxn':
if ((!allowedToOpen(7195,'1rtc'))){ echo 'No Permission'; exit(); }
  $fieldname='ClientName';
  $title="Client's Monthly Cash Transactions";
  include_once('../backendphp/layout/clickontabletoedithead.php');
  include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
  $clientname = isset($_POST['ClientName'])?$_POST['ClientName']:null;
?>
<form method="POST" action="lookupinvtools.php?w=<?php echo $whichqry; ?>" required = "true">
<!-- <center> -->
<div style="width:500px;  border: 2.5px solid grey; padding: 10px; ">
Client: 

<input type="text" name="ClientName"  list="allclients" size=40 autocomplete="off" value = <?php isset($_POST['ClientName'])?$_POST['ClientName']:null?>>

<input type="submit" name="print" value="Show Records">
<br>
<?php if (isset($_POST['ClientName'])){ ?>
      <br>
      <h4>
        Client Name: <?php echo $clientname?>
      </h4>
      <br>
<?php } 
  echo comboBox($link,'SELECT Left(`ClientName`,20) as `ClientName`, ClientNo FROM `1clients` WHERE Inactive<>1 ORDER BY ClientName','ClientNo','ClientName','allclients');

  $sql="SELECT Month(s.Date) as Month, FORMAT(SUM(ss.UnitPrice*ss.Qty),0) AS Amount FROM invty_2sale s
  JOIN invty_2salesub ss
  ON s.TxnID = ss.TxnID
  JOIN 1clients c
  ON c.ClientNo = s.ClientNo
  WHERE c.ClientNo = '".$clientname."'
  GROUP BY Month(s.Date);";
  $columnnames=array('Month','Amount'); ;
  if(isset($_POST['ClientName']))
  {
    include_once('../backendphp/layout/displayastableonlynoheaders.php');
    if(count($datatoshow)==0)
    {
      echo "No records";
    }
  }
  
?>
</div>
<!-- </center> -->
</form>
</body>
<?php break; 

case 'NoSoldin6Months':
   $title='None Sold in the Last 6 Months Per Area';   
   $fieldname='AreaNo';
?>
<form method="post" action="lookupinvtools.php?w=<?php echo $whichqry; ?>" enctype="multipart/form-data">
 Choose Area:  <input type="text" name="<?php echo $fieldname; ?>" list="areas"></input>
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
renderlist('areas');
if (!isset($_REQUEST[$fieldname])){
include_once('../backendphp/layout/clickontabletoedithead.php');
goto noform;
} else {
    $title='None Sold in the Last 6 Months - '.$_REQUEST[$fieldname];
   $areano=getValue($link,'`0area`','`Area`',$_REQUEST[$fieldname],'AreaNo');
   $areacondition=$areano==0?'':'and b.AreaNo='.$areano.' and b.Active=1';
   $title=$title.($areano==0?' (Sales from All Branches are considered.)':'');
   $sql0='create temporary table nosold6months(
    ItemCode smallint(6) not null,
    BranchNo smallint(6) not null,
    EndInvToday double null
   )
   SELECT ei.ItemCode, ei.BranchNo, ifnull(ei.EndInvToday,0) as EndInvToday FROM `invty_21endinv` ei 
LEFT JOIN `invty_39soldpast6months` s6 ON ei.ItemCode = s6.ItemCode '.($areano==0?'':'and ei.BranchNo=s6.BranchNo
JOIN `1branches` b ON b.BranchNo=ei.BranchNo'). 
' WHERE (s6.ItemCode Is Null) '.$areacondition.' GROUP BY ei.ItemCode, ei.BranchNo having sum(EndInvToday)<>0;';
//echo $sql0;
    $stmt=$link->prepare($sql0);
    $stmt->execute();
}

$sql1='Select BranchNo, Branch from 1branches where AreaNo='.$areano.' and Active=1';
$stmt=$link->prepare($sql1);
$stmt->execute();
$resultbranch=$stmt->fetchAll();
$columnnames=array('ItemCode', 'Description', 'Unit');
$sql='';
foreach ($resultbranch as $branch){
    $columnnames[]=$branch['Branch'];
    $sql=$sql.'Sum(Case when n.BranchNo='.$branch['BranchNo'].' then EndInvToday end) as `'.$branch['Branch'].'`, ';
}

   $sql='SELECT n.ItemCode, concat(c.Category, " ", i.ItemDesc) as Description, '.$sql.' i.Unit FROM invty_1items i JOIN nosold6months n ON i.ItemCode=n.ItemCode join invty_1category c on c.CatNo=i.CatNo
   JOIN `1branches` b ON b.BranchNo=n.BranchNo GROUP BY i.ItemCode ORDER BY c.Category, i.ItemDesc'; 
    // echo $sql; break;
    include('../backendphp/layout/displayastable.php');
   break;

case 'NoSaleinPeriod':
   $title='Items with No Sale Nationwide'; $formdesc='Earliest data included is last 3 years';
   include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
   ?>
<form method="post" action="lookupinvtools.php?w=<?php echo $whichqry; ?>" enctype="multipart/form-data">
 No sale in the past:  <input type="text" name="Duration" list="duration"></input>&nbsp &nbsp
 <input type='radio' name='WithInv?' value='1' checked=true>With Invty</input> &nbsp<input type='radio' name='WithInv?' value='0'>Zero Invty</input>
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
   echo comboBox($link,'SELECT "3 months" AS Duration UNION SELECT "6 months" UNION SELECT "1 year" UNION SELECT "2 years" UNION SELECT "3 years"','Duration','Duration','duration');
   if (!isset($_REQUEST['Duration'])){include_once('../backendphp/layout/clickontabletoedithead.php'); goto noform;}
   switch ($_REQUEST['Duration']){
      case '3 months': $periodindays=90; break;
      case '6 months': $periodindays=180; break;
      case '1 year': $periodindays=365; break;
      case '2 years': $periodindays=730; break;
      case '3 years': $periodindays=1095; break;
   }
   
   $title=$title.' for the past '.$_REQUEST['Duration'].($_REQUEST['WithInv?']==0?' ZERO INVTY':' WITH INVTY');
   
   $sql0='CREATE TEMPORARY TABLE allsoldperperiod AS
   SELECT 
        ss.`ItemCode` AS `ItemCode`,
        SUM(ss.`Qty`) AS `SumOfQty`
    FROM
        (`'.$currentyr.'_1rtc`.`invty_2sale` sm
        JOIN `'.$currentyr.'_1rtc`.`invty_2salesub` ss ON ((sm.`TxnID` = ss.`TxnID`)))
    WHERE
        (sm.`Date` >= (CURDATE() + INTERVAL -('.$periodindays.') DAY))
    GROUP BY ss.`ItemCode` 
    HAVING (SUM(ss.`Qty`) <> 0) 
    UNION ALL 
    
SELECT 
        ss.`ItemCode` AS `ItemCode`,
        SUM(ss.`Qty`) AS `SumOfQty`
    FROM
        (`'.$lastyr.'_1rtc`.`invty_2sale` sm
        JOIN `'.$lastyr.'_1rtc`.`invty_2salesub` ss ON ((sm.`TxnID` = ss.`TxnID`)))
    WHERE
        (sm.`Date` >= (CURDATE() + INTERVAL -('.$periodindays.') DAY))
    GROUP BY ss.`ItemCode`
    HAVING (SUM(ss.`Qty`) <> 0) 
    UNION ALL     
    
SELECT 
        ss.`ItemCode` AS `ItemCode`,
        SUM(ss.`Qty`) AS `SumOfQty`
    FROM
        (`'.$last2yrs.'_1rtc`.`invty_2sale` sm
        JOIN `'.$last2yrs.'_1rtc`.`invty_2salesub` ss ON ((sm.`TxnID` = ss.`TxnID`)))
    WHERE
        (sm.`Date` >= (CURDATE() + INTERVAL -('.$periodindays.') DAY))
    GROUP BY ss.`ItemCode`
    HAVING (SUM(ss.`Qty`) <> 0) ;
    '; $stmt=$link->prepare($sql0); $stmt->execute();
    
    $sql1='CREATE TEMPORARY TABLE allsold SELECT ItemCode,SUM(`SumOfQty`) AS Sold FROM allsoldperperiod GROUP BY ItemCode';
    $stmt=$link->prepare($sql1); $stmt->execute();
    
    $columnnames=array('Category', 'ItemCode', 'Description','Good', 'Defective', 'EndInvToday');
    
    if ($_REQUEST['WithInv?']==0){
      $columnnames=array('Category', 'ItemCode', 'Description');
      $sql2='CREATE TEMPORARY TABLE NoSale AS SELECT `a`.`ItemCode` AS `ItemCode`,  SUM(`a`.`Qty`) AS `EndInvToday`, a.BranchNo
    FROM
        `invty_20uniallposted` `a` LEFT JOIN allsold sold ON a.ItemCode=sold.ItemCode JOIN `1branches` b ON b.BranchNo=a.BranchNo
    WHERE (`b`.`Pseudobranch`=2) AND
        ((`a`.`Date` IS NOT NULL)
            AND (`a`.`Date` <= NOW())) AND sold.ItemCode IS NULL
    GROUP BY `a`.`BranchNo` , `a`.`ItemCode` HAVING `EndInvToday`=0;';
    
      $sql0='SELECT Category, `ns`.`ItemCode` AS `ItemCode`, ItemDesc AS Description 
    FROM `NoSale` `ns` JOIN `invty_1items` i ON i.ItemCode=ns.ItemCode JOIN `invty_1category` c ON c.CatNo=i.CatNo ';
    
    } else {
    $sql2='CREATE TEMPORARY TABLE NoSale AS SELECT `a`.`ItemCode` AS `ItemCode`,  SUM(CASE WHEN Defective=0 THEN Qty END) AS Good,
    SUM(CASE WHEN Defective<>0 THEN Qty END) AS Defective, SUM(`a`.`Qty`) AS `EndInvToday`, a.BranchNo
    FROM
        `invty_20uniallposted` `a` LEFT JOIN allsold sold ON a.ItemCode=sold.ItemCode JOIN `1branches` b ON b.BranchNo=a.BranchNo
    WHERE (`b`.`Pseudobranch`=2) AND
        ((`a`.`Date` IS NOT NULL)
            AND (`a`.`Date` <= NOW())) AND sold.ItemCode IS NULL
    GROUP BY `a`.`BranchNo` , `a`.`ItemCode` HAVING `EndInvToday`<>0;';
    
    $sql0='SELECT Category, `ns`.`ItemCode` AS `ItemCode`, ItemDesc AS Description, IFNULL(Good,0) AS Good, IFNULL(Defective,0) AS Defective,  `EndInvToday`
    FROM `NoSale` `ns` JOIN `invty_1items` i ON i.ItemCode=ns.ItemCode JOIN `invty_1category` c ON c.CatNo=i.CatNo ';
    }
    $stmt=$link->prepare($sql2); $stmt->execute();
    
    //$sortfield=(!isset($_POST['sortfield'])?' Category,ItemCode':$_POST['sortfield']); 
    $subtitle='<b>Central Warehouse</b>';    
    $sql=$sql0.'WHERE BranchNo=0 ';//ORDER BY '.$sortfield;
    include('../backendphp/layout/displayastable.php');
    $subtitle='<b>CDO Warehouse</b>';
    $sql=$sql0.'WHERE BranchNo=27 ';//ORDER BY '.$sortfield;  
    include('../backendphp/layout/displayastable.php');
    $subtitle='<b>Luzon Warehouse</b>';
    $sql=$sql0.'WHERE BranchNo=65 ';//ORDER BY '.$sortfield;  
    include('../backendphp/layout/displayastable.php');
    //$columnsub=$columnnamesleft;
   break;
   

case 'ItemHistory':

   $title='Item 12-Month Purchase & Sale';   
   $fieldname='ItemCode';
?>
<form method="post" action="lookupinvtools.php?w=<?php echo $whichqry; ?>" enctype="multipart/form-data">
 Branch Number <input type="text" name="BranchNo" list="branches"></input>
 Item Code <input type="text" name="<?php echo $fieldname; ?>" list="items"></input>
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
renderlist('items');renderlist('branches');
if (!isset($_REQUEST[$fieldname])){
include_once('../backendphp/layout/clickontabletoedithead.php');
goto noform;
} else {
    $title=$title.' - ItemCode '.$_REQUEST[$fieldname].' Branch No. '.$_REQUEST['BranchNo'];
    $sql='SELECT date_format(Date, \'%b %Y\') as Month, uni.BranchNo, uni.ItemCode, ifnull(Sum(case when uni.txntype in (1,2,5,10) then Qty end)*-1,0) AS Sale, ifnull(Sum(case when uni.txntype in (6,8) then Qty end),0) + ifnull(Sum(case when (uni.txntype in (7) and BranchNo<>0) then Qty end),0) AS Purchase
FROM `invty_20uniallposted` uni join `invty_1items` i on i.ItemCode=uni.ItemCode
where Date is not null and uni.ItemCode='.$_REQUEST[$fieldname].' and BranchNo='.$_REQUEST['BranchNo'].'
GROUP BY Month(Date), uni.BranchNo, uni.ItemCode  order by Date, uni.ItemCode, BranchNo;';
$columnnames=array('Month', 'Sale', 'Purchase');
include('../backendphp/layout/displayastable.php');
}
break;


case 'ProfitPerShipment':
if (!allowedToOpen(741,'1rtc')) { echo 'No permission'; exit;}
   $title='Profitability Per Interbranch Shipment';   
   $fieldname='Branch'; $formdesc='UnitPrice is actual price on the transfer receipts.  Cost is based on latest cost.<br><br>';
?>
<form method="post" action="lookupinvtools.php?w=<?php echo $whichqry; ?>" enctype="multipart/form-data">
Transfer from Warehouse <input type='text' name='wh' list='warehouses' value='Central'></input>&nbsp &nbsp &nbsp &nbsp &nbsp
    From <input type='date' name='fromdate' value="<?php echo date('Y-m-d'); ?>"></input>&nbsp &nbsp &nbsp 
    To <input type='date' name='todate' value="<?php echo date('Y-m-d'); ?>"></input>&nbsp &nbsp &nbsp <br><br>
 Transfer to Branch <input type="text" name="Branch" list="branchnames"></input>
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
renderlist('branchnames');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT * FROM `1branches` WHERE Pseudobranch=2','BranchNo','Branch','warehouses');
if (!isset($_REQUEST[$fieldname])){
include_once('../backendphp/layout/clickontabletoedithead.php');
goto noform;
} else {
    $branchno=comboBoxValueWithSql ($link,'SELECT * FROM `1branches` WHERE Branch LIKE \''.$_REQUEST[$fieldname].'\' ','BranchNo');
    $whno=comboBoxValueWithSql ($link,'SELECT * FROM `1branches` WHERE Branch LIKE \''.$_REQUEST['wh'].'\' ','BranchNo');
    $title=$title.' - FROM '.$_REQUEST['wh'].' TO '.$_REQUEST['Branch'].' ON '.$_REQUEST['fromdate'].' ON '.$_REQUEST['todate'] ;
    $sqltotal='SELECT m.TransferNo,FORMAT(SUM(s.QtySent*s.UnitPrice),2) AS UnitPrice, FORMAT(SUM(s.QtySent*IFNULL(lc.UnitCost,0)),2) AS Cost, FORMAT(SUM(s.QtySent*(s.UnitPrice-IFNULL(lc.UnitCost,0))),2) AS Profit 
FROM `invty_2transfer` m JOIN `invty_2transfersub` s ON m.TxnID=s.TxnID LEFT JOIN `invty_52latestcost` lc ON s.ItemCode=lc.ItemCode
 WHERE m.BranchNo='.$whno.' AND m.ToBranchNo='.$branchno.' AND m.DateOUT BETWEEN \''.$_REQUEST['fromdate'].'\' AND \''.$_REQUEST['todate'].'\'';
$columnnames=array('TransferNo','UnitPrice', 'Cost', 'Profit'); 
$sql=$sqltotal.' GROUP BY m.TxnID;';
$stmttotal=$link->query($sqltotal); $res1=$stmttotal->fetch();
$totalstext='Totals<br><br> UnitPrice '.$res1['UnitPrice'].'<br> Cost '.$res1['Cost'].'<br> Profit '.$res1['Profit'];

include('../backendphp/layout/displayastable.php');
}
break;

default:
    header('Location:'.$_SERVER['HTTP_REFERER']);
}
noform:
    
     $link=null; $stmt=null;
     $link=null; $stmt=null;
?>