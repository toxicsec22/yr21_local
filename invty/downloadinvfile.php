<?php
$file=$_REQUEST['filename'];
header("Content-disposition: attachment; filename=".$file);
header("Content-type: application/txt");
echo $_REQUEST['barcodedata'];
exit;//stop writing
?> 