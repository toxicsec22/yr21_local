<html>
<head>
<title><?php echo $title; ?></title>
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
        border:0px solid black;
border-collapse:collapse;
padding: 3px;
    font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
/*  page-break-inside: avoid;*/
    }
    /*tr,*/td    { page-break-inside:avoid; page-break-after:auto }
 .keeptog  { /*  page-break-inside: avoid;*/ page-break-after:auto }
 
@media print  
{
     table {
        border:0px solid black;
        border-collapse:collapse;
        padding: 1px;
    
    font-weight: 300;
/*page-break-inside: avoid;*/
    }
    tr,td    { /*page-break-inside:avoid;*/ page-break-after:auto; font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;}
    thead {color:black;font-family:sans-serif; font-weight: 600; background-color: white; }
}
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
</style>
<br><h3><?php echo $title; ?>&nbsp; &nbsp; &nbsp; &nbsp;
	<i><?php echo (isset($formdesc)?$formdesc:'');?></i></h3><br>
</head>
<body>
<?php 
$stmt=$link->prepare($sql1); $stmt->execute(); $datatoshow1=$stmt->fetchAll(PDO::FETCH_ASSOC);
$numcols1 = 0; $numcols2 = 0; $num=0;
$fields=array(); $fieldlist2="<thead>"; $fieldlist1="";

foreach($columnnames1 as $field){
    $fieldlist1=$fieldlist1 . "<thead>".$field."</thead>";
    $numcols1=$numcols1+1;
    $fields1[$numcols1]=$field;
}
foreach($columnnames2 as $field){
    $fieldlist2=$fieldlist2 . "<td>".$field."</td>";
    $numcols2=$numcols2+1;
    $fields2[$numcols2]=$field;
}
$fieldlist2=$fieldlist2 . "</tr>";

$textfordisplay="";    

$total=0;$grandtotal=0;

foreach($datatoshow1 as $main){
    $total=0;
    $textfordisplay=$textfordisplay."<div class='keeptog'><table size=\"100%\" style=\"display: inline-block; border: 0px; background-color:#FFF;;\"><tr>";
    foreach($fields1 as $col1){
       $textfordisplay=$textfordisplay."<td>". addslashes($main[$col1]) . "</td>";
       } //end foreach 
	$textfordisplay=$textfordisplay."</tr></table><br>";
	$sql3="";
	$datatoshow2=array();
       $sql3= $sql2 . " WHERE " .$groupby ." Like '" . addslashes($main[$groupby]) . "' ".(!isset($secondcondition)?'':$secondcondition).$orderby;
        // echo $sql3; 
       $stmt=$link->prepare($sql3);	$stmt->execute();
	$datatoshow2=$stmt->fetchAll(PDO::FETCH_ASSOC);$datatoshow2count=$stmt->rowCount();
	if ($stmt->rowCount()>0){
	$lastrecord=end($datatoshow2);
	$keyoflast=key($lastrecord);
	 $fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />"); 
	$textfordisplay=$textfordisplay."<table style=\"display: inline-block; border: 1px solid\"><thead>". $fieldlist2 ."</thead><tbody>";
foreach($datatoshow2 as $sub){
	$textfordisplay=$textfordisplay."<tr>";
	foreach($fields2 as $col2){
	$textfordisplay=$textfordisplay."<td>". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($sub[$col2])))) . "</td>";
	
	} // end foreach 2
	
    $total=(isset($coltototal)?$total+$sub[$coltototal]:0);
    $textfordisplay=$textfordisplay.((key($sub)!=$keyoflast)?"":(isset($runtotal)?"<td>".number_format($total,2)."</td>":"").(isset($editprocess)?'<td><a href="'.$editprocess.$sub[$txnid].'">'.$editprocesslabel.'</a></td></tr>':'</tr>'));
    } // end checking if there are records in sub
} //end foreach data 2
$textfordisplay=$textfordisplay."</tr></tbody></table><br>";
if (isset($sqlsubtotal)){
    $subtotalstext='';
    $sqlsubtotal1= $sqlsubtotal . " WHERE " .$groupby ." Like '" . addslashes($main[$groupby]) . "'"; 
    $stmtsubtotal=$link->prepare($sqlsubtotal1); $stmtsubtotal->execute(); $datatoshowsubtotal=$stmtsubtotal->fetch(PDO::FETCH_ASSOC);
    	foreach ($colsubtotals as $colsub){
	    $subtotalstext=$subtotalstext.$colsub.' '.$datatoshowsubtotal[$colsub].'<br>';
	}
    
}
$textfordisplay=$textfordisplay. (isset($nocount)?"<br><br></div>":$datatoshow2count.(($datatoshow2count>1)?" lines<br><br>":" line<br><br>")."");
$textfordisplay=$textfordisplay.(isset($subtotalstext)?$subtotalstext:''); 
$textfordisplay=$textfordisplay. (isset($coltototal)?"<br>Total ".$coltototal. ": " . number_format($total,2) . str_repeat('&nbsp',10):'').'<br><br></div>'.(count($datatoshow1)>1?'<hr>':'');

$grandtotal=$grandtotal+$total;
} //end foreach 1
echo $textfordisplay.(isset($showgrandtotal)?'Grand Total: '. number_format($grandtotal,2).str_repeat('&nbsp',10):'');
echo (isset($showcount)?count($datatoshow1).' record/s':'');
echo (isset($totaltext)?$totaltext:'');
unset ($datatoshow1,$datatoshow2);
echo (isset($footer)?$footer:'');
?>
</body>
</html>