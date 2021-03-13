<?php
$filename=$_REQUEST['filename'].'.pdf';
$filepath = $_REQUEST['filepath'];
$filename = 'Custom file name for the.pdf'; /* Note: Always use .pdf at the end. */

header('Content-type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($filepath));
header('Accept-Ranges: bytes');

@readfile($filepath);
?>