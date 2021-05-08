<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if ((!allowedToOpen(6063,'1rtc')) AND (!allowedToOpen(60631,'1rtc'))) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');


$which=!isset($_GET['w'])?'BranchList':$_GET['w'];
$title='Add New Branch';  $formdesc='<a href="newbranchdata.php">Other commands for new branches and _comkey updates.</a>';
$list='BranchList';
$table='1branches'; $txnidname='BranchNo';
$sql='SELECT b.*, a.Area, Company,RegionMinWageArea
FROM `1branches` b JOIN `0area` a ON a.AreaNo=b.AreaNo JOIN `1companies` c ON c.CompanyNo=b.CompanyNo left join `1_gamit`.`payroll_0regionsminwageareas` rmwa on rmwa.MinWageAreaID=EffectiveMinWageAreaID ';
if (allowedToOpen(6063,'1rtc')){
$columnnameslist=array('BranchNo', 'RegionMinWageArea','Branch', 'Company', 'Area', 'Anniversary', 'TargetShareWith', 'ProvincialBranch', 'RegisteredAddress','RDO', 'IPAdd', 'ProgCookie','OnBiometrics', 'LeadTimeinDays', 'Active', 'Landline', 'Mobile', 'Email', 'MovedBranch', 'PseudoBranch', 'WithSunday','ServedByWH','PriceLevel');
$columnstoadd=array_diff($columnnameslist,array('Area','Company','RegionMinWageArea'));
$columnstoadd[]='AreaNo'; $columnstoadd[]='CompanyNo'; $columnstoadd[]='EffectiveMinWageAreaID';
} else {
	$columnnameslist=array('BranchNo', 'Branch', 'ProgCookie', 'OnBiometrics');
	$columnstoadd=array('ProgCookie', 'OnBiometrics');
}

$columnstoedit=$columnstoadd;
$columnswithlists=array('Area','Company');
$listsname=array('Area'=>'areas','Company'=>'companies');
$listssql=array(
    array('sql'=>'Select * FROM `0area`', 'listvalue'=>'Area', 'label'=>'AreaNo','listname'=>'areas'),
	array('sql'=>'Select * FROM `1branches` where pseudobranch=2', 'listvalue'=>'Branch', 'label'=>'ServedByWH','listname'=>'WHno'),
	array('sql'=>'SELECT MinWageAreaID, RegionMinWageArea FROM `1_gamit`.`payroll_0regionsminwageareas` ORDER BY RegionMinWageArea','listvalue'=>'RegionMinWageArea','label'=>'MinWageAreaID','listname'=>'regionsminwageareas'),
    array('sql'=>'Select *, Company FROM `1companies` WHERE Active<>0', 'listvalue'=>'Company', 'label'=>'CompanyNo','listname'=>'companies')
);


if($which=='BranchList') {
include $path . '/acrossyrs/commonfunctions/fxngenrandpass.php';
$progcookie=generatePassword(45);
$columnentriesarray=array(
                    array('field'=>'AreaNo', 'type'=>'text','size'=>10, 'required'=>true,'list'=>'areas'),
                    array('field'=>'CompanyNo', 'type'=>'text','size'=>10, 'required'=>true,'list'=>'companies'),
                    array('field'=>'BranchNo','type'=>'text','size'=>5,'required'=>true),
                    array('field'=>'Branch', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'Anniversary', 'type'=>'date','size'=>8,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'TargetShareWith','type'=>'text','size'=>5, 'required'=>true),
                    array('field'=>'ProvincialBranch', 'type'=>'text','size'=>1, 'required'=>true, 'value'=>'0'),
                    array('field'=>'RegisteredAddress', 'type'=>'text','size'=>50, 'required'=>true),
                    array('field'=>'Mobile', 'type'=>'text','size'=>10, 'required'=>false),
                    array('field'=>'Landline', 'type'=>'text','size'=>10, 'required'=>false),
                    array('field'=>'Email', 'type'=>'text','size'=>25, 'value'=>'@1rotarytrading.com', 'required'=>true),
                    array('field'=>'ProgCookie','type'=>'hidden', 'value'=>$progcookie, 'size'=>0),
                    array('field'=>'OnBiometrics','type'=>'text', 'value'=>'0', 'size'=>0),
                    array('field'=>'LeadTimeinDays','type'=>'text','size'=>5, 'value'=>'7','required'=>true),
                    array('field'=>'PseudoBranch','type'=>'hidden', 'size'=>0, 'value'=>'0'),
                    array('field'=>'IPAdd','type'=>'hidden', 'size'=>0, 'value'=>null),
                    array('field'=>'WithSunday', 'type'=>'text','size'=>1, 'required'=>true, 'value'=>'0'),
					array('field'=>'EffectiveMinWageAreaID','type'=>'text','size'=>10,'required'=>true,'list'=>'regionsminwageareas'),
					
						
					
                    array('field'=>'Active', 'type'=>'hidden','size'=>0, 'required'=>false, 'value'=>'1'),
                    array('field'=>'MovedBranch', 'type'=>'hidden','size'=>0, 'required'=>false, 'value'=>'-1')
                    );
					if (allowedToOpen(60634,'1rtc')){
					array_push($columnentriesarray,array('field'=>'ServedByWH', 'type'=>'text','size'=>10,'list'=>'WHno', 'required'=>false),
						array('field'=>'PriceLevel', 'type'=>'text','size'=>5,'required'=>false));
					}
}
    
            $file='newbranchentry.php?w='; $fieldsinrow=6; $liststoshow=array(); 

$addcommand='Add'; $editcommand='Edit'; $editspecs='EditSpecifics'; $delcommand='Delete'; $addallowed=6063; $editallowed=(allowedToOpen(6063,'1rtc'))?6063:60631; $delallowed=6063;

if (allowedToOpen(6063,'1rtc') OR allowedToOpen(60631,'1rtc')) { if (allowedToOpen(6063,'1rtc')) { $delprocess='newbranchentry.php?w=Delete&BranchNo='; } $editprocess='newbranchentry.php?w=EditSpecifics&BranchNo='; $editprocesslabel='Edit';}

        
// set first field only if the first field should also be added/edited
$firstfield=allowedToOpen(6063,'1rtc')?'BranchNo':'ProgCookie';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';
	
include('../backendphp/layout/genlists.php');

if (allowedToOpen(60631,'1rtc')) { goto noadd2; }

 
$which=($which=='BranchList')?'AreaList':$_GET['w'];
$title='Add New Area'; unset($formdesc); 
$list='AreaList';
$table='0area'; $txnidname='AreaNo'; $width='30%';
$sql='SELECT * FROM `0area` ';
$columnnameslist=array('AreaNo', 'Area');
$columnstoadd=$columnnameslist;
$columnstoedit=$columnstoadd;
$columnswithlists=array();
$listsname=array();
$listssql=array();

$columnentriesarray=array(
                    array('field'=>'AreaNo', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'Area', 'type'=>'text','size'=>10, 'required'=>true)
                    );

    
            $file='newbranchentry.php?w='; $fieldsinrow=3; $liststoshow=array(); 

$addcommand='AddArea'; $editcommand='EditArea'; $editspecs='EditSpecificsArea'; $delcommand='DeleteArea'; $addallowed=6063; $editallowed=6063; $delallowed=6063;

if (allowedToOpen(6063,'1rtc')) { $delprocess='newbranchentry.php?w=DeleteArea&AreaNo=';$editprocess='newbranchentry.php?w=EditSpecificsArea&AreaNo='; $editprocesslabel='Edit';}

        
// set first field only if the first field should also be added/edited
$firstfield='AreaNo';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';

include('../backendphp/layout/genlists.php');

 
$which=($which=='AreaList')?'CompanyList':$_GET['w'];
$title='Add New Company'; unset($formdesc); 
$list='CompanyList';
$table='1companies'; $txnidname='CompanyNo'; $width='100%';
$sql='SELECT * FROM `1companies` ';
$columnnameslist=array('CompanyNo', 'CompanyName', 'TelNo','Company','IncorporationDate','RegisteredAddress','RDO','RepBranchNo','SSSNo','PHICNo','PagIbigNo','TIN','SECRegNo','Active');
$columnstoadd=$columnnameslist;
$columnstoedit=$columnstoadd;
$columnswithlists=array();
$listsname=array();
$listssql=array();

$columnentriesarray=array(
                    array('field'=>'CompanyNo', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'CompanyName', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'TelNo', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'Company', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'IncorporationDate', 'type'=>'date','size'=>10, 'required'=>true),
                    array('field'=>'RegisteredAddress', 'type'=>'text','size'=>10, 'required'=>true)
                    );

    
            $file='newbranchentry.php?w='; $fieldsinrow=6; $liststoshow=array(); 

$addcommand='AddCompany'; $editcommand='EditCompany'; $editspecs='EditSpecificsCompany'; $delcommand='DeleteCompany'; $addallowed=6063; $editallowed=6063; $delallowed=6063;

if (allowedToOpen(6063,'1rtc')) { $delprocess='newbranchentry.php?w=DeleteCompany&CompanyNo=';$editprocess='newbranchentry.php?w=EditSpecificsCompany&CompanyNo='; $editprocesslabel='Edit';}

        
// set first field only if the first field should also be added/edited
$firstfield='CompanyNo';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';

include('../backendphp/layout/genlists.php');

$which=($which=='CompanyList')?'Departments':$_GET['w'];
$title='Add New Department'; unset($formdesc); 
$list='Departments';
$table='1departments'; $txnidname='deptid'; $width='60%';
$sql='SELECT * FROM `1departments` ';
$columnnameslist=array('deptid','department','deptheadpositionid','orderby','tel','address');
$columnstoadd=$columnnameslist;
$columnstoedit=$columnstoadd;
$columnswithlists=array();
$listsname=array();
$listssql=array();

$columnentriesarray=array(
                    array('field'=>'deptid', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'department', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'deptheadpositionid', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'orderby', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'tel', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'RegisteredAddress', 'type'=>'text','size'=>10, 'required'=>true)
                    );

    
            $file='newbranchentry.php?w='; $fieldsinrow=6; $liststoshow=array(); 

$addcommand='AddDept'; $editcommand='EditDept'; $editspecs='EditSpecificsDept'; $delcommand='DeleteDept'; $addallowed=6063; $editallowed=6063; $delallowed=6063;

if (allowedToOpen(6063,'1rtc')) { $delprocess='newbranchentry.php?w=DeleteDept&deptid=';$editprocess='newbranchentry.php?w=EditSpecificsDept&deptid='; $editprocesslabel='Edit';}

        
// set first field only if the first field should also be added/edited
$firstfield='deptid';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';

include('../backendphp/layout/genlists.php');
noadd2:
?>