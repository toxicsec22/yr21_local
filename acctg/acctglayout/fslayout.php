<html>
<head>
<title><?php echo $title; ?></title>
<style type="text/css">
#wrap {
   width:<?php echo !isset($width)?'120%':$width; ?>;
   margin:0 auto;
}
#left {
   float:left;
   /*width:<?php echo !isset($left)?'50%':$left; ?>;*/
   overflow: auto;
}
#right {
   /*margin-left: <?php echo !isset($leftmargin)?'51%':$leftmargin; ?>;*/
   overflow: auto;
   /*width:<?php echo !isset($right)?'50%':$right; ?>;*/
}

</style>
<?php

if(!$outside) { include_once('../switchboard/contents.php');}
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/'.$url_folder.'/backendphp/layout/regulartablestyle.php';

echo '<h3>'.$title.'</h3><br><i>'.(isset($formdesc)?$formdesc:'').'</i>';
?>
</head>
<body>
<?php
// to put top row
$numcols = 0;
$num=0; $runsum=0;
$fields=array();
$fieldlist="<table><thead><tr>";
$textfordisplay="<tbody>"; $downloadsubcol='';
foreach($columnnames as $field){
    $fieldlist=$fieldlist . "<td>".$field."</td>";
    $downloadsubcol.="'".$field."';";
    $numcols=$numcols+1;
    $fields[$numcols]=$field;
}
$fieldlist=$fieldlist . (isset($runtotal)?'<td>Running Sum</td>':'')."<tr></thead>";
$downloadsubcol.= (isset($runtotal)?'Running Sum':'').PHP_EOL;
//echo $fieldlist ;
// end of top row