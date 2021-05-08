<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 

include_once('../generalinfo/lists.inc');
$whichqry=$_GET['whichqry'];

$showbranches=false; 
//$branchno=$_SESSION['bnum']; not yet needed
    
    switch ($whichqry){
       case 'Category':
              $title='Category List';
              $txnidname='CatNo';
              $sql='Select * from invty_1category order by Category';
              $columnnames=array('Category','CatNo');
              break;
       default:
              break;
}
include('../backendphp/layout/displayastabletest.php');
  $link=null; $stmt=null;
 ?>
</body>
</html>