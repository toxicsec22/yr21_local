<?php
//echo isset($title)?'<h3>'.$title.'</h3>':'';
if (!isset($_REQUEST[$fieldname])){	
	// goto noform;
    } else {
echo '<br><br>'.$fieldname=='w'? '':'<h4>'.$fieldname . ': ' . $_REQUEST[$fieldname].'</h4>';
    }
(isset($sortfield)?include('../backendphp/layout/sortbyform.php'):'');  echo '<br><br>';
echo isset($print)?$print:'';
    ?>
<!--    <table style="display: inline-block; border: 1px solid; float: left; ">-->
<?php echo (isset($_GET['closeddata'])?'<font color="red">Data protected.</font>':''); ?>
<?php

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
// echo $sql;
    $stmt=$link->prepare($sql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);

$lastrecord=end($datatoshow);
$keyoflast=key($lastrecord);
//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"CCFFFF"):"FFFFFF");
        $rcolor[1]="FFFFFF";
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2].">";
        $colorcount++;
        foreach($fields as $col){
            
          $textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";
        }
	
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":"<td><a href='" . $process1 .$txnidname."=".$rows[$txnidname]
					 .(isset($addlfield)?'&'.$addlfield.'='.$rows[$addlfield]:'')."'> " . $processlabel1 . "</td>")
				    .(!isset($processlabel2)?"":"<td><a href='" . $process2 .$txnidname."=".$rows[$txnidname].'&action_token='.$_SESSION['action_token']."'> " . $processlabel2 . "</td>")
				    .(!isset($inputprocess)?"":"<td><form method=post action='" . $inputprocess.$txnidname."=".$rows[$txnidname].'&action_token='.$_SESSION['action_token']."'>". $inputprocesslabel .
				      "<input type='".(!isset($inputtype)?"text":$inputtype)."' name='".$inputname."' size=10 ".(!isset($inputdefault)?"":"value='".$inputdefault."'")."><input type=submit value='Enter' name='submit'></form></td>")
				    ."</tr>"; 
       
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table><br>";
echo $textfordisplay;
echo count($datatoshow).((count($datatoshow)>1)?" records":" record").(isset($total)?'<br>'.$total:'');
if (isset($lookupsub)){
	?>
	<form method='POST'>
	<input type='hidden' name='lookupsub' value='<?php echo $lookupsub; ?>'>
	</form>
	<?php
	} //end if

 noform:
 ?>	

</body>
</html>