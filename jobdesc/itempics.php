<?php

$sql='Select filename from `gen_info_1itempicturelist`';
$stmt=$link->query($sql);
$result=$stmt->fetchAll();
//$picnames=array('Freon','Accumulator','Thermometer','Bulb','Bracket','Tape','Overload');
$picarray=array();
foreach ($result as $picname){
    $picarray[]=array('name'=>$picname['filename'],'file'=>'../../itempics/samplepercat/'.$picname['filename'].'.jpg');
}
$picsperrow=5; $picwidth=250;
include '../backendphp/layout/showpics.php';
 $link=null; $stmt=null;
?>