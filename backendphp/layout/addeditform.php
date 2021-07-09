<?php 
$path=$_SERVER['DOCUMENT_ROOT'];
if (isset($outside) AND $outside){ $hidecontents=true;}// for zzjye & othercompanies
else { 
    
    include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
    include_once '../switchboard/contents.php';
}

include_once $path.'/acrossyrs/commonfunctions/editokfromposted.php';
if (isset($skippost)){ $editok=($edit==2)?TRUE:FALSE;} else { $editok=editOkfromPosted($link,$table,(!isset($txnidname)?'TxnID':$txnidname),$txnid,(!isset($posted)?'Posted':$posted)); }
//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="EDDBFF";
        $rcolor[1]="FFFFFF";

echo '<title>'.$title.'</title>';

// MAIN FORM  
$stmt=$link->query($sqlmain); $result=$stmt->fetch();
if (!isset($nopost)){
    $postvalue=($result['Posted']==1?0:1); $postfield='Posted'; $txnid=!isset($txnidname)?$txnid:$txnidname;
    include('../backendphp/layout/postunpostform.php');
}

$main='';

if ($editok){
    $editmain='<td><a href="'.$editprocess.'">Edit</a>'.str_repeat('&nbsp',8).'<a href='.$delprocess.'&action_token='.$_SESSION['action_token'].' OnClick="return confirm(\'Really delete this?\');">Delete</a>';
    if (isset($addlprocess)){ $editmain=$editmain.str_repeat('&nbsp',8).'<a href="'.$addlprocess.'">'.$addlprocesslabel.'</a>';}
    $editmain=$editmain.'</td>';
    /*if (isset($secondaddl)){ $editmain=$editmain.str_repeat('&nbsp',8).'<a href="'.$secondaddl.'">'.$secondaddllabel.'</a>';}
    $editmain=$editmain.'</td>';*/
    $columnstoeditmain=$columnstoeditmain;
        
} else {
    $editmain='';
    if (isset($postedprocess)){ $editmain=$editmain.str_repeat('&nbsp',8).'<td><a href="'.$postedprocess.'">'.$postedprocesslabel.'</a></td>';}
    $columnstoeditmain=array();
}

$colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%(isset($fieldsinrowmain)?$fieldsinrowmain:4)==0?'</tr><tr>':'');
    }
$main='<table><tr>'.$main.$editmain.'</tr></table>';
    
echo '<br><br><h4>'.$title.'</h4><br><br>';
echo (isset($formdesc)?$formdesc.'<br><br>':'');

echo $main;
if ($editok){
    // additional form for special conditions
    if(isset($addform)){echo $addform;}
    include('inputsubformgeneric.php');}

//SUB FORM
unset($title);
$subth='';
foreach ($columnsub as $colsub){ $subth=$subth.'<th>'.$colsub.'</th>';}

$stmt=$link->query($sqlsub); $resultsub=$stmt->fetchAll();
$sub='';

foreach ($resultsub as $row){
if ($editok){
    $editsub='<td><a href="'.$editprocesssub.$row['TxnSubId'].'">Edit</a>'.str_repeat('&nbsp',8).'<a href='.$delprocesssub.$row['TxnSubId'].'&action_token='.$_SESSION['action_token'].' OnClick="return confirm(\'Really delete this?\');">Delete</a>';
    if (isset($addlprocesssub)){ $editsub=$editsub.str_repeat('&nbsp',8).'<a href="'.$addlprocesssub.$row['TxnSubId'].'&action_token='.$_SESSION['action_token'].'">'.$addlprocesssublabel.'</a>';}
    $editsub=$editsub.'</td>';
    //$columnstoeditsub=$columnstoeditsub;        
} else {
    $columnstoeditsub=array(); $editsub='';
}

$colno=0;
    foreach ($columnsub as $rowsub){
        $colno=$colno+1;
        $sub=$sub.'<td>'.$row[$rowsub].str_repeat('&nbsp',5).'</td>'.($colno%(isset($fieldsinrowsub)?$fieldsinrowsub:4)==0?'</tr><tr>':'');
}
$sub='<tr>'.$sub.$editsub.'</tr>';
}
echo '<table><tr>'.$subth.'</tr>'.$sub.'</table>';

//SHOW TOTAL
$stmt=$link->query($sqltotal); $resulttotal=$stmt->fetch();
echo '<br><br>Total: '.$resulttotal['Total'];
?>