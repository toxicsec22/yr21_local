<body class="wide comments table1">
<?php
echo '<style>
	th {
	  background: white;
	  position: sticky;
	  top: 0;
	  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
	}
	</style>';
$path=$_SERVER['DOCUMENT_ROOT'];
//include_once('regulartablestyle.php');
echo isset($addlmenu)?$addlmenu.'<br>':'';
// if ($_SESSION['(ak0)']==1002){ echo 'action token:'.$_SESSION['action_token'];}
$numcols = 0;
$num=0; $runsum=0;

    $stmt=$link->prepare($sql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);
    
if ($stmt->rowCount()==0){ goto nodata;}
echo (isset($subtitle) and !is_null($subtitle))?'<h4>'.$subtitle.'</h4><br>':'';
IF (isset($sortfield)){include($path.'/'.$url_folder.'/backendphp/layout/sortbyform.php');echo '<br><br>';} 

$fields=array();
$fieldlist='<table id="table2" class="display" style="width:'.(isset($width)?$width:'60%').'; font-size: 10pt;"><thead><tr>'; //border: 1px solid
$textfordisplay="<tbody>";

foreach($columnnames as $field){
    $fieldlist=$fieldlist . "<th>".$field."</th>";
    $numcols=$numcols+1;
    $fields[$numcols]=$field;
}
$fieldlist=$fieldlist . (isset($runtotal)?'<th>Running Sum</th>':'')."<tr></thead>";
echo $fieldlist ;

$lastrecord=end($datatoshow);
if (count($datatoshow)>0) { $keyoflast=key($lastrecord);}
//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]=(!isset($_REQUEST['print'])?(isset($color1)?$color1:"E6FFCC"):"FFFFFF");
       // $rcolor[0]=isset($color1)?$color1:"E6FFCC";
        $rcolor[1]=isset($color2)?$color2:"FFFFFF";
//echo "<br>key:  ".$keyoflast ."<br>";
$total=0;
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");



$oldvalue=0;

foreach($datatoshow as $rows){

        $textfordisplay=$textfordisplay."<tr bgcolor=". $rcolor[$colorcount%2].">";
        $colorcount++;
        
        if((!isset($hidesamevalues))) { goto skiphide;} else { 
            if($oldvalue!==$rows[$hidesamevalues]) { $oldvalue=$rows[$hidesamevalues]; goto skiphide;}  else {  $rows[$hidesamevalues]='';}             
            }
        skiphide:
        
        foreach($fields as $col){
            
            
          $textfordisplay=$textfordisplay."<td ".($rows[$col]<0?"bgcolor=lightcoral ":"").">". nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",addslashes($rows[$col])))) . "</td>";
        }
	$total=(isset($coltototal)?$total+$rows[$coltototal]:0);  
        $textfordisplay=$textfordisplay.((key($rows)!=$keyoflast)?"":(isset($runtotal)?"<td>".number_format($total,2)."</td>":"")
					 .(isset($editprocess)?"<td><a href='".$editprocess.addslashes($rows[$txnidname])."'>".(isset($editprocesslabel)?$editprocesslabel:"Lookup")."</a></td>":"")
            .(isset($addlprocess)?"<td><a href='".$addlprocess.addslashes($rows[$txnidname]).'&action_token='.$_SESSION['action_token']."'>".$addlprocesslabel."</a></td>":"")
					 .(isset($addlprocess2)?"<td><a href='".$addlprocess2.addslashes($rows[$txnidname]).'&action_token='.$_SESSION['action_token']."'>".$addlprocesslabel2."</a></td>":"")."</tr>");
} //end foreach
$textfordisplay=$textfordisplay.(isset($totaltable)?'<tr>'.$totaltable.'</tr>':'')."</tbody></table><br>";
echo $textfordisplay;
echo (isset($hidecount)?'':count($datatoshow).((count($datatoshow)>1)?" records":" record")).(isset($showgrandtotal)?str_repeat('&nbsp',10).'Grand Total: '. number_format($total,2):'');
echo (isset($totalstext)?$totalstext:''); goto endofreport;
nodata:
    if(isset($showsubtitlealways) and $showsubtitlealways and isset($subtitle) and !is_null($subtitle)) { echo '<h5>'.$subtitle.' - No Data</h5><br>';}
endofreport:
    ?></body>