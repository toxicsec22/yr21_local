<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(7552,'1rtc')) {   echo 'No permission'; exit;}
include_once('../switchboard/contents.php');
include_once('../invty/invlayout/pricelevelcase.php'); 
 
?>
<html>
<head>
<title>Pricetag Data from Inventory Count</title>
</head>
<body><br><br>
    <h3>Download Pricetag Data from Inventory Count</h3><br><br>
<?php
if (!isset($_POST['CountFrom'])){
?>
<form action="pricetagdatafromcount.php?done=1" method='POST' enctype="multipart/form-data">
 Count ID:  <input type=text size=20 name='CountFrom' autocomplete='off'>&nbsp &nbsp &nbsp
<input type=submit name=submit value='Prepare'>
</form>
<?php
} else {
if (isset($_GET['done'])){
	echo '<BR><BR><font color="red">Data prepared for '.$_POST['CountFrom'].' .</font><BR><BR>';
}

$filename='ForPriceTag_'.$_POST['CountFrom'].'.txt';
// CONCAT("P ",FORMAT(IF(ProvincialBranch=0,PriceLevel3,PriceLevel4),2))
	 $sql='Select s.ItemCode,concat(`c`.`Category`, \', \', `i`.`ItemDesc`) AS `ItemDesc`, i.Unit,concat(ConvertDigit(LPAD(b.BranchNo,3,0),2),ConvertDigit(date_format(m.Date,"%m%y"),2)) AS BranchDate, 
	 
	 
	CONCAT("P ",FORMAT(
	 
	'.$plcase.'
	
	 ,2))

		 AS Price, s.Count,i.CatNo from audit_2countsub s join `invty_1items` i on i.ItemCode=s.ItemCode join `invty_5latestminprice` lmp ON lmp.itemcode = i.ItemCode join `invty_1category` c on c.CatNo=i.CatNo left join `1employees` as e1 on s.EncodedByNo=e1.IDNo join audit_2countmain m on m.CountID=s.CountID  join `1branches` b ON m.BranchNo = b.BranchNo where m.CountID='.$_POST['CountFrom'].' and i.WithBarcode<>0'; 
$stmt=$link->query($sql);
$result=$stmt->fetchAll();
// echo $sql; exit();
$pricetaghead='"ItemCode","ItemDesc","Unit","Price","BranchDate","Count"'. PHP_EOL; //remove <br> when downloading
$pricetag='';
foreach($result as $row){
	if (in_array($row['CatNo'], array(90,158))){
		$price=',""';
	}else{
		$price=',"'.$row['Price'].'"';
	}
   $pricetag=$pricetag.'"'.$row['ItemCode'].'","'.$row['ItemDesc'].'","'.$row['Unit'].'"'.$price.',"'.$row['BranchDate'].'","'.$row['Count'].'"'. PHP_EOL;
}
$pricetag=$pricetaghead.$pricetag;
?>
<form style="display: inline" action='../invty/downloadinvfile.php' method='post'>
   <input type='submit' name='download' value='Download'>
   <input type='hidden' name='barcodedata' value='<?php echo $pricetag; ?>'>
   <input type='hidden' name='filename' value='<?php echo $filename; ?>'>
</form>

<?php
}
noform:
     $stmt=null;
?>
</body>
</html>