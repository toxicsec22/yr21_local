<?php
$title='Clear Checks';
$path=$_SERVER['DOCUMENT_ROOT'];
$showbranches=false;
include('banktxnsheader.php');
	if (!allowedToOpen(6237,'1rtc')) { echo 'No permission'; exit;}
$show=!isset($_REQUEST['show'])?1:$_REQUEST['show'];
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
if (!isset($_REQUEST['bankname'])){ $bank=!isset($_GET['bank'])?'':$_GET['bank'];} 
else { $bank=comboBoxValue($link,'acctg_1chartofaccounts','ShortAcctID',$_REQUEST['bankname'],'AccountID'); }
if(allowedToOpen(5432,'1rtc')) { ?>
?>
<form method="post" action="clearchecks.php<?php echo '?bank='.$bank; ?>" style="display:inline">
	<input type="text" name="bankname" list="banks" size=20 autocomplete="off" required="true">
<?php include('bankslist.php'); ?>
<input type=hidden name="show" value=0>
<input type="submit" name="lookup" value="Clear Per Bank">
</form>
<form method="post" action="clearchecks.php" style="display:inline">
<input type=hidden name="show" value=1>
<input type="submit" name="submit" value="Show All">
</form>
<?php
}
if(allowedToOpen(62371,'1rtc')) { ?>
<form method="post" action="clearchecks.php" style="display:inline">
<input type=hidden name="show" value=2>
<input type="submit" name="submit" value="Show Non-Banks">
</form>
<?php } ?>
<body><div id="wrap">
	<div id="left">
    <form method="GET" action="prclearchecks.php" enctype="multipart/form-data">
    <table>
        <thead>
            
            <tr>
            <td>Bank</td><td>TxnDate</td><td>BankBranch</td><td>Remarks</td><td>BankCheckNo</td><td>OurCheckNo</td><td>WithdrawAmt</td><td>AmtofCheck</td><td>Click to Clear</td>
        </tr></thead>
    <tbody>
    <?php
    //to make alternating rows have different colors
        $colorcount=0; 
        $rcolor[0]=isset($color1)?$color1:"FFFFCC";
        $rcolor[1]=isset($color2)?$color2:"FFFFFF";
    
     

    include_once 'sqlphp/dataforclearchecks.php';

    $sql="SELECT cc.* FROM banktxns_23toclearchecks cc JOIN banktxns_1maintaining m ON m.AccountID=cc.AccountID ".$sqllimitbanks.' WHERE `TxnDate` <= (CURDATE() + INTERVAL 3 DAY) '.$condition." ORDER BY ShortAcctID, TxnDate, WithdrawAmt"; //if ($_SESSION['(ak0)']==1002) { echo $sql.$show;}
    foreach ($link->query($sql) as $row){
echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td>'.$row['ShortAcctID'].'</td><td>'.$row['TxnDate'].'</td><td>'.$row['BankBranch'].'</td><td>'.$row['Remarks'].'</td><td>'.$row['BankCheckNo'].'</td><td>'.$row['OurCheckNo'].'</td><td>'.number_format($row['WithdrawAmt'],2).'</td><td>'.number_format($row['AmountofCheck'],2).'</td><td><a href="prclearchecks.php?txndate='.$row['TxnDate'].'&banktxno='. $row['TxnNo'] .'&vchno='.$row['CVNo'].'&currentyr='.$row['CurrentYr'].'&separate=0">Clear</a></td></tr>';
        $colorcount++;
    }
		
    ?>
    </tbody></table>
    </form></div>
<?php 
$defaultdate=!isset($_REQUEST['txndate'])?date('Y-m-d', time() - 60 * 60 * 24):$_REQUEST['txndate'];
?>
	<div id="right">
  <form method="POST" action="prclearchecks.php" enctype="multipart/form-data">  
 <table>
    <thead>
        <tr><td colspan=4 align=center>List of Uncleared Dated Checks</td><td colspan=3 align=center>Default Clear Date:<br><input type='date' name='txndate' value='<?php echo $defaultdate; ?>'></td></tr>
        <tr>
            <td>Bank</td><td>DateofCheck</td><td>OurCheckNo:  Payee</td><td>AmtofCheck</td><td colspan=3><input type='submit' name='submit' value='Choose & Clear Singly'></td>
        </tr></thead>
    <tbody>
    <?php
    $sql="SELECT un.*, CONCAT(CheckNo,'&nbsp; &nbsp; &nbsp; ',Payee) AS `CheckNo & Payee` FROM banktxns_22unclearedcheckamts un ".(allowedToOpen(62371,'1rtc')?"":" JOIN banktxns_1maintaining m ON un.FromAccountID=m.AccountID ".$sqllimitbanks)." WHERE DateofCheck<=Date_Add(Now(), Interval 7 day) AND (ReleaseDate IS NOT NULL) ".($show==2?' AND FromAccountID NOT IN (Select AccountID FROM `banktxns_1maintaining`) ':'').$condition2." order by FromAccount, CheckNo";
   ?>
       <input type='hidden' name='separate' value='1'>
       
        
 <?php
 //to make alternating rows have different colors
        $colorcount=0; 
        $rcolor[0]=isset($color1)?$color1:"CCFFFF";
        $rcolor[1]=isset($color2)?$color2:"FFFFFF";
        
 foreach ($link->query($sql) as $row){
        $colorcount++;
        echo "<tr bgcolor=". $rcolor[$colorcount%2]."><td>".$row['FromAccount']."</td><td>".$row['DateofCheck']."</td><td>".$row['CheckNo & Payee'].
        "</td><td>".number_format($row['AmountofCheck'],2)."</td>
        <td align='center' ><input type='radio' name='vchno' value='".$row['CVNo']."'>
      <input type='hidden' name='currentyr' value='".$row['CurrentYr']."'></td>"
        . '<td><a href="../acctg/formcv.php?w=CV&CVNo='.$row['TxnID'].'" target=_blank>Lookup</a></td></tr>';
 
    }
 $link=null; $stmt=null; 	
    ?>
 </table>
    </form></div>
</div></body>
</html>