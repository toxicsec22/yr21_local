<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(55061,'1rtc')) { echo 'No permission'; exit; }
if (!isset($_GET['print'])){ include_once('../switchboard/contents.php');}
 


$title='Export List for PDC Warehousing';
/* DID NOT USE THIS
function truncate($string,$length=50,$append="&hellip;") {
  $string = trim($string);

  if(strlen($string) > $length) {
    $string = wordwrap($string, $length);
    $string = explode("\n", $string, 2);
    $string = $string[0] . $append;
  }

  return $string;
}
*/

if (!isset($_GET['print'])){
    ?><title>Export PDC List</title>
<form method="POST" action="exportpdc.php?done=1" enctype="multipart/form-data">
Batch<input type='text' name='Batch' value='01' autocomplete='off'>
<input type="submit" name="submit" value="Prepare">
</form>
<?php
if (!isset($_POST['submit'])) { echo(microtime(get_as_float)); goto noform;}
/*
 * NOT FINISHED TIL UBP ANSWERS
 * 
 * 1. Check amounts must have 3 decimal places?
2. Whose account number this? Account number of check or account number where it will be deposited?
3. CustomerID, ReferenceNumber,DocumentID -- are these our internal info? Are there limitations to the type of field, such as numbers only or with other characters?
 * 4. I assume that date format is mm/dd/yyyy
5. aside from "local", is "regional" an option?
 * 6. Does Payor's Name have limitations regarding special characters?
 * 7. The timestamp has microseconds? And does it have to be unique?
 */
// THIS IS FOR UBP CHECK WAREHOUSING
$ubppayeecode='SC001';
$batch=(!isset($_POST['Batch'])?'01':str_pad($_POST['Batch'],2,'0',STR_PAD_LEFT));
    $sql0='CREATE TEMPORARY TABLE `list` AS SELECT c.`ClientName` AS SubscriberName, up.ClientNo AS SubscriberNumber, up.CRNo AS ReferenceNumber, 
        TRUNCATE(SUM(`PDC`),2) AS CheckAmount, date_format(`DateofPDC`, \'%m/%d/%Y\') AS CheckDate, date_format(`DepositOnDate`, \'%m/%d/%Y\') AS CheckPostingDate, PDCNo AS CheckNumber, PDCBank AS DraweeBank,
        PDCBRSTN AS BRSTN, b.CompanyNo FROM acctg_undepositedclientpdcs up
        JOIN `1clients` c ON c.ClientNo=up.ClientNo JOIN `acctg_2collectmain` om ON up.CRNo=om.`CollectNo` 
            and up.PDCNo=om.`CheckNo` and up.PDCBank=IFNULL(om.`CheckBank`,"")
    JOIN `1branches` b ON b.BranchNo=up.BranchNo 
    WHERE up.SendToBank=1 AND up.AtOffice=1 AND up.WithBank=0 AND `PDC`<>0 AND DATEDIFF(DateofPDC,CURDATE())>7 GROUP BY `DateofPDC`,up.`ClientNo`,`PDCNo` ORDER BY `DateofPDC`,c.`ClientName`,`PDCNo`;';
$stmt0=$link->prepare($sql0); $stmt0->execute();

$stmt=$link->query('SELECT CONCAT(FirstName, " ",SurName) AS FullName FROM `1employees` WHERE IDNo='.$_SESSION['(ak0)']); 
$resultby=$stmt->fetch(); $createdby=$resultby['FullName'];

$stmt=$link->query('Select l.CompanyNo, CompanyName, Now() AS DateCreated, AcctNo from `list` l JOIN `1companies` c ON c.CompanyNo=l.CompanyNo JOIN banktxns_1maintaining m ON l.CompanyNo=m.OwnedByCompany WHERE m.ShortAcctID LIKE "UBP%" GROUP BY l.CompanyNo'); 
$resultco=$stmt->fetchAll();

$findAndReplace = array("-" => " ", "&" => "and", "'" => "", "," => " ");

foreach($resultco as $co){
$pdcexport=''; $totalcheckamt=0; $pdcexportpdf='';

$stmt=$link->query('Select @curRow := @curRow + 1 AS `No.`, l.* from `list` l JOIN (SELECT @curRow := 0) r WHERE CompanyNo='.$co['CompanyNo']); 
$result=$stmt->fetchAll(); $resultcount=$stmt->rowCount();
$filecontrolnumber=date('ymd').$co['CompanyNo'].$batch;
foreach ($result as $row){
    // substr(str_replace(array_keys($findAndReplace), array_values($findAndReplace), $row['SubscriberName']),0,50).','.$row['SubscriberNumber'].','.$row['ReferenceNumber'].','.
    $pdcexport.='D,'.number_format($row['CheckAmount'], 3).','.$row['BRSTN']
            .$row['CheckDate'].','.$row['CheckPostingDate'].','.$row['CheckNumber'].','
            .str_replace(array_keys($findAndReplace), array_values($findAndReplace), $row['DraweeBank']).','
            .',,,,,,,,,,,,'.$filecontrolnumber.PHP_EOL; 
    $pdcexportpdf.='<tr><td>'.$row['No.'].'</td><td>'.str_replace(array_keys($findAndReplace), array_values($findAndReplace), $row['SubscriberName']).'</td><td>'.number_format($row['CheckAmount'],2).'</td><td>'.$row['CheckNumber']
            .'</td><td>'.str_replace(array_keys($findAndReplace), array_values($findAndReplace), $row['DraweeBank']).'</td><td><div  style="font-size: x-small; width:100;">___ Received<br>___ Not Received<br>___ Reject</div></td><td></td></tr>';
    $totalcheckamt=$totalcheckamt+$row['CheckAmount'];
}
$pdcexport='P,'.$ubppayeecode.PHP_EOL.$pdcexport;
$filename=$filecontrolnumber;

$pdcexportpdf='<h3><center>CHECK WAREHOUSING REQUEST FORM</center></h3>PROCESSING CENTER:  ________________<BR>TRANSACTION REF. NO. : <b>'.$filecontrolnumber
        .'</b><BR>ADDRESS:  ________________<BR>CUSTOMER NAME:  <b>'.$co['CompanyName'].'</b><BR>DATE CREATED:  '.$co['DateCreated'].'<BR>CREATED BY:  '.$createdby
        .'<BR>DATE AUTHORIZED:  ________________<BR>ACCOUNT TO BE CREDITED:  '.$co['AcctNo'].'<BR><BR>CHECK DETAILS<BR><BR>'
        . '<table><tr><td>Item</td><td>Subscriber Name</td><td>Check Amount</td><td>Check Number</td><td>Drawee Bank</td>'
        . '<td>Check Receipt</td><td>Reason for Reject</td></tr>'.$pdcexportpdf
        .'</table>TOTAL CHECK ITEM(S): '.$resultcount.'<BR>TOTAL CHECK AMOUNT(S): '.number_format($totalcheckamt,2).'<BR><BR>'
        .'BANK ACKNOWLEDGEMENT RECEIPT:<BR><BR>Total Items/Received:<BR><BR>Total Check Amount:<BR><BR>Received By:<BR><BR>Date Received:<BR><BR><BR><HR>';

?>
<form style="display: inline;" action='<?php echo $path; ?>/acrossyrs/commonfunctions/downloadascsv.php' method='post'>
   <input type='submit' name='download' value='Download as CSV'>
   <input type='hidden' name='data' value='<?php echo $pdcexport; ?>'>
   <input type='hidden' name='filename' value='<?php echo $filename.'.csv'; ?>'>
</form> &nbsp; &nbsp;
<form style="display: inline;" action='exportpdc.php?print=1' method='post' target='_blank'>
   <input type='submit' name='submit' value='Print'>
   <input type='hidden' name='datapdf' value='<?php echo $pdcexportpdf; ?>'>
   <input type='hidden' name='filenamepdf' value='<?php echo $filename; ?>'>
</form>
<br><br>
<?php

echo '<font color="red">'.$co['CompanyName'].' PDC list has been exported for download.</font><br><br>';
echo '<br><br>'.$pdcexport.'<br><br><hr><br>'.$pdcexportpdf.'<br><br>';
}
goto noform;} else { 
    ?><title><?php echo $_POST['filenamepdf']; ?></title>
<style>
    body { font-family: Arial; font-size: small; line-height: 150%; margin: 15mm;}
     table,tr,td {
        border:1px solid black; border-collapse: collapse;
        padding: 1px;
        font-family: Arial; font-size: small;
    }

a:link {    text-decoration: none;}
</style>
<?php
    echo $_POST['datapdf'];
    
}

/* THE FOLLOWING IS FOR METROBANK PDC WAREHOUSING
 * 
 
$batch=(!isset($_POST['Batch'])?'01':str_pad($_POST['Batch'],2,'0',STR_PAD_LEFT));
    $sql0='CREATE TEMPORARY TABLE `list` AS SELECT c.`ClientName` AS SubscriberName, up.ClientNo AS SubscriberNumber, up.CRNo AS ReferenceNumber, 
        TRUNCATE(SUM(`PDC`),2) AS CheckAmount, date_format(`DateofPDC`, \'%m/%d/%Y\') AS CheckDate, date_format(`DepositOnDate`, \'%m/%d/%Y\') AS CheckPostingDate, PDCNo AS CheckNumber, PDCBank AS DraweeBank,
        PDCBRSTN AS BRSTN, b.CompanyNo FROM acctg_undepositedclientpdcs up
        JOIN `1clients` c ON c.ClientNo=up.ClientNo JOIN `acctg_2collectmain` om ON up.CRNo=om.`CollectNo` 
            and up.PDCNo=om.`CheckNo` and up.PDCBank=IFNULL(om.`CheckBank`,"")
    JOIN `1branches` b ON b.BranchNo=up.BranchNo 
    WHERE up.SendToBank=1 AND up.AtOffice=1 AND up.WithBank=0 AND `PDC`<>0 AND DATEDIFF(DateofPDC,CURDATE())>7 GROUP BY `DateofPDC`,up.`ClientNo`,`PDCNo` ORDER BY `DateofPDC`,c.`ClientName`,`PDCNo`;';
$stmt0=$link->prepare($sql0); $stmt0->execute();

$stmt=$link->query('SELECT CONCAT(FirstName, " ",SurName) AS FullName FROM `1employees` WHERE IDNo='.$_SESSION['(ak0)']); 
$resultby=$stmt->fetch(); $createdby=$resultby['FullName'];

$stmt=$link->query('Select l.CompanyNo, CompanyName, Now() AS DateCreated, AcctNo from `list` l JOIN `1companies` c ON c.CompanyNo=l.CompanyNo JOIN banktxns_1maintaining m ON l.CompanyNo=m.OwnedByCompany WHERE m.ShortAcctID LIKE "MB%" GROUP BY l.CompanyNo'); 
$resultco=$stmt->fetchAll();

$findAndReplace = array("-" => " ", "&" => "and", "'" => "", "," => " ");

foreach($resultco as $co){
$pdcexport=''; $totalcheckamt=0; $pdcexportpdf='';

$stmt=$link->query('Select @curRow := @curRow + 1 AS `No.`, l.* from `list` l JOIN (SELECT @curRow := 0) r WHERE CompanyNo='.$co['CompanyNo']); 
$result=$stmt->fetchAll(); $resultcount=$stmt->rowCount();
$filecontrolnumber=date('ymd').$co['CompanyNo'].$batch;
foreach ($result as $row){
    $pdcexport.='H,'.substr(str_replace(array_keys($findAndReplace), array_values($findAndReplace), $row['SubscriberName']),0,50).','.$row['SubscriberNumber'].','.$row['ReferenceNumber'].','.$row['CheckAmount'].','
            .$row['CheckDate'].','.$row['CheckPostingDate'].','.$row['CheckNumber'].','
            .str_replace(array_keys($findAndReplace), array_values($findAndReplace), $row['DraweeBank']).','
            .$row['BRSTN'].',,,,,,,,,,,,'.$filecontrolnumber.PHP_EOL; 
    $pdcexportpdf.='<tr><td>'.$row['No.'].'</td><td>'.str_replace(array_keys($findAndReplace), array_values($findAndReplace), $row['SubscriberName']).'</td><td>'.number_format($row['CheckAmount'],2).'</td><td>'.$row['CheckNumber']
            .'</td><td>'.str_replace(array_keys($findAndReplace), array_values($findAndReplace), $row['DraweeBank']).'</td><td><div  style="font-size: x-small; width:100;">___ Received<br>___ Not Received<br>___ Reject</div></td><td></td></tr>';
    $totalcheckamt=$totalcheckamt+$row['CheckAmount'];
}
$pdcexport.='S,'.substr($co['CompanyName'],0,20).','.$resultcount.','.$totalcheckamt.','. $filecontrolnumber;
$filename=$filecontrolnumber;

$pdcexportpdf='<h3><center>CHECK WAREHOUSING REQUEST FORM</center></h3>PROCESSING CENTER:  ________________<BR>TRANSACTION REF. NO. : <b>'.$filecontrolnumber
        .'</b><BR>ADDRESS:  ________________<BR>CUSTOMER NAME:  <b>'.$co['CompanyName'].'</b><BR>DATE CREATED:  '.$co['DateCreated'].'<BR>CREATED BY:  '.$createdby
        .'<BR>DATE AUTHORIZED:  ________________<BR>ACCOUNT TO BE CREDITED:  '.$co['AcctNo'].'<BR><BR>CHECK DETAILS<BR><BR>'
        . '<table><tr><td>Item</td><td>Subscriber Name</td><td>Check Amount</td><td>Check Number</td><td>Drawee Bank</td>'
        . '<td>Check Receipt</td><td>Reason for Reject</td></tr>'.$pdcexportpdf
        .'</table>TOTAL CHECK ITEM(S): '.$resultcount.'<BR>TOTAL CHECK AMOUNT(S): '.number_format($totalcheckamt,2).'<BR><BR>'
        .'BANK ACKNOWLEDGEMENT RECEIPT:<BR><BR>Total Items/Received:<BR><BR>Total Check Amount:<BR><BR>Received By:<BR><BR>Date Received:<BR><BR><BR><HR>';

?>
<form style="display: inline;" action='../backendphp/functions/downloadascsv.php' method='post'>
   <input type='submit' name='download' value='Download as CSV'>
   <input type='hidden' name='data' value='<?php echo $pdcexport; ?>'>
   <input type='hidden' name='filename' value='<?php echo $filename.'.csv'; ?>'>
</form> &nbsp; &nbsp;
<form style="display: inline;" action='exportpdc.php?print=1' method='post' target='_blank'>
   <input type='submit' name='submit' value='Print'>
   <input type='hidden' name='datapdf' value='<?php echo $pdcexportpdf; ?>'>
   <input type='hidden' name='filenamepdf' value='<?php echo $filename; ?>'>
</form>
<br><br>
<?php

echo '<font color="red">'.$co['CompanyName'].' PDC list has been exported for download.</font><br><br>';
echo '<br><br>'.$pdcexport.'<br><br><hr><br>'.$pdcexportpdf.'<br><br>';
}
goto noform;} else { 
    ?><title><?php echo $_POST['filenamepdf']; ?></title>
<style>
    body { font-family: Arial; font-size: small; line-height: 150%; margin: 15mm;}
     table,tr,td {
        border:1px solid black; border-collapse: collapse;
        padding: 1px;
        font-family: Arial; font-size: small;
    }

a:link {    text-decoration: none;}
</style>
<?php
    echo $_POST['datapdf'];
    
}
*/

noform:
     $link=null; $stmt=null;  
?>
</body>
</html>