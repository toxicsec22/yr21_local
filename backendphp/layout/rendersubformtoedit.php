<?php
//session_start();
$numcols = 0;
$num=0;
$fields=array();
$fieldlist="<table style=\"display: inline-block; border: 1px solid\"><thead><tr>";
$textfordisplay="";
foreach($columnstoedit as $field){
    $fieldlist=$fieldlist . "<td>".$field."</td>";
    $numcols=$numcols+1;
    $fields[$numcols]=$field;
    
}

$fieldlist=$fieldlist . "<tr></thead><tbody>";
echo $fieldlist ;

    $stmt=$link->prepare($sql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);

//echo "<br>".$sql."<br>" ;

$lastrecord=end($datatoshow);
$keyoflast=key($lastrecord);
//echo "<br>key:  ".$keyoflast ."last record: ".$lastrecord ."<br>";
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr>";
        
        foreach($fields as $col){
            $type='text';
           
        if (in_array($col,$columnslist)){
            $list=' list='.$listsname[$col];
            } else {
                $list='';
            }
          // $textfordisplay=$textfordisplay."<td><input type='$type' name='$col' value='". addslashes($rows[$col]) . "' " . $list  . " autocomplete=false></td>";
          $textfordisplay=$textfordisplay."<td><input type='$type' name='$col' value=\"". $rows[$col] . "\" " . $list  . " autocomplete=false></td>";
        } //end if
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":"</tr>");
        if (isset($hiddencolumns)){
        foreach($hiddencolumns as $hiddencol){
          $textfordisplay=$textfordisplay."<td><input type='hidden' name='$hiddencol' value='". addslashes($rows[$hiddencol]) . "'>";  
        }// end for each
        } // end if
} //end foreach

include_once "../generalinfo/lists.inc";
if (isset($liststoshow)){
foreach ($liststoshow as $list){
renderlist($list);    
}//end foreach
}
include_once('renderotherlists.php');
$textfordisplay=$textfordisplay."</tbody></table><br>";

echo $textfordisplay;
?>
<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" /> 