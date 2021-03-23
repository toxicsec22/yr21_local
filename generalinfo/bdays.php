<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
include_once($path.'/'.$url_folder.'/switchboard/contents.php');
include_once ($path.'/acrossyrs/dbinit/userinit.php');


    
    $title='Birthdays and Branch Anniversaries';
    $sql='Select * from `birthdays`';
    $columnnames=array('Nickname','SurName','Branch','Bday');
    $showbranches=false;
     include('../backendphp/layout/displayastablenosort.php');
 $link=null; $stmt=null;     
    ?>
