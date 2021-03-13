<?php
$title='Clear Deposits';
$showbranches=false;
include('banktxnsheader.php');
$allowed=(allowedToOpen(6238,'1rtc'))?true:false;
$show=!isset($_REQUEST['show'])?1:$_REQUEST['show'];
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
if (!isset($_REQUEST['bankname'])){ $bank=!isset($_GET['bank'])?'':$_GET['bank'];} 
else { $bank=comboBoxValue($link,'acctg_1chartofaccounts','ShortAcctID',addslashes($_REQUEST['bankname']),'AccountID'); }
?>
<form method="post" action="cleardeposits.php<?php echo '?bank='.$bank; ?>" style="display:inline">
	<input type="text" name="bankname" list="banks" size=20 autocomplete="off" required="true">
<?php include('bankslist.php'); ?>
<input type=hidden name="show" value=0>
<input type="submit" name="lookup" value="Clear Per Bank">
</form>
<form method="post" action="cleardeposits.php" style="display:inline">
<input type=hidden name="show" value=1>
<input type="submit" name="submit" value="Show All">
</form>
<?php if(allowedToOpen(62512,'1rtc')) { ?>
<form method="post" action="cleardeposits.php" style="display:inline">
<input type=hidden name="show" value=2>
<input type="submit" name="submit" value="Show Non-Banks">
</form>
<?php } ?>
<body><div id="wrap">
	<div id="left">
    <form method="GET" action="prcleardeposits.php<?php echo '?bank='.$bank; ?>" enctype="multipart/form-data">
    <table>
        <thead><tr>
            <td>Bank</td><td>TxnDate</td><td>BankBranch</td><td>Remarks</td><td>Details</td><td>DepositAmt</td><td>OurDepAmt</td><td>OurBranch</td>
	    <?php echo $allowed?'<td>Click to Clear</td>':''; ?>
        </tr></thead>
    <tbody>
    <?php
    if (!isset($_REQUEST['bank'])){ $condition=($show==2?' WHERE un.AccountID NOT IN (Select AccountID FROM `banktxns_1maintaining`) ':''); $groupby=''; $groupby=($show==2?' group by un.AccountID':''); $sqllimitbanks=($show<>2?' JOIN banktxns_1maintaining m ON m.AccountID=un.AccountID  '.$sqllimitbanks:'');}
   // elseif (!isset($_REQUEST['bank'])){ $condition=($show==3?' WHERE un.AccountID=705 ':''); $groupby='';}
    else {
	$condition=' where un.AccountID='.$bank; $groupby=$condition." group by un.AccountID"; $sqllimitbanks=''; 
    } 

    include_once 'sqlphp/dataforcleardep.php';

    $sql="SELECT un.*,Branch AS OurBranch FROM banktxns_33tocleardeposits un LEFT JOIN `1branches` b ON b.BranchNo=un.OurBranchNo ".$sqllimitbanks
            .$condition." order by ShortAcctID, TxnDate,DepositAmt"; 
  //  if ($_SESSION['(ak0)']==1002) { echo $sql.'<br>'.$bank;}
    //to make alternating rows have different colors
        $colorcount=0; 
        $rcolor[0]=isset($color1)?$color1:"CCFFFF";
        $rcolor[1]=isset($color2)?$color2:"FFFFFF";
    
    foreach ($link->query($sql) as $row){
        
        echo "<tr bgcolor=". $rcolor[$colorcount%2]."><td>".$row['ShortAcctID']."</td><td>".$row['TxnDate']."</td><td>".$row['BankBranch']."</td><td>".$row['Remarks'].        "</td><td>".$row['Details']."</td><td>".number_format($row['DepositAmt'],2)."</td><td>".number_format($row['OurDepAmt'],2)."</td><td>".$row['OurBranch']."</td>".
	($allowed?"<td><a href='prcleardeposits.php?txndate=".$row['TxnDate'].(!isset($_REQUEST['bank'])?'':"&bank=".$bank)."&banktxno=". $row['TxnNo'] ."&depno=".$row['DepositNo']."&separate=0'>Clear</a></td>":"")."</tr>";    	$colorcount++;
    }
 //  if($_SESSION['(ak0)']==1002) {echo $sql;}	
    ?>
    </tbody></table> </form>
    <?php
    $sqlsum="SELECT sum(DepositAmt) as UnclearedBankDep FROM banktxns_33tocleardeposits un LEFT JOIN `1branches` b ON b.BranchNo=un.OurBranchNo ".$sqllimitbanks.$groupby;
    $stmtsum=$link->query($sqlsum);
    $resultsum=$stmtsum->fetch();
    echo 'Total Uncleared Bank Deposits  '.number_format($resultsum['UnclearedBankDep'],2);
   ?>
   </div>
<?php 
$defaultdate=!isset($_REQUEST['txndate'])?date('Y-m-d', time() - 60 * 60 * 24):$_REQUEST['txndate'];
?>
	<div id="right">
		<form method="POST" action="prcleardeposits.php<?php echo (!isset($_REQUEST['bank'])?'':"?bank=".$bank); ?>" enctype="multipart/form-data">  
<table>
    <thead>
        <tr><td colspan=4 align=center>List of Uncleared Deposits</td>
		<?php echo $allowed?'<td colspan=3 align=center>Default Clear Date:<br><input type="date" name="txndate" value="'.$defaultdate.'"></td>':"" ;?></tr>
        <tr>
            <td>Bank</td><td>Date</td><td>DepositNo</td><td>Type</td><td>DepAmount</td>
	    <?php echo $allowed?"<td colspan=2><input type='submit' name='submit' value='Choose & Clear Singly'></td>":""; ?>
        </tr></thead>
    <tbody>
    <?php
    //to make alternating rows have different colors
        $colorcount=0; 
        $rcolor[0]=isset($color1)?$color1:"FFFFCC";
        $rcolor[1]=isset($color2)?$color2:"FFFFFF";
    if (allowedToOpen(62512,'1rtc')) { $sql="";} 
    elseif (allowedToOpen(62511,'1rtc')){ // Kristelle only 
        $sql=" JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=un.AccountID AND (ca.AccountID IN (Select AccountID FROM `banktxns_1maintaining`) OR ca.AccountID=705) ";}  
    else {$sql="  "; }
    $sql="SELECT un.* FROM `banktxns_32uncleareddepamt` un ".$sql.' JOIN `1branches` b ON b.BranchNo=un.OurBranchNo '.$sqllimitbanks.$condition." order by Bank,Date,DepositNo"; 
 //   if($_SESSION['(ak0)']==1002) {echo $sql;}	
    ?>
    <input type='hidden' name='separate' value='1'>
<?php    foreach ($link->query($sql) as $row){
        $colorcount++;
        echo "<tr bgcolor=". $rcolor[$colorcount%2]."><td>".$row['Bank']."</td><td>".$row['Date']."</td><td>".$row['DepositNo']."</td><td>".$row['Type']."</td><td>".number_format($row['OurDepAmt'],2)."</td><td align='center' >".($allowed?"<input type='radio' name='depno' value='".$row['DepositNo']."'>":"").'<td><a href="../acctg/addeditdep.php?w=Deposit&TxnID='.$row['TxnID'].'" target=_blank>Lookup</a></td></tr>';
        
    }
		
    ?>
 </table> </form>
    <?php
    
    $sqlsum="SELECT sum(OurDepAmt) as UnclearedOurDep FROM banktxns_32uncleareddepamt un JOIN `1branches` b ON b.BranchNo=un.OurBranchNo  ".$sqllimitbanks.$groupby;
    $stmtsum=$link->query($sqlsum);
    $resultsum=$stmtsum->fetch();
    echo 'Total Uncleared  '.number_format($resultsum['UnclearedOurDep'],2);
     $link=null; $stmt=null;
   ?>
   </div>
</div>
</body>
</html>