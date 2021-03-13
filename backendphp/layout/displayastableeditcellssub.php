<?php
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

$numcols = 0;
$num=0; $runsum=0;
$fields=array(); if(!isset($colwithmonthsub)){
	$colwithmonthsub=array();
}
$fieldlist="<table style=\"display: inline-block; border: 1px solid\"><thead><tr>";
$textfordisplay="<tbody style=\"overflow:auto;\">";
foreach($columnsub as $field){
    $fieldlist=$fieldlist . "<td>".$field."</td>";
    $numcols=$numcols+1;
    $fields[$numcols]=$field;
}
$fieldlist=$fieldlist . (isset($runtotal)?'<td>Running Sum</td>':'')."<tr></thead>";
echo $fieldlist ;

    $stmt=$link->prepare($sqlsub);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);
 
$lastrecord=end($datatoshow);
if (count($datatoshow)>0) { $keyoflast=key($lastrecord);}
//to make alternating rows have different colors
        $colorcount=0; 
        $rcolor[0]=isset($color1)?$color1:"CCFFFF";
        $rcolor[1]=isset($color2)?$color2:"FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$total=0; $grandtotal=0;
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2]."><form method='post' action=".$editprocess.$rows[$txnsubid].">";
        $colorcount++;
        //$textfordisplay=$textfordisplay."<tr>";
        foreach($fields as $col){
	    if (in_array($col,$columnstoedit)){
          $textfordisplay=$textfordisplay."<td><input type='".((in_array($col,$colwithmonthsub))?'month':'text')."'  size=".($col=='Particulars'?"30":"8")." name='".$col."' value='". addslashes($rows[$col]) . "' ".($col=='Branch'?"list='".(!isset($branchlist)?'branchesper':$branchlist)."'":"").((strpos($col,'Account') !== false)?"list='accounts'":"").($col=='FromBudgetOf'?"list='".(isset($entities)?$entities."'":''):"")."><input type='hidden' name='fieldname' value='". $col . "' ></td>"; //fieldname variable is still unsuccessfully used
	    } else{
	$textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";	
	    }
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);  
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($runtotal)?"<td>".number_format($total,2)."</td>":'').'<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">'.($editok?'<td><input type="submit" value="'.$editprocesslabel.'"></td>':'')
					
					 .((isset($delprocess) and $editok)?'<td><a href='.$delprocess.$rows[$txnsubid].'&action_token='.$_SESSION['action_token'].' OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'') 
					 .(isset($addlprocess)?'<td><a href='.$addlprocess.$rows[$txnsubid].'&action_token='.$_SESSION['action_token'].'>'.$addlprocesslabel.'</a></td>':'')
					 .(isset($addlprocess2)?'<td><a href='.$addlprocess2.$rows[$txnsubid].'&action_token='.$_SESSION['action_token'].'>'.$addlprocesslabel2.'</a></td>':'')
					 ."</form></tr>");
	//$grandtotal=$grandtotal+$total;
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table><br>";
echo (isset($subtitle) and !is_null($subtitle))?'<h4>'.$subtitle.'</h4><br>':'';
echo $textfordisplay;
echo count($datatoshow).((count($datatoshow)>1)?" records":" record").str_repeat('&nbsp',5)/*.(isset($showgrandtotal)?str_repeat('&nbsp',10).' Total: '. number_format($total,2):'')*/;
echo (isset($addlinfo) and !is_null($addlinfo))?$addlinfo:'';
echo (isset($totalprice)?'<div align="left">'.'Total Amount: P '. number_format($total,2):'');
?>