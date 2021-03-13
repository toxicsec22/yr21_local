<?php
	$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	include_once('../switchboard/contents.php');	
        
        
?>
<html>
<head>
<title><?php echo $title; ?></title>
<style type="text/css">
#wrap {
   width:130%;
   margin:0 auto;
}
#left {
   float:left; display: inline;
   
}
#right {
   margin-left: 50px; display: inline;
   
}
thead {color:darkblue;font-family:sans-serif;; font-size: small;}
tbody {color:black; font-family:sans-serif;; font-size: small;}
tfoot {color:darkblue;}
table,th,td
{
border:1px solid black;
border-collapse:collapse;
padding: 3px;
}
</style>
<?php
include_once('../backendphp/layout/regulartablestyle.php');
?>
<br>
<?php echo $title; ?><br>
</head>