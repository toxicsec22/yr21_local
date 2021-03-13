<?php
$file=$_REQUEST['filename']; $filetype=$_REQUEST['type'];
header("Content-disposition: attachment; filename=".$file);
header("Content-type: application/".$filetype);
echo $_REQUEST['acctgdata'];
exit;//stop writing
//header("Location:".$_SERVER['HTTP_REFERER']);
?> 