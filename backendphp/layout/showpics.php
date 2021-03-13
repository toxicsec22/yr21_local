<?php
//$picarray, $picsperrow,$picwidth must be set

$text='<table><tr>'; $countofpics=count($picarray); $countofpic=0;
foreach ($picarray as $pic) {
         $countofpic=$countofpic+1;
	 $text=$text.($countofpic%$picsperrow<>0?'':'</tr>').'<td><img src="'.$pic['file'].'" width="'.$picwidth.'"><br>'.$pic['name'].'</td>';
}
echo $text.'</table>';
?>