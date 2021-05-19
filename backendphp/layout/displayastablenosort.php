<html>
<head>
<title><?php echo $title; ?></title>
<style>
.trcolor1 { border: 1px solid blue; }
.trcolor2 { border: 1px solid green; }
.trcolor3 { border: 1px solid red; }
.trcolor4 { border: 1px solid orange; }
.trcolor5 { border: 1px solid violet; }
.trcolor6 { border: 1px solid yellow; }
.trcolor7 { border: 1px solid skyblue; }
</style>
<?php

if (isset($outside) AND $outside){ $diraddress='../../../'.$url_folder.'/'; $hidecontents=true;}// for zzjye and aquasys use
else { $diraddress='../';}

if (isset($hidecontents) AND $hidecontents==1){ goto skipcontents;} else {include_once('../switchboard/contents.php');}
skipcontents:

include_once('regulartablestyle.php');
if (isset($_REQUEST['print'])){ include ($diraddress.'backendphp/layout/standardprintsettings.php');} else {
	echo '<style>
		th {
		  text-align:left;
		  background: white;
		  position: sticky;
		  top: 0;
		  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
		}
	</style>';
}
?>

<br><h3><?php echo $title; ?></h3>
	<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br>
</head>
<body><div style="width:<?php echo isset($width)?$width:'100%'; ?>">
    <?php
echo (isset($subtitle) and !is_null($subtitle))?'<h4>'.$subtitle.'</h4><br>':'';
IF (isset($sortfield)){include($diraddress.'backendphp/layout/sortbyform.php');echo '<br><br>';} 
$txnidname=(!isset($txnidname)?'TxnID':$txnidname);
$numcols = 0;
$num=0; $runsum=0;
$fields=array();
$fieldlist="<table style=\"".(!isset($_REQUEST['print'])?'display: inline-block;':'')." border: 1px solid\"><thead><tr>";
$textfordisplay="<tbody style=\"overflow:auto;\">";
foreach($columnnames as $field){
    $fieldlist=$fieldlist . "<th>".$field."</th>";
    $numcols=$numcols+1;
    $fields[$numcols]=$field;
}
$fieldlist=$fieldlist . (isset($runtotal)?'<td>Running Sum</td>':'')."<tr></thead>";
echo $fieldlist ;
// echo $sql; break;
    $stmt=$link->prepare($sql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);
   
$lastrecord=end($datatoshow);
if (count($datatoshow)>0) { $keyoflast=key($lastrecord);}
//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
        $rcolor[1]="FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$total=0; $grandtotal=0;
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
$oldcolor=''; $cntcolor=0;
foreach($datatoshow as $rows){
if(isset($changecolorfield)){
    if($rows[$changecolorfield]%2==0){ $rcolor[0]=(!isset($_REQUEST['print'])?"ccffff":"FFFFFF");} else { $rcolor[0]=(!isset($_REQUEST['print'])?"FFFFCC":"FFFFFF");}  
} 
        $textfordisplay=$textfordisplay."<tr  bgcolor=". $rcolor[$colorcount%2].">";
        $colorcount++;
        
        foreach($fields as $col){
        //   $textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";
          $textfordisplay=$textfordisplay."<td>". nl2br(str_replace($fromBRtoN,"\n",$rows[$col])) . "</td>";
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);  
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($formprocess)?'<td><form action="'.$formprocess.'">'.$formprocessfields.'<input type=hidden name='.$txnid.' value='.$rows[$txnidname].'><input type=submit name=submit value="'.$submitlabel.'"></form></td>':'')
            
	.(isset($editprocess)?'<td><a href="'.$editprocess.$rows[$txnidname].(isset($addlfield)?'&'.$addlfield.'='.$rows[$addlfield]:'').'" '.(isset($editprocessonclick)?$editprocessonclick:'').'>'.$editprocesslabel.'</a></td>':'')
	
        .(isset($editprocess2)?'<td><a href="'.$editprocess2.$rows[$txnidname].(isset($addlfield)?'&'.$addlfield.'='.$rows[$addlfield]:'').'" '.(isset($editprocess2onclick)?$editprocess2onclick:'').'>'.$editprocesslabel2.'</a></td>':'')
            
	.(isset($editprocess4)?'<td><a href="'.$editprocess4.$rows[$txnidname].(isset($addlfield)?'&'.$addlfield.'='.$rows[$addlfield]:'').'" '.(isset($editprocess4onclick)?$editprocess4onclick:'').'>'.$editprocesslabel4.'</a></td>':'')
            
	.(isset($editprocess5)?'<td><a href="'.$editprocess5.$rows[$txnidname].(isset($addlfield)?'&'.$addlfield.'='.$rows[$addlfield]:'').'" '.(isset($editprocess5onclick)?$editprocess5onclick:'').'>'.$editprocesslabel5.'</a></td>':'')
					  
	.(isset($addlprocess)?'<td><a href='.$addlprocess.$rows[$txnidname].'&action_token='.$_SESSION['action_token'].' '.(isset($addlprocessonclick)?$addlprocessonclick:'').'>'.$addlprocesslabel.'</a></td>':'')
            
        .(isset($addlprocess2)?'<td><a href='.$addlprocess2.$rows[$txnidname].'&action_token='.$_SESSION['action_token'].'>'.$addlprocesslabel2.'</a></td>':'')
					 
        .(isset($delprocess)?'<td><form method="post" action='.$delprocess.$rows[$txnidname].' style="display:inline"  OnClick="return confirm(\'Really delete this?\');"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input type="submit" value="Delete"></form></td>':'')
					  
        .(isset($editprocess3)?'<td><a href="'.$editprocess3.$rows[$txnidname].(isset($addlfield)?'&'.$addlfield.'='.$rows[$addlfield]:'').'">'.$editprocesslabel3.'</a></td>':'')
                                         
        .(!isset($inputprocess)?"":"<td><form method=post action='" . $inputprocess.$txnid."=".$rows[$txnidname].'&action_token='.$_SESSION['action_token']."'>". $inputprocesslabel 
                
        . "<input type='".(!isset($inputtype)?"text":$inputtype)."' name='".$inputname."' size=10 ".(!isset($inputdefault)?"":"value='".$inputdefault."'")."><input type=submit value='Enter' name='submit'></form></td>")
                
	.(isset($runtotal)?"<td>".number_format($total,2)."</td></tr>":"</tr>"));
	
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table><br>";

echo $textfordisplay.'</div>';
echo (isset($hidecount)?'':(count($datatoshow).((count($datatoshow)>1)?" records":" record"))).(isset($showgrandtotal)?str_repeat('&nbsp',10).'Grand Total: '. number_format($total,2):'');
echo (isset($totalstext)?'<br>'.$totalstext:'');
?>
</body>
</html>