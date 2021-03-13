<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(796,'1rtc')) {   echo 'No permission'; exit;}
include_once('../switchboard/contents.php');
 


$title='Export Payroll for BPI';
include('payrolllayout/addentryhead.php');
if (isset($_GET['done'])){
	echo '<font color="red">Payroll ID '.$_POST['payrollid'].' has been exported.</font>';
}
?>
<form method="POST" action="exporttobpi.php?done=1" enctype="multipart/form-data">
	<?php
     
          if (allowedToOpen(795,'1rtc')) {   
              $payrollid=(isset($_SESSION['payrollidses'])?$_SESSION['payrollidses']:((date('m')*2)+(date('d')<15?-1:0)));
	 ?>
        For Payroll ID<input type='text' name='payrollid' list='payperiods' required=true autocomplete=off value='<?php echo $payrollid?>'></input>
	&nbsp &nbsp Batch<input type='text' name='Batch' value='01' autocomplete='off'><input type='hidden' name='w' value='Payroll'>
	<?php
        }
		
		if (!isset($_GET['DateofCredit'])){
       ?>
<input type="submit" name="submit" value="Prepare"><?php  }?>
<?php include_once "../generalinfo/lists.inc"; renderlist('payperiods'); ?>
</form>
<?php
if (!isset($_POST['payrollid']) AND !isset($_GET['DateofCredit'])){
	goto noform;
    }
if (isset($_GET['DateofCredit'])){
   $dateofcredit=$_GET['DateofCredit'];
   $Batch=(!isset($_GET['Batch'])?'01':str_pad($_GET['Batch'],2,'0',STR_PAD_LEFT));
   $PayDate = date('mdy',strtotime($dateofcredit));

$sql1='CREATE TEMPORARY TABLE `bpipayroll` (
  `TxnID` int(11) unsigned NOT NULL,
  `IDNo` smallint(6) NOT NULL,
  `ATM` bigint(20) NOT NULL,
  `Amount` double DEFAULT NULL,
  `BPIHash` bigint(20) NOT NULL)
    SELECT p.TxnID, ps.IDNo, ATM, truncate(Amount,2) as NetPay, round(((substr(Lpad(ATM,10,"0"),5,2)*truncate(Amount,2))
    +(substr(Lpad(ATM,10,"0"),7,2)*truncate(Amount,2))+(substr(Lpad(ATM,10,"0"),9,2)*truncate(Amount,2)))*100,0) as BPIHash FROM `payroll_30othercreditsmain` as p join `payroll_30othercreditssub` as ps on ps.TxnID=p.TxnID join `1employees` as e on ps.IDNo=e.IDNo WHERE Posted=1 AND Amount>0 and DateofCredit=\'' . $_GET['DateofCredit'].'\' AND Batch='.$_GET['Batch'];

} else {
$payrollid=$_POST['payrollid']; $Batch=(!isset($_POST['Batch'])?'01':str_pad($_POST['Batch'],2,'0',STR_PAD_LEFT)); //$Batch='01';
$sql0='SELECT `PayrollDate` FROM `payroll_1paydates` WHERE `PayrollID`=' . $payrollid;
$stmt0=$link->query($sql0);
	//$stmt0->execute();
        $result0=$stmt0->fetch();  
$PayDate = date('mdy',strtotime($result0['PayrollDate']));
    
    $sql1='CREATE TEMPORARY TABLE `bpipayroll` (
  `PayrollID` tinyint(3) unsigned NOT NULL,
  `IDNo` smallint(6) NOT NULL,
  `ATM` bigint(20) NOT NULL,
  `NetPay` double DEFAULT NULL,
  `BPIHash` bigint(20) NOT NULL,
  `DisburseVia` tinyint(1) NOT NULL,
  `TxnID` int(11) NOT NULL ,
  PRIMARY KEY (`TxnID`),
  UNIQUE KEY `PayrollID_UNIQUE` (`PayrollID`,`IDNo`))
    SELECT PayrollID, p.IDNo, ATM, truncate(NetPay,2) as NetPay, round(((substr(Lpad(ATM,10,"0"),5,2)*truncate(NetPay,2))
    +(substr(Lpad(ATM,10,"0"),7,2)*truncate(NetPay,2))+(substr(Lpad(ATM,10,"0"),9,2)*truncate(NetPay,2)))*100,0) as BPIHash, DisburseVia, TxnID FROM `payroll_25payrolldatalookup` as p join `1employees` as e on p.IDNo=e.IDNo WHERE DisburseVia=1 and NetPay>0 and PayrollID=' . $payrollid.'  AND (PayrollID NOT IN (Select PayrollID from payroll_26approval WHERE Approved=0 OR Approved IS NULL))';
//echo $sql1."<br>";
}
//$Batch = '01';
$filename='45600.'.$Batch;
//$file = fopen($filename, "w+b");
// echo $sql1; exit();
$stmt1=$link->prepare($sql1);
	$stmt1->execute();


// $sql='UPDATE `payroll_30othercreditsmain` SET Posted=1 WHERE DateofCredit=\''.$dateofcredit.'\' ';

// $stmt=$link->prepare($sql);
// $stmt->execute();

$CoID = '45600';
$stmtceiling=$link->query('Select Max(`NetPay`) as ceiling from `bpipayroll`');
$resultceiling=$stmtceiling->fetch();
$ceiling=str_pad($resultceiling['ceiling']*100,12,'0',STR_PAD_LEFT);

$stmtfirstsum=$link->query('Select Sum(`NetPay`) as firstsum from `bpipayroll`');
$resultfirstsum=$stmtfirstsum->fetch();
$firstsum=str_pad($resultfirstsum['firstsum']*100,12,'0',STR_PAD_LEFT);
$secondsum=str_pad($resultfirstsum['firstsum']*100,15,'0',STR_PAD_LEFT);

$stmtatm=$link->query('Select Sum(`ATM`) as ATMSum from `bpipayroll`');
$resultatm=$stmtatm->fetch();
$atmsum=str_pad($resultatm['ATMSum'],15,'0',STR_PAD_LEFT);

$stmthash=$link->query('Select Sum(`BPIHash`) as BPIHashSum from `bpipayroll`');
$resulthash=$stmthash->fetch();
$hashsum=str_pad($resulthash['BPIHashSum'],18,'0',STR_PAD_LEFT);
    
$bpiexport = 'H' . $CoID . $PayDate . $Batch. '14281000654428' . $ceiling . $firstsum. str_pad('1',76,' ',STR_PAD_RIGHT).PHP_EOL;
$bpiexportper='';
$stmt=$link->query('Select * from `bpipayroll`');
$result=$stmt->fetchAll();
$resultcount=$stmt->rowCount();

foreach ($result as $row){
    $bpiexportper=$bpiexportper.'D'.$CoID.$PayDate.$Batch.'3'.str_pad($row['ATM'],10,'0',STR_PAD_LEFT).str_pad($row['NetPay']*100,12,'0',STR_PAD_LEFT).str_pad($row['BPIHash'],12,'0',STR_PAD_LEFT).str_pad('',79,' ',STR_PAD_RIGHT).PHP_EOL; //REMOVE <BR> WHEN DOWNLOADING
}
$bpiexport=$bpiexport.$bpiexportper.'T'.$CoID.$PayDate. $Batch. '24281000654'.$atmsum.$secondsum.$hashsum.str_pad($resultcount,5,'0',STR_PAD_LEFT).str_pad('',50,' ',STR_PAD_RIGHT);
//echo $PayDate.'<br>';
//echo $ceiling.'<br>';
//echo $firstsum.'<br>';
//echo $bpiexport;
//fwrite($file, $bpiexport);
//fclose($file);

if (isset($_GET['DateofCredit'])){
   echo '<font color="red">Special Credits for '.$_GET['DateofCredit']. ' has been prepared for download.</font><br><br>';
   echo '<font color="Black">Total Amount '.$_GET['Amount']. '</font><br><br>';
} else {
$sql='UPDATE `payroll_1paydates` SET Posted=1, PostedByNo=\''.$_SESSION['(ak0)'] . '\', TimeStamp=Now() WHERE Posted=0 and PayrollID='.$payrollid;
$stmt=$link->prepare($sql);
$stmt->execute();


echo '<font color="red">Payroll ID '.$payrollid. ' has been exported for download.</font><br><br>';
}
?>
<form style="display: inline" action='downloadpayrollfile.php' method='post'>
   <input type='submit' name='download' value='Download'>
   <input type='hidden' name='payrolldata' value='<?php echo $bpiexport; ?>'>
   <input type='hidden' name='filename' value='<?php echo $filename; ?>'>
</form><br><br>
<?php
echo $bpiexport;
noform:
     $link=null; $stmt=null;
?>
</body>
</html>