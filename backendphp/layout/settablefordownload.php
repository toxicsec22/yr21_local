 <?php
$txnidname='TxnID';
$numcols = 0;
$num=0; $runsum=0;
$fields=array();
$fieldlist="";
//$textfordisplay="";
$lastfield=end($columnnames);
$firstfield=reset($columnnames);//to go back to start?
foreach($columnnames as $field){
    $fieldlist=$fieldlist."" . $field.($field==$lastfield?"":",");
    $numcols=$numcols+1;
    $fields[$numcols]=$field;
}
$fieldlist=$fieldlist.(isset($runtotal)?',Running Sum':'') ;
$textfordisplay=$fieldlist .PHP_EOL ;
// echo $sql; break;
    $stmt=$link->prepare($sql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);
 
$lastrecord=end($datatoshow);
$keyoflast=key($lastrecord);

$total=0; $grandtotal=0;
foreach($datatoshow as $rows){

        foreach($fields as $col){
          $textfordisplay=$textfordisplay.  addslashes($rows[$col]) . ",";
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);  
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($runtotal)?",".number_format($total,2)."" .PHP_EOL:PHP_EOL));
	
} //end foreach
$textfordisplay=$textfordisplay.PHP_EOL;
//echo $textfordisplay;
//echo
//$textfordisplay=$textfordisplay.count($datatoshow).((count($datatoshow)>1)?" records":" record").(isset($showgrandtotal)?str_repeat('&nbsp',10).'Grand Total: '. number_format($total,2):'');
//echo
//$textfordisplay=$textfordisplay.(isset($totalstext)?$totalstext:'');
?>