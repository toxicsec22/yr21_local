<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(331,'1rtc')){ echo 'No permission'; exit;}
$showbranches=false; include_once('../switchboard/contents.php');

$choose=!isset($_REQUEST['c'])?'NewClientNo':($_REQUEST['c']);
$which=!isset($_GET['w'])?'NewClientNo':$_GET['w'];
$addcommand='Add'; $editcommand='Edit'; $editspecs='EditSpecifics'; $delcommand='Delete'; $addallowed=331; $editallowed=331; $delallowed=331;
switch ($choose){
case 'NewClientNo':
$title='New ClientNo';
$formdesc='</br></i><div style="background-color:#ededed; width:800px; padding:5px;">
<b>Notes:</b></br></br>
	1. The old client name/number must be set as inactive in the Clients List, before a new client name/number can be attached to it.</br>
	2. Once listed here, the inactive client name/number can no longer be reverted to active status.</br> 
</div>';  
$list='NewClientNo';
$file='newclientno.php?c='.$choose.'&w='; $fieldsinrow=5; $liststoshow=array(); 
$txnidname='OldClientNo';
$columnswithlists=array('OldClientNo');
$listsname=array('OldClientNo'=>'clientlist');
$columnstoadd=array('OldClientNo','NewClientNo');
$columnstoedit=$columnstoadd;
$delprocess=$file.'Delete&'.$txnidname.'=';$editprocess=$file.'EditSpecifics&'.$txnidname.'='; $editprocesslabel='Edit'; 
$listssql=array(
    array('sql'=>'SELECT ClientNo, ClientName FROM `1clients` where Inactive=1', 'listvalue'=>'ClientName', 'label'=>'ClientNo','listname'=>'clientlist')
);
$now=date('Y-m-d H:i:s');
// echo $now; exit();
	$columnentriesarray=array(
                    array('field'=>'OldClientNo', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'clientlist'),
					array('field'=>'NewClientNo', 'type'=>'text','size'=>10, 'required'=>true)
                    );
	$sql='select cnn.*,CONCAT (e.Nickname," ",e.SurName) as EncodedBy from 1clientsnewname cnn  left join 1employees e on e.IDNo=cnn.EncodedByNo';
	$columnnameslist=array('OldClientNo','NewClientNo','EncodedBy','TimeStamp');	
$encodedbyno=true;	
$table='1clientsnewname';
$firstfield='OldClientNo';
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';

if($which==$addcommand or $which==$editcommand){
	$sqlc='select * from 1clients where ClientNo=\''.$_REQUEST['OldClientNo'].'\' and Inactive=0 limit 1';
	// echo $sqlc; exit();
					$stmtc=$link->query($sqlc);
					if($stmtc->rowCount()!=0){
						echo 'This client is still active.';
						exit();
					}
	
}
     include('../backendphp/layout/genlists.php');
	

break;


}
?>