<?php
echo (isset($subtitle) and !is_null($subtitle))?'<h4>'.$subtitle.'</h4><br>':'';
    $stmt=$link->prepare($sql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);
if ($stmt->rowCount()==0){goto nodata;} 
$lastrecord=end($datatoshow);
if (count($datatoshow)>0) { $keyoflast=key($lastrecord);}
//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]=isset($alternatecolor)?$alternatecolor:"FFFFFF";
        $rcolor[1]="FFFFFF";
$textfordisplay=''; $textfordownload='';
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2].">";
        $colorcount++;
        foreach($fields as $col){
          $textfordisplay=$textfordisplay."<td>". ($rows[$col]=="0"?'':nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col]))))) . "</td>";
          $textfordownload=$textfordownload."'".($rows[$col]=="0"?'':nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col]))))) . "';";
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);  
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"</td>":"</tr>");
        $textfordownload=$textfordownload.((key($rows)!=$keyoflast)?"":PHP_EOL);
} //end foreach
echo $textfordisplay;
nodata:
?>