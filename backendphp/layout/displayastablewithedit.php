<html>
<head>
<title><?php echo $title; ?></title>
<?php
if (isset($outside) AND $outside){ $diraddress='../../../'.$url_folder.'/'; $hidecontents=true;}// for zzjye and aquasys use
else { $diraddress='../';}

if (isset($hidecontents) AND $hidecontents==1){ goto skipcontents;} else {include_once('../switchboard/contents.php');}
skipcontents:
include_once('regulartablestyle.php');
?>
<br><h3><?php echo $title; ?></h3>
	<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br>
</head>
<body>
<?php
IF (isset($sortfield)){include($diraddress.'backendphp/layout/sortbyform.php');echo '<br><br>';} 
if (isset($addlmenu)){
	echo $addlmenu.'<br>';
}
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
$fieldlist=$fieldlist . "<tr></thead>";
echo $fieldlist ;

    $stmt=$link->prepare($sql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);
 
$lastrecord=end($datatoshow);
if (count($datatoshow)>0) { $keyoflast=key($lastrecord);}
//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="FFFFCC";
        $rcolor[1]="FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$total=0;
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2].">";
        $colorcount++;
        //$textfordisplay=$textfordisplay."<tr>";
        foreach($fields as $col){
            
          $textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($formprocess)?'<td><form action='.$formprocess.'>'.$formprocessfields.'<input type=hidden name='.$txnidname.' value='.$rows[$txnidname].'><input type=submit name=submit value="'.$submitlabel.'"></form></td>':'').(isset($delprocess)?"<td><a href='".$delprocess.addslashes($rows[$txnidname])."&action_token=".$_SESSION['action_token']."' OnClick=\" return confirm('Really delete this?');\"'>Del</a></td>":"").(isset($editprocess)?"<td><a href='".$editprocess.addslashes($rows[$txnidname])."'>".$editprocesslabel."</a></td>":"").(isset($autoprocess)?"<td><a href='".$autoprocess.addslashes($rows[$txnidname])."&action_token=".$_SESSION['action_token']."'>".$autoprocesslabel."</a></td>":"").(isset($autoprocess2)?"<td><a href='".$autoprocess2.addslashes($rows[$txnidname])."&action_token=".$_SESSION['action_token']."'>".$autoprocesslabel2."</a></td>":"")."</tr>");
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table><br>";
echo $textfordisplay;
echo count($datatoshow).((count($datatoshow)>1)?" records":" record").(isset($showgrandtotal)?str_repeat('&nbsp',10).'Grand Total: '. number_format($total,2):'');
echo (isset($totalstext)?'<br>'.$totalstext:'');
?>

</body>
</html>