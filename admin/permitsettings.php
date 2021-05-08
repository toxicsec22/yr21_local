<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(8294,'1rtc')) { echo 'No Permission'; exit(); }
$showbranches=false; include_once('../switchboard/contents.php');
 
include_once('../backendphp/layout/linkstyle.php');


$which=!isset($_GET['w'])?'PermitSettings':$_GET['w'];

include_once('permitlinks.php');
$title='Add New Permit Type';  $formdesc=''; $width='50%';
$list='PermitSettings';
$table='admin_1permittype'; $txnidname='PID';
$sql='SELECT p.*, Frequency FROM `admin_1permittype` p JOIN `admin_0permitfrequency` pf ON p.FID=pf.FID ';
$columnnameslist=array('Permit', 'PermitDetails', 'Frequency');
$columnstoadd=array_diff($columnnameslist,array('Frequency'));
$columnstoadd[]='FID';
$columnstoedit=$columnstoadd;
$columnswithlists=array('Frequency');
$listsname=array('Frequency'=>'frequency');
$listssql=array(
    array('sql'=>'Select * FROM `admin_0permitfrequency`', 'listvalue'=>'Frequency', 'label'=>'FID','listname'=>'frequency')
);

if($which=='PermitSettings') {
$columnentriesarray=array(

	array('field'=>'Permit','type'=>'text','size'=>10,'required'=>true),
	array('field'=>'PermitDetails','type'=>'text','size'=>10,'required'=>true),
	array('field'=>'FID', 'type'=>'text','size'=>10, 'required'=>true,'list'=>'frequency')
	);
}

$file='permitsettings.php?w='; $fieldsinrow=6; $liststoshow=array(); 

$addcommand='Add'; $editcommand='Edit'; $editspecs='EditSpecifics'; $delcommand='Delete'; $addallowed=8294; $editallowed=8294; $delallowed=8294;

if (allowedToOpen(8294,'1rtc')) { $delprocess='permitsettings.php?w=Delete&PID=';$editprocess='permitsettings.php?w=EditSpecifics&PID='; $editprocesslabel='Edit';}

// set first field only if the first field should also be added/edited
$firstfield='Permit';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';
//$orderby = ' ORDER BY PID';
include('../backendphp/layout/genlists.php'); //unset($orderby);


$which=($_GET['w']=='PermitSettings')?'FrequencyList':$_GET['w'];
$title='Add New Frequency'; unset($formdesc); 
$list='FrequencyList';
$table='admin_0permitfrequency'; $txnidname='FID'; $width='30%';
$sql='SELECT * FROM `'.$table.'` ';
$columnnameslist=array('FID', 'Frequency');
$columnstoadd=$columnnameslist;
$columnstoedit=$columnstoadd;
$columnswithlists=array();
$listsname=array();
$listssql=array();

$columnentriesarray=array(
                    array('field'=>'FID', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'Frequency', 'type'=>'text','size'=>10, 'required'=>true)
                    );

    
            $file='permitsettings.php?w='; $fieldsinrow=3; $liststoshow=array(); 

$addcommand='AddFrequency'; $editcommand='EditFrequency'; $editspecs='EditSpecificsFrequency'; $delcommand='DeleteFrequency'; $addallowed=8294; $editallowed=8294; $delallowed=8294;

if (allowedToOpen(8294,'1rtc')) { $delprocess='permitsettings.php?w=DeleteFrequency&FID=';$editprocess='permitsettings.php?w=EditSpecificsFrequency&FID='; $editprocesslabel='Edit';}

        
// set first field only if the first field should also be added/edited
$firstfield='FID';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';

include('../backendphp/layout/genlists.php');



$which=($_GET['w']=='PermitSettings')?'StatusList':$_GET['w'];
$title='Add New Status'; unset($formdesc); 
$list='StatusList';
$table='admin_0permitstatus'; $txnidname='StatusID'; $width='30%';
$sql='SELECT * FROM `'.$table.'` ';
$columnnameslist=array('StatusID', 'StatusDetails');
$columnstoadd=$columnnameslist;
$columnstoedit=$columnstoadd;
$columnswithlists=array();
$listsname=array();
$listssql=array();

$columnentriesarray=array(
                    array('field'=>'StatusID', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'StatusDetails', 'type'=>'text','size'=>10, 'required'=>true)
                    );

    
            $file='permitsettings.php?w='; $fieldsinrow=3; $liststoshow=array(); 

$addcommand='AddStatus'; $editcommand='EditStatus'; $editspecs='EditSpecificsStatus'; $delcommand='DeleteStatus'; $addallowed=8294; $editallowed=8294; $delallowed=8294;

if (allowedToOpen(8294,'1rtc')) { $delprocess='permitsettings.php?w=DeleteStatus&StatusID=';$editprocess='permitsettings.php?w=EditSpecificsStatus&StatusID='; $editprocesslabel='Edit';}

        
// set first field only if the first field should also be added/edited
$firstfield='StatusID';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';

include('../backendphp/layout/genlists.php');

 
?>