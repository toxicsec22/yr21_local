<html>
<head>
<title><?php echo $title; ?></title>
<?php
//
if (isset($hidecontents) AND $hidecontents==1){ goto skipcontents;} else {include_once('../switchboard/contents.php');}
skipcontents:
include_once('regulartablestyle.php');
?>
<br><h3><?php echo $title; ?></h3>
	<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br>
</head>
<body>
    <?php
$numcols = 0;
$num=0; $runsum=0;
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
$fields=array();
$fieldlist="<table style=\"display: inline-block; border: 1px solid\"><thead><tr>".(isset($rowheading)?"<td>".nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",$rowheading)))."</td>":"").(isset($rowtotal)?"<td>Subtotal</td>":"");
$textfordisplay="<tbody>";
//$firstcol=reset($columnnames);
foreach($columnnames as $field){
    $fieldlist=$fieldlist . "<td>".$field."</td>";
    $numcols=$numcols+1;
    $fields[$numcols]=$field;
}
$fieldlist=$fieldlist . (isset($runtotal)?'<td>Running Sum</td>':'')."<tr></thead>";
echo $fieldlist ;

    $stmt=$link->prepare($sql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);
 
$lastrecord=end($datatoshow);
$keyoflast=key($lastrecord);


//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="FFFFCC";
        $rcolor[1]="FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$total=0; $grandtotal=0;
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
foreach($datatoshow as $rows){
    if (isset($rowtotal)){
	$stmt=$link->prepare('Select RowTotalValue from rowtotal where RowTotal like \''.$rows[$rowheading].'\'');
	$stmt->execute();
	$result=$stmt->fetch(PDO::FETCH_ASSOC);
        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2].">".(isset($rowheading)?"<td>".nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",$rows[$rowheading])))."</td>":"").(isset($rowtotal)?"<td>".number_format($result['RowTotalValue'],0)."</td>":"");
    }
        $colorcount++;
        
        foreach($fields as $col){
          $textfordisplay=$textfordisplay."<td>". number_format($rows[$col],0) . "</td>";
	  
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);  
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($runtotal)?"<td>".number_format($total,2)."</td></tr>":"</tr>"));
	//$grandtotal=$grandtotal+$total;
} //end foreach
//if (isset($sqlcoltotal)){
//$stmt=$link->prepare($sqlcoltotal);
//	$stmt->execute();
//	$resultcol=$stmt->fetch(PDO::FETCH_ASSOC);
//	$textfordisplay=$textfordisplay.(isset($rowheading)?"<td></td>":"").(isset($rowtotal)?"<td></td>":"");
//foreach($fields as $col){
//          $textfordisplay=$textfordisplay."<td>". number_format($resultcol[$col],0) . "</td>";
//	  
//        }
//}
$textfordisplay=$textfordisplay."</tbody></table><br>";
echo $textfordisplay;
echo (isset($hidecount)?'':(count($datatoshow).((count($datatoshow)>1)?" records":" record"))).((count($datatoshow)>1)?" records":" record").(isset($showgrandtotal)?str_repeat('&nbsp',10).'Grand Total: '. number_format($total,2):'');
echo isset($summary)?'<br>'.$summary:'';
?>
</body>
</html>