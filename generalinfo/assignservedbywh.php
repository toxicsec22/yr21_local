<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(64472,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');
include_once('../backendphp/layout/linkstyle.php');
$which=!isset($_GET['w'])?'List':$_GET['w'];

$title='Branches Served per Warehouses'; $width='55%';
$list='List';
$table='1branches';
$txnid='BranchNo';
$txnidname='BranchNo';

$sql='SELECT b.BranchNo, b.Branch, b.RegisteredAddress, b.Active, ServedByWH, (SELECT Branch from 1branches where BranchNo=b.ServedByWH) as ServedByWareHouse, concat(e.Nickname," ",e.SurName) as EncodedBy, b.TimeStamp FROM `1branches` b left join 1employees e on e.IDNo=b.EncodedByNo ';

$columnnameslist=array('BranchNo','Branch','ServedByWareHouse','EncodedBy','TimeStamp');
$columnstoadd=array('');
$columnstoedit=array('ServedByWH');
$columnswithlists=array('ServedByWH');
$listsname=array('ServedByWH'=>'WareHouseList');
$listssql=array(
  array('sql'=>'Select * FROM `1branches` WHERE PseudoBranch=2 AND Active<>0', 'listvalue'=>'Branch', 'label'=>'ServedByWH','listname'=>'WareHouseList')
);

  if($which=='List') {
    
  echo '<title>'.$title.'</title>';
  echo '<br><h3>'.$title.'</h3>';
    $sql.=' WHERE Active=1 AND BranchNo>0';
  }
$columnentriesarray=array();
// }
$file='assignservedbywh.php?w='; $fieldsinrow=6; $liststoshow=array();

$editcommand='Edit'; $editspecs='EditSpecifics';$editallowed=64472;
$delcommand='';$addcommand='';

if (allowedToOpen(64472,'1rtc')) { $editprocess='assignservedbywh.php?w=EditSpecifics&BranchNo='; $editprocesslabel='Edit';}
// set first field only if the first field should also be added/edited
$firstfield='ServedByWH';
$encodedbyno=true;
//set a first field so commas will work
$sqlinsert='INSERT INTO `'.$table.'` SET ';
$sqlupdate='UPDATE `'.$table.'` SET ';

//$orderby = ' ORDER BY BranchNo';
include('../backendphp/layout/genlists.php');

?>
