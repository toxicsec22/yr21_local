<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(757,'1rtc')) {   echo 'No permission'; exit;}
include_once('../switchboard/contents.php');
 
 
$date=''.$currentyr.'-'.(!isset($_POST['month'])?date('m'):$_POST['month']).'-1';
$month=!isset($_POST['month'])?date('m'):$_POST['month'];
?>
<html><head><title>Sales Below MinPrice</title></head><body>
<br>
<h3>Sales Below MinPrice</h3><br><br>
<form method="post" action="salesbelowminprice.php" enctype="multipart/form-data">
                Choose Month (1 - 12):  <input type="text" name="month" value="<?php echo ($month); ?>"></input>
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
echo 'UNDER CONSTRUCTION'; EXIT();
echo '<i>For the month of <b>'.strtoupper(date('F',strtotime($date))).'</b></i><br>';

if (allowedToOpen(7571,'1rtc')){ $condition='';
} elseif (allowedToOpen(7572,'1rtc')){
     $condition=' and sm.BranchNo in (Select BranchNo from `attend_1branchgroups` g  where g.TeamLeader='.$_SESSION['(ak0)'].') ';
} else { //branch
   $condition=' and sm.BranchNo='.$_SESSION['bnum'];
}   

// step 1

$sql0='create temporary table `unionprices` as
select 
        `lmp`.`ItemCode` AS `ItemCode`,
        `lmp`.`Date` AS `Date`,
        `lmp`.`MinPrice` AS `MinPrice`,
        `lmp`.`PMP` AS `PMP`
    from
        `invty_5lastminprice` `lmp` where `Date`<=(Select last_day(\''.$date.'\'))
    union select 
        `s`.`ItemCode` AS `ItemCode`,
        `m`.`Date` AS `Date`,
        `s`.`MinPrice` AS `MinPrice`,
        `s`.`PMP` AS `PMP`
    from
        (`invty_3order` `m`
        join `invty_3ordersub` `s` ON ((`m`.`TxnID` = `s`.`TxnID`)))
    where
        (`m`.`SupplierNo` > 100) and  `Date`<=(Select last_day(\''.$date.'\'))';

$stmt=$link->prepare($sql0); $stmt->execute();

// step 2

$sql0='create temporary table `getlatestdate` as
select 
        `lmp`.`ItemCode` AS `ItemCode`,
        max(`lmp`.`Date`) AS `MaxOfDate`
    from
        `unionprices` `lmp`
    group by `lmp`.`ItemCode`';

$stmt=$link->prepare($sql0); $stmt->execute();

// step 3

$sql0='create temporary table `latestmp` as
select 
        `lmp`.`ItemCode` AS `ItemCode`,
        `lmp`.`Date` AS `Date`,
        `lmp`.`MinPrice` AS `MinPrice`,
        `lmp`.`PMP` AS `PMP`
    from
        (`unionprices` `lmp`
        join `getlatestdate` `d` ON (((`lmp`.`ItemCode` = `d`.`ItemCode`)
            and (`lmp`.`Date` = `d`.`MaxOfDate`))))
    group by `lmp`.`ItemCode`';

$stmt=$link->prepare($sql0); $stmt->execute();

// step 4

$sql0='create temporary table salesbelowminprice as 
select sm.TxnID, sm.SaleNo, sm.Date,sm.BranchNo, sm.ClientNo, sm.Remarks, mid(sm.`Remarks`,instr(sm.`Remarks`,"approval#")+9) as Approval, b.Branch, c.ClientName, ss.ItemCode, ss.UnitPrice, lmp.MinPrice, lmp.PMP, lmp.`Date` as `MPDate` from `invty_2sale` sm join `invty_2salesub` ss on sm.TxnID=ss.TxnID join `1branches` b on b.BranchNo=sm.BranchNo join `latestmp` lmp on ss.ItemCode=lmp.ItemCode join `1clients` c on c.ClientNo=sm.ClientNo
where ss.UnitPrice<lmp.MinPrice and Month(sm.Date)='.$month.' and sm.Date>lmp.Date and ss.ItemCode<>15 '.$condition.' order by Branch,Date,SaleNo';
$stmt=$link->prepare($sql0); $stmt->execute();

$sql='Select s.* from salesbelowminprice s LEFT join `invty_7specdisctapproval` a on a.TxnID=s.TxnID AND a.ItemCode=s.ItemCode WHERE a.TxnID IS NULL;';
$showbranches=false; 
$columnnames=array('Branch','SaleNo','Date','ClientName','Remarks','ItemCode','UnitPrice','MinPrice','PMP','MPDate');
$subtitle='<br><br>No approvals';
include('../backendphp/layout/displayastableonlynoheaders.php');

$sql='Select s.*, a.* from salesbelowminprice s JOIN `invty_7specdisctapproval` a on a.TxnID=s.TxnID AND a.ItemCode=s.ItemCode WHERE a.SpecPriceApproved>s.UnitPrice;';
$showbranches=false; 
$columnnames=array('Branch','SaleNo','Date','ClientName','Remarks','ItemCode','UnitPrice','MinPrice','PMP','MPDate','SpecPriceRequest','SpecPriceApproved');
$subtitle='<br><br>Unit Price is lower than Approved Special Price';
include('../backendphp/layout/displayastableonlynoheaders.php');
noform:
      $link=null; $stmt=null;
?>
    <br><br>  END OF REPORT
</body></html>
