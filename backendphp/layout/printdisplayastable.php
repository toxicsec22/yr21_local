<title><?php echo $title.' - Print'; ?></title>
<?php 
if (isset($outside) AND $outside){ $diraddress='../../../'.$url_folder.'/';}// for zzjye and aquasys use
else { $diraddress='../';}
?>
<style>
page  
{ 
   /* size: auto;   /* auto is the initial value */ 

    /* this affects the margin in the printer settings */ 
    margin: 18mm 10mm 10mm 10mm; 
}
body  
{ 
    /* this affects the margin on the content before sending to printer */ 
    margin: 12mm 10mm 10mm 10mm; 
    font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
}
thead {color:black;font-family:sans-serif; font-weight: bold; background-color: white; }
table,td {
    border-collapse:collapse;   border:1px solid black; padding: 6px;
    font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
/*  page-break-inside: avoid;*/
    }
    /*tr,*/td    { page-break-inside:avoid; page-break-after:auto }
 .keeptog  { /*  page-break-inside: avoid;*/ page-break-after:auto }
 
a:link {
    color: darkblue;
    text-decoration: none;
}
footer {
   position:absolute;
   bottom:0;
   width:90%;
   height: 10mm;
   margin: 0mm 0mm 0mm 0mm;
}
.break { page-break-before: always; }

</style>
<br><h3><a href="javascript:window.print()"><?php echo $title; ?></a>&nbsp; &nbsp; &nbsp; &nbsp;
	<i><?php echo (isset($formdesc)?$formdesc:'');?></i></h3><br>
</head>
<body>
    <?php
IF (isset($sortfield)){include($diraddress.'backendphp/layout/sortbyform.php');echo '<br><br>';} 
$txnidname=(!isset($txnidname)?'TxnID':$txnidname);
$numcols = 0;
$num=0; $runsum=0;
$fields=array();
$fieldlist="<table><thead><tr>";
$textfordisplay="<tbody style=\"overflow:auto;\">";
foreach($columnnames as $field){
    $fieldlist=$fieldlist . "<td>".$field."</td>";
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
$keyoflast=key($lastrecord);
//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="f2f2f2";
        $rcolor[1]="FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$total=0; $grandtotal=0;
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2].">";
        $colorcount++;
        //$textfordisplay=$textfordisplay."<tr>";
        foreach($fields as $col){
          $textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);  
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($formprocess)?'<td><form action="'.$formprocess.'">'.$formprocessfields.'<input type=hidden name='.$txnidname.' value='.$rows[$txnidname].'><input type=submit name=submit value="'.$submitlabel.'"></form></td>':'')
					 .(isset($editprocess)?'<td><a href="'.$editprocess.$rows[$txnidname].'">'.$editprocesslabel.'</a></td>':'')
					 .(isset($addlprocess)?'<td><a href='.$addlprocess.$rows[$txnidname].'&action_token='.$_SESSION['action_token'].'>'.$addlprocesslabel.'</a></td>':'')
					 .(isset($delprocess)?'<td><form method="post" action='.$delprocess.$rows[$txnidname].' style="display:inline"  OnClick="return confirm(\'Really delete this?\');"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input type="submit" value="Delete"></form></td>':'')
					 .(isset($runtotal)?"<td>".number_format($total,2)."</td></tr>":"</tr>"));
	//$grandtotal=$grandtotal+$total;
} //end foreach
$textfordisplay=$textfordisplay."</tbody></table><br>";
echo $textfordisplay;
echo (isset($hidecount)?'':(count($datatoshow).((count($datatoshow)>1)?" records":" record")));
echo (isset($showgrandtotal)?str_repeat('&nbsp',10).'Grand Total: '. number_format($total,2):'');
echo (isset($totalstext)?'<br>'.$totalstext:'');
?>
</body>
</html>