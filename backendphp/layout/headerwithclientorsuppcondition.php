<html>
<head>
<title><?php echo $title; ?></title>
<?php
include_once('../switchboard/contents.php');
include_once('regulartablestyle.php');
include_once('../generalinfo/lists.inc');

if (isset($_GET['done'])){
	if ($_GET['done']==1){
	echo '<font color="red">Data encoded.</font>';
	} else {
	echo '<font color="red">No permission.</font>';
	}
}
?>    
    <br><h3><?php echo $title; ?></h3>
</head>
<body>
	<form action='<?php echo $lookupprocess; ?>' method='post'  enctype='multipart/form-data'>
	<?php echo $listcaption; ?><input type='text' name='<?php echo $listvalue; ?>' list='<?php echo $listname; ?>' autocomplete='off'></input>
	<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" /> 
<input type="submit" name="lookup" value="Lookup">
<?php
renderlist($listname);
?>
</form>
<?php
if (!isset($_POST[$listvalue])){
	goto noform;
    }
$value=getValue($link,$table,$listvalue, (stripslashes($_POST[$listvalue])),$fieldname);
    
echo $listcaption . ": <b>" . strtoupper($_POST[$listvalue]).'   ClientNo:'.$value."</b><br><br>";
if (isset($addlmenu)){
	echo $addlmenu.'<br>';
}
if (isset($addlcondition)){
	$addlcondition=$addlcondition;
	} else {
		$addlcondition='';
	}
$sql=$sql. ' WHERE '.$fieldname.'=\'' . $value.'\''.$addlcondition.' ORDER BY ' .$orderby;        

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

 // echo "<br>".$sql."<br>" ;

    
$lastrecord=end($datatoshow);
$keyoflast=key($lastrecord);
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
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($delprocess)?"<td><a href='".$delprocess.addslashes($rows[$txnid])."' OnClick=\"sendTokentoDelete()\"'>Del</a></td>":"").(isset($editprocess)?"<td><a href='".$editprocess.addslashes($rows[$txnid])."'>Edit</a></td></tr>":""));
       
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
