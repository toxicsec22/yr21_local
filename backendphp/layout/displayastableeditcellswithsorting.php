<html>
<head>
<title><?php echo $title; ?></title>
<?php

$path=$_SERVER['DOCUMENT_ROOT'];
if (isset($outside) AND $outside){ $hidecontents=true;}// for zzjye and aquasys use

if (isset($hidecontents) AND $hidecontents==1){ goto skipcontents;} else {include_once('../switchboard/contents.php');}
skipcontents:
include_once $path.'/acrossyrs/js/includesscripts.php';

if (isset($_REQUEST['print'])){ include $path.'/'.$url_folder.'/backendphp/layout/standardprintsettings.php';}
?>
<br><h3><?php echo $title; ?></h3>
	<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br>
</head>
<body class="wide comments table1">
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
$fieldlist.=(isset($runtotal)?'<th>Running Sum</th>':'').(isset($editprocess)?'<th></th>':'').(isset($addlprocess)?'<th></th>':'')
        .(isset($addlprocess2)?'<th></th>':'').(isset($delprocess)?'<th></th>':'').(isset($inputprocess)?'<th></th>':'')."</tr></thead>";
echo $fieldlist ;

    $stmt=$link->prepare($sql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);
 
$lastrecord=end($datatoshow);
if (count($datatoshow)>0) { $keyoflast=key($lastrecord);}
//echo "<br>key:  ".$keyoflast ."<br>";
$total=0; $grandtotal=0;
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
$columnstoeditselect=!isset($columnstoeditselect)?array():$columnstoeditselect;
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr><form method='post' action='".$editprocess.$rows[$txnidname]."'>";
        
        //$textfordisplay=$textfordisplay."<tr>";
        foreach($fields as $col){
	    if (in_array($col,$columnstoedit)){
			
          $textfordisplay=$textfordisplay."<td><input type='".(isset($type)?$type:"text")."' size=10 name='".$col."' value='". addslashes($rows[$col]) . "' ".(isset($disablefield)?(($rows[$triggercolumn]==$txtshouldbe)?'readonly="readonly" style="background:#D6DBDF;"':''):'')." ></td>";

	    } else if (in_array($col,$columnstoeditselect)){
			$textfordisplay=$textfordisplay."<td><select name='".$col."'><option value='". addslashes($rows[$col]) . "'>".$rows[$col]."</option>".$options."</select></td>";
		}
		else{	
	// $textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";	
	$textfordisplay=$textfordisplay."<td>". addslashes($rows[$col]) . "</td>";	
	    }
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);  
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($runtotal)?"<td>".number_format($total,2)."</td>":'').'<td><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input type="submit" value="'.$editprocesslabel.'"></td></form>'
	 .(isset($addlprocess)?'<td><a href='.$addlprocess.$rows[$txnidname].'&action_token='.$_SESSION['action_token'].'>'.$addlprocesslabel.'</a></td>':'') 
         .(isset($addlprocess2)?'<td><a href='.$addlprocess2.$rows[$txnidname].'&action_token='.$_SESSION['action_token'].'>'.$addlprocess2label.'</a></td>':'')
	 .(isset($delprocess)?'<td><form method="post" action='.$delprocess.$rows[$txnidname].' style="display:inline"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input type="submit" value="'.(!isset($delprocesslabel)?'Delete':$delprocesslabel).'"  OnClick="return confirm(\'Really delete this?\');"></form></td>':'')."</tr>");
	//$grandtotal=$grandtotal+$total;
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table><br>";
echo $textfordisplay;
echo (isset($showgrandtotal)?str_repeat('&nbsp',10).'Grand Total: '. number_format($total,2):'');

echo (isset($totalstext)?$totalstext:'');
?>
</body>
</html>