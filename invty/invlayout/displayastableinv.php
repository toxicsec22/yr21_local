<html>
<head>
<title><?php echo $title; ?></title>
<?php
include_once('../switchboard/contents.php');

include_once('../backendphp/layout/regulartablestyle.php');
?>
<br><h3><?php echo $title.(isset($titleadd)?$titleadd:''); ?></h3>
<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br><br>
</head>
<body>
<?php
//variables to define:
//$columnnames=array(...);
//$link=;
//$showbranches=boolean;
if (isset($addentry) and $addentry){ include '../invty/invlayout/inputboxesinvty.php';}
$numcols = 0;
$num=0;
$fields=array();
$fieldlist="<table style=\"display: inline-block; border: 1px solid\"><thead><tr>";
$textfordisplay="<tbody>";
foreach($columnnames as $field){
    $fieldlist=$fieldlist . "<td>".$field."</td>";
    $numcols=$numcols+1;
    $fields[$numcols]=$field;
}
$fieldlist=$fieldlist . (isset($runtotal)?'<td>Running Sum</td>':''). "<tr></thead>";
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
$total=0; $tblcondition=(!isset($tblcondition)?true:($condtype=='date'?(strtotime($tblcondition)):$tblcondition));
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2].">";
        $colorcount++;
        //$textfordisplay=$textfordisplay."<tr>";
        foreach($fields as $col){ 
          if(!isset($colwithcond) or $col<>$colwithcond) {
              $textfordisplay=$textfordisplay."<td ".($rows[$col]<0?"bgcolor=lightcoral ":"").">". addslashes($rows[$col]) . "</td>";
          } else { 
              $tblcond=((($condtype=='date'?(strtotime($rows[$colwithcond])):($rows[$colwithcond]))<$tblcondition)?false:true); 
              $textfordisplay=$textfordisplay."<td ".(($tblcond==true)?"":"bgcolor=".$colorneg).">". addslashes($rows[$col]) . "</td>";
          }
        }
       $total=$total+(isset($coltototal)?$rows[$coltototal]:0);
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($runtotal)?"<td>".number_format($total,2)."</td></tr>":"</tr>"));
       
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table><br>";
echo $textfordisplay;
echo ((isset($coltototal) AND !isset($totalstext)) ?"<br>Balance of ".$coltototal. ": " . $total . "<br>":'');
echo count($datatoshow).((count($datatoshow)>1)?" records":" record");
echo (isset($totalstext)?'<br>'.$totalstext:'');
?>
</body>
</html>