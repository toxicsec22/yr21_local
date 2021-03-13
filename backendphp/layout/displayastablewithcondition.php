<html>
<head>
<title><?php echo $title; ?></title>
<?php
include_once('../switchboard/contents.php');
include_once('regulartablestyle.php');
?>
    
    <br><br>
    <h2><?php echo $title; ?></h2>
</head>
<body>
    <form method="POST" action="<?php echo $lookupprocess ?>" enctype="multipart/form-data">
<?php 
echo $listcaption; ?><input type="text" name="<?php echo $fieldname ?>" list="<?php echo $listname ?>" size=60 autocomplete="off" required="true"><input type="submit" name="lookup" value="Lookup">
<?php
include_once "../generalinfo/lists.inc";
foreach ($liststoshow as $list){
renderlist($list);    
}//end foreach

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
$sql=$sql. ' WHERE '.$fieldname.'=\'' . addslashes($_POST[$fieldname]).'\''.$addlcondition.' ORDER BY ' .$orderby;        
//echo $sql;
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
        $rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
        $rcolor[1]="FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2].">";
        $colorcount++;
        foreach($fields as $col){
            
          $textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";
        }
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":"</tr>");
       
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