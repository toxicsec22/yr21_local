<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(58229,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');

$which=!isset($_GET['w'])?'List':$_GET['w'];
$title='Add New Findings';  
$list='List';
$table='acctg_0blotterfindingsstmt'; $txnid='FID'; $txnidname='FID';
$sql='SELECT `t`.*, NickName AS EncodedBy FROM acctg_0blotterfindingsstmt `t` JOIN 1employees e ON `t`.EncodedByNo=e.IDNo ';
$columnnameslist=array('Findings');
$columnstoadd=$columnnameslist;
$columnstoedit=$columnstoadd;
$listssql=array();
$showenc=true;



if($which=='List') {
$columnentriesarray=array(
                    array('field'=>'Findings', 'type'=>'text','size'=>70, 'required'=>true)
                    );
}
    
$file='stmtblotterfindings.php?w='; $fieldsinrow=2; $liststoshow=array(); 

$addcommand='Add'; $editcommand='Edit'; $editspecs='EditSpecifics'; $delcommand='Delete'; $addallowed=58229; $editallowed=58229; $delallowed=58229;

if (allowedToOpen(58229,'1rtc')) { $delprocess='stmtblotterfindings.php?w=Delete&FID=';$editprocess='stmtblotterfindings.php?w=EditSpecifics&FID='; $editprocesslabel='Edit';}
$width='70%';
        
// set first field only if the first field should also be added/edited
$firstfield='Findings';

$encodedbyno=true;
$editcondition=' AND FID NOT IN (SELECT DISTINCT(FID) FROM invty_2salesubblotterfindings)';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';
include('../backendphp/layout/genlists.php');


?>