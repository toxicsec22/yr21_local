<?php 
include_once('../backendphp/functions/editok.php');
$editok=editOk($main,$txnid,$link,$title);
$stmt=$link->query($sql); $result=$stmt->fetch();

echo '<title>'.$title.'</title>';
$toshow='';$coltitles='';
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
foreach($columnnames as $col){
    $coltitles=$coltitles.'<th>'.$col.'</th>';
    $toshow=$toshow.'<td>'.nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",$result[$col]))).'</td>';
}
echo '<br><br><h4>'.$title.'</h4><i>'.(!isset($formdesc)?'':$formdesc).'</i><br><br><table><tr>'.$coltitles.'</tr><tr>'.$toshow.'<tr></table><br><br>';

if ($editok){ $columnstoedit=$columnstoedit; } else { $columnstoedit=array();}

$toshow=''; $coltitles='';

foreach($columnstoedit as $col){
    $coltitles=$coltitles.'<th>'.$col.'</th>';
    if (isset($columnswithlists) and in_array($col,$columnswithlists)){
        $list=' list="'.(!isset($listsname)?$col:(in_array($col,array_keys($listsname))?($listsname[$col]):$col)).'"';} else {$list='';}
    $toshow=$toshow.'<td><input type="text" name="'.$col.'" value="'.$result[$col].'" '.$list.'></td>';
}





echo '<form method=post action='.$editprocess.'><table><tr>'.$coltitles.'</tr><tr>'.$toshow.'<td><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input type="submit" value="Submit"></td><tr></table></form>';
?>