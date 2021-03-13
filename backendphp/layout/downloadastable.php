<?php
$numcols = 0;
$num=0; $runsum=0;
$fields=array();
$fieldlist="";

foreach($columnnames as $field){
    $fieldlist=$fieldlist . $field.";";
    $numcols=$numcols+1;
    $fields[$numcols]=$field;
}
$textfordisplay=$fieldlist.PHP_EOL;

    $stmt=$link->prepare($sql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);
 
$lastrecord=end($datatoshow);
$keyoflast=key($lastrecord);

foreach($datatoshow as $rows){

        foreach($fields as $col){
          $textfordisplay=$textfordisplay.addslashes($rows[$col]) . ";";
        }
	$textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":PHP_EOL);
} //end foreach
$textfordisplay=$textfordisplay.PHP_EOL;
?>