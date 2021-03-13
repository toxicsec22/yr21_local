<html>
<head>
<title>Search</title>
</head>
<body>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once('../switchboard/contents.php');
 
include_once('../backendphp/layout/regulartablestyle.php');
 
?><br>
<h3>Search Items</h3><br><p style="font-size:small"><i> Pls inform me if you want other searches - JYE</i></p><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search in all fields:
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Search'>  </form><br><br>
<?php
if (!isset($_POST['submit'])){    goto noform;}
$sql='SELECT i.*, Category, e.Nickname as EncodedBy, i.ItemCode AS TxnID, MovementType, IF(WithBarcode=1,"Yes","No") AS With_Barcode FROM invty_1items i
        JOIN `invty_1category` t ON t.CatNo=i.CatNo JOIN invty_0movetype m ON m.MoveType=i.MoveType
        JOIN `1employees` e ON e.IDNo=i.EncodedByNo  WHERE Category LIKE  \'%'.$_POST['stringsearch'].'%\' '
        . ' OR ItemCode LIKE  \'%'.$_POST['stringsearch'].'%\'  OR ItemDesc LIKE  \'%'.$_POST['stringsearch'].'%\' '
        . ' OR ItemDesc2 LIKE  \'%'.$_POST['stringsearch'].'%\'  OR Unit LIKE  \'%'.$_POST['stringsearch'].'%\' '
        . ' OR WholesaleUnit LIKE  \'%'.$_POST['stringsearch'].'%\'  OR Remarks LIKE  \'%'.$_POST['stringsearch'].'%\' '
        . ' OR MovementType LIKE  \'%'.$_POST['stringsearch'].'%\' ';
$columnnames=array('ItemCode','Category', 'ItemDesc', 'ItemDesc2', 'Unit', 'WholesaleUnit', 'Remarks', 'MovementType','With_Barcode');
   
   $subtitle='<br><br>Results for: '.$_POST['stringsearch'];
    include('../backendphp/layout/displayastableonlynoheaders.php');
  
noform:
      $link=null; $stmt=null;
?>