<!-- DID NOT PUSH THROUGH IN USING THIS FOR ACCTG ADJUSTMENTS -->
<div id="wrapper"><div style="float:<?php echo $leftorright; ?>; width:50%;overflow: auto;">
<?php

$numcols = 0;
$num=0; $runsum=0;
$fields=array();
$fieldlist="<table style=\"display: inline-block; border: 1px solid\"><thead><tr>";
$textfordisplay="<tbody style=\"overflow:auto;\">";
foreach($columnsub2 as $field){
    $fieldlist=$fieldlist . "<td>".$field."</td>";
    $numcols=$numcols+1;
    $fields[$numcols]=$field;
}
$fieldlist=$fieldlist . (isset($runtotal)?'<td>Running Sum</td>':'')."<tr></thead>";
echo $fieldlist ;

    $stmt=$link->prepare($sqlsub2);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);
 
$lastrecord=end($datatoshow);
$keyoflast=key($lastrecord);
//to make alternating rows have different colors
        $colorcount=0; 
        $rcolor[0]=isset($color1)?$color1:"FFFFCC";
        $rcolor[1]=isset($color2)?$color2:"FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$total=0; $grandtotal=0;
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2]."><form method='post' action=".$editprocess.$rows[$txnsubid].">";
        $colorcount++;
        //$textfordisplay=$textfordisplay."<tr>";
        foreach($fields as $col){
	    if (in_array($col,$columnstoedit)){
          $textfordisplay=$textfordisplay."<td><input type='text'  size=".($col=='Particulars'?"30":"8")." name='".$col."' value='". addslashes($rows[$col]) . "' ".($col=='Branch'?"list='branchper'":"").((strpos($col,'Account') !== false)?"list='accounts'":"")."></td>";
	    } else{
	$textfordisplay=$textfordisplay."<td>". addslashes($rows[$col]) . "</td>";	
	    }
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);  
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":'<td><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">'.($editok?'<input type="submit" value="'.$editprocesslabel.'"></td>':'').(isset($addlprocess)?'<td><a href='.$addlprocess.$rows[$txnsubid].'&action_token='.$_SESSION['action_token'].'>'.$addlprocesslabel.'</a></td>':'').((isset($delprocess) and $editok)?'<td><a href='.$delprocess.$rows[$txnsubid].'&action_token='.$_SESSION['action_token'].' OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').(isset($runtotal)?"<td>".number_format($total,2)."</td></form></tr>":"</form></tr>"));
	//$grandtotal=$grandtotal+$total;
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table><br>";
echo $textfordisplay;

echo count($datatoshow).((count($datatoshow)>1)?" records":" record").str_repeat('&nbsp',5)/*.(isset($showgrandtotal)?str_repeat('&nbsp',10).' Total: '. number_format($total,2):'')*/;
echo (isset($addlinfo) and !is_null($addlinfo))?$addlinfo:'';

?></div></div>