<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(65072,'1rtc')) { echo 'No Permission'; exit(); }
$showbranches=false; include_once('../switchboard/contents.php');
 
include_once('../backendphp/layout/linkstyle.php');



$which=!isset($_GET['w'])?'PointSettings':$_GET['w'];

include_once('scorelinks.php');
$title='Add New WeightinPoints';  $formdesc=''; $width='35%';
$list='PointSettings';
$table='hr_70points'; $txnidname='PointID';
$sql='SELECT p.* FROM `hr_70points` p ';
$columnnameslist=array('WeightinPoints','PointDesc');
$columnstoadd=$columnnameslist;
$columnstoedit=$columnstoadd;
$columnswithlists=array();
$listsname=array();
$listssql=array();


if($which=='PointSettings') {
$columnentriesarray=array(

	array('field'=>'WeightinPoints','type'=>'text','size'=>10,'required'=>true),
	array('field'=>'PointDesc','type'=>'text','size'=>10,'required'=>true)
	);
}

$file='scoresettings.php?w='; $fieldsinrow=6; $liststoshow=array(); 

$addcommand='Add'; $editcommand='Edit'; $editspecs='EditSpecifics'; $delcommand='Delete'; $addallowed=65072; $editallowed=65072; $delallowed=65072;

if (allowedToOpen(65072,'1rtc')) { $delprocess='scoresettings.php?w=Delete&PointID=';$editprocess='scoresettings.php?w=EditSpecifics&PointID='; $editprocesslabel='Edit';}

// set first field only if the first field should also be added/edited
$firstfield='WeightinPoints';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';
//$orderby = ' ORDER BY PID';
include('../backendphp/layout/genlists.php'); //unset($orderby);



 
$which=($_GET['w']=='PointSettings')?'MaxPoints':$_GET['w'];
$title='Add New MaxPoint'; unset($formdesc); 
$list='MaxPoints';
$table='hr_70maxpoint'; $txnidname='MaxPointID'; $width='35%';
$sql='SELECT * FROM `'.$table.'` ';
$columnnameslist=array('MaxPoint');
$columnstoadd=$columnnameslist;
$columnstoedit=$columnstoadd;
$columnswithlists=array();
$listsname=array();
$listssql=array();

$columnentriesarray=array(
                    array('field'=>'MaxPoint', 'type'=>'text','size'=>10, 'required'=>true)
                    );

    
            $file='scoresettings.php?w='; $fieldsinrow=3; $liststoshow=array(); 

$addcommand='AddMaxPoint'; $editcommand='EditMaxPoint'; $editspecs='EditSpecificsMaxPoint'; $delcommand='DeleteMaxPoint'; $addallowed=65072; $editallowed=65072; $delallowed=65072;

if (allowedToOpen(65072,'1rtc')) { $delprocess='scoresettings.php?w=DeleteMaxPoint&MaxPointID=';$editprocess='scoresettings.php?w=EditSpecificsMaxPoint&MaxPointID='; $editprocesslabel='Edit';}

        
// set first field only if the first field should also be added/edited
$firstfield='MaxPoint';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';

include('../backendphp/layout/genlists.php');


 
?>