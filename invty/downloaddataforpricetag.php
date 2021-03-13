<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(7551,'1rtc')) {   echo 'No permission'; exit;}
include_once('../switchboard/contents.php');
 

?>
<html>
<head>
<title>Download Data for Price Tag</title>
</head>
<body><br><br>
<?php
if (!isset($_POST['TransferFrom']) or !isset($_POST['TransferTo'])){
?>
<form action="downloaddataforpricetag.php?done=1" method='POST' enctype="multipart/form-data">
From Transfer Number:  <input type=text size=20 name='TransferFrom' autocomplete='off'>&nbsp &nbsp &nbsp
To Transfer Number:  <input type=text size=20 name='TransferTo' autocomplete='off'>&nbsp &nbsp &nbsp
<input type=submit name=submit value='Prepare'>
</form>
<?php
} else {
if (isset($_GET['done'])){
	echo '<BR><BR><font color="red">Data prepared for '.$_POST['TransferFrom'].' to '.$_POST['TransferTo'] .'.</font><BR><BR>';
}

$filename='ForPriceTag_'.$_POST['TransferFrom'].'_'.$_POST['TransferTo'].'.txt';
$sql='select 
		concat(ConvertDigit(LPAD(t.ToBranchNo, 2, "0"),2),ConvertDigit(date_format(t.DateOUT,"%m%y"),2)) AS BranchDateOUT,
		ts.ItemCode,
		i.Unit,
        concat(`c`.`Category`, \', \', `i`.`ItemDesc`) AS `ItemDesc`, 
		concat("P ",FORMAT(UnitPrice,2)) AS Price,
		ts.QtySent AS QTY,i.CatNo
		
    from
       `invty_2transfer` t
        join `invty_2transfersub` ts ON t.TxnID = ts.TxnID
		join `1branches` b ON t.ToBranchNo = b.BranchNo
        join `invty_1items` i ON ts.ItemCode = i.ItemCode
        join `invty_1category` c ON c.CatNo = i.CatNo
	where t.TransferNo>=\''.$_POST['TransferFrom'].'\' and t.TransferNo<=\''.$_POST['TransferTo'].'\' and i.WithBarcode<>0 and t.BranchNo='.$_SESSION['bnum'];
// echo $sql; exit;
$stmt=$link->query($sql);
$result=$stmt->fetchAll();
// echo $sql; exit();
$pricetaghead='"ItemCode","ItemDesc","Unit","Price","BranchDateOUT","QTY"'. PHP_EOL; //remove <br> when downloading
$pricetag='';
foreach($result as $row){
	if (in_array($row['CatNo'], array(90,158)) and $row['ItemCode']!=1657){
		$price=',""';
	}else{
		$price=',"'.$row['Price'].'"';
	}
   $pricetag=$pricetag.'"'.$row['ItemCode'].'","'.$row['ItemDesc'].'","'.$row['Unit'].'"'.$price.',"'.$row['BranchDateOUT'].'","'.$row['QTY'].'"'. PHP_EOL;
}
$pricetag=$pricetaghead.$pricetag;
?>
<form style="display: inline" action='downloadinvfile.php' method='post'>
   <input type='submit' name='download' value='Download'>
   <input type='hidden' name='barcodedata' value='<?php echo $pricetag; ?>'>
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
     $stmt=null;
?>
</body>
</html>