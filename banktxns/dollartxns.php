<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6241,'1rtc')) {   echo 'No permission'; exit;} 
include_once('../switchboard/contents.php');

include_once $path.'/acrossyrs/commonfunctions/datefunctions.php';
	
	$today=getdate();
	$datelastmonth=date("Y-m-t", strtotime("last month"));//date('Y-m-d',strtotime("$y-$m-$d"));
?>
<html>
<head>
<title>Bank - Actual Data</title>
<?php
include_once('../backendphp/layout/regulartablestyle.php');
?>
<br>
</head>
<body>
<form method="POST" action="dollartxns.php" enctype="multipart/form-data">
<?php
$year=$currentyr;
include('../backendphp/layout/choosemonth.php');
  ?>
<input type="text" name="bank" list="banks" size=60 autocomplete="off" required="true">
<datalist id="banks"> 
<?php  
            $sql="SELECT * FROM banktxns_1maintaining where AccountID=132 or AccountID=133 order by `Order`";
		foreach ($link->query($sql) as $row) {
                ?>
                <option value="<?php echo $row['ShortAcctID']; ?>" label="<?php echo $row['AccountID']. " - " . $row['AcctNo']; ?>"></option>
                <?php
                } // end while
                ?>
                
</datalist>
<input type="submit" name="lookup" value="Lookup">
<?php
if (!isset($_POST['month'])){
	goto noform;
    }
    ?>
    <br>
	<b><?php echo $_POST['bank'] . ' as of ' . $_POST['month'];?></b>
<li>
<table style="display: inline-block; border: 1px solid; ">
        <thead><tr><td>TxnDate</td><td>WithdrawAmt</td><td>DepositAmt</td><td>Balance</td><td>Cleared</td><td>Remarks</td></tr></thead>
    <tbody>
    <?php
  
    $sqldate=$_POST['month'];
    $datelastmonth=datelastmonth($sqldate);
    $acctid=$_POST['bank'];
   
    $sql="SELECT `banktxns_1maintaining`.ShortAcctID, 0 as TxnNo, `banktxns_bankbalancespermonth`.AccountID, DateofBal as TxnDate, 0 as WithdrawAmt, BalPerMonth as DepositAmt, BalPerMonth as Balance, 1 as Cleared, 'Balance' as Remarks FROM `banktxns_1maintaining` INNER JOIN banktxns_bankbalancespermonth ON `banktxns_1maintaining`.AccountID = banktxns_bankbalancespermonth.AccountID WHERE ((DateofBal)='$datelastmonth' AND `banktxns_1maintaining`.ShortAcctID='$acctid')";

    foreach ($link->query($sql) as $row){
            echo "<tr><td>".$row['TxnDate']."</td><td>".number_format($row['WithdrawAmt'],2)."</td><td>".number_format($row['DepositAmt'],2)."</td><td>".number_format($row['Balance'],2)."</td><td>".$row['Cleared']."</td><td>".$row['Remarks']."</td></tr>";
    }
    $sql="SELECT TxnNo, banktxns_banktxns.AccountID, banktxns_banktxns.TxnDate, ifnull(`WithdrawAmt`,0) AS Withdraw, ifnull(`DepositAmt`,0) AS Deposit, banktxns_banktxns.Balance, banktxns_banktxns.Cleared, `Particulars` & \" \" & `BankBranch` & \" \" & `CheckNo` & \" \" & `BankTransCode` & \" \" & banktxns_banktxns.`Remarks` AS Details FROM banktxns_1maintaining INNER JOIN banktxns_banktxns ON banktxns_1maintaining.AccountID = banktxns_banktxns.AccountID WHERE (((banktxns_banktxns.TxnDate)>'$datelastmonth' And (banktxns_banktxns.TxnDate)<='$sqldate') AND ((Month(`TxnDate`))=Month('$sqldate'))  AND `banktxns_1maintaining`.ShortAcctID='$acctid')
ORDER BY AccountID, `TxnNo`";
    foreach ($link->query($sql) as $row){
            echo "<tr><td>".$row['TxnDate']."</td><td>".number_format($row['Withdraw'],2)."</td><td>".number_format($row['Deposit'],2)."</td><td>".number_format($row['Balance'],2)."</td><td>".$row['Cleared']."</td><td>".$row['Details']."</td></tr>";
    }
noform:	
     $link=null; $stmt=null;
    ?>
    </tbody></table>
<br>

</form>
</body>
</html>