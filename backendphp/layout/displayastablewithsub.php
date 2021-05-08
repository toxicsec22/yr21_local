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
<br>
<?php
if (isset($script)){echo $script;}
?>
<br><h3><?php echo $title; ?></h3>
	<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br>
</head>
<body>
<?php
//variables to define:
//$columnnames=array(...);
//$link=;
//$showbranches=boolean;

echo (isset($subtitle) and !is_null($subtitle))?'<h4>'.$subtitle.'</h4><br>':'';
if (isset($_REQUEST['print'])){ include ($diraddress.'backendphp/layout/standardprintsettings.php');}

(isset($sortfield)?include($diraddress.'backendphp/layout/sortbyform.php'):''); 
$stmt=$link->prepare($sql1);
    $stmt->execute();
    $datatoshow1=$stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($stmt->rowCount()==0){ goto nodata;}
$numcols1 = 0;
$numcols2 = 0;
$num=0;
$fields=array();
$fieldlist2="<thead>";
$fieldlist1="";

foreach($columnnames1 as $field){
    $fieldlist1=$fieldlist1 . "<thead>".$field."</thead>";
    $numcols1=$numcols1+1;
    $fields1[$numcols1]=$field;
}
foreach($columnnames2 as $field){
    $fieldlist2=$fieldlist2 . "<td>".$field."</td>";
    $numcols2=$numcols2+1;
    $fields2[$numcols2]=$field;
}
$fieldlist2=$fieldlist2 . "</tr>";


$textfordisplay="";    

//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
        $rcolor[1]="FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$total=0;$grandtotal=0;
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
foreach($datatoshow1 as $rows){
    $total=0;
    $textfordisplay=$textfordisplay."<div class='keeptog'><table size=\"100%\" style=\"display: inline-block; border: 0px; background-color:#FFF;;\"><tr>";
    foreach($fields1 as $col1){
       // $textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col1])))) . "</td>";
       $textfordisplay=$textfordisplay."<td>". nl2br($rows[$col1]) . "</td>";
       } //end foreach 
	$textfordisplay=$textfordisplay."</tr></table><br>";
	$sql3="";
	$datatoshow2=array();
       $sql3= $sql2 . " WHERE " .$groupby ." Like '" . addslashes($rows[$groupby]) . "' ".(!isset($secondcondition)?'':$secondcondition).$orderby;
       // echo $sql3; break;
       $stmt=$link->prepare($sql3);
	$stmt->execute();
	$datatoshow2=$stmt->fetchAll(PDO::FETCH_ASSOC);
	if ($stmt->rowCount()>0){
	$lastrecord=end($datatoshow2);
	if (count($datatoshow2)>0) { $keyoflast=key($lastrecord);}
	  
	$textfordisplay=$textfordisplay."<table style=\"display: inline-block; border: 1px solid\">". $fieldlist2 ."</thead><tbody>";
foreach($datatoshow2 as $rows2){
	$textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2].">".(isset($tdform)?"<form method='post' action='".$editprocess1.$rows2[$txnidname]."'>":"").(isset($tdform1)?"<form method='post' action='".$editprocess2.$rows2[$txnidname]."'>":"");
	foreach($fields2 as $col2){
	$textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows2[$col2])))) . "</td>";
	
	} // end foreach 2
	$colorcount++;
    $total=(isset($coltototal)?$total+$rows2[$coltototal]:0);
    $textfordisplay=$textfordisplay.((key($rows2)!=$keyoflast)?"":(isset($runtotal)?"<td>".number_format($total,2)."</td>":"")
        .(isset($tdform)?'<td><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">'
                .(isset($tdforminput)?$tdforminput:''). '<input type="submit" value="'.$editprocesslabel1.'"></form></td>':'')
        .(isset($tdform1)?'<td><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input type="submit" value="'.$editprocesslabel2.'"></form></td>':'').(isset($editprocess)?'<td><a href="'.$editprocess.$rows2[$txnidname].'">'.$editprocesslabel."</a></td>":"").(isset($newwindowprocess)?'<td><a style="text-decoration:none;" href="" onclick="window.open(\''.$newwindowprocess.$rows2[$txnidname].(isset($txnid2)?'&'.$txnid2.'='.$rows2[$txnid2]:'').'\', 
                         \'newwindow\', 
                         \'width='.(isset($newwindowwidth)?$newwindowwidth:'500').',height='.(isset($newwindowheight)?$newwindowheight:'500').'\'); 
              return false;">'.$newwindowprocesslabel."</a></td>":"")
        .(isset($delprocess)?'<td><a href="'.$delprocess.$rows2[$txnidname].'">'.$delprocesslabel."</a></td>":"")
        .(isset($addprocess)?'<td><a href="'.$addprocess.$rows2[$txnidname].'">'.$addprocesslabel.'</a></td>':'')
		
		 .(isset($addlfield)?'<td>'.(($rows2[$addlfield]==1)?'<a href="'.$addprocess1.$rows2[$txnidname].'">'.$addprocesslabel1.'</a></td></tr>':'<a href="'.$addprocess2.$rows2[$txnidname].'">'.$addprocesslabel2.'</a></td></tr>'):'</tr>')
		 );
    } // end checking if there are records in sub
} //end foreach data 2
$textfordisplay=$textfordisplay."</tr></tbody></table><br>";
if (isset($sqlsubtotal)){
    $subtotalstext='';
    $sqlsubtotal1= $sqlsubtotal . " WHERE " .$groupby ." Like '" . addslashes($rows[$groupby]) . "'"; 
    $stmtsubtotal=$link->prepare($sqlsubtotal1); $stmtsubtotal->execute(); $datatoshowsubtotal=$stmtsubtotal->fetch(PDO::FETCH_ASSOC);
    	foreach ($colsubtotals as $colsub){
	    $subtotalstext=$subtotalstext.$colsub.' Subtotal '.$datatoshowsubtotal[$colsub].'<br>';
	}
    
}
$textfordisplay=$textfordisplay.(isset($subtotalstext)?$subtotalstext:'');
$textfordisplay=$textfordisplay. (isset($coltototal)?"<br>Total ".$coltototal. ": " . number_format($total,2) . str_repeat('&nbsp',10):'');
$textfordisplay=$textfordisplay. (isset($coltototalsubtractedfrom)?$coltototalsubtractedfromlabel. ": " . number_format($rows[$coltototalsubtractedfrom]-$total,2) . str_repeat('&nbsp',10):'');
$textfordisplay=$textfordisplay. (isset($nocount)?"<br><br></div><hr>":count($datatoshow2)." record/s shown <br><br></div><hr>");
$grandtotal=$grandtotal+$total;
} //end foreach 1
echo $textfordisplay.(isset($showgrandtotal)?'Grand Total: '. number_format($grandtotal,2).str_repeat('&nbsp',10):''). (isset($nocount1)?'':(count($datatoshow1).' record/s shown'));
echo (isset($totalstext)?'<br>'.$totalstext:'');
goto endofreport;
nodata:
    if(isset($showsubtitlealways) and $showsubtitlealways and isset($subtitle) and !is_null($subtitle)) { echo '<h5>'.$subtitle.' - No Data</h5><br>';}
endofreport:
?>
</body>
</html>