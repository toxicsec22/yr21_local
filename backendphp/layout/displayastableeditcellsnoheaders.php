<html>
<head>
<title><?php echo $title; ?></title>
<?php
if (isset($outside) AND $outside){ $diraddress='../../../'.$url_folder.'/';}// for zzjye and aquasys use
else { $diraddress='../';}

if (isset($hidecontents) AND $hidecontents==1){ goto skipcontents;} else {include_once('../switchboard/contents.php');}
skipcontents:

include_once('regulartablestyle.php');
?>
<br><h3><?php echo $title; ?></h3>
	<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br>
	<?php if (isset($addlmenu)){ 	echo $addlmenu.'<br>';} ?>
</head>
<body>
    <?php
IF (isset($sortfield)){include($diraddress.'backendphp/layout/sortbyform.php');echo '<br><br>';} 
$numcols = 0;
$num=0; $runsum=0;
$fields=array();
$fieldlist="<table style=\"display: inline-block; border: 1px solid\"><thead><tr>";
$textfordisplay="<tbody style=\"overflow:auto;\">";
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
if (count($datatoshow)>0) { $keyoflast=key($lastrecord);}
//to make alternating rows have different colors
        $colorcount=0; 
        $rcolor[0]=isset($color1)?$color1:"CCFFFF";
        $rcolor[1]=isset($color2)?$color2:"FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$total=0; $grandtotal=0;
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2]."><form method='post' action=".$editprocess.$rows[$txnid].">";
        $colorcount++;
        //$textfordisplay=$textfordisplay."<tr>";
        foreach($fields as $col){
	    if (in_array($col,$columnstoedit)){
          $textfordisplay=$textfordisplay."<td ".($rows[$col]<0?"bgcolor=lightcoral ":"")."><input type='text' size=10 name='".$col."' value='". addslashes($rows[$col]) . "'><input type='hidden' name='fieldname' value='". $col . "'></td>"; //fieldname variable is still unsuccessfully used
	    } else{
	$textfordisplay=$textfordisplay."<td ".($rows[$col]<0?"bgcolor=lightcoral ":"").">". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";	
	    }
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);  
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":'<td><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input type="submit" value="'.$editprocesslabel.'"></td>'.(isset($runtotal)?"<td>".number_format($total,2)."</td></form>":"</form>").(isset($addlprocess)?"<td><a href='".$addlprocess.addslashes($rows[$txnid])."'>".$addlprocesslabel."</a></td>":"")."</tr>");
	//$grandtotal=$grandtotal+$total;
} //end foreach

$textfordisplay=$textfordisplay.(isset($totaltable)?'<tr>'.$totaltable.'</tr>':'')."</tbody></table><br>";
echo $textfordisplay;
echo (isset($hidecount)?'':count($datatoshow).((count($datatoshow)>1)?" records":" record")).(isset($showgrandtotal)?str_repeat('&nbsp',10).'Grand Total: '. number_format($total,2):'');
?>
</body>
</html>