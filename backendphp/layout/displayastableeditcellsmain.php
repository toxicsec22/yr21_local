<?php
$numcols = 0;
$num=0; 
$fields=array();
$fieldlist="<table style=\"display: inline-block; border: 1px solid\"><thead style=\"font-weight: normal; font-size: small;\"><tr>";
$textfordisplay="<tbody style=\"overflow:auto; background-color: white;\">";
foreach($columnnamesmain as $field){
    $fieldlist=$fieldlist . "<td>".$field."</td>";
    $numcols=$numcols+1;
    $fields[$numcols]=$field;
}
$fieldlist=$fieldlist ."<tr></thead>";
echo $fieldlist ;

    $stmt=$link->prepare($sqlmain);
    $stmt->execute();
    $datatoshow=$stmt->fetch(PDO::FETCH_ASSOC);

//to make alternating rows have different colors
        //$colorcount=0; 
        //$rcolor[0]=isset($color1)?$color1:"CCFFFF";
        //$rcolor[1]=isset($color2)?$color2:"FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
        $textfordisplay=$textfordisplay."<tr><form method='post' action=".$editprocessmain.$datatoshow[$txnidcol].">";
      //  $colorcount++;
        //$textfordisplay=$textfordisplay."<tr>";
        foreach($fields as $col){
	    if (in_array($col,$columnstoeditmain)){
          $textfordisplay=$textfordisplay."<td><input type='".((in_array($col,$colwithmonthmain))?'month':'text')."' size=10 name='".$col."' value='". addslashes($datatoshow[$col]) . "' ";
		if (in_array($col,$colwithlistmain)){
		    $textfordisplay=$textfordisplay." list='".$listsmain[$col]."'></td>"; 
		    }
			else { $textfordisplay=$textfordisplay."></td>"; }
	    } else{
	$textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($datatoshow[$col])))) . "</td>";	
	    }
        }
	
        $textfordisplay=$textfordisplay.'<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">'
					   .((isset($editprocessmain) AND $editok)?'<td><input type="submit" value="'.$editprocesslabel.'"></td>':'')
					   .((isset($delprocessmain) AND $editok)?'<td><a href='.$delprocessmain.$datatoshow[$txnidcol].'&action_token='.$_SESSION['action_token'].' OnClick="return confirm(\'Really delete this? All entries in subform will be deleted.\');">Delete</a></td>':'')."</form></tr>";

$textfordisplay=$textfordisplay."</tbody></table><br>";
echo $textfordisplay;
echo (isset($addlinfomain) and !is_null($addlinfomain))?$addlinfomain:'';
?>