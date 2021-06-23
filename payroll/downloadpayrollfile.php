<?php
$file=$_REQUEST['filename'];
header("Content-disposition: attachment; filename=".$file);
if(isset($_POST['fileext'])){
    $fileextension=$_POST['fileext'];
} else {
    $fileextension='txt';
}
header("Content-type: application/'.$fileextension.'");

if(isset($_POST['fileext'])){
    echo iconv("UTF-8", "WINDOWS-1252",$_REQUEST['payrolldata']);
} else {
    echo $_REQUEST['payrolldata'];
}
exit;//stop writing
?> 