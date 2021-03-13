<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(656,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');}
if (!isset($_REQUEST['print'])) { $showbranches=true; include_once('../switchboard/contents.php');} else {
	$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
}

  $showbranches=true;

$title='Creditable Witholding Tax Summary'; $formdesc=$_SESSION['*cname'];
$frommonth=(!isset($_REQUEST['fromdate'])?date('m'):$_REQUEST['fromdate']);
$tomonth=(!isset($_REQUEST['todate'])?date('m'):$_REQUEST['todate']);
if (!isset($_REQUEST['print'])) {
?>
<form method="post" style="display:inline" action="credwtaxsummary.php" enctype="multipart/form-data">
        From Month (1 - 12): &nbsp<input type='text' name='fromdate' size=3 value="<?php echo $frommonth; ?>"></input>&nbsp &nbsp &nbsp 
        To Month (1 - 12): &nbsp<input type='text' name='todate' size=3 value="<?php echo $tomonth; ?>"></input>&nbsp &nbsp &nbsp &nbsp
        <input type="submit" name="w" value="2306"> &nbsp &nbsp &nbsp <input type="submit" name="w" value="2307"></form><br><br>
<?php
}
if(!isset($_REQUEST['w'])){ goto noform;} 
$which=($_REQUEST['w']=='2306'?161:160);
if (!isset($_REQUEST['print'])) {
    ?><form method="post" style="display:inline" action="credwtaxsummary.php?print=1">
        <input type='hidden' name='fromdate' value="<?php echo $frommonth; ?>"></input>
        <input type='hidden' name='todate' value="<?php echo $tomonth; ?>"></input><input type='hidden' name='w' value="<?php echo $which; ?>"></input>
        <input type="submit" name="print" value="Print"></form><br><br> 
    <?php
}
$title=$title.' - '.$_REQUEST['w']; 
$sql0='CREATE TEMPORARY TABLE credwtax AS '
        . 'SELECT m.TxnID, CONCAT("CollectNo ",CollectNo) AS Collection, d.BranchNo, `Date`, c.`ClientName`, c.TIN, CONCAT(StreetAddress, ", ", Barangay, ", ", TownOrCity, ", ", Province) AS Address, 
            (SELECT SUM(s.Amount) FROM  `acctg_2collectsub` s WHERE m.TxnID=s.TxnID) AS ClientPaidAmt, SUM(d.Amount) AS Withheld,if(m.ClientNo=10004,m.Remarks,"") as Remarks 
FROM `acctg_2collectmain` m JOIN `acctg_2collectsubdeduct` d ON m.TxnID=d.TxnID 
JOIN `1branches` b ON b.BranchNo=d.BranchNo
LEFT JOIN `1clients` c ON c.ClientNo=m.`ClientNo` WHERE d.DebitAccountID='.$which.' AND 
    (MONTH(`Date`)>='.$frommonth.' AND MONTH(`Date`)<='.$tomonth.') AND b.CompanyNo='.$_SESSION['*cnum'].' GROUP BY m.TxnID
;'; 
//if(allowedtoopen(2201,'1rtc')){ echo $sql0; exit();}

$stmt0=$link->prepare($sql0); $stmt0->execute();
        
$sql='SELECT cwt.*, Branch,FORMAT(ClientPaidAmt,2) AS ClientPaidAmt, FORMAT(Withheld,2) AS `Withheld'.$_REQUEST['w'].'` FROM credwtax cwt JOIN `1branches` b ON b.BranchNo=cwt.BranchNo ORDER BY Date, Branch';
$columnnames=array('Date','Branch','Collection','ClientName','TIN','Address','Remarks','ClientPaidAmt','Withheld'.$_REQUEST['w']);
$formdesc=$formdesc.' ('. date('F',strtotime(''.$currentyr.'-'.$frommonth.'-1')).' to '. date('F',strtotime(''.$currentyr.'-'.$tomonth.'-1')).') <br>'; 
$sqltotal='SELECT ROUND(SUM(ClientPaidAmt),2) AS ClientPaidAmt, ROUND(SUM(Withheld),2) AS Withheld FROM credwtax ';
$stmt1=$link->query($sqltotal); $restotal=$stmt1->fetch();
$totalstext='Total Client Payments: '.number_format($restotal['ClientPaidAmt'], 2).'<br>Total Withheld ('.$_REQUEST['w'].'): '.number_format($restotal['Withheld'],2);
if (!isset($_REQUEST['print'])) { include('../backendphp/layout/displayastable.php'); } else { include ('../backendphp/layout/printdisplayastable.php');}
        
noform:
      $link=null; $stmt=null; $stmt0=null; $stmt1=null;
?>