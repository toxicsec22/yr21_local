<html>
<head>
<title><?php echo $title; ?></title>
<?php
include_once('../switchboard/contents.php');
include_once('regulartablestyle.php');

?>    
    <br><br>
</head>
<body>
    <form method="POST" action="<?php echo $pagetouse ?>" enctype="multipart/form-data">
<?php
if (isset($calledfrom)){
switch ($calledfrom) {
    case 4: // attendance per team per day
    case 6: // canvass per day
        echo "For Date<input type='date' name='$fieldname' ></input>";
        echo "<!-- ";
        break;
    case 5: // days assigned
        echo "<input type='date' name='$fieldname' ></input>";
        echo "<input type='date' name='$fieldname2' ></input>";
        echo "<!-- ";
        break;
    default:
        break;
}
 
}
?>
<?php echo $listcaption; ?><input type="text" name="<?php echo $fieldname ?>" list="<?php echo $listname ?>" size=60 autocomplete="off" required="true">
<datalist id="<?php echo $listname ?>"> 
<?php  
 if (in_array($calledfrom,array(4,5,6))){
    goto skiplist;
 }
		foreach ($link->query($listsql) as $row) {
                ?>
                <option value="<?php echo $row[$listvalue]; ?>" label="<?php echo $row[$listlabel]; ?>"></option>
                <?php
                } // end while
                ?>
                
</datalist>
<?php  
 if (in_array($calledfrom,array(7))){
    goto skiplist;
 }

echo $listcaption2; ?><input type="text" name="<?php echo $fieldname2 ?>" list="<?php echo $listname2 ?>" size=60 autocomplete="off" required="true">
<datalist id="<?php echo $listname2 ?>"> 
<?php  
            
		foreach ($link2->query($listsql2) as $row) {
                ?>
                <option value="<?php echo $row[$listvalue2]; ?>" label="<?php echo $row[$listlabel2]; ?>"></option>
                <?php
                } // end while
                ?>
                
</datalist id="<?php echo $listname2 ?>">

<?php
skiplist:
if (isset($calledfrom)){
    if ($calledfrom==4 or $calledfrom==5 or $calledfrom==6){
    echo "-->";
}
}
?>

<input type="submit" name="lookup" value="Lookup">
<?php
if (!isset($_POST[$fieldname])){
	goto noform;
    }
    ?>
    </form>
<?php
//variables to define:
//$datatoshow=;
//$columnnames=array(...);
//$link=;
//$showbranches=boolean;
echo $fieldname . ": " . $_POST[$fieldname]. "       " .(isset($_POST[$fieldname2])?$fieldname2 . ": " . $_POST[$fieldname2]:' ');
if (isset($_GET['calledfrom'])){
    switch ($_GET['calledfrom']){
        case 1 : //item activity
          //  $datatoshow=$_POST['datatoshow'];
            $columnnames=$_POST['columnnames'];
            $link=$_POST['dbtouse'];
            break;
        case 2://attendance per branch
            $sql=$sql .' WHERE BranchNo='. $_POST[$fieldname2] . ' and PayrollID='.$_POST[$fieldname] . ' ORDER BY ' . $orderby;
            break;
        case 3: //attendance per person
                $sql=$sql .' WHERE IDNo='. $_POST[$fieldname2] . ' and PayrollID='.$_POST[$fieldname] . ' ORDER BY ' . $orderby;
            break;
        case 4: //attendance per team per day
                $sql=$sql. ' and DateToday=\''. $_POST[$fieldname]  . '\' ORDER BY ' . $orderby;
                break;
        case 5: //days assigned
                $sql=$sql. ' and DateToday>=\''. $_POST[$fieldname]  . '\' and DateToday<=\''. $_POST[$fieldname2]  . '\'  GROUP BY `1employees`.IDNo ORDER BY ' . $orderby;
                break;
        case 6: //canvass
                $sql=$sql. ' AND CanvassDate=\'' . $_POST[$fieldname].'\' ORDER BY ' .$orderby;
                break;
        case 7: //to check attendance
                $sql=$sql. '  AND (PayrollID)=\'' . $_POST[$fieldname].'\' ORDER BY ' .$orderby;
                break;
        default:
            break;
    }
}
$numcols = 0;
$num=0;
$fields=array();
$fieldlist="<li><table style=\"display: inline-block; border: 1px solid\"><thead><tr>";
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
$keyoflast=key($lastrecord);
//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="FFFFCC";
        $rcolor[1]="FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2].">";
        
        foreach($fields as $col){
            
          $textfordisplay=$textfordisplay."<td>". addslashes($rows[$col]) . "</td>";
        }
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":"</tr>");
       
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table><br>";
echo $textfordisplay;

noform:
?>
</body>
</html>