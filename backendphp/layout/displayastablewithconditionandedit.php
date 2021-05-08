<html>
<head>
<title><?php echo $title; ?></title>
<?php
include_once('../switchboard/contents.php');
include_once('regulartablestyle.php');
 ?>  
    <br><h3><?php echo $title; ?></h3>
	<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br><br>
</head>
<body>
    <form method="POST" action="<?php echo $lookupprocess ?>" enctype="multipart/form-data">

        For Date<input type='date' name='<?php echo $fieldname; ?>' value='<?php echo date('Y-m-d');?>'></input>
<input type="submit" name="lookup" value="Lookup">
<?php
if (!isset($_POST[$fieldname])){
	goto noform;
    }
    ?>
    </form>
<?php 

echo $fieldname . ": " . $_POST[$fieldname].'<br>';
if (isset($addlmenu)){
	echo $addlmenu;
}
if (isset($addlcondition)){
	$addlcondition=$addlcondition;
	} else {
		$addlcondition='';
	}
$sql=$sql. (isset($skipsql)?'':' WHERE '.$fieldname.'=\'' . $_POST[$fieldname].'\''.$addlcondition.' ORDER BY ' .$orderby);        
// echo $sql; break;
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

//  echo "<br>".$sql."<br>" ;

    
$lastrecord=end($datatoshow);
if (count($datatoshow)>0) { $keyoflast=key($lastrecord);}
//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="FFFFCC";
        $rcolor[1]="FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2].">";
        $colorcount++;
        foreach($fields as $col){
            
          $textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";
        }
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($delprocess)?"<td><a href='".$delprocess.addslashes($rows[$txnidname])."&action_token=".$_SESSION['action_token']."' OnClick=\" return confirm('Really delete this?');\"'>Del</a></td>":"")."<td><a href='".$editprocess.addslashes($rows[$txnidname])."'>Edit</a></td></tr>");
       
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table><br>";
echo $textfordisplay;
echo count($datatoshow).((count($datatoshow)>1)?" records":" record");
if (isset($sumfield)){
	$sumsql=$sumsql. ' WHERE '.$fieldname.'=\'' . $_POST[$fieldname].'\''.$addlcondition;
	$stmt=$link->prepare($sumsql);
    $stmt->execute();
    $datatoshow=$stmt->fetch(PDO::FETCH_ASSOC);
    echo '<br>Total of '. $sumfield.': '.number_format($datatoshow[$sumfield],2);
}
noform:
?>
</body>
</html>