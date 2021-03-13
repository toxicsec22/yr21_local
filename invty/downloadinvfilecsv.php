<?php
$file=$_REQUEST['filename'];
header("Content-disposition: attachment; filename=".$file);
header("Content-type: application/csv");
echo $_REQUEST['csvfile'];
exit;//stop writing
?> 