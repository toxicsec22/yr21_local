<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6558,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');} 
include_once('../switchboard/contents.php');
 
       
$title='Update Branch IP Address';
// $pagetouse="changebranchip.php?w=EditSpecifics&";

$fieldname='BranchNo';

$processlabel1='Edit';

$txnid='BranchNo';
$orderby='Branch';    

$columnnames=array('BranchNo','Branch','IPAdd');
$method='POST';
$showbranches=false;
include_once('../backendphp/layout/clickontabletoedithead.php');

$which=(!isset($_GET['w'])?'List':$_GET['w']);

switch ($which){
   case 'List': 


?>
    <table style="display: inline-block; border: 1px solid; float: left; ">
 <?php
$sql='Select BranchNo, Branch, IPAdd from 1branches where Active<>0 and BranchNo<97 Order By Branch;';
$process1='changebranchip.php?w=EditSpecifics&';
include_once('../backendphp/layout/clickontabletoeditbody.php');
break;

case 'EditSpecifics':

$title='Edit IP Address';
$branchno=$_REQUEST['BranchNo'];

$columnstoedit=array('IPAdd');

$editprocess='changebranchip.php?w=EditIP&BranchNo='.$branchno;
$editprocesslabel='Edit';
$sql='Select BranchNo, Branch, IPAdd from 1branches WHERE (BranchNo)='.$branchno;

include('../backendphp/layout/editspecificsforlists.php');
    break;

case 'EditIP':
    
    $branchno=$_REQUEST['BranchNo'];
			$stmt=$link->prepare('UPDATE 1branches SET `1branches`.IPAdd = \''.$_REQUEST['IPAdd'].'\' WHERE (((`1branches`.BranchNo)=:BranchNo))');
			$stmt->bindValue(':BranchNo', $branchno, PDO::PARAM_STR);
			$stmt->execute();

			header("Location:changebranchip.php?BranchNo=".$branchno);
       break;
}
  $link=null; $stmt=null;
?>