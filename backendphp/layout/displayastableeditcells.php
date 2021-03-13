<html>
<head>
<title><?php echo $title; ?></title>
<?php
// 
if (isset($outside) AND $outside){ $diraddress='../../../'.$url_folder.'/';}// for zzjye and aquasys use
else { 
$diraddress='../';
include_once('../switchboard/contents.php');
include_once('regulartablestyle.php');
}
?>
<br><h3><?php echo $title; ?></h3>
	<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br>
</head>
<body>
    <?php
IF (isset($sortfield)){include($diraddress.'backendphp/layout/sortbyform.php');echo '<br><br>';} 
if (isset($liststoshow)){
 include_once "../generalinfo/lists.inc";
foreach ($liststoshow as $list){
renderlist($list);    
}//end foreach   
}//end if
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
        $rcolor[0]="FFFFCC";
        $rcolor[1]="FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$total=0; $grandtotal=0;
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
$columnstoeditselect=!isset($columnstoeditselect)?array():$columnstoeditselect;
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2]."><form method='post' action='".$editprocess.$rows[$txnid]."'>";
        $colorcount++;
        //$textfordisplay=$textfordisplay."<tr>";
        foreach($fields as $col){
	    if (in_array($col,$columnstoedit)){
			if (isset($columnswithlists) and in_array($col,$columnswithlists)){
        $list=' list="'.(!isset($listsname)?$col:(in_array($col,array_keys($listsname))?($listsname[$col]):$col)).'"';} else {$list='';}
			
          $textfordisplay=$textfordisplay."<td><input type='".(isset($type)?$type:"text")."' size=10 name='".$col."' value='". addslashes($rows[$col]) . "' ".(isset($disablefield)?(($rows[$triggercolumn]==$txtshouldbe)?'disabled':''):'')." ".$list."></td>";

	    } else if (in_array($col,$columnstoeditselect)){
			$textfordisplay=$textfordisplay."<td><select name='".$col."'><option value='". addslashes($rows[$col]) . "'>".$rows[$col]."</option>".$options."</select></td>";
		}
		else{	
	$textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";	
	    }
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);  
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($runtotal)?"<td>".number_format($total,2)."</td>":'').'<td><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input type="submit" value="'.$editprocesslabel.'"></td></form>'
	 .(isset($addlprocess)?'<td><a href='.$addlprocess.$rows[$txnid].'&action_token='.$_SESSION['action_token'].'>'.$addlprocesslabel.'</a></td>':'') 
         .(isset($addlprocess2)?'<td><a href='.$addlprocess2.$rows[$txnid].'&action_token='.$_SESSION['action_token'].'>'.$addlprocess2label.'</a></td>':'')
	 .(isset($delprocess)?'<td><form method="post" action='.$delprocess.$rows[$txnid].' style="display:inline"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input type="submit" value="'.(!isset($delprocesslabel)?'Delete':$delprocesslabel).'"  OnClick="return confirm(\'Really delete this?\');"></form></td>':'')."</tr>");
	//$grandtotal=$grandtotal+$total;
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table><br>";
echo $textfordisplay;
echo count($datatoshow).((count($datatoshow)>1)?" records":" record").(isset($showgrandtotal)?str_repeat('&nbsp',10).'Grand Total: '. number_format($total,2):'');
echo (isset($totalstext)?$totalstext:'');
?>
</body>
</html>