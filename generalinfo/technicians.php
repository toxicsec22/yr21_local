<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(912,'1rtc')) { echo 'No permission'; exit; }
$showbranches=true; include_once('../switchboard/contents.php');

$which=!isset($_GET['w'])?'List':$_GET['w'];
$title='Add New Technician';  
$list='List';
$table='gen_info_1technicians'; $txnid='TechID'; $txnidname='TechID'; 
$sql='SELECT `t`.*,mask(MobileNo,"####-###-####") AS Mobile,(SELECT GROUP_CONCAT(Branch) FROM 1branches WHERE FIND_IN_SET(BranchNo,t.BranchNos)) AS Branches,IF(EmployedByClientNo<>0,EmployedByClientNo,"") AS EmployedByClientNo,IF(EmployedByClientNo<>0,ClientName,"") AS EmployedByClient, NickName AS EncodedBy FROM gen_info_1technicians `t` JOIN 1employees e ON `t`.EncodedByNo=e.IDNo LEFT JOIN 1clients c ON t.EmployedByClientNo=c.ClientNo ';
$columnnameslist2=array('TechName', 'MobileNo','BranchNos','EmployedByClientNo','Position','Subscribed');
$columnnameslist=array('TechName', 'Mobile','Branches','EmployedByClient','Position','Subscribed');

$columnstoadd=$columnnameslist2;
$columnstoedit=$columnstoadd;
$columnswithlists=array('EmployedByClientNo');
$listsname=array('EmployedByClientNo'=>'clientlist');
$listssql=array(
    array('sql'=>'Select ClientNo,ClientName FROM `1clients` WHERE Inactive=0 AND ClientNo NOT IN (10000,10001,10004)', 'listvalue'=>'ClientName', 'label'=>'ClientNo','listname'=>'clientlist')
);
$showenc=true;

$formdesc='<br></i><div style="background-color: #e6e6e6;
  width: 1100px;
  border: 2px solid grey;
  padding: 15px;
  margin: 15px;" ><b>Encoding Guide</b><br>1. TechName = Technician Name (Juan Dela Cruz)<br>2. MobileNo = 11 digits (09121231212)<br>3. BranchNos = Branch Numbers <i>(if multiple: BranchNos: <b>1,2,3</b> else BranchNos: <b>1</b>)</i><br>4. Position = Optional<br>5. EmployedByClientNo = Optional<br>6. Subscribed = 1: Receive SMS (default), 0: Stop receiving sms. </div><i>';

if($which=='List') {
$columnentriesarray=array(
                    array('field'=>'TechName', 'type'=>'text','size'=>20, 'required'=>true),
                    array('placeholder'=>'0912-123-1212','input-mask'=>'____-___-____','maxlength'=>'13','id'=>'formatnumber','field'=>'MobileNo', 'type'=>'text','size'=>20, 'required'=>true),
                    array('field'=>'BranchNos', 'type'=>'text','size'=>20, 'value'=>$_SESSION['bnum'], 'required'=>true),
                    array('field'=>'Position', 'type'=>'text','size'=>20, 'required'=>false),
                    array('field'=>'EmployedByClientNo', 'type'=>'text','size'=>20, 'list'=>'clientlist','required'=>false),
                    array('field'=>'Subscribed', 'type'=>'hidden','size'=>5,'value'=>'0', 'required'=>0)
                    );
}
    
$file='technicians.php?w='; $fieldsinrow=4; $liststoshow=array(); 

$addcommand='Add'; $editcommand='Edit'; $editspecs='EditSpecifics'; $delcommand='Delete'; $addallowed=912; $editallowed=912; $delallowed=912;

if (allowedToOpen(912,'1rtc')) { $delprocess='technicians.php?w=Delete&TechID=';$editprocess='technicians.php?w=EditSpecifics&TechID='; $editprocesslabel='Edit';}
$width='80%';
        
// set first field only if the first field should also be added/edited
$firstfield='TechName';

$encodedbyno=true;
if (allowedToOpen(914,'1rtc')){
	$delcondition='';
} else {
	$delcondition=' AND EncodedByNo='.$_SESSION['(ak0)'].'';
}
$inputmask=true;
$strrepfield='MobileNo'; $strrepfieldfrom='-'; $strrepfieldto='';

$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';
include('../backendphp/layout/genlists.php');


?>