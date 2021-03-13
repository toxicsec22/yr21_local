<html>
<head>
<title><?php echo $title; ?></title>
<?php
$path=$_SERVER['DOCUMENT_ROOT'];
//
if (isset($outside) AND $outside){ $diraddress='../../../'.$url_folder.'/'; $hidecontents=true;}// for zzjye and aquasys use
else { $diraddress='../';}

if (isset($hidecontents) AND $hidecontents==1){ goto skipcontents;} else {include_once('../switchboard/contents.php');}
skipcontents:

//include_once('regulartablestyle.php');
if (isset($_REQUEST['print'])){ include ($diraddress.'backendphp/layout/standardprintsettings.php');}

include_once $path.'/acrossyrs/js/includesscripts.php';?>

<br><h3><?php echo $title; ?></h3>
	<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br>
</head>
<body class="wide comments table1">
	<a name="top" id="top"></a>
	
		
	<div class="fw-body">
		<div class="content">
    <?php
echo (isset($subtitle) and !is_null($subtitle))?'<h4>'.$subtitle.'</h4><br>':'';
//IF (isset($sortfield)){include($diraddress.'backendphp/layout/sortbyform.php');echo '<br><br>';} 
$txnid=(!isset($txnidname)?'TxnID':$txnidname);
$numcols = 0;
$num=0; $runsum=0;
$fields=array();
$fieldlist='<table id="table1" class="display" ><thead><tr>';// style=\"display: inline-block; border: 1px solid\" style="width:100%"
$textfordisplay="<tbody>"; // style=\"overflow:auto;\"
foreach($columnnames as $field){
    $fieldlist=$fieldlist . "<th>".$field."</th>";
    $numcols=$numcols+1;
    $fields[$numcols]=$field;
}
$fieldlist.=(isset($runtotal)?'<th>Running Sum</th>':'').(isset($editprocess)?'<th></th>':'').(isset($addlprocess)?'<th></th>':'')
        .(isset($addlprocess2)?'<th></th>':'').(isset($delprocess)?'<th></th>':'').(isset($inputprocess)?'<th></th>':'')."</tr></thead>";
echo $fieldlist ;
// echo $sql; break;
    $stmt=$link->prepare($sql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);
 
$lastrecord=end($datatoshow);
$keyoflast=key($lastrecord);
//to make alternating rows have different colors
//        $colorcount=0;
//        $rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
//        $rcolor[1]="FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$total=0; $grandtotal=0;
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr>";
       // $colorcount++;
        //$textfordisplay=$textfordisplay."<tr>";
        foreach($fields as $col){
          $textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);  
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($formprocess)?'<td><form action="'.$formprocess.'">'.$formprocessfields.'<input type=hidden name='.$txnid.' value='.$rows[$txnid].'><input type=submit name=submit value="'.$submitlabel.'"></form></td>':'')
					 .(isset($editprocess)?'<td><a href="'.$editprocess.$rows[$txnid].'">'.$editprocesslabel.'</a></td>':'')
					 .(isset($addlprocess)?'<td><a href='.$addlprocess.$rows[$txnid].'&action_token='.$_SESSION['action_token'].'>'.$addlprocesslabel.'</a></td>':'').(isset($addlprocess2)?'<td><a href='.$addlprocess2.$rows[$txnid].'&action_token='.$_SESSION['action_token'].'>'.$addlprocesslabel2.'</a></td>':'')
					 .(isset($delprocess)?'<td><form method="post" action='.$delprocess.$rows[$txnid].' style="display:inline"  OnClick="return confirm(\'Really delete this?\');"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input type="submit" value="Delete"></form></td>':'')
                                         .(!isset($inputprocess)?"":"<td><form method=post action='" . $inputprocess.$txnid."=".$rows[$txnid].'&action_token='.$_SESSION['action_token']."'>". $inputprocesslabel .
				      "<input type='".(!isset($inputtype)?"text":$inputtype)."' name='".$inputname."' size=10 ".(!isset($inputdefault)?"":"value='".$inputdefault."'")."><input type=submit value='Enter' name='submit'></form></td>")
					 .(isset($runtotal)?"<td>".number_format($total,2)."</td></tr>":"</tr>"));
	//$grandtotal=$grandtotal+$total;
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table></div><br>";
echo $textfordisplay;
//(isset($hidecount)?'':(count($datatoshow).((count($datatoshow)>1)?" records":" record"))).
echo (isset($showgrandtotal)?str_repeat('&nbsp',10).'Grand Total: '. number_format($total,2):'');
echo (isset($totalstext)?'<br>'.$totalstext:'');
?>
</div>
</body>
</html>