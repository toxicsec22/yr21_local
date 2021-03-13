<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(758,'1rtc')) {   echo 'No permission'; exit;}
include_once('../switchboard/contents.php');
 

?>
<html>
<head>
<title>Download Data for Barcode</title>
</head>
<body><br><br>
<?php
if (!isset($_POST['MRRFrom']) or !isset($_POST['MRRTo'])){
?>
<form action="sendforbarcode.php?done=1" method='POST' enctype="multipart/form-data">
From MRR Number:  <input type=text size=20 name='MRRFrom' autocomplete='off'>&nbsp &nbsp &nbsp
To MRR Number:  <input type=text size=20 name='MRRTo' autocomplete='off'>&nbsp &nbsp &nbsp
<input type=submit name=submit value='Prepare'>
</form>
<?php
} else {
if (isset($_GET['done'])){
	echo '<BR><BR><font color="red">Data prepared for '.$_POST['MRRFrom'].' to '.$_POST['MRRTo'] .'.</font><BR><BR>';
}

$sql0='Create Temporary Table sendforbarcode(
Code CHAR(4) NOT NULL,
`Desc` VARCHAR(120) NOT NULL,
Info CHAR(10) NOT NULL,
Price VARCHAR(10) NOT NULL,
ProvMinPrice VARCHAR(10) NOT NULL
)

 select 
        lpad(`s`.`ItemCode`, 4, \'0\') AS `Code`,
        concat(`i`.`ItemDesc`, \', \', `c`.`Category`) AS `Desc`,
        concat(date_format(`m`.`Date`, \'%m%d%y\'),`m`.`SupplierNo`) AS `Info`,
        ConvertDigit(round(`os`.`PriceLevel3`, 0), 0) AS `Price`,
        ConvertDigit(round(`os`.`PriceLevel4`, 0), 1) AS `ProvMinPrice`,
        `s`.`Qty` AS `Qty`
    from
        ((((`invty_2mrr` `m`
        join `invty_2mrrsub` `s` ON ((`m`.`TxnID` = `s`.`TxnID`)))
        join `invty_1items` `i` ON ((`s`.`ItemCode` = `i`.`ItemCode`)))
        join `invty_1category` `c` ON ((`c`.`CatNo` = `i`.`CatNo`)))
        join `invty_3ordersub` `os` ON ((`i`.`ItemCode` = `os`.`ItemCode`)))
      join `invty_3order` o on o.TxnID=os.TxnID and m.ForPONo=o.PONo 
	where m.MRRNo>=\''.$_POST['MRRFrom'].'\' and m.MRRNo<=\''.$_POST['MRRTo'].'\' and m.BranchNo='.$_SESSION['bnum'];
$stmt=$link->prepare($sql0);
$stmt->execute();
$filename='ForBarcode_'.$_POST['MRRFrom'].'_'.$_POST['MRRTo'].'.txt';
//NO DOWNLOADING TIL AT CLOUD
//$file = fopen($filename, "w+b");
$sql='Select * from `sendforbarcode`';
$stmt=$link->query($sql);
$result=$stmt->fetchAll();

$barcodehead='"Code","Desc","Info","Qty"'. PHP_EOL; //remove <br> when downloading
$barcode='';
foreach($result as $row){
   $barcode=$barcode.'"'.$row['Code'].'","'.$row['Desc'].'","'.$row['Info'].'","'.$row['Qty'].'"'. PHP_EOL;
}
$barcode=$barcodehead.$barcode;
//NO DOWNLOADING TIL AT CLOUD
//fwrite($file, $barcode);
//fclose($file);
//$texttodownload=$barcode;

//echo '<a href="downloadinvfile.php?filename='.$filename.'">Download file</a> &nbsp ';
?>
<form style="display: inline" action='downloadinvfile.php' method='post'>
   <input type='submit' name='download' value='Download'>
   <input type='hidden' name='barcodedata' value='<?php echo $barcode; ?>'>
   <input type='hidden' name='filename' value='<?php echo $filename; ?>'>
</form>
<!--&nbsp &nbsp OR &nbsp &nbsp 
<form style="display: inline" action='barcodedata.php' method='post'>
   <input type='submit' name='open' value='Open'> to copy the text on the screen into a text file (notepad).<br><br><br>
   <input type='hidden' name='barcodedata' value='<?php //echo $barcode; ?>'>
</form>-->
<?php
}
noform:
     $link=null; $stmt=null;
?>
</body>
</html>