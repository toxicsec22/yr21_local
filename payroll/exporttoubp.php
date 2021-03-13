<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(7951,'1rtc')) {   echo 'No permission'; exit;}
$showbranches=false;
include_once('../switchboard/contents.php');
 


$title='Export '.(!isset($_GET['DateofCredit'])?'Payroll':'Special Credits') .' for UBP';
include('payrolllayout/addentryhead.php');
if (isset($_GET['done'])){
	echo '<font color="red">Payroll ID '.$_POST['payrollid'].' has been exported.</font>';
}
?>
<form method="POST" action="exporttoubp.php?done=1" enctype="multipart/form-data">
	<?php
        if (!isset($_GET['DateofCredit'])){
        $payrollid=(isset($_SESSION['payrollidses'])?$_SESSION['payrollidses']:((date('m')*2)+(date('d')<15?-1:0)));
	 ?>
        For Payroll ID<input type='text' name='payrollid' list='payperiods' required=true autocomplete=off value='<?php echo $payrollid?>'></input>
	&nbsp &nbsp Batch<input type='text' name='Batch' value='01' autocomplete='off'><input type='hidden' name='w' value='Payroll'>
<input type="submit" name="submit" value="Prepare">
<?php 
    include_once "../generalinfo/lists.inc"; renderlist('payperiods'); 
        } else {
            
        }
?>
</form>
<?php
if (!isset($_POST['payrollid']) AND !isset($_GET['DateofCredit'])){
	goto noform;
    }
    
if (isset($_GET['DateofCredit'])){
   $dateofcredit=$_GET['DateofCredit'];
   $Batch=(!isset($_GET['Batch'])?'01':str_pad($_GET['Batch'],2,'0',STR_PAD_LEFT));
   $PayDate = date('mdY',strtotime($dateofcredit));

$sql1='CREATE TEMPORARY TABLE `ubppayroll` (
  `IDNo` smallint(6) NOT NULL,
  `UBPATM` varchar(30) NOT NULL,
  `NetPay` double DEFAULT NULL,
  UNIQUE KEY `PayrollID_UNIQUE` (`IDNo`))
    SELECT p.TxnID, ps.IDNo, UBPATM, truncate(Amount,2) as NetPay FROM `payroll_30othercreditsmain` as p join `payroll_30othercreditssub` as ps on ps.TxnID=p.TxnID join `1employees` as e on ps.IDNo=e.IDNo WHERE Posted=1 AND DisburseVia=3 AND Amount>0 and DateofCredit=\'' . $_GET['DateofCredit'].'\' AND Batch='.$_GET['Batch'];

} else {    
$payrollid=$_POST['payrollid']; $Batch=(!isset($_POST['Batch'])?'01':str_pad($_POST['Batch'],2,'0',STR_PAD_LEFT)); //$Batch='01';
$sql0='SELECT `PayrollDate` FROM `payroll_1paydates` WHERE `PayrollID`=' . $payrollid;
$stmt0=$link->query($sql0);
        $result0=$stmt0->fetch();  
$PayDate = date('mdY',strtotime($result0['PayrollDate']));
   
    $sql1='CREATE TEMPORARY TABLE `ubppayroll` (
  `PayrollID` tinyint(3) unsigned NOT NULL,
  `IDNo` smallint(6) NOT NULL,
  `UBPATM` varchar(30) NOT NULL,
  `NetPay` double DEFAULT NULL,
  UNIQUE KEY `PayrollID_UNIQUE` (`PayrollID`,`IDNo`))
    SELECT PayrollID, p.IDNo, UBPATM, truncate(NetPay,2) as NetPay FROM `payroll_25payrolldatalookup` as p join `1employees` as e on p.IDNo=e.IDNo WHERE DisburseVia=3 and NetPay>0 and PayrollID=' . $payrollid.'  AND (PayrollID NOT IN (Select PayrollID from payroll_26approval WHERE Approved=0 OR Approved IS NULL))';
    /* This is hash of BPI. No hash for UBP yet.
  `UBPHash` bigint(20) NOT NULL,
     * round(((substr(Lpad(UBPATM,10,"0"),5,2)*truncate(NetPay,2))
    +(substr(Lpad(UBPATM,10,"0"),7,2)*truncate(NetPay,2))+(substr(Lpad(UBPATM,10,"0"),9,2)*truncate(NetPay,2)))*100,0) as UBPHash
     * 
     */
// echo $sql1; exit();
}

$filename=$PayDate.'_'.$Batch.'.txt';

$stmt1=$link->prepare($sql1);
$stmt1->execute();

$stmtt=$link->query('Select Count(`IDNo`) as TotalCount,SUM(`NetPay`) as TotalAmount from `ubppayroll`');
$resultt=$stmtt->fetch();
/*
$stmthash=$link->query('Select Sum(`UBPHash`) as UBPHashSum from `ubppayroll`');
$resulthash=$stmthash->fetch();
$hashsum=str_pad($resulthash['UBPHashSum'],18,'0',STR_PAD_LEFT);
    */
$ubpexport = 'H||'.$PayDate.'|1'.PHP_EOL;
$ubpexportper='';

$stmt=$link->query('Select p.*, (@ln:=@ln+1) AS LineNo FROM `ubppayroll` p, (SELECT @ln:=0) AS t');
$result=$stmt->fetchAll();

foreach ($result as $row){
    $ubpexportper=$ubpexportper.'D|'.$row['UBPATM'].'|'.$row['LineNo'].'|'.str_pad(str_replace('.','',$row['NetPay']*100),12,"0",STR_PAD_LEFT).'|'.PHP_EOL;
}
$ubpexport=$ubpexport.$ubpexportper;
/* The summary line is not required by UBP for now
 *    .'S|'.$resultt['TotalCount'].'|'.str_pad(str_replace('.','',$resultt['TotalAmount']*100),12,"0",STR_PAD_LEFT).'|';//.$hashsum.'';
 */

//end file

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
   <input type='hidden' name='payrolldata' value='<?php echo $ubpexport; ?>'>
   <input type='hidden' name='filename' value='<?php echo $filename; ?>'>
</form><br><br>
<?php
echo $ubpexport;
noform:
     $link=null; $stmt=null;
?>
</body>
</html>