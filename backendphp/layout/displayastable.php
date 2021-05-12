<html>
<head>
<title><?php echo $title; ?></title>
<?php

$path=$_SERVER['DOCUMENT_ROOT'];
if (isset($outside) AND $outside){ $hidecontents=true;}// for zzjye and aquasys use

if (isset($hidecontents) AND $hidecontents==1){ goto skipcontents;} else {include_once($path.'/'.$url_folder.'/switchboard/contents.php');}
skipcontents:
include_once $path.'/acrossyrs/js/includesscripts.php';

if (isset($_REQUEST['print'])){ include $path.'/'.$url_folder.'/backendphp/layout/standardprintsettings.php';}
?>
<br><h3><?php echo $title; ?></h3>
	<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br>
</head>
<body>
	<a name="top" id="top"></a>
	
		
	<div class="fw-body">
		<div class="content" style="width:<?php echo isset($width)?$width:'100%'; ?>">
    <?php
echo (isset($subtitle) and !is_null($subtitle))?'<h4>'.$subtitle.'</h4><br>':'';
$txnidname=(!isset($txnidname)?'TxnID':$txnidname);
$numcols = 0;
$num=0; $runsum=0;
$fields=array();
$fieldlist='<table id="table1" class="display" style="width:100%; font-size: 10pt; ">' 
        . '<thead><tr>';
$textfordisplay="<tbody>"; 
foreach($columnnames as $field){
    $fieldlist=$fieldlist . "<th>".$field."</th>";
    $numcols=$numcols+1;
    $fields[$numcols]=$field;
}
$fieldlist.=(isset($runtotal)?'<th>Running Sum</th>':'').(isset($editprocess)?'<th></th>':'').(isset($editprocess2)?'<th></th>':'').(isset($addlprocess)?'<th></th>':'')
        .(isset($addlprocess2)?'<th></th>':'').(isset($delprocess)?'<th></th>':'').(isset($editprocess3)?'<th></th>':'').(isset($inputprocess)?'<th></th>':'')."</tr></thead>";
echo $fieldlist ;
// echo $sql; break;
    $stmt=$link->prepare($sql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);
 
$lastrecord=end($datatoshow);
if (count($datatoshow)>0) { $keyoflast=key($lastrecord);}
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
          // $textfordisplay=$textfordisplay."<td>". htmlspecialchars(nl2br(addslashes($rows[$col]))) . "</td>";
          // $textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";
          $textfordisplay=$textfordisplay."<td>". $rows[$col] . "</td>";
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);  
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($formprocess)?'<td><form action="'.$formprocess.'">'.$formprocessfields.'<input type=hidden name='.$txnidname.' value='.$rows[$txnidname].'><input type=submit name=submit value="'.$submitlabel.'"></form></td>':'')
		//showeditprocess
					 .((isset($editprocess) AND 1==(isset($rows['showeditprocess'])?$rows['showeditprocess']:'1'))?'<td><a href="'.$editprocess.$rows[$txnidname].(isset($addlfield)?'&'.$addlfield.'='.$rows[$addlfield]:'').'" '.((isset($opennewtab) AND $opennewtab)?' target=_blank':'').'>'.$editprocesslabel.'</a></td>':(isset($rows['showeditprocess'])?'<td></td>':''))
					  .(isset($editprocess2)?'<td><a href="'.$editprocess2.$rows[$txnidname].(isset($addlfield)?'&'.$addlfield.'='.$rows[$addlfield]:'').'">'.$editprocesslabel2.'</a></td>':'')
					 .((isset($addlprocess) AND 1==(isset($rows['showaddlprocess'])?$rows['showaddlprocess']:'1'))?'<td><a href='.$addlprocess.$rows[$txnidname].'&action_token='.$_SESSION['action_token'].'>'.$addlprocesslabel.'</a></td>':(isset($rows['showaddlprocess'])?'<td></td>':'')).(isset($addlprocess2)?'<td><a href='.$addlprocess2.$rows[$txnidname].'&action_token='.$_SESSION['action_token'].'>'.$addlprocesslabel2.'</a></td>':'')
					 .(isset($delprocess)?'<td><form method="post" action='.$delprocess.$rows[$txnidname].' style="display:inline"  OnClick="return confirm(\'Really delete this?\');"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input type="submit" value="Delete"></form></td>':'')
					   .(isset($editprocess3)?'<td><a href="'.$editprocess3.$rows[$txnidname].(isset($addlfield)?'&'.$addlfield.'='.$rows[$addlfield]:'').'">'.$editprocesslabel3.'</a></td>':'')
                                         .(!isset($inputprocess)?"":"<td><form method=post action='" . $inputprocess.$txnidname."=".$rows[$txnidname].'&action_token='.$_SESSION['action_token']."'>". $inputprocesslabel .
				      "<input type='".(!isset($inputtype)?"text":$inputtype)."' name='".$inputname."' size=10 ".(!isset($inputplaceholder)?"":" placeholder='".$inputplaceholder."' ").(!isset($inputdefault)?"":"value='".$inputdefault."'")."><input type=submit value='Enter' name='submit'></form></td>")
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