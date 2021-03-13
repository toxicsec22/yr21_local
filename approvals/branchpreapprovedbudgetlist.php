<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6073,'1rtc')){ echo 'No permission'; exit;}
$showbranches=false; include_once('../switchboard/contents.php');

$choose=!isset($_REQUEST['c'])?'BudgetList':($_REQUEST['c']);
$which=!isset($_GET['w'])?'BudgetList':$_GET['w'];
$addcommand='Add'; $editcommand='Edit'; $editspecs='EditSpecifics'; $delcommand='Delete'; $addallowed=6073; $editallowed=6073; $delallowed=6073;
switch ($choose){
case 'BudgetList':
$title='Branch Pre-Approved Budget List';  
$list='BudgetList';
$file='branchpreapprovedbudgetlist.php?c='.$choose.'&w='; $fieldsinrow=4; $liststoshow=array(); 
$txnidname='TypeID';
$columnswithlists=array('AccountID');
$listsname=array('AccountID'=>'ShortAcctID');
$columnstoadd=array('TypeID','BudgetDesc','AccountID');
$columnstoedit=$columnstoadd;
$delprocess=$file.'Delete&'.$txnidname.'=';$editprocess=$file.'EditSpecifics&'.$txnidname.'='; $editprocesslabel='Edit'; 
$listssql=array(
    array('sql'=>'SELECT AccountID, ShortAcctID FROM `acctg_1chartofaccounts`', 'listvalue'=>'ShortAcctID', 'label'=>'AccountID','listname'=>'ShortAcctID')
);
	$columnentriesarray=array(
                    array('field'=>'TypeID', 'type'=>'text','size'=>1, 'required'=>true),
					array('field'=>'BudgetDesc', 'type'=>'text','size'=>15, 'required'=>true),
					array('field'=>'AccountID', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'ShortAcctID')
                    );
	$sql='select * from acctg_1branchpreapprovedbudgetlist';
	$columnnameslist=array('TypeID','BudgetDesc','AccountID');	
	
$table='acctg_1branchpreapprovedbudgetlist';
$firstfield='TypeID';
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';

 if($which==$editspecs){
	 $sqlc='select * from acctg_5branchpreapprovedbudgetspermonth where TypeID='.$_REQUEST['TypeID'].' limit 1';
					 $stmtc=$link->query($sqlc);
					 if($stmtc->rowCount()!=0){
						 echo '<br/><br/><h4>This budget type has been used. You cannot edit this.</h4>';
                                                 exit();
					 }
	
 }
     include('../backendphp/layout/genlists.php');
	

break;


}
?>