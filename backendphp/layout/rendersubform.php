<html>
<head>
<title><?php echo $title; ?></title>
<?php
include_once('../switchboard/contents.php');
include_once('regulartablestyle.php');
?>
</head>
<body>
<form method="<?php echo $method; ?>" action="<?php echo $action; ?>" enctype="multipart/form-data">
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

$fieldlist=$fieldlist . "<tr></thead><tbody>";
echo $fieldlist ;
//echo $sql;
    $stmt=$link->prepare($sql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);

//echo "<br>".$sql."<br>" ;

$lastrecord=end($datatoshow);
$keyoflast=key($lastrecord);
//echo "<br>key:  ".$keyoflast ."<br>";
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");

foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr>";
        
        foreach($fields as $col){
            
          $textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";
        }
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":"</tr>");
       
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table><br>";
echo $textfordisplay;
//if ($_GET['edit']==2 or $_GET['edit']==3) {
if (in_array($_GET['edit'],array(2,3,5))){
include('rendersubformtoedit.php');
?>
<input type="submit" name="submit" value="Submit">&nbsp&nbsp&nbsp&nbsp&nbsp<a href="<?php echo $processblank; ?>"><?php echo $processlabelblank; ?></a><br>
<?php } elseif ($_GET['edit']==0 and isset($lookupsub)) {
   // echo $lookupsub;
include($lookupsub);    
}// end if ?>
</form>
</body>
</html>