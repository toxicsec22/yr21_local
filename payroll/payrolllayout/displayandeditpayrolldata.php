<html>
<head>
<title><?php echo $title; ?></title>
<?php
echo '<style>
	th {
	  background: white;
	  position: sticky;
	  top: 0;
	  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
	}
	</style>';
include_once('../switchboard/contents.php');
include_once('../backendphp/layout/regulartablestyle.php');
include_once('../generalinfo/lists.inc');
?>    
<br><h3><?php echo $title; ?></h3>
<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br>
<?php
if (isset($_GET['done'])){
	echo '<font color="red">Data has been edited.</font>';
}
?>
</head>
<body>
    <form method="POST" action="<?php echo $lookupprocess ?>" enctype="multipart/form-data">
        For Payroll ID<input type='text' name='payrollid' list='payperiods'></input>
	<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" /> 
<input type="submit" name="lookup" value="Lookup">
<?php
renderlist('payperiods');
if (!isset($_POST[$fieldname])){
	goto noform;
    }
    ?>
    </form>
<?php
// echo $fieldname . ": " . $_POST[$fieldname];
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo $fieldname . ": " . $_POST[$fieldname].' ('.comboBoxValue($link, 'payroll_1paydates', 'PayrollID', $_POST[$fieldname], 'PayrollDate').')';
if (isset($addlmenu)){
	echo $addlmenu;
}
if (isset($addlcondition)){
	$addlcondition=$addlcondition;
	} else {
		$addlcondition='';
	}
$sql=$sql. ' WHERE '.$fieldname.'=\'' . $_POST[$fieldname].'\''.$addlcondition.' ORDER BY ' .$orderby;    
$numcols = 0;
$num=0;
$fields=array();
$fieldlist="<li><table style=\"display: inline-block; border: 1px solid\"><thead><tr>";
$textfordisplay="<tbody>";
foreach($columnnames as $field){
    $fieldlist=$fieldlist . "<th>".$field."</th>";
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
        $colorcount++;
        foreach($fields as $col){
            
          $textfordisplay=$textfordisplay."<td>". htmlspecialchars(addslashes($rows[$col])) . "</td>";
        }
	$textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":
                (isset($delprocess)?'<td><form method="post" action='.$delprocess.$rows[$txnid].' style="display:inline"  OnClick="return confirm(\'Really delete this?\');"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input type="submit" value="Delete"></form></td>':'')
                 .(isset($editprocess)?"<td><a href='".$editprocess.addslashes($rows[$txnid])."'>Edit</a></td>":"")."</tr>");      
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table><br>";
echo $textfordisplay;
echo (!isset($hidecount)?(count($datatoshow).((count($datatoshow)>1)?" records":" record")):'');
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